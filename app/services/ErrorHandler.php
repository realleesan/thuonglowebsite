<?php
/**
 * Error Handler
 * Xử lý lỗi và logging cho view system
 */

class ErrorHandler {
    private $logFile;
    
    public function __construct($logFile = null) {
        $this->logFile = $logFile ?? __DIR__ . '/../../logs/view_errors.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log error with context
     */
    public function logError($message, $context = [], $level = 'ERROR') {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        
        $logEntry = "[{$timestamp}] {$level}: {$message}";
        if ($contextStr) {
            $logEntry .= " Context: {$contextStr}";
        }
        $logEntry .= PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Handle database connection errors
     */
    public function handleDatabaseError($exception, $context = []) {
        $this->logError(
            "Database error: " . $exception->getMessage(),
            array_merge($context, [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]),
            'ERROR'
        );
        
        return [
            'success' => false,
            'message' => 'Đang có lỗi kỹ thuật, vui lòng thử lại sau',
            'error_code' => 'DB_ERROR'
        ];
    }
    
    /**
     * Handle model method errors
     */
    public function handleModelError($exception, $modelName, $methodName, $params = []) {
        $this->logError(
            "Model error in {$modelName}::{$methodName}: " . $exception->getMessage(),
            [
                'model' => $modelName,
                'method' => $methodName,
                'params' => $params,
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ],
            'ERROR'
        );
        
        return [
            'success' => false,
            'message' => 'Không thể tải dữ liệu, vui lòng thử lại',
            'error_code' => 'MODEL_ERROR'
        ];
    }
    
    /**
     * Handle view rendering errors
     */
    public function handleViewError($exception, $viewName, $data = []) {
        $this->logError(
            "View rendering error in {$viewName}: " . $exception->getMessage(),
            [
                'view' => $viewName,
                'data_keys' => array_keys($data),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ],
            'ERROR'
        );
        
        return [
            'success' => false,
            'message' => 'Lỗi hiển thị trang, vui lòng thử lại',
            'error_code' => 'VIEW_ERROR'
        ];
    }
    
    /**
     * Handle validation errors
     */
    public function handleValidationError($errors, $context = []) {
        $this->logError(
            "Validation error: " . implode(', ', $errors),
            $context,
            'WARNING'
        );
        
        return [
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $errors,
            'error_code' => 'VALIDATION_ERROR'
        ];
    }
    
    /**
     * Handle permission errors
     */
    public function handlePermissionError($action, $userId = null) {
        $this->logError(
            "Permission denied for action: {$action}",
            [
                'action' => $action,
                'user_id' => $userId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ],
            'WARNING'
        );
        
        return [
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động này',
            'error_code' => 'PERMISSION_ERROR'
        ];
    }
    
    /**
     * Handle not found errors
     */
    public function handleNotFoundError($resource, $id = null) {
        $this->logError(
            "Resource not found: {$resource}" . ($id ? " (ID: {$id})" : ''),
            [
                'resource' => $resource,
                'id' => $id,
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ],
            'INFO'
        );
        
        return [
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu yêu cầu',
            'error_code' => 'NOT_FOUND'
        ];
    }
    
    /**
     * Handle rate limiting errors
     */
    public function handleRateLimitError($limit, $window) {
        $this->logError(
            "Rate limit exceeded: {$limit} requests per {$window} seconds",
            [
                'limit' => $limit,
                'window' => $window,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ],
            'WARNING'
        );
        
        return [
            'success' => false,
            'message' => 'Quá nhiều yêu cầu, vui lòng thử lại sau',
            'error_code' => 'RATE_LIMIT'
        ];
    }
    
    /**
     * Log info message
     */
    public function logInfo($message, $context = []) {
        $this->logError($message, $context, 'INFO');
    }
    
    /**
     * Log warning message
     */
    public function logWarning($message, $context = []) {
        $this->logError($message, $context, 'WARNING');
    }
    
    /**
     * Log debug message (only in development)
     */
    public function logDebug($message, $context = []) {
        if (defined('DEBUG') && DEBUG) {
            $this->logError($message, $context, 'DEBUG');
        }
    }
    
    /**
     * Get recent error logs
     */
    public function getRecentLogs($lines = 100) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $logs = [];
        $file = new SplFileObject($this->logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $logs[] = $line;
            }
            $file->next();
        }
        
        return $logs;
    }
    
    /**
     * Clear old logs (keep last N days)
     */
    public function clearOldLogs($daysToKeep = 30) {
        if (!file_exists($this->logFile)) {
            return;
        }
        
        $cutoffDate = date('Y-m-d', strtotime("-{$daysToKeep} days"));
        $tempFile = $this->logFile . '.tmp';
        
        $input = fopen($this->logFile, 'r');
        $output = fopen($tempFile, 'w');
        
        while (($line = fgets($input)) !== false) {
            // Extract date from log line
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
                if ($matches[1] >= $cutoffDate) {
                    fwrite($output, $line);
                }
            }
        }
        
        fclose($input);
        fclose($output);
        
        rename($tempFile, $this->logFile);
    }
}