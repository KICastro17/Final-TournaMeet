<?php
session_start();
include "config.php";

/* ===== ADMIN CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    die("Access Denied");
}

if (isset($_GET['id']) && isset($_GET['status'])) {

    $id     = intval($_GET['id']);
    $status = $_GET['status'];

    $allowed = ['approved', 'banned', 'pending', 'suspended'];

    if (!in_array($status, $allowed)) {
        die("Invalid Status");
    }

    $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=? AND role != 'admin'");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: view_users.php");
exit();
?>