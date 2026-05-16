<?php

require_once __DIR__ . '/../../core/database.php';

/**
 * FilterConfigService - Service for managing product filter configuration
 */
class FilterConfigService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Save filter configuration
     */
    public function saveFilterConfig(array $config): array {
        try {
            // Save criteria order and enabled status
            if (isset($config['criteria'])) {
                $this->saveCriteriaSettings($config['criteria']);
            }
            
            // Save items configuration
            if (isset($config['items'])) {
                $this->saveItemsConfiguration($config['items']);
            }
            
            return [
                'success' => true,
                'message' => 'Filter configuration saved successfully'
            ];
            
        } catch (Exception $e) {
            error_log('FilterConfigService::saveFilterConfig error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error saving filter configuration: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get filter configuration
     */
    public function getFilterConfig(): array {
        try {
            $config = [
                'criteria' => $this->getCriteriaSettings(),
                'items' => $this->getItemsConfiguration()
            ];
            
            return [
                'success' => true,
                'data' => $config
            ];
            
        } catch (Exception $e) {
            error_log('FilterConfigService::getFilterConfig error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error getting filter configuration: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Save criteria settings to filter_settings table
     */
    private function saveCriteriaSettings(array $criteria): void {
        $criteriaOrder = [];
        $criteriaEnabled = [];
        
        foreach ($criteria as $criterion) {
            $criteriaOrder[$criterion['name']] = $criterion['order'];
            $criteriaEnabled[$criterion['name']] = $criterion['enabled'];
        }
        
        // Save criteria order
        $this->saveSetting('criteria_order', json_encode($criteriaOrder));
        
        // Save criteria enabled status
        $this->saveSetting('criteria_enabled', json_encode($criteriaEnabled));
    }
    
    /**
     * Save items configuration to filter_config table
     */
    private function saveItemsConfiguration(array $items): void {
        foreach ($items as $criteriaType => $itemsList) {
            foreach ($itemsList as $item) {
                $this->saveFilterItem($criteriaType, $item);
            }
        }
    }
    
    /**
     * Save individual filter item
     */
    private function saveFilterItem(string $criteriaType, array $item): void {
        $sql = "INSERT INTO filter_config (criteria_type, item_id, parent_id, sort_order, is_enabled) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                sort_order = VALUES(sort_order), 
                is_enabled = VALUES(is_enabled),
                updated_at = CURRENT_TIMESTAMP";
        
        $this->db->query($sql, [
            $criteriaType,
            $item['id'],
            $item['parent_id'],
            $item['order'],
            $item['enabled'] ? 1 : 0
        ]);
    }
    
    /**
     * Save setting to filter_settings table
     */
    private function saveSetting(string $key, string $value): void {
        $sql = "INSERT INTO filter_settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value),
                updated_at = CURRENT_TIMESTAMP";
        
        $this->db->query($sql, [$key, $value]);
    }
    
    /**
     * Get criteria settings from filter_settings table
     */
    private function getCriteriaSettings(): array {
        $criteria = [];
        
        // Get criteria order
        $orderResult = $this->getSetting('criteria_order');
        if ($orderResult) {
            $orderData = json_decode($orderResult, true);
            if ($orderData) {
                foreach ($orderData as $name => $order) {
                    $criteria[$name] = ['order' => $order];
                }
            }
        }
        
        // Get criteria enabled status
        $enabledResult = $this->getSetting('criteria_enabled');
        if ($enabledResult) {
            $enabledData = json_decode($enabledResult, true);
            if ($enabledData) {
                foreach ($enabledData as $name => $enabled) {
                    if (!isset($criteria[$name])) {
                        $criteria[$name] = [];
                    }
                    $criteria[$name]['enabled'] = $enabled;
                }
            }
        }
        
        return $criteria;
    }
    
    /**
     * Get items configuration from filter_config table
     */
    private function getItemsConfiguration(): array {
        $sql = "SELECT criteria_type, item_id, parent_id, sort_order, is_enabled 
                FROM filter_config 
                ORDER BY criteria_type, parent_id, sort_order";
        
        $results = $this->db->query($sql);
        
        $items = [];
        foreach ($results as $row) {
            $criteriaType = $row['criteria_type'];
            
            if (!isset($items[$criteriaType])) {
                $items[$criteriaType] = [];
            }
            
            $items[$criteriaType][] = [
                'id' => (int)$row['item_id'],
                'parent_id' => (int)$row['parent_id'],
                'order' => (int)$row['sort_order'],
                'enabled' => (bool)$row['is_enabled']
            ];
        }
        
        return $items;
    }
    
    /**
     * Get setting value from filter_settings table
     */
    private function getSetting(string $key): ?string {
        $sql = "SELECT setting_value FROM filter_settings WHERE setting_key = ?";
        $result = $this->db->query($sql, [$key]);
        
        return $result[0]['setting_value'] ?? null;
    }
    
    /**
     * Get categories for filter configuration
     */
    public function getCategoriesForFilter(): array {
        try {
            $sql = "SELECT c.id, c.name, c.parent_id, c.sort_order, COUNT(p.id) as product_count
                    FROM categories c
                    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                    WHERE c.status = 'active' AND c.show_in_filter = 1
                    GROUP BY c.id
                    ORDER BY c.parent_id, c.sort_order ASC, c.name ASC";
            
            $results = $this->db->query($sql);
            
            $categories = [];
            $categoryMap = [];
            
            // First pass: create category map
            foreach ($results as $row) {
                $category = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'parent_id' => (int)$row['parent_id'],
                    'sort_order' => (int)($row['sort_order'] ?? 0),
                    'count' => (int)$row['product_count'],
                    'children' => []
                ];
                
                $categoryMap[$category['id']] = $category;
                
                if ($category['parent_id'] == 0) {
                    $categories[] = &$categoryMap[$category['id']];
                }
            }
            
            // Second pass: build hierarchy
            foreach ($categoryMap as $id => $category) {
                if ($category['parent_id'] > 0 && isset($categoryMap[$category['parent_id']])) {
                    $categoryMap[$category['parent_id']]['children'][] = &$categoryMap[$id];
                }
            }
            
            // Third pass: sort children by sort_order (same logic as products page)
            $sortChildren = function(&$nodes) use (&$sortChildren) {
                foreach ($nodes as &$node) {
                    if (!empty($node['children'])) {
                        usort($node['children'], function ($a, $b) {
                            $sortA = (int)($a['sort_order'] ?? 0);
                            $sortB = (int)($b['sort_order'] ?? 0);
                            if ($sortA === $sortB) {
                                return strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
                            }
                            return $sortA <=> $sortB;
                        });
                        $sortChildren($node['children']);
                    }
                }
            };
            
            $sortChildren($categories);
            
            return $categories;
            
        } catch (Exception $e) {
            error_log('FilterConfigService::getCategoriesForFilter error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get brands for filter configuration
     */
    public function getBrandsForFilter(): array {
        try {
            $sql = "SELECT b.id, b.name, b.sort_order, COUNT(p.id) as product_count
                    FROM brands b
                    LEFT JOIN products p ON b.id = p.brand_id AND p.status = 'active'
                    WHERE b.status = 'active' AND b.show_in_filter = 1
                    GROUP BY b.id
                    ORDER BY b.sort_order ASC, b.name ASC";
            
            $results = $this->db->query($sql);
            
            $brands = [];
            foreach ($results as $row) {
                $brands[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'sort_order' => (int)($row['sort_order'] ?? 0),
                    'count' => (int)$row['product_count']
                ];
            }
            
            return $brands;
            
        } catch (Exception $e) {
            error_log('FilterConfigService::getBrandsForFilter error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get price ranges for filter configuration
     */
    public function getPriceRangesForFilter(): array {
        try {
            // Try to get from database first
            $sql = "SELECT id, name, min_price, max_price 
                    FROM price_ranges 
                    ORDER BY sort_order, id";
            
            $results = $this->db->query($sql);
            
            if (!empty($results)) {
                return array_map(function($row) {
                    return [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'min' => (int)$row['min_price'],
                        'max' => $row['max_price'] ? (int)$row['max_price'] : null
                    ];
                }, $results);
            }
            
            // If no data in database, return empty array (no hardcoded data)
            return [];
            
        } catch (Exception $e) {
            error_log('FilterConfigService::getPriceRangesForFilter error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reset filter configuration to default
     */
    public function resetToDefault(): array {
        try {
            $this->db->pdo->beginTransaction();
            
            // Clear existing configuration
            $this->db->query("DELETE FROM filter_config");
            $this->db->query("DELETE FROM filter_settings");
            
            // Insert default settings
            $this->saveSetting('criteria_order', json_encode([
                'categories' => 1,
                'brands' => 2,
                'price_ranges' => 3
            ]));
            
            $this->saveSetting('criteria_enabled', json_encode([
                'categories' => true,
                'brands' => true,
                'price_ranges' => true
            ]));
            
            // Insert default price ranges
            $priceRanges = $this->getPriceRangesForFilter();
            foreach ($priceRanges as $index => $range) {
                $sql = "INSERT INTO filter_config (criteria_type, item_id, parent_id, sort_order, is_enabled) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $this->db->query($sql, [
                    'price_ranges',
                    $range['id'],
                    0,
                    $index + 1,
                    1
                ]);
            }
            
            $this->db->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Filter configuration reset to default successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->pdo->rollback();
            error_log('FilterConfigService::resetToDefault error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error resetting filter configuration: ' . $e->getMessage()
            ];
        }
    }
}
