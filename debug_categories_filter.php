<?php
/**
 * Debug Categories Filter Data
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Categories Filter Debug</h1>";

try {
    require_once __DIR__ . '/core/view_init.php';
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    
    $filterService = new FilterConfigService();
    
    echo "<h2>1. Filter Config Status</h2>";
    $config_result = $filterService->getFilterConfig();
    $filter_config = $config_result['success'] ? $config_result['data'] : [];
    
    echo "Config success: " . ($config_result['success'] ? 'yes' : 'no') . "<br>";
    echo "Categories enabled: " . ($filter_config['criteria']['categories']['enabled'] ?? 'not set') . "<br>";
    echo "Categories order: " . ($filter_config['criteria']['categories']['order'] ?? 'not set') . "<br>";
    
    echo "<h2>2. Categories from FilterConfigService</h2>";
    $categories = $filterService->getCategoriesForFilter();
    echo "Total categories: " . count($categories) . "<br>";
    
    if (!empty($categories)) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Parent</th><th>Enabled</th><th>Sort Order</th><th>Product Count</th></tr>";
        foreach ($categories as $cat) {
            echo "<tr>";
            echo "<td>" . $cat['id'] . "</td>";
            echo "<td>" . htmlspecialchars($cat['name']) . "</td>";
            echo "<td>" . $cat['parent_id'] . "</td>";
            echo "<td>" . ($cat['enabled'] ? 'yes' : 'no') . "</td>";
            echo "<td>" . $cat['sort_order'] . "</td>";
            echo "<td>" . ($cat['count'] ?? 0) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No categories found<br>";
    }
    
    echo "<h2>3. Raw Database Check</h2>";
    $db = Database::getInstance();
    
    // Check categories table
    $result = $db->query("SELECT COUNT(*) as total FROM categories WHERE status = 'active' AND show_in_filter = 1");
    echo "Active categories in DB: " . $result[0]['total'] . "<br>";
    
    // Check filter_config for categories
    $result = $db->query("SELECT COUNT(*) as total FROM filter_config WHERE criteria_type = 'categories'");
    echo "Filter config records for categories: " . $result[0]['total'] . "<br>";
    
    // Check enabled categories in filter_config
    $result = $db->query("SELECT COUNT(*) as total FROM filter_config WHERE criteria_type = 'categories' AND is_enabled = 1");
    echo "Enabled categories in filter_config: " . $result[0]['total'] . "<br>";
    
    echo "<h2>4. Sample Filter Config Records</h2>";
    $result = $db->query("SELECT * FROM filter_config WHERE criteria_type = 'categories' LIMIT 5");
    if (!empty($result)) {
        echo "<table border='1'>";
        echo "<tr><th>Criteria Type</th><th>Item ID</th><th>Parent ID</th><th>Enabled</th><th>Sort Order</th></tr>";
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>" . $row['criteria_type'] . "</td>";
            echo "<td>" . $row['item_id'] . "</td>";
            echo "<td>" . $row['parent_id'] . "</td>";
            echo "<td>" . ($row['is_enabled'] ? 'yes' : 'no') . "</td>";
            echo "<td>" . $row['sort_order'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No filter config records found<br>";
    }
    
    echo "<h2>5. Fix Suggestions</h2>";
    if (empty($categories)) {
        echo "❌ Categories filter is empty. Possible causes:<br>";
        echo "- All categories are disabled in filter_config<br>";
        echo "- No categories found in database<br>";
        echo "- FilterConfigService query issue<br>";
        echo "<br><strong>Solution: Re-enable categories in admin filter config</strong><br>";
    } else {
        echo "✅ Categories data exists but might be disabled in template<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
