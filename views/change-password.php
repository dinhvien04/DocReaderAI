<?php
$pageTitle = 'Đổi mật khẩu - DocReader AI Studio';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-md">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= BASE_URL ?>/dashboard" class="text-purple-600 hover:text-purple-700 mb-4 inline-block">
                ← Quay lại Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Đổi mật khẩu</h1>
            <p class="text-gray-600 mt-2">Cập nhật mật khẩu của bạn</p>
        </div>

        <!-- Change Password Form -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <form id="change-password-form" class="space-y-6">
                <!-- Current Password -->
                <div>
                    <label for="current-password" class="block text-sm font-medium text-gray-700 mb-2">
                        Mật khẩu hiện tại
                    </label>
                    <input 
                        type="password" 
                        id="current-password"
                        name="current-password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="Nhập mật khẩu hiện tại"
                    />
                </div>

                <!-- New Password -->
                <div>
                    <label for="new-password" class="block text-sm font-medium text-gray-700 mb-2">
                        Mật khẩu mới
                    </label>
                    <input 
                        type="password" 
                        id="new-password"
                        name="new-password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
                    />
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
                        Xác nhận mật khẩu mới
                    </label>
                    <input 
                        type="password" 
                        id="confirm-password"
                        name="confirm-password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="Nhập lại mật khẩu mới"
                    />
                </div>

                <!-- Security Tips -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">Mẹo bảo mật:</h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Sử dụng ít nhất 6 ký tự</li>
                        <li>• Kết hợp chữ hoa, chữ thường và số</li>
                        <li>• Không sử dụng mật khẩu dễ đoán</li>
                        <li>• Không chia sẻ mật khẩu với người khác</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    id="submit-btn"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg font-medium transition"
                >
                    Đổi mật khẩu
                </button>

                <!-- Cancel Button -->
                <a 
                    href="<?= BASE_URL ?>/dashboard" 
                    class="block w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-lg font-medium transition"
                >
                    Hủy
                </a>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('change-password-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const submitBtn = document.getElementById('submit-btn');
    
    // Validate
    if (newPassword.length < 6) {
        showToast('Mật khẩu mới phải có ít nhất 6 ký tự', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showToast('Mật khẩu xác nhận không khớp', 'error');
        return;
    }
    
    if (currentPassword === newPassword) {
        showToast('Mật khẩu mới phải khác mật khẩu hiện tại', 'error');
        return;
    }
    
    // Submit
    setLoading(submitBtn, true);
    try {
        const response = await apiRequest(`${API_BASE}/auth.php?action=change-password`, {
            method: 'POST',
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword
            })
        });
        
        if (response.success) {
            showToast('Đổi mật khẩu thành công!', 'success');
            setTimeout(() => {
                window.location.href = '<?= BASE_URL ?>/dashboard';
            }, 1500);
        }
    } catch (error) {
        // Error handled by apiRequest
    } finally {
        setLoading(submitBtn, false);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
