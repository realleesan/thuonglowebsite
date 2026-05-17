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
        foreach ($criteria as $criterion) {
            $name = $criterion['name'];
            $order = $criterion['order'];
            $enabled = $criterion['enabled'];
            
            // Save individual criteria order
            $this->saveSetting('criteria_order_' . $name, $order);
            
            // Save individual criteria enabled status
            $this->saveSetting('criteria_enabled_' . $name, $enabled);
        }
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
            $item['parent_id'] ?? 0,  // Default to 0 if null
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
        
        // Get individual criteria settings from filter_settings
        $sql = "SELECT setting_key, setting_value FROM filter_settings 
                WHERE setting_key LIKE 'criteria_%' ORDER BY setting_key";
        $results = $this->db->query($sql);
        
        foreach ($results as $row) {
            $key = $row['setting_key'];
            $value = $row['setting_value'];
            
            // Parse setting key
            if (strpos($key, 'criteria_order_') === 0) {
                $criteriaName = substr($key, 15); // Remove 'criteria_order_' (15 chars)
                if (!isset($criteria[$criteriaName])) {
                    $criteria[$criteriaName] = [];
                }
                $criteria[$criteriaName]['order'] = (int)$value;
            } elseif (strpos($key, 'criteria_enabled_') === 0) {
                $criteriaName = substr($key, 17); // Remove 'criteria_enabled_' (17 chars)
                if (!isset($criteria[$criteriaName])) {
                    $criteria[$criteriaName] = [];
                }
                $criteria[$criteriaName]['enabled'] = (bool)$value;
            }
        }
        
        // Set defaults if not found
        $defaultCriteria = ['categories', 'brands', 'price_ranges'];
        foreach ($defaultCriteria as $criterion) {
            if (!isset($criteria[$criterion])) {
                $criteria[$criterion] = [
                    'order' => 999,
                    'enabled' => true
                ];
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
            // Get categories with their filter config sort order
            $sql = "SELECT c.id, c.name, c.parent_id, c.sort_order, COUNT(p.id) as product_count,
                           COALESCE(fc.sort_order, c.sort_order, 999) as filter_sort_order,
                           COALESCE(fc.is_enabled, 1) as filter_enabled
                    FROM categories c
                    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                    LEFT JOIN filter_config fc ON fc.criteria_type = 'categories' AND fc.item_id = c.id
                    WHERE c.status = 'active' AND c.show_in_filter = 1
                    GROUP BY c.id
                    ORDER BY c.parent_id, filter_sort_order ASC, c.name ASC";
            
            $results = $this->db->query($sql);
            
            $categories = [];
            $categoryMap = [];
            
            // First pass: create category map and return ALL categories
            foreach ($results as $row) {
                $category = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'parent_id' => (int)$row['parent_id'],
                    'sort_order' => (int)($row['filter_sort_order'] ?? 999),
                    'count' => (int)$row['product_count'],
                    'enabled' => (bool)($row['filter_enabled'] ?? 1),
                    'children' => []
                ];
                
                $categoryMap[$category['id']] = $category;
                $categories[] = $category; // Add ALL categories, not just parents
            }
            
            // Second pass: build hierarchy for reference (optional)
            foreach ($categoryMap as $id => $category) {
                if ($category['parent_id'] > 0 && isset($categoryMap[$category['parent_id']])) {
                    $categoryMap[$category['parent_id']]['children'][] = &$categoryMap[$id];
                }
            }
            
            // Third pass: sort children by filter_sort_order (from filter_config table)
            $sortChildren = function(&$nodes) use (&$sortChildren) {
                foreach ($nodes as &$node) {
                    if (!empty($node['children'])) {
                        usort($node['children'], function ($a, $b) {
                            $sortA = (int)($a['sort_order'] ?? 999);
                            $sortB = (int)($b['sort_order'] ?? 999);
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
     * Get categories with hierarchy for admin filter configuration
     */
    public function getParentCategoriesForFilter(): array {
        try {
            // Get ALL categories with their filter config sort order
            $sql = "SELECT c.id, c.name, c.parent_id, c.sort_order, COUNT(p.id) as product_count,
                           COALESCE(fc.sort_order, c.sort_order, 999) as filter_sort_order,
                           COALESCE(fc.is_enabled, 1) as filter_enabled
                    FROM categories c
                    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                    LEFT JOIN filter_config fc ON fc.criteria_type = 'categories' AND fc.item_id = c.id
                    WHERE c.status = 'active' AND c.show_in_filter = 1
                    GROUP BY c.id
                    ORDER BY c.parent_id, filter_sort_order ASC, c.name ASC";
            
            $results = $this->db->query($sql);
            
            $categoryMap = [];
            $rootCategories = [];
            
            // First pass: create category map
            foreach ($results as $row) {
                $category = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'parent_id' => (int)$row['parent_id'],
                    'sort_order' => (int)($row['filter_sort_order'] ?? 999),
                    'count' => (int)$row['product_count'],
                    'enabled' => (bool)($row['filter_enabled'] ?? 1),
                    'children' => []
                ];
                
                $categoryMap[$category['id']] = $category;
                
                // Track root categories (parent_id = 0 or NULL)
                if ($category['parent_id'] == 0) {
                    $rootCategories[] = &$categoryMap[$category['id']];
                }
            }
            
            // Second pass: build hierarchy
            foreach ($categoryMap as $id => $category) {
                if ($category['parent_id'] > 0 && isset($categoryMap[$category['parent_id']])) {
                    $categoryMap[$category['parent_id']]['children'][] = &$categoryMap[$id];
                }
            }
            
            return $rootCategories;
            
        } catch (Exception $e) {
            error_log('FilterConfigService::getParentCategoriesForFilter error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get brands for filter configuration
     */
    public function getBrandsForFilter(): array {
        try {
            // Get brands with their filter config sort order
            $sql = "SELECT b.id, b.name, b.sort_order, COUNT(p.id) as product_count,
                           COALESCE(fc.sort_order, b.sort_order, 999) as filter_sort_order,
                           COALESCE(fc.is_enabled, 1) as filter_enabled
                    FROM brands b
                    LEFT JOIN products p ON b.id = p.brand_id AND p.status = 'active'
                    LEFT JOIN filter_config fc ON fc.criteria_type = 'brands' AND fc.item_id = b.id
                    WHERE b.status = 'active' AND b.show_in_filter = 1
                    GROUP BY b.id
                    ORDER BY filter_sort_order ASC, b.name ASC";
            
            $results = $this->db->query($sql);
            
            $brands = [];
            foreach ($results as $row) {
                $brands[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'sort_order' => (int)($row['filter_sort_order'] ?? 999),
                    'count' => (int)$row['product_count'],
                    'enabled' => (bool)($row['filter_enabled'] ?? 1)
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
            // Get price ranges with their filter config sort order
            $sql = "SELECT pr.id, pr.name, pr.min_price, pr.max_price, pr.sort_order,
                           COALESCE(fc.sort_order, pr.sort_order, 999) as filter_sort_order,
                           COALESCE(fc.is_enabled, 1) as filter_enabled
                    FROM price_ranges pr
                    LEFT JOIN filter_config fc ON fc.criteria_type = 'price_ranges' AND fc.item_id = pr.id
                    ORDER BY filter_sort_order ASC, pr.name ASC";
            
            $results = $this->db->query($sql);
            
            if (!empty($results)) {
                return array_map(function($row) {
                    return [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'min' => (int)$row['min_price'],
                        'max' => $row['max_price'] ? (int)$row['max_price'] : null,
                        'sort_order' => (int)($row['filter_sort_order'] ?? 999),
                        'enabled' => (bool)($row['filter_enabled'] ?? 1)
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
