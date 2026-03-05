<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== "admin") {
    die("🚫 Access Denied — Admin Only");
}

include "config.php";

/* ================= INITIAL DATA ================= */

$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")
    ->fetch_assoc()['count'];

$totalAthletes = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='athlete'")
    ->fetch_assoc()['count'];

$totalOrganizers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='organizer'")
    ->fetch_assoc()['count'];


/* ===== RECENT USERS ===== */

$recentUsers = $conn->query("
    SELECT username, email 
    FROM users 
    WHERE role != 'admin'
    ORDER BY id DESC 
    LIMIT 5
");


/* ===== NOTIFICATIONS ===== */

$notifications = $conn->query("
    SELECT * FROM notifications 
    ORDER BY created_at DESC 
    LIMIT 10
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ================= TOP NAVBAR ================= -->

<div class="admin-topbar">

    <div class="nav-left">
        <span class="gradient-brand">Admin</span>
    </div>

    <div class="hamburger" onclick="toggleMenu()">☰</div>

    <div class="nav-menu" id="navMenu">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="view_users.php">Users</a>
        <a href="#">Tournaments</a>

        <!-- ✅ Logout with Confirmation -->
        <a href="#" class="logout-mobile" onclick="openLogoutModal()">
        Log Out
        </a>

    </div>

</div>

<!-- ================= DASHBOARD ================= -->

<div class="admin-content">

<h1>Dashboard Overview</h1>

<div class="stats-grid">

    <div class="stat-card">
        <h3>TOTAL USERS</h3>
        <p id="totalUsers"><?php echo $totalUsers; ?></p>
    </div>

    <div class="stat-card">
        <h3>ATHLETES</h3>
        <p id="totalAthletes"><?php echo $totalAthletes; ?></p>
    </div>

    <div class="stat-card">
        <h3>ORGANIZERS</h3>
        <p id="totalOrganizers"><?php echo $totalOrganizers; ?></p>
    </div>

</div>

<!-- ================= NOTIFICATIONS ================= -->

<div class="dashboard-section">

    <h2>Admin Notifications</h2>

    <div class="notification-box" id="notificationBox">

        <?php if ($notifications->num_rows > 0): ?>
            <?php while($row = $notifications->fetch_assoc()): ?>
                <p>
                    <?php echo $row['message']; ?>
                    <br>
                    <small><?php echo $row['created_at']; ?></small>
                </p>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No notifications yet.</p>
        <?php endif; ?>

    </div>
</div>

<!-- ================= RECENT USERS ================= -->

<div class="dashboard-section">

    <h2>Recent Users</h2>

    <table class="admin-table">
        <tr>
            <th>Username</th>
            <th>Email</th>
        </tr>

        <tbody id="recentUsersTable">

        <?php while($row = $recentUsers->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['username']; ?></td>
            <td><?php echo $row['email']; ?></td>
        </tr>
        <?php endwhile; ?>

        </tbody>

    </table>

</div>

</div>
<!-- ================= LOGOUT MODAL ================= -->

<div id="logoutModal" class="modal">

    <div class="modal-content modern-modal" style="max-width:360px;text-align:center">

        <h3 style="margin-bottom:10px">Confirm Logout</h3>
        <p style="color:#64748b;margin-bottom:20px">
            Are you sure you want to logout?
        </p>

        <div class="modal-actions">
            <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
            <button class="save-btn" onclick="doLogout()">Yes, Logout</button>
        </div>

    </div>

</div>

<!-- ================= SCRIPT ================= -->

<script>

function toggleMenu() {
    document.getElementById("navMenu")
        .classList.toggle("active");
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

/* close when clicking outside */
window.onclick = function(e){
    const modal = document.getElementById("logoutModal");
    if(e.target === modal){
        closeLogoutModal();
    }
}

/* ================= AUTO UPDATE ================= */

function fetchDashboardData(){

    fetch("get_dashboard_data.php")
    .then(response => response.json())
    .then(data => {

        document.getElementById("totalUsers").innerText = data.totalUsers;
        document.getElementById("totalAthletes").innerText = data.totalAthletes;
        document.getElementById("totalOrganizers").innerText = data.totalOrganizers;

        let userTable = document.getElementById("recentUsersTable");
        userTable.innerHTML = "";

        data.recentUsers.forEach(user => {
            userTable.innerHTML += `
                <tr>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                </tr>
            `;
        });

        let notifBox = document.getElementById("notificationBox");
        notifBox.innerHTML = "";

        if(data.notifications.length > 0){

            data.notifications.forEach(notif => {
                notifBox.innerHTML += `
                    <p>
                        ${notif.message}
                        <br>
                        <small>${notif.created_at}</small>
                    </p>
                `;
            });

        } else {
            notifBox.innerHTML = "<p>No notifications yet.</p>";
        }

    });
}

/* AUTO REFRESH */

fetchDashboardData();
setInterval(fetchDashboardData, 5000);

</script>

</body>
</html>