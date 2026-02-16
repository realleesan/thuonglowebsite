<?php
/**
 * Test cuối cùng để kiểm tra agent registration sau khi fix
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/final_test.log');

echo "<h1>Test Cuối Cùng - Agent Registration</h1>\n";
echo "<pre>\n";

try {
    require_once 'config.php';
    require_once 'core/database.php';
    require_once 'app/models/BaseModel.php';
    require_once 'app/models/UsersModel.php';
    require_once 'app/models/AffiliateModel.php';
    require_once 'app/services/AgentRegistrationService.php';
    require_once 'app/services/AgentRegistrationData.php';
    require_once 'app/services/SpamPreventionService.php';
    require_once 'app/services/EmailNotificationService.php';
    
    echo "✓ All files loaded successfully\n";

    // Test 1: Create service instance
    echo "\n=== TEST 1: Create AgentRegistrationService ===\n";
    $agentService = new AgentRegistrationService();
    echo "✓ AgentRegistrationService created\n";

    // Test 2: Test with sample data
    echo "\n=== TEST 2: Test registerNewUserAsAgent ===\n";
    
    $userData = [
        'name' => 'Test Final User',
        'username' => 'testfinal_' . time(),
        'email' => 'testfinal_' . time() . '@gmail.com',
        'phone' => '0123456789',
        'password' => 'TestPassword123!',
        'password_confirmation' => 'TestPassword123!',
        'ref_code' => ''
    ];
    
    $agentData = [
        'email' => $userData['email'],
        'additional_info' => [
            'registration_source' => 'new_user_form',
            'requested_at' => date('Y-m-d H:i:s')
        ]
    ];
    
    echo "Testing with data:\n";
    echo "- Name: " . $userData['name'] . "\n";
    echo "- Username: " . $userData['username'] . "\n";
    echo "- Email: " . $userData['email'] . "\n";
    
    $result = $agentService->registerNewUserAsAgent($userData, $agentData);
    
    echo "\nResult:\n";
    print_r($result);
    
    if ($result['success']) {
        echo "\n✓ SUCCESS: Agent registration completed!\n";
        
        // Clean up test data
        if (isset($result['user_id'])) {
            $usersModel = new UsersModel();
            $affiliateModel = new AffiliateModel();
            
            // Delete affiliate first (foreign key constraint)
            if (isset($result['affiliate_id'])) {
                $affiliateModel->delete($result['affiliate_id']);
                echo "✓ Test affiliate deleted\n";
            }
            
            // Delete user
            $usersModel->delete($result['user_id']);
            echo "✓ Test user deleted\n";
        }
    } else {
        echo "\n✗ FAILED: " . ($result['message'] ?? 'Unknown error') . "\n";
        if (isset($result['errors'])) {
            echo "Errors:\n";
            print_r($result['errors']);
        }
    }

} catch (Exception $e) {
    echo "\n✗ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Check error log
echo "\n=== ERROR LOG ===\n";
$errorLogFile = __DIR__ . '/final_test.log';
if (file_exists($errorLogFile)) {
    $errorContent = file_get_contents($errorLogFile);
    if (!empty($errorContent)) {
        echo "Error log:\n";
        echo $errorContent . "\n";
    } else {
        echo "✓ No errors logged\n";
    }
} else {
    echo "✓ No error log file\n";
}

echo "\n=== END FINAL TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>\n";
?>