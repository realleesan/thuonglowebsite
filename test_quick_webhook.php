<?php
/**
 * Test webhook nhanh - gửi webhook giả lập để test
 * 
 * Cách dùng:
 * 1. Tạo đơn hàng test trên website
 * 2. Copy Order ID (số) từ admin panel hoặc database
 * 3. Chạy: php test_quick_webhook.php [ORDER_ID]
 * 
 * Ví dụ: php test_quick_webhook.php 123
 */

// Lấy order ID từ command line hoặc tự động tìm
$orderId = $argv[1] ?? null;

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

echo "=================================================================\n";
echo "              QUICK WEBHOOK TEST TOOL\n";
echo "=================================================================\n\n";

// Kết nối database
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage() . "\n");
}

// Nếu không có order ID, tìm đơn pending gần nhất
if (!$orderId) {
    echo "Không có Order ID. Đang tìm đơn hàng pending gần nhất...\n";
    $order = $db->query("SELECT id, order_number, total, payment_status FROM orders WHERE payment_status = 'pending' ORDER BY created_at DESC LIMIT 1");
    
    if (empty($order)) {
        echo "❌ Không tìm thấy đơn hàng pending nào!\n";
        echo "Hãy tạo đơn hàng mới hoặc chạy: php test_quick_webhook.php [ORDER_ID]\n";
        exit(1);
    }
    
    $orderId = $order[0]['id'];
    echo "✓ Tìm thấy đơn hàng:\n";
    echo "  Order ID: {$orderId}\n";
    echo "  Order Number: {$order[0]['order_number']}\n";
    echo "  Total: " . number_format($order[0]['total']) . " VND\n\n";
} else {
    // Kiểm tra order ID có tồn tại không
    $order = $db->query("SELECT id, order_number, total, payment_status FROM orders WHERE id = ?", [$orderId]);
    
    if (empty($order)) {
        echo "❌ Không tìm thấy đơn hàng với ID: {$orderId}\n";
        exit(1);
    }
    
    echo "✓ Đơn hàng:\n";
    echo "  Order ID: {$orderId}\n";
    echo "  Order Number: {$order[0]['order_number']}\n";
    echo "  Total: " . number_format($order[0]['total']) . " VND\n";
    echo "  Current Payment Status: {$order[0]['payment_status']}\n\n";
}

// Cấu hình webhook
$bankAcc = $config['sepay']['account_number'] ?? '0914960029666';
$amount = (int)$order[0]['total'];
$content = "DH{$orderId}-THANH TOAN DON HANG-" . time();

// Tạo webhook data
$webhookData = [
    'id' => 'TXN' . time() . rand(100, 999),
    'gateway' => 'MB',
    'transactionDate' => date('Y-m-d H:i:s'),
    'accountNumber' => $bankAcc,
    'subAccount' => null,
    'transferType' => 'in',
    'transferAmount' => $amount,
    'accumulated' => 1000000,
    'code' => 'TEST' . time(),
    'content' => $content,
    'description' => "Thanh toan don hang DH{$orderId}",
    'referenceCode' => "DH{$orderId}",
    'body' => "DH{$orderId}"
];

echo "Sending webhook...\n";
echo "  URL: http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/api.php?action=webhook&provider=sepay\n";
echo "  Content: {$content}\n";
echo "  Amount: " . number_format($amount) . " VND\n\n";

// Gửi webhook
$baseUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$webhookUrl = $baseUrl . '/api.php?action=webhook&provider=sepay';

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: SePay-Webhook/1.0',
    'X-Sepay-Signature: test_signature_' . md5(json_encode($webhookData))
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Result:\n";
echo "  HTTP Code: {$httpCode}\n";

if ($error) {
    echo "  ❌ cURL Error: {$error}\n";
} else {
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo "  Response: " . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if ($responseData['success']) {
            echo "\n  ✅ Webhook processed successfully!\n";
        } else {
            echo "\n  ❌ Webhook processing failed!\n";
        }
    } else {
        echo "  Raw Response: {$response}\n";
    }
}

// Kiểm tra lại đơn hàng sau 2 giây
echo "\nWaiting 2 seconds to verify...\n";
sleep(2);

$updatedOrder = $db->query("SELECT payment_status, status, payment_reference FROM orders WHERE id = ?", [$orderId]);
if ($updatedOrder) {
    echo "\nUpdated Order Status:\n";
    echo "  Payment Status: {$updatedOrder[0]['payment_status']}\n";
    echo "  Order Status: {$updatedOrder[0]['status']}\n";
    echo "  Payment Reference: " . ($updatedOrder[0]['payment_reference'] ?: 'None') . "\n\n";
    
    if ($updatedOrder[0]['payment_status'] === 'paid') {
        echo "✅ SUCCESS! Order has been marked as PAID.\n";
    } else {
        echo "❌ Payment status not updated. Check logs/webhook_debug.log for details.\n";
    }
}

echo "\n=================================================================\n";
