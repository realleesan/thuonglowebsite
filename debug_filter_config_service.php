<?php
/**
 * Debug FilterConfigService After Fix
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>FilterConfigService Debug After Fix</h1>";

try {
    require_once __DIR__ . '/core/view_init.php';
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    
    $filterService = new FilterConfigService();
    
    echo "<h2>1. getFilterConfig() Result</h2>";
    $config_result = $filterService->getFilterConfig();
    
    echo "Success: " . ($config_result['success'] ? 'yes' : 'no') . "<br>";
    
    if ($config_result['success']) {
        $filter_config = $config_result['data'];
        
        echo "<h3>Criteria Settings:</h3>";
        echo "<pre>" . print_r($filter_config['criteria'], true) . "</pre>";
        
        echo "<h3>Categories Enabled: " . ($filter_config['criteria']['categories']['enabled'] ?? 'NOT SET') . "</h3>";
        echo "<h3>Categories Order: " . ($filter_config['criteria']['categories']['order'] ?? 'NOT SET') . "</h3>";
    } else {
        echo "Error: " . $config_result['message'] . "<br>";
    }
    
    echo "<h2>2. getCategoriesForFilter() Result</h2>";
    $categories = $filterService->getCategoriesForFilter();
    
    echo "Total categories: " . count($categories) . "<br>";
    
    if (!empty($categories)) {
        echo "<h3>First 3 Categories:</h3>";
        for ($i = 0; $i < min(3, count($categories)); $i++) {
            $cat = $categories[$i];
            echo "ID: " . $cat['id'] . ", Name: " . htmlspecialchars($cat['name']) . 
                 ", Enabled: " . ($cat['enabled'] ? 'yes' : 'no') . 
                 ", Count: " . ($cat['count'] ?? 0) . "<br>";
        }
    }
    
    echo "<h2>3. Raw Filter Settings Check</h2>";
    $db = Database::getInstance();
    
    $result = $db->query("SELECT setting_key, setting_value FROM filter_settings WHERE setting_key LIKE 'criteria_%' ORDER BY setting_key");
    
    echo "<table border='1'>";
    echo "<tr><th>Setting Key</th><th>Setting Value</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['setting_key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['setting_value']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>4. Template Variables Test</h2>";
    
    // Test what template variables would be
    if ($config_result['success']) {
        $filter_config = $config_result['data'];
        
        $criteria_order = [
            'categories' => $filter_config['criteria']['categories']['order'] ?? 1,
            'brands' => $filter_config['criteria']['brands']['order'] ?? 2, 
            'price_ranges' => $filter_config['criteria']['price_ranges']['order'] ?? 3
        ];
        
        $criteria_enabled = [
            'categories' => $filter_config['criteria']['categories']['enabled'] ?? true,
            'brands' => $filter_config['criteria']['brands']['enabled'] ?? true,
            'price_ranges' => $filter_config['criteria']['price_ranges']['enabled'] ?? true
        ];
        
        echo "criteria_order: <pre>" . print_r($criteria_order, true) . "</pre>";
        echo "criteria_enabled: <pre>" . print_r($criteria_enabled, true) . "</pre>";
        
        echo "Categories should show: " . ($criteria_enabled['categories'] ? 'YES' : 'NO') . "<br>";
        echo "Categories order: " . $criteria_order['categories'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
