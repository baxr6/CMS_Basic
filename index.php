<?php
require_once __DIR__ . '/includes/bootstrap.php';
include __DIR__ . '/themes/' . $settings['theme'] . '/header.php';
?>

<h2>Welcome to the PHP CMS Forum</h2>
<p>This is a lightweight content management system with user roles, forum functionality, file uploads/downloads, and theming.</p>

<ul>
    <li><a href="/forum/index.php">Go to Forum</a></li>
    <li><a href="/auth/login.php">Login</a> or <a href="/auth/register.php">Register</a></li>
</ul>

<?php
include __DIR__ . '/themes/' . $settings['theme'] . '/footer.php';
?>
