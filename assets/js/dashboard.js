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
        const convertBtn = document.getElementById('convert-btn');
        const textArea = document.getElementById('tts-text');
        const voiceSelect = document.getElementById('voice-select');
        const audioPlayer = document.getElementById('audio-player');

        console.log('[TTS] Init TTS Card', { convertBtn, textArea, voiceSelect, audioPlayer });

        if (convertBtn) {
            convertBtn.addEventListener('click', async () => {
                console.log('[TTS] Convert button clicked');
                const text = textArea.value.trim();
                const voice = voiceSelect.value;

                console.log('[TTS] Input values:', { text: text.substring(0, 50), voice });

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
                    console.log('[TTS] Sending API request...');
                    const response = await apiRequest(`${API_BASE}/tts.php?action=convert`, {
                        method: 'POST',
                        body: JSON.stringify({ text, voice, speed: 1.0 })
                    });

                    console.log('[TTS] API response:', response);

                    if (response.success && response.data.audio_url) {
                        console.log('[TTS] Setting audio source:', response.data.audio_url);
                        audioPlayer.src = response.data.audio_url;
                        audioPlayer.classList.remove('hidden');
                        
                        // Try to play with error handling
                        try {
                            await audioPlayer.play();
                            console.log('[TTS] Audio playing successfully');
                        } catch (playError) {
                            console.error('[TTS] Audio play error:', playError);
                            showToast('Không thể phát audio: ' + playError.message, 'error');
                        }
                        
                        showToast('Chuyển đổi thành công', 'success');
                    } else {
                        console.error('[TTS] Invalid response:', response);
                        showToast('Phản hồi không hợp lệ', 'error');
                    }
                } catch (error) {
                    console.error('[TTS] Conversion error:', error);
                    showToast('Không thể chuyển đổi văn bản: ' + error.message, 'error');
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

        console.log('[Summarize] Init Summarize Card', { summarizeBtn, textArea, resultDiv, summaryText });

        if (summarizeBtn) {
            summarizeBtn.addEventListener('click', async () => {
                console.log('[Summarize] Button clicked');
                const text = textArea.value.trim();

                console.log('[Summarize] Input text length:', text.length);

                if (!text) {
                    showToast('Vui lòng nhập văn bản', 'error');
                    return;
                }

                setLoading(summarizeBtn, true);
                try {
                    console.log('[Summarize] Sending API request...');
                    const response = await apiRequest(`${API_BASE}/summarize.php?action=summarize`, {
                        method: 'POST',
                        body: JSON.stringify({ text })
                    });

                    console.log('[Summarize] API response:', response);

                    if (response.success && response.data.summary) {
                        summaryText.textContent = response.data.summary;
                        resultDiv.classList.remove('hidden');
                        showToast('Tóm tắt thành công', 'success');
                        console.log('[Summarize] Summary displayed successfully');
                    } else {
                        console.error('[Summarize] Invalid response:', response);
                        showToast('Phản hồi không hợp lệ', 'error');
                    }
                } catch (error) {
                    console.error('[Summarize] Error:', error);
                    showToast('Không thể tóm tắt văn bản: ' + error.message, 'error');
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

        console.log('[Translate] Init Translation Card', { translateBtn, textArea, targetLang, resultDiv, translatedText });

        if (translateBtn) {
            translateBtn.addEventListener('click', async () => {
                console.log('[Translate] Button clicked');
                const text = textArea.value.trim();
                const target = targetLang.value;

                console.log('[Translate] Input values:', { textLength: text.length, target });

                if (!text) {
                    showToast('Vui lòng nhập văn bản', 'error');
                    return;
                }

                setLoading(translateBtn, true);
                try {
                    console.log('[Translate] Sending API request...');
                    const response = await apiRequest(`${API_BASE}/translate.php?action=translate`, {
                        method: 'POST',
                        body: JSON.stringify({ text, targetLang: target })
                    });

                    console.log('[Translate] API response:', response);

                    if (response.success && response.data.translated_text) {
                        translatedText.textContent = response.data.translated_text;
                        resultDiv.classList.remove('hidden');
                        showToast('Dịch thành công', 'success');
                        console.log('[Translate] Translation displayed successfully');
                    } else {
                        console.error('[Translate] Invalid response:', response);
                        showToast('Phản hồi không hợp lệ', 'error');
                    }
                } catch (error) {
                    console.error('[Translate] Error:', error);
                    showToast('Không thể dịch văn bản: ' + error.message, 'error');
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
        console.log('[RecentActivity] loadActivities called');
        
        // Always use table-based history for TTS
        const tbody = document.getElementById('activity-table-body');
        console.log('[RecentActivity] tbody element:', tbody);
        
        if (!tbody) {
            console.error('[RecentActivity] tbody not found!');
            return;
        }

        // Show loading state
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    <div class="spinner mx-auto mb-2"></div>
                    Đang tải...
                </td>
            </tr>
        `;

        try {
            // Use new unified history API for TTS only
            const response = await apiRequest(`${API_BASE}/history.php?action=list&type=tts&limit=10`);
            
            if (response.success && response.data.items) {
                if (response.data.items.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                📭 Chưa có hoạt động nào
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = response.data.items.map(activity => this.renderActivityRow(activity)).join('');
                    this.attachEventListeners();
                }
            } else {
                throw new Error('Invalid response format');
            }
        } catch (error) {
            console.error('Error loading activities:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-red-500">
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
        
        // Use audio_url from new API structure
        const audioUrl = activity.audio_url || activity.file_path || '';
        
        return `
            <tr class="hover:bg-gray-50 border-b">
                <td class="px-4 py-4">
                    <div class="flex items-center gap-2">
                        <div class="text-sm text-gray-900 cursor-pointer hover:text-blue-600 transition flex-1 view-full-text-btn" data-text="${escapeHtml(activity.text || 'Không có văn bản')}" data-title="Văn bản">${escapeHtml(truncateText(activity.text || 'Không có văn bản'))}</div>
                        <button class="view-full-text-btn text-blue-500 hover:text-blue-700 text-xs" data-text="${escapeHtml(activity.text || 'Không có văn bản')}" data-title="Văn bản" title="Xem đầy đủ">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
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
                    >
                        <source src="${audioUrl}" type="audio/mpeg">
                        Trình duyệt không hỗ trợ audio
                    </audio>
                </td>
                <td class="px-4 py-4">
                    <button 
                        onclick="deleteHistoryItem(${activity.id}, 'tts')" 
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


}

// Initialize on DOM ready
let quickAccessCards;
let recentActivity;

document.addEventListener('DOMContentLoaded', () => {
    quickAccessCards = new QuickAccessCards();
    recentActivity = new RecentActivity();
    
    // Attach event listeners to history filter tabs
    document.querySelectorAll('.history-filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const filterType = this.getAttribute('data-filter');
            if (filterType && typeof filterHistory === 'function') {
                filterHistory(filterType);
            }
        });
    });
    
    // Listen for history tab opened event
    window.addEventListener('historyTabOpened', () => {
        loadHistory('tts', 1);
    });
});


// Unified History Management
let currentHistoryFilter = 'tts';
let currentHistoryPage = 1;

/**
 * Filter history by type
 */
window.filterHistory = function(type) {
    console.log('filterHistory called with type:', type);
    
    currentHistoryFilter = type;
    currentHistoryPage = 1;
    
    // Update filter tabs UI
    document.querySelectorAll('.history-filter-tab').forEach(tab => {
        tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-600');
    });
    
    const activeTab = document.getElementById(`filter-${type}`);
    if (activeTab) {
        activeTab.classList.add('active', 'border-blue-500', 'text-blue-600');
        activeTab.classList.remove('border-transparent', 'text-gray-600');
    }
    
    // Show/hide appropriate view
    const tableView = document.getElementById('history-table-view');
    const cardView = document.getElementById('history-items-container');
    const pagination = document.getElementById('history-pagination');
    
    console.log('Elements found:', {tableView, cardView, pagination});
    
    if (type === 'tts') {
        // Show table view for TTS
        if (tableView) {
            tableView.style.display = 'block';
        }
        if (cardView) {
            cardView.style.display = 'none';
        }
        if (pagination) {
            pagination.style.display = 'none';
        }
        // Load using old method
        if (typeof recentActivity !== 'undefined') {
            recentActivity.loadActivities();
        }
    } else {
        // Show card view for summarize/translate
        console.log('Switching to card view for', type);
        if (tableView) {
            tableView.style.display = 'none';
        }
        if (cardView) {
            cardView.style.display = 'block';
        }
        if (pagination) {
            pagination.style.display = 'flex';
        }
        // Load using new method
        console.log('Calling loadHistory...');
        loadHistory(type, currentHistoryPage);
    }
}

/**
 * Load history from API
 */
window.loadHistory = async function(type = 'tts', page = 1) {
    const container = document.getElementById('history-items-container');
    if (!container) return;
    
    // Show loading state
    container.innerHTML = `
        <div class="text-center py-12">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-gray-500">Đang tải lịch sử...</p>
        </div>
    `;
    
    try {
        const response = await apiRequest(`${API_BASE}/history.php?action=list&type=${type}&page=${page}&limit=20`);
        
        if (response.success && response.data) {
            const { items, total, pages } = response.data;
            
            if (items.length === 0) {
                container.innerHTML = renderEmptyState(type);
            } else {
                container.innerHTML = items.map(item => renderHistoryItem(item)).join('');
            }
            
            // Render pagination
            renderPagination(page, pages);
        } else {
            throw new Error('Invalid response');
        }
    } catch (error) {
        console.error('Error loading history:', error);
        container.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-500">⚠️ Không thể tải lịch sử. Vui lòng thử lại sau.</p>
            </div>
        `;
        showToast('Không thể tải lịch sử', 'error');
    }
}

/**
 * Render a single history item based on type
 */
function renderHistoryItem(item) {
    switch (item.type) {
        case 'tts':
            return renderTTSItem(item);
        case 'summarize':
            return renderSummarizeItem(item);
        case 'translate':
            return renderTranslateItem(item);
        default:
            return '';
    }
}

/**
 * Render TTS history item
 */
function renderTTSItem(item) {
    const truncatedText = truncateText(item.text, 100);
    const formattedDate = formatDate(item.created_at);
    
    return `
        <div class="history-item bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Chuyển văn bản thành giọng nói</h3>
                        <p class="text-sm text-gray-500">${formattedDate}</p>
                    </div>
                </div>
                <button onclick="deleteHistoryItem(${item.id}, 'tts')" class="text-red-500 hover:text-red-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm mb-2"><strong>Văn bản:</strong> ${escapeHtml(truncatedText)}</p>
                <p class="text-gray-600 text-sm"><strong>Giọng đọc:</strong> ${escapeHtml(item.voice)}</p>
            </div>
            <audio controls class="w-full">
                <source src="${item.audio_url}" type="audio/mpeg">
                Trình duyệt không hỗ trợ audio
            </audio>
        </div>
    `;
}

/**
 * Render Summarize history item
 */
function renderSummarizeItem(item) {
    const truncatedOriginal = truncateText(item.original_text, 100);
    const truncatedSummary = truncateText(item.summary_text, 100);
    const formattedDate = formatDate(item.created_at);
    
    return `
        <div class="history-item bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Tóm tắt văn bản</h3>
                        <p class="text-sm text-gray-500">${formattedDate}</p>
                    </div>
                </div>
                <button onclick="deleteHistoryItem(${item.id}, 'summarize')" class="text-red-500 hover:text-red-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
            <div class="space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-700">Văn bản gốc (${item.original_length} ký tự):</p>
                        <button class="view-full-text-btn text-blue-500 hover:text-blue-700 text-xs font-medium flex items-center gap-1" data-text="${escapeHtml(item.original_text)}" data-title="Văn bản gốc">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Xem đầy đủ
                        </button>
                    </div>
                    <p class="text-gray-600 text-sm bg-gray-50 p-3 rounded cursor-pointer hover:bg-gray-100 transition view-full-text-btn" data-text="${escapeHtml(item.original_text)}" data-title="Văn bản gốc">${escapeHtml(truncatedOriginal)}</p>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-700">Tóm tắt (${item.summary_length} ký tự):</p>
                        <button class="view-full-text-btn text-blue-500 hover:text-blue-700 text-xs font-medium flex items-center gap-1" data-text="${escapeHtml(item.summary_text)}" data-title="Tóm tắt">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Xem đầy đủ
                        </button>
                    </div>
                    <p class="text-gray-800 text-sm bg-green-50 p-3 rounded cursor-pointer hover:bg-green-100 transition view-full-text-btn" data-text="${escapeHtml(item.summary_text)}" data-title="Tóm tắt">${escapeHtml(truncatedSummary)}</p>
                </div>
            </div>
        </div>
    `;
}

/**
 * Render Translate history item
 */
function renderTranslateItem(item) {
    const truncatedOriginal = truncateText(item.original_text, 100);
    const truncatedTranslated = truncateText(item.translated_text, 100);
    const formattedDate = formatDate(item.created_at);
    
    const langNames = {
        'vi': 'Tiếng Việt',
        'en': 'Tiếng Anh',
        'ja': 'Tiếng Nhật',
        'ko': 'Tiếng Hàn',
        'zh': 'Tiếng Trung',
        'fr': 'Tiếng Pháp',
        'de': 'Tiếng Đức',
        'es': 'Tiếng Tây Ban Nha'
    };
    
    const sourceLangName = langNames[item.source_lang] || item.source_lang;
    const targetLangName = langNames[item.target_lang] || item.target_lang;
    
    return `
        <div class="history-item bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Dịch thuật</h3>
                        <p class="text-sm text-gray-500">${formattedDate}</p>
                    </div>
                </div>
                <button onclick="deleteHistoryItem(${item.id}, 'translate')" class="text-red-500 hover:text-red-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-2 mb-3">
                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">${sourceLangName}</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">${targetLangName}</span>
            </div>
            <div class="space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-700">Văn bản gốc:</p>
                        <button class="view-full-text-btn text-blue-500 hover:text-blue-700 text-xs font-medium flex items-center gap-1" data-text="${escapeHtml(item.original_text)}" data-title="Văn bản gốc">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Xem đầy đủ
                        </button>
                    </div>
                    <p class="text-gray-600 text-sm bg-gray-50 p-3 rounded cursor-pointer hover:bg-gray-100 transition view-full-text-btn" data-text="${escapeHtml(item.original_text)}" data-title="Văn bản gốc">${escapeHtml(truncatedOriginal)}</p>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-700">Bản dịch:</p>
                        <button class="view-full-text-btn text-blue-500 hover:text-blue-700 text-xs font-medium flex items-center gap-1" data-text="${escapeHtml(item.translated_text)}" data-title="Bản dịch">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Xem đầy đủ
                        </button>
                    </div>
                    <p class="text-gray-800 text-sm bg-purple-50 p-3 rounded cursor-pointer hover:bg-purple-100 transition view-full-text-btn" data-text="${escapeHtml(item.translated_text)}" data-title="Bản dịch">${escapeHtml(truncatedTranslated)}</p>
                </div>
            </div>
        </div>
    `;
}

/**
 * Delete history item
 */
window.deleteHistoryItem = async function(id, type) {
    if (!confirm('Bạn có chắc chắn muốn xóa mục này?')) {
        return;
    }
    
    try {
        const response = await apiRequest(`${API_BASE}/history.php?action=delete`, {
            method: 'POST',
            body: JSON.stringify({ id, type })
        });
        
        if (response.success) {
            showToast('Đã xóa thành công', 'success');
            // Reload current view
            loadHistory(currentHistoryFilter, currentHistoryPage);
        } else {
            showToast(response.error || 'Không thể xóa', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showToast('Không thể xóa mục này', 'error');
    }
}

/**
 * Render pagination controls
 */
function renderPagination(currentPage, totalPages) {
    const container = document.getElementById('history-pagination');
    if (!container || totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="flex items-center gap-2">';
    
    // Previous button
    if (currentPage > 1) {
        html += `
            <button onclick="loadHistory('${currentHistoryFilter}', ${currentPage - 1}); currentHistoryPage = ${currentPage - 1};" 
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Trước
            </button>
        `;
    }
    
    // Page numbers
    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    if (startPage > 1) {
        html += `
            <button onclick="loadHistory('${currentHistoryFilter}', 1); currentHistoryPage = 1;" 
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                1
            </button>
        `;
        if (startPage > 2) {
            html += '<span class="px-2 text-gray-500">...</span>';
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === currentPage;
        html += `
            <button onclick="loadHistory('${currentHistoryFilter}', ${i}); currentHistoryPage = ${i};" 
                    class="px-4 py-2 border rounded-lg transition ${
                        isActive 
                            ? 'bg-blue-500 text-white border-blue-500' 
                            : 'border-gray-300 hover:bg-gray-50'
                    }">
                ${i}
            </button>
        `;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += '<span class="px-2 text-gray-500">...</span>';
        }
        html += `
            <button onclick="loadHistory('${currentHistoryFilter}', ${totalPages}); currentHistoryPage = ${totalPages};" 
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                ${totalPages}
            </button>
        `;
    }
    
    // Next button
    if (currentPage < totalPages) {
        html += `
            <button onclick="loadHistory('${currentHistoryFilter}', ${currentPage + 1}); currentHistoryPage = ${currentPage + 1};" 
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Sau
            </button>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
}

/**
 * Render empty state based on filter type
 */
function renderEmptyState(type) {
    const messages = {
        'tts': 'Chưa có lịch sử chuyển văn bản thành giọng nói',
        'summarize': 'Chưa có lịch sử tóm tắt văn bản',
        'translate': 'Chưa có lịch sử dịch thuật'
    };
    
    const message = messages[type] || 'Chưa có lịch sử hoạt động nào';
    
    return `
        <div class="text-center py-16">
            <div class="text-6xl mb-4">📭</div>
            <p class="text-gray-500 text-lg">${message}</p>
            <p class="text-gray-400 text-sm mt-2">Hãy bắt đầu sử dụng các tính năng để tạo lịch sử</p>
        </div>
    `;
}

/**
 * Truncate text to specified length
 */
function truncateText(text, maxLength) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}


/**
 * Show full text in modal with copy button
 */
window.showFullText = function(text, title) {
    console.log('[Modal] Opening modal with text length:', text ? text.length : 0);
    
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    modal.onclick = function(e) {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    };
    
    // Create modal content
    const modalContent = document.createElement('div');
    modalContent.className = 'bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col';
    modalContent.onclick = function(e) {
        e.stopPropagation();
    };
    
    // Header
    const header = document.createElement('div');
    header.className = 'flex items-center justify-between p-6 border-b';
    header.innerHTML = `
        <h3 class="text-xl font-bold text-gray-900">${escapeHtml(title)}</h3>
        <button class="text-gray-400 hover:text-gray-600 close-modal-btn">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    `;
    
    // Body
    const body = document.createElement('div');
    body.className = 'p-6 overflow-y-auto flex-1';
    const textContainer = document.createElement('div');
    textContainer.className = 'bg-gray-50 p-4 rounded-lg';
    const pre = document.createElement('pre');
    pre.className = 'whitespace-pre-wrap text-sm text-gray-800 font-sans';
    pre.textContent = text;
    textContainer.appendChild(pre);
    body.appendChild(textContainer);
    
    // Footer
    const footer = document.createElement('div');
    footer.className = 'flex items-center justify-end gap-3 p-6 border-t bg-gray-50';
    footer.innerHTML = `
        <button class="copy-text-btn flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <span>Copy</span>
        </button>
        <button class="close-modal-btn px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
            Đóng
        </button>
    `;
    
    // Assemble modal
    modalContent.appendChild(header);
    modalContent.appendChild(body);
    modalContent.appendChild(footer);
    modal.appendChild(modalContent);
    
    // Add event listeners
    modal.querySelectorAll('.close-modal-btn').forEach(btn => {
        btn.onclick = function() {
            document.body.removeChild(modal);
        };
    });
    
    footer.querySelector('.copy-text-btn').onclick = async function() {
        try {
            await navigator.clipboard.writeText(text);
            const btn = this;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Đã copy!</span>
            `;
            btn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
            btn.classList.add('bg-green-500');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('bg-green-500');
                btn.classList.add('bg-blue-500', 'hover:bg-blue-600');
            }, 2000);
        } catch (error) {
            console.error('Copy failed:', error);
            showToast('Không thể copy', 'error');
        }
    };
    
    document.body.appendChild(modal);
    console.log('[Modal] Modal opened successfully');
};




// Add event delegation for view full text buttons
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.view-full-text-btn');
    if (btn) {
        const text = btn.getAttribute('data-text');
        const title = btn.getAttribute('data-title');
        if (text && title) {
            // Decode HTML entities
            const textarea = document.createElement('textarea');
            textarea.innerHTML = text;
            const decodedText = textarea.value;
            showFullText(decodedText, title);
        }
    }
});


// Handle file upload for Summarize tab
document.addEventListener('DOMContentLoaded', function() {
    const summarizeFileInput = document.getElementById('summarize-file-input');
    const translateFileInput = document.getElementById('translate-file-input');
    
    console.log('[FileUpload] Initializing file upload handlers');
    console.log('[FileUpload] Summarize input:', summarizeFileInput);
    console.log('[FileUpload] Translate input:', translateFileInput);
    
    if (summarizeFileInput) {
        summarizeFileInput.addEventListener('change', async function(e) {
            console.log('[Summarize] File selected');
            const file = e.target.files[0];
            if (!file) return;

            console.log('[Summarize] File:', file.name, file.type, file.size);

            const fileType = file.name.split('.').pop().toLowerCase();
            const textArea = document.getElementById('summarize-text');
            const fileName = document.getElementById('summarize-file-name');

            if (!['pdf', 'txt', 'doc', 'docx'].includes(fileType)) {
                showToast('Chỉ chấp nhận file PDF, TXT, DOC, DOCX', 'error');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showToast('File vượt quá 10MB', 'error');
                return;
            }

            try {
                const text = await processUploadedFile(file);

                if (textArea) {
                    textArea.value = text;
                    showToast('Đã trích xuất văn bản từ file!', 'success');
                }

                if (fileName) {
                    fileName.textContent = `Đã tải: ${file.name}`;
                    fileName.classList.remove('hidden');
                }

                // Reset file input
                e.target.value = '';

            } catch (error) {
                console.error('[Summarize] Process file error:', error);
                showToast(error.message || 'Không thể đọc file', 'error');
            }
        });
    }
    
    if (translateFileInput) {
        translateFileInput.addEventListener('change', async function(e) {
            console.log('[Translate] File selected');
            const file = e.target.files[0];
            if (!file) return;

            console.log('[Translate] File:', file.name, file.type, file.size);

            const fileType = file.name.split('.').pop().toLowerCase();
            const textArea = document.getElementById('translate-text');
            const fileName = document.getElementById('translate-file-name');

            if (!['pdf', 'txt', 'doc', 'docx'].includes(fileType)) {
                showToast('Chỉ chấp nhận file PDF, TXT, DOC, DOCX', 'error');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showToast('File vượt quá 10MB', 'error');
                return;
            }

            try {
                const text = await processUploadedFile(file);

                if (textArea) {
                    textArea.value = text;
                    showToast('Đã trích xuất văn bản từ file!', 'success');
                }

                if (fileName) {
                    fileName.textContent = `Đã tải: ${file.name}`;
                    fileName.classList.remove('hidden');
                }

                // Reset file input
                e.target.value = '';

            } catch (error) {
                console.error('[Translate] Process file error:', error);
                showToast(error.message || 'Không thể đọc file', 'error');
            }
        });
    }
});


/**
 * Extract text from Word document using Mammoth.js
 */
async function extractTextFromWord(file) {
    try {
        if (typeof mammoth === 'undefined') {
            throw new Error('Mammoth.js library chưa được load');
        }
        const arrayBuffer = await file.arrayBuffer();
        const result = await mammoth.extractRawText({ arrayBuffer });
        return result.value;
    } catch (error) {
        console.error('[Word] Extraction error:', error);
        throw new Error('Không thể đọc file Word');
    }
}

/**
 * Process uploaded file and extract text
 */
async function processUploadedFile(file) {
    const fileType = file.name.split('.').pop().toLowerCase();
    let text = '';

    if (fileType === 'pdf') {
        showToast('Đang đọc file PDF...', 'info');
        text = await extractTextFromPDF(file);
    } else if (fileType === 'txt') {
        text = await file.text();
    } else if (fileType === 'doc' || fileType === 'docx') {
        showToast('Đang đọc file Word...', 'info');
        text = await extractTextFromWord(file);
    } else {
        throw new Error('Định dạng file này chưa được hỗ trợ');
    }

    return text;
}
