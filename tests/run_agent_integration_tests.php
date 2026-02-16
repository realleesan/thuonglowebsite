<?php

/**
 * Agent Registration Integration Test Runner
 * 
 * Chạy tất cả integration tests cho hệ thống đăng ký đại lý
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the integration test class
require_once __DIR__ . '/AgentRegistrationIntegrationTest.php';

// Run the tests
try {
    $testRunner = new AgentRegistrationIntegrationTest();
    $success = $testRunner->runAllTests();
    
    if ($success) {
        echo "\n✅ Tất cả integration tests đã pass thành công!\n";
        echo "Hệ thống đăng ký đại lý đã sẵn sàng để triển khai.\n";
        exit(0);
    } else {
        echo "\n❌ Một số tests đã fail. Vui lòng kiểm tra lại.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "\n💥 Lỗi khi chạy tests: " . $e->getMessage() . "\n";
    exit(1);
}