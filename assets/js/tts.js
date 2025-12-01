/**
 * Text-to-Speech JavaScript
 * Handles TTS conversion and audio playback
 */

let currentAudio = null;
let currentAudioId = null;
let positionUpdateInterval = null;

/**
 * Convert text to speech
 * @param {string} text - Text to convert
 * @param {string} voice - Voice name
 * @param {number} speed - Speed (0-2)
 * @returns {Promise<object>} Response data
 */
async function convertTextToSpeech(text, voice, speed) {
    try {
        const response = await apiRequest(`${API_BASE}/tts.php?action=convert`, {
            method: 'POST',
            body: JSON.stringify({
                text,
                voice,
                speed: parseFloat(speed),
                lang: 'vi-VN'
            })
        });

        if (response.success) {
            showToast('Chuyển đổi thành công!', 'success');
            return response.data;
        }

        return null;
    } catch (error) {
        console.error('TTS conversion error:', error);
        throw error;
    }
}

/**
 * Play audio from URL
 * @param {string} url - Audio URL
 * @param {number} audioId - Audio ID for position tracking
 * @param {number} startPosition - Start position in seconds
 */
function playAudio(url, audioId = null, startPosition = 0) {
    try {
        // Get the visible audio player
        const audioPlayer = document.getElementById('audio-player');

        if (!audioPlayer) {
            console.error('Audio player element not found');
            return;
        }

        // Stop current audio if playing (though with one player this is less critical, good for cleanup)
        if (currentAudio && currentAudio !== audioPlayer) {
            pauseAudio();
        }

        currentAudio = audioPlayer;
        currentAudioId = audioId;

        // Set source and load
        currentAudio.src = url;
        currentAudio.classList.remove('hidden');

        // Set start position
        if (startPosition > 0) {
            currentAudio.currentTime = startPosition;
        }

        // Play audio
        const playPromise = currentAudio.play();

        if (playPromise !== undefined) {
            playPromise.then(_ => {
                // Playback started
                updatePlayButton(true);
            })
                .catch(error => {
                    console.error('Auto-play prevented:', error);
                    // Auto-play might be blocked, user needs to interact
                });
        }

        // Clear existing interval if any
        if (positionUpdateInterval) {
            clearInterval(positionUpdateInterval);
            positionUpdateInterval = null;
        }

        // Track position every 5 seconds
        if (audioId) {
            positionUpdateInterval = setInterval(() => {
                if (currentAudio && !currentAudio.paused) {
                    const position = Math.floor(currentAudio.currentTime);
                    updatePosition(audioId, position);
                }
            }, 5000);
        }

        // Remove old listeners to prevent duplicates (cloning is a quick way, or just setting onplaying etc)
        // For simplicity in this context, we'll assign on-event handlers which overwrite previous ones

        // Handle audio end
        currentAudio.onended = function () {
            updatePlayButton(false); // Just update UI, don't need full pause logic as it stopped itself
            if (positionUpdateInterval) {
                clearInterval(positionUpdateInterval);
                positionUpdateInterval = null;
            }
            if (audioId) {
                updatePosition(audioId, 0); // Reset position
            }
        };

        // Handle errors
        currentAudio.onerror = function (e) {
            console.error('Audio playback error:', e);
            showToast('Không thể phát audio', 'error');
            updatePlayButton(false);
        };

        // Handle pause/play events from the native controls
        currentAudio.onplay = function () {
            updatePlayButton(true);
        };

        currentAudio.onpause = function () {
            updatePlayButton(false);
        };

    } catch (error) {
        console.error('Play audio error:', error);
        showToast('Không thể phát audio', 'error');
    }
}

/**
 * Pause current audio
 */
function pauseAudio() {
    if (currentAudio) {
        currentAudio.pause();
        updatePlayButton(false);
    }

    if (positionUpdateInterval) {
        clearInterval(positionUpdateInterval);
        positionUpdateInterval = null;
    }
}

/**
 * Stop current audio
 */
function stopAudio() {
    if (currentAudio) {
        currentAudio.pause();
        currentAudio.currentTime = 0;
        // Don't nullify currentAudio if it's the DOM element, just stop it
        // currentAudio = null; 
        currentAudioId = null;
        updatePlayButton(false);
    }

    if (positionUpdateInterval) {
        clearInterval(positionUpdateInterval);
        positionUpdateInterval = null;
    }
}

/**
 * Update playback position in database
 * @param {number} id - Audio ID
 * @param {number} position - Position in seconds
 */
