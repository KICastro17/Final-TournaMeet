<?php
require_once __DIR__ . '/db.php';
if (!$current_user_id) { echo json_encode(['success'=>false,'error'=>'Not logged in']); exit; }
$body    = json_decode(file_get_contents('php://input'), true);
$post_id = (int)($body['post_id'] ?? 0);
if (!$post_id) { echo json_encode(['success'=>false,'error'=>'Invalid post']); exit; }
try {
    $check = $pdo->prepare("SELECT id FROM post_likes WHERE post_id=? AND user_id=?");
    $check->execute([$post_id, $current_user_id]);
    if ($check->fetch()) {
        // Unlike — remove like, no notification
        $pdo->prepare("DELETE FROM post_likes WHERE post_id=? AND user_id=?")->execute([$post_id,$current_user_id]);
        $liked = false;
    } else {
        // Like — insert like
        $pdo->prepare("INSERT IGNORE INTO post_likes (post_id,user_id) VALUES (?,?)")->execute([$post_id,$current_user_id]);
        $liked = true;

        // Get post owner
        $owner = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
        $owner->execute([$post_id]);
        $post_owner_id = (int)$owner->fetchColumn();

        // Get liker's username
        $uname = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $uname->execute([$current_user_id]);
        $liker_username = $uname->fetchColumn();

        // Only notify if it's not the user's own post
        if ($post_owner_id && $post_owner_id !== $current_user_id) {
            $pdo->prepare("
                INSERT INTO user_notifications (user_id, type, message)
                VALUES (?, 'like', ?)
            ")->execute([$post_owner_id, $liker_username . ' liked your post.']);
        }
    }

    $count = $pdo->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id=?");
    $count->execute([$post_id]);
    echo json_encode(['success'=>true,'liked'=>$liked,'like_count'=>(int)$count->fetchColumn()]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}