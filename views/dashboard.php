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
        <div class="p-5 flex-grow overflow-y-auto">
            <!-- Logo -->
            <div class="mb-5">
                <a href="<?= BASE_URL ?>/dashboard" class="flex items-center no-underline group">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14,2 14,8 20,8" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <span class="block text-2xl font-bold text-gray-800">DocReader</span>
                        <span class="block text-sm text-blue-600 font-semibold -mt-1">AI Studio</span>
                    </div>
                </a>
            </div>

            <div class="h-px bg-gray-200 mb-5"></div>

            <!-- Menu -->
            <nav class="space-y-2">
                <button onclick="switchTab('tts')" id="sidebar-tts" class="sidebar-link flex items-center w-full text-left px-4 py-3 rounded-xl font-medium transition-all duration-200 bg-blue-500 text-white shadow-lg">
                    <div class="p-2 rounded-lg mr-3 bg-white/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span>Chuyển văn bản</span>
                </button>

                <button onclick="switchTab('summarize')" id="sidebar-summarize" class="sidebar-link flex items-center w-full text-left px-4 py-3 rounded-xl font-medium transition-all duration-200 text-gray-700 hover:bg-green-50 hover:text-green-700">
                    <div class="p-2 rounded-lg mr-3 bg-green-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span>Tóm tắt</span>
                </button>

                <button onclick="switchTab('translate')" id="sidebar-translate" class="sidebar-link flex items-center w-full text-left px-4 py-3 rounded-xl font-medium transition-all duration-200 text-gray-700 hover:bg-purple-50 hover:text-purple-700">
                    <div class="p-2 rounded-lg mr-3 bg-purple-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </div>
                    <span>Dịch thuật</span>
                </button>

                <button onclick="switchTab('history')" id="sidebar-history" class="sidebar-link flex items-center w-full text-left px-4 py-3 rounded-xl font-medium transition-all duration-200 text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                    <div class="p-2 rounded-lg mr-3 bg-orange-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span>Lịch sử</span>
                </button>

                <button onclick="switchTab('myshares')" id="sidebar-myshares" class="sidebar-link flex items-center w-full text-left px-4 py-3 rounded-xl font-medium transition-all duration-200 text-gray-700 hover:bg-pink-50 hover:text-pink-700">
                    <div class="p-2 rounded-lg mr-3 bg-pink-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                    </div>
                    <span>Chia sẻ của tôi</span>
                </button>
            </nav>
        </div>

        <!-- User Profile Section -->
        <div class="p-4 border-t border-gray-200 flex-shrink-0">
            <div class="mb-3 p-2 rounded-lg bg-gray-50 relative">
                <button onclick="toggleUserMenu()" class="flex items-center space-x-3 w-full text-left hover:bg-gray-100 rounded-lg p-2 transition">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        <?= strtoupper(substr($currentUser['email'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-800 truncate text-sm">
                            <?= htmlspecialchars(explode('@', $currentUser['email'])[0]) ?>
                        </div>
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
            <button onclick="logout()" class="w-full py-2.5 px-4 text-white bg-gradient-to-r from-red-500 to-red-600 rounded-lg hover:from-red-600 hover:to-red-700 font-medium transition-all duration-200 flex items-center justify-center">
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
                                <!-- Edge TTS Free Voices (RECOMMENDED) -->
                                <optgroup label="⭐ Edge TTS (Miễn phí - Chất lượng cao)">
                                    <option value="vi-VN-HoaiMyNeural" selected>Hoài My (Nữ - Miền Nam)</option>
                                    <option value="vi-VN-NamMinhNeural">Nam Minh (Nam - Miền Bắc)</option>
                                </optgroup>
                                <!-- Google TTS Free Voices (Backup) -->
                                <optgroup label="🆓 Google TTS (Miễn phí - Backup)">
                                    <option value="gtts-vi">Google Tiếng Việt</option>
                                    <option value="gtts-en">Google English</option>
                                </optgroup>
                                <!-- Azure TTS Voices (Free tier available) -->
                                <optgroup label="🔊 Azure TTS (Miễn phí)">
                                    <option value="vi-VN-HoaiMyNeural-Azure">Azure - Hoài My (Nữ - Miền Nam)</option>
                                    <option value="vi-VN-NamMinhNeural-Azure">Azure - Nam Minh (Nam - Miền Bắc)</option>
                                </optgroup>
                            </select>
                        </div>
                        <!-- <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tốc độ: <span id="speed-display">1x</span></label>
                            <input id="speed-input" type="range" min="0" max="2" step="1" value="1" class="w-full">
                        </div> -->
                    </div>
                    <div class="flex gap-2">
                        <button id="convert-btn" class="flex-1 gradient-bg text-white py-3 rounded-lg font-medium hover:opacity-90 transition">
                            Chuyển đổi
                        </button>
                        <button onclick="window.clearTTSSession()" class="px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition" title="Xóa phiên làm việc">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
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
                                        <span class="text-2xl"></span>
                                        <span class="font-semibold text-gray-700">Upload file để trích xuất văn bản</span>
                                    </div>
                                    <button onclick="document.getElementById('summarize-file-input').click()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                        Chọn file
                                    </button>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">Hỗ trợ PDF, TXT, DOC, DOCX (tối đa 10MB)</p>
                                <p id="summarize-file-name" class="text-sm text-green-600 mt-2 hidden"></p>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Văn bản</label>
                                    <span id="summarize-char-count" class="text-sm text-gray-500">0 / 10000</span>
                                </div>
                                <textarea id="summarize-text" rows="8" maxlength="10000" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Dán văn bản cần tóm tắt hoặc upload file (tối đa 10000 ký tự)..."></textarea>
                            </div>
                            <button id="summarize-btn" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-medium transition">
                                Tóm tắt
                            </button>
                            <div id="summarize-result" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kết quả</label>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p id="summary-text" class="text-gray-800"></p>
                                </div>
                                <div class="mt-2 flex gap-2">
                                    <button onclick="copyToClipboard(document.getElementById('summary-text').textContent)" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                        📋 Copy
                                    </button>
                                    <button onclick="downloadAsText(document.getElementById('summary-text').textContent, 'tom-tat.txt')" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                        💾 Tải về TXT
                                    </button>
                                </div>
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
                                        <span class="text-2xl"></span>
                                        <span class="font-semibold text-gray-700">Upload file để trích xuất văn bản</span>
                                    </div>
                                    <button onclick="document.getElementById('translate-file-input').click()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                        Chọn file
                                    </button>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">Hỗ trợ PDF, TXT, DOC, DOCX (tối đa 10MB)</p>
                                <p id="translate-file-name" class="text-sm text-green-600 mt-2 hidden"></p>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Văn bản gốc</label>
                                    <span id="translate-char-count" class="text-sm text-gray-500">0 / 10000</span>
                                </div>
                                <textarea id="translate-text" rows="6" maxlength="10000" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Nhập văn bản cần dịch hoặc upload file (tối đa 10000 ký tự)..."></textarea>
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
                                <div class="mt-2 flex gap-2">
                                    <button onclick="copyToClipboard(document.getElementById('translated-text').textContent)" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                        📋 Copy
                                    </button>
                                    <button onclick="downloadAsText(document.getElementById('translated-text').textContent, 'dich-thuat.txt')" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                        💾 Tải về TXT
                                    </button>
                                </div>
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
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nội dung</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giọng đọc</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phát</th>
                                    </tr>
                                </thead>
                                <tbody id="activity-table-body" class="bg-white divide-y divide-gray-200">
                                    <!-- Activities will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- My Shares Tab -->
                    <div id="content-myshares" class="tab-content hidden">
                        <h2 class="text-2xl font-bold mb-6">Chia sẻ của tôi</h2>
                        
                        <!-- Tabs for share types -->
                        <div class="flex gap-2 mb-6 border-b border-gray-200">
                            <button onclick="loadMyShares('link')" id="share-tab-link" class="share-tab px-6 py-3 font-medium text-blue-600 border-b-2 border-blue-500">
                                 Link chia sẻ
                            </button>
                            <button onclick="loadMyShares('public')" id="share-tab-public" class="share-tab px-6 py-3 font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-900">
                                Chia sẻ công khai
                            </button>
                        </div>
                        
                        <!-- Shares Container -->
                        <div id="my-shares-container" class="space-y-4">
                            <div class="text-center py-12">
                                <div class="spinner mx-auto mb-4"></div>
                                <p class="text-gray-500">Đang tải...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Lưu position của tất cả audio đang phát trước khi chuyển tab
    // Ưu tiên AudioManager mới
    if (window.audioManager) {
        window.audioManager.saveAllPositions('tab_switch');
        console.log('[Tab] AudioManager saved all positions before switching to:', tabName);
    } else if (window.audioTracker) {
        window.audioTracker.saveOnTabSwitch();
        console.log('[Tab] AudioTracker saved all positions before switching to:', tabName);
    }
    
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Update horizontal tabs
    document.querySelectorAll('.tab-button').forEach(el => {
        el.classList.remove('active', 'border-purple-500', 'text-purple-400', 'bg-gray-900');
        el.classList.add('border-transparent', 'text-gray-400');
    });
    
    // Update sidebar links
    document.querySelectorAll('.sidebar-link').forEach(el => {
        el.classList.remove('bg-blue-500', 'text-white', 'shadow-lg', 'bg-green-500', 'bg-purple-500', 'bg-orange-500', 'bg-pink-500');
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
            'history': 'bg-orange-500',
            'myshares': 'bg-pink-500'
        };
        sidebarBtn.classList.add(colors[tabName] || 'bg-blue-500', 'text-white', 'shadow-lg');
        sidebarBtn.classList.remove('text-gray-700');
        const icon = sidebarBtn.querySelector('div:first-of-type');
        if (icon) {
            icon.classList.add('bg-white/20');
        }
    }
    
    // Load history when switching to history tab - ALWAYS reload from database
    if (tabName === 'history') {
        console.log('[Tab] Switching to history tab - FORCE RELOAD from database');
        if (typeof recentActivity !== 'undefined') {
            console.log('[Tab] Reloading activities from database...');
            recentActivity.loadActivities();
        } else {
            console.error('[Tab] recentActivity is not defined!');
        }
    }
    
    // Load my shares when switching to myshares tab
    if (tabName === 'myshares') {
        console.log('[Tab] Switching to myshares tab');
        loadMyShares('link');
    }
    
    // Save position when switching AWAY from TTS tab
    if (tabName !== 'tts') {
        const audioPlayer = document.getElementById('audio-player');
        const historyId = window.getCurrentHistoryId ? window.getCurrentHistoryId() : null;
        
        if (historyId && audioPlayer && audioPlayer.currentTime > 0) {
            console.log('[Tab] Switching away from TTS, saving position:', audioPlayer.currentTime);
            
            // Save position to database
            if (typeof apiRequest === 'function') {
                apiRequest(`${API_BASE}/update_position.php`, {
                    method: 'POST',
                    body: JSON.stringify({
                        id: historyId,
                        position: Math.floor(audioPlayer.currentTime)
                    })
                }).then(() => {
                    console.log('[Tab] Position saved before tab switch');
                });
            }
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
    // Character counter for summarize textarea
    const summarizeText = document.getElementById('summarize-text');
    const summarizeCharCount = document.getElementById('summarize-char-count');
    if (summarizeText && summarizeCharCount) {
        summarizeText.addEventListener('input', function() {
            const length = this.value.length;
            summarizeCharCount.textContent = `${length} / 10000`;
            if (length > 10000) {
                summarizeCharCount.classList.add('text-red-500');
                summarizeCharCount.classList.remove('text-gray-500');
            } else {
                summarizeCharCount.classList.remove('text-red-500');
                summarizeCharCount.classList.add('text-gray-500');
            }
        });
    }
    
    // Character counter for translate textarea
    const translateText = document.getElementById('translate-text');
    const translateCharCount = document.getElementById('translate-char-count');
    if (translateText && translateCharCount) {
        translateText.addEventListener('input', function() {
            const length = this.value.length;
            translateCharCount.textContent = `${length} / 10000`;
            if (length > 10000) {
                translateCharCount.classList.add('text-red-500');
                translateCharCount.classList.remove('text-gray-500');
            } else {
                translateCharCount.classList.remove('text-red-500');
                translateCharCount.classList.add('text-gray-500');
            }
        });
    }
    
    // Handle speed slider
    const speedInput = document.getElementById('speed-input');
    const speedDisplay = document.getElementById('speed-display');
    
    if (speedInput && speedDisplay) {
        // Speed mapping: 0 = 0.75x, 1 = 1x, 2 = 1.25x
        const speedMap = {
            '0': '0.75x',
            '1': '1x',
            '2': '1.25x'
        };
        
        speedInput.addEventListener('input', function() {
            const speedValue = this.value;
            speedDisplay.textContent = speedMap[speedValue] || '1x';
            console.log('Speed changed to:', speedMap[speedValue]);
        });
        
        // Set initial value
        speedDisplay.textContent = speedMap[speedInput.value] || '1x';
    }
    
    // Handle translate button
    const translateBtn = document.getElementById('translate-btn');
    if (translateBtn) {
        translateBtn.addEventListener('click', async function() {
            const text = document.getElementById('translate-text').value.trim();
            const sourceLang = document.getElementById('source-lang').value;
            const targetLang = document.getElementById('target-lang').value;
            
            if (!text) {
                showToast('Vui lòng nhập văn bản', 'error');
                return;
            }
            
            // Kiểm tra nếu ngôn ngữ nguồn và đích giống nhau
            if (sourceLang === targetLang) {
                console.error('[Translate] Error: Không thể dịch cùng ngôn ngữ:', sourceLang);
                showToast('Không thể dịch cùng ngôn ngữ. Vui lòng chọn ngôn ngữ đích khác.', 'error');
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

    // Make functions global by assigning to window
    window.extractTextFromPDF = extractTextFromPDF;
    window.extractTextFromWord = extractTextFromWord;
    window.loadPDFJS = loadPDFJS;
    window.loadMammoth = loadMammoth;

    // Extract text from PDF
    async function extractTextFromPDF(file) {
        try {
            // Ensure PDF.js is loaded
            if (typeof pdfjsLib === 'undefined') {
                console.log('Loading PDF.js...');
                await loadPDFJS();
            }
            
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
            // Use jsdelivr CDN which is more reliable
            script.src = 'https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js';
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
            // Ensure Mammoth.js is loaded
            if (typeof mammoth === 'undefined') {
                console.log('Loading Mammoth.js...');
                await loadMammoth();
            }
            
            // Check file extension
            const fileName = file.name.toLowerCase();
            if (!fileName.endsWith('.docx')) {
                // .doc files (old format) are not supported by Mammoth
                if (fileName.endsWith('.doc')) {
                    throw new Error('File .doc (Word 97-2003) không được hỗ trợ. Vui lòng chuyển sang định dạng .docx');
                }
            }
            
            const arrayBuffer = await file.arrayBuffer();
            console.log('Word arrayBuffer size:', arrayBuffer.byteLength);
            
            // Validate file is not empty
            if (arrayBuffer.byteLength === 0) {
                throw new Error('File Word trống');
            }
            
            const result = await mammoth.extractRawText({ arrayBuffer: arrayBuffer });
            console.log('Word text extracted, length:', result.value.length);
            
            if (result.messages && result.messages.length > 0) {
                console.log('Mammoth messages:', result.messages);
            }
            
            if (!result.value || result.value.trim().length === 0) {
                throw new Error('Không tìm thấy văn bản trong file Word');
            }
            
            return result.value.trim();
        } catch (error) {
            console.error('Word extraction error:', error);
            throw new Error('Không thể đọc file Word: ' + error.message);
        }
    }
});

/**
 * Download text content as a file
 */
function downloadAsText(text, filename = 'download.txt') {
    if (!text) {
        showToast('Không có nội dung để tải về', 'error');
        return;
    }
    
    try {
        // Create a Blob with the text content
        const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
        
        // Create a temporary download link
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        
        // Cleanup
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
        
        showToast('Đã tải file thành công', 'success');
    } catch (error) {
        console.error('Download error:', error);
        showToast('Không thể tải file: ' + error.message, 'error');
    }
}
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
<script src="<?= BASE_URL ?>/assets/js/app.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/auth.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/dashboard.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/tts.js"></script>
<!-- TTS Double Click Fix - Load last to override existing handlers -->
<script src="<?= BASE_URL ?>/assets/js/tts-fix.js?v=<?= time() ?>"></script>
<!-- TTS Session Restore - Auto-save and restore user session -->
<script src="<?= BASE_URL ?>/assets/js/tts-session-restore.js?v=<?= time() ?>"></script>
<!-- Audio Position Tracker - Unified module for all audio position saving -->
<script src="<?= BASE_URL ?>/assets/js/audio-position-tracker.js?v=<?= time() ?>"></script>
<!-- Audio Player Manager - New unified audio management -->
<script src="<?= BASE_URL ?>/assets/js/audio-player-manager.js?v=<?= time() ?>"></script>

<script>
// Define BASE_URL and API_BASE for JavaScript
const BASE_URL = '<?= BASE_URL ?>';
const API_BASE = '<?= BASE_URL ?>/api';

console.log('[Dashboard] BASE_URL:', BASE_URL);
console.log('[Dashboard] API_BASE:', API_BASE);

// Share Audio Functions
let currentShareAudio = null;
let currentModalAudio = null;
let modalFallbackInterval = null; // For fallback tracking

// Expose to window for audio-progress-tracker
window.currentModalAudio = null;

// Open audio detail modal and set currentModalAudio
window.openAudioDetailModal = function(audioData) {
    console.log('[Modal] Opening audio detail modal with data:', audioData);
    
    try {
        currentModalAudio = audioData;
        window.currentModalAudio = audioData; // Expose to window
        
        // Populate modal with audio data
        document.getElementById('modal-full-text').textContent = audioData.text || 'Không có văn bản';
        document.getElementById('modal-voice').textContent = audioData.voice || 'N/A';
        document.getElementById('modal-date').textContent = audioData.created_at || 'N/A';
    
    // Get modal player
    const modalPlayer = document.getElementById('modal-audio-player');
    const audioSource = document.getElementById('modal-audio-source');
    
    // Check if elements exist
    if (!modalPlayer) {
        console.error('[Modal] ❌ modal-audio-player not found!');
        alert('Lỗi: Không tìm thấy audio player element');
        return;
    }
    
    if (!audioSource) {
        console.error('[Modal] ❌ modal-audio-source not found!');
        alert('Lỗi: Không tìm thấy audio source element');
        return;
    }
    
    // DEBUG: Log all audioData
    console.log('[Modal] 🔍 Full audioData:', JSON.stringify(audioData, null, 2));
    console.log('[Modal] 🔍 audio_url:', audioData.audio_url);
    console.log('[Modal] 🔍 audio_url type:', typeof audioData.audio_url);
    console.log('[Modal] 🔍 modalPlayer:', modalPlayer);
    console.log('[Modal] 🔍 audioSource:', audioSource);
    
    // Check if audio_url exists - still show modal but warn
    if (!audioData.audio_url || audioData.audio_url === 'undefined' || audioData.audio_url === 'null' || audioData.audio_url === '') {
        console.warn('[Modal] ⚠️ Invalid audio URL:', audioData.audio_url);
        // Still continue to show modal
    }
    
    // Reset player first
    modalPlayer.pause();
    modalPlayer.currentTime = 0;
    
    // Set audio source
    console.log('[Modal] 📀 Setting audio URL:', audioData.audio_url);
    audioSource.src = audioData.audio_url;
    audioSource.type = 'audio/mpeg';
    
    // Verify source was set
    console.log('[Modal] 📀 Audio source element src:', audioSource.src);
    
    // Add error listener
    modalPlayer.addEventListener('error', function(e) {
        console.error('[Modal] ❌ Audio error:', e);
        console.error('[Modal] ❌ Error code:', modalPlayer.error?.code);
        console.error('[Modal] ❌ Error message:', modalPlayer.error?.message);
        alert('Lỗi phát audio: ' + (modalPlayer.error?.message || 'Unknown error'));
    }, { once: true });
    
    // Show resume info if position > 0
    const savedPosition = parseInt(audioData.position) || 0;
    
    if (savedPosition > 0) {
        const minutes = Math.floor(savedPosition / 60);
        const seconds = savedPosition % 60;
        const timeStr = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        document.getElementById('modal-resume-text').textContent = `Tiếp tục từ ${timeStr}`;
        document.getElementById('modal-resume-info').classList.remove('hidden');
        
        console.log('[Modal] 🔄 Will resume from position:', savedPosition);
        
        // CRITICAL: Set position when audio can play
        const forceSetPosition = function() {
            try {
                modalPlayer.currentTime = savedPosition;
                console.log('[Modal] ✅ Position forced to:', savedPosition, 'actual:', modalPlayer.currentTime);
            } catch (e) {
                console.error('[Modal] ❌ Failed to set position:', e);
            }
        };
        
        // Method 1: loadedmetadata (most reliable)
        modalPlayer.addEventListener('loadedmetadata', forceSetPosition, { once: true });
        
        // Method 2: canplay (backup)
        modalPlayer.addEventListener('canplay', function() {
            if (modalPlayer.currentTime < savedPosition - 1) {
                forceSetPosition();
            }
        }, { once: true });
        
        // Method 3: Force after delay (last resort)
        setTimeout(() => {
            if (modalPlayer.currentTime < savedPosition - 1) {
                console.log('[Modal] ⚠️ Using delayed force set');
                forceSetPosition();
            }
        }, 500);
        
        // NOW load the audio (after listeners are set up)
        modalPlayer.load();
        console.log('[Modal] Audio loading with resume position:', savedPosition);
    } else {
        document.getElementById('modal-resume-info').classList.add('hidden');
        
        // Load audio normally if no saved position
        modalPlayer.load();
        console.log('[Modal] Audio loading from start');
    }
    
    // Setup audio progress tracking for modal player
    // Try new simple tracker first
    if (typeof window.AudioPositionTracker !== 'undefined') {
        console.log('[Modal] ✅ Using simple tracker for ID:', audioData.id);
        window.AudioPositionTracker.setupModal(audioData.id);
    } else {
        // Fallback to old tracker
        const setupTracking = () => {
            if (typeof window.setupModalAudioSync === 'function') {
                console.log('[Modal] ✅ Using old tracker for ID:', audioData.id);
                window.setupModalAudioSync(modalPlayer, audioData.id);
            } else {
                console.log('[Modal] ⏳ Waiting for tracker...');
                setTimeout(setupTracking, 100);
            }
        };
        setupTracking();
    }
    
    // Fallback: Basic tracking if setupModalAudioSync is not available
    // This ensures position is saved even if audio-progress-tracker.js fails to load
    
    modalPlayer.addEventListener('play', function() {
        console.log('[Modal Fallback] Audio playing');
        if (modalFallbackInterval) clearInterval(modalFallbackInterval);
        
        modalFallbackInterval = setInterval(() => {
            if (!modalPlayer.paused && modalPlayer.currentTime > 0) {
                console.log('[Modal Fallback] Auto-save position:', Math.floor(modalPlayer.currentTime));
            }
        }, 5000);
    });
    
    modalPlayer.addEventListener('pause', function() {
        console.log('[Modal Fallback] Audio paused, saving position:', Math.floor(modalPlayer.currentTime));
        if (modalFallbackInterval) {
            clearInterval(modalFallbackInterval);
            modalFallbackInterval = null;
        }
        
        // Save position
        if (modalPlayer.currentTime > 0 && typeof apiRequest === 'function') {
            apiRequest(`${API_BASE}/update_position.php`, {
                method: 'POST',
                body: JSON.stringify({
                    id: audioData.id,
                    position: Math.floor(modalPlayer.currentTime)
                })
            }).then(res => {
                if (res.success) {
                    console.log('[Modal Fallback] ✅ Position saved');
                }
            }).catch(err => {
                console.error('[Modal Fallback] ❌ Save failed:', err);
            });
        }
    });
    
    // Show modal
    document.getElementById('audio-detail-modal').classList.remove('hidden');
    console.log('[Modal] ✅ Modal displayed successfully');
    } catch (error) {
        console.error('[Modal] ❌ Error opening modal:', error);
        // Still try to show modal even if there's an error
        try {
            document.getElementById('audio-detail-modal').classList.remove('hidden');
        } catch (e) {
            console.error('[Modal] ❌ Cannot show modal:', e);
        }
    }
}

// Close audio detail modal
function closeAudioDetailModal() {
    const modalPlayer = document.getElementById('modal-audio-player');
    
    // Clear fallback interval
    if (modalFallbackInterval) {
        clearInterval(modalFallbackInterval);
        modalFallbackInterval = null;
    }
    
    // Save position before closing if audio was played
    if (currentModalAudio && modalPlayer.currentTime > 0) {
        console.log('[Modal] Saving position before close:', modalPlayer.currentTime);
        
        // Use the global save function if available
        if (typeof window.AudioProgressTracker !== 'undefined' && 
            typeof window.AudioProgressTracker.savePosition === 'function') {
            window.AudioProgressTracker.savePosition();
        }
        
        // Or call API directly
        if (typeof apiRequest === 'function') {
            apiRequest(`${API_BASE}/update_position.php`, {
                method: 'POST',
                body: JSON.stringify({
                    id: currentModalAudio.id,
                    position: Math.floor(modalPlayer.currentTime)
                })
            }).then(response => {
                if (response.success) {
                    console.log('[Modal] Position saved on close');
                }
            }).catch(err => {
                console.error('[Modal] Failed to save position:', err);
            });
        }
    }
    
    document.getElementById('audio-detail-modal').classList.add('hidden');
    modalPlayer.pause();
    modalPlayer.currentTime = 0;
    currentModalAudio = null;
    window.currentModalAudio = null; // Clear window reference
}

// Download modal audio
function downloadModalAudio() {
    if (currentModalAudio && currentModalAudio.audio_url) {
        const link = document.createElement('a');
        link.href = currentModalAudio.audio_url;
        link.download = `audio_${currentModalAudio.id}.mp3`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Delete modal audio
function deleteModalAudio() {
    if (currentModalAudio && confirm('Bạn có chắc chắn muốn xóa audio này?')) {
        // Call delete API
        fetch(`${API_BASE}/history.php?action=delete&id=${currentModalAudio.id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Đã xóa audio', 'success');
                closeAudioDetailModal();
                // Reload history
                if (typeof recentActivity !== 'undefined' && recentActivity.loadActivities) {
                    recentActivity.loadActivities();
                }
            } else {
                showToast('Không thể xóa audio', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting audio:', error);
            showToast('Có lỗi xảy ra', 'error');
        });
    }
}

// Share audio from modal
window.shareModalAudio = function() {
    if (currentModalAudio) {
        openShareModal(currentModalAudio);
    } else {
        showToast('Không có audio để chia sẻ', 'error');
    }
}

// Share audio from table row
window.openShareFromTable = function(id, text, voice, audioUrl, createdAt) {
    const audioData = {
        id: id,
        text: text,
        voice: voice,
        audio_url: audioUrl,
        created_at: createdAt
    };
    openShareModal(audioData);
}

// Open share modal
window.openShareModal = async function(audio) {
    console.log('[Share] openShareModal called with:', audio);
    currentShareAudio = audio;
    
    // Load categories
    try {
        const response = await fetch(`${API_BASE}/shared_audio.php?action=categories`);
        const data = await response.json();
        
        if (data.success) {
            const categorySelect = document.getElementById('share-category');
            categorySelect.innerHTML = '<option value="">Chọn thể loại...</option>' +
                data.data.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
    
    // Pre-fill title with audio text preview
    const title = audio.text.length > 50 ? audio.text.substring(0, 50) + '...' : audio.text;
    document.getElementById('share-title').value = title;
    
    // Show modal
    document.getElementById('share-audio-modal').classList.remove('hidden');
}

// Close share modal
window.closeShareModal = function() {
    document.getElementById('share-audio-modal').classList.add('hidden');
    document.getElementById('share-audio-form').reset();
    currentShareAudio = null;
}

// Switch share tab
window.switchShareTab = function(tab) {
    // Update tabs
    const publicTab = document.getElementById('share-tab-public');
    const linkTab = document.getElementById('share-tab-link');
    const publicForm = document.getElementById('share-audio-form-public');
    const linkForm = document.getElementById('share-audio-form-link');
    
    if (tab === 'public') {
        publicTab.classList.add('border-green-500', 'text-green-600');
        publicTab.classList.remove('border-transparent', 'text-gray-500');
        linkTab.classList.remove('border-blue-500', 'text-blue-600');
        linkTab.classList.add('border-transparent', 'text-gray-500');
        publicForm.classList.remove('hidden');
        linkForm.classList.add('hidden');
    } else {
        linkTab.classList.add('border-blue-500', 'text-blue-600');
        linkTab.classList.remove('border-transparent', 'text-gray-500');
        publicTab.classList.remove('border-green-500', 'text-green-600');
        publicTab.classList.add('border-transparent', 'text-gray-500');
        linkForm.classList.remove('hidden');
        publicForm.classList.add('hidden');
    }
}

// Toggle password field
window.togglePasswordField = function() {
    const checkbox = document.getElementById('link-use-password');
    const passwordField = document.getElementById('link-password');
    passwordField.disabled = !checkbox.checked;
    if (!checkbox.checked) {
        passwordField.value = '';
    }
}

// Submit share audio (public)
window.submitShareAudio = async function(event) {
    event.preventDefault();
    
    if (!currentShareAudio) return;
    
    const formData = {
        audio_id: currentShareAudio.id,
        category_id: document.getElementById('share-category').value,
        title: document.getElementById('share-title').value.trim(),
        description: document.getElementById('share-description').value.trim()
    };
    
    try {
        const response = await fetch(`${API_BASE}/shared_audio.php?action=submit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            closeShareModal();
            closeAudioDetailModal();
        } else {
            showToast(data.error, 'error');
        }
    } catch (error) {
        console.error('Error sharing audio:', error);
        showToast('Có lỗi xảy ra khi chia sẻ audio', 'error');
    }
}

// Create share link
window.createShareLink = async function(event) {
    event.preventDefault();
    
    if (!currentShareAudio) return;
    
    const usePassword = document.getElementById('link-use-password').checked;
    const password = usePassword ? document.getElementById('link-password').value : null;
    const expiration = document.getElementById('link-expiration').value;
    const maxViews = document.getElementById('link-max-views').value;
    
    const formData = {
        audio_id: currentShareAudio.id,
        title: document.getElementById('link-title').value.trim() || null,
        password: password,
        expiration_days: expiration ? parseInt(expiration) : null,
        max_views: maxViews ? parseInt(maxViews) : null
    };
    
    try {
        const response = await fetch(`${API_BASE}/shared_audio.php?action=create_link`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show link in modal
            showShareLinkResult(data.data.share_url, data.data.share_token, password);
        } else {
            showToast(data.error, 'error');
        }
    } catch (error) {
        console.error('Error creating share link:', error);
        showToast('Có lỗi xảy ra khi tạo link', 'error');
    }
}

// Show share link result
window.showShareLinkResult = function(url, token, password) {
    const form = document.getElementById('share-audio-form-link');
    form.innerHTML = `
        <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Link đã được tạo!</h3>
            <p class="text-sm text-gray-600 mb-4">Sao chép link bên dưới để chia sẻ</p>
            
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                <div class="flex items-center gap-2">
                    <input type="text" id="share-link-url" value="${url}" readonly
                           class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded text-sm">
                    <button onclick="copyShareLink()" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>
            
            ${password ? `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4 text-left">
                    <p class="text-sm text-yellow-800"><strong>Mật khẩu:</strong> ${password}</p>
                    <p class="text-xs text-yellow-600 mt-1">Người xem cần nhập mật khẩu này</p>
                </div>
            ` : ''}
            
            <button onclick="closeShareModal()" class="w-full px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                Đóng
            </button>
        </div>
    `;
}

// Copy share link
window.copyShareLink = function() {
    const input = document.getElementById('share-link-url');
    input.select();
    document.execCommand('copy');
    showToast('Đã sao chép link!', 'success');
}

// Process pending share data if any
if (window._pendingShareData) {
    console.log('[Share] Processing pending share data');
    openShareModal(window._pendingShareData);
    window._pendingShareData = null;
}

// Process pending audio detail data if any
if (window._pendingAudioData) {
    console.log('[Modal] Processing pending audio data');
    openAudioDetailModal(window._pendingAudioData);
    window._pendingAudioData = null;
}
</script>

</body>
</html>