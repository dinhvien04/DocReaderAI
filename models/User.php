<?php
/**
 * User Model
 * Handles all database operations related to users
 */

class User {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Get user by email
     * @param string $email
     * @return array|null
     */
    public function getUserByEmail(string $email): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, password, role, status, otp, otp_expires_at, created_at, updated_at
                FROM users 
                WHERE email = :email
            ");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Error in getUserByEmail: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by username
     * @param string $username
     * @return array|null
     */
    public function getUserByUsername(string $username): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, password, role, status, otp, otp_expires_at, created_at, updated_at
                FROM users 
                WHERE username = :username
            ");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();
            
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Error in getUserByUsername: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by username or email
     * @param string $identifier Username or email
     * @return array|null
     */
    public function getUserByIdentifier(string $identifier): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, password, role, status, otp, otp_expires_at, created_at, updated_at
                FROM users 
                WHERE username = ? OR email = ?
            ");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();
            
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Error in getUserByIdentifier: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by ID
     * @param int $id
     * @return array|null
     */
    public function getUserById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, password, role, status, created_at, updated_at
                FROM users 
                WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
            
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Error in getUserById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new user with username, email and password
     * @param string $username
     * @param string $email
     * @param string $password Plain text password (will be hashed)
     * @param string $otp
     * @return int User ID or 0 on failure
     */
    public function createUser(string $username, string $email, string $password, string $otp): int {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $otpExpiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
            
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, otp, otp_expires_at, status) 
                VALUES (:username, :email, :password, :otp, :otp_expires_at, 'inactive')
            ");
            
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt
            ]);
            
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in createUser: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Update user password
     * @param string $email
     * @param string $password Plain text password (will be hashed)
     * @return bool
     */
    public function updatePassword(string $email, string $password): bool {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password = :password, otp = NULL, otp_expires_at = NULL
                WHERE email = :email
            ");
            
            return $stmt->execute([
                'password' => $hashedPassword,
                'email' => $email
            ]);
        } catch (PDOException $e) {
            error_log("Error in updatePassword: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user password by ID
     * @param int $userId
     * @param string $password Plain text password (will be hashed)
     * @return bool
     */
    public function updatePasswordById(int $userId, string $password): bool {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password = :password
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'password' => $hashedPassword,
                'id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Error in updatePasswordById: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update OTP for user
     * @param string $email
     * @param string $otp
     * @return bool
     */
    public function updateOtp(string $email, string $otp): bool {
        try {
            $otpExpiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET otp = :otp, otp_expires_at = :otp_expires_at
                WHERE email = :email
            ");
            
            return $stmt->execute([
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
                'email' => $email
            ]);
        } catch (PDOException $e) {
            error_log("Error in updateOtp: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user status
     * @param string $email
     * @param string $status 'active' or 'inactive'
     * @return bool
     */
    public function updateStatus(string $email, string $status): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET status = :status, otp = NULL, otp_expires_at = NULL
                WHERE email = :email
            ");
            
            return $stmt->execute([
                'status' => $status,
                'email' => $email
            ]);
        } catch (PDOException $e) {
            error_log("Error in updateStatus: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify user password by identifier (username or email)
     * @param string $identifier Username or email
     * @param string $password Plain text password
     * @return bool
     */
    public function verifyPassword(string $identifier, string $password): bool {
        try {
            $user = $this->getUserByIdentifier($identifier);
            
            if (!$user || empty($user['password'])) {
                return false;
            }
            
            return password_verify($password, $user['password']);
        } catch (Exception $e) {
            error_log("Error in verifyPassword: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users with pagination and search
     * @param int $page Page number (1-based)
     * @param int $limit Records per page
     * @param string $search Search term for email
     * @return array ['users' => array, 'total' => int, 'pages' => int]
     */
    public function getAllUsers(int $page = 1, int $limit = 20, string $search = ''): array {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                $whereClause = "WHERE email LIKE :search OR role LIKE :search";
                $params['search'] = "%{$search}%";
            }
            
            // Get total count
            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM users {$whereClause}");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetch()['total'];
            
            // Get users
            $stmt = $this->db->prepare("
                SELECT id, username, email, role, status, created_at, updated_at
                FROM users 
                {$whereClause}
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            return [
                'users' => $users,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
        } catch (PDOException $e) {
            error_log("Error in getAllUsers: " . $e->getMessage());
            return ['users' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1];
        }
    }
    
    /**
     * Update user role
     * @param int $userId
     * @param string $role 'user' or 'admin'
     * @return bool
     */
    public function updateUserRole(int $userId, string $role): bool {
        try {
            // Validate role
            if (!in_array($role, ['user', 'admin'])) {
                return false;
            }
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET role = :role
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'role' => $role,
                'id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Error in updateUserRole: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete user
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute(['id' => $userId]);
        } catch (PDOException $e) {
            error_log("Error in deleteUser: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify OTP
     * @param string $email
     * @param string $otp
     * @return bool
     */
    public function verifyOtp(string $email, string $otp): bool {
        try {
            $user = $this->getUserByEmail($email);
            
            if (!$user || empty($user['otp'])) {
                return false;
            }
            
            // Check if OTP matches
            if ($user['otp'] !== $otp) {
                return false;
            }
            
            // Check if OTP is expired
            if (strtotime($user['otp_expires_at']) < time()) {
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in verifyOtp: " . $e->getMessage());
            return false;
        }
    }
}
