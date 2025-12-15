<?php
/**
 * MegaLLM API Service
 * Handles summarization and translation using MegaLLM API
 */

class MegaLLMService {
    private $apiKey;
    private $baseUrl;
    private $model;
    
    public function __construct() {
        $this->apiKey = $_ENV['MEGALLM_API_KEY'] ?? '';
        $this->baseUrl = $_ENV['MEGALLM_BASE_URL'] ?? 'https://ai.megallm.io/v1';
        $this->model = $_ENV['MEGALLM_MODEL'] ?? 'gpt-5';
        
        if (empty($this->apiKey)) {
            throw new Exception('MegaLLM API key not configured');
        }
    }
    
    /**
     * Summarize text using MegaLLM
     * @param string $text Text to summarize
     * @param string $lang Language (vi, en, or auto to detect)
     * @return string Summarized text
     */
    public function summarize($text, $lang = 'auto') {
        // Auto-detect language if not specified
        if ($lang === 'auto') {
            $lang = $this->detectLanguage($text);
        }
        
        // Use prompt that preserves original language
        $prompt = "Summarize the following text concisely in the SAME LANGUAGE as the original text. Keep the summary in the original language, do not translate:\n\n{$text}";
        
        return $this->chat($prompt);
    }
    
    /**
     * Detect language of text (simple detection)
     * @param string $text Text to detect
     * @return string Language code (vi or en)
     */
    private function detectLanguage($text) {
        // Vietnamese characters pattern
        $vietnamesePattern = '/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/ui';
        
        if (preg_match($vietnamesePattern, $text)) {
            return 'vi';
        }
        
        return 'en';
    }
    
    /**
     * Translate text using MegaLLM
     * @param string $text Text to translate
     * @param string $targetLang Target language code (vi, en, etc.)
     * @param string $sourceLang Source language code (optional)
     * @return string Translated text
     */
    public function translate($text, $targetLang, $sourceLang = 'auto') {
        $langNames = [
            'vi' => 'Vietnamese',
            'en' => 'English',
            'fr' => 'French',
            'de' => 'German',
            'es' => 'Spanish',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'zh' => 'Chinese'
        ];
        
        $targetLangName = $langNames[$targetLang] ?? $targetLang;
        
        $prompt = "Translate the following text to {$targetLangName}. Only return the translated text, nothing else:\n\n{$text}";
        
        return $this->chat($prompt);
    }
    
    /**
     * Send chat completion request to MegaLLM API
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return string Response text
     */
    private function chat($prompt, $options = []) {
        $url = $this->baseUrl . '/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1000
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('MegaLLM API request failed: ' . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception('MegaLLM API returned error: ' . $httpCode . ' - ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['choices'][0]['message']['content'])) {
            throw new Exception('Invalid response from MegaLLM API');
        }
        
        return trim($result['choices'][0]['message']['content']);
    }
    
    /**
     * Test API connection
     * @return bool True if connection successful
     */
    public function testConnection() {
        try {
            $response = $this->chat('Hello');
            return !empty($response);
        } catch (Exception $e) {
            error_log('MegaLLM connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}
