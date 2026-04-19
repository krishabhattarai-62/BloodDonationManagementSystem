<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


if (isset($_GET['action']) && isset($_GET['id'])) {
    $status = ($_GET['action'] === 'approve') ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE blood_requests SET status=? WHERE id=?");
    $stmt->execute([$status, (int) $_GET['id']]);
    header("Location: admin_request.php?msg=updated");
    exit;
}

// Fetch all requests
$requests = $pdo->query("
  SELECT br.*, u.first_name, u.last_name, u.email
  FROM blood_requests br
  JOIN users u ON br.user_id = u.id
  ORDER BY br.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Blood Requests - Admin</title>
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
                <p class="page-title">Blood Requests</p>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success">Request status updated.</div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">All Blood Requests</div>
                    <div class="card-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Requested By</th>
                                    <th>Patient Name</th>
                                    <th>Blood Group</th>
                                    <th>Units</th>
                                    <th>Hospital</th>
                                    <th>Contact</th>
                                    <th>Document</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No requests found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                            <td><?= htmlspecialchars($r['patient_name']) ?></td>
                                            <td><span class="badge badge-danger"><?= $r['blood_group'] ?></span></td>
                                            <td><?= $r['units'] ?></td>
                                            <td><?= htmlspecialchars($r['hospital']) ?></td>
                                            <td><?= htmlspecialchars($r['contact']) ?></td>
                                            <td>
                                                <?php if (!empty($r['document'])): ?>
                                                    <a href="../uploads/<?= htmlspecialchars($r['document']) ?>" target="_blank"
                                                        class="btn-primary" style="padding:5px 10px; font-size:12px;">
                                                        View
                                                    </a>
                                                <?php else: ?>
                                                    No File
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $cls = ['pending' => 'badge-warning', 'approved' => 'badge-success', 'rejected' => 'badge-success'];
                                                echo "<span class='badge " . $cls[$r['status']] . "'>" . ucfirst($r['status']) . "</span>";
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($r['status'] === 'pending'): ?>
                                                    <a href="admin_request.php?action=approve&id=<?= $r['id'] ?>"
                                                        class="btn-secondary" style="padding:4px 8px; font-size:12px;">Approve</a>
                                                    <a href="admin_request.php?action=reject&id=<?= $r['id'] ?>"
                                                        style="color:#c0392b; text-decoration:none; font-size:13px;">❌ Reject</a>
                                                <?php else: ?>
                                                    <span style="color:#aaa; font-size:12px;">—</span>
                                                <?php endif; ?>
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