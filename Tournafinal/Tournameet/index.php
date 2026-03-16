<?php
session_start();
include("api/config.php");

// Fetch upcoming tournaments that have coordinates
$stmt = $pdo->query("
    SELECT name, sport, date, time, location AS venue, prize, entrance_fee, latitude, longitude
    FROM tournaments
    WHERE latitude IS NOT NULL
      AND longitude IS NOT NULL
    ORDER BY date ASC
");

$map_tournaments = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="/Tourna/favicon.png">
  <title>TournaMeet</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --orange: #F47B20;
      --orange-dark: #D96210;
      --orange-light: #FFF0E6;
      --orange-mid: #fdb97d;
      --white: #FFFFFF;
      --shadow: 0 2px 16px rgba(244,123,32,0.18);
    }
    body { background: #fafafa; font-family: 'DM Sans', sans-serif; min-height: 100vh; color: #1a1a1a; }

    #transition-ripple {
      position: fixed; border-radius: 50%;
      background: var(--orange);
      width: 40px; height: 40px;
      transform: scale(0); opacity: 0;
      pointer-events: none; z-index: 9998;
      transition: none;
    }
    #transition-ripple.animating {
      transition: transform 0.55s cubic-bezier(0.4, 0, 0.2, 1),
                  opacity 0.55s cubic-bezier(0.4, 0, 0.2, 1);
      transform: scale(80); opacity: 1;
    }

    nav {
      position: sticky; top: 0; z-index: 200;
      background: var(--white);
      border-bottom: 2px solid var(--orange);
      box-shadow: var(--shadow);
      height: 64px;
      display: flex;
      align-items: center; padding: 0 32px; gap: 12px;
    }
    .nav-left { display: flex; align-items: center; gap: 10px; }
    .logo-icon {
      width: 38px; height: 38px; border-radius: 50%;
      background: var(--orange-light); border: 2px solid var(--orange);
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .logo-icon img { width: 24px; height: 24px; object-fit: contain; }
    .brand { font-family: 'Bebas Neue', sans-serif; font-size: 1.7rem; letter-spacing: 2.5px; color: var(--orange); line-height: 1; }

    main { max-width: 1100px; margin: 0 auto; padding: 40px 32px 80px; }
    .page-header { margin-bottom: 28px; }
    .page-header h1 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(2rem, 5vw, 3.5rem);
      color: var(--orange); letter-spacing: 3px; line-height: 1; margin-bottom: 4px;
    }
    .page-header p { font-size: 0.92rem; color: #888; font-weight: 500; }

    .sports-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
    .sport-card {
      background: var(--white); border-radius: 14px; border: 1.5px solid #f0e0d0;
      box-shadow: 0 2px 12px rgba(244,123,32,0.08);
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 32px 20px 24px; cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
      text-align: center; position: relative; overflow: hidden; animation: fadeUp 0.4s ease both;
    }
    .sport-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
      background: linear-gradient(90deg, var(--orange), var(--orange-mid));
      opacity: 0; transition: opacity 0.2s;
    }
    .sport-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(244,123,32,0.18); border-color: var(--orange); }
    .sport-card:hover::before { opacity: 1; }
    .sport-card:hover .icon-wrap { background: var(--orange); }
    .sport-card:hover .icon-wrap svg { stroke: var(--white); }
    .sport-card:active { transform: scale(0.985); }
    .icon-wrap {
      width: 72px; height: 72px; border-radius: 50%;
      background: var(--orange-light); border: 2px solid #f0e0d0;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 14px; transition: background 0.2s; flex-shrink: 0;
    }
    .icon-wrap svg { width: 36px; height: 36px; stroke: var(--orange); transition: stroke 0.2s; }
    .sport-name { font-family: 'Bebas Neue', sans-serif; font-size: 1.15rem; letter-spacing: 1.5px; color: #222; margin-bottom: 4px; }
    .sport-sub { font-size: 0.75rem; color: #c0a090; font-weight: 500; }
    .sport-card:nth-child(1) { animation-delay: 0ms; }
    .sport-card:nth-child(2) { animation-delay: 60ms; }
    .sport-card:nth-child(3) { animation-delay: 120ms; }
    .sport-card:nth-child(4) { animation-delay: 180ms; }
    .sport-card:nth-child(5) { animation-delay: 240ms; }
    .sport-card:nth-child(6) { animation-delay: 300ms; }
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(18px); }
      to   { opacity:1; transform:translateY(0); }
    }

    @media (max-width: 1099px) { .sports-grid { grid-template-columns: repeat(2, 1fr); } nav { padding: 0 20px; } }
    @media (max-width: 599px) {
      .sports-grid { gap: 14px; }
      nav { padding: 0 12px; }
      main { padding: 24px 16px 60px; }
      .icon-wrap { width: 60px; height: 60px; }
      .icon-wrap svg { width: 28px; height: 28px; }
    }
  </style>
