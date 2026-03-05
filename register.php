<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="register-modern">

<div class="modern-register">

    <!-- LEFT : FORM -->
    <div class="modern-left">


        <h2>Create Account</h2>

        <form action="register_process.php" method="POST">

            <div class="input-modern">
                <label>Full Name</label>
                <input type="text" name="username" required>
            </div>

            <div class="input-modern">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="input-modern">
                <label>E-mail</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-modern">
                <label>Role</label>
                <select name="role" required>
                    <option value="athlete">Athlete</option>
                    <option value="organizer">Organizer</option>
                </select>
            </div>

            <div class="terms">
                <input type="checkbox" required>
                <span>By clicking sign up, you agree to our Terms of Services and Privacy Policy</span>
            </div>

            <button class="signup-btn">Sign up</button>

        </form>
    </div>


    <!-- RIGHT : GET STARTED PANEL -->
    <div class="modern-right">
        <h1>Get Started</h1>
        <p>Already have an account?</p>
        <a href="login.php" class="login-btn">Log in</a>
    </div>

</div>

</body>
</html>