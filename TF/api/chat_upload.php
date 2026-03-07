<?php
require_once __DIR__ . '/db.php';

if (!$current_user_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Auto-create messages table with media support
$pdo->exec("
    CREATE TABLE IF NOT EXISTS messages (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        sender_id   INT NOT NULL,
        receiver_id INT NOT NULL,
        body        TEXT,
        media_url   VARCHAR(500) DEFAULT NULL,
        media_type  ENUM('image','video') DEFAULT NULL,
        is_read     TINYINT(1) DEFAULT 0,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_convo (sender_id, receiver_id),
        INDEX idx_created (created_at)
    )
");

// Add media columns if they don't exist yet (for existing installs)
try { $pdo->exec("ALTER TABLE messages ADD COLUMN media_url VARCHAR(500) DEFAULT NULL"); } catch(Exception $e){}
try { $pdo->exec("ALTER TABLE messages ADD COLUMN media_type ENUM('image','video') DEFAULT NULL"); } catch(Exception $e){}

$me = $current_user_id;
$to = (int)($_POST['to'] ?? 0);
if (!$to) { echo json_encode(['success' => false, 'error' => 'Missing recipient']); exit; }

$uploadDir = __DIR__ . '/../../uploads/chat/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$file = $_FILES['media'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload failed']);
    exit;
}

$allowed = ['image/jpeg','image/png','image/gif','image/webp','video/mp4','video/webm','video/ogg'];
$mime    = mime_content_type($file['tmp_name']);
if (!in_array($mime, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'File type not allowed']);
    exit;
}
if ($file['size'] > 50 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 50MB)']);
    exit;
}

$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('chat_', true) . '.' . strtolower($ext);
$dest     = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'error' => 'Could not save file']);
    exit;
}

$mediaUrl  = '/Tourna/uploads/chat/' . $filename;
$mediaType = str_starts_with($mime, 'video/') ? 'video' : 'image';
$body      = trim($_POST['body'] ?? '');

$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, body, media_url, media_type) VALUES (?,?,?,?,?)");
$stmt->execute([$me, $to, $body, $mediaUrl, $mediaType]);
$newId = $pdo->lastInsertId();

$row = $pdo->prepare("SELECT id, sender_id, body, media_url, media_type, created_at FROM messages WHERE id=?");
$row->execute([$newId]);
echo json_encode(['success' => true, 'data' => $row->fetch()]);