<?php
$pageTitle = 'Dashboard - DocReader AI Studio';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Don't use header.php - create standalone page
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Toastify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/vie.png">

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50 m-0 p-0 overflow-x-hidden">

<div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-72 bg-white border-r border-gray-200 shadow-lg flex flex-col fixed h-full z-50">
        <div class="p-8 flex-grow">
            <!-- Logo -->
            <div class="mb-8">
                <a href="<?= BASE_URL ?>/dashboard" class="flex items-center no-underline group">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300">
                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14,2 14,8 20,8" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <span class="block text-2xl font-bold text-gray-800">DocReader</span>
                        <span class="block text-sm text-blue-600 font-semibold -mt-1">AI Studio</span>
                    </div>
                </a>
            </div>

            <div class="h-px bg-gray-200 mb-8"></div>

            <!-- Menu -->
            <nav class="space-y-3">
                <button onclick="switchTab('tts')" id="sidebar-tts" class="sidebar-link flex items-center w-full text-left px-5 py-4 rounded-xl font-medium transition-all duration-200 bg-blue-500 text-white shadow-lg">
                    <div class="p-2 rounded-lg mr-4 bg-white/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-base">Chuyển văn bản</div>
                        <div class="text-xs opacity-75">Text to Speech</div>
                    </div>
                </button>

                <button onclick="switchTab('summarize')" id="sidebar-summarize" class="sidebar-link flex items-center w-full text-left px-5 py-4 rounded-xl font-medium transition-all duration-200 text-gray-700 hover:bg-green-50 hover:text-green-700">
                    <div class="p-2 rounded-lg mr-4 bg-green-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-base">Tóm tắt</div>
                        <div class="text-xs opacity-75 text-gray-400">Summarize</div>
                    </div>
                </button>

                <button onclick="switchTab('translate')" id="sidebar-translate" class="sidebar-link flex items-center w-full text-left px-5 py-4 rounded-xl font-medium transition-all duration-200 text-gray-700 hover:bg-purple-50 hover:text-purple-700">
                    <div class="p-2 rounded-lg mr-4 bg-purple-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-base">Dịch thuật</div>
                        <div class="text-xs opacity-75 text-gray-400">Translation</div>
                    </div>
                </button>

                <button onclick="switchTab('history')" id="sidebar-history" class="sidebar-link flex items-center w-full text-left px-5 py-4 rounded-xl font-medium transition-all duration-200 text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                    <div class="p-2 rounded-lg mr-4 bg-orange-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-base">Lịch sử</div>
                        <div class="text-xs opacity-75 text-gray-400">History</div>
                    </div>
                </button>
            </nav>
        </div>

        <!-- User Profile Section -->
        <div class="p-6 border-t border-gray-200">
            <div class="mb-3 p-3 rounded-xl bg-gray-50 relative">
                <button onclick="toggleUserMenu()" class="flex items-center space-x-3 w-full text-left hover:bg-gray-100 rounded-lg p-2 transition">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        <?= strtoupper(substr($currentUser['email'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-800 truncate text-sm">
                            <?= htmlspecialchars(explode('@', $currentUser['email'])[0]) ?>
                        </div>
                        <div class="text-xs text-gray-500">DocReader AI Studio</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="user-menu" class="hidden absolute bottom-full left-0 right-0 mb-2 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50">
                    <a href="<?= BASE_URL ?>/profile" class="flex items-center px-4 py-3 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-gray-700 font-medium">Thông tin cá nhân</span>
                    </a>
                    <a href="<?= BASE_URL ?>/change-password" class="flex items-center px-4 py-3 hover:bg-gray-50 transition border-t border-gray-100">
                        <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <span class="text-gray-700 font-medium">Đổi mật khẩu</span>
                    </a>
                </div>
            </div>
            <button onclick="logout()" class="w-full py-3 px-4 text-white bg-gradient-to-r from-red-500 to-red-600 rounded-xl hover:from-red-600 hover:to-red-700 font-semibold transition-all duration-200 transform hover:scale-105 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Đăng xuất
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 ml-72">
        <div class="container mx-auto px-8 py-8">
            <!-- Welcome Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    Chào mừng trở lại, <?= htmlspecialchars($currentUser['username']) ?>!
                </h1>
                <p class="text-lg text-gray-600">Bạn muốn làm gì hôm nay?</p>
            </div>

            <!-- Horizontal Tabs -->
            <!-- <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
                <nav class="flex overflow-x-auto">
                    <button onclick="switchTab('tts')" id="tab-tts" class="tab-button active flex items-center px-6 py-4 text-sm font-medium border-b-2 border-purple-500 text-purple-400 bg-gray-900">
                        🎙️ <span class="ml-2">Text-to-Speech</span>
                    </button>
                    <button onclick="switchTab('summarize')" id="tab-summarize" class="tab-button flex items-center px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-700">
                        📝 <span class="ml-2">Summarize</span>
                    </button>
                    <button onclick="switchTab('translate')" id="tab-translate" class="tab-button flex items-center px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-700">
                        🌐 <span class="ml-2">Translate</span>
                    </button>
                    <button onclick="switchTab('history')" id="tab-history" class="tab-button flex items-center px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-700">
                        📊 <span class="ml-2">History</span>
                    </button>
                </nav>
            </div> -->

            <!-- Tab Content Container -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">

                <div class="p-8">
                    <!-- TTS Tab -->
                    <div id="content-tts" class="tab-content">
                <h2 class="text-2xl font-bold mb-6">Chuyển đổi văn bản thành giọng nói</h2>
                <div class="space-y-6">
                    <!-- File Upload Section -->
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-lg border-2 border-dashed border-blue-300">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl"></span>
                                <span class="font-semibold text-gray-700">Upload file để trích xuất văn bản</span>
                            </div>
                            <button onclick="document.getElementById('tts-file-input').click()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Chọn file
                            </button>
                        </div>
                        <input type="file" id="tts-file-input" accept=".doc,.docx,.pdf,.txt" class="hidden">
                        <p class="text-sm text-gray-600">Hỗ trợ PDF, TXT, DOCX (Tối đa 10MB)</p>
                        <div id="file-name-display" class="mt-2 text-sm text-green-600 font-medium hidden"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Văn bản</label>
                        <textarea id="tts-text" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Nhập văn bản cần chuyển đổi hoặc upload file..."></textarea>
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

                    <!-- Summarize Tab -->
                    <div id="content-summarize" class="tab-content hidden">
                        <h2 class="text-2xl font-bold mb-6">Tóm tắt nội dung</h2>
                        <div class="space-y-6">
                            <!-- File Upload Section -->
                            <div class="bg-blue-50 border-2 border-dashed border-blue-300 rounded-lg p-4">
                                <input type="file" id="summarize-file-input" accept=".pdf,.txt,.doc,.docx" class="hidden">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">📄</span>
                                        <span class="font-semibold text-gray-700">Upload file để trích xuất văn bản</span>
                                    </div>
                                    <button onclick="document.getElementById('summarize-file-input').click()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                        Chọn file
                                    </button>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">Hỗ trợ PDF, TXT, DOCX (tối đa 10MB)</p>
                                <p id="summarize-file-name" class="text-sm text-green-600 mt-2 hidden"></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Văn bản</label>
                                <textarea id="summarize-text" rows="8" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Dán văn bản cần tóm tắt hoặc upload file..."></textarea>
                            </div>
                            <button id="summarize-btn" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-medium transition">
                                Tóm tắt
                            </button>
                            <div id="summarize-result" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kết quả</label>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p id="summary-text" class="text-gray-800"></p>
                                </div>
                                <button onclick="copyToClipboard(document.getElementById('summary-text').textContent)" class="mt-2 text-blue-600 hover:text-blue-700 text-sm font-medium">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Tab -->
                    <div id="content-upload" class="tab-content hidden">
                        <h2 class="text-2xl font-bold mb-6">Upload tài liệu</h2>
                <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-purple-500 transition cursor-pointer">
                    <div class="text-6xl mb-4"></div>
                    <p class="text-lg font-medium text-gray-700 mb-2">Kéo thả file vào đây hoặc click để chọn</p>
                    <p class="text-sm text-gray-500">Hỗ trợ PDF, TXT (Tối đa 10MB)</p>
                    <input type="file" id="file-input" accept=".pdf,.txt" class="hidden">
                </div>
                        <textarea id="text-preview" rows="10" class="w-full px-4 py-3 border border-gray-300 rounded-lg mt-6 hidden" placeholder="Văn bản trích xuất sẽ hiển thị ở đây..."></textarea>
                    </div>

                    <!-- Translate Tab -->
                    <div id="content-translate" class="tab-content hidden">
                        <h2 class="text-2xl font-bold mb-6">Dịch thuật</h2>
                        <div class="space-y-6">
                            <!-- File Upload Section -->
                            <div class="bg-purple-50 border-2 border-dashed border-purple-300 rounded-lg p-4">
                                <input type="file" id="translate-file-input" accept=".pdf,.txt,.doc,.docx" class="hidden">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">📄</span>
                                        <span class="font-semibold text-gray-700">Upload file để trích xuất văn bản</span>
                                    </div>
                                    <button onclick="document.getElementById('translate-file-input').click()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                        Chọn file
                                    </button>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">Hỗ trợ PDF, TXT, DOCX (tối đa 10MB)</p>
                                <p id="translate-file-name" class="text-sm text-green-600 mt-2 hidden"></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Văn bản gốc</label>
                                <textarea id="translate-text" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Nhập văn bản cần dịch hoặc upload file..."></textarea>
                            </div>
                            <div class="flex items-center gap-2">
                                <select id="source-lang" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="vi">Tiếng Việt</option>
                                    <option value="en">Tiếng Anh</option>
                                </select>
                                <div class="text-gray-400">→</div>
                                <select id="target-lang" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="en">Tiếng Anh</option>
                                    <option value="vi">Tiếng Việt</option>
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
                                     Copy
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- History Tab -->
                    <div id="content-history" class="tab-content hidden">
                        <h2 class="text-2xl font-bold mb-6">Lịch sử hoạt động</h2>
                        
                        <!-- Filter Tabs -->
                        <div class="flex gap-2 mb-6 border-b border-gray-200">
                            <button onclick="window.filterHistory && window.filterHistory('tts')" id="filter-tts" class="history-filter-tab px-6 py-3 font-medium text-gray-700 border-b-2 border-blue-500 text-blue-600">
                                Âm thanh
                            </button>
                            <button onclick="window.filterHistory && window.filterHistory('summarize')" id="filter-summarize" class="history-filter-tab px-6 py-3 font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-900 hover:border-gray-300">
                                Tóm tắt
                            </button>
                            <button onclick="window.filterHistory && window.filterHistory('translate')" id="filter-translate" class="history-filter-tab px-6 py-3 font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-900 hover:border-gray-300">
                                Dịch thuật
                            </button>
                        </div>
                        
                        <!-- History Items Container (for card view) -->
                        <div id="history-items-container" class="space-y-4 hidden">
                            <!-- Items will be dynamically loaded here -->
                        </div>
                        
                        <!-- Pagination -->
                        <div id="history-pagination" class="mt-6 flex justify-center hidden">
                            <!-- Pagination will be rendered here -->
                        </div>
                        
                        <!-- Table View (default for TTS) -->
                        <div id="history-table-view" class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Văn bản</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Giọng nói</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Được tạo vào</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Âm thanh</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody id="activity-table-body" class="bg-white divide-y divide-gray-200">
                                    <!-- Activities will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Update horizontal tabs
    document.querySelectorAll('.tab-button').forEach(el => {
        el.classList.remove('active', 'border-purple-500', 'text-purple-400', 'bg-gray-900');
        el.classList.add('border-transparent', 'text-gray-400');
    });
    
    // Update sidebar links
    document.querySelectorAll('.sidebar-link').forEach(el => {
        el.classList.remove('bg-blue-500', 'text-white', 'shadow-lg', 'bg-green-500', 'bg-purple-500', 'bg-orange-500');
        el.classList.add('text-gray-700');
        const icon = el.querySelector('div:first-of-type');
        if (icon) {
            icon.classList.remove('bg-white/20');
        }
    });
    
    // Show selected tab
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Update horizontal tab button
    const tabBtn = document.getElementById('tab-' + tabName);
    if (tabBtn) {
        tabBtn.classList.add('active', 'border-purple-500', 'text-purple-400', 'bg-gray-900');
        tabBtn.classList.remove('border-transparent', 'text-gray-400');
    }
    
    // Update sidebar button
    const sidebarBtn = document.getElementById('sidebar-' + tabName);
    if (sidebarBtn) {
        const colors = {
            'tts': 'bg-blue-500',
            'summarize': 'bg-green-500',
            'translate': 'bg-purple-500',
            'history': 'bg-orange-500'
        };
        sidebarBtn.classList.add(colors[tabName], 'text-white', 'shadow-lg');
        sidebarBtn.classList.remove('text-gray-700');
        const icon = sidebarBtn.querySelector('div:first-of-type');
        if (icon) {
            icon.classList.add('bg-white/20');
        }
    }
    
    // Load history when switching to history tab
    if (tabName === 'history') {
        console.log('[Tab] Switching to history tab');
        console.log('[Tab] recentActivity exists:', typeof recentActivity !== 'undefined');
        if (typeof recentActivity !== 'undefined') {
            console.log('[Tab] Calling loadActivities...');
            recentActivity.loadActivities();
        } else {
            console.error('[Tab] recentActivity is not defined!');
        }
    }
}

function toggleUserMenu() {
    event.stopPropagation();
    const menu = document.getElementById('user-menu');
    menu.classList.toggle('hidden');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('user-menu');
    const userButton = event.target.closest('[onclick="toggleUserMenu()"]');
    
    if (!userButton && menu && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
    }
});

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

    // Handle TTS file upload
    const ttsFileInput = document.getElementById('tts-file-input');
    const ttsTextArea = document.getElementById('tts-text');
    const fileNameDisplay = document.getElementById('file-name-display');
    const charCount = document.getElementById('char-count');

    if (ttsFileInput && ttsTextArea) {
        ttsFileInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            console.log('File selected:', file.name, file.type, file.size);

            // Validate file type
            const fileType = file.name.split('.').pop().toLowerCase();
            if (!['pdf', 'txt', 'doc', 'docx'].includes(fileType)) {
                showToast('Chỉ chấp nhận file PDF, TXT, DOC, DOCX', 'error');
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                showToast('File vượt quá 10MB', 'error');
                return;
            }

            try {
                let text = '';

                if (fileType === 'txt') {
                    // TXT file - simple read
                    console.log('Reading TXT file...');
                    text = await file.text();
                    console.log('TXT content length:', text.length);
                } else if (fileType === 'pdf') {
                    // PDF file - need PDF.js
                    showToast('Đang đọc file PDF...', 'info');
                    console.log('Reading PDF file...');
                    
                    // Check if PDF.js is loaded
                    if (typeof pdfjsLib === 'undefined') {
                        console.log('Loading PDF.js library...');
                        showToast('Đang tải thư viện PDF...', 'info');
                        await loadPDFJS();
                    }
                    
                    text = await extractTextFromPDF(file);
                    console.log('PDF content length:', text.length);
                } else if (fileType === 'doc' || fileType === 'docx') {
                    // Word file - need mammoth.js
                    showToast('Đang đọc file Word...', 'info');
                    console.log('Reading Word file...');
                    
                    // Check if mammoth is loaded
                    if (typeof mammoth === 'undefined') {
                        console.log('Loading mammoth.js library...');
                        showToast('Đang tải thư viện Word...', 'info');
                        await loadMammoth();
                    }
                    
                    text = await extractTextFromWord(file);
                    console.log('Word content length:', text.length);
                }

                if (!text || text.trim().length === 0) {
                    showToast('File không có nội dung văn bản', 'error');
                    return;
                }

                // Check text length
                if (text.length > 5000) {
                    showToast('Văn bản quá dài, đã cắt xuống 5000 ký tự', 'warning');
                    text = text.substring(0, 5000);
                }

                // Update textarea
                ttsTextArea.value = text;
                
                // Update character count
                if (charCount) {
                    charCount.textContent = `${text.length} / 5000`;
                }

                // Show file name
                if (fileNameDisplay) {
                    fileNameDisplay.textContent = `✓ Đã tải: ${file.name}`;
                    fileNameDisplay.classList.remove('hidden');
                }

                showToast('Đã trích xuất văn bản thành công!', 'success');

            } catch (error) {
                console.error('File processing error:', error);
                showToast('Lỗi: ' + error.message, 'error');
            }

            // Reset input để có thể chọn lại cùng file
            e.target.value = '';
        });
    }

    // Update character count on input
    if (ttsTextArea && charCount) {
        ttsTextArea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length} / 5000`;
            
            if (length > 5000) {
                charCount.classList.add('text-red-500');
            } else {
                charCount.classList.remove('text-red-500');
            }
        });
    }

    // Load PDF.js library dynamically
    function loadPDFJS() {
        return new Promise((resolve, reject) => {
            if (typeof pdfjsLib !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
            script.onload = () => {
                console.log('PDF.js loaded successfully');
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                resolve();
            };
            script.onerror = (error) => {
                console.error('Failed to load PDF.js:', error);
                reject(new Error('Không thể tải thư viện PDF'));
            };
            document.head.appendChild(script);
        });
    }

    // Extract text from PDF
    async function extractTextFromPDF(file) {
        try {
            const arrayBuffer = await file.arrayBuffer();
            console.log('PDF arrayBuffer size:', arrayBuffer.byteLength);
            
            const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
            const pdf = await loadingTask.promise;
            console.log('PDF loaded, pages:', pdf.numPages);
            
            let fullText = '';
            
            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const textContent = await page.getTextContent();
                const pageText = textContent.items.map(item => item.str).join(' ');
                fullText += pageText + '\n\n';
                console.log(`Page ${i} extracted, length:`, pageText.length);
            }
            
            return fullText.trim();
        } catch (error) {
            console.error('PDF extraction error:', error);
            throw new Error('Không thể đọc file PDF: ' + error.message);
        }
    }

    // Load Mammoth.js library dynamically
    function loadMammoth() {
        return new Promise((resolve, reject) => {
            if (typeof mammoth !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js';
            script.onload = () => {
                console.log('Mammoth.js loaded successfully');
                resolve();
            };
            script.onerror = (error) => {
                console.error('Failed to load Mammoth.js:', error);
                reject(new Error('Không thể tải thư viện Word'));
            };
            document.head.appendChild(script);
        });
    }

    // Extract text from Word document
    async function extractTextFromWord(file) {
        try {
            const arrayBuffer = await file.arrayBuffer();
            console.log('Word arrayBuffer size:', arrayBuffer.byteLength);
            
            const result = await mammoth.extractRawText({ arrayBuffer: arrayBuffer });
            console.log('Word text extracted, length:', result.value.length);
            
            if (result.messages && result.messages.length > 0) {
                console.log('Mammoth messages:', result.messages);
            }
            
            return result.value.trim();
        } catch (error) {
            console.error('Word extraction error:', error);
            throw new Error('Không thể đọc file Word: ' + error.message);
        }
    }
});
</script>

<style>
.tab-button {
    border-bottom-width: 2px;
    transition: all 0.2s ease;
}

.sidebar-link:hover {
    transform: translateX(2px);
}
</style>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<!-- Application Scripts -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>
<script src="<?= BASE_URL ?>/assets/js/dashboard.js"></script>
<script src="<?= BASE_URL ?>/assets/js/tts.js"></script>

</body>
</html>