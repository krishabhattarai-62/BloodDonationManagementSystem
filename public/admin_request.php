<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/functions.php';
require '../config/db.php';

requireLogin();

if (isset($_GET['action']) && isset($_GET['id'])) {
    if ($_GET['action'] === 'approve') {
        $stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE id=?");
        $stmt->execute([(int) $_GET['id']]);
        $req = $stmt->fetch();

        if ($req) {
            $pdo->prepare("UPDATE blood_requests SET status='approved', remarks=NULL WHERE id=?")->execute([(int) $_GET['id']]);

            $msg = "Your blood request for patient '{$req['patient_name']}' has been approved.";
            $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$req['user_id'], $msg]);
        }

        redirectTo("admin_request.php?msg=updated");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_id'])) {
    $remarks = trim($_POST['remarks']);

    $stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE id=?");
    $stmt->execute([(int) $_POST['reject_id']]);
    $req = $stmt->fetch();

    if ($req) {
        $pdo->prepare("UPDATE blood_requests SET status='rejected', remarks=? WHERE id=?")->execute([$remarks, (int) $_POST['reject_id']]);

        $msg = "Your blood request for patient '{$req['patient_name']}' has been rejected. Reason: {$remarks}";
        $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$req['user_id'], $msg]);
    }

    redirectTo("admin_request.php?msg=updated");
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blood Requests - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="main-content">
            <?php include '../includes/dashboard_topbar.php'; ?>

            <div class="page-content">
                <p class="page-title">Blood Requests</p>

                <?php if (isset($_GET['msg'])): ?>
                    <script>document.addEventListener('DOMContentLoaded', () => showToast('Request status updated successfully.', 'success'));</script>
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
                                        <td colspan="10" class="text-center">No requests found.</td>
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
                                                        class="btn-primary" style="padding:5px 10px; font-size:12px;">View</a>
                                                <?php else: ?>
                                                    <span style="color:var(--gray-mid);">No File</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= statusBadgeClass($r['status']) ?>">
                                                    <?= ucfirst(h($r['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($r['status'] === 'pending'): ?>
                                                    <div class="admin-request-actions">
                                                        <a href="admin_request.php?action=approve&id=<?= $r['id'] ?>"
                                                            class="btn-secondary btn-table-action">
                                                            Approve
                                                        </a>
                                                        <button type="button" onclick="openRejectModal(<?= $r['id'] ?>)"
                                                            class="btn-table-action btn-reject-action">
                                                            Reject
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="color:var(--gray-mid); font-size:12px;">&#8212;</span>
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

    <!-- Reject modal -->
    <div id="rejectModal"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:10px; padding:30px; width:400px; max-width:90%;">
            <h3 style="margin-top:0; color:#c0392b;">Reject Request</h3>
            <p style="color:#555;">Please provide a reason for rejection:</p>
            <form method="POST" action="admin_request.php">
                <input type="hidden" name="reject_id" id="rejectId">
                <textarea name="remarks" required rows="4" placeholder="Enter rejection reason..."
                    style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:14px; resize:vertical; box-sizing:border-box;"></textarea>
                <div style="display:flex; gap:10px; margin-top:15px; justify-content:flex-end;">
                    <button type="button" onclick="closeRejectModal()"
                        style="padding:8px 18px; border:1px solid #ccc; background:#fff; border-radius:6px; cursor:pointer;">
                        Cancel
                    </button>
                    <button type="submit"
                        style="padding:8px 18px; background:#c0392b; color:#fff; border:none; border-radius:6px; cursor:pointer;">
                        Confirm Reject
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(id) {
            document.getElementById('rejectId').value = id;
            document.getElementById('rejectModal').style.display = 'flex';
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
