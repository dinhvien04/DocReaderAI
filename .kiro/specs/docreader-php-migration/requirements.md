# Requirements Document

## Introduction

DocReader AI Studio là một hệ thống web application cho phép người dùng chuyển đổi văn bản thành giọng nói (Text-to-Speech) sử dụng FPT AI API, quản lý tài liệu, dịch thuật và tóm tắt văn bản. Dự án này là việc migration từ Node.js/React sang PHP full stack với MySQL database, giữ nguyên toàn bộ tính năng và giao diện người dùng.

## Glossary

- **System**: DocReader AI Studio PHP Application
- **User**: Người dùng đã đăng ký tài khoản
- **Admin**: Người dùng có quyền quản trị hệ thống
- **Guest**: Người dùng chưa đăng nhập
- **TTS**: Text-to-Speech service sử dụng FPT AI API
- **Audio History**: Lịch sử các file audio đã tạo của người dùng
- **OTP**: One-Time Password dùng cho xác thực email
- **Session**: Phiên đăng nhập của người dùng
- **Upload Directory**: Thư mục lưu trữ files được upload
- **System Config**: Cấu hình hệ thống lưu trong database

## Requirements

### Requirement 1: User Authentication

**User Story:** As a Guest, I want to register an account with email verification, so that I can access the system features

#### Acceptance Criteria

1. WHEN a Guest submits registration form with valid email, THE System SHALL send an OTP code to the provided email within 30 seconds
2. WHEN a Guest enters correct OTP code, THE System SHALL allow password creation and avatar selection
3. WHEN a Guest completes registration with valid password and avatar, THE System SHALL create an inactive user account in the database
4. WHEN a Guest verifies email successfully, THE System SHALL activate the user account and redirect to dashboard
5. IF registration email already exists in database, THEN THE System SHALL display error message "Email đã được sử dụng"

### Requirement 2: User Login and Session Management

**User Story:** As a User, I want to login with email and password, so that I can access my dashboard and saved data

#### Acceptance Criteria

1. WHEN a User submits valid email and password, THE System SHALL create a session and redirect to dashboard within 2 seconds
2. WHEN a User submits invalid credentials, THE System SHALL display error message "Email hoặc mật khẩu không đúng"
3. WHILE a User session is active, THE System SHALL maintain authentication state across page navigation
4. WHEN a User clicks logout, THE System SHALL destroy the session and redirect to home page
5. IF a User account status is inactive, THEN THE System SHALL deny login and display message "Tài khoản chưa được kích hoạt"

### Requirement 3: Password Reset

**User Story:** As a User, I want to reset my password via email, so that I can regain access if I forget my password

#### Acceptance Criteria

1. WHEN a User requests password reset with valid email, THE System SHALL send OTP to the email within 30 seconds
2. WHEN a User enters correct OTP, THE System SHALL allow new password entry
3. WHEN a User submits valid new password, THE System SHALL update password in database using secure hashing
4. IF password reset email does not exist, THEN THE System SHALL display error message "Email không tồn tại"

### Requirement 4: Text-to-Speech Conversion

**User Story:** As a User, I want to convert text to speech with voice selection, so that I can listen to my documents

#### Acceptance Criteria

1. WHEN a User submits text with selected voice and speed, THE System SHALL call FPT AI API and return audio URL within 10 seconds
2. THE System SHALL support 6 Vietnamese voices from FPT AI API
3. WHEN audio conversion succeeds, THE System SHALL save audio URL and metadata to audio_history table
4. WHEN a User plays audio, THE System SHALL track playback position every 5 seconds
5. IF FPT AI API fails, THEN THE System SHALL display error message "Không thể chuyển đổi văn bản"

### Requirement 5: Document Upload and Processing

**User Story:** As a User, I want to upload PDF or TXT files and convert to speech, so that I can listen to document content

#### Acceptance Criteria

1. WHEN a User uploads a file, THE System SHALL validate file type is PDF or TXT with maximum size 10MB
2. WHEN a User uploads valid PDF file, THE System SHALL extract text content using PDF.js library
3. WHEN text extraction succeeds, THE System SHALL display extracted text in textarea for editing
4. WHEN a User converts extracted text, THE System SHALL process it through TTS service
5. IF file upload exceeds size limit, THEN THE System SHALL display error message "File vượt quá 10MB"

### Requirement 6: Audio History Management

**User Story:** As a User, I want to view and manage my audio history, so that I can replay or delete previous conversions

#### Acceptance Criteria

1. WHEN a User opens history tab, THE System SHALL display all audio records ordered by creation date descending
2. WHEN a User clicks play on history item, THE System SHALL resume playback from saved position
3. WHEN a User deletes history item, THE System SHALL remove record from database and confirm deletion
4. THE System SHALL display audio metadata including text preview, voice name, language, and creation date
5. WHILE audio is playing, THE System SHALL update position in database every 5 seconds

