<?php
/**
 * Check SePay Webhook Configuration
 */

require_once __DIR__ . '/config/config.php';

echo "=== CẤU HÌNH SEPAY WEBHOOK ===\n\n";

// Hiển thị webhook URL đúng
$protocol = 'https://';  // SePay yêu cầu HTTPS
$webhookUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'test1.web3b.com') . '/api.php?action=webhook&provider=sepay';

echo "✅ Webhook URL phải cấu hình trong SePay Dashboard:\n";
echo "   {$webhookUrl}\n\n";

// Kiểm tra xem webhook endpoint có hoạt động không
echo "=== KIỂM TRA WEBHOOK ENDPOINT ===\n\n";

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode == 200 || $httpCode == 400) {
    echo "✅ Webhook endpoint hoạt động (HTTP {$httpCode})\n";
    echo "   - 400 là bình thường vì chưa có dữ liệu\n";
    echo "   - 200 là OK khi có dữ liệu đúng\n\n";
} else {
    echo "❌ Webhook endpoint lỗi (HTTP {$httpCode})\n";
    if ($curlError) echo "   cURL Error: {$curlError}\n";
    echo "   Response: {$response}\n\n";
}

// Kiểm tra SePay config
echo "=== CẤU HÌNH HỆ THỐNG ===\n\n";
$sepayConfig = require __DIR__ . '/config/sepay.php';
echo "API Token: " . (empty($sepayConfig['api_token']) ? "❌ Chưa cấu hình" : "✅ Đã cấu hình") . "\n";
echo "Webhook Secret: " . (empty($sepayConfig['webhook_secret']) ? "❌ Chưa cấu hình" : "✅ Đã cấu hình") . "\n";
echo "Account Number: " . ($sepayConfig['account_number'] ?? 'Chưa cấu hình') . "\n";
echo "Bank Code: " . ($sepayConfig['bank_code'] ?? 'Chưa cấu hình') . "\n\n";

echo "=== HƯỚNG DẪN CẤU HÌNH TRONG SEPAY DASHBOARD ===\n\n";
echo "1. Đăng nhập vào https://sepay.vn\n";
echo "2. Vào Cài đặt → Webhook\n";
echo "3. Thêm URL: {$webhookUrl}\n";
echo "4. Chọn các sự kiện: Có giao dịch đến\n";
echo "5. Lưu cấu hình\n\n";

echo "=== LƯU Ý QUAN TRỌNG ===\n\n";
echo "- SePay CHỈ gửi webhook khi có GIAO DỊCH THẬT từ ngân hàng\n";
echo "- Không gửi webhook khi tạo QR code\n";
echo "- Phải đợi người dùng chuyển khoản xong thì SePay mới gửi\n";
echo "- Nếu chuyển khoản xong mà chưa nhận webhook, kiểm tra:\n";
echo "  + URL webhook đã đúng chưa\n";
echo "  + Domain có bị chặn không\n";
echo "  + HTTPS certificate có hợp lệ không\n";
