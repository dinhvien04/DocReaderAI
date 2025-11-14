<?php
/**
 * Data Model
 * Handles all database operations related to audio history
 */

class Data {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Add audio record to history
     * @param int $userId
     * @param string $text
     * @param string $audioUrl
     * @param string $voice
     * @param string $lang
     * @return int Audio ID or 0 on failure
     */
    public function addAudio(int $userId, string $text, string $audioUrl, string $voice, string $lang): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audio_history (user_id, text, audio_url, voice, lang, position) 
                VALUES (:user_id, :text, :audio_url, :voice, :lang, 0)
            ");
            
            $stmt->execute([
                'user_id' => $userId,
                'text' => $text,
                'audio_url' => $audioUrl,
                'voice' => $voice,
                'lang' => $lang
            ]);
            
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in addAudio: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get all audio records for a user
     * @param int $userId
     * @return array
     */
    public function getAudioByUserId(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, text, audio_url, voice, lang, position, created_at, updated_at
                FROM audio_history 
                WHERE user_id = :user_id
                ORDER BY created_at DESC
            ");
            
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getAudioByUserId: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get audio record by ID
     * @param int $id
     * @return array|null
     */
    public function getAudioById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, text, audio_url, voice, lang, position, created_at, updated_at
                FROM audio_history 
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            $audio = $stmt->fetch();
            
            return $audio ?: null;
        } catch (PDOException $e) {
            error_log("Error in getAudioById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update playback position
     * @param int $id
     * @param int $position Position in seconds
     * @return bool
     */
    public function updatePosition(int $id, int $position): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE audio_history 
                SET position = :position
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'position' => $position,
                'id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error in updatePosition: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete audio record
     * @param int $id
     * @return bool
     */
    public function deleteAudio(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM audio_history WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Error in deleteAudio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user owns the audio record
     * @param int $id Audio ID
     * @param int $userId User ID
     * @return bool
     */
    public function checkOwnership(int $id, int $userId): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM audio_history 
                WHERE id = :id AND user_id = :user_id
            ");
            
            $stmt->execute([
                'id' => $id,
                'user_id' => $userId
            ]);
            
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error in checkOwnership: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get audio history with pagination
     * @param int $userId
     * @param int $page Page number (1-based)
     * @param int $limit Records per page
     * @return array ['audios' => array, 'total' => int, 'pages' => int]
     */
    public function getAudioByUserIdPaginated(int $userId, int $page = 1, int $limit = 20): array {
        try {
            $offset = ($page - 1) * $limit;
            
            // Get total count
            $countStmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM audio_history 
                WHERE user_id = :user_id
            ");
            $countStmt->execute(['user_id' => $userId]);
            $total = (int) $countStmt->fetch()['total'];
            
            // Get audios
            $stmt = $this->db->prepare("
                SELECT id, user_id, text, audio_url, voice, lang, position, created_at, updated_at
                FROM audio_history 
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $audios = $stmt->fetchAll();
            
            return [
                'audios' => $audios,  // Consistent key name
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
        } catch (PDOException $e) {
            error_log("Error in getAudioByUserIdPaginated: " . $e->getMessage());
            return ['audios' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1];
        }
    }
    
    /**
     * Get total audio count for statistics
     * @return int
     */
    public function getTotalAudioCount(): int {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM audio_history");
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log("Error in getTotalAudioCount: " . $e->getMessage());
            return 0;
        }
    }
}
