<?php
/**
 * Admin API
 * Handles admin operations: user management, system config, statistics
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Data.php';
require_once __DIR__ . '/../models/SystemConfig.php';
require_once __DIR__ . '/../includes/functions.php';

// Check admin authorization
requireAdmin();

// Get database connection
$db = Database::getInstance()->getConnection();
$userModel = new User($db);
$dataModel = new Data($db);
$configModel = new SystemConfig($db);

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'users':
            handleGetUsers($userModel);
            break;
            
        case 'update-role':
            handleUpdateRole($userModel, $input);
            break;
            
        case 'delete-user':
            handleDeleteUser($userModel, $input);
            break;
            
        case 'toggle-status':
            handleToggleStatus($userModel, $input);
            break;
            
        case 'stats':
            handleGetStats($userModel, $dataModel);
            break;
            
        case 'update-config':
            handleUpdateConfig($configModel, $input);
            break;
            
        case 'get-configs':
            handleGetConfigs($configModel);
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
    error_log("Admin API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'code' => 'SERVER_ERROR'
    ]);
}

/**
 * Handle get all users
 */
function handleGetUsers($userModel) {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 20;
    $search = $_GET['search'] ?? '';
    
    $result = $userModel->getAllUsers((int)$page, (int)$limit, $search);
    
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
}

/**
 * Handle update user role
 */
function handleUpdateRole($userModel, $input) {
    // Validate input
    if (!isset($input['userId']) || !isset($input['role'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Thiếu thông tin bắt buộc',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $userId = (int)$input['userId'];
    $role = $input['role'];
    
    // Validate role
    if (!in_array($role, ['user', 'admin'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Role không hợp lệ',
            'code' => 'INVALID_ROLE'
        ]);
        return;
    }
    
    // Prevent self-demotion
    if ($userId === $_SESSION['user_id'] && $role === 'user') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể tự hạ quyền của chính mình',
            'code' => 'SELF_DEMOTION'
        ]);
        return;
    }
    
    // Update role
    if (!$userModel->updateUserRole($userId, $role)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể cập nhật role',
            'code' => 'UPDATE_FAILED'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã cập nhật role thành công'
    ]);
}

/**
 * Handle delete user
 */
function handleDeleteUser($userModel, $input) {
    // Validate input
    if (!isset($input['userId'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'User ID không hợp lệ',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $userId = (int)$input['userId'];
    
    // Prevent self-deletion
    if ($userId === $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể xóa tài khoản của chính mình',
            'code' => 'SELF_DELETION'
        ]);
        return;
    }
    
    // Delete user
    if (!$userModel->deleteUser($userId)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể xóa user',
            'code' => 'DELETE_FAILED'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã xóa user thành công'
    ]);
}

/**
 * Handle get statistics
 */
function handleGetStats($userModel, $dataModel) {
    try {
        // Get total users
        $allUsers = $userModel->getAllUsers(1, 999999);
        $totalUsers = $allUsers['total'];
        
        // Get active users
        $activeUsers = 0;
        foreach ($allUsers['users'] as $user) {
            if ($user['status'] === 'active') {
                $activeUsers++;
            }
        }
        
        // Get total audio conversions
        $totalConversions = $dataModel->getTotalAudioCount();
        
        // Get user growth data (last 7 days)
        $dates = [];
        $userCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = date('d/m', strtotime($date));
            
            // Count users created on or before this date
            $count = 0;
            foreach ($allUsers['users'] as $user) {
                if (strtotime($user['created_at']) <= strtotime($date . ' 23:59:59')) {
                    $count++;
                }
            }
            $userCounts[] = $count;
        }
        
        // Get conversion trends (mock data for now)
        $conversionCounts = array_fill(0, 7, 0);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'total_conversions' => $totalConversions,
                'dates' => $dates,
                'userCounts' => $userCounts,
                'conversionCounts' => $conversionCounts
            ]
        ]);
    } catch (Exception $e) {
        error_log("Error in handleGetStats: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể lấy thống kê',
            'code' => 'STATS_FAILED'
        ]);
    }
}

/**
 * Handle update system config
 */
function handleUpdateConfig($configModel, $input) {
    // Validate input
    if (!isset($input['key']) || !isset($input['value'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Thiếu thông tin bắt buộc',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $key = $input['key'];
    $value = $input['value'];
    
    // Update config
    if (!$configModel->updateConfig($key, $value)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể cập nhật config',
            'code' => 'UPDATE_FAILED'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã cập nhật config thành công'
    ]);
}

/**
 * Handle get all configs
 */
function handleGetConfigs($configModel) {
    $includePrivate = true; // Admin can see all configs
    $configs = $configModel->getConfigsByCategory($includePrivate);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'configs' => $configs
        ]
    ]);
}

/**
 * Handle toggle user status (active/inactive)
 */
function handleToggleStatus($userModel, $input) {
    // Validate input
    if (!isset($input['userId'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'User ID không hợp lệ',
            'code' => 'VALIDATION_ERROR'
        ]);
        return;
    }
    
    $userId = (int)$input['userId'];
    
    // Prevent self-locking
    if ($userId === $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể khóa tài khoản của chính mình',
            'code' => 'SELF_LOCK'
        ]);
        return;
    }
    
    // Get current user status
    $user = $userModel->getUserById($userId);
    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'User không tồn tại',
            'code' => 'USER_NOT_FOUND'
        ]);
        return;
    }
    
    // Toggle status
    $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
    
    if (!$userModel->updateStatus($user['email'], $newStatus)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể cập nhật trạng thái',
            'code' => 'UPDATE_FAILED'
        ]);
        return;
    }
    
    $message = $newStatus === 'inactive' ? 'Đã khóa tài khoản' : 'Đã mở khóa tài khoản';
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'newStatus' => $newStatus
        ]
    ]);
}
