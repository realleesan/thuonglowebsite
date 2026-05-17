<?php
/**
 * Clean up duplicate filter_config records
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Cleanup Filter Config Duplicates</h1>";

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    echo "<h2>1. Count current records</h2>";
    $result = $db->query("SELECT COUNT(*) as count FROM filter_config");
    $count = $result[0]['count'];
    echo "Total records: $count<br>";
    
    echo "<h2>2. Show duplicates by criteria_type and item_id</h2>";
    $result = $db->query("SELECT criteria_type, item_id, COUNT(*) as dup_count 
                          FROM filter_config 
                          GROUP BY criteria_type, item_id 
                          HAVING COUNT(*) > 1 
                          ORDER BY dup_count DESC");
    
    echo "Found " . count($result) . " duplicate groups<br>";
    echo "<table border='1'>";
    echo "<tr><th>Criteria Type</th><th>Item ID</th><th>Count</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['criteria_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['dup_count']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>3. Delete duplicates (keep the latest record)</h2>";
    
    // Create temp table to keep latest records
    $db->query("CREATE TEMPORARY TABLE temp_keep_records AS
                SELECT MAX(id) as keep_id, criteria_type, item_id
                FROM filter_config 
                GROUP BY criteria_type, item_id");
    
    // Delete duplicates
    $delete_sql = "DELETE FROM filter_config 
                   WHERE id NOT IN (SELECT keep_id FROM temp_keep_records)";
    $result = $db->query($delete_sql);
    
    echo "Deleted duplicates<br>";
    
    // Drop temp table
    $db->query("DROP TEMPORARY TABLE temp_keep_records");
    
    echo "<h2>4. Count after cleanup</h2>";
    $result = $db->query("SELECT COUNT(*) as count FROM filter_config");
    $count = $result[0]['count'];
    echo "Total records after cleanup: $count<br>";
    
    echo "<h2>5. Show current records</h2>";
    $result = $db->query("SELECT criteria_type, item_id, sort_order, is_enabled 
                          FROM filter_config 
                          ORDER BY criteria_type, sort_order");
    
    echo "<table border='1'>";
    echo "<tr><th>Criteria Type</th><th>Item ID</th><th>Sort Order</th><th>Enabled</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['criteria_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sort_order']) . "</td>";
        echo "<td>" . htmlspecialchars($row['is_enabled']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>✅ Cleanup completed!</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
