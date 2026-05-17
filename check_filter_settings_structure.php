<?php
/**
 * Check Filter Settings Table Structure
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Filter Settings Table Structure</h1>";

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    echo "<h2>Table Structure</h2>";
    $result = $db->query("DESCRIBE filter_settings");
    
    if (!empty($result)) {
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Could not get table structure<br>";
    }
    
    echo "<h2>Current Data</h2>";
    $result = $db->query("SELECT * FROM filter_settings");
    
    if (!empty($result)) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th>";
        // Get column names from first row
        foreach ($result[0] as $key => $value) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            foreach ($row as $key => $value) {
                if ($key !== 'id') {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ No data in filter_settings table<br>";
    }
    
    echo "<h2>Fix Based on Structure</h2>";
    if (!empty($result)) {
        echo "✅ Table has data. Using correct column names...<br>";
        
        // Try to insert categories using correct column names
        $first_row = $result[0];
        $columns = array_keys($first_row);
        
        echo "Available columns: " . implode(', ', $columns) . "<br>";
        
        // Find the correct column name for criteria type
        $type_column = null;
        foreach ($columns as $col) {
            if (strpos($col, 'type') !== false || strpos($col, 'criteria') !== false) {
                $type_column = $col;
                break;
            }
        }
        
        if ($type_column) {
            echo "Found type column: $type_column<br>";
            
            // Check if categories exists
            $check = $db->query("SELECT id FROM filter_settings WHERE $type_column = 'categories'");
            
            if (empty($check)) {
                // Build insert query based on available columns
                $insert_columns = [$type_column, 'is_enabled', 'sort_order', 'created_at', 'updated_at'];
                $insert_values = ['categories', 1, 1, 'NOW()', 'NOW()'];
                
                $sql = "INSERT INTO filter_settings (" . implode(', ', $insert_columns) . ") VALUES (?, ?, ?, NOW(), NOW())";
                $db->query($sql, ['categories', 1, 1]);
                
                echo "✅ Added categories filter settings using column: $type_column<br>";
            } else {
                echo "⚠️ Categories filter settings already exist<br>";
            }
        } else {
            echo "❌ Could not find type/criteria column<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
