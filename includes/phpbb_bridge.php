<?php
define('IN_PHPBB', true);
$phpbb_root_path = __DIR__ . '/../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// Include phpBB core
include($phpbb_root_path . 'common.' . $phpEx);

// Start phpBB session
$user->session_begin();
$auth->acl($user->data);
$user->setup();

?>