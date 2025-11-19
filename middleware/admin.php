<?php
/**
 * Admin Authorization Middleware
 * Check if user is admin
 */

// Include auth middleware first
require_once __DIR__ . '/auth.php';

// Check if user is admin
if (!isset($currentUser['role']) || $currentUser['role'] !== 'admin') {
    // Redirect to dashboard with error
    header('Location: ' . BASE_URL . '/dashboard?error=unauthorized');
    exit;
}
