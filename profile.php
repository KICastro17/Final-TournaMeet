<?php
session_start();
include "config.php";

/* ================= AUTH GUARD ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= FETCH USER ================= */
$stmt = $conn->prepare("SELECT id, username, email, role, status, bio, profile_pic, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) { session_destroy(); header("Location: login.php"); exit(); }

/* ================= MY REGISTRATIONS ================= */
$stmt = $conn->prepare("
    SELECT r.id, r.name, r.registered_at,
           t.name AS tournament_name, t.sport, t.date, t.location, t.prize
    FROM registrations r
    JOIN tournaments t ON t.id = r.tournament_id
    WHERE r.email = ?
    ORDER BY r.registered_at DESC LIMIT 10
");
$stmt->bind_param("s", $user['email']);
$stmt->execute();
$registrations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ================= UPCOMING TOURNAMENTS ================= */
$stmt = $conn->prepare("
    SELECT t.name, t.sport, t.date, t.time, t.location, t.prize, t.entry_fee
    FROM registrations r
    JOIN tournaments t ON t.id = r.tournament_id
    WHERE r.email = ? AND t.date >= CURDATE()
    ORDER BY t.date ASC LIMIT 5
");
$stmt->bind_param("s", $user['email']);
$stmt->execute();
$upcoming = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ================= STATS ================= */
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM registrations WHERE email = ?");
$stmt->bind_param("s", $user['email']);
$stmt->execute();
$total_joined = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS past FROM registrations r JOIN tournaments t ON t.id = r.tournament_id WHERE r.email = ? AND t.date < CURDATE()");
$stmt->bind_param("s", $user['email']);
$stmt->execute();
$total_past = $stmt->get_result()->fetch_assoc()['past'];
$stmt->close();

$total_upcoming = count($upcoming);


/* ================= POSTS (with multi-media) ================= */
$stmt = $conn->prepare("SELECT id, caption, location, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 12");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$posts_raw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check if post_media table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'post_media'");
$postMediaExists = $tableCheck && $tableCheck->num_rows > 0;

// Attach media for each post
$posts = [];
foreach ($posts_raw as $p) {
    $pid  = $p['id'];
    $p['media_files'] = [];

    if ($postMediaExists) {
        $mres = $conn->query("SELECT id, filename, media_type FROM post_media WHERE post_id = $pid ORDER BY sort_order");
        if ($mres) $p['media_files'] = $mres->fetch_all(MYSQLI_ASSOC);
    }

    // Always fallback to old columns if no post_media rows found
    if (empty($p['media_files'])) {
        $fres = $conn->query("SELECT media, image, media_type FROM posts WHERE id = $pid");
        if ($fres) {
            $fold  = $fres->fetch_assoc();
            $fname = !empty($fold['media']) ? $fold['media'] : (!empty($fold['image']) ? $fold['image'] : null);
            if ($fname) {
                $p['media_files'] = [[
                    'id'         => 0,
                    'filename'   => $fname,
                    'media_type' => $fold['media_type'] ?? 'image'
                ]];
            }
        }
    }
    $posts[] = $p;
}

$conn->close();

/* ================= HELPERS ================= */
$initials     = substr(strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', trim($user['username']))))), 0, 2);
$pic          = (!empty($user['profile_pic']) && file_exists('uploads' . $user['profile_pic'])) ? 'uploads' . $user['profile_pic'] : null;
$member_since = date('Y', strtotime($user['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="favicon.png">
  <title>Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="profile-tab.css">
</head>
<body>

<!-- ═══════════════ PAGE ENTRANCE TRANSITION ═══════════════
     The orange ripple that expanded FROM the profile button
     in index.html now COLLAPSES back to the same spot here,
     revealing the profile page underneath.
════════════════════════════════════════════════════════════ -->
<div id="page-enter-ripple"></div>

<style>
  #page-enter-ripple {
    position: fixed;
    top:   32px;
    right: 32px;
    width:  40px;
    height: 40px;
    border-radius: 50%;
    background: #F47B20;
    transform: scale(80);
    opacity: 1;
    pointer-events: none;
    z-index: 9999;
    transition: transform 0.55s cubic-bezier(0.4, 0, 0.2, 1),
                opacity   0.55s cubic-bezier(0.4, 0, 0.2, 1);
  }
  #page-enter-ripple.collapsed {
    transform: scale(0);
    opacity: 0;
  }
</style>

<script>
  (function () {
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        var el = document.getElementById('page-enter-ripple');
        if (el) {
          el.classList.add('collapsed');
          setTimeout(function () { if (el) el.remove(); }, 620);
        }
      });
    });
  })();
</script>
<!-- ══════════════════════════════════════════════════════ -->

<?php if (isset($_GET['updated'])): ?>
<div id="toast">✔ Profile updated successfully!</div>
<script>setTimeout(() => { const t = document.getElementById('toast'); t.style.opacity = 0; }, 3000);</script>
<?php endif; ?>

<!-- NAV -->
<nav>
  <a href="/Tourna/NewsFeed/newsfeed.php" class="nav-home">
  <svg width="14" height="14" fill="white" viewBox="0 0 20 20">
    <path d="M10 2L2 9h2v9h5v-5h2v5h5V9h2L10 2z"/>
  </svg>
  BACK
</a>
  <div class="search-bar">
    <input type="text" placeholder="Search tournaments...">
    <button><svg width="13" height="13" fill="white" viewBox="0 0 20 20"><path d="M12.9 14.32a8 8 0 111.42-1.42l4.38 4.39-1.42 1.41-4.38-4.38zM8 14A6 6 0 108 2a6 6 0 000 12z"/></svg></button>
  </div>
  <div class="nav-right">
    <span class="nav-user">👤 <?= htmlspecialchars($user['username']) ?></span>
    <a href="logout.php" class="btn-logout" onclick="return confirmLogout(event)">Logout</a>
  </div>
</nav>

<div class="page">

  <!-- PROFILE HEADER -->
  <div class="profile-header">
    <div class="profile-banner">
      <span class="profile-banner-badge">
        <?php if ($user['role'] === 'admin'): ?>🛡 Admin
        <?php elseif ($user['role'] === 'organizer'): ?>🎯 Organizer
        <?php else: ?>🏅 Athlete<?php endif; ?>
      </span>
    </div>
    <div class="profile-body">
      <div class="avatar-wrap">
        <?php if ($pic): ?>
          <img src="<?= htmlspecialchars($pic) ?>" alt="Profile Picture" class="avatar avatar-img">
        <?php else: ?>
          <div class="avatar"><?= htmlspecialchars($initials) ?></div>
        <?php endif; ?>
        <?php if ($user['status'] === 'approved'): ?><div class="avatar-online"></div><?php endif; ?>
      </div>
      <div class="profile-info">
        <div class="profile-name"><?= htmlspecialchars($user['username']) ?></div>
        <div class="profile-role"><?= ucfirst($user['role']) ?> · <?= htmlspecialchars($user['email']) ?></div>
        <div class="profile-meta">
          <span class="meta-item">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            Member since <?= $member_since ?>
          </span>
          <span class="meta-item">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
            Status: <?= ucfirst($user['status']) ?>
          </span>
        </div>
      </div>
      <div class="profile-actions">
        <a href="edit_profile.php" class="btn-primary">✏ Edit Profile</a>
      </div>
    </div>
  </div>

  <!-- STATS ROW -->
  <div class="stats-row" style="grid-template-columns: repeat(3,1fr);">
    <div class="stat-card">
      <div class="stat-num"><?= $total_joined ?></div>
      <div class="stat-label">Tournaments Joined</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $total_upcoming ?></div>
      <div class="stat-label">Upcoming</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $total_past ?></div>
      <div class="stat-label">Completed</div>
    </div>
  </div>

  <!-- BIO -->
  <?php if (!empty($user['bio'])): ?>
  <div class="card" style="margin-bottom:22px;">
    <div class="card-header"><div class="card-title">Bio</div></div>
    <div class="card-body">
      <p style="font-size:14px; color:var(--text-dark); line-height:1.7;"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
    </div>
  </div>
  <?php endif; ?>

  <div class="two-col">
    <div>

      <!-- MY REGISTRATIONS -->
      <div class="card">
        <div class="card-header"><div class="card-title">My Registrations</div></div>
        <div class="card-body" style="padding-top:10px;">
          <?php if (empty($registrations)): ?>
            <p class="empty-msg">You haven't registered for any tournaments yet.</p>
          <?php else: foreach ($registrations as $r):
            $dateStr = $r['date'] ? date('M j, Y', strtotime($r['date'])) : '—';
            $isPast  = $r['date'] && strtotime($r['date']) < time();
          ?>
          <div class="tournament-item">
            <div class="t-icon">
              <svg width="18" height="18" fill="var(--orange)" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <div class="t-info">
              <div class="t-name"><?= htmlspecialchars($r['tournament_name']) ?></div>
              <div class="t-date"><?= htmlspecialchars($r['sport']) ?> · <?= $dateStr ?> · <?= htmlspecialchars($r['location']) ?></div>
            </div>
            <span class="t-badge <?= $isPast ? 'badge-active' : 'badge-upcoming' ?>">
              <?= $isPast ? 'Completed' : 'Upcoming' ?>
            </span>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- UPCOMING TOURNAMENTS -->
      <div class="card">
        <div class="card-header"><div class="card-title">Upcoming Tournaments</div></div>
        <div class="card-body" style="padding-top:10px;">
          <?php if (empty($upcoming)): ?>
            <p class="empty-msg">No upcoming tournaments registered.</p>
          <?php else: foreach ($upcoming as $u): ?>
          <div class="tournament-item">
            <div class="t-icon">
              <svg width="18" height="18" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="t-info">
              <div class="t-name"><?= htmlspecialchars($u['name']) ?></div>
              <div class="t-date">
                <?= htmlspecialchars($u['sport']) ?> ·
                <?= date('M j, Y', strtotime($u['date'])) ?>
                <?= $u['time'] ? ' · ' . htmlspecialchars($u['time']) : '' ?> ·
                <?= htmlspecialchars($u['location']) ?>
              </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
              <div style="font-family:'Barlow Condensed',sans-serif; font-size:13px; font-weight:700; color:var(--orange);"><?= htmlspecialchars($u['prize']) ?></div>
              <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Entry: <?= htmlspecialchars($u['entry_fee']) ?></div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- POSTS -->
      <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
          <div class="card-title">My Posts</div>
          <button class="btn-primary btn-sm" id="openPostModal" style="font-size:12px;padding:6px 14px;">+ New Post</button>
        </div>
        <div class="card-body" id="postsCardBody">
          <?php if (empty($posts)): ?>
            <p class="empty-msg">No posts yet.</p>
          <?php else: ?>
          <div class="posts-grid">
            <?php foreach ($posts as $p):
              $firstMedia = $p['media_files'][0] ?? null;
              $mediaType  = $firstMedia['media_type'] ?? 'image';
              $mediaSrc = null;
              if ($firstMedia) {
                  if (file_exists('uploads/posts/' . $firstMedia['filename'])) {
                      $mediaSrc = 'uploads/posts/' . $firstMedia['filename'];
                  } elseif (file_exists('uploads/' . $firstMedia['filename'])) {
                      $mediaSrc = 'uploads/' . $firstMedia['filename'];
                  } else {
                      $mediaSrc = 'uploads/posts/' . $firstMedia['filename'];
                  }
              }
              $mediaCount = count($p['media_files']);
              $mediaJson  = json_encode(array_map(function($m) {
                  $url = file_exists('uploads/posts/' . $m['filename'])
                      ? 'uploads/posts/' . $m['filename']
                      : (file_exists('uploads/' . $m['filename'])
                          ? 'uploads/' . $m['filename']
                          : 'uploads/posts/' . $m['filename']);
                  return ['id' => $m['id'], 'url' => $url, 'type' => $m['media_type']];
              }, $p['media_files']));
            ?>
            <div class="post-card"
              data-id="<?= $p['id'] ?>"
              data-caption="<?= htmlspecialchars($p['caption'] ?? '') ?>"
              data-date="<?= date('M j, Y', strtotime($p['created_at'])) ?>"
              data-media='<?= htmlspecialchars($mediaJson, ENT_QUOTES) ?>'
              onclick="openLightbox(this)">
              <?php if ($mediaSrc && $mediaType === 'video'): ?>
                <video class="post-media" muted playsinline><source src="<?= htmlspecialchars($mediaSrc) ?>"></video>
                <div class="post-video-badge">▶ Video</div>
              <?php elseif ($mediaSrc): ?>
                <img src="<?= htmlspecialchars($mediaSrc) ?>" alt="Post" class="post-media" loading="lazy">
              <?php else: ?>
                <div class="post-no-media">📝</div>
              <?php endif; ?>
              <?php if ($mediaCount > 1): ?>
                <div class="post-multi-badge">⧉ <?= $mediaCount ?></div>
              <?php endif; ?>
              <div class="post-actions" onclick="event.stopPropagation()">
                <button class="post-action-btn post-edit-btn" onclick="openEditPost(this.closest('.post-card'))" title="Edit post">✏</button>
                <button class="post-action-btn post-delete-btn" onclick="openDeletePost(this.closest('.post-card'))" title="Delete post">🗑</button>
              </div>
              <div class="post-overlay">
                <?php if (!empty($p['caption'])): ?>
                  <div class="post-overlay-caption"><?= htmlspecialchars(mb_strimwidth($p['caption'], 0, 55, '…')) ?></div>
                <?php endif; ?>
                <div class="post-overlay-date"><?= date('M j, Y', strtotime($p['created_at'])) ?></div>
              </div>
              <div class="post-reaction-bar" id="reactionBar-<?= $p['id'] ?>"></div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- SIDEBAR -->
    <div>

      <!-- ACCOUNT DETAILS -->
      <div class="card">
        <div class="card-header"><div class="card-title">Account Details</div></div>
        <div class="card-body">
          <div class="info-row"><span class="info-label">Username</span><span class="info-val"><?= htmlspecialchars($user['username']) ?></span></div>
          <div class="info-row"><span class="info-label">Email</span><span class="info-val"><?= htmlspecialchars($user['email']) ?></span></div>
          <div class="info-row"><span class="info-label">Role</span><span class="info-val"><?= ucfirst($user['role']) ?></span></div>
          <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-val">
              <?php if ($user['status'] === 'approved'): ?>
                <span style="color:#27AE72;font-weight:700;">✔ Approved</span>
              <?php else: ?>
                <span style="color:var(--orange);font-weight:700;">⏳ Pending</span>
              <?php endif; ?>
            </span>
          </div>
          <div class="info-row"><span class="info-label">Joined</span><span class="info-val"><?= date('F j, Y', strtotime($user['created_at'])) ?></span></div>
        </div>
      </div>

      <!-- PROFILE PIC -->
      <?php if ($pic): ?>
      <div class="card">
        <div class="card-header"><div class="card-title">Profile Picture</div></div>
        <div class="card-body" style="text-align:center;">
          <img src="<?= htmlspecialchars($pic) ?>" alt="Profile Picture"
               style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid var(--gray-border);">
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

</div>

<!-- ═══════════════════════════════════ POST MODAL ═══════════════════════════════════ -->
<div id="postModal" class="modal-overlay" style="display:none;">
  <div class="modal-box">

    <div class="modal-header">
      <h3 class="modal-title">📸 Create Post</h3>
      <button class="modal-close" id="closePostModal">✕</button>
    </div>

    <!-- DROP ZONE -->
    <div class="drop-zone" id="dropZone">
      <div class="drop-zone-inner" id="dropZoneInner">
        <div class="drop-icon">🖼</div>
        <div class="drop-label">Click or drag to upload</div>
        <div class="drop-hint">JPG, PNG, GIF, WEBP, MP4, WEBM · Max 50MB</div>
      </div>
      <img id="imgPreview" class="media-preview" style="display:none;" alt="Preview">
      <video id="vidPreview" class="media-preview" style="display:none;" controls muted></video>
      <button type="button" class="preview-remove" id="removeMedia" style="display:none;">✕ Remove</button>
      <input type="file" id="mediaInput" accept="image/*,video/*" multiple hidden>
    </div>

    <!-- CAPTION -->
    <div class="modal-caption-wrap">
      <textarea id="captionInput" class="modal-caption" placeholder="Write a caption..." maxlength="500" rows="3"></textarea>
      <div class="char-count"><span id="captionCount">0</span> / 500</div>
    </div>

    <!-- ERROR -->
    <div id="postError" class="alert alert-error" style="display:none;margin:0 0 12px;"></div>

    <!-- ACTIONS -->
    <div class="modal-actions">
      <button class="btn-primary" id="submitPost" style="flex:1;padding:12px;font-size:14px;">
        <span id="submitLabel">🚀 Post</span>
        <span id="submitSpinner" style="display:none;">⏳ Uploading...</span>
      </button>
      <button class="btn-secondary modal-cancel" id="cancelPost">Cancel</button>
    </div>

  </div>
</div>

<style>
/* ── MODAL ── */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(10,15,30,0.75);
  z-index: 1000;
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
  backdrop-filter: blur(4px);
  animation: fadeIn 0.2s ease;
}
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

