<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

// Guard: must have verified OTP
if (empty($_SESSION['reset_email']) || empty($_SESSION['otp_verified'])) {
    header("Location: forget_password.php");
    exit;
}

require '../config/db.php';

$error = '';
$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE email = ?")
            ->execute([$hashed, $email]);

        // Clean up session
        unset($_SESSION['reset_email'], $_SESSION['otp_verified']);

        header("Location: login.php?reset=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Reset Password - Blood Donation Management</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-left">
            <div class="blood-icon"><img src="../assets/img/droplet-solid.png" height="100" width="100"></div>
            <p style="margin-top:10px;">Set a new password</p>
        </div>
        <div class="auth-right">
            <h2>New Password</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="reset_password.php" method="POST">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="At least 8 characters" required />
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Repeat new password" required />
                </div>
                <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">Update Password</button>
            </form>
        </div>
    </div>
</body>

</html>