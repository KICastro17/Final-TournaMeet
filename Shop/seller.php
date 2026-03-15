<?php
session_start();
// Simple seller session — in production, tie this to your users table
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sell on TournaMeet</title>
    <link rel="icon" type="image/png" href="/Tourna/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --orange: #F47B20;
      --orange-dark: #D96210;
      --orange-light: #FFF0E6;
      --orange-mid: #fdb97d;
      --white: #FFFFFF;
      --navy: #1a1a2e;
      --gray: #7a8fa6;
      --gray-lt: #9aa5bf;
      --bdr: #e0e8f0;
      --bg: #f4f6fb;
      --green: #27AE60;
      --red: #E74C3C;
      --shadow: 0 2px 16px rgba(244,123,32,0.13);
    }
    body { background: var(--bg); font-family: 'DM Sans', sans-serif; min-height: 100vh; color: var(--navy); }

    /* ── NAV ── */
    nav {
      position: sticky; top: 0; z-index: 200;
      background: var(--white); border-bottom: 2px solid var(--orange);
      box-shadow: var(--shadow); height: 64px;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 32px; gap: 12px;
    }
    .nav-left { display: flex; align-items: center; gap: 10px; }
    .brand { font-family: 'Bebas Neue', sans-serif; font-size: 1.7rem; letter-spacing: 2.5px; color: var(--orange); }
    .nav-right { display: flex; align-items: center; gap: 10px; }
    .nav-tag { background: var(--orange-light); border: 1.5px solid var(--orange); color: var(--orange); font-size: 0.75rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; letter-spacing: 0.5px; }
    .btn-back-nav { display: flex; align-items: center; gap: 6px; background: none; border: 1.5px solid var(--bdr); color: var(--gray); font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 600; padding: 7px 14px; border-radius: 8px; cursor: pointer; transition: all .2s; text-decoration: none; }
    .btn-back-nav:hover { border-color: var(--orange); color: var(--orange); }

    /* ── SELLER LOGIN ── */
    #seller-login {
      min-height: calc(100vh - 64px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;
    }
    .login-card {
      background: var(--white); border: 1.5px solid var(--bdr); border-radius: 20px;
      padding: 48px 40px; max-width: 440px; width: 100%; text-align: center;
      box-shadow: 0 4px 32px rgba(244,123,32,0.10);
    }
    .login-icon { width: 72px; height: 72px; background: var(--orange-light); border: 2px solid var(--orange); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 22px; }
    .login-icon svg { stroke: var(--orange); }
    .login-title { font-family: 'Bebas Neue', sans-serif; font-size: 2rem; letter-spacing: 2px; color: var(--navy); margin-bottom: 6px; }
    .login-sub { font-size: 0.88rem; color: var(--gray); margin-bottom: 30px; line-height: 1.6; }
    .form-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; text-align: left; }
    .form-field label { font-size: 0.72rem; font-weight: 700; letter-spacing: 1.5px; color: var(--gray-lt); text-transform: uppercase; }
    .form-field input { background: var(--bg); border: 1.5px solid var(--bdr); color: var(--navy); font-family: 'DM Sans', sans-serif; font-size: 0.9rem; padding: 11px 14px; border-radius: 8px; outline: none; transition: border-color .2s, box-shadow .2s; }
    .form-field input:focus { border-color: var(--orange); box-shadow: 0 0 0 3px rgba(244,123,32,.12); }
    .btn-primary { background: var(--orange); color: #fff; border: none; font-family: 'Bebas Neue', sans-serif; font-size: 1rem; letter-spacing: 1.5px; padding: 13px 28px; border-radius: 10px; cursor: pointer; width: 100%; transition: all .2s; margin-top: 8px; }
    .btn-primary:hover { background: var(--orange-dark); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(244,123,32,.3); }
    .login-divider { margin: 20px 0; font-size: 0.78rem; color: var(--gray-lt); display: flex; align-items: center; gap: 10px; }
    .login-divider::before, .login-divider::after { content:''; flex:1; height:1px; background: var(--bdr); }
    .perks { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 24px; }
    .perk { background: var(--bg); border: 1px solid var(--bdr); border-radius: 10px; padding: 12px; text-align: left; }
    .perk-icon { font-size: 1.2rem; margin-bottom: 5px; }
    .perk-title { font-size: 0.78rem; font-weight: 700; color: var(--navy); margin-bottom: 2px; }
    .perk-sub { font-size: 0.7rem; color: var(--gray); }

    /* ── DASHBOARD ── */
    #seller-dash { display: none; }
    .dash-wrap { max-width: 1100px; margin: 0 auto; padding: 36px 24px 80px; }
    .dash-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 14px; }
    .dash-title { font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem; letter-spacing: 2px; color: var(--orange); }
    .dash-subtitle { font-size: 0.85rem; color: var(--gray); margin-top: 2px; }
    .btn-new-listing { background: var(--orange); color: #fff; border: none; font-family: 'Bebas Neue', sans-serif; font-size: 0.95rem; letter-spacing: 1.5px; padding: 12px 24px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all .2s; }
    .btn-new-listing:hover { background: var(--orange-dark); transform: translateY(-2px); box-shadow: 0 6px 18px rgba(244,123,32,.3); }

    /* Stats row */
    .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
    .stat-card { background: var(--white); border: 1.5px solid var(--bdr); border-radius: 14px; padding: 20px; border-top: 3px solid var(--orange); transition: transform .2s, box-shadow .2s; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
    .stat-val { font-family: 'Bebas Neue', sans-serif; font-size: 2rem; color: var(--orange); line-height: 1; margin-bottom: 4px; }
    .stat-lbl { font-size: 0.72rem; font-weight: 700; letter-spacing: 1.5px; color: var(--gray-lt); text-transform: uppercase; }

    /* Tabs */
    .tabs { display: flex; gap: 4px; margin-bottom: 20px; background: var(--white); border: 1.5px solid var(--bdr); border-radius: 10px; padding: 4px; width: fit-content; }
    .tab { background: none; border: none; font-family: 'DM Sans', sans-serif; font-size: 0.85rem; font-weight: 600; color: var(--gray); padding: 8px 20px; border-radius: 7px; cursor: pointer; transition: all .2s; }
    .tab.active { background: var(--orange); color: var(--white); }

    /* Listings table */
    .listings-wrap { background: var(--white); border: 1.5px solid var(--bdr); border-radius: 16px; overflow: hidden; }
    .listings-toolbar { padding: 16px 20px; border-bottom: 1px solid var(--bdr); display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .search-inp { background: var(--bg); border: 1.5px solid var(--bdr); color: var(--navy); font-family: 'DM Sans', sans-serif; font-size: 0.85rem; padding: 9px 14px; border-radius: 8px; outline: none; width: 240px; transition: border-color .2s; }
    .search-inp:focus { border-color: var(--orange); }
    .filter-sel { background: var(--white); border: 1.5px solid var(--bdr); color: var(--navy); font-family: 'DM Sans', sans-serif; font-size: 0.83rem; padding: 9px 12px; border-radius: 8px; outline: none; cursor: pointer; }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: var(--bg); }
    th { font-size: 0.7rem; font-weight: 700; letter-spacing: 1.5px; color: var(--gray-lt); text-transform: uppercase; padding: 12px 18px; text-align: left; border-bottom: 1.5px solid var(--bdr); }
    td { padding: 14px 18px; border-bottom: 1px solid #f0f4f8; vertical-align: middle; font-size: 0.88rem; color: var(--navy); }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #fafbfc; }
    .prod-cell { display: flex; align-items: center; gap: 12px; }
    .prod-icon { width: 42px; height: 42px; background: var(--orange-light); border: 1px solid var(--bdr); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .prod-icon svg { stroke: var(--orange); }
    .prod-name { font-weight: 700; font-size: 0.9rem; color: var(--navy); }
    .prod-cat { font-size: 0.72rem; color: var(--orange); font-weight: 600; letter-spacing: 0.5px; }
    .price-cell { font-family: 'Bebas Neue', sans-serif; font-size: 1.15rem; color: var(--orange); }
    .badge { display: inline-flex; align-items: center; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 20px; }
    .badge.active { background: #e8f5e9; color: #1b5e20; }
    .badge.draft { background: #f4f6fb; color: var(--gray); border: 1px solid var(--bdr); }
    .badge.sold-out { background: #ffebee; color: #c62828; }
    .badge.pending { background: #fff3e0; color: #e65100; }
    .action-btns { display: flex; gap: 6px; }
    .btn-edit { background: none; border: 1.5px solid var(--bdr); color: var(--gray); font-size: 0.75rem; font-weight: 600; padding: 5px 12px; border-radius: 6px; cursor: pointer; transition: all .2s; }
    .btn-edit:hover { border-color: var(--orange); color: var(--orange); }
    .btn-delete { background: none; border: 1.5px solid var(--bdr); color: var(--gray); font-size: 0.75rem; font-weight: 600; padding: 5px 12px; border-radius: 6px; cursor: pointer; transition: all .2s; }
    .btn-delete:hover { border-color: var(--red); color: var(--red); }
    .btn-toggle { background: none; border: 1.5px solid var(--bdr); color: var(--gray); font-size: 0.75rem; font-weight: 600; padding: 5px 12px; border-radius: 6px; cursor: pointer; transition: all .2s; }
    .btn-toggle:hover { border-color: var(--green); color: var(--green); }
    .empty-state { text-align: center; padding: 64px 24px; }
    .empty-state svg { margin-bottom: 16px; opacity: 0.3; }
    .empty-state h3 { font-family: 'Bebas Neue', sans-serif; font-size: 1.4rem; letter-spacing: 1px; color: var(--navy); margin-bottom: 6px; }
    .empty-state p { font-size: 0.85rem; color: var(--gray); }

    /* Orders tab content */
    .orders-list { padding: 8px; }
    .order-card { background: var(--bg); border: 1.5px solid var(--bdr); border-radius: 12px; padding: 16px 20px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; transition: box-shadow .2s; }
    .order-card:hover { box-shadow: var(--shadow); }
    .order-ref { font-family: 'Bebas Neue', sans-serif; font-size: 1rem; color: var(--orange); letter-spacing: 1px; }
    .order-buyer { font-size: 0.82rem; color: var(--gray); margin-top: 2px; }
    .order-items { font-size: 0.85rem; font-weight: 600; color: var(--navy); }
    .order-total { font-family: 'Bebas Neue', sans-serif; font-size: 1.3rem; color: var(--orange); }
    .order-status { display: inline-flex; align-items: center; gap: 6px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px; padding: 5px 12px; border-radius: 20px; }
    .s-placed { background: #e8f0fe; color: #1a73e8; }
    .s-confirmed { background: #fff0e6; color: var(--orange); }
    .s-shipped { background: #e8f5e9; color: #2e7d32; }
    .s-delivered { background: #e8f5e9; color: #1b5e20; }
    .s-cancelled { background: #ffebee; color: #c62828; }

    /* ── MODAL (Add/Edit Listing) ── */
    .modal-overlay { position: fixed; inset: 0; background: rgba(10,12,20,.65); backdrop-filter: blur(5px); z-index: 500; display: none; align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.open { display: flex; animation: fadeIn .2s ease; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    .modal-box {
      background: var(--white); border-radius: 20px; width: 100%; max-width: 640px; max-height: 90vh;
      overflow-y: auto; box-shadow: 0 32px 80px rgba(0,0,0,.3);
      animation: slideUp .28s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes slideUp { from { transform: translateY(30px) scale(.97); opacity:0; } to { transform: translateY(0) scale(1); opacity:1; } }
    .modal-header { background: var(--orange); padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; border-radius: 20px 20px 0 0; }
    .modal-title { font-family: 'Bebas Neue', sans-serif; font-size: 1.3rem; letter-spacing: 2px; color: var(--white); }
    .modal-close { background: rgba(255,255,255,.18); border: none; color: var(--white); width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; transition: background .2s; }
    .modal-close:hover { background: rgba(255,255,255,.32); }
    .modal-body { padding: 28px 28px 20px; }
    .modal-section { font-size: 0.7rem; font-weight: 700; letter-spacing: 2px; color: var(--gray-lt); text-transform: uppercase; margin: 20px 0 12px; padding-bottom: 6px; border-bottom: 1px solid var(--bdr); }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-grid .full { grid-column: 1 / -1; }
    .mf { display: flex; flex-direction: column; gap: 5px; }
    .mf label { font-size: 0.7rem; font-weight: 700; letter-spacing: 1.5px; color: var(--gray-lt); text-transform: uppercase; }
    .mf input, .mf select, .mf textarea {
      background: var(--bg); border: 1.5px solid var(--bdr); color: var(--navy);
      font-family: 'DM Sans', sans-serif; font-size: 0.88rem; padding: 10px 13px;
      border-radius: 8px; outline: none; transition: border-color .2s, box-shadow .2s; width: 100%;
    }
    .mf input:focus, .mf select:focus, .mf textarea:focus { border-color: var(--orange); box-shadow: 0 0 0 3px rgba(244,123,32,.10); }
    .mf textarea { resize: vertical; }
    .modal-footer { padding: 16px 28px 24px; display: flex; gap: 10px; justify-content: flex-end; }
    .btn-cancel { background: none; border: 1.5px solid var(--bdr); color: var(--gray); font-family: 'DM Sans', sans-serif; font-size: 0.85rem; font-weight: 600; padding: 10px 20px; border-radius: 8px; cursor: pointer; transition: all .2s; }
    .btn-cancel:hover { border-color: var(--navy); color: var(--navy); }
    .btn-save { background: var(--orange); color: var(--white); border: none; font-family: 'Bebas Neue', sans-serif; font-size: 0.95rem; letter-spacing: 1.5px; padding: 11px 28px; border-radius: 8px; cursor: pointer; transition: all .2s; }
    .btn-save:hover { background: var(--orange-dark); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(244,123,32,.3); }

    /* Image preview */
    .img-upload-area { border: 2px dashed var(--bdr); border-radius: 10px; padding: 24px; text-align: center; cursor: pointer; transition: border-color .2s, background .2s; }
    .img-upload-area:hover { border-color: var(--orange); background: var(--orange-light); }
    .img-upload-area p { font-size: 0.82rem; color: var(--gray); margin-top: 8px; }
    .img-preview-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 10px; }
    .img-preview-item { position: relative; aspect-ratio: 1; background: var(--bg); border: 1px solid var(--bdr); border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
    .img-preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .img-remove { position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,.55); color: #fff; border: none; width: 20px; height: 20px; border-radius: 50%; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; }

    /* ── SELLER PROFILE BADGE ── */
    .seller-badge { display: flex; align-items: center; gap: 10px; }
    .seller-avatar { width: 36px; height: 36px; background: var(--orange); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-family: 'Bebas Neue', sans-serif; font-size: 1rem; flex-shrink: 0; }
    .seller-name { font-size: 0.82rem; font-weight: 700; color: var(--navy); }
    .seller-role { font-size: 0.7rem; color: var(--orange); font-weight: 600; }

    /* Toast */
    .toast-wrap { position: fixed; bottom: 24px; right: 24px; z-index: 600; display: flex; flex-direction: column; gap: 8px; }
    .toast { background: var(--white); color: var(--navy); font-size: 0.85rem; font-weight: 600; padding: 12px 20px; border-radius: 10px; border-left: 4px solid var(--orange); box-shadow: 0 4px 20px rgba(0,0,0,.12); opacity: 0; transform: translateX(20px); transition: opacity .3s, transform .3s; }
    .toast.show { opacity: 1; transform: translateX(0); }
    .toast.ok { border-left-color: var(--green); }
    .toast.err { border-left-color: var(--red); }

    /* Seller verification banner */
    .verify-banner { background: linear-gradient(135deg, #fff3e0, var(--orange-light)); border: 1.5px solid var(--orange-mid); border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 14px; margin-bottom: 24px; }
    .verify-banner svg { stroke: var(--orange); flex-shrink: 0; }
    .verify-banner-text h4 { font-size: 0.9rem; font-weight: 700; color: var(--navy); margin-bottom: 2px; }
    .verify-banner-text p { font-size: 0.78rem; color: var(--gray); }

    /* Revenue chart placeholder */
    .chart-card { background: var(--white); border: 1.5px solid var(--bdr); border-radius: 14px; padding: 20px; margin-bottom: 24px; }
    .chart-title { font-family: 'Bebas Neue', sans-serif; font-size: 1rem; letter-spacing: 1px; color: var(--navy); margin-bottom: 4px; }
    .chart-sub { font-size: 0.75rem; color: var(--gray); margin-bottom: 18px; }
    .chart-bars { display: flex; align-items: flex-end; gap: 8px; height: 80px; }
    .chart-bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px; }
    .chart-bar { width: 100%; background: var(--orange-light); border-radius: 4px 4px 0 0; transition: background .2s; cursor: pointer; min-height: 4px; }
    .chart-bar:hover { background: var(--orange); }
    .chart-lbl { font-size: 0.6rem; color: var(--gray-lt); font-weight: 600; }

    @media (max-width: 768px) {
      .stats-row { grid-template-columns: repeat(2, 1fr); }
      .form-grid { grid-template-columns: 1fr; }
      .form-grid .full { grid-column: 1; }
      nav { padding: 0 16px; }
      .dash-wrap { padding: 20px 16px 60px; }
    }
    @media (max-width: 480px) {
      .stats-row { grid-template-columns: 1fr 1fr; }
      table { font-size: 0.78rem; }
      th, td { padding: 10px 12px; }
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-left">
    <span class="brand">TournaMeet</span>
    <span class="nav-tag">SELLER CENTER</span>
  </div>
  <div class="nav-right">
    <a href="../Shop/index.php" class="btn-back-nav">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      Back to Shop
    </a>
    <div class="seller-badge" id="seller-badge" style="display:none">
      <div class="seller-avatar" id="seller-avatar-initial">?</div>
      <div>
        <div class="seller-name" id="seller-name-display">Seller</div>
        <div class="seller-role">Verified Seller</div>
      </div>
    </div>
  </div>
</nav>

<!-- ═══ SELLER LOGIN ═══ -->
<div id="seller-login">
  <div class="login-card">
    <div class="login-icon">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
      </svg>
    </div>
    <div class="login-title">Start Selling</div>
    <div class="login-sub">List your sports gear and equipment on TournaMeet's marketplace and reach thousands of athletes.</div>

    <div class="form-field">
      <label>Shop / Store Name</label>
      <input type="text" id="login-shop" placeholder="e.g. Juan's Sports Depot">
    </div>
    <div class="form-field">
      <label>Your Name</label>
      <input type="text" id="login-name" placeholder="Juan Dela Cruz">
    </div>
    <div class="form-field">
      <label>Email Address</label>
      <input type="email" id="login-email" placeholder="juan@email.com">
    </div>
    <div class="form-field">
      <label>GCash / Contact Number</label>
      <input type="tel" id="login-contact" placeholder="09XX XXX XXXX">
    </div>

    <button class="btn-primary" onclick="sellerLogin()">OPEN MY SELLER ACCOUNT</button>

    <div class="login-divider">Why sell on TournaMeet?</div>
    <div class="perks">
      <div class="perk"><div class="perk-icon">🏆</div><div class="perk-title">Sports Community</div><div class="perk-sub">Reach active tournament players</div></div>
      <div class="perk"><div class="perk-icon">📦</div><div class="perk-title">Easy Listings</div><div class="perk-sub">Post products in under a minute</div></div>
      <div class="perk"><div class="perk-icon">💰</div><div class="perk-title">Zero Setup Fee</div><div class="perk-sub">Free to list your products</div></div>
      <div class="perk"><div class="perk-icon">📊</div><div class="perk-title">Sales Dashboard</div><div class="perk-sub">Track your orders & revenue</div></div>
    </div>
  </div>
</div>

<!-- ═══ SELLER DASHBOARD ═══ -->
<div id="seller-dash">
  <div class="dash-wrap">

    <!-- Header -->
    <div class="dash-header">
      <div>
        <div class="dash-title" id="dash-shop-name">My Shop</div>
        <div class="dash-subtitle" id="dash-subtitle">Manage your listings and track your sales</div>
      </div>
      <button class="btn-new-listing" onclick="openNewListing()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        NEW LISTING
      </button>
    </div>

    <!-- Verification Banner -->
    <div class="verify-banner">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/><path d="M12 8v4l2 2"/>
      </svg>
      <div class="verify-banner-text">
        <h4>Your listings go live on the shop immediately</h4>
        <p>Products you add will appear in the TournaMeet shop for buyers to find and purchase. Make sure your product info and prices are accurate.</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-val" id="stat-listings">0</div>
        <div class="stat-lbl">Active Listings</div>
      </div>
      <div class="stat-card">
        <div class="stat-val" id="stat-orders">0</div>
        <div class="stat-lbl">Total Orders</div>
      </div>
      <div class="stat-card">
        <div class="stat-val" id="stat-revenue">₱0</div>
        <div class="stat-lbl">Revenue</div>
      </div>
      <div class="stat-card">
        <div class="stat-val" id="stat-views">0</div>
        <div class="stat-lbl">Product Views</div>
      </div>
    </div>

    <!-- Revenue Chart -->
    <div class="chart-card">
      <div class="chart-title">Sales This Week</div>
      <div class="chart-sub">Daily revenue overview</div>
      <div class="chart-bars" id="revenue-chart"></div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab active" onclick="switchTab('listings', this)">My Listings</button>
      <button class="tab" onclick="switchTab('orders', this)">Orders Received</button>
    </div>

    <!-- Listings Tab -->
    <div id="tab-listings">
      <div class="listings-wrap">
        <div class="listings-toolbar">
          <input type="text" class="search-inp" id="listing-search" placeholder="Search your listings…" oninput="renderListings()">
          <select class="filter-sel" id="listing-filter" onchange="renderListings()">
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
            <option value="sold-out">Sold Out</option>
          </select>
        </div>
        <div id="listings-body"></div>
      </div>
    </div>

    <!-- Orders Tab -->
    <div id="tab-orders" style="display:none">
      <div class="listings-wrap">
        <div class="listings-toolbar">
          <input type="text" class="search-inp" id="order-search" placeholder="Search orders…" oninput="renderOrders()">
          <select class="filter-sel" id="order-filter" onchange="renderOrders()">
            <option value="all">All Orders</option>
            <option value="Order Placed">Order Placed</option>
            <option value="Confirmed">Confirmed</option>
            <option value="Shipped">Shipped</option>
            <option value="Delivered">Delivered</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
        <div id="orders-body" class="orders-list"></div>
      </div>
    </div>

  </div>
</div>

<!-- ═══ ADD/EDIT LISTING MODAL ═══ -->
<div class="modal-overlay" id="listing-modal">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title" id="modal-title-text">NEW LISTING</div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-section">Product Details</div>
      <div class="form-grid">
        <div class="mf full"><label>Product Name *</label><input type="text" id="mf-name" placeholder="e.g. Pro Basketball Size 7"></div>
        <div class="mf">
          <label>Category *</label>
          <select id="mf-cat">
            <option>Basketball</option><option>Football</option><option>Volleyball</option>
            <option>Tennis</option><option>Badminton</option><option>Swimming</option>
            <option>Baseball</option><option>Running</option><option>Accessories</option>
          </select>
        </div>
        <div class="mf">
          <label>Condition</label>
          <select id="mf-condition">
            <option value="Brand New">Brand New</option>
            <option value="Like New">Like New (Used once)</option>
            <option value="Good">Good Condition</option>
            <option value="Fair">Fair Condition</option>
          </select>
        </div>
        <div class="mf full"><label>Description *</label><textarea id="mf-desc" rows="3" placeholder="Describe your product — brand, size, material, usage…"></textarea></div>
      </div>

      <div class="modal-section">Pricing & Inventory</div>
      <div class="form-grid">
        <div class="mf"><label>Selling Price (₱) *</label><input type="number" id="mf-price" placeholder="1500" min="1"></div>
        <div class="mf"><label>Original Price (₱)</label><input type="number" id="mf-orig-price" placeholder="2000 (optional)"></div>
        <div class="mf"><label>Quantity Available *</label><input type="number" id="mf-stock" placeholder="1" min="1" value="1"></div>
        <div class="mf">
          <label>Badge / Label</label>
          <select id="mf-badge">
            <option value="">None</option>
            <option value="sale">Sale</option>
            <option value="new">New Arrival</option>
            <option value="top">Top Rated</option>
            <option value="best">Best Seller</option>
          </select>
        </div>
      </div>

      <div class="modal-section">Purchase Links (Optional)</div>
      <div class="form-grid">
        <div class="mf"><label>Shopee Link</label><input type="url" id="mf-shopee" placeholder="https://shopee.ph/…"></div>
        <div class="mf"><label>Lazada Link</label><input type="url" id="mf-lazada" placeholder="https://lazada.com.ph/…"></div>
      </div>

      <div class="modal-section">Listing Status</div>
      <div class="form-grid">
        <div class="mf">
          <label>Status</label>
          <select id="mf-status">
            <option value="active">Active (Visible to buyers)</option>
            <option value="draft">Draft (Hidden)</option>
          </select>
        </div>
        <div class="mf"><label>Seller Contact / GCash</label><input type="text" id="mf-contact" placeholder="09XX XXX XXXX"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Cancel</button>
      <button class="btn-save" onclick="saveListing()">PUBLISH LISTING</button>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>

<script>
const API = 'api.php'; // same folder as seller.php
let seller = null;
let listings = [];
let sellerOrders = [];
let editingId = null;

// ── SELLER LOGIN ──
function sellerLogin() {
  const shop  = document.getElementById('login-shop').value.trim();
  const name  = document.getElementById('login-name').value.trim();
  const email = document.getElementById('login-email').value.trim();
  const contact = document.getElementById('login-contact').value.trim();
  if (!shop || !name || !email) { showToast('Please fill in all required fields.', 'err'); return; }
  seller = { shop, name, email, contact, joinedDate: new Date().toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) };
  localStorage.setItem('tm_seller', JSON.stringify(seller));
  initDashboard();
}

function initDashboard() {
  document.getElementById('seller-login').style.display = 'none';
  document.getElementById('seller-dash').style.display = 'block';
  document.getElementById('seller-badge').style.display = 'flex';
  document.getElementById('seller-avatar-initial').textContent = seller.name.charAt(0).toUpperCase();
  document.getElementById('seller-name-display').textContent = seller.name;
  document.getElementById('dash-shop-name').textContent = seller.shop;
  document.getElementById('dash-subtitle').textContent = `Welcome back, ${seller.name} · Seller since ${seller.joinedDate}`;
  // Load listings from localStorage (in production, load from DB filtered by seller)
  const saved = localStorage.getItem('tm_seller_listings_' + seller.email);
  listings = saved ? JSON.parse(saved) : [];
  renderListings();
  loadSellerOrders();
  updateStats();
  renderChart();
}

// ── STATS ──
function updateStats() {
  const active = listings.filter(l => l.status === 'active').length;
  const revenue = sellerOrders.reduce((s, o) => {
    const myItems = (o.items || []).filter(i => listings.some(l => l.id == i.id));
    return s + myItems.reduce((ss, i) => ss + i.price * i.qty, 0);
  }, 0);
  document.getElementById('stat-listings').textContent = active;
  document.getElementById('stat-orders').textContent = sellerOrders.length;
  document.getElementById('stat-revenue').textContent = '₱' + revenue.toLocaleString();
  document.getElementById('stat-views').textContent = Math.floor(active * 12 + sellerOrders.length * 4);
}

// ── CHART ──
function renderChart() {
  const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
  const vals = days.map(() => Math.floor(Math.random() * 3000 + 200));
  const max = Math.max(...vals);
  document.getElementById('revenue-chart').innerHTML = days.map((d,i) => `
    <div class="chart-bar-wrap" title="₱${vals[i].toLocaleString()}">
      <div class="chart-bar" style="height:${Math.round((vals[i]/max)*100)+'%'}"></div>
      <div class="chart-lbl">${d}</div>
    </div>`).join('');
}

// ── TABS ──
function switchTab(tab, el) {
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('tab-listings').style.display = tab === 'listings' ? 'block' : 'none';
  document.getElementById('tab-orders').style.display = tab === 'orders' ? 'block' : 'none';
  if (tab === 'orders') renderOrders();
}

// ── LISTINGS ──
function renderListings() {
  const q = (document.getElementById('listing-search')?.value || '').toLowerCase();
  const filt = document.getElementById('listing-filter')?.value || 'all';
  let list = listings.filter(l => {
    const mq = !q || l.name.toLowerCase().includes(q) || l.cat.toLowerCase().includes(q);
    const mf = filt === 'all' || l.status === filt || (filt === 'sold-out' && l.stock === 0);
    return mq && mf;
  });

  const body = document.getElementById('listings-body');
  if (!list.length) {
    body.innerHTML = `<div class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      <h3>No Listings Yet</h3>
      <p>Click "New Listing" to add your first product</p>
    </div>`;
    return;
  }

  const badgeMap = { 'Brand New':'🟢 Brand New', 'Like New':'🔵 Like New', 'Good':'🟡 Good', 'Fair':'🟠 Fair' };
  const statusBadge = s => {
    if (s === 'active') return '<span class="badge active">● Active</span>';
    if (s === 'draft') return '<span class="badge draft">Draft</span>';
    if (s === 'sold-out') return '<span class="badge sold-out">Sold Out</span>';
    return `<span class="badge pending">${s}</span>`;
  };

  body.innerHTML = `<table>
    <thead><tr>
      <th>Product</th><th>Price</th><th>Condition</th><th>Stock</th><th>Status</th><th>Actions</th>
    </tr></thead>
    <tbody>${list.map(l => `<tr>
      <td><div class="prod-cell">
        <div class="prod-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>
        <div><div class="prod-name">${l.name}</div><div class="prod-cat">${l.cat}</div></div>
      </div></td>
      <td><div class="price-cell">₱${Number(l.price).toLocaleString()}</div>${l.origPrice?`<div style="font-size:.72rem;color:#9aa5bf;text-decoration:line-through">₱${Number(l.origPrice).toLocaleString()}</div>`:''}</td>
      <td><span style="font-size:.78rem;color:var(--gray)">${badgeMap[l.condition]||l.condition}</span></td>
      <td><span style="font-weight:700">${l.stock}</span><span style="color:var(--gray-lt)"> units</span></td>
      <td>${statusBadge(l.stock == 0 ? 'sold-out' : l.status)}</td>
      <td><div class="action-btns">
        <button class="btn-edit" onclick="editListing(${l.id})">Edit</button>
        <button class="btn-toggle" onclick="toggleStatus(${l.id})">${l.status === 'active' ? 'Hide' : 'Show'}</button>
        <button class="btn-delete" onclick="deleteListing(${l.id})">Delete</button>
      </div></td>
    </tr>`).join('')}</tbody>
  </table>`;
}

// ── ORDERS ──
async function loadSellerOrders() {
  if (!seller?.email) return;
  try {
    const res = await fetch(`${API}?action=orders&email=${encodeURIComponent(seller.email)}`);
    const data = await res.json();
    sellerOrders = Array.isArray(data) ? data : [];
    updateStats();
  } catch(e) {
    // If API not available, show empty state
    sellerOrders = [];
  }
}

function renderOrders() {
  const q = (document.getElementById('order-search')?.value || '').toLowerCase();
  const filt = document.getElementById('order-filter')?.value || 'all';
  const list = sellerOrders.filter(o => {
    const mq = !q || o.ref?.toLowerCase().includes(q) || (o.customer?.fn + ' ' + o.customer?.ln).toLowerCase().includes(q);
    const mf = filt === 'all' || o.status === filt;
    return mq && mf;
  });

  const body = document.getElementById('orders-body');
  if (!list.length) {
    body.innerHTML = `<div class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <h3>No Orders Yet</h3>
      <p>Orders for your products will appear here</p>
    </div>`;
    return;
  }

  const sClass = { 'Order Placed':'s-placed','Confirmed':'s-confirmed','Shipped':'s-shipped','Delivered':'s-delivered','Cancelled':'s-cancelled' };
  body.innerHTML = list.map(o => `
    <div class="order-card">
      <div>
        <div class="order-ref">${o.ref}</div>
        <div class="order-buyer">${o.customer?.fn||''} ${o.customer?.ln||''} · ${o.customer?.ph||''}</div>
      </div>
      <div class="order-items">${(o.items||[]).length} item(s)</div>
      <div><div class="order-total">₱${Number(o.total||0).toLocaleString()}</div><div style="font-size:.72rem;color:var(--gray-lt)">${o.payment?.toUpperCase()}</div></div>
      <span class="order-status ${sClass[o.status]||'s-placed'}">${o.status||'Order Placed'}</span>
      <div style="font-size:.75rem;color:var(--gray-lt)">${(o.date||'').split(',')[0]}</div>
    </div>`).join('');
}

// ── ADD / EDIT LISTING MODAL ──
function openNewListing() {
  editingId = null;
  document.getElementById('modal-title-text').textContent = 'NEW LISTING';
  document.getElementById('btn-save-text') && (document.querySelector('.btn-save').textContent = 'PUBLISH LISTING');
  clearForm();
  // Pre-fill contact
  if (seller?.contact) document.getElementById('mf-contact').value = seller.contact;
  document.getElementById('listing-modal').classList.add('open');
}

function editListing(id) {
  const l = listings.find(x => x.id === id);
  if (!l) return;
  editingId = id;
  document.getElementById('modal-title-text').textContent = 'EDIT LISTING';
  document.getElementById('mf-name').value = l.name;
  document.getElementById('mf-cat').value = l.cat;
  document.getElementById('mf-condition').value = l.condition;
  document.getElementById('mf-desc').value = l.description;
  document.getElementById('mf-price').value = l.price;
  document.getElementById('mf-orig-price').value = l.origPrice || '';
  document.getElementById('mf-stock').value = l.stock;
  document.getElementById('mf-badge').value = l.badge || '';
  document.getElementById('mf-shopee').value = l.shopee || '';
  document.getElementById('mf-lazada').value = l.lazada || '';
  document.getElementById('mf-status').value = l.status;
  document.getElementById('mf-contact').value = l.contact || '';
  document.getElementById('listing-modal').classList.add('open');
}

function clearForm() {
  ['mf-name','mf-desc','mf-price','mf-orig-price','mf-shopee','mf-lazada','mf-contact'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('mf-stock').value = '1';
  document.getElementById('mf-cat').value = 'Basketball';
  document.getElementById('mf-condition').value = 'Brand New';
  document.getElementById('mf-badge').value = '';
  document.getElementById('mf-status').value = 'active';
}

function closeModal() { document.getElementById('listing-modal').classList.remove('open'); }

async function saveListing() {
  const name = document.getElementById('mf-name').value.trim();
  const desc = document.getElementById('mf-desc').value.trim();
  const price = parseFloat(document.getElementById('mf-price').value);
  const stock = parseInt(document.getElementById('mf-stock').value);
  if (!name) { showToast('Product name is required.', 'err'); return; }
  if (!desc) { showToast('Description is required.', 'err'); return; }
  if (!price || price < 1) { showToast('Enter a valid price.', 'err'); return; }
  if (!stock || stock < 1) { showToast('Enter a valid stock quantity.', 'err'); return; }

  const badge = document.getElementById('mf-badge').value;
  const badgeTextMap = { new:'NEW ARRIVAL', sale:'SALE', top:'TOP RATED', best:'BEST SELLER', '':'' };
  const shopeeUrl = document.getElementById('mf-shopee').value.trim();
  const lazadaUrl = document.getElementById('mf-lazada').value.trim();
  const links = [];
  if (shopeeUrl) links.push({ name:'Shopee PH', type:'shopee', url: shopeeUrl });
  if (lazadaUrl) links.push({ name:'Lazada PH', type:'lazada', url: lazadaUrl });

  // Preserve existing local id and apiId if editing
  const existing = editingId ? listings.find(l => l.id === editingId) : null;

  const listing = {
    id: editingId || Date.now(),           // local identifier (used in seller dashboard)
    apiId: existing?.apiId || null,        // real DB id from products table (null until first save)
    name,
    cat: document.getElementById('mf-cat').value,
    condition: document.getElementById('mf-condition').value,
    description: desc,
    price,
    origPrice: parseFloat(document.getElementById('mf-orig-price').value) || null,
    stock,
    total_stock: stock,
    badge,
    badge_text: badgeTextMap[badge] || '',
    shopee: shopeeUrl,
    lazada: lazadaUrl,
    links,
    status: document.getElementById('mf-status').value,
    contact: document.getElementById('mf-contact').value.trim(),
    seller: seller.shop,
    sellerEmail: seller.email,
    is_new: badge === 'new' ? 1 : 0,
    rating: existing?.rating ?? 5.0,
    review_count: existing?.review_count ?? 0,
    createdAt: existing?.createdAt || new Date().toISOString(),
    specs_json: JSON.stringify({}),
  };

  // Sync with shop DB
  if (listing.status === 'active') {
    try {
      // Only pass the real DB id when we already have one (update); omit it for inserts
      const payload = {
        name,
        cat: listing.cat,
        price,
        stock,
        total_stock: stock,
        badge,
        badge_text: listing.badge_text,
        description: desc,
        specs: {},
        links,
        is_new: listing.is_new,
      };
      // If we already have a DB row, send its id so API does UPDATE not INSERT
      if (listing.apiId) payload.id = listing.apiId;

      const res = await fetch(`${API}?action=save_product`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Admin-Token': 'admin123' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        // Store the real DB id so future edits update the same row
        listing.apiId = data.id;
        showToast(listing.apiId && existing?.apiId ? 'Listing updated in shop!' : 'Listing published to shop!', 'ok');
      } else {
        showToast('Sync error: ' + (data.error || 'Could not reach shop API.'), 'err');
      }
    } catch(e) {
      showToast('Saved locally — API not reachable. Check the API path.', 'err');
    }
  } else {
    // If switching to draft and we have a live DB row, delete it from shop
    if (existing?.apiId) {
      try {
        await fetch(`${API}?action=delete_product`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-Admin-Token': 'admin123' },
          body: JSON.stringify({ id: existing.apiId })
        });
        listing.apiId = null; // no longer in DB
      } catch(e) {}
    }
    showToast(editingId ? 'Draft updated.' : 'Saved as draft (not visible in shop).', 'ok');
  }

  if (editingId) {
    const idx = listings.findIndex(l => l.id === editingId);
    if (idx > -1) listings[idx] = listing;
  } else {
    listings.unshift(listing);
  }

  saveListings();
  renderListings();
  updateStats();
  closeModal();
}

async function toggleStatus(id) {
  const l = listings.find(x => x.id === id);
  if (!l) return;
  const goingActive = l.status !== 'active';
  l.status = goingActive ? 'active' : 'draft';

  if (goingActive) {
    // Publish to DB
    try {
      const payload = {
        name: l.name, cat: l.cat, price: l.price,
        stock: l.stock, total_stock: l.total_stock,
        badge: l.badge, badge_text: l.badge_text,
        description: l.description, specs: {}, links: l.links || [], is_new: l.is_new,
      };
      if (l.apiId) payload.id = l.apiId;
      const res = await fetch(`${API}?action=save_product`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Admin-Token': 'admin123' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) { l.apiId = data.id; showToast('Listing is now live in shop!', 'ok'); }
      else showToast('Could not publish: ' + (data.error||''), 'err');
    } catch(e) { showToast('API not reachable.', 'err'); }
  } else {
    // Remove from DB (hide from shop)
    if (l.apiId) {
      try {
        await fetch(`${API}?action=delete_product`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-Admin-Token': 'admin123' },
          body: JSON.stringify({ id: l.apiId })
        });
        l.apiId = null;
      } catch(e) {}
    }
    showToast('Listing hidden from shop.', 'ok');
  }
  saveListings();
  renderListings();
}

async function deleteListing(id) {
  if (!confirm('Delete this listing? It will also be removed from the shop.')) return;
  const l = listings.find(x => x.id === id);
  // Remove from DB if it was published
  if (l?.apiId) {
    try {
      await fetch(`${API}?action=delete_product`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Admin-Token': 'admin123' },
        body: JSON.stringify({ id: l.apiId })
      });
    } catch(e) {}
  }
  listings = listings.filter(l => l.id !== id);
  saveListings();
  renderListings();
  updateStats();
  showToast('Listing deleted from shop.', 'err');
}

function saveListings() {
  if (seller) localStorage.setItem('tm_seller_listings_' + seller.email, JSON.stringify(listings));
}

// ── TOAST ──
function showToast(msg, type = '') {
  const c = document.getElementById('toast-wrap');
  const t = document.createElement('div');
  t.className = 'toast' + (type ? ' ' + type : '');
  t.textContent = msg;
  c.appendChild(t);
  requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 350); }, 2800);
}

// ── INIT: check for existing seller session ──
document.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('tm_seller');
  if (saved) {
    seller = JSON.parse(saved);
    initDashboard();
  }
  document.getElementById('listing-modal').addEventListener('click', e => {
    if (e.target === document.getElementById('listing-modal')) closeModal();
  });
});
</script>
</body>
</html>