<?php
/**
 * Webhook Diagnostic Tool
 * Kiểm tra chi tiết webhook endpoint
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Webhook Diagnostic Tool</h1>";

// 1. Test direct POST to webhook endpoint
echo "<h2>1. Test Webhook Endpoint</h2>";

$testData = [
    'id' => 'TXN' . time(),
    'gateway' => 'MB',
    'transactionDate' => date('Y-m-d H:i:s'),
    'accountNumber' => '0914960029666',
    'transferType' => 'in',
    'transferAmount' => 5000,
    'content' => 'THANHTOAN ORD_02d358a8-CHUYEN TIEN-TEST',
    'code' => 'TEST' . time()
];

$jsonData = json_encode($testData);

// Test with https
$httpsUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'test1.web3b.com') . '/api.php?action=webhook&provider=sepay';
$httpUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'test1.web3b.com') . '/api.php?action=webhook&provider=sepay';

echo "<h3>Test HTTPS URL:</h3>";
echo "<code>{$httpsUrl}</code><br><br>";

$ch = curl_init($httpsUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: SePay-Webhook/1.0'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> {$httpCode}</p>";
if ($error) {
    echo "<p style='color:red'><strong>cURL Error:</strong> {$error}</p>";
}
echo "<p><strong>Response:</strong></p>";
echo "<pre style='background:#f5f5f5;padding:10px;'>" . htmlspecialchars($response) . "</pre>";

// 2. Check if webhook_debug.log exists and show recent entries
echo "<h2>2. Webhook Debug Log</h2>";
$logFile = __DIR__ . '/logs/webhook_debug.log';
if (file_exists($logFile)) {
    echo "<p>Log file found: {$logFile}</p>";
    echo "<p>Size: " . filesize($logFile) . " bytes</p>";
    
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    echo "<p><strong>Last 50 lines:</strong></p>";
    echo "<pre style='background:#333;color:#0f0;padding:10px;overflow:auto;max-height:400px;'>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "<p style='color:orange'>Log file not found: {$logFile}</p>";
}

// 3. Check webhook_logs table
echo "<h2>3. Webhook Logs Database</h2>";
try {
    $db = Database::getInstance();
    $logs = $db->query("SELECT * FROM sepay_webhooks_log ORDER BY received_at DESC LIMIT 5");
    
    if ($logs) {
        echo "<table border='1' cellpadding='8' style='border-collapse:collapse;width:100%'>";
        echo "<tr style='background:#007bff;color:white'><th>ID</th><th>Type</th><th>Reference</th><th>Amount</th><th>Processed</th><th>Error</th><th>Received</th></tr>";
        foreach ($logs as $log) {
            $error = $log['processing_error'] ? substr($log['processing_error'], 0, 50) : '-';
            echo "<tr>";
            echo "<td>{$log['id']}</td>";
            echo "<td>{$log['webhook_type']}</td>";
            echo "<td>{$log['reference_code']}</td>";
            echo "<td>{$log['amount']}</td>";
            echo "<td>" . ($log['processed'] ? 'Yes' : 'No') . "</td>";
            echo "<td style='color:red'>{$error}</td>";
            echo "<td>{$log['received_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No webhook logs found in database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
}

// 4. Check php://input availability
echo "<h2>4. Server Configuration</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>allow_url_fopen:</strong> " . (ini_get('allow_url_fopen') ? 'Yes' : 'No') . "</p>";
echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "</p>";
echo "<p><strong>HTTPS:</strong> " . (isset($_SERVER['HTTPS']) ? 'Yes' : 'No') . "</p>";

echo "<hr><p><a href='test_webhook_url.php'>Back to Test Page</a></p>";
