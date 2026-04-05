<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_email']) && isset($_POST['new_password'])) {
    $email = trim($_POST['reset_email']);
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$new_password, $email]);
        header("Location: login.php?reset=1");
        exit;
    } else {
        $error = "Email not found in our records.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Forgot Password - Blood Donation Management</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>

    <div class="auth-wrapper">
        <div class="auth-left">
            <div class="blood-icon"><img src="../assets/img/droplet-solid.png" height="100" width="100"></div>
            <h2 style="color:white; font-size:20px;">Forgot Password?</h2>
            <p style="margin-top:10px;">Reset your password to drop back in and save lives.</p>
        </div>

        <div class="auth-right">
            <h2>Reset Password</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="forget_password.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="reset_email" placeholder="" required />
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="" required />
                </div>

                <button type="submit" class="btn-primary" style="width:100%; margin-top:8px;">Reset Password</button>

                <p class="text-center" style="margin-top:12px; font-size:13px; color:#888;">
                    Remembered it? <a href="login.php" style="color:#c0392b;">Login instead</a>
                </p>
            </form>
        </div>
    </div>

</body>

</html>