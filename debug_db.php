<?php
require_once 'core/database.php';

try {
    $db = Database::getInstance();
    
    echo "1. Connection test: ";
    if ($db->testConnection()) {
        echo "OK\n";
    } else {
        echo "FAILED\n";
        exit;
    }
    
    echo "2. Database info:\n";
    $info = $db->getInfo();
    print_r($info);
    
    echo "\n3. Show tables:\n";
    $tables = $db->query("SHOW TABLES");
    foreach ($tables as $table) {
        echo "- " . array_values($table)[0] . "\n";
    }
    
    echo "\n4. Test table operations:\n";
    
    // Create test table
    $db->execute("DROP TABLE IF EXISTS debug_test");
    $db->execute("CREATE TABLE debug_test (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100))");
    echo "Table created\n";
    
    // Insert
    $id = $db->table('debug_test')->insert(['name' => 'Test Item']);
    echo "Inserted ID: $id\n";
    
    // Select all
    $all = $db->table('debug_test')->get();
    echo "All records: " . count($all) . "\n";
    
    // Find by ID
    $found = $db->table('debug_test')->find($id);
    echo "Found by ID: " . ($found ? $found['name'] : 'Not found') . "\n";
    
    // Update
    $db->table('debug_test')->where('id', $id)->update(['name' => 'Updated Item']);
    echo "Updated\n";
    
    // Check update
    $updated = $db->table('debug_test')->find($id);
    echo "After update: " . ($updated ? $updated['name'] : 'Not found') . "\n";
    
    // Delete
    $db->table('debug_test')->where('id', $id)->delete();
    echo "Deleted\n";
    
    // Check delete
    $remaining = $db->table('debug_test')->get();
    echo "Remaining records: " . count($remaining) . "\n";
    
    // Clean up
    $db->execute("DROP TABLE debug_test");
    echo "Table dropped\n";
    
    echo "\nAll tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>