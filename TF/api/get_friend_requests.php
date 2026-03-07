<?php
/**
 * get_friend_requests.php
 * Returns count of pending incoming friend requests for the nav badge.
 */
require_once __DIR__ . '/db.php';

if (!$current_user_id) {
    echo json_encode(['success'=>true,'count'=>0]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS cnt FROM friendships
        WHERE friend_id=? AND status='pending'
    ");
    $stmt->execute([$current_user_id]);
    $row = $stmt->fetch();
    echo json_encode(['success'=>true,'count'=>(int)$row['cnt']]);
} catch (PDOException $e) {
    echo json_encode(['success'=>true,'count'=>0]);
}