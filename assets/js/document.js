/**
 * Document Handling JavaScript
 * Handles file upload, PDF extraction, and history management
 */

/**
 * Upload document file
 * @param {File} file - File to upload
 * @returns {Promise<object>} Response data
 */
async function uploadDocument(file) {
    try {
        const formData = new FormData();
        formData.append('file', file);

        const response = await fetch(`${API_BASE}/document.php?action=upload`, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Upload failed');
        }

        if (data.success) {
            showToast('Upload thành công!', 'success');
        }

        return data;
    } catch (error) {
        console.error('Upload error:', error);
        showToast(error.message, 'error');
        throw error;
    }
}

/**
 * Extract text from PDF file using PDF.js
 * @param {File} file - PDF file
 * @returns {Promise<string>} Extracted text
 */
async function extractTextFromPDF(file) {
    try {
        const arrayBuffer = await file.arrayBuffer();
        
        // Load PDF
        const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        
        let fullText = '';
        
        // Extract text from each page
        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const textContent = await page.getTextContent();
            const pageText = textContent.items.map(item => item.str).join(' ');
            fullText += pageText + '\n\n';
        }
        
        return fullText.trim();
    } catch (error) {
        console.error('PDF extraction error:', error);
        showToast('Không thể đọc file PDF', 'error');
        throw error;
    }
}

/**
 * Get audio history
 * @param {number} page - Page number
 * @param {number} limit - Items per page
 * @returns {Promise<object>} History data
 */
async function getHistory(page = 1, limit = 20) {
    try {
        const response = await apiRequest(
            `${API_BASE}/document.php?action=history&page=${page}&limit=${limit}`
        );

        return response.data;
    } catch (error) {
        console.error('Get history error:', error);
        throw error;
    }
}

/**
 * Delete audio record
 * @param {number} id - Audio ID
 * @returns {Promise<boolean>} Success status
 */
async function deleteAudio(id) {
    if (!confirm('Bạn có chắc muốn xóa audio này?')) {
        return false;
    }

    try {
        const response = await apiRequest(
            `${API_BASE}/document.php?action=delete&id=${id}`,
            { method: 'DELETE' }
        );

        if (response.success) {
            showToast('Đã xóa thành công', 'success');
            // Refresh history list
            await loadHistory();
        }

        return response.success;
    } catch (error) {
        console.error('Delete audio error:', error);
        throw error;
    }
}

/**
 * Render history list
 * @param {array} items - History items
 */
function renderHistoryList(items) {
    const container = document.getElementById('history-list');
    if (!container) return;

    if (!items || items.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12 text-gray-500">
                <p class="text-lg">Chưa có lịch sử audio</p>
                <p class="text-sm mt-2">Hãy tạo audio đầu tiên của bạn!</p>
            </div>
        `;
        return;
    }

    container.innerHTML = '';

    items.forEach(item => {
        const div = document.createElement('div');
        div.className = 'bg-white p-4 rounded-lg shadow hover:shadow-md transition mb-4';
        div.id = `audio-item-${item.id}`;
        
        const textPreview = item.text.length > 100 
            ? item.text.substring(0, 100) + '...' 
            : item.text;

        div.innerHTML = `
            <div class="flex flex-col gap-4">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-gray-800 mb-2">${textPreview}</p>
                        <div class="flex flex-wrap gap-2 text-sm text-gray-600">
                            <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded">
                                🎙️ ${item.voice}
                            </span>
                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded">
                                📅 ${formatDate(item.created_at)}
                            </span>
                        </div>
                    </div>
                    <button 
                        onclick="deleteAudio(${item.id})" 
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded transition self-start"
                    >
                        🗑️ Xóa
                    </button>
                </div>
                
                <!-- Audio Player -->
                <div class="bg-gray-50 p-3 rounded-lg">
                    <audio 
                        id="audio-player-${item.id}" 
                        src="${item.audio_url}" 
                        class="w-full"
                        preload="metadata"
                        onloadedmetadata="initAudioPlayer(${item.id}, ${item.position})"
                    ></audio>
                    
                    <!-- Custom Controls -->
                    <div class="flex items-center gap-3 mt-2">
                        <button 
                            id="play-btn-${item.id}"
                            onclick="togglePlayPause(${item.id})"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition flex items-center gap-2"
                        >
                            <span id="play-icon-${item.id}">▶️</span>
                            <span id="play-text-${item.id}">Phát</span>
                        </button>
                        
                        <div class="flex-1 flex items-center gap-2">
                            <span id="current-time-${item.id}" class="text-sm text-gray-600 min-w-[45px]">0:00</span>
                            <input 
                                type="range" 
                                id="seek-bar-${item.id}"
                                class="flex-1 h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                                min="0" 
                                max="100" 
                                value="0"
                                oninput="seekAudio(${item.id}, this.value)"
                            >
                            <span id="duration-${item.id}" class="text-sm text-gray-600 min-w-[45px]">0:00</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">🔊</span>
                            <input 
                                type="range" 
                                id="volume-${item.id}"
                                class="w-20 h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                                min="0" 
                                max="100" 
                                value="100"
                                oninput="setVolume(${item.id}, this.value)"
                            >
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(div);
    });
}

