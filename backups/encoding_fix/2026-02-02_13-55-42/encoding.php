<?php
/**
 * File Encoding Validation and Conversion Tools
 * Thuong Lo Website - UTF-8 Encoding Fix
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
        'content_length' => strlen($content)
    ];
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
    
    // Remove BOM if not requested and present
    if (!$addBOM && substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
    }
    
    return file_put_contents($filepath, $content) !== false;
}

/**
 * Scan directory for PHP files and validate encoding
 * @param string $directory Directory to scan
 * @param bool $recursive Whether to scan recursively
 * @return array Validation results for all files
 */
function scanDirectoryEncoding($directory = '.', $recursive = true) {
    $results = [];
    
    if ($recursive) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
    } else {
        $iterator = new DirectoryIterator($directory);
    }
    
    foreach ($iterator as $file) {
        if ($file->isFile() && pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php') {
            $filepath = $file->getPathname();
            $results[$filepath] = validateFileEncoding($filepath);
        }
    }
    
    return $results;
}

/**
 * Create backup of files before conversion
 * @param array $files Array of file paths
 * @param string $backupDir Backup directory
 * @return bool Success status
 */
function createBackup($files, $backupDir = 'backups/encoding_fix') {
    if (!is_dir($backupDir)) {
        if (!mkdir($backupDir, 0755, true)) {
            return false;
        }
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $backupSubDir = $backupDir . '/' . $timestamp;
    
    if (!mkdir($backupSubDir, 0755, true)) {
        return false;
    }
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            $backupPath = $backupSubDir . '/' . basename($file);
            if (!copy($file, $backupPath)) {
                return false;
            }
        }
    }
    
    return true;
}

/**
 * Generate encoding report
 * @param array $scanResults Results from scanDirectoryEncoding
 * @return string HTML report
 */
function generateEncodingReport($scanResults) {
    $html = "<h2>File Encoding Report</h2>\n";
    $html .= "<table border='1' cellpadding='5' cellspacing='0'>\n";
    $html .= "<tr><th>File</th><th>Valid UTF-8</th><th>Has BOM</th><th>Detected Encoding</th><th>Size</th></tr>\n";
    
    foreach ($scanResults as $file => $result) {
        $validUTF8 = $result['valid_utf8'] ? 'Yes' : 'No';
        $hasBOM = $result['has_bom'] ? 'Yes' : 'No';
        $encoding = $result['encoding'] ?: 'Unknown';
        $size = isset($result['file_size']) ? number_format($result['file_size']) . ' bytes' : 'Unknown';
        
        $rowClass = $result['valid_utf8'] ? '' : ' style="background-color: #ffcccc;"';
        
        $html .= "<tr{$rowClass}>";
        $html .= "<td>" . htmlspecialchars($file) . "</td>";
        $html .= "<td>{$validUTF8}</td>";
        $html .= "<td>{$hasBOM}</td>";
        $html .= "<td>{$encoding}</td>";
        $html .= "<td>{$size}</td>";
        $html .= "</tr>\n";
    }
    
    $html .= "</table>\n";
    
    return $html;
}