<?php
session_start();
// Only organizers and admins can access this page
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'] ?? '', ['organizer', 'admin'])) {
    header('Location: login.php');
    exit;
}

include "config.php";
require_once "organizer_helpers.php";
ensureOrganizerSchema($conn);

$role     = $_SESSION['role'];
$username = $_SESSION['username'];

// Quick stats for the welcome bar
$isAdmin = $role === 'admin';
if ($isAdmin) {
    $totalRes = $conn->query("SELECT COUNT(*) AS c FROM tournaments");
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM tournaments WHERE created_by = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $totalRes = $stmt->get_result();
}
$totalTournaments = ($totalRes && ($row = $totalRes->fetch_assoc())) ? intval($row['c']) : 0;

if ($isAdmin) {
    $activeRes = $conn->query("SELECT COUNT(*) AS c FROM tournaments WHERE is_closed = 0");
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM tournaments WHERE created_by = ? AND is_closed = 0");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $activeRes = $stmt->get_result();
}
$activeTournaments = ($activeRes && ($row = $activeRes->fetch_assoc())) ? intval($row['c']) : 0;

// Sport categories – same list as athlete dashboard, but cards link to create_tournament.php
$sports = [
    ['name' => 'Ball Sports',   'icon' => 'fa-futbol',          'sub' => 'Basketball · Football · Volleyball'],
    ['name' => 'Racket Sports', 'icon' => 'fa-table-tennis',    'sub' => 'Badminton · Tennis · Squash'],
    ['name' => 'Combatives',    'icon' => 'fa-hand-rock',       'sub' => 'Boxing · MMA · Karate'],
    ['name' => 'Endurance',     'icon' => 'fa-running',         'sub' => 'Running · Cycling · Triathlon'],
    ['name' => 'Precision',     'icon' => 'fa-crosshairs',      'sub' => 'Archery · Shooting · Darts'],
    ['name' => 'E-Sports',      'icon' => 'fa-gamepad',         'sub' => 'FPS · MOBA · Fighting'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TOURNAMEET – Organizer Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --orange:      #e97817;
    --orange-dark: #c85c00;
    --cream:       #fffaf4;
    --ink:         #2b1c11;
    --line:        #e6d8c6;
}
* { box-sizing: border-box; }
body {
    font-family: "Manrope", "Segoe UI", sans-serif;
    background: var(--cream);
    margin: 0;
    color: var(--ink);
}

/* ── Top bar ── */
.topbar {
    background: linear-gradient(180deg, #ec8a2d 0%, #de7316 100%);
    border-bottom: 1px solid rgba(0,0,0,0.08);
    padding: 10px 24px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    position: sticky;
    top: 0;
    z-index: 100;
}
.brand {
    font-family: "Bebas Neue", sans-serif;
    font-size: 28px;
    letter-spacing: 1.5px;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}
.top-search {
    flex: 1;
    max-width: 460px;
    position: relative;
}
.top-search input {
    width: 100%;
    border: 1px solid rgba(255,255,255,0.5);
    border-radius: 999px;
    padding: 9px 38px 9px 16px;
    background: rgba(255,255,255,0.18);
    color: #fff;
    font: inherit;
    font-size: 14px;
}
.top-search input::placeholder { color: rgba(255,255,255,0.7); }
.top-search input:focus { outline: none; background: rgba(255,255,255,0.28); }
.top-search i {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.8);
    font-size: 13px;
}
.top-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
}
.top-actions a {
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 13px;
}
.top-actions a:hover { text-decoration: underline; }
.role-pill {
    padding: 4px 11px;
    border-radius: 999px;
    background: rgba(255,255,255,0.22);
    border: 1px solid rgba(255,255,255,0.4);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* ── Main container ── */
.container {
    max-width: 1160px;
    margin: 0 auto;
    padding: 36px 24px 60px;
}

/* ── KPI strip ── */
.kpi-strip {
    display: grid;
    grid-template-columns: repeat(4, minmax(0,1fr));
    gap: 12px;
    margin-bottom: 32px;
}
.kpi-card {
    background: #fff;
    border: 1px solid #ecd5bf;
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.kpi-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: linear-gradient(135deg, #ff8c00, #e97817);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    flex-shrink: 0;
}
.kpi-info .k { font-size: 11px; color: #9a7060; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
.kpi-info .v { font-size: 24px; font-weight: 800; color: var(--ink); line-height: 1.1; }

/* ── Section header ── */
.section-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 20px;
    gap: 14px;
}
.section-head h2 {
    margin: 0;
    font-family: "Bebas Neue", sans-serif;
    font-size: 48px;
    letter-spacing: 1.5px;
    color: #be5b0a;
    line-height: 0.95;
}
.section-head p {
    margin: 6px 0 0;
    color: #9a7060;
    font-size: 14px;
}
.quick-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}
.qa-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 14px;
    border-radius: 9px;
    border: 1.5px solid var(--line);
    background: #fff;
    color: #7a400d;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.18s;
}
.qa-btn:hover { background: #fff4e6; border-color: #f5c88a; }
.qa-btn.primary {
    background: linear-gradient(120deg, #ff8c00, #e97817);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(233,120,23,0.3);
}
.qa-btn.primary:hover { opacity: 0.92; }

/* ── Category grid ── */
.category-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 20px;
}

/* ── Category card ── */
.cat-card {
    background: #fff;
    border: 1.5px solid #ecd5bf;
    border-radius: 16px;
    padding: 32px 20px 26px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    text-decoration: none;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.cat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #f5a442, #e97817);
    opacity: 0;
    transition: opacity 0.2s;
}
.cat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px rgba(190,91,10,0.16);
    border-color: #f5a442;
}
.cat-card:hover::before { opacity: 1; }

