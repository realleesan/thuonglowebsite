<?php
/**
 * Check Hardcoded Data Script
 * Tìm các file PHP đang hardcode dữ liệu thay vì sử dụng SQL
 */

echo "=== KIỂM TRA HARDCODED DATA TRONG VIEWS ===\n\n";

// Lấy tất cả file PHP trong views
$viewFiles = array_merge(
    glob('app/views/**/*.php'),
    glob('app/views/*/*.php'),
    glob('app/views/*.php')
);

$hardcodedFiles = [];
$modelFiles = [];
$mixedFiles = [];

foreach ($viewFiles as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    $filename = str_replace(getcwd() . '/', '', $file);
    
    // Skip layout files
    if (strpos($file, '_layout') !== false) continue;
    
    $hasModel = false;
    $hasHardcode = false;
    $hardcodePatterns = [];
    
    // Kiểm tra có sử dụng Models không
    if (preg_match('/require.*Model\.php/i', $content) || 
        preg_match('/new\s+\w+Model/i', $content) ||
        preg_match('/\$\w+Model/i', $content)) {
        $hasModel = true;
    }
    
    // Kiểm tra các pattern hardcode phổ biến
    $patterns = [
        'hardcoded_arrays' => '/\$\w+\s*=\s*\[[\s\S]*?["\'].*?["\'][\s\S]*?\];/m',
        'hardcoded_options' => '/<option[^>]*value=["\'][^"\']*["\'][^>]*>[^<]+<\/option>/i',
        'hardcoded_table_rows' => '/<tr[^>]*>[\s\S]*?<td[^>]*>[^<]*[a-zA-Z0-9][^<]*<\/td>[\s\S]*?<\/tr>/i',
        'hardcoded_list_items' => '/<li[^>]*>[^<]*[a-zA-Z0-9][^<]*<\/li>/i',
        'hardcoded_cards' => '/<div[^>]*class=["\'][^"\']*card[^"\']*["\'][^>]*>[\s\S]*?[a-zA-Z0-9][\s\S]*?<\/div>/i',
        'static_data_arrays' => '/\$\w+\s*=\s*\[[\s\S]*?["\']id["\']\s*=>\s*\d+[\s\S]*?\];/m'
    ];
    
    foreach ($patterns as $patternName => $pattern) {
        if (preg_match($pattern, $content)) {
            $hasHardcode = true;
            $hardcodePatterns[] = $patternName;
        }
    }
    
    // Kiểm tra hardcode đặc biệt - dữ liệu trong HTML
    $htmlDataPatterns = [
        'table_with_static_data' => '/<table[\s\S]*?<tbody[\s\S]*?<tr[\s\S]*?<td[^>]*>[^<]*(?:Nguyễn|Trần|Lê|Phạm|Hoàng|Huỳnh|Phan|Vũ|Võ|Đặng|Bùi|Đỗ|Hồ|Ngô|Dương|Lý)[^<]*<\/td>[\s\S]*?<\/tr>[\s\S]*?<\/tbody>[\s\S]*?<\/table>/i',
        'product_cards_static' => '/<div[^>]*class=["\'][^"\']*product[^"\']*["\'][^>]*>[\s\S]*?<h[1-6][^>]*>[^<]*(?:Sản phẩm|Product|Khóa học|Course)[^<]*<\/h[1-6]>[\s\S]*?<\/div>/i',
        'news_items_static' => '/<div[^>]*class=["\'][^"\']*news[^"\']*["\'][^>]*>[\s\S]*?<h[1-6][^>]*>[^<]*[a-zA-Z0-9][^<]*<\/h[1-6]>[\s\S]*?<\/div>/i',
        'user_info_static' => '/<div[^>]*class=["\'][^"\']*user[^"\']*["\'][^>]*>[\s\S]*?(?:admin@|user@|test@)[^<]*[\s\S]*?<\/div>/i'
    ];
    
    foreach ($htmlDataPatterns as $patternName => $pattern) {
        if (preg_match($pattern, $content)) {
            $hasHardcode = true;
            $hardcodePatterns[] = $patternName;
        }
    }
    
    // Phân loại file
    if ($hasModel && $hasHardcode) {
        $mixedFiles[$filename] = $hardcodePatterns;
    } elseif ($hasModel && !$hasHardcode) {
        $modelFiles[] = $filename;
    } elseif (!$hasModel && $hasHardcode) {
        $hardcodedFiles[$filename] = $hardcodePatterns;
    }
}

