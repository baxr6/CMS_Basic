<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
global $settings;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = __DIR__ . '/../uploads/';
    $filename = basename($_FILES['file']['name']);
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
        $stmt = $pdo->prepare("INSERT INTO files (user_id, filename, filepath) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $filename, $filepath]);
        flash('success', 'File uploaded successfully.');
    } else {
        flash('error', 'Failed to upload file.');
    }
    header("Location: uploads.php");
    exit;
}

$files = $pdo->query("SELECT * FROM files ORDER BY uploaded_at DESC")->fetchAll();
?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
 ?>
<h2>Manage File Uploads</h2>
<?php if ($msg = flash('success')) echo "<p style='color:green;'>$msg</p>"; ?>
<?php if ($msg = flash('error')) echo "<p style='color:red;'>$msg</p>"; ?>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>

<h3>Uploaded Files</h3>
<ul>
    <?php foreach ($files as $f): ?>
        <li>
            <?= htmlspecialchars($f['filename']) ?>
            - <a href="/downloads/download.php?file=<?= urlencode($f['filename']) ?>" target="_blank">Download</a>
        </li>
    <?php endforeach; ?>
</ul>
<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php';
 ?>
