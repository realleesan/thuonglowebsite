<?php
/**
 * Quick Webhook Status Check
 * Kiểm tra nhanh trạng thái webhook
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/models/SepayWebhookLogModel.php';

echo "🔍 KIỂM TRA TRẠNG THÁI WEBHOOK\n";
echo str_repeat('=', 60) . "\n\n";

// 1. Kiểm tra database connection
echo "1. Kiểm tra kết nối database... ";
try {
    $webhookModel = new SepayWebhookLogModel();
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ LỖI: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Kiểm tra bảng sepay_webhooks_log
echo "2. Kiểm tra bảng sepay_webhooks_log... ";
try {
    $stats = $webhookModel->getStats();
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ LỖI: " . $e->getMessage() . "\n";
    echo "   Có thể bảng chưa được tạo. Chạy migration.\n";
    exit(1);
}

// 3. Hiển thị thống kê
echo "\n📊 THỐNG KÊ:\n";
echo "   - Tổng webhooks: " . ($stats['total_webhooks'] ?? 0) . "\n";
echo "   - Payment IN: " . ($stats['payment_in_count'] ?? 0) . "\n";
echo "   - Payment OUT: " . ($stats['payment_out_count'] ?? 0) . "\n";
echo "   - Đã xử lý: " . ($stats['processed_count'] ?? 0) . "\n";
echo "   - Thành công: " . ($stats['success_count'] ?? 0) . "\n";
echo "   - Thất bại: " . ($stats['failed_count'] ?? 0) . "\n";

// 4. Hiển thị 5 webhooks gần nhất
echo "\n📝 5 WEBHOOKS GẦN NHẤT:\n";
$logs = $webhookModel->getRecentWithPagination(1, 5);

if (empty($logs['data'])) {
    echo "   ⚠️  Chưa có webhook nào\n";
    echo "\n💡 HƯỚNG DẪN:\n";
    echo "   1. Chạy: php scripts/simulate_sepay_webhook.php\n";
    echo "   2. Hoặc chuyển khoản vào: 0389654785 (MB Bank)\n";
    echo "   3. Nội dung: DH1\n";
    echo "   4. Chạy lại script này để kiểm tra\n";
} else {
    foreach ($logs['data'] as $i => $log) {
        echo "\n   " . ($i + 1) . ". ID: {$log['id']}\n";
        echo "      Loại: {$log['webhook_type']}\n";
        echo "      Transaction: {$log['transaction_id']}\n";
        echo "      Mã tham chiếu: {$log['reference_code']}\n";
        echo "      Số tiền: " . number_format($log['amount'], 0, ',', '.') . " đ\n";
        echo "      Trạng thái: " . ($log['processed'] ? ($log['success'] ? '✅ Thành công' : '❌ Thất bại') : '⏳ Chưa xử lý') . "\n";
        echo "      Thời gian: {$log['received_at']}\n";
        if ($log['processing_error']) {
            echo "      Lỗi: {$log['processing_error']}\n";
        }
    }
}

// 5. Kiểm tra file logs
echo "\n📁 KIỂM TRA FILE LOGS:\n";
$logFiles = [
    'logs/webhook_debug.log' => 'Webhook debug log',
    'logs/webhook.log' => 'Webhook activity log',
    'logs/payment.log' => 'Payment activity log',
];

foreach ($logFiles as $file => $description) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        $sizeKB = round($size / 1024, 2);
        echo "   ✅ {$description}: {$sizeKB} KB\n";
        
        // Hiển thị 3 dòng cuối
        if ($size > 0) {
            $lines = file($fullPath);
            $lastLines = array_slice($lines, -3);
            echo "      Dòng cuối:\n";
            foreach ($lastLines as $line) {
                echo "      " . trim($line) . "\n";
            }
        }
    } else {
        echo "   ⚠️  {$description}: Chưa tồn tại\n";
    }
}

// 6. Kiểm tra webhook endpoint
echo "\n🌐 KIỂM TRA WEBHOOK ENDPOINT:\n";
$webhookUrl = 'https://test1.web3b.com/api.php?action=webhook/test';
echo "   URL: {$webhookUrl}\n";
echo "   Testing... ";

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ LỖI: {$error}\n";
} elseif ($httpCode === 200) {
    echo "✅ OK (HTTP {$httpCode})\n";
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "   Response: " . ($data['message'] ?? 'OK') . "\n";
    }
} else {
    echo "⚠️  HTTP {$httpCode}\n";
}

// 7. Kiểm tra cấu hình Sepay
echo "\n⚙️  CẤU HÌNH SEPAY:\n";
$config = require __DIR__ . '/../config.php';
$sepayConfig = $config['sepay'] ?? [];

echo "   API Key: " . (empty($sepayConfig['api_key']) ? '❌ Chưa cấu hình' : '✅ Đã cấu hình') . "\n";
echo "   Account Number: " . ($sepayConfig['account_number'] ?? '❌ Chưa cấu hình') . "\n";
echo "   Bank Code: " . ($sepayConfig['bank_code'] ?? '❌ Chưa cấu hình') . "\n";
echo "   Test Mode: " . ($sepayConfig['test_mode'] ? '✅ Bật' : '❌ Tắt') . "\n";

// 8. Kết luận
echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ HOÀN TẤT KIỂM TRA\n\n";

if (($stats['total_webhooks'] ?? 0) === 0) {
    echo "⚠️  CẢNH BÁO: Chưa nhận được webhook nào!\n\n";
    echo "🔧 KHẮC PHỤC:\n";
    echo "   1. Test local: php scripts/simulate_sepay_webhook.php\n";
    echo "   2. Xem logs: php scripts/view_webhook_logs.php\n";
    echo "   3. Đọc hướng dẫn: docs/WEBHOOK_DEBUG.md\n";
    echo "   4. Cấu hình webhook URL trong Sepay:\n";
    echo "      https://test1.web3b.com/api.php?action=webhook/sepay\n";
} else {
    echo "✅ Hệ thống đang hoạt động bình thường!\n";
}

echo "\n";
