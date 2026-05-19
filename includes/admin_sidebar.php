<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-logo">
        <i class="fa-solid fa-droplet"></i>
        <span>Blood<br>Donation</span>
    </div>
    <nav>
        <ul>
            <li>
                <a href="admin_dashboard.php"
                    class="<?= ($current_page === 'admin_dashboard.php' || $current_page === 'dashboard.php') ? 'active' : '' ?>">
                    <span class="icon"><i class="fa-solid fa-house"></i></span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="admin_user.php" class="<?= $current_page === 'admin_user.php' ? 'active' : '' ?>">
                    <span class="icon"><i class="fa-solid fa-users"></i></span>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="admin_request.php" class="<?= $current_page === 'admin_request.php' ? 'active' : '' ?>">
                    <span class="icon"><i class="fa-solid fa-clipboard-list"></i></span>
                    <span>Requests</span>
                </a>
            </li>
            <li>
                <a href="admin_donation.php" class="<?= $current_page === 'admin_donation.php' ? 'active' : '' ?>">
                    <span class="icon"><i class="fa-solid fa-calendar"></i></span>
                    <span>Donations</span>
                </a>
            </li>
            <li>
                <a href="admin_reserve.php" class="<?= $current_page === 'admin_reserve.php' ? 'active' : '' ?>">
                    <span class="icon"><i class="fa-solid fa-hospital"></i></span>
                    <span>Blood Reserve</span>
                </a>
            </li>
        </ul>
    </nav>
</div>