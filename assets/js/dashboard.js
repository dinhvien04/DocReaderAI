/**
 * Dashboard JavaScript
 * Handles all dashboard card interactions and recent activity
 */

// Quick Access Cards Management
class QuickAccessCards {
    constructor() {
        this.initTTSCard();
        this.initSummarizeCard();
        this.initTranslationCard();
        this.initFileManagementCard();
    }

    // TTS Card
    initTTSCard() {
        const convertBtn = document.getElementById('tts-convert-btn');
        const textArea = document.getElementById('tts-text');
        const voiceSelect = document.getElementById('voice-select');
        const audioPlayer = document.getElementById('tts-audio-player');

        if (convertBtn) {
            convertBtn.addEventListener('click', async () => {
                const text = textArea.value.trim();
                const voice = voiceSelect.value;

                if (!text) {
                    showToast('Vui lòng nhập văn bản', 'error');
                    return;
                }

                if (text.length > 5000) {
                    showToast('Văn bản quá dài (tối đa 5000 ký tự)', 'error');
                    return;
                }

                setLoading(convertBtn, true);
                try {
                    const response = await apiRequest(`${API_BASE}/tts.php?action=convert`, {
                        method: 'POST',
                        body: JSON.stringify({ text, voice, speed: 1.0 })
                    });

                    if (response.success && response.data.audio_url) {
                        audioPlayer.src = response.data.audio_url;
                        audioPlayer.classList.remove('hidden');
                        audioPlayer.play();
                        showToast('Chuyển đổi thành công', 'success');
                    }
                } catch (error) {
                    showToast('Không thể chuyển đổi văn bản', 'error');
                } finally {
                    setLoading(convertBtn, false);
                }
            });
        }
    }

    // Summarize Card
    initSummarizeCard() {
        const summarizeBtn = document.getElementById('summarize-btn');
        const textArea = document.getElementById('summarize-text');
        const resultDiv = document.getElementById('summarize-result');
        const summaryText = document.getElementById('summary-text');

        if (summarizeBtn) {
            summarizeBtn.addEventListener('click', async () => {
                const text = textArea.value.trim();

                if (!text) {
                    showToast('Vui lòng nhập văn bản', 'error');
                    return;
                }

                setLoading(summarizeBtn, true);
                try {
                    const response = await apiRequest(`${API_BASE}/summarize.php?action=summarize`, {
                        method: 'POST',
                        body: JSON.stringify({ text })
                    });

                    if (response.success && response.data.summary) {
                        summaryText.textContent = response.data.summary;
                        resultDiv.classList.remove('hidden');
                        showToast('Tóm tắt thành công', 'success');
                    }
                } catch (error) {
                    showToast('Không thể tóm tắt văn bản', 'error');
                } finally {
                    setLoading(summarizeBtn, false);
                }
            });
        }
    }

    // Translation Card
    initTranslationCard() {
        const translateBtn = document.getElementById('translate-btn');
        const textArea = document.getElementById('translate-text');
        const sourceLang = document.getElementById('source-lang');
        const targetLang = document.getElementById('target-lang');
        const resultDiv = document.getElementById('translate-result');
        const translatedText = document.getElementById('translated-text');

        if (translateBtn) {
            translateBtn.addEventListener('click', async () => {
                const text = textArea.value.trim();
                const target = targetLang.value;

                if (!text) {
                    showToast('Vui lòng nhập văn bản', 'error');
                    return;
                }

                setLoading(translateBtn, true);
                try {
                    const response = await apiRequest(`${API_BASE}/translate.php?action=translate`, {
                        method: 'POST',
                        body: JSON.stringify({ text, targetLang: target })
                    });

                    if (response.success && response.data.translated_text) {
                        translatedText.textContent = response.data.translated_text;
                        resultDiv.classList.remove('hidden');
                        showToast('Dịch thành công', 'success');
                    }
                } catch (error) {
                    showToast('Không thể dịch văn bản', 'error');
                } finally {
                    setLoading(translateBtn, false);
                }
            });
        }
    }

    // File Management Card
    initFileManagementCard() {
        this.loadRecentFiles();
    }

