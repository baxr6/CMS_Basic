<?php
// auth.php

require_once __DIR__ . '/db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_moderator() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'moderator']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /auth/login.php');
        exit;
    }
}

function require_admin() {
    if (!is_admin()) {
        header('Location: /index.php');
        exit;
    }
}
