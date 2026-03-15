<?php
session_start();
include "config.php";

/* ================= CHECK CONNECTION ================= */
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

/* ================= CHECK REQUEST ================= */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

/* ================= GET INPUT ================= */
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    die("Email and Password are required.");
}

/* ================= FIND USER ================= */
$sql  = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare Failed: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {

    $user = $result->fetch_assoc();

    /* ================= VERIFY PASSWORD ================= */
    if (password_verify($password, $user['password'])) {

        /* ================= APPROVAL CHECK ================= */
        if ($user['status'] !== 'approved') {
            die("⛔ Your account is waiting for admin approval.");
        }

        /* ================= LOGIN SUCCESS ================= */
        $_SESSION['user_id']         = $user['id'];
        $_SESSION['username']        = $user['username'];
        $_SESSION['role']            = $user['role'];
        $_SESSION['profile_pic']  = $user['profile_pic'] ?? '';  // ← matches users.profile_pic column

        /* ================= ROLE REDIRECT ================= */
        if ($user['role'] === "admin") {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] === "organizer") {
            header("Location: Organizer/organizer_index.php");
        } else {
            header("Location: /Tourna/NewsFeed/newsfeed.php");
        }
        exit();

    } else {
        echo "Invalid password ❌";
    }

} else {
    echo "User not found ❌";
}

$stmt->close();
$conn->close();
?>