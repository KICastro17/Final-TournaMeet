<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id         = (int)($data['id']         ?? 0);
$created_by = $_SESSION['username'];
$name       = trim($data['name']        ?? '');
$sport      = trim($data['sport']       ?? '');
$date       = trim($data['date']        ?? '');
$time       = trim($data['time']        ?? '');
$venue      = trim($data['venue']       ?? '');
$prize      = trim($data['prize']       ?? '');
$lat        = !empty($data['lat'])      ? (float)$data['lat']  : null;
$lng        = !empty($data['lng'])      ? (float)$data['lng']  : null;
$max        = !empty($data['max'])      ? (int)$data['max']    : 16;
$format     = trim($data['format']      ?? '');
$deadline   = !empty($data['deadline']) ? $data['deadline']    : null;
$desc       = trim($data['desc']        ?? '');

if (!$id || !$name || !$sport || !$date || !$venue) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$stmt = $conn->prepare("
    UPDATE tournaments
    SET name=?, sport=?, location=?, date=?, time=?,
        format=?, prize=?, description=?, slots_total=?,
        registration_deadline=?, latitude=?, longitude=?
    WHERE id=? AND created_by=?
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssssssssissddis",
    $name, $sport, $venue, $date, $time,
    $format, $prize, $desc, $max,
    $deadline, $lat, $lng,
    $id, $created_by
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>