# Implementation Plan

## Phase 1: Foundation Setup

- [x] 1. Setup project structure and dependencies


  - Create directory structure (config/, models/, controllers/, services/, middleware/, api/, views/, assets/, includes/, uploads/)
  - Create composer.json with required dependencies (firebase/php-jwt, phpmailer/phpmailer, vlucas/phpdotenv)
  - Run `composer install` to install dependencies
  - _Requirements: 12.1, 12.2, 12.3_



- [ ] 2. Create database schema and initial data
  - Write database.sql with CREATE DATABASE statement
  - Create users table with indexes (idx_email, idx_status)
  - Create audio_history table with foreign key and indexes (idx_user_id, idx_created_at)
  - Create system_config table with indexes (idx_config_key, idx_category)
  - Insert default system configs (8 config entries)


  - _Requirements: 10.1, 10.2, 10.3, 10.4_

- [ ] 3. Implement configuration layer
  - Create config/database.php with Database singleton class and PDO connection
  - Create config/config.php to load .env file and define constants (BASE_URL, UPLOAD_DIR, MAX_FILE_SIZE)



  - Create config/.env.example template with all required variables
  - Set timezone to Asia/Ho_Chi_Minh
  - _Requirements: 12.1, 12.2_

- [ ] 4. Create .htaccess for routing and security
  - Write URL rewriting rules for clean URLs
  - Add security headers (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection)


  - Set PHP settings (upload_max_filesize, post_max_size, max_execution_time)
  - Disable script execution in uploads directory
  - Protect .env file from direct access
  - _Requirements: 12.1, 12.4_

## Phase 2: Data Access Layer (Models)

- [ ] 5. Implement User model
  - Create models/User.php class with PDO dependency injection
  - Implement getUserByEmail() method with prepared statement


  - Implement getUserById() method
  - Implement createUser() method with password hashing
  - Implement updatePassword() method with password_hash()
  - Implement updateOtp() and updateStatus() methods
  - Implement verifyPassword() method with password_verify()
  - Implement getAllUsers() with pagination and search
  - Implement updateUserRole() and deleteUser() methods
  - _Requirements: 1.3, 2.1, 2.2, 3.3, 9.1, 9.3, 9.4_




- [ ] 6. Implement Data model for audio history
  - Create models/Data.php class with PDO dependency injection
  - Implement addAudio() method to save audio metadata
  - Implement getAudioByUserId() method with ORDER BY created_at DESC
  - Implement getAudioById() method
  - Implement updatePosition() method for playback tracking


  - Implement deleteAudio() method
  - Implement checkOwnership() method for authorization
  - _Requirements: 4.3, 6.1, 6.2, 6.3, 6.5_

- [ ] 7. Implement SystemConfig model
  - Create models/SystemConfig.php class with PDO dependency injection


  - Implement getConfig() method to retrieve single config value
  - Implement setConfig() method to create new config
  - Implement getAllConfigs() method with optional includePrivate filter
  - Implement updateConfig() method
  - _Requirements: 10.1, 10.2, 10.3, 10.4_



## Phase 3: Business Logic Layer (Services)

- [ ] 8. Implement FPT AI Service for Text-to-Speech
  - Create services/FptAiService.php class with API key from config
  - Implement textToSpeech() method using cURL to call FPT AI API
  - Implement getAvailableVoices() method returning 6 Vietnamese voices
  - Implement validateConnection() method to test API
  - Add error handling for API failures
  - _Requirements: 4.1, 4.2, 4.5_



- [ ] 9. Implement Google API Service
  - Create services/GoogleApiService.php class with API key from config
  - Implement translateText() method using Google Translate API
  - Implement summarizeText() method using Google AI API
  - Implement detectLanguage() method
  - Add error handling for API failures
  - _Requirements: 7.1, 7.4, 8.1, 8.4_



- [ ] 10. Implement Email Service
  - Create services/EmailService.php class with PHPMailer
  - Configure SMTP settings from .env file
  - Implement sendOtpEmail() method with HTML template
  - Implement sendWelcomeEmail() method
  - Create email templates with system branding
  - Add error handling for email sending failures


  - _Requirements: 1.1, 3.1, 14.1, 14.2, 14.5_

## Phase 4: API Endpoints

