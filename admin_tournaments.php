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

$pendingCount = $conn->query("
    SELECT COUNT(*) as count FROM tournaments 
    WHERE status = 'pending' OR status IS NULL
")->fetch_assoc()['count'];

$result = $conn->query("
    SELECT id, name, sport, location, date, time,
           format, prize, entrance_fee, description,
           slots_total, slots_taken, created_by,
           registration_deadline,
           COALESCE(status, 'pending') AS approval_status
    FROM tournaments
    ORDER BY id DESC
");

$all = [];
while ($row = $result->fetch_assoc()) {
    $all[] = $row;
}

$pending  = array_filter($all, fn($t) => $t['approval_status'] === 'pending');
$approved = array_filter($all, fn($t) => $t['approval_status'] === 'approved');
$declined = array_filter($all, fn($t) => $t['approval_status'] === 'declined');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tournaments — Admin</title>
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
            --green:    #10b981;
            --red:      #ef4444;
            --blue:     #3b82f6;
            --radius:   14px;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            min-height: 100vh;
        }

        /* ── TOPBAR ── */
        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; height: 64px;
            background: rgba(11,13,16,0.95);
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 50;
        }
        .brand {
            font-family: 'Syne', sans-serif; font-weight: 800; font-size: 20px;
            background: linear-gradient(135deg, var(--accent), #fbbf24);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-links a {
            text-decoration: none; color: var(--muted); font-size: 14px;
            font-weight: 500; padding: 7px 14px; border-radius: 8px;
            transition: color 0.2s, background 0.2s;
        }
        .nav-links a:hover { color: var(--text); background: var(--surface2); }
        .nav-links a.active { color: var(--text); background: var(--surface2); }
        .logout-btn {
            background: var(--accent) !important; color: #fff !important;
            border-radius: 8px !important; padding: 7px 18px !important;
            font-weight: 600 !important;
        }
        .logout-btn:hover { opacity: 0.85; }

        /* ── PAGE ── */
        .page { max-width: 960px; margin: 0 auto; padding: 48px 24px 80px; }

        .page-header { margin-bottom: 32px; animation: fadeUp 0.4s ease both; }
        .eyebrow {
            font-size: 11px; font-weight: 600; letter-spacing: 2.5px;
            text-transform: uppercase; color: var(--accent); margin-bottom: 6px;
        }
        .page-header h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(26px, 4vw, 38px);
            font-weight: 800; letter-spacing: -1px;
        }

        /* ── TABS ── */
        .tabs {
            display: flex; gap: 4px; margin-bottom: 24px;
            border-bottom: 1px solid var(--border); padding-bottom: 0;
            animation: fadeUp 0.4s ease 0.05s both;
        }
        .tab {
            padding: 10px 18px; border-radius: 8px 8px 0 0;
            font-size: 14px; font-weight: 500; cursor: pointer;
            border: none; background: none; color: var(--muted);
            font-family: 'DM Sans', sans-serif;
            display: flex; align-items: center; gap: 8px;
            transition: color 0.2s, background 0.2s;
            position: relative; bottom: -1px;
            border: 1px solid transparent;
        }
        .tab:hover { color: var(--text); }
        .tab.active {
            color: var(--text);
            background: var(--surface);
            border-color: var(--border);
            border-bottom-color: var(--surface);
        }
        .tab-count {
            font-size: 11px; font-weight: 700;
            padding: 2px 7px; border-radius: 50px;
            background: rgba(255,255,255,0.07);
        }
        .tab.active.t-pending  .tab-count { background: rgba(249,115,22,0.2); color: var(--accent); }
        .tab.active.t-approved .tab-count { background: rgba(16,185,129,0.2); color: var(--green); }
        .tab.active.t-declined .tab-count { background: rgba(239,68,68,0.2);  color: var(--red); }

        /* ── PANELS ── */
        .panel { display: none; animation: fadeUp 0.3s ease both; }
        .panel.active { display: block; }

        /* ── TOURNAMENT CARD ── */
        .t-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px 24px;
            margin-bottom: 14px;
            transition: border-color 0.2s;
        }
        .t-card:hover { border-color: rgba(255,255,255,0.13); }

        .t-card-top {
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: 16px;
            margin-bottom: 14px;
        }
        .t-sport-pill {
            display: inline-block; font-size: 10px; font-weight: 700;
            letter-spacing: 1.5px; text-transform: uppercase;
            background: rgba(249,115,22,0.12); color: var(--accent);
            padding: 2px 9px; border-radius: 50px; margin-bottom: 7px;
        }
        .t-name {
            font-family: 'Syne', sans-serif; font-size: 17px;
            font-weight: 700; letter-spacing: -0.3px; margin-bottom: 3px;
        }
        .t-organizer { font-size: 12px; color: var(--muted); }
        .t-organizer strong { color: rgba(255,255,255,0.55); }

        .t-status {
            font-size: 11px; font-weight: 700; letter-spacing: 0.8px;
            text-transform: uppercase; padding: 4px 12px;
            border-radius: 50px; flex-shrink: 0; white-space: nowrap;
        }
        .t-status.pending  { background: rgba(249,115,22,0.12); color: var(--accent); }
        .t-status.approved { background: rgba(16,185,129,0.12); color: var(--green); }
        .t-status.declined { background: rgba(239,68,68,0.12);  color: var(--red); }

        .t-meta {
            display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;
        }
        .t-chip {
            display: flex; align-items: center; gap: 5px;
            font-size: 12px; color: var(--muted);
            background: var(--surface2);
            padding: 5px 11px; border-radius: 7px;
        }
        .t-chip svg { color: var(--accent); flex-shrink: 0; }

        .t-desc {
            font-size: 13px; color: var(--muted); line-height: 1.65;
            margin-bottom: 16px; padding: 12px 14px;
            background: rgba(255,255,255,0.025);
            border-left: 3px solid rgba(249,115,22,0.3);
            border-radius: 0 8px 8px 0;
        }

        .t-actions { display: flex; gap: 8px; justify-content: flex-end; }

        .btn-approve, .btn-decline {
            padding: 9px 22px; border-radius: 9px;
            font-size: 13px; font-weight: 600; cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            display: flex; align-items: center; gap: 6px;
            transition: all 0.2s;
        }
        .btn-approve {
            background: rgba(16,185,129,0.12); color: var(--green);
            border: 1px solid rgba(16,185,129,0.3);
        }
        .btn-approve:hover { background: var(--green); color: #fff; border-color: var(--green); }
        .btn-decline {
            background: rgba(239,68,68,0.1); color: var(--red);
            border: 1px solid rgba(239,68,68,0.25);
        }
        .btn-decline:hover { background: var(--red); color: #fff; border-color: var(--red); }

        /* ── EMPTY ── */
        .empty {
            text-align: center; padding: 72px 20px; color: var(--muted);
        }
        .empty-icon {
            font-size: 40px; margin-bottom: 16px; opacity: 0.5;
        }
        .empty h3 {
            font-family: 'Syne', sans-serif; font-size: 17px;
            font-weight: 700; color: rgba(255,255,255,0.15); margin-bottom: 6px;
        }
        .empty p { font-size: 13px; color: rgba(255,255,255,0.1); }

        /* ── TOAST ── */
        .toast {
            position: fixed; bottom: 32px; left: 50%;
            transform: translateX(-50%) translateY(16px);
            background: #1e2025; color: var(--text);
            padding: 12px 24px; border-radius: 50px;
            font-size: 13px; font-weight: 600;
            opacity: 0; pointer-events: none; z-index: 999;
            transition: opacity 0.3s, transform 0.3s;
            border: 1px solid var(--border);
        }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .toast.success { background: #0d2e1f; color: var(--green); border-color: rgba(16,185,129,0.3); }
        .toast.error   { background: #2e0d0d; color: var(--red);   border-color: rgba(239,68,68,0.3); }

        /* ── LOGOUT MODAL ── */
        .modal {
            display: none; position: fixed; inset: 0; z-index: 200;
            background: rgba(0,0,0,0.65); backdrop-filter: blur(6px);
            align-items: center; justify-content: center;
        }
        .modal.open { display: flex; }
        .modal-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 18px; padding: 36px 32px 28px;
            max-width: 360px; width: 90%; text-align: center;
            animation: scaleIn 0.2s ease;
        }
        .modal-icon { font-size: 28px; margin-bottom: 14px; }
        .modal-card h3 { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; margin-bottom: 8px; }
        .modal-card p { color: var(--muted); font-size: 14px; margin-bottom: 24px; line-height: 1.6; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .btn-ghost { padding: 10px 22px; border-radius: 9px; font-size: 14px; font-weight: 600; cursor: pointer; background: var(--surface2); color: var(--text); border: 1px solid var(--border); transition: opacity 0.2s; }
        .btn-danger { padding: 10px 22px; border-radius: 9px; font-size: 14px; font-weight: 600; cursor: pointer; background: var(--accent); color: #fff; border: none; transition: opacity 0.2s; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.94); }
            to   { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 600px) {
            .topbar { padding: 0 16px; }
            .page { padding: 28px 16px 60px; }
            .t-card-top { flex-direction: column; gap: 10px; }
            .t-actions { flex-direction: column; }
            .btn-approve, .btn-decline { justify-content: center; }
        }
    </style>
</head>
<body>

<nav class="topbar">
    <span class="brand">Admin</span>
    <div class="nav-links">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="view_users.php">Users</a>
        <a href="admin_tournaments.php" class="active">Tournaments</a>
        <a href="#" class="logout-btn" onclick="document.getElementById('logoutModal').classList.add('open'); return false;">Log Out</a>
    </div>
</nav>

<main class="page">

    <div class="page-header">
        <div class="eyebrow">Admin Panel</div>
        <h1>Tournament Approvals</h1>
    </div>

    <!-- TABS -->
    <div class="tabs">
        <button class="tab t-pending active" onclick="switchTab('pending')">
            Pending <span class="tab-count"><?php echo count($pending); ?></span>
        </button>
        <button class="tab t-approved" onclick="switchTab('approved')">
            Approved <span class="tab-count"><?php echo count($approved); ?></span>
        </button>
        <button class="tab t-declined" onclick="switchTab('declined')">
            Declined <span class="tab-count"><?php echo count($declined); ?></span>
        </button>
    </div>

    <!-- PENDING -->
    <div class="panel active" id="panel-pending">
        <?php if (empty($pending)): ?>
            <div class="empty">
                <div class="empty-icon">⏳</div>
                <h3>No Pending Tournaments</h3>
                <p>New tournaments from organizers will appear here.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pending as $t): ?>
                <?php
                    $date     = $t['date']                  ? date('M j, Y', strtotime($t['date'])) : '—';
                    $deadline = $t['registration_deadline'] ? date('M j, Y', strtotime($t['registration_deadline'])) : '—';
                    $fee      = $t['entrance_fee'] > 0      ? '₱' . number_format($t['entrance_fee'], 0) : 'Free';
                ?>
                <div class="t-card" id="card-<?php echo $t['id']; ?>">
                    <div class="t-card-top">
                        <div>
                            <div class="t-sport-pill"><?php echo htmlspecialchars($t['sport']); ?></div>
                            <div class="t-name"><?php echo htmlspecialchars($t['name']); ?></div>
                            <div class="t-organizer">by <strong><?php echo htmlspecialchars($t['created_by']); ?></strong></div>
                        </div>
                        <span class="t-status pending">Pending</span>
                    </div>

                    <div class="t-meta">
                        <div class="t-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php echo $date; ?>
                        </div>
                        <div class="t-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?php echo htmlspecialchars($t['location'] ?? '—'); ?>
                        </div>
                        <?php if ($t['time']): ?>
                        <div class="t-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <?php echo htmlspecialchars($t['time']); ?>
                        </div>
                        <?php endif; ?>
                        <div class="t-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                            <?php echo $fee; ?>
                        </div>
                        <?php if ($t['prize']): ?>
                        <div class="t-chip">🏆 <?php echo htmlspecialchars($t['prize']); ?></div>
                        <?php endif; ?>
                        <div class="t-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <?php echo intval($t['slots_total']); ?> slots · deadline <?php echo $deadline; ?>
                        </div>
                    </div>

                    <?php if ($t['description']): ?>
                    <div class="t-desc"><?php echo htmlspecialchars(substr($t['description'], 0, 200)) . (strlen($t['description']) > 200 ? '…' : ''); ?></div>
                    <?php endif; ?>

                    <div class="t-actions">
                        <button class="btn-decline" onclick="doAction(<?php echo $t['id']; ?>, 'declined')">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Decline
                        </button>
                        <button class="btn-approve" onclick="doAction(<?php echo $t['id']; ?>, 'approved')">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Approve
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- APPROVED -->
    <div class="panel" id="panel-approved">
        <?php if (empty($approved)): ?>
            <div class="empty">
                <div class="empty-icon">✅</div>
                <h3>No Approved Tournaments</h3>
                <p>Approved tournaments will appear here and on the athlete dashboard.</p>
            </div>
        <?php else: ?>
            <?php foreach ($approved as $t): ?>
                <?php
                    $date = $t['date'] ? date('M j, Y', strtotime($t['date'])) : '—';
                    $fee  = $t['entrance_fee'] > 0 ? '₱' . number_format($t['entrance_fee'], 0) : 'Free';
                ?>
                <div class="t-card" id="card-<?php echo $t['id']; ?>">
                    <div class="t-card-top">
                        <div>
                            <div class="t-sport-pill"><?php echo htmlspecialchars($t['sport']); ?></div>
                            <div class="t-name"><?php echo htmlspecialchars($t['name']); ?></div>
                            <div class="t-organizer">by <strong><?php echo htmlspecialchars($t['created_by']); ?></strong></div>
                        </div>
                        <span class="t-status approved">Approved</span>
                    </div>
                    <div class="t-meta">
                        <div class="t-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php echo $date; ?>
                        </div>
                        <div class="t-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?php echo htmlspecialchars($t['location'] ?? '—'); ?>
                        </div>
                        <div class="t-chip">🏆 <?php echo htmlspecialchars($t['prize'] ?? '—'); ?></div>
                        <div class="t-chip"><?php echo $fee; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- DECLINED -->
    <div class="panel" id="panel-declined">
        <?php if (empty($declined)): ?>
            <div class="empty">
                <div class="empty-icon">❌</div>
                <h3>No Declined Tournaments</h3>
                <p>Declined tournaments will appear here.</p>
            </div>
        <?php else: ?>
            <?php foreach ($declined as $t): ?>
                <?php
                    $date = $t['date'] ? date('M j, Y', strtotime($t['date'])) : '—';
                    $fee  = $t['entrance_fee'] > 0 ? '₱' . number_format($t['entrance_fee'], 0) : 'Free';
                ?>
                <div class="t-card" id="card-<?php echo $t['id']; ?>">
                    <div class="t-card-top">
                        <div>
                            <div class="t-sport-pill"><?php echo htmlspecialchars($t['sport']); ?></div>
                            <div class="t-name"><?php echo htmlspecialchars($t['name']); ?></div>
                            <div class="t-organizer">by <strong><?php echo htmlspecialchars($t['created_by']); ?></strong></div>
                        </div>
                        <span class="t-status declined">Declined</span>
                    </div>
                    <div class="t-meta">
                        <div class="t-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php echo $date; ?>
                        </div>
                        <div class="t-chip">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?php echo htmlspecialchars($t['location'] ?? '—'); ?>
                        </div>
                        <div class="t-chip"><?php echo $fee; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</main>

<!-- LOGOUT MODAL -->
<div id="logoutModal" class="modal">
    <div class="modal-card">
        <div class="modal-icon">🚪</div>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to end your admin session?</p>
        <div class="modal-actions">
            <button class="btn-ghost" onclick="document.getElementById('logoutModal').classList.remove('open')">Cancel</button>
            <button class="btn-danger" onclick="window.location.href='logout.php'">Yes, Logout</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
function switchTab(name) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.querySelector('.tab.t-' + name).classList.add('active');
    document.getElementById('panel-' + name).classList.add('active');
}

function doAction(id, action) {
    const label = action === 'approved' ? 'Approve' : 'Decline';
    if (!confirm(label + ' this tournament?')) return;

    fetch('admin_tournament_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(action === 'approved' ? '✅ Tournament approved' : '❌ Tournament declined', action === 'approved' ? 'success' : 'error');
            const card = document.getElementById('card-' + id);
            if (card) {
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity = '0';
                card.style.transform = 'translateX(30px)';
                setTimeout(() => location.reload(), 400);
            }
        } else {
            alert('Error: ' + (data.message || 'Something went wrong'));
        }
    })
    .catch(err => alert('Network error: ' + err.message));
}

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show ' + (type || '');
    setTimeout(() => t.className = 'toast', 3000);
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('logoutModal').classList.remove('open');
});
</script>
</body>
</html>