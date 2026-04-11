<?php
require_once '../includes/functions.php';
logoutUser();
if (isset($_SESSION['csrf_token'])) {
    unset($_SESSION['csrf_token']);
}
header("Location: login.php");
exit();