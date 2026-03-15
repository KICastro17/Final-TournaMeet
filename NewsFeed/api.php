<?php
session_start();
require_once __DIR__ . '/../config.php'; // gives $conn (mysqli)

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthenticated']); exit;
}

$uid    = (int) $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

// ── Helper: send notification (skip if acting on own post) ───────────────
function sendNotif($conn, $to_user_id, $from_user_id, $type, $message) {
    if ($to_user_id === $from_user_id) return; // don't notify yourself
    $stmt = $conn->prepare("INSERT INTO user_notifications (user_id, type, message, is_read, created_at) VALUES (?,?,?,0,NOW())");
    $stmt->bind_param("iss", $to_user_id, $type, $message);
    $stmt->execute();
    $stmt->close();
}

switch ($action) {

    // ── TOGGLE LIKE ──────────────────────────────────────────────────────
    case 'toggle_like':
        $pid = (int)($_POST['post_id'] ?? 0);
        if (!$pid) { echo json_encode(['success'=>false,'error'=>'No post_id']); break; }

        $stmt = $conn->prepare("SELECT id FROM post_likes WHERE post_id=? AND user_id=?");
        $stmt->bind_param("ii", $pid, $uid);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $stmt = $conn->prepare("DELETE FROM post_likes WHERE post_id=? AND user_id=?");
            $stmt->bind_param("ii", $pid, $uid);
            $stmt->execute(); $stmt->close();
            $liked = false;
        } else {
            $stmt = $conn->prepare("INSERT IGNORE INTO post_likes (post_id,user_id) VALUES (?,?)");
            $stmt->bind_param("ii", $pid, $uid);
            $stmt->execute(); $stmt->close();
            $liked = true;

            // ── Notify post owner ────────────────────────────────────
            $post = $conn->query("SELECT user_id FROM posts WHERE id=$pid")->fetch_assoc();
            $liker = $conn->query("SELECT username FROM users WHERE id=$uid")->fetch_assoc();
            if ($post && $liker) {
                sendNotif($conn, (int)$post['user_id'], $uid, 'like',
                    $liker['username'] . ' liked your post.');
            }
        }

        $count = (int)$conn->query("SELECT COUNT(*) FROM post_likes WHERE post_id=$pid")->fetch_row()[0];
        echo json_encode(['success'=>true,'liked'=>$liked,'count'=>$count]);
        break;

    // ── ADD COMMENT ──────────────────────────────────────────────────────
    case 'add_comment':
        $pid     = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if (!$pid || !$content) { echo json_encode(['success'=>false,'error'=>'Missing params']); break; }

        $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment) VALUES (?,?,?)");
        $stmt->bind_param("iis", $pid, $uid, $content);
        $stmt->execute();
        $cid = $conn->insert_id;
        $stmt->close();

        // ── Notify post owner ────────────────────────────────────────
        $post      = $conn->query("SELECT user_id FROM posts WHERE id=$pid")->fetch_assoc();
        $commenter = $conn->query("SELECT username FROM users WHERE id=$uid")->fetch_assoc();
        if ($post && $commenter) {
            $preview = mb_strlen($content) > 40 ? mb_substr($content, 0, 40) . '…' : $content;
            sendNotif($conn, (int)$post['user_id'], $uid, 'comment',
                $commenter['username'] . ' commented: "' . $preview . '"');
        }

        $row = $conn->query("SELECT username, profile_pic FROM users WHERE id=$uid")->fetch_assoc();
        echo json_encode([
            'success' => true,
            'comment' => [
                'id'       => $cid,
                'content'  => $content,
                'username' => $row['username'],
                'pic'      => $row['profile_pic'] ? '../uploads' . $row['profile_pic'] : null,
                'ago'      => 'Just now',
            ]
        ]);
        break;

    // ── GET COMMENTS ─────────────────────────────────────────────────────
    case 'get_comments':
        $pid = (int)($_GET['post_id'] ?? 0);
        if (!$pid) { echo json_encode(['success'=>false,'error'=>'No post_id']); break; }

        $stmt = $conn->prepare("
            SELECT c.id, c.comment AS content, c.created_at, u.username, u.profile_pic
            FROM post_comments c
            JOIN users u ON u.id = c.user_id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $comments = [];
        foreach ($rows as $c) {
            $diff = time() - strtotime($c['created_at']);
            if ($diff < 60)         $ago = 'Just now';
            elseif ($diff < 3600)   $ago = floor($diff/60).'m ago';
            elseif ($diff < 86400)  $ago = floor($diff/3600).'h ago';
            else                    $ago = date('M j', strtotime($c['created_at']));

            $comments[] = [
                'id'       => $c['id'],
                'content'  => $c['content'],
                'username' => $c['username'],
                'pic'      => $c['profile_pic'] ? '../uploads' . $c['profile_pic'] : null,
                'ago'      => $ago,
            ];
        }
        echo json_encode(['success'=>true,'comments'=>$comments,'count'=>count($comments)]);
        break;

    // ── REACT ────────────────────────────────────────────────────────────
    case 'react':
        $pid   = (int)($_POST['post_id'] ?? 0);
        $emoji = trim($_POST['emoji'] ?? '');
        if (!$pid || !$emoji) { echo json_encode(['success'=>false,'error'=>'Missing params']); break; }

        $conn->query("CREATE TABLE IF NOT EXISTS post_reactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL, user_id INT NOT NULL,
            reaction VARCHAR(10) NOT NULL, created_at DATETIME DEFAULT NOW(),
            UNIQUE KEY uq_react (post_id, user_id)
        )");

        $stmt = $conn->prepare("SELECT reaction FROM post_reactions WHERE post_id=? AND user_id=?");
        $stmt->bind_param("ii", $pid, $uid);
        $stmt->execute();
        $cur = $stmt->get_result()->fetch_row()[0] ?? null;
        $stmt->close();

        if ($cur === $emoji) {
            $stmt = $conn->prepare("DELETE FROM post_reactions WHERE post_id=? AND user_id=?");
            $stmt->bind_param("ii", $pid, $uid);
            $stmt->execute(); $stmt->close();
            $myReaction = null;
        } else {
            $stmt = $conn->prepare("INSERT INTO post_reactions (post_id,user_id,reaction) VALUES (?,?,?) ON DUPLICATE KEY UPDATE reaction=VALUES(reaction)");
            $stmt->bind_param("iis", $pid, $uid, $emoji);
            $stmt->execute(); $stmt->close();
            $myReaction = $emoji;

            // ── Notify post owner ────────────────────────────────────
            $post    = $conn->query("SELECT user_id FROM posts WHERE id=$pid")->fetch_assoc();
            $reactor = $conn->query("SELECT username FROM users WHERE id=$uid")->fetch_assoc();
            if ($post && $reactor) {
                sendNotif($conn, (int)$post['user_id'], $uid, 'like',
                    $reactor['username'] . ' reacted ' . $emoji . ' to your post.');
            }
        }

        $rs = $conn->query("SELECT reaction, COUNT(*) AS cnt FROM post_reactions WHERE post_id=$pid GROUP BY reaction");
        $reactions = [];
        while ($r = $rs->fetch_assoc()) $reactions[$r['reaction']] = (int)$r['cnt'];
        echo json_encode(['success'=>true,'reactions'=>$reactions,'my_reaction'=>$myReaction]);
        break;

    default:
        echo json_encode(['success'=>false,'error'=>"Unknown action: $action"]);
}

$conn->close();