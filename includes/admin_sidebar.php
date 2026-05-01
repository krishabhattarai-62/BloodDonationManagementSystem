<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-logo">
        <span style="font-size:26px; line-height:1;">&#129656;</span>
        <span>Blood<br>Donation</span>
    </div>
    <nav>
        <ul>
            <li>
                <a href="admin_dashboard.php"
                    class="<?= ($current_page === 'admin_dashboard.php' || $current_page === 'dashboard.php') ? 'active' : '' ?>">
                    <span class="icon">&#127968;</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="admin_user.php" class="<?= $current_page === 'admin_user.php' ? 'active' : '' ?>">
                    <span class="icon">&#128101;</span>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="admin_request.php" class="<?= $current_page === 'admin_request.php' ? 'active' : '' ?>">
                    <span class="icon">&#128203;</span>
                    <span>Requests</span>
                </a>
            </li>
            <li>
                <a href="admin_donation.php" class="<?= $current_page === 'admin_donation.php' ? 'active' : '' ?>">
                    <span class="icon">&#128197;</span>
                    <span>Donations</span>
                </a>
            </li>
            <li>
                <a href="admin_reserve.php" class="<?= $current_page === 'admin_reserve.php' ? 'active' : '' ?>">
                    <span class="icon">&#127981;</span>
                    <span>Blood Reserve</span>
                </a>
            </li>
        </ul>
    </nav>
</div>