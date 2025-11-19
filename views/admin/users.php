<?php
$pageTitle = 'User Management - Admin';
require_once __DIR__ . '/../../middleware/admin.php';
require_once __DIR__ . '/../../includes/header.php';
?>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/vie.png">

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">👥 Quản lý Users</h1>
        <a href="<?= BASE_URL ?>/admin" class="text-purple-600 hover:text-purple-700">← Quay lại Dashboard</a>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <input 
            type="text" 
            id="user-search" 
            placeholder="🔍 Tìm kiếm theo email..." 
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
        />
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="users-tbody" class="bg-white divide-y divide-gray-200">
                <tr><td colspan="6" class="text-center py-8">Đang tải...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="user-pagination" class="flex justify-center gap-2 mt-6"></div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