/**
 * Load and display history
 */
async function loadHistory() {
    const container = document.getElementById('history-list');
    if (!container) return;

    try {
        container.innerHTML = '<div class="text-center py-8"><div class="spinner mx-auto"></div><p class="mt-2">Đang tải...</p></div>';
        
        const data = await getHistory();
        renderHistoryList(data.audios);

        // Render pagination if needed
        if (data.pages > 1) {
            renderPagination(data.current_page, data.pages);
        }
    } catch (error) {
        container.innerHTML = '<div class="text-center py-8 text-red-500">Không thể tải lịch sử</div>';
    }
}

/**
 * Render pagination
 * @param {number} currentPage - Current page
 * @param {number} totalPages - Total pages
 */
function renderPagination(currentPage, totalPages) {
    const paginationContainer = document.getElementById('pagination');
    if (!paginationContainer) return;

    paginationContainer.innerHTML = '';

    for (let i = 1; i <= totalPages; i++) {
        const button = document.createElement('button');
        button.textContent = i;
        button.className = i === currentPage 
            ? 'px-4 py-2 bg-purple-600 text-white rounded'
            : 'px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded';
        
        button.addEventListener('click', async function() {
            const data = await getHistory(i);
            renderHistoryList(data.audios);
            renderPagination(i, totalPages);
        });

        paginationContainer.appendChild(button);
    }
}

/**
 * Handle file upload form
 */
function handleFileUpload() {
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('drop-zone');
    const textPreview = document.getElementById('text-preview');
    const uploadBtn = document.getElementById('upload-btn');

    if (!fileInput || !dropZone) return;

    // Handle file selection
    fileInput.addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (file) {
            await processFile(file);
        }
    });

    // Handle drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-purple-500', 'bg-purple-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-purple-500', 'bg-purple-50');
    });

    dropZone.addEventListener('drop', async function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-purple-500', 'bg-purple-50');
        
        const file = e.dataTransfer.files[0];
        if (file) {
            await processFile(file);
        }
    });

    // Process uploaded file
    async function processFile(file) {
        const fileType = file.name.split('.').pop().toLowerCase();

        if (!['pdf', 'txt'].includes(fileType)) {
            showToast('Chỉ chấp nhận file PDF hoặc TXT', 'error');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            showToast('File vượt quá 10MB', 'error');
            return;
        }

        try {
            let text = '';

            if (fileType === 'pdf') {
                showToast('Đang đọc file PDF...', 'info');
                text = await extractTextFromPDF(file);
            } else {
                text = await file.text();
            }

            if (textPreview) {
                textPreview.value = text;
                textPreview.classList.remove('hidden');
            }

            // Upload file to server
            await uploadDocument(file);

        } catch (error) {
            console.error('Process file error:', error);
        }
    }
}

/**
 * Initialize audio player with saved position
 * @param {number} audioId - Audio ID
 * @param {number} savedPosition - Saved position in seconds
 */
function initAudioPlayer(audioId, savedPosition) {
    const audio = document.getElementById(`audio-player-${audioId}`);
    if (!audio) return;
    
    // Set saved position
    if (savedPosition > 0 && audio.duration) {
        audio.currentTime = savedPosition;
    }
    
    // Update duration display
    const duration = document.getElementById(`duration-${audioId}`);
    if (duration && audio.duration) {
        duration.textContent = formatTime(audio.duration);
    }
    
    // Update seek bar max
    const seekBar = document.getElementById(`seek-bar-${audioId}`);
    if (seekBar && audio.duration) {
        seekBar.max = audio.duration;
        seekBar.value = savedPosition;
    }
    
    // Update time as audio plays
    audio.addEventListener('timeupdate', function() {
        const currentTime = document.getElementById(`current-time-${audioId}`);
        if (currentTime) {
            currentTime.textContent = formatTime(audio.currentTime);
        }
        
        if (seekBar) {
            seekBar.value = audio.currentTime;
        }
        
        // Save position every 5 seconds
        if (Math.floor(audio.currentTime) % 5 === 0) {
            updatePosition(audioId, Math.floor(audio.currentTime));
        }
    });
    
    // Handle audio end
    audio.addEventListener('ended', function() {
        const playIcon = document.getElementById(`play-icon-${audioId}`);
        const playText = document.getElementById(`play-text-${audioId}`);
        if (playIcon) playIcon.textContent = '▶️';
        if (playText) playText.textContent = 'Phát';
        updatePosition(audioId, 0); // Reset position
    });
}

/**
 * Toggle play/pause for specific audio
 * @param {number} audioId - Audio ID
 */
