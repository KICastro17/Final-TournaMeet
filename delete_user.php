<?php
session_start();
include "config.php";

if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$id = intval($_GET['id']);

$conn->query("DELETE FROM users WHERE id=$id");

header("Location: view_users.php");
exit();
?>