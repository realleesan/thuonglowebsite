<?php
/**
 * Simulate Sepay Webhook
 * Gửi webhook giả lập để test hệ thống
 */

// URL webhook của bạn
$webhookUrl = 'http://localhost/api.php?action=webhook&provider=sepay';

// Dữ liệu webhook mẫu từ Sepay (payment IN - khách hàng chuyển tiền)
$webhookData = [
    'id' => 'TXN' . time(),
    'gateway' => 'MB',
    'transactionDate' => date('Y-m-d H:i:s'),
    'accountNumber' => '0389654785',
    'subAccount' => null,
    'transferType' => 'in',
    'transferAmount' => 10000,
    'accumulated' => 100000,
    'code' => 'OQCH0007OziG',
    'content' => '118650641631-DH1-CHUYEN TIEN-OQCH0007OziG-MOMO118650641631MOMO',
    'description' => 'Thanh toan don hang DH1',
    'referenceCode' => 'DH1',
    'body' => 'DH1'
];

echo "Đang gửi webhook đến: {$webhookUrl}\n";
echo "Dữ liệu gửi:\n";
echo json_encode($webhookData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Gửi request
$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: SePay-Webhook/1.0',
    'X-Sepay-Signature: test_signature_' . md5(json_encode($webhookData))
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Kết quả:\n";
echo "HTTP Code: {$httpCode}\n";

if ($error) {
    echo "Lỗi: {$error}\n";
} else {
    echo "Response:\n";
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo $response . "\n";
    }
}

echo "\n";
echo "Kiểm tra log trong database (bảng sepay_webhooks_log)\n";
echo "Hoặc xem file logs/webhook_debug.log\n";
