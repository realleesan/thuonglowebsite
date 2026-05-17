<?php
/**
 * Check filter_config table structure
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Filter Config Table Structure</h1>";

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    echo "<h2>1. Table Structure</h2>";
    $result = $db->query("DESCRIBE filter_config");
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>2. Indexes</h2>";
    $result = $db->query("SHOW INDEX FROM filter_config");
    
    echo "<table border='1'>";
    echo "<tr><th>Table</th><th>Non_unique</th><th>Key_name</th><th>Seq_in_index</th><th>Column_name</th><th>Collation</th><th>Cardinality</th><th>Sub_part</th><th>Packed</th><th>Null</th><th>Index_type</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Table']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Non_unique']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Seq_in_index']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Column_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Collation']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Cardinality']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Sub_part']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Packed']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Index_type']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>3. Sample Records</h2>";
    $result = $db->query("SELECT * FROM filter_config LIMIT 5");
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Criteria Type</th><th>Item ID</th><th>Parent ID</th><th>Sort Order</th><th>Is Enabled</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['criteria_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['parent_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sort_order']) . "</td>";
        echo "<td>" . htmlspecialchars($row['is_enabled']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
