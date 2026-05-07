<?php
/**
 * Test PayOS Payout Signature Generation
 * This script helps debug signature issues with PayOS API
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/services/PayOSService.php';

// Test data that would be sent to PayOS payout API
$testData = [
    'referenceId' => 'RUT12345',
    'amount' => 50000,
    'description' => 'Rut tien RUT12345', // Max 25 characters
    'toBin' => '970422',
    'toAccountNumber' => '1234567890',
    'category' => ['salary'] // Array of strings, not object
];

echo "=== PayOS Payout Signature Test ===\n\n";

// Load config
$config = require __DIR__ . '/config.php';
$payosConfig = $config['payos'];

echo "Configuration:\n";
echo "- Client ID: " . substr($payosConfig['client_id'], 0, 8) . "...\n";
echo "- API Key: " . substr($payosConfig['api_key'], 0, 8) . "...\n";
echo "- Checksum Key: " . substr($payosConfig['payout_checksum_key'], 0, 10) . "...\n";
echo "- Test Mode: " . ($payosConfig['test_mode'] ? 'YES' : 'NO') . "\n\n";

echo "Test Data:\n";
echo json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Manual signature generation according to PayOS documentation
function generatePayOSSignatureManual($data, $checksumKey) {
    echo "=== Manual Signature Generation ===\n";
    
    $signatureData = [];
    
    foreach ($data as $key => $value) {
        // Handle null/undefined values as empty string
        if ($value === null) {
            $signatureData[$key] = '';
            continue;
        }
        
        // Handle arrays and objects
        if (is_array($value) || is_object($value)) {
            // Sort object keys recursively
            $sortedValue = deepSortData($value);
            $signatureData[$key] = json_encode($sortedValue, JSON_UNESCAPED_UNICODE);
        } else {
            // Convert primitive values to string
            $signatureData[$key] = (string)$value;
        }
    }
    
    // Sort keys alphabetically
    ksort($signatureData);
    
    echo "Processed Data:\n";
    print_r($signatureData);
    
    // Build query string with encodeURI equivalent (rawurlencode)
    $pairs = [];
    foreach ($signatureData as $key => $value) {
        $pairs[] = rawurlencode($key) . '=' . rawurlencode($value);
    }
    
    $dataString = implode('&', $pairs);
    echo "\nData String for HMAC:\n";
    echo $dataString . "\n\n";
    
    // Generate HMAC-SHA256 signature
    $signature = hash_hmac('sha256', $dataString, $checksumKey);
    
    echo "Generated Signature: " . $signature . "\n";
    echo "Checksum Key (first 10): " . substr($checksumKey, 0, 10) . "...\n\n";
    
    return $signature;
}

function deepSortData($data) {
    if (is_array($data)) {
        $isAssoc = array_keys($data) !== range(0, count($data) - 1);
        
        if ($isAssoc) {
            ksort($data);
        }
        
        foreach ($data as $key => $value) {
            $data[$key] = deepSortData($value);
        }
        
        return $data;
    }
    
    if (is_object($data)) {
        $arr = (array)$data;
        ksort($arr);
        foreach ($arr as $key => $value) {
            $arr[$key] = deepSortData($value);
        }
        return (object)$arr;
    }
    
    return $data;
}

// Test manual signature generation
$manualSignature = generatePayOSSignatureManual($testData, $payosConfig['payout_checksum_key']);

// Test with PayOSService
echo "=== PayOSService Signature Generation ===\n";
try {
    $payosService = new PayOSService();
    
    // Use reflection to access private method
    $reflection = new ReflectionClass($payosService);
    $method = $reflection->getMethod('generatePayoutSignature');
    $method->setAccessible(true);
    
    $serviceSignature = $method->invoke($payosService, $testData);
    
    echo "PayOSService Signature: " . $serviceSignature . "\n";
    
    // Compare signatures
    if ($manualSignature === $serviceSignature) {
        echo "\n✅ SIGNATURES MATCH! Implementation is correct.\n";
    } else {
        echo "\n❌ SIGNATURES DON'T MATCH! There's an issue.\n";
        echo "Manual:  " . $manualSignature . "\n";
        echo "Service: " . $serviceSignature . "\n";
    }
    
} catch (Exception $e) {
    echo "Error testing PayOSService: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";

// Test description generation
echo "\n=== Testing Description Generation ===\n";
$testCodes = ['RUT12345', 'RUT123456789', 'VERYLONGCODE123456789', 'RUT'];

foreach ($testCodes as $code) {
    $desc = generateDescription($code);
    echo "Code: {$code} -> Description: '{$desc}' (length: " . strlen($desc) . ")\n";
}

function generateDescription($withdrawCode) {
    $patterns = [
        "Rut {$withdrawCode}",
        "Rutien {$withdrawCode}",
        "TT {$withdrawCode}",
        "Chi {$withdrawCode}",
        substr($withdrawCode, -15),
    ];
    
    foreach ($patterns as $pattern) {
        if (strlen($pattern) <= 25) {
            return $pattern;
        }
    }
    
    return substr($withdrawCode, 0, 25);
}

// Test with different data variations
echo "\n=== Testing Different Data Variations ===\n";

$testCases = [
    'Simple data' => [
        'amount' => 100000,
        'referenceId' => 'TEST001'
    ],
    'With null value' => [
        'amount' => 50000,
        'referenceId' => 'TEST002',
        'description' => null
    ],
    'Complex category' => [
        'amount' => 75000,
        'referenceId' => 'TEST003',
        'category' => ['salary', 'bonus'] // Array of strings
    ]
];

foreach ($testCases as $caseName => $testCase) {
    echo "\n--- $caseName ---\n";
    $sig = generatePayOSSignatureManual($testCase, $payosConfig['payout_checksum_key']);
    echo "Signature: $sig\n";
}
?>
