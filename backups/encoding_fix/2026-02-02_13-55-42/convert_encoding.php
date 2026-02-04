<?php
/**
 * Batch Encoding Conversion Script
 * Thuong Lo Website - UTF-8 Encoding Fix
 */

require_once __DIR__ . '/../core/encoding.php';

// Configuration
$addBOM = true; // Set to true if hosting editor requires BOM
$createBackupFirst = true;
$dryRun = false; // Set to true to see what would be converted without actually doing it

// Get files to convert from command line or scan
$filesToConvert = [];

if ($argc > 1) {
    // Files specified as command line arguments
    for ($i = 1; $i < $argc; $i++) {
        if (file_exists($argv[$i])) {
            $filesToConvert[] = $argv[$i];
        }
    }
} else {
    // Scan for files that need conversion
    echo "Scanning for files that need encoding conversion...\n";
    $scanResults = scanDirectoryEncoding('.', true);
    
    foreach ($scanResults as $file => $result) {
        if (!$result['valid_utf8'] || ($addBOM && !$result['has_bom'])) {
            $filesToConvert[] = $file;
        }
    }
}

if (empty($filesToConvert)) {
    echo "No files need encoding conversion.\n";
    exit(0);
}

echo "Files to convert: " . count($filesToConvert) . "\n";

if ($dryRun) {
    echo "\n=== DRY RUN MODE - No files will be modified ===\n";
}

foreach ($filesToConvert as $file) {
    echo "- $file\n";
}

if (!$dryRun) {
    echo "\nProceed with conversion? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) !== 'y') {
        echo "Conversion cancelled.\n";
        exit(0);
    }
}

// Create backup if requested
if ($createBackupFirst && !$dryRun) {
    echo "\nCreating backup...\n";
    if (createBackup($filesToConvert)) {
        echo "Backup created successfully.\n";
    } else {
        echo "Failed to create backup. Aborting.\n";
        exit(1);
    }
}

// Convert files
$converted = 0;
$failed = 0;

echo "\nConverting files...\n";

foreach ($filesToConvert as $file) {
    if ($dryRun) {
        echo "Would convert: $file\n";
        $converted++;
    } else {
        $before = validateFileEncoding($file);
        
        if (convertToUTF8WithBOM($file, $addBOM)) {
            $after = validateFileEncoding($file);
            
            $bomStatus = $after['has_bom'] ? 'with BOM' : 'without BOM';
            echo "✅ Converted: $file -> UTF-8 ($bomStatus)\n";
            $converted++;
        } else {
            echo "❌ Failed: $file\n";
            $failed++;
        }
    }
}

echo "\n=== Conversion Summary ===\n";
echo "Files converted: $converted\n";
echo "Files failed: $failed\n";

if (!$dryRun && $converted > 0) {
    echo "\nRunning post-conversion validation...\n";
    
    $postResults = [];
    foreach ($filesToConvert as $file) {
        $postResults[$file] = validateFileEncoding($file);
    }
    
    $stillInvalid = 0;
    foreach ($postResults as $file => $result) {
        if (!$result['valid_utf8']) {
            echo "❌ Still invalid: $file\n";
            $stillInvalid++;
        }
    }
    
    if ($stillInvalid === 0) {
        echo "✅ All files successfully converted to valid UTF-8!\n";
    } else {
        echo "⚠️  $stillInvalid files still have encoding issues.\n";
    }
}