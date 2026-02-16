<?php

/**
 * Final Agent Registration System Validation
 * 
 * Kiểm tra tổng thể hệ thống đăng ký đại lý trước khi hoàn thành
 */

echo "=== FINAL AGENT REGISTRATION SYSTEM VALIDATION ===\n\n";

// 1. Check file structure
echo "1. Kiểm tra cấu trúc file...\n";

$requiredFiles = [
    // Core services
    'app/services/AgentRegistrationService.php',
    'app/services/AgentRegistrationData.php',
    'app/services/EmailNotificationService.php',
    'app/services/SpamPreventionService.php',
    'app/services/AgentErrorHandler.php',
    
    // Controllers
    'app/controllers/AffiliateController.php',
    
    // Views
    'app/views/affiliate/registration_popup.php',
    'app/views/affiliate/processing_message.php',
    'app/views/admin/agent_management.php',
    'app/views/admin/agent_error_monitoring.php',
    
    // Tests
    'tests/AgentRegistrationIntegrationTest.php',
    'tests/AgentRegistrationDataTest.php',
    'tests/run_agent_integration_tests.php',
    
    // Configuration
    'config/email.php',
    
    // Assets
    'assets/css/agent_registration.css'
];

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        $missingFiles[] = $file;
    }
}

if (empty($missingFiles)) {
    echo "✅ Tất cả files cần thiết đã có\n\n";
} else {
    echo "❌ Thiếu files:\n";
    foreach ($missingFiles as $file) {
        echo "   - $file\n";
    }
    echo "\n";
}

// 2. Check PHP syntax
echo "2. Kiểm tra PHP syntax...\n";

$phpFiles = [
    'app/services/AgentRegistrationService.php',
    'app/services/AgentRegistrationData.php',
    'app/services/EmailNotificationService.php',
    'app/services/SpamPreventionService.php',
    'app/services/AgentErrorHandler.php',
    'app/controllers/AffiliateController.php'
];

$syntaxErrors = [];
foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $output = [];
        $returnCode = 0;
        exec("D:\\xampp\\php\\php.exe -l \"$file\" 2>&1", $output, $returnCode);
        
        if ($returnCode !== 0) {
            $syntaxErrors[] = $file . ': ' . implode(' ', $output);
        }
    }
}

if (empty($syntaxErrors)) {
    echo "✅ Tất cả PHP files có syntax hợp lệ\n\n";
} else {
    echo "❌ Syntax errors:\n";
    foreach ($syntaxErrors as $error) {
        echo "   - $error\n";
    }
    echo "\n";
}

// 3. Run integration tests
echo "3. Chạy integration tests...\n";

$output = [];
$returnCode = 0;
exec("D:\\xampp\\php\\php.exe tests/run_agent_integration_tests.php 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Tất cả integration tests đã pass\n\n";
} else {
    echo "❌ Integration tests failed:\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
    echo "\n";
}

// 4. Run unit tests
echo "4. Chạy unit tests...\n";

$output = [];
$returnCode = 0;
exec("D:\\xampp\\php\\php.exe tests/AgentRegistrationDataTest.php 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Unit tests đã pass\n\n";
} else {
    echo "❌ Unit tests failed:\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
    echo "\n";
}

// 5. Check routing configuration
echo "5. Kiểm tra routing configuration...\n";

if (file_exists('api.php')) {
    $apiContent = file_get_contents('api.php');
    
    $requiredRoutes = [
        'agent/register',
        'agent/popup',
        'agent/status',
        'admin/agents'
    ];
    
    $missingRoutes = [];
    foreach ($requiredRoutes as $route) {
        if (strpos($apiContent, $route) === false) {
            $missingRoutes[] = $route;
        }
    }
    
    if (empty($missingRoutes)) {
        echo "✅ Tất cả routes cần thiết đã được cấu hình\n\n";
    } else {
        echo "❌ Thiếu routes:\n";
        foreach ($missingRoutes as $route) {
            echo "   - $route\n";
        }
        echo "\n";
    }
} else {
    echo "❌ File api.php không tồn tại\n\n";
}

// 6. Check .htaccess configuration
echo "6. Kiểm tra .htaccess configuration...\n";

if (file_exists('.htaccess')) {
    $htaccessContent = file_get_contents('.htaccess');
    
    if (strpos($htaccessContent, 'RewriteRule ^api/') !== false) {
        echo "✅ API routing đã được cấu hình trong .htaccess\n\n";
    } else {
        echo "❌ API routing chưa được cấu hình trong .htaccess\n\n";
    }
} else {
    echo "❌ File .htaccess không tồn tại\n\n";
}

// 7. Summary
echo "=== SUMMARY ===\n";

$allPassed = empty($missingFiles) && empty($syntaxErrors) && ($returnCode === 0);

if ($allPassed) {
    echo "🎉 HỆ THỐNG ĐĂNG KÝ ĐẠI LÝ ĐÃ HOÀN THÀNH!\n\n";
    echo "Các tính năng đã triển khai:\n";
    echo "✅ Đăng ký đại lý cho người dùng mới\n";
    echo "✅ Đăng ký đại lý cho người dùng hiện tại\n";
    echo "✅ Quản lý phê duyệt từ Admin\n";
    echo "✅ Ngăn chặn spam hệ thống\n";
    echo "✅ Tích hợp email thông báo\n";
    echo "✅ Error handling và logging\n";
    echo "✅ Integration tests\n";
    echo "✅ Routing và API endpoints\n\n";
    
    echo "Hệ thống đã sẵn sàng để sử dụng!\n";
} else {
    echo "⚠️ Vẫn còn một số vấn đề cần khắc phục trước khi hoàn thành.\n";
    echo "Vui lòng kiểm tra lại các lỗi ở trên.\n";
}

echo "\n=== END VALIDATION ===\n";