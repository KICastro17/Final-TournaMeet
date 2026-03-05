<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$pic = !empty($user['profile_pic'])
    ? "uploads" . ltrim($user['profile_pic'], '/')
    : "default_avatar.jpg";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile.css">
</head>

<body class="profile-body">

<div class="app-layout">

<!-- ================= SIDEBAR ================= -->
<div id="appSidebar" class="app-sidebar">

    <div class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fa fa-bars"></i>
    </div>

    <a href="dashboard.php"><i class="fa fa-home"></i><span>Dashboard</span></a>
    <a href="videos.php"><i class="fa fa-video"></i><span>Videos</span></a>
    <a href="shop.php"><i class="fa fa-store"></i><span>Shop</span></a>
    <a href="map.php"><i class="fa fa-map"></i><span>Map</span></a>
    <a href="teams.php"><i class="fa fa-users"></i><span>Teams</span></a>
    <a href="profile.php" class="active"><i class="fa fa-user"></i><span>Profile</span></a>
    <a href="logout.php" class="logout"><i class="fa fa-sign-out-alt"></i><span>Logout</span></a>

</div>


<!-- ================= PAGE ================= -->
<div class="profile-page">

<div class="profile-card">

<!-- LEFT PANEL -->
<div class="profile-left">

    <img src="<?= htmlspecialchars($pic) ?>" class="avatar">

    <h2><?= htmlspecialchars($user['username']) ?></h2>
    <p><?= htmlspecialchars($user['email']) ?></p>

    <a href="edit_profile.php" class="edit-btn">Edit Profile</a>

</div>


<!-- ================= RIGHT PANEL ================= -->
<div class="profile-right">

<h2>Posts</h2>

<button class="add-post-btn" onclick="openModal()">+ Add Post</button>


<!-- ================= CREATE MODAL ================= -->
<div id="postModal" class="create-modal">

<div class="create-box">

<div class="create-header">
    <h3>Create Post</h3>
    <span onclick="closeModal()">×</span>
</div>

<div class="create-user">
    <img src="<?= htmlspecialchars($pic) ?>" class="create-avatar">
    <?= htmlspecialchars($user['username']) ?>
</div>


<form action="save_post.php" method="POST" enctype="multipart/form-data">

    <!-- caption -->
    <textarea
        name="content"
        class="create-caption"
        placeholder="What's on your mind?"
        required
    ></textarea>

    <!-- preview -->
    <div id="previewContainer"></div>

    <!-- footer -->
    <div class="create-footer">

        <input type="file" id="mediaInput" name="media" hidden accept="image/*,video/*">

        <label for="mediaInput" class="media-btn">
            <i class="fa fa-photo-film"></i>
        </label>

        <button type="submit" class="post-submit">Post</button>

    </div>

</form>

</div>
</div>


<!-- ================= POSTS ================= -->
<div class="posts-wrapper">

<?php
$postStmt = $conn->prepare("SELECT * FROM posts WHERE user_id=? ORDER BY created_at DESC");
$postStmt->bind_param("i", $id);
$postStmt->execute();
$posts = $postStmt->get_result();

if($posts->num_rows == 0){
    echo "<p class='no-posts'>        No posts yet</p>";
}

while($post = $posts->fetch_assoc()):
$media = $post['image'];
?>

<div class="post-card" onclick="viewPost(this)">

    <div class="post-header">
        <img src="<?= htmlspecialchars($pic) ?>" class="post-avatar">

        <div>
            <div class="post-name"><?= htmlspecialchars($user['username']) ?></div>
            <div class="post-date"><?= $post['created_at'] ?></div>
        </div>

        <div class="post-actions" onclick="event.stopPropagation()">

    <!-- EDIT -->
    <button 
        type="button"
        class="post-edit"
        onclick="openEditPrompt(<?= $post['id'] ?>, `<?= htmlspecialchars($post['caption'], ENT_QUOTES) ?>`)">
        Edit
    </button>

    <!-- DELETE -->
    <form action="delete_post.php" method="POST" style="display:inline;">
        <input type="hidden" name="id" value="<?= $post['id'] ?>">
        <button type="submit" class="post-delete">Delete</button>
    </form>

</div>

    <div class="post-text">
        <?= nl2br(htmlspecialchars($post['caption'])) ?>
    </div>

    <?php if($media): ?>
        <?php if(preg_match('/\.(mp4|webm|mov)$/i', $media)): ?>
            <video src="uploads/<?= $media ?>" class="post-video"></video>
        <?php else: ?>
            <img src="uploads/<?= $media ?>" class="post-image">
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php endwhile; ?>

</div>
</div>
</div>
</div>
<div id="postViewModal" class="post-view-modal" onclick="closePostModal()">
    <div id="postViewContent" class="post-view-content" onclick="event.stopPropagation()"></div>
</div>

<!-- ✅ ADD HERE -->
<div id="editModal" class="create-modal">
    <div class="create-box">

        <div class="create-header">
            <h3>Edit Caption</h3>
            <span onclick="closeEditModal()">×</span>
        </div>

        <form action="update_post.php" method="POST">
            <input type="hidden" name="post_id" id="editPostId">

            <textarea name="caption" id="editCaption" class="create-caption"></textarea>

            <button type="submit" class="post-submit">Save Changes</button>
        </form>

    </div>
</div>


<!-- ================= SCRIPTS ================= -->
<script>

// sidebar
function toggleSidebar(){
    document.getElementById("appSidebar").classList.toggle("collapsed");
}

// modal
function openModal(){
    document.getElementById("postModal").style.display="flex";
}
function closeModal(){
    document.getElementById("postModal").style.display="none";
}

// textarea auto grow
const caption = document.querySelector(".create-caption");

caption.addEventListener("input", () => {
    caption.style.height = "auto";
    caption.style.height = caption.scrollHeight + "px";
});

// media preview
const input = document.getElementById("mediaInput");
const preview = document.getElementById("previewContainer");

input.addEventListener("change", e => {

    const file = e.target.files[0];
    if(!file) return;

    const url = URL.createObjectURL(file);

    preview.innerHTML = "";

    if(file.type.startsWith("video")){
        preview.innerHTML = `<video controls src="${url}"></video>`;
    } else {
        preview.innerHTML = `<img src="${url}">`;
    }
});
function viewPost(card){
    const modal = document.getElementById("postViewModal");
    const content = document.getElementById("postViewContent");

    content.innerHTML = card.outerHTML;

    // re-enable video controls
    content.querySelectorAll("video").forEach(v=>{
        v.controls = true;
    });

    modal.style.display = "flex";
}

function closePostModal(){
    document.getElementById("postViewModal").style.display="none";
}
function openEditModal(id, text){
    document.getElementById("editPostId").value = id;
    document.getElementById("editCaption").value = text;
    document.getElementById("editModal").style.display = "flex";
}

function closeEditModal(){
    document.getElementById("editModal").style.display = "none";
}
</script>

</body>
</html>