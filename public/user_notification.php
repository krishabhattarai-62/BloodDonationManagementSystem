<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require '../config/db.php';
require '../includes/notification.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

markAllRead($pdo, $_SESSION['user_id']);
$notifications = getNotifications($pdo, $_SESSION['user_id'], 50);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Notifications</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body>
    <div class="dashboard">
        <?php include '../includes/donor_sidebar.php'; ?>
        <div class="main-content">

            <?php include '../includes/dashboard_topbar.php'; ?>

            <div class="page-content">
                <p class="page-title">Your Notifications</p>
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <p style="color:#999; text-align:center;">No notifications yet.</p>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                                <div
                                    style="padding:12px 15px; border-bottom:1px solid #f0f0f0; display:flex; align-items:flex-start; gap:12px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="#f5c000" stroke="#f5c000" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" style="margin-top:2px; flex-shrink:0;">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                    </svg>
                                    <div>
                                        <p style="margin:0; font-size:14px; color:#333;">
                                            <?= htmlspecialchars($n['message']) ?>
                                        </p>
                                        <small style="color:#999;">
                                            <?= date('M d, Y h:i A', strtotime($n['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>