.modal-box {
  background: white;
  border-radius: 16px;
  width: 100%; max-width: 480px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  overflow: hidden;
  animation: slideUp 0.25s ease;
}
@keyframes slideUp { from { transform:translateY(30px); opacity:0; } to { transform:translateY(0); opacity:1; } }

.modal-header {
  background: var(--navy);
  padding: 18px 22px;
  display: flex; align-items: center; justify-content: space-between;
}
.modal-title {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 18px; font-weight: 800;
  color: white; margin: 0;
}
.modal-close {
  background: rgba(255,255,255,0.1); border: none;
  color: white; width: 30px; height: 30px;
  border-radius: 50%; cursor: pointer; font-size: 13px;
  transition: background 0.2s;
}
.modal-close:hover { background: rgba(255,255,255,0.2); }

/* ── DROP ZONE ── */
.drop-zone {
  margin: 20px 22px 0;
  border: 2px dashed var(--gray-border);
  border-radius: 12px;
  min-height: 160px;
  position: relative;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  overflow: hidden;
  display: flex; align-items: center; justify-content: center;
}
.drop-zone.dragover { border-color: var(--orange); background: rgba(232,98,42,0.04); }
.drop-zone-inner { text-align: center; padding: 28px 20px; }
.drop-icon { font-size: 36px; margin-bottom: 8px; }
.drop-label { font-family: 'Barlow Condensed', sans-serif; font-size: 15px; font-weight: 700; color: var(--text-dark); }
.drop-hint  { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

.media-preview {
  width: 100%; max-height: 260px;
  object-fit: cover; display: block;
}
.preview-remove {
  position: absolute; top: 8px; right: 8px;
  background: rgba(0,0,0,0.6); color: white;
  border: none; border-radius: 20px;
  font-size: 11px; font-weight: 700;
  padding: 4px 10px; cursor: pointer;
  transition: background 0.2s;
}
.preview-remove:hover { background: rgba(229,62,62,0.9); }

/* ── CAPTION ── */
.modal-caption-wrap { padding: 14px 22px 0; }
.modal-caption {
  width: 100%; padding: 11px 14px;
  border: 1.5px solid var(--gray-border); border-radius: 8px;
  font-family: 'Barlow', sans-serif; font-size: 14px;
  color: var(--text-dark); background: var(--gray-bg);
  outline: none; resize: none;
  transition: border-color 0.2s;
  box-sizing: border-box;
}
.modal-caption:focus { border-color: var(--orange); background: white; }

/* ── MODAL ACTIONS ── */
.modal-actions {
  display: flex; gap: 10px;
  padding: 16px 22px 22px;
}
.modal-cancel {
  padding: 12px 20px;
  font-family: 'Barlow Condensed', sans-serif;
  font-weight: 700; font-size: 14px;
  text-transform: uppercase; letter-spacing: 0.6px;
  background: rgba(26,37,64,0.06);
  color: var(--text-dark);
  border: 1.5px solid var(--gray-border);
  border-radius: 8px; cursor: pointer;
  transition: background 0.2s;
}
.modal-cancel:hover { background: rgba(26,37,64,0.12); }
</style>

<script>
(function () {
  const modal        = document.getElementById('postModal');
  const dropZone     = document.getElementById('dropZone');
  const dropInner    = document.getElementById('dropZoneInner');
  const mediaInput   = document.getElementById('mediaInput');
  const imgPreview   = document.getElementById('imgPreview');
  const vidPreview   = document.getElementById('vidPreview');
  const removeBtn    = document.getElementById('removeMedia');
  const captionInput = document.getElementById('captionInput');
  const captionCount = document.getElementById('captionCount');
  const submitBtn    = document.getElementById('submitPost');
  const submitLabel  = document.getElementById('submitLabel');
  const submitSpinner= document.getElementById('submitSpinner');
  const postError    = document.getElementById('postError');
  const postsGrid    = document.querySelector('.posts-grid');

  let selectedFiles = [];

  document.getElementById('openPostModal').addEventListener('click', () => {
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  });
  function closeModal() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
    resetModal();
  }
  document.getElementById('closePostModal').addEventListener('click', closeModal);
  document.getElementById('cancelPost').addEventListener('click', closeModal);
  modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

  dropZone.addEventListener('click', () => mediaInput.click());

  dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
  dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
  dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    Array.from(e.dataTransfer.files).forEach(f => addFile(f));
    renderPreviews();
  });

  mediaInput.addEventListener('change', e => {
    Array.from(e.target.files).forEach(f => addFile(f));
    renderPreviews();
    mediaInput.value = '';
  });

  function addFile(file) {
    const allowed = ['image/jpeg','image/png','image/gif','image/webp','video/mp4','video/webm','video/ogg'];
    if (!allowed.includes(file.type)) return;
    if (file.size > 50 * 1024 * 1024) return;
    selectedFiles.push(file);
  }

  function renderPreviews() {
    if (!selectedFiles.length) {
      dropInner.style.display = 'block';
      imgPreview.style.display = 'none';
      vidPreview.style.display = 'none';
      removeBtn.style.display = 'none';
      const pg = document.getElementById('previewGrid');
      if (pg) pg.remove();
      return;
    }

    dropInner.style.display = 'none';
    imgPreview.style.display = 'none';
    vidPreview.style.display = 'none';
    removeBtn.style.display = 'none';

    let pg = document.getElementById('previewGrid');
    if (!pg) {
      pg = document.createElement('div');
      pg.id = 'previewGrid';
      pg.style.cssText = 'display:grid;grid-template-columns:repeat(3,1fr);gap:6px;padding:10px;width:100%;box-sizing:border-box;';
      dropZone.appendChild(pg);
    }
    pg.innerHTML = '';

    selectedFiles.forEach((file, idx) => {
      const wrap = document.createElement('div');
      wrap.style.cssText = 'position:relative;aspect-ratio:1;border-radius:8px;overflow:hidden;background:#111;';

      if (file.type.startsWith('video/')) {
        const vid = document.createElement('video');
        vid.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        vid.muted = true; vid.src = URL.createObjectURL(file);
        wrap.appendChild(vid);
        const badge = document.createElement('div');
        badge.style.cssText = 'position:absolute;top:4px;left:4px;background:rgba(0,0,0,0.6);color:white;font-size:10px;padding:2px 6px;border-radius:10px;';
        badge.textContent = '▶';
        wrap.appendChild(badge);
      } else {
        const img = document.createElement('img');
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        const reader = new FileReader();
        reader.onload = e => img.src = e.target.result;
        reader.readAsDataURL(file);
        wrap.appendChild(img);
      }

      const rm = document.createElement('button');
      rm.style.cssText = 'position:absolute;top:4px;right:4px;width:22px;height:22px;background:rgba(229,62,62,0.9);border:none;border-radius:50%;color:white;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;';
      rm.textContent = '✕';
      rm.onclick = (e) => { e.stopPropagation(); selectedFiles.splice(idx, 1); renderPreviews(); };
      wrap.appendChild(rm);
      pg.appendChild(wrap);
    });

    if (selectedFiles.length < 10) {
      const addMore = document.createElement('div');
      addMore.style.cssText = 'aspect-ratio:1;border-radius:8px;border:2px dashed rgba(255,255,255,0.2);display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;color:rgba(255,255,255,0.5);font-size:22px;';
      addMore.innerHTML = '<span>+</span><span style="font-size:10px;margin-top:2px;">Add more</span>';
      addMore.onclick = (e) => { e.stopPropagation(); mediaInput.click(); };
      pg.appendChild(addMore);
    }
  }

  function resetMedia() {
    selectedFiles = [];
    mediaInput.value = '';
    imgPreview.style.display = 'none';
    vidPreview.style.display = 'none';
    vidPreview.src = '';
    removeBtn.style.display = 'none';
    dropInner.style.display = 'block';
    const pg = document.getElementById('previewGrid');
    if (pg) pg.remove();
  }

  function resetModal() {
    resetMedia();
    captionInput.value = '';
    captionCount.textContent = '0';
    postError.style.display = 'none';
  }

  captionInput.addEventListener('input', () => {
    captionCount.textContent = captionInput.value.length;
  });

  submitBtn.addEventListener('click', async () => {
    postError.style.display = 'none';

    if (!selectedFiles.length) {
      showError('Please select at least one image or video.');
      return;
    }

    const formData = new FormData();
    selectedFiles.forEach(f => formData.append('media[]', f));
    formData.append('caption', captionInput.value.trim());

    submitLabel.style.display = 'none';
    submitSpinner.style.display = 'inline';
    submitBtn.disabled = true;

    try {
      const res  = await fetch('create_post.php', { method: 'POST', body: formData });
      const text = await res.text();
      let data;
      try { data = JSON.parse(text); }
      catch(e) { showError('Server error: ' + text.slice(0,100)); return; }

      if (data.success) {
        closeModal();
        prependPost(data);
        showToast('Post shared successfully!');
      } else {
        showError(data.error || 'Something went wrong.');
      }
    } catch (err) {
      showError('Upload failed. Check your connection and try again.');
    } finally {
      submitLabel.style.display = 'inline';
      submitSpinner.style.display = 'none';
      submitBtn.disabled = false;
    }
  });

  function prependPost(data) {
    const emptyMsg = document.querySelector('.card-body .empty-msg');
    if (emptyMsg) emptyMsg.remove();

    if (!postsGrid) { location.reload(); return; }

    const card = document.createElement('div');
    card.className = 'post-card';
    card.setAttribute('data-caption', data.caption || '');
    card.setAttribute('data-date', data.date);
    card.setAttribute('data-id', data.post_id);
    card.setAttribute('onclick', 'openLightbox(this)');
    card.style.animation = 'slideUp 0.3s ease';

    const mediaArr = (data.media || []).map(m => ({ id: 0, url: m.url, type: m.media_type }));
    card.setAttribute('data-media', JSON.stringify(mediaArr));
    card.setAttribute('data-location', data.location || '');

    const first   = data.media && data.media[0];
    const mediaEl = first
      ? (first.media_type === 'video'
          ? `<video class="post-media" muted playsinline><source src="${first.url}"></video><div class="post-video-badge">▶ Video</div>`
          : `<img src="${first.url}" alt="Post" class="post-media">`)
      : '<div class="post-no-media">📝</div>';

    const multiBadge = mediaArr.length > 1 ? `<div class="post-multi-badge">⧉ ${mediaArr.length}</div>` : '';

    card.innerHTML = `
      ${mediaEl}
      ${multiBadge}
      <div class="post-actions" onclick="event.stopPropagation()">
        <button class="post-action-btn post-edit-btn" onclick="openEditPost(this.closest('.post-card'))" title="Edit caption">✏</button>
        <button class="post-action-btn post-delete-btn" onclick="openDeletePost(this.closest('.post-card'))" title="Delete post">🗑</button>
      </div>
      <div class="post-overlay">
        ${data.caption ? `<div class="post-overlay-caption">${data.caption}</div>` : ''}
        <div class="post-overlay-date">${data.date}</div>
      </div>
    `;
    postsGrid.prepend(card);
  }

  function showError(msg) {
    postError.textContent = '❌ ' + msg;
    postError.style.display = 'block';
  }

  function showToast(msg) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#27AE72;color:white;padding:12px 20px;border-radius:8px;font-family:Barlow Condensed,sans-serif;font-weight:700;font-size:14px;box-shadow:0 4px 16px rgba(0,0,0,.2);z-index:9999;transition:opacity 0.4s;';
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = 0; setTimeout(() => t.remove(), 400); }, 3000);
  }
})();
</script>


