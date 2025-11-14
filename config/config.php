<?php
/**
 * Application Configuration
 * Load environment variables and define constants
 */

// Load Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Load environment variables if .env exists
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    }
} else {
    // Fallback: Load .env manually if composer not installed
    if (file_exists(__DIR__ . '/../.env')) {
        $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Define application constants
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/KK');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB in bytes
define('MAX_TEXT_LENGTH', 5000);
define('OTP_EXPIRY_MINUTES', 10);
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds

// Error reporting (disable in production)
if ($_ENV['APP_ENV'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Session configuration (must be set BEFORE session_start())
// These are now set in index.php before session_start()
// ini_set('session.cookie_httponly', '1');
// ini_set('session.use_only_cookies', '1');
// ini_set('session.cookie_secure', '0');
// ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);

// PHP settings
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', '300');
ini_set('memory_limit', '256M');
