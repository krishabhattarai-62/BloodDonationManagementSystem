<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

checkSessionTimeout();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

requireDonor();

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$first_name = $user['first_name'];
$last_name = $user['last_name'];
$contact_number = $user['contact_number'];
$email = $user['email'];
$gender = $user['gender'];
$age = $user['age'];
$blood_group = $user['blood_group'] ?? '';
$alreadyEligible = $user['eligible'] ?? 0;
$location_name = $user['location_name'] ?? '';
$latitude = $user['latitude'] ?? '';
$longitude = $user['longitude'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_POST['csrf_token']) || !validateCSRF($_POST['csrf_token'])) {
        $error = "Invalid request!";
    } else {

        $first_name = cleanInput($_POST['first_name']);
        $last_name = cleanInput($_POST['last_name']);
        $contact_number = cleanInput($_POST['contact_number']);
        $gender = cleanInput($_POST['gender']);
        $age = cleanInput($_POST['age']);
        $blood_group = cleanInput($_POST['blood_group']);
        $eligible = isset($_POST['eligible']) ? 1 : 0;
        $location_name = cleanInput($_POST['location_name'] ?? '');
        $latitude = cleanInput($_POST['latitude'] ?? '');
        $longitude = cleanInput($_POST['longitude'] ?? '');

        $location_name = ($location_name !== '') ? $location_name : null;
        $latitude = ($latitude !== '') ? $latitude : null;
        $longitude = ($longitude !== '') ? $longitude : null;

        $extracted_blood_group = null;

        // OCR reads the blood group from the uploaded proof image.
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/bmp'];
            $maxSize = 5 * 1024 * 1024;

            $fileTmp = $_FILES['image']['tmp_name'];
            $fileType = mime_content_type($fileTmp);
            $fileSize = $_FILES['image']['size'];

            if (!in_array($fileType, $allowedTypes)) {
                $error = "Invalid file type. Please upload a JPEG, PNG, WEBP, or BMP image.";
            } elseif ($fileSize > $maxSize) {
                $error = "Image is too large. Maximum allowed size is 5MB.";
            } else {

                $uploadDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
                $uniqueName = uniqid('ocr_', true);
                $outputBase = $uploadDir . $uniqueName . '_out';

                if (is_uploaded_file($fileTmp) && is_readable($fileTmp)) {

                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        $tesseractPath = 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe';
                    } else {
                        $tesseractCandidates = [
                            '/opt/homebrew/bin/tesseract',
                            '/usr/local/bin/tesseract',
                            '/usr/bin/tesseract',
                        ];
                        $tesseractPath = 'tesseract';
                        foreach ($tesseractCandidates as $candidate) {
                            if (is_executable($candidate)) {
                                $tesseractPath = $candidate;
                                break;
                            }
                        }
                    }

                    $escapedTesseract = escapeshellarg($tesseractPath);
                    $escapedInput = escapeshellarg($fileTmp);
                    $escapedOutput = escapeshellarg($outputBase);
                    $command = "$escapedTesseract $escapedInput $escapedOutput 2>&1";
                    exec($command, $cmdOutput, $returnCode);

                    $ocrTextFile = $outputBase . '.txt';

                    if ($returnCode === 0 && file_exists($ocrTextFile)) {

                        $text = file_get_contents($ocrTextFile);

                        // Normalize common OCR variants before matching.
                        $normalized = strtoupper($text);
                        $normalized = preg_replace('/\s+/', ' ', $normalized);
                        $normalized = str_replace(['(+)', '(-)'], ['+', '-'], $normalized);
                        $normalized = preg_replace('/\bPOSITIVE\b/', '+', $normalized);
                        $normalized = preg_replace('/\bNEGATIVE\b/', '-', $normalized);
                        $normalized = preg_replace('/([A-Z])\s+([+-])/', '$1$2', $normalized);

                        preg_match('/(AB[+-]|[ABO][+-])/', $normalized, $match);
                        $extracted_blood_group = $match[0] ?? null;

                    } else {
                        $error = $returnCode === 127
                            ? "OCR tool not found. Please install Tesseract or check its path."
                            : "OCR processing failed. Please try a clearer image.";
                    }

                    @unlink($ocrTextFile);

                    // Keep failed OCR temp files from piling up.
                    $oldFiles = glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_*');
                    foreach ($oldFiles as $oldFile) {
                        if (filemtime($oldFile) < time() - 3600) {
                            @unlink($oldFile);
                        }
                    }

                } else {
                    $error = "Failed to read uploaded image. Please try again.";
                }
            }

        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = "File upload error (code: " . $_FILES['image']['error'] . ").";
        }

        if (empty($error)) {
            if (
                empty($first_name) || empty($last_name) || empty($contact_number) ||
                empty($gender) || empty($age) || empty($blood_group)
            ) {
                $error = "All fields are required!";
            } elseif (!is_numeric($age) || $age < 18 || $age > 65) {
                $error = "Age must be between 18 and 65.";
            } elseif (!in_array($gender, ['male', 'female', 'other'])) {
                $error = "Invalid gender.";
            } elseif ($extracted_blood_group === null) {
                $error = "Could not detect blood group from uploaded image.";
            } elseif (strtoupper($extracted_blood_group) !== strtoupper($blood_group)) {
                $error = "Blood group mismatch! Image shows $extracted_blood_group but you selected $blood_group.";
            }
        }

        if (empty($error)) {

            $stmt = $pdo->prepare(
                "UPDATE users 
                 SET first_name=?, last_name=?, contact_number=?, gender=?, age=?, eligible=?, blood_group=?,
                     location_name=?, latitude=?, longitude=?
                 WHERE id=?"
            );

            if (
                $stmt->execute([
                    $first_name,
                    $last_name,
                    $contact_number,
                    $gender,
                    $age,
                    $eligible,
                    $blood_group,
                    $location_name,
                    $latitude,
                    $longitude,
                    $user_id
                ])
            ) {
                $success = "Profile updated successfully!";
                $alreadyEligible = $eligible;
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Profile - Blood Donation</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <?php include '../includes/icon_fonts.php'; ?>
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/donor_sidebar.php'; ?>

        <div class="main-content">

            <?php include '../includes/dashboard_topbar.php'; ?>

            <div class="page-content">
                <p class="page-title" style="text-align:center; justify-content:center;">My Profile</p>

                <div style="display:flex; justify-content:center;">
                    <div class="form-card" style="width:100%; max-width:520px;">

                        <div class="card-header">Edit Your Details</div>

                        <div class="card-body">

                            <?php if ($success): ?>
                                <script>document.addEventListener('DOMContentLoaded', () => showToast('<?= htmlspecialchars($success) ?>', 'success'));</script>
                            <?php endif; ?>

                            <?php if ($error): ?>
                                <script>document.addEventListener('DOMContentLoaded', () => showToast('<?= htmlspecialchars($error) ?>', 'error'));</script>
                            <?php endif; ?>

                            <form action="donor_profile.php" method="POST" enctype="multipart/form-data">

                                <div class="form-group">
                                    <div class="form-row">
                                        <div>
                                            <label>First Name</label>
                                            <input type="text" name="first_name"
                                                value="<?= htmlspecialchars($first_name) ?>" required />
                                        </div>
                                        <div>
                                            <label>Last Name</label>
                                            <input type="text" name="last_name"
                                                value="<?= htmlspecialchars($last_name) ?>" required />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Email <small style="color:var(--text-muted);font-weight:400;">(cannot be
                                            changed)</small></label>
                                    <input type="email" value="<?= htmlspecialchars($email) ?>" disabled />
                                </div>

                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="contact_number"
                                        value="<?= htmlspecialchars($contact_number) ?>" placeholder="98XXXXXXXX" />
                                </div>

                                <div class="form-group">
                                    <div class="form-row-triple">
                                        <div>
                                            <label>Gender</label>
                                            <select name="gender">
                                                <option value="male" <?= $gender == 'male' ? 'selected' : '' ?>>Male
                                                </option>
                                                <option value="female" <?= $gender == 'female' ? 'selected' : '' ?>>Female
                                                </option>
                                                <option value="other" <?= $gender == 'other' ? 'selected' : '' ?>>Other
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label>Age</label>
                                            <input type="number" name="age" min="18" max="65"
                                                value="<?= htmlspecialchars($age) ?>" />
                                        </div>
                                        <div>
                                            <label>Blood Group</label>
                                            <select name="blood_group">
                                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $g): ?>
                                                    <option value="<?= $g ?>" <?= $blood_group == $g ? 'selected' : '' ?>>
                                                        <?= $g ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group profile-location-field">
                                    <label>Your Location</label>
                                    <div class="profile-location-row">
                                        <input type="text" id="location_display" name="location_name"
                                            placeholder="Click Detect to update your location"
                                            value="<?= htmlspecialchars($location_name) ?>" readonly />
                                        <button type="button" id="detect-location-btn" class="btn-primary"
                                            style="white-space:nowrap; padding:10px 14px;">
                                            <i class="fa-solid fa-location-dot"></i> Detect
                                        </button>
                                    </div>
                                    <small id="location-status" class="profile-location-status"></small>
                                </div>
                                <input type="hidden" id="latitude" name="latitude"
                                    value="<?= htmlspecialchars((string) $latitude) ?>" />
                                <input type="hidden" id="longitude" name="longitude"
                                    value="<?= htmlspecialchars((string) $longitude) ?>" />

                                <div class="eligibility-box">
                                    <h4>Donation Eligibility</h4>
                                    <ul>
                                        <li>Age between 18 and 65 years</li>
                                        <li>Feeling well and healthy today</li>
                                        <li>No major surgery in the last 6 months</li>
                                        <li>Not currently on strong medications</li>
                                    </ul>
                                    <label class="eligibility-checkbox">
                                        <input type="checkbox" name="eligible" <?= $alreadyEligible == 1 ? 'checked' : '' ?>>
                                        <span>I confirm I meet all the above conditions and am eligible to
                                            donate.</span>
                                    </label>
                                </div>

                                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/bmp"
                                    required>

                                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

                                <div class="text-center mt-20">
                                    <button type="submit" class="btn-primary" style="padding:11px 40px;">
                                        Save Changes
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/location_detect_script.php'; ?>
</body>

</html>
