<?php
$pageTitle = 'Thông tin cá nhân - DocReader AI Studio';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-2xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= BASE_URL ?>/dashboard" class="text-purple-600 hover:text-purple-700 mb-4 inline-block">
                ← Quay lại Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-900">👤 Thông tin cá nhân</h1>
        </div>

        <!-- Profile Form -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <form id="profile-form" class="space-y-6">
                <!-- Avatar -->
                <div class="flex justify-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-3xl">
                        <?= strtoupper(substr($currentUser['email'], 0, 1)) ?>
                    </div>
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input 
                        type="text" 
                        value="<?= htmlspecialchars($currentUser['username']) ?>" 
                        disabled
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                    />
                    <p class="text-xs text-gray-500 mt-1">Username không thể thay đổi</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input 
                        type="email" 
                        value="<?= htmlspecialchars($currentUser['email']) ?>" 
                        disabled
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                    />
                    <p class="text-xs text-gray-500 mt-1">Email không thể thay đổi</p>
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vai trò</label>
                    <div class="px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-medium <?= $currentUser['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                            <?= $currentUser['role'] === 'admin' ? '👑 Admin' : '👤 User' ?>
                        </span>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Thông tin tài khoản</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Trạng thái:</span>
                            <span class="font-medium text-green-600">✓ Đang hoạt động</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ngày tạo:</span>
                            <span class="font-medium text-gray-900"><?= date('d/m/Y', strtotime($currentUser['created_at'] ?? 'now')) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-6 border-t">
                    <a href="<?= BASE_URL ?>/change-password" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg font-medium transition text-center">
                        🔒 Đổi mật khẩu
                    </a>
                    <a href="<?= BASE_URL ?>/dashboard" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-lg font-medium transition text-center">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
