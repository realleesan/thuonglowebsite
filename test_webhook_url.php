<?php
/**
 * Test Webhook via URL
 * Test webhook với format ORD_xxx
 * 
 * URL: http://test1.web3b.com/test_webhook_url.php
 * 
 * Parameters:
 *   - order_id: ID đơn hàng (optional, mặc định tìm đơn pending mới nhất)
 *   - action: 'test' để gửi webhook test
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

header('Content-Type: text/html; charset=utf-8');

// Kết nối database
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("<h2 style='color:red'>Database Error: " . htmlspecialchars($e->getMessage()) . "</h2>");
}

$action = $_GET['action'] ?? '';
$orderId = $_GET['order_id'] ?? null;
$result = '';

// Xử lý test webhook
if ($action === 'test' && $orderId) {
    $order = $db->query("SELECT * FROM orders WHERE id = ?", [$orderId]);
    if ($order) {
        $order = $order[0];
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
        
        // Use HTTPS to avoid redirect that may lose POST data
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $baseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $webhookUrl = $baseUrl . '/api.php?action=webhook&provider=sepay';
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: SePay-Webhook/1.0'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        sleep(2);
        $updated = $db->query("SELECT payment_status, status FROM orders WHERE id = ?", [$orderId]);
        
        $result = "<div style='background:#d4edda;padding:15px;margin:20px 0;border-radius:5px;'>";
        $result .= "<h3>Kết quả Test Webhook</h3>";
        $result .= "<p><strong>Data Sent:</strong> <pre style='background:#333;color:#fff;padding:10px;overflow:auto;'>" . htmlspecialchars(json_encode($webhookData, JSON_PRETTY_PRINT)) . "</pre></p>";
        $result .= "<p><strong>Webhook URL:</strong> {$webhookUrl}</p>";
        if ($curlError) {
            $result .= "<p style='color:red'><strong>cURL Error:</strong> {$curlError}</p>";
        }
        $result .= "<p><strong>HTTP Code:</strong> {$httpCode}</p>";
        $result .= "<p><strong>Raw Response:</strong> " . htmlspecialchars($response) . "</p>";
        $result .= "<p><strong>Payment Status sau test:</strong> <span style='color:" . ($updated[0]['payment_status'] === 'paid' ? 'green' : 'red') . ";font-weight:bold'>{$updated[0]['payment_status']}</span></p>";
        if ($updated[0]['payment_status'] === 'paid') {
            $result .= "<p style='color:green;font-size:18px'>✅ SUCCESS! Webhook hoạt động chính xác!</p>";
        } else {
            $result .= "<p style='color:red'>❌ FAILED! Kiểm tra logs/webhook_debug.log</p>";
        }
        $result .= "</div>";
    }
}

// Lấy danh sách đơn hàng gần đây
$recentOrders = $db->query("SELECT id, order_number, total, payment_status, status, created_at FROM orders ORDER BY created_at DESC LIMIT 10");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Webhook - ORD Format</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f5f5f5; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-paid { color: #28a745; font-weight: bold; }
        .btn-test { 
            background: #28a745; color: white; padding: 8px 16px; 
            text-decoration: none; border-radius: 4px; display: inline-block;
        }
        .btn-test:hover { background: #218838; }
        .format-box { 
            background: #f8f9fa; border: 2px solid #007bff; 
            padding: 15px; margin: 20px 0; border-radius: 5px;
        }
        .code { 
            background: #333; color: #fff; padding: 15px; 
            font-family: monospace; border-radius: 5px; overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🔧 Test Webhook - Format ORD_xxx</h1>
    
    <?php echo $result; ?>
    
    <div class="format-box">
        <h3>📋 Thông tin Format</h3>
        <p><strong>Format QR Code hiện tại:</strong> <code>THANHTOAN ORD_xxx</code></p>
        <p><strong>Webhook nhận diện:</strong> Pattern <code>ORD_[a-zA-Z0-9_]+</code></p>
        <p><strong>Flow:</strong> Webhook extract ORD_xxx → Tìm order theo order_number → Cập nhật payment_status = 'paid'</p>
    </div>
    
    <h2>📦 Danh sách đơn hàng gần đây</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Order Number</th>
                <th>Total</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentOrders as $order): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><code><?php echo htmlspecialchars($order['order_number']); ?></code></td>
                <td><?php echo number_format($order['total']); ?>đ</td>
                <td class="status-<?php echo $order['payment_status']; ?>">
                    <?php echo $order['payment_status']; ?>
                </td>
                <td><?php echo $order['status']; ?></td>
                <td><?php echo $order['created_at']; ?></td>
                <td>
                    <?php if ($order['payment_status'] === 'pending'): ?>
                    <a href="?action=test&order_id=<?php echo $order['id']; ?>" class="btn-test">
                        🧪 Test Webhook
                    </a>
                    <?php else: ?>
                    <span style="color:#28a745">✓ Đã thanh toán</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <h2>🔍 Kiểm tra Regex</h2>
    <div class="code">
<?php
$testContents = [
    "THANHTOAN ORD_02d358a8-CHUYEN TIEN",
    "ORD_02d358a8-THANH TOAN",
    "DH73-CHUYEN TIEN",
    "RUT1234567890-THANH TOAN"
];

echo "Test các format content:\n";
echo str_repeat("-", 60) . "\n";

foreach ($testContents as $content) {
    echo "Content: {$content}\n";
    if (preg_match('/(ORD_[a-zA-Z0-9_]+)/', $content, $matches)) {
        echo "  ✓ Match ORD pattern: {$matches[1]}\n";
    } elseif (preg_match('/(DH\d+|RUT\d{10})/', $content, $matches)) {
        echo "  ✓ Match DH/RUT pattern: {$matches[1]}\n";
    } else {
        echo "  ❌ No match\n";
    }
    echo "\n";
}
?>
    </div>
    
    <h2>📁 Logs</h2>
    <p>Kiểm tra file <code>logs/webhook_debug.log</code> để xem chi tiết webhook received.</p>
    
    <p style="margin-top:30px;color:#666;text-align:center">
        Test Page | Web3B Payment System
    </p>
</body>
</html>
