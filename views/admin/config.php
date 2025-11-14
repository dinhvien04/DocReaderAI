<?php
$pageTitle = 'System Config - Admin';
require_once __DIR__ . '/../../middleware/admin.php';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">⚙️ Cấu hình hệ thống</h1>
        <a href="<?= BASE_URL ?>/admin" class="text-purple-600 hover:text-purple-700">← Quay lại Dashboard</a>
    </div>

    <div id="configs-container">
        <div class="text-center py-8">Đang tải...</div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
