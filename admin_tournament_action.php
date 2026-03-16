<?php
session_start();
include "config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data   = json_decode(file_get_contents('php://input'), true);
$id     = intval($data['id']     ?? 0);
$action = trim($data['action']   ?? '');

if (!$id || !in_array($action, ['approved', 'declined'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$stmt = $conn->prepare("UPDATE tournaments SET status = ? WHERE id = ?");
$stmt->bind_param("si", $action, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'action' => $action]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
?>