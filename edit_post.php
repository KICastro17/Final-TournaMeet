<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit();
}

$user_id = (int)$_SESSION['user_id'];
$post_id = (int)($_POST['post_id'] ?? 0);
$caption = trim($_POST['caption'] ?? '');
$remove_ids = json_decode($_POST['remove_ids'] ?? '[]', true) ?: []; // media IDs to delete

$allowed_images = ['image/jpeg','image/png','image/gif','image/webp'];
$allowed_videos = ['video/mp4','video/webm','video/ogg'];
$max_size       = 50 * 1024 * 1024;

if (!$post_id) { echo json_encode(['error' => 'Invalid post.']); exit(); }

/* ── Verify ownership ── */
$stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post || (int)$post['user_id'] !== $user_id) {
    echo json_encode(['error' => 'Not authorized.']); exit();
}

/* ── Remove selected media ── */
foreach ($remove_ids as $mid) {
    $mid = (int)$mid;
    $stmt = $conn->prepare("SELECT filename FROM post_media WHERE id = ? AND post_id = ?");
    $stmt->bind_param("ii", $mid, $post_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $path = 'uploads/posts/' . $row['filename'];
        if (file_exists($path)) unlink($path);
        $conn->query("DELETE FROM post_media WHERE id = $mid");
    }
}

/* ── Add new media files ── */
$new_media = [];
if (!empty($_FILES['new_media']['name'][0])) {
    if (!is_dir('uploads/posts')) mkdir('uploads/posts', 0755, true);

    // Get current max sort_order
    $res   = $conn->query("SELECT COALESCE(MAX(sort_order),0)+1 AS next FROM post_media WHERE post_id = $post_id");
    $order = (int)$res->fetch_assoc()['next'];

    $files = $_FILES['new_media'];
    $count = count($files['name']);
    $stmt  = $conn->prepare("INSERT INTO post_media (post_id, filename, media_type, sort_order) VALUES (?,?,?,?)");

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
            $mtype = in_array($type, $allowed_videos) ? 'video' : 'image';
            $stmt->bind_param("issi", $post_id, $filename, $mtype, $order);
            $stmt->execute();
            $new_media[] = [
                'id'         => $conn->insert_id,
                'filename'   => $filename,
                'media_type' => $mtype,
                'url'        => $upload_path
            ];
            $order++;
        }
    }
    $stmt->close();
}

/* ── Update caption ── */
$stmt = $conn->prepare("UPDATE posts SET caption = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $caption, $post_id, $user_id);
$stmt->execute();
$stmt->close();

/* ── Return updated media list ── */
$res       = $conn->query("SELECT id, filename, media_type FROM post_media WHERE post_id = $post_id ORDER BY sort_order");
$all_media = $res->fetch_all(MYSQLI_ASSOC);
$conn->close();

echo json_encode([
    'success'   => true,
    'caption'   => htmlspecialchars($caption),
    'new_media' => $new_media,
    'all_media' => $all_media
]);