<!-- ═══════════════════════ LIGHTBOX ═══════════════════════ -->
<div id="lightbox" class="lightbox-overlay" style="display:none;" onclick="if(event.target===this)closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()">✕</button>
  <div class="lightbox-box">
    <div class="lightbox-media-wrap" id="lightboxMediaWrap" style="position:relative;">
      <div id="lightboxMedia"></div>
      <button class="lb-nav lb-prev" id="lbPrev" onclick="lbNavigate(-1)" style="display:none;">&#8249;</button>
      <button class="lb-nav lb-next" id="lbNext" onclick="lbNavigate(1)"  style="display:none;">&#8250;</button>
      <div id="lbCounter" class="lb-counter" style="display:none;"></div>
    </div>
    <div class="lightbox-info">
      <div class="lightbox-user">
        <div class="lightbox-avatar" id="lbAvatar">
          <?php if ($pic): ?>
            <img src="<?= htmlspecialchars($pic) ?>" alt="">
          <?php else: ?>
            <?= htmlspecialchars($initials) ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="lightbox-username"><?= htmlspecialchars($user['username']) ?></div>
          <div class="lightbox-date" id="lightboxDate"></div>
        </div>
      </div>

      <div class="lightbox-caption" id="lightboxCaption"></div>

      <div class="lb-comments-wrap">
        <div class="lb-comments-list" id="lbCommentsList"></div>

        <div class="lb-react-area">
          <div class="lb-like-wrap" id="lbLikeWrap">
            <button class="lb-main-like-btn" id="lbMainLikeBtn" onclick="handleMainLikeClick()">
              <span id="lbMainLikeIcon">👍</span>
              <span id="lbMainLikeLabel">Like</span>
            </button>
            <div class="lb-reaction-picker" id="lbReactionPicker">
              <button class="rp-btn" data-reaction="like"  onclick="toggleReaction('like')" >👍<div class="rp-label">Like</div></button>
              <button class="rp-btn" data-reaction="love"  onclick="toggleReaction('love')" >❤️<div class="rp-label">Love</div></button>
              <button class="rp-btn" data-reaction="fire"  onclick="toggleReaction('fire')" >🔥<div class="rp-label">Fire</div></button>
              <button class="rp-btn" data-reaction="wow"   onclick="toggleReaction('wow')"  >😮<div class="rp-label">Wow</div></button>
              <button class="rp-btn" data-reaction="haha"  onclick="toggleReaction('haha')" >😂<div class="rp-label">Haha</div></button>
            </div>
          </div>
          <div class="lb-react-summary" id="lbReactSummary"></div>
        </div>

        <div class="lb-comment-form">
          <textarea id="lbCommentInput" class="lb-comment-input" placeholder="Write a comment..." maxlength="500" rows="1"></textarea>
          <button class="lb-comment-submit" id="lbCommentSubmit" onclick="submitComment()">
            <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M2 21l21-9L2 3v7l15 2-15 2v7z"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let lbMedia  = [];
