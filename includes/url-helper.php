<?php
/**
 * URL Helper Functions
 */

// Get base URL
function baseUrl($path = '') {
    $base = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');
    return $path ? $base . '/' . $path : $base;
}

// Get asset URL
function asset($path) {
    return baseUrl($path);
}

// Redirect to URL
function redirectTo($path) {
    header('Location: ' . baseUrl($path));
    exit;
}
