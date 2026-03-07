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

// Helper: get username by id
function getUsername($pdo, $user_id) {
    $s = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $s->execute([$user_id]);
    return $s->fetchColumn();
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

            // ── NOTIFICATION: notify the receiver of the friend request ──
            $sender_username = getUsername($pdo, $current_user_id);
            $pdo->prepare("
                INSERT INTO user_notifications (user_id, type, message)
                VALUES (?, 'friend_request', ?)
            ")->execute([$friend_id, $sender_username . ' sent you a friend request.']);
            // ─────────────────────────────────────────────────────────────

            echo json_encode(['success'=>true,'message'=>'Friend request sent']);
            break;

        // ── Accept request ──
        case 'accept':
            if (!$friendship_id) {
                echo json_encode(['success'=>false,'error'=>'Invalid request']); exit;
            }
            // Make sure current user is the receiver
            $stmt = $pdo->prepare("
                UPDATE friendships SET status='accepted'
                WHERE id=? AND friend_id=? AND status='pending'
            ");
            $stmt->execute([$friendship_id, $current_user_id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success'=>false,'error'=>'Request not found or already handled']);
            } else {
                // ── NOTIFICATION: notify the sender that request was accepted ──
                $accepter_username = getUsername($pdo, $current_user_id);
                // Get the original sender (user_id in friendships)
                $row = $pdo->prepare("SELECT user_id FROM friendships WHERE id = ?");
                $row->execute([$friendship_id]);
                $original_sender_id = (int)$row->fetchColumn();

                if ($original_sender_id) {
                    $pdo->prepare("
                        INSERT INTO user_notifications (user_id, type, message)
                        VALUES (?, 'friend_request', ?)
                    ")->execute([$original_sender_id, $accepter_username . ' accepted your friend request. You are now connected!']);
                }
                // ──────────────────────────────────────────────────────────────

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