<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
    <title>Admin Dashboard - Blood Donation</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="main-content">
            <div class="topbar">
                <h2>Blood Donation Management</h2>
                <div class="topbar-right">
                    <span>👤 <?= htmlspecialchars($_SESSION['first_name']) ?></span>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

            <div class="page-content">
                <p class="page-title">Admin Dashboard</p>

                <!-- Stat Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <h3><?= $total_users ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🩸</div>
                        <div class="stat-info">
                            <h3><?= $total_requests ?></h3>
                            <p>Blood Requests</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-info">
                            <h3><?= $pending ?></h3>
                            <p>Pending Requests</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-info">
                            <h3><?= $total_donations ?></h3>
                            <p>Donations Scheduled</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Requests Table -->
                <div class="card">
                    <div class="card-header">
                        Recent Blood Requests
                        <a href="admin_request.php" style="color:white; font-size:13px;">View All →</a>
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
                            <tbody>
                                <?php if (empty($recent)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No requests yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                            <td><?= htmlspecialchars($r['patient_name']) ?></td>
                                            <td><?= $r['blood_group'] ?></td>
                                            <td><?= $r['units'] ?></td>
                                            <td>
                                                <?php
                                                $cls = ['pending' => 'badge-warning', 'approved' => 'badge-success', 'rejected' => 'badge-danger'];
                                                echo "<span class='badge " . $cls[$r['status']] . "'>" . ucfirst($r['status']) . "</span>";
                                                ?>
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
</body>

</html>