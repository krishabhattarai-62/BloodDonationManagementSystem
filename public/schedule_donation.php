<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid      = $_SESSION['user_id'];
$msg      = '';
$dateError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donation_date'])) {

    if (empty($_POST['donation_date'])) {
        $dateError = true;
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch();

        $date = $_POST['donation_date'];
        // FIX: also save donation_time
        $time = $_POST['donation_time'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO donations (user_id, donation_date, donation_time, blood_group, units) VALUES (?,?,?,?,?)");
        $stmt->execute([$uid, $date, $time, $user['blood_group'], $_POST['units']]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
                    <span>&#128100; <?= htmlspecialchars($_SESSION['first_name']) ?></span>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

            <div class="donate-page-wrapper">
                <div style="width:100%; max-width:440px;">

                    <p class="page-title" style="margin-bottom:18px;">Donate Blood</p>

                    <?php if ($msg === 'success'): ?>
                        <div class="alert alert-success">Blood donation scheduled successfully!</div>
                    <?php endif; ?>

                    <?php if ($eligible == 0): ?>
                        <div class="alert alert-error">
                            You are not eligible to donate blood.<br>
                            Please update your profile first.
                        </div>
                    <?php endif; ?>

                    <?php if ($dateError): ?>
                        <div class="alert alert-error">Please select a donation date.</div>
                    <?php endif; ?>

                    <div class="form-card">
                        <div class="card-header">Pick a Date</div>
                        <div class="card-body">

                            <div class="calendar-nav">
                                <button onclick="prevMonth()">&#8249;</button>
                                <span id="monthYear"></span>
                                <button onclick="nextMonth()">&#8250;</button>
                            </div>

                            <div class="calendar-grid" id="calendarGrid"></div>

                            <form action="schedule_donation.php" method="POST" id="scheduleForm">

                                <input type="hidden" name="donation_date" id="donation_date" required />

                                <p style="font-size:13px; color:#666; margin-bottom:15px;">
                                    Selected Date:
                                    <strong id="selectedDateDisplay" style="color:var(--red-mid);">None</strong>
                                </p>

                                <!-- FIX 2: Time picker -->
                                <div class="form-group">
                                    <label>Preferred Time</label>
                                    <input type="time" name="donation_time" id="donation_time" required />
                                </div>

                                <!-- FIX 3: Max units changed from 10 to 2 -->
                                <div class="form-group">
                                    <label>Units to Donate</label>
                                    <input type="number" name="units" min="1" max="2" value="1" required />
                                </div>

                                <button type="submit" class="btn-primary" style="width:100%;"
                                    <?php if (!$eligible) echo 'disabled title="Update your profile to become eligible"'; ?>>
                                    Confirm Donation
                                </button>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        let currentDate = new Date();

        function renderCalendar() {
            const grid      = document.getElementById('calendarGrid');
            const monthYear = document.getElementById('monthYear');
            grid.innerHTML  = '';

            const year  = currentDate.getFullYear();
            const month = currentDate.getMonth();

            const months = ['January','February','March','April','May','June',
                            'July','August','September','October','November','December'];
            monthYear.textContent = months[month] + ' ' + year;

            ['Su','Mo','Tu','We','Th','Fr','Sa'].forEach(d => {
                const el = document.createElement('div');
                el.className   = 'calendar-day header';
                el.textContent = d;
                grid.appendChild(el);
            });

            const firstDay  = new Date(year, month, 1).getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();
            const today     = new Date();

            // FIX 1: Calculate the 6-month ceiling
            const maxDate = new Date(today.getFullYear(), today.getMonth() + 6, today.getDate());

            for (let i = 0; i < firstDay; i++) {
                grid.appendChild(document.createElement('div'));
            }

            for (let day = 1; day <= totalDays; day++) {
                const el       = document.createElement('div');
                el.className   = 'calendar-day';
                el.textContent = day;

                const thisDate = new Date(year, month, day);

                // FIX 1: Block past dates AND dates beyond 6 months
                const isPast   = thisDate < new Date(today.getFullYear(), today.getMonth(), today.getDate());
                const isTooFar = thisDate > maxDate;

                if (isPast || isTooFar) {
                    el.style.opacity = '0.3';
                    el.style.cursor  = 'not-allowed';
                    if (isTooFar) {
                        el.title = 'Cannot schedule more than 6 months in advance';
                    }
                } else {
                    el.onclick = function () {
                        document.querySelectorAll('.calendar-day.selected')
                            .forEach(x => x.classList.remove('selected'));
                        el.classList.add('selected');

                        const pad     = n => String(n).padStart(2, '0');
                        const dateStr = year + '-' + pad(month + 1) + '-' + pad(day);
                        document.getElementById('donation_date').value            = dateStr;
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

    <?php include '../includes/footer.php'; ?>
</body>

</html>