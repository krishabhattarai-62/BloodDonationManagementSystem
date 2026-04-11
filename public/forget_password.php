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

        // Save OTP to DB
        $stmt = $pdo->prepare("UPDATE users SET otp = ? WHERE email = ?");
        $stmt->execute([$otp, $email]);

        // Store email in session to carry across pages
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

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="forget_password.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="reset_email" placeholder="" required />
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