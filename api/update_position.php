<?php
/**
 * Update Audio Position API
 * Saves the current playback position for resuming later
 * 
 * Endpoint: POST /api/update_position.php
 * Body: { "id": 123, "position": 45 }
 * 
 * Features:
 * - Auto-save every 5-10 seconds during playback
 * - Save on pause/leave page
 * - Reset to 0 when audio ends
 */

session_start();

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    // Get JSON input (support both regular POST and sendBeacon)
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Validate input
    $id = isset($input['id']) ? (int)$input['id'] : null;
    $position = isset($input['position']) ? (int)$input['position'] : 0;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID is required',
            'code' => 'VALIDATION_ERROR'
        ]);
        exit;
    }
    
    // Ensure position is non-negative
    $position = max(0, $position);
    
    // Get database connection
    $db = Database::getInstance()->getConnection();
    
    // First check if record exists and belongs to user
    $checkStmt = $db->prepare("SELECT id, user_id, position FROM audio_history WHERE id = ?");
    $checkStmt->execute([$id]);
    $record = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Audio not found',
            'code' => 'NOT_FOUND'
        ]);
        exit;
    }
    
    if ($record['user_id'] != $userId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Not authorized',
            'code' => 'FORBIDDEN'
        ]);
        exit;
    }
    
    // Skip update if position hasn't changed (optimization)
    if ((int)$record['position'] === $position) {
        echo json_encode([
            'success' => true,
            'message' => 'Position unchanged',
            'data' => [
                'id' => $id,
                'position' => $position,
                'changed' => false
            ]
        ]);
        exit;
    }
    
    // Update position with timestamp
    $stmt = $db->prepare("
        UPDATE audio_history 
        SET position = ?, updated_at = NOW()
        WHERE id = ? AND user_id = ?
    ");
    
    $stmt->execute([$position, $id, $userId]);
    
    // Log for debugging (only in development)
    if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
        error_log("[UpdatePosition] ID=$id, Position=$position, UserID=$userId");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Position updated successfully',
        'data' => [
            'id' => $id,
            'position' => $position,
            'previous_position' => (int)$record['position'],
            'changed' => true
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Update position DB error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'code' => 'DATABASE_ERROR'
    ]);
} catch (Exception $e) {
    error_log("Update position error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'code' => 'SERVER_ERROR'
    ]);
}
