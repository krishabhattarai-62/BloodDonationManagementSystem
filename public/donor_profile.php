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
$eligible = isset($_POST['eligible']) ? 1 : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !validateCSRF($_POST['csrf_token'])
    ) {
        $error = "Invalid request!";
    } else {


        $first_name = cleanInput($_POST['first_name']);
        $last_name = cleanInput($_POST['last_name']);
        $contact_number = cleanInput($_POST['contact_number']);
        // $email = cleanInput($_POST['email']);
        $gender = cleanInput($_POST['gender']);
        $age = cleanInput($_POST['age']);
        $blood_group = cleanInput($_POST['blood_group']);

        if (empty($first_name) || empty($last_name) || empty($contact_number) || empty($gender) || empty($age) || empty($blood_group)) {
            $error = "All fields are required!";
        } elseif (!validateEmail($email)) {
            $error = "Invalid email format!";
        } elseif (!is_numeric($age) || $age < 18 || $age > 65) {
            $error = "Age must be between 18 and 65!";
        } elseif (!in_array($gender, ['male', 'female', 'other'])) {
            $error = "Invalid gender!";
        } else {
            $stmt = $pdo->prepare(
                "UPDATE users SET first_name = ?, last_name = ?, contact_number = ?, gender = ?, age = ?,eligible = ?, blood_group = ? WHERE id = ?"
            );
            if ($stmt->execute([$first_name, $last_name, $contact_number, $gender, $age, $eligible, $blood_group, $user_id])) {
                $success = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>My Profile - Blood Donation</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/donor_sidebar.php'; ?>
        <div class="main-content">
            <div class="topbar">
                <h2>Blood Donation Management</h2>
                <div class="topbar-right">
                    <span>👤 <?= htmlspecialchars($_SESSION['first_name']) ?></span>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

            <div class="page-content">
                <p class="page-title">Choose Edit in your details below</p>

                <?php if ($success): ?>
                    <div class="alert-success"><?= $success ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert-error"><?= $error ?></div>
                <?php endif; ?>


                <div class="form-card">
                    <div class="card-header">My Profile</div>
                    <div class="card-body">
                        <form action="donor_profile.php" method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>"
                                        required />
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>"
                                        required />
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email (cannot change)</label>
                                <input type="email" value="<?= htmlspecialchars($email) ?>" disabled
                                    style="background:#f5f5f5;" />
                            </div>

                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="contact_number"
                                    value="<?= htmlspecialchars($contact_number) ?>" />
                            </div>

                            <div class="form-row-triple">
                                <div>
                                    <label>Gender</label>
                                    <select name="gender">
                                        <option value="male" <?php if ($gender == 'male')
                                            echo 'selected'; ?>>Male</option>
                                        <option value="female" <?php if ($gender == 'female')
                                            echo 'selected'; ?>>Female</option>
                                        <option value="other" <?php if ($gender == 'other')
                                            echo 'selected'; ?>>Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Age</label>
                                    <input type="number" name="age" min="18" max="65"
                                        value="<?= htmlspecialchars($age) ?>">
                                </div>
                                <div>
                                    <label>Blood Group</label>
                                    <select name="blood_group">
                                        <option value="A+" <?php if ($blood_group == 'A+')
                                            echo 'selected'; ?>>A+</option>
                                        <option value="A-" <?php if ($blood_group == 'A-')
                                            echo 'selected'; ?>>A-</option>
                                        <option value="B+" <?php if ($blood_group == 'B+')
                                            echo 'selected'; ?>>B+</option>
                                        <option value="B-" <?php if ($blood_group == 'B-')
                                            echo 'selected'; ?>>B-</option>
                                        <option value="AB+" <?php if ($blood_group == 'AB+')
                                            echo 'selected'; ?>>AB+</option>
                                        <option value="AB-" <?php if ($blood_group == 'AB-')
                                            echo 'selected'; ?>>AB-</option>
                                        <option value="O+" <?php if ($blood_group == 'O+')
                                            echo 'selected'; ?>>O+</option>
                                        <option value="O-" <?php if ($blood_group == 'O-')
                                            echo 'selected'; ?>>O-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <div>
                                    <h4>Eligibility Test</h4>
                                    <ul>
                                        <li>Age must be between 18 and 65 years</li>
                                        <li>You are feeling well (no fever, infection, or illness)</li>
                                        <li>No major surgery in the last 6 months</li>
                                        <li>Not taking strong medications (like antibiotics)</li>
                                    </ul>
                                </div>
                            </div>


                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="eligible" <?php if ($eligible == 1)
                                        echo 'checked'; ?>>
                                    I confirm that I meet all the above conditions
                                </label>
                            </div>

                            <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

                            <div class="text-center mt-20">
                                <button type="submit" class="btn-primary" style="padding: 11px 40px;">UPDATE</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>