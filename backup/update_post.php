<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$caption = $_POST['caption'];

$stmt = $conn->prepare("
    UPDATE posts
    SET caption=?
    WHERE id=? AND user_id=?
");
$stmt->bind_param("sii", $caption, $post_id, $user_id);
$stmt->execute();

header("Location: profile.php");
exit;
?>