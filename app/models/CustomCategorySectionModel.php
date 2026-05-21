<?php
/**
 * Custom Category Section Model
 * Handles custom category sections data operations
 */

require_once __DIR__ . '/BaseModel.php';

class CustomCategorySectionModel extends BaseModel {
    protected $table = 'custom_category_sections';
    protected $fillable = ['title', 'category_id', 'display_type', 'sort_order', 'is_active'];
    
    /**
     * Get all custom category sections with category details
     */
    public function getAllWithCategory($activeOnly = false) {
        $sql = "
            SELECT s.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} s
            LEFT JOIN categories c ON s.category_id = c.id
        ";
        
        $bindings = [];
        if ($activeOnly) {
            $sql .= " WHERE s.is_active = 1 AND c.status = 'active'";
        }
        
        $sql .= " ORDER BY s.sort_order ASC, s.id ASC";
        
        try {
            return $this->db->query($sql, $bindings) ?: [];
        } catch (Exception $e) {
            error_log("CustomCategorySectionModel::getAllWithCategory error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create section
     */
    public function createSection($data) {
        return $this->create($data);
    }
    
    /**
     * Update section
     */
    public function updateSection($id, $data) {
        return $this->update($id, $data);
    }
    
    /**
     * Delete section
     */
    public function deleteSection($id) {
        return $this->delete($id);
    }
    
    /**
     * Get section count
     */
    public function getCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $result = $this->db->query($sql);
            return isset($result[0]['count']) ? intval($result[0]['count']) : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Toggle section status (active/inactive)
     */
    public function toggleStatus($id) {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Get section by ID
     */
    public function getById($id) {
        return $this->find($id);
    }
}
