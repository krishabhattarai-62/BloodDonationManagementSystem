<?php
require_once '../includes/functions.php';

checkSessionTimeout();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$first_name = $_SESSION['first_name'];

if ($role === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
} else {
    header("Location: donor_dashboard.php");
    exit();
}
?>