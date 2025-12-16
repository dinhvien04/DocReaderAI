<?php
/**
 * Shared Audio View Page
 * Displays audio shared via link
 */
$pageTitle = 'Audio được chia sẻ - DocReader AI Studio';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$shareCode = $_GET['code'] ?? '';
?>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-12">
        <div id="content-container" class="max-w-2xl mx-auto">
            <!-- Loading State -->
            <div id="loading-state" class="bg-white rounded-xl shadow-lg p-8 text-center">
                <div class="animate-spin w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                <p class="text-gray-500">Đang tải audio...</p>
            </div>
            
            <!-- Error State -->
            <div id="error-state" class="bg-white rounded-xl shadow-lg p-8 text-center hidden">
                <div class="text-6xl mb-4">😔</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Không tìm thấy audio</h2>
                <p id="error-message" class="text-gray-500 mb-6">Link chia sẻ không tồn tại hoặc đã hết hạn</p>
                <a href="<?= BASE_URL ?><?= isLoggedIn() ? '/dashboard' : '' ?>" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    <?= isLoggedIn() ? 'Về Dashboard' : 'Về trang chủ' ?>
                </a>
            </div>
            
            <!-- Audio Content -->
            <div id="audio-content" class="hidden">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                            </div>
                            <div>
                                <h1 id="audio-title" class="text-2xl font-bold">Audio được chia sẻ</h1>
                                <p id="audio-author" class="text-white/80 text-sm">Chia sẻ bởi ***</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Audio Player -->
                    <div class="p-6">
                        <audio id="audio-player" controls class="w-full mb-6"></audio>
                        
                        <!-- Text Content -->
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Nội dung văn bản</h3>
                            <div id="audio-text" class="bg-gray-50 p-4 rounded-lg text-gray-700 max-h-64 overflow-y-auto whitespace-pre-wrap"></div>
                        </div>
                        
                        <!-- Meta Info -->
                        <div class="flex items-center justify-between text-sm text-gray-500 border-t pt-4">
                            <div class="flex items-center gap-4">
                                <span id="audio-voice">🎤 Giọng đọc</span>
                                <span id="audio-views">👁️ 0 lượt xem</span>
                            </div>
                            <button onclick="copyShareLink()" class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                </svg>
                                Copy link
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php if (!isLoggedIn()): ?>
                <!-- CTA for guests -->
                <div class="mt-8 bg-blue-50 rounded-xl p-6 text-center">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Bạn muốn tạo audio của riêng mình?</h3>
                    <p class="text-gray-600 mb-4">Đăng ký miễn phí và bắt đầu chuyển văn bản thành giọng nói AI ngay hôm nay!</p>
                    <a href="<?= BASE_URL ?>/register" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                        Đăng ký miễn phí
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>

    <script>
    const API_BASE = '<?= BASE_URL ?>/api';
    const shareCode = '<?= htmlspecialchars($shareCode) ?>';

    async function loadSharedAudio() {
        if (!shareCode) {
            showError('Mã chia sẻ không hợp lệ');
            return;
        }

        try {
            const response = await fetch(`${API_BASE}/share.php?action=view-link&code=${shareCode}`);
            const data = await response.json();

            if (data.success && data.data) {
                displayAudio(data.data);
            } else {
                showError(data.error || 'Không tìm thấy audio');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Lỗi kết nối server');
        }
    }

    function displayAudio(audio) {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('audio-content').classList.remove('hidden');

        document.getElementById('audio-title').textContent = audio.title || 'Audio được chia sẻ';
        document.getElementById('audio-author').textContent = `Chia sẻ bởi ${audio.author}`;
        document.getElementById('audio-player').src = audio.audio_url;
        document.getElementById('audio-text').textContent = audio.text;
        document.getElementById('audio-voice').textContent = `🎤 ${audio.voice}`;
        document.getElementById('audio-views').textContent = `👁️ ${audio.views + 1} lượt xem`;
    }

    function showError(message) {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('error-state').classList.remove('hidden');
        document.getElementById('error-message').textContent = message;
    }

    async function copyShareLink() {
        try {
            await navigator.clipboard.writeText(window.location.href);
            showToast('Đã copy link!', 'success');
        } catch (error) {
            showToast('Không thể copy link', 'error');
        }
    }

    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        
        toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg mb-2`;
        toast.textContent = message;
        container.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Load on page ready
    document.addEventListener('DOMContentLoaded', loadSharedAudio);
    </script>


