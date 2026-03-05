<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Reset Password</h2>

    <form action="forgot_password_process.php" method="POST">

        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>New Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Update Password</button>

        <div class="link">
            <a href="login.php">Back to Login</a>
        </div>

    </form>
</div>

</body>
</html>
