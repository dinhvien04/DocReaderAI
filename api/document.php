<?php
/**
 * Document API
 * Handles document upload, history, and audio management
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Data.php';
require_once __DIR__ . '/../includes/functions.php';

// Get database connection
$db = Database::getInstance()->getConnection();
$dataModel = new Data($db);

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Get JSON input for non-upload actions
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'history':
            $userId = requireAuth();
            handleGetHistory($dataModel, $userId);
            break;
        
        case 'list':
            $userId = requireAuth();
            handleGetList($dataModel, $userId);
            break;
        
        case 'get':
            $userId = requireAuth();
            $audioId = $_GET['id'] ?? 0;
            handleGetFile($dataModel, $userId, $audioId);
            break;
            
        case 'upload':
            $userId = requireAuth();
            handleUpload($userId);
            break;
            
        case 'delete':
            $userId = requireAuth();
            $audioId = $_GET['id'] ?? 0;
            handleDelete($dataModel, $userId, $audioId);
            break;
            
        case 'update-position':
            $userId = requireAuth();
            handleUpdatePosition($dataModel, $userId, $input);
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
    error_log("Document API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'code' => 'SERVER_ERROR'
    ]);
}

/**
 * Handle get audio history
 */
function handleGetHistory($dataModel, $userId) {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 20;
    
    $result = $dataModel->getAudioByUserIdPaginated($userId, (int)$page, (int)$limit);
    
    // Format for recent activity table
    $history = [];
    if (isset($result['audios'])) {  // Changed from 'data' to 'audios'
        foreach ($result['audios'] as $audio) {
            // Generate filename from id and voice
            $filename = 'audio_' . $audio['id'] . '_' . $audio['voice'] . '.mp3';
            
            // Estimate duration based on text length (rough: 150 words per minute)
            $wordCount = str_word_count($audio['text']);
            $minutes = floor($wordCount / 150);
            $seconds = floor(($wordCount % 150) / 2.5);
            $duration = sprintf('%02d:%02d', $minutes, $seconds);
            
            $history[] = [
                'id' => $audio['id'],
                'filename' => $filename,
                'text' => $audio['text'],  // Include text
                'voice' => $audio['voice'],  // Include voice
                'lang' => $audio['lang'],  // Include language
                'position' => $audio['position'],  // Include saved position
                'created_at' => $audio['created_at'],
                'duration' => $duration,
                'file_path' => $audio['audio_url']  // Use audio_url as file_path
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'history' => $history,
            'total' => $result['total'] ?? 0,
            'page' => $result['current_page'] ?? 1
        ]
    ]);
}

/**
 * Handle get file list (for recent files card)
 */
function handleGetList($dataModel, $userId) {
    $limit = $_GET['limit'] ?? 3;
    
    $result = $dataModel->getAudioByUserIdPaginated($userId, 1, (int)$limit);
    
    // Format for file list
    $files = [];
    if (isset($result['audios'])) {  // Changed from 'data' to 'audios'
        foreach ($result['audios'] as $audio) {
            // Generate filename from id and voice
            $filename = 'audio_' . $audio['id'] . '_' . $audio['voice'] . '.mp3';
            
            // Estimate duration based on text length
            $wordCount = str_word_count($audio['text']);
            $minutes = floor($wordCount / 150);
            $seconds = floor(($wordCount % 150) / 2.5);
            $duration = sprintf('%02d:%02d', $minutes, $seconds);
            
            $files[] = [
                'id' => $audio['id'],
                'filename' => $filename,
                'created_at' => $audio['created_at'],
                'duration' => $duration
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'files' => $files
        ]
    ]);
}

/**
 * Handle get single file
 */
function handleGetFile($dataModel, $userId, $audioId) {
    if (!$audioId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Audio ID không hợp lệ',
            'code' => 'INVALID_ID'
        ]);
        return;
    }
    
    // Check ownership
    if (!$dataModel->checkOwnership($audioId, $userId)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Bạn không có quyền truy cập audio này',
            'code' => 'FORBIDDEN'
        ]);
        return;
    }
    
    // Get audio data
    $audio = $dataModel->getAudioById($audioId);
    
    if (!$audio) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Không tìm thấy audio',
            'code' => 'NOT_FOUND'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $audio['id'],
            'filename' => $audio['filename'] ?? 'audio_' . $audio['id'] . '.mp3',
            'file_path' => $audio['file_path'] ?? '',
            'duration' => $audio['duration'] ?? '00:00',
            'created_at' => $audio['created_at']
        ]
    ]);
}

/**
 * Handle file upload
 */
function handleUpload($userId) {
    // Check if file was uploaded
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Không có file được upload',
            'code' => 'NO_FILE'
        ]);
        return;
    }
    
    $file = $_FILES['file'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Lỗi khi upload file',
            'code' => 'UPLOAD_ERROR'
        ]);
        return;
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'File vượt quá 10MB',
            'code' => 'FILE_TOO_LARGE'
        ]);
        return;
    }
    
    // Validate file type
    $allowedExtensions = ['pdf', 'txt'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Chỉ chấp nhận file PDF hoặc TXT',
            'code' => 'INVALID_FILE_TYPE'
        ]);
        return;
    }
    
    // Generate unique filename
    $filename = uniqid('doc_') . '_' . time() . '.' . $fileExtension;
    $uploadPath = UPLOAD_DIR . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể lưu file',
            'code' => 'SAVE_FAILED'
        ]);
        return;
    }
    
    // Extract text from file
    $text = '';
    if ($fileExtension === 'txt') {
        $text = file_get_contents($uploadPath);
    } else {
        // For PDF, text extraction will be done on client side using PDF.js
        $text = ''; // Client will send extracted text separately
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Upload thành công',
        'data' => [
            'filename' => $filename,
            'path' => '/uploads/' . $filename,
            'text' => $text,
            'type' => $fileExtension
        ]
    ]);
}

/**
 * Handle delete audio
 */
function handleDelete($dataModel, $userId, $audioId) {
    if (!$audioId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Audio ID không hợp lệ',
            'code' => 'INVALID_ID'
        ]);
        return;
    }
    
    // Check ownership
    if (!$dataModel->checkOwnership($audioId, $userId)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Bạn không có quyền xóa audio này',
            'code' => 'FORBIDDEN'
        ]);
        return;
    }
    
    // Delete audio
    if (!$dataModel->deleteAudio($audioId)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể xóa audio',
            'code' => 'DELETE_FAILED'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã xóa thành công'
    ]);
}

/**
 * Handle update playback position
 */
function handleUpdatePosition($dataModel, $userId, $input) {
    // Validate input
    if (!isset($input['id']) || !isset($input['position'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Thiếu thông tin bắt buộc',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $audioId = (int)$input['id'];
    $position = (int)$input['position'];
    
    // Check ownership
    if (!$dataModel->checkOwnership($audioId, $userId)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Bạn không có quyền cập nhật audio này',
            'code' => 'FORBIDDEN'
        ]);
        return;
    }
    
    // Update position
    if (!$dataModel->updatePosition($audioId, $position)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể cập nhật vị trí',
            'code' => 'UPDATE_FAILED'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã cập nhật vị trí'
    ]);
}
