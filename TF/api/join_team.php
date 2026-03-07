<?php
require_once __DIR__ . '/db.php';
if (!$current_user_id) { echo json_encode(['success'=>false,'error'=>'Not logged in']); exit; }
$body    = json_decode(file_get_contents('php://input'), true);
$team_id = (int)($body['team_id'] ?? 0);
if (!$team_id) { echo json_encode(['success'=>false,'error'=>'Invalid team']); exit; }
try {
    $pdo->prepare("INSERT IGNORE INTO team_members (team_id,user_id) VALUES (?,?)")->execute([$team_id,$current_user_id]);
    echo json_encode(['success'=>true]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}