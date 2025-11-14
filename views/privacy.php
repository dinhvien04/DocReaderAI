<?php
$pageTitle = 'Chính sách bảo mật - DocReader AI Studio';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🔒 Chính sách bảo mật</h1>
            <p class="text-gray-600">Cập nhật lần cuối: <?= date('d/m/Y') ?></p>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-lg shadow-lg p-8 space-y-6">
            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-3">1. Cam kết bảo mật</h2>
                <p class="text-gray-700 leading-relaxed">
                    DocReader AI Studio cam kết bảo vệ quyền riêng tư và thông tin cá nhân của bạn. Chính sách bảo mật này 
                    giải thích cách chúng tôi thu thập, sử dụng, lưu trữ và bảo vệ thông tin của bạn khi sử dụng dịch vụ.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-3">2. Thông tin chúng tôi thu thập</h2>
                <p class="text-gray-700 leading-relaxed mb-3">
                    Chúng tôi thu thập các loại thông tin sau:
                </p>
                <ul class="space-y-3 text-gray-700">
                    <li><strong>2.1. Thông tin tài khoản:</strong> Email, username, mật khẩu (được mã hóa)</li>
                    <li><strong>2.2. Thông tin sử dụng:</strong> Văn bản bạn chuyển đổi, lịch sử audio, thời gian sử dụng</li>
                    <li><strong>2.3. Thông tin kỹ thuật:</strong> Địa chỉ IP, loại trình duyệt, hệ điều hành</li>
                    <li><strong>2.4. Cookies:</strong> Để duy trì phiên đăng nhập và cải thiện trải nghiệm người dùng</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-3">3. Cách chúng tôi sử dụng thông tin</h2>
                <p class="text-gray-700 leading-relaxed mb-3">
                    Thông tin của bạn được sử dụng để:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                    <li>Cung cấp và cải thiện dịch vụ</li>
                    <li>Xác thực và bảo mật tài khoản của bạn</li>
                    <li>Lưu trữ lịch sử và tùy chỉnh trải nghiệm</li>
                    <li>Gửi thông báo quan trọng về dịch vụ</li>
                    <li>Phân tích và cải thiện hiệu suất hệ thống</li>
                    <li>Tuân thủ các yêu cầu pháp lý</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-3">4. Bảo vệ thông tin</h2>
                <p class="text-gray-700 leading-relaxed mb-3">
                    Chúng tôi áp dụng các biện pháp bảo mật sau:
                </p>
                <ul class="space-y-2 text-gray-700">
                    <li>✓ Mã hóa mật khẩu bằng thuật toán bcrypt</li>
                    <li>✓ Sử dụng HTTPS để bảo mật kết nối</li>
                    <li>✓ Xác thực phiên làm việc (session) an toàn</li>
                    <li>✓ Giới hạn quyền truy cập vào dữ liệu</li>
                    <li>✓ Sao lưu dữ liệu định kỳ</li>
                    <li>✓ Giám sát và phát hiện xâm nhập</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-3">5. Chia sẻ thông tin</h2>
                <p class="text-gray-700 leading-relaxed mb-3">
                    Chúng tôi <strong>KHÔNG</strong> bán hoặc cho thuê thông tin cá nhân của bạn. Thông tin chỉ được chia sẻ trong các trường hợp:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                    <li>Với sự đồng ý của bạn</li>
                    <li>Với các nhà cung cấp dịch vụ (Azure, Google Cloud) để xử lý dữ liệu</li>
                    <li>Khi được yêu cầu bởi pháp luật</li>
                    <li>Để bảo vệ quyền lợi và an toàn của chúng tôi và người dùng</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-3">6. Quyền của bạn</h2>
                <p class="text-gray-700 leading-relaxed mb-3">
                    Bạn có các quyền sau đối với thông tin cá nhân:
                </p>
                <ul class="space-y-2 text-gray-700">
                    <li><strong>Quyền truy cập:</strong> Xem thông tin cá nhân mà chúng tôi lưu trữ</li>
                    <li><strong>Quyền sửa đổi:</strong> Cập nhật thông tin không chính xác</li>
                    <li><strong>Quyền xóa:</strong> Yêu cầu xóa tài khoản và dữ liệu</li>
                    <li><strong>Quyền từ chối:</strong> Từ chối nhận email marketing (nếu có)</li>
                    <li><strong>Quyền khiếu nại:</strong> Liên hệ với chúng tôi về vấn đề bảo mật</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-3">7. Lưu trữ dữ liệu</h2>
                <p class="text-gray-700 leading-relaxed">
                    Dữ liệu của bạn được lưu trữ trên máy chủ an toàn tại Việt Nam. Chúng tôi lưu giữ thông tin của bạn 
                    miễn là tài khoản còn hoạt động hoặc cần thiết để cung cấp dịch vụ. Khi bạn xóa tài khoản, dữ liệu 
                    sẽ được xóa vĩnh viễn trong vòng 30 ngày.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-3">8. Cookies</h2>
                <p class="text-gray-700 leading-relaxed">
                    Chúng tôi sử dụng cookies để duy trì phiên đăng nhập và cải thiện trải nghiệm. Bạn có thể tắt cookies 
                    trong trình duyệt, nhưng điều này có thể ảnh hưởng đến chức năng của dịch vụ.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-3">9. Thay đổi chính sách</h2>
                <p class="text-gray-700 leading-relaxed">
                    Chúng tôi có thể cập nhật Chính sách bảo mật này theo thời gian. Chúng tôi sẽ thông báo cho bạn về 
                    các thay đổi quan trọng qua email hoặc thông báo trên trang web.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-purple-600 mb-3">10. Liên hệ</h2>
                <p class="text-gray-700 leading-relaxed">
                    Nếu bạn có câu hỏi về Chính sách bảo mật hoặc muốn thực hiện quyền của mình, vui lòng liên hệ:
                </p>
                <div class="mt-3 space-y-1 text-gray-700">
                    <p><strong>Email:</strong> <a href="mailto:privacy@docreader.com" class="text-purple-600 hover:underline">privacy@docreader.com</a></p>
                    <p><strong>Hotline:</strong> 1900 xxxx</p>
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
