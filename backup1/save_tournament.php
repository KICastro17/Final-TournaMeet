<?php
session_start();

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'] ?? '', ['organizer', 'admin'])) {
    header('Location: login.php');
    exit;
}

include "config.php";
require_once "organizer_helpers.php";
ensureOrganizerSchema($conn);

// If sport was passed as a hidden field (category route), fall back to sport_manual
if (empty(trim($_POST['sport'] ?? '')) && !empty(trim($_POST['sport_manual'] ?? ''))) {
    $_POST['sport'] = trim($_POST['sport_manual']);
}

$payload = sanitizeTournamentPayload($_POST);
$data    = $payload['data'];
$errors  = $payload['errors'];

if (!empty($errors)) {
    // Show a friendly error page with back button
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Validation Error – TOURNAMEET</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:"Manrope",sans-serif;background:#fffaf4;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
    .card{background:#fff;border:1px solid #f0d9bf;border-radius:16px;padding:36px;max-width:500px;width:100%;box-shadow:0 14px 30px rgba(200,120,0,0.1)}
    h2{color:#b72b2b;font-size:22px;margin-bottom:16px}
    ul{padding-left:18px;color:#5d2a2a;font-size:14px;line-height:2}
    .btn{display:inline-block;margin-top:22px;padding:11px 22px;background:linear-gradient(120deg,#ff8c00,#e97817);color:#fff;border-radius:9px;text-decoration:none;font-weight:700;font-size:14px}
    </style>
    </head>
    <body>
    <div class="card">
        <h2><i>⚠️</i> Could not publish tournament</h2>
        <ul>
        <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
        <?php endforeach; ?>
        </ul>
        <a class="btn" href="javascript:history.back()">← Fix &amp; Try Again</a>
    </div>
    </body>
    </html>
    <?php
    exit;
}

$createdBy = $_SESSION['username'];

$stmt = $conn->prepare("INSERT INTO tournaments
    (title, description, sport, event_date, event_time, registration_deadline,
     location, registration_fee, prize_pool, slots, requirements,
     organizer_note, is_closed, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)");

// Show prepare error clearly
if (!$stmt) {
    die("<pre style='color:red;padding:20px'>
SQL Prepare Failed: " . $conn->error . "
    </pre>");
}

$stmt->bind_param(
    "sssssssddisss",
    $data['title'],
    $data['description'],
    $data['sport'],
    $data['date'],
    $data['time'],
    $data['registration_deadline'],
    $data['location'],
    $data['registration_fee'],
    $data['prize_pool'],
    $data['slots'],
    $data['requirements'],
    $data['organizer_note'],
    $createdBy
);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: organizer_dashboard.php?msg=created");
    exit;
}

$errMsg = htmlspecialchars($stmt->error ?: $conn->error);
$stmt->close();

echo "<p style='color:red;font-family:monospace;padding:20px'>DB Error: $errMsg</p>";
?>