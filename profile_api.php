<?php
// profile_api.php — Handles GET (fetch) and POST (save) for profile data

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

// ─── GET: Fetch profile by ID ─────────────────────────────────────────────────
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$id) {
        echo json_encode(['error' => 'Profile ID is required.']);
        exit;
    }

    $db = getDB();

    // Fetch main profile
    $stmt = $db->prepare("SELECT * FROM profiles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();

    if (!$profile) {
        http_response_code(404);
        echo json_encode(['error' => 'Profile not found.']);
        exit;
    }

    // Fetch stats
    $stmt = $db->prepare("SELECT * FROM profile_stats WHERE profile_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $profile['stats'] = $stmt->get_result()->fetch_assoc();

    // Fetch tournaments
    $stmt = $db->prepare("SELECT * FROM tournaments WHERE profile_id = ? ORDER BY date DESC");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $profile['tournaments'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch sports played
    $stmt = $db->prepare("SELECT sport_name FROM sports_played WHERE profile_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $profile['sports'] = array_column($rows, 'sport_name');

    // Fetch achievements
    $stmt = $db->prepare("SELECT * FROM achievements WHERE profile_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $profile['achievements'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $db->close();
    echo json_encode(['success' => true, 'data' => $profile]);
    exit;
}

// ─── POST: Save / Update profile ─────────────────────────────────────────────
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input.']);
        exit;
    }

    $db = getDB();

    // Required field check
    if (empty($input['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name is required.']);
        exit;
    }

    $id          = isset($input['id']) ? (int)$input['id'] : 0;
    $name        = $db->real_escape_string($input['name']);
    $role        = $db->real_escape_string($input['role']        ?? '');
    $location    = $db->real_escape_string($input['location']    ?? '');
    $sport       = $db->real_escape_string($input['sport']       ?? '');
    $position    = $db->real_escape_string($input['position']    ?? '');
    $team        = $db->real_escape_string($input['team']        ?? '');
    $region      = $db->real_escape_string($input['region']      ?? '');
    $level       = $db->real_escape_string($input['level']       ?? '');
    $member_since = isset($input['member_since']) ? (int)$input['member_since'] : null;

    // ── INSERT or UPDATE profile ──
    if ($id > 0) {
        // Update existing
        $stmt = $db->prepare("UPDATE profiles SET name=?, role=?, location=?, sport=?, position=?, team=?, region=?, level=?, member_since=? WHERE id=?");
        $stmt->bind_param("ssssssssii", $name, $role, $location, $sport, $position, $team, $region, $level, $member_since, $id);
        $stmt->execute();
    } else {
        // Insert new
        $stmt = $db->prepare("INSERT INTO profiles (name, role, location, sport, position, team, region, level, member_since) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssssi", $name, $role, $location, $sport, $position, $team, $region, $level, $member_since);
        $stmt->execute();
        $id = $db->insert_id;
    }

    // ── Save stats ──
    if (isset($input['stats'])) {
        $s = $input['stats'];
        $joined    = (int)($s['tournaments_joined'] ?? 0);
        $won       = (int)($s['tournaments_won']    ?? 0);
        $organized = (int)($s['organized']          ?? 0);
        $earnings  = (float)($s['prize_earnings']   ?? 0);
        $win_rate  = (int)($s['win_rate']            ?? 0);
        $att_rate  = (int)($s['attendance_rate']     ?? 0);

        $stmt = $db->prepare("INSERT INTO profile_stats (profile_id, tournaments_joined, tournaments_won, organized, prize_earnings, win_rate, attendance_rate)
            VALUES (?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
                tournaments_joined=VALUES(tournaments_joined),
                tournaments_won=VALUES(tournaments_won),
                organized=VALUES(organized),
                prize_earnings=VALUES(prize_earnings),
                win_rate=VALUES(win_rate),
                attendance_rate=VALUES(attendance_rate)");
        $stmt->bind_param("iiiiidi", $id, $joined, $won, $organized, $earnings, $win_rate, $att_rate);
        $stmt->execute();
    }

    // ── Save sports played (replace all) ──
    if (isset($input['sports']) && is_array($input['sports'])) {
        $db->query("DELETE FROM sports_played WHERE profile_id = $id");
        $stmt = $db->prepare("INSERT INTO sports_played (profile_id, sport_name) VALUES (?, ?)");
        foreach ($input['sports'] as $sport_name) {
            $sport_name = $db->real_escape_string($sport_name);
            $stmt->bind_param("is", $id, $sport_name);
            $stmt->execute();
        }
    }

    // ── Save tournaments (replace all) ──
    if (isset($input['tournaments']) && is_array($input['tournaments'])) {
        $db->query("DELETE FROM tournaments WHERE profile_id = $id");
        $stmt = $db->prepare("INSERT INTO tournaments (profile_id, name, venue, date, result, type) VALUES (?,?,?,?,?,?)");
        foreach ($input['tournaments'] as $t) {
            $tname  = $db->real_escape_string($t['name']   ?? '');
            $venue  = $db->real_escape_string($t['venue']  ?? '');
            $date   = $db->real_escape_string($t['date']   ?? '');
            $result = $db->real_escape_string($t['result'] ?? 'upcoming');
            $type   = $db->real_escape_string($t['type']   ?? 'joined');
            $stmt->bind_param("isssss", $id, $tname, $venue, $date, $result, $type);
            $stmt->execute();
        }
    }

    // ── Save achievements (replace all) ──
    if (isset($input['achievements']) && is_array($input['achievements'])) {
        $db->query("DELETE FROM achievements WHERE profile_id = $id");
        $stmt = $db->prepare("INSERT INTO achievements (profile_id, icon, title, description, earned) VALUES (?,?,?,?,?)");
        foreach ($input['achievements'] as $a) {
            $icon   = $db->real_escape_string($a['icon']        ?? '');
            $title  = $db->real_escape_string($a['title']       ?? '');
            $desc   = $db->real_escape_string($a['description'] ?? '');
            $earned = (int)($a['earned'] ?? 0);
            $stmt->bind_param("isssi", $id, $icon, $title, $desc, $earned);
            $stmt->execute();
        }
    }

    $db->close();
    echo json_encode(['success' => true, 'profile_id' => $id, 'message' => 'Profile saved successfully.']);
    exit;
}

// ─── Unsupported method ───────────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
?>