### Requirement 7: Text Translation

**User Story:** As a User, I want to translate text between languages, so that I can understand foreign content

#### Acceptance Criteria

1. WHEN a User submits text with target language, THE System SHALL call Google Translate API and return translated text within 5 seconds
2. THE System SHALL support translation to English, Vietnamese, Japanese, Korean, and Chinese
3. WHEN translation succeeds, THE System SHALL display translated text in result area
4. IF Google API fails, THEN THE System SHALL display error message "Không thể dịch văn bản"

### Requirement 8: Text Summarization

**User Story:** As a User, I want to summarize long text, so that I can quickly understand main points

#### Acceptance Criteria

1. WHEN a User submits text for summarization, THE System SHALL call Google AI API with summarization prompt
2. WHEN summarization succeeds, THE System SHALL display summary text within 10 seconds
3. THE System SHALL limit summarization to text with minimum 100 characters
4. IF text is too short, THEN THE System SHALL display message "Văn bản quá ngắn để tóm tắt"

### Requirement 9: Admin User Management

**User Story:** As an Admin, I want to manage user accounts, so that I can control system access

#### Acceptance Criteria

1. WHEN an Admin accesses user management page, THE System SHALL display all users with pagination of 20 records per page
2. WHEN an Admin searches users, THE System SHALL filter results by email or role in real-time
3. WHEN an Admin changes user role, THE System SHALL update role in database and display confirmation
4. WHEN an Admin deletes user, THE System SHALL remove user and all related audio_history records via cascade delete
5. IF non-admin user attempts to access admin pages, THEN THE System SHALL redirect to dashboard with error message

### Requirement 10: Admin System Configuration

**User Story:** As an Admin, I want to configure system settings, so that I can control limits and features

#### Acceptance Criteria

1. WHEN an Admin accesses config page, THE System SHALL display all configuration entries grouped by category
2. WHEN an Admin updates config value, THE System SHALL validate input and save to system_config table
3. THE System SHALL support config categories including limits, api_keys, email_settings, and features
4. WHEN config update succeeds, THE System SHALL display success message and refresh config display
5. WHERE config is marked as public, THE System SHALL expose value to frontend via API

### Requirement 11: Admin Dashboard Statistics

**User Story:** As an Admin, I want to view system statistics, so that I can monitor usage and performance

#### Acceptance Criteria

1. WHEN an Admin opens dashboard, THE System SHALL display total users count, active users count, and total audio conversions
2. THE System SHALL display statistics with data updated within last 5 minutes
3. WHEN an Admin views charts, THE System SHALL render usage trends using Chart.js library
4. THE System SHALL calculate statistics from database queries with optimized indexes

### Requirement 12: Security and Data Protection

**User Story:** As a System Administrator, I want secure data handling, so that user information is protected

#### Acceptance Criteria

1. THE System SHALL hash all passwords using PHP password_hash function with bcrypt algorithm
2. THE System SHALL use PDO prepared statements for all database queries to prevent SQL injection
3. THE System SHALL sanitize all user inputs using htmlspecialchars function to prevent XSS attacks
4. THE System SHALL validate file uploads to allow only PDF and TXT extensions
5. THE System SHALL set upload directory permissions to prevent direct script execution

### Requirement 13: Responsive User Interface

**User Story:** As a User, I want responsive interface on all devices, so that I can use the system on mobile and desktop

#### Acceptance Criteria

1. THE System SHALL render all pages using Tailwind CSS responsive utilities
2. WHEN a User accesses on mobile device with width below 768px, THE System SHALL display mobile-optimized navigation
3. THE System SHALL maintain gradient backgrounds and animations identical to original React version
4. THE System SHALL ensure all forms and buttons are touch-friendly with minimum 44px touch targets
5. WHEN page loads, THE System SHALL display content within 3 seconds on 3G connection

### Requirement 14: Email Notifications

**User Story:** As a User, I want to receive email notifications, so that I can verify my account and reset password

#### Acceptance Criteria

1. WHEN a User registers, THE System SHALL send HTML formatted OTP email using PHPMailer within 30 seconds
2. WHEN a User requests password reset, THE System SHALL send OTP email with 10-minute expiration
3. THE System SHALL use SMTP configuration from environment variables for email delivery
4. THE System SHALL include system logo and branding in all email templates
5. IF email sending fails, THEN THE System SHALL log error and display message "Không thể gửi email"

### Requirement 15: API Error Handling

**User Story:** As a Developer, I want consistent API error responses, so that frontend can handle errors properly

#### Acceptance Criteria

1. WHEN API request fails, THE System SHALL return JSON response with error field and HTTP status code
2. THE System SHALL return 400 status for validation errors with detailed error messages
3. THE System SHALL return 401 status for authentication failures
4. THE System SHALL return 403 status for authorization failures
5. THE System SHALL return 500 status for server errors with generic error message to prevent information disclosure
