<?php
/**
 * Audio Player Component
 * Reusable audio player with position tracking
 * 
 * Usage:
 * <?php 
 * $audioData = ['id' => 123, 'audio_url' => '...', 'position' => 45, 'text' => '...'];
 * include 'views/components/audio-player.php';
 * ?>
 * 
 * Required: $audioData array with keys: id, audio_url, position (optional), text (optional)
 */

// Ensure $audioData is set
if (!isset($audioData) || !is_array($audioData)) {
    return;
}

$audioId = (int)($audioData['id'] ?? 0);
$audioUrl = htmlspecialchars($audioData['audio_url'] ?? '');
$savedPosition = (int)($audioData['position'] ?? 0);
$text = htmlspecialchars($audioData['text'] ?? '');
$voice = htmlspecialchars($audioData['voice'] ?? 'Unknown');

if (!$audioId || !$audioUrl) {
    return;
}
?>

<!-- Audio Player Component -->
<div class="audio-player-wrapper" data-audio-id="<?= $audioId ?>">
    <?php if ($text): ?>
    <div class="audio-text mb-2 text-sm text-gray-600 line-clamp-2">
        <?= $text ?>
    </div>
    <?php endif; ?>
    
    <div class="audio-controls flex items-center gap-3">
        <audio 
            id="audio-<?= $audioId ?>"
            class="audio-player w-full"
            controls
            preload="metadata"
            data-audio-id="<?= $audioId ?>"
            data-saved-position="<?= $savedPosition ?>"
        >
            <source src="<?= $audioUrl ?>" type="audio/mpeg">
            Trình duyệt không hỗ trợ audio
        </audio>
    </div>
    
    <?php if ($savedPosition > 0): ?>
    <div class="audio-resume-badge mt-1">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
            </svg>
            Tiếp tục từ <?= gmdate("i:s", $savedPosition) ?>
        </span>
    </div>
    <?php endif; ?>
</div>

<script>
// Auto-register this audio player when DOM is ready
(function() {
    const audioId = <?= $audioId ?>;
    const savedPosition = <?= $savedPosition ?>;
    
    function initPlayer() {
        const audio = document.getElementById('audio-' + audioId);
        if (audio && window.audioManager) {
            window.audioManager.register(audio, audioId, savedPosition);
        } else if (audio && window.audioTracker) {
            // Fallback to old tracker
            window.audioTracker.track(audio, audioId, 'history');
            if (savedPosition > 0) {
                window.audioTracker.restorePosition(audio, savedPosition);
            }
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPlayer);
    } else {
        initPlayer();
    }
})();
</script>
