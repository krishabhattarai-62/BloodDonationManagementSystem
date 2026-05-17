<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

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
        $_SESSION['otp_verified'] = true;

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
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-left">
                <div class="blood-icon"><img src="../assets/img/droplet-solid.png" height="100" width="100"></div>
                <h1>Check Your<br>Email</h1>
                <p>Enter the 6-digit OTP we sent to your inbox.</p>
            </div>

            <div class="auth-right">

                <div>
                    <h2>Enter OTP</h2>
                    <p style="font-size:13px; color:#555; margin-bottom:20px;">
                        Sent to <strong><?= htmlspecialchars($email) ?></strong>
                    </p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form action="verify_otp.php" method="POST">
                        <div class="auth-field">
                            <label>OTP Code</label>
                            <input type="text" name="otp" maxlength="6" placeholder="— — — — — —"
                                style="letter-spacing: 10px; font-size: 22px; text-align: center;"
                                autocomplete="one-time-code" required />
                        </div>

                        <button type="submit" class="btn-primary" style="width:100%; margin-top:8px;">Verify
                            OTP</button>
                    </form>
                </div>

                <div>
                    <hr class="auth-divider">
                    <div class="auth-bottom">
                        <p>Didn't receive the code?</p>
                        <a href="forget_password.php"><button class="btn-secondary">Resend OTP</button></a>
                        <a href="login.php" class="auth-back-home">Back to Login</a>
                    </div>
                    <p class="auth-footer">&copy; <?php echo date("Y"); ?> Blood Donation System</p>
                </div>

            </div>

        </div>
    </div>
</body>

</html>
