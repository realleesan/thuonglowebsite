<?php
/**
 * Script kiểm tra tiến độ chuyển đổi từ JSON sang SQL
 */

echo "=== KIỂM TRA TIẾN ĐỘ CHUYỂN ĐỔI JSON SANG SQL ===\n\n";

// Tìm tất cả file PHP trong views còn sử dụng JSON
$jsonFiles = [];
$convertedFiles = [];

// Quét thư mục views
function scanDirectory($dir, &$jsonFiles, &$convertedFiles) {
    $files = glob($dir . '/*.php');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        
        // Kiểm tra có sử dụng JSON không
        if (strpos($content, 'fake_data.json') !== false || 
            strpos($content, 'demo_accounts.json') !== false ||
            strpos($content, 'user_fake_data.json') !== false) {
            $jsonFiles[] = $file;
        } else if (strpos($content, 'Model.php') !== false || 
                   strpos($content, 'new ') !== false && strpos($content, 'Model') !== false) {
            $convertedFiles[] = $file;
        }
    }
    
    // Quét thư mục con
    $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($subdirs as $subdir) {
        scanDirectory($subdir, $jsonFiles, $convertedFiles);
    }
}

// Quét các thư mục views
scanDirectory('app/views/admin', $jsonFiles, $convertedFiles);
scanDirectory('app/views/auth', $jsonFiles, $convertedFiles);
scanDirectory('app/views/users', $jsonFiles, $convertedFiles);
scanDirectory('app/views/affiliate', $jsonFiles, $convertedFiles);

echo "📊 THỐNG KÊ:\n";
echo "- File còn sử dụng JSON: " . count($jsonFiles) . "\n";
echo "- File đã chuyển đổi: " . count($convertedFiles) . "\n";
echo "- Tổng file: " . (count($jsonFiles) + count($convertedFiles)) . "\n\n";

if (!empty($jsonFiles)) {
    echo "❌ CÁC FILE CHƯA CHUYỂN ĐỔI:\n";
    foreach ($jsonFiles as $file) {
        echo "   - " . str_replace(getcwd() . '/', '', $file) . "\n";
    }
    echo "\n";
}

if (!empty($convertedFiles)) {
    echo "✅ CÁC FILE ĐÃ CHUYỂN ĐỔI:\n";
    foreach ($convertedFiles as $file) {
        echo "   - " . str_replace(getcwd() . '/', '', $file) . "\n";
    }
    echo "\n";
}

// Kiểm tra các file JSON data còn tồn tại
echo "📁 KIỂM TRA CÁC FILE JSON DATA:\n";
$jsonDataFiles = [
    'app/views/admin/data/fake_data.json',
    'app/views/auth/data/demo_accounts.json',
    'app/views/users/data/user_fake_data.json'
];

foreach ($jsonDataFiles as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "   - $file (${size} bytes) - CÓ THỂ XÓA\n";
    } else {
        echo "   - $file - KHÔNG TỒN TẠI\n";
    }
}

echo "\n=== KẾT THÚC KIỂM TRA ===\n";