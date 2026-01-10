<?php
/**
 * Explore Public Audios Page
 * Displays all approved public shared audios
 */
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khám phá Audio - DocReader AI Studio</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/vie.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .audio-card:hover { transform: translateY(-4px); }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="<?= BASE_URL ?>/" class="flex items-center space-x-2">
                    <div class="text-white text-2xl font-bold">
                        🎙️ DocReader AI Studio
                    </div>
                </a>
                
                <div class="flex items-center space-x-6">
                    <a href="<?= BASE_URL ?>/" class="text-white hover:text-gray-200 transition">
                        Trang chủ
                    </a>
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="<?= BASE_URL ?>/dashboard" class="text-white hover:text-gray-200 transition">
                            Dashboard
                        </a>
                        <button onclick="logout()" class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition font-medium">
                            Đăng xuất
                        </button>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login" class="text-white hover:text-gray-200 transition">
                            Đăng nhập
                        </a>
                        <a href="<?= BASE_URL ?>/register" class="bg-white text-purple-600 px-6 py-2 rounded-lg hover:bg-gray-100 transition font-medium">
                            Đăng ký
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">🎵 Khám phá Audio</h1>
        <p class="text-gray-600">Nghe các audio được cộng đồng chia sẻ</p>
    </div>

    <!-- Category Filter -->
    <div id="categories-container" class="flex flex-wrap gap-2 justify-center mb-8">
        <button onclick="filterByCategory(0)" id="cat-0" class="px-4 py-2 rounded-full bg-blue-500 text-white font-medium transition">
            Tất cả
        </button>
        <!-- Categories will be loaded here -->
    </div>

    <!-- Audio Grid -->
    <div id="audios-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Loading state -->
        <div class="col-span-full text-center py-12">
            <div class="animate-spin w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
            <p class="text-gray-500">Đang tải...</p>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination-container" class="flex justify-center mt-8 gap-2"></div>

    <!-- Empty State -->
    <div id="empty-state" class="hidden text-center py-16">
        <div class="text-6xl mb-4">🎵</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Chưa có audio nào</h3>
        <p class="text-gray-500 mb-6">Hãy là người đầu tiên chia sẻ audio của bạn!</p>
        <a href="<?= BASE_URL ?>/register" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
            Bắt đầu ngay
        </a>
    </div>
</div>
    </main>

<!-- Audio Player Modal -->
<div id="player-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <h3 id="modal-title" class="text-xl font-bold mb-1">Audio Title</h3>
                    <p id="modal-category" class="text-white/80 text-sm">Category</p>
                </div>
                <button onclick="closePlayerModal()" class="text-white/80 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6">
            <audio id="modal-player" controls class="w-full mb-4"></audio>
            <div id="modal-text" class="bg-gray-50 p-4 rounded-lg text-gray-700 max-h-48 overflow-y-auto text-sm whitespace-pre-wrap"></div>
            <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
                <span id="modal-author">👤 Author</span>
                <span id="modal-views">👁️ 0 views</span>
            </div>
        </div>
    </div>
</div>

<script>
const API_BASE = '<?= BASE_URL ?>/api';
let currentCategory = 0;
let currentPage = 1;

