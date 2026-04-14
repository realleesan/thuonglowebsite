<?php
/**
 * Test webhook với format ORD_xxx mới
 * 
 * Chạy: php test_webhook_ord_format.php [ORDER_ID]
 */

$orderId = $argv[1] ?? null;

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

echo "=================================================================\n";
echo "       TEST WEBHOOK VỚI FORMAT ORD_xxx\n";
echo "=================================================================\n\n";

// Kết nối database
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage() . "\n");
}

// Tìm đơn hàng
if (!$orderId) {
    $order = $db->query("SELECT * FROM orders WHERE payment_status = 'pending' ORDER BY created_at DESC LIMIT 1");
    if (empty($order)) {
        echo "Không có đơn hàng pending. Tạo đơn test...\n";
        exit(1);
    }
    $order = $order[0];
} else {
    $order = $db->query("SELECT * FROM orders WHERE id = ?", [$orderId]);
    if (empty($order)) {
        echo "Không tìm thấy đơn hàng ID: {$orderId}\n";
        exit(1);
    }
    $order = $order[0];
}

echo "Đơn hàng test:\n";
echo "  - ID: {$order['id']}\n";
echo "  - Order Number: {$order['order_number']}\n";
echo "  - Total: " . number_format($order['total']) . " VND\n";
echo "  - Current Payment Status: {$order['payment_status']}\n\n";

// Test extractReferenceFromContent
echo "[TEST] Extract Reference Code:\n";
$testContents = [
    "THANHTOAN {$order['order_number']}-CHUYEN TIEN-TEST",
    "{$order['order_number']}-THANH TOAN",
    "DH{$order['id']}-CHUYEN TIEN",
    "RUT1234567890-THANH TOAN",
];

foreach ($testContents as $content) {
    echo "  Content: '{$content}'\n";
    
    // Test regex giống trong WebhookController mới
    if (preg_match('/(ORD_[a-zA-Z0-9_]+)/', $content, $matches)) {
        echo "    ✓ Extracted ORD: {$matches[1]}\n";
    } elseif (preg_match('/(DH\d+|RUT\d{10})/', $content, $matches)) {
        echo "    ✓ Extracted DH/RUT: {$matches[1]}\n";
    } else {
        echo "    ❌ No match\n";
    }
}

// Test findByOrderNumber
echo "\n[TEST] Find Order by Order Number:\n";
$orderNumber = $order['order_number'];
echo "  Looking for: {$orderNumber}\n";

$found = $db->query("SELECT id, order_number FROM orders WHERE order_number = ? LIMIT 1", [$orderNumber]);
if ($found) {
    echo "    ✓ Found: ID={$found[0]['id']}, Number={$found[0]['order_number']}\n";
} else {
    echo "    ❌ Not found\n";
}

// Test gửi webhook
echo "\n[TEST] Send Webhook:\n";

$bankAcc = $config['sepay']['account_number'] ?? '0914960029666';
$content = "THANHTOAN {$order['order_number']}-CHUYEN TIEN-" . time();

$webhookData = [
    'id' => 'TXN' . time() . rand(100, 999),
    'gateway' => 'MB',
    'transactionDate' => date('Y-m-d H:i:s'),
    'accountNumber' => $bankAcc,
    'transferType' => 'in',
    'transferAmount' => (int)$order['total'],
    'content' => $content,
    'code' => 'TEST' . time()
];

echo "  Content: {$content}\n";
echo "  Amount: " . number_format($order['total']) . " VND\n";

// Gửi webhook
$baseUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$webhookUrl = $baseUrl . '/api.php?action=webhook&provider=sepay';

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: SePay-Webhook/1.0'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\n  Result:\n";
echo "    HTTP Code: {$httpCode}\n";

$responseData = json_decode($response, true);
if ($responseData) {
    echo "    Success: " . ($responseData['success'] ? 'Yes' : 'No') . "\n";
    if (!$responseData['success'] && isset($responseData['error'])) {
        echo "    Error: {$responseData['error']}\n";
    }
}

// Kiểm tra lại sau 2 giây
echo "\n  Waiting 2 seconds...\n";
sleep(2);

$updated = $db->query("SELECT payment_status, status FROM orders WHERE id = ?", [$order['id']]);
echo "  Updated Status:\n";
echo "    Payment Status: {$updated[0]['payment_status']}\n";
echo "    Order Status: {$updated[0]['status']}\n";

if ($updated[0]['payment_status'] === 'paid') {
    echo "\n✅ SUCCESS! Webhook đã cập nhật thanh toán thành công!\n";
} else {
    echo "\n❌ FAILED! Payment status không được cập nhật.\n";
    echo "   Kiểm tra logs/webhook_debug.log để xem chi tiết.\n";
}

echo "\n=================================================================\n";
