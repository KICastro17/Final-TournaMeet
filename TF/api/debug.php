<?php
/**
 * debug.php
 * Open this in your browser to test if the DB connection and users table work.
 * DELETE this file after debugging!
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'user_system');

echo "<h2>Tournameet – DB Debug</h2>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color:green'>✅ DB connected to <strong>" . DB_NAME . "</strong></p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ DB connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Show session info
session_start();
echo "<h3>Session</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Show all users
echo "<h3>All Users in `users` table</h3>";
$rows = $pdo->query("SELECT id, username, email, role, status FROM users LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
if ($rows) {
    echo "<table border='1' cellpadding='6'><tr><th>id</th><th>username</th><th>email</th><th>role</th><th>status</th></tr>";
    foreach ($rows as $r) {
        echo "<tr><td>{$r['id']}</td><td>{$r['username']}</td><td>{$r['email']}</td><td>{$r['role']}</td><td>{$r['status']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>⚠️ No users found in the table.</p>";
}

// Test get_users API directly
echo "<h3>API Test — Athletes (suggested)</h3>";
$athletes = $pdo->query("SELECT id, username, role, bio, profile_pic FROM users WHERE status = 'approved' AND role = 'athlete' LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($athletes, true) . "</pre>";

echo "<h3>API Test — Organizers (suggested)</h3>";
$orgs = $pdo->query("SELECT id, username, role, bio, profile_pic FROM users WHERE status = 'approved' AND role IN ('organizer','admin') LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($orgs, true) . "</pre>";