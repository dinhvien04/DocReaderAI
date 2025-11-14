-- DocReader AI Studio Database Schema
-- MySQL 5.7+

-- Create database
CREATE DATABASE IF NOT EXISTS docreader_ai_studio
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE docreader_ai_studio;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    otp VARCHAR(10) NULL,
    otp_expires_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: audio_history
CREATE TABLE IF NOT EXISTS audio_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    text TEXT NOT NULL,
    audio_url TEXT NOT NULL,
    voice VARCHAR(50) NOT NULL,
    lang VARCHAR(10) NOT NULL,
    position INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: system_config
CREATE TABLE IF NOT EXISTS system_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT NOT NULL,
    description TEXT,
    category VARCHAR(50) DEFAULT 'limits',
    is_public BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_key (config_key),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system configurations
INSERT INTO system_config (config_key, config_value, description, category, is_public) VALUES
('max_file_size', '10485760', 'Kích thước file tối đa (10MB = 10485760 bytes)', 'limits', true),
('max_text_length', '5000', 'Độ dài văn bản tối đa cho chuyển đổi giọng nói', 'limits', true),
('otp_expiry_minutes', '10', 'Thời gian hết hạn OTP (phút)', 'security', false),
('max_audio_history', '100', 'Số lượng lịch sử audio tối đa mỗi người dùng', 'limits', true),
('tts_default_speed', '1', 'Tốc độ đọc mặc định (0.5 = chậm, 1 = bình thường, 2 = nhanh)', 'features', true),
('enable_translation', '1', 'Bật tính năng dịch thuật (0 = tắt, 1 = bật)', 'features', true),
('enable_summarization', '1', 'Bật tính năng tóm tắt (0 = tắt, 1 = bật)', 'features', true),
('session_timeout', '1800', 'Thời gian hết phiên đăng nhập (giây) - 1800 = 30 phút', 'security', false)
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- Create default admin user (password: admin123)
-- Password hash for 'admin123' using bcrypt
INSERT INTO users (username, email, password, role, status) VALUES
('admin', 'admin@docreader.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active')
ON DUPLICATE KEY UPDATE email=email;

-- Display success message
SELECT 'Database schema created successfully!' AS message;
SELECT 'Default admin user: admin (or admin@docreader.com) / admin123' AS credentials;
