/**
 * TTS Double Click Fix
 * Simple solution to prevent double requests
 */

(function() {
    'use strict';
    
    console.log('[TTS-Fix] Loading TTS double-click fix...');
    
    // Wait for DOM to be ready
    function initTTSFix() {
        const convertBtn = document.getElementById('convert-btn');
        const textArea = document.getElementById('tts-text');
        const voiceSelect = document.getElementById('voice-select');
        const audioPlayer = document.getElementById('audio-player');
        
        if (!convertBtn) {
            console.warn('[TTS-Fix] Convert button not found');
            return;
        }
        
        console.log('[TTS-Fix] Initializing fix for convert button');
        
        // Remove all existing event listeners by cloning the button
        const newBtn = convertBtn.cloneNode(true);
        convertBtn.parentNode.replaceChild(newBtn, convertBtn);
        
        // State management
        let isProcessing = false;
        let lastClickTime = 0;
        const debounceDelay = 500;
        
        // Single event listener with debouncing
        newBtn.addEventListener('click', async function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            console.log('[TTS-Fix] Button clicked');
            
            // Check debounce
            const now = Date.now();
            if (isProcessing || (now - lastClickTime) < debounceDelay) {
                console.log('[TTS-Fix] Request blocked - processing:', isProcessing, 'timeSince:', now - lastClickTime);
                return;
            }
            
            const text = textArea?.value?.trim();
            const voice = voiceSelect?.value || 'vi-VN-HoaiMyNeural';
            
            // Validation
            if (!text) {
                showToast('Vui lòng nhập văn bản', 'error');
                return;
            }
            
            if (text.length > 5000) {
                showToast('Văn bản quá dài (tối đa 5000 ký tự)', 'error');
                return;
            }
            
            // Set processing state
            isProcessing = true;
            lastClickTime = now;
            newBtn.disabled = true;
            newBtn.innerHTML = 'Đang xử lý...';
            
            console.log('[TTS-Fix] Starting conversion...');
            
            try {
                const response = await apiRequest(`${API_BASE}/tts.php?action=convert`, {
                    method: 'POST',
                    body: JSON.stringify({ text, voice, speed: 1.0 })
                });
                
                console.log('[TTS-Fix] API response:', response);
                console.log('[TTS-Fix] Audio ID:', response.data?.audio_id);
                console.log('[TTS-Fix] Audio URL:', response.data?.audio_url);
                
                if (response.success && response.data.audio_url) {
                    console.log('[TTS-Fix] Conversion successful, audio_id:', response.data.audio_id);
                    
                    if (audioPlayer) {
                        audioPlayer.src = response.data.audio_url;
                        audioPlayer.classList.remove('hidden');
                        
                        try {
                            await audioPlayer.play();
                            showToast('Chuyển đổi thành công', 'success');
                        } catch (playError) {
                            console.error('[TTS-Fix] Audio play error:', playError);
                            showToast('Không thể phát audio: ' + playError.message, 'error');
                        }
                    }
                    
                    // Reload history to show new audio
                    console.log('[TTS-Fix] Reloading history...');
                    setTimeout(() => {
                        // Try multiple ways to reload history
                        if (typeof recentActivity !== 'undefined' && recentActivity && recentActivity.loadActivities) {
                            console.log('[TTS-Fix] Reloading via recentActivity');
                            recentActivity.loadActivities();
                        } else if (typeof window.RecentActivity !== 'undefined') {
                            console.log('[TTS-Fix] Creating new RecentActivity instance');
                            const activity = new window.RecentActivity();
                            activity.loadActivities();
                        } else {
                            console.warn('[TTS-Fix] Could not reload history - recentActivity not available');
                        }
                    }, 500);
                } else {
                    console.error('[TTS-Fix] Invalid response:', response);
                    showToast('Phản hồi không hợp lệ', 'error');
                }
            } catch (error) {
                console.error('[TTS-Fix] Conversion error:', error);
                showToast('Không thể chuyển đổi văn bản: ' + error.message, 'error');
            } finally {
                // Always restore button state
                isProcessing = false;
                newBtn.disabled = false;
                newBtn.innerHTML = 'Chuyển đổi';
                console.log('[TTS-Fix] Button state restored');
            }
        });
        
        console.log('[TTS-Fix] Fix applied successfully');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTTSFix);
    } else {
        initTTSFix();
    }
})();