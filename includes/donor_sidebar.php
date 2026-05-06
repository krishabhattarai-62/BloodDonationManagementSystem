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
                <a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                    <span class="icon">&#127968;</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="donor_profile.php" class="<?= $current_page === 'donor_profile.php' ? 'active' : '' ?>">
                    <span class="icon">&#128100;</span>
                    <span>My Profile</span>
                </a>
            </li>
            <li>
                <a href="schedule_donation.php"
                    class="<?= $current_page === 'schedule_donation.php' ? 'active' : '' ?>">
                    <span class="icon">&#128197;</span>
                    <span>Donate Blood</span>
                </a>
            </li>
            <li>
                <a href="donor_request.php" class="<?= $current_page === 'donor_request.php' ? 'active' : '' ?>">
                    <span class="icon">&#129656;</span>
                    <span>Request Blood</span>
                </a>
            </li>
            <li>
                <a href="user_request.php" class="<?= $current_page === 'user_request.php' ? 'active' : '' ?>">
                    <span class="icon">&#128203;</span>
                    <span>My Requests</span>
                </a>
            </li>
        </ul>
    </nav>
</div>