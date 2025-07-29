<?php
// includes/bootstrap.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'dfw2');
define('DB_USER', 'deano');
define('DB_PASS', 'D3@n2277');

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Load all settings into global $settings
load_settings(); // ✅ now $settings['site_name'] is available everywhere
global $settings;
$siteName = $settings['site_name'] ?? 'CMS';
$siteDesc = get_setting('site_description', 'A simple PHP CMS');
$siteTheme = $settings['theme'] ?? 'default';