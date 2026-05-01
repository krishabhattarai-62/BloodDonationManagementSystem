<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blood Donation System</title>
    <link rel="stylesheet" href="/BloodDonationManagementSystem/assets/css/style.css">
</head>

<body>

    <header class="site-header">
        <a href="/BloodDonationManagementSystem/index.php" class="site-header-brand">
            <span>&#128987;</span>
            Blood Donation System
        </a>
        <nav class="site-header-nav">
            <a href="/BloodDonationManagementSystem/index.php">Home</a>
            <a href="/BloodDonationManagementSystem/public/login.php">Login</a>
            <a href="/BloodDonationManagementSystem/public/signup.php" class="nav-cta">Register</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/BloodDonationManagementSystem/public/dashboard.php">Dashboard</a>
                <a href="/BloodDonationManagementSystem/public/logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </header>