<?php
$pageTitle = '404 - Không tìm thấy trang';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 bg-gray-50">
    <div class="max-w-2xl w-full text-center">
        <!-- Animated Robot -->
        <div class="mb-8">
            <div class="text-9xl animate-bounce">🤖</div>
        </div>

        <!-- Error Message -->
        <h1 class="text-6xl font-bold text-gray-900 mb-4">
            404
        </h1>
        <h2 class="text-3xl font-bold text-gray-700 mb-4">
            Oops! Trang không tồn tại
        </h2>
        <p class="text-xl text-gray-600 mb-8">
            Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a 
                href="<?= BASE_URL ?>/" 
                class="inline-block gradient-bg text-white px-8 py-3 rounded-lg font-semibold hover:opacity-90 transition"
            >
                🏠 Về trang chủ
            </a>
            <a 
                href="<?= BASE_URL ?>/dashboard" 
                class="inline-block bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition"
            >
                📊 Dashboard
            </a>
        </div>

        <!-- Helpful Links -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-gray-600 mb-4">Có thể bạn đang tìm:</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?= BASE_URL ?>/login" class="text-purple-600 hover:text-purple-700 font-medium">
                    Đăng nhập
                </a>
                <span class="text-gray-400">•</span>
                <a href="<?= BASE_URL ?>/register" class="text-purple-600 hover:text-purple-700 font-medium">
                    Đăng ký
                </a>
                <span class="text-gray-400">•</span>
                <a href="<?= BASE_URL ?>/dashboard" class="text-purple-600 hover:text-purple-700 font-medium">
                    Dashboard
                </a>
                <span class="text-gray-400">•</span>
                <a href="javascript:history.back()" class="text-purple-600 hover:text-purple-700 font-medium">
                    Quay lại
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

.animate-bounce {
    animation: bounce 2s infinite;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
