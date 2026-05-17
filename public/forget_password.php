<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require '../config/db.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['reset_email']);

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() === 0) {
        $error = "Email not found in our records.";
    } else {
        $otp = rand(100000, 999999);

        $stmt = $pdo->prepare("UPDATE users SET otp = ? WHERE email = ?");
        $stmt->execute([$otp, $email]);

        $_SESSION['reset_email'] = $email;

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = 'c5bf0a512d5b85';
            $mail->Password = '1bdee822f50964';
            $mail->Port = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $mail->setFrom('noreply@bloodsystem.com', 'Blood Donation System');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset OTP';
            $mail->Body = " Your Otp Code is : {$otp}";

            $mail->send();
            header("Location: verify_otp.php");
            exit;

        } catch (Exception $e) {
            $error = "Failed to send email: {$mail->ErrorInfo}";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Forgot Password - Blood Donation Management</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-left">
                <div class="blood-icon"><img src="../assets/img/droplet-solid.png" height="100" width="100"></div>
                <h2>Forgot Password?</h2>
                <p>Reset your password.</p>
            </div>

            <div class="auth-right">

                <div>
                    <h2>Reset Password</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form action="forget_password.php" method="POST">

                        <div class="auth-field">
                            <label>Email Address</label>
                            <input type="email" name="reset_email" required />
                        </div>

                        <button type="submit" class="btn-primary" style="width:100%; margin-top:8px;">Send OTP</button>

                    </form>
                </div>

                <div>
                    <hr class="auth-divider">
                    <div class="auth-bottom">
                        <p>Remembered it?</p>
                        <a href="login.php"><button class="btn-secondary">Login instead</button></a>
                        <a href="../index.php" class="auth-back-home">Back to Home</a>
                    </div>
                    <p class="auth-footer">&copy; <?php echo date("Y"); ?> Blood Donation System</p>
                </div>

            </div>

        </div>
    </div>

</body>

</html>