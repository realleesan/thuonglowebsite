<?php
/**
 * Test nhanh agent registration sau khi sửa lỗi
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Nhanh - Agent Registration</h1>\n";
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
    
    echo "✓ Files loaded\n";

    // Test data
    $userData = [
        'name' => 'Test Quick User',
        'username' => 'testquick_' . time(),
        'email' => 'testquick_' . time() . '@gmail.com',
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
    
    echo "✓ Test data prepared\n";
    echo "Email: " . $userData['email'] . "\n";
    
    // Create service
    $agentService = new AgentRegistrationService();
    echo "✓ AgentRegistrationService created\n";
    
    // Call registerNewUserAsAgent
    echo "\nCalling registerNewUserAsAgent...\n";
    $result = $agentService->registerNewUserAsAgent($userData, $agentData);
    
    echo "Result:\n";
    print_r($result);
    
    if ($result['success']) {
        echo "\n🎉 SUCCESS! Agent registration worked!\n";
        echo "User ID: " . ($result['user_id'] ?? 'N/A') . "\n";
        echo "Affiliate ID: " . ($result['affiliate_id'] ?? 'N/A') . "\n";
        
        // Clean up
        if (isset($result['user_id']) && isset($result['affiliate_id'])) {
            $usersModel = new UsersModel();
            $affiliateModel = new AffiliateModel();
            
            $affiliateModel->delete($result['affiliate_id']);
            $usersModel->delete($result['user_id']);
            echo "✓ Test data cleaned up\n";
        }
    } else {
        echo "\n❌ FAILED\n";
        echo "Message: " . ($result['message'] ?? 'Unknown') . "\n";
        if (isset($result['errors'])) {
            echo "Errors:\n";
            print_r($result['errors']);
        }
    }

} catch (Exception $e) {
    echo "\n💥 EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== END TEST ===\n";
echo "</pre>\n";
?>