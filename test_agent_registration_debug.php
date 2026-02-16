<?php
/**
 * Test file để debug lỗi đăng ký đại lý
 * Chạy file này để kiểm tra các vấn đề có thể xảy ra
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_errors.log');

echo "<h1>Test Debug Đăng Ký Đại Lý</h1>\n";
echo "<pre>\n";

// Test 1: Kiểm tra các file cần thiết
echo "=== TEST 1: Kiểm tra các file cần thiết ===\n";
$requiredFiles = [
    'config.php',
    'core/database.php',
    'app/models/BaseModel.php',
    'app/models/UsersModel.php',
    'app/models/AffiliateModel.php',
    'app/services/AgentRegistrationService.php',
    'app/services/AgentRegistrationData.php',
    'app/services/SpamPreventionService.php',
    'app/services/EmailNotificationService.php',
    'app/controllers/AuthController.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} - OK\n";
    } else {
        echo "✗ {$file} - MISSING\n";
    }
}

// Test 2: Kiểm tra kết nối database
echo "\n=== TEST 2: Kiểm tra kết nối database ===\n";
try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = Database::getInstance();
    echo "✓ Database connection - OK\n";
    
    // Test query
    $result = $db->query("SELECT 1 as test");
    if ($result && isset($result[0]['test'])) {
        echo "✓ Database query test - OK\n";
    } else {
        echo "✗ Database query test - FAILED\n";
    }
} catch (Exception $e) {
    echo "✗ Database connection - ERROR: " . $e->getMessage() . "\n";
}

// Test 3: Kiểm tra bảng cần thiết
echo "\n=== TEST 3: Kiểm tra bảng cần thiết ===\n";
$requiredTables = ['users', 'affiliates'];

try {
    foreach ($requiredTables as $table) {
        $result = $db->query("SHOW TABLES LIKE '{$table}'");
        if ($result && count($result) > 0) {
            echo "✓ Table '{$table}' - EXISTS\n";
            
            // Kiểm tra cấu trúc bảng
            $columns = $db->query("DESCRIBE {$table}");
            echo "  Columns: " . implode(', ', array_column($columns, 'Field')) . "\n";
        } else {
            echo "✗ Table '{$table}' - MISSING\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Table check - ERROR: " . $e->getMessage() . "\n";
}

// Test 4: Kiểm tra các class cần thiết
echo "\n=== TEST 4: Kiểm tra các class cần thiết ===\n";
$requiredClasses = [
    'BaseModel' => 'app/models/BaseModel.php',
    'UsersModel' => 'app/models/UsersModel.php',
    'AffiliateModel' => 'app/models/AffiliateModel.php',
    'AgentRegistrationService' => 'app/services/AgentRegistrationService.php',
    'AgentRegistrationData' => 'app/services/AgentRegistrationData.php'
];

foreach ($requiredClasses as $className => $filePath) {
    try {
        if (file_exists($filePath)) {
            require_once $filePath;
            if (class_exists($className)) {
                echo "✓ Class '{$className}' - OK\n";
            } else {
                echo "✗ Class '{$className}' - NOT FOUND IN FILE\n";
            }
        } else {
            echo "✗ Class '{$className}' - FILE MISSING\n";
        }
    } catch (Exception $e) {
        echo "✗ Class '{$className}' - ERROR: " . $e->getMessage() . "\n";
    }
}

// Test 5: Test tạo instance các service
echo "\n=== TEST 5: Test tạo instance các service ===\n";
try {
    $usersModel = new UsersModel();
    echo "✓ UsersModel instance - OK\n";
} catch (Exception $e) {
    echo "✗ UsersModel instance - ERROR: " . $e->getMessage() . "\n";
}

try {
    $affiliateModel = new AffiliateModel();
    echo "✓ AffiliateModel instance - OK\n";
} catch (Exception $e) {
    echo "✗ AffiliateModel instance - ERROR: " . $e->getMessage() . "\n";
}

try {
    $agentService = new AgentRegistrationService();
    echo "✓ AgentRegistrationService instance - OK\n";
} catch (Exception $e) {
    echo "✗ AgentRegistrationService instance - ERROR: " . $e->getMessage() . "\n";
}

// Test 6: Simulate agent registration data
echo "\n=== TEST 6: Test dữ liệu đăng ký đại lý ===\n";
$testUserData = [
    'name' => 'Test User',
    'username' => 'testuser123',
    'email' => 'testuser@gmail.com',
    'phone' => '0123456789',
    'password' => 'TestPassword123!',
    'password_confirmation' => 'TestPassword123!',
    'ref_code' => ''
];

$testAgentData = [
    'email' => 'testuser@gmail.com',
    'additional_info' => [
        'registration_source' => 'new_user_form',
        'requested_at' => date('Y-m-d H:i:s')
    ]
];

try {
    $agentRegistrationData = new AgentRegistrationData(array_merge($testAgentData, [
        'request_type' => 'new_user',
        'status' => 'pending'
    ]));
    
    $validationErrors = $agentRegistrationData->validate();
    if (empty($validationErrors)) {
        echo "✓ AgentRegistrationData validation - OK\n";
    } else {
        echo "✗ AgentRegistrationData validation - ERRORS:\n";
        foreach ($validationErrors as $field => $error) {
            echo "  - {$field}: {$error}\n";
        }
    }
} catch (Exception $e) {
    echo "✗ AgentRegistrationData test - ERROR: " . $e->getMessage() . "\n";
}

// Test 7: Test method registerNewUserAsAgent (dry run)
echo "\n=== TEST 7: Test registerNewUserAsAgent method ===\n";
try {
    // Chỉ test validation và setup, không thực sự tạo user
    echo "Testing method signature and basic validation...\n";
    
    $reflection = new ReflectionClass('AgentRegistrationService');
    $method = $reflection->getMethod('registerNewUserAsAgent');
    echo "✓ Method registerNewUserAsAgent exists\n";
    
    $parameters = $method->getParameters();
    echo "✓ Method parameters: " . count($parameters) . " parameters\n";
    foreach ($parameters as $param) {
        echo "  - " . $param->getName() . " (" . ($param->getType() ? $param->getType()->getName() : 'mixed') . ")\n";
    }
    
} catch (Exception $e) {
    echo "✗ Method test - ERROR: " . $e->getMessage() . "\n";
}

// Test 8: Kiểm tra session và CSRF
echo "\n=== TEST 8: Kiểm tra session ===\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "✓ Session started\n";
} else {
    echo "✓ Session already active\n";
}

echo "Session ID: " . session_id() . "\n";

// Test 9: Kiểm tra error log
echo "\n=== TEST 9: Kiểm tra error log ===\n";
$errorLogFile = __DIR__ . '/debug_errors.log';
if (file_exists($errorLogFile)) {
    $errorContent = file_get_contents($errorLogFile);
    if (!empty($errorContent)) {
        echo "⚠ Error log có nội dung:\n";
        echo $errorContent . "\n";
    } else {
        echo "✓ Error log trống\n";
    }
} else {
    echo "✓ Chưa có error log\n";
}

echo "\n=== KẾT THÚC TEST ===\n";
echo "Thời gian: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>\n";

// Tạo form test đơn giản
echo "<h2>Form Test Đăng Ký Đại Lý</h2>\n";
echo "<form method='POST' action='test_agent_registration_process.php'>\n";
echo "<p><label>Họ tên: <input type='text' name='name' value='Test User' required></label></p>\n";
echo "<p><label>Username: <input type='text' name='username' value='testuser" . rand(100, 999) . "' required></label></p>\n";
echo "<p><label>Email: <input type='email' name='email' value='testuser" . rand(100, 999) . "@gmail.com' required></label></p>\n";
echo "<p><label>Phone: <input type='tel' name='phone' value='012345678" . rand(0, 9) . "' required></label></p>\n";
echo "<p><label>Password: <input type='password' name='password' value='TestPassword123!' required></label></p>\n";
echo "<p><label>Confirm Password: <input type='password' name='confirm_password' value='TestPassword123!' required></label></p>\n";
echo "<p><label>Account Type: <select name='account_type'><option value='user'>User</option><option value='agent' selected>Agent</option></select></label></p>\n";
echo "<p><input type='submit' value='Test Đăng Ký Đại Lý'></p>\n";
echo "</form>\n";
?>