async function loadCategories() {
    try {
        const response = await fetch(`${API_BASE}/share.php?action=categories`);
        const data = await response.json();

        if (data.success && data.data.categories) {
            const container = document.getElementById('categories-container');
            data.data.categories.forEach(cat => {
                const btn = document.createElement('button');
                btn.id = `cat-${cat.id}`;
                btn.className = 'px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition';
                btn.onclick = () => filterByCategory(cat.id);
                btn.textContent = cat.name;
                container.appendChild(btn);
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadAudios(category = 0, page = 1) {
    const container = document.getElementById('audios-container');
    const emptyState = document.getElementById('empty-state');
    
    container.innerHTML = `
        <div class="col-span-full text-center py-12">
            <div class="animate-spin w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
            <p class="text-gray-500">Đang tải...</p>
        </div>
    `;
    emptyState.classList.add('hidden');

    try {
        let url = `${API_BASE}/share.php?action=get-public&page=${page}&limit=12`;
        if (category > 0) url += `&category=${category}`;

        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.data.items) {
            if (data.data.items.length === 0) {
                container.innerHTML = '';
                emptyState.classList.remove('hidden');
            } else {
                container.innerHTML = data.data.items.map(renderAudioCard).join('');
                renderPagination(data.data.current_page, data.data.pages);
            }
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `
            <div class="col-span-full text-center py-12">
                <p class="text-red-500">⚠️ Không thể tải dữ liệu</p>
            </div>
        `;
    }
}

function renderAudioCard(audio) {
    const truncatedText = audio.text.length > 80 ? audio.text.substring(0, 80) + '...' : audio.text;
    const truncatedTitle = audio.title.length > 40 ? audio.title.substring(0, 40) + '...' : audio.title;

    return `
        <div class="audio-card bg-white rounded-xl shadow-lg overflow-hidden transition-all duration-300 cursor-pointer hover:shadow-xl"
             onclick='openPlayerModal(${JSON.stringify(audio).replace(/'/g, "&#39;")})'>
            <div class="bg-gradient-to-r from-blue-400 to-purple-500 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-white text-2xl">
                        🎵
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-white font-bold truncate">${escapeHtml(truncatedTitle)}</h3>
                        <p class="text-white/80 text-sm">${escapeHtml(audio.category_name)}</p>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <p class="text-gray-600 text-sm mb-3 line-clamp-2">${escapeHtml(truncatedText)}</p>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>👤 ${escapeHtml(audio.author)}</span>
                    <span>👁️ ${audio.views} lượt xem</span>
                </div>
            </div>
        </div>
    `;
}

function filterByCategory(categoryId) {
    currentCategory = categoryId;
    currentPage = 1;

    // Update button styles
    document.querySelectorAll('[id^="cat-"]').forEach(btn => {
        if (btn.id === `cat-${categoryId}`) {
            btn.classList.remove('bg-gray-200', 'text-gray-700');
            btn.classList.add('bg-blue-500', 'text-white');
        } else {
            btn.classList.remove('bg-blue-500', 'text-white');
            btn.classList.add('bg-gray-200', 'text-gray-700');
        }
    });

    loadAudios(categoryId, 1);
}

function renderPagination(current, total) {
    const container = document.getElementById('pagination-container');
    if (total <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    
    if (current > 1) {
        html += `<button onclick="goToPage(${current - 1})" class="px-4 py-2 bg-white rounded-lg shadow hover:bg-gray-50 transition">← Trước</button>`;
    }

    for (let i = 1; i <= total; i++) {
        if (i === current) {
            html += `<button class="px-4 py-2 bg-blue-500 text-white rounded-lg shadow">${i}</button>`;
        } else if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
            html += `<button onclick="goToPage(${i})" class="px-4 py-2 bg-white rounded-lg shadow hover:bg-gray-50 transition">${i}</button>`;
        } else if (i === current - 2 || i === current + 2) {
            html += `<span class="px-2 text-gray-400">...</span>`;
        }
    }

    if (current < total) {
        html += `<button onclick="goToPage(${current + 1})" class="px-4 py-2 bg-white rounded-lg shadow hover:bg-gray-50 transition">Sau →</button>`;
    }

    container.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    loadAudios(currentCategory, page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function openPlayerModal(audio) {
    document.getElementById('modal-title').textContent = audio.title;
    document.getElementById('modal-category').textContent = audio.category_name;
    document.getElementById('modal-player').src = audio.audio_url;
    document.getElementById('modal-text').textContent = audio.text;
    document.getElementById('modal-author').textContent = `👤 ${audio.author}`;
    document.getElementById('modal-views').textContent = `👁️ ${audio.views} lượt xem`;
    
    document.getElementById('player-modal').classList.remove('hidden');
    document.getElementById('player-modal').classList.add('flex');
}

function closePlayerModal() {
    document.getElementById('modal-player').pause();
    document.getElementById('player-modal').classList.add('hidden');
    document.getElementById('player-modal').classList.remove('flex');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal on backdrop click
document.getElementById('player-modal').addEventListener('click', function(e) {
    if (e.target === this) closePlayerModal();
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadAudios();
});
</script>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-8 mt-auto">
    <div class="container mx-auto px-4 text-center">
        <p>&copy; <?= date('Y') ?> DocReader AI Studio. All rights reserved.</p>
    </div>
</footer>

<script>
function logout() {
    if (confirm('Bạn có chắc muốn đăng xuất?')) {
        window.location.href = '<?= BASE_URL ?>/logout';
    }
}
</script>
</body>
</html>
