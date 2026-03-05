<!DOCTYPE html>
<html>
<head>
    <title>TournaMeet Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="register-modern"> <!-- reuse same background -->

<div class="modern-register">

    <!-- LEFT SIDE (FORM) -->
    <div class="modern-left">


        <h2>Login to your account</h2>

        <form action="login_process.php" method="POST">

            <div class="input-modern">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-modern">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="loginPassword" required>
                    <span class="toggle-password" onclick="togglePassword('loginPassword', this)">👁</span>
                </div>
            </div>

            <button type="submit" class="signup-btn">Login</button>

            <div class="link">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

        </form>
    </div>


    <!-- RIGHT SIDE (ORANGE PANEL) -->
    <div class="modern-right">
        <h1>Welcome back!</h1>
        <p>New here?</p>
        <a href="register.php" class="login-btn">Create Account</a>
    </div>

</div>


<script>
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "◡";
    } else {
        input.type = "password";
        icon.textContent = "👁";
    }
}
</script>

</body>
</html>