<?php
/**
 * Check JavaScript Assets for Legacy Code
 * Kiểm tra các file JS trong assets/ để tìm JSON logic, fake data, core/affiliate imports
 */

echo "=== KIỂM TRA JAVASCRIPT ASSETS ===\n\n";

$jsDir = 'assets/js/';
$jsFiles = glob($jsDir . '*.js');

$totalIssues = 0;

foreach ($jsFiles as $file) {
    echo "🔍 KIỂM TRA: $file\n";
    echo str_repeat('-', 60) . "\n";
    
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    
    $issues = [];
    
    foreach ($lines as $lineNum => $line) {
        $lineNum++; // 1-based line numbers
        $trimmedLine = trim($line);
        
        // Skip empty lines and comments
        if (empty($trimmedLine) || strpos($trimmedLine, '//') === 0 || strpos($trimmedLine, '/*') === 0) {
            continue;
        }
        
        // Check for JSON.parse() usage
        if (preg_match('/JSON\.parse\s*\(/', $line)) {
            $issues[] = "Line $lineNum: JSON.parse() usage - " . trim($line);
        }
        
        // Check for hardcoded fake data arrays
        if (preg_match('/(?:var|let|const)\s+\w+\s*=\s*\[/', $line) && 
            preg_match('/(fake|demo|test|sample|mock)/i', $line)) {
            $issues[] = "Line $lineNum: Fake data array - " . trim($line);
        }
        
        // Check for large hardcoded data arrays (more than 3 elements)
        if (preg_match('/(?:var|let|const)\s+\w+\s*=\s*\[([^[\]]*,){3,}/', $line)) {
            $issues[] = "Line $lineNum: Large hardcoded array - " . trim($line);
        }
        
        // Check for core/ imports or references
        if (preg_match('/(import|require|include).*[\'"].*core\//', $line)) {
            $issues[] = "Line $lineNum: Core import - " . trim($line);
        }
        
        // Check for affiliate data loader references
        if (preg_match('/(AffiliateDataLoader|AffiliateErrorHandler)/i', $line)) {
            $issues[] = "Line $lineNum: Old affiliate reference - " . trim($line);
        }
        
        // Check for hardcoded Vietnamese data
        if (preg_match('/[\'"][^\'\"]*(?:sản phẩm|dịch vụ|khóa học|đơn hàng|người dùng)[^\'\"]*[\'\"]/', $line) &&
            preg_match('/(?:var|let|const|\[)/', $line)) {
            $issues[] = "Line $lineNum: Hardcoded Vietnamese data - " . trim($line);
        }
        
        // Check for old JSON file references
        if (preg_match('/[\'"][^\'\"]*\.json[\'"]/', $line)) {
            $issues[] = "Line $lineNum: JSON file reference - " . trim($line);
        }
        
        // Check for fetch() calls to JSON endpoints
        if (preg_match('/fetch\s*\([^)]*\.json/', $line)) {
            $issues[] = "Line $lineNum: Fetch JSON endpoint - " . trim($line);
        }
        
        // Check for XMLHttpRequest to JSON
        if (preg_match('/XMLHttpRequest.*\.json|\.open\([^)]*\.json/', $line)) {
            $issues[] = "Line $lineNum: XMLHttpRequest to JSON - " . trim($line);
        }
    }
    
    if (empty($issues)) {
        echo "✅ Không phát hiện vấn đề nào\n";
    } else {
        echo "⚠️ Phát hiện " . count($issues) . " vấn đề:\n";
        foreach ($issues as $issue) {
            echo "   - $issue\n";
        }
        $totalIssues += count($issues);
    }
    
    echo "\n";
}

echo "🎯 TỔNG KẾT:\n";
echo "Đã kiểm tra " . count($jsFiles) . " files JavaScript.\n";
echo "Tổng cộng phát hiện $totalIssues vấn đề cần xử lý.\n\n";

if ($totalIssues > 0) {
    echo "📋 KHUYẾN NGHỊ:\n";
    echo "1. Thay thế JSON.parse() bằng API calls\n";
    echo "2. Xóa các mảng fake data hardcoded\n";
    echo "3. Cập nhật các import/require cũ\n";
    echo "4. Chuyển hardcoded data sang dynamic loading\n";
}
?>