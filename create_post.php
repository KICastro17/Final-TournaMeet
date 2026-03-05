<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']); exit();
}

$user_id = $_SESSION['user_id'];
$caption  = trim($_POST['caption']  ?? '');

$allowed_images = ['image/jpeg','image/png','image/gif','image/webp'];
$allowed_videos = ['video/mp4','video/webm','video/ogg'];
$max_size       = 50 * 1024 * 1024; // 50MB

if (!is_dir('uploads/posts')) mkdir('uploads/posts', 0755, true);

/* ── Handle multiple files ── */
$files = isset($_FILES['media']) ? $_FILES['media'] : null;
// Support both 'media' and 'media[]'
if (!isset($_FILES['media']['name'][0]) && isset($_FILES['media']['name'])) {
    // single file wrapped in array structure already
}
if (!$files || empty($files['name'][0])) {
    echo json_encode(['error' => 'Please select at least one image or video.']); exit();
}

$saved = []; // [{filename, media_type, url}]

$count = count($files['name']);
for ($i = 0; $i < $count; $i++) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

    $tmp  = $files['tmp_name'][$i];
    $size = $files['size'][$i];
    $type = mime_content_type($tmp);
    $ext  = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));

    if (!in_array($type, array_merge($allowed_images, $allowed_videos))) continue;
    if ($size > $max_size) continue;

    $filename    = uniqid('post_', true) . '.' . $ext;
    $upload_path = 'uploads/posts/' . $filename;

    if (move_uploaded_file($tmp, $upload_path)) {
        $saved[] = [
            'filename'   => $filename,
            'media_type' => in_array($type, $allowed_videos) ? 'video' : 'image',
            'url'        => $upload_path
        ];
    }
}

if (empty($saved)) {
    echo json_encode(['error' => 'No valid files uploaded.']); exit();
}

/* ── Insert post ── */
$stmt = $conn->prepare("INSERT INTO posts (user_id, caption, location, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iss", $user_id, $caption, $location);
$stmt->execute();
$post_id = $conn->insert_id;
$stmt->close();

/* ── Insert each media file ── */
$stmt = $conn->prepare("INSERT INTO post_media (post_id, filename, media_type, sort_order) VALUES (?,?,?,?)");
foreach ($saved as $idx => $m) {
    $stmt->bind_param("issi", $post_id, $m['filename'], $m['media_type'], $idx);
    $stmt->execute();
}
$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'post_id' => $post_id,
    'media'   => $saved,
    'caption'  => htmlspecialchars($caption),
    'date'    => date('M j, Y')
]); 