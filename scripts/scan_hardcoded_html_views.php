<?php
/**
 * Scan Hardcoded HTML in Views
 * Quét các file view có hardcode HTML thay vì dùng database
 */

echo "=== QUÉT HARDCODED HTML TRONG VIEWS ===\n\n";

function scanDirectory($dir, $level = 0) {
    $results = [];
    $items = glob($dir . '/*');
    
    foreach ($items as $item) {
        if (is_dir($item)) {
            $results = array_merge($results, scanDirectory($item, $level + 1));
        } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'php') {
            $results[] = $item;
        }
    }
    
    return $results;
}

function analyzeViewFile($filePath) {
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    
    $issues = [];
    $hasModels = false;
    $hasDatabase = false;
    $hardcodedContent = 0;
    
    // Check if file uses Models
    if (preg_match('/require_once.*Model\.php|new \w+Model\(\)|\$\w+Model/', $content)) {
        $hasModels = true;
    }
    
    // Check if file uses database
    if (preg_match('/getAll\(\)|getById\(\)|database|sql/i', $content)) {
        $hasDatabase = true;
    }
    
    foreach ($lines as $lineNum => $line) {
        $lineNum++; // 1-based
        $trimmed = trim($line);
        
        // Skip empty lines and comments
        if (empty($trimmed) || strpos($trimmed, '//') === 0 || strpos($trimmed, '/*') === 0) {
            continue;
        }
        
        // Check for hardcoded content patterns
        
        // 1. Hardcoded text content in HTML
        if (preg_match('/<(h[1-6]|p|span|div)[^>]*>[^<]*[a-zA-ZÀ-ỹ]{10,}[^<]*<\//', $line)) {
            if (!preg_match('/\$\w+|\{\{|\<\?php/', $line)) {
                $hardcodedContent++;
                $issues[] = "Line $lineNum: Hardcoded text content - " . substr(trim($line), 0, 80) . "...";
            }
        }
        
        // 2. Hardcoded list items
        if (preg_match('/<li[^>]*>[^<]*[a-zA-ZÀ-ỹ]{5,}[^<]*<\/li>/', $line)) {
            if (!preg_match('/\$\w+|\{\{|\<\?php/', $line)) {
                $hardcodedContent++;
                $issues[] = "Line $lineNum: Hardcoded list item - " . substr(trim($line), 0, 80) . "...";
            }
        }
        
        // 3. Hardcoded table data
        if (preg_match('/<td[^>]*>[^<]*[a-zA-ZÀ-ỹ]{3,}[^<]*<\/td>/', $line)) {
            if (!preg_match('/\$\w+|\{\{|\<\?php/', $line)) {
                $hardcodedContent++;
                $issues[] = "Line $lineNum: Hardcoded table data - " . substr(trim($line), 0, 80) . "...";
            }
        }
        
        // 4. Hardcoded card/section content
        if (preg_match('/<(div|section)[^>]*class="[^"]*card[^"]*"[^>]*>/', $line)) {
            // Check next few lines for hardcoded content
            for ($i = 1; $i <= 5; $i++) {
                if (isset($lines[$lineNum + $i - 1])) {
                    $nextLine = $lines[$lineNum + $i - 1];
                    if (preg_match('/>[^<]*[a-zA-ZÀ-ỹ]{10,}[^<]*</', $nextLine) && 
                        !preg_match('/\$\w+|\{\{|\<\?php/', $nextLine)) {
                        $hardcodedContent++;
                        $issues[] = "Line " . ($lineNum + $i) . ": Hardcoded card content - " . substr(trim($nextLine), 0, 80) . "...";
                        break;
                    }
                }
            }
        }
        
        // 5. Hardcoded navigation items
        if (preg_match('/<a[^>]*href="[^"]*"[^>]*>[^<]*[a-zA-ZÀ-ỹ]{3,}[^<]*<\/a>/', $line)) {
            if (!preg_match('/\$\w+|\{\{|\<\?php/', $line) && 
                !preg_match('/(href="[#\/]|onclick=)/', $line)) {
                $issues[] = "Line $lineNum: Hardcoded navigation - " . substr(trim($line), 0, 80) . "...";
            }
        }
    }
    
    return [
        'hasModels' => $hasModels,
        'hasDatabase' => $hasDatabase,
        'hardcodedCount' => $hardcodedContent,
        'issues' => array_slice($issues, 0, 10), // Limit to 10 issues per file
        'totalIssues' => count($issues)
    ];
}

