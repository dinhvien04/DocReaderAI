<?php
/**
 * Summarize API
 * Handles text summarization requests
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../services/MegaLLMService.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
    exit;
}

// Get action from query string
$action = $_GET['action'] ?? '';

// Handle different actions
switch ($action) {
    case 'summarize':
        handleSummarize();
        break;
    
    case 'summarize-file':
        handleSummarizeFile();
        break;
    
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
        break;
}

/**
 * Handle text summarization
 */
function handleSummarize() {
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['text']) || empty(trim($input['text']))) {
            throw new Exception('Text is required');
        }
        
        $text = trim($input['text']);
        
        // Validate text length
        if (strlen($text) < 100) {
            throw new Exception('Text is too short to summarize (minimum 100 characters)');
        }
        
        if (strlen($text) > 10000) {
            throw new Exception('Text is too long (maximum 10000 characters)');
        }
        
        // Use MegaLLM API for summarization
        $megaLLM = new MegaLLMService();
        $summary = $megaLLM->summarize($text, 'vi');
        
        echo json_encode([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'original_length' => strlen($text),
                'summary_length' => strlen($summary)
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Handle file summarization
 */
function handleSummarizeFile() {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No file uploaded or upload error');
        }
        
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpPath = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validate file type
        $allowedExtensions = ['txt', 'pdf', 'doc', 'docx'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Allowed: TXT, PDF, DOC, DOCX');
        }
        
        // Validate file size (max 10MB)
        if ($fileSize > 10 * 1024 * 1024) {
            throw new Exception('File too large (max 10MB)');
        }
        
        // Extract text from file
        $text = '';
        
        if ($fileExtension === 'txt') {
            $text = file_get_contents($fileTmpPath);
        } elseif ($fileExtension === 'pdf') {
            // For PDF, we'll need to use a library or external tool
            // For now, return error asking user to paste text
            throw new Exception('PDF file support coming soon. Please copy and paste the text.');
        } elseif (in_array($fileExtension, ['doc', 'docx'])) {
            // For DOC/DOCX, we'll need a library
            throw new Exception('DOC/DOCX file support coming soon. Please copy and paste the text.');
        }
        
        // Validate extracted text
        if (empty(trim($text))) {
            throw new Exception('Could not extract text from file');
        }
        
        if (strlen($text) < 100) {
            throw new Exception('Text is too short to summarize (minimum 100 characters)');
        }
        
        if (strlen($text) > 10000) {
            // Truncate if too long
            $text = substr($text, 0, 10000);
        }
        
        // Use MegaLLM API for summarization
        $megaLLM = new MegaLLMService();
        $summary = $megaLLM->summarize($text, 'vi');
        
        echo json_encode([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'filename' => $fileName,
                'original_length' => strlen($text),
                'summary_length' => strlen($summary)
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Generate summary from text
 * This is a simple implementation. In production, use AI services.
 */
function generateSummary($text) {
    // Split text into sentences
    $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    
    if (count($sentences) <= 3) {
        return $text;
    }
    
    // Simple extractive summarization: take first, middle, and last sentences
    $summaryCount = max(3, (int)(count($sentences) * 0.3));
    
    // Score sentences based on position and length
    $scoredSentences = [];
    foreach ($sentences as $index => $sentence) {
        $score = 0;
        
        // First and last sentences get higher scores
        if ($index === 0 || $index === count($sentences) - 1) {
            $score += 10;
        }
        
        // Sentences in the middle get moderate scores
        if ($index > 0 && $index < count($sentences) - 1) {
            $score += 5;
        }
        
        // Longer sentences (but not too long) get higher scores
        $wordCount = str_word_count($sentence);
        if ($wordCount >= 10 && $wordCount <= 30) {
            $score += 5;
        }
        
        $scoredSentences[] = [
            'sentence' => $sentence,
            'score' => $score,
            'index' => $index
        ];
    }
    
    // Sort by score (descending)
    usort($scoredSentences, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    // Take top sentences
    $topSentences = array_slice($scoredSentences, 0, $summaryCount);
    
    // Sort by original index to maintain order
    usort($topSentences, function($a, $b) {
        return $a['index'] - $b['index'];
    });
    
    // Combine sentences
    $summary = implode(' ', array_column($topSentences, 'sentence'));
    
    return $summary;
}
