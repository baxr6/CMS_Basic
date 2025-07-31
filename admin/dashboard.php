<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
global $settings;
require_admin();
include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
?>

<div class="admin-dashboard">
    <h2>Admin Dashboard</h2>
    
    <ul class="admin-menu">
        <li class="admin-menu-item">
            <a href="users.php" class="admin-btn users">
                <div class="admin-btn-icon">
                    👥
                </div>
                <div class="admin-btn-content">
                    <div class="admin-btn-title">Manage Users</div>
                    <div class="admin-btn-description">Add, edit, and manage user accounts, permissions, and roles</div>
                </div>
                <div class="admin-btn-arrow">→</div>
            </a>
        </li>
        
        <li class="admin-menu-item">
            <a href="uploads.php" class="admin-btn uploads">
                <div class="admin-btn-icon">
                    📁
                </div>
                <div class="admin-btn-content">
                    <div class="admin-btn-title">Manage File Uploads</div>
                    <div class="admin-btn-description">View, organize, and manage all uploaded files and media</div>
                </div>
                <div class="admin-btn-arrow">→</div>
            </a>
        </li>
        
        <li class="admin-menu-item">
            <a href="settings.php" class="admin-btn settings">
                <div class="admin-btn-icon">
                    ⚙️
                </div>
                <div class="admin-btn-content">
                    <div class="admin-btn-title">Site Settings</div>
                    <div class="admin-btn-description">Configure site preferences, themes, and global options</div>
                </div>
                <div class="admin-btn-arrow">→</div>
            </a>
        </li>
<li class="admin-menu-item">
    <a href="forum_categories.php" class="admin-btn forums">
        <div class="admin-btn-icon">🗂️</div>
        <div class="admin-btn-content">
            <div class="admin-btn-title">Forum Categories</div>
            <div class="admin-btn-description">Create, update, and delete forum categories</div>
        </div>
        <div class="admin-btn-arrow">→</div>
    </a>
</li>
        <li class="admin-menu-item">
            <a href="pages.php" class="admin-btn pages">
                <div class="admin-btn-icon">
                    📄
                </div>
                <div class="admin-btn-content">
                    <div class="admin-btn-title">Manage Pages</div>
                    <div class="admin-btn-description">Create, edit, and organize website pages and content</div>
                </div>
                <div class="admin-btn-arrow">→</div>
            </a>
        </li>
    </ul>
</div>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php'; ?>