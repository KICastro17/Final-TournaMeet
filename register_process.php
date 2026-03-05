<?php
session_start();
include "config.php";

/* ================= CHECK CONNECTION ================= */
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

/* ================= CHECK FORM ================= */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit();
}

/* ================= GET INPUT ================= */
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';

if (empty($username) || empty($email) || empty($password) || empty($role)) {
    die("All fields are required.");
}

/* ================= HASH PASSWORD ================= */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* ================================================= */
/* ========== AUTO APPROVAL LOGIC (NEW) ============= */
/* ================================================= */

/*
Athlete  → approved immediately
Organizer → needs admin approval
*/
$status = ($role === "athlete") ? "approved" : "pending";

/* ================= INSERT USER ================= */
$sql = "INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare Failed: " . $conn->error);
}

$stmt->bind_param("sssss", $username, $email, $hashedPassword, $role, $status);

if ($stmt->execute()) {

    /* ===== OPTIONAL: CREATE NOTIFICATION ===== */
    $message = "New user registered: " . $username . " (" . $role . ")";
    $notif = $conn->prepare("INSERT INTO notifications (message) VALUES (?)");

    if ($notif) {
        $notif->bind_param("s", $message);
        $notif->execute();
        $notif->close();
    }

    echo "
    <div style='text-align:center;margin-top:100px;font-family:Arial;'>

        <h1 style='color:green;'>Registration Successful ✅</h1>
        <p>Your account has been created.</p>
    ";

    if ($status === "pending") {
        echo "<p style='color:orange;'>Your account is waiting for admin approval.</p>";
    }

    echo "
        <br>
        <a href='login.php' style='padding:10px 20px;background:#ff6f00;color:white;text-decoration:none;border-radius:6px;'>
            Go to Login
        </a>
    </div>
    ";

} else {
    echo "
    <div style='text-align:center;margin-top:100px;font-family:Arial;'>
        <h2 style='color:red;'>Registration Failed ❌</h2>
        <p>Error: " . $stmt->error . "</p>
        <a href='register.php'>Try Again</a>
    </div>
    ";
}

$stmt->close();
$conn->close();
?>