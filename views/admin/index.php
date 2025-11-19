<?php
$pageTitle = 'Admin Dashboard - DocReader AI Studio';
require_once __DIR__ . '/../../middleware/admin.php';
require_once __DIR__ . '/../../includes/header.php';
?>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/vie.png">

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">⚙️ Admin Dashboard</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Tổng người dùng</p>
                    <p id="total-users" class="text-3xl font-bold text-gray-900 mt-2">0</p>
                </div>
                <div class="text-5xl">👥</div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Người dùng hoạt động</p>
                    <p id="active-users" class="text-3xl font-bold text-green-600 mt-2">0</p>
                </div>
                <div class="text-5xl">✅</div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Tổng audio</p>
                    <p id="total-conversions" class="text-3xl font-bold text-purple-600 mt-2">0</p>
                </div>
                <div class="text-5xl">🎙️</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Tăng trưởng người dùng</h3>
            <canvas id="userGrowthChart"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xu hướng chuyển đổi</h3>
            <canvas id="conversionChart"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Thao tác nhanh</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="<?= BASE_URL ?>/admin-users" class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 transition">
                <span class="text-3xl">👥</span>
                <div>
                    <p class="font-medium text-gray-900">Quản lý Users</p>
                    <p class="text-sm text-gray-500">Xem và quản lý người dùng</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/admin-config" class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 transition">
                <span class="text-3xl">⚙️</span>
                <div>
                    <p class="font-medium text-gray-900">Cấu hình hệ thống</p>
                    <p class="text-sm text-gray-500">Điều chỉnh settings</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/dashboard" class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 transition">
                <span class="text-3xl">📊</span>
                <div>
                    <p class="font-medium text-gray-900">User Dashboard</p>
                    <p class="text-sm text-gray-500">Xem dashboard người dùng</p>
                </div>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
