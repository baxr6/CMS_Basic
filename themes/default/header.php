<?php
// Updated header.php with modular blocks
if (!isset($_SESSION)) session_start();
require_once __DIR__ . '/../../includes/bootstrap.php';

global $settings, $blockManager;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($siteName) ?></title>
    <link rel="stylesheet" href="/themes/<?= htmlspecialchars($siteTheme) ?>/css/style.css">
    <link rel="stylesheet" href="/themes/<?= htmlspecialchars($siteTheme) ?>/css/blocks.css">
</head>
<body>
<div class="page-wrap">
    <!-- Top Banner Blocks -->
    <div class="top-blocks">
        <?php render_blocks('top'); ?>
    </div>

    <nav>
        <a href="/index.php">Home</a>
        <a href="/forum/index.php">Forum</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="/admin/dashboard.php">Admin</a>
            <?php endif; ?>
            <a href="/notifications.php">Notifications</a>
            <a href="/forum/search.php">Search</a>
            <a href="/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="/auth/login.php">Login</a>
            <a href="/auth/register.php">Register</a>
        <?php endif; ?>
    </nav>

    <!-- Site Title + Description -->
    <div class="container">
        <h1><?= htmlspecialchars($siteName) ?></h1>
        <p><?= htmlspecialchars($siteDesc) ?></p>
    </div>

    <!-- Navigation Bar -->
    <div class="container">
        <hr />
<?php 
echo generate_dynamic_breadcrumb('default', [
    'separator' => '›',
    'home_label' => 'Home',
]); 

?>
<!-- Add JSON-LD for SEO: -->
<?php echo generate_dynamic_breadcrumb('json-ld'); ?>

        <hr />
    </div>

    <!-- 3-column structure -->
    <div class="page-columns">
        <!-- Left Sidebar -->
        <div class="column-left">
            <?php render_blocks('left'); ?>
        </div>

        <!-- Center (Main content starts in page file) -->
        <div class="column-center">