    async loadRecentFiles() {
        const filesList = document.getElementById('recent-files-list');
        if (!filesList) return;

        try {
            const response = await apiRequest(`${API_BASE}/document.php?action=list&limit=3`);
            
            if (response.success && response.data.files) {
                if (response.data.files.length === 0) {
                    filesList.innerHTML = '<p class="text-gray-500 text-sm">Chưa có file nào</p>';
                } else {
                    filesList.innerHTML = response.data.files.map(file => `
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">${file.filename}</p>
                                <p class="text-xs text-gray-500">${this.getRelativeTime(file.created_at)}</p>
                            </div>
                        </div>
                    `).join('');
                }
            }
        } catch (error) {
            filesList.innerHTML = '<p class="text-gray-500 text-sm">Không thể tải files</p>';
        }
    }

    getRelativeTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return 'Hôm nay';
        if (diffDays === 1) return '1 ngày trước';
        if (diffDays < 7) return `${diffDays} ngày trước`;
        if (diffDays < 30) return `${Math.floor(diffDays / 7)} tuần trước`;
        return `${Math.floor(diffDays / 30)} tháng trước`;
    }
}

// Recent Activity Management
class RecentActivity {
    constructor() {
        this.loadActivities();
    }

    async loadActivities() {
        const tbody = document.getElementById('activity-table-body');
        if (!tbody) return;

        // Show loading state
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                    <div class="spinner mx-auto mb-2"></div>
                    Đang tải...
                </td>
            </tr>
        `;

        try {
            const response = await apiRequest(`${API_BASE}/document.php?action=history&limit=10`);
            
            if (response.success && response.data.history) {
                if (response.data.history.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                📭 Chưa có hoạt động nào
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = response.data.history.map(activity => this.renderActivityRow(activity)).join('');
                    this.attachEventListeners();
                }
            } else {
                throw new Error('Invalid response format');
            }
        } catch (error) {
            console.error('Error loading activities:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-red-500">
                        ⚠️ Không thể tải lịch sử hoạt động. Vui lòng thử lại sau.
                    </td>
                </tr>
            `;
            showToast('Không thể tải lịch sử', 'error');
        }
    }

    renderActivityRow(activity) {
        // Escape HTML to prevent XSS
        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        // Truncate text if too long
        const truncateText = (text, maxLength = 50) => {
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        };
        
        // Format voice name
        const voiceName = activity.voice || 'FEMALE';
        
        // Saved position (in seconds)
        const savedPosition = activity.position || 0;
        
        return `
            <tr class="hover:bg-gray-50 border-b">
                <td class="px-4 py-4">
                    <div class="text-sm text-gray-900">${escapeHtml(truncateText(activity.text || 'Không có văn bản'))}</div>
                </td>
                <td class="px-4 py-4">
                    <div class="text-sm text-gray-700">${voiceName}</div>
                </td>
                <td class="px-4 py-4">
                    <div class="text-sm text-gray-600">${formatDate(activity.created_at)}</div>
                </td>
                <td class="px-4 py-4">
                    <audio 
                        id="audio-${activity.id}" 
                        controls 
                        class="w-64" 
                        preload="metadata"
                        data-audio-id="${activity.id}"
                        data-saved-position="${savedPosition}"
                    >
                        <source src="${activity.file_path}" type="audio/mpeg">
                        Trình duyệt không hỗ trợ audio
                    </audio>
                </td>
                <td class="px-4 py-4">
                    <button 
                        onclick="recentActivity.handleDelete(${activity.id})" 
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition"
                        title="Xóa"
                    >
                        Xóa
                    </button>
                </td>
            </tr>
        `;
    }

    attachEventListeners() {
        // Attach event listeners to all audio elements
        const audioElements = document.querySelectorAll('audio[data-audio-id]');
        
        audioElements.forEach(audio => {
            const audioId = parseInt(audio.dataset.audioId);
            const savedPosition = parseInt(audio.dataset.savedPosition) || 0;
            
            // Set saved position when metadata is loaded
            audio.addEventListener('loadedmetadata', () => {
                if (savedPosition > 0 && savedPosition < audio.duration) {
                    audio.currentTime = savedPosition;
                    console.log(`Resumed audio ${audioId} at ${savedPosition}s`);
                }
            });
            
            // Save position when paused
            audio.addEventListener('pause', () => {
                const currentTime = Math.floor(audio.currentTime);
                if (currentTime > 0 && currentTime < audio.duration) {
                    this.savePosition(audioId, currentTime);
                    console.log(`Saved on pause: ${currentTime}s`);
                }
            });
            
            // Reset position when ended
            audio.addEventListener('ended', () => {
                this.savePosition(audioId, 0);
                console.log(`Audio ended, reset position`);
            });
        });
        
        // Save all audio positions when leaving the page or switching tabs
        window.addEventListener('beforeunload', () => {
            audioElements.forEach(audio => {
                if (!audio.paused && audio.currentTime > 0) {
                    const audioId = parseInt(audio.dataset.audioId);
                    const currentTime = Math.floor(audio.currentTime);
                    // Use sendBeacon for reliable save on page unload
                    const data = JSON.stringify({ id: audioId, position: currentTime });
                    navigator.sendBeacon(`${API_BASE}/document.php?action=update-position`, data);
                }
            });
        });
        
        // Save when switching tabs (page visibility change)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                audioElements.forEach(audio => {
                    if (!audio.paused && audio.currentTime > 0) {
                        const audioId = parseInt(audio.dataset.audioId);
                        const currentTime = Math.floor(audio.currentTime);
                        this.savePosition(audioId, currentTime);
                        console.log(`Saved on tab switch: ${currentTime}s`);
                    }
                });
            }
        });
    }

    async savePosition(audioId, position) {
        try {
            await apiRequest(`${API_BASE}/document.php?action=update-position`, {
                method: 'POST',
                body: JSON.stringify({ id: audioId, position: position })
            });
            console.log(`Saved position for audio ${audioId}: ${position}s`);
        } catch (error) {
            console.error('Error saving position:', error);
        }
    }

    attachEventListeners() {
        // Event listeners are attached via onclick in the HTML
    }

    async handlePlay(fileId) {
        try {
            const response = await apiRequest(`${API_BASE}/document.php?action=get&id=${fileId}`);
            
            if (response.success && response.data.file_path) {
                const audio = new Audio(response.data.file_path);
                audio.play().catch(err => {
                    console.error('Audio play error:', err);
                    showToast('Không thể phát audio. File có thể không tồn tại.', 'error');
                });
                showToast('Đang phát audio', 'info');
            } else {
                showToast('Không tìm thấy file audio', 'error');
            }
        } catch (error) {
            console.error('Play error:', error);
            showToast('Không thể phát file', 'error');
        }
    }

    async handleDownload(fileId) {
        try {
            const response = await apiRequest(`${API_BASE}/document.php?action=get&id=${fileId}`);
            
            if (response.success && response.data.file_path) {
                const link = document.createElement('a');
                link.href = response.data.file_path;
                link.download = response.data.filename || 'audio.mp3';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                showToast('Đang tải xuống', 'success');
            } else {
                showToast('Không tìm thấy file', 'error');
            }
        } catch (error) {
            console.error('Download error:', error);
            showToast('Không thể tải xuống file', 'error');
        }
    }

    async handleDelete(fileId) {
        if (!confirm('Bạn có chắc chắn muốn xóa file này?')) {
            return;
        }

        try {
            const response = await apiRequest(`${API_BASE}/document.php?action=delete&id=${fileId}`, {
                method: 'POST'
            });
            
            if (response.success) {
                showToast('Đã xóa file thành công', 'success');
                // Reload the table
                this.loadActivities();
            } else {
                showToast(response.error || 'Không thể xóa file', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showToast('Không thể xóa file', 'error');
        }
    }
}

// Initialize on DOM ready
let quickAccessCards;
let recentActivity;

document.addEventListener('DOMContentLoaded', () => {
    quickAccessCards = new QuickAccessCards();
    recentActivity = new RecentActivity();
});
