<?php
/**
 * Text-to-Speech API
 * Supports multiple TTS engines:
 * - Azure Speech Service (Premium - paid)
 * - Edge TTS (Free - high quality)
 * - Google TTS / gTTS (Free - backup)
 */

// Disable output buffering to prevent HTML errors
ob_start();

// Error handler to return JSON instead of HTML
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred',
        'code' => 'PHP_ERROR',
        'details' => $errstr
    ]);
    exit;
});

// Exception handler
set_exception_handler(function($e) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server exception occurred',
        'code' => 'PHP_EXCEPTION',
        'details' => $e->getMessage()
    ]);
    exit;
});

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
require_once __DIR__ . '/../services/EdgeTTSService.php';
require_once __DIR__ . '/../services/GTTSService.php';
require_once __DIR__ . '/../includes/functions.php';

// Get database connection
$db = Database::getInstance()->getConnection();
$dataModel = new Data($db);

// Initialize TTS services
$azureService = new AzureSpeechService(
    $_ENV['AZURE_SPEECH_KEY'] ?? '',
    $_ENV['AZURE_SPEECH_REGION'] ?? 'eastus',
    $_ENV['AZURE_SPEECH_KEY2'] ?? ''
);
$edgeService = new EdgeTTSService();
$gttsService = new GTTSService();

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'convert':
            $userId = requireAuth();
            handleConvert($dataModel, $azureService, $edgeService, $gttsService, $userId, $input);
            break;
            
        case 'voices':
            handleGetVoices($azureService, $edgeService, $gttsService);
            break;
            
        case 'test':
            $userId = requireAuth();
            $engine = $_GET['engine'] ?? 'azure';
            handleTestConnection($azureService, $edgeService, $gttsService, $engine);
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
 * Supports Azure (premium), Edge-TTS (free), and gTTS (free) engines
 */
function handleConvert($dataModel, $azureService, $edgeService, $gttsService, $userId, $input) {
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
    
    // Determine which engine to use based on voice
    $isGTTS = strpos($voice, 'gtts-') === 0;
    $isAzure = strpos($voice, '-Azure') !== false;
    
    // Validate text length (use mb_strlen for UTF-8 characters)
    // Edge-TTS and gTTS limit: 5000 chars, Azure: 10000 chars
    $textLength = mb_strlen($text, 'UTF-8');
    $maxLength = $isAzure ? 10000 : 5000;
    
    if ($textLength > $maxLength) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => "Văn bản vượt quá {$maxLength} ký tự (hiện tại: {$textLength} ký tự)",
            'code' => 'TEXT_TOO_LONG'
        ]);
        return;
    }
    
    // TEMPORARY FIX: If Edge-TTS fails, fallback to gTTS for Vietnamese
    $useGTTSFallback = false;
    
    // Remove -Azure suffix if present
    $cleanVoice = str_replace('-Azure', '', $voice);
    
    if ($isGTTS) {
        // Use gTTS (Free - backup)
        $engine = 'gtts';
        $result = $gttsService->textToSpeech($text, $voice, $speed);
        
        if (!$result['success']) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Không thể chuyển đổi văn bản (gTTS)',
                'code' => 'TTS_FAILED',
                'details' => $result['error'] ?? 'Unknown error'
            ]);
            return;
        }
        
        $audioUrl = BASE_URL . $result['file_path'];
        
    } elseif ($isAzure) {
        // Use Azure Speech Service (Premium)
        $engine = 'azure';
        $result = $azureService->textToSpeech($text, $cleanVoice, $speed);
        
        if (!$result['success']) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Không thể chuyển đổi văn bản (Azure)',
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
        
    } else {
        // Use Edge TTS (Free - high quality) - DEFAULT
        $engine = 'edge-tts';
        $edgeError = null;
        $gttsError = null;
        
        error_log("[TTS] Starting Edge-TTS conversion for $textLength chars");
        $startTime = microtime(true);
        
        $result = $edgeService->textToSpeech($text, $cleanVoice, $speed);
        
        $endTime = microtime(true);
        error_log("[TTS] Edge-TTS took " . round($endTime - $startTime, 2) . " seconds");
        
        // FALLBACK: If Edge-TTS fails, try gTTS for Vietnamese
        if (!$result['success']) {
            $edgeError = $result['error'] ?? 'Unknown error';
            error_log("[TTS] Edge-TTS failed: " . $edgeError);
            error_log("[TTS] Attempting fallback to gTTS...");
            
            // Map Edge voice to gTTS language
            $gttsLang = 'vi'; // Default Vietnamese
            if (strpos($cleanVoice, 'en-') === 0) {
                $gttsLang = 'en';
            } elseif (strpos($cleanVoice, 'ja-') === 0) {
                $gttsLang = 'ja';
            } elseif (strpos($cleanVoice, 'ko-') === 0) {
                $gttsLang = 'ko';
            } elseif (strpos($cleanVoice, 'zh-') === 0) {
                $gttsLang = 'zh-CN';
            }
            
            $gttsVoice = 'gtts-' . $gttsLang;
            $result = $gttsService->textToSpeech($text, $gttsVoice, $speed);
            
            if ($result['success']) {
                $engine = 'gtts-fallback';
                error_log("[TTS] Fallback to gTTS successful");
            } else {
                $gttsError = $result['error'] ?? 'Unknown error';
                error_log("[TTS] gTTS also failed: " . $gttsError);
                
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Không thể chuyển đổi văn bản. Vui lòng thử lại sau.',
                    'code' => 'TTS_FAILED',
                    'details' => [
                        'edge_error' => $edgeError,
                        'gtts_error' => $gttsError,
                        'text_length' => $textLength
                    ]
                ]);
                return;
            }
        }
        
        $audioUrl = BASE_URL . $result['file_path'];
    }
    
    // Remove the old Azure-only code block
    if (false) {
    }
    
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
            'lang' => $lang,
            'engine' => $engine,
            'text_length' => $textLength
        ]
    ]);
}

