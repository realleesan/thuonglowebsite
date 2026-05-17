<?php
/**
 * Clean up duplicate filter_config records
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Cleaning Filter Config Duplicates</h1>";

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    echo "<h2>Before Cleanup</h2>";
    
    // Check current state
    $result = $db->query("SELECT COUNT(*) as total FROM filter_config");
    echo "Total records: " . $result[0]['total'] . "<br>";
    
    $result = $db->query("SELECT COUNT(DISTINCT CONCAT(criteria_type, '-', item_id, '-', parent_id)) as unique_combos FROM filter_config");
    echo "Unique combinations: " . $result[0]['unique_combos'] . "<br>";
    
    // Show duplicates
    $result = $db->query("
        SELECT criteria_type, item_id, parent_id, COUNT(*) as count 
        FROM filter_config 
        GROUP BY criteria_type, item_id, parent_id 
        HAVING COUNT(*) > 1 
        ORDER BY count DESC
        LIMIT 10
    ");
    
    echo "<h3>Top Duplicates:</h3>";
    foreach ($result as $row) {
        echo "- {$row['criteria_type']}-{$row['item_id']}-{$row['parent_id']}: {$row['count']} copies<br>";
    }
    
    echo "<h2>Cleaning...</h2>";
    
    // Create backup
    $backup_table = "filter_config_backup_" . date('Y_m_d_H_i_s');
    $db->query("CREATE TABLE $backup_table AS SELECT * FROM filter_config");
    echo "✅ Created backup: $backup_table<br>";
    
    // Delete duplicates keeping only the latest (highest id)
    $db->query("
        DELETE t1 FROM filter_config t1
        INNER JOIN filter_config t2 
        WHERE t1.id < t2.id 
        AND t1.criteria_type = t2.criteria_type 
        AND t1.item_id = t2.item_id 
        AND t1.parent_id = t2.parent_id
    ");
    
    echo "✅ Deleted duplicate records<br>";
    
    // Check after cleanup
    $result = $db->query("SELECT COUNT(*) as total FROM filter_config");
    echo "Records after cleanup: " . $result[0]['total'] . "<br>";
    
    // Also clean up filter_settings if needed
    $result = $db->query("SELECT COUNT(*) as total FROM filter_settings");
    echo "Filter settings records: " . $result[0]['total'] . "<br>";
    
    // Reset to default configuration if needed
    echo "<h2>Reset to Default Configuration</h2>";
    
    // Clear existing config
    $db->query("DELETE FROM filter_config");
    $db->query("DELETE FROM filter_settings");
    echo "✅ Cleared existing configuration<br>";
    
    // Insert default criteria order
    $default_criteria = [
        ['categories', 1],
        ['brands', 2], 
        ['price_ranges', 3]
    ];
    
    foreach ($default_criteria as $criteria) {
        $db->query("INSERT INTO filter_settings (setting_key, setting_value) VALUES (?, ?)", 
            ["criteria_order_{$criteria[0]}", $criteria[1]]);
        $db->query("INSERT INTO filter_settings (setting_key, setting_value) VALUES (?, ?)", 
            ["criteria_enabled_{$criteria[0]}", "1"]);
    }
    
    echo "✅ Inserted default criteria settings<br>";
    
    echo "<h2>✅ Cleanup Completed!</h2>";
    echo "<p>You can now test the products page again.</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
