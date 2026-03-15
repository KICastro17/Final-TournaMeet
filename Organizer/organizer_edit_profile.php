<?php
session_start();
include("../config.php");

/* ================= AUTH GUARD ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= FETCH USER ================= */
$stmt = $conn->prepare("SELECT id, username, email, role, bio, profile_pic, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) { 
    session_destroy(); 
    header("Location: ../login.php"); 
    exit(); 
}

$error = '';
$success = '';

/* ================= HANDLE FORM SUBMISSION ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username)) {
        $error = 'Username is required';
    } elseif (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters';
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = 'Passwords do not match';
    }

    if (empty($error)) {
        // Handle profile picture upload
        $pic_path = $user['profile_pic'];
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file = $_FILES['profile_pic'];
            
            if (!in_array($file['type'], $allowed)) {
                $error = 'Only image files are allowed (JPEG, PNG, GIF, WebP)';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'Image must be smaller than 5MB';
            }

            if (empty($error)) {
                $upload_dir = '../uploads/profiles/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $filename = 'profile_' . $user_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $filepath = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    if ($user['profile_pic'] && file_exists('../uploads' . $user['profile_pic'])) {
                        unlink('../uploads' . $user['profile_pic']);
                    }
                    $pic_path = '/uploads/profiles/' . $filename;
                } else {
                    $error = 'Failed to upload image';
                }
            }
        }

        // Update user info
        if (empty($error)) {
            $sql = "UPDATE users SET username = ?, email = ?, bio = ?, profile_pic = ?";
            $params = [$username, $email, $bio, $pic_path];
            $types = "ssss";

            if (!empty($new_password)) {
                $sql .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_BCRYPT);
                $types .= "s";
            }

            $sql .= " WHERE id = ?";
            $params[] = $user_id;
            $types .= "i";

            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);

                if ($stmt->execute()) {
                    $success = 'Profile updated successfully!';
                    $_SESSION['username'] = $username;
                    $conn->close();
                    // Redirect back to organizer profile
                    header("Location: organizer_profile.php?updated=1");
                    exit();
                } else {
                    $error = 'Database error: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Database error: ' . $conn->error;
            }
        }
    }
}

// Close connection only if it's still open and form wasn't processed successfully
if (!isset($success) && isset($conn)) {
    $conn->close();
}

/* ================= HELPERS ================= */
$pic = (!empty($user['profile_pic']) && file_exists('../uploads' . $user['profile_pic'])) ? '../uploads' . $user['profile_pic'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../favicon.png">
  <title>Edit Organizer Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="organizer_profile.css">
  <style>
    .edit-container {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
    }

    .edit-card {
      background: var(--white);
      border-radius: 14px;
      border: 1.5px solid var(--border);
      overflow: hidden;
      box-shadow: var(--shadow-md);
    }

    .edit-header {
      background: linear-gradient(135deg, var(--primary) 0%, #F59C42 100%);
      padding: 24px;
      color: var(--white);
    }

    .edit-header h1 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.8rem;
      letter-spacing: 1.5px;
      margin: 0;
      text-transform: uppercase;
    }

    .edit-body {
      padding: 32px;
    }

    .form-group {
      margin-bottom: 24px;
    }

    .form-group:last-child {
      margin-bottom: 0;
    }

    .form-label {
      display: block;
      font-weight: 700;
      font-size: 0.85rem;
      color: var(--text);
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 8px;
    }

    .form-input,
    .form-textarea {
      width: 100%;
      padding: 12px 14px;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem;
      color: var(--text);
      background: var(--light);
      outline: none;
      transition: all 0.2s;
      box-sizing: border-box;
    }

    .form-input:focus,
    .form-textarea:focus {
      border-color: var(--primary);
      background: var(--white);
      box-shadow: 0 0 0 3px rgba(244, 123, 32, 0.15);
    }

    .form-textarea {
      resize: vertical;
      min-height: 100px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .profile-pic-section {
      text-align: center;
      padding-bottom: 24px;
      border-bottom: 1.5px solid var(--border);
      margin-bottom: 24px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .current-pic {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--primary);
      margin: 0 auto 12px;
      display: block;
    }

    .pic-upload-label {
      display: inline-block;
      background: var(--primary);
      color: var(--white);
      padding: 8px 16px;
      border-radius: 50px;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.8rem;
      transition: all 0.2s;
      margin-bottom: 8px;
    }

    .pic-upload-label:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }

    #profile_pic {
      display: none;
    }

    .form-actions {
      display: flex;
      gap: 12px;
      margin-top: 32px;
      padding-top: 24px;
      border-top: 1.5px solid var(--border);
    }

    .btn-save,
    .btn-cancel {
      flex: 1;
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      font-family: 'Bebas Neue', sans-serif;
      font-size: 0.95rem;
      letter-spacing: 1px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-save {
      background: var(--primary);
      color: var(--white);
      box-shadow: 0 4px 14px rgba(244, 123, 32, 0.35);
    }

    .btn-save:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }

    .btn-cancel {
      background: var(--light);
      color: var(--text);
      border: 2px solid var(--border);
    }

    .btn-cancel:hover {
      background: var(--border);
      transform: translateY(-2px);
    }

    .alert {
      padding: 14px 18px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: 500;
    }

    .alert-error {
      background: #FFE6E6;
      color: #E74C3C;
      border: 1.5px solid #F5A5A5;
    }

    .alert-success {
      background: #E6F8F0;
      color: #27AE72;
      border: 1.5px solid #A0E0B8;
    }

    @media (max-width: 599px) {
      .form-row {
        grid-template-columns: 1fr;
      }

      .edit-body {
        padding: 20px;
      }

      .form-actions {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-left">
    <a href="organizer_index.php" class="nav-home">
      <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 2L2 9h2v9h5v-5h2v5h5V9h2L10 2z"/>
      </svg>
      HOME
    </a>
  </div>
  <div class="nav-center">
    <div class="search-bar">
      <input type="text" placeholder="Search tournaments...">
      <button type="button"><svg width="13" height="13" fill="white" viewBox="0 0 20 20"><path d="M12.9 14.32a8 8 0 111.42-1.42l4.38 4.39-1.42 1.41-4.38-4.38zM8 14A6 6 0 108 2a6 6 0 000 12z"/></svg></button>
    </div>
  </div>
  <div class="nav-right">
    <span class="nav-user">👤 <?= htmlspecialchars($user['username']) ?></span>
    <button class="btn-logout" onclick="window.location.href='../logout.php'">LOGOUT</button>
  </div>
</nav>

<main>
  <div class="edit-container">
    <div class="edit-card">
      <div class="edit-header">
        <h1>✏ Edit Profile</h1>
      </div>

      <div class="edit-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
          <!-- PROFILE PICTURE -->
          <div class="profile-pic-section">
            <?php if ($pic): ?>
              <img src="<?= htmlspecialchars($pic) ?>" alt="Current Profile Picture" class="current-pic">
            <?php else: ?>
              <div class="current-pic" style="background: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: white;">
                <?= substr(strtoupper($user['username']), 0, 1) ?>
              </div>
            <?php endif; ?>
            <br>
            <label for="profile_pic" class="pic-upload-label">📷 CHANGE PICTURE</label>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">JPEG, PNG, or GIF (Max 5MB)</p>
          </div>

          <!-- USERNAME & EMAIL -->
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-input" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
          </div>

          <!-- BIO -->
          <div class="form-group">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-textarea" placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
          </div>

          <!-- PASSWORD SECTION -->
          <div class="form-group" style="border-top: 1.5px solid var(--border); padding-top: 24px; margin-top: 24px;">
            <label class="form-label" style="margin-bottom: 16px;">Change Password (Leave empty to keep current)</label>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-input" placeholder="At least 6 characters">
              </div>
              <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-input" placeholder="Repeat password">
              </div>
            </div>
          </div>

          <!-- ACTIONS -->
          <div class="form-actions">
            <button type="submit" class="btn-save">💾 SAVE CHANGES</button>
            <button type="button" class="btn-cancel" onclick="window.location.href='organizer_profile.php'">CANCEL</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>

<script>
  // Preview image before upload
  document.getElementById('profile_pic').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(event) {
        const img = document.querySelector('.current-pic');
        if (img.tagName === 'IMG') {
          img.src = event.target.result;
        } else {
          const newImg = document.createElement('img');
          newImg.src = event.target.result;
          newImg.className = 'current-pic';
          img.replaceWith(newImg);
        }
      };
      reader.readAsDataURL(file);
    }
  });
</script>

</body>
</html>