<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

// Guard: must come from forget_password page
if (empty($_SESSION['reset_email'])) {
    header("Location: forget_password.php");
    exit;
}

require '../config/db.php';

$error = '';
$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = trim($_POST['otp']);

    $stmt = $pdo->prepare("SELECT otp FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Something went wrong. Please try again.";
    } elseif ((int) $enteredOtp !== (int) $user['otp']) {
        $error = "Invalid OTP. Please try again.";
    } else {
        // OTP correct — mark verified, go to reset page
        $_SESSION['otp_verified'] = true;

        // Clear OTP from DB so it can't be reused
        $pdo->prepare("UPDATE users SET otp = NULL WHERE email = ?")
            ->execute([$email]);

        header("Location: reset_password.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Verify OTP - Blood Donation Management</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-left">
            <div class="blood-icon"><img src="../assets/img/droplet-solid.png" height="100" width="100"></div>
            <h2 style="color:white;font-size:20px;">Check Your Email</h2>
            <p style="margin-top:10px;">Enter the 6-digit OTP we sent to your inbox.</p>
        </div>
        <div class="auth-right">
            <h2>Enter OTP</h2>
            <p style="font-size:13px;color:#555;margin-bottom:16px;">
                Sent to <strong>
                    <?= htmlspecialchars($email) ?>
                </strong>
            </p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="verify_otp.php" method="POST">
                <div class="form-group">
                    <label>OTP Code</label>
                    <input type="text" name="otp" maxlength="6" placeholder="______"
                        style="letter-spacing:8px;font-size:24px;text-align:center;" autocomplete="one-time-code"
                        required />
                </div>
                <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">Verify OTP</button>
                <p class="text-center" style="margin-top:12px;font-size:13px;color:#888;">
                    Didn't get it? <a href="forget_password.php" style="color:#c0392b;">Resend OTP</a>
                </p>
            </form>
        </div>
    </div>
</body>

</html>