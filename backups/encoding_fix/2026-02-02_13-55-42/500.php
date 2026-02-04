<?php
// Báº¯t lá»—i vÃ  ghi log
function logError() {
    // Láº¥y thÃ´ng tin lá»—i cuá»‘i cÃ¹ng
    $error = error_get_last();
    
    // Náº¿u khÃ´ng cÃ³ lá»—i thá»±c sá»±, táº¡o log cho viá»‡c truy cáº­p trang 500
    if ($error === null) {
        $error = array(
            'type' => 'ACCESS',
            'message' => 'Direct access to 500 error page',
            'file' => __FILE__,
            'line' => 0
        );
    }
    
    // Láº¥y thÃ´ng tin cáº§n thiáº¿t
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'Unknown');
    $errorType = $error['type'] ?? 'Unknown';
    $message = $error['message'] ?? 'Unknown error';
    $file = $error['file'] ?? 'Unknown file';
    $line = $error['line'] ?? 'Unknown line';
    
    // Äá»‹nh dáº¡ng log: [Thá»i gian] [IP] [MÃ£ lá»—i] [Ná»™i dung] [File] [DÃ²ng]
    $logEntry = "[{$timestamp}] [{$ip}] [{$errorType}] [{$message}] [{$file}] [{$line}]" . PHP_EOL;
    
    // Ghi vÃ o file log
    $logFile = __DIR__ . '/../logs/error.log';
    
    // Táº¡o thÆ° má»¥c logs náº¿u chÆ°a tá»“n táº¡i
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Ghi log
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Gá»i hÃ m ghi log
logError();

// Äáº·t HTTP status code 500
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
            <a href="<?php echo $base; ?>app/views/home/index.php" class="back-home-btn">Quay láº¡i trang chá»§</a>
            <div class="sad-file-icon"></div>
        </div>
    </div>
</body>
</html>

