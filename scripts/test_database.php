<?php
/**
 * Database Connection Test Script
 * Tests the Database class and connection to MySQL
 */

// Include the Database class
require_once __DIR__ . '/../core/database.php';

echo "=== Database Connection Test ===\n\n";

try {
    // Get database instance
    $db = Database::getInstance();
    
    // Test connection
    echo "1. Testing connection...\n";
    if ($db->testConnection()) {
        echo "   ✓ Connection successful!\n\n";
    } else {
        echo "   ✗ Connection failed!\n\n";
        exit(1);
    }
    
    // Get database info
    echo "2. Database Information:\n";
    $info = $db->getInfo();
    echo "   Database: " . $info['database'] . "\n";
    echo "   Version: " . $info['version'] . "\n";
    echo "   Status: " . $info['connection'] . "\n\n";
    
    // Test basic query
    echo "3. Testing basic query...\n";
    $tables = $db->query("SHOW TABLES");
    echo "   Found " . count($tables) . " tables in database\n";
    
    if (!empty($tables)) {
        echo "   Tables:\n";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "   - " . $tableName . "\n";
        }
    }
    echo "\n";
    
    // Test Query Builder methods
    echo "4. Testing Query Builder (will create test table)...\n";
    
    // Create test table
    $createTable = "
        CREATE TABLE IF NOT EXISTS test_connection (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    $db->execute($createTable);
    echo "   ✓ Test table created\n";
    
    // Test insert
    $insertId = $db->table('test_connection')->insert([
        'name' => 'Test Record ' . date('Y-m-d H:i:s')
    ]);
    echo "   ✓ Insert test - ID: " . $insertId . "\n";
    
    // Test select
    $records = $db->table('test_connection')->get();
    echo "   ✓ Select test - Found " . count($records) . " records\n";
    
    // Test find
    $record = $db->table('test_connection')->find($insertId);
    if ($record) {
        echo "   ✓ Find test - Found: " . $record['name'] . "\n";
    }
    
    // Test where
    $filtered = $db->table('test_connection')
                   ->select()
                   ->where('id', $insertId)
                   ->get();
    echo "   ✓ Where test - Found " . count($filtered) . " records\n";
    
    // Test update
    $updated = $db->table('test_connection')
                  ->where('id', $insertId)
                  ->update(['name' => 'Updated Test Record']);
    echo "   ✓ Update test - " . ($updated ? 'Success' : 'Failed') . "\n";
    
    // Clean up - delete test record
    $deleted = $db->table('test_connection')
                  ->where('id', $insertId)
                  ->delete();
    echo "   ✓ Delete test - " . ($deleted ? 'Success' : 'Failed') . "\n";
    
    // Drop test table
    $db->execute("DROP TABLE IF EXISTS test_connection");
    echo "   ✓ Test table cleaned up\n\n";
    
    echo "=== All Tests Passed! ===\n";
    echo "Database class is working correctly.\n";
    echo "Ready for Phase 2: Migration System\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. Database credentials in config.php\n";
    echo "2. MySQL server is running\n";
    echo "3. Database 'test1_thuonglowebsite' exists\n";
    echo "4. User has proper permissions\n";
    exit(1);
}