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
  .nav-left{display:flex;align-items:center;}
  .home-btn{display:flex;align-items:center;gap:8px;background:var(--orange);color:#fff;border:none;border-radius:10px;padding:8px 16px;font-family:'Barlow',sans-serif;font-weight:600;font-size:.9rem;cursor:pointer;transition:background .15s,transform .1s;}
  .home-btn:hover{background:#d96a10;}
  .home-btn:active{transform:scale(.97);}
  .home-btn span{letter-spacing:.02em;}
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

  /* Profile Modal */
  .profile-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1000;align-items:center;justify-content:center;padding:20px;}
  .profile-overlay.open{display:flex;}
  .profile-modal{background:#fff;border-radius:24px;width:100%;max-width:500px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.3);animation:popIn .25s cubic-bezier(.34,1.56,.64,1);}
  @keyframes popIn{from{transform:scale(.88);opacity:0}to{transform:scale(1);opacity:1}}
  .pm-banner{height:100px;position:relative;flex-shrink:0;}
  .pm-banner-bg{position:absolute;inset:0;}
  .pm-banner-pattern{position:absolute;inset:0;opacity:.15;background-image:repeating-linear-gradient(45deg,#fff 0,#fff 2px,transparent 0,transparent 50%);background-size:14px 14px;}
  .pm-close{position:absolute;top:10px;right:10px;width:28px;height:28px;border-radius:50%;background:rgba(0,0,0,.28);border:none;cursor:pointer;color:#fff;font-size:.95rem;display:flex;align-items:center;justify-content:center;transition:background .15s;}
  .pm-close:hover{background:rgba(0,0,0,.5);}
  .pm-avatar-wrap{position:absolute;bottom:-30px;left:22px;}
  .pm-avatar{width:64px;height:64px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.5rem;color:var(--orange);border:3px solid #fff;overflow:hidden;box-shadow:0 4px 14px rgba(0,0,0,.15);}
  .pm-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
  .pm-scroll{overflow-y:auto;flex:1;}
  .pm-scroll::-webkit-scrollbar{width:4px;}
  .pm-scroll::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
  .pm-info{padding:38px 22px 16px;border-bottom:1.5px solid var(--border);}
  .pm-name{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.3rem;text-transform:uppercase;margin-bottom:4px;}
  .pm-role{display:inline-block;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:2px 10px;border-radius:20px;margin-bottom:8px;}
  .pm-bio{font-size:.84rem;color:var(--muted);line-height:1.5;margin-bottom:14px;}
  .pm-stats{display:flex;gap:0;border:1.5px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:14px;}
  .pm-stat{flex:1;padding:10px 6px;text-align:center;border-right:1.5px solid var(--border);}
  .pm-stat:last-child{border-right:none;}
  .pm-stat .sv{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.15rem;color:var(--orange);}
  .pm-stat .sl{font-size:.62rem;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;}
  .pm-actions{display:flex;gap:8px;}
  .pm-actions .btn{padding:9px 8px;font-size:.82rem;flex:1;}
  .pm-posts{padding:16px 22px 22px;}
  .pm-posts-title{font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:.82rem;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:12px;display:flex;align-items:center;gap:8px;}
  .pm-posts-title span{background:var(--orange-light);color:var(--orange);border-radius:20px;font-size:.68rem;padding:1px 8px;font-weight:700;}
  .pm-post{background:var(--bg);border-radius:12px;padding:13px;margin-bottom:10px;border:1.5px solid var(--border);}
  .pm-post:last-child{margin-bottom:0;}
  .pm-post-top{display:flex;align-items:center;gap:9px;margin-bottom:9px;}
  .pm-post-av{width:32px;height:32px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:.78rem;color:var(--orange);flex-shrink:0;overflow:hidden;}
  .pm-post-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
  .pm-post-name{font-weight:700;font-size:.84rem;}
  .pm-post-time{font-size:.7rem;color:var(--muted);}
  .pm-post-content{font-size:.84rem;line-height:1.55;color:var(--text);white-space:pre-wrap;margin-bottom:9px;}
  .pm-post-img{width:100%;border-radius:9px;max-height:200px;object-fit:cover;margin-bottom:9px;display:block;}
  .pm-post-foot{display:flex;gap:6px;padding-top:8px;border-top:1px solid var(--border);}
  .pm-like-btn{display:flex;align-items:center;gap:4px;background:transparent;border:none;cursor:pointer;font-family:'Barlow',sans-serif;font-size:.76rem;font-weight:600;color:var(--muted);padding:4px 8px;border-radius:8px;transition:all .15s;}
  .pm-like-btn:hover{background:#fff;color:var(--text);}
  .pm-like-btn.liked{color:var(--red);}
  .pm-like-btn svg{width:13px;height:13px;}
  .pm-no-posts{text-align:center;padding:24px 0;color:var(--muted);font-size:.86rem;}
  .pm-posts-loading{text-align:center;padding:18px 0;color:var(--muted);font-size:.84rem;}
  .avatar-clickable{cursor:pointer;transition:opacity .15s;}
  .avatar-clickable:hover{opacity:.82;}

  /* ── POST DETAIL MODAL ── */
  .post-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:2000;display:none;align-items:center;justify-content:center;padding:16px;}
  .post-modal-overlay.open{display:flex;}
  .post-modal{background:#fff;border-radius:20px;width:100%;max-width:680px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;position:relative;}
  .post-modal-close{position:absolute;top:12px;right:14px;background:rgba(0,0,0,.45);color:#fff;border:none;width:30px;height:30px;border-radius:50%;font-size:16px;cursor:pointer;z-index:10;display:flex;align-items:center;justify-content:center;line-height:1;}
  .post-modal-media{background:#111;max-height:360px;overflow:hidden;flex-shrink:0;}
  .post-modal-media img,.post-modal-media video{width:100%;max-height:360px;object-fit:contain;display:block;}
  .post-modal-body{flex:1;overflow-y:auto;padding:16px 20px;}
  .post-modal-body::-webkit-scrollbar{width:3px;}
  .post-modal-body::-webkit-scrollbar-thumb{background:var(--border);}
  .post-modal-author{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
  .post-modal-av{width:38px;height:38px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:.9rem;color:var(--orange);overflow:hidden;flex-shrink:0;position:relative;}
  .post-modal-av img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:50%;}
  .post-modal-username{font-weight:700;font-size:.92rem;}
  .post-modal-time{font-size:.72rem;color:var(--muted);}
  .post-modal-caption{font-size:.9rem;line-height:1.6;margin-bottom:14px;white-space:pre-wrap;}
  .post-reactions-bar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border);}
  .reaction-btn{display:flex;align-items:center;gap:4px;background:var(--bg);border:1.5px solid var(--border);border-radius:20px;padding:5px 12px;cursor:pointer;font-size:.85rem;font-weight:600;transition:all .15s;color:var(--text);}
  .reaction-btn:hover{border-color:var(--orange);background:var(--orange-light);}
  .reaction-btn.active{background:var(--orange-light);border-color:var(--orange);color:var(--orange);}
  .reaction-btn .emoji{font-size:1.1rem;}
  .reaction-btn .cnt{font-size:.78rem;}
  .post-like-row{display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border);}
  .post-like-btn{display:flex;align-items:center;gap:6px;background:none;border:1.5px solid var(--border);border-radius:20px;padding:6px 14px;cursor:pointer;font-size:.82rem;font-weight:600;color:var(--muted);transition:all .15s;}
  .post-like-btn:hover{border-color:#E04040;color:#E04040;}
  .post-like-btn.liked{border-color:#E04040;color:#E04040;background:#fff0f0;}
  .post-like-btn svg{width:14px;height:14px;}
  .comments-title{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:10px;}
  .comment-item{display:flex;gap:9px;margin-bottom:12px;}
  .comment-av{width:30px;height:30px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:.7rem;color:var(--orange);flex-shrink:0;overflow:hidden;position:relative;}
  .comment-av img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:50%;}
  .comment-bubble{background:var(--bg);border-radius:12px;padding:8px 12px;flex:1;}
  .comment-name{font-weight:700;font-size:.78rem;margin-bottom:2px;}
  .comment-body{font-size:.82rem;line-height:1.5;white-space:pre-wrap;}
  .comment-time{font-size:.67rem;color:var(--muted);margin-top:3px;}
  .no-comments{text-align:center;padding:16px 0;color:var(--muted);font-size:.83rem;}
  .comment-input-row{display:flex;gap:8px;align-items:flex-end;padding:12px 20px;border-top:1px solid var(--border);background:#fff;flex-shrink:0;}
  .comment-input-row textarea{flex:1;border:1.5px solid var(--border);border-radius:16px;padding:8px 14px;font-family:'Barlow',sans-serif;font-size:.85rem;outline:none;resize:none;max-height:80px;background:var(--bg);transition:border-color .2s;line-height:1.4;}
  .comment-input-row textarea:focus{border-color:var(--orange);}
  .comment-send-btn{width:36px;height:36px;border-radius:50%;background:var(--orange);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .comment-send-btn:hover{background:#d96a10;}
  .comment-send-btn svg{width:14px;height:14px;fill:#fff;}

</style>
</head>
<body>

<nav>
  <div class="nav-left">
    <button class="home-btn" onclick="window.location.href='/Tourna/Tournafinal/Tournameet/index.php'">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
      <span>HOME</span>
    </button>
  </div>
  <div class="nav-search">
    <input type="text" placeholder="Search tournaments, sports…"/>
    <button><svg viewBox="0 0 20 20"><path d="M13.6 12.2a6 6 0 1 0-1.4 1.4l4.2 4.2 1.4-1.4-4.2-4.2zm-5.6.8a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/></svg></button>
  </div>
  <div class="nav-icons">
    <button title="Messages" onclick="location.href='Chats/chat.php'" style="position:relative" id="msgNavBtn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span class="req-badge" id="msg-badge" style="display:none">0</span></button>
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
    <!-- Banner -->
    <div class="pm-banner">
      <div class="pm-banner-bg" id="pmBannerBg"></div>
      <div class="pm-banner-pattern"></div>
      <button class="pm-close" onclick="closeProfile()">&#x2715;</button>
      <div class="pm-avatar-wrap">
        <div class="pm-avatar" id="pmAvatar"></div>
      </div>
    </div>
    <!-- Scrollable content -->
    <div class="pm-scroll">
      <!-- Info -->
      <div class="pm-info">
        <div class="pm-name" id="pmName">—</div>
        <span class="pm-role" id="pmRole">Athlete</span>
        <div class="pm-bio" id="pmBio">No bio yet.</div>
        <div class="pm-stats">
          <div class="pm-stat"><div class="sv" id="pmFriends">—</div><div class="sl">Friends</div></div>
          <div class="pm-stat"><div class="sv" id="pmTeams">—</div><div class="sl">Teams</div></div>
          <div class="pm-stat"><div class="sv" id="pmPosts">—</div><div class="sl">Posts</div></div>
          <div class="pm-stat"><div class="sv" id="pmJoined">—</div><div class="sl">Joined</div></div>
        </div>
        <div class="pm-actions" id="pmActions"></div>
      </div>
      <!-- Posts -->
      <div class="pm-posts">
        <div class="pm-posts-title">Posts <span id="pmPostsCount">0</span></div>
        <div id="pmPostsFeed"><div class="pm-posts-loading">Loading posts…</div></div>
      </div>
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
    ? `<img src="${u.profile_pic}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;" onload="this.nextElementSibling.style.display='none'" onerror="this.style.display='none'"><span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">${esc(u.initials||u.username.substring(0,2).toUpperCase())}</span>`
    : esc(u.initials||u.username.substring(0,2).toUpperCase());

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

  return `
  <div class="person-card ${mode==='request'?'request-card':''}" id="pcard-${u.id}-${mode}">
    ${mode==='request' ? '<div class="request-label">Wants to connect</div>' : ''}
    <div class="avatar avatar-clickable" onclick="openProfile(this.dataset.u, '${mode}')" data-u="${encodeURIComponent(JSON.stringify(u))}" title="View profile">${avatarInner}</div>
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

// Profile Modal
const bannerColors = ['#F07B20','#2775C9','#3EC87A','#9B59B6','#E04040','#F39C12','#1ABC9C'];

function timeAgoJS(dateStr) {
  const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
  if (diff < 60)     return 'Just now';
  if (diff < 3600)   return Math.floor(diff/60)   + 'm ago';
  if (diff < 86400)  return Math.floor(diff/3600)  + 'h ago';
  if (diff < 604800) return Math.floor(diff/86400) + 'd ago';
  return new Date(dateStr).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
}

async function openProfile(uJson, mode) {
  const u = JSON.parse(decodeURIComponent(uJson));
  const color = bannerColors[u.id % bannerColors.length];

  // Banner color
  document.getElementById('pmBannerBg').style.background = color;

  // Avatar
  const pmAv = document.getElementById('pmAvatar');
  if (u.profile_pic) {
    pmAv.innerHTML = '';
    const _img = document.createElement('img');
    _img.src = u.profile_pic;
    _img.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:50%;';
    _img.onerror = function(){ pmAv.innerHTML = esc(u.initials||u.username.substring(0,2).toUpperCase()); };
    pmAv.appendChild(_img);
  } else {
    pmAv.innerHTML = esc(u.initials||u.username.substring(0,2).toUpperCase());
  }

  // Name & role
  document.getElementById('pmName').textContent = u.username;
  const roleEl = document.getElementById('pmRole');
  roleEl.textContent = u.role === 'athlete' ? 'Athlete' : (u.role === 'organizer' ? 'Organizer' : 'Coach');
  roleEl.className = 'pm-role ' + (u.role === 'athlete' ? 'role-athlete' : 'role-coach');

  // Bio
  document.getElementById('pmBio').textContent = u.bio || 'No bio yet.';

  // Stats
  document.getElementById('pmFriends').textContent = '…';
  document.getElementById('pmTeams').textContent   = '…';
  document.getElementById('pmPosts').textContent   = '…';
  document.getElementById('pmJoined').textContent  = '…';

  // Action buttons
  renderPmActions(u, mode);

  // Open modal
  document.getElementById('profileModal').classList.add('open');
  // Reset scroll
  document.querySelector('.pm-scroll').scrollTop = 0;

  // Show posts loading state
  document.getElementById('pmPostsFeed').innerHTML = '<div class="pm-posts-loading">Loading posts…</div>';
  document.getElementById('pmPostsCount').textContent = '…';

  // Fetch stats + posts in parallel
  const [statsRes, postsRes] = await Promise.all([
    fetch(`${API}/get_profile_stats.php?user_id=${u.id}`).then(r=>r.json()).catch(()=>({})),
    fetch(`${API}/get_user_posts.php?user_id=${u.id}`).then(r=>r.json()).catch(()=>({data:[]}))
  ]);

  // Fill stats
  document.getElementById('pmFriends').textContent = statsRes.friends ?? '0';
  document.getElementById('pmTeams').textContent   = statsRes.teams   ?? '0';
  document.getElementById('pmJoined').textContent  = statsRes.joined  ?? '—';

  // Fill posts
  const posts = postsRes.data || [];
  document.getElementById('pmPosts').textContent     = posts.length;
  document.getElementById('pmPostsCount').textContent = posts.length;

  if (!posts.length) {
    document.getElementById('pmPostsFeed').innerHTML =
      `<div class="pm-no-posts">No posts yet.</div>`;
    return;
  }

  const _init = esc(u.initials || u.username.substring(0,2).toUpperCase());
  const avatarHtml = u.profile_pic
    ? `<img src="${u.profile_pic}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.outerHTML='<span>'+_init+'</span>'">`
    : _init;

  document.getElementById('pmPostsFeed').innerHTML = posts.map(p => {
    // Build media HTML from media_files array
    const media = p.media_files || [];
    let mediaHtml = '';
    if (media.length === 1) {
      const m = media[0];
      mediaHtml = m.media_type === 'video'
        ? `<video class="pm-post-img" src="${esc(m.url)}" controls muted style="max-height:200px;width:100%;object-fit:cover;border-radius:9px;margin-bottom:9px;"></video>`
        : `<img class="pm-post-img" src="${esc(m.url)}" alt="" onerror="this.style.display='none'">`;
    } else if (media.length > 1) {
      mediaHtml = `<div style="display:grid;grid-template-columns:repeat(${Math.min(media.length,3)},1fr);gap:4px;margin-bottom:9px;border-radius:9px;overflow:hidden;">
        ${media.slice(0,3).map(m => m.media_type === 'video'
          ? `<video src="${esc(m.url)}" muted style="width:100%;aspect-ratio:1;object-fit:cover;"></video>`
          : `<img src="${esc(m.url)}" style="width:100%;aspect-ratio:1;object-fit:cover;" onerror="this.style.display='none'">`
        ).join('')}
        ${media.length > 3 ? `<div style="aspect-ratio:1;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.1rem;">+${media.length-3}</div>` : ''}
      </div>`;
    }
    const caption = p.caption || p.content || '';
    // Make media clickable to open post detail
    const clickableMedia = mediaHtml.replace('<div class="msg-media"', '<div class="msg-media" style="cursor:pointer"')
      .replace(/(<img [^>]+)(>)/g, '$1 onclick="openPostModal('+p.id+', postDataStore['+p.id+'])" style="cursor:pointer"$2')
      .replace(/(<video [^>]+)(>)/g, '$1 onclick="openPostModal('+p.id+', postDataStore['+p.id+'])"$2');
    postDataStore[p.id] = {post: p, user: u};
    return `
    <div class="pm-post" id="pmpost-${p.id}" style="cursor:pointer" onclick="openPostModal(${p.id}, postDataStore[${p.id}])">
      <div class="pm-post-top">
        <div class="pm-post-av">${avatarHtml}</div>
        <div>
          <div class="pm-post-name">${esc(u.username)}</div>
          <div class="pm-post-time">${timeAgoJS(p.created_at)}</div>
        </div>
      </div>
      ${caption ? `<div class="pm-post-content">${esc(caption)}</div>` : ''}
      ${mediaHtml}
      <div class="pm-post-foot">
        <button class="pm-like-btn ${p.liked_by_me ? 'liked' : ''}"
                id="pmlike-${p.id}"
                onclick="pmToggleLike(${p.id}, this)">
          <svg viewBox="0 0 24 24" fill="${p.liked_by_me ? 'var(--red)' : 'none'}" stroke="${p.liked_by_me ? 'var(--red)' : 'currentColor'}" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
          <span id="pmlike-count-${p.id}">${p.like_count||0}</span> Likes
        </button>
      </div>
    </div>`;
  }).join('');
}

function renderPmActions(u, mode) {
  const actEl = document.getElementById('pmActions');
  if (mode === 'friend') {
    actEl.innerHTML = `
      <button class="btn btn-chat" onclick="closeProfile();openChat('${esc(u.username)}','${esc(u.initials)}')">${icons.chat} Message</button>
      <button class="btn btn-remove" style="flex:0 0 auto;padding:9px 12px" onclick="closeProfile();removeFriend(${u.id})" title="Remove">${icons.remove}</button>`;
  } else if (mode === 'request') {
    actEl.innerHTML = `
      <button class="btn btn-accept"  onclick="closeProfile();respondRequest(${u.friendship_id},'accept', this)">${icons.check} Accept</button>
      <button class="btn btn-decline" onclick="closeProfile();respondRequest(${u.friendship_id},'decline',this)">${icons.remove} Decline</button>`;
  } else {
    if (u.friend_status === 'pending') {
      actEl.innerHTML = `
        <button class="btn btn-pending" disabled>${icons.clock} Pending</button>
        <button class="btn btn-chat" onclick="closeProfile();openChat('${esc(u.username)}','${esc(u.initials)}')">${icons.chat}</button>`;
    } else {
      actEl.innerHTML = `
        <button class="btn btn-add" onclick="closeProfile();sendRequest(${u.id}, document.getElementById('add-${u.id}'))">${icons.add} Add Friend</button>
        <button class="btn btn-chat" onclick="closeProfile();openChat('${esc(u.username)}','${esc(u.initials)}')">${icons.chat}</button>`;
    }
  }
}

async function pmToggleLike(postId, btn) {
  const res = await fetch(`${API}/toggle_like.php`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({post_id: postId})
  }).then(r=>r.json()).catch(()=>({success:false}));
  if (res.success) {
    document.getElementById('pmlike-count-' + postId).textContent = res.like_count;
    const svg = btn.querySelector('svg');
    if (res.liked) {
      btn.classList.add('liked');
      svg.setAttribute('fill','var(--red)'); svg.setAttribute('stroke','var(--red)');
    } else {
      btn.classList.remove('liked');
      svg.setAttribute('fill','none'); svg.setAttribute('stroke','currentColor');
    }
  }
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

// Poll unread message count for nav badge
async function pollUnreadMsgs() {
  const res = await fetch('./api/chat_api.php?action=unread_count').then(r=>r.json()).catch(()=>({count:0}));
  const badge = document.getElementById('msg-badge');
  if (res.count > 0) {
    badge.textContent = res.count > 9 ? '9+' : res.count;
    badge.style.display = 'flex';
  } else {
    badge.style.display = 'none';
  }
}
pollUnreadMsgs();
setInterval(pollUnreadMsgs, 15000);


// ── POST DATA STORE ──
const postDataStore = {};

// ── POST MODAL ──
let activePostId = null;

async function openPostModal(postId, store) {
  activePostId = postId;
  const p = store.post;
  const u = store.user;
  document.getElementById('postModal').classList.add('open');
  document.getElementById('postCommentInput').style.display = 'flex';

  // Media
  const mediaEl = document.getElementById('postModalMedia');
  const files = p.media_files || [];
  if (files.length) {
    const f = files[0];
    mediaEl.style.display = '';
    mediaEl.innerHTML = f.media_type === 'video'
      ? `<video src="${esc(f.url)}" controls style="width:100%;max-height:360px;object-fit:contain;"></video>`
      : `<img src="${esc(f.url)}" alt="" style="width:100%;max-height:360px;object-fit:contain;">`;
  } else {
    mediaEl.style.display = 'none';
  }

  // Body skeleton
  document.getElementById('postModalBody').innerHTML = '<div style="padding:20px;color:var(--muted);font-size:.85rem;">Loading…</div>';

  // Author avatar
  const avHtml = u.profile_pic
    ? `<img src="${esc(u.profile_pic)}" onerror="this.style.display='none'">`
    : esc(u.initials || u.username.substring(0,2).toUpperCase());

  const caption = p.caption || p.content || '';

  // Use post_actions.php (same as profile.php) for reactions+comments
  const POST_ACTIONS = './api/post_actions_proxy.php';
  let reactions = [], comments = [], data_myReaction = null;
  try {
    const r    = await fetch(`${POST_ACTIONS}?action=load&post_id=${postId}`);
    const text = await r.text();
    // Show raw response in modal body for debugging
    document.getElementById('postModalBody').innerHTML =
      `<pre style="font-size:10px;white-space:pre-wrap;padding:10px;color:#333;">${text.slice(0,500)}</pre>`;
    let d;
    try { d = JSON.parse(text); } catch(e) { console.error('post_actions parse error:', text.slice(0,200)); d = {}; }
    if (d.success) {
      reactions       = Object.entries(d.counts||{}).map(([reaction,cnt])=>({reaction,cnt}));
      data_myReaction = d.myReaction || null;
      comments        = d.comments || [];
    } else {
      console.warn('post_actions load failed:', d.error || d);
    }
  } catch(e) { console.error('post_actions load error', e); }
  // Map profile.php reaction keys to emojis
  const REACT_MAP = {like:'👍', love:'❤️', fire:'🔥', wow:'😮', haha:'😂'};
  const REACT_KEYS = ['like','love','fire','wow','haha'];

  // Build reaction buttons matching profile.php format
  const reactBtns = REACT_KEYS.map(key => {
    const emoji = REACT_MAP[key];
    const found = reactions.find(r => r.reaction === key);
    const cnt   = found ? parseInt(found.cnt) : 0;
    const mine  = (data_myReaction === key);
    return `<button class="reaction-btn ${mine?'active':''}" id="react-${postId}-${key}"
      onclick="toggleReaction(${postId},'${key}',this)">
      <span class="emoji">${emoji}</span>
      <span class="cnt" id="rcnt-${postId}-${key}">${cnt||''}</span>
    </button>`;
  }).join('');

  // Build comment list
  const commentHtml = comments.length
    ? comments.map(cm => {
        const init = esc((cm.username||'?').substring(0,2).toUpperCase());
        const cmAv = cm.profile_pic
          ? `<img src="${esc(cm.profile_pic)}" onerror="this.style.display='none'">`
          : init;
        const cmText = cm.comment || cm.body || '';
        return `<div class="comment-item">
          <div class="comment-av">${cmAv}</div>
          <div class="comment-bubble">
            <div class="comment-name">${esc(cm.username)}</div>
            <div class="comment-body">${esc(cmText)}</div>
            <div class="comment-time">${timeAgoJS(cm.created_at)}</div>
          </div>
        </div>`;
      }).join('')
    : '<div class="no-comments">No comments yet. Be the first!</div>';

  document.getElementById('postModalBody').innerHTML = `
    <div class="post-modal-author">
      <div class="post-modal-av">${avHtml}</div>
      <div>
        <div class="post-modal-username">${esc(u.username)}</div>
        <div class="post-modal-time">${timeAgoJS(p.created_at)}</div>
      </div>
    </div>
    ${caption ? `<div class="post-modal-caption">${esc(caption)}</div>` : ''}
    <div class="post-like-row">
      <button class="post-like-btn ${p.liked_by_me?'liked':''}" id="postlike-${postId}" onclick="postModalLike(${postId},this)">
        <svg viewBox="0 0 24 24" fill="${p.liked_by_me?'#E04040':'none'}" stroke="${p.liked_by_me?'#E04040':'currentColor'}" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <span id="postlike-cnt-${postId}">${p.like_count||0}</span> Likes
      </button>
    </div>
    <div class="post-reactions-bar">${reactBtns}</div>
    <div class="comments-title">Comments <span style="background:var(--orange-light);color:var(--orange);border-radius:20px;padding:1px 8px;font-size:.68rem;">${comments.length}</span></div>
    <div id="comments-feed-${postId}">${commentHtml}</div>
  `;
}

function closePostModal() {
  document.getElementById('postModal').classList.remove('open');
  document.getElementById('postModalMedia').innerHTML = '';
  document.getElementById('postModalBody').innerHTML  = '';
  document.getElementById('commentTextarea').value    = '';
  activePostId = null;
}
function closePostModalBg(e) {
  if (e.target === document.getElementById('postModal')) closePostModal();
}

async function postModalLike(postId, btn) {
  const res = await fetch(`${API}/toggle_like.php`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({post_id: postId})
  }).then(r=>r.json()).catch(()=>({success:false}));
  if (!res.success) return;
  document.getElementById('postlike-cnt-'+postId).textContent = res.like_count;
  const svg = btn.querySelector('svg');
  if (res.liked) {
    btn.classList.add('liked');
    svg.setAttribute('fill','#E04040'); svg.setAttribute('stroke','#E04040');
  } else {
    btn.classList.remove('liked');
    svg.setAttribute('fill','none'); svg.setAttribute('stroke','currentColor');
  }
  // Sync the card like button too
  const cardBtn = document.getElementById('pmlike-'+postId);
  if (cardBtn) {
    document.getElementById('pmlike-count-'+postId).textContent = res.like_count;
    const cSvg = cardBtn.querySelector('svg');
    if (res.liked) { cardBtn.classList.add('liked'); cSvg.setAttribute('fill','var(--red)'); cSvg.setAttribute('stroke','var(--red)'); }
    else { cardBtn.classList.remove('liked'); cSvg.setAttribute('fill','none'); cSvg.setAttribute('stroke','currentColor'); }
  }
  if (postDataStore[postId]) postDataStore[postId].post.liked_by_me = res.liked;
}

async function toggleReaction(postId, reactionKey, btn) {
  const POST_ACTIONS = './api/post_actions_proxy.php';
  const fd = new FormData();
  fd.append('action',   'react');
  fd.append('post_id',  postId);
  fd.append('reaction', reactionKey);
  try {
    const res = await fetch(POST_ACTIONS, {method:'POST', body:fd}).then(r=>r.json());
    if (!res.success) return;

    // Update all reaction buttons for this post
    const REACT_KEYS = ['like','love','fire','wow','haha'];
    const REACT_MAP  = {like:'👍', love:'❤️', fire:'🔥', wow:'😮', haha:'😂'};
    REACT_KEYS.forEach(key => {
      const b    = document.getElementById(`react-${postId}-${key}`);
      const cntEl= document.getElementById(`rcnt-${postId}-${key}`);
      if (b) b.classList.toggle('active', res.myReaction === key);
      if (cntEl) cntEl.textContent = (res.counts?.[key] || '') + '';
    });
  } catch(e) { console.error('toggleReaction error', e); }
}

async function submitComment() {
  if (!activePostId) return;
  const ta   = document.getElementById('commentTextarea');
  const body = ta.value.trim();
  if (!body) return;

  // Optimistically clear input
  ta.value = ''; ta.style.height = 'auto';

  const POST_ACTIONS = './api/post_actions_proxy.php';
  let res;
  try {
    const fd = new FormData();
    fd.append('action',   'comment');
    fd.append('post_id',  activePostId);
    fd.append('comment',  body);  // profile.php uses 'comment' not 'body'
    const r = await fetch(POST_ACTIONS, {method:'POST', body:fd});
    res = await r.json();
  } catch(e) {
    console.error('Comment submit error:', e);
    return;
  }

  if (!res || !res.success) {
    const errMsg = res?.error || JSON.stringify(res);
    console.error('Comment failed:', errMsg);
    const feed2 = document.getElementById('comments-feed-' + activePostId);
    if (feed2) feed2.insertAdjacentHTML('beforeend',
      `<div style="color:red;font-size:.78rem;padding:6px 10px;background:#fff0f0;border-radius:8px;margin:4px 0;">
        ❌ ${errMsg}</div>`);
    return;
  }

  // profile.php returns: {success, comment_id, comment, username, profile_pic, created_at}
  const cm   = res;
  const init = esc((cm.username||'?').substring(0,2).toUpperCase());
  const picSrc = cm.profile_pic ? '/Tourna/uploads' + cm.profile_pic : null;
  const cmAv = picSrc
    ? `<img src="${esc(picSrc)}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.style.display='none'">`
    : init;

  // Find feed — try both with activePostId as number and string
  const feed = document.getElementById('comments-feed-' + activePostId);
  if (feed) {
    const noC = feed.querySelector('.no-comments');
    if (noC) noC.remove();
    feed.insertAdjacentHTML('beforeend', `
      <div class="comment-item">
        <div class="comment-av">${cmAv}</div>
        <div class="comment-bubble">
          <div class="comment-name">${esc(cm.username)}</div>
          <div class="comment-body">${esc(cm.comment || cm.body || body)}</div>
          <div class="comment-time">Just now</div>
        </div>
      </div>`);
    feed.lastElementChild.scrollIntoView({behavior:'smooth'});

    // Update comment count badge
    const countBadge = feed.closest('.post-modal-body')?.querySelector('.comments-title span');
    if (countBadge) countBadge.textContent = feed.querySelectorAll('.comment-item').length;
  } else {
    console.error('Feed element not found: comments-feed-' + activePostId);
  }
}
</script>

<!-- POST DETAIL MODAL -->
<div class="post-modal-overlay" id="postModal" onclick="closePostModalBg(event)">
  <div class="post-modal" id="postModalInner">
    <button class="post-modal-close" onclick="closePostModal()">✕</button>
    <div class="post-modal-media" id="postModalMedia"></div>
    <div class="post-modal-body" id="postModalBody"></div>
    <div class="comment-input-row" id="postCommentInput" style="display:none;">
      <textarea id="commentTextarea" placeholder="Write a comment…" rows="1"
        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();submitComment();}"
        oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,80)+'px'"></textarea>
      <button class="comment-send-btn" onclick="submitComment()">
        <svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/></svg>
      </button>
    </div>
  </div>
</div>

</body>
</html>