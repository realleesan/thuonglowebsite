<?php
/**
 * AuthErrorHandler Service
 * Handles authentication-related errors and logging
 */

class AuthErrorHandler {
    private string $logFile;
    
    public function __construct() {
        $this->logFile = 'logs/auth.log';
        $this->ensureLogDirectory();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Handle validation errors
     */
    public function handleValidationError(array $errors): array {
        return [
            'success' => false,
            'type' => 'validation',
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $errors,
            'redirect' => null
        ];
    }
    
    /**
     * Handle authentication errors
     */
    public function handleAuthenticationError(string $type, array $context = []): array {
        $messages = [
            'invalid_credentials' => 'Email/số điện thoại hoặc mật khẩu không đúng',
            'account_locked' => 'Tài khoản đã bị khóa do đăng nhập sai quá nhiều lần',
            'account_banned' => 'Tài khoản đã bị cấm',
            'account_inactive' => 'Tài khoản chưa được kích hoạt',
            'session_expired' => 'Phiên đăng nhập đã hết hạn',
            'insufficient_permissions' => 'Bạn không có quyền truy cập tài nguyên này',
            'rate_limited' => 'Quá nhiều lần thử đăng nhập. Vui lòng thử lại sau',
        ];
        
        $message = $messages[$type] ?? 'Lỗi xác thực';
        
        // Log authentication error
        $this->logSecurityEvent('auth_error', array_merge($context, [
            'type' => $type,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]));
        
        return [
            'success' => false,
            'type' => 'authentication',
            'message' => $message,
            'errors' => [],
            'redirect' => $this->getRedirectForAuthError($type)
        ];
    }
    
    /**
     * Handle security errors
     */
    public function handleSecurityError(string $type, array $context = []): array {
        $messages = [
            'csrf_mismatch' => 'Token bảo mật không hợp lệ. Vui lòng thử lại',
            'sql_injection' => 'Phát hiện nội dung không an toàn',
            'xss_attempt' => 'Phát hiện nội dung không an toàn',
            'suspicious_activity' => 'Phát hiện hoạt động đáng ngờ',
            'invalid_token' => 'Token không hợp lệ hoặc đã hết hạn',
        ];
        
        $message = $messages[$type] ?? 'Lỗi bảo mật';
        
        // Log security event
        $this->logSecurityEvent('security_violation', array_merge($context, [
            'type' => $type,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'severity' => 'high',
        ]));
        
        return [
            'success' => false,
            'type' => 'security',
            'message' => $message,
            'errors' => [],
            'redirect' => '/auth/login'
        ];
    }
    
    /**
     * Handle system errors
     */
    public function handleSystemError(\Exception $error, array $context = []): array {
        // Log system error
        $this->logSecurityEvent('system_error', array_merge($context, [
            'error' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
        ]));
        
        return [
            'success' => false,
            'type' => 'system',
            'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau',
            'errors' => [],
            'redirect' => null
        ];
    }
    
    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $context = []): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'context' => $context,
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        
        // Write to log file
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get redirect URL for authentication errors
     */
    private function getRedirectForAuthError(string $type): ?string {
        switch ($type) {
            case 'session_expired':
            case 'insufficient_permissions':
                return '/auth/login';
            case 'account_locked':
            case 'rate_limited':
                return '/auth/login?locked=1';
            case 'account_banned':
                return '/auth/login?banned=1';
            default:
                return null;
        }
    }
    
    /**
     * Create success response
     */
    public function createSuccessResponse(string $message, array $data = [], ?string $redirect = null): array {
        return [
            'success' => true,
            'type' => 'success',
            'message' => $message,
            'data' => $data,
            'errors' => [],
            'redirect' => $redirect
        ];
    }
    
    /**
     * Log successful authentication
     */
    public function logSuccessfulAuth(array $user): void {
        $this->logSecurityEvent('auth_success', [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    }
    
    /**
     * Log failed authentication attempt
     */
    public function logFailedAuth(string $identifier, string $reason = 'invalid_credentials'): void {
        $this->logSecurityEvent('auth_failed', [
            'identifier' => $identifier,
            'reason' => $reason,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    }
    
    /**
     * Log logout event
     */
    public function logLogout(array $user): void {
        $this->logSecurityEvent('auth_logout', [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);
    }
}