.cat-icon-wrap {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #fff4e6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 18px;
    transition: background 0.2s;
}
.cat-card:hover .cat-icon-wrap { background: #ffe8cc; }
.cat-icon-wrap i {
    font-size: 34px;
    color: #e97817;
}
.cat-name {
    font-family: "Bebas Neue", sans-serif;
    font-size: 20px;
    letter-spacing: 1.5px;
    color: #2b1c11;
    margin-bottom: 6px;
}
.cat-sub {
    font-size: 12px;
    color: #b08060;
    line-height: 1.5;
}
.cat-cta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 16px;
    padding: 7px 16px;
    border-radius: 999px;
    background: linear-gradient(120deg, #ff8c00, #e97817);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    opacity: 0;
    transform: translateY(4px);
    transition: opacity 0.2s, transform 0.2s;
}
.cat-card:hover .cat-cta {
    opacity: 1;
    transform: none;
}

/* ── Success flash ── */
.flash {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #eaf7ef;
    border: 1px solid #88d4a8;
    color: #1a6b3e;
    padding: 11px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 24px;
}

@media (max-width: 900px) {
    .kpi-strip       { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .category-grid   { grid-template-columns: repeat(2, minmax(0,1fr)); }
}
@media (max-width: 620px) {
    .topbar          { flex-wrap: wrap; }
    .top-search      { order: 3; max-width: none; width: 100%; }
    .category-grid   { grid-template-columns: 1fr; }
    .kpi-strip       { grid-template-columns: 1fr 1fr; }
    .section-head h2 { font-size: 36px; }
}
</style>
</head>
<body>

<!-- ── Top bar ────────────────────────────────────────────── -->
<nav class="topbar">
    <div class="brand">
        <i class="fas fa-trophy"></i> TOURNAMEET
    </div>
    <div class="top-search">
        <input type="text" placeholder="Search tournaments, sports..." id="catSearch">
        <i class="fas fa-search"></i>
    </div>
    <div class="top-actions">
        <span class="role-pill"><?php echo htmlspecialchars(strtoupper($role)); ?></span>
        <a href="my_tournaments.php"><i class="fas fa-list-alt"></i> My Tournaments</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<!-- ── Main ───────────────────────────────────────────────── -->
<div class="container">

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'created'): ?>
    <div class="flash"><i class="fas fa-check-circle"></i> Tournament published successfully! It's now visible to athletes.</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div class="flash"><i class="fas fa-check-circle"></i> Tournament updated successfully.</div>
    <?php endif; ?>

    <!-- KPI strip -->
    <div class="kpi-strip">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-trophy"></i></div>
            <div class="kpi-info">
                <div class="k">My Tournaments</div>
                <div class="v"><?php echo $totalTournaments; ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-bolt"></i></div>
            <div class="kpi-info">
                <div class="k">Active</div>
                <div class="v"><?php echo $activeTournaments; ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
            <div class="kpi-info">
                <div class="k">Organizer</div>
                <div class="v" style="font-size:15px;padding-top:3px;"><?php echo htmlspecialchars($username); ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="kpi-info">
                <div class="k">Role</div>
                <div class="v" style="font-size:15px;padding-top:3px;"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
            </div>
        </div>
    </div>

    <!-- Section header -->
    <div class="section-head">
        <div>
            <h2>CHOOSE A CATEGORY</h2>
            <p>Select a sport category to create a new tournament</p>
        </div>
        <div class="quick-actions">
            <a href="create_tournament.php" class="qa-btn primary">
                <i class="fas fa-plus"></i> Create Tournament
            </a>
            <a href="my_tournaments.php" class="qa-btn">
                <i class="fas fa-list-alt"></i> Manage
            </a>
        </div>
    </div>

    <!-- Category grid -->
    <div class="category-grid" id="categoryGrid">
        <?php foreach ($sports as $sport): ?>
        <a href="create_tournament.php?sport=<?php echo urlencode($sport['name']); ?>"
           class="cat-card"
           data-name="<?php echo htmlspecialchars(strtolower($sport['name'])); ?>"
           data-sub="<?php echo htmlspecialchars(strtolower($sport['sub'])); ?>">
            <div class="cat-icon-wrap">
                <i class="fas <?php echo htmlspecialchars($sport['icon']); ?>"></i>
            </div>
            <div class="cat-name"><?php echo htmlspecialchars($sport['name']); ?></div>
            <div class="cat-sub"><?php echo htmlspecialchars($sport['sub']); ?></div>
            <span class="cat-cta"><i class="fas fa-plus"></i> Create Tournament</span>
        </a>
        <?php endforeach; ?>
    </div>

</div><!-- /container -->

<script>
// Live search filter for category cards
const searchInput = document.getElementById('catSearch');
const cards       = document.querySelectorAll('.cat-card');

searchInput.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    cards.forEach(function (card) {
        const name = card.dataset.name  || '';
        const sub  = card.dataset.sub   || '';
        card.style.display = (name.includes(q) || sub.includes(q)) ? '' : 'none';
    });
});
</script>

</body>
</html>