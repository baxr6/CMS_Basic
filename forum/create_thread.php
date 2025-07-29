<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
global $settings;
$category_id = (int)($_GET['category'] ?? 0);

// Validate that the category exists
$stmt = $pdo->prepare("SELECT id, name FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    die('Invalid category specified.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Basic validation
    if (empty($title)) {
        $error = 'Thread title is required.';
    } elseif (empty($content)) {
        $error = 'Thread content is required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO threads (category_id, user_id, title, content, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$category_id, $_SESSION['user_id'], $title, $content]);

            header("Location: category.php?id=$category_id");
            exit;
        } catch (PDOException $e) {
            $error = 'Error creating thread. Please try again.';
        }
    }
}
?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
 ?>
<h2>Create New Thread in: <?= htmlspecialchars($category['name']) ?></h2>

<?php if ($error): ?>
    <div style="color: red; margin-bottom: 15px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="post">
    <input type="text" name="title" placeholder="Thread Title" required 
           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"><br><br>
    <textarea name="content" placeholder="Thread Content" rows="6" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea><br><br>
    <button type="submit">Create Thread</button>
    <a href="category.php?id=<?= $category_id ?>">Cancel</a>
</form>
<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php';
 ?>