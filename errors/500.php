<?php
// Bắt lỗi và ghi log
function logError() {
    // Lấy thông tin lỗi cuối cùng
    $error = error_get_last();
    
    // Nếu không có lỗi thực sự, tạo log cho việc truy cập trang 500
    if ($error === null) {
        $error = array(
            'type' => 'ACCESS',
            'message' => 'Direct access to 500 error page',
            'file' => __FILE__,
            'line' => 0
        );
    }
    
    // Lấy thông tin cần thiết
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'Unknown');
    $errorType = $error['type'] ?? 'Unknown';
    $message = $error['message'] ?? 'Unknown error';
    $file = $error['file'] ?? 'Unknown file';
    $line = $error['line'] ?? 'Unknown line';
    
    // Định dạng log: [Thời gian] [IP] [Mã lỗi] [Nội dung] [File] [Dòng]
    $logEntry = "[{$timestamp}] [{$ip}] [{$errorType}] [{$message}] [{$file}] [{$line}]" . PHP_EOL;
    
    // Ghi vào file log
    $logFile = __DIR__ . '/../logs/error.log';
    
    // Tạo thư mục logs nếu chưa tồn tại
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Ghi log
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Gọi hàm ghi log
logError();

// Đặt HTTP status code 500
http_response_code(500);
?>
<?php
$segments = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
$base = '/' . ($segments[0] ?? '') . '/';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Internal Server Error</title>
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/error.css">
</head>
<body>
    <div class="browser-window">
        <div class="browser-header">
            <div class="browser-dots">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
        <div class="content">
            <h1 class="error-code">500</h1>
            <p class="error-message">Internal Server Error</p>
            <a href="<?php echo $base; ?>index.php?page=home" class="back-home-btn">Quay lại trang chủ</a>
            <div class="sad-file-icon"></div>
        </div>
    </div>
</body>
</html>