function togglePlayPause(audioId) {
    const audio = document.getElementById(`audio-player-${audioId}`);
    const playIcon = document.getElementById(`play-icon-${audioId}`);
    const playText = document.getElementById(`play-text-${audioId}`);
    
    if (!audio) return;
    
    // Pause all other audios
    document.querySelectorAll('audio').forEach(a => {
        if (a.id !== `audio-player-${audioId}` && !a.paused) {
            a.pause();
        }
    });
    
    if (audio.paused) {
        audio.play();
        if (playIcon) playIcon.textContent = '⏸️';
        if (playText) playText.textContent = 'Tạm dừng';
    } else {
        audio.pause();
        if (playIcon) playIcon.textContent = '▶️';
        if (playText) playText.textContent = 'Phát';
    }
}

/**
 * Seek to specific position
 * @param {number} audioId - Audio ID
 * @param {number} value - Seek position
 */
function seekAudio(audioId, value) {
    const audio = document.getElementById(`audio-player-${audioId}`);
    if (audio) {
        audio.currentTime = value;
    }
}

/**
 * Set volume
 * @param {number} audioId - Audio ID
 * @param {number} value - Volume (0-100)
 */
function setVolume(audioId, value) {
    const audio = document.getElementById(`audio-player-${audioId}`);
    if (audio) {
        audio.volume = value / 100;
    }
}

/**
 * Format time in MM:SS
 * @param {number} seconds - Time in seconds
 * @returns {string} Formatted time
 */
function formatTime(seconds) {
    if (isNaN(seconds)) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${String(secs).padStart(2, '0')}`;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    handleFileUpload();
    handleSummarizeFileUpload();
    handleTranslateFileUpload();
    
    // Load history if on history tab
    if (document.getElementById('history-list')) {
        loadHistory();
    }
});


/**
 * Handle file upload for Summarize tab
 */
function handleSummarizeFileUpload() {
    // Use event delegation to handle file input even if element is hidden initially
    document.addEventListener('change', async function(e) {
        if (e.target && e.target.id === 'summarize-file-input') {
            console.log('File selected for summarize');
            const file = e.target.files[0];
            if (!file) return;

            const fileType = file.name.split('.').pop().toLowerCase();

            if (!['pdf', 'txt', 'doc', 'docx'].includes(fileType)) {
                showToast('Chỉ chấp nhận file PDF, TXT, DOC, DOCX', 'error');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showToast('File vượt quá 10MB', 'error');
                return;
            }

            try {
                let text = '';

                if (fileType === 'pdf') {
                    showToast('Đang đọc file PDF...', 'info');
                    // Use global function from dashboard.php
                    if (typeof window.extractTextFromPDF === 'function') {
                        text = await window.extractTextFromPDF(file);
                    } else {
                        throw new Error('PDF reader not available');
                    }
                } else if (fileType === 'doc' || fileType === 'docx') {
                    showToast('Đang đọc file Word...', 'info');
                    // Use global function from dashboard.php
                    if (typeof window.extractTextFromWord === 'function') {
                        text = await window.extractTextFromWord(file);
                    } else {
                        throw new Error('Word reader not available');
                    }
                } else {
                    text = await file.text();
                }

                const textArea = document.getElementById('summarize-text');
                const fileName = document.getElementById('summarize-file-name');
                const charCount = document.getElementById('summarize-char-count');

                // Truncate text if exceeds 10000 characters
                const maxLength = 10000;
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
                console.error('Process file error:', error);
                showToast(error.message || 'Không thể đọc file', 'error');
            }
        }
    });
}

/**
 * Handle file upload for Translate tab
 */
function handleTranslateFileUpload() {
    // Use event delegation to handle file input even if element is hidden initially
    document.addEventListener('change', async function(e) {
        if (e.target && e.target.id === 'translate-file-input') {
            console.log('File selected for translate');
            const file = e.target.files[0];
            if (!file) return;

            const fileType = file.name.split('.').pop().toLowerCase();

            if (!['pdf', 'txt', 'doc', 'docx'].includes(fileType)) {
                showToast('Chỉ chấp nhận file PDF, TXT, DOC, DOCX', 'error');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showToast('File vượt quá 10MB', 'error');
                return;
            }

            try {
                let text = '';

                if (fileType === 'pdf') {
                    showToast('Đang đọc file PDF...', 'info');
                    // Use global function from dashboard.php
                    if (typeof window.extractTextFromPDF === 'function') {
                        text = await window.extractTextFromPDF(file);
                    } else {
                        throw new Error('PDF reader not available');
                    }
                } else if (fileType === 'doc' || fileType === 'docx') {
                    showToast('Đang đọc file Word...', 'info');
                    // Use global function from dashboard.php
                    if (typeof window.extractTextFromWord === 'function') {
                        text = await window.extractTextFromWord(file);
                    } else {
                        throw new Error('Word reader not available');
                    }
                } else {
                    text = await file.text();
                }

                const textArea = document.getElementById('translate-text');
                const fileName = document.getElementById('translate-file-name');
                const charCount = document.getElementById('translate-char-count');

                // Truncate text if exceeds 10000 characters
                const maxLength = 10000;
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
                console.error('Process file error:', error);
                showToast('Không thể đọc file', 'error');
            }
        }
    });
}

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
        console.error('Word extraction error:', error);
        throw new Error('Không thể đọc file Word');
    }
}