</head>
<body>

  <div id="transition-ripple"></div>

  <nav>
    <div class="nav-left">
      <a href="/Tourna/NewsFeed/newsfeed.php" title="Go back" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
        <div class="logo-icon" style="flex-shrink:0;">
          <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
            <path d="M19 12H5" stroke="var(--orange)" stroke-width="2.2" stroke-linecap="round"/>
            <path d="M12 19l-7-7 7-7" stroke="var(--orange)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <span style="font-family:'DM Sans',sans-serif;font-size:0.82rem;font-weight:600;color:var(--orange);letter-spacing:0.3px;">Back</span>
      </a>
      <span class="brand">TournaMeet</span>
    </div>
  </nav>

  <main>
    <div class="page-header">
      <h1>Choose a Category</h1>
      <p>Browse tournaments by sport type</p>
    </div>
    <div class="sports-grid">
      <button class="sport-card" onclick="selectSport('Ball Sports')">
        <div class="icon-wrap">
          <svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="18" r="13"/><line x1="18" y1="5" x2="18" y2="31"/>
            <line x1="5" y1="18" x2="31" y2="18"/>
            <path d="M18 5 C10 8 10 28 18 31" fill="none"/>
            <path d="M18 5 C26 8 26 28 18 31" fill="none"/>
          </svg>
        </div>
        <div class="sport-name">Ball Sports</div>
        <div class="sport-sub">Basketball · Football · Volleyball</div>
      </button>

      <button class="sport-card" onclick="selectSport('Racket Sports')">
        <div class="icon-wrap">
          <svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <ellipse cx="13" cy="12" rx="6" ry="8" transform="rotate(-35 13 12)"/>
            <line x1="7" y1="7" x2="12" y2="19" stroke-width="1" opacity="0.5"/>
            <line x1="10" y1="5" x2="15" y2="17" stroke-width="1" opacity="0.5"/>
            <line x1="13" y1="4" x2="18" y2="16" stroke-width="1" opacity="0.5"/>
            <line x1="7" y1="10" x2="17" y2="6" stroke-width="1" opacity="0.5"/>
            <line x1="7" y1="13" x2="18" y2="9" stroke-width="1" opacity="0.5"/>
            <line x1="8" y1="16" x2="19" y2="12" stroke-width="1" opacity="0.5"/>
            <line x1="17" y1="18" x2="30" y2="32" stroke-width="2.4"/>
            <circle cx="29" cy="10" r="3"/>
            <line x1="29" y1="7" x2="23" y2="1" stroke-width="1.4"/>
            <line x1="29" y1="7" x2="27" y2="0" stroke-width="1.4"/>
            <line x1="29" y1="7" x2="29" y2="0" stroke-width="1.4"/>
            <line x1="29" y1="7" x2="32" y2="1" stroke-width="1.4"/>
            <path d="M23 1 Q29 -1 35 2" fill="none" stroke-width="1.5"/>
          </svg>
        </div>
        <div class="sport-name">Racket Sports</div>
        <div class="sport-sub">Badminton · Tennis · Squash</div>
      </button>

      <button class="sport-card" onclick="selectSport('Combatives')">
        <div class="icon-wrap">
          <svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12 Q3 8 9 8 L15 8 Q18 8 18 12 L18 19 Q18 22 15 22 L9 22 Q3 22 3 18 Z" fill="currentColor" fill-opacity="0.08"/>
            <path d="M3 12 Q3 8 9 8 L15 8 Q18 8 18 12 L18 19 Q18 22 15 22 L9 22 Q3 22 3 18 Z"/>
            <path d="M15 8 Q20 7 19 12 Q18 13 18 12"/>
            <rect x="5" y="22" width="11" height="5" rx="2.5"/>
            <path d="M33 12 Q33 8 27 8 L21 8 Q18 8 18 12 L18 19 Q18 22 21 22 L27 22 Q33 22 33 18 Z" fill="currentColor" fill-opacity="0.08"/>
            <path d="M33 12 Q33 8 27 8 L21 8 Q18 8 18 12 L18 19 Q18 22 21 22 L27 22 Q33 22 33 18 Z"/>
            <path d="M21 8 Q16 7 17 12 Q18 13 18 12"/>
            <rect x="20" y="22" width="11" height="5" rx="2.5"/>
            <line x1="17" y1="13" x2="19" y2="16" stroke-width="1.8" opacity="0.6"/>
            <line x1="19" y1="13" x2="17" y2="16" stroke-width="1.8" opacity="0.6"/>
          </svg>
        </div>
        <div class="sport-name">Combatives</div>
        <div class="sport-sub">Boxing · MMA · Karate</div>
      </button>

      <button class="sport-card" onclick="selectSport('Endurance')">
        <div class="icon-wrap">
          <svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="6" r="3.5"/>
            <line x1="18" y1="9.5" x2="18" y2="21"/>
            <line x1="18" y1="13" x2="11" y2="16"/>
            <line x1="11" y1="16" x2="8" y2="10"/>
            <circle cx="8" cy="9" r="2" fill="currentColor" fill-opacity="0.1"/>
            <circle cx="8" cy="9" r="2"/>
            <path d="M11 16 Q7 12 8 10" fill="none" stroke-width="2.5"/>
            <line x1="18" y1="13" x2="25" y2="16"/>
            <line x1="25" y1="16" x2="28" y2="10"/>
            <circle cx="28" cy="9" r="2" fill="currentColor" fill-opacity="0.1"/>
            <circle cx="28" cy="9" r="2"/>
            <path d="M25 16 Q29 12 28 10" fill="none" stroke-width="2.5"/>
            <line x1="18" y1="21" x2="13" y2="30"/>
            <line x1="13" y1="30" x2="11" y2="35"/>
            <line x1="18" y1="21" x2="23" y2="30"/>
            <line x1="23" y1="30" x2="25" y2="35"/>
          </svg>
        </div>
        <div class="sport-name">Endurance</div>
        <div class="sport-sub">Running · Cycling · Triathlon</div>
      </button>

      <button class="sport-card" onclick="selectSport('Precision')">
        <div class="icon-wrap">
          <svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="17" cy="20" r="12"/>
            <circle cx="17" cy="20" r="7.5"/>
            <circle cx="17" cy="20" r="3.5"/>
            <circle cx="17" cy="20" r="1.2" fill="currentColor"/>
            <line x1="32" y1="6" x2="18" y2="20" stroke-width="2.2"/>
            <polyline points="29,6 32,6 32,9" stroke-width="1.8"/>
          </svg>
        </div>
        <div class="sport-name">Precision</div>
        <div class="sport-sub">Archery · Shooting · Darts</div>
      </button>

      <button class="sport-card" onclick="selectSport('E-sports')">
        <div class="icon-wrap">
          <svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 11 C13 11 9 11 7 14 L6 20 C5 23 5 26 6 29 C7 31 9 33 12 32 C15 31 15 28 17 24 L19 24 C21 28 21 31 24 32 C27 33 29 31 30 29 C31 26 31 23 30 20 L29 14 C27 11 23 11 21 11 Z"/>
            <line x1="11" y1="18" x2="11" y2="23" stroke-width="2"/>
            <line x1="8.5" y1="20.5" x2="13.5" y2="20.5" stroke-width="2"/>
            <circle cx="23" cy="19" r="1.2" fill="currentColor"/>
            <circle cx="26" cy="22" r="1.2" fill="currentColor"/>
          </svg>
        </div>
        <div class="sport-name">E-sports</div>
        <div class="sport-sub">FPS · MOBA · Fighting</div>
      </button>
    </div>
  </main>

  <script>
  function selectSport(name) {
    const pages = {
      'Ball Sports':   'ballsport.html',
      'Racket Sports': 'racketsports.html',
      'Combatives':    'combatives.html',
      'Endurance':     'endurance.html',
      'Precision':     'precision.html',
      'E-sports':      'esports.html'
    };
    window.location.href = pages[name] || `/category.html?category=${encodeURIComponent(name)}`;
  }
  </script>
</body>
</html>