let lbIndex  = 0;

function openLightbox(card) {
  if (!card || !card.getAttribute('data-id')) return;

  const mediaRaw = card.getAttribute('data-media');
  const caption  = card.getAttribute('data-caption') || '';
  const date     = card.getAttribute('data-date')    || '';

  try {
    lbMedia = JSON.parse(mediaRaw || '[]');
  } catch(e) { lbMedia = []; }

  if (!lbMedia.length) {
    const src  = card.getAttribute('data-src')  || '';
    const type = card.getAttribute('data-type') || 'image';
    if (src) lbMedia = [{ url: src, type }];
  }

  if (!lbMedia.length) return;

  lbIndex = 0;
  renderLightboxSlide();

  document.getElementById('lightboxCaption').textContent = caption;
  document.getElementById('lightboxDate').textContent    = date;

  document.getElementById('lbPrev').style.display = lbMedia.length > 1 ? 'flex' : 'none';
  document.getElementById('lbNext').style.display = lbMedia.length > 1 ? 'flex' : 'none';
  document.getElementById('lbCounter').style.display = lbMedia.length > 1 ? 'block' : 'none';

  const lb = document.getElementById('lightbox');
  lb.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function renderLightboxSlide() {
  const m         = lbMedia[lbIndex];
  const mediaWrap = document.getElementById('lightboxMedia');
  mediaWrap.innerHTML = m.type === 'video'
    ? `<video controls autoplay muted style="max-height:80vh;width:100%;object-fit:contain;"><source src="${m.url}"></video>`
    : `<img src="${m.url}" alt="Post" style="max-height:80vh;width:100%;object-fit:contain;">`;
  document.getElementById('lbCounter').textContent = (lbIndex + 1) + ' / ' + lbMedia.length;
}

function lbNavigate(dir) {
  const vid = document.getElementById('lightboxMedia').querySelector('video');
  if (vid) vid.pause();
  lbIndex = (lbIndex + dir + lbMedia.length) % lbMedia.length;
  renderLightboxSlide();
}

function closeLightbox() {
  const lb  = document.getElementById('lightbox');
  const vid = lb.querySelector('video');
  if (vid) vid.pause();
  lb.style.display = 'none';
  document.body.style.overflow = '';
  lbMedia = []; lbIndex = 0;
}

document.addEventListener('keydown', e => {
  const lb = document.getElementById('lightbox');
  if (lb.style.display === 'none') return;
  if (e.key === 'Escape')     closeLightbox();
  if (e.key === 'ArrowRight') lbNavigate(1);
  if (e.key === 'ArrowLeft')  lbNavigate(-1);
});
</script>


<!-- ═══════════════════════ LOGOUT WARNING MODAL ═══════════════════════ -->
<div id="logoutModal" class="modal-overlay" style="display:none;">
  <div class="modal-box" style="max-width:380px;">
    <div class="modal-header" style="background:var(--navy);">
      <h3 class="modal-title">🚪 Log Out</h3>
      <button class="modal-close" onclick="closeLogoutModal()">✕</button>
    </div>
    <div style="padding:28px 26px 10px; text-align:center;">
      <div style="font-size:48px; margin-bottom:14px;">👋</div>
      <div style="font-family:'Barlow Condensed',sans-serif; font-size:20px; font-weight:800; color:var(--text-dark); margin-bottom:8px;">
        Are you sure you want to log out?
      </div>
      <div style="font-size:13.5px; color:var(--text-muted); line-height:1.6;">
        You'll need to sign in again to access your profile and tournaments.
      </div>
    </div>
    <div class="modal-actions" style="padding:20px 26px 26px;">
      <a href="logout.php" class="btn-primary" style="flex:1; padding:12px; font-size:14px; text-align:center; text-decoration:none; border-radius:8px; display:block;">
        Yes, Log Out
      </a>
      <button class="modal-cancel" onclick="closeLogoutModal()" style="flex:1;">Stay</button>
    </div>
  </div>
</div>

<script>
function confirmLogout(e) {
  e.preventDefault();
  const m = document.getElementById('logoutModal');
  m.style.display = 'flex';
  document.body.style.overflow = 'hidden';
  return false;
}
function closeLogoutModal() {
  document.getElementById('logoutModal').style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('logoutModal').addEventListener('click', function(e) {
  if (e.target === this) closeLogoutModal();
});
</script>


<!-- ═══════════════ EDIT POST MODAL ═══════════════ -->
<div id="editPostModal" class="modal-overlay" style="display:none;">
  <div class="modal-box" style="max-width:520px;">
    <div class="modal-header">
      <h3 class="modal-title">✏ Edit Post</h3>
      <button class="modal-close" onclick="closeEditPost()">✕</button>
    </div>

    <div style="padding:20px 22px 0;">
      <div style="font-family:'Barlow Condensed',sans-serif;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:10px;">Current Media</div>
      <div id="editMediaGrid" class="edit-media-grid"></div>
      <div id="noMediaMsg" style="display:none;font-size:13px;color:var(--text-muted);padding:8px 0;">No media — post will be caption only.</div>
    </div>

    <div style="padding:16px 22px 0;">
      <div style="font-family:'Barlow Condensed',sans-serif;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:8px;">Add More Media</div>
      <label for="editNewMedia" class="add-media-btn">
        <span>+ Click to add images / videos</span>
        <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">JPG, PNG, WEBP, MP4 · Max 50MB each</div>
      </label>
      <input type="file" id="editNewMedia" accept="image/*,video/*" multiple hidden>
      <div id="editNewPreviews" class="edit-media-grid" style="margin-top:10px;"></div>
    </div>

    <div style="padding:16px 22px 0;">
      <textarea id="editCaptionInput" class="modal-caption" maxlength="500" rows="3" placeholder="Write a caption..."></textarea>
      <div class="char-count"><span id="editCaptionCount">0</span> / 500</div>
    </div>

    <div id="editPostError" class="alert alert-error" style="display:none;margin:0 22px 0;"></div>

    <div class="modal-actions" style="padding:14px 22px 22px;">
      <button class="btn-primary" id="saveEditPost" style="flex:1;padding:12px;font-size:14px;">💾 Save Changes</button>
      <button class="modal-cancel" onclick="closeEditPost()">Cancel</button>
    </div>
  </div>
</div>

<!-- ═══════════════ DELETE POST MODAL ═══════════════ -->
<div id="deletePostModal" class="modal-overlay" style="display:none;">
  <div class="modal-box" style="max-width:360px;">
    <div class="modal-header" style="background:#c0392b;">
      <h3 class="modal-title">🗑 Delete Post</h3>
      <button class="modal-close" onclick="closeDeletePost()">✕</button>
    </div>
    <div style="padding:28px 26px 12px;text-align:center;">
      <div style="font-size:44px;margin-bottom:12px;">⚠️</div>
      <div style="font-family:'Barlow Condensed',sans-serif;font-size:19px;font-weight:800;color:var(--text-dark);margin-bottom:8px;">Delete this post?</div>
      <div style="font-size:13.5px;color:var(--text-muted);line-height:1.6;">This cannot be undone. All images and videos will be permanently removed.</div>
      <div id="deletePostError" class="alert alert-error" style="display:none;margin-top:14px;"></div>
    </div>
    <div class="modal-actions" style="padding:16px 26px 26px;">
      <button id="confirmDeletePost" style="flex:1;padding:12px;font-size:14px;background:#e53e3e;color:white;border:none;border-radius:8px;font-family:'Barlow Condensed',sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;cursor:pointer;">Yes, Delete</button>
      <button class="modal-cancel" onclick="closeDeletePost()">Cancel</button>
    </div>
  </div>
</div>

<script>
/* ════════════════ EDIT POST ════════════════ */
let editTargetCard  = null;
let removeMediaIds  = [];
let newFilesToAdd   = [];

function openEditPost(card) {
  editTargetCard = card;
  removeMediaIds = [];
  newFilesToAdd  = [];

  const caption = card.getAttribute('data-caption') || '';
  const media   = JSON.parse(card.getAttribute('data-media') || '[]');

  document.getElementById('editCaptionInput').value = caption;
  document.getElementById('editCaptionCount').textContent = caption.length;
  document.getElementById('editPostError').style.display = 'none';
  document.getElementById('editNewPreviews').innerHTML = '';
  document.getElementById('editNewMedia').value = '';

  renderEditMediaGrid(media);

  document.getElementById('editPostModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function renderEditMediaGrid(mediaArr) {
  const grid   = document.getElementById('editMediaGrid');
  const noMsg  = document.getElementById('noMediaMsg');
  grid.innerHTML = '';

  if (!mediaArr.length) { noMsg.style.display = 'block'; grid.style.display = 'none'; return; }
  noMsg.style.display = 'none'; grid.style.display = 'grid';

  mediaArr.forEach(m => {
    if (removeMediaIds.includes(m.id)) return;
    const wrap = document.createElement('div');
    wrap.className  = 'edit-thumb-wrap';
    wrap.dataset.id = m.id;

    const thumb = m.type === 'video'
      ? `<video class="edit-thumb" muted playsinline><source src="${m.url}"></video><div class="post-video-badge" style="font-size:10px;">▶</div>`
      : `<img src="${m.url}" class="edit-thumb" alt="">`;

    wrap.innerHTML = `${thumb}<button class="edit-thumb-remove" onclick="markRemove(${m.id}, this.closest('.edit-thumb-wrap'))">✕</button>`;
    grid.appendChild(wrap);
  });

  if (!grid.children.length) { noMsg.style.display = 'block'; grid.style.display = 'none'; }
}

function markRemove(mediaId, wrap) {
  removeMediaIds.push(mediaId);
  wrap.style.transition = 'opacity 0.2s, transform 0.2s';
  wrap.style.opacity    = '0';
  wrap.style.transform  = 'scale(0.8)';
  setTimeout(() => {
    wrap.remove();
    const grid = document.getElementById('editMediaGrid');
    if (!grid.children.length) {
      document.getElementById('noMediaMsg').style.display = 'block';
      grid.style.display = 'none';
    }
  }, 200);
}

document.getElementById('editNewMedia').addEventListener('change', function() {
  const previews = document.getElementById('editNewPreviews');
  Array.from(this.files).forEach(file => {
    newFilesToAdd.push(file);
    const wrap = document.createElement('div');
    wrap.className = 'edit-thumb-wrap';
    const isVideo = file.type.startsWith('video/');
    if (isVideo) {
      const vid = document.createElement('video');
      vid.className = 'edit-thumb'; vid.muted = true; vid.controls = false;
      vid.src = URL.createObjectURL(file);
      wrap.appendChild(vid);
      const badge = document.createElement('div');
      badge.className = 'post-video-badge'; badge.style.fontSize = '10px'; badge.textContent = '▶';
      wrap.appendChild(badge);
    } else {
      const img = document.createElement('img');
      img.className = 'edit-thumb';
      const reader = new FileReader();
      reader.onload = e => img.src = e.target.result;
      reader.readAsDataURL(file);
      wrap.appendChild(img);
    }
    const rmBtn = document.createElement('button');
    rmBtn.className = 'edit-thumb-remove';
    rmBtn.textContent = '✕';
    rmBtn.onclick = () => {
      newFilesToAdd = newFilesToAdd.filter(f => f !== file);
      wrap.remove();
    };
    wrap.appendChild(rmBtn);
    previews.appendChild(wrap);
  });
  this.value = '';
});

document.getElementById('editCaptionInput').addEventListener('input', function() {
  document.getElementById('editCaptionCount').textContent = this.value.length;
});

document.getElementById('saveEditPost').addEventListener('click', async function() {
  if (!editTargetCard) return;
  const errEl  = document.getElementById('editPostError');
  errEl.style.display = 'none';

  const fd = new FormData();
  fd.append('post_id',    editTargetCard.getAttribute('data-id'));
  fd.append('caption',    document.getElementById('editCaptionInput').value.trim());
  fd.append('remove_ids', JSON.stringify(removeMediaIds));
  newFilesToAdd.forEach(f => fd.append('new_media[]', f));

  this.textContent = '⏳ Saving...'; this.disabled = true;

  try {
    const res  = await fetch('edit_post.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      const newMediaArr = data.all_media.map(m => ({
        id:   m.id,
        url:  'uploads/posts/' + m.filename,
        type: m.media_type
      }));
      editTargetCard.setAttribute('data-media', JSON.stringify(newMediaArr));
      editTargetCard.setAttribute('data-caption', data.caption);

      if (data.all_media.length) {
        const first = data.all_media[0];
        const src   = 'uploads/posts/' + first.filename;
        const existing = editTargetCard.querySelector('.post-media, video.post-media');
        if (existing) {
          if (first.media_type === 'video') {
            existing.querySelector('source') && (existing.querySelector('source').src = src);
          } else {
            existing.src = src;
          }
        }
      }

      let badge = editTargetCard.querySelector('.post-multi-badge');
      if (data.all_media.length > 1) {
        if (!badge) { badge = document.createElement('div'); badge.className = 'post-multi-badge'; editTargetCard.appendChild(badge); }
        badge.textContent = '⧉ ' + data.all_media.length;
      } else if (badge) { badge.remove(); }

      const ovCap = editTargetCard.querySelector('.post-overlay-caption');
      if (data.caption) {
        const txt = data.caption.length > 55 ? data.caption.slice(0,55)+'…' : data.caption;
        if (ovCap) ovCap.textContent = txt;
        else {
          const ov = editTargetCard.querySelector('.post-overlay');
          if (ov) { const d=document.createElement('div'); d.className='post-overlay-caption'; d.textContent=txt; ov.prepend(d); }
        }
      } else if (ovCap) ovCap.remove();

      closeEditPost();
      showPostToast('Post updated!');
    } else {
      errEl.textContent = '❌ ' + (data.error || 'Failed to save.');
      errEl.style.display = 'block';
    }
  } catch(e) {
    errEl.textContent = '❌ Network error.'; errEl.style.display = 'block';
  } finally {
    this.textContent = '💾 Save Changes'; this.disabled = false;
  }
});

