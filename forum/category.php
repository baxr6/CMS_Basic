<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';


$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;


if ($category_id <= 0) {
    http_response_code(404);
    include '../themes/' . $siteTheme . '/header.php';
    ?>
    <div class='container'>
        <h2>Invalid Category</h2>
        <p>No valid category selected.</p>
        <div class="debug-info" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
            <h4>Debug Information:</h4>
            <p><strong>Raw GET data:</strong> <?= htmlspecialchars(print_r($_GET, true)) ?></p>
            <p><strong>Current URL:</strong> <?= htmlspecialchars($_SERVER['REQUEST_URI']) ?></p>
            <p><strong>Category ID received:</strong> <?= htmlspecialchars($category_id) ?></p>
            <p><strong>Expected URL format:</strong> category.php?id=1</p>
        </div>
        <p><a href="../index.php" class="btn btn-primary">← Back to Home</a></p>
    </div>
    <?php
    include '../themes/' . $siteTheme . '/footer.php';
    exit;
}

// Test database connection
try {
    $test_query = $pdo->query("SELECT COUNT(*) FROM categories");
    $total_categories = $test_query->fetchColumn();
    error_log("Total categories in database: " . $total_categories);
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Fetch the category with error handling
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($category) {
        error_log("Category found: " . $category['name']);
    } else {
        error_log("No category found with ID: " . $category_id);
        
        // Show available categories for debugging
        $all_cats = $pdo->query("SELECT id, name FROM categories ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        error_log("Available categories: " . print_r($all_cats, true));
    }
} catch (Exception $e) {
    error_log("Database query error: " . $e->getMessage());
    $category = false;
}

if (!$category) {
    http_response_code(404);
    include '../themes/' . $siteTheme . '/header.php';
    ?>
    <div class='container'>
        <h2>Category Not Found</h2>
        <p>This forum category does not exist.</p>
        
        <div class="debug-info" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
            <h4>Debug Information:</h4>
            <p><strong>Looking for category ID:</strong> <?= htmlspecialchars($category_id) ?></p>
            
            <?php
            try {
                $all_categories = $pdo->query("SELECT id, name FROM categories ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
                if (count($all_categories) > 0) {
                    echo "<p><strong>Available categories:</strong></p><ul>";
                    foreach ($all_categories as $cat) {
                        echo "<li>ID " . htmlspecialchars($cat['id']) . ": " . htmlspecialchars($cat['name']) . 
                             " (<a href='?id=" . $cat['id'] . "'>View</a>)</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p><strong>No categories found in database!</strong></p>";
                    echo "<p>You may need to create some categories first.</p>";
                }
            } catch (Exception $e) {
                echo "<p><strong>Database error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>
        </div>
        
        <p><a href="../index.php" class="btn btn-primary">← Back to Home</a></p>
    </div>
    <?php
    include '../themes/' . $siteTheme . '/footer.php';
    exit;
}

// Pagination setup
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$threads_per_page = 20;
$offset = ($page - 1) * $threads_per_page;

// Get total thread count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM threads WHERE category_id = ?");
$count_stmt->execute([$category_id]);
$total_threads = $count_stmt->fetchColumn();
$total_pages = ceil($total_threads / $threads_per_page);

// MariaDB Fix 
$threads_per_page = (int) $threads_per_page;
$offset = (int) $offset;

$threads_stmt = $pdo->prepare("
    SELECT 
        threads.*,
        users.username,
        (SELECT COUNT(*) FROM posts WHERE thread_id = threads.id) as reply_count,
        (SELECT MAX(created_at) FROM posts WHERE thread_id = threads.id) as last_post_time,
        (SELECT users.username FROM posts 
         JOIN users ON posts.user_id = users.id 
         WHERE posts.thread_id = threads.id 
         ORDER BY posts.created_at DESC LIMIT 1) as last_post_author
    FROM threads 
    JOIN users ON threads.user_id = users.id 
    WHERE threads.category_id = ? 
    ORDER BY 
        CASE WHEN threads.is_pinned = 1 THEN 0 ELSE 1 END,
        COALESCE((SELECT MAX(created_at) FROM posts WHERE thread_id = threads.id), threads.created_at) DESC
    LIMIT $threads_per_page OFFSET $offset
");
$threads_stmt->execute([$category_id]);
$threads = $threads_stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate breadcrumb
$breadcrumb_items = [
    ['text' => 'Home', 'url' => '../index.php', 'type' => 'home'],
    ['text' => 'Forums', 'url' => '../forum.php', 'type' => 'section'],
    ['text' => htmlspecialchars($category['name']), 'url' => '', 'type' => 'current']
];

// Render header
include '../themes/' . $siteTheme . '/header.php';

// Helper function for time formatting
function formatTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    if ($time < 31536000) return floor($time/2592000) . 'mo ago';
    return floor($time/31536000) . 'y ago';
}
?>

<!-- Debug panel (remove this in production) -->
<div class="debug-panel" style="background: #e8f5e9; border: 1px solid #4caf50; padding: 10px; margin: 10px 0; border-radius: 5px;">
    <strong>Debug:</strong> Successfully loaded category "<?= htmlspecialchars($category['name']) ?>" (ID: <?= $category_id ?>)
    <br><strong>Threads found:</strong> <?= $total_threads ?>
</div>

<!-- Breadcrumb Navigation -->
<nav class="breadcrumb" aria-label="Breadcrumb">
    <ul class="breadcrumb-list">
        <?php foreach ($breadcrumb_items as $index => $item): ?>
            <li class="breadcrumb-item <?= $item['type'] === 'current' ? 'breadcrumb-current' : 'breadcrumb-' . $item['type'] ?>">
                <?php if ($item['url'] && $item['type'] !== 'current'): ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>" class="breadcrumb-link"><?= $item['text'] ?></a>
                <?php else: ?>
                    <span class="breadcrumb-text"><?= $item['text'] ?></span>
                <?php endif; ?>
                <?php if ($index < count($breadcrumb_items) - 1): ?>
                    <span class="breadcrumb-separator">›</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<div class="container">
    <!-- Category Header -->
    <div class="category-header">
        <h1 class="category-title">
            <span class="category-icon">📁</span>
            <?= htmlspecialchars($category['name']) ?>
        </h1>
        <?php if (!empty($category['description'])): ?>
            <p class="category-description"><?= htmlspecialchars($category['description']) ?></p>
        <?php endif; ?>
        
        <div class="category-stats">
            <span class="stat-item">
                <strong><?= $total_threads ?></strong> thread<?= $total_threads !== 1 ? 's' : '' ?>
            </span>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="create_thread.php?category=<?= $category_id ?>" class="btn btn-primary">
                <span class="btn-icon">✏️</span>
                Create New Thread
            </a>
        <?php else: ?>
            <a href="../login.php" class="btn btn-secondary">
                <span class="btn-icon">🔐</span>
                Login to Create Thread
            </a>
        <?php endif; ?>
        
        <!-- Sort/Filter Options -->
        <div class="thread-filters">
            <select class="form-control" onchange="window.location.href=this.value" aria-label="Sort threads">
                <option value="?id=<?= $category_id ?>&page=<?= $page ?>">Sort by: Latest Activity</option>
                <option value="?id=<?= $category_id ?>&page=<?= $page ?>&sort=created">Sort by: Creation Date</option>
                <option value="?id=<?= $category_id ?>&page=<?= $page ?>&sort=replies">Sort by: Most Replies</option>
            </select>
        </div>
    </div>

    <!-- Threads List -->
    <?php if (count($threads) === 0): ?>
        <div class="empty-state">
            <div class="empty-icon">💬</div>
            <h3>No threads yet</h3>
            <p>Be the first to start a discussion in this category!</p>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="create_thread.php?category=<?= $category_id ?>" class="btn btn-primary">
                    Create First Thread
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="threads-container">
            <table class="forum-table threads-table">
                <thead>
                    <tr>
                        <th class="thread-title-col">Thread</th>
                        <th class="thread-stats-col">Replies</th>
                        <th class="thread-author-col">Author</th>
                        <th class="thread-activity-col">Last Activity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($threads as $thread): ?>
                        <tr class="thread-row <?= !empty($thread['is_pinned']) ? 'pinned-thread' : '' ?>">
                            <td class="thread-title">
                                <?php if (!empty($thread['is_pinned'])): ?>
                                    <span class="pin-icon" title="Pinned Thread">📌</span>
                                <?php endif; ?>
                                
                                <div class="thread-info">
                                    <a href="thread.php?id=<?= $thread['id'] ?>" class="thread-link">
                                        <?= htmlspecialchars($thread['title']) ?>
                                    </a>
                                    <div class="thread-meta">
                                        <span class="thread-date">
                                            Started <?= formatTimeAgo($thread['created_at']) ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="thread-stats">
                                <div class="reply-count">
                                    <strong><?= max(0, ($thread['reply_count'] ?? 1) - 1) ?></strong>
                                    <small>replies</small>
                                </div>
                            </td>
                            
                            <td class="thread-author">
                                <div class="author-info">
                                    <span class="author-name"><?= htmlspecialchars($thread['username']) ?></span>
                                </div>
                            </td>
                            
                            <td class="thread-activity">
                                <?php if ($thread['last_post_time']): ?>
                                    <div class="last-activity">
                                        <div class="activity-time"><?= formatTimeAgo($thread['last_post_time']) ?></div>
                                        <div class="activity-author">by <?= htmlspecialchars($thread['last_post_author'] ?? 'Unknown') ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="last-activity">
                                        <div class="activity-time"><?= formatTimeAgo($thread['created_at']) ?></div>
                                        <div class="activity-author">by <?= htmlspecialchars($thread['username']) ?></div>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav class="pagination-nav" aria-label="Thread pagination">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a href="?id=<?= $category_id ?>&page=<?= $page - 1 ?>" class="page-link" aria-label="Previous page">
                                ‹ Previous
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1): ?>
                        <li class="page-item">
                            <a href="?id=<?= $category_id ?>&page=1" class="page-link">1</a>
                        </li>
                        <?php if ($start_page > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">…</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <?php if ($i === $page): ?>
                                <span class="page-link current-page" aria-current="page"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?id=<?= $category_id ?>&page=<?= $i ?>" class="page-link"><?= $i ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">…</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a href="?id=<?= $category_id ?>&page=<?= $total_pages ?>" class="page-link"><?= $total_pages ?></a>
                        </li>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a href="?id=<?= $category_id ?>&page=<?= $page + 1 ?>" class="page-link" aria-label="Next page">
                                Next ›
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../themes/' . $siteTheme . '/footer.php'; ?>