<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'] ?? 0;

$result = $conn->query("
    SELECT id, type, message, is_read, created_at
    FROM user_notifications
    WHERE user_id = $user_id
    ORDER BY created_at DESC
    LIMIT 60
");
$notifications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all'])) {
    $conn->query("UPDATE user_notifications SET is_read=1 WHERE user_id=$user_id");
    header("Location: notifications.php");
    exit;
}

$uc = count(array_filter($notifications, fn($n) => !$n['is_read']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="/Tourna/favicon.png">
  <title>Notifications · TournaMeet</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --orange:       #F47B20;
      --orange-dark:  #D96210;
      --orange-light: #FFF0E6;
      --white:        #FFFFFF;
      --shadow:       0 2px 16px rgba(244,123,32,0.18);
    }
    body { background: #fafafa; font-family: 'DM Sans', sans-serif; min-height: 100vh; color: #1a1a1a; }

    /* NAV */
    nav {
      position: sticky; top: 0; z-index: 200;
      background: var(--white); border-bottom: 2px solid var(--orange);
      box-shadow: var(--shadow); height: 64px;
      display: grid; grid-template-columns: 1fr 1fr 1fr;
      align-items: center; padding: 0 32px; gap: 12px;
    }
    .nav-left { display: flex; align-items: center; gap: 10px; }
    .home-btn {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--orange); color: #fff; border: none; border-radius: 10px;
      padding: 8px 16px; font-family: 'DM Sans', sans-serif; font-weight: 700;
      font-size: .85rem; text-decoration: none; cursor: pointer;
      transition: background 0.2s, transform 0.1s; flex-shrink: 0;
    }
    .home-btn:hover { background: var(--orange-dark); }
    .home-btn:active { transform: scale(.97); }
    .logo-icon {
      width: 38px; height: 38px; border-radius: 50%;
      background: var(--orange-light); border: 2px solid var(--orange);
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .logo-icon img { width: 24px; height: 24px; object-fit: contain; }
    .brand { font-family: 'Bebas Neue', sans-serif; font-size: 1.7rem; letter-spacing: 2.5px; color: var(--orange); line-height: 1; }
    .nav-center { display: flex; justify-content: center; }
    .search-wrap { position: relative; width: 100%; max-width: 340px; }
    .search-wrap input {
      width: 100%; height: 40px; border: 2px solid var(--orange); border-radius: 50px;
      padding: 0 44px 0 18px; font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
      background: var(--orange-light); outline: none; transition: box-shadow 0.2s, background 0.2s;
    }
    .search-wrap input::placeholder { color: #aaa; }
    .search-wrap input:focus { background: var(--white); box-shadow: 0 0 0 3px rgba(244,123,32,0.25); }
    .search-wrap button {
      position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
      background: var(--orange); border: none; border-radius: 50%;
      width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
      cursor: pointer;
    }
    .nav-right { display: flex; justify-content: flex-end; align-items: center; gap: 8px; }
    .nav-icon-btn {
      background: var(--orange-light); border: 1.5px solid var(--orange); border-radius: 50%;
      width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;
      cursor: pointer; transition: background 0.2s, transform 0.15s; text-decoration: none;
    }
    .nav-icon-btn:hover { background: var(--orange); transform: scale(1.08); }
    .nav-icon-btn:hover svg { stroke: white !important; }
    .nav-icon-btn.active { background: var(--orange); }
    .nav-icon-btn.active svg { stroke: white !important; }

    /* PAGE */
    main { max-width: 760px; margin: 0 auto; padding: 44px 24px 80px; }

    .page-header {
      display: flex; align-items: flex-end; justify-content: space-between;
      margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
    }
    .page-header-left h1 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(2rem, 6vw, 3.2rem);
      color: var(--orange); letter-spacing: 3px; line-height: 1; margin-bottom: 4px;
    }
    .page-header-left p { font-size: 0.88rem; color: #aaa; font-weight: 500; }
    .mark-all-btn {
      background: var(--orange-light); border: 1.5px solid var(--orange);
      color: var(--orange); border-radius: 50px; padding: 8px 20px;
      font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 700;
      cursor: pointer; transition: background 0.2s, color 0.2s;
    }
    .mark-all-btn:hover { background: var(--orange); color: #fff; }

    /* FILTER TABS */
    .filter-bar {
      display: flex; gap: 6px; margin-bottom: 22px;
      overflow-x: auto; padding-bottom: 2px; scrollbar-width: none;
    }
    .filter-bar::-webkit-scrollbar { display: none; }
    .filter-tab {
      border: 1.5px solid #e8ddd4; background: #fff; color: #999;
      border-radius: 50px; padding: 7px 18px;
      font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 600;
      cursor: pointer; white-space: nowrap; transition: all 0.15s;
    }
    .filter-tab:hover  { border-color: var(--orange); color: var(--orange); background: var(--orange-light); }
    .filter-tab.active { background: var(--orange); color: #fff; border-color: var(--orange); }

    /* SECTION LABELS */
    .section-label {
      font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em;
      text-transform: uppercase; color: #c8b8aa;
      padding: 0 0 8px; margin: 20px 0 4px;
      border-bottom: 1px solid #f0e8e0;
    }

    /* NOTIF CARD */
    .notif-card {
      display: flex; gap: 14px; align-items: flex-start;
      background: #fff; border-radius: 14px; border: 1.5px solid #f0e8e0;
      padding: 16px 18px; margin-bottom: 10px;
      cursor: pointer; transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
      animation: fadeUp 0.35s ease both; position: relative; overflow: hidden;
    }
    .notif-card::before {
      content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
      background: var(--orange); opacity: 0; transition: opacity 0.15s; border-radius: 4px 0 0 4px;
    }
    .notif-card.unread { background: #fff9f5; border-color: #f5deca; }
    .notif-card.unread::before { opacity: 1; }
    .notif-card:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(244,123,32,0.13); border-color: var(--orange); }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(14px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .notif-avatar {
      width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .notif-body { flex: 1; min-width: 0; }
    .notif-msg { font-size: 0.88rem; color: #2a1a0e; line-height: 1.5; margin-bottom: 5px; }
    .notif-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .notif-time { font-size: 0.75rem; color: #bbb; }
    .notif-type-pill {
      font-size: 0.68rem; font-weight: 700; border-radius: 20px;
      padding: 2px 9px; text-transform: uppercase; letter-spacing: 0.06em;
    }
    .unread-dot {
      width: 9px; height: 9px; border-radius: 50%;
      background: var(--orange); flex-shrink: 0; margin-top: 6px;
    }

    /* EMPTY */
    .empty-state { text-align: center; padding: 72px 20px; animation: fadeUp 0.4s ease both; }
    .empty-icon {
      width: 80px; height: 80px; border-radius: 50%;
      background: var(--orange-light); border: 2px solid #f0ddd0;
      display: flex; align-items: center; justify-content: center;
      font-size: 32px; margin: 0 auto 18px;
    }
    .empty-state h3 { font-family: 'Bebas Neue', sans-serif; font-size: 1.6rem; letter-spacing: 2px; color: #ccc; margin-bottom: 6px; }
    .empty-state p  { font-size: 0.85rem; color: #ccc; }

    @media (max-width: 599px) {
      nav { padding: 0 12px; grid-template-columns: auto 1fr auto; }
      main { padding: 24px 14px 60px; }
      .notif-card { padding: 13px 14px; }
      .home-btn span { display: none; }
    }
  </style>
</head>
<body>

  <nav>
    <div class="nav-left">
      <!-- Home button -->
      <a href="/Tourna/NewsFeed/newsfeed.php" class="home-btn">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/>
          <path d="M9 21V12h6v9"/>
        </svg>
        <span>HOME</span>
      </a>
      <!-- Logo -->
      <a href="/Tourna/index.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
        <div class="logo-icon"><img src="/Tourna/LIGHT ICON-Tournameet.png" alt="TournaMeet"></div>
        <span class="brand">TournaMeet</span>
      </a>
    </div>

    <div class="nav-center">
      <div class="search-wrap">
        <input type="text" placeholder="Search tournaments, sports…"/>
        <button><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button>
      </div>
    </div>

    <div class="nav-right">
      <a href="/Tourna/Shop/index.php" class="nav-icon-btn" title="Shop">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </a>
      <a href="/Tourna/TF/Chats/chat.php" class="nav-icon-btn" title="Messages">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </a>
      <a href="/Tourna/TF/teams_friends.php" class="nav-icon-btn" title="Teams & Friends">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="18" cy="7" r="2.5"/><path d="M21 20c0-2.8-1.8-5.1-4.3-5.8"/></svg>
      </a>
      <a href="/Tourna/index.php" class="nav-icon-btn" title="Map">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </a>
      <a href="/Tourna/notifications.php" class="nav-icon-btn active" title="Notifications">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      </a>
      <a href="/Tourna/profile.php" class="nav-icon-btn" title="Profile">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      </a>
    </div>
  </nav>

  <main>
    <div class="page-header">
      <div class="page-header-left">
        <h1>Notifications</h1>
        <p><?= $uc > 0 ? "$uc unread notification" . ($uc > 1 ? 's' : '') : 'All caught up' ?></p>
      </div>
      <?php if ($uc > 0): ?>
      <form method="POST">
        <button class="mark-all-btn" name="mark_all" value="1">Mark all as read</button>
      </form>
      <?php endif; ?>
    </div>

    <div class="filter-bar" id="filterBar">
      <button class="filter-tab active" data-filter="all">All</button>
      <button class="filter-tab" data-filter="like">❤️ Likes</button>
      <button class="filter-tab" data-filter="comment">💬 Comments</button>
      <button class="filter-tab" data-filter="friend_request">👥 Friends</button>
      <button class="filter-tab" data-filter="tournament">🏆 Tournaments</button>
    </div>

    <div id="notifList">
      <?php if (empty($notifications)): ?>
        <div class="empty-state">
          <div class="empty-icon">🔔</div>
          <h3>Nothing Yet</h3>
          <p>Your notifications will show up here</p>
        </div>
      <?php else:

        $type_cfg = [
          'like'           => ['bg'=>'#fff0e6','color'=>'#e05c00','icon'=>'❤️','pill_bg'=>'#ffe4cc','label'=>'Like'],
          'comment'        => ['bg'=>'#ffeadb','color'=>'#d44f00','icon'=>'💬','pill_bg'=>'#ffd9c2','label'=>'Comment'],
          'friend_request' => ['bg'=>'#ffe2cc','color'=>'#b84500','icon'=>'👥','pill_bg'=>'#ffd0ad','label'=>'Friend'],
          'tournament'     => ['bg'=>'#fff4e0','color'=>'#e05c00','icon'=>'🏆','pill_bg'=>'#ffecc4','label'=>'Tournament'],
        ];

        function time_ago($datetime) {
          $diff = time() - strtotime($datetime);
          if ($diff < 60)     return 'just now';
          if ($diff < 3600)   return floor($diff / 60)    . 'm ago';
          if ($diff < 86400)  return floor($diff / 3600)  . 'h ago';
          if ($diff < 604800) return floor($diff / 86400) . 'd ago';
          return date('M j', strtotime($datetime));
        }

        $unread_notifs = array_values(array_filter($notifications, fn($n) => !$n['is_read']));
        $read_notifs   = array_values(array_filter($notifications, fn($n) =>  $n['is_read']));

        foreach ([['label'=>'New','items'=>$unread_notifs],['label'=>'Earlier','items'=>$read_notifs]] as $section):
          if (empty($section['items'])) continue;
      ?>
        <div class="section-label"><?= $section['label'] ?></div>

        <?php foreach ($section['items'] as $i => $n):
          $cfg        = $type_cfg[$n['type']] ?? ['bg'=>'#f5f5f5','color'=>'#888','icon'=>'🔔','pill_bg'=>'#eee','label'=>ucfirst($n['type'])];
          $delay      = min($i * 45, 300);
          $read_class = $n['is_read'] ? '' : 'unread';
        ?>
        <div class="notif-card <?= $read_class ?>"
             data-type="<?= htmlspecialchars($n['type']) ?>"
             data-id="<?= (int)$n['id'] ?>"
             style="animation-delay:<?= $delay ?>ms;"
             onclick="markRead(<?= (int)$n['id'] ?>, this)">

          <div class="notif-avatar" style="background:<?= $cfg['bg'] ?>;">
            <?= $cfg['icon'] ?>
          </div>

          <div class="notif-body">
            <p class="notif-msg"><?= htmlspecialchars($n['message']) ?></p>
            <div class="notif-meta">
              <span class="notif-time"><?= time_ago($n['created_at']) ?></span>
              <span class="notif-type-pill" style="background:<?= $cfg['pill_bg'] ?>;color:<?= $cfg['color'] ?>;"><?= $cfg['label'] ?></span>
            </div>
          </div>

          <?php if (!$n['is_read']): ?>
            <div class="unread-dot"></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

  <script>
    document.getElementById('filterBar').addEventListener('click', function(e) {
      var tab = e.target.closest('.filter-tab');
      if (!tab) return;
      document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      var f = tab.dataset.filter;
      document.querySelectorAll('.notif-card').forEach(function(card) {
        card.style.display = (f === 'all' || card.dataset.type === f) ? 'flex' : 'none';
      });
      document.querySelectorAll('.section-label').forEach(function(lbl) {
        var next = lbl.nextElementSibling;
        var hasVisible = false;
        while (next && !next.classList.contains('section-label')) {
          if (next.style.display !== 'none') hasVisible = true;
          next = next.nextElementSibling;
        }
        lbl.style.display = hasVisible ? '' : 'none';
      });
    });

    function markRead(id, el) {
      if (!el.classList.contains('unread')) return;
      el.classList.remove('unread');
      var dot = el.querySelector('.unread-dot');
      if (dot) dot.remove();
      fetch('/Tourna/notif_read.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, action: 'read' })
      }).catch(function(){});
    }
  </script>

</body>
</html>