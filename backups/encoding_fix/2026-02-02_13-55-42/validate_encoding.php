<?php
/**
 * Batch Encoding Validation Script
 * Thuong Lo Website - UTF-8 Encoding Fix
 */

require_once __DIR__ . '/../core/encoding.php';

// Configuration
$directories = [
    'app',
    'core',
    'api'
];

$excludePatterns = [
    '/vendor/',
    '/node_modules/',
    '/backups/',
    '/logs/'
];

echo "=== File Encoding Validation Report ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

$allResults = [];
$totalFiles = 0;
$invalidFiles = 0;

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        echo "Directory not found: $dir\n";
        continue;
    }
    
    echo "Scanning directory: $dir\n";
    $results = scanDirectoryEncoding($dir, true);
    
    foreach ($results as $file => $result) {
        // Skip excluded patterns
        $skip = false;
        foreach ($excludePatterns as $pattern) {
            if (strpos($file, $pattern) !== false) {
                $skip = true;
                break;
            }
        }
        
        if ($skip) {
            continue;
        }
        
        $allResults[$file] = $result;
        $totalFiles++;
        
        if (!$result['valid_utf8']) {
            $invalidFiles++;
            echo "  ❌ $file - Invalid UTF-8 (detected: {$result['encoding']})\n";
        } else {
            $bomStatus = $result['has_bom'] ? 'with BOM' : 'without BOM';
            echo "  ✅ $file - Valid UTF-8 ($bomStatus)\n";
        }
    }
}

echo "\n=== Summary ===\n";
echo "Total PHP files scanned: $totalFiles\n";
echo "Valid UTF-8 files: " . ($totalFiles - $invalidFiles) . "\n";
echo "Invalid files: $invalidFiles\n";

if ($invalidFiles > 0) {
    echo "\n=== Files needing conversion ===\n";
    foreach ($allResults as $file => $result) {
        if (!$result['valid_utf8']) {
            echo "- $file (detected: {$result['encoding']})\n";
        }
    }
}

// Generate HTML report
$htmlReport = generateEncodingReport($allResults);
file_put_contents('reports/encoding_report.html', $htmlReport);
echo "\nHTML report saved to: reports/encoding_report.html\n";

// Save JSON report for further processing
file_put_contents('reports/encoding_report.json', json_encode($allResults, JSON_PRETTY_PRINT));
echo "JSON report saved to: reports/encoding_report.json\n";