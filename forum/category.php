<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    http_response_code(404);
    include '../themes/' . $siteTheme . '/header.php';
    echo "<div class='container'><h2>Invalid Category</h2><p>No valid category selected.</p></div>";
    include '../themes/' . $siteTheme . '/footer.php';
    exit;
}

// Fetch the category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    http_response_code(404);
    include '../themes/' . $siteTheme . '/header.php';
    echo "<div class='container'><h2>Category Not Found</h2><p>This forum category does not exist.</p></div>";
    include '../themes/' . $siteTheme . '/footer.php';
    exit;
}

// Fetch threads in the category
$threads_stmt = $pdo->prepare("
    SELECT threads.*, users.username 
    FROM threads 
    JOIN users ON threads.user_id = users.id 
    WHERE threads.category_id = ? 
    ORDER BY threads.created_at DESC
");
$threads_stmt->execute([$category_id]);
$threads = $threads_stmt->fetchAll(PDO::FETCH_ASSOC);

// Render header
include '../themes/' . $siteTheme . '/header.php';
?>

<div class="container">
    <h2>Category: <?= htmlspecialchars($category['name']) ?></h2>
    <p><?= htmlspecialchars($category['description']) ?></p>

    <?php if (count($threads) === 0): ?>
        <p>No threads in this category yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($threads as $thread): ?>
                <li>
                    <a href="thread.php?id=<?= $thread['id'] ?>">
                        <?= htmlspecialchars($thread['title']) ?>
                    </a>
                    <small>by <?= htmlspecialchars($thread['username']) ?> on <?= date('Y-m-d H:i', strtotime($thread['created_at'])) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($_SESSION['user_id'])): ?>
        <p><a href="create_thread.php?category=<?= $category_id ?>">+ Create New Thread</a></p>
    <?php endif; ?>
</div>

<?php include '../themes/' . $siteTheme . '/footer.php'; ?>
