<?php
/**
 * Google API Service
 * Handles translation and summarization using Google Cloud APIs
 */

class GoogleApiService {
    private $apiKey;
    private $translateUrl = 'https://translation.googleapis.com/language/translate/v2';
    private $aiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    
    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }
    
    /**
     * Translate text to target language
     * @param string $text Text to translate
     * @param string $targetLang Target language code (en, vi, ja, ko, zh)
     * @return array ['success' => bool, 'translated_text' => string, 'error' => string]
     */
    public function translateText(string $text, string $targetLang): array {
        try {
            // Validate text
            if (empty($text)) {
                return [
                    'success' => false,
                    'error' => 'Text cannot be empty'
                ];
            }
            
            // Prepare request data
            $data = [
                'q' => $text,
                'target' => $targetLang,
                'format' => 'text',
                'key' => $this->apiKey
            ];
            
            // Make API request
            $response = $this->makeRequest($this->translateUrl, $data, 'POST');
            
            if ($response['success']) {
                $translatedText = $response['data']['data']['translations'][0]['translatedText'] ?? '';
                
                return [
                    'success' => true,
                    'translated_text' => $translatedText,
                    'detected_language' => $response['data']['data']['translations'][0]['detectedSourceLanguage'] ?? 'unknown'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? 'Translation failed'
                ];
            }
        } catch (Exception $e) {
            error_log("Error in translateText: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred during translation'
            ];
        }
    }
    
    /**
     * Summarize text using Google AI
     * @param string $text Text to summarize
     * @param string $prompt Custom prompt (optional)
     * @return array ['success' => bool, 'summary' => string, 'error' => string]
     */
    public function summarizeText(string $text, string $prompt = ''): array {
        try {
            // Validate text length
            if (strlen($text) < 100) {
                return [
                    'success' => false,
                    'error' => 'Text is too short to summarize (minimum 100 characters)'
                ];
            }
            
            // Default prompt if not provided
            if (empty($prompt)) {
                $prompt = "Hãy tóm tắt văn bản sau một cách ngắn gọn và súc tích, giữ lại những ý chính quan trọng nhất:\n\n";
            }
            
            $fullPrompt = $prompt . $text;
            
            // Prepare request data for Gemini API
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt]
                        ]
                    ]
                ]
            ];
            
            // Make API request
            $url = $this->aiUrl . '?key=' . $this->apiKey;
            $response = $this->makeRequest($url, $data, 'POST', true);
            
            if ($response['success']) {
                $summary = $response['data']['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                return [
                    'success' => true,
                    'summary' => trim($summary)
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? 'Summarization failed'
                ];
            }
        } catch (Exception $e) {
            error_log("Error in summarizeText: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred during summarization'
            ];
        }
    }
    
    /**
     * Detect language of text
     * @param string $text Text to detect
     * @return array ['success' => bool, 'language' => string, 'confidence' => float]
     */
    public function detectLanguage(string $text): array {
        try {
            if (empty($text)) {
                return [
                    'success' => false,
                    'error' => 'Text cannot be empty'
                ];
            }
            
            $url = 'https://translation.googleapis.com/language/translate/v2/detect';
            $data = [
                'q' => $text,
                'key' => $this->apiKey
            ];
            
            $response = $this->makeRequest($url, $data, 'POST');
            
            if ($response['success']) {
                $detection = $response['data']['data']['detections'][0][0] ?? null;
                
                if ($detection) {
                    return [
                        'success' => true,
                        'language' => $detection['language'],
                        'confidence' => $detection['confidence'] ?? 0
                    ];
                }
            }
            
            return [
                'success' => false,
                'error' => 'Language detection failed'
            ];
        } catch (Exception $e) {
            error_log("Error in detectLanguage: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred during language detection'
            ];
        }
    }
    
    /**
     * Make HTTP request to Google API
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param string $method HTTP method (GET or POST)
     * @param bool $jsonBody Send as JSON body instead of form data
     * @return array Response data
     */
    private function makeRequest(string $endpoint, array $data, string $method = 'POST', bool $jsonBody = false): array {
        try {
            $ch = curl_init();
            
            if ($method === 'GET') {
                $endpoint .= '?' . http_build_query($data);
                curl_setopt($ch, CURLOPT_HTTPGET, true);
            } else {
                curl_setopt($ch, CURLOPT_POST, true);
                
                if ($jsonBody) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    $headers = ['Content-Type: application/json'];
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    $headers = ['Content-Type: application/x-www-form-urlencoded'];
                }
                
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            curl_close($ch);
            
            if ($error) {
                return [
                    'success' => false,
                    'error' => 'cURL Error: ' . $error
                ];
            }
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'error' => 'HTTP Error: ' . $httpCode
                ];
            }
            
            $responseData = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'Invalid JSON response'
                ];
            }
            
            // Check for API errors
            if (isset($responseData['error'])) {
                return [
                    'success' => false,
                    'error' => $responseData['error']['message'] ?? 'API Error'
                ];
            }
            
            return [
                'success' => true,
                'data' => $responseData
            ];
        } catch (Exception $e) {
            error_log("Error in makeRequest: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Request failed: ' . $e->getMessage()
            ];
        }
    }
}
