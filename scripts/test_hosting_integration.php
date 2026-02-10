<?php
/**
 * Hosting Integration Test Script
 * Kiểm tra website hoạt động đúng trên hosting environment
 */

echo "=== KIỂM TRA HOSTING INTEGRATION ===\n\n";

// 1. Kiểm tra cấu hình môi trường
echo "KIỂM TRA CẤU HÌNH MÔI TRƯỜNG:\n";

// Load config
$config = require_once __DIR__ . '/../config.php';

// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . $host . '/';

echo "   - Base URL: " . $baseUrl . "\n";
echo "   - Environment: " . $config['app']['environment'] . "\n";
echo "   - Debug Mode: " . ($config['app']['debug'] ? 'ON' : 'OFF') . "\n";

// 2. Kiểm tra file assets tồn tại
echo "\nKIỂM TRA ASSETS TỒN TẠI:\n";

$assetFiles = [
    'assets/css/home.css',
    'assets/js/home.js',
    'assets/icons/logo/logo.png',
    'assets/css/header.css',
    'assets/css/footer.css'
];

foreach ($assetFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file - TỒN TẠI\n";
    } else {
        echo "   ❌ $file - THIẾU\n";
    }
}

// 3. Kiểm tra .htaccess
echo "\nKIỂM TRA .HTACCESS:\n";

if (file_exists('.htaccess')) {
    echo "   ✅ .htaccess - TỒN TẠI\n";
    
    $htaccessContent = file_get_contents('.htaccess');
    
    // Kiểm tra các rules quan trọng
    $rules = [
        'RewriteEngine On' => strpos($htaccessContent, 'RewriteEngine On') !== false,
        'HTTPS Redirect' => strpos($htaccessContent, 'HTTPS') !== false,
        'Index.php removal' => strpos($htaccessContent, 'index.php') !== false,
        'Security rules' => strpos($htaccessContent, 'deny from all') !== false
    ];
    
    foreach ($rules as $rule => $exists) {
        echo "   " . ($exists ? '✅' : '❌') . " $rule\n";
    }
} else {
    echo "   ❌ .htaccess - THIẾU\n";
}

// 4. Kiểm tra database connection
echo "\nKIỂM TRA DATABASE CONNECTION:\n";
echo "   ⚠️ Database connection - SKIPPED (not configured for local testing)\n";

// 5. Kiểm tra models hoạt động
echo "\nKIỂM TRA MODELS:\n";
echo "   ⚠️ Models test - SKIPPED (requires database connection)\n";

// 6. Kiểm tra error pages
echo "\nKIỂM TRA ERROR PAGES:\n";

$errorPages = [
    '404.php' => 'errors/404.php',
    '403.php' => 'errors/403.php',
    '500.php' => 'errors/500.php'
];

foreach ($errorPages as $page => $file) {
    if (file_exists($file)) {
        echo "   ✅ $page - TỒN TẠI\n";
    } else {
        echo "   ❌ $page - THIẾU\n";
    }
}

// 7. Tổng kết
echo "\n" . str_repeat("=", 60) . "\n";
echo "TỔNG KẾT HOSTING INTEGRATION:\n";

echo "\nHƯỚNG DẪN TIẾP THEO:\n";
echo "   1. Upload toàn bộ source code lên hosting\n";
echo "   2. Cấu hình database connection trong config.php\n";
echo "   3. Chạy migrations: php scripts/migrate.php\n";
echo "   4. Chạy seeders: php scripts/seed.php\n";
echo "   5. Test website trên domain: https://test1.web3b.com/\n";

echo "\n" . str_repeat("=", 60) . "\n";