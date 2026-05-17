<?php
/**
 * Add unique key to filter_config table
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Add Unique Key to Filter Config Table</h1>";

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    echo "<h2>1. Current indexes</h2>";
    $result = $db->query("SHOW INDEX FROM filter_config");
    
    echo "<table border='1'>";
    echo "<tr><th>Table</th><th>Non_unique</th><th>Key_name</th><th>Seq_in_index</th><th>Column_name</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Table']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Non_unique']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Seq_in_index']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Column_name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>2. Add unique key on (criteria_type, item_id)</h2>";
    
    // Add unique key
    $sql = "ALTER TABLE filter_config ADD UNIQUE KEY uk_criteria_item (criteria_type, item_id)";
    
    try {
        $db->query($sql);
        echo "✅ Unique key added successfully!<br>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "⚠️ Unique key already exists<br>";
        } else {
            echo "❌ Error adding unique key: " . $e->getMessage() . "<br>";
            
            // Check if there are duplicates that need to be cleaned up first
            echo "<h3>3. Check for duplicates</h3>";
            $dupSql = "SELECT criteria_type, item_id, COUNT(*) as dup_count 
                      FROM filter_config 
                      GROUP BY criteria_type, item_id 
                      HAVING COUNT(*) > 1";
            
            $dupResult = $db->query($dupSql);
            
            if (!empty($dupResult)) {
                echo "<p>❌ Found duplicates that need to be cleaned up first:</p>";
                echo "<table border='1'>";
                echo "<tr><th>Criteria Type</th><th>Item ID</th><th>Duplicate Count</th></tr>";
                foreach ($dupResult as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['criteria_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['dup_count']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                echo "<h3>4. Clean up duplicates</h3>";
                echo "<p>Running cleanup to remove duplicates...</p>";
                
                // Create temp table to keep latest records
                $db->query("CREATE TEMPORARY TABLE temp_keep_records AS
                           SELECT MAX(id) as keep_id, criteria_type, item_id
                           FROM filter_config 
                           GROUP BY criteria_type, item_id");
                
                // Delete duplicates
                $deleteSql = "DELETE FROM filter_config 
                              WHERE id NOT IN (SELECT keep_id FROM temp_keep_records)";
                $db->query($deleteSql);
                
                // Drop temp table
                $db->query("DROP TEMPORARY TABLE temp_keep_records");
                
                echo "✅ Duplicates cleaned up!<br>";
                
                // Try adding unique key again
                try {
                    $db->query($sql);
                    echo "✅ Unique key added successfully after cleanup!<br>";
                } catch (Exception $e2) {
                    echo "❌ Still error: " . $e2->getMessage() . "<br>";
                }
            } else {
                echo "<p>No duplicates found</p>";
            }
        }
    }
    
    echo "<h2>3. Final indexes</h2>";
    $result = $db->query("SHOW INDEX FROM filter_config");
    
    echo "<table border='1'>";
    echo "<tr><th>Table</th><th>Non_unique</th><th>Key_name</th><th>Seq_in_index</th><th>Column_name</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Table']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Non_unique']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Seq_in_index']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Column_name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>✅ Process completed!</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
