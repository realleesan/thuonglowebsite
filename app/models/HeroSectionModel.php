<?php
/**
 * Hero Section Model
 * Handles hero section data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class HeroSectionModel extends BaseModel {
    protected $table = 'hero_sections';
    protected $fillable = [
        'title_main', 'title_highlight', 'subtitle', 'background_color',
        'text_color', 'highlight_color', 'font_family', 'title_font_size',
        'subtitle_font_size', 'image_url', 'image_alt', 'is_active'
    ];
    
    /**
     * Get active hero section
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
            error_log("Get active error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get hero section with buttons
     */
    public function getWithButtons($id = null) {
        try {
            if ($id === null) {
                $heroSection = $this->getActive();
                if (!$heroSection) return null;
                $id = $heroSection['id'];
            } else {
                $heroSection = $this->find($id);
                if (!$heroSection) return null;
            }
            
            // Get buttons for this hero section
            $sql = "
                SELECT * FROM hero_buttons 
                WHERE hero_section_id = ? AND is_active = 1 
                ORDER BY sort_order ASC
            ";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$id]);
            $buttons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $heroSection['buttons'] = $buttons ?: [];
            
            return $heroSection;
        } catch (Exception $e) {
            error_log("Get with buttons error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new hero section
     */
    public function createHeroSection($data) {
        // Filter only fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Set default values
        $filteredData['is_active'] = $filteredData['is_active'] ?? 1;
        $filteredData['background_color'] = $filteredData['background_color'] ?? '#ffffff';
        $filteredData['text_color'] = $filteredData['text_color'] ?? '#333333';
        $filteredData['highlight_color'] = $filteredData['highlight_color'] ?? '#356DF1';
        $filteredData['font_family'] = $filteredData['font_family'] ?? 'Arial, sans-serif';
        $filteredData['title_font_size'] = $filteredData['title_font_size'] ?? '48px';
        $filteredData['subtitle_font_size'] = $filteredData['subtitle_font_size'] ?? '18px';
        
        return $this->create($filteredData);
    }
    
    /**
     * Update hero section
     */
    public function updateHeroSection($id, $data) {
        // Filter only fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        return $this->update($id, $filteredData);
    }
    
    /**
     * Toggle hero section status
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
    
    /**
     * Get all hero sections for admin
     */
    public function getAllForAdmin() {
        $sql = "
            SELECT hs.*, 
                   COUNT(hb.id) as button_count
            FROM {$this->table} hs
            LEFT JOIN hero_buttons hb ON hs.id = hb.hero_section_id
            GROUP BY hs.id
            ORDER BY hs.created_at DESC
        ";
        
        return $this->db->query($sql);
    }
    
    /**
     * Delete hero section and its buttons
     */
    public function deleteHeroSection($id) {
        try {
            $this->db->beginTransaction();
            
            // Delete buttons first (foreign key will handle this, but being explicit)
            $deleteButtonsSql = "DELETE FROM hero_buttons WHERE hero_section_id = ?";
            $this->db->query($deleteButtonsSql, [$id]);
            
            // Delete hero section
            $result = $this->delete($id);
            
            $this->db->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get hero section for API response
     */
    public function getForApi($id = null) {
        $heroSection = $this->getWithButtons($id);
        
        if (!$heroSection) {
            return null;
        }
        
        // Format for API response
        return [
            'id' => $heroSection['id'],
            'title_main' => $heroSection['title_main'],
            'title_highlight' => $heroSection['title_highlight'],
            'subtitle' => $heroSection['subtitle'],
            'background_color' => $heroSection['background_color'],
            'text_color' => $heroSection['text_color'],
            'highlight_color' => $heroSection['highlight_color'],
            'font_family' => $heroSection['font_family'],
            'title_font_size' => $heroSection['title_font_size'],
            'subtitle_font_size' => $heroSection['subtitle_font_size'],
            'image_url' => $heroSection['image_url'],
            'image_alt' => $heroSection['image_alt'],
            'is_active' => (bool)$heroSection['is_active'],
            'buttons' => array_map(function($button) {
                return [
                    'id' => $button['id'],
                    'button_text' => $button['button_text'],
                    'button_url' => $button['button_url'],
                    'button_style' => $button['button_style'],
                    'background_color' => $button['background_color'],
                    'text_color' => $button['text_color'],
                    'border_color' => $button['border_color'],
                    'hover_background_color' => $button['hover_background_color'],
                    'hover_text_color' => $button['hover_text_color'],
                    'font_size' => $button['font_size'],
                    'padding' => $button['padding'],
                    'border_radius' => $button['border_radius'],
                    'sort_order' => $button['sort_order'],
                    'is_active' => (bool)$button['is_active']
                ];
            }, $heroSection['buttons'])
        ];
    }
}
