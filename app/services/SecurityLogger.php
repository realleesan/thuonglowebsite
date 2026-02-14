<?php
/**
 * Security Logger
 * Comprehensive security logging and monitoring system
 * Logs all authentication attempts, security events, and suspicious activities
 */

class SecurityLogger {
    private string $logPath;
    private string $securityLogFile;
    private string $authLogFile;
    private string $suspiciousLogFile;
    private array $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../../config.php';
        $this->logPath = __DIR__ . '/../../logs/';
        $this->securityLogFile = $this->logPath . 'security.log';
        $this->authLogFile = $this->logPath . 'auth.log';
        $this->suspiciousLogFile = $this->logPath . 'suspicious.log';
        
        // Ensure log directory exists
        $this->ensureLogDirectory();
    }
    
    /**
     * Log authentication attempts (success/failure)
     */
    public function logAuthAttempt(string $type, array $data): void {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'session_id' => session_id(),
            'data' => $data
        ];
        
        $this->writeLog($this->authLogFile, $logData);
        
        // Also log to security log for failed attempts
        if ($type === 'login_failed' || $type === 'registration_failed') {
            $this->logSecurityEvent('auth_failure', $logData);
        }
    }
    
    /**
     * Log security violations and suspicious activities
     */
    public function logSecurityEvent(string $event, array $context = []): void {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'severity' => $this->getEventSeverity($event),
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'session_id' => session_id(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'context' => $context,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? ''
        ];
        
        $this->writeLog($this->securityLogFile, $logData);
        
        // Log high severity events to suspicious log
        if ($logData['severity'] === 'high' || $logData['severity'] === 'critical') {
            $this->logSuspiciousActivity($event, $logData);
        }
    }
    
    /**
     * Log suspicious activities that require immediate attention
     */
    public function logSuspiciousActivity(string $activity, array $data): void {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'activity' => $activity,
            'severity' => 'suspicious',
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'session_id' => session_id(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'data' => $data,
            'requires_attention' => true
        ];
        
        $this->writeLog($this->suspiciousLogFile, $logData);
        
        // Send alert for critical suspicious activities
        $this->sendSecurityAlert($activity, $logData);
    }
    
    /**
     * Log rate limiting triggers
     */
    public function logRateLimit(string $action, array $data): void {
        $this->logSecurityEvent('rate_limit_triggered', [
            'action' => $action,
            'attempts' => $data['attempts'] ?? 0,
            'time_window' => $data['time_window'] ?? 0,
            'identifier' => $data['identifier'] ?? $this->getClientIdentifier()
        ]);
    }
    
    /**
     * Log password reset requests
     */
    public function logPasswordReset(string $type, array $data): void {
        $logData = [
            'type' => $type,
            'email' => $data['email'] ?? 'unknown',
            'token_generated' => $data['token_generated'] ?? false,
            'token_used' => $data['token_used'] ?? false
        ];
        
        $this->logAuthAttempt('password_reset_' . $type, $logData);
    }
    
    /**
     * Log session management events
     */
    public function logSessionEvent(string $event, array $data = []): void {
        $logData = [
            'event' => $event,
            'session_id' => session_id(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'data' => $data
        ];
        
        $this->logSecurityEvent('session_' . $event, $logData);
    }
    
    /**
     * Log input validation failures
     */
    public function logValidationFailure(string $type, array $data): void {
        $severity = $this->getValidationSeverity($type);
        
        $this->logSecurityEvent('validation_failure', [
            'validation_type' => $type,
            'severity' => $severity,
            'input_data' => $this->sanitizeLogData($data),
            'potential_attack' => in_array($type, ['sql_injection', 'xss', 'csrf'])
        ]);
    }
    
    /**
     * Log access control violations
     */
    public function logAccessViolation(string $resource, array $data): void {
        $this->logSecurityEvent('access_violation', [
            'resource' => $resource,
            'required_role' => $data['required_role'] ?? 'unknown',
            'user_role' => $data['user_role'] ?? 'guest',
            'user_id' => $_SESSION['user_id'] ?? null
        ]);
    }
    
    /**
     * Log system errors related to security
     */
    public function logSystemError(string $error, array $context = []): void {
        $this->logSecurityEvent('system_error', [
            'error' => $error,
            'context' => $context,
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ]);
    }
    
    /**
     * Get security event statistics
     */
    public function getSecurityStats(int $hours = 24): array {
        $stats = [
            'auth_attempts' => $this->countLogEntries($this->authLogFile, $hours),
            'security_events' => $this->countLogEntries($this->securityLogFile, $hours),
            'suspicious_activities' => $this->countLogEntries($this->suspiciousLogFile, $hours),
            'failed_logins' => $this->countSpecificEvents($this->authLogFile, 'login_failed', $hours),
            'rate_limits' => $this->countSpecificEvents($this->securityLogFile, 'rate_limit_triggered', $hours),
            'access_violations' => $this->countSpecificEvents($this->securityLogFile, 'access_violation', $hours)
        ];
        
        return $stats;
    }
    
    /**
     * Get recent security events
     */
    public function getRecentEvents(int $limit = 50): array {
        $events = [];
        
        // Read recent events from security log
        if (file_exists($this->securityLogFile)) {
            $lines = $this->getRecentLogLines($this->securityLogFile, $limit);
            foreach ($lines as $line) {
                $event = json_decode($line, true);
                if ($event) {
                    $events[] = $event;
                }
            }
        }
        
        // Sort by timestamp (most recent first)
        usort($events, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return array_slice($events, 0, $limit);
    }
    
    /**
     * Check for suspicious patterns
     */
    public function detectSuspiciousPatterns(): array {
        $patterns = [];
        
        // Check for multiple failed logins from same IP
        $failedLogins = $this->getFailedLoginsByIp(1); // Last 1 hour
        foreach ($failedLogins as $ip => $count) {
            if ($count >= 10) {
                $patterns[] = [
                    'type' => 'brute_force_attempt',
                    'ip' => $ip,
                    'count' => $count,
                    'severity' => 'high'
                ];
            }
        }
        
        // Check for SQL injection attempts
        $sqlInjections = $this->countSpecificEvents($this->securityLogFile, 'validation_failure', 1, ['validation_type' => 'sql_injection']);
        if ($sqlInjections > 0) {
            $patterns[] = [
                'type' => 'sql_injection_attempts',
                'count' => $sqlInjections,
                'severity' => 'critical'
            ];
        }
        
        // Check for XSS attempts
        $xssAttempts = $this->countSpecificEvents($this->securityLogFile, 'validation_failure', 1, ['validation_type' => 'xss']);
        if ($xssAttempts > 0) {
            $patterns[] = [
                'type' => 'xss_attempts',
                'count' => $xssAttempts,
                'severity' => 'high'
            ];
        }
        
        return $patterns;
    }
    
    /**
     * Clean old log files
     */
    public function cleanOldLogs(int $daysToKeep = 30): void {
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        
        $logFiles = [
            $this->authLogFile,
            $this->securityLogFile,
            $this->suspiciousLogFile
        ];
        
        foreach ($logFiles as $logFile) {
            if (file_exists($logFile)) {
                $this->cleanLogFile($logFile, $cutoffTime);
            }
        }
    }
    
    // Private helper methods
    
    private function ensureLogDirectory(): void {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
        
        // Create .htaccess to protect log files
        $htaccessFile = $this->logPath . '.htaccess';
        if (!file_exists($htaccessFile)) {
            file_put_contents($htaccessFile, "Deny from all\n");
        }
    }
    
    private function writeLog(string $logFile, array $data): void {
        $logEntry = json_encode($data) . "\n";
        
        // Use file locking to prevent corruption
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Rotate log if it gets too large (10MB)
        if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
            $this->rotateLog($logFile);
        }
    }
    
    private function rotateLog(string $logFile): void {
        $rotatedFile = $logFile . '.' . date('Y-m-d-H-i-s');
        rename($logFile, $rotatedFile);
        
        // Compress old log file
        if (function_exists('gzencode')) {
            $content = file_get_contents($rotatedFile);
            file_put_contents($rotatedFile . '.gz', gzencode($content));
            unlink($rotatedFile);
        }
    }
    
    private function getClientIp(): string {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }
        
        return 'unknown';
    }
    
    private function getClientIdentifier(): string {
        $ip = $this->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        return md5($ip . $userAgent);
    }
    
    private function getEventSeverity(string $event): string {
        $severityMap = [
            'sql_injection' => 'critical',
            'xss_attempt' => 'high',
            'csrf_mismatch' => 'high',
            'brute_force' => 'high',
            'access_violation' => 'medium',
            'rate_limit_triggered' => 'medium',
            'auth_failure' => 'low',
            'session_timeout' => 'low',
            'validation_failure' => 'medium'
        ];
        
        return $severityMap[$event] ?? 'low';
    }
    
    private function getValidationSeverity(string $type): string {
        $severityMap = [
            'sql_injection' => 'critical',
            'xss' => 'high',
            'csrf' => 'high',
            'email' => 'low',
            'password' => 'low',
            'phone' => 'low'
        ];
        
        return $severityMap[$type] ?? 'medium';
    }
    
    private function sanitizeLogData(array $data): array {
        // Remove sensitive information from log data
        $sensitiveKeys = ['password', 'token', 'csrf_token', 'credit_card'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '[REDACTED]';
            }
        }
        
        return $data;
    }
    
    private function countLogEntries(string $logFile, int $hours): int {
        if (!file_exists($logFile)) {
            return 0;
        }
        
        $cutoffTime = time() - ($hours * 3600);
        $count = 0;
        
        $handle = fopen($logFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $entry = json_decode($line, true);
                if ($entry && strtotime($entry['timestamp']) >= $cutoffTime) {
                    $count++;
                }
            }
            fclose($handle);
        }
        
        return $count;
    }
    
    private function countSpecificEvents(string $logFile, string $eventType, int $hours, array $filters = []): int {
        if (!file_exists($logFile)) {
            return 0;
        }
        
        $cutoffTime = time() - ($hours * 3600);
        $count = 0;
        
        $handle = fopen($logFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $entry = json_decode($line, true);
                if ($entry && 
                    strtotime($entry['timestamp']) >= $cutoffTime &&
                    (isset($entry['event']) && $entry['event'] === $eventType || 
                     isset($entry['type']) && $entry['type'] === $eventType)) {
                    
                    // Apply additional filters
                    $matches = true;
                    foreach ($filters as $key => $value) {
                        if (!isset($entry['context'][$key]) || $entry['context'][$key] !== $value) {
                            $matches = false;
                            break;
                        }
                    }
                    
                    if ($matches) {
                        $count++;
                    }
                }
            }
            fclose($handle);
        }
        
        return $count;
    }
    
    private function getRecentLogLines(string $logFile, int $limit): array {
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = [];
        $handle = fopen($logFile, 'r');
        
        if ($handle) {
            // Read file backwards to get most recent entries
            fseek($handle, -1, SEEK_END);
            $pos = ftell($handle);
            $line = '';
            
            while ($pos >= 0 && count($lines) < $limit) {
                $char = fgetc($handle);
                if ($char === "\n" || $pos === 0) {
                    if (trim($line) !== '') {
                        $lines[] = strrev($line);
                    }
                    $line = '';
                } else {
                    $line .= $char;
                }
                fseek($handle, --$pos);
            }
            
            fclose($handle);
        }
        
        return array_reverse($lines);
    }
    
    private function getFailedLoginsByIp(int $hours): array {
        $failedLogins = [];
        
        if (!file_exists($this->authLogFile)) {
            return $failedLogins;
        }
        
        $cutoffTime = time() - ($hours * 3600);
        
        $handle = fopen($this->authLogFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $entry = json_decode($line, true);
                if ($entry && 
                    strtotime($entry['timestamp']) >= $cutoffTime &&
                    $entry['type'] === 'login_failed') {
                    
                    $ip = $entry['ip'];
                    $failedLogins[$ip] = ($failedLogins[$ip] ?? 0) + 1;
                }
            }
            fclose($handle);
        }
        
        return $failedLogins;
    }
    
    private function cleanLogFile(string $logFile, int $cutoffTime): void {
        $tempFile = $logFile . '.tmp';
        $inputHandle = fopen($logFile, 'r');
        $outputHandle = fopen($tempFile, 'w');
        
        if ($inputHandle && $outputHandle) {
            while (($line = fgets($inputHandle)) !== false) {
                $entry = json_decode($line, true);
                if ($entry && strtotime($entry['timestamp']) >= $cutoffTime) {
                    fwrite($outputHandle, $line);
                }
            }
            
            fclose($inputHandle);
            fclose($outputHandle);
            
            rename($tempFile, $logFile);
        }
    }
    
    private function sendSecurityAlert(string $activity, array $data): void {
        // In a production environment, this would send alerts via email, SMS, or webhook
        // For now, we'll just log it as a critical event
        error_log("SECURITY ALERT: {$activity} - " . json_encode($data));
        
        // You could integrate with services like:
        // - Email notifications
        // - Slack webhooks
        // - SMS alerts
        // - Security monitoring services
    }
}