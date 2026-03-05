<?php
include "config.php";

header('Content-Type: application/json');

/* ===== STATS ===== */

$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")
    ->fetch_assoc()['count'];

$totalAthletes = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='athlete'")
    ->fetch_assoc()['count'];

$totalOrganizers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='organizer'")
    ->fetch_assoc()['count'];


/* ===== RECENT USERS ===== */

$recentUsersQuery = $conn->query("
    SELECT username, email 
    FROM users 
    WHERE role != 'admin'
    ORDER BY id DESC 
    LIMIT 5
");

$recentUsers = [];

while($row = $recentUsersQuery->fetch_assoc()){
    $recentUsers[] = $row;
}


/* ===== NOTIFICATIONS ===== */

$notificationsQuery = $conn->query("
    SELECT message, created_at 
    FROM notifications 
    ORDER BY created_at DESC 
    LIMIT 10
");

$notifications = [];

while($row = $notificationsQuery->fetch_assoc()){
    $notifications[] = $row;
}


/* ===== RETURN JSON ===== */

echo json_encode([
    "totalUsers" => $totalUsers,
    "totalAthletes" => $totalAthletes,
    "totalOrganizers" => $totalOrganizers,
    "recentUsers" => $recentUsers,
    "notifications" => $notifications
]);
?>