function closeEditPost() {
  document.getElementById('editPostModal').style.display = 'none';
  document.body.style.overflow = '';
  editTargetCard = null; removeMediaIds = []; newFilesToAdd = [];
}

/* ════════════════ DELETE POST ════════════════ */
let deleteTargetCard = null;
function openDeletePost(card) {
  deleteTargetCard = card;
  document.getElementById('deletePostError').style.display = 'none';
  document.getElementById('deletePostModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeDeletePost() {
  document.getElementById('deletePostModal').style.display = 'none';
  document.body.style.overflow = '';
  deleteTargetCard = null;
}
document.getElementById('confirmDeletePost').addEventListener('click', async function() {
  if (!deleteTargetCard) return;
  const errEl = document.getElementById('deletePostError');
  errEl.style.display = 'none';
  this.textContent = '⏳ Deleting...'; this.disabled = true;
  try {
    const fd = new FormData();
    fd.append('post_id', deleteTargetCard.getAttribute('data-id'));
    const res  = await fetch('delete_post.php', { method: 'POST', body: fd });
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch(e) { errEl.textContent = '❌ Server error: ' + text.slice(0,100); errEl.style.display='block'; return; }

    if (data.success) {
      const cardToRemove = deleteTargetCard;
      closeDeletePost();

      cardToRemove.style.transition = 'opacity 0.25s, transform 0.25s';
      cardToRemove.style.opacity = '0';
      cardToRemove.style.transform = 'scale(0.8)';

      setTimeout(() => {
        cardToRemove.remove();

        const grid = document.querySelector('.posts-grid');
        const cardBody = document.getElementById('postsCardBody');

        if (grid && grid.querySelectorAll('.post-card').length === 0) {
          grid.remove();
        }

        if (cardBody && !cardBody.querySelector('.post-card') && !cardBody.querySelector('.posts-grid')) {
          cardBody.innerHTML = '<p class="empty-msg">No posts yet.</p>';
        }
      }, 280);

      showPostToast('Post deleted.');
    } else {
      errEl.textContent = '❌ ' + (data.error || 'Failed to delete.');
      errEl.style.display = 'block';
    }
  } catch(e) {
    errEl.textContent = '❌ Network error.';
    errEl.style.display = 'block';
  } finally {
    this.textContent = 'Yes, Delete';
    this.disabled = false;
  }
});

document.getElementById('editPostModal').addEventListener('click', e => { if(e.target===document.getElementById('editPostModal')) closeEditPost(); });
document.getElementById('deletePostModal').addEventListener('click', e => { if(e.target===document.getElementById('deletePostModal')) closeDeletePost(); });

function showPostToast(msg) {
  const t = document.createElement('div');
  t.textContent = msg;
  t.style.cssText='position:fixed;bottom:24px;right:24px;background:#27AE72;color:white;padding:12px 20px;border-radius:8px;font-family:Barlow Condensed,sans-serif;font-weight:700;font-size:14px;box-shadow:0 4px 16px rgba(0,0,0,.2);z-index:9999;transition:opacity 0.4s;';
  document.body.appendChild(t);
  setTimeout(()=>{ t.style.opacity=0; setTimeout(()=>t.remove(),400); },2500);
}
</script>


<script>
/* ════════════════════════════════════════════
   REACTIONS + COMMENTS
════════════════════════════════════════════ */
let currentPostId  = null;
const EMOJIS = { like:'👍', love:'❤️', fire:'🔥', wow:'😮', haha:'😂' };
const REACTION_COLORS = { like:'#1877F2', love:'#E0245E', fire:'#F97316', wow:'#F59E0B', haha:'#F59E0B' };
let currentMyReaction = null;

async function loadPostData(postId) {
  currentPostId = postId;

  updateReactionUI({}, null, 0);
  document.getElementById('lbCommentsList').innerHTML = '<div class="lb-no-comments" style="opacity:0.35;font-size:12px;">Loading...</div>';

  try {
    const res  = await fetch('post_actions.php?action=load&post_id=' + postId);
    const text = await res.text();

    let data;
    try {
      data = JSON.parse(text);
    } catch(e) {
      document.getElementById('lbCommentsList').innerHTML =
        '<div class="lb-no-comments" style="color:red;font-size:11px;">Parse error: ' + text.slice(0, 150) + '</div>';
      return;
    }

    if (!data.success) {
      document.getElementById('lbCommentsList').innerHTML =
        '<div class="lb-no-comments" style="color:red;font-size:11px;">' + (data.error || 'Load failed') + '</div>';
      return;
    }

    updateReactionUI(data.counts || {}, data.myReaction || null, data.total || 0);
    renderComments(data.comments || []);
    updateGridReactionBar(postId, data.counts || {}, data.total || 0);

  } catch(e) {
    document.getElementById('lbCommentsList').innerHTML =
      '<div class="lb-no-comments" style="color:red;font-size:11px;">Network error: ' + e.message + '</div>';
  }
}

function updateReactionUI(counts, myReaction, total) {
  currentMyReaction = myReaction;

  Object.keys(EMOJIS).forEach(r => {
    const btn = document.querySelector(`.rp-btn[data-reaction="${r}"]`);
    if (btn) btn.classList.toggle('rp-active', myReaction === r);
  });

  const mainIcon  = document.getElementById('lbMainLikeIcon');
  const mainLabel = document.getElementById('lbMainLikeLabel');
  const mainBtn   = document.getElementById('lbMainLikeBtn');
  if (mainIcon && mainLabel && mainBtn) {
    if (myReaction) {
      mainIcon.textContent  = EMOJIS[myReaction];
      mainLabel.textContent = myReaction.charAt(0).toUpperCase() + myReaction.slice(1);
      mainBtn.classList.add('lb-liked');
      mainBtn.style.color = REACTION_COLORS[myReaction] || 'var(--orange)';
    } else {
      mainIcon.textContent  = '👍';
      mainLabel.textContent = 'Like';
      mainBtn.classList.remove('lb-liked');
      mainBtn.style.color = '';
    }
  }

  const summary = document.getElementById('lbReactSummary');
  if (summary) {
    const sorted = Object.entries(counts)
      .filter(([,v]) => v > 0)
      .sort((a,b) => b[1]-a[1]);
    if (sorted.length) {
      const topEmojis = sorted.slice(0,3).map(([r]) =>
        `<span class="summary-emoji">${EMOJIS[r]}</span>`
      ).join('');
      summary.innerHTML = `<div class="summary-bubbles">${topEmojis}</div><span class="summary-count">${total}</span>`;
    } else {
      summary.innerHTML = '';
    }
  }
}

function handleMainLikeClick() {
  const picker = document.getElementById('lbReactionPicker');
  const isTouch = window.matchMedia('(hover: none)').matches;
  if (isTouch) {
    picker.classList.toggle('picker-open');
    return;
  }
  if (currentMyReaction) {
    toggleReaction(currentMyReaction);
  } else {
    toggleReaction('like');
  }
}

document.addEventListener('click', function(e) {
  const picker  = document.getElementById('lbReactionPicker');
  const wrap    = document.getElementById('lbLikeWrap');
  if (picker && wrap && !wrap.contains(e.target)) {
    picker.classList.remove('picker-open');
  }
});

async function toggleReaction(reaction) {
  const picker = document.getElementById('lbReactionPicker');
  if (picker) { picker.style.opacity='0'; picker.style.pointerEvents='none'; setTimeout(()=>{ picker.style.opacity=''; picker.style.pointerEvents=''; }, 300); }
  if (!currentPostId) return;
  const fd = new FormData();
  fd.append('action', 'react');
  fd.append('post_id', currentPostId);
  fd.append('reaction', reaction);
  try {
    const res  = await fetch('post_actions.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      updateReactionUI(data.counts, data.myReaction, data.total);
      updateGridReactionBar(currentPostId, data.counts, data.total);
    }
  } catch(e) {}
}

function renderComments(comments) {
  const list = document.getElementById('lbCommentsList');
  if (!comments.length) {
    list.innerHTML = '<div class="lb-no-comments">No comments yet. Be the first!</div>';
    return;
  }
  list.innerHTML = comments.map(c => {
    const picSrc = c.profile_pic ? 'uploads' + c.profile_pic : null;
    const initials = c.username.split(' ').map(w => w[0]).join('').toUpperCase().slice(0,2);
    const avatar = picSrc
      ? `<img src="${picSrc}" class="comment-avatar-img" alt="">`
      : `<div class="comment-avatar-init">${initials}</div>`;
    return `
    <div class="lb-comment" id="comment-${c.id}">
      <div class="comment-avatar">${avatar}</div>
      <div class="comment-body">
        <div class="comment-meta">
          <span class="comment-username">${c.username}</span>
          <span class="comment-time">${formatDate(c.created_at)}</span>
        </div>
        <div class="comment-text">${escapeHtml(c.comment)}</div>
      </div>
    </div>`;
  }).join('');
  list.scrollTop = list.scrollHeight;
}

async function submitComment() {
  const input = document.getElementById('lbCommentInput');
  const text  = input.value.trim();
  if (!text || !currentPostId) return;

  const btn = document.getElementById('lbCommentSubmit');
  btn.disabled = true;

  const fd = new FormData();
  fd.append('action', 'comment');
  fd.append('post_id', currentPostId);
  fd.append('comment', text);

  try {
    const res  = await fetch('post_actions.php', { method: 'POST', body: fd });
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); }
    catch(e) {
      alert('Server error: ' + text.slice(0, 200));
      return;
    }

    if (data.success) {
      input.value = '';
      input.style.height = 'auto';

      const list   = document.getElementById('lbCommentsList');
      const noMsg  = list.querySelector('.lb-no-comments');
      if (noMsg) noMsg.remove();

      const picSrc  = data.profile_pic ? 'uploads' + data.profile_pic : null;
      const initials = data.username.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
      const avatar  = picSrc
        ? `<img src="${picSrc}" class="comment-avatar-img" alt="">`
        : `<div class="comment-avatar-init">${initials}</div>`;

      const div = document.createElement('div');
      div.className = 'lb-comment';
      div.id = 'comment-' + data.comment_id;
      div.innerHTML = `
        <div class="comment-avatar">${avatar}</div>
        <div class="comment-body">
          <div class="comment-meta">
            <span class="comment-username">${escapeHtml(data.username)}</span>
            <span class="comment-time">${data.created_at}</span>
          </div>
          <div class="comment-text">${escapeHtml(data.comment)}</div>
        </div>`;
      list.appendChild(div);
      list.scrollTop = list.scrollHeight;
    } else {
      alert(data.error || 'Failed to post comment.');
    }
  } catch(e) {
    alert('Network error posting comment.');
  } finally {
    btn.disabled = false;
  }
}

