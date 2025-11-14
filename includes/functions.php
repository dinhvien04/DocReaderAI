<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

// Include URL helper
require_once __DIR__ . '/url-helper.php';

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Redirect to URL
 * @param string $url
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Sanitize input data
 * @param mixed $data
 * @return mixed
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate 6-digit OTP
 * @return string
 */
function generateOtp(): string {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Format date to Vietnamese format
 * @param string $date
 * @return string
 */
function formatDate(string $date): string {
    return date('d/m/Y H:i', strtotime($date));
}

/**
 * Get current user from session
 * @return array|null
 */
function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Check authentication and return user ID
 * @return int|null
 */
function requireAuth(): ?int {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized',
            'code' => 'UNAUTHORIZED'
        ]);
        exit;
    }
    return $_SESSION['user_id'];
}

/**
 * Check admin role
 * @return bool
 */
function requireAdmin(): bool {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Forbidden - Admin access required',
            'code' => 'FORBIDDEN'
        ]);
        exit;
    }
    return true;
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random string
 * @param int $length
 * @return string
 */
function generateRandomString(int $length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get base URL for the application
 * @return string
 */
function getBaseUrl(): string {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . ($script === '/' ? '' : $script);
}

/**
 * Generate URL for a page
 * @param string $page
 * @return string
 */
function url(string $page = ''): string {
    $base = getBaseUrl();
    return $page ? $base . '/' . ltrim($page, '/') : $base;
}
