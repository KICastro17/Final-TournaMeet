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
        $pdo->prepare("DELETE FROM post_likes WHERE post_id=? AND user_id=?")->execute([$post_id,$current_user_id]);
        $liked = false;
    } else {
        $pdo->prepare("INSERT IGNORE INTO post_likes (post_id,user_id) VALUES (?,?)")->execute([$post_id,$current_user_id]);
        $liked = true;
    }
    $count = $pdo->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id=?");
    $count->execute([$post_id]);
    echo json_encode(['success'=>true,'liked'=>$liked,'like_count'=>(int)$count->fetchColumn()]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}