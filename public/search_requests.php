<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$q = trim($_POST['q'] ?? '');
$like = "%$q%";

$stmt = $pdo->prepare("
    SELECT br.*, u.first_name, u.last_name
    FROM blood_requests br
    JOIN users u ON br.user_id = u.id
    WHERE
        u.first_name   LIKE :q1 OR
        u.last_name    LIKE :q2 OR
        br.patient_name LIKE :q3 OR
        br.blood_group  LIKE :q4 OR
        br.status       LIKE :q5
    ORDER BY br.created_at DESC
    LIMIT 20
");

$stmt->execute([
    ':q1' => $like,
    ':q2' => $like,
    ':q3' => $like,
    ':q4' => $like,
    ':q5' => $like,
]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    echo '<tr><td colspan="6" class="text-center">No results found.</td></tr>';
    exit;
}

$cls = [
    'pending' => 'badge-warning',
    'approved' => 'badge-success',
    'rejected' => 'badge-danger'
];

foreach ($results as $i => $r):
    ?>
    <tr>
        <td>
            <?= $i + 1 ?>
        </td>
        <td>
            <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>
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
            <span class="badge <?= $cls[$r['status']] ?? '' ?>">
                <?= ucfirst($r['status']) ?>
            </span>
        </td>
    </tr>
<?php endforeach; ?>