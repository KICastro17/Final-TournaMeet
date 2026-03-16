<?php
header('Content-Type: application/json');
include('config.php'); // Tourna/config.php

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing tournament id']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM tournaments WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Tournament not found']);
    exit;
}

// Count registrations
$slotsTaken = 0;
$rs = $conn->prepare("SELECT COUNT(*) FROM tournament_registrations WHERE tournament_id = ? AND status IN ('pending','approved')");
if ($rs) {
    $rs->bind_param('i', $id);
    $rs->execute();
    $rs->bind_result($slotsTaken);
    $rs->fetch();
    $rs->close();
}

$fee      = floatval($row['entrance_fee'] ?? 0);
$feeStr   = $fee > 0 ? '₱' . number_format($fee, 2) : 'Free';
$prize    = floatval($row['prize_pool'] ?? 0);
$prizeStr = $prize > 0 ? '₱' . number_format($prize, 2) : ($row['prize'] ?? '—');

$timeStr = '';
if (!empty($row['time'])) {
    $t = DateTime::createFromFormat('H:i:s', $row['time']);
    if (!$t) $t = DateTime::createFromFormat('H:i', $row['time']);
    if ($t) $timeStr = $t->format('g:i A');
}

$data = [
    'id'             => intval($row['id']),
    'name'           => $row['name']        ?? '',
    'sport'          => $row['sport']       ?? '',
    'organizer'      => $row['organizer']   ?? ($row['created_by'] ?? 'Organizer'),
    'location'       => $row['location']    ?? '',
    'date'           => $row['date']        ?? '',
    'time'           => $timeStr,
    'slots_total'    => intval($row['slots_total'] ?? 0),
    'slots_taken'    => intval($slotsTaken),
    'prize'          => $prizeStr,
    'entrance_fee'   => $fee,
    'entry_fee'      => $feeStr,
    'format'         => $row['format']      ?? 'Standard',
    'description'    => $row['description'] ?? '',
    'image_url'      => $row['image_url']   ?? null,
    'requirements'   => $row['requirements']   ?? '',
    'organizer_note' => $row['organizer_note'] ?? '',
    'is_closed'      => intval($row['is_closed'] ?? 0),
    'deadline'       => $row['registration_deadline'] ?? '',
];

echo json_encode(['data' => $data]);