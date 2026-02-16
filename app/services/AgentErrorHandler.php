<?php

require_once __DIR__ . '/SecurityLogger.php';

/**
 * Agent Registration Error Handler
 * Handles errors and logging for agent registration system
 * Requirements: 5.4
 */
class AgentErrorHandler
{
    private $logger;
    private $logFile;
    
    public function __construct()
    {
        $this->logger = new SecurityLogger();
        $this->logFile = __DIR__ . '/../../logs/agent_registration.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Handle agent registration errors
     */
    public function handleRegistrationError($error, $context = [])
    {
        $errorData = [
            'type' => 'agent_registration_error',
            'message' => $error instanceof Exception ? $error->getMessage() : $error,
            'code' => $error instanceof Exception ? $error->getCode() : 0,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Log to file
        $this->logToFile($errorData);
        
        // Log to security logger if it's a security-related error
        if ($this->isSecurityError($error)) {
            $this->logger->logSecurityEvent('agent_registration_security_error', $errorData);
        }
        
        return $this->formatErrorResponse($error, $context);
    }
    
    /**
     * Handle email notification errors
     */
    public function handleEmailError($error, $context = [])
    {
        $errorData = [
            'type' => 'agent_email_error',
            'message' => $error instanceof Exception ? $error->getMessage() : $error,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        
        $this->logToFile($errorData);
        
        // Email errors should not stop the registration process
        return [
            'continue_process' => true,
            'log_message' => 'Email notification failed but registration continued'
        ];
    }
    
    /**
     * Handle database errors
     */
    public function handleDatabaseError($error, $context = [])
    {
        $errorData = [
            'type' => 'agent_database_error',
            'message' => $error instanceof Exception ? $error->getMessage() : $error,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? null,
            'query' => $context['query'] ?? 'unknown'
        ];
        
        $this->logToFile($errorData);
        
        // Database errors are critical
        return [
            'success' => false,
            'error' => 'Database operation failed. Please try again.',
            'code' => 500
        ];
    }
    
    /**
     * Handle spam prevention errors
     */
    public function handleSpamError($error, $context = [])
    {
        $errorData = [
            'type' => 'agent_spam_prevention',
            'message' => $error instanceof Exception ? $error->getMessage() : $error,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $this->logToFile($errorData);
        
        // Log as security event
        $this->logger->logSecurityEvent('agent_spam_attempt', $errorData);
        
        return [
            'success' => false,
            'error' => 'Request blocked due to spam prevention. Please try again later.',
            'code' => 429
        ];
    }
    
    /**
     * Handle validation errors
     */
    public function handleValidationError($errors, $context = [])
    {
        $errorData = [
            'type' => 'agent_validation_error',
            'errors' => $errors,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        
        $this->logToFile($errorData);
        
        return [
            'success' => false,
            'error' => 'Validation failed',
            'validation_errors' => $errors,
            'code' => 400
        ];
    }
    
    /**
     * Handle admin operation errors
     */
    public function handleAdminError($error, $context = [])
    {
        $errorData = [
            'type' => 'agent_admin_error',
            'message' => $error instanceof Exception ? $error->getMessage() : $error,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'admin_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $this->logToFile($errorData);
        
        // Log admin actions for audit
        $this->logger->logSecurityEvent('agent_admin_operation_error', $errorData);
        
        return [
            'success' => false,
            'error' => 'Admin operation failed. Please try again.',
            'code' => 500
        ];
    }
    
    /**
     * Log successful operations for audit trail
     */
    public function logSuccess($operation, $context = [])
    {
        $logData = [
            'type' => 'agent_success',
            'operation' => $operation,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $this->logToFile($logData);
    }
    
    /**
     * Check if error is security-related
     */
    private function isSecurityError($error)
    {
        $securityKeywords = [
            'sql injection',
            'xss',
            'csrf',
            'unauthorized',
            'permission denied',
            'rate limit',
            'spam',
            'malicious'
        ];
        
        $message = $error instanceof Exception ? $error->getMessage() : $error;
        $message = strtolower($message);
        
        foreach ($securityKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Format error response for API
     */
    private function formatErrorResponse($error, $context = [])
    {
        $message = $error instanceof Exception ? $error->getMessage() : $error;
        $code = $error instanceof Exception ? $error->getCode() : 500;
        
        // Don't expose sensitive information in production
        if (!defined('DEBUG') || !DEBUG) {
            $message = $this->sanitizeErrorMessage($message);
        }
        
        return [
            'success' => false,
            'error' => $message,
            'code' => $code ?: 500
        ];
    }
    
    /**
     * Sanitize error messages for production
     */
    private function sanitizeErrorMessage($message)
    {
        // Map of internal errors to user-friendly messages
        $errorMap = [
            'duplicate entry' => 'This request has already been submitted.',
            'connection refused' => 'Service temporarily unavailable. Please try again later.',
            'access denied' => 'Permission denied.',
            'invalid input' => 'Invalid input provided.',
            'rate limit exceeded' => 'Too many requests. Please try again later.'
        ];
        
        $lowerMessage = strtolower($message);
        
        foreach ($errorMap as $internal => $friendly) {
            if (strpos($lowerMessage, $internal) !== false) {
                return $friendly;
            }
        }
        
        // Default generic message
        return 'An error occurred. Please try again.';
    }
    
    /**
     * Log to file
     */
    private function logToFile($data)
    {
        $logEntry = date('Y-m-d H:i:s') . ' - ' . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        
        // Use file locking to prevent corruption
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Rotate log file if it gets too large (10MB)
        if (file_exists($this->logFile) && filesize($this->logFile) > 10 * 1024 * 1024) {
            $this->rotateLogFile();
        }
    }
    
    /**
     * Rotate log file
     */
    private function rotateLogFile()
    {
        $backupFile = $this->logFile . '.' . date('Y-m-d-H-i-s') . '.bak';
        rename($this->logFile, $backupFile);
        
        // Keep only last 5 backup files
        $logDir = dirname($this->logFile);
        $backupFiles = glob($logDir . '/agent_registration.log.*.bak');
        
        if (count($backupFiles) > 5) {
            // Sort by modification time and remove oldest
            usort($backupFiles, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $filesToRemove = array_slice($backupFiles, 0, count($backupFiles) - 5);
            foreach ($filesToRemove as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * Get error statistics for monitoring
     */
    public function getErrorStats($hours = 24)
    {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $cutoffTime = time() - ($hours * 3600);
        $stats = [
            'total_errors' => 0,
            'by_type' => [],
            'recent_errors' => []
        ];
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $parts = explode(' - ', $line, 2);
            if (count($parts) < 2) continue;
            
            $timestamp = strtotime($parts[0]);
            if ($timestamp < $cutoffTime) continue;
            
            $data = json_decode($parts[1], true);
            if (!$data) continue;
            
            $stats['total_errors']++;
            
            $type = $data['type'] ?? 'unknown';
            $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
            
            if (count($stats['recent_errors']) < 10) {
                $stats['recent_errors'][] = [
                    'timestamp' => $parts[0],
                    'type' => $type,
                    'message' => $data['message'] ?? 'Unknown error'
                ];
            }
        }
        
        return $stats;
    }
}