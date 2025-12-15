<?php
/**
 * History API
 * Handles unified history for TTS, Summarization, and Translation
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get database connection
$db = Database::getInstance()->getConnection();

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Get JSON input for DELETE requests
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'list':
            $userId = requireAuth();
            handleList($db, $userId);
            break;
            
        case 'delete':
            $userId = requireAuth();
            handleDelete($db, $userId, $input);
            break;
            
        case 'update-position':
            $userId = requireAuth();
            handleUpdatePosition($db, $userId, $input);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action',
                'code' => 'INVALID_ACTION'
            ]);
    }
} catch (Exception $e) {
    error_log("History API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'code' => 'SERVER_ERROR'
    ]);
}


/**
 * Handle list action - fetch history with filtering
 */
function handleList($db, $userId) {
    // Get parameters
    $type = $_GET['type'] ?? 'all';
    $page = max(1, intval($_GET['page'] ?? 1));
    // Allow up to 10000 records (effectively unlimited for most users)
    $limit = max(1, min(10000, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    // Validate type parameter
    $validTypes = ['all', 'tts', 'summarize', 'translate'];
    if (!in_array($type, $validTypes)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid type parameter',
            'code' => 'INVALID_TYPE'
        ]);
        return;
    }
    
    $items = [];
    $total = 0;
    
    try {
        if ($type === 'all' || $type === 'tts') {
            // Fetch TTS history
            $stmt = $db->prepare("
                SELECT id, text, audio_url, voice, lang, position, created_at
                FROM audio_history
                WHERE user_id = ?
                ORDER BY created_at DESC
                " . ($type === 'tts' ? "LIMIT ? OFFSET ?" : "")
            );
            
            if ($type === 'tts') {
                $stmt->execute([$userId, $limit, $offset]);
            } else {
                $stmt->execute([$userId]);
            }
            
            $ttsItems = $stmt->fetchAll();
            foreach ($ttsItems as $item) {
                $items[] = [
                    'id' => $item['id'],
                    'type' => 'tts',
                    'text' => $item['text'],
                    'audio_url' => $item['audio_url'],
                    'voice' => $item['voice'],
                    'lang' => $item['lang'],
                    'position' => $item['position'], // Include position
                    'created_at' => $item['created_at']
                ];
            }
            
            if ($type === 'tts') {
                // Get total count for TTS
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM audio_history WHERE user_id = ?");
                $stmt->execute([$userId]);
                $total = $stmt->fetch()['count'];
            }
        }
        
        if ($type === 'all' || $type === 'summarize') {
            // Fetch Summarize history
            $stmt = $db->prepare("
                SELECT id, original_text, summary_text, original_length, summary_length, created_at
                FROM summarize_history
                WHERE user_id = ?
                ORDER BY created_at DESC
                " . ($type === 'summarize' ? "LIMIT ? OFFSET ?" : "")
            );
            
            if ($type === 'summarize') {
                $stmt->execute([$userId, $limit, $offset]);
            } else {
                $stmt->execute([$userId]);
            }
            
            $summarizeItems = $stmt->fetchAll();
            foreach ($summarizeItems as $item) {
                $items[] = [
                    'id' => $item['id'],
                    'type' => 'summarize',
                    'original_text' => $item['original_text'],
                    'summary_text' => $item['summary_text'],
                    'original_length' => $item['original_length'],
                    'summary_length' => $item['summary_length'],
                    'created_at' => $item['created_at']
                ];
            }
            
            if ($type === 'summarize') {
                // Get total count for Summarize
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM summarize_history WHERE user_id = ?");
                $stmt->execute([$userId]);
                $total = $stmt->fetch()['count'];
            }
        }
        
        if ($type === 'all' || $type === 'translate') {
            // Fetch Translation history
            $stmt = $db->prepare("
                SELECT id, original_text, translated_text, source_lang, target_lang, created_at
                FROM translation_history
                WHERE user_id = ?
                ORDER BY created_at DESC
                " . ($type === 'translate' ? "LIMIT ? OFFSET ?" : "")
            );
            
            if ($type === 'translate') {
                $stmt->execute([$userId, $limit, $offset]);
            } else {
                $stmt->execute([$userId]);
            }
            
            $translateItems = $stmt->fetchAll();
            foreach ($translateItems as $item) {
                $items[] = [
                    'id' => $item['id'],
                    'type' => 'translate',
                    'original_text' => $item['original_text'],
                    'translated_text' => $item['translated_text'],
                    'source_lang' => $item['source_lang'],
                    'target_lang' => $item['target_lang'],
                    'created_at' => $item['created_at']
                ];
            }
            
            if ($type === 'translate') {
                // Get total count for Translation
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM translation_history WHERE user_id = ?");
                $stmt->execute([$userId]);
                $total = $stmt->fetch()['count'];
            }
        }
        
        // For 'all' type, sort combined results by created_at DESC
        if ($type === 'all') {
            usort($items, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            // Get total count for all types
            $stmt = $db->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM audio_history WHERE user_id = ?) +
                    (SELECT COUNT(*) FROM summarize_history WHERE user_id = ?) +
                    (SELECT COUNT(*) FROM translation_history WHERE user_id = ?) as total
            ");
            $stmt->execute([$userId, $userId, $userId]);
            $total = $stmt->fetch()['total'];
            
            // Apply pagination to combined results
            $items = array_slice($items, $offset, $limit);
        }
        
        // Calculate total pages
        $pages = $total > 0 ? ceil($total / $limit) : 1;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'pages' => $pages
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in handleList: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error',
            'code' => 'DATABASE_ERROR'
        ]);
    }
}


/**
 * Handle delete action - remove history item
 */
function handleDelete($db, $userId, $input) {
    // Validate input
    if (empty($input['type']) || empty($input['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Type and ID are required',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $type = $input['type'];
    $id = intval($input['id']);
    
    // Validate type parameter
    $validTypes = ['tts', 'summarize', 'translate'];
    if (!in_array($type, $validTypes)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid type parameter',
            'code' => 'INVALID_TYPE'
        ]);
        return;
    }
    
    try {
        // Determine table name based on type
        $tableName = '';
        switch ($type) {
            case 'tts':
                $tableName = 'audio_history';
                break;
            case 'summarize':
                $tableName = 'summarize_history';
                break;
            case 'translate':
                $tableName = 'translation_history';
                break;
        }
        
        // First, verify the record exists and belongs to the user
        $stmt = $db->prepare("SELECT id FROM {$tableName} WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $record = $stmt->fetch();
        
        if (!$record) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Record not found or access denied',
                'code' => 'NOT_FOUND'
            ]);
            return;
        }
        
        // Delete the record
        $stmt = $db->prepare("DELETE FROM {$tableName} WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa thành công'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to delete record',
                'code' => 'DELETE_FAILED'
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Database error in handleDelete: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error',
            'code' => 'DATABASE_ERROR'
        ]);
    }
}


/**
 * Handle update audio position
 */
function handleUpdatePosition($db, $userId, $input) {
    try {
        $id = $input['id'] ?? null;
        $type = $input['type'] ?? 'tts';
        $position = isset($input['position']) ? (int)$input['position'] : 0;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'ID is required'
            ]);
            return;
        }
        
        // Only support TTS for now
        if ($type !== 'tts') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Only TTS type is supported'
            ]);
            return;
        }
        
        // Update position in audio_history table
        $stmt = $db->prepare("
            UPDATE audio_history 
            SET position = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([$position, $id, $userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Position updated successfully',
                'data' => [
                    'id' => $id,
                    'position' => $position
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Audio not found or not authorized'
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Update position error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update position'
        ]);
    }
}
