<?php
/**
 * File Encoding Validation and Conversion Tools
 * For Thuong Lo Website - UTF-8 Encoding Fix
 */

/**
 * Validate file encoding and BOM status
 * @param string $filepath Path to the file to validate
 * @return array Validation results
 */
function validateFileEncoding($filepath) {
    if (!file_exists($filepath)) {
        return [
            'error' => 'File not found',
            'valid_utf8' => false,
            'has_bom' => false,
            'encoding' => null
        ];
    }
    
    $content = file_get_contents($filepath);
    
    // Check if file is valid UTF-8
    $isValidUTF8 = mb_check_encoding($content, 'UTF-8');
    
    // Check for BOM
    $bom = substr($content, 0, 3);
    $hasBOM = ($bom === "\xEF\xBB\xBF");
    
    // Detect encoding
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
    
    return [
        'valid_utf8' => $isValidUTF8,
        'has_bom' => $hasBOM,
        'encoding' => $encoding,
        'file_size' => filesize($filepath),
        'contains_vietnamese' => containsVietnameseChars($content)
    ];
}

/**
 * Check if content contains Vietnamese characters
 * @param string $content File content to check
 * @return bool True if Vietnamese characters found
 */
function containsVietnameseChars($content) {
    // Vietnamese characters pattern
    $vietnamesePattern = '/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđĐ]/u';
    return preg_match($vietnamesePattern, $content);
}

/**
 * Convert file to UTF-8 with BOM if needed
 * @param string $filepath Path to the file to convert
 * @param bool $addBOM Whether to add BOM
 * @return bool Success status
 */
function convertToUTF8WithBOM($filepath, $addBOM = true) {
    if (!file_exists($filepath)) {
        return false;
    }
    
    $content = file_get_contents($filepath);
    
    // Detect current encoding
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
    
    // Convert to UTF-8 if needed
    if ($encoding && $encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
    }
    
    // Add BOM if requested and not present
    if ($addBOM && substr($content, 0, 3) !== "\xEF\xBB\xBF") {
        $content = "\xEF\xBB\xBF" . $content;
    }
    
    return file_put_contents($filepath, $content) !== false;
}

/**
 * Scan directory for PHP files and validate encoding
 * @param string $directory Directory to scan
 * @return array Results for each file
 */
function scanDirectoryForEncoding($directory) {
    $results = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filepath = $file->getPathname();
            $results[$filepath] = validateFileEncoding($filepath);
        }
    }
    
    return $results;
}

/**
 * Create backup of files before conversion
 * @param array $files Array of file paths
 * @return string Backup directory path
 */
function createBackup($files) {
    $backupDir = 'backups/encoding_fix/' . date('Y-m-d_H-i-s');
    
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            $backupFile = $backupDir . '/' . basename($file);
            copy($file, $backupFile);
        }
    }
    
    return $backupDir;
}

// CLI usage
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $command = $argv[1];
    
    switch ($command) {
        case 'validate':
            $file = $argv[2] ?? '.';
            if (is_file($file)) {
                $result = validateFileEncoding($file);
                echo "File: $file\n";
                echo "Valid UTF-8: " . ($result['valid_utf8'] ? 'Yes' : 'No') . "\n";
                echo "Has BOM: " . ($result['has_bom'] ? 'Yes' : 'No') . "\n";
                echo "Encoding: " . ($result['encoding'] ?? 'Unknown') . "\n";
                echo "Contains Vietnamese: " . ($result['contains_vietnamese'] ? 'Yes' : 'No') . "\n";
            } else {
                echo "Please provide a valid file path\n";
            }
            break;
            
        case 'scan':
            $dir = $argv[2] ?? '.';
            $results = scanDirectoryForEncoding($dir);
            
            echo "Scanning directory: $dir\n";
            echo "Found " . count($results) . " PHP files\n\n";
            
            foreach ($results as $file => $result) {
                if (!$result['valid_utf8'] || $result['contains_vietnamese']) {
                    echo "File: $file\n";
                    echo "  Valid UTF-8: " . ($result['valid_utf8'] ? 'Yes' : 'No') . "\n";
                    echo "  Has BOM: " . ($result['has_bom'] ? 'Yes' : 'No') . "\n";
                    echo "  Encoding: " . ($result['encoding'] ?? 'Unknown') . "\n";
                    echo "  Contains Vietnamese: " . ($result['contains_vietnamese'] ? 'Yes' : 'No') . "\n";
                    echo "\n";
                }
            }
            break;
            
        default:
            echo "Usage:\n";
            echo "  php validate_encoding.php validate <file>\n";
            echo "  php validate_encoding.php scan <directory>\n";
    }
}
?>