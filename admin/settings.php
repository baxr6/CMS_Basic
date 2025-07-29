<?php

// Admin Settings.php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
global $settings;
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = ['site_name', 'site_description', 'theme'];
    
    foreach ($keys as $key) {
        $value = trim($_POST[$key] ?? '');
        $stmt = $pdo->prepare("REPLACE INTO settings (`key`, `value`) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    }

    flash('success', 'Settings updated.');
    header('Location: settings.php');
    exit;
}

// Load current settings
$current = [
    'site_name' => get_setting('site_name'),
    'site_description' => get_setting('site_description'),
    'theme' => get_setting('theme')
];
?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
 ?>
<div class="container">

    <h2>Site Settings</h2>
    <?php if ($msg = flash('success')): ?>
        <div class="flash-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Site Name:</label>
        <input type="text" name="site_name" value="<?= htmlspecialchars($current['site_name']) ?>" required>

        <label>Site Description:</label>
        <input type="text" name="site_description" value="<?= htmlspecialchars($current['site_description']) ?>">

<label>Theme:</label>
<select name="theme" required>
    <?php
    $themeDir = __DIR__ . '/../themes/';
    $themes = array_filter(scandir($themeDir), function($item) use ($themeDir) {
        return is_dir($themeDir . $item) &&
               $item !== '.' &&
               $item !== '..' &&
               file_exists($themeDir . $item . '/header.php') &&
               file_exists($themeDir . $item . '/footer.php');
    });

    foreach ($themes as $theme):
        $selected = ($theme === $current['theme']) ? 'selected' : '';
        echo "<option value=\"" . htmlspecialchars($theme) . "\" $selected>" . htmlspecialchars(ucfirst($theme)) . "</option>";
    endforeach;
    ?>
</select>

        <button type="submit">Save Settings</button>
    </form>

</div>
<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php';
 ?>
