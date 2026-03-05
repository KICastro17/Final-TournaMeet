<?php
session_start();
require 'config.php';

/* ================= AUTH GUARD ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, bio, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

/* ================= PROFILE PIC PATH ================= */
$pic = (!empty($user['profile_pic']) && file_exists('uploads' . $user['profile_pic']))
    ? 'uploads' . $user['profile_pic']
    : null;

/* ================= AVATAR INITIALS FALLBACK ================= */
$initials = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', trim($user['username'])))));
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile – Ball Sports</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="profile-tab.css">
  <style>
    /* ── PAGE LAYOUT ── */
    .edit-page {
      min-height: 100vh;
      background: var(--gray-bg);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .edit-card {
      background: white;
      border-radius: 16px;
      border: 1.5px solid var(--gray-border);
      width: 100%;
      max-width: 520px;
      overflow: hidden;
      box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    }

    /* ── CARD HEADER ── */
    .edit-card-header {
      background: var(--navy);
      padding: 28px 32px 22px;
      position: relative;
      overflow: hidden;
    }
    .edit-card-header::before {
      content: '';
      position: absolute; inset: 0;
      background: repeating-linear-gradient(45deg, transparent, transparent 18px, rgba(255,255,255,0.03) 18px, rgba(255,255,255,0.03) 36px);
    }
    .edit-card-header h2 {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 22px; font-weight: 800;
      color: white; letter-spacing: 0.5px;
      position: relative; z-index: 1;
    }
    .edit-card-header p {
      font-size: 13px; color: rgba(255,255,255,0.45);
      margin-top: 3px; position: relative; z-index: 1;
    }

    /* ── AVATAR UPLOAD ── */
    .avatar-upload-wrap {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 28px 32px 0;
      gap: 10px;
    }
    .avatar-upload-ring {
      position: relative;
      width: 100px; height: 100px;
      cursor: pointer;
    }
    .avatar-upload-ring img,
    .avatar-upload-ring .avatar-initials {
      width: 100px; height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--gray-border);
      display: flex; align-items: center; justify-content: center;
    }
    .avatar-initials {
      background: var(--orange);
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 800; font-size: 34px;
      color: white; letter-spacing: 1px;
    }
    .avatar-edit-btn {
      position: absolute; bottom: 2px; right: 2px;
      width: 30px; height: 30px;
      background: var(--orange);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      border: 3px solid white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      transition: background 0.2s, transform 0.2s;
    }
    .avatar-upload-ring:hover .avatar-edit-btn {
      background: var(--orange-light);
      transform: scale(1.1);
    }
    .avatar-edit-btn svg { width: 12px; height: 12px; }
    .avatar-upload-hint {
      font-size: 12px; color: var(--text-muted);
      font-weight: 500;
    }

    /* ── FORM BODY ── */
    .edit-form-body {
      padding: 24px 32px 32px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 7px;
    }
    .form-group label {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 12px; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.8px;
      color: var(--text-muted);
    }
    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid var(--gray-border);
      border-radius: 8px;
      font-family: 'Barlow', sans-serif;
      font-size: 14px;
      color: var(--text-dark);
      background: var(--gray-bg);
      outline: none;
      transition: border-color 0.2s, background 0.2s;
      resize: none;
    }
    .form-group input:focus,
    .form-group textarea:focus {
      border-color: var(--orange);
      background: white;
    }
    .form-group textarea { height: 100px; line-height: 1.6; }

    .char-count {
      font-size: 11px; color: var(--text-muted);
      text-align: right; margin-top: -4px;
    }
    .char-count.over { color: #e53e3e; }

    /* ── BUTTONS ── */
    .edit-actions {
      display: flex; gap: 10px; margin-top: 4px;
    }
    .edit-actions .btn-primary {
      flex: 1; padding: 12px;
      font-size: 14px; letter-spacing: 0.8px;
      border-radius: 8px; cursor: pointer;
      border: none;
    }
    .edit-actions .btn-secondary {
      flex: 1; padding: 12px;
      font-size: 14px; letter-spacing: 0.8px;
      border-radius: 8px; cursor: pointer;
      text-align: center; text-decoration: none;
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 700; text-transform: uppercase;
      background: rgba(26,37,64,0.06);
      color: var(--text-dark);
      border: 1.5px solid var(--gray-border);
      transition: background 0.2s;
    }
    .edit-actions .btn-secondary:hover { background: rgba(26,37,64,0.12); }

    /* ── ERROR / SUCCESS ── */
    .alert {
      padding: 11px 14px;
      border-radius: 8px;
      font-size: 13px; font-weight: 600;
      margin-bottom: 4px;
    }
    .alert-error   { background: rgba(229,62,62,0.08);  color: #c0392b; border: 1.5px solid rgba(229,62,62,0.2); }
    .alert-success { background: rgba(39,174,96,0.08);  color: #27AE72; border: 1.5px solid rgba(39,174,96,0.2); }
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="index.php" class="nav-home">
    <svg width="14" height="14" fill="white" viewBox="0 0 20 20"><path d="M10 2L2 9h2v9h5v-5h2v5h5V9h2L10 2z"/></svg>
    HOME
  </a>
  <div class="search-bar">
    <input type="text" placeholder="Search tournaments...">
    <button>
      <svg width="13" height="13" fill="white" viewBox="0 0 20 20"><path d="M12.9 14.32a8 8 0 111.42-1.42l4.38 4.39-1.42 1.41-4.38-4.38zM8 14A6 6 0 108 2a6 6 0 000 12z"/></svg>
    </button>
  </div>
  <div class="nav-right">
    <span class="nav-user">👤 <?= htmlspecialchars($user['username']) ?></span>
    <a href="logout.php" class="btn-logout">Logout</a>
  </div>
</nav>

<div class="edit-page">
  <div class="edit-card">

    <!-- HEADER -->
    <div class="edit-card-header">
      <h2>✏ Edit Profile</h2>
      <p>Update your username, bio, and profile picture</p>
    </div>

    <!-- FORM -->
    <form action="update_profile.php" method="POST" enctype="multipart/form-data">

      <!-- AVATAR UPLOAD -->
      <div class="avatar-upload-wrap">
        <label for="pfpInput" class="avatar-upload-ring">
          <?php if ($pic): ?>
            <img src="<?= htmlspecialchars($pic) ?>" id="preview" alt="Profile Picture">
          <?php else: ?>
            <div class="avatar-initials" id="initialsBox"><?= htmlspecialchars($initials) ?></div>
            <img src="" id="preview" alt="Profile Picture" style="display:none; width:100px; height:100px; border-radius:50%; object-fit:cover; border:4px solid var(--gray-border);">
          <?php endif; ?>
          <div class="avatar-edit-btn">
            <svg fill="white" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
          </div>
        </label>
        <input type="file" id="pfpInput" name="profile_pic" accept="image/*" hidden>
        <span class="avatar-upload-hint">Click avatar to change · JPG, PNG, WEBP · Max 2MB</span>
      </div>

      <div class="edit-form-body">

        <!-- USERNAME -->
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username"
                 value="<?= htmlspecialchars($user['username']) ?>"
                 maxlength="100" required>
        </div>

        <!-- BIO -->
        <div class="form-group">
          <label for="bio">Bio</label>
          <textarea id="bio" name="bio" maxlength="300"
                    placeholder="Tell others about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
          <div class="char-count"><span id="bioCount"><?= strlen($user['bio'] ?? '') ?></span> / 300</div>
        </div>

        <!-- ACTIONS -->
        <div class="edit-actions">
          <button type="submit" class="btn-primary">💾 Save Changes</button>
          <a href="profile.php" class="btn-secondary">Cancel</a>
        </div>

      </div>
    </form>

  </div>
</div>

<script>
  // Live image preview
  document.getElementById('pfpInput').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const preview = document.getElementById('preview');
    const initials = document.getElementById('initialsBox');

    const reader = new FileReader();
    reader.onload = function (ev) {
      preview.src = ev.target.result;
      preview.style.display = 'block';
      if (initials) initials.style.display = 'none';
    };
    reader.readAsDataURL(file);
  });

  // Bio character counter
  const bioEl    = document.getElementById('bio');
  const bioCount = document.getElementById('bioCount');
  bioEl.addEventListener('input', function () {
    const len = bioEl.value.length;
    bioCount.textContent = len;
    bioCount.parentElement.classList.toggle('over', len >= 300);
  });
</script>

</body>
</html>