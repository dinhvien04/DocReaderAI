<?php
/**
 * Get History Item API
 * Retrieves a single audio history item with file path and saved position
 * 
 * Endpoint: GET /api/get_history_item.php?id=123
 * Response: { success: true, data: { id, text, audio_url, voice, position, created_at } }
 */

session_start();

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    // Require authentication
    $userId = requireAuth();
    
    // Get audio ID from query parameter
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID is required',
            'code' => 'VALIDATION_ERROR'
        ]);
        exit;
    }
    
    // Get database connection
    $db = Database::getInstance()->getConnection();
    
    // Fetch audio history item with position
    $stmt = $db->prepare("
        SELECT id, user_id, text, audio_url, voice, lang, position, created_at, updated_at
        FROM audio_history
        WHERE id = ? AND user_id = ?
    ");
    
    $stmt->execute([$id, $userId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Audio not found or access denied',
            'code' => 'NOT_FOUND'
        ]);
        exit;
    }
    
    // Return the item with position
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => (int)$item['id'],
            'text' => $item['text'],
            'audio_url' => $item['audio_url'],
            'voice' => $item['voice'],
            'lang' => $item['lang'],
            'position' => (int)$item['position'], // Last saved position in seconds
            'created_at' => $item['created_at'],
            'updated_at' => $item['updated_at']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Get history item error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'code' => 'DATABASE_ERROR'
    ]);
} catch (Exception $e) {
    error_log("Get history item error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'code' => 'SERVER_ERROR'
    ]);
}
