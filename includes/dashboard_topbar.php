<?php
/**
 * Shared dashboard topbar: notifications, profile link, logout.
 * Expects active session with user_id and role.
 */
if (!isset($_SESSION['user_id'])) {
    return;
}

if (!isset($pdo)) {
    require_once __DIR__ . '/../config/db.php';
}

require_once __DIR__ . '/notification.php';

$unread = getUnreadCount($pdo, $_SESSION['user_id']);
$isAdmin = (($_SESSION['role'] ?? '') === 'admin');
$notifUrl = $isAdmin ? 'admin_notification.php' : 'user_notification.php';
$profileUrl = $isAdmin ? 'admin_dashboard.php' : 'donor_profile.php';
$profileTitle = $isAdmin ? 'Admin Dashboard' : 'My Profile';
?>
<div class="topbar">
    <h2>Blood Donation Management</h2>
    <div class="topbar-right">
        <a href="<?= htmlspecialchars($notifUrl) ?>" class="topbar-bell" title="Notifications">
            <i class="fa-solid fa-bell"></i>
            <?php if ($unread > 0): ?>
                <span class="topbar-bell-badge"><?= $unread > 9 ? '9+' : $unread ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= htmlspecialchars($profileUrl) ?>" class="topbar-user topbar-profile-link"
            title="<?= htmlspecialchars($profileTitle) ?>">
            <i class="fa-solid fa-user"></i>
            <?= htmlspecialchars($_SESSION['first_name']) ?>
        </a>
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
    </div>
</div>
