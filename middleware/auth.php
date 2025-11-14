<?php
/**
 * Authentication Middleware
 * Check if user is logged in and set current user
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Set current user variable for views
$currentUser = $_SESSION['user'] ?? null;

// Check if session is expired
if (isset($_SESSION['last_activity'])) {
    $sessionTimeout = SESSION_TIMEOUT;
    if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
        // Session expired
        session_unset();
        session_destroy();
        header('Location: /login?error=session_expired');
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();
