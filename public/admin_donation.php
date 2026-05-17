<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';
require_once '../includes/ensure_donations_schema.php';
ensureDonationsSchema($pdo);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$donations = $pdo->query("
    SELECT d.*, u.first_name, u.last_name, u.blood_group
    FROM donations d
    JOIN users u ON d.user_id = u.id
    ORDER BY d.donation_date DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Donations - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="main-content">
            <?php include '../includes/dashboard_topbar.php'; ?>

            <div class="page-content">
                <p class="page-title">Scheduled Donations</p>

                <div class="card">
                    <div class="card-header">All Donations</div>
                    <div class="card-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Donor Name</th>
                                    <th>Blood Group</th>
                                    <th>Units</th>
                                    <th>Donation Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($donations)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No donations scheduled.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($donations as $i => $d): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?></td>
                                            <td><span class="badge badge-danger"><?= $d['blood_group'] ?></span></td>
                                            <td><?= $d['units'] ?></td>
                                            <td><?= date('d M Y', strtotime($d['donation_date'])) ?></td>
                                            <td>
                                                <?php
                                                $cls = ['scheduled' => 'badge-info', 'completed' => 'badge-success', 'cancelled' => 'badge-danger'];
                                                echo "<span class='badge " . $cls[$d['status']] . "'>" . ucfirst($d['status']) . "</span>";
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

    <?php include '../includes/footer.php'; ?>
</body>

</html>