- [ ] 11. Implement Authentication API
  - Create api/auth.php with action-based routing
  - Implement login action: validate credentials, create session, return user data


  - Implement register action: validate input, create user, send welcome email
  - Implement logout action: destroy session
  - Implement send-otp action: generate OTP, save to database, send email
  - Implement verify-otp action: check OTP validity and expiration
  - Implement reset-password action: validate OTP, update password
  - Return consistent JSON responses with success/error structure
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.1, 2.2, 2.4, 3.1, 3.2, 3.3_



- [ ] 12. Implement Text-to-Speech API
  - Create api/tts.php with action-based routing
  - Implement convert action: validate text length, call FptAiService, save to audio_history
  - Implement voices action: return available voices from FptAiService
  - Implement test action: validate FPT AI connection
  - Add authentication check for all actions
  - Return JSON responses with audio URL and metadata
  - _Requirements: 4.1, 4.2, 4.3, 4.5_



- [ ] 13. Implement Document API
  - Create api/document.php with action-based routing
  - Implement history action: get user's audio history with pagination
  - Implement upload action: validate file type/size, save to uploads/, return file path
  - Implement delete action: check ownership, delete from database and filesystem

  - Implement update-position action: save playback position
  - Add authentication check for all actions
  - _Requirements: 5.1, 5.2, 5.3, 5.5, 6.1, 6.2, 6.3, 6.5_

- [ ] 14. Implement Translation API
  - Create api/translate.php with action-based routing
  - Implement translate action: validate input, call GoogleApiService
  - Implement summary action: validate text length, call summarizeText()
  - Implement detect action: detect language using GoogleApiService



  - Add authentication check for all actions
  - Return JSON responses with translated/summarized text
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 8.1, 8.2, 8.3, 8.4_

- [ ] 15. Implement Admin API
  - Create api/admin.php with action-based routing
  - Implement users action: get all users with pagination and search
  - Implement update-role action: validate role, update user role


  - Implement delete-user action: delete user and cascade audio_history
  - Implement stats action: calculate total users, active users, total conversions
  - Implement update-config action: validate and update system config
  - Add admin authorization check for all actions
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 10.1, 10.2, 10.3, 10.4, 11.1, 11.2, 11.3, 11.4_



## Phase 5: Middleware and Shared Components

- [ ] 16. Implement authentication and authorization middleware
  - Create middleware/auth.php to check session and set $currentUser variable
  - Redirect to login page if not authenticated
  - Create middleware/admin.php to check admin role
  - Redirect to dashboard if not admin


  - _Requirements: 2.3, 9.5_

- [ ] 17. Create shared helper functions
  - Create includes/functions.php with helper functions
  - Implement isLoggedIn() to check session status
  - Implement isAdmin() to check admin role
  - Implement redirect() helper


  - Implement sanitize() for input sanitization with htmlspecialchars()
  - Implement generateOtp() to generate 6-digit OTP
  - Implement formatDate() for datetime formatting
  - _Requirements: 12.2, 12.3, 12.4_

- [ ] 18. Create header and footer components
  - Create includes/header.php with HTML5 doctype, meta tags, Tailwind CSS CDN



  - Add navigation bar with logo and user menu
  - Add mobile responsive menu
  - Create includes/footer.php with footer content and copyright
  - Include JavaScript libraries (Toastify, PDF.js, Chart.js)
  - Include application scripts (app.js, auth.js, tts.js, document.js, admin.js)
  - _Requirements: 13.1, 13.2, 13.3, 13.4_

## Phase 6: Frontend JavaScript

- [x] 19. Implement core JavaScript utilities


  - Create assets/js/app.js with global configuration
  - Implement showToast() function using Toastify
  - Implement apiRequest() wrapper with error handling
  - Implement setLoading() for button loading states
  - _Requirements: 15.1, 15.2_

- [x] 20. Implement authentication JavaScript


  - Create assets/js/auth.js with authentication functions
  - Implement login() function with AJAX call to auth API
  - Implement register() function with multi-step form handling
  - Implement sendOtp() function
  - Implement verifyOtp() function
  - Implement logout() function
  - Implement storeSession() and getSession() for session management
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.4, 3.1, 3.2_



- [ ] 21. Implement Text-to-Speech JavaScript
  - Create assets/js/tts.js with TTS functions
  - Implement convertTextToSpeech() function calling tts API
  - Implement playAudio() function with HTML5 Audio API
  - Implement pauseAudio() function
  - Implement updatePosition() function with 5-second interval tracking
  - Implement handleVoiceSelection() to populate voice dropdown with 6 voices


  - _Requirements: 4.1, 4.2, 4.4, 6.5_

