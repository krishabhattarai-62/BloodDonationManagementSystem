<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

require_once '../includes/functions.php';
requireLogin();

$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='donor'")->fetchColumn();
$total_requests = $pdo->query("SELECT COUNT(*) FROM blood_requests")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status='pending'")->fetchColumn();
$total_donations = $pdo->query("SELECT COUNT(*) FROM donations")->fetchColumn();

$recent = $pdo->query("
    SELECT br.*, u.first_name, u.last_name
    FROM blood_requests br
    JOIN users u ON br.user_id = u.id
    ORDER BY br.created_at DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - Blood Donation</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="main-content">
            <?php include '../includes/dashboard_topbar.php'; ?>

            <div class="page-content">
                <p class="page-title">Admin Dashboard</p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                        <div class="stat-info">
                            <h3><?= $total_users ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-droplet"></i></div>
                        <div class="stat-info">
                            <h3><?= $total_requests ?></h3>
                            <p>Blood Requests</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                        <div class="stat-info">
                            <h3><?= $pending ?></h3>
                            <p>Pending Requests</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-calendar"></i></div>
                        <div class="stat-info">
                            <h3><?= $total_donations ?></h3>
                            <p>Donations Scheduled</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-search-panel">
                    <div class="dashboard-search-panel-label">Search</div>
                    <div class="dashboard-search-wrap">
                        <i class="fa-solid fa-magnifying-glass search-field-icon"></i>
                        <input type="text" id="searchInput" class="dashboard-search-input"
                            placeholder="Search by name, blood group, status..." />
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        Recent Blood Requests
                        <a href="admin_request.php">View All <i class="fa-solid fa-arrow-right"></i></a>
                    </div>

                    <div class="card-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Requested By</th>
                                    <th>Patient Name</th>
                                    <th>Blood Group</th>
                                    <th>Units</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="requestTableBody">
                                <?php if (empty($recent)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No requests yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    foreach ($recent as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                            <td><?= htmlspecialchars($r['patient_name']) ?></td>
                                            <td><?= $r['blood_group'] ?></td>
                                            <td><?= $r['units'] ?></td>
                                            <td>
                                                <span class="badge <?= statusBadgeClass($r['status']) ?>">
                                                    <?= ucfirst(h($r['status'])) ?>
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

            // Wait briefly so fast typing does not spam the server.
            searchTimer = setTimeout(() => {

                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Searching...</td></tr>';

                fetch('/BloodDonationManagementSystem/public/search_requests.php', {
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
