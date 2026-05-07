<?php
/**
 * Debug PayOS Service
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/services/PayOSService.php';

// Create PayOSService instance
$payosService = new PayOSService();

// Test bank info
$bankInfo = [
    'bank_code' => '970422',
    'account_number' => '0914960029666',
    'account_holder' => 'LE VU BAO NHAT'
];

// Test payout
$result = $payosService->createPayout(
    'RUT123456789',
    50000,
    $bankInfo,
    'Test payout debug'
);

echo "<h1>PayOS Service Debug</h1>";
echo "<h2>Result:</h2>";
echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

// Debug signature generation
echo "<h2>Signature Debug:</h2>";
$requestData = [
    'referenceId' => 'RUT123456789',
    'amount' => 50000,
    'description' => 'Test payout debug',
    'toBin' => '970422',
    'toAccountNumber' => '0914960029666',
    'toAccountName' => 'LE VU BAO NHAT',
    'category' => (object)['0' => 'salary']
];

$signatureData = [];
foreach ($requestData as $key => $value) {
    if ($value === null) {
        continue;
    }
    
    if (is_array($value) || is_object($value)) {
        if ($key === 'category') {
            $signatureData[$key] = json_encode($value);
            echo "Category JSON: " . json_encode($value) . "\n";
        } else {
            continue;
        }
    } else {
        $signatureData[$key] = (string)$value;
    }
}

ksort($signatureData);
echo "<pre>Signature Data: " . json_encode($signatureData, JSON_UNESCAPED_UNICODE) . "</pre>";

$pairs = [];
foreach ($signatureData as $key => $value) {
    $pairs[] = $key . '=' . $value;
}

$dataString = implode('&', $pairs);
echo "<pre>Data String: $dataString</pre>";

$config = require __DIR__ . '/config.php';
$signature = hash_hmac('sha256', $dataString, $config['payos']['checksum_key']);
echo "<pre>Generated Signature: $signature</pre>";

// Check test mode
echo "<h2>Service Status:</h2>";
$reflection = new ReflectionClass($payosService);
$testModeProp = $reflection->getProperty('testMode');
$testModeProp->setAccessible(true);
$testModeValue = $testModeProp->getValue($payosService);

$apiUrlProp = $reflection->getProperty('apiUrl');
$apiUrlProp->setAccessible(true);
$apiUrlValue = $apiUrlProp->getValue($payosService);

echo "<pre>Test Mode: " . ($testModeValue ? 'YES' : 'NO') . "</pre>";
echo "<pre>API URL: $apiUrlValue</pre>";

// Check config
$config = require __DIR__ . '/config.php';
echo "<h2>Config:</h2>";
echo "<pre>test_mode: " . ($config['payos']['test_mode'] ? 'YES' : 'NO') . "</pre>";
echo "<pre>api_url: " . $config['payos']['api_url'] . "</pre>";

?>
