/**
 * Audio Player Manager
 * Unified module for managing audio playback with position saving/resuming
 * 
 * Features:
 * 1. Auto-save position every 5 seconds during playback
 * 2. Save on pause, seek, tab switch, page unload
 * 3. Resume from saved position on load
 * 4. Reset position when audio ends
 * 5. Handle multiple audio players (pause others when one plays)
 */

(function() {
    'use strict';

    const API_BASE = '/KK/api';
    
    class AudioPlayerManager {
        constructor(options = {}) {
            // Configuration
            this.config = {
                autoSaveInterval: options.autoSaveInterval || 5000, // 5 seconds
                minPositionToSave: options.minPositionToSave || 1,  // Min 1 second
                debounceDelay: options.debounceDelay || 500,        // Debounce for seek
                apiEndpoint: options.apiEndpoint || `${API_BASE}/update_position.php`,
                getItemEndpoint: options.getItemEndpoint || `${API_BASE}/get_history_item.php`
            };
            
            // State
            this.players = new Map();           // audioId -> { element, intervalId, lastSaved }
            this.debounceTimers = new Map();    // audioId -> timerId
            this.currentlyPlaying = null;       // Currently playing audioId
            
            // Initialize global listeners
            this.initGlobalListeners();
            
            console.log('[AudioManager] Initialized with config:', this.config);
        }
        
        /**
         * Initialize global event listeners
         */
        initGlobalListeners() {
            // Save all positions before page unload
            window.addEventListener('beforeunload', () => {
                this.saveAllPositions('beforeunload');
            });
            
            // Save when tab becomes hidden
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.saveAllPositions('visibilitychange');
                }
            });
            
            // Handle offline/online for localStorage fallback
            window.addEventListener('offline', () => {
                console.log('[AudioManager] Offline - will use localStorage');
            });
            
            window.addEventListener('online', () => {
                this.syncFromLocalStorage();
            });
        }
        
        /**
         * Register an audio element for tracking
         * @param {HTMLAudioElement} audioElement - The audio element
         * @param {number} audioId - Database ID of the audio
         * @param {number} savedPosition - Previously saved position (optional)
         */
        register(audioElement, audioId, savedPosition = 0) {
            if (!audioElement || !audioId) {
                console.warn('[AudioManager] Invalid audio element or ID');
                return;
            }
            
            // Unregister if already registered
            if (this.players.has(audioId)) {
                this.unregister(audioId);
            }
            
            // Store player info
            this.players.set(audioId, {
                element: audioElement,
                intervalId: null,
                lastSaved: savedPosition
            });
            
            // Attach event listeners
            this.attachListeners(audioElement, audioId);
            
            // Restore position if available
            if (savedPosition > 0) {
                this.restorePosition(audioElement, savedPosition);
            }
            
            console.log(`[AudioManager] Registered audio ${audioId}, saved position: ${savedPosition}s`);
        }
        
        /**
         * Attach event listeners to audio element
         */
        attachListeners(audio, audioId) {
            // PLAY - Start auto-save interval and pause other players
            audio.addEventListener('play', () => {
                this.onPlay(audioId);
            });
            
            // PAUSE - Save current position
            audio.addEventListener('pause', () => {
                this.onPause(audioId);
            });
            
            // ENDED - Reset position to 0
            audio.addEventListener('ended', () => {
                this.onEnded(audioId);
            });
            
            // SEEKED - Save position after seeking (debounced)
            audio.addEventListener('seeked', () => {
                this.onSeeked(audioId);
            });
            
            // TIMEUPDATE - Backup save every 10 seconds
            audio.addEventListener('timeupdate', () => {
                this.onTimeUpdate(audioId);
            });
            
            // ERROR - Log errors
            audio.addEventListener('error', (e) => {
                console.error(`[AudioManager] Audio ${audioId} error:`, e);
            });
        }
        
        /**
         * Handle play event
         */
        onPlay(audioId) {
            const player = this.players.get(audioId);
            if (!player) return;
            
            console.log(`[AudioManager] Audio ${audioId} started playing`);
            
            // Pause other players and save their positions
            this.pauseOthers(audioId);
            
            // Set as currently playing
            this.currentlyPlaying = audioId;
            
            // Start auto-save interval
            this.startAutoSave(audioId);
        }
        
        /**
         * Handle pause event
         */
        onPause(audioId) {
            const player = this.players.get(audioId);
            if (!player) return;
            
            const position = Math.floor(player.element.currentTime);
            console.log(`[AudioManager] Audio ${audioId} paused at ${position}s`);
            
            // Stop auto-save
            this.stopAutoSave(audioId);
            
            // Save position
            if (this.shouldSave(player.element, position)) {
                this.savePosition(audioId, position, 'pause');
            }
            
            // Clear currently playing if this was it
            if (this.currentlyPlaying === audioId) {
                this.currentlyPlaying = null;
            }
        }
        
        /**
         * Handle ended event
         */
        onEnded(audioId) {
            const player = this.players.get(audioId);
            if (!player) return;
            
            console.log(`[AudioManager] Audio ${audioId} ended - resetting position`);
            
            // Stop auto-save
            this.stopAutoSave(audioId);
            
            // Reset position to 0
            this.savePosition(audioId, 0, 'ended');
            player.lastSaved = 0;
            
            // Clear currently playing
            if (this.currentlyPlaying === audioId) {
                this.currentlyPlaying = null;
            }
        }
        
        /**
         * Handle seeked event (debounced)
         */
        onSeeked(audioId) {
            const player = this.players.get(audioId);
            if (!player) return;
            
            const position = Math.floor(player.element.currentTime);
            
            // Clear existing debounce timer
            if (this.debounceTimers.has(audioId)) {
                clearTimeout(this.debounceTimers.get(audioId));
            }
            
            // Set new debounce timer
            const timerId = setTimeout(() => {
                if (this.shouldSave(player.element, position)) {
                    this.savePosition(audioId, position, 'seek');
                }
                this.debounceTimers.delete(audioId);
            }, this.config.debounceDelay);
            
            this.debounceTimers.set(audioId, timerId);
        }
        
        /**
         * Handle timeupdate event (backup save every 10s)
         */
        onTimeUpdate(audioId) {
            const player = this.players.get(audioId);
            if (!player || player.element.paused) return;
            
            const position = Math.floor(player.element.currentTime);
            
            // Save every 10 seconds as backup
            if (position > 0 && position % 10 === 0 && player.lastSaved !== position) {
                this.savePosition(audioId, position, 'timeupdate');
            }
        }
        
        /**
         * Start auto-save interval
         */
        startAutoSave(audioId) {
            const player = this.players.get(audioId);
            if (!player) return;
            
            // Clear existing interval
            this.stopAutoSave(audioId);
            
            // Start new interval
            player.intervalId = setInterval(() => {
                if (!player.element.paused && !player.element.ended) {
                    const position = Math.floor(player.element.currentTime);
                    if (this.shouldSave(player.element, position)) {
                        this.savePosition(audioId, position, 'interval');
                    }
                }
            }, this.config.autoSaveInterval);
            
            console.log(`[AudioManager] Started auto-save for audio ${audioId}`);
        }
        
        /**
         * Stop auto-save interval
         */
        stopAutoSave(audioId) {
            const player = this.players.get(audioId);
            if (!player || !player.intervalId) return;
            
            clearInterval(player.intervalId);
            player.intervalId = null;
            
            console.log(`[AudioManager] Stopped auto-save for audio ${audioId}`);
        }
        
        /**
         * Check if position should be saved
         */
        shouldSave(audio, position) {
            return position >= this.config.minPositionToSave && 
                   position < audio.duration;
        }
        
        /**
         * Pause all other players
         */
        pauseOthers(currentAudioId) {
            this.players.forEach((player, audioId) => {
                if (audioId !== currentAudioId && !player.element.paused) {
                    const position = Math.floor(player.element.currentTime);
                    console.log(`[AudioManager] Pausing audio ${audioId} at ${position}s`);
                    
                    // Save position before pausing
                    if (this.shouldSave(player.element, position)) {
                        this.savePosition(audioId, position, 'pause_other');
                    }
                    
                    player.element.pause();
                }
            });
        }
        
        /**
         * Save position to server
         */
        async savePosition(audioId, position, trigger = 'unknown') {
            const player = this.players.get(audioId);
            
            // Skip if same as last saved
            if (player && player.lastSaved === position) {
                return;
            }
            
            // Update last saved
            if (player) {
                player.lastSaved = position;
            }
            
            console.log(`[AudioManager] Saving: audio=${audioId}, pos=${position}s, trigger=${trigger}`);
            
            // Use sendBeacon for beforeunload (more reliable)
            if (trigger === 'beforeunload') {
                const data = JSON.stringify({ id: audioId, position: position });
                navigator.sendBeacon(this.config.apiEndpoint, data);
                return;
            }
            
            // Check if online
            if (!navigator.onLine) {
                this.saveToLocalStorage(audioId, position);
                return;
            }
            
            try {
                const response = await fetch(this.config.apiEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: audioId, position: position }),
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                if (data.success) {
                    console.log(`[AudioManager] Position saved successfully`);
                }
            } catch (error) {
                console.error(`[AudioManager] Save error:`, error);
                // Fallback to localStorage
                this.saveToLocalStorage(audioId, position);
            }
        }
        
        /**
         * Save all playing audio positions
         */
        saveAllPositions(trigger) {
            this.players.forEach((player, audioId) => {
                if (!player.element.paused && player.element.currentTime > 0) {
                    const position = Math.floor(player.element.currentTime);
                    this.savePosition(audioId, position, trigger);
                }
            });
        }
        
        /**
         * Restore position when audio loads
         */
        restorePosition(audio, position) {
            if (position <= 0) return;
            
            const restore = () => {
                if (position < audio.duration) {
                    audio.currentTime = position;
                    console.log(`[AudioManager] Restored position: ${position}s`);
                }
            };
            
            // If metadata already loaded
            if (audio.readyState >= 1) {
                restore();
            } else {
                // Wait for metadata
                audio.addEventListener('loadedmetadata', restore, { once: true });
            }
        }
        
        /**
         * Save to localStorage (offline fallback)
         */
        saveToLocalStorage(audioId, position) {
            try {
                const key = `audio_pos_${audioId}`;
                localStorage.setItem(key, JSON.stringify({
                    position: position,
                    timestamp: Date.now()
                }));
                console.log(`[AudioManager] Saved to localStorage: ${key}`);
            } catch (e) {
                console.error('[AudioManager] localStorage error:', e);
            }
        }
        
        /**
         * Sync positions from localStorage to server
         */
        async syncFromLocalStorage() {
            const keys = Object.keys(localStorage).filter(k => k.startsWith('audio_pos_'));
            
            for (const key of keys) {
                try {
                    const data = JSON.parse(localStorage.getItem(key));
                    const audioId = parseInt(key.replace('audio_pos_', ''));
                    
                    // Only sync if less than 24 hours old
                    if (Date.now() - data.timestamp < 24 * 60 * 60 * 1000) {
                        await this.savePosition(audioId, data.position, 'sync');
                    }
                    
                    localStorage.removeItem(key);
                } catch (e) {
                    console.error(`[AudioManager] Sync error for ${key}:`, e);
                }
            }
        }
        
        /**
         * Unregister an audio element
         */
        unregister(audioId) {
            const player = this.players.get(audioId);
            if (!player) return;
            
            // Save final position
            if (!player.element.paused && player.element.currentTime > 0) {
                this.savePosition(audioId, Math.floor(player.element.currentTime), 'unregister');
            }
            
            // Stop auto-save
            this.stopAutoSave(audioId);
            
            // Clear debounce timer
            if (this.debounceTimers.has(audioId)) {
                clearTimeout(this.debounceTimers.get(audioId));
                this.debounceTimers.delete(audioId);
            }
            
            // Remove from map
            this.players.delete(audioId);
            
            console.log(`[AudioManager] Unregistered audio ${audioId}`);
        }
        
        /**
         * Unregister all audio elements
         */
        unregisterAll() {
            this.players.forEach((_, audioId) => {
                this.unregister(audioId);
            });
        }
        
        /**
         * Get audio item from server (with saved position)
         */
        async getHistoryItem(audioId) {
            try {
                const response = await fetch(`${this.config.getItemEndpoint}?id=${audioId}`, {
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                return data.success ? data.data : null;
            } catch (error) {
                console.error(`[AudioManager] Get item error:`, error);
                return null;
            }
        }
    }
    
    // Create global instance
    window.AudioPlayerManager = AudioPlayerManager;
    window.audioManager = new AudioPlayerManager();
    
    console.log('[AudioManager] Module loaded');
    
})();
