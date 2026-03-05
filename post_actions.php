<?php
ob_start(); // catch any accidental output
session_start();
require 'config.php';
ob_clean(); // discard anything output so far
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit();
}

$user_id = (int)$_SESSION['user_id'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

/* ══════════════════════════════════════
   GET: load reactions + comments for a post
══════════════════════════════════════ */
if ($action === 'load') {
    $post_id = (int)($_GET['post_id'] ?? 0);
    if (!$post_id) { echo json_encode(['error' => 'Invalid post']); exit(); }

    $counts     = [];
    $myReaction = null;
    $comments   = [];

    // Check tables exist
    $r1 = $conn->query("SHOW TABLES LIKE 'post_reactions'");
    $r2 = $conn->query("SHOW TABLES LIKE 'post_comments'");
    $hasReactions = $r1 && $r1->num_rows > 0;
    $hasComments  = $r2 && $r2->num_rows > 0;

    if ($hasReactions) {
        $res = $conn->query("SELECT reaction, COUNT(*) AS cnt FROM post_reactions WHERE post_id = $post_id GROUP BY reaction");
        if ($res) while ($row = $res->fetch_assoc()) $counts[$row['reaction']] = (int)$row['cnt'];

        $stmt = $conn->prepare("SELECT reaction FROM post_reactions WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $myReaction = $stmt->get_result()->fetch_assoc()['reaction'] ?? null;
        $stmt->close();
    }

    if ($hasComments) {
        $stmt = $conn->prepare("
            SELECT c.id, c.comment, c.created_at, c.user_id,
                   u.username, u.profile_pic
            FROM post_comments c
            JOIN users u ON u.id = c.user_id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    echo json_encode([
        'success'       => true,
        'counts'        => $counts,
        'myReaction'    => $myReaction,
        'comments'      => $comments,
        'total'         => array_sum($counts),
        'tablesExist'   => ($hasReactions && $hasComments)
    ]);
    exit();
}

/* ══════════════════════════════════════
   POST: toggle reaction
══════════════════════════════════════ */
if ($action === 'react') {
    $post_id  = (int)($_POST['post_id'] ?? 0);
    $reaction = $_POST['reaction'] ?? '';
    $allowed  = ['like','love','fire','wow','haha'];

    if (!$post_id || !in_array($reaction, $allowed)) {
        echo json_encode(['error' => 'Invalid']); exit();
    }

    // Check if user already reacted
    $stmt = $conn->prepare("SELECT id, reaction FROM post_reactions WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        if ($existing['reaction'] === $reaction) {
            // Same reaction → remove (toggle off)
            $conn->query("DELETE FROM post_reactions WHERE post_id = $post_id AND user_id = $user_id");
            $myReaction = null;
        } else {
            // Different reaction → update
            $stmt = $conn->prepare("UPDATE post_reactions SET reaction = ? WHERE post_id = ? AND user_id = ?");
            $stmt->bind_param("sii", $reaction, $post_id, $user_id);
            $stmt->execute();
            $stmt->close();
            $myReaction = $reaction;
        }
    } else {
        // New reaction
        $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction) VALUES (?,?,?)");
        $stmt->bind_param("iis", $post_id, $user_id, $reaction);
        $stmt->execute();
        $stmt->close();
        $myReaction = $reaction;
    }

    // Return updated counts
    $res = $conn->query("SELECT reaction, COUNT(*) AS cnt FROM post_reactions WHERE post_id = $post_id GROUP BY reaction");
    $counts = [];
    while ($row = $res->fetch_assoc()) $counts[$row['reaction']] = (int)$row['cnt'];

    echo json_encode([
        'success'    => true,
        'counts'     => $counts,
        'myReaction' => $myReaction,
        'total'      => array_sum($counts)
    ]);
    exit();
}

/* ══════════════════════════════════════
   POST: add comment
══════════════════════════════════════ */
if ($action === 'comment') {
    $post_id = (int)($_POST['post_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if (!$post_id || empty($comment)) {
        echo json_encode(['error' => 'Comment cannot be empty.']); exit();
    }
    if (mb_strlen($comment) > 500) {
        echo json_encode(['error' => 'Comment too long.']); exit();
    }

    // Check table exists
    $check = $conn->query("SHOW TABLES LIKE 'post_comments'");
    if (!$check || $check->num_rows === 0) {
        echo json_encode(['error' => 'Table post_comments does not exist. Run reactions_comments_migration.sql first.']); exit();
    }

    $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment) VALUES (?,?,?)");
    if (!$stmt) {
        echo json_encode(['error' => 'DB prepare failed: ' . $conn->error]); exit();
    }
    $stmt->bind_param("iis", $post_id, $user_id, $comment);
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'DB execute failed: ' . $stmt->error]); exit();
    }
    $comment_id = $conn->insert_id;
    $stmt->close();

    // Fetch user info for response
    $stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo json_encode([
        'success'    => true,
        'comment_id' => $comment_id,
        'comment'    => htmlspecialchars($comment),
        'username'   => htmlspecialchars($u['username']),
        'profile_pic'=> $u['profile_pic'],
        'created_at' => date('M j, Y · g:i A')
    ]);
    exit();
}

/* ══════════════════════════════════════
   POST: delete comment (own only)
══════════════════════════════════════ */
if ($action === 'delete_comment') {
    $comment_id = (int)($_POST['comment_id'] ?? 0);
    if (!$comment_id) { echo json_encode(['error' => 'Invalid']); exit(); }

    $stmt = $conn->prepare("DELETE FROM post_comments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['error' => 'Unknown action']);