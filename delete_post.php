<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit();
}

$user_id = (int)$_SESSION['user_id'];
$post_id = (int)($_POST['post_id'] ?? 0);

if (!$post_id) {
    echo json_encode(['error' => 'Invalid post.']); exit();
}

/* ── Verify ownership ── */
$stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post || (int)$post['user_id'] !== $user_id) {
    echo json_encode(['error' => 'Not authorized.']); exit();
}

/* ── Delete media files from disk ── */
$res = $conn->query("SELECT filename FROM post_media WHERE post_id = $post_id");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $path = 'uploads/posts/' . $row['filename'];
        if (file_exists($path)) unlink($path);
    }
}

/* ── Also check old single media columns as fallback ── */
$res2 = $conn->query("SELECT media, image FROM posts WHERE id = $post_id");
if ($res2) {
    $old = $res2->fetch_assoc();
    foreach (['media', 'image'] as $col) {
        if (!empty($old[$col])) {
            $path = 'uploads/posts/' . $old[$col];
            if (file_exists($path)) unlink($path);
        }
    }
}

/* ── Delete post (cascade deletes post_media rows) ── */
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => true]);