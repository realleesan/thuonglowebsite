<?php
/**
 * Top Banner Model
 * Handles top announcement banner operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class TopBannerModel extends BaseModel {
    protected $table = 'top_banners';
    protected $fillable = [
        'content', 'button_text', 'button_url', 'is_active'
    ];
    
    /**
     * Get active Top Banner
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
            error_log("Get active top banner error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get first Top Banner record (for administration)
     */
    public function getFirst() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1";
            $result = $this->db->getPdo()->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Get first top banner error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new Top Banner
     */
    public function createBanner($data) {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Set default values
        $filteredData['is_active'] = $filteredData['is_active'] ?? 1;
        $filteredData['button_text'] = $filteredData['button_text'] ?? 'Khám phá ngay!';
        $filteredData['button_url'] = $filteredData['button_url'] ?? '?page=products';
        
        return $this->create($filteredData);
    }
    
    /**
     * Update Top Banner
     */
    public function updateBanner($id, $data) {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        return $this->update($id, $filteredData);
    }
    
    /**
     * Toggle Top Banner visibility status
     */
    public function toggleStatus($id) {
        try {
            $current = $this->find($id);
            if (!$current) {
                return false;
            }
            
            $newStatus = $current['is_active'] ? 0 : 1;
            return $this->update($id, ['is_active' => $newStatus]);
        } catch (Exception $e) {
            error_log("Toggle top banner status error: " . $e->getMessage());
            return false;
        }
    }
}
