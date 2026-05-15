<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$q = trim($_GET['q'] ?? '');
$like = "%$q%";

if ($q !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE role = 'donor' 
        AND (
            first_name      LIKE ? OR 
            last_name       LIKE ? OR 
            email           LIKE ? OR 
            contact_number  LIKE ? OR 
            blood_group     LIKE ?
        ) 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$like, $like, $like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM users WHERE role='donor' ORDER BY created_at DESC");
}

$users = $stmt->fetchAll();

if (empty($users)) {
    echo '<tr><td colspan="7" class="text-center">No users found.</td></tr>';
    exit;
}

foreach ($users as $i => $u):
    $name = htmlspecialchars($u['first_name'] . ' ' . $u['last_name']);
    $email = htmlspecialchars($u['email']);
    $contact = htmlspecialchars($u['contact_number']);
    $date = date('d M Y', strtotime($u['created_at']));
    $blood = $u['blood_group']
        ? '<span class="badge badge-danger">' . htmlspecialchars($u['blood_group']) . '</span>'
        : '<span style="color:var(--gray-mid);">&#8212;</span>';
    $deleteUrl = "admin_user.php?delete=" . (int) $u['id'];
    ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= $name ?></td>
        <td><?= $email ?></td>
        <td><?= $blood ?></td>
        <td><?= $contact ?></td>
        <td><?= $date ?></td>
        <td>
            <a href="<?= $deleteUrl ?>" onclick="return confirm('Are you sure you want to delete this user?')"
                style="color:var(--red-mid); text-decoration:none; font-size:13px; font-weight:600;">
                Delete
            </a>
        </td>
    </tr>
<?php endforeach; ?>