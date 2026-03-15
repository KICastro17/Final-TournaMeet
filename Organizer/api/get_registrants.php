<?php
session_start();
header('Content-Type: application/json');
include('../../config.php');

if (empty($_SESSION['username'])) { echo json_encode(['success'=>false,'message'=>'Not logged in']); exit; }

$tid      = intval($_GET['tournament_id'] ?? 0);
$username = $_SESSION['username'];
if (!$tid) { echo json_encode(['success'=>false,'message'=>'Invalid ID']); exit; }

$check = $conn->prepare("SELECT id FROM tournaments WHERE id = ? AND created_by = ?");
$check->bind_param('is', $tid, $username);
$check->execute();
if (!$check->get_result()->fetch_assoc()) { echo json_encode(['success'=>false,'message'=>'Not your tournament']); exit; }
$check->close();

$stmt = $conn->prepare("SELECT id, athlete_username, team_name, members, status, joined_at FROM tournament_registrations WHERE tournament_id = ? ORDER BY joined_at ASC");
$stmt->bind_param('i', $tid);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
echo json_encode(['success'=>true,'registrants'=>$rows]);
?>