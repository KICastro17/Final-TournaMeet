<?php
session_start();

// ── DB ──
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'user_system');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB error: " . $e->getMessage());
}

$current_user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['userId'] ?? null;
$view_id         = (int)($_GET['id'] ?? 0);

if (!$view_id) { header('Location: teams_friends.php'); exit; }

// Redirect to own profile if viewing self
if ($current_user_id && $view_id === (int)$current_user_id) {
    header('Location: profile.php'); exit;
}

// ── Fetch the user being viewed ──
$stmt = $pdo->prepare("SELECT id,username,email,role,bio,profile_pic,created_at FROM users WHERE id=?");
$stmt->execute([$view_id]);
$user = $stmt->fetch();
if (!$user) { header('Location: teams_friends.php'); exit; }

$initials   = strtoupper(substr($user['username'],0,2));
$joinYear   = date('Y', strtotime($user['created_at']));
$roleLabel  = ucfirst($user['role']);
$profilePic = $user['profile_pic'] ? 'uploads/' . $user['profile_pic'] : null;

// ── Friendship status ──
$friendStatus  = 'none';
$friendship_id = null;
$hasFriendships = (bool)$pdo->query("SHOW TABLES LIKE 'friendships'")->rowCount();
if ($hasFriendships && $current_user_id) {
    $fs = $pdo->prepare("SELECT id, status, user_id FROM friendships WHERE (user_id=? AND friend_id=?) OR (user_id=? AND friend_id=?) LIMIT 1");
    $fs->execute([$current_user_id,$view_id,$view_id,$current_user_id]);
    $row = $fs->fetch();
    if ($row) {
        $friendStatus  = $row['status'];
        $friendship_id = $row['id'];
        // 'pending' where current user is receiver = incoming
        if ($row['status']==='pending' && (int)$row['user_id']===$view_id) $friendStatus = 'incoming';
        // 'pending' where current user is sender
        if ($row['status']==='pending' && (int)$row['user_id']===(int)$current_user_id) $friendStatus = 'pending';
    }
}

// ── Stats ──
$friendCount = 0;
if ($hasFriendships) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM friendships WHERE status='accepted' AND (user_id=? OR friend_id=?)");
    $stmt->execute([$view_id,$view_id]);
    $friendCount = (int)$stmt->fetchColumn();
}
$teamCount = 0;
if ((bool)$pdo->query("SHOW TABLES LIKE 'team_members'")->rowCount()) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM team_members WHERE user_id=?");
    $stmt->execute([$view_id]);
    $teamCount = (int)$stmt->fetchColumn();
}

