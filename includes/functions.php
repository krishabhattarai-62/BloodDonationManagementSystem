<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cleanInput($data)
{
    return h(trim((string) $data));
}

function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password)
{
    return strlen($password) >= 8;
}

function generateCSRF()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRF($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirectTo($path)
{
    header("Location: $path");
    exit();
}

function requireLogin($path = 'login.php')
{
    if (!isset($_SESSION['user_id'])) {
        redirectTo($path);
    }
}

function requireDonor()
{
    if (!isDonor()) {
        redirectTo('admin_dashboard.php');
    }
}

function isDonor()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'donor';
}

function statusBadgeClass($status)
{
    return [
        'pending' => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-danger',
    ][$status] ?? 'badge-secondary';
}

function containsKeyword($text, array $keywords)
{
    foreach ($keywords as $keyword) {
        if (str_contains($text, $keyword)) {
            return true;
        }
    }

    return false;
}

function nullableValue($value)
{
    return ($value !== null && $value !== '') ? $value : null;
}

function checkSessionTimeout()
{
    if (
        isset($_SESSION['LAST_ACTIVITY']) &&
        (time() - $_SESSION['LAST_ACTIVITY'] > 900)
    ) {
        session_unset();
        session_destroy();
        redirectTo('../public/login.php');
    }

    $_SESSION['LAST_ACTIVITY'] = time();
}

/**
 * Logs in verified accounts only.
 */
function loginUser($pdo, $email, $password)
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        if (isset($user['email_verified']) && $user['email_verified'] == 0) {
            return 'unverified';
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['LAST_ACTIVITY'] = time();

        return true;
    }

    return false;
}

/**
 * Creates a donor pending email verification.
 */
function registerUserUnverified(
    $pdo,
    $first_name,
    $last_name,
    $contact_number,
    $address,
    $email,
    $gender,
    $age,
    $password,
    $otp,
    $location_name = null,
    $latitude = null,
    $longitude = null
) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $location_name = nullableValue($location_name);
    $latitude = nullableValue($latitude);
    $longitude = nullableValue($longitude);

    $stmt = $pdo->prepare("
        INSERT INTO users 
            (first_name, last_name, contact_number, address, email, gender, age, 
             password, otp, email_verified, role, location_name, latitude, longitude)
        VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'donor', ?, ?, ?)
    ");

    return $stmt->execute([
        $first_name,
        $last_name,
        $contact_number,
        $address,
        $email,
        $gender,
        $age,
        $hashed,
        $otp,
        $location_name,
        $latitude,
        $longitude
    ]);
}

/**
 * Creates a verified donor directly for older admin flows.
 */
function registerUser($pdo, $first_name, $last_name, $contact_number, $address, $email, $gender, $age, $password)
{
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users
            (first_name, last_name, contact_number, address, email, gender, age, password, role, email_verified)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, 'donor', 1)
    ");

    return $stmt->execute([
        $first_name,
        $last_name,
        $contact_number,
        $address,
        $email,
        $gender,
        $age,
        $hashedPassword,
    ]);
}

/**
 * Sends a verification OTP through the configured SMTP sandbox.
 */
function sendVerificationOTP($email, $first_name, $otp)
{
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
        $mail->isHTML(false);
        $mail->Subject = 'Email Verification Code';

        $mail->Body = "Hello $first_name, Your verification code is: $otp Enter this code to verify your account. - Blood Donation System";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}

function logoutUser()
{
    $_SESSION = [];
    session_unset();
    session_destroy();
}
