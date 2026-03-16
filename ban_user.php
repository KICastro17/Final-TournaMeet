<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}

if ($_SESSION['role'] !== "admin") {
    die("Admin only");
}

if (!isset($_GET['id'])) {
    die("User ID missing");
}

$id = intval($_GET['id']);

$sql = "UPDATE users SET status='banned' WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    header("Location: view_users.php");
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>