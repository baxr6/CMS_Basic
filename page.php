<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? strtolower(trim((string)$_GET['slug'])) : '';
// First, let's see what slugs exist in the database
$allSlugs = $pdo->query("SELECT slug, title, is_published FROM pages ORDER BY slug");
while ($row = $allSlugs->fetch(PDO::FETCH_ASSOC)) {
}

// Now try the original query
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_published = 1");
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);


// Try without the is_published constraint
$stmt2 = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
$stmt2->execute([$slug]);
$pageAny = $stmt2->fetch(PDO::FETCH_ASSOC);

include __DIR__ . '/themes/' . $siteTheme . '/header.php';
?>

<?php if (!$page): ?>
    <div class="container">
        <h2>404 - Page Not Found</h2>
        <p>The page you're looking for doesn't exist or is not published.</p>
        <p><strong>Requested slug:</strong> <?= htmlspecialchars($slug) ?></p>
    </div>
<?php else: ?>
    <div class="container">
        <h2><?= htmlspecialchars($page['title']) ?></h2>
        <div class="page-content">
            <?= $page['content'] ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/themes/' . $siteTheme . '/footer.php'; ?>