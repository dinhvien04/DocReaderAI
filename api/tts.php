<?php
/**
 * Text-to-Speech API
 * Handles TTS conversion using FPT AI
 */

session_start();

// CORS headers for browser compatibility
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Data.php';
require_once __DIR__ . '/../services/AzureSpeechService.php';
require_once __DIR__ . '/../includes/functions.php';

// Get database connection
$db = Database::getInstance()->getConnection();
$dataModel = new Data($db);
$azureService = new AzureSpeechService(
    $_ENV['AZURE_SPEECH_KEY'] ?? '',
    $_ENV['AZURE_SPEECH_REGION'] ?? 'eastus',
    $_ENV['AZURE_SPEECH_KEY2'] ?? ''
);

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'convert':
            $userId = requireAuth();
            handleConvert($dataModel, $azureService, $userId, $input);
            break;
            
        case 'voices':
            handleGetVoices($azureService);
            break;
            
        case 'test':
            $userId = requireAuth();
            handleTestConnection($azureService);
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
    error_log("TTS API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'code' => 'SERVER_ERROR'
    ]);
}

/**
 * Handle text-to-speech conversion
 */
function handleConvert($dataModel, $azureService, $userId, $input) {
    // Validate input
    if (empty($input['text'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Text không được để trống',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $text = $input['text'];
    $voice = $input['voice'] ?? 'vi-VN-HoaiMyNeural';
    $speed = $input['speed'] ?? 1;
    $lang = $input['lang'] ?? 'vi-VN';
    
    // Validate text length
    if (strlen($text) > MAX_TEXT_LENGTH) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Văn bản vượt quá ' . MAX_TEXT_LENGTH . ' ký tự',
            'code' => 'TEXT_TOO_LONG'
        ]);
        return;
    }
    
    // Use Azure Speech Service
    $result = $azureService->textToSpeech($text, $voice, $speed);
    
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể chuyển đổi văn bản',
            'code' => 'TTS_FAILED',
            'details' => $result['error'] ?? 'Unknown error'
        ]);
        return;
    }
    
    // Save audio file
    $filename = 'azure_' . time() . '_' . uniqid() . '.mp3';
    $saveResult = $azureService->saveAudioFile($result['audio_data'], $filename);
    
    if (!$saveResult['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể lưu file audio',
            'code' => 'SAVE_FAILED'
        ]);
        return;
    }
    
    $audioUrl = BASE_URL . $saveResult['file_path'];
    
    // Save to audio history
    $audioId = $dataModel->addAudio($userId, $text, $audioUrl, $voice, $lang);
    
    if (!$audioId) {
        error_log("Failed to save audio history for user $userId");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Chuyển đổi thành công',
        'data' => [
            'audio_id' => $audioId,
            'audio_url' => $audioUrl,
            'voice' => $voice,
            'lang' => $lang
        ]
    ]);
}

/**
 * Handle get available voices
 */
function handleGetVoices($azureService) {
    $voices = $azureService->getAvailableVoices();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'voices' => $voices
        ]
    ]);
}

/**
 * Handle test connection
 */
function handleTestConnection($azureService) {
    $result = $azureService->testConnection();
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Kết nối Azure Speech Service thành công'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể kết nối đến Azure Speech Service',
            'code' => 'CONNECTION_FAILED',
            'details' => $result['error'] ?? 'Unknown error'
        ]);
    }
}
