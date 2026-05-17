<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once '../includes/functions.php';
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$uid = $_SESSION['user_id'];
$q = trim($_POST['q'] ?? '');
$like = "%$q%";

$stmt = $pdo->prepare("
    SELECT * FROM blood_requests
    WHERE
        user_id = :uid AND (
            patient_name LIKE :q1 OR
            blood_group  LIKE :q2 OR
            hospital     LIKE :q3 OR
            status       LIKE :q4
        )
    ORDER BY created_at DESC
    LIMIT 20
");

$stmt->execute([
    ':uid' => $uid,
    ':q1' => $like,
    ':q2' => $like,
    ':q3' => $like,
    ':q4' => $like,
]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    echo '<tr><td colspan="6" class="text-center">No results found.</td></tr>';
    exit;
}

foreach ($results as $i => $r):
    ?>
    <tr>
        <td>
            <?= $i + 1 ?>
        </td>
        <td>
            <?= htmlspecialchars($r['patient_name']) ?>
        </td>
        <td>
            <?= htmlspecialchars($r['blood_group']) ?>
        </td>
        <td>
            <?= htmlspecialchars($r['units']) ?>
        </td>
        <td>
            <?= htmlspecialchars($r['hospital']) ?>
        </td>
        <td>
            <span class="badge <?= statusBadgeClass($r['status']) ?>">
                <?= ucfirst(h($r['status'])) ?>
            </span>
        </td>
    </tr>
<?php endforeach; ?>
