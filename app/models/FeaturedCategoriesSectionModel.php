<?php
/**
 * Featured Categories Section Model
 * Handles featured categories section data operations
 */

require_once __DIR__ . '/BaseModel.php';

class FeaturedCategoriesSectionModel extends BaseModel {
    protected $table = 'featured_categories_section';
    
    /**
     * Get the first (and only) featured categories section
     */
    public function getFirst() {
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1";
        $result = $this->db->query($sql);
        return $result ? $result[0] : null;
    }
    
    /**
     * Create a new featured categories section
     */
    public function createSection($data) {
        return $this->create($data);
    }
    
    /**
     * Update featured categories section
     */
    public function updateSection($id, $data) {
        return $this->update($id, $data);
    }
    
    /**
     * Toggle section status (active/inactive)
     */
    public function toggleStatus($id) {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Get section by ID
     */
    public function getById($id) {
        return $this->find($id);
    }
    
    /**
     * Check if table exists
     */
    public function tableExists() {
        $sql = "SHOW TABLES LIKE '{$this->table}'";
        $result = $this->db->query($sql);
        return !empty($result);
    }
    
    /**
     * Create table if not exists (fallback method)
     */
    public function createTableIfNotExists() {
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title TEXT NOT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        return $this->db->query($sql);
    }
}
