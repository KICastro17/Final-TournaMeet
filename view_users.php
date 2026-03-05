<?php
session_start();
include "config.php";

/* ===== ADMIN CHECK ===== */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== "admin") {
    die("🚫 Admin Access Only");
}

/* ===== SEARCH & FILTER ===== */

$search = $_GET['search'] ?? "";
$filter = $_GET['filter'] ?? "";

$query = "SELECT * FROM users WHERE role != 'admin'";

if (!empty($search)) {
    $searchEsc = $conn->real_escape_string($search);
    $query .= " AND (username LIKE '%$searchEsc%' OR email LIKE '%$searchEsc%')";
}

if (!empty($filter)) {
    $filterEsc = $conn->real_escape_string($filter);
    $query .= " AND status='$filterEsc'";
}

$query .= " ORDER BY id DESC";

$result = $conn->query($query);


/* ================================================= */
/* ===== ADDED: STATS (SAFE – DOESN'T AFFECT LOGIC) */
/* ================================================= */

$totalUsers = $conn->query("SELECT COUNT(*) c FROM users WHERE role!='admin'")->fetch_assoc()['c'];
$pendingCount = $conn->query("SELECT COUNT(*) c FROM users WHERE status='pending'")->fetch_assoc()['c'];
$approvedCount = $conn->query("SELECT COUNT(*) c FROM users WHERE status='approved'")->fetch_assoc()['c'];
$bannedCount = $conn->query("SELECT COUNT(*) c FROM users WHERE status='banned'")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<!-- ================================================= -->
<!-- ================= TOP NAVBAR ===================== -->
<!-- ================================================= -->

<div class="admin-topbar">

    <div class="nav-left">
        <span class="gradient-brand">Admin</span>
    </div>

    <div class="hamburger" onclick="toggleMenu()">☰</div>

    <div class="nav-menu" id="navMenu">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="view_users.php">Users</a>
        <a href="#">Tournaments</a>
        <a href="#" class="logout-mobile" onclick="openLogoutModal()">Log Out</a>
    </div>

</div>

<!-- ================================================= -->
<!-- ================= CONTENT ======================== -->
<!-- ================================================= -->

<div class="admin-content">

<!-- ===== NEW HEADER ===== -->
<div class="page-header">
    <h2>User Management</h2>
    <p>Manage registrations, approvals and permissions</p>
</div>


<!-- ===== NEW STATS ROW ===== -->
<div class="users-stats">

    <div class="mini-stat">
        <span>Total Users</span>
        <h3><?= $totalUsers ?></h3>
    </div>

    <div class="mini-stat pending">
        <span>Pending</span>
        <h3><?= $pendingCount ?></h3>
    </div>

    <div class="mini-stat approved">
        <span>Approved</span>
        <h3><?= $approvedCount ?></h3>
    </div>

    <div class="mini-stat banned">
        <span>Banned</span>
        <h3><?= $bannedCount ?></h3>
    </div>

</div>


<!-- ===== SEARCH + FILTER CARD ===== -->
<div class="controls-card">

<form method="GET" class="user-controls">

<input type="text"
name="search"
placeholder="Search user..."
value="<?= htmlspecialchars($search); ?>">

<select name="filter" onchange="this.form.submit()">
<option value="">All Users</option>
<option value="pending" <?= $filter=="pending"?"selected":""; ?>>Pending</option>
<option value="approved" <?= $filter=="approved"?"selected":""; ?>>Approved</option>
<option value="suspended" <?= $filter=="suspended"?"selected":""; ?>>Suspended</option>
<option value="banned" <?= $filter=="banned"?"selected":""; ?>>Banned</option>
</select>

</form>

</div>


<!-- ================= USERS TABLE ================= -->

<table class="admin-table">

<tr>
<th>ID</th>
<th>Username</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>
<td><?= $row['id']; ?></td>
<td><?= htmlspecialchars($row['username']); ?></td>
<td><?= htmlspecialchars($row['email']); ?></td>
<td><?= htmlspecialchars($row['role']); ?></td>
<td><?= htmlspecialchars($row['status']); ?></td>

<td>

<div class="action-dropdown">

    <button class="action-btn">Actions ▾</button>

    <div class="dropdown-menu">

        <button onclick="openEditModal(
            '<?= $row['id']; ?>',
            '<?= htmlspecialchars($row['username']); ?>',
            '<?= htmlspecialchars($row['email']); ?>',
            '<?= $row['role']; ?>',
            '<?= $row['status']; ?>'
        )">
            Edit User
        </button>

        <?php if($row['status']=="pending"): ?>
            <a href="update_user_status.php?id=<?= $row['id']; ?>&status=approved">Approve</a>
            <a href="update_user_status.php?id=<?= $row['id']; ?>&status=banned">Decline</a>
        <?php endif; ?>

    </div>

</div>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>
<!-- ===== MODERN EDIT USER MODAL ===== -->
<div id="editModal" class="modal">

    <div class="modal-content modern-modal">

        <div class="modal-header">
            <h3>Edit User</h3>
            <span class="modal-close" onclick="closeModal()">✕</span>
        </div>

        <form id="editForm" class="modal-form">

            <input type="hidden" id="edit_id">

            <div class="form-group">
                <label>Name</label>
                <input type="text" id="edit_name">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" id="edit_email">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Role</label>
                    <select id="edit_role">
                        <option value="athlete">Athlete</option>
                        <option value="organizer">Organizer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select id="edit_status">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="banned">Banned</option>
                    </select>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="save-btn" onclick="saveUser()">Save</button>
                <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
            </div>

        </form>

    </div>
</div>
<!-- ===== LOGOUT MODAL ===== -->
<div id="logoutModal" class="modal">

    <div class="modal-content modern-modal" style="max-width:360px;text-align:center">

        <h3 style="margin-bottom:10px">Confirm Logout</h3>

        <p style="color:#64748b;margin-bottom:20px">
            Are you sure you want to logout?
        </p>

        <div class="modal-actions">
            <button type="button" class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
            <button type="button" class="save-btn" onclick="doLogout()">Yes, Logout</button>
        </div>

    </div>

</div>
<script>
function toggleMenu() {
    document.getElementById("navMenu").classList.toggle("active");
}
function openEditModal(id,name,email,role,status){
    document.getElementById("editModal").style.display="flex";

    edit_id.value=id;
    edit_name.value=name;
    edit_email.value=email;
    edit_role.value=role;
    edit_status.value=status;
}

function closeModal(){
    document.getElementById("editModal").style.display="none";
}

function saveUser(){

    fetch("update_user_ajax.php",{
        method:"POST",
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:
        "id="+edit_id.value+
        "&username="+edit_name.value+
        "&email="+edit_email.value+
        "&role="+edit_role.value+
        "&status="+edit_status.value
    })
    .then(()=>location.reload());
}
/* ================= LOGOUT MODAL ================= */

function openLogoutModal(){
    document.getElementById("logoutModal").style.display = "flex";
}

function closeLogoutModal(){
    document.getElementById("logoutModal").style.display = "none";
}

function doLogout(){
    window.location.href = "logout.php";
}

/* click outside closes modal */
window.addEventListener("click", function(e){
    const modal = document.getElementById("logoutModal");
    if(e.target === modal){
        closeLogoutModal();
    }
});
</script>

</body>
</html>