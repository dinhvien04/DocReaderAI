<?php
$pageTitle = 'Về chúng tôi - DocReader AI Studio';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">📖 Về chúng tôi</h1>
            <p class="text-xl text-gray-600">DocReader AI Studio - Giải pháp chuyển đổi văn bản thành giọng nói</p>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-lg shadow-lg p-8 space-y-8">
            <!-- Mission -->
            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-4">🎯 Sứ mệnh</h2>
                <p class="text-gray-700 leading-relaxed">
                    DocReader AI Studio được phát triển với sứ mệnh mang đến giải pháp chuyển đổi văn bản thành giọng nói 
                    chất lượng cao, giúp người dùng dễ dàng tiếp cận thông tin một cách thuận tiện và hiệu quả nhất.
                </p>
            </section>

            <!-- Features -->
            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-4">✨ Tính năng nổi bật</h2>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <span class="text-green-500 mr-3 text-xl">✓</span>
                        <span class="text-gray-700"><strong>Chuyển đổi văn bản thành giọng nói</strong> với công nghệ AI tiên tiến</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-3 text-xl">✓</span>
                        <span class="text-gray-700"><strong>Tóm tắt nội dung</strong> tự động, tiết kiệm thời gian</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-3 text-xl">✓</span>
                        <span class="text-gray-700"><strong>Dịch thuật đa ngôn ngữ</strong> chính xác và nhanh chóng</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-3 text-xl">✓</span>
                        <span class="text-gray-700"><strong>Hỗ trợ nhiều định dạng file</strong>: PDF, TXT, DOC, DOCX</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-3 text-xl">✓</span>
                        <span class="text-gray-700"><strong>Lưu lịch sử</strong> và tiếp tục nghe từ vị trí đã dừng</span>
                    </li>
                </ul>
            </section>

            <!-- Technology -->
            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-4">🚀 Công nghệ</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Chúng tôi sử dụng các công nghệ AI và Machine Learning hàng đầu để mang đến trải nghiệm tốt nhất:
                </p>
                <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <li class="flex items-center">
                        <span class="text-blue-500 mr-2">▸</span>
                        <span class="text-gray-700">Azure Speech Services</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-blue-500 mr-2">▸</span>
                        <span class="text-gray-700">Google Cloud AI</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-blue-500 mr-2">▸</span>
                        <span class="text-gray-700">Natural Language Processing</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-blue-500 mr-2">▸</span>
                        <span class="text-gray-700">Deep Learning Models</span>
                    </li>
                </ul>
            </section>

            <!-- Contact -->
            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-4">📞 Liên hệ</h2>
                <div class="space-y-2 text-gray-700">
                    <p><strong>Email:</strong> support@docreader.com</p>
                    <p><strong>Hotline:</strong> 1900 xxxx</p>
                    <p><strong>Địa chỉ:</strong> Hà Nội, Việt Nam</p>
                </div>
            </section>

            <!-- Back Button -->
            <div class="text-center pt-6 border-t">
                <a href="<?= BASE_URL ?>/" class="text-purple-600 hover:text-purple-700 font-medium">
                    ← Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
