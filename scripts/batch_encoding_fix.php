<?php
/**
 * Batch Encoding Fix Script
 * For Thuong Lo Website - UTF-8 Encoding Fix
 */

require_once 'validate_encoding.php';

/**
 * Batch process all PHP files for encoding fixes
 */
function batchFixEncoding($dryRun = true) {
    $directories = [
        'app',
        'core',
        'errors',
        '.'  // Root directory for index.php, config.php, etc.
    ];
    
    $allFiles = [];
    $issueFiles = [];
    
    echo "=== Batch Encoding Fix Tool ===\n";
    echo "Mode: " . ($dryRun ? "DRY RUN (no changes)" : "LIVE (will modify files)") . "\n\n";
    
    // Scan all directories
    foreach ($directories as $dir) {
        if (!is_dir($dir)) continue;
        
        echo "Scanning directory: $dir\n";
        $results = scanDirectoryForEncoding($dir);
        
        foreach ($results as $file => $result) {
            $allFiles[] = $file;
            
            // Check if file needs fixing
            $needsFix = false;
            $reasons = [];
            
            if (!$result['valid_utf8']) {
                $needsFix = true;
                $reasons[] = "Invalid UTF-8";
            }
            
            if ($result['contains_vietnamese'] && !$result['has_bom']) {
                $needsFix = true;
                $reasons[] = "Vietnamese content without BOM";
            }
            
            if ($result['encoding'] && $result['encoding'] !== 'UTF-8') {
                $needsFix = true;
                $reasons[] = "Non-UTF-8 encoding: " . $result['encoding'];
            }
            
            if ($needsFix) {
                $issueFiles[] = [
                    'file' => $file,
                    'reasons' => $reasons,
                    'result' => $result
                ];
            }
        }
    }
    
    echo "\n=== Scan Results ===\n";
    echo "Total PHP files scanned: " . count($allFiles) . "\n";
    echo "Files needing fixes: " . count($issueFiles) . "\n\n";
    
    if (empty($issueFiles)) {
        echo "✅ All files are properly encoded!\n";
        return true;
    }
    
    // Display issues
    echo "Files with encoding issues:\n";
    foreach ($issueFiles as $issue) {
        echo "📄 " . $issue['file'] . "\n";
        foreach ($issue['reasons'] as $reason) {
            echo "   ⚠️  $reason\n";
        }
        echo "\n";
    }
    
    if ($dryRun) {
        echo "This was a dry run. To apply fixes, run with --fix parameter\n";
        return false;
    }
    
    // Create backup
    echo "Creating backup...\n";
    $filesToBackup = array_column($issueFiles, 'file');
    $backupDir = createBackup($filesToBackup);
    echo "Backup created in: $backupDir\n\n";
    
    // Apply fixes
    echo "Applying fixes...\n";
    $fixedCount = 0;
    
    foreach ($issueFiles as $issue) {
        $file = $issue['file'];
        echo "Fixing: $file\n";
        
        if (convertToUTF8WithBOM($file, true)) {
            $fixedCount++;
            echo "  ✅ Fixed\n";
        } else {
            echo "  ❌ Failed to fix\n";
        }
    }
    
    echo "\n=== Fix Results ===\n";
    echo "Files fixed: $fixedCount / " . count($issueFiles) . "\n";
    
    // Validate fixes
    echo "\nValidating fixes...\n";
    $validationErrors = 0;
    
    foreach ($issueFiles as $issue) {
        $file = $issue['file'];
        $newResult = validateFileEncoding($file);
        
        if (!$newResult['valid_utf8']) {
            echo "❌ $file still has encoding issues\n";
            $validationErrors++;
        }
    }
    
    if ($validationErrors === 0) {
        echo "✅ All fixes validated successfully!\n";
    } else {
        echo "⚠️  $validationErrors files still have issues\n";
    }
    
    return $validationErrors === 0;
}

/**
 * Test encoding validation tools
 */
function testValidationTools() {
    echo "=== Testing Validation Tools ===\n\n";
    
    // Test with a sample file
    $testFile = 'config.php';
    if (file_exists($testFile)) {
        echo "Testing with file: $testFile\n";
        $result = validateFileEncoding($testFile);
        
        echo "Results:\n";
        echo "  Valid UTF-8: " . ($result['valid_utf8'] ? 'Yes' : 'No') . "\n";
        echo "  Has BOM: " . ($result['has_bom'] ? 'Yes' : 'No') . "\n";
        echo "  Encoding: " . ($result['encoding'] ?? 'Unknown') . "\n";
        echo "  Contains Vietnamese: " . ($result['contains_vietnamese'] ? 'Yes' : 'No') . "\n";
        echo "  File size: " . $result['file_size'] . " bytes\n";
    } else {
        echo "Test file not found: $testFile\n";
    }
    
    echo "\n✅ Validation tools test completed\n";
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $command = $argv[1] ?? 'scan';
    
    switch ($command) {
        case 'scan':
            batchFixEncoding(true);
            break;
            
        case 'fix':
            batchFixEncoding(false);
            break;
            
        case 'test':
            testValidationTools();
            break;
            
        default:
            echo "Usage:\n";
            echo "  php batch_encoding_fix.php scan   - Scan for encoding issues (dry run)\n";
            echo "  php batch_encoding_fix.php fix    - Fix encoding issues\n";
            echo "  php batch_encoding_fix.php test   - Test validation tools\n";
    }
} else {
    // Web interface
    echo "<h1>Batch Encoding Fix Tool</h1>";
    echo "<p>This tool must be run from command line for security reasons.</p>";
    echo "<p>Use: <code>php batch_encoding_fix.php scan</code></p>";
}
?>