async function updatePosition(id, position) {
    try {
        await apiRequest(`${API_BASE}/document.php?action=update-position`, {
            method: 'PATCH',
            body: JSON.stringify({ id, position })
        });
    } catch (error) {
        console.error('Update position error:', error);
        // Don't show error to user for position updates
    }
}

/**
 * Update play button UI
 * @param {boolean} isPlaying - Is audio playing
 */
function updatePlayButton(isPlaying) {
    const playBtn = document.getElementById('play-btn');
    if (playBtn) {
        if (isPlaying) {
            playBtn.innerHTML = '⏸️ Pause';
        } else {
            playBtn.innerHTML = '▶️ Play';
        }
    }
}

/**
 * Get available voices
 * @returns {Promise<array>} List of voices
 */
async function getVoices() {
    try {
        const response = await apiRequest(`${API_BASE}/tts.php?action=voices`);
        return response.data.voices || [];
    } catch (error) {
        console.error('Get voices error:', error);
        return [];
    }
}

/**
 * Populate voice selection dropdown
 */
async function handleVoiceSelection() {
    const voiceSelect = document.getElementById('voice-select');
    if (!voiceSelect) return;

    try {
        // Show loading
        voiceSelect.innerHTML = '<option>Đang tải...</option>';
        voiceSelect.disabled = true;

        const voices = await getVoices();

        voiceSelect.innerHTML = '';
        voiceSelect.disabled = false;

        if (voices.length === 0) {
            voiceSelect.innerHTML = '<option>Không có giọng đọc</option>';
            return;
        }

        voices.forEach(voice => {
            const option = document.createElement('option');
            option.value = voice.value;
            option.textContent = voice.label;
            voiceSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Handle voice selection error:', error);
        voiceSelect.innerHTML = '<option>Lỗi tải giọng đọc</option>';
        voiceSelect.disabled = false;
    }
}

/**
 * Handle TTS form submission
 */
function handleTTSForm() {
    const textInput = document.getElementById('tts-text');
    const voiceSelect = document.getElementById('voice-select');
    const speedInput = document.getElementById('speed-input');
    const convertBtn = document.getElementById('convert-btn');
    // const audioPlayer = document.getElementById('audio-player'); // Accessed in playAudio now

    // Initialize speed display
    if (speedInput) {
        const val = parseFloat(speedInput.value);
        const speedDisplay = document.getElementById('speed-display');
        if (speedDisplay) {
            speedDisplay.textContent = `${val}x`;
        }
    }

    // NOTE: Convert button event listener moved to dashboard.js with TTSButtonController
    // This prevents double event registration and implements proper debouncing
    const convertBtn = document.getElementById('convert-btn');
    if (!convertBtn) return;

    // DISABLED: Event listener now handled by TTSButtonController in dashboard.js
    /*
    convertBtn.addEventListener('click', async function () {
        const text = textInput?.value.trim();
        const voice = voiceSelect?.value || 'vi-VN-HoaiMyNeural';
        const speed = parseFloat(speedInput?.value || 1);

        // Validate
        if (!text) {
            showToast('Vui lòng nhập văn bản', 'error');
            return;
        }

        if (text.length > 5000) {
            showToast('Văn bản không được vượt quá 5000 ký tự', 'error');
            return;
        }

        // Convert
        setLoading(convertBtn, true);
        try {
            const result = await convertTextToSpeech(text, voice, speed);

            if (result && result.audio_url) {
                // Play audio using the centralized function
                playAudio(result.audio_url, result.audio_id);
            }
        } catch (error) {
            // Error handled
        } finally {
            setLoading(convertBtn, false);
        }
    });
    */

    // Handle play/pause button (custom button if exists)
    const playBtn = document.getElementById('play-btn');
    playBtn?.addEventListener('click', function () {
        if (currentAudio) {
            if (currentAudio.paused) {
                currentAudio.play();
            } else {
                currentAudio.pause();
            }
        }
    });

    // Handle stop button
    const stopBtn = document.getElementById('stop-btn');
    stopBtn?.addEventListener('click', function () {
        stopAudio();
    });

    // Update character count
    textInput?.addEventListener('input', function () {
        const charCount = document.getElementById('char-count');
        if (charCount) {
            charCount.textContent = `${this.value.length} / 5000`;
        }
    });

    // Update speed display
    speedInput?.addEventListener('input', function () {
        const speedDisplay = document.getElementById('speed-display');
        if (speedDisplay) {
            speedDisplay.textContent = `${this.value}x`;
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    handleVoiceSelection();
    handleTTSForm();
});
