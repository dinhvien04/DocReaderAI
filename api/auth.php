<?php
/**
 * Authentication API
 * Handles login, register, logout, OTP operations
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../includes/functions.php';

// Get database connection
$db = Database::getInstance()->getConnection();
$userModel = new User($db);
$emailService = new EmailService();

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'login':
            handleLogin($userModel, $input);
            break;
            
        case 'register':
            handleRegister($userModel, $emailService, $input);
            break;
            
        case 'verify-otp':
            handleVerifyOtp($userModel, $input);
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        case 'reset-password':
            handleResetPassword($userModel, $emailService, $input);
            break;
            
        case 'change-password':
            handleChangePassword($userModel, $input);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action',
                'code' => 'INVALID_ACTION'
            ]);
    }
} catch (Exception $e) {
    error_log("Auth API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'code' => 'SERVER_ERROR'
    ]);
}

/**
 * Handle login - Support username or email
 */
function handleLogin($userModel, $input) {
    // Validate input
    if (empty($input['identifier']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Username/Email và mật khẩu không được để trống',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $identifier = sanitize($input['identifier']); // Can be username or email
    $password = $input['password'];
    
    // Get user by username or email
    $user = $userModel->getUserByIdentifier($identifier);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Username/Email hoặc mật khẩu không đúng',
            'code' => 'INVALID_CREDENTIALS'
        ]);
        return;
    }
    
    // Check if account is active
    if ($user['status'] !== 'active') {
        // Check if account has OTP (means it's pending activation)
        $isPendingActivation = !empty($user['otp']);
        
        $errorMessage = $isPendingActivation 
            ? 'Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email để xác thực OTP.'
            : 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.';
        
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => $errorMessage,
            'code' => $isPendingActivation ? 'PENDING_ACTIVATION' : 'ACCOUNT_LOCKED'
        ]);
        return;
    }
    
    // Verify password
    if (!$userModel->verifyPassword($identifier, $password)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Username/Email hoặc mật khẩu không đúng',
            'code' => 'INVALID_CREDENTIALS'
        ]);
        return;
    }
    
    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role']
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'data' => [
            'user' => $_SESSION['user']
        ]
    ]);
}

/**
 * Handle register - New flow: collect all info first, then send OTP
 */
function handleRegister($userModel, $emailService, $input) {
    // Validate input
    if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Username, email và mật khẩu không được để trống',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $username = sanitize($input['username']);
    $email = sanitize($input['email']);
    $password = $input['password'];
    
    // Validate username format (alphanumeric, underscore, 3-20 chars)
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Username phải từ 3-20 ký tự, chỉ chứa chữ, số và dấu gạch dưới',
            'code' => 'INVALID_USERNAME'
        ]);
        return;
    }
    
    // Check if username already exists
    $existingUser = $userModel->getUserByUsername($username);
    if ($existingUser) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Username đã được sử dụng',
            'code' => 'USERNAME_EXISTS'
        ]);
        return;
    }
    
    // Check if email already exists
    $existingEmail = $userModel->getUserByEmail($email);
    if ($existingEmail) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Email đã được sử dụng',
            'code' => 'EMAIL_EXISTS'
        ]);
        return;
    }
    
    // Generate OTP
    $otp = generateOtp();
    
    // Create user with all info (status = inactive)
    $userId = $userModel->createUser($username, $email, $password, $otp);
    
    if (!$userId) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể tạo tài khoản',
            'code' => 'CREATE_USER_FAILED'
        ]);
        return;
    }
    
    // Send OTP email
    if (!$emailService->sendOtpEmail($email, $otp, 'registration')) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể gửi email xác nhận',
            'code' => 'EMAIL_SEND_FAILED'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'OTP đã được gửi đến email của bạn',
        'data' => [
            'email' => $email
        ]
    ]);
}

/**
 * Handle verify OTP and activate account
 */
