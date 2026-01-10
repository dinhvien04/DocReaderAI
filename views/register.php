<?php
$pageTitle = 'Đăng ký - DocReader AI Studio';

// Start session to check if user is already logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, redirect based on role
if (isset($_SESSION['user_id']) && isset($_SESSION['user'])) {
    $redirectUrl = $_SESSION['user']['role'] === 'admin' 
        ? '/KK/views/admin' 
        : '/KK/dashboard';
    header('Location: ' . $redirectUrl);
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-50 to-blue-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-4xl font-bold text-gray-900 mb-2">
                Đăng ký tài khoản
            </h2>
            <p class="text-gray-600">
                Tạo tài khoản miễn phí ngay hôm nay
            </p>
        </div>

        <!-- Registration Form -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <!-- Step 1: Enter Information -->
            <div id="step-1">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Thông tin tài khoản</h3>
                <form id="register-form" class="space-y-4">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username
                        </label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="username (3-20 ký tự)"
                        />
                        <p class="text-xs text-gray-500 mt-1">Chỉ chứa chữ, số và dấu gạch dưới</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="your@email.com"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Mật khẩu
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                onclick="togglePassword('password', 'toggle-password-icon')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                            >
                                <svg id="toggle-password-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Tối thiểu 6 ký tự</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
                            Xác nhận mật khẩu
                        </label>
                        <div class="relative">
                            <input
                                id="confirm-password"
                                name="confirm-password"
                                type="password"
                                required
                                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                onclick="togglePassword('confirm-password', 'toggle-confirm-password-icon')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                            >
                                <svg id="toggle-confirm-password-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        id="register-btn"
                        class="w-full gradient-bg text-white py-3 rounded-lg font-medium hover:opacity-90 transition"
                    >
                        Đăng ký
                    </button>
                </form>
            </div>

            <!-- Step 2: Verify OTP -->
            <div id="step-2" class="hidden">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Xác nhận OTP</h3>
                <p class="text-gray-600 mb-6">
                    Chúng tôi đã gửi mã OTP đến email của bạn. Vui lòng nhập mã để kích hoạt tài khoản.
                </p>
                <form id="verify-form" class="space-y-4">
                    <div>
                        <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">
                             Mã OTP (6 chữ số)
                        </label>
                        <input
                            id="otp"
                            name="otp"
                            type="text"
                            maxlength="6"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center text-2xl tracking-widest"
                            placeholder="000000"
                        />
                    </div>

                    <button
                        type="submit"
                        id="verify-btn"
                        class="w-full gradient-bg text-white py-3 rounded-lg font-medium hover:opacity-90 transition"
                    >
                        Xác nhận
                    </button>

                    <button
                        type="button"
                        id="back-btn"
                        class="w-full bg-gray-200 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-300 transition"
                    >
                        ← Quay lại
                    </button>
                </form>
            </div>
        </div>

        <!-- Login Link -->
        <div class="text-center">
            <span class="text-gray-600">Đã có tài khoản? </span>
            <a href="<?= BASE_URL ?>/login" class="font-medium text-purple-600 hover:text-purple-500">
                Đăng nhập ngay
            </a>
        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="<?= BASE_URL ?>/" class="text-sm text-gray-600 hover:text-gray-900">
                ← Quay lại trang chủ
            </a>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
        `;
    } else {
        input.type = 'password';
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
