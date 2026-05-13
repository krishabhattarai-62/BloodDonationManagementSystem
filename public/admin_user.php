<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([(int) $_GET['delete']]);
    header("Location: admin_user.php?msg=deleted");
    exit;
}

$users = $pdo->query("SELECT * FROM users WHERE role='donor' ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Users - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="main-content">
            <div class="topbar">
                <h2>Blood Donation Management</h2>
                <div class="topbar-right">
                    <span>&#128100; <?= htmlspecialchars($_SESSION['first_name']) ?></span>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

            <div class="page-content">
                <p class="page-title">Manage Users</p>

                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                    <div class="alert alert-success">User deleted successfully.</div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">All Registered Users</div>
                    <div class="card-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Blood Group</th>
                                    <th>Contact</th>
                                    <th>Registered</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No users found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $i => $u): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td>
                                                <?php if ($u['blood_group']): ?>
                                                    <span class="badge badge-danger"><?= $u['blood_group'] ?></span>
                                                <?php else: ?>
                                                    <span style="color:var(--gray-mid);">&#8212;</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($u['contact_number']) ?></td>
                                            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                            <td>
                                                <a href="admin_user.php?delete=<?= $u['id'] ?>"
                                                    onclick="return confirm('Are you sure you want to delete this user?')"
                                                    style="color:var(--red-mid); text-decoration:none; font-size:13px; font-weight:600;">
                                                    Delete
                                                </a>
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