function handleVerifyOtp($userModel, $input) {
    // Validate input
    if (empty($input['email']) || empty($input['otp'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Email và OTP không được để trống',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $email = sanitize($input['email']);
    $otp = $input['otp'];
    
    // Verify OTP
    if (!$userModel->verifyOtp($email, $otp)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'OTP không đúng hoặc đã hết hạn',
            'code' => 'INVALID_OTP'
        ]);
        return;
    }
    
    // Activate account
    if (!$userModel->updateStatus($email, 'active')) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể kích hoạt tài khoản',
            'code' => 'ACTIVATION_FAILED'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Tài khoản đã được kích hoạt thành công'
    ]);
}

/**
 * Handle logout
 */
function handleLogout() {
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Đăng xuất thành công'
    ]);
}

/**
 * Handle change password (for logged-in users)
 */
function handleChangePassword($userModel, $input) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Vui lòng đăng nhập',
            'code' => 'UNAUTHORIZED'
        ]);
        return;
    }
    
    // Validate input
    if (empty($input['current_password']) || empty($input['new_password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Mật khẩu hiện tại và mật khẩu mới không được để trống',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $userId = $_SESSION['user_id'];
    $currentPassword = $input['current_password'];
    $newPassword = $input['new_password'];
    
    // Get user info
    $user = $userModel->getUserById($userId);
    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Người dùng không tồn tại',
            'code' => 'USER_NOT_FOUND'
        ]);
        return;
    }
    
    // Verify current password
    if (!$userModel->verifyPassword($user['username'], $currentPassword)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Mật khẩu hiện tại không đúng',
            'code' => 'INVALID_PASSWORD'
        ]);
        return;
    }
    
    // Validate new password strength
    if (strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Mật khẩu mới phải có ít nhất 6 ký tự',
            'code' => 'WEAK_PASSWORD'
        ]);
        return;
    }
    
    // Update password
    if (!$userModel->updatePasswordById($userId, $newPassword)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể cập nhật mật khẩu',
            'code' => 'UPDATE_PASSWORD_FAILED'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Mật khẩu đã được thay đổi thành công'
    ]);
}

/**
 * Handle reset password
 */
function handleResetPassword($userModel, $emailService, $input) {
    $step = $input['step'] ?? 'send-otp';
    
    if ($step === 'send-otp') {
        // Step 1: Send OTP to email
        if (empty($input['email'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Email không được để trống',
                'code' => 'VALIDATION_ERROR'
            ]);
            return;
        }
        
        $email = sanitize($input['email']);
        
        // Check if user exists
        $user = $userModel->getUserByEmail($email);
        if (!$user) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Email không tồn tại',
                'code' => 'EMAIL_NOT_FOUND'
            ]);
            return;
        }
        
        // Generate and update OTP
        $otp = generateOtp();
        if (!$userModel->updateOtp($email, $otp)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Không thể gửi OTP',
                'code' => 'UPDATE_OTP_FAILED'
            ]);
            return;
        }
        
        // Send OTP email
        if (!$emailService->sendOtpEmail($email, $otp, 'reset')) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Không thể gửi email',
                'code' => 'EMAIL_SEND_FAILED'
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'OTP đã được gửi đến email của bạn'
        ]);
        
    } else if ($step === 'reset') {
        // Step 2: Verify OTP and reset password
        if (empty($input['email']) || empty($input['otp']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Thiếu thông tin bắt buộc',
                'code' => 'VALIDATION_ERROR'
            ]);
            return;
        }
        
        $email = sanitize($input['email']);
        $otp = $input['otp'];
        $password = $input['password'];
        
        // Verify OTP
        if (!$userModel->verifyOtp($email, $otp)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'OTP không đúng hoặc đã hết hạn',
                'code' => 'INVALID_OTP'
            ]);
            return;
        }
        
        // Update password
        if (!$userModel->updatePassword($email, $password)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Không thể cập nhật mật khẩu',
                'code' => 'UPDATE_PASSWORD_FAILED'
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Mật khẩu đã được cập nhật thành công'
        ]);
    }
}
