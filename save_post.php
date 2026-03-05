<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$caption = $_POST['content'] ?? "";

$mediaName = null;

/* ========= UPLOAD MEDIA (image OR video) ========= */
if(!empty($_FILES['media']['name'])){

    $folder = "uploads/";

    // create folder if missing
    if(!is_dir($folder)){
        mkdir($folder, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));

    // allowed types only (security)
    $allowed = ['jpg','jpeg','png','gif','webp','mp4','webm','mov'];

    if(in_array($ext, $allowed)){

        $mediaName = time() . "_" . rand(1000,9999) . "." . $ext;

        move_uploaded_file(
            $_FILES['media']['tmp_name'],
            $folder . $mediaName
        );
    }
}

/* ========= SAVE ========= */
$stmt = $conn->prepare("
    INSERT INTO posts (user_id, caption, image)
    VALUES (?, ?, ?)
");

$stmt->bind_param("iss", $user_id, $caption, $mediaName);
$stmt->execute();

header("Location: profile.php");
exit;
?>