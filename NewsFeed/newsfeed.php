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
$catColors  = ['Ball Sports'=>'#F47B20','Racket Sports'=>'#2563eb','Combatives'=>'#dc2626','Endurance'=>'#059669','Precision'=>'#7c3aed','E-Sports'=>'#0891b2','General'=>'#6b7280'];
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
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --orange:       #F47B20;
      --orange-dark:  #D96210;
      --orange-light: #FFF0E6;
      --orange-mid:   #fdb97d;
      --white:        #FFFFFF;
      --bg:           #f5f5f5;
      --surface:      #FFFFFF;
      --border:       #f0e0d0;
      --border-soft:  #e8e8e8;
      --text:         #1a1a1a;
      --text-muted:   #888;
      --shadow:       0 2px 16px rgba(244,123,32,0.10);
      --shadow-hover: 0 6px 24px rgba(244,123,32,0.18);
      --radius:       14px;
    }

    html { scroll-behavior: smooth; }
    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 15px;
      min-height: 100vh;
    }

    /* ══════════════ NAVBAR ══════════════ */
    .navbar {
      position: sticky; top: 0; z-index: 200;
      background: var(--white);
      border-bottom: 2px solid var(--orange);
      box-shadow: var(--shadow);
      height: 64px;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 48px;
    }
    .brand-text {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.7rem; letter-spacing: 3px;
      color: var(--orange); line-height: 1;
    }
    .nav-actions { display: flex; align-items: center; gap: 8px; }
    .nav-icon-btn {
      background: var(--orange-light);
      border: 1.5px solid var(--orange);
      border-radius: 50%;
      width: 38px; height: 38px;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: var(--orange);
      transition: background 0.2s, transform 0.15s;
    }
    .nav-icon-btn:hover { background: var(--orange); color: var(--white); transform: scale(1.07); }
    .nav-icon-btn:hover svg { stroke: var(--white); }
    .nav-avatar {
      width: 38px; height: 38px; border-radius: 50%;
      background: linear-gradient(135deg, var(--orange), var(--orange-mid));
      border: 2px solid var(--orange);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Bebas Neue', sans-serif; font-size: 1rem; color: var(--white);
      overflow: hidden; cursor: pointer; text-decoration: none;
      transition: transform 0.15s, box-shadow 0.2s;
    }
    .nav-avatar:hover { transform: scale(1.07); box-shadow: 0 0 0 3px rgba(244,123,32,0.3); }
    .nav-avatar img { width: 100%; height: 100%; object-fit: cover; }

    /* ══════════════ PAGE LAYOUT ══════════════ */
    .page-body {
      max-width: 100%;
      margin: 0 auto;
      padding: 36px 40px 80px;
      display: grid;
      grid-template-columns: 280px 1fr 280px;
      gap: 28px;
      align-items: start;
    }

    /* ══════════════ SIDEBAR ══════════════ */
    .sidebar { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 86px; }

    .widget {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      padding: 24px 26px;
      box-shadow: var(--shadow);
      width: 100%;
    }
    .widget-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.05rem; letter-spacing: 2px;
      color: var(--orange); margin-bottom: 14px;
      padding-bottom: 8px;
      border-bottom: 1.5px solid var(--orange-light);
    }
    .widget-empty { font-size: 13px; color: var(--text-muted); text-align: center; padding: 8px 0; }

    /* user widget */
    .user-widget { padding: 0; overflow: hidden; }
    .user-widget-bg {
      height: 90px;
      background: linear-gradient(135deg, var(--orange), var(--orange-mid));
    }
    .user-widget-body { padding: 0 16px 18px; text-align: center; }
    .uw-avatar {
      width: 76px; height: 76px; border-radius: 50%;
      border: 3px solid var(--white);
      background: linear-gradient(135deg, var(--orange), var(--orange-mid));
      display: flex; align-items: center; justify-content: center;
      margin: -38px auto 14px;
      font-family: 'Bebas Neue', sans-serif; font-size: 1.5rem; color: var(--white);
      overflow: hidden; box-shadow: 0 4px 14px rgba(244,123,32,0.35);
    }
    .uw-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .uw-name { font-weight: 700; font-size: 1rem; margin-bottom: 12px; color: var(--text); }
    .uw-btn {
      display: inline-block; background: var(--orange); color: var(--white);
      padding: 8px 24px; border-radius: 50px; font-size: 0.85rem; font-weight: 600;
      text-decoration: none; letter-spacing: 0.5px;
      transition: background 0.2s, transform 0.15s;
    }
    .uw-btn:hover { background: var(--orange-dark); transform: translateY(-1px); }

    /* quick nav */
    .quick-nav { display: flex; flex-direction: column; gap: 4px; }
    .qn-item {
      display: flex; align-items: center; gap: 10px;
      padding: 13px 16px; border-radius: 10px;
      text-decoration: none; color: var(--text);
      font-size: 0.95rem; font-weight: 500;
      transition: background 0.18s, color 0.18s;
    }
    .qn-item:hover { background: var(--orange-light); color: var(--orange); }
    .qn-item.active { background: var(--orange-light); color: var(--orange); font-weight: 700; }
    .qn-icon { font-size: 1.1rem; width: 22px; text-align: center; }

    /* trending */
    .trending-list { display: flex; flex-direction: column; gap: 14px; }
    .trend-item { display: flex; align-items: center; gap: 10px; }
    .trend-rank {
      width: 32px; height: 32px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Bebas Neue', sans-serif; font-size: 0.8rem; flex-shrink: 0;
    }
    .trend-info { flex: 1; min-width: 0; }
    .trend-name { font-size: 0.85rem; font-weight: 600; display: block; margin-bottom: 4px;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .trend-bar-wrap { height: 4px; background: #f0e0d0; border-radius: 4px; overflow: hidden; }
    .trend-bar { height: 100%; border-radius: 4px; transition: width 0.6s ease; }
    .trend-count { font-size: 0.82rem; font-weight: 700; color: var(--text-muted); flex-shrink: 0; }

    /* promo widget */
    .widget-promo {
      background: linear-gradient(135deg, var(--orange), var(--orange-mid)) !important;
      border-color: transparent !important;
      display: flex; flex-direction: column; align-items: center;
      text-align: center; gap: 8px; padding: 20px 16px !important;
    }
    .promo-icon { font-size: 2rem; }
    .promo-text { display: flex; flex-direction: column; gap: 2px; }
    .promo-text strong { font-size: 0.9rem; font-weight: 700; color: var(--white); }
    .promo-text span { font-size: 0.78rem; color: rgba(255,255,255,0.8); }
    .promo-btn {
      display: inline-block; background: var(--white); color: var(--orange);
      padding: 7px 18px; border-radius: 50px; font-size: 0.8rem; font-weight: 700;
      text-decoration: none; margin-top: 4px;
      transition: transform 0.15s, box-shadow 0.2s;
    }
    .promo-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    /* ══════════════ FEED MAIN ══════════════ */
    .feed-main { display: flex; flex-direction: column; gap: 22px; min-width: 0; }

    .feed-header { margin-bottom: 4px; }
    .feed-header h1 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(2.2rem, 4vw, 3.5rem);
      letter-spacing: 3px; color: var(--orange); line-height: 1;
    }
    .feed-subtitle { font-size: 0.95rem; color: var(--text-muted); margin-top: 5px; }

    /* compose card */
    .compose-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      padding: 28px;
      box-shadow: var(--shadow);
      transition: box-shadow 0.2s;
    }
    .compose-card:focus-within { box-shadow: var(--shadow-hover); border-color: var(--orange-mid); }

    .compose-top { display: flex; gap: 12px; align-items: flex-start; }
    .compose-avatar-wrap .avatar {
      width: 46px; height: 46px; border-radius: 50%;
      background: linear-gradient(135deg, var(--orange), var(--orange-mid));
      display: flex; align-items: center; justify-content: center;
      font-family: 'Bebas Neue', sans-serif; font-size: 1rem; color: var(--white);
      overflow: hidden; flex-shrink: 0;
    }
    .compose-avatar-wrap .avatar img { width: 100%; height: 100%; object-fit: cover; }

    .compose-top textarea {
      flex: 1; border: 1.5px solid var(--border); border-radius: 10px;
      padding: 10px 14px; font-family: 'DM Sans', sans-serif; font-size: 0.92rem;
      color: var(--text); background: #fdfaf8; resize: none;
      outline: none; transition: border-color 0.2s, box-shadow 0.2s;
      min-height: 58px;
    }
    .compose-top textarea:focus {
      border-color: var(--orange);
      box-shadow: 0 0 0 3px rgba(244,123,32,0.12);
      background: var(--white);
    }
    .compose-top textarea::placeholder { color: #c0a090; }

    .compose-toolbar {
      margin-top: 12px; padding-top: 12px;
      border-top: 1.5px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 8px;
    }
    .compose-attachments { display: flex; gap: 6px; }
    .attach-btn {
      display: flex; align-items: center; gap: 5px;
      background: var(--orange-light); border: 1.5px solid var(--border);
      border-radius: 8px; padding: 6px 12px;
      font-size: 0.78rem; font-weight: 600; color: var(--orange-dark);
      cursor: pointer; transition: background 0.18s;
    }
    .attach-btn:hover { background: #ffe0c8; }

    .compose-right { display: flex; align-items: center; gap: 8px; }
    .compose-right select {
      appearance: none; border: 1.5px solid var(--border); border-radius: 8px;
      padding: 7px 32px 7px 12px; font-family: 'DM Sans', sans-serif;
      font-size: 0.8rem; color: var(--text); background: #fdfaf8;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23F47B20' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 10px center;
      cursor: pointer; outline: none;
      transition: border-color 0.2s;
    }
    .compose-right select:focus { border-color: var(--orange); }

    .btn-ghost {
      background: #f5f5f5; border: 1.5px solid var(--border-soft);
      border-radius: 8px; padding: 7px 14px;
      font-size: 0.8rem; font-weight: 600; color: #666;
      cursor: pointer; font-family: 'DM Sans', sans-serif;
      transition: background 0.18s;
    }
    .btn-ghost:hover { background: #ebebeb; }

    .btn-primary {
      display: flex; align-items: center; gap: 6px;
      background: var(--orange); color: var(--white);
      border: none; border-radius: 8px; padding: 7px 18px;
      font-family: 'Bebas Neue', sans-serif; font-size: 0.95rem; letter-spacing: 1.5px;
      cursor: pointer; transition: background 0.2s, transform 0.15s;
      box-shadow: 0 3px 10px rgba(244,123,32,0.3);
    }
    .btn-primary:hover { background: var(--orange-dark); transform: translateY(-1px); }
    .btn-primary:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }

    /* ── filter tabs ── */
    .filter-scroll { overflow-x: auto; padding-bottom: 2px; }
    .filter-scroll::-webkit-scrollbar { height: 3px; }
    .filter-scroll::-webkit-scrollbar-thumb { background: var(--orange-mid); border-radius: 3px; }

    .filter-tabs {
      display: flex; gap: 6px;
      padding: 2px 2px 6px;
      white-space: nowrap;
    }
    .filter-tab {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 10px 22px; border-radius: 50px;
      border: 1.5px solid var(--border);
      background: var(--white); color: var(--text-muted);
      font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 600;
      letter-spacing: 0.5px; cursor: pointer;
      transition: all 0.18s;
    }
    .filter-tab:hover { border-color: var(--orange); color: var(--orange); background: var(--orange-light); }
    .filter-tab.active {
      background: var(--orange); border-color: var(--orange);
      color: var(--white); box-shadow: 0 3px 10px rgba(244,123,32,0.3);
    }
    .tab-icon { font-size: 0.85rem; }

    /* ══════════════ POST CARD ══════════════ */
    .post-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
      animation: slideUp 0.3s ease both;
    }
    .post-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-hover); border-color: var(--orange-mid); }

    @keyframes slideUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }

    /* colored top stripe */
    .post-card::before {
      content: '';
      display: block; height: 3px;
      background: linear-gradient(90deg, var(--orange), var(--orange-mid));
    }

    .post-header {
      display: flex; align-items: flex-start; justify-content: space-between;
      padding: 20px 24px 12px;
    }
    .post-author { display: flex; align-items: center; gap: 11px; }
    .post-avatar-slot { flex-shrink: 0; }

    /* avatar helpers used by JS */
    .avatar-sm {
      width: 46px; height: 46px; border-radius: 50%;
      background: linear-gradient(135deg, var(--orange), var(--orange-mid));
      display: flex; align-items: center; justify-content: center;
      font-family: 'Bebas Neue', sans-serif; font-size: 1rem; color: var(--white);
      overflow: hidden; flex-shrink: 0; border: 2px solid var(--border);
    }
    .avatar-sm img { width: 100%; height: 100%; object-fit: cover; }
    .avatar-xs {
      width: 36px; height: 36px; border-radius: 50%;
      background: linear-gradient(135deg, var(--orange), var(--orange-mid));
      display: flex; align-items: center; justify-content: center;
      font-family: 'Bebas Neue', sans-serif; font-size: 0.9rem; color: var(--white);
      overflow: hidden; flex-shrink: 0;
    }
    .avatar-xs img { width: 100%; height: 100%; object-fit: cover; }
    .avatar-md {
      width: 46px; height: 46px; border-radius: 50%;
      background: linear-gradient(135deg, var(--orange), var(--orange-mid));
      display: flex; align-items: center; justify-content: center;
      font-family: 'Bebas Neue', sans-serif; font-size: 1rem; color: var(--white);
      overflow: hidden; flex-shrink: 0;
    }
    .avatar-md img { width: 100%; height: 100%; object-fit: cover; }

    .author-info { display: flex; flex-direction: column; gap: 2px; }
    .author-name-row { display: flex; align-items: center; gap: 7px; }
    .author-name { font-weight: 700; font-size: 0.95rem; color: var(--text); }
    .own-badge {
      background: var(--orange-light); color: var(--orange-dark);
      font-size: 0.65rem; font-weight: 700; letter-spacing: 0.8px;
      padding: 1px 7px; border-radius: 50px; border: 1px solid var(--orange-mid);
    }
    .author-meta { display: flex; align-items: center; gap: 5px; font-size: 0.75rem; color: var(--text-muted); }
    .meta-dot { opacity: 0.4; }
    .category-badge {
      font-size: 0.68rem; font-weight: 700; letter-spacing: 0.6px;
      text-transform: uppercase;
      background: var(--orange-light); color: var(--orange-dark);
      padding: 1px 7px; border-radius: 50px;
    }

    .post-header-right { display: flex; gap: 6px; align-items: center; }
    .delete-btn {
      background: none; border: 1.5px solid #ffd0d0; border-radius: 7px;
      width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: #e05252; transition: all 0.18s;
    }
    .delete-btn:hover { background: #e05252; color: var(--white); border-color: #e05252; }

    .post-content {
      padding: 8px 24px 20px;
      font-size: 0.97rem; line-height: 1.65; color: var(--text);
    }

    .post-media-grid {
      display: grid; gap: 3px;
      margin: 0 0 2px;
    }
    .post-media-grid.single img,
    .post-media-grid.single video { width: 100%; max-height: 600px; object-fit: cover; }
    .post-media-grid.double { grid-template-columns: 1fr 1fr; }
    .post-media-grid.double img { width: 100%; height: 220px; object-fit: cover; }
    .post-media-grid img,
    .post-media-grid video { border-radius: 0; cursor: pointer; transition: opacity 0.15s; }
    .post-media-grid img:hover { opacity: 0.92; }

    /* actions bar */
    .post-actions {
      display: flex; align-items: center; gap: 4px;
      padding: 14px 20px;
      border-top: 1.5px solid var(--border);
      background: #fdfaf8;
    }
    .action-btn {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 9px 16px; border-radius: 9px;
      border: none; background: none;
      font-family: 'DM Sans', sans-serif; font-size: 0.88rem; font-weight: 600;
      color: var(--text-muted); cursor: pointer;
      transition: background 0.18s, color 0.18s;
    }
    .action-btn:hover { background: var(--orange-light); color: var(--orange); }
    .action-btn.liked { color: var(--orange); }
    .action-btn.liked svg path { fill: var(--orange); stroke: var(--orange); }

    .reaction-chips { display: flex; gap: 4px; flex-wrap: wrap; margin-left: 4px; }
    .reaction-chip {
      background: var(--orange-light); border: 1px solid var(--border);
      border-radius: 50px; padding: 2px 9px;
      font-size: 0.75rem; display: flex; align-items: center; gap: 4px;
    }

    /* comments */
    .comments-section { border-top: 1.5px solid var(--border); background: #fdfaf8; }
    .comments-list { padding: 12px 16px; display: flex; flex-direction: column; gap: 10px; }
    .comment-item { display: flex; gap: 9px; align-items: flex-start; }
    .comment-bubble {
      background: var(--white); border: 1.5px solid var(--border);
      border-radius: 12px 12px 12px 4px;
      padding: 8px 13px; flex: 1;
    }
    .comment-author { font-size: 0.78rem; font-weight: 700; color: var(--orange-dark); margin-bottom: 2px; }
    .comment-text   { font-size: 0.85rem; color: var(--text); line-height: 1.5; }
    .comment-time   { font-size: 0.7rem; color: var(--text-muted); margin-top: 3px; }

    .comment-input-row {
      display: flex; gap: 9px; align-items: center;
      padding: 10px 16px; border-top: 1.5px solid var(--border);
    }
    .comment-box {
      flex: 1; background: var(--white); border: 1.5px solid var(--border);
      border-radius: 50px; display: flex; align-items: center;
      padding: 0 6px 0 14px; transition: border-color 0.2s;
    }
    .comment-box:focus-within { border-color: var(--orange); }
    .comment-input {
      flex: 1; border: none; background: none; outline: none;
      font-family: 'DM Sans', sans-serif; font-size: 0.85rem; color: var(--text);
      padding: 8px 0;
    }
    .comment-input::placeholder { color: #c0a090; }
    .comment-send {
      background: var(--orange); border: none; border-radius: 50%;
      width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
      cursor: pointer; flex-shrink: 0;
      transition: background 0.2s, transform 0.15s;
    }
    .comment-send:hover { background: var(--orange-dark); transform: scale(1.08); }

    /* ── reaction picker ── */
    .reaction-picker {
      position: fixed; z-index: 500;
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: 50px;
      box-shadow: 0 8px 28px rgba(244,123,32,0.2);
      padding: 6px 10px;
      display: none; gap: 6px;
    }
    .reaction-picker.visible { display: flex; }
    .reaction-picker button {
      background: none; border: none; font-size: 1.4rem; cursor: pointer;
      border-radius: 50%; width: 38px; height: 38px;
      display: flex; align-items: center; justify-content: center;
      transition: background 0.15s, transform 0.15s;
    }
    .reaction-picker button:hover { background: var(--orange-light); transform: scale(1.25); }

    /* loading */
    .loading-wrap { display: flex; justify-content: center; padding: 40px; }
    .spinner {
      width: 36px; height: 36px; border-radius: 50%;
      border: 3px solid var(--border);
      border-top-color: var(--orange);
      animation: spin 0.7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ══════════════ POST MODAL ══════════════ */
    #postModal { font-family: 'DM Sans', sans-serif; }
    #postModal > div {
      border-radius: 16px !important;
      border: 1.5px solid var(--border) !important;
    }
    #pmLikeBtn:hover { background: var(--orange-light) !important; color: var(--orange) !important; }
    #pmLikeBtn.liked { color: var(--orange) !important; }

    .pm-nav {
      position: absolute; top: 50%; transform: translateY(-50%);
      background: rgba(244,123,32,0.75); border: none; color: var(--white);
      width: 40px; height: 40px; border-radius: 50%; font-size: 22px;
      cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 5;
    }
    .pm-nav:hover { background: var(--orange); }
    .pm-prev { left: 10px; } .pm-next { right: 10px; }
    .pm-counter {
      position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%);
      background: rgba(244,123,32,0.75); color: var(--white);
      font-size: 12px; padding: 3px 10px; border-radius: 12px;
    }

    /* ══════════════ RESPONSIVE ══════════════ */
    @media (max-width: 1280px) {
      .page-body { grid-template-columns: 250px 1fr 250px; gap: 24px; padding: 36px 28px 80px; }
    }
    @media (max-width: 1000px) {
      .page-body { grid-template-columns: 240px 1fr; gap: 20px; padding: 28px 20px 60px; }
      .sidebar-right { display: none; }
    }
    @media (max-width: 680px) {
      .page-body { grid-template-columns: 1fr; padding: 16px 12px 60px; }
      .sidebar-left { display: none; }
      .navbar { padding: 0 16px; }
    }
  </style>
</head>
<body>

<nav class="navbar">
  <span class="brand-text">TOURNAMEET</span>
  <div class="nav-actions">
    <a href="/Tourna/TF/Chats/chat.php">
      <button class="nav-icon-btn" title="Messages">
        <svg viewBox="0 0 24 24" fill="none" width="18" height="18" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 8l9 6 9-6" stroke-linecap="round"/></svg>
      </button>
    </a>
    <a href="/Tourna/notifications.php">
      <button class="nav-icon-btn" title="Notifications">
        <svg viewBox="0 0 24 24" fill="none" width="18" height="18" stroke="currentColor" stroke-width="1.8"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
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

  <!-- LEFT SIDEBAR -->
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
        <a href="newsfeed.php"  class="qn-item active"><span class="qn-icon">📰</span> Newsfeed</a>
        <a href="/Tourna/Tournafinal/Tournameet/index.php" class="qn-item"><span class="qn-icon">🏆</span> Tournaments</a>
        <a href="/Tourna/TF/teams_friends.php" class="qn-item"><span class="qn-icon">👥</span> Teams & Friends</a>
        <a href="/Tourna/Shop/index.php" class="qn-item"><span class="qn-icon">🏪</span> Shop</a>
      </nav>
    </div>

    <!-- Community stats -->
    <div class="widget">
      <div class="widget-title">Community</div>
      <div style="display:flex;flex-direction:column;gap:14px;">
        <?php foreach ([['🗒️','Total Posts',$totalPosts],['👥','Members',$totalMembers],['✨','Today\'s Posts',$todayPosts]] as [$icon,$label,$val]): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;">
          <span style="font-size:0.88rem;color:var(--text-muted);display:flex;align-items:center;gap:7px;"><?=$icon?> <?=$label?></span>
          <span style="font-family:'Bebas Neue',sans-serif;font-size:1.25rem;color:var(--orange);"><?=$val?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>

  <!-- MAIN FEED -->
  <main class="feed-main">
    <header class="feed-header">
      <h1>Newsfeed</h1>
      <p class="feed-subtitle">Updates from your tournament community</p>
    </header>

    <!-- Compose -->
    <div class="compose-card">
      <div class="compose-top">
        <div class="compose-avatar-wrap">
          <?php if ($picPath): ?>
            <div class="avatar avatar-md has-photo"><img src="<?= htmlspecialchars($picPath) ?>" alt="Me"/></div>
          <?php else: ?>
            <div class="avatar avatar-md"><?= htmlspecialchars($initials) ?></div>
          <?php endif; ?>
        </div>
        <textarea id="postContent" placeholder="What's happening in your tournament world? 🏆" rows="1"></textarea>
      </div>
      <div class="compose-toolbar" id="composeActions" style="display:none">
        <div class="compose-attachments">
          <button class="attach-btn">
            <svg viewBox="0 0 24 24" fill="none" width="14" height="14" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><path d="M21 15l-5-5L5 21" stroke-linecap="round"/></svg>
            Photo
          </button>
          <button class="attach-btn">
            <svg viewBox="0 0 24 24" fill="none" width="14" height="14" stroke="currentColor" stroke-width="1.8"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7" stroke-width="2.5" stroke-linecap="round"/></svg>
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
            <svg viewBox="0 0 24 24" fill="none" width="12" height="12"><path d="M22 2L11 13" stroke="white" stroke-width="2.2" stroke-linecap="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            POST
          </button>
        </div>
      </div>
    </div>

    <!-- Filter tabs -->
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

    <!-- Posts -->
    <div id="postsFeed">
      <div class="loading-wrap"><div class="spinner"></div></div>
    </div>
  </main>

  <!-- RIGHT SIDEBAR -->
  <aside class="sidebar sidebar-right">
    <div class="widget">
      <div class="widget-title">🔥 Trending</div>
      <div class="trending-list">
        <?php if (empty($trending)): ?>
          <p class="widget-empty">Start posting to see trends!</p>
        <?php else:
          $max = max(array_column($trending,'cnt')) ?: 1;
          foreach ($trending as $i => $t):
            $col = $catColors[$t['category']] ?? '#F47B20';
            $pct = round(($t['cnt']/$max)*100);
        ?>
          <div class="trend-item">
            <div class="trend-rank" style="background:<?=$col?>18;color:<?=$col?>"><?=$i+1?></div>
            <div class="trend-info">
              <span class="trend-name"><?= htmlspecialchars($t['category']) ?></span>
              <div class="trend-bar-wrap"><div class="trend-bar" style="width:<?=$pct?>%;background:<?=$col?>"></div></div>
            </div>
            <span class="trend-count"><?=$t['cnt']?></span>
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

    <?php if (!empty($members)): ?>
    <div class="widget">
      <div class="widget-title">👑 Top Members</div>
      <div style="display:flex;flex-direction:column;gap:14px;">
        <?php foreach ($members as $m):
          $mPic = !empty($m['profile_pic']) ? '../uploads'.$m['profile_pic'] : null;
          $mInit = strtoupper(substr($m['username'],0,2));
        ?>
        <div style="display:flex;align-items:center;gap:9px;">
          <div class="avatar-xs" style="background:linear-gradient(135deg,var(--orange),var(--orange-mid));">
            <?php if($mPic): ?><img src="<?=htmlspecialchars($mPic)?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
            <?php else: ?><span style="font-family:'Bebas Neue',sans-serif;color:#fff;font-size:0.75rem;"><?=$mInit?></span><?php endif; ?>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:0.9rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($m['username'])?></div>
            <div style="font-size:0.78rem;color:var(--text-muted);"><?=$m['post_count']?> posts</div>
          </div>
          <span style="font-family:'Bebas Neue',sans-serif;font-size:1rem;color:var(--orange);">#<?=array_search($m,$members)+1?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </aside>
</div>

<!-- REACTION PICKER -->
<div class="reaction-picker" id="reactionPicker">
  <button data-emoji="🔥">🔥</button>
  <button data-emoji="👏">👏</button>
  <button data-emoji="🏆">🏆</button>
  <button data-emoji="❤️">❤️</button>
  <button data-emoji="😮">😮</button>
</div>

<!-- POST TEMPLATE -->
<template id="postTemplate">
  <article class="post-card" data-post-id="">
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
          <svg viewBox="0 0 24 24" fill="none" width="13" height="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>
          </svg>
        </button>
      </div>
    </div>
    <p class="post-content"></p>
    <div class="post-media-grid" style="display:none"></div>
    <div class="post-actions">
      <button class="action-btn btn-like">
        <svg viewBox="0 0 24 24" fill="none" width="14" height="14" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linejoin="round"/></svg>
        <span class="like-count">0</span>
      </button>
      <button class="action-btn btn-comment-toggle">
        <svg viewBox="0 0 24 24" fill="none" width="14" height="14" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke-linejoin="round"/></svg>
        <span class="comment-count">0</span>
      </button>
      <button class="action-btn btn-react" style="display:none">
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
            <svg viewBox="0 0 24 24" fill="none" width="12" height="12"><path d="M22 2L11 13" stroke="white" stroke-width="2.5" stroke-linecap="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
      </div>
    </div>
  </article>
</template>

<!-- POST FULL VIEW MODAL -->
<div id="postModal" onclick="if(event.target===this)closePostModal()" style="display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,0.8);align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:16px;width:100%;max-width:960px;max-height:92vh;display:flex;overflow:hidden;position:relative;box-shadow:0 24px 80px rgba(244,123,32,0.3);">
    <button onclick="closePostModal()" style="position:absolute;top:12px;right:12px;z-index:10;background:rgba(244,123,32,0.85);border:none;color:#fff;width:32px;height:32px;border-radius:50%;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;">✕</button>
    <div id="pmMediaWrap" style="flex:1;background:#1a1a1a;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;min-height:400px;"></div>
    <div style="width:360px;min-width:300px;display:flex;flex-direction:column;max-height:92vh;border-left:1.5px solid #f0e0d0;">
      <div style="padding:14px 16px;border-bottom:1.5px solid #f0e0d0;display:flex;align-items:center;gap:10px;flex-shrink:0;background:#fff8f3;">
        <div id="pmAvatar"></div>
        <div>
          <div id="pmUsername" style="font-weight:700;font-size:14px;color:#1a1a1a;"></div>
          <div id="pmTime" style="font-size:12px;color:#999;margin-top:1px;"></div>
        </div>
      </div>
      <div id="pmCaption" style="padding:12px 16px;font-size:14px;color:#444;line-height:1.6;border-bottom:1.5px solid #f0e0d0;max-height:100px;overflow-y:auto;flex-shrink:0;"></div>
      <div style="padding:8px 16px;border-bottom:1.5px solid #f0e0d0;display:flex;gap:16px;font-size:13px;color:#888;flex-shrink:0;">
        <span>❤️ <span id="pmLikeCnt">0</span> likes</span>
        <span>💬 <span id="pmCommentCnt">0</span> comments</span>
      </div>
      <div style="padding:4px 16px;border-bottom:1.5px solid #f0e0d0;display:flex;gap:4px;flex-shrink:0;background:#fff8f3;">
        <button id="pmLikeBtn" style="flex:1;padding:8px;border:none;border-radius:8px;background:none;cursor:pointer;font-size:13px;font-weight:600;color:#888;display:flex;align-items:center;justify-content:center;gap:6px;font-family:'DM Sans',sans-serif;transition:background 0.18s,color 0.18s;">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          Like
        </button>
        <button onclick="document.getElementById('pmCommentInput').focus()" style="flex:1;padding:8px;border:none;border-radius:8px;background:none;cursor:pointer;font-size:13px;font-weight:600;color:#888;display:flex;align-items:center;justify-content:center;gap:6px;font-family:'DM Sans',sans-serif;">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Comment
        </button>
      </div>
      <div id="pmCommentList" style="flex:1;overflow-y:auto;padding:12px 16px;"></div>
      <div style="padding:10px 16px;border-top:1.5px solid #f0e0d0;display:flex;gap:8px;align-items:center;flex-shrink:0;background:#fff8f3;">
        <div id="pmCommentAv" style="flex-shrink:0;"></div>
        <div style="flex:1;background:#fff;border:1.5px solid #f0e0d0;border-radius:50px;display:flex;align-items:center;padding:0 6px 0 14px;transition:border-color 0.2s;">
          <input id="pmCommentInput" type="text" placeholder="Write a comment…" maxlength="500"
            style="flex:1;border:none;background:none;padding:10px 0;font-size:14px;outline:none;font-family:'DM Sans',sans-serif;color:#1a1a1a;"
            onkeydown="if(event.key==='Enter')submitModalComment()"/>
          <button onclick="submitModalComment()" style="background:var(--orange);border:none;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
            <svg viewBox="0 0 24 24" fill="none" width="14" height="14"><path d="M22 2L11 13" stroke="white" stroke-width="2.2" stroke-linecap="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

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