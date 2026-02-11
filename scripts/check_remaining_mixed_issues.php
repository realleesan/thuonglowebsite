<?php
/**
 * Check Remaining Mixed Issues
 * Kiểm tra chi tiết các vấn đề còn lại trong files mixed
 */

echo "=== KIỂM TRA CHI TIẾT CÁC VẤN ĐỀ CÒN LẠI ===\n\n";

$mixedFiles = [
    'app/views/admin/dashboard.php',
    'app/views/affiliate/dashboard.php', 
    'app/views/auth/auth.php',
    'app/views/users/dashboard.php',
    'app/views/categories/categories.php',
    'app/views/contact/contact.php',
    'app/views/products/products.php'
];

foreach ($mixedFiles as $file) {
    echo "🔍 KIỂM TRA: $file\n";
    echo str_repeat('-', 60) . "\n";
    
    if (!file_exists($file)) {
        echo "❌ File không tồn tại!\n\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    
    $issues = [];
    
    // Check for hardcoded arrays
    foreach ($lines as $lineNum => $line) {
        $lineNum++; // 1-based line numbers
        
        // Check for hardcoded data patterns
        if (preg_match('/\$\w+\s*=\s*\[/', $line) && 
            !preg_match('/(foreach|array_filter|array_map|array_slice)/', $line) &&
            !preg_match('/\$_(GET|POST|SESSION|COOKIE)/', $line)) {
            
            // Skip if it's loading from Models
            if (!preg_match('/(Model|getAll|getBy|findBy)/', $line)) {
                $issues[] = "Line $lineNum: Hardcoded array - " . trim($line);
            }
        }
        
        // Check for hardcoded HTML options
        if (preg_match('/<option[^>]*>.*<\/option>/', $line) && 
            !preg_match('/\$\w+/', $line)) {
            $issues[] = "Line $lineNum: Hardcoded option - " . trim($line);
        }
        
        // Check for hardcoded cards/divs with static content
        if (preg_match('/<div[^>]*class="[^"]*card[^"]*"/', $line) && 
            preg_match('/[0-9]+\s*(sản phẩm|dịch vụ|khóa học)/i', $line)) {
            $issues[] = "Line $lineNum: Hardcoded card content - " . trim($line);
        }
        
        // Check for static numbers in spans
        if (preg_match('/<span[^>]*>[0-9,]+\s*(sản phẩm|dịch vụ|khóa học|đơn hàng)/i', $line)) {
            $issues[] = "Line $lineNum: Static count - " . trim($line);
        }
    }
    
    if (empty($issues)) {
        echo "✅ Không phát hiện vấn đề nào\n";
    } else {
        echo "⚠️ Phát hiện " . count($issues) . " vấn đề:\n";
        foreach ($issues as $issue) {
            echo "   - $issue\n";
        }
    }
    
    echo "\n";
}

echo "🎯 TỔNG KẾT:\n";
echo "Đã kiểm tra " . count($mixedFiles) . " files mixed để tìm các vấn đề còn sót lại.\n\n";
?>