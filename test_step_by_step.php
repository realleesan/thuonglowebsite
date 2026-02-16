<?php
/**
 * Test từng bước để tìm lỗi
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(30); // 30 seconds timeout

echo "<h1>Test Từng Bước</h1>\n";
echo "<pre>\n";

try {
    echo "Step 1: Loading basic files...\n";
    require_once 'config.php';
    require_once 'core/database.php';
    echo "✓ Config and database loaded\n";

    echo "\nStep 2: Loading models...\n";
    require_once 'app/models/BaseModel.php';
    require_once 'app/models/UsersModel.php';
    require_once 'app/models/AffiliateModel.php';
    echo "✓ Models loaded\n";

    echo "\nStep 3: Testing model creation...\n";
    $usersModel = new UsersModel();
    $affiliateModel = new AffiliateModel();
    echo "✓ Models instantiated\n";

    echo "\nStep 4: Loading service dependencies...\n";
    require_once 'app/services/ErrorHandler.php';
    echo "✓ ErrorHandler loaded\n";
    
    require_once 'app/services/AgentRegistrationData.php';
    echo "✓ AgentRegistrationData loaded\n";
    
    echo "\nStep 5: Testing AgentRegistrationData...\n";
    $testAgentData = [
        'email' => 'test@gmail.com',
        'additional_info' => [
            'registration_source' => 'new_user_form',
            'requested_at' => date('Y-m-d H:i:s')
        ],
        'request_type' => 'new_user',
        'status' => 'pending'
    ];
    
    $agentRegistrationData = new AgentRegistrationData($testAgentData);
    echo "✓ AgentRegistrationData created\n";
    
    $validationErrors = $agentRegistrationData->validate();
    if (empty($validationErrors)) {
        echo "✓ AgentRegistrationData validation passed\n";
    } else {
        echo "✗ AgentRegistrationData validation failed:\n";
        print_r($validationErrors);
    }

    echo "\nStep 6: Loading SpamPreventionService...\n";
    require_once 'app/services/SpamPreventionService.php';
    $spamService = new SpamPreventionService();
    echo "✓ SpamPreventionService loaded\n";

    echo "\nStep 7: Loading EmailNotificationService...\n";
    require_once 'app/services/EmailNotificationService.php';
    $emailService = new EmailNotificationService();
    echo "✓ EmailNotificationService loaded\n";

    echo "\nStep 8: Loading AuthService...\n";
    require_once 'app/services/AuthService.php';
    echo "✓ AuthService file loaded\n";
    
    // Try to create AuthService instance
    try {
        $authService = new AuthService();
        echo "✓ AuthService instantiated\n";
    } catch (Exception $e) {
        echo "✗ AuthService instantiation failed: " . $e->getMessage() . "\n";
        echo "This might be the issue!\n";
    }

    echo "\nStep 9: Loading RoleManager...\n";
    require_once 'app/services/RoleManager.php';
    try {
        $roleManager = new RoleManager();
        echo "✓ RoleManager instantiated\n";
    } catch (Exception $e) {
        echo "✗ RoleManager instantiation failed: " . $e->getMessage() . "\n";
    }

    echo "\nStep 10: Loading AgentRegistrationService...\n";
    require_once 'app/services/AgentRegistrationService.php';
    echo "✓ AgentRegistrationService file loaded\n";
    
    try {
        $agentService = new AgentRegistrationService();
        echo "✓ AgentRegistrationService instantiated\n";
    } catch (Exception $e) {
        echo "✗ AgentRegistrationService instantiation failed: " . $e->getMessage() . "\n";
        echo "Error details: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }

    echo "\nStep 11: Testing simple user creation...\n";
    $testUserData = [
        'name' => 'Test Step User',
        'username' => 'teststep_' . time(),
        'email' => 'teststep_' . time() . '@gmail.com',
        'phone' => '0123456789',
        'password' => password_hash('TestPassword123!', PASSWORD_DEFAULT)
    ];
    
    try {
        $userId = $usersModel->create($testUserData);
        if ($userId) {
            echo "✓ User created with ID: {$userId}\n";
            // Clean up
            $usersModel->delete($userId);
            echo "✓ Test user deleted\n";
        } else {
            echo "✗ User creation failed\n";
        }
    } catch (Exception $e) {
        echo "✗ User creation error: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "\n💥 FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END STEP BY STEP TEST ===\n";
echo "</pre>\n";
?>