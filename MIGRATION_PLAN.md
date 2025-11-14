 DANH SÁCH CÔNG VIỆC - PHP FULL STACK (Frontend + Backend)
🎯 PHẦN 1: CẤU TRÚC PROJECT
docreader-php/
├── config/
│   ├── database.php          # Kết nối MySQL
│   ├── config.php            # Cấu hình chung
│   └── .env                  # Environment variables
├── includes/
│   ├── header.php            # Header chung
│   ├── footer.php            # Footer chung
│   └── functions.php         # Helper functions
├── models/
│   ├── User.php              # Model User
│   ├── Data.php              # Model Audio/Document
│   └── SystemConfig.php      # Model Config
├── controllers/
│   ├── AuthController.php    # Xử lý auth
│   ├── DashboardController.php # Dashboard logic
│   ├── AdminController.php   # Admin logic
│   └── ApiController.php     # API endpoints
├── services/
│   ├── FptAiService.php      # FPT AI TTS
│   ├── GoogleApiService.php  # Google APIs
│   └── EmailService.php      # PHPMailer
├── middleware/
│   ├── auth.php              # Check login
│   └── admin.php             # Check admin role
├── api/
│   ├── auth.php              # API: login, register, logout
│   ├── tts.php               # API: text-to-speech
│   ├── document.php          # API: upload, delete, history
│   ├── translate.php         # API: translate, summary
│   └── admin.php             # API: admin functions
├── assets/
│   ├── css/
│   │   └── style.css         # CSS chính (hoặc Tailwind)
│   ├── js/
│   │   ├── app.js            # JS chính
│   │   ├── auth.js           # Login/Register logic
│   │   ├── tts.js            # Text-to-Speech
│   │   ├── document.js       # Document handling
│   │   └── admin.js          # Admin functions
│   └── images/
│       ├── logo.webp
│       ├── robot.gif
│       └── avatars/          # 9 avatars
├── uploads/                  # Thư mục upload files
├── views/                    # Hoặc pages/
│   ├── index.php             # Home page
│   ├── login.php             # Login page
│   ├── register.php          # Register page
│   ├── dashboard.php         # User dashboard
│   ├── reset-password.php    # Reset password
│   ├── admin/
│   │   ├── index.php         # Admin dashboard
│   │   ├── users.php         # User management
│   │   └── config.php        # System config
│   └── 404.php               # Not found page
├── database.sql              # SQL schema
├── .htaccess                 # URL rewriting
├── index.php                 # Entry point (router)
└── composer.json             # PHP dependencies
🎯 PHẦN 2: DATABASE (MySQL)
File: database.sql
[ ] Tạo database docreader_ai_studio

[ ] Bảng users:

CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') DEFAULT 'user',
  status ENUM('active', 'inactive') DEFAULT 'inactive',
  otp VARCHAR(10) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
[ ] Bảng audio_history:

