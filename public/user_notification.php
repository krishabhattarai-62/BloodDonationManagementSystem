<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require '../config/db.php';
require '../includes/notification.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$unread = getUnreadCount($pdo, $_SESSION['user_id']);
markAllRead($pdo, $_SESSION['user_id']);
$notifications = getNotifications($pdo, $_SESSION['user_id'], 50);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Notifications</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .notif-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            margin-right: 15px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #fff;
        }

        .notif-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #e53e3e;
            color: #fff;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 700;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid #fff;
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <?php include '../includes/donor_sidebar.php'; ?>
        <div class="main-content">

            <div class="topbar">
                <h2>Blood Donation Management</h2>
                <div class="topbar-right">
                    <a href="user_notification.php" class="notif-btn" title="Notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                            fill="#f5c000" stroke="#f5c000" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        <?php if ($unread > 0): ?>
                            <span class="notif-badge"><?= $unread > 9 ? '9+' : $unread ?></span>
                        <?php endif; ?>
                    </a>
                    <span>&#128100; <?= htmlspecialchars($_SESSION['first_name']) ?></span>
                    <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
                </div>
            </div>

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