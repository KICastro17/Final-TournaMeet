<?php
session_start();
include("../config.php");

/* ================= AUTH GUARD ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= FETCH USER ================= */
$stmt = $conn->prepare("SELECT id, username, email, role, bio, profile_pic, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) { 
    session_destroy(); 
    header("Location: ../login.php"); 
    exit(); 
}

/* ================= MY TOURNAMENTS ================= */
$stmt = $conn->prepare("
    SELECT id, name, sport, date, time, location, prize, description
    FROM tournaments
    WHERE created_by = ?
    ORDER BY date DESC LIMIT 15
");
$stmt->bind_param("s", $user['username']);
$stmt->execute();
$my_tournaments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ================= UPCOMING TOURNAMENTS (NEXT 30 DAYS) ================= */
$stmt = $conn->prepare("
    SELECT id, name, sport, date, time, location, prize
    FROM tournaments
    WHERE created_by = ? AND date >= CURDATE() AND date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY date ASC LIMIT 5
");
$stmt->bind_param("s", $user['username']);
$stmt->execute();
$upcoming = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ================= STATS ================= */
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM tournaments WHERE created_by = ?");
$stmt->bind_param("s", $user['username']);
$stmt->execute();
$total_created = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("
    SELECT COUNT(r.id) AS total_registrations
    FROM registrations r
    JOIN tournaments t ON t.id = r.tournament_id
    WHERE t.created_by = ?
");
$stmt->bind_param("s", $user['username']);
$stmt->execute();
$total_registrations = $stmt->get_result()->fetch_assoc()['total_registrations'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS active FROM tournaments WHERE created_by = ? AND date >= CURDATE()");
$stmt->bind_param("s", $user['username']);
$stmt->execute();
$active_tournaments = $stmt->get_result()->fetch_assoc()['active'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS completed FROM tournaments WHERE created_by = ? AND date < CURDATE()");
$stmt->bind_param("s", $user['username']);
$stmt->execute();
$completed_tournaments = $stmt->get_result()->fetch_assoc()['completed'];
$stmt->close();

$conn->close();

/* ================= HELPERS ================= */
$initials     = substr(strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', trim($user['username']))))), 0, 2);
$pic          = (!empty($user['profile_pic']) && file_exists('../uploads' . $user['profile_pic'])) ? '../uploads' . $user['profile_pic'] : null;
$member_since = date('Y', strtotime($user['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../favicon.png">
  <title>Organizer Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="organizer_profile.css">
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-left">
    <a href="organizer_index.php" class="nav-home">
      <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 2L2 9h2v9h5v-5h2v5h5V9h2L10 2z"/>
      </svg>
      HOME
    </a>
  </div>
  <div class="nav-center">
    <div class="search-bar">
      <input type="text" placeholder="Search tournaments...">
      <button type="button"><svg width="13" height="13" fill="white" viewBox="0 0 20 20"><path d="M12.9 14.32a8 8 0 111.42-1.42l4.38 4.39-1.42 1.41-4.38-4.38zM8 14A6 6 0 108 2a6 6 0 000 12z"/></svg></button>
    </div>
  </div>
  <div class="nav-right">
    <span class="nav-user">👤 <?= htmlspecialchars($user['username']) ?></span>
    <button class="btn-logout" onclick="window.location.href='../logout.php'">LOGOUT</button>
  </div>
</nav>

<main>
  <!-- PAGE HEADER -->
  <div class="page-header">
    <h1>🎯 Organizer Dashboard</h1>
    <a href="organizer_index.php" class="btn-primary">+ CREATE TOURNAMENT</a>
  </div>

  <!-- PROFILE HEADER -->
  <div class="profile-header">
    <div class="profile-banner-badge">🎯 ORGANIZER</div>
    <div class="profile-body">
      <div class="avatar-wrap">
        <?php if ($pic): ?>
          <img src="<?= htmlspecialchars($pic) ?>" alt="Profile" class="avatar-img">
        <?php else: ?>
          <div class="avatar"><?= htmlspecialchars($initials) ?></div>
        <?php endif; ?>
      </div>
      <div class="profile-info">
        <div class="profile-name"><?= htmlspecialchars($user['username']) ?></div>
        <div class="profile-role">ORGANIZER · <?= htmlspecialchars($user['email']) ?></div>
        <div class="profile-meta">
          <span class="meta-item">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            Member since <?= $member_since ?>
          </span>
        </div>
      </div>
      <div class="profile-actions">
        <a href="organizer_edit_profile.php" class="btn-secondary">✏ EDIT PROFILE</a>
      </div>
    </div>
  </div>

  <!-- STATS -->
  <div class="stats-row" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
      <div class="stat-num"><?= $total_created ?></div>
      <div class="stat-label">Tournaments Created</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $active_tournaments ?></div>
      <div class="stat-label">Upcoming Events</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= intval($total_registrations) ?></div>
      <div class="stat-label">Total Registrations</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $completed_tournaments ?></div>
      <div class="stat-label">Completed Tournaments</div>
    </div>
  </div>

  <!-- BIO -->
  <?php if (!empty($user['bio'])): ?>
  <div class="card" style="margin-bottom: 32px;">
    <div class="card-header"><div class="card-title">About Me</div></div>
    <div class="card-body">
      <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
    </div>
  </div>
  <?php endif; ?>

  <div class="two-col">
    <div>

      <!-- MY TOURNAMENTS -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">My Tournaments</div>
        </div>
        <div class="card-body">
          <?php if (empty($my_tournaments)): ?>
            <p class="empty-msg">You haven't created any tournaments yet.</p>
          <?php else: foreach ($my_tournaments as $t):
            $dateStr = $t['date'] ? date('M j, Y', strtotime($t['date'])) : '—';
            $isPast  = $t['date'] && strtotime($t['date']) < time();
          ?>
          <div class="tournament-item">
            <div class="t-icon">⭐</div>
            <div class="t-info">
              <div class="t-name"><?= htmlspecialchars($t['name']) ?></div>
              <div class="t-date"><?= htmlspecialchars($t['sport']) ?> · <?= $dateStr ?> · <?= htmlspecialchars($t['location']) ?></div>
            </div>
            <span class="t-badge <?= $isPast ? 'badge-active' : 'badge-upcoming' ?>">
              <?= $isPast ? 'COMPLETED' : 'UPCOMING' ?>
            </span>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- UPCOMING TOURNAMENTS -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Next 30 Days</div>
        </div>
        <div class="card-body">
          <?php if (empty($upcoming)): ?>
            <p class="empty-msg">No tournaments scheduled in the next 30 days.</p>
          <?php else: foreach ($upcoming as $u): ?>
          <div class="tournament-item">
            <div class="t-icon">📅</div>
            <div class="t-info">
              <div class="t-name"><?= htmlspecialchars($u['name']) ?></div>
              <div class="t-date">
                <?= htmlspecialchars($u['sport']) ?> · <?= date('M j, Y', strtotime($u['date'])) ?>
                <?= $u['time'] ? ' · ' . htmlspecialchars($u['time']) : '' ?> · <?= htmlspecialchars($u['location']) ?>
              </div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

    </div>

    <!-- SIDEBAR -->
    <div>
      <!-- ACCOUNT DETAILS -->
      <div class="card">
        <div class="card-header"><div class="card-title">Account Details</div></div>
        <div class="card-body">
          <div class="info-row">
            <span class="info-label">Username</span>
            <span class="info-val"><?= htmlspecialchars($user['username']) ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-val"><?= htmlspecialchars($user['email']) ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Role</span>
            <span class="info-val"><?= ucfirst($user['role']) ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Joined</span>
            <span class="info-val"><?= date('F j, Y', strtotime($user['created_at'])) ?></span>
          </div>
        </div>
      </div>

      <!-- QUICK STATS -->
      <div class="card">
        <div class="card-header"><div class="card-title">Quick Stats</div></div>
        <div class="card-body">
          <div class="info-row">
            <span class="info-label">Avg Registrations</span>
            <span class="info-val"><?= $total_created > 0 ? number_format($total_registrations / $total_created, 1) : '0' ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Success Rate</span>
            <span class="info-val">
              <?php 
                echo $total_created > 0 ? number_format(($completed_tournaments / $total_created) * 100, 0) . '%' : '0%';
              ?>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">Active Events</span>
            <span class="info-val"><?= $active_tournaments ?></span>
          </div>
        </div>
      </div>

      <!-- PROFILE PIC -->
      <?php if ($pic): ?>
      <div class="card">
        <div class="card-header"><div class="card-title">Profile Photo</div></div>
        <div class="card-body text-center">
          <img src="<?= htmlspecialchars($pic) ?>" alt="Profile" style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary);">
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

</main>

</body>
</html>