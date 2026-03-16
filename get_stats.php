<?php
session_start();
include "config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$total    = $conn->query("SELECT COUNT(*) c FROM users WHERE role!='admin'")->fetch_assoc()['c'];
$pending  = $conn->query("SELECT COUNT(*) c FROM users WHERE status='pending'")->fetch_assoc()['c'];
$approved = $conn->query("SELECT COUNT(*) c FROM users WHERE status='approved'")->fetch_assoc()['c'];
$banned   = $conn->query("SELECT COUNT(*) c FROM users WHERE status='banned'")->fetch_assoc()['c'];

echo json_encode([
    'total'    => (int) $total,
    'pending'  => (int) $pending,
    'approved' => (int) $approved,
    'banned'   => (int) $banned,
]);

$conn->close();
?>