/**
 * Handle get available voices from all engines
 */
function handleGetVoices($azureService, $edgeService, $gttsService) {
    // Get Azure voices (Premium)
    $azureVoices = $azureService->getAvailableVoices();
    
    // Get Edge-TTS voices (Free - high quality)
    $edgeVoices = $edgeService->getAvailableVoices();
    
    // Get gTTS voices (Free - backup)
    $gttsVoices = $gttsService->getAvailableVoices();
    
    // Combine all voices
    $allVoices = [
        'azure' => [
            'name' => 'Azure Speech (Premium)',
            'description' => 'Chất lượng cao, giọng tự nhiên',
            'free' => false,
            'voices' => $azureVoices
        ],
        'edge-tts' => [
            'name' => 'Edge TTS (Miễn phí)',
            'description' => 'Miễn phí, chất lượng cao, giọng tự nhiên',
            'free' => true,
            'voices' => $edgeVoices
        ],
        'gtts' => [
            'name' => 'Google TTS (Miễn phí)',
            'description' => 'Miễn phí, chất lượng tốt',
            'free' => true,
            'voices' => $gttsVoices
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'engines' => $allVoices,
            'voices' => array_merge($azureVoices, $edgeVoices, $gttsVoices)
        ]
    ]);
}

/**
 * Handle test connection for specified engine
 */
function handleTestConnection($azureService, $edgeService, $gttsService, $engine = 'azure') {
    if ($engine === 'edge-tts') {
        $result = $edgeService->testConnection();
        $serviceName = 'Edge TTS';
    } elseif ($engine === 'gtts') {
        $result = $gttsService->testConnection();
        $serviceName = 'Google TTS (gTTS)';
    } else {
        $result = $azureService->testConnection();
        $serviceName = 'Azure Speech Service';
    }
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => "Kết nối $serviceName thành công",
            'engine' => $engine
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => "Không thể kết nối đến $serviceName",
            'code' => 'CONNECTION_FAILED',
            'details' => $result['error'] ?? 'Unknown error',
            'engine' => $engine
        ]);
    }
}

// Flush output buffer
ob_end_flush();
