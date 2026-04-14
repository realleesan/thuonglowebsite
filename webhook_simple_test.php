<?php
/**
 * Simple Webhook Test - Post directly to webhook
 */

require_once __DIR__ . '/config.php';

// Simulate SePay webhook data
$data = [
    'id' => 'TXN' . time() . rand(100, 999),
    'gateway' => 'MB',
    'transactionDate' => date('Y-m-d H:i:s'),
    'accountNumber' => '0914960029666',
    'transferType' => 'in',
    'transferAmount' => 5000,
    'content' => 'THANHTOAN ORD_02d358a8-CHUYEN TIEN-TEST',
    'code' => 'TEST' . time()
];

$json = json_encode($data);

// Test direct POST
$url = 'https://test1.web3b.com/api.php?action=webhook&provider=sepay';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
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

echo "HTTP Code: {$httpCode}\n";
if ($error) echo "Error: {$error}\n";
echo "Response: {$response}\n";

// Also test with php://input simulation
echo "\n--- Testing with direct input ---\n";
$testInput = file_get_contents('php://input');
echo "Received input: {$testInput}\n";
