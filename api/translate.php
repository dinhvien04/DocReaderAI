<?php
/**
 * Translation API
 * Handles translation and summarization using Google APIs
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
require_once __DIR__ . '/../services/MegaLLMService.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize MegaLLM API service
$megaLLM = new MegaLLMService();

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'translate':
            $userId = requireAuth();
            handleTranslate($megaLLM, $input);
            break;
            
        case 'summary':
            $userId = requireAuth();
            handleSummarize($megaLLM, $input);
            break;
            
        case 'detect':
            $userId = requireAuth();
            handleDetectLanguage($megaLLM, $input);
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
function handleTranslate($megaLLM, $input) {
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
    
    // Validate text length (max 10000 characters)
    $textLength = mb_strlen($text, 'UTF-8');
    if ($textLength > 10000) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => "Văn bản vượt quá 10000 ký tự (hiện tại: {$textLength} ký tự)",
            'code' => 'TEXT_TOO_LONG'
        ]);
        return;
    }
    
    // Validate target language
    $allowedLanguages = ['en', 'vi', 'ja', 'ko', 'zh', 'fr', 'de', 'es'];
    if (!in_array($targetLang, $allowedLanguages)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Ngôn ngữ không được hỗ trợ',
            'code' => 'UNSUPPORTED_LANGUAGE'
        ]);
        return;
    }
    
    try {
        // Call MegaLLM API
        $translatedText = $megaLLM->translate($text, $targetLang);
        
        // Detect source language (simple detection)
        $sourceLang = 'vi'; // Default
        if (preg_match('/[a-zA-Z]/', $text) && !preg_match('/[\x{0080}-\x{FFFF}]/u', $text)) {
            $sourceLang = 'en';
        }
        
        // Save to translation_history
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO translation_history 
                (user_id, original_text, translated_text, source_lang, target_lang) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $text,
                $translatedText,
                $sourceLang,
                $targetLang
            ]);
        } catch (PDOException $e) {
            error_log("Failed to save translation history: " . $e->getMessage());
            // Continue even if history save fails
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Dịch thành công',
            'data' => [
                'translated_text' => $translatedText,
                'target_language' => $targetLang
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể dịch văn bản: ' . $e->getMessage(),
            'code' => 'TRANSLATION_FAILED'
        ]);
    }
}

/**
 * Handle text summarization
 */
function handleSummarize($megaLLM, $input) {
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
    
    try {
        // Call MegaLLM API
        $summary = $megaLLM->summarize($text, 'vi');
        
        echo json_encode([
            'success' => true,
            'message' => 'Tóm tắt thành công',
            'data' => [
                'summary' => $summary
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể tóm tắt văn bản: ' . $e->getMessage(),
            'code' => 'SUMMARIZATION_FAILED'
        ]);
    }
}

/**
 * Handle language detection (not supported by MegaLLM, return default)
 */
function handleDetectLanguage($megaLLM, $input) {
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
    
    // Simple language detection based on character set
    $text = $input['text'];
    $language = 'vi'; // Default to Vietnamese
    
    if (preg_match('/[a-zA-Z]/', $text) && !preg_match('/[\x{0080}-\x{FFFF}]/u', $text)) {
        $language = 'en';
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Nhận diện thành công',
        'data' => [
            'language' => $language,
            'confidence' => 0.8
        ]
    ]);
}
