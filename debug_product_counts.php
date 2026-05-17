<?php
/**
 * Debug Product Counts in Sidebar
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Product Counts</h1>";

try {
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    
    echo "<h2>1. Check Categories with Product Counts</h2>";
    $categories = $filterService->getCategoriesForFilter();
    
    echo "Categories count: " . count($categories) . "<br>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Parent ID</th><th>Product Count</th><th>Enabled</th></tr>";
    foreach ($categories as $category) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($category['id']) . "</td>";
        echo "<td>" . htmlspecialchars($category['name']) . "</td>";
        echo "<td>" . htmlspecialchars($category['parent_id']) . "</td>";
        echo "<td>" . htmlspecialchars($category['count']) . "</td>";
        echo "<td>" . htmlspecialchars($category['enabled'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>2. Check Brands with Product Counts</h2>";
    $brands = $filterService->getBrandsForFilter();
    
    echo "Brands count: " . count($brands) . "<br>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Product Count</th><th>Enabled</th></tr>";
    foreach ($brands as $brand) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($brand['id']) . "</td>";
        echo "<td>" . htmlspecialchars($brand['name']) . "</td>";
        echo "<td>" . htmlspecialchars($brand['count']) . "</td>";
        echo "<td>" . htmlspecialchars($brand['enabled'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>3. Check Products Table Status</h2>";
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    $result = $db->query("SELECT status, COUNT(*) as count FROM products GROUP BY status");
    echo "<table border='1'>";
    echo "<tr><th>Status</th><th>Count</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['count']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>4. Check Category-Product Relationships</h2>";
    $result = $db->query("SELECT c.id, c.name, COUNT(p.id) as product_count
                          FROM categories c
                          LEFT JOIN products p ON c.id = p.category_id
                          WHERE c.status = 'active' AND c.show_in_filter = 1
                          GROUP BY c.id
                          ORDER BY c.name
                          LIMIT 10");
    
    echo "<table border='1'>";
    echo "<tr><th>Category ID</th><th>Name</th><th>Product Count</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['product_count']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>5. Check Brand-Product Relationships</h2>";
    $result = $db->query("SELECT b.id, b.name, COUNT(p.id) as product_count
                          FROM brands b
                          LEFT JOIN products p ON b.id = p.brand_id
                          WHERE b.status = 'active' AND b.show_in_filter = 1
                          GROUP BY b.id
                          ORDER BY b.name
                          LIMIT 10");
    
    echo "<table border='1'>";
    echo "<tr><th>Brand ID</th><th>Name</th><th>Product Count</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['product_count']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
