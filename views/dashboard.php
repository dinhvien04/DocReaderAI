<?php
$pageTitle = 'Dashboard - DocReader AI Studio';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Welcome Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            👋 Xin chào, <?= htmlspecialchars($currentUser['email']) ?>
        </h1>
        <p class="text-gray-600">Chào mừng bạn đến với Dashboard</p>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px overflow-x-auto">
                <button onclick="switchTab('tts')" id="tab-tts" class="tab-button active px-6 py-4 text-sm font-medium border-b-2 border-purple-600 text-purple-600">
                    🎙️ Text-to-Speech
                </button>
                <button onclick="switchTab('upload')" id="tab-upload" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    📄 Upload Document
                </button>
                <button onclick="switchTab('translate')" id="tab-translate" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    🌐 Translate
                </button>
                <button onclick="switchTab('history')" id="tab-history" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    📊 History
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- TTS Tab -->
            <div id="content-tts" class="tab-content">
                <h2 class="text-2xl font-bold mb-6">🎙️ Chuyển đổi văn bản thành giọng nói</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Văn bản</label>
                        <textarea id="tts-text" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Nhập văn bản cần chuyển đổi..."></textarea>
                        <div class="text-right text-sm text-gray-500 mt-1">
                            <span id="char-count">0 / 5000</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Giọng đọc</label>
                            <select id="voice-select" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="vi-VN-HoaiMyNeural">Hoài My (Nữ - Miền Bắc)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tốc độ: <span id="speed-display">1x</span></label>
                            <input id="speed-input" type="range" min="0" max="2" step="1" value="1" class="w-full">
                        </div>
                    </div>
                    <button id="convert-btn" class="w-full gradient-bg text-white py-3 rounded-lg font-medium hover:opacity-90 transition">
                        Chuyển đổi
                    </button>
                    <audio id="audio-player" controls class="w-full hidden mt-4"></audio>
                </div>
            </div>

            <!-- Upload Tab -->
            <div id="content-upload" class="tab-content hidden">
                <h2 class="text-2xl font-bold mb-6">📄 Upload tài liệu</h2>
                <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-purple-500 transition cursor-pointer">
                    <div class="text-6xl mb-4">📁</div>
                    <p class="text-lg font-medium text-gray-700 mb-2">Kéo thả file vào đây hoặc click để chọn</p>
                    <p class="text-sm text-gray-500">Hỗ trợ PDF, TXT (Tối đa 10MB)</p>
                    <input type="file" id="file-input" accept=".pdf,.txt" class="hidden">
                </div>
                <textarea id="text-preview" rows="10" class="w-full px-4 py-3 border border-gray-300 rounded-lg mt-6 hidden" placeholder="Văn bản trích xuất sẽ hiển thị ở đây..."></textarea>
            </div>

            <!-- Translate Tab -->
            <div id="content-translate" class="tab-content hidden">
                <h2 class="text-2xl font-bold mb-6">🌐 Dịch thuật</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Văn bản gốc</label>
                        <textarea id="translate-text" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Nhập văn bản cần dịch..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ngôn ngữ đích</label>
                        <select id="target-lang" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="en">🇬🇧 Tiếng Anh</option>
                            <option value="vi">🇻🇳 Tiếng Việt</option>
                            <option value="ja">🇯🇵 Tiếng Nhật</option>
                            <option value="ko">🇰🇷 Tiếng Hàn</option>
                            <option value="zh">🇨🇳 Tiếng Trung</option>
                        </select>
                    </div>
                    <button id="translate-btn" class="w-full gradient-bg text-white py-3 rounded-lg font-medium hover:opacity-90 transition">
                        Dịch
                    </button>
                    <div id="translate-result" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kết quả</label>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <p id="translated-text" class="text-gray-800"></p>
                        </div>
                        <button onclick="copyToClipboard(document.getElementById('translated-text').textContent)" class="mt-2 text-purple-600 hover:text-purple-700 text-sm font-medium">
                            📋 Copy
                        </button>
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div id="content-history" class="tab-content hidden">
                <h2 class="text-2xl font-bold mb-6">📊 Lịch sử Audio</h2>
                <div id="history-list"></div>
                <div id="pagination" class="flex justify-center gap-2 mt-6"></div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-button').forEach(el => {
        el.classList.remove('active', 'border-purple-600', 'text-purple-600');
        el.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab
    document.getElementById('content-' + tabName).classList.remove('hidden');
    const btn = document.getElementById('tab-' + tabName);
    btn.classList.add('active', 'border-purple-600', 'text-purple-600');
    btn.classList.remove('border-transparent', 'text-gray-500');
    
    // Load history when switching to history tab
    if (tabName === 'history') {
        loadHistory();
    }
}

// Initialize dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle translate button
    const translateBtn = document.getElementById('translate-btn');
    if (translateBtn) {
        translateBtn.addEventListener('click', async function() {
            const text = document.getElementById('translate-text').value.trim();
            const targetLang = document.getElementById('target-lang').value;
            
            if (!text) {
                showToast('Vui lòng nhập văn bản', 'error');
                return;
            }
            
            setLoading(this, true);
            try {
                const response = await apiRequest(`${API_BASE}/translate.php?action=translate`, {
                    method: 'POST',
                    body: JSON.stringify({ text, targetLang })
                });
                
                if (response.success) {
                    document.getElementById('translated-text').textContent = response.data.translated_text;
                    document.getElementById('translate-result').classList.remove('hidden');
                }
            } catch (error) {
                // Error handled
            } finally {
                setLoading(this, false);
            }
        });
    }

    // Handle drop zone click
    const dropZone = document.getElementById('drop-zone');
    if (dropZone) {
        dropZone.addEventListener('click', function() {
            document.getElementById('file-input').click();
        });
    }
});
</script>

<style>
.tab-button.active {
    border-bottom-width: 2px;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
