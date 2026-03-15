<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
$uid = (int) $_SESSION['user_id'];

try {
    $db   = getDB();
    $stmt = $db->prepare("SELECT id, username, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    $me = $stmt->fetch();

    $trending = [];
    try {
        $trendStmt = $db->query("SELECT category, COUNT(*) AS cnt FROM posts GROUP BY category ORDER BY cnt DESC LIMIT 5");
        $trending  = $trendStmt->fetchAll();
    } catch (Exception $e) {}

    $membersStmt = $db->query("SELECT u.username, u.profile_pic, COUNT(p.id) AS post_count FROM users u LEFT JOIN posts p ON p.user_id = u.id GROUP BY u.id ORDER BY post_count DESC LIMIT 5");
    $members = $membersStmt->fetchAll();

    $totalPosts   = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $totalMembers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $todayPosts   = $db->query("SELECT COUNT(*) FROM posts WHERE DATE(created_at) = CURDATE()")->fetchColumn();
} catch (Exception $e) {
    $me = ['id'=>$uid,'username'=>'User','profile_pic'=>null];
    $trending = []; $members = []; $totalPosts = 0; $totalMembers = 0; $todayPosts = 0;
}

if (!$me) { session_destroy(); header('Location: ../login.php'); exit; }

$username = $me['username'] ?? 'User';
$initials = strtoupper(substr($username, 0, 2));
$picPath  = null;
if (!empty($me['profile_pic'])) $picPath = '../uploads' . $me['profile_pic'];

$categories = ['All','Ball Sports','Racket Sports','Combatives','Endurance','Precision','E-Sports','General'];
$catIcons   = ['Ball Sports'=>'⚽','Racket Sports'=>'🏸','Combatives'=>'🥊','Endurance'=>'🏃','Precision'=>'🎯','E-Sports'=>'🎮','General'=>'📢'];
$catColors  = ['Ball Sports'=>'#e8630a','Racket Sports'=>'#2563eb','Combatives'=>'#dc2626','Endurance'=>'#059669','Precision'=>'#7c3aed','E-Sports'=>'#0891b2','General'=>'#6b7280'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Newsfeed — Tournameet</title>
  <link rel="icon" type="image/png" href="/Tourna/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@600;700;800;900&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/newsfeed.css"/>
</head>
<body>

<nav class="navbar">
  <span class="brand-text">TOURNAMEET</span>
  <div class="nav-actions">
    <a href="/Tourna/TF/Chats/chat.php">
      <button class="nav-icon-btn" title="Messages">
        <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3 8l9 6 9-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </a>
    <a href="/Tourna/notifications.php">
      <button class="nav-icon-btn" title="Notifications">
        <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8"/><path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8"/></svg>
      </button>
    </a>
    <a href="../profile.php" class="nav-avatar" title="My Profile">
      <?php if ($picPath): ?>
        <img src="<?= htmlspecialchars($picPath) ?>" alt="<?= htmlspecialchars($username) ?>"/>
      <?php else: ?>
        <span><?= htmlspecialchars($initials) ?></span>
      <?php endif; ?>
    </a>
  </div>
</nav>

<div class="page-body">
  <aside class="sidebar sidebar-left">
    <div class="widget user-widget">
      <div class="user-widget-bg"></div>
      <div class="user-widget-body">
        <div class="uw-avatar">
          <?php if ($picPath): ?><img src="<?= htmlspecialchars($picPath) ?>" alt="<?= htmlspecialchars($username) ?>"/>
          <?php else: ?><span><?= htmlspecialchars($initials) ?></span><?php endif; ?>
        </div>
        <div class="uw-name"><?= htmlspecialchars($username) ?></div>
        <a href="../profile.php" class="uw-btn">View Profile</a>
      </div>
    </div>
    <div class="widget">
      <div class="widget-title">Quick Nav</div>
      <nav class="quick-nav">
        <a href="newsfeed.php" class="qn-item active"><span class="qn-icon">📰</span> Newsfeed</a>
        <a href="/Tourna/Tournafinal/Tournameet/index.php" class="qn-item"><span class="qn-icon">🏆</span> Tournaments</a>
        <a href="/Tourna/TF/teams_friends.php" class="qn-item"><span class="qn-icon">👥</span> Teams & Friends</a>
        <a href="/Tourna/Shop/index.php" class="qn-item"><span class="qn-icon">🏪</span> Shop</a>
      </nav>
    </div>
  </aside>

  <main class="feed-main">
    <header class="feed-header">
      <h1>Newsfeed</h1>
      <p class="feed-subtitle">Updates from your tournament community</p>
    </header>

    <div class="card compose-card">
      <div class="compose-top">
        <div class="compose-avatar-wrap">
          <?php if ($picPath): ?>
            <div class="avatar avatar-md has-photo"><img src="<?= htmlspecialchars($picPath) ?>" alt="Me"/></div>
          <?php else: ?>
            <div class="avatar avatar-md initials-avatar"><?= htmlspecialchars($initials) ?></div>
          <?php endif; ?>
        </div>
        <textarea id="postContent" placeholder="What's happening in your tournament world? 🏆" rows="1"></textarea>
      </div>
      <div class="compose-toolbar" id="composeActions" style="display:none">
        <div class="compose-attachments">
          <button class="attach-btn">
            <svg viewBox="0 0 24 24" fill="none" width="15" height="15"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.8"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            Photo
          </button>
          <button class="attach-btn">
            <svg viewBox="0 0 24 24" fill="none" width="15" height="15"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" stroke="currentColor" stroke-width="1.8"/><line x1="7" y1="7" x2="7.01" y2="7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Tag
          </button>
        </div>
        <div class="compose-right">
          <select id="postCategory">
            <?php foreach (array_slice($categories,1) as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>"><?= ($catIcons[$cat]??'').' '.htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn-ghost" id="cancelPost">Cancel</button>
          <button class="btn-primary" id="submitPost" disabled>
            <svg viewBox="0 0 24 24" fill="none" width="13" height="13"><path d="M22 2L11 13" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            POST
          </button>
        </div>
      </div>
    </div>

    <div class="filter-scroll">
      <div class="filter-tabs" id="filterTabs">
        <?php foreach ($categories as $cat): ?>
          <button class="filter-tab <?= $cat==='All'?'active':'' ?>" data-cat="<?= htmlspecialchars($cat) ?>">
            <?php if($cat!=='All'): ?><span class="tab-icon"><?= $catIcons[$cat]??'' ?></span><?php endif; ?>
            <?= strtoupper(htmlspecialchars($cat)) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div id="postsFeed">
      <div class="loading-wrap"><div class="spinner"></div></div>
    </div>
  </main>

  <aside class="sidebar sidebar-right">
    <div class="widget">
      <div class="widget-title">🔥 Trending Categories</div>
      <div class="trending-list">
        <?php if (empty($trending)): ?>
          <p class="widget-empty">Start posting to see trends!</p>
        <?php else:
          $max = max(array_column($trending,'cnt')) ?: 1;
          foreach ($trending as $i => $t):
            $col = $catColors[$t['category']] ?? '#e8630a';
            $pct = round(($t['cnt']/$max)*100);
        ?>
          <div class="trend-item">
            <div class="trend-rank" style="background:<?= $col ?>18;color:<?= $col ?>"><?= $i+1 ?></div>
            <div class="trend-info">
              <span class="trend-name"><?= htmlspecialchars($t['category']) ?></span>
              <div class="trend-bar-wrap"><div class="trend-bar" style="width:<?= $pct ?>%;background:<?= $col ?>"></div></div>
            </div>
            <span class="trend-count"><?= $t['cnt'] ?></span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
    <div class="widget widget-promo">
      <div class="promo-icon">🏆</div>
      <div class="promo-text">
        <strong>Find your next tournament</strong>
        <span>Browse events near you</span>
      </div>
      <a href="/Tourna/Tournafinal/Tournameet/index.php" class="promo-btn">Explore</a>
    </div>
  </aside>
</div>

<!-- ══ REACTION PICKER ══════════════════════════════════════════════════ -->
<div class="reaction-picker" id="reactionPicker">
  <button data-emoji="🔥">🔥</button>
  <button data-emoji="👏">👏</button>
  <button data-emoji="🏆">🏆</button>
  <button data-emoji="❤️">❤️</button>
  <button data-emoji="😮">😮</button>
</div>

<!-- ══ POST TEMPLATE ════════════════════════════════════════════════════ -->
<template id="postTemplate">
  <article class="card post-card" data-post-id="">
    <div class="post-header">
      <div class="post-author">
        <div class="post-avatar-slot"></div>
        <div class="author-info">
          <div class="author-name-row">
            <span class="author-name"></span>
            <span class="own-badge" style="display:none">You</span>
          </div>
          <div class="author-meta">
            <span class="post-time"></span>
            <span class="meta-dot">·</span>
            <span class="category-badge"></span>
          </div>
        </div>
      </div>
      <div class="post-header-right">
        <button class="delete-btn" style="display:none" title="Delete">
          <svg viewBox="0 0 24 24" fill="none" width="14" height="14">
            <polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </button>
      </div>
    </div>
    <p class="post-content"></p>
    <div class="post-media-grid" style="display:none"></div>
    <div class="post-actions">
      <button class="action-btn btn-like">
        <svg viewBox="0 0 24 24" fill="none" width="15" height="15"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span class="like-count">0</span>
      </button>
      <button class="action-btn btn-comment-toggle">
        <svg viewBox="0 0 24 24" fill="none" width="15" height="15"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span class="comment-count">0</span>
      </button>
      <button class="action-btn btn-react">
        <span class="my-emoji">😊</span>
        <span>React</span>
      </button>
      <div class="reaction-chips"></div>
    </div>
    <div class="comments-section" style="display:none">
      <div class="comments-list"></div>
      <div class="comment-input-row">
        <div class="comment-user-av-slot"></div>
        <div class="comment-box">
          <input type="text" class="comment-input" placeholder="Write a comment…"/>
          <button class="comment-send">
            <svg viewBox="0 0 24 24" fill="none" width="13" height="13"><path d="M22 2L11 13" stroke="white" stroke-width="2.5" stroke-linecap="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
      </div>
    </div>
  </article>
</template>

<!-- ══ POST FULL VIEW MODAL ═════════════════════════════════════════════ -->
<div id="postModal" onclick="if(event.target===this)closePostModal()" style="display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,0.85);align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:960px;max-height:92vh;display:flex;overflow:hidden;position:relative;box-shadow:0 24px 80px rgba(0,0,0,0.5);">

    <!-- Close -->
    <button onclick="closePostModal()" style="position:absolute;top:12px;right:12px;z-index:10;background:rgba(0,0,0,0.5);border:none;color:#fff;width:32px;height:32px;border-radius:50%;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1;">✕</button>

    <!-- LEFT: Media -->
    <div id="pmMediaWrap" style="flex:1;background:#000;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;min-height:400px;"></div>

    <!-- RIGHT: Info + Comments -->
    <div style="width:360px;min-width:300px;display:flex;flex-direction:column;max-height:92vh;border-left:1px solid #e4e6ea;">

      <!-- Author -->
      <div style="padding:14px 16px;border-bottom:1px solid #e4e6ea;display:flex;align-items:center;gap:10px;flex-shrink:0;">
        <div id="pmAvatar"></div>
        <div>
          <div id="pmUsername" style="font-weight:700;font-size:14px;color:#1a2540;"></div>
          <div id="pmTime" style="font-size:12px;color:#999;margin-top:1px;"></div>
        </div>
      </div>

      <!-- Caption -->
      <div id="pmCaption" style="padding:12px 16px;font-size:14px;color:#333;line-height:1.6;border-bottom:1px solid #e4e6ea;max-height:100px;overflow-y:auto;flex-shrink:0;"></div>

      <!-- Counts -->
      <div style="padding:8px 16px;border-bottom:1px solid #e4e6ea;display:flex;gap:16px;font-size:13px;color:#666;flex-shrink:0;">
        <span>❤️ <span id="pmLikeCnt">0</span> likes</span>
        <span>💬 <span id="pmCommentCnt">0</span> comments</span>
      </div>

      <!-- Action buttons -->
      <div style="padding:4px 16px;border-bottom:1px solid #e4e6ea;display:flex;gap:4px;flex-shrink:0;">
        <button id="pmLikeBtn" style="flex:1;padding:8px;border:none;border-radius:6px;background:none;cursor:pointer;font-size:13px;font-weight:600;color:#666;display:flex;align-items:center;justify-content:center;gap:6px;">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="1.8"/></svg>
          Like
        </button>
        <button onclick="document.getElementById('pmCommentInput').focus()" style="flex:1;padding:8px;border:none;border-radius:6px;background:none;cursor:pointer;font-size:13px;font-weight:600;color:#666;display:flex;align-items:center;justify-content:center;gap:6px;">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="1.8"/></svg>
          Comment
        </button>
      </div>

      <!-- Comments list -->
      <div id="pmCommentList" style="flex:1;overflow-y:auto;padding:12px 16px;"></div>

      <!-- Comment input -->
      <div style="padding:10px 16px;border-top:1px solid #e4e6ea;display:flex;gap:8px;align-items:center;flex-shrink:0;">
        <div id="pmCommentAv" style="flex-shrink:0;"></div>
        <div style="flex:1;background:#f0f2f5;border-radius:20px;display:flex;align-items:center;padding:0 14px;">
          <input id="pmCommentInput" type="text" placeholder="Write a comment…" maxlength="500"
            style="flex:1;border:none;background:none;padding:10px 0;font-size:14px;outline:none;font-family:inherit;"
            onkeydown="if(event.key==='Enter')submitModalComment()"/>
          <button onclick="submitModalComment()" style="background:none;border:none;cursor:pointer;color:#e8630a;padding:4px;">
            <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><path d="M22 2L11 13" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

<style>
.pm-nav {
  position:absolute;top:50%;transform:translateY(-50%);
  background:rgba(0,0,0,0.5);border:none;color:#fff;
  width:40px;height:40px;border-radius:50%;font-size:26px;
  cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:5;
}
.pm-nav:hover{background:rgba(0,0,0,0.8);}
.pm-prev{left:10px;} .pm-next{right:10px;}
.pm-counter{position:absolute;bottom:10px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.55);color:#fff;font-size:12px;padding:3px 10px;border-radius:12px;}
#pmLikeBtn:hover{background:#fff0ea;color:#e8630a;}
#pmLikeBtn.liked{color:#e8630a;}
#pmLikeBtn.liked svg path{fill:#e8630a;stroke:#e8630a;}
</style>

<!-- Pass current user to JS -->
<script>
const CURRENT_USER = {
  id:       <?= (int)$uid ?>,
  name:     <?= json_encode($username) ?>,
  photo:    <?= json_encode($picPath) ?>,
  initials: <?= json_encode($initials) ?>,
};
</script>
<script src="js/newsfeed.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const av = document.getElementById('pmCommentAv');
  if (av) av.appendChild(makeAvatar(CURRENT_USER.photo, CURRENT_USER.name, 'avatar-xs'));
});
document.addEventListener('keydown', e => { if(e.key==='Escape') closePostModal(); });
</script>
</body>
</html>