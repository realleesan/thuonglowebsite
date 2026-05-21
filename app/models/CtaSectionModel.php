<?php
/**
 * CTA Section Model
 * Handles cta section data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class CtaSectionModel extends BaseModel {
    protected $table = 'cta_sections';
    protected $fillable = [
        'title', 'subtitle', 'content', 'button_text', 
        'button_url', 'background_color', 'image_url', 'is_active'
    ];
    
    /**
     * Get active CTA section
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
            error_log("Get active CTA section error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get first CTA section record (for administration)
     */
    public function getFirst() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1";
            $result = $this->db->getPdo()->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Get first CTA section error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new CTA section
     */
    public function createSection($data) {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Set default values
        $filteredData['is_active'] = $filteredData['is_active'] ?? 1;
        $filteredData['background_color'] = $filteredData['background_color'] ?? '#ECEDEF';
        $filteredData['button_text'] = $filteredData['button_text'] ?? 'Đăng ký ngay';
        $filteredData['button_url'] = $filteredData['button_url'] ?? '?page=agent';
        
        return $this->create($filteredData);
    }
    
    /**
     * Update CTA section
     */
    public function updateSection($id, $data) {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        return $this->update($id, $filteredData);
    }
    
    /**
     * Toggle CTA section visibility status
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
            error_log("Toggle CTA status error: " . $e->getMessage());
            return false;
        }
    }
}
