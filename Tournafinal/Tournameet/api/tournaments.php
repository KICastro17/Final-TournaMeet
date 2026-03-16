<?php
header('Content-Type: application/json');
include('config.php'); // Tourna/config.php

$categoryMap = [
    'ball'       => ['Ball Sports','Basketball','Football','Volleyball','Rugby','Baseball',
                     'Handball','Sepak Takraw','3x3 Basketball','Beach Volleyball','Futsal','Softball'],
    'racket'     => ['Racket Sports','Badminton','Tennis','Squash','Pickleball','Table Tennis'],
    'combatives' => ['Combatives','Boxing','MMA','Karate','Judo','Taekwondo','Wrestling',
                     'Arnis','Muay Thai','Jiu Jitsu'],
    'endurance'  => ['Endurance','Running','Cycling','Triathlon','Marathon','Swimming',
                     'Rowing','Cross Country','Trail Running'],
    'precision'  => ['Precision','Archery','Shooting','Darts','Golf','Bowling','Billiards'],
    'esports'    => ['E-sports','Esports','FPS','MOBA','Fighting','RTS','Battle Royale',
                     'Mobile Legends','Valorant','DOTA 2','Street Fighter'],
];

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$sort     = isset($_GET['sort'])     ? trim($_GET['sort'])     : '';

$sql    = "SELECT * FROM tournaments WHERE is_closed = 0 AND date >= CURDATE()";
$params = [];
$types  = '';

if ($category && isset($categoryMap[$category])) {
    $sports       = $categoryMap[$category];
    $placeholders = implode(',', array_fill(0, count($sports), '?'));
    $sql   .= " AND sport IN ($placeholders)";
    $types .= str_repeat('s', count($sports));
    $params = array_merge($params, $sports);
}

if ($search !== '') {
    $sql   .= " AND (name LIKE ? OR sport LIKE ? OR location LIKE ?)";
    $like   = '%' . $search . '%';
    $types .= 'sss';
    $params = array_merge($params, [$like, $like, $like]);
}

switch ($sort) {
    case 'az':     $sql .= ' ORDER BY name ASC';   break;
    case 'za':     $sql .= ' ORDER BY name DESC';  break;
    case 'newest': $sql .= ' ORDER BY date DESC';  break;
    case 'slots':  $sql .= ' ORDER BY slots_total DESC'; break;
    default:       $sql .= ' ORDER BY date ASC, id ASC';
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$data = [];
foreach ($rows as $row) {
    $tid = intval($row['id']);

    // Count registrations
    $rs = $conn->prepare("SELECT COUNT(*) FROM tournament_registrations WHERE tournament_id = ? AND status IN ('pending','approved')");
    $slotsTaken = 0;
    if ($rs) {
        $rs->bind_param('i', $tid);
        $rs->execute();
        $rs->bind_result($slotsTaken);
        $rs->fetch();
        $rs->close();
    }

    $fee      = floatval($row['registration_fee'] ?? 0);
    $feeStr   = $fee > 0 ? '₱' . number_format($fee, 2) : 'Free';
    $prize    = floatval($row['prize_pool'] ?? 0);
    $prizeStr = $prize > 0 ? '₱' . number_format($prize, 2) : ($row['prize'] ?? '—');

    // Format time
    $timeStr = '';
    if (!empty($row['time'])) {
        $t = DateTime::createFromFormat('H:i:s', $row['time']);
        if (!$t) $t = DateTime::createFromFormat('H:i', $row['time']);
        if ($t) $timeStr = $t->format('g:i A');
    }

    $data[] = [
        'id'          => $tid,
        'name'        => $row['name']        ?? '',
        'sport'       => $row['sport']       ?? '',
        'organizer'   => $row['organizer']   ?? ($row['created_by'] ?? 'Organizer'),
        'location'    => $row['location']    ?? '',
        'date'        => $row['date']        ?? '',
        'time'        => $timeStr,
        'slots_total' => intval($row['slots_total'] ?? 0),
        'slots_taken' => intval($slotsTaken),
        'prize'       => $prizeStr,
        'entry_fee'   => $feeStr,
        'format'      => $row['format']      ?? 'Standard',
        'description' => $row['description'] ?? '',
        'image_url'   => $row['image_url']   ?? null,
        'is_closed'   => intval($row['is_closed'] ?? 0),
    ];
}

echo json_encode(['data' => $data]);