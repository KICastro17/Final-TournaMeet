<?php
/**
 * get_profile_stats.php
 * GET: user_id = int
 * Returns: friends count, teams count, member-since year
 */
require_once __DIR__ . '/db.php';

$user_id = (int)($_GET['user_id'] ?? 0);
if (!$user_id) {
    echo json_encode(['friends'=>0,'teams'=>0,'joined'=>'—']);
    exit;
}

try {
    // Friends count
    $hasFriendships = (bool) $pdo->query("SHOW TABLES LIKE 'friendships'")->rowCount();
    $friendCount = 0;
    if ($hasFriendships) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM friendships
            WHERE status='accepted'
              AND (user_id=? OR friend_id=?)
        ");
        $stmt->execute([$user_id, $user_id]);
        $friendCount = (int)$stmt->fetchColumn();
    }

    // Teams count
    $hasTeams = (bool) $pdo->query("SHOW TABLES LIKE 'team_members'")->rowCount();
    $teamCount = 0;
    if ($hasTeams) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM team_members WHERE user_id=?");
        $stmt->execute([$user_id]);
        $teamCount = (int)$stmt->fetchColumn();
    }

    // Join year
    $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    $joinYear = $row ? date('Y', strtotime($row['created_at'])) : '—';

    echo json_encode([
        'friends' => $friendCount,
        'teams'   => $teamCount,
        'joined'  => $joinYear,
    ]);

} catch (PDOException $e) {
    echo json_encode(['friends'=>0,'teams'=>0,'joined'=>'—']);
}