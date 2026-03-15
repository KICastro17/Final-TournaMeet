<?php
session_start();
include "config.php";

/* ===== ADMIN CHECK ===== */
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if ($_SESSION['role'] !== "admin") { die("🚫 Admin Access Only"); }

/* ===== SEARCH & FILTER ===== */
$search = $_GET['search'] ?? "";
$filter = $_GET['filter'] ?? "";

$query = "SELECT * FROM users WHERE role != 'admin'";
if (!empty($search)) {
    $searchEsc = $conn->real_escape_string($search);
    $query .= " AND (username LIKE '%$searchEsc%' OR email LIKE '%$searchEsc%')";
}
if (!empty($filter)) {
    $filterEsc = $conn->real_escape_string($filter);
    $query .= " AND status='$filterEsc'";
}
$query .= " ORDER BY id DESC";
$result = $conn->query($query);

/* ===== STATS ===== */
$totalUsers    = $conn->query("SELECT COUNT(*) c FROM users WHERE role!='admin'")->fetch_assoc()['c'];
$pendingCount  = $conn->query("SELECT COUNT(*) c FROM users WHERE status='pending'")->fetch_assoc()['c'];
$approvedCount = $conn->query("SELECT COUNT(*) c FROM users WHERE status='approved'")->fetch_assoc()['c'];
$bannedCount   = $conn->query("SELECT COUNT(*) c FROM users WHERE status='banned'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users</title>
<link rel="icon" type="image/png" href="favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --bg:       #0b0d10;
        --surface:  #111318;
        --surface2: #181c23;
        --border:   rgba(255,255,255,0.07);
        --text:     #e8eaf0;
        --muted:    #6b7280;
        --accent:   #f97316;
        --blue:     #3b82f6;
        --green:    #10b981;
        --yellow:   #f59e0b;
        --red:      #ef4444;
        --radius:   14px;
    }

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

    /* ── TOPBAR ── */
    .topbar {
        position: sticky; top: 0; z-index: 100;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 32px; height: 64px;
        background: rgba(11,13,16,0.85);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border);
    }
    .brand {
        font-family: 'Syne', sans-serif;
        font-weight: 800; font-size: 20px; letter-spacing: -0.5px;
        background: linear-gradient(135deg, var(--accent), #fbbf24);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .nav-links { display: flex; align-items: center; gap: 4px; }
    .nav-links a {
        text-decoration: none; color: var(--muted);
        font-size: 14px; font-weight: 500;
        padding: 7px 14px; border-radius: 8px;
        transition: color 0.2s, background 0.2s;
    }
    .nav-links a:hover { color: var(--text); background: var(--surface2); }
    .nav-links a.active { color: var(--text); }
    .logout-btn {
        background: var(--accent) !important;
        color: #fff !important; border-radius: 8px !important;
        padding: 7px 18px !important; font-weight: 600 !important;
    }
    .logout-btn:hover { opacity: 0.85; }
    .hamburger {
        display: none; cursor: pointer; font-size: 22px;
        color: var(--text); background: none; border: none; padding: 4px 8px;
    }

    /* ── PAGE ── */
    .page {
        position: relative; z-index: 1;
        max-width: 1200px; margin: 0 auto;
        padding: 48px 24px 80px;
    }

    .page-header {
        margin-bottom: 36px;
        animation: fadeUp 0.4s ease both;
    }
    .page-header .eyebrow {
        font-size: 11px; font-weight: 700; letter-spacing: 2.5px;
        text-transform: uppercase; color: var(--accent); margin-bottom: 6px;
    }
    .page-header h1 {
        font-family: 'Syne', sans-serif;
        font-size: clamp(24px, 3.5vw, 36px);
        font-weight: 800; letter-spacing: -1px;
    }
    .page-header p { color: var(--muted); font-size: 14px; margin-top: 4px; }

    /* ── MINI STATS ── */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px; margin-bottom: 28px;
    }
    .mini-stat {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 20px;
        position: relative; overflow: hidden;
        animation: fadeUp 0.4s ease both;
        transition: border-color 0.3s;
    }
    .mini-stat:hover { border-color: rgba(255,255,255,0.14); }
    .mini-stat:nth-child(1) { animation-delay: 0.05s; }
    .mini-stat:nth-child(2) { animation-delay: 0.10s; }
    .mini-stat:nth-child(3) { animation-delay: 0.15s; }
    .mini-stat:nth-child(4) { animation-delay: 0.20s; }

    .mini-stat::before {
        content: ''; position: absolute;
        left: 0; top: 0; bottom: 0; width: 3px; border-radius: 3px 0 0 3px;
    }
    .mini-stat.total::before  { background: var(--accent); }
    .mini-stat.pending::before { background: var(--yellow); }
    .mini-stat.approved::before { background: var(--green); }
    .mini-stat.banned::before  { background: var(--red); }

    .mini-stat span {
        font-size: 10px; font-weight: 700;
        letter-spacing: 2px; text-transform: uppercase; color: var(--muted);
    }
    .mini-stat h3 {
        font-family: 'Syne', sans-serif;
        font-size: 34px; font-weight: 800; letter-spacing: -1.5px;
        line-height: 1; margin-top: 6px;
    }
    .mini-stat.total h3   { color: var(--accent); }
    .mini-stat.pending h3  { color: var(--yellow); }
    .mini-stat.approved h3 { color: var(--green); }
    .mini-stat.banned h3   { color: var(--red); }

    /* ── CONTROLS ── */
    .controls-bar {
        display: flex; gap: 12px; align-items: center;
        margin-bottom: 20px;
        animation: fadeUp 0.4s ease 0.15s both;
    }
    .search-wrap {
        position: relative; flex: 1; max-width: 320px;
    }
    .search-wrap svg {
        position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
        color: var(--muted); pointer-events: none;
    }
    .controls-bar input[type="text"] {
        width: 100%;
        padding: 10px 14px 10px 38px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 9px;
        color: var(--text); font-size: 14px;
        font-family: 'DM Sans', sans-serif;
        transition: border-color 0.2s;
        outline: none;
    }
    .controls-bar input[type="text"]:focus { border-color: var(--accent); }
    .controls-bar input::placeholder { color: var(--muted); }

    .controls-bar select {
        padding: 10px 36px 10px 14px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 9px;
        color: var(--text); font-size: 14px;
        font-family: 'DM Sans', sans-serif;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        cursor: pointer; outline: none;
        transition: border-color 0.2s;
    }
    .controls-bar select:focus { border-color: var(--accent); }

    .search-btn {
        padding: 10px 20px;
        background: var(--accent); color: #fff;
        border: none; border-radius: 9px;
        font-size: 14px; font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .search-btn:hover { opacity: 0.85; }

    /* ── TABLE ── */
    .table-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        animation: fadeUp 0.4s ease 0.2s both;
    }
    .admin-table {
        width: 100%; border-collapse: collapse;
    }
    .admin-table th {
        font-size: 10px; font-weight: 700;
        letter-spacing: 1.8px; text-transform: uppercase;
        color: var(--muted); padding: 13px 20px;
        text-align: left;
        background: var(--surface2);
        border-bottom: 1px solid var(--border);
    }
    .admin-table td {
        padding: 14px 20px; font-size: 13.5px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        transition: background 0.15s;
    }
    .admin-table tr:last-child td { border-bottom: none; }
    .admin-table tbody tr:hover td { background: var(--surface2); }

    .user-cell { display: flex; align-items: center; gap: 10px; }
    .avatar {
        width: 32px; height: 32px;
        background: linear-gradient(135deg, var(--accent), #fbbf24);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .user-name { font-weight: 500; }
    .user-email { font-size: 12px; color: var(--muted); }

    .role-badge {
        display: inline-block;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 600; letter-spacing: 0.3px;
    }
    .role-athlete   { background: rgba(59,130,246,0.15); color: #60a5fa; }
    .role-organizer { background: rgba(139,92,246,0.15); color: #a78bfa; }

    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 600;
    }
    .status-badge::before {
        content: ''; width: 6px; height: 6px; border-radius: 50%;
    }
    .status-approved { background: rgba(16,185,129,0.12); color: #34d399; }
    .status-approved::before { background: #34d399; }
    .status-pending  { background: rgba(245,158,11,0.12); color: #fbbf24; }
    .status-pending::before  { background: #fbbf24; }
    .status-banned   { background: rgba(239,68,68,0.12);  color: #f87171; }
    .status-banned::before   { background: #f87171; }
    .status-suspended { background: rgba(107,114,128,0.15); color: #9ca3af; }
    .status-suspended::before { background: #9ca3af; }

    /* ── DROPDOWN ── */
    .action-dropdown { position: relative; display: inline-block; }
    .action-btn {
        padding: 7px 14px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text); font-size: 13px; font-weight: 500;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer; transition: border-color 0.2s;
    }
    .action-btn:hover { border-color: rgba(255,255,255,0.18); }
    .dropdown-menu {
        display: none;
        position: absolute; right: 0; top: calc(100% + 6px);
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        min-width: 150px;
        box-shadow: 0 12px 32px rgba(0,0,0,0.5);
        z-index: 50; overflow: hidden;
    }
    .action-dropdown:hover .dropdown-menu { display: block; }
    .dropdown-menu button,
    .dropdown-menu a {
        display: block; width: 100%;
        padding: 10px 16px;
        background: none; border: none;
        color: var(--text); font-size: 13px;
        font-family: 'DM Sans', sans-serif;
        text-align: left; text-decoration: none;
        cursor: pointer; transition: background 0.15s;
    }
    .dropdown-menu button:hover,
    .dropdown-menu a:hover { background: rgba(255,255,255,0.06); }
    .dropdown-menu a.approve { color: var(--green); }
    .dropdown-menu a.decline { color: var(--red); }

    /* ── MODALS ── */
    .modal {
        display: none; position: fixed; inset: 0; z-index: 999;
        background: rgba(0,0,0,0.65); backdrop-filter: blur(6px);
        align-items: center; justify-content: center;
    }
    .modal.open { display: flex; }

    .modal-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 0;
        width: 90%; max-width: 460px;
        overflow: hidden;
        animation: scaleIn 0.22s ease;
    }
    .modal-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
    }
    .modal-head h3 {
        font-family: 'Syne', sans-serif;
        font-size: 17px; font-weight: 700;
    }
    .close-btn {
        background: none; border: none; color: var(--muted);
        font-size: 18px; cursor: pointer; line-height: 1;
        transition: color 0.2s;
    }
    .close-btn:hover { color: var(--text); }

    .modal-body { padding: 24px; }
    .form-group { margin-bottom: 16px; }
    .form-group label {
        display: block; font-size: 11px; font-weight: 700;
        letter-spacing: 1.5px; text-transform: uppercase;
        color: var(--muted); margin-bottom: 6px;
    }
    .form-group input,
    .form-group select {
        width: 100%; padding: 10px 14px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text); font-size: 14px;
        font-family: 'DM Sans', sans-serif;
        outline: none; transition: border-color 0.2s;
    }
    .form-group input:focus,
    .form-group select:focus { border-color: var(--accent); }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .modal-foot {
        display: flex; gap: 10px; justify-content: flex-end;
        padding: 16px 24px;
        border-top: 1px solid var(--border);
    }
    .btn {
        padding: 10px 22px; border-radius: 9px;
        font-size: 14px; font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer; border: none;
        transition: opacity 0.2s;
    }
    .btn:hover { opacity: 0.85; }
    .btn-ghost  { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
    .btn-save   { background: var(--accent); color: #fff; }
    .btn-danger { background: var(--red); color: #fff; }

    /* logout modal */
    .modal-simple {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 18px; padding: 36px 32px 28px;
        max-width: 340px; width: 90%; text-align: center;
        animation: scaleIn 0.22s ease;
    }
    .modal-simple h3 { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; margin-bottom: 8px; }
    .modal-simple p  { color: var(--muted); font-size: 14px; margin-bottom: 24px; }

    /* ── ANIMATIONS ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.93); }
        to   { opacity: 1; transform: scale(1); }
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 680px) {
        .stats-row { grid-template-columns: 1fr 1fr; }
        .nav-links { display: none; }
        .hamburger { display: block; }
        .nav-links.open {
            display: flex; flex-direction: column;
            position: absolute; top: 64px; left: 0; right: 0;
            background: var(--surface); border-bottom: 1px solid var(--border);
            padding: 12px 0; gap: 0;
        }
        .nav-links.open a { border-radius: 0; padding: 12px 24px; }
        .admin-table th:nth-child(1),
        .admin-table td:nth-child(1) { display: none; }
        .form-row { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>

<!-- ── TOPBAR ── -->
<nav class="topbar">
    <span class="brand">Admin</span>
    <div class="nav-links" id="navMenu">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="view_users.php" class="active">Users</a>
        <a href="#" class="logout-btn" onclick="openLogoutModal(); return false;">Log Out</a>
    </div>
    <button class="hamburger" onclick="toggleMenu()">☰</button>
</nav>

<!-- ── PAGE ── -->
<main class="page">

    <div class="page-header">
        <div class="eyebrow">Admin Panel</div>
        <h1>User Management</h1>
        <p>Manage registrations, approvals and permissions</p>
    </div>

    <!-- STATS -->
    <div class="stats-row">
        <div class="mini-stat total">
            <span>Total Users</span>
            <h3><?= $totalUsers ?></h3>
        </div>
        <div class="mini-stat pending">
            <span>Pending</span>
            <h3><?= $pendingCount ?></h3>
        </div>
        <div class="mini-stat approved">
            <span>Approved</span>
            <h3><?= $approvedCount ?></h3>
        </div>
        <div class="mini-stat banned">
            <span>Banned</span>
            <h3><?= $bannedCount ?></h3>
        </div>
    </div>

    <!-- CONTROLS -->
    <form method="GET">
        <div class="controls-bar">
            <div class="search-wrap">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" placeholder="Search username or email…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="filter" onchange="this.form.submit()">
                <option value="">All Users</option>
                <option value="pending"   <?= $filter=="pending"   ?"selected":"" ?>>Pending</option>
                <option value="approved"  <?= $filter=="approved"  ?"selected":"" ?>>Approved</option>
                <option value="suspended" <?= $filter=="suspended" ?"selected":"" ?>>Suspended</option>
                <option value="banned"    <?= $filter=="banned"    ?"selected":"" ?>>Banned</option>
            </select>
            <button type="submit" class="search-btn">Search</button>
        </div>
    </form>

    <!-- TABLE -->
    <div class="table-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td style="color:var(--muted);font-size:12px">#<?= $row['id'] ?></td>
                <td>
                    <div class="user-cell">
                        <div class="avatar"><?= strtoupper(substr($row['username'],0,1)) ?></div>
                        <div>
                            <div class="user-name"><?= htmlspecialchars($row['username']) ?></div>
                            <div class="user-email"><?= htmlspecialchars($row['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="role-badge role-<?= $row['role'] ?>">
                        <?= ucfirst(htmlspecialchars($row['role'])) ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge status-<?= $row['status'] ?>">
                        <?= ucfirst(htmlspecialchars($row['status'])) ?>
                    </span>
                </td>
                <td>
                    <div class="action-dropdown">
                        <button class="action-btn">Actions ▾</button>
                        <div class="dropdown-menu">
                            <button onclick="openEditModal(
                                '<?= $row['id'] ?>',
                                '<?= htmlspecialchars($row['username']) ?>',
                                '<?= htmlspecialchars($row['email']) ?>',
                                '<?= $row['role'] ?>',
                                '<?= $row['status'] ?>'
                            )">Edit User</button>
                            <?php if($row['status']=="pending"): ?>
                                <a class="approve" href="update_user_status.php?id=<?= $row['id'] ?>&status=approved">Approve</a>
                                <a class="decline" href="update_user_status.php?id=<?= $row['id'] ?>&status=banned">Decline</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</main>

<!-- ── EDIT MODAL ── -->
<div id="editModal" class="modal">
    <div class="modal-card">
        <div class="modal-head">
            <h3>Edit User</h3>
            <button class="close-btn" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit_id">
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="edit_name">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="edit_email">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Role</label>
                    <select id="edit_role">
                        <option value="athlete">Athlete</option>
                        <option value="organizer">Organizer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="edit_status">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="banned">Banned</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
            <button class="btn btn-save"  onclick="saveUser()">Save Changes</button>
        </div>
    </div>
</div>

<!-- ── LOGOUT MODAL ── -->
<div id="logoutModal" class="modal">
    <div class="modal-simple">
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to end your admin session?</p>
        <div style="display:flex;gap:10px;justify-content:center">
            <button class="btn btn-ghost"   onclick="closeLogoutModal()">Cancel</button>
            <button class="btn btn-danger"  onclick="doLogout()">Yes, Logout</button>
        </div>
    </div>
</div>

<script>
function toggleMenu() {
    document.getElementById('navMenu').classList.toggle('open');
}

/* edit modal */
function openEditModal(id, name, email, role, status) {
    document.getElementById('edit_id').value     = id;
    document.getElementById('edit_name').value   = name;
    document.getElementById('edit_email').value  = email;
    document.getElementById('edit_role').value   = role;
    document.getElementById('edit_status').value = status;
    document.getElementById('editModal').classList.add('open');
}
function closeModal() {
    document.getElementById('editModal').classList.remove('open');
}
function saveUser() {
    fetch('update_user_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:
            'id='       + document.getElementById('edit_id').value +
            '&username='+ document.getElementById('edit_name').value +
            '&email='   + document.getElementById('edit_email').value +
            '&role='    + document.getElementById('edit_role').value +
            '&status='  + document.getElementById('edit_status').value
    }).then(() => location.reload());
}

/* logout modal */
function openLogoutModal()  { document.getElementById('logoutModal').classList.add('open'); }
function closeLogoutModal() { document.getElementById('logoutModal').classList.remove('open'); }
function doLogout()         { window.location.href = 'logout.php'; }

window.addEventListener('click', e => {
    const edit    = document.getElementById('editModal');
    const logout  = document.getElementById('logoutModal');
    if (e.target === edit)   closeModal();
    if (e.target === logout) closeLogoutModal();
});
</script>
</body>
</html>