<?php
/**
 * get_user_posts.php
 * Matches the schema used by profile.php:
 * - posts table: id, user_id, caption, location, created_at
 * - post_media table: id, post_id, filename, media_type, sort_order
 * - Falls back to posts.media / posts.image columns if no post_media rows
 */
require_once __DIR__ . '/db.php';

$user_id = (int)($_GET['user_id'] ?? 0);
if (!$user_id) { echo json_encode(['success'=>true,'data',[]]); exit; }

try {
    // Check which columns actually exist in posts table
    $cols = [];
    $res  = $pdo->query("SHOW COLUMNS FROM posts");
    if ($res) foreach ($res->fetchAll() as $r) $cols[] = $r['Field'];

    $hasCaption  = in_array('caption',   $cols);
    $hasContent  = in_array('content',   $cols);
    $hasImageUrl = in_array('image_url', $cols);
    $hasMedia    = in_array('media',     $cols);
    $hasImage    = in_array('image',     $cols);
    $hasMediaType= in_array('media_type',$cols);

    // Check if post_media table exists
    $pmExists = (bool)$pdo->query("SHOW TABLES LIKE 'post_media'")->rowCount();

    // Build caption/content select
    $captionCol = $hasCaption ? 'p.caption' : ($hasContent ? 'p.content' : "'' ");

    $viewer = $current_user_id ?? 0;

    // Check if post_likes table exists
    $plExists = (bool)$pdo->query("SHOW TABLES LIKE 'post_likes'")->rowCount();

    if ($plExists) {
        $stmt = $pdo->prepare("
            SELECT p.id, $captionCol AS caption, p.created_at,
                   COUNT(DISTINCT pl.id) AS like_count,
                   MAX(CASE WHEN pl.user_id = ? THEN 1 ELSE 0 END) AS liked_by_me
            FROM posts p
            LEFT JOIN post_likes pl ON pl.post_id = p.id
            WHERE p.user_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$viewer, $user_id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT p.id, $captionCol AS caption, p.created_at,
                   0 AS like_count, 0 AS liked_by_me
            FROM posts p
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$user_id]);
    }
    $posts_raw = $stmt->fetchAll();

    $posts = [];
    foreach ($posts_raw as $p) {
        $pid = $p['id'];
        $p['media_files'] = [];

        // Try post_media table first
        if ($pmExists) {
            $ms = $pdo->prepare("SELECT id, filename, media_type FROM post_media WHERE post_id = ? ORDER BY sort_order");
            $ms->execute([$pid]);
            $p['media_files'] = $ms->fetchAll();
        }

        // Fallback to old columns
        if (empty($p['media_files'])) {
            $fallbackCol = $hasMedia ? 'media' : ($hasImage ? 'image' : null);
            $typeCol     = $hasMediaType ? 'media_type' : null;
            if ($fallbackCol) {
                $fq  = "SELECT $fallbackCol AS filename" . ($typeCol ? ", $typeCol AS media_type" : ", 'image' AS media_type") . " FROM posts WHERE id = ?";
                $fs  = $pdo->prepare($fq);
                $fs->execute([$pid]);
                $fold = $fs->fetch();
                if ($fold && !empty($fold['filename'])) {
                    $p['media_files'] = [[
                        'id'         => 0,
                        'filename'   => $fold['filename'],
                        'media_type' => $fold['media_type'] ?? 'image',
                    ]];
                }
            } elseif ($hasImageUrl) {
                $fs = $pdo->prepare("SELECT image_url AS filename, 'image' AS media_type FROM posts WHERE id = ?");
                $fs->execute([$pid]);
                $fold = $fs->fetch();
                if ($fold && !empty($fold['filename'])) {
                    $p['media_files'] = [[
                        'id'         => 0,
                        'filename'   => $fold['filename'],
                        'media_type' => 'image',
                    ]];
                }
            }
        }

        // Build full URLs for each media file
        foreach ($p['media_files'] as &$m) {
            $fn = $m['filename'];
            if (str_starts_with($fn, '/') || str_starts_with($fn, 'http')) {
                $m['url'] = $fn; // already a full path
            } else {
                $m['url'] = '/Tourna/uploads/posts/' . $fn;
            }
        }
        unset($m);

        $p['like_count']  = (int)($p['like_count'] ?? 0);
        $p['liked_by_me'] = (bool)($p['liked_by_me'] ?? false);
        $posts[] = $p;
    }

    echo json_encode(['success'=>true,'data'=>$posts]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage(),'data'=>[]]);
}