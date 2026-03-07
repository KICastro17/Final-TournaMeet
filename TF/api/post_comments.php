<?php
require_once __DIR__ . '/db.php';
if (!$current_user_id) { echo json_encode(['success'=>false,'error'=>'Not logged in']); exit; }

// Auto-create tables — no FK constraints to avoid engine conflicts
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS post_comments (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        post_id    INT NOT NULL,
        user_id    INT NOT NULL,
        body       TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_post (post_id),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch(Exception $e) {}

// Detect actual comment text column name
$commentBodyCol = 'body';
try {
    $cols = $pdo->query("SHOW COLUMNS FROM post_comments")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('content', $cols)) $commentBodyCol = 'content';
    elseif (in_array('message', $cols)) $commentBodyCol = 'message';
    elseif (in_array('comment', $cols)) $commentBodyCol = 'comment';
    elseif (!in_array('body', $cols)) {
        $pdo->exec("ALTER TABLE post_comments ADD COLUMN body TEXT NOT NULL DEFAULT ''");
    }
} catch(Exception $e) {}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS post_reactions (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        post_id    INT NOT NULL,
        user_id    INT NOT NULL,
        emoji      VARCHAR(10) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_reaction (post_id, user_id, emoji),
        INDEX idx_post (post_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch(Exception $e) {}

$method   = $_SERVER['REQUEST_METHOD'];
$action   = $_GET['action'] ?? '';
$body_raw = file_get_contents('php://input');
$body     = json_decode($body_raw, true) ?? [];
$post_id  = (int)($_GET['post_id'] ?? $body['post_id'] ?? 0);

// ── GET comments ──
if ($method === 'GET' && $action === 'comments') {
    if (!$post_id) { echo json_encode(['success'=>true,'data'=>[]]); exit; }
    $stmt = $pdo->prepare("
        SELECT c.id, c.$commentBodyCol AS body, c.created_at,
               u.username, u.profile_pic
        FROM post_comments c
        JOIN users u ON u.id = c.user_id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
        LIMIT 100
    ");
    $stmt->execute([$post_id]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['profile_pic'] = $r['profile_pic'] ? '/Tourna/uploads' . $r['profile_pic'] : null;
    }
    echo json_encode(['success'=>true,'data'=>$rows]);
    exit;
}

// ── GET reactions ──
if ($method === 'GET' && $action === 'reactions') {
    if (!$post_id) { echo json_encode(['success'=>true,'data'=>[]]); exit; }
    $stmt = $pdo->prepare("
        SELECT emoji, COUNT(*) AS cnt,
               MAX(CASE WHEN user_id=? THEN 1 ELSE 0 END) AS mine
        FROM post_reactions WHERE post_id=?
        GROUP BY emoji
    ");
    $stmt->execute([$current_user_id, $post_id]);
    echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    exit;
}

// ── POST comment ──
if ($method === 'POST' && $action === 'comment') {
    $text = trim($body['body'] ?? '');
    if (!$text) { echo json_encode(['success'=>false,'error'=>'Empty comment']); exit; }
    if (!$post_id) { echo json_encode(['success'=>false,'error'=>'Missing post_id']); exit; }

    try {
        $pdo->prepare("INSERT INTO post_comments (post_id, user_id, $commentBodyCol) VALUES (?,?,?)")
            ->execute([$post_id, $current_user_id, $text]);
        $new_id = $pdo->lastInsertId();

        $me = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id=?");
        $me->execute([$current_user_id]);
        $me = $me->fetch();

        // ── NOTIFICATION: notify post owner ──
        $owner = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
        $owner->execute([$post_id]);
        $post_owner_id = (int)$owner->fetchColumn();

        if ($post_owner_id && $post_owner_id !== $current_user_id) {
            $pdo->prepare("
                INSERT INTO user_notifications (user_id, type, message)
                VALUES (?, 'comment', ?)
            ")->execute([
                $post_owner_id,
                $me['username'] . ' commented: "' . mb_strimwidth($text, 0, 60, '…') . '"'
            ]);
        }
        // ─────────────────────────────────────

        echo json_encode(['success'=>true, 'data'=>[
            'id'          => (int)$new_id,
            'body'        => $text,
            'created_at'  => date('Y-m-d H:i:s'),
            'username'    => $me['username'],
            'profile_pic' => $me['profile_pic'] ? '/Tourna/uploads' . $me['profile_pic'] : null,
        ]]);
    } catch(Exception $e) {
        echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    }
    exit;
}

// ── POST reaction (toggle) ──
if ($method === 'POST' && $action === 'react') {
    $emoji = trim($body['emoji'] ?? '');
    if (!$emoji || !$post_id) { echo json_encode(['success'=>false,'error'=>'Missing data']); exit; }

    try {
        $check = $pdo->prepare("SELECT id FROM post_reactions WHERE post_id=? AND user_id=? AND emoji=?");
        $check->execute([$post_id, $current_user_id, $emoji]);
        if ($check->fetch()) {
            $pdo->prepare("DELETE FROM post_reactions WHERE post_id=? AND user_id=? AND emoji=?")
                ->execute([$post_id, $current_user_id, $emoji]);
            $active = false;
        } else {
            $pdo->prepare("INSERT IGNORE INTO post_reactions (post_id, user_id, emoji) VALUES (?,?,?)")
                ->execute([$post_id, $current_user_id, $emoji]);
            $active = true;
        }
        $cnt = $pdo->prepare("SELECT COUNT(*) FROM post_reactions WHERE post_id=? AND emoji=?");
        $cnt->execute([$post_id, $emoji]);
        echo json_encode(['success'=>true,'active'=>$active,'count'=>(int)$cnt->fetchColumn()]);
    } catch(Exception $e) {
        echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    }
    exit;
}

echo json_encode(['success'=>false,'error'=>'Unknown action: '.$action]);