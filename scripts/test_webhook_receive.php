<?php
/**
 * Test Webhook Receiver
 * Ghi log tất cả request đến để debug webhook từ Sepay
 */

// Tạo thư mục logs nếu chưa có
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = $logDir . '/webhook_debug.log';

// Lấy thời gian hiện tại
$timestamp = date('Y-m-d H:i:s');

// Lấy raw POST data
$rawData = file_get_contents('php://input');

// Lấy tất cả headers
$headers = [];
if (function_exists('getallheaders')) {
    $headers = getallheaders();
} else {
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
            $headers[$headerName] = $value;
        }
    }
}

// Lấy IP address
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Lấy request method
$method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';

// Lấy query string
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Lấy $_POST data
$postData = $_POST;

// Tạo log entry
$logEntry = [
    'timestamp' => $timestamp,
    'method' => $method,
    'ip_address' => $ipAddress,
    'query_string' => $queryString,
    'headers' => $headers,
    'raw_data' => $rawData,
    'post_data' => $postData,
    'get_data' => $_GET,
    'server_info' => [
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? '',
        'SERVER_PROTOCOL' => $_SERVER['SERVER_PROTOCOL'] ?? '',
        'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? '',
        'CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? 0,
    ]
];

// Ghi vào file log
$logText = "\n" . str_repeat('=', 80) . "\n";
$logText .= "WEBHOOK REQUEST - {$timestamp}\n";
$logText .= str_repeat('=', 80) . "\n";
$logText .= json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$logText .= "\n" . str_repeat('=', 80) . "\n";

file_put_contents($logFile, $logText, FILE_APPEND);

// Trả về response cho Sepay
header('Content-Type: application/json; charset=utf-8');
http_response_code(200);

echo json_encode([
    'success' => true,
    'message' => 'Webhook received and logged',
    'timestamp' => $timestamp,
    'log_file' => basename($logFile)
], JSON_UNESCAPED_UNICODE);
