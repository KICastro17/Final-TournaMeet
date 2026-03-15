<?php
require_once __DIR__ . '/db.php';

if (!$current_user_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

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

// Add columns if upgrading from older install
try { $pdo->exec("ALTER TABLE messages ADD COLUMN media_url VARCHAR(500) DEFAULT NULL"); } catch(Exception $e){}
try { $pdo->exec("ALTER TABLE messages ADD COLUMN media_type ENUM('image','video') DEFAULT NULL"); } catch(Exception $e){}
try { $pdo->exec("ALTER TABLE messages ADD COLUMN is_deleted TINYINT(1) DEFAULT 0"); } catch(Exception $e){}
try { $pdo->exec("ALTER TABLE messages ADD COLUMN is_edited TINYINT(1) DEFAULT 0"); } catch(Exception $e){}

$me      = $current_user_id;
$get     = $_GET['action']  ?? '';
$rawBody = file_get_contents('php://input');
$body    = json_decode($rawBody, true) ?: [];
$action  = $get ?: ($body['action'] ?? '');

// GET: friends list with last message + unread count
if ($action === 'friends') {
    $hasFriendships = (bool)$pdo->query("SHOW TABLES LIKE 'friendships'")->rowCount();
    if (!$hasFriendships) {
        echo json_encode(['success'=>true,'data'=>[]]);
        exit;
    }
    $stmt = $pdo->prepare("
        SELECT
            u.id, u.username, u.profile_pic,
            UPPER(LEFT(u.username,2)) AS initials,
            (SELECT m2.body FROM messages m2
             WHERE (m2.sender_id=u.id AND m2.receiver_id=:me1)
                OR (m2.sender_id=:me2 AND m2.receiver_id=u.id)
             ORDER BY m2.created_at DESC LIMIT 1) AS last_message,
            (SELECT m2.created_at FROM messages m2
             WHERE (m2.sender_id=u.id AND m2.receiver_id=:me3)
                OR (m2.sender_id=:me4 AND m2.receiver_id=u.id)
             ORDER BY m2.created_at DESC LIMIT 1) AS last_time,
            (SELECT COUNT(*) FROM messages m2
             WHERE m2.sender_id=u.id AND m2.receiver_id=:me5 AND m2.is_read=0) AS unread
        FROM users u
        INNER JOIN friendships f
            ON (f.user_id=:me6 AND f.friend_id=u.id)
            OR (f.friend_id=:me7 AND f.user_id=u.id)
        WHERE f.status='accepted'
        ORDER BY last_time DESC, u.username ASC
    ");
    $stmt->execute([
        ':me1'=>$me,':me2'=>$me,':me3'=>$me,':me4'=>$me,
        ':me5'=>$me,':me6'=>$me,':me7'=>$me
    ]);
    $friends = $stmt->fetchAll();
    foreach ($friends as &$f) {
        if ($f['profile_pic'] && !str_starts_with($f['profile_pic'],'http')) {
            $f['profile_pic'] = '/Tourna/uploads' . $f['profile_pic'];
        }
        $f['unread'] = (int)$f['unread'];
    }
    echo json_encode(['success'=>true,'data'=>$friends]);
    exit;
}

// GET: full message history
if ($action === 'messages') {
    $with = (int)($_GET['with'] ?? 0);
    if (!$with) { echo json_encode(['success'=>true,'data'=>[]]); exit; }
    $pdo->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0")
        ->execute([$with,$me]);
    $stmt = $pdo->prepare("
        SELECT id, sender_id, body, media_url, media_type, is_deleted, is_edited, created_at
        FROM messages
        WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
        ORDER BY created_at ASC LIMIT 100
    ");
    $stmt->execute([$me,$with,$with,$me]);
    echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    exit;
}

// GET: poll for new messages only (after a given message ID)
if ($action === 'poll') {
    $with  = (int)($_GET['with']  ?? 0);
    $after = (int)($_GET['after'] ?? 0);
    if (!$with) { echo json_encode(['success'=>true,'data'=>[]]); exit; }
    $pdo->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0")
        ->execute([$with,$me]);
    $stmt = $pdo->prepare("
        SELECT id, sender_id, body, media_url, media_type, is_deleted, is_edited, created_at
        FROM messages
        WHERE ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)) AND id>?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$me,$with,$with,$me,$after]);
    echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    exit;
}

// GET: unread count across all convos (for nav badge)
if ($action === 'unread_count') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0");
    $stmt->execute([$me]);
    echo json_encode(['success'=>true,'count'=>(int)$stmt->fetchColumn()]);
    exit;
}

// POST: send message
if ($action === 'send') {
    $to  = (int)($body['to']   ?? 0);
    $msg = trim($body['body']  ?? '');
    if (!$to || !$msg) { echo json_encode(['success'=>false,'error'=>'Missing data']); exit; }
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id,receiver_id,body) VALUES (?,?,?)");
    $stmt->execute([$me,$to,$msg]);
    $newId = $pdo->lastInsertId();
    $row = $pdo->prepare("SELECT id,sender_id,body,media_url,media_type,is_deleted,is_edited,created_at FROM messages WHERE id=?");
    $row->execute([$newId]);
    echo json_encode(['success'=>true,'data'=>$row->fetch()]);
    exit;
}

// POST: unsend message (mark as deleted for everyone)
if ($action === 'unsend') {
    $msgId = (int)($body['message_id'] ?? 0);
    if (!$msgId) {
        echo json_encode(['success'=>false,'message'=>'Missing message ID']);
        exit;
    }
    // Verify the message belongs to this user
    $check = $pdo->prepare("SELECT id FROM messages WHERE id=? AND sender_id=?");
    $check->execute([$msgId, $me]);
    if (!$check->fetch()) {
        echo json_encode(['success'=>false,'message'=>'You can only unsend your own messages']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE messages SET is_deleted=1, body=NULL, media_url=NULL WHERE id=?");
    $stmt->execute([$msgId]);
    echo json_encode(['success'=>true]);
    exit;
}

// POST: edit message body
if ($action === 'edit') {
    $msgId   = (int)($body['message_id'] ?? 0);
    $newBody = trim($body['body'] ?? '');
    if (!$msgId || !$newBody) {
        echo json_encode(['success'=>false,'message'=>'Missing message ID or body']);
        exit;
    }
    // Verify ownership and that message is not deleted
    $check = $pdo->prepare("SELECT id FROM messages WHERE id=? AND sender_id=? AND is_deleted=0");
    $check->execute([$msgId, $me]);
    if (!$check->fetch()) {
        echo json_encode(['success'=>false,'message'=>'You can only edit your own messages']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE messages SET body=?, is_edited=1 WHERE id=?");
    $stmt->execute([$newBody, $msgId]);
    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Unknown action']);