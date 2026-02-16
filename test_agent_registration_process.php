<?php
/**
 * Test file để process đăng ký đại lý và debug lỗi
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/process_errors.log');

echo "<h1>Test Process Đăng Ký Đại Lý</h1>\n";
echo "<pre>\n";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Chỉ chấp nhận POST request\n";
    echo "<a href='test_agent_registration_debug.php'>Quay lại test debug</a>\n";
    exit;
}

echo "=== THÔNG TIN REQUEST ===\n";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "POST Data:\n";
print_r($_POST);

try {
    // Load required files
    echo "\n=== LOADING FILES ===\n";
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

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Prepare data
    echo "\n=== PREPARING DATA ===\n";
    $userData = [
        'name' => $_POST['name'] ?? '',
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'password' => $_POST['password'] ?? '',
        'password_confirmation' => $_POST['confirm_password'] ?? '',
        'ref_code' => $_POST['ref_code'] ?? '',
    ];

    $accountType = $_POST['account_type'] ?? 'user';
    echo "Account Type: {$accountType}\n";
    echo "User Data prepared\n";

    if ($accountType === 'agent') {
        echo "\n=== PROCESSING AGENT REGISTRATION ===\n";
        
        // Validate agent email
        $agentEmail = $userData['email'] ?? '';
        echo "Agent Email: {$agentEmail}\n";
        
        if (empty($agentEmail)) {
            throw new Exception('Email là bắt buộc cho đăng ký đại lý');
        }
        
        if (!str_ends_with(strtolower($agentEmail), '@gmail.com')) {
            throw new Exception('Chỉ chấp nhận địa chỉ Gmail (@gmail.com) cho đăng ký đại lý');
        }
        echo "✓ Email validation passed\n";
        
        // Prepare agent data
        $agentData = [
            'email' => $agentEmail,
            'additional_info' => [
                'registration_source' => 'new_user_form',
                'requested_at' => date('Y-m-d H:i:s')
            ]
        ];
        echo "✓ Agent data prepared\n";
        
        // Create AgentRegistrationService
        echo "\n=== CREATING SERVICES ===\n";
        $agentService = new AgentRegistrationService();
        echo "✓ AgentRegistrationService created\n";
        
        // Call registerNewUserAsAgent
        echo "\n=== CALLING registerNewUserAsAgent ===\n";
        $result = $agentService->registerNewUserAsAgent($userData, $agentData);
        
        echo "Result:\n";
        print_r($result);
        
        if ($result['success']) {
            echo "\n✓ SUCCESS: Agent registration completed successfully!\n";
            echo "User ID: " . ($result['user_id'] ?? 'N/A') . "\n";
            echo "Affiliate ID: " . ($result['affiliate_id'] ?? 'N/A') . "\n";
            echo "Email sent: " . ($result['email_sent'] ? 'Yes' : 'No') . "\n";
        } else {
            echo "\n✗ FAILED: Agent registration failed\n";
            echo "Message: " . ($result['message'] ?? 'Unknown error') . "\n";
            if (isset($result['errors'])) {
                echo "Errors:\n";
                print_r($result['errors']);
            }
        }
        
    } else {
        echo "\n=== PROCESSING REGULAR USER REGISTRATION ===\n";
        echo "This test focuses on agent registration only\n";
    }

} catch (Exception $e) {
    echo "\n✗ EXCEPTION OCCURRED:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
    
    // Log to file
    error_log("Agent Registration Test Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
}

// Check error log
echo "\n=== ERROR LOG CHECK ===\n";
$errorLogFile = __DIR__ . '/process_errors.log';
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

echo "\n=== END TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>\n";

echo "<p><a href='test_agent_registration_debug.php'>Quay lại test debug</a></p>\n";
?>