- [ ] 22. Implement document handling JavaScript
  - Create assets/js/document.js with document functions
  - Implement uploadDocument() function with FormData
  - Implement extractTextFromPDF() using PDF.js library
  - Implement getHistory() function to load audio history
  - Implement deleteAudio() function with confirmation dialog


  - Implement renderHistoryList() to display audio items with play/delete buttons
  - _Requirements: 5.1, 5.2, 5.3, 6.1, 6.2, 6.3_

- [ ] 23. Implement admin JavaScript
  - Create assets/js/admin.js with admin functions
  - Implement getUsers() function with pagination and search
  - Implement updateUserRole() function



  - Implement deleteUser() function with confirmation
  - Implement getStats() function to load statistics
  - Implement updateConfig() function
  - Implement renderUserTable() to display user list
  - Implement renderCharts() using Chart.js for statistics visualization

  - _Requirements: 9.1, 9.2, 9.3, 9.4, 10.2, 10.4, 11.1, 11.2, 11.3_

## Phase 7: Public Views

- [ ] 24. Create home page
  - Create views/index.php with hero section and gradient background
  - Add features showcase section (TTS, Translation, Document processing)
  - Add statistics section
  - Add navigation with Login/Register buttons

  - Include header and footer components
  - Use Tailwind CSS for styling matching original design
  - _Requirements: 13.1, 13.2, 13.3, 13.4_

- [ ] 25. Create login page
  - Create views/login.php with login form
  - Add email and password input fields
  - Add remember me checkbox

  - Add links to register and reset password pages
  - Implement AJAX form submission using auth.js
  - Add error message display area
  - Style with Tailwind CSS matching original Login.jsx
  - _Requirements: 2.1, 2.2, 2.4_

- [ ] 26. Create registration page
  - Create views/register.php with multi-step form (3 steps)

  - Step 1: Email input and Send OTP button
  - Step 2: OTP verification with 6-digit input
  - Step 3: Password input and avatar selection (9 avatars)
  - Add progress indicator
  - Implement AJAX form submission using auth.js
  - Style with Tailwind CSS matching original Register.jsx
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [ ] 27. Create password reset page
  - Create views/reset-password.php with multi-step form (3 steps)
  - Step 1: Email input


  - Step 2: OTP verification
  - Step 3: New password input with confirmation
  - Add progress indicator
  - Implement AJAX form submission using auth.js
  - Style with Tailwind CSS matching original ResetPass.jsx
  - _Requirements: 3.1, 3.2, 3.3, 3.4_

- [x] 28. Create 404 error page

  - Create views/404.php with 404 error message
  - Add animated illustration or robot.gif
  - Add "Back to home" button
  - Style with Tailwind CSS matching original NotFound.jsx
  - _Requirements: 15.4_

## Phase 8: Protected Views (User Dashboard)

- [ ] 29. Create dashboard page with tab navigation
  - Create views/dashboard.php with authentication check
  - Implement tab navigation (TTS, Upload Document, Translate, History)
  - Add tab switching functionality with JavaScript

  - Include header and footer components
  - Style with Tailwind CSS matching original Dashboard.jsx
  - _Requirements: 4.1, 5.1, 6.1, 7.1, 8.1_

- [ ] 30. Implement TTS tab in dashboard
  - Add textarea for text input with 5000 character limit
  - Add voice selector dropdown with 6 FPT AI voices
  - Add speed control slider (0.5x - 2.0x)
  - Add Convert button with loading state
  - Add HTML5 audio player with controls
  - Add download button for audio file
  - Wire up with tts.js functions
  - _Requirements: 4.1, 4.2, 4.4_



- [ ] 31. Implement Upload Document tab in dashboard
  - Add file input accepting PDF and TXT files
  - Add drag & drop zone for file upload
  - Add text preview area (editable textarea)
  - Add Convert to speech button
  - Add progress indicator for file processing
  - Wire up with document.js functions for PDF extraction

  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 32. Implement Translate tab in dashboard
  - Add source text textarea
  - Add target language selector (EN, VI, JA, KO, ZH)
  - Add Translate button
  - Add result display area
  - Add Copy to clipboard button

  - Wire up with translate API calls
  - _Requirements: 7.1, 7.2, 7.3_

