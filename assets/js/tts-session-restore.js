/**
 * TTS Page Manager
 * 
 * BEHAVIOR: TTS page always starts FRESH (no session restore)
 * Audio progress is saved to DATABASE (audio_history.position)
 * User can resume listening from History page modal
 */

(function() {
    'use strict';
    
    console.log('[TTS Page] TTS page always starts fresh - no session restore');
    
    /**
     * Clear TTS page to fresh state
     */
    window.clearTTSSession = function() {
        console.log('[TTS Page] Resetting to fresh state...');
        
        // Clear text input
        const textArea = document.getElementById('tts-text');
        if (textArea) {
            textArea.value = '';
            const charCount = document.getElementById('char-count');
            if (charCount) charCount.textContent = '0 / 5000';
        }
        
        // Reset voice to default
        const voiceSelect = document.getElementById('voice-select');
        if (voiceSelect) voiceSelect.selectedIndex = 0;
        
        // Reset speed
        const speedInput = document.getElementById('speed-input');
        const speedDisplay = document.getElementById('speed-display');
        if (speedInput) {
            speedInput.value = '1';
            if (speedDisplay) speedDisplay.textContent = '1x';
        }
        
        // Hide audio player
        const audioPlayer = document.getElementById('audio-player');
        if (audioPlayer) {
            audioPlayer.pause();
            audioPlayer.currentTime = 0;
            audioPlayer.src = '';
            audioPlayer.classList.add('hidden');
        }
        
        // Clear localStorage
        localStorage.removeItem('tts_session_data');
        
        console.log('[TTS Page] Page reset complete - ready for new text');
    };
    
    /**
     * Initialize fresh page
     */
    function init() {
        console.log('[TTS Page] Initializing fresh page...');
        
        // Clear old session data
        localStorage.removeItem('tts_session_data');
        
        // Ensure audio player is hidden
        const audioPlayer = document.getElementById('audio-player');
        if (audioPlayer) {
            audioPlayer.classList.add('hidden');
        }
        
        console.log('[TTS Page] Ready for new input');
    }
    
    // Initialize when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
