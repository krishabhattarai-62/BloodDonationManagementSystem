<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

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
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/donor_sidebar.php'; ?>

        <div class="main-content">
            <?php include '../includes/dashboard_topbar.php'; ?>

            <div class="page-content">
                <p class="page-title">
                    Welcome, <?= htmlspecialchars($user_info['first_name']) ?>!
                </p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-droplet"></i></div>
                        <div class="stat-info">
                            <h3><?= $user_info['blood_group'] ?? '—' ?></h3>
                            <p>My Blood Group</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-clipboard-list"></i></div>
                        <div class="stat-info">
                            <h3><?= $req_count ?></h3>
                            <p>My Requests</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-calendar"></i></div>
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

                <div class="dashboard-search-panel">
                    <div class="dashboard-search-panel-label">Search</div>
                    <div class="dashboard-search-wrap">
                        <i class="fa-solid fa-magnifying-glass search-field-icon"></i>
                        <input type="text" id="searchInput" class="dashboard-search-input"
                            placeholder="Search by patient, blood group, hospital, status..." />
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        My Recent Blood Requests
                        <a href="user_request.php" style="color:white; font-size:13px;">View All <i class="fa-solid fa-arrow-right"></i></a>
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