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

$uid = $_SESSION['user_id'];
$msg = '';

$query = "SELECT blood_group, SUM(units) AS total_units 
          FROM donations 
          GROUP BY blood_group";

$stmt = $pdo->prepare($query);
$stmt->execute();

$blood_data = [];

while ($row = $stmt->fetch()) {
    $blood_data[$row['blood_group']] = $row['total_units'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
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
                <p class="page-title">Blood Reserve</p>

                <div class="card blood-reserve-panel">
                    <div class="card-header">
                        <i class="fa-solid fa-droplet"></i> Blood Groups Inventory
                    </div>
                    <div class="card-body">
                        <div class="card-container">
                            <?php
                            $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

                            foreach ($groups as $group) {
                                $units = isset($blood_data[$group]) ? $blood_data[$group] : 0;
                                ?>
                                <div class="card">
                                    <h3><?= $group ?></h3>
                                    <p><?= $units ?> Units</p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>