- [ ] 33. Implement History tab in dashboard
  - Add audio list container with pagination
  - Display each item with text preview, voice, date, duration
  - Add Play/Pause button for each item
  - Add Resume from position functionality
  - Add Delete button with confirmation

  - Add position tracking indicator
  - Wire up with document.js functions
  - _Requirements: 6.1, 6.2, 6.3, 6.5_

## Phase 9: Admin Views

- [ ] 34. Create admin dashboard page
  - Create views/admin/index.php with admin authorization check
  - Add statistics cards (total users, active users, total conversions, storage used)
  - Add Chart.js charts (user growth line chart, conversion trends bar chart, voice usage pie chart)
  - Add quick actions section (View all users, System config, Export data)
  - Add recent activity log
  - Wire up with admin.js getStats() function
  - Style with Tailwind CSS matching original AdminDashboard.jsx
  - _Requirements: 11.1, 11.2, 11.3, 11.4_

- [ ] 35. Create user management page
  - Create views/admin/users.php with admin authorization check
  - Add search bar for email search
  - Add filter dropdowns (role: All/User/Admin, status: All/Active/Inactive)
  - Add user table with columns (ID, Email, Role, Status, Created date, Actions)
  - Add role toggle switch for each user
  - Add Edit and Delete buttons
  - Add pagination (20 users per page)
  - Add bulk actions (Delete selected)
  - Wire up with admin.js functions
  - Style with Tailwind CSS matching original UserManagement.jsx
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [ ] 36. Create system configuration page
  - Create views/admin/config.php with admin authorization check
  - Group configs by category (Limits, Security, Features, API Keys)
  - Display config list with inline editing input fields
  - Add Save button for each config
  - Add Reset to default button
  - Display config descriptions
  - Add validation messages
  - Mask API keys for security
  - Wire up with admin.js updateConfig() function
  - Style with Tailwind CSS matching original SystemConfig.jsx
  - _Requirements: 10.1, 10.2, 10.3, 10.4_

## Phase 10: Routing and Integration

- [ ] 37. Implement main application router
  - Create index.php as entry point with session_start()
  - Require config files and helper functions
  - Implement switch-case routing based on ?page parameter
  - Add routes for all pages (home, login, register, dashboard, admin pages, reset-password)
  - Apply middleware for protected routes
  - Set 404 response for invalid routes
  - _Requirements: 2.3, 9.5, 15.4_

- [ ] 38. Create CSS styles or configure Tailwind
  - Create assets/css/style.css for custom styles
  - Add gradient backgrounds matching original design
  - Add card, button, and form styles
  - Add animations and transitions
  - Ensure responsive design for mobile devices
  - Or use Tailwind CSS CDN with custom configuration
  - _Requirements: 13.1, 13.2, 13.3, 13.4_

- [ ] 39. Setup assets and images
  - Create assets/images/ directory
  - Add logo.webp file
  - Add robot.gif for animations
  - Create avatars/ subdirectory with 9 avatar images
  - Ensure all images are optimized for web
  - _Requirements: 13.1_

## Phase 11: Testing and Security

- [x] 40. Implement security measures

  - Verify all database queries use PDO prepared statements
  - Verify all output uses htmlspecialchars() for XSS prevention
  - Verify password hashing uses password_hash() with bcrypt
  - Verify file upload validation (type, size, MIME type)
  - Verify session security settings (httponly, secure flags)
  - Test SQL injection attempts on all forms
  - Test XSS attempts on all input fields
  - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

- [ ]* 41. Perform integration testing
  - Test complete registration flow (email → OTP → password → avatar)
  - Test login with valid and invalid credentials
  - Test password reset flow (email → OTP → new password)
  - Test TTS conversion with all 6 voices
  - Test file upload with PDF and TXT files
  - Test audio playback and position tracking
  - Test translation feature with multiple languages
  - Test summarization feature
  - Test history management (play, delete)
  - Test admin user management (search, role change, delete)
  - Test admin system config updates
  - Test mobile responsiveness on different screen sizes
  - Test cross-browser compatibility (Chrome, Firefox, Safari, Edge)
  - _Requirements: All requirements_

- [ ]* 42. Create deployment documentation
  - Document server requirements (PHP 7.4+, MySQL 5.7+, Apache/Nginx)
  - Document installation steps (composer install, database import, .env configuration)
  - Document folder permissions setup (uploads/ directory)
  - Document web server configuration (mod_rewrite, virtual host)
  - Document production optimizations (OPcache, query cache, CDN)
  - Create troubleshooting guide for common issues
  - _Requirements: Deployment considerations_
