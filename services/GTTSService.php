<?php
/**
 * Google Text-to-Speech Service (gTTS)
 * Free alternative to Azure TTS
 * 
 * Requires Python 3 with gtts and pydub packages:
 * pip install gtts pydub
 */

class GTTSService {
    
    private $pythonPath;
    private $scriptPath;
    private $uploadDir;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Python executable path
        // Option 1: Use virtual environment Python (recommended)
        // Option 2: Use system Python if installed globally
        $this->pythonPath = $this->findPythonPath();
        
        // Path to Python script
        $this->scriptPath = __DIR__ . '/../scripts/gtts_convert.py';
        
        // Upload directory for audio files
        $this->uploadDir = __DIR__ . '/../uploads/audio/';
        
        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Find Python executable path
     * Checks multiple locations for Python
     */
    private function findPythonPath() {
        // ========================================
        // CẤU HÌNH ĐƯỜNG DẪN PYTHON Ở ĐÂY
        // ========================================
        // Đường dẫn cố định đến Python trong virtual environment
        // Thay đổi đường dẫn này nếu cần
        $fixedPythonPath = 'D:/TrucQuanHoa/.venv/Scripts/python.exe';
        
        // Kiểm tra đường dẫn cố định trước
        if (file_exists($fixedPythonPath)) {
            error_log("[gTTS] Using fixed Python path: " . $fixedPythonPath);
            return $fixedPythonPath;
        }
        
        error_log("[gTTS] Fixed Python path not found: " . $fixedPythonPath);
        
        // Fallback: tìm trong các vị trí khác
        $possiblePaths = [
            // Common Python installation paths on Windows
            'C:/Python311/python.exe',
            'C:/Python310/python.exe',
            'C:/Python39/python.exe',
            'C:/Users/' . get_current_user() . '/AppData/Local/Programs/Python/Python311/python.exe',
            'C:/Users/' . get_current_user() . '/AppData/Local/Programs/Python/Python310/python.exe',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                error_log("[gTTS] Found Python at: " . $path);
                return $path;
            }
        }
        
