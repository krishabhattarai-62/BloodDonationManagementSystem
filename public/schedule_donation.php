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
$emptyDate = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donation_date'])) {
    if (!empty($_POST['donation_date'])) {
        $emptyDate = false;

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch();

        $date = $_POST['donation_date'];
        $stmt = $pdo->prepare("INSERT INTO donations (user_id, donation_date,blood_group,units) VALUES (?,?,?,?)");
        $stmt->execute([$uid, $date, $user['blood_group'], $_POST['units']]);
        $msg = 'success';
    }
}

$stmt = $pdo->prepare("SELECT eligible FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

$eligible = $user['eligible'] ?? 0;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Donate Blood - Blood Donation</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="dashboard">

        <?php include '../includes/donor_sidebar.php'; ?>

        <div class="main-content">
            <div class="topbar">
                <h2>Blood Donation Management</h2>
                <div class="topbar-right">
                    <span>👤
                        <?= htmlspecialchars($_SESSION['first_name']) ?>
                    </span>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

            <div class="page-content">
                <p class="page-title">Donate Blood</p>

                <?php if ($msg === 'success'): ?>
                    <div class="alert alert-success">Blood donation scheduled successfully!</div>
                <?php endif; ?>

                <?php if ($eligible == 0): ?>

                    <div class="alert alert-error">
                        ❌ You are not eligible to donate blood. <br>
                        👉 Please go to your profile page to update eligibility.
                    </div>

                <?php endif; ?>

                <?php if ($emptyDate): ?>

                    <div class="alert alert-error">
                        👉 Please select date.
                    </div>

                <?php endif; ?>



                <div class="form-card" style="max-width:420px;">
                    <div class="card-header">Pick a Date</div>
                    <div class="card-body">

                        <!-- JS Calendar -->
                        <div class="calendar-nav">
                            <button onclick="prevMonth()">‹</button>
                            <span id="monthYear" style="font-weight:600; color:#c0392b;"></span>
                            <button onclick="nextMonth()">›</button>
                        </div>

                        <div class="calendar-grid" id="calendarGrid"></div>

                        <form action="schedule_donation.php" method="POST" id="scheduleForm">
                            <input type="hidden" name="donation_date" id="donation_date" required />
                            <p style="font-size:13px; color:#666; margin-bottom:15px;">
                                Selected Date: <strong id="selectedDateDisplay" style="color:#c0392b;">None</strong>
                            </p>
                            <div class="form-group">
                                <label>Units Required</label>
                                <input type="number" name="units" min="1" max="10" value="1" required />
                            </div>
                            <button type="submit" class="btn-primary" style="width:100%;" <?php if (!$eligible)
                                echo "disabled" ?>>Confirm</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <script>
            // ===== CALENDAR JAVASCRIPT =====
            let currentDate = new Date();
            let selectedDate = null;

            function renderCalendar() {
                const grid = document.getElementById('calendarGrid');
                const monthYear = document.getElementById('monthYear');
                grid.innerHTML = '';

                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();

                const months = ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'];
                monthYear.textContent = months[month] + ' ' + year;

                // Day headers
                const days = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
                days.forEach(d => {
                    const el = document.createElement('div');
                    el.className = 'calendar-day header';
                    el.textContent = d;
                    grid.appendChild(el);
                });

                const firstDay = new Date(year, month, 1).getDay();
                const totalDays = new Date(year, month + 1, 0).getDate();
                const today = new Date();

                // Empty cells before first day
                for (let i = 0; i < firstDay; i++) {
                    grid.appendChild(document.createElement('div'));
                }

                // Day cells
                for (let day = 1; day <= totalDays; day++) {
                    const el = document.createElement('div');
                    el.className = 'calendar-day';
                    el.textContent = day;

                    const thisDate = new Date(year, month, day);

                    // Disable past dates
                    if (thisDate < new Date(today.getFullYear(), today.getMonth(), today.getDate())) {
                        el.style.opacity = '0.3';
                        el.style.cursor = 'not-allowed';
                    } else {
                        el.onclick = function () {
                            // Remove previous selection
                            document.querySelectorAll('.calendar-day.selected').forEach(x => x.classList.remove('selected'));
                            el.classList.add('selected');

                            const pad = n => String(n).padStart(2, '0');
                            const dateStr = year + '-' + pad(month + 1) + '-' + pad(day);

                            document.getElementById('donation_date').value = dateStr;
                            document.getElementById('selectedDateDisplay').textContent = dateStr;
                        };
                    }

                    grid.appendChild(el);
                }
            }

            function prevMonth() {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            }

            function nextMonth() {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            }

            renderCalendar();
        </script>
    </body>

    </html>