document.getElementById('lbCommentInput').addEventListener('keydown', e => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); submitComment(); }
});

function updateGridReactionBar(postId, counts, total) {
  const bar = document.getElementById('reactionBar-' + postId);
  if (!bar) return;

  const top = Object.entries(counts)
    .filter(([,v]) => v > 0)
    .sort((a,b) => b[1]-a[1])
    .slice(0,3)
    .map(([r]) => EMOJIS[r])
    .join('');

  if (total > 0) {
    bar.innerHTML = `<span class="grid-reactions">${top} ${total}</span>`;
    bar.style.display = 'block';
  } else {
    bar.innerHTML = '';
    bar.style.display = 'none';
  }
}

const _origOpenLightbox = openLightbox;
openLightbox = function(card) {
  _origOpenLightbox(card);
  const postId = card.getAttribute('data-id');
  if (postId) loadPostData(parseInt(postId));
};

const _origCloseLightbox = closeLightbox;
closeLightbox = function() {
  _origCloseLightbox();
  currentPostId = null;
};

function escapeHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatDate(str) {
  try { return new Date(str).toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric'}); }
  catch(e) { return str; }
}

</script>

<script src="profile-tab.js"></script>

  <div id="locationResults" class="location-results">
    <div class="location-hint">Start typing to search...</div>
  </div>
</div>
<script>
  // Find your home/back button and update it
  document.addEventListener('DOMContentLoaded', function() {
    const homeBtn = document.getElementById('home-btn'); // or whatever your button ID is
    if (homeBtn) {
      homeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const params = new URLSearchParams(window.location.search);
        const redirect = params.get('redirect');
        
        if (redirect === 'organizer') {
          window.location.href = '/Tourna/organizer_dashboard.php';
        } else {
          window.location.href = '/Tourna/athlete_dashboard.php';
        }
      });
    }
  });
</script>
</body>
</html>