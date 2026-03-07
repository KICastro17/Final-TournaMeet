<?php
require_once __DIR__ . '/db.php';
$type = $_GET['type'] ?? 'open';

try {
    $checkTeams = $pdo->query("SHOW TABLES LIKE 'teams'");
    if (!$checkTeams || $checkTeams->rowCount() === 0) {
        echo json_encode(['success'=>true,'data'=>[]]);
        exit;
    }

    if ($type==='my' && $current_user_id) {
        $stmt = $pdo->prepare("
            SELECT t.id,t.name,t.sport,t.color,u.username AS coach_name,COUNT(tm2.user_id) AS total_members
            FROM teams t
            INNER JOIN team_members tm  ON tm.team_id=t.id AND tm.user_id=?
            INNER JOIN users u          ON u.id=t.coach_id
            LEFT  JOIN team_members tm2 ON tm2.team_id=t.id
            GROUP BY t.id,t.name,t.sport,t.color,u.username
            ORDER BY t.name ASC
        ");
        $stmt->execute([$current_user_id]);
    } else {
        $excludeJoined = $current_user_id ? "AND t.id NOT IN (SELECT team_id FROM team_members WHERE user_id=?)" : "";
        $stmt = $pdo->prepare("
            SELECT t.id,t.name,t.sport,t.color,u.username AS coach_name,COUNT(tm.user_id) AS total_members
            FROM teams t
            INNER JOIN users u         ON u.id=t.coach_id
            LEFT  JOIN team_members tm ON tm.team_id=t.id
            WHERE 1=1 $excludeJoined
            GROUP BY t.id,t.name,t.sport,t.color,u.username
            ORDER BY t.created_at DESC LIMIT 12
        ");
        $stmt->execute($current_user_id ? [$current_user_id] : []);
    }

    $teams = $stmt->fetchAll();
    $memberStmt = $pdo->prepare("
        SELECT u.username,UPPER(LEFT(u.username,2)) AS initials,u.profile_pic
        FROM team_members tm INNER JOIN users u ON u.id=tm.user_id
        WHERE tm.team_id=? ORDER BY tm.joined_at ASC LIMIT 4
    ");
    foreach ($teams as &$team) {
        $memberStmt->execute([$team['id']]);
        $members = $memberStmt->fetchAll();
        foreach ($members as &$m) {
            if ($m['profile_pic'] && !str_starts_with($m['profile_pic'],'http'))
                $m['profile_pic'] = '../uploads/'.$m['profile_pic'];
        }
        $team['members'] = $members;
    }
    echo json_encode(['success'=>true,'data'=>$teams]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage(),'data'=>[]]);
}