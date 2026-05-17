<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validateCSRF($_POST['csrf_token'])) {
        $error = "Invalid request!";
    } else {

        $email = cleanInput($_POST['email']);
        $password = cleanInput($_POST['password']);

        if (empty($email) || empty($password)) {
            $error = "Email and password are required!";
        } elseif (!validateEmail($email)) {
            $error = "Invalid email format!";
        } else {
            if (loginUser($pdo, $email, $password)) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Blood Donation System</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body class="auth-page-body">

    <header class="site-header">
        <a href="../index.php" class="site-header-brand">
            <img src="../assets/img/droplet-solid.png" alt="Blood Drop" width="20" height="20"
                style="vertical-align:middle; margin-right:6px; filter: brightness(0) invert(1);">
            Blood Donation System
        </a>
        <nav class="site-header-nav">
            <a href="../index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="signup.php" class="nav-cta">Register</a>
        </nav>
    </header>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-left">
                <div class="blood-icon">
                    <img src="../assets/img/droplet-solid.png" alt="Blood Drop" height="100" width="100" />
                </div>
                <h1>Welcome</h1>
                <p>To Blood Management System</p>
            </div>

            <div class="auth-right">
                <h2>Login</h2>
                <p class="auth-subtitle">Sign in to manage donations and blood requests</p>

                <?php if ($error): ?>
                    <script>document.addEventListener('DOMContentLoaded', () => showToast('<?= htmlspecialchars($error) ?>', 'error'));</script>
                <?php endif; ?>

                <form method="POST" class="auth-form">

                    <div class="auth-field">
                        <label>Email</label>
                        <input type="text" name="email" value="<?= htmlspecialchars($email) ?>"
                            placeholder="you@example.com" autocomplete="email" />
                    </div>

                    <div class="auth-field">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter your password"
                            autocomplete="current-password" />
                    </div>

                    <a href="forget_password.php" class="auth-forgot">Forgot Password?</a>

                    <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>" />

                    <div class="auth-form-actions auth-form-actions--center auth-form-actions--full">
                        <button type="submit" class="btn-primary">Login</button>
                    </div>

                </form>

                <hr class="auth-divider" />

                <div class="auth-bottom">
                    <p>Don&apos;t have an account?</p>
                    <div class="auth-form-actions auth-form-actions--center auth-form-actions--full">
                        <a href="signup.php"><button type="button" class="btn-primary">Signup</button></a>
                    </div>
                    <div class="auth-form-actions auth-form-actions--center auth-form-actions--full">
                        <a href="../index.php"><button type="button" class="btn-back"><i
                                    class="fa-solid fa-arrow-left"></i> Back to Home</button></a>
                    </div>
                </div>

                <p class="auth-footer">&copy; <?= date("Y") ?> Blood Donation System</p>
            </div>

        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

</body>

</html>
