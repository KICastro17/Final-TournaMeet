<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

if(!isset($_POST['id'])){
    header("Location: profile.php");
    exit;
}

$post_id = intval($_POST['id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();

header("Location: profile.php");
exit;
?>