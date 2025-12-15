<?php
/**
 * Edge Text-to-Speech Service
 * Uses Microsoft Edge's TTS engine (free, high quality)
 * 
 * Requires Python 3 with edge-tts package:
 * pip install edge-tts
 */

class EdgeTTSService {
    
    private $pythonPath;
    private $scriptPath;
    private $uploadDir;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Python executable path
        $this->pythonPath = $this->findPythonPath();
        
        // Path to Python script
        $this->scriptPath = __DIR__ . '/../scripts/edge_tts_convert.py';
        
        // Upload directory for audio files
        $this->uploadDir = __DIR__ . '/../uploads/audio/';
        
        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Find Python executable path
     */
    private function findPythonPath() {
        // Try common Python paths
        $possiblePaths = [
            'D:/TrucQuanHoa/.venv/Scripts/python.exe',
            'C:/Python312/python.exe',
            'C:/Python311/python.exe',
            'C:/Python310/python.exe',
            'C:/Python39/python.exe',
            '/usr/bin/python3',
            '/usr/local/bin/python3',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                error_log("[Edge-TTS] Found Python at: $path");
                return $path;
            }
        }
        
        // Try to find python in PATH
        $output = [];
        $returnCode = 0;
        
        // Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('where python 2>&1', $output, $returnCode);
            if ($returnCode === 0 && !empty($output[0])) {
                error_log("[Edge-TTS] Found Python via 'where': " . $output[0]);
                return trim($output[0]);
            }
        } else {
            // Linux/Mac
            exec('which python3 2>&1', $output, $returnCode);
            if ($returnCode === 0 && !empty($output[0])) {
                error_log("[Edge-TTS] Found Python via 'which': " . $output[0]);
                return trim($output[0]);
            }
        }
        
        error_log("[Edge-TTS] Python not found, using default 'python'");
        return 'python';
    }
    
    /**
     * Get available voices for Edge TTS
     * 
     * @return array List of available voices
     */
    public function getAvailableVoices() {
        return [
            // Vietnamese voices
            [
                'id' => 'vi-VN-HoaiMyNeural',
                'name' => 'Hoài My (Nữ - Miền Nam)',
                'lang' => 'vi-VN',
                'gender' => 'Female',
                'engine' => 'edge-tts',
                'free' => true
            ],
            [
                'id' => 'vi-VN-NamMinhNeural',
                'name' => 'Nam Minh (Nam - Miền Bắc)',
                'lang' => 'vi-VN',
                'gender' => 'Male',
                'engine' => 'edge-tts',
                'free' => true
            ],
            
            // English voices
            [
                'id' => 'en-US-JennyNeural',
                'name' => 'Jenny (Female - US)',
                'lang' => 'en-US',
                'gender' => 'Female',
                'engine' => 'edge-tts',
                'free' => true
            ],
            [
                'id' => 'en-US-GuyNeural',
                'name' => 'Guy (Male - US)',
                'lang' => 'en-US',
                'gender' => 'Male',
                'engine' => 'edge-tts',
                'free' => true
            ],
            [
                'id' => 'en-GB-SoniaNeural',
                'name' => 'Sonia (Female - UK)',
                'lang' => 'en-GB',
                'gender' => 'Female',
                'engine' => 'edge-tts',
                'free' => true
            ],
            [
                'id' => 'en-GB-RyanNeural',
                'name' => 'Ryan (Male - UK)',
                'lang' => 'en-GB',
                'gender' => 'Male',
                'engine' => 'edge-tts',
                'free' => true
            ],
            
            // Japanese voices
            [
                'id' => 'ja-JP-NanamiNeural',
                'name' => 'Nanami (Female - Japan)',
                'lang' => 'ja-JP',
                'gender' => 'Female',
                'engine' => 'edge-tts',
                'free' => true
            ],
            [
                'id' => 'ja-JP-KeitaNeural',
                'name' => 'Keita (Male - Japan)',
                'lang' => 'ja-JP',
                'gender' => 'Male',
                'engine' => 'edge-tts',
                'free' => true
            ],
            
            // Korean voices
            [
                'id' => 'ko-KR-SunHiNeural',
                'name' => 'Sun-Hi (Female - Korea)',
                'lang' => 'ko-KR',
                'gender' => 'Female',
                'engine' => 'edge-tts',
                'free' => true
            ],
            [
                'id' => 'ko-KR-InJoonNeural',
                'name' => 'InJoon (Male - Korea)',
                'lang' => 'ko-KR',
                'gender' => 'Male',
                'engine' => 'edge-tts',
                'free' => true
            ],
            
            // Chinese voices
            [
                'id' => 'zh-CN-XiaoxiaoNeural',
                'name' => 'Xiaoxiao (Female - China)',
                'lang' => 'zh-CN',
                'gender' => 'Female',
                'engine' => 'edge-tts',
                'free' => true
            ],
            [
                'id' => 'zh-CN-YunxiNeural',
                'name' => 'Yunxi (Male - China)',
                'lang' => 'zh-CN',
                'gender' => 'Male',
                'engine' => 'edge-tts',
                'free' => true
            ],
        ];
    }
    
    /**
     * Convert text to speech using Edge TTS
     * 
     * @param string $text Text to convert
     * @param string $voice Voice ID (e.g., 'vi-VN-HoaiMyNeural')
     * @param float $speed Speed multiplier (0.5-2.0)
     * @return array Result with success status and audio data
     */
    public function textToSpeech($text, $voice = 'vi-VN-HoaiMyNeural', $speed = 1.0) {
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
            
            // Generate unique filename
            $filename = 'edge_' . time() . '_' . uniqid() . '.mp3';
            $outputPath = $this->uploadDir . $filename;
            
            // Increase execution time for long text
            $oldMaxExecTime = ini_get('max_execution_time');
            set_time_limit(120); // 2 minutes for long text
            
            // Use base64 encoding to avoid shell escaping issues with Vietnamese text
            $textBase64 = base64_encode($text);
            
            // Build command with --base64 flag
            $command = sprintf(
                '%s %s --base64 %s %s %s %s 2>&1',
                escapeshellcmd($this->pythonPath),
                escapeshellarg($this->scriptPath),
                escapeshellarg($textBase64),
                escapeshellarg($outputPath),
                escapeshellarg($voice),
                escapeshellarg($speed)
            );
            
            // Log for debugging
            error_log("[Edge-TTS] Python path: " . $this->pythonPath);
            error_log("[Edge-TTS] Script path: " . $this->scriptPath);
            error_log("[Edge-TTS] Text length: " . strlen($text));
            error_log("[Edge-TTS] Voice: $voice");
            error_log("[Edge-TTS] Output: $outputPath");
            error_log("[Edge-TTS] Command: $command");
            
            // Execute Python script
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            error_log("[Edge-TTS] Return code: $returnCode");
            error_log("[Edge-TTS] Output lines: " . count($output));
            error_log("[Edge-TTS] Full output: " . implode("\n", $output));
            
            // Restore execution time
            set_time_limit($oldMaxExecTime);
            
            // Parse output - get last line (JSON result)
            $outputStr = '';
            if (!empty($output)) {
                // Find JSON line (starts with {)
                foreach (array_reverse($output) as $line) {
                    if (strpos(trim($line), '{') === 0) {
                        $outputStr = $line;
                        break;
                    }
                }
            }
            
            if (empty($outputStr)) {
                error_log("Edge-TTS: No JSON output. Full output: " . implode("\n", $output));
                return [
                    'success' => false,
                    'error' => 'No response from Edge-TTS script'
                ];
            }
            
            $result = json_decode($outputStr, true);
            
            if ($result && $result['success']) {
                return [
                    'success' => true,
                    'file_path' => '/uploads/audio/' . $filename,
                    'filename' => $filename,
                    'engine' => 'edge-tts',
                    'voice' => $voice
                ];
            } else {
                $error = $result['error'] ?? 'Unknown error';
                error_log("Edge-TTS Error: " . $error);
                return [
                    'success' => false,
                    'error' => $error
                ];
            }
            
        } catch (Exception $e) {
            error_log("Edge-TTS Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Test if Edge TTS is available
     * 
     * @return array Test result
     */
    public function testConnection() {
        try {
            // Test Python and edge-tts availability
            $command = sprintf(
                '%s -c "import edge_tts; print(\'OK\')" 2>&1',
                escapeshellcmd($this->pythonPath)
            );
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && in_array('OK', $output)) {
                return [
                    'success' => true,
                    'message' => 'Edge-TTS is available'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Edge-TTS not installed. Run: pip install edge-tts'
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
