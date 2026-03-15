<?php
session_start();
include("config.php");

$data   = json_decode(file_get_contents('php://input'), true);
$id     = (int)($data['id'] ?? 0);
$action = $data['action'] ?? '';
$uid    = (int)($_SESSION['user_id'] ?? 0);

if ($action === 'read' && $id) {
    $conn->query("UPDATE user_notifications SET is_read=1 WHERE id=$id AND user_id=$uid");
}
if ($action === 'read_all' && $uid) {
    $conn->query("UPDATE user_notifications SET is_read=1 WHERE user_id=$uid");
}

echo json_encode(['ok' => true]);