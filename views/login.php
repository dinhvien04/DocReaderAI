<?php
$pageTitle = 'Đăng nhập - DocReader AI Studio';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-4xl font-bold text-gray-900 mb-2">
                🔐 Đăng nhập
            </h2>
            <p class="text-gray-600">
                Chào mừng bạn quay trở lại!
            </p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form id="login-form" class="space-y-6">
                <!-- Username or Email -->
                <div>
                    <label for="identifier" class="block text-sm font-medium text-gray-700 mb-2">
                        👤 Username hoặc Email
                    </label>
                    <input
                        id="identifier"
                        name="identifier"
                        type="text"
                        required
                        class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="username hoặc email@example.com"
                    />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        🔒 Mật khẩu
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="••••••••"
                    />
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            name="remember-me"
                            type="checkbox"
                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                        />
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="<?= BASE_URL ?>/reset-password" class="font-medium text-purple-600 hover:text-purple-500">
                            Quên mật khẩu?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white gradient-bg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition"
                    >
                        Đăng nhập
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Chưa có tài khoản?
                        </span>
                    </div>
                </div>
            </div>

            <!-- Register Link -->
            <div class="mt-6 text-center">
                <a href="<?= BASE_URL ?>/register" class="font-medium text-purple-600 hover:text-purple-500">
                    Đăng ký ngay →
                </a>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="<?= BASE_URL ?>/" class="text-sm text-gray-600 hover:text-gray-900">
                ← Quay lại trang chủ
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
