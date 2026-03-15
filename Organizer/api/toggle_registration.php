<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

if (empty($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data      = json_decode(file_get_contents('php://input'), true);
$id        = (int)($data['id']        ?? 0);
$is_closed = (int)($data['is_closed'] ?? 0);
$username  = $_SESSION['username'];

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$stmt = $conn->prepare("UPDATE tournaments SET is_closed = ? WHERE id = ? AND created_by = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$stmt->bind_param("iis", $is_closed, $id, $username);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed or not your tournament']);
}

$stmt->close();
$conn->close();
?>