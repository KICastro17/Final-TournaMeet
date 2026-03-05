<?php
include "config.php";

$email = $_POST['email'];
$newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "UPDATE users SET password=? WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $newPassword, $email);
$stmt->execute();

$success = $stmt->affected_rows > 0;

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<?php if ($success): ?>
    <h2>✅ Password Updated!</h2>
    <div class="link">
        <a href="login.php">Go to Login</a>
    </div>
<?php else: ?>
    <h2 style="color:red;">❌ Email not found!</h2>
    <div class="link">
        <a href="forgot_password.php">Try Again</a>
    </div>
<?php endif; ?>

</div>

</body>
</html>
