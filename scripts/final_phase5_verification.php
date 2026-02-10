<?php
/**
 * Final Phase 5 Verification Script
 * Kiểm tra hoàn tất việc chuyển đổi từ JSON sang SQL Models
 */

echo "=== KIỂM TRA HOÀN TẤT PHASE 5 - CHUYỂN ĐỔI JSON SANG SQL ===\n\n";

// 1. Kiểm tra các file core đã bị xóa
echo "🗑️ KIỂM TRA CÁC FILE CORE ĐÃ XÓA:\n";
$coreFiles = [
    'core/AffiliateDataLoader.php',
    'core/AffiliateErrorHandler.php'
];

foreach ($coreFiles as $file) {
    if (file_exists($file)) {
        echo "   ❌ $file - VẪN TỒN TẠI (CẦN XÓA)\n";
    } else {
        echo "   ✅ $file - ĐÃ XÓA\n";
    }
}

// 2. Kiểm tra view files không còn sử dụng JSON
echo "\n📁 KIỂM TRA VIEW FILES:\n";
$viewDirs = [
    'app/views/admin',
    'app/views/affiliate', 
    'app/views/auth',
    'app/views/users'
];

$jsonReferences = 0;
$modelReferences = 0;
$totalFiles = 0;

function scanViewFiles($dir, &$jsonReferences, &$modelReferences, &$totalFiles) {
    $files = glob($dir . '/*.php');
    foreach ($files as $file) {
        $totalFiles++;
        $content = file_get_contents($file);
        
        // Kiểm tra references đến JSON
        if (strpos($content, 'fake_data.json') !== false || 
            strpos($content, 'demo_accounts.json') !== false ||
            strpos($content, 'user_fake_data.json') !== false ||
            strpos($content, 'AffiliateDataLoader') !== false ||
            strpos($content, 'AffiliateErrorHandler') !== false) {
            $jsonReferences++;
            echo "   ❌ " . str_replace(getcwd() . '/', '', $file) . " - VẪN DÙNG JSON/OLD CLASSES\n";
        }
        
        // Kiểm tra references đến Models
        if (strpos($content, 'Model.php') !== false || 
            (strpos($content, 'new ') !== false && strpos($content, 'Model') !== false)) {
            $modelReferences++;
        }
    }
    
    // Quét thư mục con
    $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($subdirs as $subdir) {
        scanViewFiles($subdir, $jsonReferences, $modelReferences, $totalFiles);
    }
}

foreach ($viewDirs as $dir) {
    if (is_dir($dir)) {
        scanViewFiles($dir, $jsonReferences, $modelReferences, $totalFiles);
    }
}

echo "\n📊 THỐNG KÊ VIEW FILES:\n";
echo "   - Tổng số file PHP: $totalFiles\n";
echo "   - File sử dụng JSON/Old Classes: $jsonReferences\n";
echo "   - File sử dụng Models: $modelReferences\n";

// 3. Kiểm tra JavaScript files
echo "\n🟨 KIỂM TRA JAVASCRIPT FILES:\n";
$jsFiles = glob('assets/js/*.js');
$jsJsonReferences = 0;

foreach ($jsFiles as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'fake_data.json') !== false || 
        strpos($content, 'demo_accounts.json') !== false ||
        strpos($content, 'user_fake_data.json') !== false) {
        $jsJsonReferences++;
        echo "   ⚠️ " . str_replace(getcwd() . '/', '', $file) . " - VẪN DÙNG JSON\n";
    }
}

if ($jsJsonReferences === 0) {
    echo "   ✅ Không có JS file nào sử dụng JSON data files\n";
}

// 4. Kiểm tra Models có đầy đủ không
echo "\n🏗️ KIỂM TRA MODELS:\n";
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

foreach ($modelFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file - TỒN TẠI\n";
    } else {
        echo "   ❌ $file - THIẾU\n";
    }
}

// 5. Kết luận
echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 KẾT QUẢ CUỐI CÙNG:\n";

if ($jsonReferences === 0) {
    echo "✅ HOÀN THÀNH: Tất cả view files đã chuyển sang Models\n";
} else {
    echo "❌ CHƯA HOÀN THÀNH: Còn $jsonReferences file chưa chuyển đổi\n";
}

if (!file_exists('core/AffiliateDataLoader.php') && !file_exists('core/AffiliateErrorHandler.php')) {
    echo "✅ HOÀN THÀNH: Đã xóa các file core không dùng\n";
} else {
    echo "❌ CHƯA HOÀN THÀNH: Vẫn còn file core cần xóa\n";
}

echo "\n📈 TỔNG KẾT PHASE 5:\n";
echo "   - View files đã chuyển đổi: " . ($totalFiles - $jsonReferences) . "/$totalFiles\n";
echo "   - Tỷ lệ hoàn thành: " . round((($totalFiles - $jsonReferences) / $totalFiles) * 100, 1) . "%\n";

if ($jsonReferences === 0 && !file_exists('core/AffiliateDataLoader.php') && !file_exists('core/AffiliateErrorHandler.php')) {
    echo "\n🎉 PHASE 5 HOÀN THÀNH 100%!\n";
    echo "   Tất cả view files đã chuyển từ JSON sang SQL Models\n";
} else {
    echo "\n⚠️ PHASE 5 CHƯA HOÀN THÀNH\n";
    echo "   Vui lòng kiểm tra và khắc phục các vấn đề trên\n";
}

echo "\n" . str_repeat("=", 60) . "\n";