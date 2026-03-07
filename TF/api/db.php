<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'user_system');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage(), 'data' => []]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    session_set_cookie_params(['path' => '/']);
    session_start();
}

// Auto-create friendships table if it doesn't exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS friendships (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT NOT NULL,
        friend_id   INT NOT NULL,
        status      ENUM('pending','accepted','declined') DEFAULT 'pending',
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_friendship (user_id, friend_id),
        FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

// Adjust session key to match your login system
$current_user_id = $_SESSION['user_id']
    ?? $_SESSION['id']
    ?? $_SESSION['userId']
    ?? $_SESSION['uid']
    ?? null;

header('Content-Type: application/json');