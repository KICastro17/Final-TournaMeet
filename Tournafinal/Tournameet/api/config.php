<?php
$host   = 'localhost';
$dbname = 'user_system';
$user   = 'root';
$pass   = '';

// PDO (keep if other pages use it)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// MySQLi (used by api/tournaments.php and other API files)
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("MySQLi connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>