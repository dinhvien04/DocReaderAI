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
('max_file_size', '10485760', 'Maximum upload file size in bytes (10MB)', 'limits', true),
('max_text_length', '5000', 'Maximum text length for TTS conversion', 'limits', true),
('otp_expiry_minutes', '10', 'OTP expiration time in minutes', 'security', false),
('max_audio_history', '100', 'Maximum audio history records per user', 'limits', true),
('tts_default_speed', '1', 'Default TTS speed (0.5 to 2.0)', 'features', true),
('enable_translation', '1', 'Enable translation feature (0 or 1)', 'features', true),
('enable_summarization', '1', 'Enable summarization feature (0 or 1)', 'features', true),
('session_timeout', '1800', 'Session timeout in seconds (30 minutes)', 'security', false)
ON DUPLICATE KEY UPDATE config_value=VALUES(config_value);

-- Create default admin user (password: admin123)
-- Password hash for 'admin123' using bcrypt
INSERT INTO users (username, email, password, role, status) VALUES
('admin', 'admin@docreader.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active')
ON DUPLICATE KEY UPDATE email=email;

-- Display success message
SELECT 'Database schema created successfully!' AS message;
SELECT 'Default admin user: admin (or admin@docreader.com) / admin123' AS credentials;
