<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* 
   ✅ Auto redirect to dashboard
   ✅ No welcome page
*/

if ($_SESSION['role'] === "admin") {
    header("Location: admin_dashboard.php");
    exit();
} else {
    header("Location: user_dashboard.php"); 
    // If you have user dashboard
    exit();
}
?>