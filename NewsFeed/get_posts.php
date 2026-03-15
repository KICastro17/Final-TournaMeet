<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php'; // gives $conn (mysqli)

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']); exit;
}

$uid = (int) $_SESSION['user_id'];

// Check optional tables
$hasPM = $conn->query("SHOW TABLES LIKE 'post_media'")->num_rows > 0;
$hasPL = $conn->query("SHOW TABLES LIKE 'post_likes'")->num_rows > 0;
$hasPC = $conn->query("SHOW TABLES LIKE 'post_comments'")->num_rows > 0;

$rows = $conn->query("
    SELECT p.id, p.user_id, p.caption, p.media, p.image, p.media_type, p.created_at,
           u.username, u.profile_pic
    FROM posts p
    JOIN users u ON u.id = p.user_id
    ORDER BY p.created_at DESC
    LIMIT 40
")->fetch_all(MYSQLI_ASSOC);

$posts = [];
foreach ($rows as $row) {
    $pid = (int) $row['id'];

    // Media
    $media = [];
    if ($hasPM) {
        $ms = $conn->query("SELECT filename, media_type FROM post_media WHERE post_id=$pid ORDER BY sort_order");
        if ($ms) while ($m = $ms->fetch_assoc())
            $media[] = ['url' => '../uploads/posts/' . $m['filename'], 'type' => $m['media_type']];
    }
    if (empty($media)) {
        $fname = !empty($row['media']) ? $row['media'] : (!empty($row['image']) ? $row['image'] : null);
        if ($fname) $media[] = ['url' => '../uploads/posts/' . $fname, 'type' => $row['media_type'] ?? 'image'];
    }

    // Likes
    $likeCount = 0; $iLiked = false;
    if ($hasPL) {
        $likeCount = (int)$conn->query("SELECT COUNT(*) FROM post_likes WHERE post_id=$pid")->fetch_row()[0];
        $iLiked    = (bool)$conn->query("SELECT COUNT(*) FROM post_likes WHERE post_id=$pid AND user_id=$uid")->fetch_row()[0];
    }

    // Comment count
    $commentCount = 0;
    if ($hasPC) {
        $commentCount = (int)$conn->query("SELECT COUNT(*) FROM post_comments WHERE post_id=$pid")->fetch_row()[0];
    }

    // Time ago
    $diff = time() - strtotime($row['created_at']);
    if ($diff < 60)         $ago = 'Just now';
    elseif ($diff < 3600)   $ago = floor($diff/60).'m ago';
    elseif ($diff < 86400)  $ago = floor($diff/3600).'h ago';
    elseif ($diff < 604800) $ago = floor($diff/86400).'d ago';
    else                    $ago = date('M j, Y', strtotime($row['created_at']));

    $posts[] = [
        'id'            => $pid,
        'user_id'       => (int)$row['user_id'],
        'is_own'        => ((int)$row['user_id'] === $uid),
        'caption'       => $row['caption'] ?? '',
        'username'      => $row['username'],
        'pic'           => $row['profile_pic'] ? '../uploads' . $row['profile_pic'] : null,
        'ago'           => $ago,
        'media'         => $media,
        'like_count'    => $likeCount,
        'i_liked'       => $iLiked,
        'comment_count' => $commentCount,
    ];
}

echo json_encode(['success'=>true,'posts'=>$posts]);
$conn->close();