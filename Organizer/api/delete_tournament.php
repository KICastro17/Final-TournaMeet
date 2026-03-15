<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id   = (int)($data['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid tournament ID']);
    exit;
}

$created_by = $_SESSION['username'];

// Only allow deleting own tournaments
$stmt = $conn->prepare("DELETE FROM tournaments WHERE id = ? AND created_by = ?");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB prepare error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("is", $id, $created_by);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tournament not found or not yours']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>