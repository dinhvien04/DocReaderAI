<?php
/**
 * Demo: Audio Position Tracking
 * Test page for audio playback position saving/resuming
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check authentication
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '?page=login');
    exit;
}

$userId = $_SESSION['user_id'];

// Get recent audio history
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT id, text, audio_url, voice, lang, position, created_at
    FROM audio_history
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$audioItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo: Audio Position Tracking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .audio-player {
            width: 100%;
            height: 40px;
        }
        .position-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🎵 Demo: Audio Position Tracking</h1>
        <p class="text-gray-600 mb-8">Test tính năng lưu và khôi phục vị trí phát audio</p>
        
        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
            <h2 class="font-semibold text-blue-800 mb-2">📋 Hướng dẫn test:</h2>
            <ol class="list-decimal list-inside text-blue-700 space-y-1 text-sm">
                <li>Phát một audio và tua đến vị trí bất kỳ</li>
                <li>Nhấn pause hoặc refresh trang</li>
                <li>Quay lại - audio sẽ tiếp tục từ vị trí đã lưu</li>
                <li>Khi audio phát xong, position sẽ reset về 0</li>
            </ol>
        </div>
        
        <!-- Audio List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">🎧 Audio History</h2>
            </div>
            
            <div class="divide-y divide-gray-200">
                <?php if (empty($audioItems)): ?>
                <div class="p-8 text-center text-gray-500">
                    <p class="text-4xl mb-2">📭</p>
                    <p>Chưa có audio nào. Hãy tạo audio từ TTS trước.</p>
                    <a href="<?= BASE_URL ?>?page=dashboard" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Đi đến Dashboard
                    </a>
                </div>
                <?php else: ?>
                    <?php foreach ($audioItems as $item): ?>
                    <div class="p-6 hover:bg-gray-50 transition" id="audio-row-<?= $item['id'] ?>">
                        <div class="flex items-start gap-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <!-- Text preview -->
                                <p class="text-gray-800 text-sm mb-2 line-clamp-2">
                                    <?= htmlspecialchars(mb_substr($item['text'], 0, 150)) ?>...
                                </p>
                                
                                <!-- Meta info -->
                                <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd"/>
                                        </svg>
                                        <?= htmlspecialchars($item['voice']) ?>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                        </svg>
                                        <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
                                    </span>
                                    
                                    <!-- Position badge -->
                                    <?php if ($item['position'] > 0): ?>
                                    <span class="position-badge inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                        </svg>
                                        Tiếp tục từ <?= gmdate("i:s", $item['position']) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Chưa nghe
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Audio Player -->
                                <audio 
                                    id="audio-<?= $item['id'] ?>"
                                    class="audio-player"
                                    controls
                                    preload="metadata"
                                    data-audio-id="<?= $item['id'] ?>"
                                    data-saved-position="<?= $item['position'] ?>"
                                >
                                    <source src="<?= htmlspecialchars($item['audio_url']) ?>" type="audio/mpeg">
                                    Trình duyệt không hỗ trợ audio
                                </audio>
                                
                                <!-- Debug info -->
                                <div class="mt-2 text-xs text-gray-400" id="debug-<?= $item['id'] ?>">
                                    ID: <?= $item['id'] ?> | Saved Position: <span class="saved-pos"><?= $item['position'] ?>s</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Debug Console -->
        <div class="mt-8 bg-gray-900 rounded-lg p-4">
            <h3 class="text-green-400 font-mono text-sm mb-2">📟 Debug Console</h3>
            <div id="debug-console" class="font-mono text-xs text-gray-300 h-40 overflow-y-auto">
                <p class="text-gray-500">[Ready] Waiting for audio events...</p>
            </div>
        </div>
        
        <!-- Back link -->
        <div class="mt-6 text-center">
            <a href="<?= BASE_URL ?>?page=dashboard" class="text-blue-600 hover:text-blue-800">
                ← Quay lại Dashboard
            </a>
        </div>
    </div>
    
    <!-- Load Audio Manager -->
    <script src="<?= BASE_URL ?>/assets/js/audio-player-manager.js"></script>
    
    <script>
    // Debug logger
    const debugConsole = document.getElementById('debug-console');
    function log(message, type = 'info') {
        const time = new Date().toLocaleTimeString();
        const colors = {
            info: 'text-blue-400',
            success: 'text-green-400',
            warning: 'text-yellow-400',
            error: 'text-red-400'
        };
        const p = document.createElement('p');
        p.className = colors[type] || colors.info;
        p.textContent = `[${time}] ${message}`;
        debugConsole.appendChild(p);
        debugConsole.scrollTop = debugConsole.scrollHeight;
    }
    
    // Override console.log for debugging
    const originalLog = console.log;
    console.log = function(...args) {
        originalLog.apply(console, args);
        if (args[0] && typeof args[0] === 'string' && args[0].includes('[AudioManager]')) {
            log(args.join(' '), 'info');
        }
    };
    
    // Initialize audio players
    document.addEventListener('DOMContentLoaded', function() {
        log('Initializing audio players...', 'info');
        
        const audioElements = document.querySelectorAll('audio[data-audio-id]');
        
        audioElements.forEach(audio => {
            const audioId = parseInt(audio.dataset.audioId);
            const savedPosition = parseInt(audio.dataset.savedPosition) || 0;
            
            // Register with AudioPlayerManager
            if (window.audioManager) {
                window.audioManager.register(audio, audioId, savedPosition);
                log(`Registered audio ${audioId} with saved position ${savedPosition}s`, 'success');
            }
            
            // Add event listeners for debugging
            audio.addEventListener('play', () => {
                log(`▶️ Audio ${audioId} started playing`, 'info');
            });
            
            audio.addEventListener('pause', () => {
                const pos = Math.floor(audio.currentTime);
                log(`⏸️ Audio ${audioId} paused at ${pos}s`, 'warning');
            });
            
            audio.addEventListener('ended', () => {
                log(`⏹️ Audio ${audioId} ended - position reset to 0`, 'success');
            });
            
            audio.addEventListener('seeked', () => {
                const pos = Math.floor(audio.currentTime);
                log(`⏩ Audio ${audioId} seeked to ${pos}s`, 'info');
            });
            
            audio.addEventListener('loadedmetadata', () => {
                if (savedPosition > 0 && savedPosition < audio.duration) {
                    log(`🔄 Audio ${audioId} restored to ${savedPosition}s`, 'success');
                }
            });
        });
        
        log(`Total ${audioElements.length} audio players initialized`, 'success');
    });
    </script>
</body>
</html>
