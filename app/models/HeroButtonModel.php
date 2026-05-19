<?php
/**
 * Hero Button Model
 * Handles hero button data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class HeroButtonModel extends BaseModel {
    protected $table = 'hero_buttons';
    protected $fillable = [
        'hero_section_id', 'button_text', 'button_url', 'button_style',
        'background_color', 'text_color', 'border_color', 'hover_background_color',
        'hover_text_color', 'font_size', 'padding', 'border_radius',
        'sort_order', 'is_active'
    ];
    
    /**
     * Get buttons by hero section ID
     */
    public function getByHeroSection($heroSectionId, $activeOnly = true) {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE hero_section_id = ?" . ($activeOnly ? " AND is_active = 1" : "") . "
            ORDER BY sort_order ASC
        ";
        
        return $this->db->query($sql, [$heroSectionId]);
    }
    
    /**
     * Create new hero button
     */
    public function createButton($data) {
        // Filter only fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Set default values
        $filteredData['sort_order'] = $filteredData['sort_order'] ?? $this->getNextSortOrder($filteredData['hero_section_id']);
        $filteredData['is_active'] = $filteredData['is_active'] ?? 1;
        $filteredData['button_style'] = $filteredData['button_style'] ?? 'primary';
        $filteredData['font_size'] = $filteredData['font_size'] ?? '16px';
        $filteredData['padding'] = $filteredData['padding'] ?? '12px 24px';
        $filteredData['border_radius'] = $filteredData['border_radius'] ?? '6px';
        
        // Set default colors based on button style
        if (empty($filteredData['background_color']) || empty($filteredData['text_color'])) {
            $defaultColors = $this->getDefaultColors($filteredData['button_style']);
            $filteredData['background_color'] = $filteredData['background_color'] ?? $defaultColors['background'];
            $filteredData['text_color'] = $filteredData['text_color'] ?? $defaultColors['text'];
        }
        
        return $this->create($filteredData);
    }
    
    /**
     * Update hero button
     */
    public function updateButton($id, $data) {
        // Filter only fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        return $this->update($id, $filteredData);
    }
    
    /**
     * Toggle button status
     */
    public function toggleStatus($id) {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Update button sort order
     */
    public function updateSortOrder($id, $newOrder) {
        $sql = "UPDATE {$this->table} SET sort_order = ? WHERE id = ?";
        return $this->db->query($sql, [$newOrder, $id]);
    }
    
    /**
     * Reorder buttons for a hero section
     */
    public function reorderButtons($heroSectionId, $buttonIds) {
        try {
            $this->db->beginTransaction();
            
            foreach ($buttonIds as $index => $buttonId) {
                $sql = "UPDATE {$this->table} SET sort_order = ? WHERE id = ? AND hero_section_id = ?";
                $this->db->query($sql, [$index + 1, $buttonId, $heroSectionId]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Delete button
     */
    public function deleteButton($id) {
        return $this->delete($id);
    }
    
    /**
     * Delete all buttons by hero section ID
     */
    public function deleteByHeroSectionId($heroSectionId) {
        return $this->db->table($this->table)->where('hero_section_id', $heroSectionId)->delete();
    }
    
    /**
     * Get next sort order for a hero section
     */
    private function getNextSortOrder($heroSectionId) {
        $result = $this->db->table($this->table)
            ->select('MAX(sort_order) as max_sort')
            ->where('hero_section_id', $heroSectionId)
            ->first();
        return ($result && $result['max_sort']) ? $result['max_sort'] + 1 : 1;
    }
    
    /**
     * Get default colors based on button style
     */
    private function getDefaultColors($style) {
        $colors = [
            'primary' => ['background' => '#356DF1', 'text' => '#ffffff'],
            'secondary' => ['background' => '#6c757d', 'text' => '#ffffff'],
            'outline' => ['background' => 'transparent', 'text' => '#356DF1'],
            'ghost' => ['background' => 'transparent', 'text' => '#333333']
        ];
        
        return $colors[$style] ?? $colors['primary'];
    }
    
    /**
     * Get button for API response
     */
    public function getForApi($id) {
        $button = $this->find($id);
        
        if (!$button) {
            return null;
        }
        
        return [
            'id' => $button['id'],
            'hero_section_id' => $button['hero_section_id'],
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
    }
    
    /**
     * Validate button data
     */
    public function validateButtonData($data, $isUpdate = false) {
        $errors = [];
        
        // Required fields
        if (!$isUpdate && empty($data['hero_section_id'])) {
            $errors[] = 'Hero Section ID is required';
        }
        
        if (empty($data['button_text'])) {
            $errors[] = 'Button text is required';
        }
        
        if (empty($data['button_url'])) {
            $errors[] = 'Button URL is required';
        }
        
        // Validate URL format
        if (!empty($data['button_url']) && !filter_var($data['button_url'], FILTER_VALIDATE_URL) && strpos($data['button_url'], '?') !== 0) {
            $errors[] = 'Button URL must be a valid URL or relative path';
        }
        
        // Validate button style
        if (!empty($data['button_style']) && !in_array($data['button_style'], ['primary', 'secondary', 'outline', 'ghost'])) {
            $errors[] = 'Button style must be one of: primary, secondary, outline, ghost';
        }
        
        // Validate color format
        $colorFields = ['background_color', 'text_color', 'border_color', 'hover_background_color', 'hover_text_color'];
        foreach ($colorFields as $field) {
            if (!empty($data[$field]) && !$this->isValidColor($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid color (hex, rgb, or color name)';
            }
        }
        
        // Validate sort order
        if (!empty($data['sort_order']) && (!is_numeric($data['sort_order']) || $data['sort_order'] < 0)) {
            $errors[] = 'Sort order must be a positive number';
        }
        
        return $errors;
    }
    
    /**
     * Check if color is valid
     */
    private function isValidColor($color) {
        // Check for hex color
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            return true;
        }
        
        // Check for rgb/rgba color
        if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+\s*)?\)$/', $color)) {
            return true;
        }
        
        // Check for common color names
        $commonColors = ['red', 'green', 'blue', 'white', 'black', 'gray', 'grey', 'yellow', 'orange', 'purple', 'pink', 'brown', 'transparent'];
        if (in_array(strtolower($color), $commonColors)) {
            return true;
        }
        
        return false;
    }
}
