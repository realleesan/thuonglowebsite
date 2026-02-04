<?php
/**
 * Test Charset Headers
 * Thuong Lo Website - UTF-8 Encoding Fix
 */

require_once __DIR__ . '/../core/encoding.php';

echo "=== Charset Headers Test ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Check if headers are set correctly
echo "1. Testing PHP charset headers...\n";

// Start output buffering to capture headers
ob_start();

// Set charset headers (same as in master.php)
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Get headers
$headers = headers_list();
ob_end_clean();

$charsetHeaderFound = false;
foreach ($headers as $header) {
    if (stripos($header, 'content-type') !== false && stripos($header, 'utf-8') !== false) {
        echo "✅ Charset header found: $header\n";
        $charsetHeaderFound = true;
    }
}

if (!$charsetHeaderFound) {
    echo "❌ Charset header not found\n";
}

// Test 2: Check PHP encoding settings
echo "\n2. Testing PHP encoding settings...\n";

$internalEncoding = mb_internal_encoding();
$httpOutput = mb_http_output();
$defaultCharset = ini_get('default_charset');

echo "Internal encoding: $internalEncoding\n";
echo "HTTP output encoding: $httpOutput\n";
echo "Default charset: $defaultCharset\n";

if ($internalEncoding === 'UTF-8') {
    echo "✅ Internal encoding is UTF-8\n";
} else {
    echo "❌ Internal encoding is not UTF-8\n";
}

if ($httpOutput === 'UTF-8') {
    echo "✅ HTTP output encoding is UTF-8\n";
} else {
    echo "❌ HTTP output encoding is not UTF-8\n";
}

// Test 3: Test Vietnamese characters
echo "\n3. Testing Vietnamese character handling...\n";

$testStrings = [
    'Xin chào',
    'Tiếng Việt',
    'Học trực tuyến',
    'Thương lộ',
    'Đăng ký',
    'Tài khoản'
];

foreach ($testStrings as $str) {
    $isValidUTF8 = mb_check_encoding($str, 'UTF-8');
    $length = mb_strlen($str, 'UTF-8');
    
    if ($isValidUTF8) {
        echo "✅ '$str' - Valid UTF-8 (length: $length)\n";
    } else {
        echo "❌ '$str' - Invalid UTF-8\n";
    }
}

// Test 4: Test file encoding consistency
echo "\n4. Testing file encoding consistency...\n";

$testFiles = [
    'app/views/_layout/master.php',
    'app/views/home/home.php',
    'app/controllers/AuthController.php'
];

foreach ($testFiles as $file) {
    if (file_exists($file)) {
        $result = validateFileEncoding($file);
        $bomStatus = $result['has_bom'] ? 'with BOM' : 'without BOM';
        
        if ($result['valid_utf8']) {
            echo "✅ $file - Valid UTF-8 ($bomStatus)\n";
        } else {
            echo "❌ $file - Invalid UTF-8 (detected: {$result['encoding']})\n";
        }
    } else {
        echo "⚠️  $file - File not found\n";
    }
}

echo "\n=== Test Complete ===\n";