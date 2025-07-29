<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = $_POST['id'] ?? null;
    $title  = trim($_POST['title']);
    $slug   = trim($_POST['slug']);
    $content = $_POST['content'];
    $pub    = isset($_POST['is_published']) ? 1 : 0;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE pages SET title=?, slug=?, content=?, is_published=? WHERE id=?");
        $stmt->execute([$title, $slug, $content, $pub, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, is_published) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $pub]);
    }

    flash('success', 'Page saved.');
    header('Location: pages.php');
    exit;
}

// Fetch pages
$pages = $pdo->query("SELECT * FROM pages ORDER BY created_at DESC")->fetchAll();
$edit  = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/header.php'; ?>
<div class="container">
    <h2>Manage Pages</h2>

    <?php if ($msg = flash('success')): ?>
        <div class="flash-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">

        <label>Title:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($edit['title'] ?? '') ?>" required>

        <label>Slug (e.g. about, contact):</label>
        <input type="text" name="slug" value="<?= htmlspecialchars($edit['slug'] ?? '') ?>" required>

        <label>Content:</label>
        <textarea name="content" id="editor" rows="10"><?= htmlspecialchars($edit['content'] ?? '') ?></textarea>

        <label><input type="checkbox" name="is_published" <?= !empty($edit['is_published']) ? 'checked' : '' ?>> Published</label><br><br>

        <button type="submit">Save Page</button>
    </form>

    <hr>
    <h3>Existing Pages</h3>
    <ul>
        <?php foreach ($pages as $p): ?>
            <li>
                <a href="?edit=<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></a> —
                <a href="/page.php?slug=<?= urlencode($p['slug']) ?>" target="_blank">View</a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- Load WYSIWYG -->
<script src="https://cdn.tiny.cloud/1/vigggb5atp7up142gn880impw6t76pavrlkth0ijb6qz701r/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
  selector: '#editor',
  plugins: 'link image code lists',
  toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image | code'
});
</script>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php'; ?>
