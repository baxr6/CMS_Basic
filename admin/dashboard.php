<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
global $settings;
require_admin();
include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
?>
<h2>Admin Dashboard</h2>
<ul>
    <li><a href="users.php">Manage Users</a></li>
    <li><a href="uploads.php">Manage File Uploads</a></li>
    <li><a href="settings.php">Site Settings</a></li>
    <li><a href="pages.php">Manage Pages</a></li>

</ul>
<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php';
 ?>