// Báo cáo kết quả
echo "📊 THỐNG KÊ:\n";
echo "   - Files sử dụng Models: " . count($modelFiles) . "\n";
echo "   - Files hardcode hoàn toàn: " . count($hardcodedFiles) . "\n";
echo "   - Files mixed (Model + hardcode): " . count($mixedFiles) . "\n";
echo "   - Tổng files kiểm tra: " . (count($modelFiles) + count($hardcodedFiles) + count($mixedFiles)) . "\n\n";

// Chi tiết files hardcode
if (!empty($hardcodedFiles)) {
    echo "❌ FILES HARDCODE HOÀN TOÀN:\n";
    foreach ($hardcodedFiles as $file => $patterns) {
        echo "   $file\n";
        foreach ($patterns as $pattern) {
            echo "     - $pattern\n";
        }
        echo "\n";
    }
}

// Chi tiết files mixed
if (!empty($mixedFiles)) {
    echo "⚠️ FILES MIXED (CÓ MODEL NHƯNG VẪN CÒN HARDCODE):\n";
    foreach ($mixedFiles as $file => $patterns) {
        echo "   $file\n";
        foreach ($patterns as $pattern) {
            echo "     - $pattern\n";
        }
        echo "\n";
    }
}

// Files tốt
if (!empty($modelFiles)) {
    echo "✅ FILES SỬ DỤNG MODELS (SAMPLE):\n";
    $sampleFiles = array_slice($modelFiles, 0, 10);
    foreach ($sampleFiles as $file) {
        echo "   $file\n";
    }
    if (count($modelFiles) > 10) {
        echo "   ... và " . (count($modelFiles) - 10) . " files khác\n";
    }
    echo "\n";
}

// Tổng kết và đề xuất
echo str_repeat("=", 60) . "\n";
echo "🎯 TỔNG KẾT:\n";

$totalIssues = count($hardcodedFiles) + count($mixedFiles);
$totalFiles = count($modelFiles) + count($hardcodedFiles) + count($mixedFiles);
$completionRate = round((count($modelFiles) / $totalFiles) * 100, 1);

echo "   - Tỷ lệ hoàn thành: $completionRate%\n";
echo "   - Files cần fix: $totalIssues\n\n";

if ($totalIssues > 0) {
    echo "🔧 ĐỀ XUẤT HÀNH ĐỘNG:\n";
    echo "   1. Ưu tiên fix files hardcode hoàn toàn trước\n";
    echo "   2. Sau đó fix files mixed\n";
    echo "   3. Thay thế hardcode bằng:\n";
    echo "      - Load Models tương ứng\n";
    echo "      - Sử dụng foreach loops\n";
    echo "      - Dynamic data rendering\n\n";
    
    echo "📋 CÁC BƯỚC FIX:\n";
    echo "   1. Thêm require_once cho Model tương ứng\n";
    echo "   2. Tạo instance Model và gọi getAll() hoặc methods khác\n";
    echo "   3. Thay thế hardcode HTML bằng PHP loops\n";
    echo "   4. Test để đảm bảo data hiển thị đúng\n";
} else {
    echo "🎉 HOÀN HẢO!\n";
    echo "   Tất cả files đã sử dụng Models và không còn hardcode.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

// Return data for further processing
return [
    'hardcoded' => $hardcodedFiles,
    'mixed' => $mixedFiles,
    'model_files' => $modelFiles,
    'completion_rate' => $completionRate
];