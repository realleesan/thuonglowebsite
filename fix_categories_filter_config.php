<?php
/**
 * Fix Categories Filter Config Records
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fix Categories Filter Config</h1>";

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    echo "<h2>Step 1: Get Active Categories</h2>";
    $result = $db->query("SELECT id, name, parent_id, sort_order FROM categories WHERE status = 'active' AND show_in_filter = 1 ORDER BY parent_id, sort_order, name");
    
    if (empty($result)) {
        echo "❌ No active categories found<br>";
        exit;
    }
    
    echo "Found " . count($result) . " active categories<br>";
    
    echo "<h2>Step 2: Insert Filter Config Records</h2>";
    
    $insert_count = 0;
    foreach ($result as $category) {
        // Check if record already exists
        $check = $db->query(
            "SELECT id FROM filter_config WHERE criteria_type = 'categories' AND item_id = ?",
            [$category['id']]
        );
        
        if (empty($check)) {
            // Insert new record
            $parent_id = $category['parent_id'] ?? 0; // Handle null parent_id
            $sort_order = $category['sort_order'] ?? 0; // Handle null sort_order
            $db->query(
                "INSERT INTO filter_config (criteria_type, item_id, parent_id, sort_order, is_enabled, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                ['categories', $category['id'], $parent_id, $sort_order, 1]
            );
            
            echo "✅ Added: " . htmlspecialchars($category['name']) . " (ID: " . $category['id'] . ")<br>";
            $insert_count++;
        } else {
            echo "⚠️ Exists: " . htmlspecialchars($category['name']) . " (ID: " . $category['id'] . ")<br>";
        }
    }
    
    echo "<h2>Step 3: Update Filter Settings</h2>";
    
    // Check if categories criteria exists in filter_settings
    $check = $db->query(
        "SELECT id FROM filter_settings WHERE type = 'categories'"
    );
    
    if (empty($check)) {
        // Insert categories criteria settings
        $db->query(
            "INSERT INTO filter_settings (type, is_enabled, sort_order, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
            ['categories', 1, 1]
        );
        echo "✅ Added categories criteria settings<br>";
    } else {
        echo "⚠️ Categories criteria settings already exist<br>";
    }
    
    echo "<h2>Step 4: Verify Results</h2>";
    
    $result = $db->query("SELECT COUNT(*) as total FROM filter_config WHERE criteria_type = 'categories'");
    echo "Filter config records for categories: " . $result[0]['total'] . "<br>";
    
    $result = $db->query("SELECT COUNT(*) as total FROM filter_config WHERE criteria_type = 'categories' AND is_enabled = 1");
    echo "Enabled categories in filter_config: " . $result[0]['total'] . "<br>";
    
    echo "<h2>✅ Fix Completed!</h2>";
    echo "<p>Inserted $insert_count new category filter records.</p>";
    echo "<p>Now test the products page: <a href='/?page=products'>Products Page</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
