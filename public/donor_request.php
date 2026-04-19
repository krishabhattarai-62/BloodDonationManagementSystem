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

        // Create folder if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["document"]["name"]);
        $targetFile = $uploadDir . $fileName;

        // Move file
        if (move_uploaded_file($_FILES["document"]["tmp_name"], $targetFile)) {
            $document_path = $fileName;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO blood_requests (user_id, patient_name, blood_group, units, hospital, contact, document) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$uid, $patient_name, $blood_group, $units, $hospital, $contact, $document_path]);
    $msg = 'success';

    header("Refresh:1");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Request Blood - Blood Donation Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/donor_sidebar.php'; ?>



        <div class="form-card">
            <div class="card-header">Request Blood</div>
            <?php if ($msg === 'success'): ?>
                <div class="alert alert-success">Blood Requested successfully!</div>
            <?php endif; ?>
            <div class="card-body">
                <form action="donor_request.php" method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Patient Name</label>
                        <input type="text" name="patient_name" placeholder="Enter patient name" required />
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Blood Group Needed</label>
                            <select name="blood_group" required>
                                <option value="">Select</option>
                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $g)
                                    echo "<option>$g</option>"; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Units Required</label>
                            <input type="number" name="units" min="1" max="10" value="1" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Hospital / Location</label>
                        <input type="text" name="hospital" placeholder="e.g. Grande Hospital, Kathmandu" required />
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact" placeholder="98XXXXXXXX" required />
                    </div>

                    <div class="form-group">
                        <label>Upload Document</label>
                        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required />
                    </div>

                    <div class="text-center mt-20">
                        <button type="submit" class="btn-primary" style="padding: 11px 50px;">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>