// Scan all view files
$viewsDir = 'app/views';
$allViewFiles = scanDirectory($viewsDir);

$totalFiles = 0;
$filesWithIssues = 0;
$filesWithModels = 0;
$totalIssues = 0;

$categorizedFiles = [
    'clean' => [], // Files using Models/Database
    'mixed' => [], // Files with some Models but also hardcoded content
    'hardcoded' => [], // Files with mostly hardcoded content
    'static' => [] // Files that should be static (like about, contact)
];

echo "📊 PHÂN TÍCH CÁC FILE VIEWS:\n";
echo str_repeat('-', 80) . "\n";

foreach ($allViewFiles as $file) {
    $totalFiles++;
    $relativePath = str_replace('app/views/', '', $file);
    
    $analysis = analyzeViewFile($file);
    
    if ($analysis['totalIssues'] > 0) {
        $filesWithIssues++;
        $totalIssues += $analysis['totalIssues'];
    }
    
    if ($analysis['hasModels']) {
        $filesWithModels++;
    }
    
    // Categorize files
    $category = 'hardcoded';
    
    if ($analysis['hasModels'] && $analysis['hardcodedCount'] < 5) {
        $category = 'clean';
    } elseif ($analysis['hasModels'] && $analysis['hardcodedCount'] >= 5) {
        $category = 'mixed';
    } elseif (preg_match('/(about|contact|auth\/login|auth\/register)/', $relativePath)) {
        $category = 'static';
    }
    
    $categorizedFiles[$category][] = [
        'path' => $relativePath,
        'analysis' => $analysis
    ];
}

// Display results by category
foreach ($categorizedFiles as $category => $files) {
    if (empty($files)) continue;
    
    $categoryNames = [
        'clean' => '✅ CLEAN - Sử dụng Models/Database',
        'mixed' => '🔄 MIXED - Có Models nhưng còn hardcode',
        'hardcoded' => '❌ HARDCODED - Chủ yếu hardcode HTML',
        'static' => '⚪ STATIC - Nên giữ static'
    ];
    
    echo "\n" . $categoryNames[$category] . " (" . count($files) . " files):\n";
    echo str_repeat('-', 60) . "\n";
    
    foreach ($files as $fileInfo) {
        $path = $fileInfo['path'];
        $analysis = $fileInfo['analysis'];
        
        echo "📁 $path\n";
        echo "   Models: " . ($analysis['hasModels'] ? '✅' : '❌') . 
             " | Database: " . ($analysis['hasDatabase'] ? '✅' : '❌') . 
             " | Hardcoded: {$analysis['hardcodedCount']} issues\n";
        
        if (!empty($analysis['issues']) && $category !== 'static') {
            echo "   Vấn đề:\n";
            foreach (array_slice($analysis['issues'], 0, 3) as $issue) {
                echo "     - $issue\n";
            }
            if ($analysis['totalIssues'] > 3) {
                echo "     - ... và " . ($analysis['totalIssues'] - 3) . " vấn đề khác\n";
            }
        }
        echo "\n";
    }
}

echo "\n📈 THỐNG KÊ TỔNG QUAN:\n";
echo str_repeat('-', 60) . "\n";
echo "📊 Tổng số files: $totalFiles\n";
echo "✅ Files sử dụng Models: $filesWithModels (" . round($filesWithModels/$totalFiles*100, 1) . "%)\n";
echo "❌ Files có hardcoded content: $filesWithIssues (" . round($filesWithIssues/$totalFiles*100, 1) . "%)\n";
echo "🔢 Tổng số vấn đề hardcode: $totalIssues\n\n";

echo "🎯 KHUYẾN NGHỊ:\n";
echo str_repeat('-', 60) . "\n";
echo "1. Ưu tiên sửa files HARDCODED (" . count($categorizedFiles['hardcoded']) . " files)\n";
echo "2. Tiếp tục sửa files MIXED (" . count($categorizedFiles['mixed']) . " files)\n";
echo "3. Giữ nguyên files STATIC (" . count($categorizedFiles['static']) . " files)\n";
echo "4. Kiểm tra lại files CLEAN (" . count($categorizedFiles['clean']) . " files)\n\n";

echo "🚀 MỤC TIÊU: Chuyển tất cả hardcoded content sang database-driven content!\n";
?>