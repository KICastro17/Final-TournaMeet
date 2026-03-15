<?php
session_start();
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','user_system');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",DB_USER,DB_PASS,[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    ]);
} catch(PDOException $e){ die("DB error"); }

$current_user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['userId'] ?? null;
if (!$current_user_id) { header('Location: /Tourna/TF/Chats/chat.php'); exit; }

$me = $pdo->prepare("SELECT id,username,profile_pic FROM users WHERE id=?");
$me->execute([$current_user_id]);
$me = $me->fetch();
$myInitials = strtoupper(substr($me['username'],0,2));
$myPic = $me['profile_pic'] ? '/Tourna/uploads' . $me['profile_pic'] : null;
$openWith = (int)($_GET['with'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Messages – Tournameet</title>
  <link rel="icon" type="image/png" href="/Tourna/favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet"/>
<style>
:root{
  --orange:#F07B20;--orange-light:#FDE8D4;
  --bg:#F2F0EE;--text:#1A1A1A;
  --muted:#888;--border:#E8E4E0;
  --sidebar:280px;
}
*{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;overflow:hidden;font-family:'Barlow',sans-serif;background:var(--bg);color:var(--text);}

nav{height:58px;background:#fff;border-bottom:2px solid var(--orange);display:flex;align-items:center;padding:0 24px;gap:12px;position:fixed;top:0;left:0;right:0;z-index:100;}
.home-btn{display:flex;align-items:center;gap:7px;background:var(--orange);color:#fff;border:none;border-radius:10px;padding:7px 14px;font-family:'Barlow',sans-serif;font-weight:600;font-size:.88rem;cursor:pointer;transition:background .15s;}
.home-btn:hover{background:#d96a10;}
.nav-title{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.2rem;text-transform:uppercase;color:var(--orange);}

.chat-layout{display:flex;height:calc(100vh - 58px);margin-top:58px;}

/* SIDEBAR */
.sidebar{width:var(--sidebar);background:#fff;border-right:1.5px solid var(--border);display:flex;flex-direction:column;flex-shrink:0;}
.sidebar-head{padding:18px 16px 12px;border-bottom:1.5px solid var(--border);}
.sidebar-head h2{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.1rem;text-transform:uppercase;margin-bottom:10px;}
.search-box{display:flex;align-items:center;background:var(--bg);border-radius:30px;border:1.5px solid var(--border);padding:0 6px 0 12px;}
.search-box input{flex:1;border:none;background:transparent;outline:none;font-family:'Barlow',sans-serif;font-size:.84rem;padding:7px 0;color:var(--text);}
.friends-list{flex:1;overflow-y:auto;}
.friends-list::-webkit-scrollbar{width:3px;}
.friends-list::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px;}
.friend-item{display:flex;align-items:center;gap:11px;padding:12px 16px;cursor:pointer;transition:background .15s;border-bottom:1px solid var(--border);}
.friend-item:hover{background:var(--bg);}
.friend-item.active{background:var(--orange-light);}
.friend-item.active .fi-name{color:var(--orange);}
.fi-av{width:42px;height:42px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1rem;color:var(--orange);flex-shrink:0;overflow:hidden;}
.fi-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.fi-info{flex:1;min-width:0;}
.fi-name{font-weight:700;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.fi-last{font-size:.75rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;}
.fi-unread{background:var(--orange);color:#fff;font-size:.65rem;font-weight:800;border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.empty-friends{text-align:center;padding:32px 16px;color:var(--muted);}
.empty-friends p{font-size:.85rem;}

/* CHAT AREA */
.chat-area{flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden;}
.no-chat{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--muted);gap:12px;}
.no-chat svg{width:64px;height:64px;opacity:.2;}
.chat-head{padding:14px 20px;background:#fff;border-bottom:1.5px solid var(--border);display:flex;align-items:center;gap:12px;flex-shrink:0;}
.ch-av{width:40px;height:40px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:.95rem;color:var(--orange);overflow:hidden;flex-shrink:0;}
.ch-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.ch-name{font-weight:700;font-size:1rem;}
.ch-status{font-size:.75rem;color:#3EC87A;margin-top:1px;}

/* MESSAGES */
.messages-wrap{flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:6px;background:var(--bg);}
.messages-wrap::-webkit-scrollbar{width:4px;}
.messages-wrap::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}

/* MSG ROW with action buttons */
.msg-row{display:flex;align-items:flex-end;gap:8px;width:100%;position:relative;}
.msg-row.mine{flex-direction:row-reverse;}

/* Action buttons that appear on hover */
.msg-actions{
  display:flex;
  gap:4px;
  align-items:center;
  opacity:0;
  pointer-events:none;
  transition:opacity .18s;
  flex-shrink:0;
  order:2;
}
.msg-row.mine .msg-actions{order:0; flex-direction:row-reverse;}
.msg-row:hover .msg-actions{opacity:1;pointer-events:auto;}
.msg-action-btn{
  width:26px;height:26px;border-radius:50%;
  background:#fff;border:1.5px solid var(--border);
  cursor:pointer;display:flex;align-items:center;justify-content:center;
  transition:background .15s,border-color .15s,transform .1s;
  color:var(--muted);
  box-shadow:0 1px 4px rgba(0,0,0,.08);
}
.msg-action-btn:hover{background:var(--orange-light);border-color:var(--orange);color:var(--orange);transform:scale(1.1);}
.msg-action-btn.unsend-btn:hover{background:#FDECEA;border-color:#E53935;color:#E53935;}
.msg-action-btn svg{width:12px;height:12px;}

.msg-av{width:28px;height:28px;border-radius:50%;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:.65rem;color:var(--orange);flex-shrink:0;overflow:hidden;position:relative;}
.msg-av img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:50%;}
.msg-col{display:flex;flex-direction:column;max-width:65%;min-width:0;}
.msg-row.mine  .msg-col{align-items:flex-end;}
.msg-row.theirs .msg-col{align-items:flex-start;}
.msg-bubble{display:inline-block;padding:9px 14px;border-radius:18px;font-size:.88rem;line-height:1.5;word-wrap:break-word;overflow-wrap:break-word;white-space:pre-wrap;max-width:100%;}
.msg-row.theirs .msg-bubble{background:#fff;color:var(--text);border-bottom-left-radius:4px;box-shadow:0 1px 4px rgba(0,0,0,.06);}
.msg-row.mine   .msg-bubble{background:var(--orange);color:#fff;border-bottom-right-radius:4px;}
.msg-time{font-size:.65rem;color:var(--muted);margin-top:3px;padding:0 2px;display:flex;align-items:center;gap:4px;}
.msg-edited-tag{font-size:.6rem;color:var(--muted);font-style:italic;}
.date-divider{text-align:center;font-size:.72rem;color:var(--muted);margin:8px 0;font-weight:600;letter-spacing:.04em;text-transform:uppercase;}

/* Unsent message style */
.msg-unsent .msg-bubble{
  background:transparent !important;
  border:1.5px dashed var(--border);
  color:var(--muted) !important;
  font-style:italic;
  font-size:.8rem;
  box-shadow:none;
}
.msg-unsent .msg-av{opacity:.4;}

/* INLINE EDIT */
.msg-edit-wrap{
  display:flex;
  align-items:center;
  gap:6px;
  background:#fff;
  border:1.5px solid var(--orange);
  border-radius:18px;
  padding:6px 10px;
  max-width:380px;
  box-shadow:0 2px 12px rgba(240,123,32,.15);
}
.msg-edit-input{
  flex:1;border:none;outline:none;
  font-family:'Barlow',sans-serif;font-size:.88rem;
  background:transparent;color:var(--text);
  resize:none;max-height:80px;line-height:1.4;
}
.msg-edit-save{width:28px;height:28px;border-radius:50%;background:var(--orange);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s;}
.msg-edit-save:hover{background:#d96a10;}
.msg-edit-save svg{width:12px;height:12px;fill:#fff;}
.msg-edit-cancel{width:28px;height:28px;border-radius:50%;background:var(--bg);border:1.5px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--muted);font-size:12px;transition:background .15s;}
.msg-edit-cancel:hover{background:#FDECEA;border-color:#E53935;color:#E53935;}

/* MEDIA MESSAGES */
.msg-media{max-width:260px;border-radius:14px;overflow:hidden;cursor:pointer;display:block;}
.msg-media img{width:100%;max-height:280px;object-fit:cover;display:block;border-radius:14px;}
.msg-media video{width:100%;max-height:280px;border-radius:14px;display:block;}
.msg-row.mine .msg-media img,
.msg-row.mine .msg-media video{border-bottom-right-radius:4px;}
.msg-row.theirs .msg-media img,
.msg-row.theirs .msg-media video{border-bottom-left-radius:4px;}

/* MEDIA LIGHTBOX */
.media-lb{position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;display:none;align-items:center;justify-content:center;}
.media-lb.open{display:flex;}
.media-lb img,.media-lb video{max-width:92vw;max-height:92vh;border-radius:10px;object-fit:contain;}
.media-lb-close{position:absolute;top:20px;right:24px;color:#fff;font-size:28px;cursor:pointer;background:none;border:none;line-height:1;}

/* CONFIRM DIALOG */
.confirm-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:500;display:none;align-items:center;justify-content:center;}
.confirm-overlay.open{display:flex;}
.confirm-box{background:#fff;border-radius:18px;padding:24px 28px;max-width:320px;width:90%;box-shadow:0 16px 48px rgba(0,0,0,.2);}
.confirm-box h3{font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.1rem;text-transform:uppercase;margin-bottom:8px;color:var(--text);}
.confirm-box p{font-size:.85rem;color:var(--muted);line-height:1.5;margin-bottom:20px;}
.confirm-btns{display:flex;gap:10px;justify-content:flex-end;}
.confirm-cancel{padding:8px 18px;border-radius:10px;background:var(--bg);border:1.5px solid var(--border);font-family:'Barlow',sans-serif;font-weight:600;font-size:.85rem;cursor:pointer;transition:background .15s;}
.confirm-cancel:hover{background:var(--border);}
.confirm-delete{padding:8px 18px;border-radius:10px;background:#E53935;border:none;color:#fff;font-family:'Barlow',sans-serif;font-weight:600;font-size:.85rem;cursor:pointer;transition:background .15s;}
.confirm-delete:hover{background:#c62828;}

/* INPUT AREA */
.chat-input-area{background:#fff;border-top:1.5px solid var(--border);flex-shrink:0;}

/* Media preview bar */
.media-preview-bar{display:none;padding:10px 16px 0;gap:8px;flex-wrap:wrap;}
.media-preview-bar.has-files{display:flex;}
.mp-thumb{position:relative;width:64px;height:64px;border-radius:10px;overflow:hidden;flex-shrink:0;background:#eee;}
.mp-thumb img,.mp-thumb video{width:100%;height:100%;object-fit:cover;}
.mp-remove{position:absolute;top:2px;right:2px;width:18px;height:18px;border-radius:50%;background:rgba(0,0,0,.6);color:#fff;border:none;cursor:pointer;font-size:10px;display:flex;align-items:center;justify-content:center;line-height:1;}

/* Emoji picker */
.emoji-picker-wrap{position:relative;}
.emoji-panel{position:absolute;bottom:calc(100% + 8px);left:0;background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:12px;width:300px;box-shadow:0 8px 32px rgba(0,0,0,.15);display:none;z-index:50;}
.emoji-panel.open{display:block;}
.emoji-tabs{display:flex;gap:4px;margin-bottom:8px;overflow-x:auto;}
.emoji-tab{background:none;border:none;font-size:1.1rem;padding:4px 8px;border-radius:8px;cursor:pointer;flex-shrink:0;}
.emoji-tab.active{background:var(--orange-light);}
.emoji-grid{display:grid;grid-template-columns:repeat(8,1fr);gap:2px;max-height:180px;overflow-y:auto;}
.emoji-grid::-webkit-scrollbar{width:3px;}
.emoji-grid::-webkit-scrollbar-thumb{background:var(--border);}
.emoji-btn{font-size:1.25rem;background:none;border:none;cursor:pointer;padding:4px;border-radius:6px;line-height:1;text-align:center;}
.emoji-btn:hover{background:var(--bg);}

.chat-input-row{display:flex;align-items:center;gap:8px;padding:10px 16px 12px;}
.icon-btn{width:36px;height:36px;border-radius:50%;background:none;border:1.5px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s,border-color .15s;color:var(--muted);font-size:1.1rem;}
.icon-btn:hover{background:var(--orange-light);border-color:var(--orange);color:var(--orange);}
.chat-input-row textarea{flex:1;border:1.5px solid var(--border);border-radius:22px;padding:9px 16px;font-family:'Barlow',sans-serif;font-size:.88rem;outline:none;resize:none;max-height:100px;background:var(--bg);transition:border-color .2s;line-height:1.4;}
.chat-input-row textarea:focus{border-color:var(--orange);}
.send-btn{width:40px;height:40px;border-radius:50%;background:var(--orange);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s,transform .1s;}
.send-btn:hover{background:#d96a10;}
.send-btn:active{transform:scale(.93);}
.send-btn svg{width:17px;height:17px;fill:#fff;}
.send-btn:disabled{background:#ccc;cursor:default;}

/* Upload progress */
.upload-progress{display:none;align-items:center;gap:8px;padding:6px 16px;font-size:.78rem;color:var(--muted);}
.upload-progress.show{display:flex;}
.progress-bar-wrap{flex:1;height:4px;background:var(--border);border-radius:4px;overflow:hidden;}
.progress-bar{height:100%;background:var(--orange);border-radius:4px;width:0%;transition:width .2s;}

.skeleton{background:linear-gradient(90deg,#ece8e4 25%,#f5f2ef 50%,#ece8e4 75%);background-size:200%;animation:shimmer 1.4s infinite;border-radius:8px;}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(70px);background:#1A1A1A;color:#fff;border-radius:30px;padding:9px 22px;font-size:.85rem;font-weight:600;z-index:9999;transition:transform .3s;pointer-events:none;}
.toast.show{transform:translateX(-50%) translateY(0);}
</style>
</head>
<body>

<nav>
  <button class="home-btn" onclick="location.href='/Tourna/NewsFeed/newsfeed.php'">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
    HOME
  </button>
  <span class="nav-title">Messages</span>
</nav>

<div class="chat-layout">
  <div class="sidebar">
    <div class="sidebar-head">
      <h2>Chats</h2>
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search friends…" oninput="filterFriends(this.value)"/>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      </div>
    </div>
    <div class="friends-list" id="friendsList">
      <?php for($i=0;$i<4;$i++): ?>
      <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border)">
        <div class="skeleton" style="width:42px;height:42px;border-radius:50%;flex-shrink:0"></div>
        <div style="flex:1;display:flex;flex-direction:column;gap:6px">
          <div class="skeleton" style="height:11px;width:70%"></div>
          <div class="skeleton" style="height:9px;width:50%"></div>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <div class="chat-area">
    <div class="no-chat" id="noChat">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <p>Select a friend to start chatting</p>
    </div>

    <div id="activeChat" style="display:none;flex-direction:column;height:100%;overflow:hidden;">
      <div class="chat-head">
        <div class="ch-av" id="chAv"></div>
        <div>
          <div class="ch-name" id="chName">—</div>
          <div class="ch-status">Online</div>
        </div>
      </div>

      <div class="messages-wrap" id="messagesWrap"></div>

      <div class="chat-input-area">
        <!-- Upload progress -->
        <div class="upload-progress" id="uploadProgress">
          <span>Uploading…</span>
          <div class="progress-bar-wrap"><div class="progress-bar" id="progressBar"></div></div>
        </div>

        <!-- Media previews -->
        <div class="media-preview-bar" id="mediaPreviewBar"></div>

        <!-- Input row -->
        <div class="chat-input-row">
          <!-- Emoji -->
          <div class="emoji-picker-wrap">
            <button class="icon-btn" id="emojiBtn" title="Emoji">😊</button>
            <div class="emoji-panel" id="emojiPanel">
              <div class="emoji-tabs" id="emojiTabs"></div>
              <div class="emoji-grid" id="emojiGrid"></div>
            </div>
          </div>

          <!-- Media upload -->
          <button class="icon-btn" id="mediaBtn" title="Send photo/video">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </button>
          <input type="file" id="mediaInput" accept="image/*,video/*" multiple hidden/>

          <textarea id="msgInput" placeholder="Type a message…" rows="1"
            onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
          <button class="send-btn" id="sendBtn" onclick="sendMessage()">
            <svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Media lightbox -->
<div class="media-lb" id="mediaLb" onclick="closeLb()">
  <button class="media-lb-close" onclick="closeLb()">✕</button>
  <div id="mediaLbContent"></div>
</div>

<!-- Unsend confirm dialog -->
<div class="confirm-overlay" id="confirmOverlay">
  <div class="confirm-box">
    <h3>Unsend Message?</h3>
    <p>This message will be removed for everyone in the chat. This cannot be undone.</p>
    <div class="confirm-btns">
      <button class="confirm-cancel" onclick="closeConfirm()">Cancel</button>
      <button class="confirm-delete" id="confirmDeleteBtn">Unsend</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API        = '../api/chat_api.php';
const UPLOAD_API = '../api/chat_upload.php';
const MY_ID      = <?= (int)$current_user_id ?>;
const MY_INIT    = <?= json_encode($myInitials) ?>;
const MY_PIC     = <?= json_encode($myPic) ?>;
const OPEN_WITH  = <?= $openWith ?>;

let activeFriend  = null;
let lastMsgId     = 0;
let pollTimer     = null;
let allFriends    = [];
let pendingFiles  = [];
let pendingUnsendId = null;

// ── EMOJI DATA ──
const EMOJI_CATS = [
  { label:'😊', name:'Smileys', emojis:['😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🥸','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕'] },
  { label:'👋', name:'Gestures', emojis:['👋','🤚','🖐️','✋','🖖','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👍','👎','✊','👊','🤛','🤜','👏','🙌','👐','🤲','🤝','🙏','💪','🦾','🦿','🦵','🦶','👂','🦻','👃','🫀','🫁','🧠','🦷','🦴','👀','👁️','👅','👄'] },
  { label:'❤️', name:'Hearts', emojis:['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','💟','☮️','✝️','☪️','🕉️','✡️','🔯','🕎','☯️','🛐','⛎','♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓','🆔','⚛️'] },
  { label:'🎉', name:'Activities', emojis:['🎉','🎊','🎈','🎀','🎁','🎗️','🎟️','🎫','🏆','🥇','🥈','🥉','🏅','🎖️','🏵️','🎪','🤹','🎭','🎨','🎬','🎤','🎧','🎼','🎵','🎶','🎷','🎸','🎹','🎺','🎻','🥁','🪘','🎮','🕹️','🎲','🎯','🎳','🎰','🧩','🪀','🪁','⚽','🏀','🏈','⚾','🥎','🎾','🏐','🏉','🎱','🏓','🏸'] },
  { label:'🌟', name:'Symbols', emojis:['⭐','🌟','✨','💫','⚡','🔥','💥','❄️','🌊','🌈','☀️','🌙','⛅','🌤️','🌦️','⛈️','🌧️','❓','❗','💯','🔴','🟠','🟡','🟢','🔵','🟣','⚫','⚪','🟤','✅','❌','⛔','🚫','💢','♻️','✔️','💤','🔔','🔕','🎵','🎶','💬','💭','🗯️','💠','🔷','🔹','🔶','🔸'] },
];

// ── FRIENDS ──
async function loadFriends() {
  const res = await callApi('?action=friends');
  allFriends = res.data || [];
  renderFriends(allFriends);
  if (OPEN_WITH) {
    const f = allFriends.find(f => f.id == OPEN_WITH);
    if (f) openChat(f);
  }
}

function renderFriends(list) {
  const el = document.getElementById('friendsList');
  if (!list.length) {
    el.innerHTML = `<div class="empty-friends"><p>No friends yet.<br>Add friends to start chatting!</p></div>`;
    return;
  }
  el.innerHTML = list.map(f => {
    const avHtml   = f.profile_pic ? `<img src="${esc(f.profile_pic)}" onerror="this.style.display='none'">` : esc(f.initials);
    const isActive = activeFriend && activeFriend.id == f.id;
    const lastMsg  = f.last_message ? truncate(f.last_message, 30) : 'Say hello!';
    const unread   = f.unread > 0 ? `<div class="fi-unread">${f.unread>9?'9+':f.unread}</div>` : '';
    const dataU    = encodeURIComponent(JSON.stringify(f));
    return `<div class="friend-item ${isActive?'active':''}" onclick="openChat(JSON.parse(decodeURIComponent(this.dataset.u)))" data-u="${dataU}" data-id="${f.id}">
      <div class="fi-av">${avHtml}</div>
      <div class="fi-info">
        <div class="fi-name">${esc(f.username)}</div>
        <div class="fi-last">${esc(lastMsg)}</div>
      </div>${unread}
    </div>`;
  }).join('');
}

function filterFriends(q) {
  renderFriends(allFriends.filter(f => f.username.toLowerCase().includes(q.toLowerCase())));
}

// ── OPEN CHAT ──
async function openChat(friend) {
  activeFriend = friend;
  document.querySelectorAll('.friend-item').forEach(el => el.classList.toggle('active', el.dataset.id == friend.id));
  document.getElementById('noChat').style.display = 'none';
  document.getElementById('activeChat').style.display = 'flex';

  const avEl = document.getElementById('chAv');
  avEl.innerHTML = friend.profile_pic
    ? `<img src="${esc(friend.profile_pic)}" onerror="this.style.display='none'">` : esc(friend.initials||friend.username.substring(0,2).toUpperCase());
  document.getElementById('chName').textContent = friend.username;

  document.getElementById('messagesWrap').innerHTML = '<div style="text-align:center;padding:20px;color:var(--muted);font-size:.85rem">Loading…</div>';
  lastMsgId = 0;

  const res  = await callApi(`?action=messages&with=${friend.id}`);
  const msgs = res.data || [];
  renderMessages(msgs);
  if (msgs.length) lastMsgId = Math.max(...msgs.map(m=>+m.id));
  scrollToBottom();

  allFriends = allFriends.map(f => f.id == friend.id ? {...f, unread:0} : f);
  renderFriends(allFriends);
  clearInterval(pollTimer);
  pollTimer = setInterval(pollMessages, 2000);
  document.getElementById('msgInput').focus();
}

// ── MESSAGE RENDERING ──
function msgHTML(m) {
  const mine    = +m.sender_id === MY_ID;
  const d       = new Date(m.created_at);
  const timeStr = d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
  const avSrc   = mine ? MY_PIC : (activeFriend.profile_pic || null);
  const avInit  = mine ? MY_INIT : esc((activeFriend.initials||activeFriend.username.substring(0,2)).toUpperCase());
  const avHtml  = avSrc ? `<img src="${esc(avSrc)}" onerror="this.style.display='none'">` : avInit;

  const isUnsent = m.is_deleted == 1 || m.unsent == 1;
  const isEdited = m.is_edited == 1 || m.edited == 1;

  let content = '';
  if (isUnsent) {
    content = `<div class="msg-bubble">Message unsent</div>`;
  } else if (m.media_url) {
    if (m.media_type === 'video') {
      content = `<div class="msg-media" onclick="openLb('video','${esc(m.media_url)}')">
        <video src="${esc(m.media_url)}" muted playsinline preload="metadata"></video>
      </div>`;
    } else {
      content = `<div class="msg-media" onclick="openLb('image','${esc(m.media_url)}')">
        <img src="${esc(m.media_url)}" alt="image" loading="lazy">
      </div>`;
    }
    if (m.body) content += `<div class="msg-bubble">${escHtml(m.body)}</div>`;
  } else {
    content = `<div class="msg-bubble">${escHtml(m.body)}</div>`;
  }

  const editedTag = isEdited && !isUnsent ? `<span class="msg-edited-tag">edited</span>` : '';
  const timeHtml  = `<div class="msg-time">${timeStr}${editedTag}</div>`;

  // Action buttons — only show for own non-unsent messages
  let actionBtns = '';
  if (mine && !isUnsent) {
    const canEdit = !m.media_url; // only text messages can be edited
    actionBtns = `<div class="msg-actions">
      ${canEdit ? `<button class="msg-action-btn edit-btn" title="Edit" onclick="startEdit(${m.id}, this)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      </button>` : ''}
      <button class="msg-action-btn unsend-btn" title="Unsend" onclick="confirmUnsend(${m.id})">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
      </button>
    </div>`;
  }

  return `<div class="msg-row ${mine?'mine':'theirs'} ${isUnsent?'msg-unsent':''}" data-msg-id="${m.id}">
    <div class="msg-av">${avHtml}</div>
    <div class="msg-col">${content}${timeHtml}</div>
    ${actionBtns}
  </div>`;
}

function renderMessages(msgs) {
  const wrap = document.getElementById('messagesWrap');
  if (!msgs.length) {
    wrap.innerHTML = '<div style="text-align:center;padding:40px 20px;color:var(--muted);font-size:.88rem">No messages yet. Say hello! 👋</div>';
    return;
  }
  let html = '', lastDate = '';
  msgs.forEach(m => {
    const d    = new Date(m.created_at);
    const date = d.toLocaleDateString('en-US', {weekday:'long', month:'short', day:'numeric'});
    if (date !== lastDate) { html += `<div class="date-divider">${date}</div>`; lastDate = date; }
    html += msgHTML(m);
  });
  wrap.innerHTML = html;
}

async function pollMessages() {
  if (!activeFriend) return;
  const res  = await callApi(`?action=poll&with=${activeFriend.id}&after=${lastMsgId}`);
  const msgs = res.data || [];
  if (!msgs.length) return;
  const wrap     = document.getElementById('messagesWrap');
  const atBottom = wrap.scrollHeight - wrap.scrollTop - wrap.clientHeight < 80;
  if (wrap.querySelector('[style]')) wrap.innerHTML = '';
  msgs.forEach(m => { lastMsgId = Math.max(lastMsgId, +m.id); wrap.insertAdjacentHTML('beforeend', msgHTML(m)); });
  if (atBottom) scrollToBottom();
  loadFriends();
}

// ── SEND TEXT ──
async function sendMessage() {
  if (!activeFriend) return;
  if (pendingFiles.length) { await sendMediaFiles(); return; }
  const inp  = document.getElementById('msgInput');
  const body = inp.value.trim();
  if (!body) return;
  inp.value = ''; autoResize(inp);
  document.getElementById('sendBtn').disabled = true;
  const res = await callApi('', {action:'send', to: activeFriend.id, body});
  document.getElementById('sendBtn').disabled = false;
  if (res.success) {
    lastMsgId = Math.max(lastMsgId, +res.data.id);
    const wrap = document.getElementById('messagesWrap');
    if (wrap.querySelector('[style]')) wrap.innerHTML = '';
    wrap.insertAdjacentHTML('beforeend', msgHTML(res.data));
    scrollToBottom(); loadFriends();
  } else toast('Failed to send');
}

// ── SEND MEDIA ──
async function sendMediaFiles() {
  if (!activeFriend || !pendingFiles.length) return;
  const body = document.getElementById('msgInput').value.trim();
  document.getElementById('msgInput').value = '';
  autoResize(document.getElementById('msgInput'));

  const progress = document.getElementById('uploadProgress');
  const bar      = document.getElementById('progressBar');
  progress.classList.add('show');
  document.getElementById('sendBtn').disabled = true;

  for (let i = 0; i < pendingFiles.length; i++) {
    const file = pendingFiles[i];
    bar.style.width = Math.round(((i) / pendingFiles.length) * 100) + '%';
    const fd = new FormData();
    fd.append('media', file);
    fd.append('to', activeFriend.id);
    if (i === 0 && body) fd.append('body', body);
    try {
      const r    = await fetch(UPLOAD_API, {method:'POST', body:fd});
      const data = await r.json();
      if (data.success) {
        lastMsgId = Math.max(lastMsgId, +data.data.id);
        const wrap = document.getElementById('messagesWrap');
        if (wrap.querySelector('[style]')) wrap.innerHTML = '';
        wrap.insertAdjacentHTML('beforeend', msgHTML(data.data));
        scrollToBottom();
      }
    } catch(e) { toast('Upload failed'); }
  }

  bar.style.width = '100%';
  setTimeout(() => { progress.classList.remove('show'); bar.style.width = '0%'; }, 400);
  pendingFiles = [];
  renderMediaPreviews();
  document.getElementById('sendBtn').disabled = false;
  loadFriends();
}

// ── UNSEND ──
function confirmUnsend(msgId) {
  pendingUnsendId = msgId;
  document.getElementById('confirmOverlay').classList.add('open');
  document.getElementById('confirmDeleteBtn').onclick = () => doUnsend(msgId);
}

function closeConfirm() {
  document.getElementById('confirmOverlay').classList.remove('open');
  pendingUnsendId = null;
}

async function doUnsend(msgId) {
  closeConfirm();
  const res = await callApi('', {action: 'unsend', message_id: msgId});
  if (res.success) {
    // Update the message row in-place
    const row = document.querySelector(`.msg-row[data-msg-id="${msgId}"]`);
    if (row) {
      row.classList.add('msg-unsent');
      const col = row.querySelector('.msg-col');
      if (col) {
        col.querySelector('.msg-bubble') && (col.querySelector('.msg-bubble').textContent = 'Message unsent');
        col.querySelector('.msg-media') && col.querySelector('.msg-media').remove();
      }
      row.querySelector('.msg-actions')?.remove();
    }
    loadFriends();
    toast('Message unsent');
  } else {
    toast(res.message || 'Could not unsend message');
  }
}

// ── EDIT ──
function startEdit(msgId, btnEl) {
  // Cancel any existing edit
  document.querySelectorAll('.msg-edit-wrap').forEach(el => cancelEditNode(el));

  const row = document.querySelector(`.msg-row[data-msg-id="${msgId}"]`);
  if (!row) return;
  const col    = row.querySelector('.msg-col');
  const bubble = col.querySelector('.msg-bubble');
  if (!bubble) return;

  const originalText = bubble.textContent;

  // Replace bubble with inline editor
  bubble.style.display = 'none';
  const editWrap = document.createElement('div');
  editWrap.className = 'msg-edit-wrap';
  editWrap.dataset.originalText = originalText;
  editWrap.dataset.msgId = msgId;
  editWrap.innerHTML = `
    <textarea class="msg-edit-input" rows="1">${esc(originalText)}</textarea>
    <button class="msg-edit-cancel" title="Cancel" onclick="cancelEdit(${msgId})">✕</button>
    <button class="msg-edit-save" title="Save" onclick="saveEdit(${msgId})">
      <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
    </button>`;
  col.insertBefore(editWrap, bubble);

  const textarea = editWrap.querySelector('.msg-edit-input');
  autoResize(textarea);
  textarea.addEventListener('input', () => autoResize(textarea));
  textarea.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); saveEdit(msgId); }
    if (e.key === 'Escape') cancelEdit(msgId);
  });
  textarea.focus();
  textarea.setSelectionRange(textarea.value.length, textarea.value.length);
}

function cancelEditNode(editWrap) {
  const msgId = editWrap.dataset.msgId;
  const row   = document.querySelector(`.msg-row[data-msg-id="${msgId}"]`);
  if (!row) return;
  const bubble = row.querySelector('.msg-bubble');
  if (bubble) bubble.style.display = '';
  editWrap.remove();
}

function cancelEdit(msgId) {
  const editWrap = document.querySelector(`.msg-edit-wrap[data-msg-id="${msgId}"]`);
  if (editWrap) cancelEditNode(editWrap);
}

async function saveEdit(msgId) {
  const editWrap = document.querySelector(`.msg-edit-wrap[data-msg-id="${msgId}"]`);
  if (!editWrap) return;
  const newBody = editWrap.querySelector('.msg-edit-input').value.trim();
  if (!newBody) { toast('Message cannot be empty'); return; }

  const res = await callApi('', {action: 'edit', message_id: msgId, body: newBody});
  if (res.success) {
    const row    = document.querySelector(`.msg-row[data-msg-id="${msgId}"]`);
    const bubble = row?.querySelector('.msg-bubble');
    if (bubble) {
      bubble.innerHTML = escHtml(newBody);
      bubble.style.display = '';
    }
    // Add/update edited tag in time row
    const timeEl = row?.querySelector('.msg-time');
    if (timeEl && !timeEl.querySelector('.msg-edited-tag')) {
      timeEl.insertAdjacentHTML('beforeend', `<span class="msg-edited-tag">edited</span>`);
    }
    editWrap.remove();
    toast('Message updated');
  } else {
    toast(res.message || 'Could not edit message');
  }
}

// ── MEDIA PICKER ──
document.getElementById('mediaBtn').addEventListener('click', () => {
  document.getElementById('mediaInput').click();
});
document.getElementById('mediaInput').addEventListener('change', function() {
  Array.from(this.files).forEach(f => pendingFiles.push(f));
  this.value = '';
  renderMediaPreviews();
});
function renderMediaPreviews() {
  const bar = document.getElementById('mediaPreviewBar');
  if (!pendingFiles.length) { bar.classList.remove('has-files'); bar.innerHTML = ''; return; }
  bar.classList.add('has-files');
  bar.innerHTML = pendingFiles.map((f, i) => {
    const isVideo = f.type.startsWith('video/');
    const url = URL.createObjectURL(f);
    const media = isVideo ? `<video src="${url}" muted playsinline></video>` : `<img src="${url}" alt="">`;
    return `<div class="mp-thumb">${media}<button class="mp-remove" onclick="removePending(${i})">✕</button></div>`;
  }).join('');
}
function removePending(idx) { pendingFiles.splice(idx, 1); renderMediaPreviews(); }

// ── MEDIA LIGHTBOX ──
function openLb(type, src) {
  const lb = document.getElementById('mediaLb');
  document.getElementById('mediaLbContent').innerHTML = type === 'video'
    ? `<video src="${src}" controls autoplay style="max-width:92vw;max-height:92vh;border-radius:10px;"></video>`
    : `<img src="${src}" style="max-width:92vw;max-height:92vh;border-radius:10px;object-fit:contain;">`;
  lb.classList.add('open');
}
function closeLb() {
  const lb = document.getElementById('mediaLb');
  lb.querySelector('video')?.pause();
  lb.classList.remove('open');
  document.getElementById('mediaLbContent').innerHTML = '';
}

// ── CONFIRM OVERLAY CLOSE ON BG CLICK ──
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeConfirm();
});

// ── EMOJI PICKER ──
(function initEmoji() {
  const tabs = document.getElementById('emojiTabs');
  const grid = document.getElementById('emojiGrid');
  function renderTab(idx) {
    grid.innerHTML = EMOJI_CATS[idx].emojis.map(e =>
      `<button class="emoji-btn" onclick="insertEmoji('${e}')">${e}</button>`).join('');
    document.querySelectorAll('.emoji-tab').forEach((t,i) => t.classList.toggle('active', i===idx));
  }
  tabs.innerHTML = EMOJI_CATS.map((c,i) =>
    `<button class="emoji-tab ${i===0?'active':''}" onclick="emojiTabClick(${i})">${c.label}</button>`).join('');
  renderTab(0);
  window.emojiTabClick = function(idx) { renderTab(idx); };
})();

document.getElementById('emojiBtn').addEventListener('click', e => {
  e.stopPropagation();
  document.getElementById('emojiPanel').classList.toggle('open');
});
document.addEventListener('click', e => {
  if (!e.target.closest('.emoji-picker-wrap')) document.getElementById('emojiPanel').classList.remove('open');
});
function insertEmoji(emoji) {
  const inp = document.getElementById('msgInput');
  const pos = inp.selectionStart;
  inp.value = inp.value.slice(0, pos) + emoji + inp.value.slice(pos);
  inp.selectionStart = inp.selectionEnd = pos + emoji.length;
  inp.focus(); autoResize(inp);
}

// ── KEY HANDLER ──
function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    if (pendingFiles.length) sendMediaFiles(); else sendMessage();
  }
}

// ── HELPERS ──
function autoResize(el) { el.style.height='auto'; el.style.height=Math.min(el.scrollHeight,100)+'px'; }
function scrollToBottom() { const w=document.getElementById('messagesWrap'); w.scrollTop=w.scrollHeight; }
async function callApi(query, bodyData) {
  try {
    const opts = bodyData
      ? {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(bodyData)}
      : {method:'GET'};
    return await (await fetch(API + query, opts)).json();
  } catch(e) { return {success:false,data:[]}; }
}
function esc(s)     { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>'); }
function truncate(s,n) { return s.length>n?s.slice(0,n)+'…':s; }
function toast(msg) { const el=document.getElementById('toast'); el.textContent=msg; el.classList.add('show'); setTimeout(()=>el.classList.remove('show'),2500); }

loadFriends();
</script>
</body>
</html>