// ── Posts (auto-create table if missing) ──
$pdo->exec("
    CREATE TABLE IF NOT EXISTS posts (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        user_id    INT NOT NULL,
        content    TEXT NOT NULL,
        image_url  VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
");
$pdo->exec("
    CREATE TABLE IF NOT EXISTS post_likes (
        id      INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        UNIQUE KEY ul (post_id, user_id),
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

$posts = $pdo->prepare("
    SELECT p.*,
           COUNT(DISTINCT pl.id) AS like_count,
           MAX(CASE WHEN pl.user_id=? THEN 1 ELSE 0 END) AS liked_by_me
    FROM posts p
    LEFT JOIN post_likes pl ON pl.post_id=p.id
    WHERE p.user_id=?
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 20
");
$posts->execute([$current_user_id ?? 0, $view_id]);
$posts = $posts->fetchAll();

// ── Banner color per user ──
$colors = ['#F07B20','#2775C9','#3EC87A','#9B59B6','#E04040','#F39C12','#1ABC9C'];
$bannerColor = $colors[$view_id % count($colors)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($user['username']) ?> – Tournameet</title>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet"/>
<style>
:root {
  --orange:#F07B20;--orange-light:#FDE8D4;--orange-mid:#F9C49A;
  --bg:#F2F0EE;--card:#fff;--text:#1A1A1A;
  --muted:#888;--border:#E8E4E0;--radius:18px;
  --green:#3EC87A;--red:#E04040;
  --banner:<?= $bannerColor ?>;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Barlow',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

/* NAV */
nav{background:#fff;border-bottom:2px solid var(--orange);display:flex;align-items:center;justify-content:space-between;padding:0 36px;height:62px;position:sticky;top:0;z-index:100;}
.nav-left{display:flex;align-items:center;}
.home-btn{display:flex;align-items:center;gap:8px;background:var(--orange);color:#fff;border:none;border-radius:10px;padding:8px 16px;font-family:'Barlow',sans-serif;font-weight:600;font-size:.9rem;cursor:pointer;transition:background .15s;}
.home-btn:hover{background:#d96a10;}
.back-btn{display:flex;align-items:center;gap:6px;background:transparent;color:var(--muted);border:1.5px solid var(--border);border-radius:10px;padding:7px 14px;font-family:'Barlow',sans-serif;font-weight:600;font-size:.85rem;cursor:pointer;transition:all .15s;margin-left:10px;}
.back-btn:hover{border-color:var(--orange);color:var(--orange);}

/* BANNER */
.profile-banner{height:200px;background:var(--banner);position:relative;overflow:hidden;}
.banner-pattern{position:absolute;inset:0;opacity:.12;background-image:repeating-linear-gradient(45deg,#fff 0,#fff 2px,transparent 0,transparent 50%);background-size:14px 14px;}
.banner-glow{position:absolute;inset:0;background:radial-gradient(ellipse at 30% 60%, rgba(255,255,255,.18) 0%, transparent 70%);}

/* PROFILE HEADER */
.profile-header{background:#fff;border-bottom:1.5px solid var(--border);padding:0 48px 28px;position:relative;}
.avatar-wrap{position:absolute;top:-52px;left:48px;}
.profile-avatar{width:104px;height:104px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:2.2rem;color:var(--orange);border:4px solid #fff;box-shadow:0 4px 20px rgba(0,0,0,.12);overflow:hidden;}
.profile-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%;}

.profile-info{padding-top:64px;display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;}
.profile-info-left{}
.profile-name{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:2rem;text-transform:uppercase;line-height:1;margin-bottom:6px;}
.profile-role{display:inline-block;font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:3px 12px;border-radius:20px;margin-bottom:10px;}
.role-athlete{background:var(--orange-light);color:var(--orange);}
.role-coach{background:#E3F0FF;color:#2775C9;}
.profile-bio{color:var(--muted);font-size:.92rem;line-height:1.5;max-width:480px;margin-bottom:14px;}
.profile-meta{display:flex;flex-wrap:wrap;gap:10px;}
.meta-tag{display:flex;align-items:center;gap:5px;font-size:.8rem;color:var(--muted);}
.meta-tag svg{width:14px;height:14px;color:var(--orange);}

.profile-actions{display:flex;gap:10px;align-items:center;flex-shrink:0;margin-top:8px;}
.btn{display:flex;align-items:center;gap:6px;border:none;border-radius:10px;cursor:pointer;font-family:'Barlow',sans-serif;font-weight:600;font-size:.88rem;padding:10px 18px;transition:all .15s;}
.btn:active{transform:scale(.97);}
.btn-orange{background:var(--orange);color:#fff;}
.btn-orange:hover{background:#d96a10;}
.btn-orange:disabled{background:#ccc;cursor:default;}
.btn-outline{background:#fff;color:var(--text);border:1.5px solid var(--border);}
.btn-outline:hover{border-color:var(--orange);color:var(--orange);}
.btn-green{background:var(--green);color:#fff;}
.btn-green:hover{background:#31a863;}
.btn-red-outline{background:#fff;color:var(--red);border:1.5px solid #fdd;}
.btn-red-outline:hover{background:#ffeaea;}
.btn-pending{background:var(--orange-light);color:var(--orange);cursor:default;}
.btn svg{width:15px;height:15px;flex-shrink:0;}

/* STATS BAR */
.stats-bar{display:flex;gap:0;border-top:1.5px solid var(--border);margin-top:22px;}
.stat-item{flex:1;padding:16px 12px;text-align:center;border-right:1.5px solid var(--border);}
.stat-item:last-child{border-right:none;}
.stat-val{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.6rem;color:var(--orange);}
.stat-lbl{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:2px;}

/* LAYOUT */
.profile-layout{display:grid;grid-template-columns:300px 1fr;gap:24px;padding:28px 48px 60px;max-width:1100px;margin:0 auto;}
@media(max-width:780px){.profile-layout{grid-template-columns:1fr;padding:20px 18px 40px;}.profile-header{padding:0 18px 22px;}.avatar-wrap{left:18px;}.profile-banner{height:140px;}}

/* SIDEBAR */
.sidebar-card{background:#fff;border-radius:var(--radius);border:1.5px solid var(--border);padding:20px;margin-bottom:16px;}
.sidebar-title{font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:.85rem;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:14px;}
.info-row{display:flex;align-items:center;gap:10px;font-size:.86rem;color:var(--text);margin-bottom:10px;}
.info-row:last-child{margin-bottom:0;}
.info-row svg{width:16px;height:16px;color:var(--orange);flex-shrink:0;}
.info-row span{color:var(--muted);font-size:.8rem;margin-left:auto;}

/* POSTS */
.posts-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.posts-title{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.2rem;text-transform:uppercase;}
.posts-count{background:var(--orange-light);color:var(--orange);border-radius:20px;font-size:.75rem;font-weight:700;padding:2px 10px;}

.post-card{background:#fff;border-radius:var(--radius);border:1.5px solid var(--border);padding:20px;margin-bottom:16px;transition:box-shadow .2s;}
.post-card:hover{box-shadow:0 4px 20px rgba(240,123,32,.1);}
.post-top{display:flex;align-items:center;gap:12px;margin-bottom:14px;}
.post-avatar{width:40px;height:40px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:.95rem;color:var(--orange);flex-shrink:0;overflow:hidden;}
.post-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.post-username{font-weight:700;font-size:.9rem;}
.post-time{font-size:.75rem;color:var(--muted);}
.post-content{font-size:.92rem;line-height:1.6;color:var(--text);margin-bottom:14px;white-space:pre-wrap;}
.post-image{width:100%;border-radius:12px;margin-bottom:14px;max-height:360px;object-fit:cover;}
.post-actions{display:flex;gap:8px;border-top:1.5px solid var(--border);padding-top:12px;}
.post-action-btn{display:flex;align-items:center;gap:5px;background:transparent;border:none;cursor:pointer;font-family:'Barlow',sans-serif;font-size:.82rem;font-weight:600;color:var(--muted);padding:5px 10px;border-radius:8px;transition:all .15s;}
.post-action-btn:hover{background:var(--bg);color:var(--text);}
.post-action-btn.liked{color:var(--red);}
.post-action-btn svg{width:15px;height:15px;}

.no-posts{text-align:center;padding:48px 20px;color:var(--muted);}
.no-posts svg{width:52px;height:52px;margin-bottom:12px;opacity:.3;}
.no-posts p{font-size:.95rem;}

/* TOAST */
.toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(80px);background:#1A1A1A;color:#fff;border-radius:30px;padding:10px 24px;font-size:.875rem;font-weight:600;z-index:9999;transition:transform .3s;pointer-events:none;display:flex;align-items:center;gap:8px;}
.toast.show{transform:translateX(-50%) translateY(0);}
.toast-icon{color:var(--orange);}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-left">
    <button class="home-btn" onclick="window.location.href='/Tourna/TF/teams_friends.php'">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
      <span>Home</span>
    </button>
  </div>
</nav>

<!-- BANNER -->
<div class="profile-banner">
  <div class="banner-pattern"></div>
  <div class="banner-glow"></div>
</div>

<!-- PROFILE HEADER -->
<div class="profile-header">
  <div class="avatar-wrap">
    <div class="profile-avatar">
      <?php if ($profilePic): ?>
        <img src="<?= htmlspecialchars($profilePic) ?>" alt="<?= htmlspecialchars($user['username']) ?>">
      <?php else: ?>
        <?= $initials ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="profile-info">
    <div class="profile-info-left">
      <div class="profile-name"><?= htmlspecialchars($user['username']) ?></div>
      <span class="profile-role <?= $user['role']==='athlete'?'role-athlete':'role-coach' ?>"><?= $roleLabel ?></span>
      <div class="profile-bio"><?= htmlspecialchars($user['bio'] ?: 'No bio yet.') ?></div>
      <div class="profile-meta">
        <div class="meta-tag">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Member since <?= $joinYear ?>
        </div>
        <div class="meta-tag">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
          <?= $user['role']==='athlete'?'Competing':'Coaching' ?>
        </div>
      </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="profile-actions" id="profileActions">
      <?php if (!$current_user_id): ?>
        <button class="btn btn-outline" disabled>Log in to connect</button>
      <?php elseif ($friendStatus === 'accepted'): ?>
        <button class="btn btn-outline" onclick="openChat()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Message
        </button>
        <button class="btn btn-red-outline" id="removeFriendBtn" onclick="removeFriend()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
          Remove Friend
        </button>
      <?php elseif ($friendStatus === 'pending'): ?>
        <button class="btn btn-pending" disabled>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          Request Sent
        </button>
        <button class="btn btn-outline" onclick="openChat()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Message
        </button>
      <?php elseif ($friendStatus === 'incoming'): ?>
        <button class="btn btn-green" onclick="respondRequest('accept')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
          Accept Request
        </button>
        <button class="btn btn-red-outline" onclick="respondRequest('decline')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
          Decline
        </button>
      <?php else: ?>
        <button class="btn btn-orange" id="addFriendBtn" onclick="sendRequest()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
          Add Friend
        </button>
        <button class="btn btn-outline" onclick="openChat()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Message
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- STATS BAR -->
  <div class="stats-bar">
    <div class="stat-item"><div class="stat-val"><?= $friendCount ?></div><div class="stat-lbl">Friends</div></div>
    <div class="stat-item"><div class="stat-val"><?= $teamCount ?></div><div class="stat-lbl">Teams</div></div>
    <div class="stat-item"><div class="stat-val"><?= count($posts) ?></div><div class="stat-lbl">Posts</div></div>
    <div class="stat-item"><div class="stat-val"><?= $joinYear ?></div><div class="stat-lbl">Joined</div></div>
  </div>
</div>

<!-- MAIN LAYOUT -->
<div class="profile-layout">

  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="sidebar-card">
      <div class="sidebar-title">About</div>
      <div class="info-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <?= htmlspecialchars($user['username']) ?>
      </div>
      <div class="info-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <?= htmlspecialchars($user['email']) ?>
      </div>
      <div class="info-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
        <?= $roleLabel ?>
        <span><?= $joinYear ?></span>
      </div>
    </div>

    <div class="sidebar-card">
      <div class="sidebar-title">Stats</div>
      <div class="info-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Friends
        <span><?= $friendCount ?></span>
      </div>
      <div class="info-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Teams Joined
        <span><?= $teamCount ?></span>
      </div>
      <div class="info-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Posts
        <span><?= count($posts) ?></span>
      </div>
    </div>
  </div>

  <!-- POSTS FEED -->
  <div class="posts-feed">
    <div class="posts-header">
      <div class="posts-title">Posts</div>
      <span class="posts-count"><?= count($posts) ?> total</span>
    </div>

    <?php if (empty($posts)): ?>
      <div class="no-posts">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        <p><?= htmlspecialchars($user['username']) ?> hasn't posted anything yet.</p>
      </div>
    <?php else: ?>
      <?php foreach ($posts as $post): ?>
        <?php
          $timeAgo = timeAgo($post['created_at']);
          $postAvatar = $profilePic
            ? "<img src=\"".htmlspecialchars($profilePic)."\" alt=\"\">"
            : $initials;
        ?>
        <div class="post-card" id="post-<?= $post['id'] ?>">
          <div class="post-top">
            <div class="post-avatar"><?= $postAvatar ?></div>
            <div>
              <div class="post-username"><?= htmlspecialchars($user['username']) ?></div>
              <div class="post-time"><?= $timeAgo ?></div>
            </div>
          </div>
          <div class="post-content"><?= htmlspecialchars($post['content']) ?></div>
          <?php if ($post['image_url']): ?>
            <img class="post-image" src="uploads/<?= htmlspecialchars($post['image_url']) ?>" alt="Post image">
          <?php endif; ?>
          <div class="post-actions">
            <button class="post-action-btn <?= $post['liked_by_me']?'liked':'' ?>"
                    id="like-btn-<?= $post['id'] ?>"
                    onclick="toggleLike(<?= $post['id'] ?>, this)">
              <svg viewBox="0 0 24 24" fill="<?= $post['liked_by_me']?'var(--red)':'none' ?>" stroke="<?= $post['liked_by_me']?'var(--red)':'currentColor' ?>" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              <span id="like-count-<?= $post['id'] ?>"><?= $post['like_count'] ?></span> Likes
            </button>
            <button class="post-action-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Comment
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<!-- TOAST -->
<div class="toast" id="toast"><span class="toast-icon">✓</span><span id="toastMsg">Done!</span></div>

<script>
const API      = './api';
const VIEW_ID  = <?= $view_id ?>;
const FSHIP_ID = <?= $friendship_id ?? 'null' ?>;
const USERNAME = <?= json_encode($user['username']) ?>;
const INITIALS = <?= json_encode($initials) ?>;

// ── Friend Actions ──
async function sendRequest() {
  const btn = document.getElementById('addFriendBtn');
  btn.disabled = true; btn.innerHTML = '… Sending';
  const res = await post('friend_action.php', {action:'send', friend_id: VIEW_ID});
  if (res.success) {
    btn.className = 'btn btn-pending';
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Request Sent';
    toast('Friend request sent!');
  } else {
    btn.disabled = false;
    btn.innerHTML = '+ Add Friend';
    toast(res.error || 'Something went wrong');
  }
}

async function removeFriend() {
  if (!confirm('Remove this friend?')) return;
  const res = await post('friend_action.php', {action:'remove', friend_id: VIEW_ID});
  if (res.success) {
    toast('Friend removed');
    setTimeout(() => location.reload(), 800);
  } else toast(res.error || 'Something went wrong');
}

async function respondRequest(action) {
  const res = await post('friend_action.php', {action, friendship_id: FSHIP_ID});
  if (res.success) {
    toast(action==='accept' ? '🎉 Now friends!' : 'Request declined');
    setTimeout(() => location.reload(), 800);
  } else toast(res.error || 'Something went wrong');
}

// ── Like ──
async function toggleLike(postId, btn) {
  const res = await post('toggle_like.php', {post_id: postId});
  if (res.success) {
    const countEl = document.getElementById('like-count-' + postId);
    countEl.textContent = res.like_count;
    if (res.liked) {
      btn.classList.add('liked');
      btn.querySelector('svg').setAttribute('fill','var(--red)');
      btn.querySelector('svg').setAttribute('stroke','var(--red)');
    } else {
      btn.classList.remove('liked');
      btn.querySelector('svg').setAttribute('fill','none');
      btn.querySelector('svg').setAttribute('stroke','currentColor');
    }
  }
}

// ── Chat (placeholder) ──
function openChat() {
  toast('Opening chat with ' + USERNAME + '…');
  // Wire to your chat system here
}

// ── Helpers ──
async function post(endpoint, data) {
  try {
    const r = await fetch(`${API}/${endpoint}`, {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify(data)
    });
    return await r.json();
  } catch(e) { return {success:false,error:'Network error'}; }
}

function toast(msg) {
  document.getElementById('toastMsg').textContent = msg;
  const el = document.getElementById('toast');
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 2600);
}
</script>
</body>
</html>
<?php
function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return floor($diff/60)   . 'm ago';
    if ($diff < 86400)  return floor($diff/3600)  . 'h ago';
    if ($diff < 604800) return floor($diff/86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}
?>