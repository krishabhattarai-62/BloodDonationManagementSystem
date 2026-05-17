<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

if (empty($_SESSION['verify_email']) || empty($_SESSION['verify_flow'])) {
    header("Location: signup.php");
    exit();
}

$email = $_SESSION['verify_email'];
$first_name = $_SESSION['verify_first_name'] ?? 'there';
$flow = $_SESSION['verify_flow'];
$error = '';
$resent = false;

if (isset($_GET['resend'])) {
    $otp = rand(100000, 999999);
    $pdo->prepare("UPDATE users SET otp = ? WHERE email = ?")
        ->execute([$otp, $email]);

    if (sendVerificationOTP($email, $first_name, $otp)) {
        $resent = true;
    } else {
        $error = "Failed to resend the code. Please try again.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {

    $enteredOtp = trim($_POST['otp']);

    $stmt = $pdo->prepare("SELECT otp, email_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "User not found. Please sign up again.";
    } elseif (empty($enteredOtp) || strlen($enteredOtp) !== 6 || !ctype_digit($enteredOtp)) {
        $error = "Please enter a valid 6-digit code.";
    } elseif ((int) $enteredOtp !== (int) $user['otp']) {
        $error = "Incorrect code. Please check your email and try again.";
    } else {
        if ($flow === 'reset_password') {
            $pdo->prepare("UPDATE users SET email_verified = 1, otp = NULL WHERE email = ?")
                ->execute([$email]);

            unset($_SESSION['verify_email'], $_SESSION['verify_first_name'], $_SESSION['verify_flow']);

            $_SESSION['reset_email'] = $email;
            $_SESSION['otp_verified'] = true;

            header("Location: reset_password.php");
            exit();

        } else {
            $pdo->prepare("UPDATE users SET email_verified = 1, otp = NULL WHERE email = ?")
                ->execute([$email]);

            unset($_SESSION['verify_email'], $_SESSION['verify_first_name'], $_SESSION['verify_flow']);

            header("Location: login.php?verified=1");
            exit();
        }
    }
}

$backLink = ($flow === 'reset_password') ? 'forget_password.php' : 'signup.php';
$backLabel = ($flow === 'reset_password') ? 'Back to Forgot Password' : 'Back to Sign Up';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify Email - Blood Donation System</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-left">
                <div class="blood-icon">
                    <img src="../assets/img/droplet-solid.png" alt="Blood Drop" />
                </div>
                <h2>Check Your Email</h2>
                <p>We sent a 6-digit code to your inbox. Enter it below to continue.</p>
            </div>

            <div class="auth-right">
                <h2><?= $flow === 'reset_password' ? 'Verify to Reset Password' : 'Verify Your Email' ?></h2>

                <p style="font-size:13px; color:#555; margin-bottom:20px;">
                    Code sent to <strong><?= htmlspecialchars($email) ?></strong>
                </p>

                <?php if ($resent): ?>
                    <div class="alert alert-success">A new code has been sent to your email.</div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="verify_email.php">
                    <div class="auth-field">
                        <label>6-Digit Code</label>
                        <input type="text" name="otp" maxlength="6" placeholder="------" autocomplete="one-time-code"
                            inputmode="numeric" pattern="[0-9]{6}"
                            style="letter-spacing:10px; font-size:26px; text-align:center; font-weight:700;" autofocus
                            required />
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%; margin-top:6px;">
                        <?= $flow === 'reset_password' ? 'Verify &amp; Continue' : 'Verify &amp; Activate Account' ?>
                    </button>
                </form>

                <p style="text-align:center; margin-top:18px; font-size:13px; color:#888;">
                    Didn't receive it?
                    <a href="verify_email.php?resend=1" style="color:#dc2626; font-weight:600; text-decoration:none;">
                        Resend Code
                    </a>
                </p>

                <p style="text-align:center; margin-top:10px; font-size:12px;">
                    <a href="<?= $backLink ?>" style="color:#9ca3af; text-decoration:none;">
                        <i class="fa-solid fa-arrow-left"></i> <?= $backLabel ?>
                    </a>
                </p>

                <p class="auth-footer">&copy; <?= date("Y") ?> Blood Donation System</p>
            </div>

        </div>
    </div>
</body>

</html>