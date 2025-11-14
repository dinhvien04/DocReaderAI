<?php
/**
 * DocReader AI Studio - Main Application Router
 * Entry point for all requests
 */

// Configure session BEFORE starting it
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_secure', '0'); // Set to '1' if using HTTPS
ini_set('session.gc_maxlifetime', '1800'); // 30 minutes

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Get page from URL
$page = $_GET['page'] ?? 'home';

// Route to appropriate view
switch ($page) {
    // Public pages
    case 'home':
    case '':
        include __DIR__ . '/views/index.php';
        break;
    
    case 'login':
        include __DIR__ . '/views/login.php';
        break;
    
    case 'register':
        include __DIR__ . '/views/register.php';
        break;
    
    case 'reset-password':
        include __DIR__ . '/views/reset-password.php';
        break;
    
    case 'about':
        include __DIR__ . '/views/about.php';
        break;
    
    case 'terms':
        include __DIR__ . '/views/terms.php';
        break;
    
    case 'privacy':
        include __DIR__ . '/views/privacy.php';
        break;
    
    // Protected pages (require authentication)
    case 'dashboard':
        include __DIR__ . '/views/dashboard.php';
        break;
    
    case 'profile':
        include __DIR__ . '/views/profile.php';
        break;
    
    case 'change-password':
        include __DIR__ . '/views/change-password.php';
        break;
    
    // Admin pages (require admin role)
    case 'admin':
        include __DIR__ . '/views/admin/index.php';
        break;
    
    case 'admin-users':
        include __DIR__ . '/views/admin/users.php';
        break;
    
    case 'admin-config':
        include __DIR__ . '/views/admin/config.php';
        break;
    
    // 404 - Not found
    default:
        http_response_code(404);
        include __DIR__ . '/views/404.php';
        break;
}
