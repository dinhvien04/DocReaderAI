<?php
$pageTitle = 'Đặt lại mật khẩu - DocReader AI Studio';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-4xl font-bold text-gray-900 mb-2">
                🔑 Đặt lại mật khẩu
            </h2>
            <p class="text-gray-600">
                Khôi phục quyền truy cập tài khoản của bạn
            </p>
        </div>

        <!-- Progress Indicator -->
        <div class="flex justify-center items-center space-x-4">
            <div id="reset-progress-1" class="flex items-center">
                <div class="w-10 h-10 rounded-full gradient-bg text-white flex items-center justify-center font-bold">1</div>
                <span class="ml-2 text-sm font-medium text-gray-700">Email</span>
            </div>
            <div class="w-12 h-1 bg-gray-300"></div>
            <div id="reset-progress-2" class="flex items-center opacity-50">
                <div class="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold">2</div>
                <span class="ml-2 text-sm font-medium text-gray-700">OTP</span>
            </div>
            <div class="w-12 h-1 bg-gray-300"></div>
            <div id="reset-progress-3" class="flex items-center opacity-50">
                <div class="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold">3</div>
                <span class="ml-2 text-sm font-medium text-gray-700">Mật khẩu mới</span>
            </div>
        </div>

        <!-- Reset Password Form -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <!-- Step 1: Email -->
            <div id="reset-step-1">
                <h3 class="text-xl font-bold mb-4 text-gray-800">Bước 1: Nhập email</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Nhập email đã đăng ký để nhận mã OTP
                </p>
                <div class="space-y-4">
                    <div>
                        <label for="reset-email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input
                            id="reset-email"
                            type="email"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="your@email.com"
                        />
                    </div>
                    <button
                        id="reset-send-otp-btn"
                        type="button"
                        class="w-full gradient-bg text-white py-3 rounded-lg font-medium hover:opacity-90 transition"
                    >
                        Gửi mã OTP
                    </button>
                </div>
            </div>

            <!-- Step 2: OTP Verification -->
            <div id="reset-step-2" class="hidden">
                <h3 class="text-xl font-bold mb-4 text-gray-800">Bước 2: Xác thực OTP</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Mã OTP đã được gửi đến email của bạn. Mã có hiệu lực trong 10 phút.
                </p>
                <div class="space-y-4">
                    <div>
                        <label for="reset-otp" class="block text-sm font-medium text-gray-700 mb-2">
                            Mã OTP (6 chữ số)
                        </label>
                        <input
                            id="reset-otp"
                            type="text"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-center text-2xl tracking-widest"
                            placeholder="000000"
                        />
                    </div>
                    <button
                        id="reset-verify-otp-btn"
                        type="button"
                        class="w-full gradient-bg text-white py-3 rounded-lg font-medium hover:opacity-90 transition"
                    >
                        Xác thực OTP
                    </button>
                    <button
                        id="resend-otp-btn"
                        type="button"
                        class="w-full bg-gray-200 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-300 transition text-sm"
                    >
                        Gửi lại mã OTP
                    </button>
                </div>
            </div>

            <!-- Step 3: New Password -->
            <div id="reset-step-3" class="hidden">
                <h3 class="text-xl font-bold mb-4 text-gray-800">Bước 3: Mật khẩu mới</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Tạo mật khẩu mới cho tài khoản của bạn
                </p>
                <div class="space-y-4">
                    <div>
                        <label for="new-password" class="block text-sm font-medium text-gray-700 mb-2">
                            Mật khẩu mới
                        </label>
                        <input
                            id="new-password"
                            type="password"
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Tối thiểu 6 ký tự"
                        />
                    </div>
                    <div>
                        <label for="confirm-new-password" class="block text-sm font-medium text-gray-700 mb-2">
                            Xác nhận mật khẩu mới
                        </label>
                        <input
                            id="confirm-new-password"
                            type="password"
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Nhập lại mật khẩu"
                        />
                    </div>
                    <button
                        id="reset-complete-btn"
                        type="button"
                        class="w-full gradient-bg text-white py-3 rounded-lg font-medium hover:opacity-90 transition"
                    >
                        Đặt lại mật khẩu
                    </button>
                </div>
            </div>
        </div>

        <!-- Login Link -->
        <div class="text-center">
            <span class="text-gray-600">Nhớ mật khẩu? </span>
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
// Handle reset password flow
document.addEventListener('DOMContentLoaded', function() {
    let resetEmail = '';
    let resetOtp = '';

    // Step 1: Send OTP
    document.getElementById('reset-send-otp-btn')?.addEventListener('click', async function() {
        const email = document.getElementById('reset-email').value.trim();
        
        if (!email || !isValidEmail(email)) {
            showToast('Email không hợp lệ', 'error');
            return;
        }

        resetEmail = email;
        const btn = this;
        setLoading(btn, true);

        try {
            await sendResetOtp(email);
            toggleElement(document.getElementById('reset-step-1'), false);
            toggleElement(document.getElementById('reset-step-2'), true);
            document.getElementById('reset-progress-1').classList.add('opacity-50');
            document.getElementById('reset-progress-2').classList.remove('opacity-50');
        } catch (error) {
            // Error handled
        } finally {
            setLoading(btn, false);
        }
    });

    // Step 2: Verify OTP (just store it, don't verify yet)
    document.getElementById('reset-verify-otp-btn')?.addEventListener('click', async function() {
        const otp = document.getElementById('reset-otp').value.trim();
        
        if (!otp || otp.length !== 6) {
            showToast('OTP phải có 6 chữ số', 'error');
            return;
        }

        resetOtp = otp;
        
        // Move to next step
        toggleElement(document.getElementById('reset-step-2'), false);
        toggleElement(document.getElementById('reset-step-3'), true);
        document.getElementById('reset-progress-2').classList.add('opacity-50');
        document.getElementById('reset-progress-3').classList.remove('opacity-50');
    });

    // Resend OTP
    document.getElementById('resend-otp-btn')?.addEventListener('click', async function() {
        const btn = this;
        setLoading(btn, true);
        try {
            await sendResetOtp(resetEmail);
        } finally {
            setLoading(btn, false);
        }
    });

    // Step 3: Reset Password
    document.getElementById('reset-complete-btn')?.addEventListener('click', async function() {
        const password = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-new-password').value;
        
        if (!password || password.length < 6) {
            showToast('Mật khẩu phải có ít nhất 6 ký tự', 'error');
            return;
        }

        if (password !== confirmPassword) {
            showToast('Mật khẩu không khớp', 'error');
            return;
        }

        const btn = this;
        setLoading(btn, true);

        try {
            await resetPassword(resetEmail, resetOtp, password);
        } catch (error) {
            // Nếu OTP sai hoặc hết hạn, quay lại bước 2 để nhập lại
            if (error.message.includes('OTP') || error.message.includes('hết hạn') || error.message.includes('INVALID_OTP')) {
                toggleElement(document.getElementById('reset-step-3'), false);
                toggleElement(document.getElementById('reset-step-2'), true);
                document.getElementById('reset-progress-3').classList.add('opacity-50');
                document.getElementById('reset-progress-2').classList.remove('opacity-50');
                document.getElementById('reset-otp').value = '';
                resetOtp = '';
            }
        } finally {
            setLoading(btn, false);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
