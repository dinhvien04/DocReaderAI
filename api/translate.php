<?php
/**
 * Translation API
 * Handles translation and summarization using Google APIs
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/GoogleApiService.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize Google API service
$googleService = new GoogleApiService($_ENV['GOOGLE_API_KEY']);

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'translate':
            $userId = requireAuth();
            handleTranslate($googleService, $input);
            break;
            
        case 'summary':
            $userId = requireAuth();
            handleSummarize($googleService, $input);
            break;
            
        case 'detect':
            $userId = requireAuth();
            handleDetectLanguage($googleService, $input);
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
    error_log("Translation API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'code' => 'SERVER_ERROR'
    ]);
}

/**
 * Handle text translation
 */
function handleTranslate($googleService, $input) {
    // Validate input
    if (empty($input['text']) || empty($input['targetLang'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Text và ngôn ngữ đích không được để trống',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $text = $input['text'];
    $targetLang = $input['targetLang'];
    
    // Validate target language
    $allowedLanguages = ['en', 'vi', 'ja', 'ko', 'zh', 'zh-CN', 'zh-TW'];
    if (!in_array($targetLang, $allowedLanguages)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Ngôn ngữ không được hỗ trợ',
            'code' => 'UNSUPPORTED_LANGUAGE'
        ]);
        return;
    }
    
    // Call Google Translate API
    $result = $googleService->translateText($text, $targetLang);
    
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể dịch văn bản',
            'code' => 'TRANSLATION_FAILED',
            'details' => $result['error']
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Dịch thành công',
        'data' => [
            'translated_text' => $result['translated_text'],
            'detected_language' => $result['detected_language'] ?? 'unknown',
            'target_language' => $targetLang
        ]
    ]);
}

/**
 * Handle text summarization
 */
function handleSummarize($googleService, $input) {
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
    $prompt = $input['prompt'] ?? '';
    
    // Validate text length
    if (strlen($text) < 100) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Văn bản quá ngắn để tóm tắt',
            'code' => 'TEXT_TOO_SHORT'
        ]);
        return;
    }
    
    // Call Google AI API
    $result = $googleService->summarizeText($text, $prompt);
    
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể tóm tắt văn bản',
            'code' => 'SUMMARIZATION_FAILED',
            'details' => $result['error']
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Tóm tắt thành công',
        'data' => [
            'summary' => $result['summary']
        ]
    ]);
}

/**
 * Handle language detection
 */
function handleDetectLanguage($googleService, $input) {
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
    
    // Call Google Translate API
    $result = $googleService->detectLanguage($text);
    
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể nhận diện ngôn ngữ',
            'code' => 'DETECTION_FAILED',
            'details' => $result['error']
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Nhận diện thành công',
        'data' => [
            'language' => $result['language'],
            'confidence' => $result['confidence']
        ]
    ]);
}