CREATE TABLE audio_history (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  text TEXT NOT NULL,
  audio_url TEXT NOT NULL,
  voice VARCHAR(50) NOT NULL,
  lang VARCHAR(10) NOT NULL,
  position INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
[ ] Bảng system_config:

CREATE TABLE system_config (
  id INT PRIMARY KEY AUTO_INCREMENT,
  config_key VARCHAR(100) UNIQUE NOT NULL,
  config_value TEXT NOT NULL,
  description TEXT,
  category VARCHAR(50) DEFAULT 'limits',
  is_public BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
[ ] Insert default configs

[ ] Create indexes

🎯 PHẦN 3: CONFIG & SETUP
File: config/database.php
[ ] Kết nối MySQL với PDO
[ ] Error handling
[ ] Charset UTF-8
File: config/config.php
[ ] Load .env file (dùng vlucas/phpdotenv)
[ ] Define constants (BASE_URL, UPLOAD_DIR, etc.)
[ ] Timezone settings
File: .env
[ ] Database credentials
[ ] JWT secret key
[ ] FPT AI API key
[ ] Google API key
[ ] SMTP settings
File: composer.json
[ ] firebase/php-jwt
[ ] phpmailer/phpmailer
[ ] vlucas/phpdotenv
[ ] (optional) guzzlehttp/guzzle
File: .htaccess
[ ] URL rewriting
[ ] Security headers
[ ] PHP settings
🎯 PHẦN 4: MODELS (Database Layer)
File: models/User.php
[ ] getUserByEmail($email)
[ ] getUserById($id)
[ ] createUser($email, $otp)
[ ] updatePassword($email, $password)
[ ] updateOtp($email, $otp)
[ ] updateStatus($email, $status)
[ ] verifyPassword($email, $password)
[ ] getAllUsers($page, $limit, $search)
[ ] updateUserRole($userId, $role)
[ ] deleteUser($userId)
File: models/Data.php
[ ] addAudio($userId, $text, $audioUrl, $voice, $lang)
[ ] getAudioByUserId($userId)
[ ] getAudioById($id)
[ ] updatePosition($id, $position)
[ ] deleteAudio($id)
[ ] checkOwnership($id, $userId)
File: models/SystemConfig.php
[ ] getConfig($key)
[ ] setConfig($key, $value, $description, $category)
[ ] getAllConfigs($includePrivate)
[ ] updateConfig($key, $value)
🎯 PHẦN 5: SERVICES (Business Logic)
File: services/FptAiService.php
[ ] textToSpeech($text, $voice, $speed, $format)
[ ] getAvailableVoices()
[ ] validateConnection()
[ ] Sử dụng cURL để gọi FPT AI API
File: services/GoogleApiService.php
[ ] textToSpeech($text, $lang, $gender) (optional)
[ ] translateText($text, $targetLang)
[ ] summarizeText($text, $prompt)
[ ] detectLanguage($text)
[ ] Sử dụng cURL hoặc Guzzle
File: services/EmailService.php
[ ] sendOtpEmail($email, $otp, $type)
[ ] sendWelcomeEmail($email)
[ ] Sử dụng PHPMailer
[ ] HTML email templates
🎯 PHẦN 6: CONTROLLERS (Request Handlers)
File: controllers/AuthController.php
[ ] login() - Xử lý form login
[ ] register() - Xử lý form register
[ ] logout() - Destroy session
[ ] sendOtp() - Gửi OTP
[ ] verifyOtp() - Verify OTP
[ ] resetPassword() - Reset password
File: controllers/DashboardController.php
[ ] index() - Show dashboard
[ ] getHistory() - Lấy audio history
[ ] deleteAudio() - Xóa audio
[ ] updatePosition() - Update playback position
File: controllers/AdminController.php
[ ] index() - Admin dashboard
[ ] users() - User management
[ ] updateUserRole() - Change user role
[ ] deleteUser() - Delete user
[ ] systemConfig() - System config page
[ ] updateConfig() - Update config
File: controllers/ApiController.php
[ ] Handle all AJAX/API requests
[ ] Return JSON responses
[ ] Error handling
🎯 PHẦN 7: API ENDPOINTS (AJAX)
File: api/auth.php
[ ] POST /api/auth.php?action=login
[ ] POST /api/auth.php?action=register
[ ] POST /api/auth.php?action=logout
[ ] POST /api/auth.php?action=send-otp
[ ] POST /api/auth.php?action=verify-otp
[ ] POST /api/auth.php?action=reset-password
File: api/tts.php
[ ] POST /api/tts.php?action=convert - Text to speech
[ ] GET /api/tts.php?action=voices - Get available voices
[ ] GET /api/tts.php?action=test - Test connection
File: api/document.php
[ ] GET /api/document.php?action=history - Get history
[ ] POST /api/document.php?action=upload - Upload file
[ ] DELETE /api/document.php?action=delete&id=X - Delete audio
[ ] PATCH /api/document.php?action=update-position - Update position
File: api/translate.php
[ ] POST /api/translate.php?action=translate - Translate text
[ ] POST /api/translate.php?action=summary - Summarize text
[ ] POST /api/translate.php?action=detect - Detect language
File: api/admin.php
[ ] GET /api/admin.php?action=users - Get all users
[ ] POST /api/admin.php?action=update-role - Update user role
[ ] DELETE /api/admin.php?action=delete-user - Delete user
[ ] GET /api/admin.php?action=stats - Get statistics
[ ] POST /api/admin.php?action=update-config - Update config
🎯 PHẦN 8: VIEWS (Frontend Pages)
File: views/index.php (Home Page)
[ ] Hero section với gradient background
[ ] Features showcase
[ ] Stats section
[ ] Navigation (Login/Register buttons)
[ ] Footer
[ ] Giống y hệt Home.jsx hiện tại
File: views/login.php
[ ] Form login
[ ] Email + Password fields
[ ] Remember me checkbox
[ ] Link to register & reset password
[ ] AJAX submit
[ ] Giống y hệt Login.jsx
File: views/register.php
[ ] Step 1: Email + Send OTP
[ ] Step 2: Verify OTP
[ ] Step 3: Password + Avatar selection
[ ] AJAX submit
[ ] Giống y hệt Register.jsx
File: views/dashboard.php
[ ] Check login (session/JWT)
[ ] Tabs: TTS, Upload Document, Translate, History
[ ] Text-to-Speech component:
Textarea
Voice selection (6 giọng FPT AI)
Speed control
Convert button
Audio player
[ ] Upload Document component:
File upload (PDF, TXT)
Extract text
Convert to speech
[ ] Translate component:
Source text
Target language
Translate button
[ ] History component:
List audio history
Play/Resume
Delete
Position tracking
[ ] Giống y hệt Dashboard.jsx
File: views/reset-password.php
[ ] Step 1: Enter email
[ ] Step 2: Verify OTP
[ ] Step 3: New password
[ ] Giống y hệt ResetPass.jsx
File: views/admin/index.php
[ ] Check admin role
[ ] Statistics cards
[ ] Charts (Chart.js)
[ ] Quick actions
[ ] Giống y hệt AdminDashboard.jsx
File: views/admin/users.php
[ ] User table
[ ] Search
[ ] Pagination
[ ] Role management
[ ] Delete user
[ ] Giống y hệt UserManagement.jsx
File: views/admin/config.php
[ ] Config list
[ ] Edit config
[ ] Save button
[ ] Giống y hệt SystemConfig.jsx
File: views/404.php
[ ] Not found page
[ ] Giống y hệt NotFound.jsx
🎯 PHẦN 9: INCLUDES (Shared Components)
File: includes/header.php
[ ] HTML head
[ ] Meta tags
[ ] CSS links (Tailwind CDN hoặc custom CSS)
[ ] Navigation bar
[ ] Logo
[ ] User menu (nếu đã login)
File: includes/footer.php
[ ] Footer content
[ ] Copyright
[ ] Links
[ ] JS scripts
[ ] Close HTML tags
File: includes/functions.php
[ ] isLoggedIn() - Check session
[ ] isAdmin() - Check admin role
[ ] redirect($url) - Redirect helper
[ ] sanitize($data) - Input sanitization
[ ] generateOtp() - Generate OTP
[ ] formatDate($date) - Date formatting
🎯 PHẦN 10: MIDDLEWARE (Security)
File: middleware/auth.php
[ ] Check if user is logged in
[ ] Verify JWT token (nếu dùng JWT)
[ ] Hoặc check PHP session
[ ] Redirect to login if not authenticated
File: middleware/admin.php
[ ] Check if user is admin
[ ] Redirect to dashboard if not admin
🎯 PHẦN 11: ASSETS (CSS/JS)
File: assets/css/style.css
[ ] Gradient backgrounds
[ ] Card styles
[ ] Button styles
[ ] Form styles
[ ] Animations
[ ] Responsive design
[ ] Hoặc dùng Tailwind CSS CDN để giữ nguyên design
File: assets/js/app.js
[ ] Initialize app
[ ] Global functions
[ ] Toast notifications
File: assets/js/auth.js
[ ] login(email, password) - AJAX login
[ ] register(data) - AJAX register
[ ] sendOtp(email) - Send OTP
[ ] verifyOtp(email, otp) - Verify OTP
[ ] logout() - Logout
[ ] Store token/session
File: assets/js/tts.js
[ ] convertTextToSpeech(text, voice, speed) - Call API
[ ] playAudio(url) - Play audio
[ ] pauseAudio() - Pause audio
[ ] updatePosition(id, position) - Save position
[ ] Voice selection handling
File: assets/js/document.js
[ ] uploadDocument(file) - Upload file
[ ] extractText(file) - Extract text from PDF (PDF.js)
[ ] getHistory() - Load history
[ ] deleteAudio(id) - Delete audio
[ ] Render history list
File: assets/js/admin.js
[ ] getUsers(page, search) - Load users
[ ] updateUserRole(userId, role) - Update role
[ ] deleteUser(userId) - Delete user
[ ] getStats() - Load statistics
[ ] updateConfig(key, value) - Update config
🎯 PHẦN 12: ROUTING
File: index.php (Entry Point)
[ ] Router đơn giản:

<?php
$page = $_GET['page'] ?? 'home';

switch($page) {
    case 'home':
        include 'views/index.php';
        break;
    case 'login':
        include 'views/login.php';
        break;
    case 'register':
        include 'views/register.php';
        break;
    case 'dashboard':
        include 'middleware/auth.php';
        include 'views/dashboard.php';
        break;
    case 'admin':
        include 'middleware/admin.php';
        include 'views/admin/index.php';
        break;
    default:
        include 'views/404.php';
}
?>
[ ] Hoặc dùng .htaccess để clean URLs:

RewriteEngine On
RewriteRule ^login$ index.php?page=login [L]
RewriteRule ^register$ index.php?page=register [L]
RewriteRule ^dashboard$ index.php?page=dashboard [L]
🎯 PHẦN 13: AUTHENTICATION
Chọn 1 trong 2 cách:
Cách 1: PHP Session (Đơn giản hơn)

[ ] session_start() trong mỗi page
[ ] Lưu user info trong $_SESSION['user']
[ ] Check session trong middleware
Cách 2: JWT Token (Giống Node.js)

[ ] Generate JWT khi login
[ ] Store JWT trong localStorage (JS)
[ ] Gửi JWT trong header mỗi request
[ ] Verify JWT trong PHP
🎯 PHẦN 14: LIBRARIES & DEPENDENCIES
PHP (Composer):
[ ] composer require firebase/php-jwt - JWT
[ ] composer require phpmailer/phpmailer - Email
[ ] composer require vlucas/phpdotenv - .env
Frontend (CDN):
[ ] Tailwind CSS - <link href="https://cdn.tailwindcss.com">
[ ] PDF.js - <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/...">
[ ] Chart.js - <script src="https://cdn.jsdelivr.net/npm/chart.js">
[ ] Toastify - <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
🎯 PHẦN 15: TESTING & DEPLOYMENT
Testing:
[ ] Test login/register flow
[ ] Test FPT AI TTS
[ ] Test file upload
[ ] Test audio playback
[ ] Test admin functions
[ ] Test on mobile
Security:
[ ] SQL injection prevention (PDO prepared statements)
[ ] XSS protection (htmlspecialchars())
[ ] CSRF tokens
[ ] Password hashing (password_hash())
[ ] Input validation
[ ] File upload validation
Deployment:
[ ] Setup XAMPP/WAMP/LAMP
[ ] Import database.sql
[ ] Configure .env
[ ] Set folder permissions (uploads/)
[ ] Test on localhost
[ ] Deploy to hosting
📊 TỔNG KẾT
Tổng số file cần tạo:

Config: 3 files
Models: 3 files
Services: 3 files
Controllers: 4 files
API: 5 files
Views: 10 files
Includes: 3 files
Middleware: 2 files
Assets: 6 JS files + 1 CSS file
Database: 1 SQL file
TỔNG: ~40 files
Thời gian ước tính: 2-4 ngày

Độ ưu tiên:

⭐⭐⭐ Database + Config + Models
⭐⭐⭐ Authentication (Login/Register)
⭐⭐ Dashboard + TTS
⭐⭐ Frontend pages
⭐ Admin functions