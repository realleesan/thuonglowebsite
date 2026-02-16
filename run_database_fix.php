<?php
/**
 * Script để thêm cột additional_info vào bảng affiliates
 */

echo "<h1>Fix Database - Add additional_info Column</h1>\n";
echo "<pre>\n";

try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = Database::getInstance();
    echo "✓ Database connected\n";
    
    // Check if column already exists
    echo "\nChecking if additional_info column exists...\n";
    $columns = $db->query("DESCRIBE affiliates");
    $hasColumn = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'additional_info') {
            $hasColumn = true;
            break;
        }
    }
    
    if ($hasColumn) {
        echo "✓ Column additional_info already exists\n";
    } else {
        echo "✗ Column additional_info does not exist, adding it...\n";
        
        // Add the column
        $sql = "ALTER TABLE affiliates ADD COLUMN additional_info LONGTEXT NULL AFTER payment_details";
        $result = $db->query($sql);
        
        if ($result !== false) {
            echo "✓ Column additional_info added successfully\n";
        } else {
            echo "✗ Failed to add column additional_info\n";
            throw new Exception("Failed to add column");
        }
    }
    
    // Verify the column was added
    echo "\nVerifying column structure...\n";
    $columns = $db->query("DESCRIBE affiliates");
    
    echo "Current affiliates table structure:\n";
    foreach ($columns as $column) {
        echo sprintf("%-25s %-15s %-10s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null']
        );
    }
    
    // Test insert with additional_info
    echo "\nTesting insert with additional_info...\n";
    $testData = [
        'user_id' => 1, // Assuming user ID 1 exists
        'status' => 'pending',
        'additional_info' => json_encode([
            'test' => 'data',
            'registration_source' => 'test'
        ]),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $fields = array_keys($testData);
    $placeholders = array_fill(0, count($fields), '?');
    $sql = "INSERT INTO affiliates (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    try {
        $insertId = $db->query($sql, array_values($testData));
        if ($insertId) {
            echo "✓ Test insert successful with ID: {$insertId}\n";
            
            // Clean up test data
            $db->query("DELETE FROM affiliates WHERE id = ?", [$insertId]);
            echo "✓ Test data cleaned up\n";
        } else {
            echo "✗ Test insert failed\n";
        }
    } catch (Exception $e) {
        echo "✗ Test insert error: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Database fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Database fix failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== END DATABASE FIX ===\n";
echo "</pre>\n";
?>