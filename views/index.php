<?php
$pageTitle = 'DocReader AI Studio - Chuyển đổi văn bản thành giọng nói AI';
require_once __DIR__ . '/../includes/header.php';
?>

    <!-- Hero Section -->
    <section class="py-20 sm:py-32">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="flex flex-col gap-8 text-center lg:text-left">
                    <div class="flex flex-col gap-4">
                        <h1 class="text-4xl lg:text-6xl font-black leading-tight tracking-tighter">
                            Chuyển văn bản thành giọng nói tự nhiên và truyền cảm
                        </h1>
                        <p class="text-base lg:text-lg text-gray-600 max-w-xl mx-auto lg:mx-0">
                            Trải nghiệm công nghệ Azure Speech AI tiên tiến để tạo ra âm thanh chất lượng cao cho mọi nhu cầu của bạn.
                        </p>
                    </div>
                    <div class="flex flex-col gap-4 items-center lg:items-start">
                        <div class="flex w-full max-w-lg items-stretch rounded-lg bg-white p-2 shadow-lg border border-gray-200">
                            <input 
                                type="text" 
                                class="flex-1 px-3 py-3 text-base border-none focus:outline-none focus:ring-0 rounded-md" 
                                placeholder="Nhập văn bản của bạn tại đây để thử..."
                                readonly
                            />
                            <a href="./register" class="px-5 py-3 bg-blue-600 text-white text-sm font-bold rounded-md hover:bg-blue-700 transition-colors">
                                Thử ngay
                            </a>
                        </div>  
                    </div>
                </div>
                <div class="w-full aspect-[4/3] rounded-xl shadow-2xl overflow-hidden relative">
                    <!-- Hero Image -->
                    <img 
                        src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=800&h=600&fit=crop&q=80" 
                        alt="Person using AI voice technology" 
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="explore" class="py-20 sm:py-24 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col gap-12">
                <div class="flex flex-col gap-3 text-center max-w-2xl mx-auto">
                    <h2 class="text-3xl lg:text-4xl font-bold tracking-tight"> Audio từ cộng đồng</h2>
                    <p class="text-gray-600">Khám phá các audio được chia sẻ bởi người dùng khác</p>
                </div>
                
                <div id="public-audios-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Loading state -->
                    <div class="col-span-full text-center py-8">
                        <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto"></div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="<?= BASE_URL ?>/explore" class="inline-flex items-center gap-2 px-6 py-3 bg-white border-2 border-blue-600 text-blue-600 font-bold rounded-lg hover:bg-blue-50 transition-colors">
                        Xem tất cả
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- Features Section -->
    <section id="features" class="py-20 sm:py-24 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col gap-12">
                <div class="flex flex-col gap-3 text-center max-w-2xl mx-auto">
                    <h2 class="text-3xl lg:text-4xl font-bold tracking-tight">Các tính năng vượt trội</h2>
                    <p class="text-gray-600">Khám phá các công cụ mạnh mẽ giúp bạn tạo ra âm thanh hoàn hảo một cách dễ dàng.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 text-blue-600 text-2xl">
                            🎵
                        </div>
                        <div class="flex flex-col gap-1">
                            <h3 class="text-lg font-bold">Giọng nói tự nhiên</h3>
                            <p class="text-sm text-gray-600">Sử dụng Azure AI để tạo ra giọng nói chân thực và biểu cảm như người thật.</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 text-blue-600 text-2xl">
                            🌐
                        </div>
                        <div class="flex flex-col gap-1">
                            <h3 class="text-lg font-bold">Đa ngôn ngữ</h3>
                            <p class="text-sm text-gray-600">Hỗ trợ Tiếng Việt và Tiếng Anh với giọng đọc Neural chất lượng cao.</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 text-blue-600 text-2xl">
                            ⚡
                        </div>
                        <div class="flex flex-col gap-1">
                            <h3 class="text-lg font-bold">Tùy chỉnh tốc độ</h3>
                            <p class="text-sm text-gray-600">Dễ dàng điều chỉnh tốc độ giọng nói để phù hợp nhu cầu của bạn.</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 text-blue-600 text-2xl">
                            💾
                        </div>
                        <div class="flex flex-col gap-1">
                            <h3 class="text-lg font-bold">Tải xuống dễ dàng</h3>
                            <p class="text-sm text-gray-600">Xuất file âm thanh chất lượng cao ở định dạng MP3 phổ biến.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Applications Section -->
    <section id="applications" class="py-20 sm:py-24">
        <div class="container mx-auto px-4">
            <div class="flex flex-col gap-12">
                <div class="flex flex-col gap-3 text-center max-w-2xl mx-auto">
                    <h2 class="text-3xl lg:text-4xl font-bold tracking-tight">Ứng dụng cho mọi lĩnh vực</h2>
                    <p class="text-gray-600">Minh họa cách ứng dụng giúp ích cho người sáng tạo nội dung, nhà giáo dục, doanh nghiệp, và người dùng cá nhân.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="flex flex-col gap-4 bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow">
                        <div class="h-48 w-full overflow-hidden">
                            <img 
                                src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&h=400&fit=crop&q=80" 
                                alt="Students learning" 
                                class="w-full h-full object-cover hover:scale-110 transition-transform duration-300"
                            />
                        </div>
                        <div class="p-6 pt-0 flex flex-col gap-2">
                            <h3 class="text-xl font-bold">Giáo dục & Học tập</h3>
                            <p class="text-sm text-gray-600">Tạo tài liệu học tập âm thanh, sách nói cho học sinh hoặc học ngoại ngữ với phát âm chuẩn.</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow">
                        <div class="h-48 w-full overflow-hidden">
                            <img 
                                src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&h=400&fit=crop&q=80" 
                                alt="Business team" 
                                class="w-full h-full object-cover hover:scale-110 transition-transform duration-300"
                            />
                        </div>
                        <div class="p-6 pt-0 flex flex-col gap-2">
                            <h3 class="text-xl font-bold">Doanh nghiệp & Marketing</h3>
                            <p class="text-sm text-gray-600">Thuyết minh video quảng cáo, tạo lời chào tổng đài tự động chuyên nghiệp và hấp dẫn.</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow">
                        <div class="h-48 w-full overflow-hidden">
                            <img 
                                src="https://images.unsplash.com/photo-1485846234645-a62644f84728?w=600&h=400&fit=crop&q=80" 
                                alt="Content creator" 
                                class="w-full h-full object-cover hover:scale-110 transition-transform duration-300"
                            />
                        </div>
                        <div class="p-6 pt-0 flex flex-col gap-2">
                            <h3 class="text-xl font-bold">Sáng tạo nội dung</h3>
                            <p class="text-sm text-gray-600">Lồng tiếng cho video YouTube, podcast, hoặc các dự án sáng tạo khác một cách nhanh chóng.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Public Audios Section -->
    
    <!-- CTA Section -->
    <section id="cta" class="py-20 sm:py-24">
        <div class="container mx-auto px-4">
            <div class="bg-blue-600 text-white rounded-xl p-8 sm:p-16 text-center shadow-xl">
                <div class="max-w-2xl mx-auto flex flex-col gap-8 items-center">
                    <div class="flex flex-col gap-3">
                        <h2 class="text-3xl lg:text-4xl font-bold tracking-tight">Sẵn sàng để bắt đầu?</h2>
                        <p class="opacity-90">Đăng ký tài khoản miễn phí ngay hôm nay và khám phá toàn bộ sức mạnh của giọng nói AI.</p>
                    </div>
                    <a href="./register" class="px-8 py-3 bg-white text-blue-600 text-base font-bold rounded-lg hover:scale-105 transition-transform shadow-md">
                        Tạo tài khoản ngay
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <!-- <footer class="bg-gray-50 border-t border-gray-200">
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">
                        🎙️
                    </div>
                    <p class="text-sm text-gray-600">© 2025 DocReader AI Studio. All rights reserved.</p>
                </div>
                <div class="flex items-center gap-6 text-sm text-gray-600">
                    <a href="./about" class="hover:text-blue-600 transition-colors">Về chúng tôi</a>
                    <a href="./terms" class="hover:text-blue-600 transition-colors">Điều khoản dịch vụ</a>
                    <a href="./privacy" class="hover:text-blue-600 transition-colors">Chính sách bảo mật</a>
                </div>
            </div>
        </div>
    </footer> -->
    <script>
    // Load public audios for homepage
    async function loadPublicAudios() {
        const container = document.getElementById('public-audios-container');
        
        try {
            const response = await fetch('<?= BASE_URL ?>/api/share.php?action=get-public&limit=4');
            const data = await response.json();
            
            if (data.success && data.data.items && data.data.items.length > 0) {
                container.innerHTML = data.data.items.map(audio => {
                    const truncatedTitle = audio.title.length > 30 ? audio.title.substring(0, 30) + '...' : audio.title;
                    const truncatedText = audio.text.length > 60 ? audio.text.substring(0, 60) + '...' : audio.text;
                    
                    // Store audio data for modal
                    window.audioDataMap = window.audioDataMap || {};
                    window.audioDataMap[audio.id] = audio;
                    
                    return `
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow cursor-pointer group" onclick="openAudioModal(${audio.id})">
                            <div class="bg-gradient-to-r from-blue-400 to-purple-500 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white text-xl">
                                        🎵
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-white font-bold text-sm truncate">${escapeHtml(truncatedTitle)}</h3>
                                        <p class="text-white/80 text-xs">${escapeHtml(audio.category_name)}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4">
                                <p class="text-gray-600 text-xs mb-3 line-clamp-2">${escapeHtml(truncatedText)}</p>
                                <div class="flex items-center justify-between text-xs text-gray-500 mt-2">
                                    <span> ${escapeHtml(audio.author)}</span>
                                    <span>👁️ ${audio.views}</span>
                                </div>
                                <button class="mt-3 w-full py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition">
                                    Nghe & Xem nội dung
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = `
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">Chưa có audio nào được chia sẻ</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading public audios:', error);
            container.innerHTML = `
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500">Không thể tải audio</p>
                </div>
            `;
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Open audio modal with full content
    function openAudioModal(audioId) {
        const audio = window.audioDataMap[audioId];
        if (!audio) return;
        
        // Create modal if not exists
        let modal = document.getElementById('audio-detail-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'audio-detail-modal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4';
            modal.onclick = function(e) {
                if (e.target === modal) closeAudioModal();
            };
            document.body.appendChild(modal);
        }
        
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center text-3xl">
                                🎵
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">${escapeHtml(audio.title)}</h3>
                                <p class="text-white/80 text-sm">${escapeHtml(audio.category_name)} • 👤 ${escapeHtml(audio.author)} • 👁️ ${audio.views} lượt xem</p>
                            </div>
                        </div>
                        <button onclick="closeAudioModal()" class="text-white/80 hover:text-white p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Audio Player -->
                <div class="p-4 bg-gray-50 border-b">
                    <audio id="modal-audio-player" controls class="w-full">
                        <source src="${audio.audio_url}" type="audio/mpeg">
                    </audio>
                </div>
                
                <!-- Content -->
                <div class="p-6 overflow-y-auto flex-1">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">📝 Nội dung văn bản</h4>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-800 whitespace-pre-wrap leading-relaxed">${escapeHtml(audio.text)}</p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="p-4 border-t bg-gray-50 flex justify-end">
                    <button onclick="closeAudioModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                        Đóng
                    </button>
                </div>
            </div>
        `;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    // Close audio modal
    function closeAudioModal() {
        const modal = document.getElementById('audio-detail-modal');
        if (modal) {
            // Pause audio when closing
            const audioPlayer = document.getElementById('modal-audio-player');
            if (audioPlayer) audioPlayer.pause();
            
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
    
    document.addEventListener('DOMContentLoaded', loadPublicAudios);
    </script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
