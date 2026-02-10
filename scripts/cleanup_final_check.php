<?php
/**
 * Final Cleanup Check Script
 * Tìm và loại bỏ tất cả JSON references và old core imports
 */

echo "=== KIỂM TRA VÀ DỌN DẸP CUỐI CÙNG ===\n\n";

// 1. Tìm tất cả file JS có JSON references
echo "1. KIỂM TRA JAVASCRIPT FILES:\n";
$jsFiles = glob('assets/js/*.js');
$jsIssues = [];

foreach ($jsFiles as $file) {
    $content = file_get_contents($file);
    $issues = [];
    
    // Kiểm tra JSON references
    if (strpos($content, 'fake_data.json') !== false) {
        $issues[] = 'fake_data.json';
    }
    if (strpos($content, 'demo_accounts.json') !== false) {
        $issues[] = 'demo_accounts.json';
    }
    if (strpos($content, 'user_fake_data.json') !== false) {
        $issues[] = 'user_fake_data.json';
    }
    if (strpos($content, '.json') !== false && 
        (strpos($content, 'data/') !== false || strpos($content, '/data') !== false)) {
        $issues[] = 'other JSON files';
    }
    
    if (!empty($issues)) {
        $jsIssues[$file] = $issues;
        echo "   ❌ $file - " . implode(', ', $issues) . "\n";
    } else {
        echo "   ✅ $file - CLEAN\n";
    }
}

// 2. Tìm tất cả file PHP có old core imports
echo "\n2. KIỂM TRA PHP FILES - OLD CORE IMPORTS:\n";
$phpFiles = array_merge(
    glob('app/views/**/*.php'),
    glob('app/views/*/*.php'),
    glob('app/views/*.php'),
    glob('app/controllers/*.php'),
    glob('app/models/*.php')
);

$phpIssues = [];

foreach ($phpFiles as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    $issues = [];
    
    // Kiểm tra old core imports
    if (strpos($content, 'AffiliateDataLoader') !== false) {
        $issues[] = 'AffiliateDataLoader';
    }
    if (strpos($content, 'AffiliateErrorHandler') !== false) {
        $issues[] = 'AffiliateErrorHandler';
    }
    if (strpos($content, 'core/AffiliateDataLoader') !== false) {
        $issues[] = 'core/AffiliateDataLoader';
    }
    if (strpos($content, 'core/AffiliateErrorHandler') !== false) {
        $issues[] = 'core/AffiliateErrorHandler';
    }
    
    // Kiểm tra JSON references trong PHP
    if (strpos($content, 'fake_data.json') !== false) {
        $issues[] = 'fake_data.json';
    }
    if (strpos($content, 'demo_accounts.json') !== false) {
        $issues[] = 'demo_accounts.json';
    }
    if (strpos($content, 'user_fake_data.json') !== false) {
        $issues[] = 'user_fake_data.json';
    }
    
    if (!empty($issues)) {
        $phpIssues[$file] = $issues;
        echo "   ❌ $file - " . implode(', ', $issues) . "\n";
    }
}

if (empty($phpIssues)) {
    echo "   ✅ Tất cả PHP files - CLEAN\n";
}

// 3. Kiểm tra đặc biệt folder affiliate
echo "\n3. KIỂM TRA ĐẶC BIỆT AFFILIATE FOLDER:\n";
$affiliateFiles = array_merge(
    glob('app/views/affiliate/**/*.php'),
    glob('app/views/affiliate/*/*.php'),
    glob('app/views/affiliate/*.php')
);

$affiliateIssues = [];

foreach ($affiliateFiles as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    $issues = [];
    
    // Kiểm tra các patterns cụ thể
    if (preg_match('/require.*core\/affiliate/i', $content)) {
        $issues[] = 'core/affiliate* import';
    }
    if (preg_match('/include.*core\/affiliate/i', $content)) {
        $issues[] = 'core/affiliate* include';
    }
    if (strpos($content, 'AffiliateDataLoader') !== false) {
        $issues[] = 'AffiliateDataLoader class';
    }
    if (strpos($content, 'AffiliateErrorHandler') !== false) {
        $issues[] = 'AffiliateErrorHandler class';
    }
    
    if (!empty($issues)) {
        $affiliateIssues[$file] = $issues;
        echo "   ❌ $file - " . implode(', ', $issues) . "\n";
    }
}

if (empty($affiliateIssues)) {
    echo "   ✅ Tất cả affiliate files - CLEAN\n";
}

// 4. Tổng hợp và đề xuất fix
echo "\n" . str_repeat("=", 60) . "\n";
echo "📋 TỔNG HỢP VẤN ĐỀ:\n";

$totalIssues = count($jsIssues) + count($phpIssues) + count($affiliateIssues);

if ($totalIssues > 0) {
    echo "   - JavaScript files có vấn đề: " . count($jsIssues) . "\n";
    echo "   - PHP files có vấn đề: " . count($phpIssues) . "\n";
    echo "   - Affiliate files có vấn đề: " . count($affiliateIssues) . "\n";
    echo "   - TỔNG: $totalIssues files cần fix\n\n";
    
    echo "🔧 ĐỀ XUẤT FIX:\n";
    
    // JS Issues
    if (!empty($jsIssues)) {
        echo "\nJavaScript Files:\n";
        foreach ($jsIssues as $file => $issues) {
            echo "   $file:\n";
            foreach ($issues as $issue) {
                if (strpos($issue, '.json') !== false) {
                    echo "     - Thay thế '$issue' bằng API endpoint\n";
                }
            }
        }
    }
    
    // PHP Issues  
    if (!empty($phpIssues)) {
        echo "\nPHP Files:\n";
        foreach ($phpIssues as $file => $issues) {
            echo "   $file:\n";
            foreach ($issues as $issue) {
                if (strpos($issue, 'Affiliate') !== false) {
                    echo "     - Xóa import/usage của '$issue'\n";
                } elseif (strpos($issue, '.json') !== false) {
                    echo "     - Thay thế '$issue' bằng Model usage\n";
                }
            }
        }
    }
    
    // Affiliate Issues
    if (!empty($affiliateIssues)) {
        echo "\nAffiliate Files:\n";
        foreach ($affiliateIssues as $file => $issues) {
            echo "   $file:\n";
            foreach ($issues as $issue) {
                echo "     - Xóa '$issue' và sử dụng AffiliateModel\n";
            }
        }
    }
    
} else {
    echo "   🎉 KHÔNG CÓ VẤN ĐỀ NÀO!\n";
    echo "   Tất cả files đã clean và sẵn sàng.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

// Return issues for automated fixing
return [
    'js' => $jsIssues,
    'php' => $phpIssues, 
    'affiliate' => $affiliateIssues
];