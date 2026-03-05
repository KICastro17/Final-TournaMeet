<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])) exit;

$id = $_SESSION['user_id'];

$username = $_POST['username'] ?? null;
$bio = $_POST['bio'] ?? null;

$filename = null;


/* ========= IMAGE UPLOAD ========= */

if (!empty($_FILES['profile_pic']['name'])) {

    $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $filename = "user_" . $id . "_" . time() . "." . $ext;

    move_uploaded_file(
        $_FILES['profile_pic']['tmp_name'],
        "uploads" . $filename
    );

    $stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
    $stmt->bind_param("si", $filename, $id);
    $stmt->execute();

    header("Location: profile.php");
    exit;
}


/* ========= TEXT UPDATE ========= */

$stmt = $conn->prepare("UPDATE users SET username=?, bio=? WHERE id=?");
$stmt->bind_param("ssi", $username, $bio, $id);
$stmt->execute();

header("Location: profile.php");
exit;