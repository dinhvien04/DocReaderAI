# DocReader AI Studio - PHP Full Stack

Hệ thống chuyển đổi văn bản thành giọng nói với AI, hỗ trợ dịch thuật và tóm tắt văn bản.

## 🚀 Tính năng

- 🎙️ **Text-to-Speech**: Azure Speech Service với giọng đọc Neural chất lượng cao
  - 2 giọng Tiếng Việt: Hoài My (Nữ), Nam Minh (Nam)
  - 4 giọng Tiếng Anh: Jenny, Guy, Aria, Davis
- 📄 **Xử lý tài liệu**: Upload và đọc file PDF, TXT, DOC, DOCX
- 🌐 **Dịch thuật**: Hỗ trợ 8 ngôn ngữ (EN, VI, JA, KO, ZH, FR, DE, ES)
- 📝 **Tóm tắt văn bản**: AI thông minh với MegaLLM API
- 📊 **Quản lý lịch sử**: Lưu trữ và tiếp tục phát từ vị trí đã dừng
- 👥 **Admin Dashboard**: Quản lý users và cấu hình hệ thống

## 📋 Yêu cầu hệ thống

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx với mod_rewrite
- Composer
- Extension: PDO, cURL, mbstring

## 🛠️ Cài đặt

### 1. Clone project

```bash
cd c:\xampp\htdocs
git clone <repository-url> docreader-php
cd docreader-php
```

### 2. Cài đặt dependencies

```bash
composer install
```

### 3. Cấu hình database

```bash
# Tạo database
mysql -u root -p
CREATE DATABASE docreader_ai_studio;
exit

# Import schema
mysql -u root -p docreader_ai_studio < database.sql
```

### 4. Cấu hình environment

```bash
# Copy file .env.example
copy .env.example .env

# Chỉnh sửa .env với thông tin của bạn
notepad .env
```

Cấu hình cần thiết:
```env
DB_HOST=localhost
DB_NAME=docreader_ai_studio
DB_USER=root
DB_PASS=

# Azure Speech Service (Required)
AZURE_SPEECH_KEY=your_azure_speech_key
AZURE_SPEECH_KEY2=your_azure_speech_key2
AZURE_SPEECH_REGION=eastus

# MegaLLM API (Required for translation & summarization)
MEGALLM_API_KEY=your_megallm_api_key
MEGALLM_BASE_URL=https://ai.megallm.io/v1
MEGALLM_MODEL=gpt-5

# Email
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
```

### Lấy API Keys

**Azure Speech Service (Required):**
1. Truy cập: https://portal.azure.com/
2. Tạo resource "Speech Services"
3. Copy Key 1, Key 2 và Region
4. Free tier: 5 triệu ký tự/tháng
5. Recommended regions: eastus, southeastasia, eastasia

**MegaLLM API (Required):**
1. Truy cập: https://ai.megallm.io/
2. Đăng ký tài khoản và lấy API key
3. Hỗ trợ OpenAI-compatible API format

### 5. Set permissions

```bash
# Windows (PowerShell as Admin)
icacls uploads /grant Users:F

# Linux/Mac
chmod 755 uploads/
```

### 6. Cấu hình Apache

Đảm bảo mod_rewrite được enable và DocumentRoot trỏ đến thư mục project.

**httpd.conf hoặc httpd-vhosts.conf:**
```apache
<VirtualHost *:80>
    ServerName docreader.local
    DocumentRoot "c:/xampp/htdocs/docreader-php"
    
    <Directory "c:/xampp/htdocs/docreader-php">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**hosts file (C:\Windows\System32\drivers\etc\hosts):**
```
127.0.0.1 docreader.local
```

### 7. Khởi động server

```bash
# XAMPP
# Start Apache và MySQL từ XAMPP Control Panel

# Hoặc PHP built-in server (development only)
php -S localhost:8000
```

## 🔑 Tài khoản mặc định

**Admin:**
- Email: `admin@docreader.com`
- Password: `admin123`

## 📁 Cấu trúc thư mục

```
docreader-php/
├── api/              # API endpoints
├── assets/           # CSS, JS, images
├── config/           # Configuration files
├── controllers/      # Request handlers
├── includes/         # Shared components
├── middleware/       # Auth & admin checks
├── models/           # Database models
├── services/         # Business logic
├── uploads/          # User uploaded files
├── views/            # Frontend pages
├── .htaccess         # URL rewriting
├── index.php         # Application entry point
└── database.sql      # Database schema
```

## 🔧 API Endpoints

### Authentication
- `POST /api/auth.php?action=login`
- `POST /api/auth.php?action=register`
- `POST /api/auth.php?action=send-otp`
- `POST /api/auth.php?action=verify-otp`
- `POST /api/auth.php?action=reset-password`
- `POST /api/auth.php?action=logout`

### Text-to-Speech
- `POST /api/tts.php?action=convert` - Convert text to speech
  - Body: `{ text, voice, speed, lang }`
- `GET /api/tts.php?action=voices` - Get available voices
- `GET /api/tts.php?action=test` - Test Azure connection

### Document
- `GET /api/document.php?action=history`
- `POST /api/document.php?action=upload`
- `DELETE /api/document.php?action=delete&id=X`
- `PATCH /api/document.php?action=update-position`

### Translation
- `POST /api/translate.php?action=translate`
- `POST /api/translate.php?action=summary`
- `POST /api/translate.php?action=detect`

### Admin
- `GET /api/admin.php?action=users`
- `POST /api/admin.php?action=update-role`
- `DELETE /api/admin.php?action=delete-user`
- `GET /api/admin.php?action=stats`
- `POST /api/admin.php?action=update-config`

## 🧪 Testing

### Test Azure Speech Service

```bash
php test-azure-tts.php
```

### Test Web Application

1. Truy cập: `http://docreader.local` hoặc `http://localhost:8000`
2. Đăng ký tài khoản mới hoặc login với admin
3. Test các tính năng:
   - TTS conversion với Azure Speech
   - File upload
   - Translation
   - History management
   - Admin functions

## 🔒 Security Features

- ✅ Password hashing với bcrypt
- ✅ PDO prepared statements (SQL injection prevention)
- ✅ XSS protection với htmlspecialchars()
- ✅ CSRF protection
- ✅ File upload validation
- ✅ Session security
- ✅ OTP email verification

## 📝 License

MIT License

## 👨‍💻 Author

DocReader AI Studio Team

## 🐛 Troubleshooting

### Lỗi database connection
- Kiểm tra MySQL đã chạy
- Kiểm tra thông tin trong .env
- Kiểm tra user có quyền truy cập database

### Lỗi 404 Not Found
- Kiểm tra mod_rewrite đã enable
- Kiểm tra .htaccess file tồn tại
- Kiểm tra AllowOverride All trong Apache config

### Lỗi upload file
- Kiểm tra permissions của thư mục uploads/
- Kiểm tra upload_max_filesize trong php.ini
- Kiểm tra post_max_size trong php.ini

### Lỗi composer install
- Cài đặt Composer: https://getcomposer.org/
- Chạy: `composer update`
- Kiểm tra PHP version >= 7.4

## 📞 Support

Email: support@docreader.com
