<?php
session_start();
require 'config.php';

/* ================= AUTH GUARD ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id       = $_SESSION['user_id'];
$username = trim($_POST['username'] ?? '');
$bio      = trim($_POST['bio']      ?? '');

if (empty($username)) {
    die("Username is required.");
}

/* ================= FETCH CURRENT PIC ================= */
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pic_filename = $current['profile_pic']; // keep existing by default

/* ================= HANDLE PROFILE PIC UPLOAD ================= */
if (!empty($_FILES['profile_pic']['name'])) {

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size      = 2 * 1024 * 1024; // 2MB

    $file_tmp  = $_FILES['profile_pic']['tmp_name'];
    $file_type = mime_content_type($file_tmp);
    $file_size = $_FILES['profile_pic']['size'];
    $file_ext  = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_type, $allowed_types)) {
        die("❌ Invalid file type. Only JPG, PNG, GIF, WEBP allowed.");
    }

    if ($file_size > $max_size) {
        die("❌ File too large. Maximum size is 2MB.");
    }

    // Create uploads folder if it doesn't exist
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }

    // Delete old picture if it exists
    if (!empty($pic_filename) && file_exists('uploads' . $pic_filename)) {
        unlink('uploads' . $pic_filename);
    }

    // Save new picture with unique name
    $new_filename  = '/' . uniqid('pfp_', true) . '.' . $file_ext;
    $upload_path   = 'uploads' . $new_filename;

    if (!move_uploaded_file($file_tmp, $upload_path)) {
        die("❌ Failed to upload picture. Please try again.");
    }

    $pic_filename = $new_filename;
}

/* ================= UPDATE DATABASE ================= */
$stmt = $conn->prepare("UPDATE users SET username = ?, bio = ?, profile_pic = ? WHERE id = ?");
$stmt->bind_param("sssi", $username, $bio, $pic_filename, $id);

if ($stmt->execute()) {
    $_SESSION['username'] = $username; // keep session in sync
    header("Location: profile.php?updated=1");
    exit();
} else {
    die("❌ Failed to update profile: " . $conn->error);
}

$stmt->close();
$conn->close();
?>
