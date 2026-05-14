<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

require_once '../includes/notification.php';
$unread = getUnreadCount($pdo, $_SESSION['user_id']);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] === 'admin') {
    include 'admin_dashboard.php';
    exit;
}

$uid = $_SESSION['user_id'];

$my_requests = $pdo->prepare("SELECT COUNT(*) FROM blood_requests WHERE user_id=?");
$my_requests->execute([$uid]);
$req_count = $my_requests->fetchColumn();

$my_donations = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE user_id=?");
$my_donations->execute([$uid]);
$don_count = $my_donations->fetchColumn();

$recent = $pdo->prepare("SELECT * FROM blood_requests WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$recent->execute([$uid]);
$recent = $recent->fetchAll();

$user_info = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user_info->execute([$uid]);
$user_info = $user_info->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - Blood Donation</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/donor_sidebar.php'; ?>

        <div class="main-content">
            <div class="topbar">
                <h2>Blood Donation Management</h2>
                <div class="topbar-right">
                    <a href="user_notification.php"
                        style="position:relative; text-decoration:none; margin-right:15px; font-size:20px;">
                        <span class="material-icons"></span>🔔
                        <?php if ($unread > 0): ?>
                            <span style="
                                position:absolute; top:-6px; right:-8px;
                                background:red; color:white;
                                border-radius:50%; font-size:11px;
                                width:18px; height:18px;
                                display:flex; align-items:center; justify-content:center;
                                font-weight:bold;">
                                <?= $unread > 9 ? '9+' : $unread ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <span>&#128100; <?= htmlspecialchars($_SESSION['first_name']) ?></span>
                    <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
                </div>
            </div>

            <div class="page-content">
                <p class="page-title">
                    Welcome, <?= htmlspecialchars($user_info['first_name']) ?>!
                </p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">&#129656;</div>
                        <div class="stat-info">
                            <h3><?= $user_info['blood_group'] ?? '—' ?></h3>
                            <p>My Blood Group</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#128203;</div>
                        <div class="stat-info">
                            <h3><?= $req_count ?></h3>
                            <p>My Requests</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#128197;</div>
                        <div class="stat-info">
                            <h3><?= $don_count ?></h3>
                            <p>Donations Scheduled</p>
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap;">
                    <a href="donor_request.php"><button class="btn-primary">Request Blood</button></a>
                    <a href="schedule_donation.php"><button class="btn-secondary">Donate Blood</button></a>
                </div>

                <div class="card">
                    <div class="card-header">
                        My Recent Blood Requests
                        <a href="user_request.php" style="color:white; font-size:13px;">View All &#8594;</a>
                    </div>

                    <!-- SEARCH BAR -->
                    <div style="padding: 12px 16px; border-bottom: 1px solid #eee;">
                        <input type="text" id="searchInput"
                            placeholder="Search by patient, blood group, hospital, status..." style="
                                width: 350px;
                                padding: 8px 12px;
                                border: 1px solid #ddd;
                                border-radius: 6px;
                                font-size: 0.9em;
                                outline: none;
                            " />
                    </div>

                    <div class="card-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Patient Name</th>
                                    <th>Blood Group</th>
                                    <th>Units</th>
                                    <th>Hospital</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="requestTableBody">
                                <?php if (empty($recent)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            No requests yet.
                                            <a href="donor_request.php" style="color:var(--red-mid);">Make one!</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $cls = [
                                        'pending' => 'badge-warning',
                                        'approved' => 'badge-success',
                                        'rejected' => 'badge-danger'
                                    ];
                                    foreach ($recent as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($r['patient_name']) ?></td>
                                            <td><?= $r['blood_group'] ?></td>
                                            <td><?= $r['units'] ?></td>
                                            <td><?= htmlspecialchars($r['hospital']) ?></td>
                                            <td>
                                                <span class="badge <?= $cls[$r['status']] ?>">
                                                    <?= ucfirst($r['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        let searchTimer = null;

        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimer);

            const q = this.value.trim();
            const tbody = document.getElementById('requestTableBody');

            searchTimer = setTimeout(() => {

                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Searching...</td></tr>';

                fetch('/BloodDonationManagementSystem/public/search_my_requests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'q=' + encodeURIComponent(q)
                })
                    .then(res => res.text())
                    .then(html => {
                        tbody.innerHTML = html;
                    })
                    .catch(() => {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Error fetching results.</td></tr>';
                    });

            }, 300);
        });
    </script>

</body>

</html>