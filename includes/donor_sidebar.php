<div class="sidebar">
    <div class="sidebar-logo">
        <span style="font-size:28px;">🩸</span>
        <span>Blood<br />Donation</span>
    </div>
    <nav>
        <ul>
            <li><a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>"><span
                        class="icon"><i class="fa-solid fa-house"></i></span><span>Dashboard</span></a></li>
            <li><a href="donor_profile.php" class="<?= $current_page === 'donor_profile.php' ? 'active' : '' ?>"><span
                        class="icon">👤</span><span>My Profile</span></a></li>
            <li><a href="schedule_donation.php"
                    class="<?= $current_page === 'schedule_donation.php' ? 'active' : '' ?>"><span
                        class="icon">📅</span><span>Donate Blood</span></a></li>
            <li><a href="donor_request.php" class="<?= $current_page === 'donor_request.php' ? 'active' : '' ?>"><span
                        class="icon">🩸</span><span>Request Blood</span></a></li>
            <li><a href="user_request.php" class="<?= $current_page === 'user_request.php' ? 'active' : '' ?>"><span
                        class="icon">📋</span><span>My Requests</span></a></li>
            <li><a href="logout.php"><span class="icon">🚪</span><span>Logout</span></a></li>
        </ul>
    </nav>
</div>