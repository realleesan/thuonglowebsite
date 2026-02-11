<?php
/**
 * Detailed Mixed Files Check
 * Kiểm tra chi tiết từng file mixed đã được fix đúng chưa
 */

echo "=== KIỂM TRA CHI TIẾT CÁC FILE MIXED ===\n\n";

$mixedFiles = [
    'app/views/admin/dashboard.php' => 'Admin Dashboard',
    'app/views/affiliate/dashboard.php' => 'Affiliate Dashboard', 
    'app/views/auth/auth.php' => 'Authentication System',
    'app/views/users/dashboard.php' => 'User Dashboard'
];

foreach ($mixedFiles as $file => $name) {
    echo "🔍 KIỂM TRA CHI TIẾT: $name ($file)\n";
    echo str_repeat("-", 60) . "\n";
    
    if (!file_exists($file)) {
        echo "   ❌ File không tồn tại\n\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    
    // 1. Kiểm tra Models được load
    echo "1. MODELS LOADING:\n";
    $modelIncludes = [];
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/require_once.*Model\.php/', $line)) {
            $modelIncludes[] = "   Line " . ($lineNum + 1) . ": " . trim($line);
        }
    }
    
    if (!empty($modelIncludes)) {
        echo "   ✅ Models được load:\n";
        foreach ($modelIncludes as $include) {
            echo "   $include\n";
        }
    } else {
        echo "   ❌ Không có Models nào được load\n";
    }
    
    // 2. Kiểm tra Model instances
    echo "\n2. MODEL INSTANCES:\n";
    $modelInstances = [];
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/\$\w+Model\s*=\s*new\s+\w+Model/', $line)) {
            $modelInstances[] = "   Line " . ($lineNum + 1) . ": " . trim($line);
        }
    }
    
    if (!empty($modelInstances)) {
        echo "   ✅ Model instances:\n";
        foreach ($modelInstances as $instance) {
            echo "   $instance\n";
        }
    } else {
        echo "   ❌ Không có Model instances\n";
    }
    
    // 3. Kiểm tra data loading từ Models
    echo "\n3. DATA LOADING FROM MODELS:\n";
    $dataLoading = [];
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/\$\w+\s*=\s*\$\w+Model->/', $line)) {
            $dataLoading[] = "   Line " . ($lineNum + 1) . ": " . trim($line);
        }
    }
    
    if (!empty($dataLoading)) {
        echo "   ✅ Data loading từ Models:\n";
        foreach (array_slice($dataLoading, 0, 5) as $loading) {
            echo "   $loading\n";
        }
        if (count($dataLoading) > 5) {
            echo "   ... và " . (count($dataLoading) - 5) . " dòng khác\n";
        }
    } else {
        echo "   ❌ Không có data loading từ Models\n";
    }
    
    // 4. Kiểm tra các vấn đề còn sót lại
    echo "\n4. VẤN ĐỀ CÒN SÓT LẠI:\n";
    $issues = [];
    
    // Kiểm tra $data[] references
    $dataRefs = [];
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/\$data\[/', $line)) {
            $dataRefs[] = "   Line " . ($lineNum + 1) . ": " . trim($line);
        }
    }
    
    if (!empty($dataRefs)) {
        $issues[] = "❌ Vẫn có \$data[] references:";
        foreach (array_slice($dataRefs, 0, 3) as $ref) {
            $issues[] = "   $ref";
        }
        if (count($dataRefs) > 3) {
            $issues[] = "   ... và " . (count($dataRefs) - 3) . " dòng khác";
        }
    }
    
    // Kiểm tra undefined variables trong HTML
    $undefinedVars = [];
    foreach ($lines as $lineNum => $line) {
        // Tìm các biến được sử dụng trong HTML nhưng không được định nghĩa
        if (preg_match('/echo.*\$\w+\[["\'](\w+)["\']\]/', $line, $matches)) {
            $varName = $matches[1];
            // Kiểm tra xem biến này có được định nghĩa không
            $defined = false;
            foreach ($lines as $checkLine) {
                if (preg_match('/["\']' . $varName . '["\']\s*=>/', $checkLine)) {
                    $defined = true;
                    break;
                }
            }
            if (!$defined) {
                $undefinedVars[] = "   Line " . ($lineNum + 1) . ": Undefined '$varName' in " . trim($line);
            }
        }
    }
    
    if (!empty($undefinedVars)) {
        $issues[] = "❌ Undefined variables:";
        foreach (array_slice($undefinedVars, 0, 3) as $var) {
            $issues[] = "   $var";
        }
    }
    
    // Kiểm tra hardcoded arrays với tên người Việt
    $hardcodedNames = [];
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/\[.*["\'](?:Nguyễn|Trần|Lê|Phạm|Hoàng)/', $line)) {
            $hardcodedNames[] = "   Line " . ($lineNum + 1) . ": " . trim($line);
        }
    }
    
    if (!empty($hardcodedNames)) {
        $issues[] = "❌ Hardcoded Vietnamese names:";
        foreach ($hardcodedNames as $name) {
            $issues[] = "   $name";
        }
    }
    
    if (empty($issues)) {
        echo "   ✅ Không có vấn đề nào\n";
    } else {
        foreach ($issues as $issue) {
            echo "   $issue\n";
        }
    }
    
    // 5. Tổng kết file này
    echo "\n5. TỔNG KẾT FILE:\n";
    $hasModels = !empty($modelIncludes) && !empty($modelInstances);
    $hasDataLoading = !empty($dataLoading);
    $hasIssues = !empty($issues);
    
    if ($hasModels && $hasDataLoading && !$hasIssues) {
        echo "   🎉 FILE HOÀN THÀNH - Đã chuyển đổi hoàn toàn sang Models\n";
    } elseif ($hasModels && $hasDataLoading) {
        echo "   ⚠️ FILE CẦN TINH CHỈNH - Có Models nhưng vẫn còn vấn đề nhỏ\n";
    } elseif ($hasModels) {
        echo "   🔄 FILE ĐANG CHUYỂN ĐỔI - Có Models nhưng chưa sử dụng đầy đủ\n";
    } else {
        echo "   ❌ FILE CHƯA CHUYỂN ĐỔI - Chưa có Models\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

echo "🎯 TỔNG KẾT TOÀN BỘ:\n";
echo "Đã kiểm tra chi tiết 4 files mixed để đánh giá mức độ chuyển đổi.\n";
echo "Mỗi file cần có: Models loading + Model instances + Data loading + Không có issues.\n\n";