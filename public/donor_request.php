<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_name = trim($_POST['patient_name']);
    $blood_group = $_POST['blood_group'];
    $units = (int) $_POST['units'];
    $hospital = trim($_POST['hospital']);
    $contact = trim($_POST['contact']);
    $document_path = null;

    if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["document"]["name"]);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES["document"]["tmp_name"], $targetFile)) {
            $document_path = $fileName;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO blood_requests (user_id, patient_name, blood_group, units, hospital, contact, document) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$uid, $patient_name, $blood_group, $units, $hospital, $contact, $document_path]);

    // ── Notify admin ──────────────────────────────────────────
    $requesterName = $_SESSION['first_name'];
    $admin = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetch();
    if ($admin) {
        $msg_text = "New blood request by {$requesterName} for patient '{$patient_name}' ({$blood_group}, {$units} unit/s) at {$hospital}.";
        $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
            ->execute([$admin['id'], $msg_text]);
    }
    // ─────────────────────────────────────────────────────────

    $msg = 'success';
    header("Refresh:1");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Request Blood - Blood Donation Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/donor_sidebar.php'; ?>

        <div class="main-content">
            <div class="topbar">
                <h2>Blood Donation Management</h2>
                <div class="topbar-right">
                    <span>&#128100; <?= htmlspecialchars($_SESSION['first_name']) ?></span>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

            <!-- FIXED: centered wrapper for form -->
            <div class="request-form-wrapper">
                <div class="form-card" style="max-width:560px; width:100%;">
                    <div class="card-header">Request Blood</div>

                    <?php if ($msg === 'success'): ?>
                        <script>document.addEventListener('DOMContentLoaded', () => showToast('Blood request submitted successfully!', 'success'));</script>
                    <?php endif; ?>

                    <?php if ($msg === 'error'): ?>
                        <script>document.addEventListener('DOMContentLoaded', () => showToast('Something went wrong. Try again.', 'error'));</script>
                    <?php endif; ?>

                    <div class="card-body">
                        <form action="donor_request.php" method="POST" enctype="multipart/form-data">

                            <div class="form-group">
                                <label>Patient Name <span class="required">*</span></label>
                                <input type="text" name="patient_name" placeholder="Enter patient full name" required />
                            </div>

                            <div class="form-row mb-18">
                                <div class="form-group">
                                    <label>Blood Group Needed <span class="required">*</span></label>
                                    <select name="blood_group" required>
                                        <option value="">Select blood group</option>
                                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $g)
                                            echo "<option value=\"$g\">$g</option>"; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Units Required <span class="required">*</span></label>
                                    <input type="number" name="units" min="1" max="10" value="1" required />
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Hospital / Location <span class="required">*</span></label>
                                <input type="text" name="hospital" placeholder="e.g. Grande Hospital, Kathmandu"
                                    required />
                            </div>

                            <div class="form-group">
                                <label>Contact Number <span class="required">*</span></label>
                                <input type="text" name="contact" placeholder="98XXXXXXXX" required />
                            </div>

                            <div class="form-group">
                                <label>Upload Supporting Document <span class="required">*</span></label>
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" style="width:100%;"
                                    required />
                                <p style="font-size:11px; color:var(--gray-mid); margin-top:5px;">
                                    Accepted: PDF, JPG, PNG (medical proof or prescription)
                                </p>
                            </div>

                            <div class="text-center mt-20">
                                <button type="submit" class="btn-primary" style="padding:11px 50px;">
                                    Submit Request
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

</body>

</html>