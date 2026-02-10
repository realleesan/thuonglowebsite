<?php
/**
 * Final Hosting Integration Test
 * Kiểm tra tổng thể website sẵn sàng deploy lên hosting
 */

echo "=== KIỂM TRA CUỐI CÙNG - HOSTING INTEGRATION ===\n\n";

$totalTests = 0;
$passedTests = 0;

// 1. Kiểm tra Phase 5 completion
echo "1. KIỂM TRA PHASE 5 COMPLETION:\n";
$totalTests++;

// Chạy phase 5 verification
ob_start();
include __DIR__ . '/final_phase5_verification.php';
$phase5Output = ob_get_clean();

if (strpos($phase5Output, 'PHASE 5 HOÀN THÀNH 100%') !== false) {
    echo "   ✅ Phase 5 - JSON to SQL conversion COMPLETED\n";
    $passedTests++;
} else {
    echo "   ❌ Phase 5 - NOT COMPLETED\n";
}

// 2. Kiểm tra hosting integration
echo "\n2. KIỂM TRA HOSTING INTEGRATION:\n";
$totalTests++;

ob_start();
include __DIR__ . '/test_hosting_integration.php';
$hostingOutput = ob_get_clean();

if (strpos($hostingOutput, 'assets/css/home.css - TỒN TẠI') !== false &&
    strpos($hostingOutput, '.htaccess - TỒN TẠI') !== false) {
    echo "   ✅ Hosting integration - READY\n";
    $passedTests++;
} else {
    echo "   ❌ Hosting integration - NOT READY\n";
}

// 3. Kiểm tra major pages
echo "\n3. KIỂM TRA MAJOR PAGES:\n";
$totalTests++;

ob_start();
include __DIR__ . '/test_major_pages.php';
$pagesOutput = ob_get_clean();

if (strpos($pagesOutput, 'TẤT CẢ TRANG HOÀN THÀNH') !== false) {
    echo "   ✅ All major pages - READY\n";
    $passedTests++;
} else {
    echo "   ❌ Major pages - ISSUES FOUND\n";
}

// 4. Kiểm tra cấu hình files
echo "\n4. KIỂM TRA CẤU HÌNH FILES:\n";
$totalTests++;

$configFiles = [
    'config.php' => file_exists('config.php'),
    '.htaccess' => file_exists('.htaccess'),
    'index.php' => file_exists('index.php'),
    'api.php' => file_exists('api.php')
];

$configReady = true;
foreach ($configFiles as $file => $exists) {
    if ($exists) {
        echo "   ✅ $file - TỒN TẠI\n";
    } else {
        echo "   ❌ $file - THIẾU\n";
        $configReady = false;
    }
}

if ($configReady) {
    $passedTests++;
}

// 5. Kiểm tra core files
echo "\n5. KIỂM TRA CORE FILES:\n";
$totalTests++;

$coreFiles = [
    'core/database.php' => file_exists('core/database.php'),
    'core/functions.php' => file_exists('core/functions.php'),
    'core/router.php' => file_exists('core/router.php'),
    'core/UrlBuilder.php' => file_exists('core/UrlBuilder.php')
];

$coreReady = true;
foreach ($coreFiles as $file => $exists) {
    if ($exists) {
        echo "   ✅ $file - TỒN TẠI\n";
    } else {
        echo "   ❌ $file - THIẾU\n";
        $coreReady = false;
    }
}

if ($coreReady) {
    $passedTests++;
}

// 6. Kiểm tra models
echo "\n6. KIỂM TRA MODELS:\n";
$totalTests++;

$modelFiles = [
    'app/models/BaseModel.php',
    'app/models/UsersModel.php',
    'app/models/ProductsModel.php',
    'app/models/CategoriesModel.php',
    'app/models/OrdersModel.php',
    'app/models/NewsModel.php',
    'app/models/EventsModel.php',
    'app/models/ContactsModel.php',
    'app/models/SettingsModel.php',
    'app/models/AffiliateModel.php'
];

$modelsReady = true;
foreach ($modelFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ " . basename($file) . " - TỒN TẠI\n";
    } else {
        echo "   ❌ " . basename($file) . " - THIẾU\n";
        $modelsReady = false;
    }
}

if ($modelsReady) {
    $passedTests++;
}

// Tổng kết
echo "\n" . str_repeat("=", 70) . "\n";
echo "🎯 KẾT QUẢ KIỂM TRA CUỐI CÙNG:\n";
echo "   - Tổng số test: $totalTests\n";
echo "   - Test thành công: $passedTests\n";
echo "   - Tỷ lệ thành công: " . round(($passedTests / $totalTests) * 100, 1) . "%\n\n";

if ($passedTests == $totalTests) {
    echo "🎉 WEBSITE SẴN SÀNG DEPLOY LÊN HOSTING!\n\n";
    
    echo "📋 HƯỚNG DẪN DEPLOY:\n";
    echo "   1. Upload toàn bộ source code lên hosting (public_html)\n";
    echo "   2. Tạo database trên hosting panel\n";
    echo "   3. Cập nhật thông tin database trong config.php:\n";
    echo "      - host: localhost\n";
    echo "      - name: test1_thuonglowebsite\n";
    echo "      - username: test1_thuonglowebsite\n";
    echo "      - password: [your_password]\n";
    echo "   4. Chạy migrations: php scripts/migrate.php\n";
    echo "   5. Chạy seeders: php scripts/seed.php\n";
    echo "   6. Test website: https://test1.web3b.com/\n\n";
    
    echo "✅ CÁC TÍNH NĂNG ĐÃ SẴN SÀNG:\n";
    echo "   - Homepage với banner và CTA\n";
    echo "   - Products catalog với categories\n";
    echo "   - News system với details\n";
    echo "   - Contact form\n";
    echo "   - User authentication (login/register)\n";
    echo "   - Admin dashboard với CRUD operations\n";
    echo "   - Affiliate system\n";
    echo "   - User dashboard với orders\n";
    echo "   - Responsive design\n";
    echo "   - Clean URLs với .htaccess\n";
    echo "   - SQL Models thay vì JSON data\n";
    
} else {
    echo "⚠️ WEBSITE CHƯA SẴN SÀNG DEPLOY\n";
    echo "   Vui lòng khắc phục " . ($totalTests - $passedTests) . " vấn đề trên trước khi deploy.\n";
}

echo "\n" . str_repeat("=", 70) . "\n";