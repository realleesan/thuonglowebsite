<?php
/**
 * Read error_log utility script for Hosting environment
 */
define('THUONGLO_INIT', true);
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>";
echo "<h2 style='color: #991b1b; border-bottom: 2px solid #ef4444; padding-bottom: 8px;'>📋 ĐỌC FILE NHẬT KÝ LỖI HỆ THỐNG (ERROR_LOG)</h2>";

$logFiles = [
    __DIR__ . '/error_log',
    __DIR__ . '/app/controllers/error_log',
    __DIR__ . '/../error_log'
];

$found = false;

foreach ($logFiles as $logFile) {
    if (file_exists($logFile) && is_readable($logFile)) {
        $found = true;
        echo "<h3 style='color: #2563eb;'>✔ Tìm thấy file lỗi tại: <code>" . htmlspecialchars($logFile) . "</code></h3>";
        
        $lines = file($logFile);
        $totalLines = count($lines);
        $start = max(0, $totalLines - 50); // Lấy 50 dòng cuối
        
        echo "<pre style='background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; overflow-x: auto; max-height: 500px;'>";
        for ($i = $start; $i < $totalLines; $i++) {
            echo htmlspecialchars($lines[$i]);
        }
        echo "</pre>";
    }
}

if (!$found) {
    echo "<p style='color: orange; font-weight: bold;'>⚠️ Không tìm thấy file `error_log` nào trong các thư mục thông thường hoặc file không có quyền đọc.</p>";
    echo "<p>Hãy thử kiểm tra bảng điều khiển Hosting (cPanel / DirectAdmin) mục 'Error Logs' để xem log lỗi chi tiết.</p>";
}

echo "</div>";
