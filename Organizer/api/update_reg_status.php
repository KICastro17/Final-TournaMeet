<?php
session_start();
header('Content-Type: application/json');
include('../../config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }
if (empty($_SESSION['username'])) { echo json_encode(['success'=>false,'message'=>'Not logged in']); exit; }

$data     = json_decode(file_get_contents('php://input'), true);
$reg_id   = intval($data['id']   ?? 0);
$status   = trim($data['status'] ?? '');
$username = $_SESSION['username'];

if (!$reg_id || !in_array($status, ['approved','rejected','pending'])) { echo json_encode(['success'=>false,'message'=>'Invalid data']); exit; }

$stmt = $conn->prepare("UPDATE tournament_registrations tr JOIN tournaments t ON t.id = tr.tournament_id SET tr.status = ?, tr.reviewed_by = ?, tr.reviewed_at = NOW() WHERE tr.id = ? AND t.created_by = ?");
$stmt->bind_param('ssis', $status, $username, $reg_id, $username);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>'Update failed']);
}
$stmt->close();
?>