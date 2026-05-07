<?php
/**
 * Compare PayOSService vs Direct Signature
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/services/PayOSService.php';

$config = require __DIR__ . '/config.php';
$payosConfig = $config['payos'];

echo "<h1>Signature Comparison</h1>";

// Test data (same as PayOSService)
$testData = [
    'referenceId' => 'RUT123456789',
    'amount' => 50000,
    'description' => 'Test payout debug',
    'toBin' => '970422',
    'toAccountNumber' => '0914960029666',
    'toAccountName' => 'LE VU BAO NHAT',
    'category' => (object)['0' => 'salary']
];

echo "<h2>Test Data:</h2>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

// Method 1: Direct (from test_payos_simple.php)
echo "<h2>Method 1: Direct (test file)</h2>";
$signatureData1 = [];
foreach ($testData as $key => $value) {
    if ($value === null) continue;
    
    if (is_array($value) || is_object($value)) {
        if ($key === 'category') {
            $signatureData1[$key] = json_encode($value);
        } else {
            continue;
        }
    } else {
        $signatureData1[$key] = (string)$value;
    }
}
ksort($signatureData1);

$pairs1 = [];
foreach ($signatureData1 as $key => $value) {
    $pairs1[] = $key . '=' . $value;
}
$dataString1 = implode('&', $pairs1);
$signature1 = hash_hmac('sha256', $dataString1, $payosConfig['checksum_key']);

echo "<pre>Category JSON: " . $signatureData1['category'] . "</pre>";
echo "<pre>Data String: $dataString1</pre>";
echo "<pre>Signature: $signature1</pre>";

// Method 2: PayOSService logic (copy exact from service)
echo "<h2>Method 2: PayOSService Logic</h2>";
$signatureData2 = [];
foreach ($testData as $key => $value) {
    if ($value === null) {
        continue;
    }
    
    if (is_array($value) || is_object($value)) {
        if ($key === 'category') {
            // Exact logic from PayOSService
            if (is_array($value) && isset($value['0'])) {
                $signatureData2[$key] = json_encode(['0' => $value['0']]);
            } elseif (is_object($value) && isset($value->{'0'})) {
                $signatureData2[$key] = json_encode(['0' => $value->{'0'}]);
            } else {
                $signatureData2[$key] = json_encode($value);
            }
        } else {
            continue;
        }
    } else {
        $signatureData2[$key] = (string)$value;
    }
}
ksort($signatureData2);

$pairs2 = [];
foreach ($signatureData2 as $key => $value) {
    $pairs2[] = $key . '=' . $value;
}
$dataString2 = implode('&', $pairs2);
$signature2 = hash_hmac('sha256', $dataString2, $payosConfig['checksum_key']);

echo "<pre>Category JSON: " . $signatureData2['category'] . "</pre>";
echo "<pre>Data String: $dataString2</pre>";
echo "<pre>Signature: $signature2</pre>";

// Compare
echo "<h2>Comparison:</h2>";
echo "<pre>Category Match: " . ($signatureData1['category'] === $signatureData2['category'] ? 'YES' : 'NO') . "</pre>";
echo "<pre>Data String Match: " . ($dataString1 === $dataString2 ? 'YES' : 'NO') . "</pre>";
echo "<pre>Signature Match: " . ($signature1 === $signature2 ? 'YES' : 'NO') . "</pre>";

if ($dataString1 !== $dataString2) {
    echo "<h3>Data String Differences:</h3>";
    echo "<pre>Method 1 length: " . strlen($dataString1) . "</pre>";
    echo "<pre>Method 2 length: " . strlen($dataString2) . "</pre>";
    
    // Find first difference
    $minLen = min(strlen($dataString1), strlen($dataString2));
    for ($i = 0; $i < $minLen; $i++) {
        if ($dataString1[$i] !== $dataString2[$i]) {
            echo "<pre>First difference at position $i:</pre>";
            echo "<pre>Method 1: ..." . substr($dataString1, max(0, $i-10), 20) . "...</pre>";
            echo "<pre>Method 2: ..." . substr($dataString2, max(0, $i-10), 20) . "...</pre>";
            break;
        }
    }
}

?>
