    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-auto">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- About -->
                <div>
                    <h3 class="text-xl font-bold mb-4">DocReader AI Studio</h3>
                    <p class="text-gray-400">
                        Chuyển đổi văn bản thành giọng nói với công nghệ AI tiên tiến
                    </p>
                </div>
                
                <!-- Links -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Liên kết</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= BASE_URL ?>/" class="text-gray-400 hover:text-white transition">Trang chủ</a></li>
                        <li><a href="<?= BASE_URL ?>/dashboard" class="text-gray-400 hover:text-white transition">Dashboard</a></li>
                        <li><a href="<?= BASE_URL ?>/about" class="text-gray-400 hover:text-white transition">Về chúng tôi</a></li>
                        <li><a href="<?= BASE_URL ?>/terms" class="text-gray-400 hover:text-white transition">Điều khoản dịch vụ</a></li>
                        <li><a href="<?= BASE_URL ?>/privacy" class="text-gray-400 hover:text-white transition">Chính sách bảo mật</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Liên hệ</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li>Email: support@docreader.com</li>
                        <li>Hotline: 1900 xxxx</li>
                        <li>Địa chỉ: Hà Nội, Việt Nam</li>
                    </ul>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="text-center mt-8 pt-8 border-t border-gray-700">
                <p class="text-gray-400">
                    &copy; <?= date('Y') ?> DocReader AI Studio. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- PDF.js Worker -->
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    
    <!-- Application Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/app.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/assets/js/auth.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/assets/js/tts.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/assets/js/document.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>/assets/js/dashboard.js?v=<?= time() ?>"></script>

    <script src="<?= BASE_URL ?>/assets/js/admin.js?v=<?= time() ?>"></script>
    
    <!-- Mobile Menu Toggle -->
    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
</body>
</html>