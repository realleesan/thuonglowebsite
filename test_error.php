<?php
/**
 * File test để kiểm tra lỗi 500
 * Upload lên thư mục gốc và truy cập: https://test1.web3b.com/test_error.php
 */

// Hiển thị tất cả lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Kiểm tra hệ thống</h1>";
echo "<pre>";

// Test 1: PHP Version
echo "=== 1. PHP Version ===\n";
echo "PHP Version: " . phpversion() . "\n\n";

// Test 2: Check required extensions
echo "=== 2. Required Extensions ===\n";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'mbstring'];
foreach ($required_extensions as $ext) {
    echo $ext . ": " . (extension_loaded($ext) ? "OK" : "MISSING") . "\n";
}
echo "\n";

// Test 3: Check file permissions
echo "=== 3. Directory & File Checks ===\n";
$paths = [
    'logs/' => is_dir(__DIR__ . '/logs'),
    'logs writable' => is_writable(__DIR__ . '/logs'),
    'core/database.php' => file_exists(__DIR__ . '/core/database.php'),
    'config.php' => file_exists(__DIR__ . '/config.php'),
    '.env' => file_exists(__DIR__ . '/.env'),
];
foreach ($paths as $name => $exists) {
    echo $name . ": " . ($exists ? "OK" : "MISSING/NOT WRITABLE") . "\n";
}
echo "\n";

// Test 4: Try to load config
echo "=== 4. Config Load Test ===\n";
try {
    $config = require __DIR__ . '/config.php';
    if ($config === 1 || $config === true) {
        global $config;
    }
    echo "Config loaded: OK\n";
    echo "Environment: " . ($config['app']['environment'] ?? 'unknown') . "\n";
    echo "Debug mode: " . ($config['app']['debug'] ? 'true' : 'false') . "\n\n";
} catch (Exception $e) {
    echo "Config ERROR: " . $e->getMessage() . "\n\n";
}

// Test 5: Try database connection
echo "=== 5. Database Connection Test ===\n";
try {
    if (!isset($config['database'])) {
        echo "Database config not available\n";
    } else {
        $dbConfig = $config['database'];
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
        echo "Database connection: OK\n";
        
        // Test query
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        echo "Test query: " . ($result['test'] == 1 ? "OK" : "FAILED") . "\n\n";
    }
} catch (PDOException $e) {
    echo "Database ERROR: " . $e->getMessage() . "\n\n";
} catch (Exception $e) {
    echo "Database ERROR: " . $e->getMessage() . "\n\n";
}

// Test 6: Check core files
echo "=== 6. Core Files Check ===\n";
$core_files = [
    'core/database.php',
    'core/functions.php',
    'core/security.php',
    'core/env.php',
    'core/view_init.php',
    'core/UrlBuilder.php',
];
foreach ($core_files as $file) {
    $path = __DIR__ . '/' . $file;
    echo $file . ": ";
    if (file_exists($path)) {
        // Check syntax
        $output = [];
        $return = 0;
        exec("php -l " . escapeshellarg($path) . " 2>&1", $output, $return);
        if ($return === 0) {
            echo "OK (syntax valid)";
        } else {
            echo "SYNTAX ERROR: " . implode(" ", $output);
        }
    } else {
        echo "MISSING";
    }
    echo "\n";
}
echo "\n";

// Test 7: Try loading core files
echo "=== 7. Core Files Load Test ===\n";
try {
    define('THUONGLO_INIT', true);
    
    require_once __DIR__ . '/core/env.php';
    echo "env.php: OK\n";
    
    require_once __DIR__ . '/core/database.php';
    echo "database.php: OK\n";
    
    require_once __DIR__ . '/core/functions.php';
    echo "functions.php: OK\n";
    
    require_once __DIR__ . '/core/security.php';
    echo "security.php: OK\n";
    
    require_once __DIR__ . '/core/view_init.php';
    echo "view_init.php: OK\n";
    
} catch (Exception $e) {
    echo "ERROR loading core file: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "FATAL ERROR loading core file: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
echo "\n";

// Test 8: ServiceManager Test
echo "=== 8. ServiceManager Test ===\n";
try {
    require_once __DIR__ . '/app/services/ServiceManager.php';
    echo "ServiceManager.php: OK\n";
    
    $errorHandler = new ErrorHandler();
    $serviceManager = new ServiceManager($errorHandler);
    echo "ServiceManager instance: OK\n";
    
    $publicService = $serviceManager->getService('public');
    echo "PublicService instance: OK\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
echo "<h2>Test hoàn tất!</h2>";
echo "<p>Nếu có lỗi xuất hiện ở trên, hãy kiểm tra và sửa lỗi tương ứng.</p>";
