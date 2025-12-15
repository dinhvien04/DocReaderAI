/**
 * Audio Position Tracker
 * Module thống nhất để lưu vị trí audio trong các trường hợp:
 * 1. Pause - Khi nhấn pause
 * 2. Tab switch - Khi chuyển tab trình duyệt
 * 3. Page unload - Khi đóng/refresh trang
 * 4. Audio ended - Khi audio phát xong (reset về 0)
 * 5. Periodic save - Lưu định kỳ mỗi 5 giây khi đang phát
 * 6. Seek - Khi user tua audio
 * 7. Switch audio - Khi chuyển sang audio khác
 * 8. Switch tab (trong app) - Khi chuyển tab TTS/History/etc
 * 9. Logout - Khi user đăng xuất
 * 10. Network offline - Khi mất kết nối mạng
 */

(function() {
    'use strict';

    console.log('[AudioTracker] Loading Audio Position Tracker...');

    class AudioPositionTracker {
        constructor() {
            // Lưu trữ các audio đang được track
            this.trackedAudios = new Map();
            
            // Interval ID cho periodic save
            this.saveIntervals = new Map();
            
            // Cấu hình
            this.config = {
                periodicSaveInterval: 5000, // 5 giây
                minPositionToSave: 1, // Tối thiểu 1 giây mới lưu
                debounceDelay: 500, // Debounce cho seek
                apiEndpoint: '/KK/api/update_position.php'
            };

            // Debounce timer cho seek
            this.seekDebounceTimers = new Map();

            // Khởi tạo global event listeners
            this.initGlobalListeners();
            
            console.log('[AudioTracker] Initialized with config:', this.config);
        }

        /**
         * Khởi tạo các event listener toàn cục
         */
        initGlobalListeners() {
            // 1. Page unload - Lưu tất cả audio đang phát
            window.addEventListener('beforeunload', () => {
                console.log('[AudioTracker] Page unload - saving all positions');
                this.saveAllPositions('beforeunload');
            });

            // 2. Tab switch (browser) - Lưu khi chuyển tab
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    console.log('[AudioTracker] Tab hidden - saving all positions');
                    this.saveAllPositions('visibilitychange');
                }
            });

            // 3. Network offline - Lưu vào localStorage khi mất mạng
            window.addEventListener('offline', () => {
                console.log('[AudioTracker] Network offline - saving to localStorage');
                this.saveAllPositionsToLocalStorage();
            });

            // 4. Network online - Sync từ localStorage lên server
            window.addEventListener('online', () => {
                console.log('[AudioTracker] Network online - syncing from localStorage');
                this.syncFromLocalStorage();
            });

            console.log('[AudioTracker] Global listeners initialized');
        }

        /**
         * Đăng ký theo dõi một audio element
         * @param {HTMLAudioElement} audioElement - Audio element cần track
         * @param {number} audioId - ID của audio trong database
         * @param {string} source - Nguồn: 'tts' hoặc 'history'
         */
        track(audioElement, audioId, source = 'tts') {
            if (!audioElement || !audioId) {
                console.warn('[AudioTracker] Invalid audio element or ID');
                return;
            }

            // Nếu đã track rồi thì bỏ qua
            if (this.trackedAudios.has(audioId)) {
                console.log(`[AudioTracker] Audio ${audioId} already tracked`);
                return;
            }

            console.log(`[AudioTracker] Tracking audio ${audioId} from ${source}`);

            // Lưu thông tin audio
            this.trackedAudios.set(audioId, {
                element: audioElement,
                source: source,
                lastSavedPosition: 0
            });

            // Gắn các event listeners
            this.attachAudioListeners(audioElement, audioId);
        }

        /**
         * Gắn event listeners cho audio element
         */
        attachAudioListeners(audio, audioId) {
            // 1. PAUSE - Lưu khi nhấn pause
            audio.addEventListener('pause', () => {
                const position = Math.floor(audio.currentTime);
                if (this.shouldSave(audio, position)) {
                    console.log(`[AudioTracker] Pause event - saving position ${position}s for audio ${audioId}`);
                    this.savePosition(audioId, position, 'pause');
                }
            });

            // 2. ENDED - Reset về 0 khi phát xong
            audio.addEventListener('ended', () => {
                console.log(`[AudioTracker] Audio ${audioId} ended - resetting position`);
                this.savePosition(audioId, 0, 'ended');
                this.stopPeriodicSave(audioId);
            });

            // 3. PLAY - Bắt đầu periodic save khi phát
            audio.addEventListener('play', () => {
                console.log(`[AudioTracker] Audio ${audioId} playing - starting periodic save`);
                this.startPeriodicSave(audioId);
            });

            // 4. SEEKING - Lưu khi user tua (debounced)
            audio.addEventListener('seeked', () => {
                const position = Math.floor(audio.currentTime);
                console.log(`[AudioTracker] Seek event - position ${position}s for audio ${audioId}`);
                this.debouncedSave(audioId, position, 'seek');
            });

            // 5. TIMEUPDATE - Backup save mỗi 10 giây
            audio.addEventListener('timeupdate', () => {
                const position = Math.floor(audio.currentTime);
                // Lưu mỗi 10 giây như backup
                if (position > 0 && position % 10 === 0) {
                    const tracked = this.trackedAudios.get(audioId);
                    if (tracked && tracked.lastSavedPosition !== position) {
                        this.savePosition(audioId, position, 'timeupdate');
                    }
                }
            });

            // 6. ERROR - Log lỗi
            audio.addEventListener('error', (e) => {
                console.error(`[AudioTracker] Audio ${audioId} error:`, e);
            });
        }

        /**
         * Kiểm tra có nên lưu position không
         */
        shouldSave(audio, position) {
            return position >= this.config.minPositionToSave && 
                   position < audio.duration;
        }

        /**
         * Lưu position với debounce (cho seek)
         */
        debouncedSave(audioId, position, trigger) {
            // Clear timer cũ nếu có
            if (this.seekDebounceTimers.has(audioId)) {
                clearTimeout(this.seekDebounceTimers.get(audioId));
            }

            // Set timer mới
            const timer = setTimeout(() => {
                this.savePosition(audioId, position, trigger);
                this.seekDebounceTimers.delete(audioId);
            }, this.config.debounceDelay);

            this.seekDebounceTimers.set(audioId, timer);
        }

        /**
         * Bắt đầu lưu định kỳ
         */
        startPeriodicSave(audioId) {
            // Dừng interval cũ nếu có
            this.stopPeriodicSave(audioId);

            const tracked = this.trackedAudios.get(audioId);
            if (!tracked) return;

            const interval = setInterval(() => {
                const audio = tracked.element;
                if (audio && !audio.paused && !audio.ended) {
                    const position = Math.floor(audio.currentTime);
                    if (this.shouldSave(audio, position)) {
                        this.savePosition(audioId, position, 'periodic');
                    }
                }
            }, this.config.periodicSaveInterval);

            this.saveIntervals.set(audioId, interval);
            console.log(`[AudioTracker] Started periodic save for audio ${audioId}`);
        }

        /**
         * Dừng lưu định kỳ
         */
        stopPeriodicSave(audioId) {
            if (this.saveIntervals.has(audioId)) {
                clearInterval(this.saveIntervals.get(audioId));
                this.saveIntervals.delete(audioId);
                console.log(`[AudioTracker] Stopped periodic save for audio ${audioId}`);
            }
        }

        /**
         * Lưu position lên server
         */
        async savePosition(audioId, position, trigger = 'unknown') {
            try {
                // Cập nhật lastSavedPosition
                const tracked = this.trackedAudios.get(audioId);
                if (tracked) {
                    tracked.lastSavedPosition = position;
                }

                console.log(`[AudioTracker] Saving position: audio=${audioId}, pos=${position}s, trigger=${trigger}`);

                // Nếu đang offline, lưu vào localStorage
                if (!navigator.onLine) {
                    this.saveToLocalStorage(audioId, position);
                    return;
                }

                // Gửi request lên server
                const response = await fetch(this.config.apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: audioId,
                        position: position
                    }),
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                console.log(`[AudioTracker] Position saved successfully:`, data);

            } catch (error) {
                console.error(`[AudioTracker] Error saving position:`, error);
                // Fallback: lưu vào localStorage
                this.saveToLocalStorage(audioId, position);
            }
        }

        /**
         * Lưu tất cả positions (dùng cho beforeunload, visibilitychange)
         */
        saveAllPositions(trigger) {
            this.trackedAudios.forEach((tracked, audioId) => {
                const audio = tracked.element;
                if (audio && !audio.paused && audio.currentTime > 0) {
                    const position = Math.floor(audio.currentTime);
                    
                    // Dùng sendBeacon cho beforeunload để đảm bảo request được gửi
                    if (trigger === 'beforeunload') {
                        const data = JSON.stringify({ id: audioId, position: position });
                        navigator.sendBeacon(this.config.apiEndpoint, data);
                        console.log(`[AudioTracker] Beacon sent for audio ${audioId}: ${position}s`);
                    } else {
                        this.savePosition(audioId, position, trigger);
                    }
                }
            });
        }

        /**
         * Lưu vào localStorage (offline fallback)
         */
        saveToLocalStorage(audioId, position) {
            try {
                const key = `audio_position_${audioId}`;
                const data = {
                    position: position,
                    timestamp: Date.now()
                };
                localStorage.setItem(key, JSON.stringify(data));
                console.log(`[AudioTracker] Saved to localStorage: ${key}`, data);
            } catch (error) {
                console.error('[AudioTracker] Error saving to localStorage:', error);
            }
        }

        /**
         * Lưu tất cả vào localStorage
         */
        saveAllPositionsToLocalStorage() {
            this.trackedAudios.forEach((tracked, audioId) => {
                const audio = tracked.element;
                if (audio && audio.currentTime > 0) {
                    this.saveToLocalStorage(audioId, Math.floor(audio.currentTime));
                }
            });
        }

        /**
         * Sync từ localStorage lên server khi online
         */
        async syncFromLocalStorage() {
            const keys = Object.keys(localStorage).filter(k => k.startsWith('audio_position_'));
            
            for (const key of keys) {
                try {
                    const data = JSON.parse(localStorage.getItem(key));
                    const audioId = parseInt(key.replace('audio_position_', ''));
                    
                    // Chỉ sync nếu data không quá cũ (< 24 giờ)
                    if (Date.now() - data.timestamp < 24 * 60 * 60 * 1000) {
                        await this.savePosition(audioId, data.position, 'sync');
                        localStorage.removeItem(key);
                        console.log(`[AudioTracker] Synced and removed: ${key}`);
                    } else {
                        localStorage.removeItem(key);
                        console.log(`[AudioTracker] Removed stale data: ${key}`);
                    }
                } catch (error) {
                    console.error(`[AudioTracker] Error syncing ${key}:`, error);
                }
            }
        }

        /**
         * Hủy theo dõi một audio
         */
        untrack(audioId) {
            this.stopPeriodicSave(audioId);
            
            // Lưu position cuối cùng trước khi untrack
            const tracked = this.trackedAudios.get(audioId);
            if (tracked && tracked.element) {
                const position = Math.floor(tracked.element.currentTime);
                if (position > 0) {
                    this.savePosition(audioId, position, 'untrack');
                }
            }

            this.trackedAudios.delete(audioId);
            console.log(`[AudioTracker] Untracked audio ${audioId}`);
        }

        /**
         * Lưu position khi chuyển tab trong app (TTS -> History, etc)
         */
        saveOnTabSwitch() {
            console.log('[AudioTracker] App tab switch - saving all positions');
            this.saveAllPositions('app_tab_switch');
        }

        /**
         * Lưu position khi logout
         */
        saveOnLogout() {
            console.log('[AudioTracker] Logout - saving all positions');
            this.saveAllPositions('logout');
        }

        /**
         * Lưu khi chuyển sang audio khác
         */
        saveOnSwitchAudio(currentAudioId) {
            const tracked = this.trackedAudios.get(currentAudioId);
            if (tracked && tracked.element) {
                const position = Math.floor(tracked.element.currentTime);
                if (position > 0) {
                    console.log(`[AudioTracker] Switching audio - saving position ${position}s for audio ${currentAudioId}`);
                    this.savePosition(currentAudioId, position, 'switch_audio');
                }
            }
        }

        /**
         * Khôi phục position từ server/localStorage
         */
        restorePosition(audioElement, savedPosition) {
            if (savedPosition > 0 && audioElement) {
                audioElement.addEventListener('loadedmetadata', () => {
                    if (savedPosition < audioElement.duration) {
                        audioElement.currentTime = savedPosition;
                        console.log(`[AudioTracker] Restored position: ${savedPosition}s`);
                    }
                }, { once: true });
            }
        }

        // ==========================================
        // CÁC TRƯỜNG HỢP CHO HISTORY
        // ==========================================

        /**
         * Lưu position khi click play audio khác trong danh sách history
         * Pause audio đang phát và lưu position
         */
        saveOnPlayAnother(newAudioId) {
            console.log(`[AudioTracker] Playing another audio ${newAudioId} - saving all playing audios`);
            
            this.trackedAudios.forEach((tracked, audioId) => {
                if (audioId !== newAudioId) {
                    const audio = tracked.element;
                    if (audio && !audio.paused && audio.currentTime > 0) {
                        const position = Math.floor(audio.currentTime);
                        console.log(`[AudioTracker] Pausing and saving audio ${audioId} at ${position}s`);
                        audio.pause(); // Pause audio đang phát
                        this.savePosition(audioId, position, 'play_another');
                    }
                }
            });
        }

        /**
         * Lưu position trước khi xóa audio
         */
        saveBeforeDelete(audioId) {
            const tracked = this.trackedAudios.get(audioId);
            if (tracked && tracked.element) {
                const audio = tracked.element;
                if (!audio.paused && audio.currentTime > 0) {
                    const position = Math.floor(audio.currentTime);
                    console.log(`[AudioTracker] Saving before delete: audio ${audioId} at ${position}s`);
                    // Không cần lưu lên server vì sẽ bị xóa, chỉ pause
                    audio.pause();
                }
                // Cleanup
                this.stopPeriodicSave(audioId);
                this.trackedAudios.delete(audioId);
            }
        }

        /**
         * Lưu tất cả positions trước khi chuyển trang (pagination)
         */
        saveOnPagination() {
            console.log('[AudioTracker] Pagination - saving all positions');
            this.saveAllPositions('pagination');
            // Cleanup tất cả tracked audios vì sẽ load lại
            this.cleanupAll();
        }

        /**
         * Lưu tất cả positions trước khi filter history
         */
        saveOnFilter() {
            console.log('[AudioTracker] Filter - saving all positions');
            this.saveAllPositions('filter');
            // Cleanup tất cả tracked audios vì sẽ load lại
            this.cleanupAll();
        }

        /**
         * Lưu tất cả positions trước khi reload history
         */
        saveOnReload() {
            console.log('[AudioTracker] Reload - saving all positions');
            this.saveAllPositions('reload');
            // Cleanup tất cả tracked audios vì sẽ load lại
            this.cleanupAll();
        }

        /**
         * Cleanup tất cả tracked audios (không lưu position)
         */
        cleanupAll() {
            // Stop tất cả periodic saves
            this.saveIntervals.forEach((interval, audioId) => {
                clearInterval(interval);
            });
            this.saveIntervals.clear();
            
            // Clear debounce timers
            this.seekDebounceTimers.forEach((timer) => {
                clearTimeout(timer);
            });
            this.seekDebounceTimers.clear();
            
            // Clear tracked audios
            this.trackedAudios.clear();
            
            console.log('[AudioTracker] Cleaned up all tracked audios');
        }

        /**
         * Lưu position của audio đang phát (nếu có)
         * Dùng cho các trường hợp cần lưu nhanh
         */
        saveCurrentlyPlaying() {
            let savedCount = 0;
            this.trackedAudios.forEach((tracked, audioId) => {
                const audio = tracked.element;
                if (audio && !audio.paused && audio.currentTime > 0) {
                    const position = Math.floor(audio.currentTime);
                    this.savePosition(audioId, position, 'currently_playing');
                    savedCount++;
                }
            });
            console.log(`[AudioTracker] Saved ${savedCount} currently playing audio(s)`);
            return savedCount;
        }

        /**
         * Lấy audio đang phát (nếu có)
         */
        getCurrentlyPlaying() {
            for (const [audioId, tracked] of this.trackedAudios) {
                if (tracked.element && !tracked.element.paused) {
                    return { audioId, element: tracked.element };
                }
            }
            return null;
        }

        /**
         * Kiểm tra xem có audio nào đang phát không
         */
        isAnyPlaying() {
            return this.getCurrentlyPlaying() !== null;
        }
    }

    // Tạo instance global
    window.audioTracker = new AudioPositionTracker();

    console.log('[AudioTracker] Module loaded successfully');

})();
