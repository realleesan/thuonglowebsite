<?php
/**
 * Featured Products Section Model
 * Handles featured products section data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class FeaturedProductsSectionModel extends BaseModel {
    protected $table = 'featured_products_section';
    protected $fillable = ['title', 'is_active'];
    
    /**
     * Get active featured products section
     */
    public function getActive() {
        try {
            $sql = "
                SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                ORDER BY id DESC 
                LIMIT 1
            ";
            
            $result = $this->db->getPdo()->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Get active featured products section error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get first record (for single section management)
     */
    public function getFirst() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1";
            $result = $this->db->getPdo()->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Get first featured products section error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new featured products section
     */
    public function createSection($data) {
        // Filter only fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Set default values
        $filteredData['is_active'] = $filteredData['is_active'] ?? 1;
        
        return $this->create($filteredData);
    }
    
    /**
     * Update featured products section
     */
    public function updateSection($id, $data) {
        // Filter only fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        return $this->update($id, $filteredData);
    }
    
    /**
     * Toggle section status
     */
    public function toggleStatus($id) {
        try {
            // Get current status
            $current = $this->find($id);
            if (!$current) {
                return false;
            }
            
            // Toggle status
            $newStatus = $current['is_active'] ? 0 : 1;
            return $this->update($id, ['is_active' => $newStatus]);
            
        } catch (Exception $e) {
            error_log("Toggle status error: " . $e->getMessage());
            return false;
        }
    }
}
