<?php
ob_start();
ini_set('session.cookie_path', '/');
session_set_cookie_params(['path' => '/']);
session_start();
ob_clean();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php'; // sets $pdo and $current_user_id

if (!$current_user_id) {
    echo json_encode(['error' => 'Unauthorized']); exit();
}

$action  = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = (int)$current_user_id;

// ── Helper: insert notification (never crashes) ──
function insertNotif($pdo, $recipient_id, $type, $message) {
    if (!$recipient_id) return;
    try {
        $s = $pdo->prepare("
            INSERT INTO user_notifications (user_id, type, message, is_read, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        $s->execute([$recipient_id, $type, $message]);
    } catch (Exception $e) {
        error_log("insertNotif error: " . $e->getMessage());
    }
}

// ── LOAD reactions + comments ──
if ($action === 'load') {
    $post_id = (int)($_GET['post_id'] ?? 0);
    if (!$post_id) { echo json_encode(['success'=>false,'error'=>'Invalid post']); exit(); }

    $counts = []; $myReaction = null; $comments = [];

    try {
        $res = $pdo->prepare("SELECT reaction, COUNT(*) AS cnt FROM post_reactions WHERE post_id=? GROUP BY reaction");
        $res->execute([$post_id]);
        foreach ($res->fetchAll() as $row) $counts[$row['reaction']] = (int)$row['cnt'];

        $r2 = $pdo->prepare("SELECT reaction FROM post_reactions WHERE post_id=? AND user_id=?");
        $r2->execute([$post_id, $user_id]);
        $myReaction = ($r2->fetch()['reaction']) ?? null;
    } catch(Exception $e) {}

    try {
        $r3 = $pdo->prepare("
            SELECT c.id, c.comment, c.created_at, c.user_id,
                   u.username, u.profile_pic
            FROM post_comments c
            JOIN users u ON u.id = c.user_id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $r3->execute([$post_id]);
        $comments = $r3->fetchAll();
        foreach ($comments as &$cm) {
            $cm['profile_pic'] = $cm['profile_pic'] ? '/Tourna/uploads' . $cm['profile_pic'] : null;
        }
    } catch(Exception $e) { $comments = []; }

    echo json_encode([
        'success'    => true,
        'counts'     => $counts,
        'myReaction' => $myReaction,
        'comments'   => $comments,
        'total'      => array_sum($counts),
    ]);
    exit();
}

// ── REACT (toggle) ──
if ($action === 'react') {
    $post_id  = (int)($_POST['post_id'] ?? 0);
    $reaction = $_POST['reaction'] ?? '';
    $allowed  = ['like','love','fire','wow','haha'];
    if (!$post_id || !in_array($reaction, $allowed)) { echo json_encode(['error'=>'Invalid']); exit(); }

    $check = $pdo->prepare("SELECT id, reaction FROM post_reactions WHERE post_id=? AND user_id=?");
    $check->execute([$post_id, $user_id]);
    $existing = $check->fetch();

    if ($existing) {
        if ($existing['reaction'] === $reaction) {
            $pdo->prepare("DELETE FROM post_reactions WHERE post_id=? AND user_id=?")->execute([$post_id,$user_id]);
            $myReaction = null;
        } else {
            $pdo->prepare("UPDATE post_reactions SET reaction=? WHERE post_id=? AND user_id=?")->execute([$reaction,$post_id,$user_id]);
            $myReaction = $reaction;
        }
    } else {
        $pdo->prepare("INSERT IGNORE INTO post_reactions (post_id,user_id,reaction) VALUES (?,?,?)")->execute([$post_id,$user_id,$reaction]);
        $myReaction = $reaction;

        // ── Notify post owner (only on new react, not on toggle off) ──
        $emoji_map = ['like'=>'👍','love'=>'❤️','fire'=>'🔥','wow'=>'😮','haha'=>'😂'];
        $emoji = $emoji_map[$reaction] ?? '';

        // Get post owner + reactor username
        $postRow = $pdo->prepare("SELECT user_id FROM posts WHERE id = ? LIMIT 1");
        $postRow->execute([$post_id]);
        $post_owner_id = (int)($postRow->fetchColumn() ?? 0);

        // Don't notify yourself
        if ($post_owner_id && $post_owner_id !== $user_id) {
            $uRow = $pdo->prepare("SELECT username FROM users WHERE id = ? LIMIT 1");
            $uRow->execute([$user_id]);
            $reactor_name = $uRow->fetchColumn() ?: 'Someone';
            insertNotif($pdo, $post_owner_id, 'like', "$reactor_name reacted $emoji to your post.");
        }
    }

    $res = $pdo->prepare("SELECT reaction, COUNT(*) AS cnt FROM post_reactions WHERE post_id=? GROUP BY reaction");
    $res->execute([$post_id]);
    $counts = [];
    foreach ($res->fetchAll() as $row) $counts[$row['reaction']] = (int)$row['cnt'];

    echo json_encode(['success'=>true,'counts'=>$counts,'myReaction'=>$myReaction,'total'=>array_sum($counts)]);
    exit();
}

// ── COMMENT ──
if ($action === 'comment') {
    $post_id = (int)($_POST['post_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if (!$post_id || !$comment) { echo json_encode(['success'=>false,'error'=>'Missing data']); exit(); }
    if (mb_strlen($comment) > 500) { echo json_encode(['success'=>false,'error'=>'Too long']); exit(); }

    try {
        $pdo->prepare("INSERT INTO post_comments (post_id,user_id,comment) VALUES (?,?,?)")->execute([$post_id,$user_id,$comment]);
        $new_id = $pdo->lastInsertId();

        $me = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
        $me->execute([$user_id]);
        $me = $me->fetch();

        // ── Notify post owner ──
        $postRow = $pdo->prepare("SELECT user_id FROM posts WHERE id = ? LIMIT 1");
        $postRow->execute([$post_id]);
        $post_owner_id = (int)($postRow->fetchColumn() ?? 0);

        // Don't notify yourself
        if ($post_owner_id && $post_owner_id !== $user_id) {
            $commenter_name = $me['username'] ?? 'Someone';
            insertNotif($pdo, $post_owner_id, 'comment', "$commenter_name commented on your post.");
        }

        echo json_encode([
            'success'     => true,
            'comment_id'  => (int)$new_id,
            'comment'     => htmlspecialchars($comment),
            'username'    => $me['username'],
            'profile_pic' => $me['profile_pic'] ? '/Tourna/uploads' . $me['profile_pic'] : null,
            'created_at'  => date('M j, Y · g:i A'),
        ]);
    } catch(Exception $e) {
        echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    }
    exit();
}

echo json_encode(['success'=>false,'error'=>'Unknown action: '.$action]);