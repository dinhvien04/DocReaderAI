# Tài liệu Luồng Chạy - DocReader AI Studio

## Mục lục
1. [Chức năng Đăng nhập/Đăng ký](#1-chức-năng-đăng-nhậpđăng-ký)
2. [Chức năng Text-to-Speech (TTS)](#2-chức-năng-text-to-speech-tts)
3. [Chức năng Tóm tắt văn bản](#3-chức-năng-tóm-tắt-văn-bản)
4. [Chức năng Dịch thuật](#4-chức-năng-dịch-thuật)
5. [Chức năng Lịch sử](#5-chức-năng-lịch-sử)
6. [Chức năng Chia sẻ](#6-chức-năng-chia-sẻ)

---

## 1. Chức năng Đăng nhập/Đăng ký

### 1.1. Đăng nhập

**Luồng từ View đến Endpoint:**

```
views/login.php
    ↓ (User nhập username/email và password)
    ↓ (Submit form)
assets/js/auth.js → login()
    ↓ (POST request)
api/auth.php?action=login
    ↓ (Xử lý)
models/User.php → getUserByIdentifier()
    ↓ (Kiểm tra database)
config/database.php
    ↓ (Trả về kết quả)
Response JSON → {success, data: {user}}
    ↓ (Redirect)
views/dashboard.php (user) hoặc views/admin/index.php (admin)
```

**Chi tiết:**

1. **View Layer** (`views/login.php`):
   - Hiển thị form đăng nhập với 2 trường: identifier (username/email) và password
   - Form có id="login-form"

2. **JavaScript Layer** (`assets/js/auth.js`):
   - Function `handleLoginForm()` lắng nghe sự kiện submit
   - Function `login(identifier, password)` gửi POST request đến API
   - Sử dụng `apiRequest()` từ `app.js` để gọi API

3. **API Endpoint** (`api/auth.php?action=login`):
   - Nhận JSON input: `{identifier, password}`
   - Validate input không rỗng
   - Gọi `getUserByIdentifier()` từ User model
   - Kiểm tra trạng thái tài khoản (active/inactive)
   - Verify password bằng `verifyPassword()`
   - Tạo session với user_id và thông tin user
   - Trả về JSON: `{success: true, data: {user}}`

4. **Model Layer** (`models/User.php`):
   - `getUserByIdentifier($identifier)`: Query database tìm user theo username hoặc email
   - `verifyPassword($identifier, $password)`: So sánh password hash

5. **Response Handling**:
   - Nếu thành công: Lưu session, redirect theo role (admin/user)
   - Nếu thất bại: Hiển thị toast error

---

### 1.2. Đăng ký

**Luồng từ View đến Endpoint:**

```
views/register.php
    ↓ (User nhập username, email, password)
    ↓ (Submit form - Step 1)
assets/js/auth.js → register()
    ↓ (POST request)
api/auth.php?action=register
    ↓ (Tạo user + gửi OTP)
services/EmailService.php → sendOtpEmail()
    ↓ (Email sent)
Response JSON → {success, data: {email}}
    ↓ (Chuyển sang Step 2)
views/register.php (Step 2 - Verify OTP)
    ↓ (User nhập OTP)
assets/js/auth.js → verifyOtp()
    ↓ (POST request)
api/auth.php?action=verify-otp
    ↓ (Kích hoạt tài khoản)
models/User.php → updateStatus('active')
    ↓ (Redirect)
views/login.php
```

**Chi tiết:**

1. **View Layer** (`views/register.php`):
   - Step 1: Form nhập username, email, password, confirm-password
   - Step 2: Form nhập OTP (6 chữ số)

2. **JavaScript Layer** (`assets/js/auth.js`):
   - `handleRegisterForm()`: Xử lý 2 steps
   - Step 1: `register({username, email, password})` → Gửi thông tin đăng ký
   - Step 2: `verifyOtp(email, otp)` → Xác thực OTP

3. **API Endpoint** (`api/auth.php`):
   - **action=register**:
     - Validate username format (3-20 ký tự, chỉ chữ số và _)
     - Kiểm tra username/email đã tồn tại
     - Generate OTP 6 số
     - Tạo user với status='inactive'
     - Gửi email OTP
   - **action=verify-otp**:
     - Verify OTP trong database
     - Cập nhật status='active'
     - Xóa OTP

4. **Email Service** (`services/EmailService.php`):
   - `sendOtpEmail($email, $otp, $type)`: Gửi email chứa mã OTP

---

## 2. Chức năng Text-to-Speech (TTS)

**Luồng từ View đến Endpoint:**

```
views/dashboard.php (Tab TTS)
    ↓ (User nhập text hoặc upload file)
    ↓ (Chọn giọng đọc)
    ↓ (Click "Chuyển đổi")
assets/js/tts-fix.js → TTSButtonController
    ↓ (Kiểm tra debounce)
    ↓ (POST request)
api/tts.php?action=convert
    ↓ (Xác định engine: Azure/Edge-TTS/gTTS)
services/EdgeTTSService.php → textToSpeech()
    ↓ (Gọi Python script)
scripts/edge_tts_convert.py
    ↓ (Tạo file audio)
uploads/audio/edge_tts_*.mp3
    ↓ (Lưu vào database)
models/Data.php → addAudio()
    ↓ (Trả về)
Response JSON → {success, data: {audio_id, audio_url}}
    ↓ (Phát audio)
views/components/audio-player.php
    ↓ (Quản lý position)
assets/js/audio-player-manager.js
```

**Chi tiết:**

1. **View Layer** (`views/dashboard.php`):
   - Tab "Chuyển văn bản thành giọng nói"
   - Textarea nhập text (max 5000 ký tự)
   - Select chọn giọng đọc (Edge-TTS, gTTS, Azure)
   - Button "Chuyển đổi" với id="convert-btn"
   - Audio player để phát âm thanh

2. **JavaScript Layer**:
   - **`assets/js/tts-fix.js`**:
     - Khởi tạo `TTSButtonController` để quản lý button state
     - Xử lý sự kiện click button
     - Debounce 500ms để tránh double-click
   - **`assets/js/TTSButtonController.js`**:
     - Class quản lý trạng thái processing
     - `canProcess()`: Kiểm tra có thể xử lý request không
     - `setProcessing(true/false)`: Cập nhật UI button

3. **API Endpoint** (`api/tts.php?action=convert`):
   - Nhận input: `{text, voice, speed, lang}`
   - Validate độ dài text (max 5000 cho Edge-TTS/gTTS, 10000 cho Azure)
   - Xác định engine dựa vào voice:
     - `gtts-*`: Sử dụng gTTS
     - `*-Azure`: Sử dụng Azure Speech
     - Mặc định: Sử dụng Edge-TTS
   - Gọi service tương ứng
   - Lưu audio vào database
   - Trả về audio_url

4. **Service Layer**:
   - **`services/EdgeTTSService.php`** (Miễn phí, chất lượng cao):
     - `textToSpeech($text, $voice, $speed)`: Gọi Python script
     - Chạy `edge_tts_convert.py` với subprocess
     - Lưu file vào `uploads/audio/`
   - **`services/GTTSService.php`** (Miễn phí, backup):
     - `textToSpeech($text, $voice, $speed)`: Gọi Python script
     - Chạy `gtts_convert.py`
   - **`services/AzureSpeechService.php`** (Premium, trả phí):
     - `textToSpeech($text, $voice, $speed)`: Gọi Azure API
     - Sử dụng Azure Speech SDK

5. **Python Scripts**:
   - **`scripts/edge_tts_convert.py`**:
     - Nhận arguments: text, voice, output_file, rate
     - Sử dụng thư viện `edge-tts`
     - Tạo file MP3
   - **`scripts/gtts_convert.py`**:
     - Sử dụng thư viện `gTTS`
     - Tạo file MP3

6. **Model Layer** (`models/Data.php`):
   - `addAudio($userId, $text, $audioUrl, $voice, $lang)`: Lưu vào bảng `audio_history`

7. **Audio Player**:
   - **`assets/js/audio-player-manager.js`**:
     - Class `AudioPlayerManager` quản lý tất cả audio players
     - Tự động pause audio khác khi phát audio mới
     - Lưu position khi pause/ended
     - Khôi phục position khi load lại

---

## 3. Chức năng Tóm tắt văn bản

**Luồng từ View đến Endpoint:**

```
views/dashboard.php (Tab Summarize)
    ↓ (User nhập text hoặc upload file)
    ↓ (Click "Tóm tắt")
assets/js/dashboard.js → QuickAccessCards.initSummarizeCard()
    ↓ (POST request)
api/summarize.php?action=summarize
    ↓ (Gọi AI service)
services/MegaLLMService.php → summarize()
    ↓ (API call)
External API (MegaLLM)
    ↓ (Lưu vào database)
models/summarize_history
    ↓ (Trả về)
Response JSON → {success, data: {summary}}
    ↓ (Hiển thị kết quả)
views/dashboard.php (Summarize result div)
```

**Chi tiết:**

1. **View Layer** (`views/dashboard.php`):
   - Tab "Tóm tắt nội dung"
   - File upload cho PDF/TXT/DOC/DOCX
   - Textarea nhập text (max 10000 ký tự)
   - Button "Tóm tắt" với id="summarize-btn"
   - Div hiển thị kết quả với id="summarize-result"

2. **JavaScript Layer** (`assets/js/dashboard.js`):
   - Class `QuickAccessCards`:
     - `initSummarizeCard()`: Khởi tạo event listeners
     - Xử lý click button "Tóm tắt"
     - Gửi POST request với `{text}`
   - `handleSummarizeFileUpload()`: Xử lý upload file
     - Đọc file PDF bằng PDF.js
     - Đọc file Word bằng Mammoth.js
     - Trích xuất text và điền vào textarea

3. **API Endpoint** (`api/summarize.php?action=summarize`):
   - Nhận input: `{text}`
   - Validate độ dài text (min 100, max 10000 ký tự)
   - Gọi `MegaLLMService->summarize()`
   - Lưu vào bảng `summarize_history`
   - Trả về summary

4. **Service Layer** (`services/MegaLLMService.php`):
   - `summarize($text, $lang)`: Gọi API MegaLLM
   - Endpoint: `https://api.megalm.com/v1/summarize`
   - Sử dụng API key từ `.env`

5. **Database**:
   - Bảng `summarize_history`:
     - user_id, original_text, summary_text
     - original_length, summary_length
     - created_at

---

## 4. Chức năng Dịch thuật

**Luồng từ View đến Endpoint:**

```
views/dashboard.php (Tab Translate)
    ↓ (User nhập text hoặc upload file)
    ↓ (Chọn ngôn ngữ nguồn và đích)
    ↓ (Click "Dịch")
assets/js/dashboard.php (inline script)
    ↓ (POST request)
api/translate.php?action=translate
    ↓ (Gọi AI service)
services/MegaLLMService.php → translate()
    ↓ (API call)
External API (MegaLLM)
    ↓ (Lưu vào database)
models/translation_history
    ↓ (Trả về)
Response JSON → {success, data: {translated_text}}
    ↓ (Hiển thị kết quả)
views/dashboard.php (Translate result div)
```

**Chi tiết:**

1. **View Layer** (`views/dashboard.php`):
   - Tab "Dịch thuật"
   - File upload cho PDF/TXT/DOC/DOCX
   - Textarea nhập text (max 10000 ký tự)
   - 2 select boxes: source-lang và target-lang
   - Button "Dịch" với id="translate-btn"
   - Div hiển thị kết quả với id="translate-result"

2. **JavaScript Layer**:
   - Inline script trong `dashboard.php`:
     - Xử lý click button "Dịch"
     - Gửi POST request với `{text, targetLang}`
   - `handleTranslateFileUpload()` trong `document.js`:
     - Xử lý upload file tương tự như Summarize

3. **API Endpoint** (`api/translate.php?action=translate`):
   - Nhận input: `{text, targetLang}`
   - Validate độ dài text (max 10000 ký tự)
   - Validate ngôn ngữ hỗ trợ: en, vi, ja, ko, zh, fr, de, es
   - Gọi `MegaLLMService->translate()`
   - Detect source language (đơn giản)
   - Lưu vào bảng `translation_history`
   - Trả về translated_text

4. **Service Layer** (`services/MegaLLMService.php`):
   - `translate($text, $targetLang)`: Gọi API MegaLLM
   - Endpoint: `https://api.megalm.com/v1/translate`

5. **Database**:
   - Bảng `translation_history`:
     - user_id, original_text, translated_text
     - source_lang, target_lang
     - created_at

---

## 5. Chức năng Lịch sử

**Luồng từ View đến Endpoint:**

```
views/dashboard.php (Tab History)
    ↓ (User click tab filter: TTS/Summarize/Translate)
assets/js/dashboard.js → filterHistory()
    ↓ (GET request)
api/history.php?action=list&type=tts&limit=1000
    ↓ (Query database)
models/audio_history / summarize_history / translation_history
    ↓ (Trả về)
Response JSON → {success, data: {items, total, pages}}
    ↓ (Render)
views/dashboard.php (Table view hoặc Card view)
    ↓ (Audio player)
assets/js/audio-player-manager.js
```

**Chi tiết:**

1. **View Layer** (`views/dashboard.php`):
   - Tab "Lịch sử hoạt động"
   - 3 filter tabs: TTS, Tóm tắt, Dịch thuật
   - Table view cho TTS (với audio player)
   - Card view cho Summarize và Translate
   - Pagination

2. **JavaScript Layer** (`assets/js/dashboard.js`):
   - `filterHistory(type)`:
     - Lưu position của tất cả audio trước khi filter
     - Cập nhật UI tabs
     - Gọi API tương ứng
   - Class `RecentActivity`:
     - `loadActivities()`: Load TTS history
     - `renderActivityRow()`: Render từng row trong table
     - `attachEventListeners()`: Đăng ký audio players với AudioPlayerManager

3. **API Endpoint** (`api/history.php?action=list`):
   - Nhận parameters: `type`, `page`, `limit`
   - Query database theo type:
     - `tts`: Bảng `audio_history`
     - `summarize`: Bảng `summarize_history`
     - `translate`: Bảng `translation_history`
   - Trả về items với pagination

4. **Audio Position Tracking**:
   - **`api/update_position.php`**:
     - Nhận: `{id, position}`
     - Cập nhật cột `position` trong `audio_history`
   - **`assets/js/audio-player-manager.js`**:
     - Tự động lưu position mỗi 5 giây
     - Lưu khi pause, ended, hoặc chuyển tab
     - Khôi phục position khi load lại

5. **Delete History**:
   - Button xóa trên mỗi item
   - Gọi `deleteHistoryItem(id, type)`
   - API: `api/history.php?action=delete`
   - Xóa khỏi database và refresh list

---

## 6. Chức năng Chia sẻ

### 6.1. Chia sẻ công khai (Public Share)

**Luồng từ View đến Endpoint:**

```
views/dashboard.php (History table)
    ↓ (User click button "Chia sẻ" trên audio)
    ↓ (Modal hiển thị)
assets/js/dashboard.php (inline script) → openShareModal()
    ↓ (User chọn category, nhập title, description)
    ↓ (Click "Gửi yêu cầu")
    ↓ (POST request)
api/share.php?action=request-public
    ↓ (Lưu vào database với status='pending')
models/shared_audios
    ↓ (Admin duyệt)
views/admin/shares.php
    ↓ (Admin click "Duyệt")
api/share.php?action=approve
    ↓ (Cập nhật status='approved')
    ↓ (Hiển thị trên trang chủ)
views/explore.php
```

**Chi tiết:**

1. **User Request**:
   - Button "📤" trên mỗi audio trong history
   - Modal với form: category, title, description
   - API: `api/share.php?action=request-public`
   - Lưu vào bảng `shared_audios` với status='pending'

2. **Admin Approval**:
   - View: `views/admin/shares.php`
   - List tất cả requests với filter: pending/approved/rejected
   - Button "Duyệt" hoặc "Từ chối"
   - API: `api/share.php?action=approve` hoặc `action=reject`

3. **Public Display**:
   - View: `views/explore.php`
   - Hiển thị tất cả audio đã được duyệt
   - Filter theo category
   - API: `api/share.php?action=get-public`

### 6.2. Chia sẻ bằng Link

**Luồng từ View đến Endpoint:**

```
views/dashboard.php (Tab "Chia sẻ của tôi")
    ↓ (User click "Tạo link chia sẻ")
    ↓ (POST request)
api/share.php?action=create-link
    ↓ (Generate share code)
    ↓ (Lưu vào database)
models/audio_share_links
    ↓ (Trả về share URL)
Response JSON → {success, share_url}
    ↓ (User copy link)
    ↓ (Người khác truy cập link)
views/share.php?code=xxx
    ↓ (GET request)
api/share.php?action=view-link&code=xxx
    ↓ (Tăng view count)
    ↓ (Trả về audio data)
Response JSON → {success, data: {audio_url, text, voice}}
    ↓ (Phát audio)
views/share.php (Audio player)
```

**Chi tiết:**

1. **Create Link**:
   - Tab "Chia sẻ của tôi" trong dashboard
   - Button "Tạo link chia sẻ"
   - API: `api/share.php?action=create-link`
   - Generate random share_code (16 ký tự hex)
   - Lưu vào bảng `audio_share_links`
   - Trả về URL: `BASE_URL/share/{share_code}`

2. **View Shared Link**:
   - URL: `views/share.php?code={share_code}`
   - API: `api/share.php?action=view-link&code={share_code}`
   - Tăng view count
   - Hiển thị audio player với thông tin audio

3. **Manage Links**:
   - Tab "Chia sẻ của tôi"
   - List tất cả links đã tạo
   - Button "Xóa" để deactivate link
   - API: `api/share.php?action=delete-link`

---

## Tổng kết

### Kiến trúc tổng quan:

```
Views (PHP)
    ↓
JavaScript (Event Handlers)
    ↓
API Endpoints (PHP)
    ↓
Services (Business Logic)
    ↓
Models (Database Access)
    ↓
Database (MySQL)
```

### Các thành phần chính:

1. **Frontend**:
   - Views: PHP templates
   - JavaScript: Event handling, API calls, UI updates
   - CSS: Tailwind CSS

2. **Backend**:
   - API: RESTful endpoints
   - Services: Business logic (TTS, AI, Email)
   - Models: Database operations
   - Middleware: Authentication, Authorization

3. **External Services**:
   - Edge-TTS (Python)
   - gTTS (Python)
   - Azure Speech API
   - MegaLLM API

4. **Database**:
   - users
   - audio_history
   - summarize_history
   - translation_history
   - shared_audios
   - audio_share_links
