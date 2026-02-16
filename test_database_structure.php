<?php
/**
 * Test file để kiểm tra cấu trúc database cho agent registration
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Database Structure cho Agent Registration</h1>\n";
echo "<pre>\n";

try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = Database::getInstance();
    echo "✓ Database connected\n";
    
    // Check users table structure
    echo "\n=== USERS TABLE STRUCTURE ===\n";
    $usersColumns = $db->query("DESCRIBE users");
    if ($usersColumns) {
        foreach ($usersColumns as $column) {
            echo sprintf("%-25s %-15s %-10s %-10s\n", 
                $column['Field'], 
                $column['Type'], 
                $column['Null'], 
                $column['Key']
            );
        }
        
        // Check for agent-related fields
        $agentFields = ['agent_request_status', 'agent_request_date'];
        echo "\nChecking agent-related fields:\n";
        foreach ($agentFields as $field) {
            $found = false;
            foreach ($usersColumns as $column) {
                if ($column['Field'] === $field) {
                    echo "✓ {$field} - EXISTS\n";
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo "✗ {$field} - MISSING\n";
            }
        }
    } else {
        echo "✗ Could not get users table structure\n";
    }
    
    // Check affiliates table structure
    echo "\n=== AFFILIATES TABLE STRUCTURE ===\n";
    $affiliatesColumns = $db->query("DESCRIBE affiliates");
    if ($affiliatesColumns) {
        foreach ($affiliatesColumns as $column) {
            echo sprintf("%-25s %-15s %-10s %-10s\n", 
                $column['Field'], 
                $column['Type'], 
                $column['Null'], 
                $column['Key']
            );
        }
    } else {
        echo "✗ Could not get affiliates table structure\n";
    }
    
    // Test sample data insertion
    echo "\n=== TEST SAMPLE DATA INSERTION ===\n";
    
    // Test users table insert
    $testUserData = [
        'name' => 'Test Agent User',
        'username' => 'testagent_' . time(),
        'email' => 'testagent_' . time() . '@gmail.com',
        'phone' => '0123456789',
        'password' => password_hash('TestPassword123!', PASSWORD_DEFAULT),
        'agent_request_status' => 'pending',
        'agent_request_date' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Build insert query
    $fields = array_keys($testUserData);
    $placeholders = array_fill(0, count($fields), '?');
    $sql = "INSERT INTO users (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    echo "Testing users insert query:\n";
    echo $sql . "\n";
    
    try {
        $userId = $db->query($sql, array_values($testUserData));
        if ($userId) {
            echo "✓ Test user inserted successfully with ID: {$userId}\n";
            
            // Test affiliates table insert
            $testAffiliateData = [
                'user_id' => $userId,
                'status' => 'pending',
                'additional_info' => json_encode([
                    'registration_source' => 'new_user_form',
                    'requested_at' => date('Y-m-d H:i:s')
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $affiliateFields = array_keys($testAffiliateData);
            $affiliatePlaceholders = array_fill(0, count($affiliateFields), '?');
            $affiliateSql = "INSERT INTO affiliates (" . implode(', ', $affiliateFields) . ") VALUES (" . implode(', ', $affiliatePlaceholders) . ")";
            
            echo "\nTesting affiliates insert query:\n";
            echo $affiliateSql . "\n";
            
            $affiliateId = $db->query($affiliateSql, array_values($testAffiliateData));
            if ($affiliateId) {
                echo "✓ Test affiliate inserted successfully with ID: {$affiliateId}\n";
                
                // Clean up test data
                echo "\nCleaning up test data...\n";
                $db->query("DELETE FROM affiliates WHERE id = ?", [$affiliateId]);
                $db->query("DELETE FROM users WHERE id = ?", [$userId]);
                echo "✓ Test data cleaned up\n";
                
            } else {
                echo "✗ Failed to insert test affiliate\n";
            }
        } else {
            echo "✗ Failed to insert test user\n";
        }
    } catch (Exception $e) {
        echo "✗ Insert test failed: " . $e->getMessage() . "\n";
    }
    
    // Check for any existing agent registrations
    echo "\n=== EXISTING AGENT REGISTRATIONS ===\n";
    $existingAgents = $db->query("SELECT COUNT(*) as count FROM users WHERE agent_request_status IS NOT NULL");
    if ($existingAgents) {
        echo "Users with agent requests: " . $existingAgents[0]['count'] . "\n";
    }
    
    $existingAffiliates = $db->query("SELECT COUNT(*) as count FROM affiliates");
    if ($existingAffiliates) {
        echo "Total affiliates: " . $existingAffiliates[0]['count'] . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database test failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== END DATABASE TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>\n";
?>