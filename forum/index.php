<?php
// Forum Index.php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$stmt = $pdo->query("
    SELECT c.id, c.name, c.description,
           COUNT(t.id) AS thread_count,
           (
               SELECT MAX(p.created_at)
               FROM threads t2
               JOIN posts p ON p.thread_id = t2.id
               WHERE t2.category_id = c.id
           ) AS last_post_time
    FROM categories c
    LEFT JOIN threads t ON t.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name
");

$categories = $stmt->fetchAll();
?>

<?php include '../themes/' . $siteTheme . '/header.php'; ?>

<h2>Forum Categories</h2>

<table class="forum-table">
    <thead>
        <tr>
            <th>Category</th>
            <th>Threads</th>
            <th>Last Post</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td>
                    <a href="category.php?id=<?= $cat['id'] ?>">
                        <strong><?= htmlspecialchars($cat['name']) ?></strong>
                    </a><br>
                    <small><?= htmlspecialchars($cat['description']) ?></small>
                </td>
                <td><?= $cat['thread_count'] ?></td>
<td>
    <?= $cat['last_post_time']
        ? date('Y-m-d H:i', strtotime($cat['last_post_time']))
        : '<span class="no-post">No posts yet</span>' ?>
</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../themes/' . $siteTheme . '/footer.php'; ?>
