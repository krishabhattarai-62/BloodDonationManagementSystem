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
    <title>Admin Notifications</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body>
    <div class="dashboard">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/dashboard_topbar.php'; ?>
            <div class="page-content">
                <p class="page-title">Admin Notifications</p>
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <p style="color:#999; text-align:center;">No notifications yet.</p>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                                <div style="
                                padding:12px 15px;
                                border-bottom:1px solid #f0f0f0;
                                display:flex;
                                align-items:flex-start;
                                gap:12px;
                                background: <?= $n['is_read'] ? '#fff' : '#fff8f8' ?>;">
                                    <span class="notif-icon"><i class="fa-solid fa-bell"></i></span>
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