<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Mark as read
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$_SESSION['user_id']]);
?>

<?php include 'themes/default/header.php'; ?>
<h2>Your Notifications</h2>

<ul>
<?php foreach ($notifications as $n): ?>
    <li>
        <?= htmlspecialchars($n['message']) ?> –
        <a href="/forum/thread.php?id=<?= $n['thread_id'] ?>">View</a>
    </li>
<?php endforeach; ?>
</ul>
<?php include 'themes/default/footer.php'; ?>
