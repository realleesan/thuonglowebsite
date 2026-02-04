<?php
/**
 * Post-deployment Validation Script
 * Thuong Lo Website - UTF-8 Encoding Fix
 */

require_once __DIR__ . '/../core/encoding.php';

echo "=== Post-deployment Validation ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

$errors = [];
$warnings = [];

// 1. Test website accessibility
echo "1. Testing website accessibility...\n";
$baseUrl = 'https://test1.web3b.com'; // Update with actual hosting URL

$testPages = [
    '/' => 'Home page',
    '/about' => 'About page',
    '/contact' => 'Contact page',
    '/auth/login' => 'Login page'
];

foreach ($testPages as $path => $description) {
    $url = $baseUrl . $path;
    
    // Use curl to test if available, otherwise skip
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            // Check if charset is in content-type header
            if (stripos($contentType, 'charset=utf-8') !== false) {
                echo "✅ $description - Accessible with UTF-8 charset\n";
            } else {
                $warnings[] = "$description - Accessible but charset not detected in headers";
                echo "⚠️  $description - Accessible but charset not in headers\n";
            }
        } else {
            $errors[] = "$description - HTTP $httpCode error";
            echo "❌ $description - HTTP $httpCode error\n";
        }
    } else {
        echo "⚠️  Skipping URL tests - curl not available\n";
        break;
    }
}

// 2. Re-validate file encoding after deployment
echo "\n2. Re-validating file encoding...\n";
$scanResults = scanDirectoryEncoding('.', true);
$encodingIssues = 0;

foreach ($scanResults as $file => $result) {
    if (!$result['valid_utf8']) {
        $encodingIssues++;
        $errors[] = "File encoding corrupted during deployment: $file";
    }
}

if ($encodingIssues === 0) {
    echo "✅ All files maintained UTF-8 encoding after deployment\n";
} else {
    echo "❌ $encodingIssues files have encoding issues after deployment\n";
}

// 3. Test file editing capability (if possible)
echo "\n3. Testing file editing capability...\n";
$testFile = 'tests/deployment_test.php';
$testContent = "<?php\n// Deployment test with Vietnamese: Xin chào\necho 'Test successful';\n";

// Create test file
if (file_put_contents($testFile, $testContent)) {
    // Read it back
    $readContent = file_get_contents($testFile);
    
    if ($readContent === $testContent) {
        echo "✅ File creation and reading works correctly\n";
        
        // Test encoding
        $encoding = validateFileEncoding($testFile);
        if ($encoding['valid_utf8']) {
            echo "✅ Test file maintains UTF-8 encoding\n";
        } else {
            $errors[] = "Test file encoding corrupted";
            echo "❌ Test file encoding corrupted\n";
        }
    } else {
        $errors[] = "File content corrupted during read/write";
        echo "❌ File content corrupted during read/write\n";
    }
    
    // Clean up
    unlink($testFile);
} else {
    $warnings[] = "Cannot create test file - check write permissions";
    echo "⚠️  Cannot create test file - check write permissions\n";
}

// 4. Check server headers (if possible)
echo "\n4. Checking server headers...\n";
if (function_exists('get_headers')) {
    $headers = get_headers($baseUrl . '/', 1);
    
    $charsetFound = false;
    if (isset($headers['Content-Type'])) {
        $contentType = is_array($headers['Content-Type']) ? 
                      end($headers['Content-Type']) : $headers['Content-Type'];
        
        if (stripos($contentType, 'charset=utf-8') !== false) {
            $charsetFound = true;
            echo "✅ Server returns UTF-8 charset in headers\n";
        }
    }
    
    if (!$charsetFound) {
        $warnings[] = "Server headers don't explicitly specify UTF-8 charset";
        echo "⚠️  Server headers don't explicitly specify UTF-8 charset\n";
    }
} else {
    echo "⚠️  Cannot check server headers - get_headers not available\n";
}

// 5. Test Vietnamese content display
echo "\n5. Testing Vietnamese content display...\n";
$vietnameseTest = "Xin chào từ Thương Lộ - Nền tảng học trực tuyến";

if (mb_check_encoding($vietnameseTest, 'UTF-8')) {
    echo "✅ Vietnamese content encoding is valid\n";
    
    // Test length calculation
    $byteLength = strlen($vietnameseTest);
    $charLength = mb_strlen($vietnameseTest, 'UTF-8');
    
    if ($charLength < $byteLength) {
        echo "✅ Multi-byte character handling is working (chars: $charLength, bytes: $byteLength)\n";
    } else {
        $warnings[] = "Multi-byte character handling may not be working correctly";
        echo "⚠️  Multi-byte character handling may not be working correctly\n";
    }
} else {
    $errors[] = "Vietnamese content encoding test failed";
    echo "❌ Vietnamese content encoding test failed\n";
}

// Summary
echo "\n=== Post-deployment Summary ===\n";
echo "Errors: " . count($errors) . "\n";
echo "Warnings: " . count($warnings) . "\n";

if (!empty($errors)) {
    echo "\n=== ERRORS (Need immediate attention) ===\n";
    foreach ($errors as $error) {
        echo "❌ $error\n";
    }
}

if (!empty($warnings)) {
    echo "\n=== WARNINGS (Monitor and consider fixing) ===\n";
    foreach ($warnings as $warning) {
        echo "⚠️  $warning\n";
    }
}

$deploymentSuccess = empty($errors);
echo "\n=== DEPLOYMENT VALIDATION RESULT ===\n";
if ($deploymentSuccess) {
    echo "✅ DEPLOYMENT SUCCESSFUL - Encoding fixes are working\n";
    exit(0);
} else {
    echo "❌ DEPLOYMENT ISSUES DETECTED - Review errors above\n";
    exit(1);
}