<?php
/**
 * SystemConfig Model
 * Handles all database operations related to system configuration
 */

class SystemConfig {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Get configuration value by key
     * @param string $key
     * @return string|null
     */
    public function getConfig(string $key): ?string {
        try {
            $stmt = $this->db->prepare("
                SELECT config_value 
                FROM system_config 
                WHERE config_key = :key
            ");
            
            $stmt->execute(['key' => $key]);
            $result = $stmt->fetch();
            
            return $result ? $result['config_value'] : null;
        } catch (PDOException $e) {
            error_log("Error in getConfig: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Set/Create new configuration
     * @param string $key
     * @param string $value
     * @param string $description
     * @param string $category
     * @return bool
     */
    public function setConfig(string $key, string $value, string $description = '', string $category = 'limits'): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO system_config (config_key, config_value, description, category) 
                VALUES (:key, :value, :description, :category)
                ON DUPLICATE KEY UPDATE 
                    config_value = :value,
                    description = :description,
                    category = :category
            ");
            
            return $stmt->execute([
                'key' => $key,
                'value' => $value,
                'description' => $description,
                'category' => $category
            ]);
        } catch (PDOException $e) {
            error_log("Error in setConfig: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all configurations
     * @param bool $includePrivate Include non-public configs
     * @return array
     */
    public function getAllConfigs(bool $includePrivate = false): array {
        try {
            $whereClause = $includePrivate ? '' : 'WHERE is_public = 1';
            
            $stmt = $this->db->prepare("
                SELECT id, config_key, config_value, description, category, is_public, created_at, updated_at
                FROM system_config 
                {$whereClause}
                ORDER BY category, config_key
            ");
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getAllConfigs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update configuration value
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function updateConfig(string $key, string $value): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE system_config 
                SET config_value = :value
                WHERE config_key = :key
            ");
            
            return $stmt->execute([
                'value' => $value,
                'key' => $key
            ]);
        } catch (PDOException $e) {
            error_log("Error in updateConfig: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get configurations grouped by category
     * @param bool $includePrivate Include non-public configs
     * @return array Associative array with categories as keys
     */
    public function getConfigsByCategory(bool $includePrivate = false): array {
        try {
            $configs = $this->getAllConfigs($includePrivate);
            $grouped = [];
            
            foreach ($configs as $config) {
                $category = $config['category'];
                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }
                $grouped[$category][] = $config;
            }
            
            return $grouped;
        } catch (Exception $e) {
            error_log("Error in getConfigsByCategory: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete configuration
     * @param string $key
     * @return bool
     */
    public function deleteConfig(string $key): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM system_config WHERE config_key = :key");
            return $stmt->execute(['key' => $key]);
        } catch (PDOException $e) {
            error_log("Error in deleteConfig: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if configuration exists
     * @param string $key
     * @return bool
     */
    public function configExists(string $key): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM system_config 
                WHERE config_key = :key
            ");
            
            $stmt->execute(['key' => $key]);
            $result = $stmt->fetch();
            
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error in configExists: " . $e->getMessage());
            return false;
        }
    }
}
