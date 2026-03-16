<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== "admin") {
    die("🚫 Access Denied — Admin Only");
}

include "config.php";

$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")
    ->fetch_assoc()['count'];

$totalAthletes = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='athlete'")
    ->fetch_assoc()['count'];

$totalOrganizers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='organizer'")
    ->fetch_assoc()['count'];

$recentUsers = $conn->query("
    SELECT username, email 
    FROM users 
    WHERE role != 'admin'
    ORDER BY id DESC 
    LIMIT 5
");

$notifications = $conn->query("
    SELECT * FROM notifications 
    ORDER BY created_at DESC 
    LIMIT 10
");

// Pending tournaments count for badge
$pendingCount = $conn->query("
    SELECT COUNT(*) as count FROM tournaments 
    WHERE status IS NULL OR status = 'pending'
")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0b0d10;
            --surface:   #111318;
            --surface2:  #181c23;
            --border:    rgba(255,255,255,0.07);
            --text:      #e8eaf0;
            --muted:     #6b7280;
            --accent:    #f97316;
            --accent2:   #fb923c;
            --blue:      #3b82f6;
            --green:     #10b981;
            --red:       #ef4444;
            --radius:    14px;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none; z-index: 0;
        }

        /* ══ TOPBAR ══ */
        .topbar {
            position: sticky; top: 0; z-index: 500;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px;
            height: 64px;
            background: rgba(11,13,16,0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }

        .brand {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 20px;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--accent), #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex; align-items: center; gap: 4px; overflow: visible;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
            padding: 7px 14px;
            border-radius: 8px;
            transition: color 0.2s, background 0.2s;
        }
        .nav-links a:hover { color: var(--text); background: var(--surface2); }
        .nav-links a.active { color: var(--text); }

        /* Tournaments nav button */
        .nav-tournament-link {
            display: inline-flex !important;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
            padding: 7px 14px;
            border-radius: 8px;
            transition: color 0.2s, background 0.2s;
            cursor: pointer;
        }
        .nav-tournament-link:hover { color: var(--text); background: var(--surface2); }

        .pending-badge {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 18px; height: 18px;
            background: var(--accent);
            color: #fff;
            font-size: 10px; font-weight: 700;
            border-radius: 50px;
            padding: 0 5px;
            line-height: 1;
            animation: pulse-badge 2s infinite;
        }
        .pending-badge.hidden { display: none; }

        @keyframes pulse-badge {
            0%, 100% { box-shadow: 0 0 0 0 rgba(249,115,22,0.5); }
            50%       { box-shadow: 0 0 0 5px rgba(249,115,22,0); }
        }

        .logout-btn {
            background: var(--accent);
            color: #fff !important;
            border-radius: 8px !important;
            padding: 7px 18px !important;
            font-weight: 600 !important;
            transition: opacity 0.2s !important;
        }
        .logout-btn:hover { opacity: 0.85; background: transparent !important; }

        .hamburger {
            display: none;
            cursor: pointer;
            font-size: 22px;
            color: var(--text);
            background: none; border: none;
            padding: 4px 8px;
        }

        /* ══ PAGE ══ */
        .page {
            position: relative;
            max-width: 1100px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }

        .page-header {
            margin-bottom: 40px;
            animation: fadeUp 0.5s ease both;
        }
        .page-header .eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 6px;
        }
        .page-header h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(26px, 4vw, 38px);
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--text);
        }

        /* ══ STATS ══ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px 28px 24px;
            overflow: hidden;
            animation: fadeUp 0.5s ease both;
            transition: border-color 0.3s, transform 0.25s;
        }
        .stat-card:hover { transform: translateY(-3px); border-color: rgba(255,255,255,0.15); }
        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.10s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }

        .stat-card::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0;
            width: 4px;
            border-radius: 4px 0 0 4px;
        }
        .stat-card.orange::before { background: var(--accent); }
        .stat-card.blue::before   { background: var(--blue); }
        .stat-card.green::before  { background: var(--green); }

        .stat-card::after {
            content: '';
            position: absolute; right: -30px; top: -30px;
            width: 120px; height: 120px;
            border-radius: 50%;
            opacity: 0.08;
        }
        .stat-card.orange::after { background: var(--accent); }
        .stat-card.blue::after   { background: var(--blue); }
        .stat-card.green::after  { background: var(--green); }

        .stat-label {
            font-size: 10px; font-weight: 700;
            letter-spacing: 2px; text-transform: uppercase;
            color: var(--muted); margin-bottom: 12px;
        }
        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 52px; font-weight: 800;
            letter-spacing: -2px; line-height: 1;
        }
        .stat-card.orange .stat-value { color: var(--accent); }
        .stat-card.blue   .stat-value { color: var(--blue); }
        .stat-card.green  .stat-value { color: var(--green); }

        /* ══ TWO-COL ══ */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 20px;
            align-items: start;
        }

        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            animation: fadeUp 0.5s ease 0.2s both;
        }

        .section-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border);
        }
        .section-head h2 {
            font-family: 'Syne', sans-serif;
            font-size: 15px; font-weight: 700; letter-spacing: -0.3px;
        }
        .badge {
            font-size: 11px; font-weight: 600;
            background: rgba(249,115,22,0.15);
            color: var(--accent);
            padding: 3px 9px; border-radius: 20px;
        }

        .notif-list { padding: 8px 0; max-height: 360px; overflow-y: auto; }
        .notif-list::-webkit-scrollbar { width: 4px; }
        .notif-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        .notif-item {
            display: flex; gap: 14px; align-items: flex-start;
            padding: 14px 24px;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: var(--surface2); }

        .notif-dot {
            width: 8px; height: 8px;
            background: var(--accent); border-radius: 50%;
            margin-top: 5px; flex-shrink: 0;
        }
        .notif-msg { font-size: 13.5px; line-height: 1.5; color: var(--text); }
        .notif-time { font-size: 11px; color: var(--muted); margin-top: 2px; }

        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th {
            font-size: 10px; font-weight: 700;
            letter-spacing: 1.8px; text-transform: uppercase;
            color: var(--muted); padding: 12px 24px; text-align: left;
            background: var(--surface2); border-bottom: 1px solid var(--border);
        }
        .admin-table td {
            padding: 14px 24px; font-size: 13.5px;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }
        .admin-table tr:last-child td { border-bottom: none; }
        .admin-table tr:hover td { background: var(--surface2); }

        .td-name { display: flex; align-items: center; gap: 10px; }
        .avatar {
            width: 30px; height: 30px;
            background: linear-gradient(135deg, var(--accent), #fbbf24);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .td-email { color: var(--muted); font-size: 13px; }

        /* ══ TOURNAMENTS MODAL ══ */
        .tmodal {
            display: none;
            position: fixed; inset: 0; z-index: 600;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(8px);
            align-items: center; justify-content: center;
            padding: 20px;
        }
        .tmodal.open { display: flex; }

        .tmodal-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            width: 100%; max-width: 860px;
            max-height: 88vh;
            display: flex; flex-direction: column;
            overflow: hidden;
            animation: scaleIn 0.25s ease;
        }

        .tmodal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 22px 28px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .tmodal-header-left { display: flex; align-items: center; gap: 12px; }
        .tmodal-header-left h2 {
            font-family: 'Syne', sans-serif;
            font-size: 18px; font-weight: 800; letter-spacing: -0.5px;
        }
        .tmodal-badge {
            font-size: 11px; font-weight: 700;
            background: rgba(249,115,22,0.18);
            color: var(--accent);
            padding: 3px 10px; border-radius: 20px;
        }
        .tmodal-close {
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--muted);
            width: 34px; height: 34px;
            border-radius: 50%;
            cursor: pointer; font-size: 16px;
            display: flex; align-items: center; justify-content: center;
            transition: color 0.2s, border-color 0.2s;
        }
        .tmodal-close:hover { color: var(--text); border-color: rgba(255,255,255,0.2); }

        /* Filter tabs */
        .tmodal-tabs {
            display: flex; gap: 4px;
            padding: 12px 28px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .ttab {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px; font-weight: 500;
            cursor: pointer;
            border: none;
            background: none;
            color: var(--muted);
            font-family: 'DM Sans', sans-serif;
            transition: background 0.2s, color 0.2s;
            display: flex; align-items: center; gap: 6px;
        }
        .ttab:hover { background: var(--surface2); color: var(--text); }
        .ttab.active { background: var(--surface2); color: var(--text); }
        .ttab .ttab-count {
            font-size: 10px; font-weight: 700;
            padding: 1px 6px; border-radius: 50px;
            background: rgba(255,255,255,0.08);
        }
        .ttab.active.tab-pending .ttab-count { background: rgba(249,115,22,0.2); color: var(--accent); }
        .ttab.active.tab-approved .ttab-count { background: rgba(16,185,129,0.2); color: var(--green); }
        .ttab.active.tab-declined .ttab-count { background: rgba(239,68,68,0.2); color: var(--red); }

        .tmodal-body {
            flex: 1; overflow-y: auto;
            padding: 16px 28px 24px;
        }
        .tmodal-body::-webkit-scrollbar { width: 4px; }
        .tmodal-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        /* Tournament item */
        .t-item {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 22px;
            margin-bottom: 12px;
            transition: border-color 0.2s;
            animation: fadeUp 0.3s ease both;
        }
        .t-item:hover { border-color: rgba(255,255,255,0.12); }

        .t-item-top {
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: 16px;
            margin-bottom: 14px;
        }
        .t-item-left { flex: 1; min-width: 0; }
        .t-sport-pill {
            display: inline-block;
            font-size: 10px; font-weight: 700; letter-spacing: 1.5px;
            text-transform: uppercase;
            background: rgba(249,115,22,0.12);
            color: var(--accent);
            padding: 2px 9px; border-radius: 50px;
            margin-bottom: 7px;
        }
        .t-item-name {
            font-family: 'Syne', sans-serif;
            font-size: 16px; font-weight: 700; letter-spacing: -0.3px;
            color: var(--text);
            margin-bottom: 4px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .t-item-organizer {
            font-size: 12px; color: var(--muted);
        }
        .t-item-organizer strong { color: rgba(255,255,255,0.6); }

        .t-item-status {
            font-size: 11px; font-weight: 700; letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 4px 11px; border-radius: 50px;
            flex-shrink: 0;
        }
        .t-item-status.pending  { background: rgba(249,115,22,0.12); color: var(--accent); }
        .t-item-status.approved { background: rgba(16,185,129,0.12); color: var(--green); }
        .t-item-status.declined { background: rgba(239,68,68,0.12);  color: var(--red); }

        .t-item-meta {
            display: flex; flex-wrap: wrap; gap: 10px;
            margin-bottom: 16px;
        }
        .t-meta-chip {
            display: flex; align-items: center; gap: 5px;
            font-size: 12px; color: var(--muted);
            background: rgba(255,255,255,0.04);
            padding: 4px 10px; border-radius: 6px;
        }
        .t-meta-chip svg { flex-shrink: 0; color: var(--accent); opacity: 0.8; }

        .t-item-desc {
            font-size: 13px; color: var(--muted);
            line-height: 1.6;
            margin-bottom: 16px;
            padding: 12px;
            background: rgba(255,255,255,0.03);
            border-radius: 8px;
            border-left: 3px solid rgba(249,115,22,0.3);
        }

        .t-item-actions {
            display: flex; gap: 8px; justify-content: flex-end;
        }

        .btn-approve {
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; border: none;
            background: rgba(16,185,129,0.15);
            color: var(--green);
            border: 1px solid rgba(16,185,129,0.3);
            font-family: 'DM Sans', sans-serif;
            transition: background 0.2s, border-color 0.2s, transform 0.15s;
            display: flex; align-items: center; gap: 6px;
        }
        .btn-approve:hover {
            background: var(--green); color: #fff;
            border-color: var(--green); transform: translateY(-1px);
        }

        .btn-decline {
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; border: none;
            background: rgba(239,68,68,0.12);
            color: var(--red);
            border: 1px solid rgba(239,68,68,0.25);
            font-family: 'DM Sans', sans-serif;
            transition: background 0.2s, border-color 0.2s, transform 0.15s;
            display: flex; align-items: center; gap: 6px;
        }
        .btn-decline:hover {
            background: var(--red); color: #fff;
            border-color: var(--red); transform: translateY(-1px);
        }

        /* empty state */
        .t-empty {
            text-align: center; padding: 60px 20px;
            color: var(--muted);
        }
        .t-empty-icon {
            width: 60px; height: 60px;
            background: var(--surface2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 24px;
        }
        .t-empty h3 {
            font-family: 'Syne', sans-serif;
            font-size: 16px; font-weight: 700;
            color: rgba(255,255,255,0.2);
            margin-bottom: 6px;
        }
        .t-empty p { font-size: 13px; color: rgba(255,255,255,0.12); }

        /* skeleton */
        .t-skel {
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 12px; padding: 20px 22px; margin-bottom: 12px;
        }
        .skel-line {
            background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.09) 50%, rgba(255,255,255,0.05) 75%);
            background-size: 400% 100%;
            animation: shimmer 1.5s ease-in-out infinite;
            border-radius: 5px;
        }
        @keyframes shimmer {
            0%   { background-position: 100% 50%; }
            100% { background-position: -100% 50%; }
        }

        /* ══ LOGOUT MODAL ══ */
        .modal {
            display: none;
            position: fixed; inset: 0; z-index: 700;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(6px);
            align-items: center; justify-content: center;
        }
        .modal.open { display: flex; }
        .modal-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 18px; padding: 36px 32px 28px;
            max-width: 360px; width: 90%;
            text-align: center; animation: scaleIn 0.22s ease;
        }
        .modal-icon {
            width: 52px; height: 52px;
            background: rgba(249,115,22,0.12); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px; font-size: 22px;
        }
        .modal-card h3 {
            font-family: 'Syne', sans-serif;
            font-size: 20px; font-weight: 700; margin-bottom: 8px;
        }
        .modal-card p { color: var(--muted); font-size: 14px; margin-bottom: 24px; line-height: 1.6; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .btn {
            padding: 10px 22px; border-radius: 9px;
            font-size: 14px; font-weight: 600;
            cursor: pointer; border: none;
            transition: opacity 0.2s, transform 0.15s;
        }
        .btn:hover { opacity: 0.85; transform: translateY(-1px); }
        .btn-ghost { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
        .btn-danger { background: var(--accent); color: #fff; }

        /* ══ ANIMATIONS ══ */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.93); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* ══ RESPONSIVE ══ */
        @media (max-width: 780px) {
            .stats-grid { grid-template-columns: 1fr; }
            .two-col    { grid-template-columns: 1fr; }
            .nav-links  { display: none; }
            .hamburger  { display: block; }
            .nav-links.open {
                display: flex; flex-direction: column;
                position: absolute; top: 64px; left: 0; right: 0;
                background: var(--surface); border-bottom: 1px solid var(--border);
                padding: 12px 0; gap: 0;
            }
            .nav-links.open a { border-radius: 0; padding: 12px 24px; }
        }
    </style>
</head>
<body>

<!-- ══ TOPBAR ══ -->
<nav class="topbar">
    <span class="brand">Admin</span>

    <div class="nav-links" id="navMenu">
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="view_users.php">Users</a>
        <a href="admin_tournaments.php">Tournaments <?php if($pendingCount > 0): ?><span style="background:var(--accent);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:50px;margin-left:2px;"><?php echo $pendingCount; ?></span><?php endif; ?></a>
        <a href="#" class="logout-btn" onclick="openLogoutModal(); return false;">Log Out</a>
    </div>

    <button class="hamburger" onclick="toggleMenu()">☰</button>
</nav>

<!-- ══ PAGE ══ -->
<main class="page">

    <div class="page-header">
        <div class="eyebrow">Control Panel</div>
        <h1>Dashboard Overview</h1>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card orange">
            <div class="stat-label">Total Users</div>
            <div class="stat-value" id="totalUsers"><?php echo $totalUsers; ?></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Athletes</div>
            <div class="stat-value" id="totalAthletes"><?php echo $totalAthletes; ?></div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Organizers</div>
            <div class="stat-value" id="totalOrganizers"><?php echo $totalOrganizers; ?></div>
        </div>
    </div>

    <!-- TWO COL -->
    <div class="two-col">

        <!-- NOTIFICATIONS -->
        <div class="section-card">
            <div class="section-head">
                <h2>Notifications</h2>
                <span class="badge">Live</span>
            </div>
            <div class="notif-list" id="notificationBox">
                <?php if ($notifications->num_rows > 0): ?>
                    <?php while($row = $notifications->fetch_assoc()): ?>
                        <div class="notif-item">
                            <div class="notif-dot"></div>
                            <div>
                                <div class="notif-msg"><?php echo htmlspecialchars($row['message']); ?></div>
                                <div class="notif-time"><?php echo $row['created_at']; ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="notif-item"><div class="notif-msg" style="color:var(--muted)">No notifications yet.</div></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RECENT USERS -->
        <div class="section-card">
            <div class="section-head">
                <h2>Recent Users</h2>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody id="recentUsersTable">
                    <?php while($row = $recentUsers->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="td-name">
                                <div class="avatar"><?php echo strtoupper(substr($row['username'], 0, 1)); ?></div>
                                <?php echo htmlspecialchars($row['username']); ?>
                            </div>
                        </td>
                        <td class="td-email"><?php echo htmlspecialchars($row['email']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<!-- ══ TOURNAMENTS MODAL ══ -->
<div id="tournamentsModal" class="tmodal">
    <div class="tmodal-card">
        <div class="tmodal-header">
            <div class="tmodal-header-left">
                <h2>Tournaments</h2>
                <span class="tmodal-badge" id="tmodalPendingBadge"><?php echo $pendingCount; ?> pending</span>
            </div>
            <button class="tmodal-close" onclick="closeTournamentsModal()">✕</button>
        </div>

        <div class="tmodal-tabs">
            <button class="ttab tab-pending active" onclick="switchTab('pending')" id="tab-pending">
                Pending <span class="ttab-count" id="count-pending">–</span>
            </button>
            <button class="ttab tab-approved" onclick="switchTab('approved')" id="tab-approved">
                Approved <span class="ttab-count" id="count-approved">–</span>
            </button>
            <button class="ttab tab-declined" onclick="switchTab('declined')" id="tab-declined">
                Declined <span class="ttab-count" id="count-declined">–</span>
            </button>
        </div>

        <div class="tmodal-body" id="tmodalBody">
            <!-- loaded via JS -->
        </div>
    </div>
</div>

<!-- ══ LOGOUT MODAL ══ -->
<div id="logoutModal" class="modal">
    <div class="modal-card">
        <div class="modal-icon">🚪</div>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to end your admin session?</p>
        <div class="modal-actions">
            <button class="btn btn-ghost" onclick="closeLogoutModal()">Cancel</button>
            <button class="btn btn-danger" onclick="doLogout()">Yes, Logout</button>
        </div>
    </div>
</div>

<!-- ══ SCRIPTS ══ -->
<script>
/* ── Nav ── */
function toggleMenu() {
    document.getElementById('navMenu').classList.toggle('open');
}

/* ── Logout Modal ── */
function openLogoutModal()  { document.getElementById('logoutModal').classList.add('open'); }
function closeLogoutModal() { document.getElementById('logoutModal').classList.remove('open'); }
function doLogout()         { window.location.href = 'logout.php'; }

window.addEventListener('click', e => {
    if (e.target === document.getElementById('logoutModal')) closeLogoutModal();
    if (e.target === document.getElementById('tournamentsModal')) closeTournamentsModal();
});

/* ── Dashboard auto-refresh ── */
function fetchDashboardData() {
    fetch('get_dashboard_data.php')
        .then(r => r.json())
        .then(data => {
            document.getElementById('totalUsers').textContent      = data.totalUsers;
            document.getElementById('totalAthletes').textContent   = data.totalAthletes;
            document.getElementById('totalOrganizers').textContent = data.totalOrganizers;

            const tbody = document.getElementById('recentUsersTable');
            tbody.innerHTML = data.recentUsers.map(u => `
                <tr>
                    <td><div class="td-name"><div class="avatar">${u.username.charAt(0).toUpperCase()}</div>${u.username}</div></td>
                    <td class="td-email">${u.email}</td>
                </tr>
            `).join('');

            const box = document.getElementById('notificationBox');
            if (data.notifications.length > 0) {
                box.innerHTML = data.notifications.map(n => `
                    <div class="notif-item">
                        <div class="notif-dot"></div>
                        <div>
                            <div class="notif-msg">${n.message}</div>
                            <div class="notif-time">${n.created_at}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                box.innerHTML = '<div class="notif-item"><div class="notif-msg" style="color:var(--muted)">No notifications yet.</div></div>';
            }
        })
        .catch(() => {});
}

fetchDashboardData();
setInterval(fetchDashboardData, 5000);

/* ════════════════════════════════
   TOURNAMENTS MODAL
════════════════════════════════ */
let allTournaments = [];
let currentTab = 'pending';

function openTournamentsModal() {
    document.getElementById('tournamentsModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    loadTournaments();
}

function closeTournamentsModal() {
    document.getElementById('tournamentsModal').classList.remove('open');
    document.body.style.overflow = '';
}

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.ttab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    renderTournaments();
}

function loadTournaments() {
    showTSkeleton();
    // Build URL relative to current page location
    const base = window.location.pathname.replace(/\/[^\/]*$/, '/');
    fetch('admin_get_pending_tournaments.php')
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status + ' — ' + r.url);
            return r.json();
        })
        .then(data => {
            if (data.success) {
                allTournaments = data.data || [];
                updateCounts();
                renderTournaments();
            } else {
                showTError(data.message || 'API returned success:false');
            }
        })
        .catch(err => showTError(err.message));
}

function updateCounts() {
    const counts = { pending: 0, approved: 0, declined: 0 };
    allTournaments.forEach(t => {
        const s = t.approval_status || 'pending';
        if (counts[s] !== undefined) counts[s]++;
    });
    document.getElementById('count-pending').textContent  = counts.pending;
    document.getElementById('count-approved').textContent = counts.approved;
    document.getElementById('count-declined').textContent = counts.declined;

    // Update nav badge
    const badge = document.getElementById('navPendingBadge');
    const tBadge = document.getElementById('tmodalPendingBadge');
    badge.textContent = counts.pending;
    badge.classList.toggle('hidden', counts.pending === 0);
    tBadge.textContent = counts.pending + ' pending';
}

function renderTournaments() {
    const body = document.getElementById('tmodalBody');
    const filtered = allTournaments.filter(t => (t.approval_status || 'pending') === currentTab);

    if (!filtered.length) {
        const labels = { pending: 'No pending tournaments', approved: 'No approved tournaments', declined: 'No declined tournaments' };
        const subs   = { pending: 'Organizers haven\'t submitted any yet.', approved: 'Approve a pending tournament to see it here.', declined: 'Declined tournaments will appear here.' };
        body.innerHTML = `
            <div class="t-empty">
                <div class="t-empty-icon">${currentTab === 'pending' ? '⏳' : currentTab === 'approved' ? '✅' : '❌'}</div>
                <h3>${labels[currentTab]}</h3>
                <p>${subs[currentTab]}</p>
            </div>`;
        return;
    }

    body.innerHTML = filtered.map((t, i) => {
        const status   = t.approval_status || 'pending';
        const dateStr  = t.date  ? new Date(t.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
        const deadStr  = t.registration_deadline ? new Date(t.registration_deadline).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
        const feeStr   = t.entrance_fee > 0 ? '₱' + parseFloat(t.entrance_fee).toLocaleString() : 'Free';
        const desc     = t.description ? t.description.substring(0, 180) + (t.description.length > 180 ? '…' : '') : '';

        const actionBtns = status === 'pending' ? `
            <button class="btn-decline" onclick="tournamentAction(${t.id}, 'declined')">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Decline
            </button>
            <button class="btn-approve" onclick="tournamentAction(${t.id}, 'approved')">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
            </button>` : '';

        return `
        <div class="t-item" id="t-item-${t.id}" style="animation-delay:${i * 40}ms">
            <div class="t-item-top">
                <div class="t-item-left">
                    <div class="t-sport-pill">${esc(t.sport)}</div>
                    <div class="t-item-name" title="${esc(t.name)}">${esc(t.name)}</div>
                    <div class="t-item-organizer">by <strong>${esc(t.created_by)}</strong></div>
                </div>
                <span class="t-item-status ${status}">${status}</span>
            </div>
            <div class="t-item-meta">
                <div class="t-meta-chip">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    ${dateStr}
                </div>
                <div class="t-meta-chip">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    ${esc(t.location || '—')}
                </div>
                <div class="t-meta-chip">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    ${esc(t.time || '—')}
                </div>
                <div class="t-meta-chip">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    ${feeStr}
                </div>
                ${t.prize ? `<div class="t-meta-chip">🏆 ${esc(t.prize)}</div>` : ''}
                <div class="t-meta-chip">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    ${t.slots_total || '?'} slots · deadline ${deadStr}
                </div>
            </div>
            ${desc ? `<div class="t-item-desc">${esc(desc)}</div>` : ''}
            ${actionBtns ? `<div class="t-item-actions">${actionBtns}</div>` : ''}
        </div>`;
    }).join('');
}

function tournamentAction(id, action) {
    const label = action === 'approved' ? 'Approve' : 'Decline';
    if (!confirm(`${label} this tournament?`)) return;

    const base = window.location.pathname.replace(/\/[^\/]*$/, '/');
    fetch('admin_tournament_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const t = allTournaments.find(x => x.id == id);
            if (t) t.approval_status = action;
            updateCounts();
            const el = document.getElementById('t-item-' + id);
            if (el) {
                el.style.transition = 'opacity 0.25s, transform 0.25s';
                el.style.opacity = '0';
                el.style.transform = 'translateX(20px)';
                setTimeout(() => renderTournaments(), 280);
            }
        } else {
            alert('Failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => alert('Network error: ' + err.message));
}

function showTSkeleton() {
    document.getElementById('tmodalBody').innerHTML = [1,2,3].map(() => `
        <div class="t-skel">
            <div class="skel-line" style="height:11px;width:70px;margin-bottom:10px;"></div>
            <div class="skel-line" style="height:18px;width:55%;margin-bottom:6px;"></div>
            <div class="skel-line" style="height:13px;width:30%;margin-bottom:16px;"></div>
            <div style="display:flex;gap:8px;margin-bottom:14px;">
                ${[80,110,90,70].map(w => `<div class="skel-line" style="height:26px;width:${w}px;border-radius:6px;"></div>`).join('')}
            </div>
            <div class="skel-line" style="height:52px;border-radius:8px;margin-bottom:14px;"></div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <div class="skel-line" style="height:34px;width:90px;border-radius:8px;"></div>
                <div class="skel-line" style="height:34px;width:90px;border-radius:8px;"></div>
            </div>
        </div>
    `).join('');
}

function showTError(msg) {
    document.getElementById('tmodalBody').innerHTML = `
        <div class="t-empty">
            <div class="t-empty-icon">⚠️</div>
            <h3>Could not load tournaments</h3>
            <p style="font-family:monospace;font-size:11px;background:rgba(255,255,255,0.05);padding:8px 12px;border-radius:6px;margin:8px 0;word-break:break-all;">${esc(msg || 'Unknown error')}</p>
            <button onclick="loadTournaments()" style="margin-top:14px;padding:8px 18px;background:var(--accent);color:#fff;border:none;border-radius:8px;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:600;">Retry</button>
        </div>`;
}
}

function esc(str) {
    return String(str || '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeTournamentsModal();
        closeLogoutModal();
    }
});
</script>

</body>
</html>