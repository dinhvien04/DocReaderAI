<?php
/**
 * Audio Sharing API
 * Handles public sharing requests and link sharing
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        // User actions
        case 'request-public':
            requireAuth();
            handleRequestPublic($db, $input);
            break;
            
        case 'create-link':
            requireAuth();
            handleCreateLink($db, $input);
            break;
            
        case 'my-shares':
            requireAuth();
            handleMyShares($db);
            break;
            
        case 'delete-link':
            requireAuth();
            handleDeleteLink($db, $input);
            break;
            
        case 'cancel-request':
            requireAuth();
            handleCancelRequest($db, $input);
            break;
            
        // Public actions (no auth required)
        case 'get-public':
            handleGetPublic($db);
            break;
            
        case 'view-link':
            handleViewLink($db);
            break;
            
        // Admin actions
        case 'admin-list':
            requireAdmin();
            handleAdminList($db);
            break;
            
        case 'approve':
            requireAdmin();
            handleApprove($db, $input);
            break;
            
        case 'reject':
            requireAdmin();
            handleReject($db, $input);
            break;
            
        case 'categories':
            handleGetCategories($db);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Share API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

/**
 * Request public sharing (needs admin approval)
 */
function handleRequestPublic($db, $input) {
    $userId = $_SESSION['user_id'];
    $audioId = (int)($input['audio_id'] ?? 0);
    $categoryId = (int)($input['category_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    
    if (!$audioId || !$categoryId || !$title) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Thiếu thông tin bắt buộc']);
        return;
    }
    
    // Verify audio belongs to user
    $stmt = $db->prepare("SELECT id FROM audio_history WHERE id = ? AND user_id = ?");
    $stmt->execute([$audioId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Không có quyền chia sẻ audio này']);
        return;
    }
    
    // Check if already requested
    $stmt = $db->prepare("SELECT id, status FROM shared_audios WHERE audio_id = ? AND user_id = ?");
    $stmt->execute([$audioId, $userId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['status'] === 'pending') {
            echo json_encode(['success' => false, 'error' => 'Yêu cầu đang chờ duyệt']);
            return;
        }
        if ($existing['status'] === 'approved') {
            echo json_encode(['success' => false, 'error' => 'Audio đã được chia sẻ công khai']);
            return;
        }
        // If rejected, allow re-request by updating
        $stmt = $db->prepare("UPDATE shared_audios SET category_id = ?, title = ?, description = ?, status = 'pending', admin_note = NULL, created_at = NOW() WHERE id = ?");
        $stmt->execute([$categoryId, $title, $description, $existing['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO shared_audios (audio_id, user_id, category_id, title, description, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$audioId, $userId, $categoryId, $title, $description]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Đã gửi yêu cầu chia sẻ công khai']);
}

/**
 * Create shareable link
 */
function handleCreateLink($db, $input) {
    try {
        $userId = $_SESSION['user_id'];
        $audioId = (int)($input['audio_id'] ?? 0);
        $title = trim($input['title'] ?? '');
        
        if (!$audioId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Audio ID không hợp lệ']);
            return;
        }
        
        // Verify audio belongs to user
        $stmt = $db->prepare("SELECT id, text FROM audio_history WHERE id = ? AND user_id = ?");
        $stmt->execute([$audioId, $userId]);
        $audio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$audio) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Không có quyền chia sẻ audio này']);
            return;
        }
        
        // Check if table exists, create if not
        try {
            $db->query("SELECT 1 FROM audio_share_links LIMIT 1");
        } catch (PDOException $e) {
            // Table doesn't exist, create it
            $db->exec("
                CREATE TABLE IF NOT EXISTS `audio_share_links` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `audio_id` int(11) NOT NULL,
                    `user_id` int(11) NOT NULL,
                    `share_code` varchar(32) NOT NULL,
                    `title` varchar(255) DEFAULT NULL,
                    `is_active` tinyint(1) DEFAULT 1,
                    `views` int(11) DEFAULT 0,
                    `expires_at` timestamp NULL DEFAULT NULL,
                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `share_code` (`share_code`),
                    KEY `audio_id` (`audio_id`),
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
        
        // Check if link already exists
        $stmt = $db->prepare("SELECT share_code FROM audio_share_links WHERE audio_id = ? AND user_id = ? AND is_active = 1");
        $stmt->execute([$audioId, $userId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            $shareUrl = BASE_URL . '/share/' . $existing['share_code'];
            echo json_encode([
                'success' => true,
                'share_code' => $existing['share_code'],
                'share_url' => $shareUrl,
                'message' => 'Link chia sẻ đã tồn tại'
            ]);
            return;
        }
        
        // Generate unique share code
        $shareCode = bin2hex(random_bytes(8));
        
        // Use audio text as title if not provided
        if (!$title) {
            $title = mb_substr($audio['text'], 0, 100);
        }
        
        $stmt = $db->prepare("INSERT INTO audio_share_links (audio_id, user_id, share_code, title) VALUES (?, ?, ?, ?)");
        $stmt->execute([$audioId, $userId, $shareCode, $title]);
        
        $shareUrl = BASE_URL . '/share/' . $shareCode;
        
        echo json_encode([
            'success' => true,
            'share_code' => $shareCode,
            'share_url' => $shareUrl,
            'message' => 'Đã tạo link chia sẻ'
        ]);
    } catch (Exception $e) {
        error_log("Create link error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Lỗi tạo link: ' . $e->getMessage()]);
    }
}

/**
 * Get user's shares
 */
function handleMyShares($db) {
    $userId = $_SESSION['user_id'];
    
    // Get public share requests
    $publicShares = [];
    try {
        $stmt = $db->prepare("
            SELECT sa.*, ah.text, ah.audio_url, ac.name as category_name
            FROM shared_audios sa
            JOIN audio_history ah ON sa.audio_id = ah.id
            JOIN audio_categories ac ON sa.category_id = ac.id
            WHERE sa.user_id = ?
            ORDER BY sa.created_at DESC
        ");
        $stmt->execute([$userId]);
        $publicShares = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Table might not exist
        $publicShares = [];
    }
    
    // Get link shares
    $linkShares = [];
    try {
        $stmt = $db->prepare("
            SELECT asl.*, ah.text, ah.audio_url
            FROM audio_share_links asl
            JOIN audio_history ah ON asl.audio_id = ah.id
            WHERE asl.user_id = ? AND asl.is_active = 1
            ORDER BY asl.created_at DESC
        ");
        $stmt->execute([$userId]);
        $linkShares = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add share URL to link shares
        foreach ($linkShares as &$share) {
            $share['share_url'] = BASE_URL . '/share/' . $share['share_code'];
        }
    } catch (PDOException $e) {
        // Table might not exist
        $linkShares = [];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'public_shares' => $publicShares,
            'link_shares' => $linkShares
        ]
    ]);
}

/**
 * Delete share link
 */
function handleDeleteLink($db, $input) {
    $userId = $_SESSION['user_id'];
    $linkId = (int)($input['id'] ?? 0);
    
    $stmt = $db->prepare("UPDATE audio_share_links SET is_active = 0 WHERE id = ? AND user_id = ?");
    $stmt->execute([$linkId, $userId]);
    
    echo json_encode(['success' => true, 'message' => 'Đã xóa link chia sẻ']);
}

/**
 * Cancel public share request
 */
function handleCancelRequest($db, $input) {
    $userId = $_SESSION['user_id'];
    $shareId = (int)($input['id'] ?? 0);
    
    $stmt = $db->prepare("DELETE FROM shared_audios WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$shareId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã hủy yêu cầu']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Không thể hủy yêu cầu này']);
    }
}

/**
 * Get public shared audios (for homepage)
 */
function handleGetPublic($db) {
    $categoryId = (int)($_GET['category'] ?? 0);
    $limit = (int)($_GET['limit'] ?? 12);
    $page = (int)($_GET['page'] ?? 1);
    $offset = ($page - 1) * $limit;
    
    $where = "sa.status = 'approved'";
    $params = [];
    
    if ($categoryId > 0) {
        $where .= " AND sa.category_id = ?";
        $params[] = $categoryId;
    }
    
    // Get total count
    $stmt = $db->prepare("SELECT COUNT(*) FROM shared_audios sa WHERE $where");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    // Get items
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare("
        SELECT sa.*, ah.text, ah.audio_url, ah.voice, ac.name as category_name, ac.icon as category_icon,
               u.email as author_email
        FROM shared_audios sa
        JOIN audio_history ah ON sa.audio_id = ah.id
        JOIN audio_categories ac ON sa.category_id = ac.id
        JOIN users u ON sa.user_id = u.id
        WHERE $where
        ORDER BY sa.approved_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mask email
    foreach ($items as &$item) {
        $email = $item['author_email'];
        $parts = explode('@', $email);
        $item['author'] = substr($parts[0], 0, 3) . '***';
        unset($item['author_email']);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ]
    ]);
}

/**
 * View shared audio via link
 */
function handleViewLink($db) {
    $code = $_GET['code'] ?? '';
    
    if (!$code) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Mã chia sẻ không hợp lệ']);
        return;
    }
    
    $stmt = $db->prepare("
        SELECT asl.*, ah.text, ah.audio_url, ah.voice, ah.lang, u.email as author_email
        FROM audio_share_links asl
        JOIN audio_history ah ON asl.audio_id = ah.id
        JOIN users u ON asl.user_id = u.id
        WHERE asl.share_code = ? AND asl.is_active = 1
    ");
    $stmt->execute([$code]);
    $share = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$share) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Link chia sẻ không tồn tại hoặc đã hết hạn']);
        return;
    }
    
    // Increment view count
    $stmt = $db->prepare("UPDATE audio_share_links SET views = views + 1 WHERE id = ?");
    $stmt->execute([$share['id']]);
    
    // Mask email
    $email = $share['author_email'];
    $parts = explode('@', $email);
    $share['author'] = substr($parts[0], 0, 3) . '***';
    unset($share['author_email']);
    
    echo json_encode(['success' => true, 'data' => $share]);
}

/**
 * Admin: Get all share requests
 */
function handleAdminList($db) {
    $status = $_GET['status'] ?? 'all';
    
    $where = "1=1";
    $params = [];
    
    if ($status !== 'all') {
        $where .= " AND sa.status = ?";
        $params[] = $status;
    }
    
    $stmt = $db->prepare("
        SELECT sa.*, ah.text, ah.audio_url, ac.name as category_name, u.email as user_email
        FROM shared_audios sa
        JOIN audio_history ah ON sa.audio_id = ah.id
        JOIN audio_categories ac ON sa.category_id = ac.id
        JOIN users u ON sa.user_id = u.id
        WHERE $where
        ORDER BY 
            CASE sa.status 
                WHEN 'pending' THEN 1 
                WHEN 'approved' THEN 2 
                ELSE 3 
            END,
            sa.created_at DESC
    ");
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => ['items' => $items]]);
}

/**
 * Admin: Approve share request
 */
function handleApprove($db, $input) {
    $shareId = (int)($input['id'] ?? 0);
    $adminId = $_SESSION['user_id'];
    
    $stmt = $db->prepare("UPDATE shared_audios SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ? AND status = 'pending'");
    $stmt->execute([$adminId, $shareId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã duyệt yêu cầu']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Không thể duyệt yêu cầu này']);
    }
}

/**
 * Admin: Reject share request
 */
function handleReject($db, $input) {
    $shareId = (int)($input['id'] ?? 0);
    $note = trim($input['note'] ?? '');
    
    $stmt = $db->prepare("UPDATE shared_audios SET status = 'rejected', admin_note = ?, rejected_at = NOW() WHERE id = ? AND status = 'pending'");
    $stmt->execute([$note, $shareId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã từ chối yêu cầu']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Không thể từ chối yêu cầu này']);
    }
}

/**
 * Get categories
 */
function handleGetCategories($db) {
    $stmt = $db->query("SELECT * FROM audio_categories WHERE is_active = 1 ORDER BY display_order");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => ['categories' => $categories]]);
}
