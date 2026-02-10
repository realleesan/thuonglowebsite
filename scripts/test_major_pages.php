<?php
/**
 * Major Pages Test Script
 * Kiểm tra tất cả các trang chính hoạt động đúng
 */

echo "=== KIỂM TRA CÁC TRANG CHÍNH ===\n\n";

// Load config
$config = require_once __DIR__ . '/../config.php';

// Test pages
$testPages = [
    'Home' => [
        'file' => 'app/views/home/home.php',
        'css' => 'assets/css/home.css',
        'js' => 'assets/js/home.js'
    ],
    'Products' => [
        'file' => 'app/views/products/products.php',
        'css' => 'assets/css/products.css',
        'js' => 'assets/js/products.js'
    ],
    'News' => [
        'file' => 'app/views/news/news.php',
        'css' => 'assets/css/news_details.css',
        'js' => 'assets/js/news_detail.js'
    ],
    'Contact' => [
        'file' => 'app/views/contact/contact.php',
        'css' => 'assets/css/contact.css',
        'js' => 'assets/js/contact.js'
    ],
    'Auth' => [
        'file' => 'app/views/auth/auth.php',
        'css' => 'assets/css/auth.css',
        'js' => 'assets/js/auth.js'
    ],
    'About' => [
        'file' => 'app/views/about/about.php',
        'css' => 'assets/css/about.css',
        'js' => 'assets/js/about.js'
    ]
];

$totalPages = count($testPages);
$passedPages = 0;

foreach ($testPages as $pageName => $pageFiles) {
    echo "KIỂM TRA TRANG $pageName:\n";
    
    $pageStatus = true;
    
    // Kiểm tra PHP file
    if (file_exists($pageFiles['file'])) {
        echo "   ✅ PHP file - TỒN TẠI\n";
        
        // Kiểm tra syntax PHP (simplified)
        echo "   ✅ PHP syntax - OK (assumed)\n";
    } else {
        echo "   ❌ PHP file - THIẾU\n";
        $pageStatus = false;
    }
    
    // Kiểm tra CSS file
    if (file_exists($pageFiles['css'])) {
        echo "   ✅ CSS file - TỒN TẠI\n";
    } else {
        echo "   ❌ CSS file - THIẾU\n";
        $pageStatus = false;
    }
    
    // Kiểm tra JS file
    if (file_exists($pageFiles['js'])) {
        echo "   ✅ JS file - TỒN TẠI\n";
    } else {
        echo "   ❌ JS file - THIẾU\n";
        $pageStatus = false;
    }
    
    // Kiểm tra nội dung PHP file có sử dụng Models không
    if (file_exists($pageFiles['file'])) {
        $content = file_get_contents($pageFiles['file']);
        
        // Kiểm tra có sử dụng Models
        if (strpos($content, 'Model') !== false) {
            echo "   ✅ Uses Models - YES\n";
        } else {
            echo "   ⚠️ Uses Models - NO (may use static data)\n";
        }
        
        // Kiểm tra không còn JSON references
        if (strpos($content, 'fake_data.json') === false && 
            strpos($content, 'demo_accounts.json') === false &&
            strpos($content, 'user_fake_data.json') === false) {
            echo "   ✅ No JSON references - CLEAN\n";
        } else {
            echo "   ❌ JSON references - FOUND\n";
            $pageStatus = false;
        }
    }
    
    if ($pageStatus) {
        echo "   🎉 TRANG $pageName - HOÀN THÀNH\n";
        $passedPages++;
    } else {
        echo "   ⚠️ TRANG $pageName - CẦN KHẮC PHỤC\n";
    }
    
    echo "\n";
}

// Kiểm tra layout files
echo "KIỂM TRA LAYOUT FILES:\n";

$layoutFiles = [
    'Master Layout' => 'app/views/_layout/master.php',
    'Header' => 'app/views/_layout/header.php',
    'Footer' => 'app/views/_layout/footer.php',
    'Breadcrumb' => 'app/views/_layout/breadcrumb.php'
];

foreach ($layoutFiles as $name => $file) {
    if (file_exists($file)) {
        echo "   ✅ $name - TỒN TẠI\n";
        echo "   ✅ $name syntax - OK (assumed)\n";
    } else {
        echo "   ❌ $name - THIẾU\n";
    }
}

// Tổng kết
echo "\n" . str_repeat("=", 60) . "\n";
echo "TỔNG KẾT KIỂM TRA TRANG:\n";
echo "   - Trang đã kiểm tra: $totalPages\n";
echo "   - Trang hoàn thành: $passedPages\n";
echo "   - Tỷ lệ thành công: " . round(($passedPages / $totalPages) * 100, 1) . "%\n";

if ($passedPages == $totalPages) {
    echo "   🎉 TẤT CẢ TRANG HOÀN THÀNH!\n";
} else {
    echo "   ⚠️ CÒN " . ($totalPages - $passedPages) . " TRANG CẦN KHẮC PHỤC\n";
}

echo "\n" . str_repeat("=", 60) . "\n";