<?php
/**
 * Test Route to Debug HTTP 500 Errors
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #fff3cd; color: #856404; border: 1px solid #ffeeba; margin-bottom: 20px;'>";
echo "<h2>Hệ thống Debug Lỗi 500 - ThuongLo Website</h2>";
echo "<p>Đang giả lập và kiểm tra lỗi khi truy cập module quản trị Homepage...</p>";
echo "</div>";

// Giả lập các tham số GET để tải admin homepage
$_GET['page'] = 'admin';
$_GET['module'] = 'homepage';
$_GET['action'] = 'index';

// Khởi chạy file Front Controller chính và bắt lỗi nếu có
try {
    if (file_exists('index.php')) {
        require_once 'index.php';
    } else {
        throw new Exception("Không tìm thấy tệp tin index.php gốc!");
    }
} catch (Throwable $e) {
    echo "<div style='font-family: Consolas, monospace; padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3 style='margin-top:0;'>Đã phát hiện ngoại lệ / Lỗi Fatal:</h3>";
    echo "<p><b>Thông báo lỗi:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>Tại tệp tin:</b> " . htmlspecialchars($e->getFile()) . " (Dòng " . $e->getLine() . ")</p>";
    echo "<h4>Trace Log chi tiết:</h4>";
    echo "<pre style='white-space: pre-wrap;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
