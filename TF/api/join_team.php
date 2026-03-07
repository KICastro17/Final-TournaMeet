<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>TOURNAMEET – Teams & Friends</title>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet"/>
<style>
  :root {
    --orange:#F07B20;--orange-light:#FDE8D4;--orange-mid:#F9C49A;
    --bg:#F2F0EE;--card:#FFFFFF;--text:#1A1A1A;
    --muted:#888;--border:#E8E4E0;--radius:18px;
    --green:#3EC87A;--red:#E04040;
  }
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:'Barlow',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

  nav{background:#fff;border-bottom:2px solid var(--orange);display:flex;align-items:center;justify-content:space-between;padding:0 36px;height:62px;position:sticky;top:0;z-index:100;}
  .nav-logo{display:flex;align-items:center;gap:10px;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.25rem;letter-spacing:.04em;color:var(--orange);text-transform:uppercase;}
  .nav-logo svg{width:28px;height:28px;}
  .nav-search{flex:1;max-width:440px;margin:0 32px;display:flex;align-items:center;background:var(--bg);border-radius:40px;border:1.5px solid var(--border);overflow:hidden;padding:0 6px 0 18px;}
  .nav-search input{flex:1;border:none;background:transparent;outline:none;font-family:'Barlow',sans-serif;font-size:.9rem;color:var(--text);padding:8px 0;}
  .nav-search button{background:var(--orange);border:none;border-radius:50%;width:34px;height:34px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .nav-search button svg{width:16px;height:16px;fill:#fff;}
  .nav-icons{display:flex;gap:6px;}
  .nav-icons button{width:38px;height:38px;border-radius:50%;border:1.5px solid var(--border);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);transition:border-color .2s,color .2s;position:relative;}
  .nav-icons button:hover,.nav-icons button.active{border-color:var(--orange);color:var(--orange);}
  .nav-icons svg{width:18px;height:18px;}
  .req-badge{position:absolute;top:-4px;right:-4px;background:var(--red);color:#fff;font-size:.6rem;font-weight:800;border-radius:50%;width:17px;height:17px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;display:none;}

  .page-header{padding:40px 48px 0;}
  .page-header h1{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:2.8rem;text-transform:uppercase;color:var(--orange);line-height:1;}
  .page-header p{color:var(--muted);font-size:.95rem;margin-top:6px;}

  .tab-bar{display:flex;margin:28px 48px 0;border-bottom:2px solid var(--border);}
  .tab{font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:1rem;letter-spacing:.06em;text-transform:uppercase;padding:10px 28px;border:none;background:transparent;cursor:pointer;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;transition:color .2s,border-color .2s;position:relative;}
  .tab.active{color:var(--orange);border-bottom-color:var(--orange);}
  .tab:hover:not(.active){color:var(--text);}
  .tab-badge{background:var(--red);color:#fff;font-size:.6rem;font-weight:800;border-radius:20px;padding:1px 6px;margin-left:6px;vertical-align:middle;display:none;}

  .main{padding:32px 48px 60px;}

  .section-title{font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:1.1rem;letter-spacing:.08em;text-transform:uppercase;color:var(--text);display:flex;align-items:center;gap:10px;margin-bottom:18px;}
  .section-title span{background:var(--orange-light);color:var(--orange);border-radius:20px;font-size:.75rem;padding:2px 10px;font-weight:700;letter-spacing:.04em;}
  .section-divider{height:1.5px;background:var(--border);margin:8px 0 36px;}
  .people-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:44px;}

  /* ── Person Card ── */
  .person-card{background:var(--card);border-radius:var(--radius);padding:24px 18px 18px;display:flex;flex-direction:column;align-items:center;border:1.5px solid var(--border);transition:box-shadow .2s,border-color .2s,transform .15s;position:relative;}
  .person-card:hover{box-shadow:0 8px 28px rgba(240,123,32,.13);border-color:var(--orange-mid);transform:translateY(-2px);}

  .avatar{width:64px;height:64px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.4rem;color:var(--orange);margin-bottom:12px;position:relative;flex-shrink:0;overflow:hidden;}
  .avatar img{width:100%;height:100%;border-radius:50%;object-fit:cover;}
  .online-dot{width:13px;height:13px;background:var(--green);border-radius:50%;border:2px solid #fff;position:absolute;bottom:2px;right:2px;}
  .person-name{font-weight:700;font-size:.95rem;text-align:center;margin-bottom:3px;}
  .person-sport{font-size:.78rem;color:var(--muted);text-align:center;margin-bottom:10px;}
  .role-badge{font-size:.7rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:2px 10px;border-radius:20px;margin-bottom:14px;}
  .role-athlete{background:var(--orange-light);color:var(--orange);}
  .role-coach{background:#E3F0FF;color:#2775C9;}

  .card-actions{display:flex;gap:8px;width:100%;}
  .btn{flex:1;border:none;border-radius:10px;cursor:pointer;font-family:'Barlow',sans-serif;font-weight:600;font-size:.8rem;padding:8px 6px;transition:background .15s,color .15s,transform .1s;display:flex;align-items:center;justify-content:center;gap:5px;}
  .btn:active{transform:scale(.97);}
  .btn-add{background:var(--orange);color:#fff;}
  .btn-add:hover{background:#d96a10;}
  .btn-add:disabled{background:#ccc;cursor:default;}
  .btn-pending{background:var(--orange-light);color:var(--orange);cursor:default;}
  .btn-chat{background:var(--bg);color:var(--text);border:1.5px solid var(--border);}
  .btn-chat:hover{border-color:var(--orange);color:var(--orange);}
  .btn-remove{background:var(--bg);color:var(--red);border:1.5px solid #fdd;flex:0 0 auto;padding:8px 10px;}
  .btn-remove:hover{background:#ffeaea;}
  .btn-accept{background:var(--green);color:#fff;}
  .btn-accept:hover{background:#31a863;}
  .btn-decline{background:var(--bg);color:var(--red);border:1.5px solid #fdd;}
  .btn-decline:hover{background:#ffeaea;}
  .btn svg{width:14px;height:14px;flex-shrink:0;}

  /* ── Request card highlight ── */
  .request-card{border-color:var(--orange-mid);background:#FFFAF6;}
  .request-label{position:absolute;top:10px;left:10px;background:var(--orange);color:#fff;font-size:.62rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;border-radius:20px;padding:2px 8px;}

  /* ── Teams ── */
  .teams-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;margin-bottom:44px;}
  .team-card{background:var(--card);border-radius:var(--radius);border:1.5px solid var(--border);overflow:hidden;transition:box-shadow .2s,border-color .2s,transform .15s;}
  .team-card:hover{box-shadow:0 8px 28px rgba(240,123,32,.13);border-color:var(--orange-mid);transform:translateY(-2px);}
  .team-banner{height:80px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;}
  .team-banner-pattern{position:absolute;inset:0;opacity:.12;background-image:repeating-linear-gradient(45deg,#fff 0,#fff 2px,transparent 0,transparent 50%);background-size:12px 12px;}
  .team-icon{width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;position:relative;z-index:1;}
  .team-icon svg{width:28px;height:28px;fill:#fff;}
  .team-body{padding:18px 20px;}
  .team-name{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.15rem;text-transform:uppercase;margin-bottom:2px;}
  .team-coach{font-size:.8rem;color:var(--muted);margin-bottom:12px;}
  .team-coach strong{color:var(--text);}
  .team-meta{display:flex;gap:16px;margin-bottom:14px;}
  .team-meta-item .val{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.2rem;color:var(--orange);}
  .team-meta-item .lbl{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;}
  .team-members{display:flex;margin-bottom:16px;margin-top:4px;}
  .team-member-avatar{width:30px;height:30px;border-radius:50%;background:var(--orange-light);color:var(--orange);font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:.8rem;display:flex;align-items:center;justify-content:center;border:2px solid #fff;margin-left:-8px;}
  .team-member-avatar:first-child{margin-left:0;}
  .team-member-more{background:var(--bg);color:var(--muted);font-size:.7rem;font-weight:700;}
  .team-sport-tag{display:inline-flex;align-items:center;gap:5px;background:var(--orange-light);color:var(--orange);border-radius:20px;font-size:.75rem;font-weight:700;padding:3px 12px;margin-bottom:14px;text-transform:uppercase;letter-spacing:.04em;}
  .team-actions{display:flex;gap:8px;}
  .btn-join{background:var(--orange);color:#fff;flex:1;}
  .btn-join:hover{background:#d96a10;}
  .btn-joined{background:var(--orange-light);color:var(--orange);flex:1;cursor:default;}
  .btn-view{background:var(--bg);color:var(--text);border:1.5px solid var(--border);flex:1;}
  .btn-view:hover{border-color:var(--orange);color:var(--orange);}

  /* ── Skeleton ── */
  .skeleton{background:linear-gradient(90deg,#ece8e4 25%,#f5f2ef 50%,#ece8e4 75%);background-size:200%;animation:shimmer 1.4s infinite;border-radius:10px;}
  @keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
  .skel-card{background:var(--card);border-radius:var(--radius);padding:24px 18px 18px;border:1.5px solid var(--border);display:flex;flex-direction:column;align-items:center;gap:10px;}
  .skel-avatar{width:64px;height:64px;border-radius:50%;}
  .skel-line{height:12px;width:80%;}
  .skel-line-sm{height:10px;width:55%;}
  .skel-btn{height:34px;width:100%;border-radius:10px;}

  .empty-state{text-align:center;padding:40px 20px;color:var(--muted);grid-column:1/-1;}
  .empty-state svg{width:48px;height:48px;margin-bottom:12px;opacity:.4;}
  .empty-state p{font-size:.95rem;}

  /* ── Chat Modal ── */
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:999;align-items:flex-end;justify-content:flex-end;padding:24px;}
  .modal-overlay.open{display:flex;}
  .chat-modal{background:#fff;border-radius:20px;width:340px;height:460px;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden;animation:slideUp .25s ease;}
  @keyframes slideUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
  .chat-header{background:var(--orange);color:#fff;padding:14px 18px;display:flex;align-items:center;gap:12px;}
  .chat-header .chat-avatar{width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1rem;}
  .chat-header .chat-name{font-weight:700;font-size:.95rem;flex:1;}
  .chat-header .chat-close{background:transparent;border:none;color:rgba(255,255,255,.8);cursor:pointer;font-size:1.2rem;line-height:1;padding:0 4px;}
  .chat-messages{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;}
  .msg{max-width:75%;}
  .msg.them{align-self:flex-start;}
  .msg.me{align-self:flex-end;}
  .msg-bubble{border-radius:14px;padding:9px 14px;font-size:.85rem;line-height:1.4;}
  .msg.them .msg-bubble{background:var(--bg);color:var(--text);border-bottom-left-radius:4px;}
  .msg.me .msg-bubble{background:var(--orange);color:#fff;border-bottom-right-radius:4px;}
  .msg-time{font-size:.68rem;color:var(--muted);margin-top:3px;}
  .msg.me .msg-time{text-align:right;}
  .chat-input{padding:12px 14px;border-top:1.5px solid var(--border);display:flex;gap:8px;}
  .chat-input input{flex:1;border:1.5px solid var(--border);border-radius:24px;padding:8px 16px;font-family:'Barlow',sans-serif;font-size:.85rem;outline:none;background:var(--bg);transition:border-color .2s;}
  .chat-input input:focus{border-color:var(--orange);}
  .chat-input button{background:var(--orange);border:none;border-radius:50%;width:36px;height:36px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .chat-input button svg{width:16px;height:16px;fill:#fff;}

  .toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(80px);background:#1A1A1A;color:#fff;border-radius:30px;padding:10px 24px;font-size:.875rem;font-weight:600;z-index:9999;transition:transform .3s ease;pointer-events:none;display:flex;align-items:center;gap:8px;}
  .toast.show{transform:translateX(-50%) translateY(0);}
  .toast-icon{color:var(--orange);}

  /* ── Profile Modal ── */
  .profile-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px;}
  .profile-overlay.open{display:flex;}
  .profile-modal{background:#fff;border-radius:24px;width:100%;max-width:420px;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.25);animation:popIn .25s cubic-bezier(.34,1.56,.64,1);}
  @keyframes popIn{from{transform:scale(.88);opacity:0}to{transform:scale(1);opacity:1}}

  .pm-banner{height:110px;position:relative;display:flex;align-items:flex-end;justify-content:center;padding-bottom:0;}
  .pm-banner-bg{position:absolute;inset:0;}
  .pm-banner-pattern{position:absolute;inset:0;opacity:.15;background-image:repeating-linear-gradient(45deg,#fff 0,#fff 2px,transparent 0,transparent 50%);background-size:14px 14px;}
  .pm-close{position:absolute;top:12px;right:12px;width:30px;height:30px;border-radius:50%;background:rgba(0,0,0,.25);border:none;cursor:pointer;color:#fff;font-size:1rem;display:flex;align-items:center;justify-content:center;transition:background .15s;}
  .pm-close:hover{background:rgba(0,0,0,.45);}

  .pm-avatar-wrap{position:absolute;bottom:-38px;left:50%;transform:translateX(-50%);}
  .pm-avatar{width:76px;height:76px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.8rem;color:var(--orange);border:4px solid #fff;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.15);}
  .pm-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%;}

  .pm-body{padding:52px 28px 26px;text-align:center;}
  .pm-name{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.5rem;text-transform:uppercase;margin-bottom:4px;}
  .pm-role{display:inline-block;font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:3px 12px;border-radius:20px;margin-bottom:10px;}
  .pm-bio{font-size:.88rem;color:var(--muted);line-height:1.5;margin-bottom:20px;min-height:36px;}
  .pm-stats{display:flex;justify-content:center;gap:0;border:1.5px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:22px;}
  .pm-stat{flex:1;padding:12px 8px;text-align:center;border-right:1.5px solid var(--border);}
  .pm-stat:last-child{border-right:none;}
  .pm-stat .sv{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.3rem;color:var(--orange);}
  .pm-stat .sl{font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;margin-top:1px;}
  .pm-meta{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-bottom:22px;}
  .pm-meta-tag{display:flex;align-items:center;gap:5px;background:var(--bg);border-radius:20px;padding:5px 12px;font-size:.78rem;color:var(--muted);}
  .pm-meta-tag svg{width:13px;height:13px;flex-shrink:0;color:var(--orange);}
  .pm-actions{display:flex;gap:10px;}
  .pm-actions .btn{padding:10px 8px;font-size:.85rem;}
  .avatar-clickable{cursor:pointer;transition:opacity .15s;}
  .avatar-clickable:hover{opacity:.85;}
</style>
</head>
<body>

<nav>
  <div class="nav-logo">
    <svg viewBox="0 0 28 28" fill="none">
      <circle cx="14" cy="14" r="13" stroke="#F07B20" stroke-width="2"/>
      <path d="M9 18l3-7 2 4 2-4 3 7" stroke="#F07B20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <circle cx="14" cy="8" r="2" fill="#F07B20"/>
    </svg>
    TOURNAMEET
  </div>
  <div class="nav-search">
    <input type="text" placeholder="Search tournaments, sports…"/>
    <button><svg viewBox="0 0 20 20"><path d="M13.6 12.2a6 6 0 1 0-1.4 1.4l4.2 4.2 1.4-1.4-4.2-4.2zm-5.6.8a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/></svg></button>
  </div>
  <div class="nav-icons">
    <button title="Messages"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></button>
    <button title="Location"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></button>
    <button title="Friend Requests" id="req-nav-btn" onclick="switchTab('requests', document.querySelector('[data-tab=requests]'))">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span class="req-badge" id="nav-req-badge">0</span>
    </button>
    <button title="Notifications"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg></button>
    <button class="active" title="Profile"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></button>
  </div>
</nav>

<div class="page-header">
  <h1>Teams &amp; Friends</h1>
  <p>Connect with athletes, coaches, and join teams in your sport</p>
</div>

<div class="tab-bar">
  <button class="tab active" data-tab="friends"   onclick="switchTab('friends',this)">Friends &amp; Athletes</button>
  <button class="tab"        data-tab="coaches"   onclick="switchTab('coaches',this)">Coaches &amp; Organizers</button>
  <button class="tab"        data-tab="teams"     onclick="switchTab('teams',this)">Teams</button>
  <button class="tab"        data-tab="requests"  onclick="switchTab('requests',this)">
    Requests <span class="tab-badge" id="tab-req-badge">0</span>
  </button>
</div>

<div class="main">

  <!-- FRIENDS TAB -->
  <div id="tab-friends">
    <div class="section-title">My Friends <span id="friends-count">…</span></div>
    <div class="people-grid" id="friends-list"></div>
    <div class="section-divider"></div>
    <div class="section-title">Athletes you may know <span>Suggested</span></div>
    <div class="people-grid" id="suggested-athletes"></div>
  </div>

  <!-- COACHES TAB -->
  <div id="tab-coaches" style="display:none">
    <div class="section-title">My Coaches <span id="coaches-count">…</span></div>
    <div class="people-grid" id="coaches-list"></div>
    <div class="section-divider"></div>
    <div class="section-title">Organizers &amp; Coaches near you <span>Suggested</span></div>
    <div class="people-grid" id="suggested-coaches"></div>
  </div>

  <!-- TEAMS TAB -->
  <div id="tab-teams" style="display:none">
    <div class="section-title">My Teams <span id="myteams-count">…</span></div>
    <div class="teams-grid" id="my-teams"></div>
    <div class="section-divider"></div>
    <div class="section-title">Open Teams to Join <span>Available</span></div>
    <div class="teams-grid" id="open-teams"></div>
  </div>

  <!-- REQUESTS TAB -->
  <div id="tab-requests" style="display:none">
    <div class="section-title">Incoming Friend Requests <span id="req-count">…</span></div>
    <div class="people-grid" id="requests-list"></div>
  </div>

</div>

<!-- PROFILE MODAL -->
<div class="profile-overlay" id="profileModal" onclick="closeProfileOnBg(event)">
  <div class="profile-modal" id="profileModalInner">
    <div class="pm-banner">
      <div class="pm-banner-bg" id="pmBannerBg"></div>
      <div class="pm-banner-pattern"></div>
      <button class="pm-close" onclick="closeProfile()">✕</button>
      <div class="pm-avatar-wrap">
        <div class="pm-avatar" id="pmAvatar"></div>
      </div>
    </div>
    <div class="pm-body">
      <div class="pm-name" id="pmName">—</div>
      <span class="pm-role" id="pmRole">Athlete</span>
      <div class="pm-bio" id="pmBio">No bio yet.</div>
      <div class="pm-stats">
        <div class="pm-stat"><div class="sv" id="pmFriends">—</div><div class="sl">Friends</div></div>
        <div class="pm-stat"><div class="sv" id="pmTeams">—</div><div class="sl">Teams</div></div>
        <div class="pm-stat"><div class="sv" id="pmJoined">—</div><div class="sl">Joined</div></div>
      </div>
      <div class="pm-meta" id="pmMeta"></div>
      <div class="pm-actions" id="pmActions"></div>
    </div>
  </div>
</div>

<!-- CHAT MODAL -->
<div class="modal-overlay" id="chatModal">
  <div class="chat-modal">
    <div class="chat-header">
      <div class="chat-avatar" id="chatAvatar">?</div>
      <div class="chat-name" id="chatName">Chat</div>
      <button class="chat-close" onclick="closeChat()">✕</button>
    </div>
    <div class="chat-messages" id="chatMessages"></div>
    <div class="chat-input">
      <input type="text" id="chatInput" placeholder="Type a message…" onkeydown="if(event.key==='Enter')sendMsg()"/>
      <button onclick="sendMsg()"><svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/></svg></button>
    </div>
  </div>
</div>

<div class="toast" id="toast">
  <span class="toast-icon">✓</span>
  <span id="toastMsg">Done!</span>
</div>

<script>
const API = './api';

// ── Icons ──
const icons = {
  add:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>`,
  chat:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`,
  remove:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>`,
  check:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>`,
  clock:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>`,
};

function skeletonCards(n=4) {
  return Array(n).fill(`
    <div class="skel-card">
      <div class="skeleton skel-avatar"></div>
      <div class="skeleton skel-line"></div>
      <div class="skeleton skel-line-sm"></div>
      <div class="skeleton skel-btn"></div>
    </div>`).join('');
}

function emptyState(msg) {
  return `<div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
    <p>${msg}</p>
  </div>`;
}

// ── Person Card ──
function personCard(u, mode) {
  // mode: 'friend' | 'suggested' | 'request'
  const badgeClass = u.role === 'athlete' ? 'role-athlete' : 'role-coach';
  const badgeLabel = u.role === 'athlete' ? 'Athlete' : (u.role === 'organizer' ? 'Organizer' : 'Coach');
  const avatarInner = u.profile_pic
    ? `<img src="${u.profile_pic}" alt="${u.username}" onerror="this.style.display='none'">`
    : u.initials;

  let actions = '';

  if (mode === 'friend') {
    actions = `
      <button class="btn btn-chat" onclick="openChat('${esc(u.username)}','${esc(u.initials)}')">${icons.chat} Chat</button>
      <button class="btn btn-remove" onclick="removeFriend(${u.id})" title="Remove">${icons.remove}</button>`;

  } else if (mode === 'request') {
    actions = `
      <button class="btn btn-accept"  onclick="respondRequest(${u.friendship_id},'accept',  this)">${icons.check} Accept</button>
      <button class="btn btn-decline" onclick="respondRequest(${u.friendship_id},'decline', this)">${icons.remove} Decline</button>`;

  } else {
    // suggested
    if (u.friend_status === 'pending') {
      actions = `
        <button class="btn btn-pending" disabled>${icons.clock} Pending</button>
        <button class="btn btn-chat" onclick="openChat('${esc(u.username)}','${esc(u.initials)}')">${icons.chat}</button>`;
    } else {
      actions = `
        <button class="btn btn-add" id="add-${u.id}" onclick="sendRequest(${u.id},this)">${icons.add} Add</button>
        <button class="btn btn-chat" onclick="openChat('${esc(u.username)}','${esc(u.initials)}')">${icons.chat}</button>`;
    }
  }

  // Encode user data for click handler
  const uData = encodeURIComponent(JSON.stringify(u));

  return `
  <div class="person-card ${mode==='request'?'request-card':''}" id="pcard-${u.id}-${mode}">
    ${mode==='request' ? '<div class="request-label">Wants to connect</div>' : ''}
    <div class="avatar avatar-clickable" onclick="openProfile(decodeURIComponent('${uData}'), '${mode}')" title="View profile">${avatarInner}</div>
    <div class="person-name">${esc(u.username)}</div>
    <div class="person-sport">${esc(u.bio)}</div>
    <div class="role-badge ${badgeClass}">${badgeLabel}</div>
    <div class="card-actions">${actions}</div>
  </div>`;
}

function esc(str) {
  return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ── Load Friends ──
async function loadFriends() {
  document.getElementById('friends-list').innerHTML = skeletonCards();
  document.getElementById('suggested-athletes').innerHTML = skeletonCards();

  const [connRes, suggRes] = await Promise.all([
    fetch(`${API}/get_users.php?role=athlete&type=friends`).then(r=>r.json()).catch(()=>({data:[]})),
    fetch(`${API}/get_users.php?role=athlete&type=suggested`).then(r=>r.json()).catch(()=>({data:[]})),
  ]);

  const connected = connRes.data || [];
  const suggested = suggRes.data || [];

  document.getElementById('friends-count').textContent = connected.length + ' connected';
  document.getElementById('friends-list').innerHTML = connected.length
    ? connected.map(u => personCard(u,'friend')).join('')
    : emptyState('No friends added yet');

  document.getElementById('suggested-athletes').innerHTML = suggested.length
    ? suggested.map(u => personCard(u,'suggested')).join('')
    : emptyState('No suggestions available');
}

// ── Load Coaches ──
async function loadCoaches() {
  document.getElementById('coaches-list').innerHTML = skeletonCards();
  document.getElementById('suggested-coaches').innerHTML = skeletonCards();

  const [connRes, suggRes] = await Promise.all([
    fetch(`${API}/get_users.php?role=organizer&type=friends`).then(r=>r.json()).catch(()=>({data:[]})),
    fetch(`${API}/get_users.php?role=organizer&type=suggested`).then(r=>r.json()).catch(()=>({data:[]})),
  ]);

  const connected = connRes.data || [];
  const suggested = suggRes.data || [];

  document.getElementById('coaches-count').textContent = connected.length + ' connected';
  document.getElementById('coaches-list').innerHTML = connected.length
    ? connected.map(u => personCard(u,'friend')).join('')
    : emptyState('No coaches connected yet');

  document.getElementById('suggested-coaches').innerHTML = suggested.length
    ? suggested.map(u => personCard(u,'suggested')).join('')
    : emptyState('No suggestions available');
}

// ── Load Teams ──
async function loadTeams() {
  document.getElementById('my-teams').innerHTML = skeletonCards(2);
  document.getElementById('open-teams').innerHTML = skeletonCards(2);

  const [myRes, openRes] = await Promise.all([
    fetch(`${API}/get_teams.php?type=my`).then(r=>r.json()).catch(()=>({data:[]})),
    fetch(`${API}/get_teams.php?type=open`).then(r=>r.json()).catch(()=>({data:[]})),
  ]);

  const myTeams   = myRes.data   || [];
  const openTeams = openRes.data || [];

  document.getElementById('myteams-count').textContent = myTeams.length + ' joined';
  document.getElementById('my-teams').innerHTML = myTeams.length
    ? myTeams.map(t => teamCard(t,true)).join('')
    : emptyState("You haven't joined any teams yet");

  document.getElementById('open-teams').innerHTML = openTeams.length
    ? openTeams.map(t => teamCard(t,false)).join('')
    : emptyState('No open teams available right now');
}

// ── Load Incoming Requests ──
async function loadRequests() {
  document.getElementById('requests-list').innerHTML = skeletonCards();

  const res = await fetch(`${API}/get_users.php?role=athlete&type=requests`).then(r=>r.json()).catch(()=>({data:[]}));
  const requests = res.data || [];

  updateRequestBadge(requests.length);
  document.getElementById('req-count').textContent = requests.length + ' pending';
  document.getElementById('requests-list').innerHTML = requests.length
    ? requests.map(u => personCard(u,'request')).join('')
    : emptyState('No pending friend requests');
}

// ── Update badge counts ──
function updateRequestBadge(count) {
  const navBadge = document.getElementById('nav-req-badge');
  const tabBadge = document.getElementById('tab-req-badge');
  if (count > 0) {
    navBadge.textContent = count;
    navBadge.style.display = 'flex';
    tabBadge.textContent = count;
    tabBadge.style.display = 'inline';
  } else {
    navBadge.style.display = 'none';
    tabBadge.style.display = 'none';
  }
}

// ── Actions ──
async function sendRequest(userId, btn) {
  btn.disabled = true;
  btn.innerHTML = icons.clock + ' Sending…';

  const res = await fetch(`${API}/friend_action.php`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'send', friend_id: userId})
  }).then(r=>r.json()).catch(()=>({success:false}));

  if (res.success) {
    btn.className = 'btn btn-pending';
    btn.innerHTML = icons.clock + ' Pending';
    btn.disabled = true;
    toast('Friend request sent! ✓');
  } else {
    btn.disabled = false;
    btn.innerHTML = icons.add + ' Add';
    toast(res.error || 'Something went wrong');
  }
}

async function respondRequest(friendshipId, action, btn) {
  const card = btn.closest('.person-card');
  btn.disabled = true;

  const res = await fetch(`${API}/friend_action.php`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action, friendship_id: friendshipId})
  }).then(r=>r.json()).catch(()=>({success:false}));

  if (res.success) {
    toast(action === 'accept' ? '🎉 Friend request accepted!' : 'Request declined');
    card.style.opacity = '0';
    card.style.transform = 'scale(.95)';
    card.style.transition = 'all .3s';
    setTimeout(() => {
      card.remove();
      // Refresh counts
      loadRequests();
      if (action === 'accept') loadFriends();
    }, 350);
  } else {
    btn.disabled = false;
    toast(res.error || 'Something went wrong');
  }
}

async function removeFriend(id) {
  const card = document.getElementById('pcard-'+id+'-friend');
  if (card) { card.style.opacity='.4'; card.style.pointerEvents='none'; }

  const res = await fetch(`${API}/friend_action.php`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'remove', friend_id: id})
  }).then(r=>r.json()).catch(()=>({success:false}));

  if (res.success) {
    toast('Connection removed');
    setTimeout(() => card && card.remove(), 400);
  } else {
    if (card) { card.style.opacity='1'; card.style.pointerEvents=''; }
    toast('Something went wrong');
  }
}

async function joinTeam(id) {
  const btn = document.getElementById('join-'+id);
  if (btn) { btn.disabled=true; btn.textContent='Joining…'; }

  const res = await fetch(`${API}/join_team.php`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({team_id:id})
  }).then(r=>r.json()).catch(()=>({success:false}));

  if (res.success) {
    toast('You joined the team! 🎉');
    setTimeout(() => loadTeams(), 800);
  } else {
    if (btn) { btn.disabled=false; btn.textContent='Join Team'; }
    toast('Something went wrong');
  }
}

// ── Team Card ──
function teamCard(t, joined) {
  const color   = t.color || '#F07B20';
  const members = (t.members||[]).map(m =>
    `<div class="team-member-avatar">${m.profile_pic
      ? `<img src="${m.profile_pic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover">`
      : m.initials}</div>`).join('');
  const extra = (t.total_members||0) - (t.members||[]).length;

  return `
  <div class="team-card">
    <div class="team-banner" style="background:${color}">
      <div class="team-banner-pattern"></div>
      <div class="team-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" width="24" height="24"><circle cx="12" cy="12" r="10"/><path d="M12 2v20M2 12h20"/></svg>
      </div>
    </div>
    <div class="team-body">
      <div class="team-name">${esc(t.name)}</div>
      <div class="team-coach">Led by <strong>${esc(t.coach_name)}</strong></div>
      <div class="team-sport-tag">${esc(t.sport||'Sport')}</div>
      <div class="team-members">
        ${members}
        ${extra>0?`<div class="team-member-avatar team-member-more">+${extra}</div>`:''}
      </div>
      <div class="team-meta">
        <div class="team-meta-item"><div class="val">${t.total_members||0}</div><div class="lbl">Members</div></div>
        <div class="team-meta-item"><div class="val">${joined?'You':'Open'}</div><div class="lbl">Status</div></div>
      </div>
      <div class="team-actions">
        ${joined
          ? `<button class="btn btn-joined">✓ Joined</button>
             <button class="btn btn-view" onclick="openChat('${esc(t.name)} Team','${esc(t.name[0])}')">Team Chat</button>`
          : `<button class="btn btn-join" id="join-${t.id}" onclick="joinTeam(${t.id})">Join Team</button>
             <button class="btn btn-view">View</button>`
        }
      </div>
    </div>
  </div>`;
}

// ── Tabs ──
const tabLoaded = {};
function switchTab(tab, btn) {
  ['friends','coaches','teams','requests'].forEach(t => {
    document.getElementById('tab-'+t).style.display = t===tab ? 'block' : 'none';
  });
  document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  if (tab==='coaches'  && !tabLoaded.coaches)  { loadCoaches();  tabLoaded.coaches=true;  }
  if (tab==='teams'    && !tabLoaded.teams)    { loadTeams();    tabLoaded.teams=true;    }
  if (tab==='requests' && !tabLoaded.requests) { loadRequests(); tabLoaded.requests=true; }
  // Always reload requests to keep count fresh
  if (tab==='requests') { loadRequests(); }
}

// ── Chat ──
function openChat(name, init) {
  document.getElementById('chatName').textContent = name;
  document.getElementById('chatAvatar').textContent = init;
  document.getElementById('chatMessages').innerHTML = `
    <div class="msg them"><div class="msg-bubble">Hey! Ready for the next tournament?</div><div class="msg-time">Just now</div></div>`;
  document.getElementById('chatModal').classList.add('open');
  document.getElementById('chatInput').focus();
}
function closeChat() { document.getElementById('chatModal').classList.remove('open'); }
function sendMsg() {
  const inp = document.getElementById('chatInput');
  const text = inp.value.trim(); if (!text) return;
  const now = new Date().toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
  const msgs = document.getElementById('chatMessages');
  msgs.innerHTML += `<div class="msg me"><div class="msg-bubble">${esc(text)}</div><div class="msg-time">${now}</div></div>`;
  inp.value = ''; msgs.scrollTop = msgs.scrollHeight;
}

// ── Profile Modal ──
const bannerColors = ['#F07B20','#2775C9','#3EC87A','#9B59B6','#E04040','#F39C12','#1ABC9C'];

function openProfile(uJson, mode) {
  const u = JSON.parse(uJson);
  const color = bannerColors[u.id % bannerColors.length];

  // Banner
  document.getElementById('pmBannerBg').style.background = color;

  // Avatar
  const pmAv = document.getElementById('pmAvatar');
  pmAv.innerHTML = u.profile_pic
    ? `<img src="${u.profile_pic}" onerror="this.style.display='none'">${u.initials}`
    : u.initials;

  // Name & role
  document.getElementById('pmName').textContent = u.username;
  const roleEl = document.getElementById('pmRole');
  roleEl.textContent = u.role === 'athlete' ? 'Athlete' : (u.role === 'organizer' ? 'Organizer' : 'Coach');
  roleEl.className = 'pm-role ' + (u.role === 'athlete' ? 'role-athlete' : 'role-coach');

  // Bio
  document.getElementById('pmBio').textContent = u.bio || 'No bio yet.';

  // Stats — fetch live from server
  document.getElementById('pmFriends').textContent = '…';
  document.getElementById('pmTeams').textContent   = '…';
  document.getElementById('pmJoined').textContent  = '…';
  fetch(`${API}/get_profile_stats.php?user_id=${u.id}`)
    .then(r => r.json()).then(d => {
      document.getElementById('pmFriends').textContent = d.friends  ?? '0';
      document.getElementById('pmTeams').textContent   = d.teams    ?? '0';
      document.getElementById('pmJoined').textContent  = d.joined   ?? '—';
    }).catch(() => {
      document.getElementById('pmFriends').textContent = '0';
      document.getElementById('pmTeams').textContent   = '0';
      document.getElementById('pmJoined').textContent  = '—';
    });

  // Meta tags
  const joinYear = u.created_at ? new Date(u.created_at).getFullYear() : null;
  document.getElementById('pmMeta').innerHTML = `
    ${joinYear ? `<div class="pm-meta-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> Member since ${joinYear}</div>` : ''}
    <div class="pm-meta-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg> ${u.role==='athlete'?'Competing':'Coaching'}</div>
  `;

  // Action buttons
  const actEl = document.getElementById('pmActions');
  if (mode === 'friend') {
    actEl.innerHTML = `
      <button class="btn btn-chat" style="flex:2" onclick="closeProfile();openChat('${esc(u.username)}','${esc(u.initials)}')">${icons.chat} Send Message</button>
      <button class="btn btn-remove" style="flex:0 0 auto;padding:10px 14px" onclick="closeProfile();removeFriend(${u.id})" title="Remove friend">${icons.remove}</button>`;
  } else if (mode === 'request') {
    actEl.innerHTML = `
      <button class="btn btn-accept"  onclick="closeProfile();respondRequest(${u.friendship_id},'accept', this)">${icons.check} Accept</button>
      <button class="btn btn-decline" onclick="closeProfile();respondRequest(${u.friendship_id},'decline',this)">${icons.remove} Decline</button>`;
  } else {
    if (u.friend_status === 'pending') {
      actEl.innerHTML = `
        <button class="btn btn-pending" disabled style="flex:2">${icons.clock} Request Pending</button>
        <button class="btn btn-chat" onclick="closeProfile();openChat('${esc(u.username)}','${esc(u.initials)}')">${icons.chat}</button>`;
    } else {
      actEl.innerHTML = `
        <button class="btn btn-add" style="flex:2" onclick="closeProfile();sendRequest(${u.id}, document.getElementById('add-${u.id}'))">${icons.add} Add Friend</button>
        <button class="btn btn-chat" onclick="closeProfile();openChat('${esc(u.username)}','${esc(u.initials)}')">${icons.chat}</button>`;
    }
  }

  document.getElementById('profileModal').classList.add('open');
}

function closeProfile() {
  document.getElementById('profileModal').classList.remove('open');
}

function closeProfileOnBg(e) {
  if (e.target === document.getElementById('profileModal')) closeProfile();
}

// ── Toast ──
function toast(msg) {
  document.getElementById('toastMsg').textContent = msg;
  const el = document.getElementById('toast');
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 2600);
}

// ── Init ──
loadFriends();

// Poll for new requests every 30s (badge update)
async function pollRequests() {
  const res = await fetch(`${API}/get_friend_requests.php`).then(r=>r.json()).catch(()=>({count:0}));
  updateRequestBadge(res.count || 0);
}
pollRequests();
setInterval(pollRequests, 30000);
</script>
</body>
</html>