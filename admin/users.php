<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
global $settings;
// Promote user
if (isset($_GET['promote'])) {
    $id = (int)$_GET['promote'];
    $stmt = $pdo->prepare("UPDATE users SET role = 'moderator' WHERE id = ?");
    $stmt->execute([$id]);
    flash('success', 'User promoted to moderator.');
    header('Location: users.php');
    exit;
}

// Delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    flash('success', 'User deleted.');
    header('Location: users.php');
    exit;
}

$users = $pdo->query("SELECT id, username, role FROM users ORDER BY id")->fetchAll();
?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
 ?>
<h2>Manage Users</h2>
<?php if ($msg = flash('success')) echo "<p style='color:green;'>$msg</p>"; ?>
<table border="1" cellpadding="5">
    <tr><th>ID</th><th>Username</th><th>Role</th><th>Actions</th></tr>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= $u['role'] ?></td>
            <td>
                <?php if ($u['role'] === 'user'): ?>
                    <a href="?promote=<?= $u['id'] ?>">Promote to Moderator</a>
                <?php endif; ?>
                <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php';
 ?>
