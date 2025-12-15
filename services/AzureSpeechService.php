<?php
/**
 * Azure Speech Service
 * Handles Text-to-Speech conversion using Microsoft Azure Cognitive Services
 */

class AzureSpeechService {
    private $subscriptionKey;
    private $subscriptionKey2;
    private $region;
    private $endpoint;
    
    public function __construct(string $subscriptionKey, string $region, string $subscriptionKey2 = '') {
        $this->subscriptionKey = $subscriptionKey;
        $this->subscriptionKey2 = $subscriptionKey2;
        $this->region = $region;
        $this->endpoint = "https://{$region}.tts.speech.microsoft.com/cognitiveservices/v1";
    }
    
    /**
     * Convert text to speech
     * @param string $text Text to convert
     * @param string $voice Voice name (e.g., vi-VN-HoaiMyNeural)
     * @param float $rate Speech rate (0.5 to 2.0, default 1.0)
     * @param string $format Audio format (audio-16khz-128kbitrate-mono-mp3, audio-24khz-48kbitrate-mono-mp3)
     * @return array ['success' => bool, 'audio_data' => string, 'error' => string]
     */
    public function textToSpeech(string $text, string $voice = 'vi-VN-HoaiMyNeural', float $rate = 1.0, string $format = 'audio-24khz-48kbitrate-mono-mp3'): array {
        try {
            // Validate text length (use mb_strlen for UTF-8 characters)
            $textLength = mb_strlen($text, 'UTF-8');
            if ($textLength > MAX_TEXT_LENGTH) {
                return [
                    'success' => false,
                    'error' => 'Text exceeds maximum length of ' . MAX_TEXT_LENGTH . ' characters (current: ' . $textLength . ')'
                ];
            }
            
            // Ensure UTF-8 encoding
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
            
            // Calculate rate percentage (Azure uses percentage: 0.5 = -50%, 2.0 = +100%)
            $ratePercent = ($rate - 1.0) * 100;
            $rateStr = ($ratePercent >= 0 ? '+' : '') . number_format($ratePercent, 0) . '%';
            
            // Build SSML
            $ssml = $this->buildSSML($text, $voice, $rateStr);
            
            // Make API request
            $response = $this->makeRequest($ssml, $format);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'audio_data' => $response['audio_data'],
                    'message' => 'Text converted successfully'
                ];
            } else {
                // Try with secondary key if primary fails
                if (!empty($this->subscriptionKey2)) {
                    $response = $this->makeRequest($ssml, $format, true);
                    if ($response['success']) {
                        return [
                            'success' => true,
                            'audio_data' => $response['audio_data'],
                            'message' => 'Text converted successfully (using secondary key)'
                        ];
                    }
                }
                
                return [
                    'success' => false,
                    'error' => $response['error'] ?? 'Failed to convert text to speech'
                ];
            }
        } catch (Exception $e) {
            error_log("Error in Azure textToSpeech: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred during text-to-speech conversion'
            ];
        }
    }
    
    /**
     * Build SSML (Speech Synthesis Markup Language)
     * @param string $text Text content
     * @param string $voice Voice name
     * @param string $rate Speech rate
     * @return string SSML XML
     */
    private function buildSSML(string $text, string $voice, string $rate): string {
        $escapedText = htmlspecialchars($text, ENT_XML1, 'UTF-8');
        
        return <<<SSML
<speak version='1.0' xml:lang='vi-VN'>
    <voice xml:lang='vi-VN' name='{$voice}'>
        <prosody rate='{$rate}'>
            {$escapedText}
        </prosody>
    </voice>
</speak>
SSML;
    }
    
    /**
     * Get available voices
     * @return array List of available voices
     */
    public function getAvailableVoices(): array {
        return [
            // Vietnamese Neural Voices
            [
                'value' => 'vi-VN-HoaiMyNeural',
                'label' => 'Hoài My (Nữ - Miền Bắc)',
                'gender' => 'female',
                'type' => 'neural',
                'lang' => 'vi-VN'
            ],
            [
                'value' => 'vi-VN-NamMinhNeural',
                'label' => 'Nam Minh (Nam - Miền Bắc)',
                'gender' => 'male',
                'type' => 'neural',
                'lang' => 'vi-VN'
            ],
            
            // English US Neural Voices
            [
                'value' => 'en-US-JennyNeural',
                'label' => 'Jenny (Female - US)',
                'gender' => 'female',
                'type' => 'neural',
                'lang' => 'en-US'
            ],
            [
                'value' => 'en-US-GuyNeural',
                'label' => 'Guy (Male - US)',
                'gender' => 'male',
                'type' => 'neural',
                'lang' => 'en-US'
            ],
            [
                'value' => 'en-US-AriaNeural',
                'label' => 'Aria (Female - US)',
                'gender' => 'female',
                'type' => 'neural',
                'lang' => 'en-US'
            ],
            [
                'value' => 'en-US-DavisNeural',
                'label' => 'Davis (Male - US)',
                'gender' => 'male',
                'type' => 'neural',
                'lang' => 'en-US'
            ]
        ];
    }
    
    /**
     * Validate API connection
     * @return bool
     */
    public function validateConnection(): bool {
        try {
            $result = $this->textToSpeech('Test', 'vi-VN-HoaiMyNeural', 1.0);
            return $result['success'];
        } catch (Exception $e) {
            error_log("Error in validateConnection: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Test connection to Azure Speech Service
     * @return array ['success' => bool, 'message' => string, 'error' => string]
     */
    public function testConnection(): array {
        try {
            // Try to get access token by making a simple request
            $testText = "Test";
            $result = $this->textToSpeech($testText, 'vi-VN-HoaiMyNeural', 1.0);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Azure Speech Service connection successful'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Connection test failed'
                ];
            }
        } catch (Exception $e) {
            error_log("Error in testConnection: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Make HTTP request to Azure Speech API
     * @param string $ssml SSML content
     * @param string $format Audio format
     * @param bool $useSecondaryKey Use secondary key
     * @return array Response data
     */
    private function makeRequest(string $ssml, string $format, bool $useSecondaryKey = false): array {
        try {
            $key = $useSecondaryKey ? $this->subscriptionKey2 : $this->subscriptionKey;
            
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $ssml,
                CURLOPT_HTTPHEADER => [
                    'Ocp-Apim-Subscription-Key: ' . $key,
                    'Content-Type: application/ssml+xml',
                    'X-Microsoft-OutputFormat: ' . $format,
                    'User-Agent: DocReaderAI'
                ],
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
                // Try to parse error message
                $errorMsg = 'HTTP Error: ' . $httpCode;
                if ($response) {
                    $jsonError = json_decode($response, true);
                    if ($jsonError && isset($jsonError['error']['message'])) {
                        $errorMsg .= ' - ' . $jsonError['error']['message'];
                    }
                }
                
                return [
                    'success' => false,
                    'error' => $errorMsg
                ];
            }
            
            // Response is binary audio data
            return [
                'success' => true,
                'audio_data' => $response
            ];
        } catch (Exception $e) {
            error_log("Error in makeRequest: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Request failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Save audio data to file
     * @param string $audioData Binary audio data
     * @param string $filename Filename (without path)
     * @return array ['success' => bool, 'file_path' => string, 'error' => string]
     */
    public function saveAudioFile(string $audioData, string $filename): array {
        try {
            $uploadDir = __DIR__ . '/../uploads/audio/';
            
            // Create directory if not exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filePath = $uploadDir . $filename;
            
            // Save file
            $result = file_put_contents($filePath, $audioData);
            
            if ($result === false) {
                return [
                    'success' => false,
                    'error' => 'Failed to save audio file'
                ];
            }
            
            return [
                'success' => true,
                'file_path' => '/uploads/audio/' . $filename
            ];
        } catch (Exception $e) {
            error_log("Error in saveAudioFile: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to save audio file: ' . $e->getMessage()
            ];
        }
    }
}
