<?php
/**
 * Test chi tiết để tìm nguyên nhân lỗi 500
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/detailed_debug.log');

echo "<h1>Test Chi Tiết Lỗi Đăng Ký Đại Lý</h1>\n";
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
    
    $db = Database::getInstance();
    echo "✓ All files loaded and database connected\n";

    // Test 1: Kiểm tra method getModel trong AgentRegistrationService
    echo "\n=== TEST 1: Kiểm tra getModel method ===\n";
    $agentService = new AgentRegistrationService();
    
    // Use reflection to check if getModel method exists
    $reflection = new ReflectionClass($agentService);
    $methods = $reflection->getMethods();
    $hasGetModel = false;
    foreach ($methods as $method) {
        if ($method->getName() === 'getModel') {
            $hasGetModel = true;
            echo "✓ getModel method exists\n";
            break;
        }
    }
    
    if (!$hasGetModel) {
        echo "✗ getModel method NOT FOUND - This is likely the issue!\n";
    }

    // Test 2: Kiểm tra UsersModel create method chi tiết
    echo "\n=== TEST 2: Test UsersModel create method ===\n";
    $usersModel = new UsersModel();
    
    // Check fillable fields
    $reflection = new ReflectionClass($usersModel);
    $fillableProperty = $reflection->getProperty('fillable');
    $fillableProperty->setAccessible(true);
    $fillableFields = $fillableProperty->getValue($usersModel);
    
    echo "Fillable fields in UsersModel:\n";
    if (empty($fillableFields)) {
        echo "⚠ WARNING: No fillable fields defined - this might cause issues!\n";
    } else {
        print_r($fillableFields);
    }

    // Test simple user creation
    $testUserData = [
        'name' => 'Test User Simple',
        'username' => 'testuser_' . time(),
        'email' => 'testuser_' . time() . '@gmail.com',
        'phone' => '0123456789',
        'password' => password_hash('TestPassword123!', PASSWORD_DEFAULT)
    ];
    
    echo "\nTesting simple user creation...\n";
    try {
        $userId = $usersModel->create($testUserData);
        if ($userId) {
            echo "✓ Simple user creation SUCCESS - ID: {$userId}\n";
            // Clean up
            $usersModel->delete($userId);
            echo "✓ Test user cleaned up\n";
        } else {
            echo "✗ Simple user creation FAILED\n";
        }
    } catch (Exception $e) {
        echo "✗ Simple user creation ERROR: " . $e->getMessage() . "\n";
    }

    // Test 3: Test với agent fields
    echo "\n=== TEST 3: Test user creation with agent fields ===\n";
    $testUserDataWithAgent = [
        'name' => 'Test Agent User',
        'username' => 'testagent_' . time(),
        'email' => 'testagent_' . time() . '@gmail.com',
        'phone' => '0123456789',
        'password' => password_hash('TestPassword123!', PASSWORD_DEFAULT),
        'agent_request_status' => 'pending',
        'agent_request_date' => date('Y-m-d H:i:s')
    ];
    
    try {
        $userId = $usersModel->create($testUserDataWithAgent);
        if ($userId) {
            echo "✓ Agent user creation SUCCESS - ID: {$userId}\n";
            // Clean up
            $usersModel->delete($userId);
            echo "✓ Test agent user cleaned up\n";
        } else {
            echo "✗ Agent user creation FAILED\n";
        }
    } catch (Exception $e) {
        echo "✗ Agent user creation ERROR: " . $e->getMessage() . "\n";
    }

    // Test 4: Test AffiliateModel create
    echo "\n=== TEST 4: Test AffiliateModel create ===\n";
    $affiliateModel = new AffiliateModel();
    
    // Check fillable fields
    $reflection = new ReflectionClass($affiliateModel);
    $fillableProperty = $reflection->getProperty('fillable');
    $fillableProperty->setAccessible(true);
    $fillableFields = $fillableProperty->getValue($affiliateModel);
    
    echo "Fillable fields in AffiliateModel:\n";
    if (empty($fillableFields)) {
        echo "⚠ WARNING: No fillable fields defined!\n";
    } else {
        print_r($fillableFields);
    }

    // Test 5: Test AgentRegistrationData validation
    echo "\n=== TEST 5: Test AgentRegistrationData validation ===\n";
    $testAgentData = [
        'email' => 'testagent@gmail.com',
        'additional_info' => [
            'registration_source' => 'new_user_form',
            'requested_at' => date('Y-m-d H:i:s')
        ],
        'request_type' => 'new_user',
        'status' => 'pending'
    ];
    
    try {
        $agentRegistrationData = new AgentRegistrationData($testAgentData);
        $validationErrors = $agentRegistrationData->validate();
        
        if (empty($validationErrors)) {
            echo "✓ AgentRegistrationData validation passed\n";
        } else {
            echo "✗ AgentRegistrationData validation failed:\n";
            print_r($validationErrors);
        }
    } catch (Exception $e) {
        echo "✗ AgentRegistrationData ERROR: " . $e->getMessage() . "\n";
    }

    // Test 6: Test SpamPreventionService
    echo "\n=== TEST 6: Test SpamPreventionService ===\n";
    try {
        $spamService = new SpamPreventionService();
        $isRateLimited = $spamService->isRateLimited(null);
        echo "Rate limited check: " . ($isRateLimited ? 'YES' : 'NO') . "\n";
        echo "✓ SpamPreventionService working\n";
    } catch (Exception $e) {
        echo "✗ SpamPreventionService ERROR: " . $e->getMessage() . "\n";
    }

    // Test 7: Test EmailNotificationService
    echo "\n=== TEST 7: Test EmailNotificationService ===\n";
    try {
        $emailService = new EmailNotificationService();
        echo "✓ EmailNotificationService instance created\n";
        
        // Check if sendRegistrationConfirmation method exists
        $reflection = new ReflectionClass($emailService);
        $methods = $reflection->getMethods();
        $hasMethod = false;
        foreach ($methods as $method) {
            if ($method->getName() === 'sendRegistrationConfirmation') {
                $hasMethod = true;
                break;
            }
        }
        
        if ($hasMethod) {
            echo "✓ sendRegistrationConfirmation method exists\n";
        } else {
            echo "✗ sendRegistrationConfirmation method NOT FOUND\n";
        }
    } catch (Exception $e) {
        echo "✗ EmailNotificationService ERROR: " . $e->getMessage() . "\n";
    }

    // Test 8: Test complete flow simulation (without actual database insert)
    echo "\n=== TEST 8: Simulate complete agent registration flow ===\n";
    
    $userData = [
        'name' => 'Test Complete Flow',
        'username' => 'testcomplete_' . time(),
        'email' => 'testcomplete_' . time() . '@gmail.com',
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
    
    echo "Simulating registerNewUserAsAgent call...\n";
    try {
        // This will actually call the method - be careful!
        // Comment out if you don't want to create real data
        $result = $agentService->registerNewUserAsAgent($userData, $agentData);
        
        echo "Result:\n";
        print_r($result);
        
        if ($result['success']) {
            echo "✓ Complete flow SUCCESS!\n";
            // Clean up if successful
            if (isset($result['user_id'])) {
                $usersModel->delete($result['user_id']);
                echo "✓ Test user cleaned up\n";
            }
        } else {
            echo "✗ Complete flow FAILED\n";
            echo "Error message: " . ($result['message'] ?? 'Unknown') . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Complete flow EXCEPTION: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }

} catch (Exception $e) {
    echo "✗ MAIN EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Check error log
echo "\n=== ERROR LOG ===\n";
$errorLogFile = __DIR__ . '/detailed_debug.log';
if (file_exists($errorLogFile)) {
    $errorContent = file_get_contents($errorLogFile);
    if (!empty($errorContent)) {
        echo "Error log content:\n";
        echo $errorContent . "\n";
    } else {
        echo "✓ No errors in log\n";
    }
} else {
    echo "✓ No error log file\n";
}

echo "\n=== END DETAILED TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>\n";
?>