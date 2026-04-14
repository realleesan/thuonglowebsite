<?php
/**
 * Test để kiểm tra format mã đơn hàng và QR code
 * 
 * Chạy file này sau khi tạo đơn hàng để kiểm tra:
 * 1. Order ID trong database
 * 2. Format QR code content
 * 3. Sự khớp nhau giữa payment.php và WebhookController
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

echo "=================================================================\n";
echo "       KIỂM TRA FORMAT MÃ ĐƠN HÀNG VÀ QR CODE\n";
echo "=================================================================\n\n";

// Kết nối database
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// ============================================================================
// LẤY ĐƠN HÀNG MỚI NHẤT
// ============================================================================
echo "[1] ĐƠN HÀNG MỚI NHẤT TRONG DATABASE:\n";
echo "-----------------------------------------------------------------\n";

try {
    $orders = $db->query("SELECT id, order_number, total, payment_status, status, created_at FROM orders ORDER BY created_at DESC LIMIT 3");
    
    foreach ($orders as $order) {
        echo "\n✓ Order ID: {$order['id']}\n";
        echo "  - Order Number: {$order['order_number']}\n";
        echo "  - Total: " . number_format($order['total']) . " VND\n";
        echo "  - Payment Status: {$order['payment_status']}\n";
        echo "  - Created: {$order['created_at']}\n\n";
        
        // ============================================================================
        // KIỂM TRA FORMAT QR CODE
        // ============================================================================
        echo "  [QR Code Analysis]:\n";
        
        // Format hiện tại trong payment.php
        $bankAcc = "0914960029666";
        $bankName = "MBBank";
        $currentContent = "THANHTOAN " . $order['order_number'];  // Format hiện tại
        $correctContent = "DH" . $order['id'];  // Format đúng cho webhook
        
        echo "    Format HIỆN TẠI: '{$currentContent}'\n";
        echo "    Format ĐÚNG:      '{$correctContent}'\n\n";
        
        // Kiểm tra xem có thể trích xuất DH\d+ không
        if (preg_match('/(DH\d+)/', $currentContent, $matches)) {
            echo "    ✓ Webhook CÓ THỂ trích xuất: {$matches[1]}\n";
        } else {
            echo "    ❌ Webhook KHÔNG THỂ trích xuất DH\\d+ từ format hiện tại!\n";
            echo "       → Webhook sẽ không tìm thấy order ID!\n";
        }
        
        // URL QR code
        $currentQrUrl = "https://qr.sepay.vn/img?bank={$bankName}&acc={$bankAcc}&template=compact&amount=" . (int)$order['total'] . "&des=" . urlencode($currentContent);
        $correctQrUrl = "https://qr.sepay.vn/img?bank={$bankName}&acc={$bankAcc}&template=compact&amount=" . (int)$order['total'] . "&des=" . urlencode($correctContent);
        
        echo "\n    QR URL (hiện tại): {$currentQrUrl}\n";
        echo "    QR URL (đúng):     {$correctQrUrl}\n";
        
        // ============================================================================
        // TEST WEBHOOK VỚI CẢ 2 FORMAT
        // ============================================================================
        echo "\n  [Test Webhook]:\n";
        
        // Tạo đường dẫn webhook
        $baseUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $webhookUrl = $baseUrl . '/api.php?action=webhook&provider=sepay';
        
        // Test với format HIỆN TẠI (sai)
        echo "\n    Test với format HIỆN TẠI:\n";
        $webhookDataWrong = [
            'id' => 'TXN' . time() . rand(100,999),
            'gateway' => 'MB',
            'transactionDate' => date('Y-m-d H:i:s'),
            'accountNumber' => $bankAcc,
            'transferType' => 'in',
            'transferAmount' => (int)$order['total'],
            'content' => $currentContent . "-CHUYEN TIEN-TEST" . time(),
            'code' => 'TEST' . time()
        ];
        
        echo "      Content: {$webhookDataWrong['content']}\n";
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookDataWrong));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: SePay-Webhook/1.0'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        echo "      HTTP: {$httpCode} | Success: " . ($responseData['success'] ? 'Yes' : 'No') . "\n";
        if (!$responseData['success']) {
            echo "      Error: " . ($responseData['error'] ?? 'Unknown') . "\n";
        }
        
        sleep(1); // Đợi 1 giây
        
        // Test với format ĐÚNG
        echo "\n    Test với format ĐÚNG (DH{$order['id']}):\n";
        $webhookDataCorrect = [
            'id' => 'TXN' . time() . rand(100,999),
            'gateway' => 'MB',
            'transactionDate' => date('Y-m-d H:i:s'),
            'accountNumber' => $bankAcc,
            'transferType' => 'in',
            'transferAmount' => (int)$order['total'],
            'content' => $correctContent . "-CHUYEN TIEN-TEST" . time(),
            'code' => 'TEST' . time()
        ];
        
        echo "      Content: {$webhookDataCorrect['content']}\n";
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookDataCorrect));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: SePay-Webhook/1.0'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        echo "      HTTP: {$httpCode} | Success: " . ($responseData['success'] ? 'Yes' : 'No') . "\n";
        if (!$responseData['success']) {
            echo "      Error: " . ($responseData['error'] ?? 'Unknown') . "\n";
        }
        
        // Kiểm tra lại đơn hàng sau khi test
        sleep(1);
        $checkOrder = $db->query("SELECT payment_status FROM orders WHERE id = ?", [$order['id']]);
        if ($checkOrder) {
            echo "\n    Payment status sau test: {$checkOrder[0]['payment_status']}\n";
        }
        
        echo "\n" . str_repeat("-", 65) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

// ============================================================================
// TỔNG KẾT
// ============================================================================
echo "\n=================================================================\n";
echo "                        TỔNG KẾT\n";
echo "=================================================================\n\n";

echo "VẤN ĐỀ CHÍNH:\n";
echo "-----------------------------------------------------------------\n";
echo "Trong file app/views/payment/payment.php (dòng 214):\n";
echo "  \$content = \"THANHTOAN \" . \$orderId;\n\n";
echo "→ Tạo content: 'THANHTOAN ORD_abc123'\n\n";

echo "Trong file app/controllers/WebhookController.php (dòng 303-311):\n";
echo "  preg_match('/(DH\\d+|RUT\\d{10})/', \$content, \$matches)\n\n";
echo "→ Chỉ nhận diện: 'DH1', 'DH123', 'RUT1234567890'\n\n";

echo "KẾT QUẢ:\n";
echo "-----------------------------------------------------------------\n";
echo "❌ Webhook KHÔNG THỂ trích xuất order ID từ 'THANHTOAN ORD_xxx'\n";
echo "❌ Webhook sẽ không tìm thấy đơn hàng để cập nhật\n";
echo "❌ Payment status sẽ luôn là 'pending'\n\n";

echo "GIẢI PHÁP:\n";
echo "-----------------------------------------------------------------\n";
echo "Sửa file payment.php, dòng 214:\n";
echo "  Từ: \$content = \"THANHTOAN \" . \$orderId;\n";
echo "  Thành: \$content = \"DH\" . \$order['id'];  // Sử dụng order ID\n\n";

echo "Sau đó QR code sẽ có content: 'DH123' thay vì 'THANHTOAN ORD_abc123'\n";
echo "Webhook sẽ nhận diện được DH123 → Order ID = 123 → Cập nhật thành công!\n\n";

echo "=================================================================\n";
