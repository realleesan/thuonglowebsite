<?php
/**
 * Debug getParentCategoriesForFilter
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Parent Categories</h1>";

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    echo "<h2>1. Check all parent categories in database</h2>";
    $result = $db->query("SELECT id, name, parent_id, status, show_in_filter FROM categories WHERE parent_id = 0 ORDER BY name");
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Parent ID</th><th>Status</th><th>Show in Filter</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['parent_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['show_in_filter']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>2. Test getParentCategoriesForFilter query</h2>";
    $sql = "SELECT c.id, c.name, c.parent_id, c.sort_order, COUNT(p.id) as product_count,
                   COALESCE(fc.sort_order, c.sort_order, 999) as filter_sort_order,
                   COALESCE(fc.is_enabled, 1) as filter_enabled
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            LEFT JOIN filter_config fc ON fc.criteria_type = 'categories' AND fc.item_id = c.id
            WHERE c.status = 'active' AND c.show_in_filter = 1 AND c.parent_id = 0
            GROUP BY c.id
            ORDER BY filter_sort_order ASC, c.name ASC";
    
    $result = $db->query($sql);
    
    echo "Query result count: " . count($result) . "<br>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Parent ID</th><th>Sort Order</th><th>Product Count</th><th>Filter Sort Order</th><th>Filter Enabled</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['parent_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sort_order']) . "</td>";
        echo "<td>" . htmlspecialchars($row['product_count']) . "</td>";
        echo "<td>" . htmlspecialchars($row['filter_sort_order']) . "</td>";
        echo "<td>" . htmlspecialchars($row['filter_enabled']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>3. Test FilterConfigService::getParentCategoriesForFilter</h2>";
    
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    
    $categories = $filterService->getParentCategoriesForFilter();
    
    echo "Service result count: " . count($categories) . "<br>";
    echo "<pre>" . print_r($categories, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
