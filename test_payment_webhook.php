<?php
/**
 * Test file để kiểm tra luồng thanh toán và webhook
 * 
 * Chạy file này để kiểm tra:
 * 1. Webhook endpoint có hoạt động không
 * 2. Order ID có được trích xuất đúng từ reference code không
 * 3. Payment status có được cập nhật không
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/models/OrdersModel.php';
require_once __DIR__ . '/app/models/SepayWebhookLogModel.php';

// Database connection
$dbConfig = $config['database'];
$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
try {
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

echo "=================================================================\n";
echo "       KIỂM TRA HỆ THỐNG THANH TOÁN SEPAY WEBHOOK\n";
echo "=================================================================\n\n";

// ============================================================================
// 1. KIỂM TRA WEBHOOK LOGS GẦN ĐÂY
// ============================================================================
echo "[1] KIỂM TRA WEBHOOK LOGS GẦN ĐÂY:\n";
echo "-----------------------------------------------------------------\n";

try {
    $stmt = $pdo->query("SELECT * FROM sepay_webhooks_log ORDER BY received_at DESC LIMIT 5");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($logs)) {
        echo "⚠️  Không có webhook log nào trong database!\n";
        echo "    → Có thể SePay chưa gửi webhook hoặc webhook bị lỗi\n\n";
    } else {
        foreach ($logs as $log) {
            echo "✓ Webhook ID: {$log['id']}\n";
            echo "  - Type: {$log['webhook_type']}\n";
            echo "  - Reference: {$log['reference_code']}\n";
            echo "  - Amount: {$log['amount']}\n";
            echo "  - Status: {$log['status']}\n";
            echo "  - Processed: " . ($log['processed'] ? 'Yes' : 'No') . "\n";
            echo "  - Received: {$log['received_at']}\n";
            if ($log['processing_error']) {
                echo "  - ❌ Error: {$log['processing_error']}\n";
            }
            echo "\n";
        }
    }
} catch (PDOException $e) {
    echo "❌ Lỗi truy vấn: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// 2. KIỂM TRA ĐƠN HÀNG GẦN ĐÂY
// ============================================================================
echo "\n[2] KIỂM TRA ĐƠN HÀNG GẦN ĐÂY (5 đơn mới nhất):\n";
echo "-----------------------------------------------------------------\n";

try {
    $stmt = $pdo->query("SELECT id, order_number, total, payment_status, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orders)) {
        echo "⚠️  Không có đơn hàng nào trong database!\n\n";
    } else {
        foreach ($orders as $order) {
            echo "✓ Order ID: {$order['id']} | Number: {$order['order_number']}\n";
            echo "  - Total: {$order['total']} | Payment: {$order['payment_status']} | Status: {$order['status']}\n";
            echo "  - Created: {$order['created_at']}\n\n";
        }
    }
} catch (PDOException $e) {
    echo "❌ Lỗi truy vấn: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// 3. KIỂM TRA FORMAT REFERENCE CODE
// ============================================================================
echo "\n[3] KIỂM TRA FORMAT REFERENCE CODE:\n";
echo "-----------------------------------------------------------------\n";

// Các format content từ SePay có thể có
$testContents = [
    '118650641631-DH1-CHUYEN TIEN-OQCH0007OziG-MOMO118650641631MOMO',  // Format chuẩn
    'DH123',  // Format đơn giản
    'THANHTOAN ORD_abc123',  // Format từ payment.php hiện tại
    '118650641631-THANHTOAN ORD_abc123-xxx',  // Format có thể có
    'RUT1234567890',  // Format rút tiền
];

foreach ($testContents as $content) {
    echo "Test content: '{$content}'\n";
    
    // Test regex giống trong WebhookController
    if (preg_match('/(DH\d+|RUT\d{10})/', $content, $matches)) {
        $refCode = $matches[1];
        echo "  ✓ Extracted: {$refCode}\n";
        
        if (preg_match('/DH(\d+)/', $refCode, $idMatches)) {
            $orderId = (int)$idMatches[1];
            echo "  ✓ Order ID: {$orderId}\n";
            
            // Kiểm tra order có tồn tại không
            $checkStmt = $pdo->prepare("SELECT id, order_number FROM orders WHERE id = ?");
            $checkStmt->execute([$orderId]);
            $foundOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($foundOrder) {
                echo "  ✓ Found order in DB: #{$foundOrder['order_number']}\n";
            } else {
                echo "  ❌ Order ID {$orderId} NOT FOUND in database!\n";
            }
        }
    } else {
        echo "  ❌ Cannot extract reference code (no DH\d+ or RUT\d{10} pattern found)\n";
    }
    echo "\n";
}

// ============================================================================
// 4. KIỂM TRA CẤU HÌNH SEPAY
// ============================================================================
echo "\n[4] KIỂM TRA CẤU HÌNH SEPAY:\n";
echo "-----------------------------------------------------------------\n";

echo "Order Prefix: " . ($config['sepay']['order_prefix'] ?? 'NOT SET') . "\n";
echo "Account Number: " . ($config['sepay']['account_number'] ?? 'NOT SET') . "\n";
echo "Bank Code: " . ($config['sepay']['bank_code'] ?? 'NOT SET') . "\n";
echo "API Key: " . (empty($config['sepay']['api_key']) ? 'NOT SET' : substr($config['sepay']['api_key'], 0, 10) . '...') . "\n";
echo "Webhook Secret: " . (empty($config['sepay']['webhook_secret']) ? 'NOT SET' : 'SET (' . strlen($config['sepay']['webhook_secret']) . ' chars)') . "\n\n";

// ============================================================================
// 5. KIỂM TRA WEBHOOK ENDPOINT
// ============================================================================
echo "\n[5] KIỂM TRA WEBHOOK ENDPOINT:\n";
echo "-----------------------------------------------------------------\n";

$baseUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$webhookUrl = $baseUrl . '/api.php?action=webhook&provider=sepay';

echo "Webhook URL: {$webhookUrl}\n\n";

// Test GET request (test endpoint)
$ch = curl_init($webhookUrl . '&test=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Test endpoint (GET): HTTP {$httpCode}\n";
if ($response) {
    $data = json_decode($response, true);
    if ($data) {
        echo "Response: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "Raw response: {$response}\n";
    }
} else {
    echo "❌ No response from server\n";
}

// ============================================================================
// 6. KIỂM TRA LOG FILE
// ============================================================================
echo "\n\n[6] KIỂM TRA LOG FILES:\n";
echo "-----------------------------------------------------------------\n";

$logFiles = [
    'logs/webhook_debug.log',
    'logs/webhook_error.log', 
    'logs/payment.log',
    'logs/webhook.log'
];

foreach ($logFiles as $logFile) {
    $path = __DIR__ . '/' . $logFile;
    if (file_exists($path)) {
        $size = filesize($path);
        $modified = date('Y-m-d H:i:s', filemtime($path));
        echo "✓ {$logFile}: {$size} bytes (modified: {$modified})\n";
        
        // Hiển thị 10 dòng cuối
        $lines = file($path);
        $lastLines = array_slice($lines, -10);
        echo "  Last 10 lines:\n";
        foreach ($lastLines as $line) {
            echo "    " . trim($line) . "\n";
        }
    } else {
        echo "⚠️  {$logFile}: NOT FOUND\n";
    }
    echo "\n";
}

// ============================================================================
// 7. KIỂM TRA VẤN ĐỀ CHÍNH
// ============================================================================
echo "\n[7] PHÂN TÍCH VẤN ĐỀ CHÍNH:\n";
echo "-----------------------------------------------------------------\n";

echo "Vấn đề có thể gặp phải:\n\n";

echo "A. FORMAT MÃ ĐƠN HÀNG KHÔNG KHỚP:\n";
echo "   - Trong payment.php: order_number = 'ORD_' + random (VD: ORD_abc123)\n";
echo "   - Trong WebhookController: Tìm pattern DH\\d+ (VD: DH1, DH123)\n";
echo "   - QR content: 'THANHTOAN ORD_abc123'\n";
echo "   - Webhook không thể trích xuất DH\\d+ từ 'THANHTOAN ORD_abc123'\n\n";

echo "B. CÁCH KIỂM TRA:\n";
echo "   1. Tạo đơn hàng mới và ghi lại order_number\n";
echo "   2. Quét QR và thanh toán\n";
echo "   3. Kiểm tra logs/webhook_debug.log xem có nhận webhook không\n";
echo "   4. Nếu có webhook nhưng không cập nhật status → xem processing_error\n\n";

echo "C. GIẢI PHÁP GỢI Ý:\n";
echo "   1. Sửa payment.php: Dùng order ID thay vì order_number trong QR content\n";
echo "   2. Format: DH{order_id} thay vì 'THANHTOAN ORD_xxx'\n";
echo "   3. Hoặc sửa WebhookController để hỗ trợ cả 2 format\n\n";

// ============================================================================
// 8. TEST WEBHOOK VỚI ĐƠN HÀNG THỰC
// ============================================================================
echo "\n[8] TEST WEBHOOK VỚI ĐƠN HÀNG THỰC:\n";
echo "-----------------------------------------------------------------\n";

try {
    // Lấy đơn hàng pending gần nhất
    $stmt = $pdo->query("SELECT * FROM orders WHERE payment_status = 'pending' ORDER BY created_at DESC LIMIT 1");
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo "Found pending order:\n";
        echo "  - ID: {$order['id']}\n";
        echo "  - Number: {$order['order_number']}\n";
        echo "  - Total: {$order['total']}\n\n";
        
        // Tạo webhook data với đúng format
        $webhookData = [
            'id' => 'TXN' . time(),
            'gateway' => 'MB',
            'transactionDate' => date('Y-m-d H:i:s'),
            'accountNumber' => $config['sepay']['account_number'] ?? '0914960029666',
            'transferType' => 'in',
            'transferAmount' => (int)$order['total'],
            'content' => "DH{$order['id']}-THANH TOAN DON HANG",
            'code' => 'TEST' . time()
        ];
        
        echo "Sending test webhook with DH format...\n";
        echo "Content: {$webhookData['content']}\n\n";
        
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
        curl_close($ch);
        
        echo "HTTP Code: {$httpCode}\n";
        echo "Response: {$response}\n\n";
        
        // Kiểm tra lại đơn hàng
        sleep(1);
        $checkStmt = $pdo->prepare("SELECT payment_status FROM orders WHERE id = ?");
        $checkStmt->execute([$order['id']]);
        $updatedOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Payment status after webhook: {$updatedOrder['payment_status']}\n";
        if ($updatedOrder['payment_status'] === 'paid') {
            echo "✅ SUCCESS! Payment status updated correctly.\n";
        } else {
            echo "❌ FAILED! Payment status not updated.\n";
            echo "   Check logs/webhook_debug.log for details.\n";
        }
    } else {
        echo "⚠️  Không có đơn hàng pending nào. Hãy tạo đơn hàng mới trước.\n";
    }
} catch (PDOException $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

echo "\n=================================================================\n";
echo "                     KẾT THÚC KIỂM TRA\n";
echo "=================================================================\n";
