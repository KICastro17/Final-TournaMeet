<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$created_by = $_SESSION['username'];
$name       = trim($data['name']     ?? '');
$sport      = trim($data['sport']    ?? '');
$date       = trim($data['date']     ?? '');
$time       = trim($data['time']     ?? '');
$venue      = trim($data['venue']    ?? '');
$prize      = trim($data['prize']    ?? '');
$lat        = !empty($data['lat'])   ? (float)$data['lat']  : null;
$lng        = !empty($data['lng'])   ? (float)$data['lng']  : null;
$max        = !empty($data['max'])   ? (int)$data['max']    : 16;
$format     = trim($data['format']   ?? '');
$deadline   = !empty($data['deadline']) ? $data['deadline'] : null;
$desc       = trim($data['desc']     ?? '');
$fee        = isset($data['fee']) && $data['fee'] !== '' ? (float)$data['fee'] : 0.00;

if (!$name || !$sport || !$date || !$venue) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO tournaments
        (name, sport, location, date, time, format, prize, entrance_fee, description,
         slots_total, slots_taken, organizer, created_by,
         registration_deadline, latitude, longitude)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB prepare error: ' . $conn->error]);
    exit;
}

$stmt->bind_param(
    "sssssssdsisssdd",
    $name, $sport, $venue, $date, $time, $format, $prize, $fee, $desc,
    $max, $created_by, $created_by, $deadline, $lat, $lng
);

if ($stmt->execute()) {
    $tournament_id = $conn->insert_id;

    // ── Notify all athletes about the new tournament ──
    $formatted_date = date('M j, Y', strtotime($date));
    $message = "$created_by created a new $sport tournament: \"$name\" on $formatted_date.";

    $athletes = $conn->query("SELECT id FROM users WHERE role = 'athlete'");
    if ($athletes && $athletes->num_rows > 0) {
        $notif_stmt = $conn->prepare("
            INSERT INTO user_notifications (user_id, type, message, is_read, created_at)
            VALUES (?, 'tournament', ?, 0, NOW())
        ");
        while ($athlete = $athletes->fetch_assoc()) {
            // Don't notify yourself
            if ($athlete['id'] == ($_SESSION['user_id'] ?? 0)) continue;
            $notif_stmt->bind_param("is", $athlete['id'], $message);
            $notif_stmt->execute();
        }
        $notif_stmt->close();
    }

    echo json_encode(['success' => true, 'id' => $tournament_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>