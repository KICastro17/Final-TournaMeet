<?php
header('Content-Type: application/json');
session_start();
include('../../../config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body      = json_decode(file_get_contents('php://input'), true);
$tid       = intval($body['tournament_id'] ?? 0);
$team_name = trim($body['team_name']       ?? '');
$members   = trim($body['members']         ?? '');

// Get username from session
$athlete_username = $_SESSION['username'] ?? '';

if (!$tid || !$athlete_username) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields or not logged in']);
    exit;
}

// Get tournament
$stmt = $conn->prepare("SELECT slots_total, slots_taken, is_closed FROM tournaments WHERE id = ?");
$stmt->bind_param('i', $tid);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$t) {
    http_response_code(404);
    echo json_encode(['error' => 'Tournament not found']);
    exit;
}

if ($t['is_closed']) {
    http_response_code(409);
    echo json_encode(['error' => 'Registration for this tournament is closed']);
    exit;
}

if ($t['slots_taken'] >= $t['slots_total']) {
    http_response_code(409);
    echo json_encode(['error' => 'This tournament is already full']);
    exit;
}

// Check duplicate
$stmt = $conn->prepare("SELECT id FROM tournament_registrations WHERE tournament_id = ? AND athlete_username = ?");
$stmt->bind_param('is', $tid, $athlete_username);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exists) {
    http_response_code(409);
    echo json_encode(['error' => 'You are already registered for this tournament']);
    exit;
}

// Insert
$stmt = $conn->prepare("
    INSERT INTO tournament_registrations (tournament_id, athlete_username, team_name, members, status)
    VALUES (?, ?, ?, ?, 'pending')
");
$stmt->bind_param('isss', $tid, $athlete_username, $team_name, $members);

if ($stmt->execute()) {
    $conn->query("UPDATE tournaments SET slots_taken = slots_taken + 1 WHERE id = $tid");
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>