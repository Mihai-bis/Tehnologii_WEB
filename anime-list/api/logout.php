<?php
require_once __DIR__ . '/../includes/functions.php';

// Clear all session data
$_SESSION = [];

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Destroy session
session_destroy();

setFlash('success', 'You have been logged out successfully.');
redirect(SITE_URL . '/index.php');
