<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";
$first_name = "";
$last_name = "";
$contact_number = "";
$address = "";
$email = "";
$gender = "";
$age = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $first_name = cleanInput($_POST['first_name']);
    $last_name = cleanInput($_POST['last_name']);
    $contact_number = cleanInput($_POST['contact_number']);
    $address = cleanInput($_POST['address']);
    $email = cleanInput($_POST['email']);
    $gender = cleanInput($_POST['gender']);
    $age = cleanInput($_POST['age']);
    $password = cleanInput($_POST['password']);
    $confirm_password = cleanInput($_POST['confirm_password']);

    $location_name = cleanInput($_POST['location_name'] ?? '');
    $latitude = cleanInput($_POST['latitude']);
    $longitude = cleanInput($_POST['longitude']);

    if (!isset($_POST['csrf_token']) || !validateCSRF($_POST['csrf_token'])) {
        $error = "Invalid request!";
    } elseif (
        empty($first_name) || empty($last_name) || empty($contact_number) ||
        empty($address) || empty($email) || empty($gender) ||
        empty($age) || empty($password) || empty($confirm_password)
    ) {
        $error = "All fields are required!";
    } elseif (!validateEmail($email)) {
        $error = "Invalid email format!";
    } elseif (!in_array($gender, ['male', 'female', 'other'])) {
        $error = "Invalid gender selected!";
    } elseif (!is_numeric($age) || $age < 18 || $age > 65) {
        $error = "Age must be between 18 and 65!";
    } elseif (!preg_match('/^(98|97)[0-9]{8}$/', $contact_number)) {
        $error = "Invalid contact number! Use Nepal format (98XXXXXXXX or 97XXXXXXXX).";
    } elseif (!validatePassword($password)) {
        $error = "Password must be at least 8 characters!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $contactCheck = $pdo->prepare("SELECT id FROM users WHERE contact_number = ?");
        $contactCheck->execute([$contact_number]);
        if ($contactCheck->fetch()) {
            $error = "This contact number is already registered.";
        } else {
            $check = $pdo->prepare("SELECT id, email_verified FROM users WHERE email = ?");
            $check->execute([$email]);
            $existing = $check->fetch();

            if ($existing && $existing['email_verified'] == 1) {
                $error = "This email is already registered. Please login.";
            } elseif ($existing && $existing['email_verified'] == 0) {
                $otp = rand(100000, 999999);
                $pdo->prepare("UPDATE users SET otp = ? WHERE email = ?")
                    ->execute([$otp, $email]);

                if (sendVerificationOTP($email, $first_name, $otp)) {
                    $_SESSION['verify_email'] = $email;
                    $_SESSION['verify_first_name'] = $first_name;
                    $_SESSION['verify_flow'] = 'signup';
                    header("Location: verify_email.php");
                    exit();
                } else {
                    $error = "Failed to send verification email. Please try again.";
                }
            } else {
                $otp = rand(100000, 999999);

                if (
                    registerUserUnverified(
                        $pdo,
                        $first_name,
                        $last_name,
                        $contact_number,
                        $address,
                        $email,
                        $gender,
                        $age,
                        $password,
                        $otp,
                        $location_name,
                        $latitude,
                        $longitude
                    )
                ) {
                    $_SESSION['verify_email'] = $email;
                    $_SESSION['verify_first_name'] = $first_name;
                    $_SESSION['verify_flow'] = 'signup';

                    if (sendVerificationOTP($email, $first_name, $otp)) {
                        header("Location: verify_email.php");
                        exit();
                    } else {
                        header("Location: verify_email.php");
                        exit();
                    }
                } else {
                    $error = "Registration failed. Email may already be in use.";
                }
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
    <title>Sign Up - Blood Donation System</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .auth-field label::after,
        .auth-field-row>div label::after,
        .auth-field-inline>div label::after {
            content: " *";
            color: var(--red-mid);
        }

        /* Exclude location label and gender label from asterisk if needed */
        .no-asterisk::after {
            content: "" !important;
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-left">
                <div class="blood-icon">
                    <img src="../assets/img/droplet-solid.png" alt="Blood Drop" />
                </div>
                <h1>Sign Up</h1>
                <p>Create your account to start saving lives through blood donation.</p>
            </div>

            <div class="auth-right">
                <h2>Create your account</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">

                    <div class="auth-field-row">
                        <div>
                            <label>First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>"
                                required />
                        </div>
                        <div>
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required />
                        </div>
                    </div>

                    <div class="auth-field-row">
                        <div>
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" value="<?= htmlspecialchars($contact_number) ?>"
                                required />
                        </div>
                        <div>
                            <label>Address</label>
                            <input type="text" name="address" value="<?= htmlspecialchars($address) ?>" required />
                        </div>
                    </div>

                    <div class="auth-field-inline">
                        <div>
                            <label>Email</label>
                            <input type="text" name="email" value="<?= htmlspecialchars($email) ?>"
                                placeholder="you@example.com" required />
                        </div>
                        <div>
                            <label>Gender</label>
                            <select name="gender">
                                <option value="male" <?= $gender == 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $gender == 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= $gender == 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <label>Age</label>
                            <input type="number" name="age" min="18" max="65" value="<?= htmlspecialchars($age) ?>"
                                required />
                        </div>
                    </div>

                    <div class="auth-field">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="At least 8 characters" required />
                    </div>

                    <div class="auth-field">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="Repeat your password" required />
                    </div>

                    <!-- Location Field -->
                    <div class="auth-field">
                        <label class="no-asterisk">Your Location</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="text" id="location_display" name="location_name"
                                placeholder="Click the button to detect location"
                                value="<?= htmlspecialchars($location_name ?? '') ?>" readonly
                                style="flex:1; background:var(--input-bg, #f9f9f9); cursor:default;" />
                            <button type="button" id="detect-location-btn"
                                style="white-space:nowrap; padding:10px 14px;" class="btn-primary">
                                📍 Detect
                            </button>
                        </div>
                        <small id="location-status"
                            style="color:var(--text-muted); margin-top:4px; display:block;"></small>
                    </div>

                    <input type="hidden" id="latitude" name="latitude" value="" />
                    <input type="hidden" id="longitude" name="longitude" value="" />
                    <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

                    <div class="auth-form-actions">
                        <a href="login.php"><button type="button" class="btn-back">&#8592; Back</button></a>
                        <button type="submit" class="btn-primary">Create Account</button>
                    </div>

                </form>

                <p class="auth-footer">&copy; <?= date("Y") ?> Blood Donation System</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('detect-location-btn').addEventListener('click', function () {
            const btn = this;
            const status = document.getElementById('location-status');
            const input = document.getElementById('location_display');

            if (!navigator.geolocation) {
                status.textContent = 'Geolocation is not supported by your browser.';
                status.style.color = 'red';
                return;
            }

            btn.disabled = true;
            btn.textContent = '⏳ Detecting...';
            status.style.color = 'var(--text-muted)';
            status.textContent = 'Getting your location...';

            navigator.geolocation.getCurrentPosition(
                async function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;

                    try {
                        const res = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
                            { headers: { 'Accept-Language': 'en' } }
                        );
                        const data = await res.json();

                        const parts = [
                            data.address?.suburb,
                            data.address?.city || data.address?.town || data.address?.village,
                            data.address?.state,
                            data.address?.country
                        ].filter(Boolean);

                        const locationName = parts.join(', ');
                        input.value = locationName;
                        status.textContent = '✅ Location detected successfully.';
                        status.style.color = 'green';

                    } catch (e) {
                        input.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                        status.textContent = '⚠️ Could not get location name, coordinates saved.';
                        status.style.color = 'orange';
                    }

                    btn.disabled = false;
                    btn.textContent = '📍 Detect';
                },
                function (err) {
                    const messages = {
                        1: 'Permission denied. Please allow location access.',
                        2: 'Location unavailable. Try again.',
                        3: 'Request timed out. Try again.'
                    };
                    status.textContent = messages[err.code] || 'Could not get location.';
                    status.style.color = 'red';
                    btn.disabled = false;
                    btn.textContent = '📍 Detect';
                },
                { timeout: 10000, maximumAge: 60000 }
            );
        });
    </script>

</body>

</html>