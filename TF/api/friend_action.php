<?php
/**
 * friend_action.php
 * POST JSON body:
 *   action      = 'send' | 'accept' | 'decline' | 'remove'
 *   friend_id   = int   (for send/remove)
 *   friendship_id = int (for accept/decline)
 */
require_once __DIR__ . '/db.php';

if (!$current_user_id) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}

$body          = json_decode(file_get_contents('php://input'), true);
$action        = $body['action']        ?? '';
$friend_id     = (int)($body['friend_id']     ?? 0);
$friendship_id = (int)($body['friendship_id'] ?? 0);

// Helper: insert a notification
function insertNotif($pdo, $user_id, $type, $message) {
    try {
        $s = $pdo->prepare("
            INSERT INTO user_notifications (user_id, type, message, is_read, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        $s->execute([$user_id, $type, $message]);
    } catch (Exception $e) {
        error_log("insertNotif failed: " . $e->getMessage());
    }
}

// Helper: get username
function getUsername($pdo, $user_id) {
    $s = $pdo->prepare("SELECT username FROM users WHERE id = ? LIMIT 1");
    $s->execute([$user_id]);
    return $s->fetchColumn() ?: 'Someone';
}

try {
    switch ($action) {

        // ── Send friend request ──
        case 'send':
            if (!$friend_id || $friend_id === $current_user_id) {
                echo json_encode(['success'=>false,'error'=>'Invalid user']); exit;
            }
            // Check not already requested/accepted
            $check = $pdo->prepare("
                SELECT id, status FROM friendships
                WHERE (user_id=? AND friend_id=?) OR (user_id=? AND friend_id=?)
                LIMIT 1
            ");
            $check->execute([$current_user_id,$friend_id,$friend_id,$current_user_id]);
            $existing = $check->fetch();

            if ($existing) {
                echo json_encode(['success'=>false,'error'=>'Request already exists','status'=>$existing['status']]);
                exit;
            }

            $stmt = $pdo->prepare("
                INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'pending')
            ");
            $stmt->execute([$current_user_id, $friend_id]);

            // ── Notify recipient ──
            $sender_name = getUsername($pdo, $current_user_id);
            insertNotif($pdo, $friend_id, 'friend_request', "$sender_name sent you a friend request.");

            echo json_encode(['success'=>true,'message'=>'Friend request sent']);
            break;

        // ── Accept request ──
        case 'accept':
            if (!$friendship_id) {
                echo json_encode(['success'=>false,'error'=>'Invalid request']); exit;
            }

            // Get the sender's id before updating
            $fRow = $pdo->prepare("SELECT user_id FROM friendships WHERE id=? AND friend_id=? AND status='pending'");
            $fRow->execute([$friendship_id, $current_user_id]);
            $fData = $fRow->fetch();

            $stmt = $pdo->prepare("
                UPDATE friendships SET status='accepted'
                WHERE id=? AND friend_id=? AND status='pending'
            ");
            $stmt->execute([$friendship_id, $current_user_id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success'=>false,'error'=>'Request not found or already handled']);
            } else {
                // ── Notify the original sender that request was accepted ──
                if ($fData) {
                    $accepter_name = getUsername($pdo, $current_user_id);
                    insertNotif($pdo, $fData['user_id'], 'friend_request', "$accepter_name accepted your friend request.");
                }
                echo json_encode(['success'=>true,'message'=>'Friend request accepted']);
            }
            break;

        // ── Decline request ──
        case 'decline':
            if (!$friendship_id) {
                echo json_encode(['success'=>false,'error'=>'Invalid request']); exit;
            }
            $stmt = $pdo->prepare("
                DELETE FROM friendships
                WHERE id=? AND friend_id=? AND status='pending'
            ");
            $stmt->execute([$friendship_id, $current_user_id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success'=>false,'error'=>'Request not found']);
            } else {
                echo json_encode(['success'=>true,'message'=>'Friend request declined']);
            }
            break;

        // ── Remove friend ──
        case 'remove':
            if (!$friend_id) {
                echo json_encode(['success'=>false,'error'=>'Invalid user']); exit;
            }
            $stmt = $pdo->prepare("
                DELETE FROM friendships
                WHERE (user_id=? AND friend_id=?) OR (user_id=? AND friend_id=?)
            ");
            $stmt->execute([$current_user_id,$friend_id,$friend_id,$current_user_id]);
            echo json_encode(['success'=>true,'message'=>'Friend removed']);
            break;

        default:
            echo json_encode(['success'=>false,'error'=>'Unknown action']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}