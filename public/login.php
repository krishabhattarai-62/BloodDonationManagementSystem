<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validateCSRF($_POST['csrf_token'])) {
        $error = "Invalid request!";
    } else {

        $email = cleanInput($_POST['email']);
        $password = cleanInput($_POST['password']);

        if (empty($email) || empty($password)) {
            $error = "Email and password are required!";
        } elseif (!validateEmail($email)) {
            $error = "Invalid email format!";
        } else {
            if (loginUser($pdo, $email, $password)) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login - Blood Donation System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-left">
                <div class="blood-icon"><img src="../assets/img/droplet-solid.png" height="100" width="100"></div>
                <h1>Welcome</h1>
                <p>To Blood Management System</p>
            </div>

            <div class="auth-right">

                <div>
                    <h2>Login</h2>

                    <?php if ($error): ?>
                        <div class="alert-error">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="auth-field">
                            <label>Email</label>
                            <input type="text" name="email" value="<?= htmlspecialchars($email) ?>">
                        </div>

                        <div class="auth-field">
                            <label>Password</label>
                            <input type="password" name="password">
                        </div>

                        <a href="forget_password.php" class="auth-forgot">Forgot Password?</a>

                        <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

                        <button type="submit" class="btn-primary">Login</button>

                    </form>
                </div>

                <div>
                    <hr class="auth-divider">
                    <div class="auth-bottom">
                        <p>Dont have an account?</p>
                        <a href="register.php"><button class="btn-secondary">Signup</button></a>
                        <a href="../index.php" class="auth-back-home">Back to Home</a>
                    </div>
                    <p class="auth-footer">&copy;
                        <?php echo date("Y"); ?> Blood Donation System
                    </p>
                </div>

            </div>

        </div>
    </div>

</body>

</html>