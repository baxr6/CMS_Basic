<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
global $settings;
$search = trim($_GET['q'] ?? '');

$results = [];

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT threads.id, threads.title, threads.content, users.username
        FROM threads
        JOIN users ON threads.user_id = users.id
        WHERE threads.title LIKE ? OR threads.content LIKE ?
        ORDER BY threads.created_at DESC
    ");
    $like = "%$search%";
    $stmt->execute([$like, $like]);
    $results = $stmt->fetchAll();
}
?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
 ?>
<h3>Search Forum</h3>

<form method="get">
    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search threads..." required>
    <button type="submit">Search</button>
</form>

<?php if ($search !== ''): ?>
    <h3>Results for "<?= htmlspecialchars($search) ?>"</h3>
    <?php if ($results): ?>
        <ul>
            <?php foreach ($results as $r): ?>
                <li>
                    <a href="thread.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></a>
                    by <?= htmlspecialchars($r['username']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php';
 ?>