        // Last resort - try system PATH
        error_log("[gTTS] No Python found, trying 'python' command");
        return 'python';
    }
    
    /**
     * Get available voices for gTTS
     * 
     * @return array List of available voices
     */
    public function getAvailableVoices() {
        return [
            // Vietnamese
            [
                'id' => 'gtts-vi',
                'name' => 'Google Vietnamese',
                'lang' => 'vi',
                'gender' => 'Female',
                'engine' => 'gtts',
                'free' => true
            ],
            // English
            [
                'id' => 'gtts-en',
                'name' => 'Google English',
                'lang' => 'en',
                'gender' => 'Female',
                'engine' => 'gtts',
                'free' => true
            ],
            // Japanese
            [
                'id' => 'gtts-ja',
                'name' => 'Google Japanese',
                'lang' => 'ja',
                'gender' => 'Female',
                'engine' => 'gtts',
                'free' => true
            ],
            // Korean
            [
                'id' => 'gtts-ko',
                'name' => 'Google Korean',
                'lang' => 'ko',
                'gender' => 'Female',
                'engine' => 'gtts',
                'free' => true
            ],
            // Chinese
            [
                'id' => 'gtts-zh',
                'name' => 'Google Chinese',
                'lang' => 'zh-CN',
                'gender' => 'Female',
                'engine' => 'gtts',
                'free' => true
            ],
            // French
            [
                'id' => 'gtts-fr',
                'name' => 'Google French',
                'lang' => 'fr',
                'gender' => 'Female',
                'engine' => 'gtts',
                'free' => true
            ],
            // German
            [
                'id' => 'gtts-de',
                'name' => 'Google German',
                'lang' => 'de',
                'gender' => 'Female',
                'engine' => 'gtts',
                'free' => true
            ],
            // Spanish
            [
                'id' => 'gtts-es',
                'name' => 'Google Spanish',
                'lang' => 'es',
                'gender' => 'Female',
                'engine' => 'gtts',
                'free' => true
            ],
        ];
    }
    
    /**
     * Convert text to speech using gTTS
     * 
     * @param string $text Text to convert
     * @param string $voice Voice ID (e.g., 'gtts-vi')
     * @param float $speed Speed multiplier (0.5-2.0)
     * @return array Result with success status and audio data
     */
    public function textToSpeech($text, $voice = 'gtts-vi', $speed = 1.0) {
        try {
            // Validate text
            if (empty(trim($text))) {
                return [
                    'success' => false,
                    'error' => 'Text is empty'
                ];
            }
            
            if (mb_strlen($text, 'UTF-8') > 10000) {
                return [
                    'success' => false,
                    'error' => 'Text too long (max 10000 characters)'
                ];
            }
            
            // Extract language from voice ID
            $lang = $this->extractLangFromVoice($voice);
            
            // Generate unique filename
            $filename = 'gtts_' . time() . '_' . uniqid() . '.mp3';
            $outputPath = $this->uploadDir . $filename;
            
            // Use base64 encoding to avoid shell escaping issues with special characters
            $textBase64 = base64_encode($text);
            
            // Build command with --base64 flag
            $command = sprintf(
                '%s %s --base64 %s %s %s %s 2>&1',
                escapeshellcmd($this->pythonPath),
                escapeshellarg($this->scriptPath),
                escapeshellarg($textBase64),
                escapeshellarg($outputPath),
                escapeshellarg($lang),
                escapeshellarg($speed)
            );
            
            error_log("[gTTS] Python: " . $this->pythonPath);
            error_log("[gTTS] Script: " . $this->scriptPath);
            error_log("[gTTS] Text length: " . mb_strlen($text, 'UTF-8'));
            error_log("[gTTS] Output: " . $outputPath);
            error_log("[gTTS] Lang: " . $lang);
            error_log("[gTTS] Using base64 encoding for text");
            
            // Execute Python script
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            error_log("[gTTS] Return code: " . $returnCode);
            error_log("[gTTS] Output lines: " . count($output));
            error_log("[gTTS] Full output: " . implode("\n", $output));
            
            // Parse output - find JSON line
            $outputStr = '';
            foreach (array_reverse($output) as $line) {
                if (strpos(trim($line), '{') === 0) {
                    $outputStr = $line;
                    break;
                }
            }
            
            if (empty($outputStr)) {
                error_log("[gTTS] No JSON output found");
                return [
                    'success' => false,
                    'error' => 'No response from gTTS script. Output: ' . implode("\n", $output)
                ];
            }
            
            $result = json_decode($outputStr, true);
            
            if ($result && $result['success']) {
                return [
                    'success' => true,
                    'file_path' => '/uploads/audio/' . $filename,
                    'filename' => $filename,
                    'engine' => 'gtts',
                    'voice' => $voice,
                    'lang' => $lang
                ];
            } else {
                $error = $result['error'] ?? 'Unknown error';
                error_log("gTTS Error: " . $error);
                return [
                    'success' => false,
                    'error' => $error
                ];
            }
            
        } catch (Exception $e) {
            error_log("gTTS Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Extract language code from voice ID
     * 
     * @param string $voice Voice ID (e.g., 'gtts-vi')
     * @return string Language code
     */
    private function extractLangFromVoice($voice) {
        $langMap = [
            'gtts-vi' => 'vi',
            'gtts-en' => 'en',
            'gtts-ja' => 'ja',
            'gtts-ko' => 'ko',
            'gtts-zh' => 'zh-CN',
            'gtts-fr' => 'fr',
            'gtts-de' => 'de',
            'gtts-es' => 'es',
        ];
        
        return $langMap[$voice] ?? 'vi';
    }
    
    /**
     * Test if gTTS is available
     * 
     * @return array Test result
     */
    public function testConnection() {
        try {
            // Test Python and gtts availability
            $command = sprintf(
                '%s -c "from gtts import gTTS; print(\'OK\')" 2>&1',
                escapeshellcmd($this->pythonPath)
            );
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && in_array('OK', $output)) {
                return [
                    'success' => true,
                    'message' => 'gTTS is available'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'gTTS not installed. Run: pip install gtts pydub'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
