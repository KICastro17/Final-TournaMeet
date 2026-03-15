<?php
session_start();
require_once __DIR__ . '/../config.php'; // gives $conn (mysqli)

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit;
}

$user_id = (int) $_SESSION['user_id'];
$caption = trim($_POST['caption'] ?? '');

if (!$caption) {
    echo json_encode(['success'=>false,'error'=>'Caption is required']); exit;
}

// Insert post (text only — media optional)
$stmt = $conn->prepare("INSERT INTO posts (user_id, caption, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("is", $user_id, $caption);
$stmt->execute();
$post_id = $conn->insert_id;
$stmt->close();

// Handle optional media upload
$saved = [];
$allowed_images = ['image/jpeg','image/png','image/gif','image/webp'];
$allowed_videos = ['video/mp4','video/webm','video/ogg'];
$files = $_FILES['media'] ?? null;

if ($files && !empty($files['name'][0])) {
    if (!is_array($files['name'])) {
        foreach ($files as $k => $v) $files[$k] = [$v];
    }
    if (!is_dir('../uploads/posts')) mkdir('../uploads/posts', 0755, true);
    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
        $tmp  = $files['tmp_name'][$i];
        $type = mime_content_type($tmp);
        $ext  = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($type, array_merge($allowed_images, $allowed_videos))) continue;
        if ($files['size'][$i] > 50*1024*1024) continue;
        $filename = uniqid('post_', true) . '.' . $ext;
        if (move_uploaded_file($tmp, '../uploads/posts/' . $filename)) {
            $mtype = in_array($type, $allowed_videos) ? 'video' : 'image';
            // Insert into post_media if table exists
            $check = $conn->query("SHOW TABLES LIKE 'post_media'");
            if ($check && $check->num_rows > 0) {
                $idx  = count($saved);
                $stmt = $conn->prepare("INSERT INTO post_media (post_id, filename, media_type, sort_order) VALUES (?,?,?,?)");
                $stmt->bind_param("issi", $post_id, $filename, $mtype, $idx);
                $stmt->execute(); $stmt->close();
            } else {
                $conn->query("UPDATE posts SET media='$filename', media_type='$mtype' WHERE id=$post_id");
            }
            $saved[] = ['url' => '../uploads/posts/' . $filename, 'type' => $mtype];
        }
    }
}

$row = $conn->query("SELECT username, profile_pic FROM users WHERE id=$user_id")->fetch_assoc();
$conn->close();

echo json_encode([
    'success'  => true,
    'post_id'  => $post_id,
    'caption'  => htmlspecialchars($caption),
    'username' => $row['username'],
    'pic'      => $row['profile_pic'] ? '../uploads' . $row['profile_pic'] : null,
    'media'    => $saved,
]);