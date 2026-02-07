<?php
/**
 * Affiliate Error Handler
 * Xử lý và hiển thị lỗi cho affiliate system
 */

class AffiliateErrorHandler {
    
    /**
     * Display error message
     * @param string $message Error message
     * @param string $type Error type (error, warning, info)
     * @return string HTML error message
     */
    public static function displayError($message, $type = 'error') {
        $iconClass = self::getIconClass($type);
        $alertClass = self::getAlertClass($type);
        
        return <<<HTML
<div class="alert alert-{$alertClass}">
    <i class="{$iconClass}"></i>
    <span>{$message}</span>
</div>
HTML;
    }
    
    /**
     * Display empty state message
     * @param string $message Empty state message
     * @param string $icon Icon class (optional)
     * @return string HTML empty state
     */
    public static function displayEmptyState($message, $icon = 'fas fa-inbox') {
        return <<<HTML
<div class="empty-state">
    <i class="{$icon}"></i>
    <p>{$message}</p>
</div>
HTML;
    }
    
    /**
     * Log error to file
     * @param string $message Error message
     * @param string $context Additional context
     * @return void
     */
    public static function logError($message, $context = '') {
        $logFile = __DIR__ . '/../logs/affiliate_error.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}";
        
        if (!empty($context)) {
            $logMessage .= " | Context: {$context}";
        }
        
        $logMessage .= PHP_EOL;
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        error_log($logMessage, 3, $logFile);
    }
    
    /**
     * Handle JSON loading error
     * @param array $error Error details from DataLoader
     * @return string HTML error message
     */
    public static function handleJsonError($error) {
        $code = $error['code'] ?? 'UNKNOWN_ERROR';
        $message = $error['message'] ?? 'Đã xảy ra lỗi không xác định';
        
        // Log error
        self::logError("JSON Error [{$code}]: {$message}");
        
        // Display user-friendly message
        switch ($code) {
            case 'FILE_NOT_FOUND':
                return self::displayError('Không tìm thấy file dữ liệu. Vui lòng liên hệ quản trị viên.', 'error');
            
            case 'FILE_READ_ERROR':
                return self::displayError('Không thể đọc file dữ liệu. Vui lòng thử lại sau.', 'error');
            
            case 'JSON_PARSE_ERROR':
                return self::displayError('Dữ liệu không hợp lệ. Vui lòng liên hệ quản trị viên.', 'error');
            
            case 'EMPTY_DATA':
                return self::displayEmptyState('Chưa có dữ liệu');
            
            default:
                return self::displayError($message, 'error');
        }
    }
    
    /**
     * Handle validation error
     * @param array $validation Validation result
     * @return string HTML error message
     */
    public static function handleValidationError($validation) {
        $message = $validation['message'] ?? 'Dữ liệu không hợp lệ';
        
        // Log error
        self::logError("Validation Error: {$message}");
        
        return self::displayError($message, 'warning');
    }
    
    /**
     * Get icon class for error type
     * @param string $type Error type
     * @return string Icon class
     */
    private static function getIconClass($type) {
        $icons = [
            'error' => 'fas fa-exclamation-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'info' => 'fas fa-info-circle',
            'success' => 'fas fa-check-circle'
        ];
        
        return $icons[$type] ?? $icons['error'];
    }
    
    /**
     * Get alert class for error type
     * @param string $type Error type
     * @return string Alert class
     */
    private static function getAlertClass($type) {
        $classes = [
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            'success' => 'success'
        ];
        
        return $classes[$type] ?? $classes['error'];
    }
    
    /**
     * Display 404 error page
     * @param string $message Custom message (optional)
     * @return void
     */
    public static function display404($message = null) {
        http_response_code(404);
        
        if ($message === null) {
            $message = 'Trang bạn tìm kiếm không tồn tại.';
        }
        
        echo <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #F9FAFB;
            color: #374151;
        }
        .error-container {
            text-align: center;
            padding: 40px;
        }
        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: #356DF1;
            margin: 0;
        }
        .error-message {
            font-size: 24px;
            font-weight: 600;
            color: #111827;
            margin: 20px 0;
        }
        .error-description {
            font-size: 16px;
            color: #6B7280;
            margin: 20px 0;
        }
        .back-btn {
            display: inline-block;
            padding: 12px 24px;
            background: #356DF1;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: #000000;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <h2 class="error-message">Không tìm thấy trang</h2>
        <p class="error-description">{$message}</p>
        <a href="?page=affiliate&module=dashboard" class="back-btn">
            <i class="fas fa-home"></i> Về trang chủ
        </a>
    </div>
</body>
</html>
HTML;
        exit;
    }
    
    /**
     * Validate required fields
     * @param array $data Data to validate
     * @param array $required Required field names
     * @return array Validation result
     */
    public static function validateRequired($data, $required) {
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            return [
                'valid' => false,
                'message' => 'Thiếu các trường bắt buộc: ' . implode(', ', $missing)
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Dữ liệu hợp lệ'
        ];
    }
    
    /**
     * Validate amount field
     * @param mixed $amount Amount to validate
     * @param int $min Minimum amount (optional)
     * @param int $max Maximum amount (optional)
     * @return array Validation result
     */
    public static function validateAmount($amount, $min = null, $max = null) {
        if (!is_numeric($amount)) {
            return [
                'valid' => false,
                'message' => 'Số tiền không hợp lệ'
            ];
        }
        
        $amount = (float) $amount;
        
        if ($amount < 0) {
            return [
                'valid' => false,
                'message' => 'Số tiền không được âm'
            ];
        }
        
        if ($min !== null && $amount < $min) {
            return [
                'valid' => false,
                'message' => 'Số tiền tối thiểu là ' . AffiliateDataLoader::formatCurrency($min)
            ];
        }
        
        if ($max !== null && $amount > $max) {
            return [
                'valid' => false,
                'message' => 'Số tiền tối đa là ' . AffiliateDataLoader::formatCurrency($max)
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Số tiền hợp lệ'
        ];
    }
}
