/**
 * Dashboard JavaScript
 * Handles all dashboard card interactions and recent activity
 */

/**
 * Handle audio play event - pause other audios and save their positions
 * @param {number} audioId - ID of the audio being played
 */
window.handleAudioPlay = function(audioId) {
    console.log(`[Dashboard] Audio ${audioId} started playing`);
    
    // Ưu tiên sử dụng AudioPlayerManager mới
    if (window.audioManager) {
        // AudioManager tự động xử lý pause others trong onPlay
        console.log(`[Dashboard] AudioManager handling play for ${audioId}`);
    }
    // Fallback về AudioPositionTracker cũ
    else if (window.audioTracker) {
        window.audioTracker.saveOnPlayAnother(audioId);
    }
}

// Quick Access Cards Management
class QuickAccessCards {
    constructor() {
        // NOTE: TTS Card initialization moved to tts-fix.js to prevent double-click issues
        // this.initTTSCard(); // DISABLED - handled by tts-fix.js
        this.initSummarizeCard();
        this.initTranslationCard();
        this.initFileManagementCard();
    }

    // TTS Card - DISABLED - Now handled by tts-fix.js
    initTTSCard() {
        console.log('[TTS] initTTSCard called but disabled - using tts-fix.js instead');
        // All TTS functionality moved to tts-fix.js to prevent double-click issues
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
    // NOTE: Translation is now handled in dashboard.php inline script to avoid duplicate handlers
    // This method is kept empty to prevent double event binding
    initTranslationCard() {
        console.log('[Translate] initTranslationCard called but disabled - using dashboard.php inline script instead');
        // All translation functionality moved to dashboard.php to prevent duplicate handlers
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
            const response = await apiRequest(`${API_BASE}/history.php?action=list&type=tts&limit=1000`);

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
                        class="w-64 audio-player" 
                        preload="metadata"
                        data-audio-id="${activity.id}"
                        data-saved-position="${activity.position || 0}"
                        onplay="handleAudioPlay(${activity.id})"
                    >
                        <source src="${audioUrl}" type="audio/mpeg">
                        Trình duyệt không hỗ trợ audio
                    </audio>
                </td>
                <td class="px-4 py-4">
                    <div class="flex gap-2">
                        <button 
                            onclick="openShareModal(${activity.id})"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm transition"
                            title="Chia sẻ"
                            data-audio-text="${escapeHtml(truncateText(activity.text || '', 50))}"
                        >
                            📤
                        </button>
                        <button 
                            onclick="deleteHistoryItem(${activity.id}, 'tts')" 
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm transition"
                            title="Xóa"
                        >
                            🗑️
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    attachEventListeners() {
        // Sử dụng AudioPlayerManager (mới) hoặc AudioPositionTracker (cũ)
        const audioElements = document.querySelectorAll('audio[data-audio-id]');

        audioElements.forEach(audio => {
            const audioId = parseInt(audio.dataset.audioId);
            const savedPosition = parseInt(audio.dataset.savedPosition) || 0;

            // Ưu tiên sử dụng AudioPlayerManager mới
            if (window.audioManager) {
                window.audioManager.register(audio, audioId, savedPosition);
                console.log(`[History] Registered audio ${audioId} with AudioManager, saved position: ${savedPosition}s`);
            }
            // Fallback về AudioPositionTracker cũ
            else if (window.audioTracker) {
                window.audioTracker.track(audio, audioId, 'history');
                
                // Khôi phục position đã lưu
                if (savedPosition > 0) {
                    window.audioTracker.restorePosition(audio, savedPosition);
                }
                
                console.log(`[History] Registered audio ${audioId} with AudioTracker, saved position: ${savedPosition}s`);
            } else {
                // Fallback cuối cùng nếu không có tracker nào
                console.warn('[History] No audio tracker available, using basic fallback');
                
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
            }
        });

        // Attach view full text button listeners
        this.attachViewFullTextListeners();
    }

    attachViewFullTextListeners() {
        // Attach click listeners for view full text buttons
        document.querySelectorAll('.view-full-text-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const text = this.dataset.text;
                const title = this.dataset.title || 'Văn bản';
                if (typeof showFullTextModal === 'function') {
                    showFullTextModal(text, title);
                }
            });
        });
    }

    async savePosition(audioId, position) {
        try {
            await apiRequest(`${API_BASE}/update_position.php`, {
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
        tab.addEventListener('click', function () {
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
window.filterHistory = function (type) {
    console.log('filterHistory called with type:', type);

    // Lưu position trước khi filter - ưu tiên AudioManager
    if (window.audioManager) {
        window.audioManager.saveAllPositions('filter');
        window.audioManager.unregisterAll();
    } else if (window.audioTracker) {
        window.audioTracker.saveOnFilter();
    }

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

    console.log('Elements found:', { tableView, cardView, pagination });

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
window.loadHistory = async function (type = 'tts', page = 1) {
    // Lưu position trước khi reload - ưu tiên AudioManager
    if (window.audioManager) {
        window.audioManager.saveAllPositions('reload');
        window.audioManager.unregisterAll();
    } else if (window.audioTracker) {
        window.audioTracker.saveOnReload();
    }

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
window.deleteHistoryItem = async function (id, type) {
    if (!confirm('Bạn có chắc chắn muốn xóa mục này?')) {
        return;
    }

    // Cleanup audio trước khi xóa (nếu là TTS)
    if (type === 'tts') {
        if (window.audioManager) {
            window.audioManager.unregister(id);
        } else if (window.audioTracker) {
            window.audioTracker.saveBeforeDelete(id);
        }
    }

    try {
        const response = await apiRequest(`${API_BASE}/history.php?action=delete`, {
            method: 'POST',
            body: JSON.stringify({ id, type })
        });

        if (response.success) {
            showToast('Đã xóa thành công', 'success');
            // Reload current view
            if (type === 'tts' && typeof recentActivity !== 'undefined') {
                recentActivity.loadActivities();
            } else {
                loadHistory(currentHistoryFilter, currentHistoryPage);
            }
        } else {
            showToast(response.error || 'Không thể xóa', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showToast('Không thể xóa mục này', 'error');
    }
}

/**
 * Go to page with position saving
 */
window.goToPage = function(page) {
    // Lưu position trước khi chuyển trang - ưu tiên AudioManager
    if (window.audioManager) {
        window.audioManager.saveAllPositions('pagination');
        window.audioManager.unregisterAll();
    } else if (window.audioTracker) {
        window.audioTracker.saveOnPagination();
    }
    currentHistoryPage = page;
    loadHistory(currentHistoryFilter, page);
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
            <button onclick="goToPage(${currentPage - 1})" 
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
            <button onclick="goToPage(1)" 
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
            <button onclick="goToPage(${i})" 
                    class="px-4 py-2 border rounded-lg transition ${isActive
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
            <button onclick="goToPage(${totalPages})" 
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                ${totalPages}
            </button>
        `;
    }

    // Next button
    if (currentPage < totalPages) {
        html += `
            <button onclick="goToPage(${currentPage + 1})" 
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
window.showFullText = function (text, title) {
    console.log('[Modal] Opening modal with text length:', text ? text.length : 0);

    // Create modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    modal.onclick = function (e) {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    };

    // Create modal content
    const modalContent = document.createElement('div');
    modalContent.className = 'bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col';
    modalContent.onclick = function (e) {
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
        <button class="download-text-btn flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Tải về TXT</span>
        </button>
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
        btn.onclick = function () {
            document.body.removeChild(modal);
        };
    });

    // Download button handler
    footer.querySelector('.download-text-btn').onclick = function () {
        try {
            // Create filename from title
            const filename = title.replace(/[^a-z0-9]/gi, '-').toLowerCase() + '.txt';
            downloadAsText(text, filename);
        } catch (error) {
            console.error('Download failed:', error);
            showToast('Không thể tải file', 'error');
        }
    };

    // Copy button handler
    footer.querySelector('.copy-text-btn').onclick = async function () {
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
document.addEventListener('click', function (e) {
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
document.addEventListener('DOMContentLoaded', function () {
    const summarizeFileInput = document.getElementById('summarize-file-input');
    const translateFileInput = document.getElementById('translate-file-input');

    console.log('[FileUpload] Initializing file upload handlers');
    console.log('[FileUpload] Summarize input:', summarizeFileInput);
    console.log('[FileUpload] Translate input:', translateFileInput);

    if (summarizeFileInput) {
        summarizeFileInput.addEventListener('change', async function (e) {
            console.log('[Summarize] File selected');
            const file = e.target.files[0];
            if (!file) return;

            console.log('[Summarize] File:', file.name, file.type, file.size);

            const fileType = file.name.split('.').pop().toLowerCase();
            const textArea = document.getElementById('summarize-text');
            const fileName = document.getElementById('summarize-file-name');
            const charCount = document.getElementById('summarize-char-count');
            const maxLength = 10000;

            if (!['pdf', 'txt', 'doc', 'docx'].includes(fileType)) {
                showToast('Chỉ chấp nhận file PDF, TXT, DOC, DOCX', 'error');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showToast('File vượt quá 10MB', 'error');
                return;
            }

            try {
                let text = await processUploadedFile(file);
                
                // Truncate if exceeds max length
                let truncated = false;
                if (text.length > maxLength) {
                    text = text.substring(0, maxLength);
                    truncated = true;
                }

                if (textArea) {
                    textArea.value = text;
                    if (truncated) {
                        showToast(`Văn bản đã được cắt còn ${maxLength} ký tự`, 'warning');
                    } else {
                        showToast('Đã trích xuất văn bản từ file!', 'success');
                    }
                }
                
                // Update character counter
                if (charCount) {
                    charCount.textContent = `${text.length} / ${maxLength}`;
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
        translateFileInput.addEventListener('change', async function (e) {
            console.log('[Translate] File selected');
            const file = e.target.files[0];
            if (!file) return;

            console.log('[Translate] File:', file.name, file.type, file.size);

            const fileType = file.name.split('.').pop().toLowerCase();
            const textArea = document.getElementById('translate-text');
            const fileName = document.getElementById('translate-file-name');
            const charCount = document.getElementById('translate-char-count');
            const maxLength = 10000;

            if (!['pdf', 'txt', 'doc', 'docx'].includes(fileType)) {
                showToast('Chỉ chấp nhận file PDF, TXT, DOC, DOCX', 'error');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showToast('File vượt quá 10MB', 'error');
                return;
            }

            try {
                let text = await processUploadedFile(file);
                
                // Truncate if exceeds max length
                let truncated = false;
                if (text.length > maxLength) {
                    text = text.substring(0, maxLength);
                    truncated = true;
                }

                if (textArea) {
                    textArea.value = text;
                    if (truncated) {
                        showToast(`Văn bản đã được cắt còn ${maxLength} ký tự`, 'warning');
                    } else {
                        showToast('Đã trích xuất văn bản từ file!', 'success');
                    }
                }
                
                // Update character counter
                if (charCount) {
                    charCount.textContent = `${text.length} / ${maxLength}`;
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


// Audio Resume Playback Feature
(function () {
    console.log('[AudioResume] Initializing audio resume playback');

    // Track all audio elements
    const audioElements = new Map();

    // Save position to server
    async function saveAudioPosition(audioId, position) {
        try {
            console.log(`[AudioResume] Saving position for audio ${audioId}: ${position}s`);
            await apiRequest(`${API_BASE}/history.php?action=update-position`, {
                method: 'POST',
                body: JSON.stringify({
                    id: audioId,
                    type: 'tts',
                    position: Math.floor(position)
                })
            });
        } catch (error) {
            console.error('[AudioResume] Failed to save position:', error);
        }
    }

    // Initialize audio element with saved position
    function initAudioElement(audio) {
        const audioId = parseInt(audio.dataset.audioId);
        const savedPosition = parseInt(audio.dataset.savedPosition) || 0;

        if (!audioId) return;

        console.log(`[AudioResume] Init audio ${audioId}, saved position: ${savedPosition}s`);

        // Store reference
        audioElements.set(audioId, audio);

        // Set saved position when metadata is loaded
        audio.addEventListener('loadedmetadata', function () {
            if (savedPosition > 0 && savedPosition < audio.duration) {
                audio.currentTime = savedPosition;
                console.log(`[AudioResume] Resumed audio ${audioId} at ${savedPosition}s`);
            }
        }, { once: true });

        // Save position every 5 seconds while playing
        let saveInterval;
        audio.addEventListener('play', function () {
            console.log(`[AudioResume] Audio ${audioId} started playing`);
            saveInterval = setInterval(() => {
                if (!audio.paused && audio.currentTime > 0) {
                    saveAudioPosition(audioId, audio.currentTime);
                }
            }, 5000);
        });

        // Save position when paused
        audio.addEventListener('pause', function () {
            console.log(`[AudioResume] Audio ${audioId} paused at ${audio.currentTime}s`);
            clearInterval(saveInterval);
            if (audio.currentTime > 0 && audio.currentTime < audio.duration) {
                saveAudioPosition(audioId, audio.currentTime);
            }
        });

        // Reset position when ended
        audio.addEventListener('ended', function () {
            console.log(`[AudioResume] Audio ${audioId} ended, resetting position`);
            clearInterval(saveInterval);
            saveAudioPosition(audioId, 0);
        });
    }

    // Initialize all audio elements on page
    function initAllAudioElements() {
        const audios = document.querySelectorAll('audio.audio-player[data-audio-id]');
        console.log(`[AudioResume] Found ${audios.length} audio elements`);
        audios.forEach(initAudioElement);
    }

    // Save all playing audio positions before page unload
    window.addEventListener('beforeunload', function () {
        console.log('[AudioResume] Page unloading, saving all positions');
        audioElements.forEach((audio, audioId) => {
            if (!audio.paused && audio.currentTime > 0) {
                // Use sendBeacon for reliable save on page unload
                const data = JSON.stringify({
                    id: audioId,
                    type: 'tts',
                    position: Math.floor(audio.currentTime)
                });
                navigator.sendBeacon(`${API_BASE}/history.php?action=update-position`, data);
            }
        });
    });

    // Save positions when switching tabs (page visibility change)
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            console.log('[AudioResume] Tab hidden, saving all positions');
            audioElements.forEach((audio, audioId) => {
                if (!audio.paused && audio.currentTime > 0) {
                    saveAudioPosition(audioId, audio.currentTime);
                }
            });
        }
    });

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllAudioElements);
    } else {
        initAllAudioElements();
    }

    // Re-initialize when history is loaded (for dynamic content)
    window.addEventListener('historyLoaded', initAllAudioElements);

    // Expose function for manual initialization
    window.initAudioResume = initAllAudioElements;
})();


// Trigger audio resume after history loads
const originalLoadActivities = window.RecentActivity?.prototype?.loadActivities;
if (originalLoadActivities) {
    window.RecentActivity.prototype.loadActivities = async function () {
        await originalLoadActivities.call(this);
        // Trigger audio resume initialization
        setTimeout(() => {
            if (window.initAudioResume) {
                window.initAudioResume();
            }
        }, 500);
    };
}


// ==================== AUDIO SHARING FUNCTIONS ====================

let shareModalAudioId = null;
let shareCategories = [];

/**
 * Open share modal
 */
window.openShareModal = async function(audioId, audioText = '') {
    shareModalAudioId = audioId;
    
    // If audioText not provided, try to get from button's data attribute
    if (!audioText) {
        const btn = document.querySelector(`button[onclick="openShareModal(${audioId})"]`);
        if (btn && btn.dataset.audioText) {
            audioText = btn.dataset.audioText;
        }
    }
    
    // Load categories if not loaded
    if (shareCategories.length === 0) {
        await loadShareCategories();
    }
    
    // Create modal if not exists
    let modal = document.getElementById('share-modal');
    if (!modal) {
        modal = createShareModal();
        document.body.appendChild(modal);
    }
    
    // Reset form
    document.getElementById('share-title').value = audioText || '';
    document.getElementById('share-description').value = '';
    document.getElementById('share-type').value = 'link';
    toggleShareType('link');
    
    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

/**
 * Close share modal
 */
window.closeShareModal = function() {
    const modal = document.getElementById('share-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    shareModalAudioId = null;
}

/**
 * Create share modal HTML
 */
function createShareModal() {
    const modal = document.createElement('div');
    modal.id = 'share-modal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50';
    modal.onclick = function(e) {
        if (e.target === modal) closeShareModal();
    };
    
    const categoryOptions = shareCategories.map(cat => 
        `<option value="${cat.id}">${cat.name}</option>`
    ).join('');
    
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold">📤 Chia sẻ Audio</h3>
                    <button onclick="closeShareModal()" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Share Type Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Loại chia sẻ</label>
                    <select id="share-type" onchange="toggleShareType(this.value)" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="link"> Tạo link chia sẻ</option>
                        <option value="public">Chia sẻ công khai (cần duyệt)</option>
                    </select>
                </div>
                
                <!-- Title -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề</label>
                    <input type="text" id="share-title" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nhập tiêu đề...">
                </div>
                
                <!-- Public share fields -->
                <div id="public-share-fields" class="hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Danh mục</label>
                        <select id="share-category" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            ${categoryOptions}
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả (tùy chọn)</label>
                        <textarea id="share-description" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Mô tả ngắn về audio..."></textarea>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                        <p class="text-sm text-yellow-800">⚠️ Yêu cầu chia sẻ công khai sẽ được Admin xem xét trước khi hiển thị.</p>
                    </div>
                </div>
                
                <!-- Share Link Result -->
                <div id="share-link-result" class="hidden mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Link chia sẻ</label>
                    <div class="flex gap-2">
                        <input type="text" id="share-link-url" readonly class="flex-1 border border-gray-300 rounded-lg px-4 py-2 bg-gray-50">
                        <button onclick="copyShareLink()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                            Copy
                        </button>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-3">
                    <button onclick="closeShareModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Hủy
                    </button>
                    <button id="share-submit-btn" onclick="submitShare()" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        Chia sẻ
                    </button>
                </div>
            </div>
        </div>
    `;
    
    return modal;
}

/**
 * Toggle share type fields
 */
window.toggleShareType = function(type) {
    const publicFields = document.getElementById('public-share-fields');
    const linkResult = document.getElementById('share-link-result');
    const submitBtn = document.getElementById('share-submit-btn');
    
    if (type === 'public') {
        publicFields.classList.remove('hidden');
        submitBtn.textContent = 'Gửi yêu cầu';
    } else {
        publicFields.classList.add('hidden');
        submitBtn.textContent = 'Tạo link';
    }
    
    linkResult.classList.add('hidden');
}

/**
 * Load share categories
 */
async function loadShareCategories() {
    try {
        const response = await apiRequest(`${API_BASE}/share.php?action=categories`);
        if (response.success && response.data.categories) {
            shareCategories = response.data.categories;
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

/**
 * Submit share request
 */
window.submitShare = async function() {
    if (!shareModalAudioId) return;
    
    const shareType = document.getElementById('share-type').value;
    const title = document.getElementById('share-title').value.trim();
    
    if (!title) {
        showToast('Vui lòng nhập tiêu đề', 'error');
        return;
    }
    
    const submitBtn = document.getElementById('share-submit-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Đang xử lý...';
    
    try {
        if (shareType === 'link') {
            // Create share link
            const response = await apiRequest(`${API_BASE}/share.php?action=create-link`, {
                method: 'POST',
                body: JSON.stringify({
                    audio_id: shareModalAudioId,
                    title: title
                })
            });
            
            if (response.success) {
                document.getElementById('share-link-url').value = response.share_url;
                document.getElementById('share-link-result').classList.remove('hidden');
                submitBtn.textContent = 'Đã tạo link!';
                showToast('Đã tạo link chia sẻ!', 'success');
            } else {
                showToast(response.error || 'Không thể tạo link', 'error');
                submitBtn.textContent = 'Tạo link';
            }
        } else {
            // Request public share
            const categoryId = document.getElementById('share-category').value;
            const description = document.getElementById('share-description').value.trim();
            
            const response = await apiRequest(`${API_BASE}/share.php?action=request-public`, {
                method: 'POST',
                body: JSON.stringify({
                    audio_id: shareModalAudioId,
                    category_id: categoryId,
                    title: title,
                    description: description
                })
            });
            
            if (response.success) {
                showToast('Đã gửi yêu cầu chia sẻ công khai!', 'success');
                closeShareModal();
            } else {
                showToast(response.error || 'Không thể gửi yêu cầu', 'error');
                submitBtn.textContent = 'Gửi yêu cầu';
            }
        }
    } catch (error) {
        console.error('Share error:', error);
        showToast('Lỗi kết nối', 'error');
        submitBtn.textContent = shareType === 'link' ? 'Tạo link' : 'Gửi yêu cầu';
    }
    
    submitBtn.disabled = false;
}

/**
 * Copy share link to clipboard
 */
window.copyShareLink = async function() {
    const linkInput = document.getElementById('share-link-url');
    try {
        await navigator.clipboard.writeText(linkInput.value);
        showToast('Đã copy link!', 'success');
    } catch (error) {
        // Fallback
        linkInput.select();
        document.execCommand('copy');
        showToast('Đã copy link!', 'success');
    }
}


// ==================== MY SHARES TAB FUNCTIONS ====================

let currentShareTab = 'link';

/**
 * Load my shares
 */
window.loadMyShares = async function(type = 'link') {
    currentShareTab = type;
    
    // Update tab styles
    document.querySelectorAll('.share-tab').forEach(tab => {
        tab.classList.remove('text-blue-600', 'border-blue-500');
        tab.classList.add('text-gray-600', 'border-transparent');
    });
    
    const activeTab = document.getElementById(`share-tab-${type}`);
    if (activeTab) {
        activeTab.classList.remove('text-gray-600', 'border-transparent');
        activeTab.classList.add('text-blue-600', 'border-blue-500');
    }
    
    const container = document.getElementById('my-shares-container');
    container.innerHTML = `
        <div class="text-center py-12">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-gray-500">Đang tải...</p>
        </div>
    `;
    
    try {
        const response = await apiRequest(`${API_BASE}/share.php?action=my-shares`);
        
        if (response.success && response.data) {
            const items = type === 'link' ? response.data.link_shares : response.data.public_shares;
            
            if (!items || items.length === 0) {
                container.innerHTML = renderEmptyShareState(type);
            } else {
                container.innerHTML = items.map(item => 
                    type === 'link' ? renderLinkShareItem(item) : renderPublicShareItem(item)
                ).join('');
            }
        }
    } catch (error) {
        console.error('Error loading shares:', error);
        container.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-500">⚠️ Không thể tải dữ liệu</p>
            </div>
        `;
    }
}

/**
 * Render link share item
 */
function renderLinkShareItem(item) {
    const truncatedText = item.text.length > 100 ? item.text.substring(0, 100) + '...' : item.text;
    const createdAt = new Date(item.created_at).toLocaleString('vi-VN');
    
    return `
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h3 class="font-bold text-gray-900 mb-1">${escapeHtml(item.title || 'Không có tiêu đề')}</h3>
                    <p class="text-sm text-gray-500">${createdAt} •  ${item.views} lượt xem</p>
                </div>
                <button onclick="deleteLinkShare(${item.id})" class="text-red-500 hover:text-red-700 transition" title="Xóa">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
            
            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                <p class="text-sm text-gray-700">${escapeHtml(truncatedText)}</p>
            </div>
            
            <div class="flex items-center gap-2">
                <input type="text" readonly value="${item.share_url}" class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
                <button onclick="copyToClipboard('${item.share_url}')" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                    Copy
                </button>
                <a href="${item.share_url}" target="_blank" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                    Xem
                </a>
            </div>
        </div>
    `;
}

/**
 * Render public share item
 */
function renderPublicShareItem(item) {
    const truncatedText = item.text.length > 100 ? item.text.substring(0, 100) + '...' : item.text;
    const createdAt = new Date(item.created_at).toLocaleString('vi-VN');
    
    const statusBadge = {
        'pending': '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">⏳ Chờ duyệt</span>',
        'approved': '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">✅ Đã duyệt</span>',
        'rejected': '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">❌ Từ chối</span>'
    };
    
    let actions = '';
    if (item.status === 'pending') {
        actions = `
            <button onclick="cancelPublicShare(${item.id})" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                Hủy yêu cầu
            </button>
        `;
    }
    
    return `
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-bold text-gray-900">${escapeHtml(item.title)}</h3>
                        ${statusBadge[item.status]}
                    </div>
                    <p class="text-sm text-gray-500"> ${escapeHtml(item.category_name)} • ${createdAt}</p>
                </div>
            </div>
            
            ${item.description ? `<p class="text-gray-600 text-sm mb-3">${escapeHtml(item.description)}</p>` : ''}
            
            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                <p class="text-sm text-gray-700">${escapeHtml(truncatedText)}</p>
            </div>
            
            ${item.admin_note ? `
                <div class="bg-red-50 p-3 rounded-lg mb-4">
                    <p class="text-sm text-red-700"><strong>Lý do từ chối:</strong> ${escapeHtml(item.admin_note)}</p>
                </div>
            ` : ''}
            
            <div class="flex items-center justify-between">
                <audio controls class="h-10">
                    <source src="${item.audio_url}" type="audio/mpeg">
                </audio>
                <div class="flex gap-2">
                    ${actions}
                </div>
            </div>
        </div>
    `;
}

/**
 * Render empty state for shares
 */
function renderEmptyShareState(type) {
    const messages = {
        'link': {
            icon: '',
            title: 'Chưa có link chia sẻ nào',
            desc: 'Tạo link chia sẻ từ lịch sử audio để chia sẻ với bạn bè'
        },
        'public': {
            icon: '',
            title: 'Chưa có yêu cầu chia sẻ công khai nào',
            desc: 'Gửi yêu cầu chia sẻ công khai để audio của bạn xuất hiện trên trang Khám phá'
        }
    };
    
    const msg = messages[type];
    
    return `
        <div class="text-center py-16">
            <div class="text-6xl mb-4">${msg.icon}</div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">${msg.title}</h3>
            <p class="text-gray-500">${msg.desc}</p>
        </div>
    `;
}

/**
 * Delete link share
 */
window.deleteLinkShare = async function(id) {
    if (!confirm('Bạn có chắc muốn xóa link chia sẻ này?')) return;
    
    try {
        const response = await apiRequest(`${API_BASE}/share.php?action=delete-link`, {
            method: 'POST',
            body: JSON.stringify({ id })
        });
        
        if (response.success) {
            showToast('Đã xóa link chia sẻ', 'success');
            loadMyShares('link');
        } else {
            showToast(response.error || 'Không thể xóa', 'error');
        }
    } catch (error) {
        showToast('Lỗi kết nối', 'error');
    }
}

/**
 * Cancel public share request
 */
window.cancelPublicShare = async function(id) {
    if (!confirm('Bạn có chắc muốn hủy yêu cầu này?')) return;
    
    try {
        const response = await apiRequest(`${API_BASE}/share.php?action=cancel-request`, {
            method: 'POST',
            body: JSON.stringify({ id })
        });
        
        if (response.success) {
            showToast('Đã hủy yêu cầu', 'success');
            loadMyShares('public');
        } else {
            showToast(response.error || 'Không thể hủy', 'error');
        }
    } catch (error) {
        showToast('Lỗi kết nối', 'error');
    }
}

/**
 * Copy text to clipboard
 */
window.copyToClipboard = async function(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Đã copy!', 'success');
    } catch (error) {
        showToast('Không thể copy', 'error');
    }
}
