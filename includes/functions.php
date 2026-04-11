<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cleanInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
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

function requireDonor()
{
    if (!isDonor()) {
        header("Location: admin_dashboard.php");
        exit();
    }
}

function isDonor()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'donor';
}


function checkSessionTimeout()
{
    if (
        isset($_SESSION['LAST_ACTIVITY']) &&
        (time() - $_SESSION['LAST_ACTIVITY'] > 900)
    ) {
        session_unset();
        session_destroy();
        header("Location: ../public/login.php");
        exit();
    }

    $_SESSION['LAST_ACTIVITY'] = time();
}

function loginUser($pdo, $email, $password)
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['LAST_ACTIVITY'] = time();

        return true;
    }
    return false;
}

function registerUser($pdo, $first_name, $last_name, $contact_number, $address, $email, $gender, $age, $password)
{
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $role = 'donor';

    $stmt = $pdo->prepare(
        "INSERT INTO users (first_name, last_name, contact_number, address, email, gender, age, password, role)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    return $stmt->execute([$first_name, $last_name, $contact_number, $address, $email, $gender, $age, $hashedPassword, $role]);
}

function logoutUser()
{
    $_SESSION = [];
    session_unset();
    session_destroy();
}