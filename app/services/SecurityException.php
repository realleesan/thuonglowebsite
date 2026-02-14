<?php
/**
 * SecurityException
 * Custom exception for security-related errors
 */

class SecurityException extends Exception {
    private string $securityType;
    private array $context;
    
    public function __construct(string $message = "", string $securityType = "general", array $context = [], int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        
        $this->securityType = $securityType;
        $this->context = $context;
        
        // Log security exception
        $this->logSecurityException();
    }
    
    /**
     * Get security type
     */
    public function getSecurityType(): string {
        return $this->securityType;
    }
    
    /**
     * Get context data
     */
    public function getContext(): array {
        return $this->context;
    }
    
    /**
     * Log security exception
     */
    private function logSecurityException(): void {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'security_exception',
            'security_type' => $this->securityType,
            'message' => $this->getMessage(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        ];
        
        $logLine = json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        
        // Ensure logs directory exists
        $logDir = 'logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Write to security log
        file_put_contents('logs/security.log', $logLine, FILE_APPEND | LOCK_EX);
    }
}