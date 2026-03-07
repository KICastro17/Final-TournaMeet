<?php
/**
 * get_users.php
 * GET: role=athlete|organizer  &  type=friends|suggested|requests
 */
require_once __DIR__ . '/db.php';

$role = $_GET['role'] ?? 'athlete';
$type = $_GET['type'] ?? 'suggested';

$roleFilter   = ($role === 'organizer') ? ['organizer', 'admin'] : ['athlete'];
$placeholders = implode(',', array_fill(0, count($roleFilter), '?'));

try {

    // ── ACCEPTED FRIENDS ──
    if ($type === 'friends') {
        if (!$current_user_id) { echo json_encode(['success'=>true,'data'=>[]]); exit; }

        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.role,
                   COALESCE(NULLIF(u.bio,''),'No bio yet') AS bio,
                   u.profile_pic,
                   UPPER(LEFT(u.username,2))               AS initials,
                   'accepted'                              AS friend_status,
                   NULL                                    AS friendship_id
            FROM users u
            INNER JOIN friendships f
                ON (f.user_id=? AND f.friend_id=u.id)
                OR (f.friend_id=? AND f.user_id=u.id)
            WHERE f.status='accepted'
              AND u.id != ?
              AND u.role IN ($placeholders)
            ORDER BY u.username ASC
        ");
        $stmt->execute([$current_user_id, $current_user_id, $current_user_id, ...$roleFilter]);
        echo json_encode(['success'=>true,'data'=>formatUsers($stmt->fetchAll())]);
        exit;
    }

    // ── INCOMING FRIEND REQUESTS (pending, where current user is the receiver) ──
    if ($type === 'requests') {
        if (!$current_user_id) { echo json_encode(['success'=>true,'data'=>[]]); exit; }

        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.role,
                   COALESCE(NULLIF(u.bio,''),'No bio yet') AS bio,
                   u.profile_pic,
                   UPPER(LEFT(u.username,2))               AS initials,
                   'pending'                               AS friend_status,
                   f.id                                    AS friendship_id
            FROM users u
            INNER JOIN friendships f ON f.user_id = u.id
            WHERE f.friend_id = ?
              AND f.status    = 'pending'
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$current_user_id]);
        echo json_encode(['success'=>true,'data'=>formatUsers($stmt->fetchAll())]);
        exit;
    }

    // ── SUGGESTED (everyone else) ──
    if ($current_user_id) {
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.role,
                   COALESCE(NULLIF(u.bio,''),'No bio yet') AS bio,
                   u.profile_pic,
                   UPPER(LEFT(u.username,2))               AS initials,
                   COALESCE(
                       (SELECT f2.status FROM friendships f2
                        WHERE (f2.user_id=? AND f2.friend_id=u.id)
                           OR (f2.friend_id=? AND f2.user_id=u.id)
                        LIMIT 1),
                       'none'
                   ) AS friend_status,
                   NULL AS friendship_id
            FROM users u
            WHERE u.role IN ($placeholders)
              AND u.id  != ?
              AND NOT EXISTS (
                  SELECT 1 FROM friendships f3
                  WHERE f3.status='accepted'
                    AND ((f3.user_id=? AND f3.friend_id=u.id)
                      OR (f3.friend_id=? AND f3.user_id=u.id))
              )
            ORDER BY u.created_at DESC
            LIMIT 30
        ");
        $stmt->execute([
            $current_user_id, $current_user_id,
            ...$roleFilter,
            $current_user_id,
            $current_user_id, $current_user_id
        ]);
    } else {
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.role,
                   COALESCE(NULLIF(u.bio,''),'No bio yet') AS bio,
                   u.profile_pic,
                   UPPER(LEFT(u.username,2)) AS initials,
                   'none' AS friend_status, NULL AS friendship_id
            FROM users u
            WHERE u.role IN ($placeholders)
            ORDER BY u.created_at DESC LIMIT 30
        ");
        $stmt->execute($roleFilter);
    }

    echo json_encode(['success'=>true,'data'=>formatUsers($stmt->fetchAll())]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage(),'data'=>[]]);
}

function formatUsers(array $users): array {
    foreach ($users as &$u) {
        if (!empty($u['profile_pic']) && !str_starts_with($u['profile_pic'], 'http')) {
            $u['profile_pic'] = '../uploads/' . $u['profile_pic'];
        }
    }
    return $users;
}