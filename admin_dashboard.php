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

/* ================= INITIAL DATA ================= */

$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")
    ->fetch_assoc()['count'];

$totalAthletes = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='athlete'")
    ->fetch_assoc()['count'];

$totalOrganizers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='organizer'")
    ->fetch_assoc()['count'];

/* ===== RECENT USERS ===== */
$recentUsers = $conn->query("
    SELECT username, email 
    FROM users 
    WHERE role != 'admin'
    ORDER BY id DESC 
    LIMIT 5
");

/* ===== NOTIFICATIONS ===== */
$notifications = $conn->query("
    SELECT * FROM notifications 
    ORDER BY created_at DESC 
    LIMIT 10
");
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

        /* ── noise overlay ── */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none; z-index: 0;
        }

        /* ══════════════ TOPBAR ══════════════ */
        .topbar {
            position: sticky; top: 0; z-index: 100;
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
            display: flex; align-items: center; gap: 4px;
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

        .logout-btn {
            background: var(--accent);
            color: #fff !important;
            border-radius: 8px !important;
            padding: 7px 18px !important;
            font-weight: 600 !important;
            transition: opacity 0.2s !important;
        }
        .logout-btn:hover { opacity: 0.85; background: transparent !important; }

        /* hamburger */
        .hamburger {
            display: none;
            cursor: pointer;
            font-size: 22px;
            color: var(--text);
            background: none; border: none;
            padding: 4px 8px;
        }

        /* ══════════════ MAIN CONTENT ══════════════ */
        .page {
            position: relative; z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }

        /* ── page header ── */
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

        /* ══════════════ STAT CARDS ══════════════ */
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

        /* colored bar on left edge */
        .stat-card::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0;
            width: 4px;
            border-radius: 4px 0 0 4px;
        }
        .stat-card.orange::before { background: var(--accent); }
        .stat-card.blue::before   { background: var(--blue); }
        .stat-card.green::before  { background: var(--green); }

        /* glow blob */
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
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 52px;
            font-weight: 800;
            letter-spacing: -2px;
            line-height: 1;
        }
        .stat-card.orange .stat-value { color: var(--accent); }
        .stat-card.blue   .stat-value { color: var(--blue); }
        .stat-card.green  .stat-value { color: var(--green); }

        /* ══════════════ TWO-COL LAYOUT ══════════════ */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 20px;
            align-items: start;
        }

        /* ══════════════ SECTION CARD ══════════════ */
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
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .badge {
            font-size: 11px;
            font-weight: 600;
            background: rgba(249,115,22,0.15);
            color: var(--accent);
            padding: 3px 9px;
            border-radius: 20px;
        }

        /* ── notifications ── */
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
            background: var(--accent);
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .notif-msg { font-size: 13.5px; line-height: 1.5; color: var(--text); }
        .notif-time { font-size: 11px; color: var(--muted); margin-top: 2px; }

        /* ── recent users table ── */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        .admin-table th {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--muted);
            padding: 12px 24px;
            text-align: left;
            background: var(--surface2);
            border-bottom: 1px solid var(--border);
        }
        .admin-table td {
            padding: 14px 24px;
            font-size: 13.5px;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }
        .admin-table tr:last-child td { border-bottom: none; }
        .admin-table tr:hover td { background: var(--surface2); }

        .td-name {
            display: flex; align-items: center; gap: 10px;
        }
        .avatar {
            width: 30px; height: 30px;
            background: linear-gradient(135deg, var(--accent), #fbbf24);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .td-email { color: var(--muted); font-size: 13px; }

        /* ══════════════ MODAL ══════════════ */
        .modal {
            display: none;
            position: fixed; inset: 0; z-index: 999;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(6px);
            align-items: center; justify-content: center;
        }
        .modal.open { display: flex; }

        .modal-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 36px 32px 28px;
            max-width: 360px; width: 90%;
            text-align: center;
            animation: scaleIn 0.22s ease;
        }
        .modal-icon {
            width: 52px; height: 52px;
            background: rgba(249,115,22,0.12);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
            font-size: 22px;
        }
        .modal-card h3 {
            font-family: 'Syne', sans-serif;
            font-size: 20px; font-weight: 700;
            margin-bottom: 8px;
        }
        .modal-card p { color: var(--muted); font-size: 14px; margin-bottom: 24px; line-height: 1.6; }

        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .btn {
            padding: 10px 22px;
            border-radius: 9px;
            font-size: 14px; font-weight: 600;
            cursor: pointer; border: none; transition: opacity 0.2s, transform 0.15s;
        }
        .btn:hover { opacity: 0.85; transform: translateY(-1px); }
        .btn-ghost {
            background: var(--surface2);
            color: var(--text);
            border: 1px solid var(--border);
        }
        .btn-danger { background: var(--accent); color: #fff; }

        /* ══════════════ ANIMATIONS ══════════════ */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.93); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* ══════════════ RESPONSIVE ══════════════ */
        @media (max-width: 780px) {
            .stats-grid { grid-template-columns: 1fr; }
            .two-col    { grid-template-columns: 1fr; }
            .nav-links  { display: none; }
            .hamburger  { display: block; }
            .nav-links.open {
                display: flex; flex-direction: column;
                position: absolute; top: 64px; left: 0; right: 0;
                background: var(--surface);
                border-bottom: 1px solid var(--border);
                padding: 12px 0;
                gap: 0;
            }
            .nav-links.open a { border-radius: 0; padding: 12px 24px; }
        }
    </style>
</head>
<body>

<!-- ══════════════ TOPBAR ══════════════ -->
<nav class="topbar">
    <span class="brand">Admin</span>

    <div class="nav-links" id="navMenu">
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="view_users.php">Users</a>
        <a href="#" class="logout-btn" onclick="openLogoutModal(); return false;">Log Out</a>
    </div>

    <button class="hamburger" onclick="toggleMenu()">☰</button>
</nav>

<!-- ══════════════ PAGE ══════════════ -->
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

<!-- ══════════════ LOGOUT MODAL ══════════════ -->
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

<!-- ══════════════ SCRIPT ══════════════ -->
<script>
function toggleMenu() {
    document.getElementById('navMenu').classList.toggle('open');
}

/* ── Logout Modal ── */
function openLogoutModal()  { document.getElementById('logoutModal').classList.add('open'); }
function closeLogoutModal() { document.getElementById('logoutModal').classList.remove('open'); }
function doLogout()         { window.location.href = 'logout.php'; }

window.addEventListener('click', e => {
    const modal = document.getElementById('logoutModal');
    if (e.target === modal) closeLogoutModal();
});

/* ── Auto-refresh dashboard ── */
function fetchDashboardData() {
    fetch('get_dashboard_data.php')
        .then(r => r.json())
        .then(data => {

            document.getElementById('totalUsers').textContent     = data.totalUsers;
            document.getElementById('totalAthletes').textContent  = data.totalAthletes;
            document.getElementById('totalOrganizers').textContent = data.totalOrganizers;

            /* recent users */
            const tbody = document.getElementById('recentUsersTable');
            tbody.innerHTML = data.recentUsers.map(u => `
                <tr>
                    <td>
                        <div class="td-name">
                            <div class="avatar">${u.username.charAt(0).toUpperCase()}</div>
                            ${u.username}
                        </div>
                    </td>
                    <td class="td-email">${u.email}</td>
                </tr>
            `).join('');

            /* notifications */
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
        .catch(() => {}); /* silent fail */
}

fetchDashboardData();
setInterval(fetchDashboardData, 5000);
</script>

</body>
</html>