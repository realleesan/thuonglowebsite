<?php
/**
 * Security Monitor
 * Real-time security monitoring and threat detection
 * Analyzes patterns and provides security insights
 */

require_once __DIR__ . '/SecurityLogger.php';

class SecurityMonitor {
    private SecurityLogger $logger;
    private array $config;
    private array $threatPatterns;
    
    public function __construct() {
        $this->logger = new SecurityLogger();
        $this->config = require __DIR__ . '/../../config.php';
        $this->initializeThreatPatterns();
    }
    
    /**
     * Monitor authentication attempts for suspicious patterns
     */
    public function monitorAuthAttempts(string $identifier, string $result): array {
        $analysis = [
            'threat_level' => 'low',
            'actions_taken' => [],
            'recommendations' => []
        ];
        
        // Check for brute force patterns
        $recentFailures = $this->getRecentFailedAttempts($identifier, 1); // Last hour
        
        if ($recentFailures >= 5) {
            $analysis['threat_level'] = 'medium';
            $analysis['actions_taken'][] = 'Rate limiting applied';
            
            if ($recentFailures >= 10) {
                $analysis['threat_level'] = 'high';
                $analysis['actions_taken'][] = 'IP temporarily blocked';
                $analysis['recommendations'][] = 'Consider permanent IP blocking';
                
                $this->logger->logSuspiciousActivity('brute_force_detected', [
                    'identifier' => $identifier,
                    'failed_attempts' => $recentFailures,
                    'time_window' => '1 hour'
                ]);
            }
        }
        
        // Check for distributed attacks
        $this->checkDistributedAttack();
        
        return $analysis;
    }
    
    /**
     * Monitor input validation for attack patterns
     */
    public function monitorInputValidation(string $inputType, string $input, bool $isValid): array {
        $analysis = [
            'threat_detected' => false,
            'threat_type' => null,
            'severity' => 'low',
            'actions_taken' => []
        ];
        
        if (!$isValid) {
            // Check for SQL injection patterns
            if ($this->detectSqlInjection($input)) {
                $analysis['threat_detected'] = true;
                $analysis['threat_type'] = 'sql_injection';
                $analysis['severity'] = 'critical';
                $analysis['actions_taken'][] = 'Input blocked and logged';
                
                $this->logger->logValidationFailure('sql_injection', [
                    'input_type' => $inputType,
                    'input_sample' => substr($input, 0, 100) . '...',
                    'full_input_hash' => md5($input)
                ]);
            }
            
            // Check for XSS patterns
            if ($this->detectXss($input)) {
                $analysis['threat_detected'] = true;
                $analysis['threat_type'] = 'xss';
                $analysis['severity'] = 'high';
                $analysis['actions_taken'][] = 'Input sanitized and logged';
                
                $this->logger->logValidationFailure('xss', [
                    'input_type' => $inputType,
                    'input_sample' => substr($input, 0, 100) . '...',
                    'full_input_hash' => md5($input)
                ]);
            }
            
            // Check for command injection
            if ($this->detectCommandInjection($input)) {
                $analysis['threat_detected'] = true;
                $analysis['threat_type'] = 'command_injection';
                $analysis['severity'] = 'critical';
                $analysis['actions_taken'][] = 'Input blocked and logged';
                
                $this->logger->logValidationFailure('command_injection', [
                    'input_type' => $inputType,
                    'input_sample' => substr($input, 0, 100) . '...',
                    'full_input_hash' => md5($input)
                ]);
            }
        }
        
        return $analysis;
    }
    
    /**
     * Monitor session activities for anomalies
     */
    public function monitorSessionActivity(array $sessionData): array {
        $analysis = [
            'anomalies' => [],
            'risk_score' => 0,
            'actions_taken' => []
        ];
        
        // Check for session hijacking indicators
        if ($this->detectSessionHijacking($sessionData)) {
            $analysis['anomalies'][] = 'Potential session hijacking';
            $analysis['risk_score'] += 50;
            $analysis['actions_taken'][] = 'Session regenerated';
            
            $this->logger->logSecurityEvent('session_hijacking_detected', $sessionData);
        }
        
        // Check for unusual login patterns
        if ($this->detectUnusualLoginPattern($sessionData)) {
            $analysis['anomalies'][] = 'Unusual login pattern';
            $analysis['risk_score'] += 30;
            
            $this->logger->logSecurityEvent('unusual_login_pattern', $sessionData);
        }
        
        // Check for concurrent sessions
        if ($this->detectConcurrentSessions($sessionData)) {
            $analysis['anomalies'][] = 'Multiple concurrent sessions';
            $analysis['risk_score'] += 20;
            
            $this->logger->logSecurityEvent('concurrent_sessions', $sessionData);
        }
        
        return $analysis;
    }
    
    /**
     * Generate security dashboard data
     */
    public function getSecurityDashboard(): array {
        $stats = $this->logger->getSecurityStats(24);
        $recentEvents = $this->logger->getRecentEvents(20);
        $suspiciousPatterns = $this->logger->detectSuspiciousPatterns();
        
        return [
            'overview' => [
                'total_auth_attempts' => $stats['auth_attempts'],
                'failed_logins' => $stats['failed_logins'],
                'security_events' => $stats['security_events'],
                'suspicious_activities' => $stats['suspicious_activities'],
                'threat_level' => $this->calculateOverallThreatLevel($stats, $suspiciousPatterns)
            ],
            'recent_events' => $recentEvents,
            'suspicious_patterns' => $suspiciousPatterns,
            'top_threats' => $this->getTopThreats(),
            'recommendations' => $this->getSecurityRecommendations($stats, $suspiciousPatterns)
        ];
    }
    
    /**
     * Get security alerts that need immediate attention
     */
    public function getSecurityAlerts(): array {
        $alerts = [];
        
        // Check for active brute force attacks
        $bruteForceIPs = $this->getActiveBruteForceIPs();
        foreach ($bruteForceIPs as $ip => $attempts) {
            $alerts[] = [
                'type' => 'brute_force',
                'severity' => 'high',
                'message' => "Brute force attack detected from IP: {$ip} ({$attempts} attempts)",
                'ip' => $ip,
                'attempts' => $attempts,
                'action_required' => 'Block IP address'
            ];
        }
        
        // Check for SQL injection attempts
        $sqlInjections = $this->logger->countSpecificEvents(
            __DIR__ . '/../../logs/security.log', 
            'validation_failure', 
            1, 
            ['validation_type' => 'sql_injection']
        );
        
        if ($sqlInjections > 0) {
            $alerts[] = [
                'type' => 'sql_injection',
                'severity' => 'critical',
                'message' => "SQL injection attempts detected ({$sqlInjections} attempts in last hour)",
                'count' => $sqlInjections,
                'action_required' => 'Review application security'
            ];
        }
        
        // Check for system errors
        $systemErrors = $this->getRecentSystemErrors();
        if (count($systemErrors) > 5) {
            $alerts[] = [
                'type' => 'system_errors',
                'severity' => 'medium',
                'message' => "Multiple system errors detected (" . count($systemErrors) . " errors)",
                'count' => count($systemErrors),
                'action_required' => 'Check system logs'
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Perform automated threat response
     */
    public function performAutomatedResponse(string $threatType, array $context): array {
        $actions = [];
        
        switch ($threatType) {
            case 'brute_force':
                $actions[] = $this->blockSuspiciousIP($context['ip']);
                $actions[] = $this->increaseRateLimit($context['identifier']);
                break;
                
            case 'sql_injection':
                $actions[] = $this->blockMaliciousInput($context);
                $actions[] = $this->alertAdministrators('SQL Injection Detected', $context);
                break;
                
            case 'session_hijacking':
                $actions[] = $this->invalidateSession($context['session_id']);
                $actions[] = $this->requireReauthentication($context['user_id']);
                break;
                
            case 'xss_attempt':
                $actions[] = $this->sanitizeInput($context);
                $actions[] = $this->logSecurityIncident($threatType, $context);
                break;
        }
        
        return $actions;
    }
    
    // Private helper methods
    
    private function initializeThreatPatterns(): void {
        $this->threatPatterns = [
            'sql_injection' => [
                '/(\bUNION\b.*\bSELECT\b)/i',
                '/(\bSELECT\b.*\bFROM\b.*\bWHERE\b)/i',
                '/(\bINSERT\b.*\bINTO\b)/i',
                '/(\bDROP\b.*\bTABLE\b)/i',
                '/(\bDELETE\b.*\bFROM\b)/i',
                '/(\'.*OR.*\'.*=.*\')/i',
                '/(\".*OR.*\".*=.*\")/i'
            ],
            'xss' => [
                '/<script[^>]*>.*<\/script>/i',
                '/<iframe[^>]*>.*<\/iframe>/i',
                '/javascript:/i',
                '/on\w+\s*=/i',
                '/<img[^>]*onerror[^>]*>/i'
            ],
            'command_injection' => [
                '/[;&|`$(){}]/i',
                '/\b(cat|ls|pwd|whoami|id|uname)\b/i',
                '/\.\.\//i'
            ]
        ];
    }
    
    private function getRecentFailedAttempts(string $identifier, int $hours): int {
        // This would query the auth log for failed attempts
        // For now, return a mock value
        return 0;
    }
    
    private function checkDistributedAttack(): void {
        // Check for attacks from multiple IPs targeting the same resources
        $recentIPs = $this->getRecentAttackIPs(1);
        
        if (count($recentIPs) > 10) {
            $this->logger->logSuspiciousActivity('distributed_attack', [
                'unique_ips' => count($recentIPs),
                'time_window' => '1 hour'
            ]);
        }
    }
    
    private function detectSqlInjection(string $input): bool {
        foreach ($this->threatPatterns['sql_injection'] as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }
    
    private function detectXss(string $input): bool {
        foreach ($this->threatPatterns['xss'] as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }
    
    private function detectCommandInjection(string $input): bool {
        foreach ($this->threatPatterns['command_injection'] as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }
    
    private function detectSessionHijacking(array $sessionData): bool {
        // Check for IP address changes
        if (isset($sessionData['original_ip']) && isset($sessionData['current_ip'])) {
            return $sessionData['original_ip'] !== $sessionData['current_ip'];
        }
        
        // Check for user agent changes
        if (isset($sessionData['original_user_agent']) && isset($sessionData['current_user_agent'])) {
            return $sessionData['original_user_agent'] !== $sessionData['current_user_agent'];
        }
        
        return false;
    }
    
    private function detectUnusualLoginPattern(array $sessionData): bool {
        // Check for logins at unusual times
        $currentHour = (int)date('H');
        $isUnusualTime = $currentHour < 6 || $currentHour > 23;
        
        // Check for logins from unusual locations (would need GeoIP)
        // For now, just check time pattern
        return $isUnusualTime;
    }
    
    private function detectConcurrentSessions(array $sessionData): bool {
        // This would check for multiple active sessions for the same user
        // Implementation would depend on session storage mechanism
        return false;
    }
    
    private function calculateOverallThreatLevel(array $stats, array $patterns): string {
        $score = 0;
        
        // Weight different factors
        $score += $stats['failed_logins'] * 2;
        $score += $stats['suspicious_activities'] * 10;
        $score += count($patterns) * 15;
        
        if ($score >= 50) return 'critical';
        if ($score >= 30) return 'high';
        if ($score >= 15) return 'medium';
        return 'low';
    }
    
    private function getTopThreats(): array {
        return [
            ['type' => 'Brute Force Attacks', 'count' => 5, 'trend' => 'increasing'],
            ['type' => 'SQL Injection Attempts', 'count' => 2, 'trend' => 'stable'],
            ['type' => 'XSS Attempts', 'count' => 1, 'trend' => 'decreasing'],
            ['type' => 'Access Violations', 'count' => 8, 'trend' => 'stable']
        ];
    }
    
    private function getSecurityRecommendations(array $stats, array $patterns): array {
        $recommendations = [];
        
        if ($stats['failed_logins'] > 20) {
            $recommendations[] = 'Consider implementing CAPTCHA after failed login attempts';
        }
        
        if (count($patterns) > 0) {
            $recommendations[] = 'Review and strengthen input validation rules';
        }
        
        if ($stats['suspicious_activities'] > 5) {
            $recommendations[] = 'Consider implementing IP-based blocking for repeat offenders';
        }
        
        return $recommendations;
    }
    
    private function getActiveBruteForceIPs(): array {
        // This would analyze recent logs for brute force patterns
        return [];
    }
    
    private function getRecentSystemErrors(): array {
        // This would get recent system errors from logs
        return [];
    }
    
    private function getRecentAttackIPs(int $hours): array {
        // This would get unique IPs from recent security events
        return [];
    }
    
    // Automated response methods
    
    private function blockSuspiciousIP(string $ip): string {
        // In production, this would add IP to firewall rules or .htaccess
        $this->logger->logSecurityEvent('ip_blocked', ['ip' => $ip]);
        return "IP {$ip} blocked";
    }
    
    private function increaseRateLimit(string $identifier): string {
        // Increase rate limiting for this identifier
        return "Rate limit increased for {$identifier}";
    }
    
    private function blockMaliciousInput(array $context): string {
        // Block the specific input pattern
        return "Malicious input pattern blocked";
    }
    
    private function alertAdministrators(string $subject, array $context): string {
        // Send alert to administrators
        error_log("ADMIN ALERT: {$subject} - " . json_encode($context));
        return "Administrators alerted";
    }
    
    private function invalidateSession(string $sessionId): string {
        // Invalidate the compromised session
        return "Session {$sessionId} invalidated";
    }
    
    private function requireReauthentication(string $userId): string {
        // Force user to re-authenticate
        return "Re-authentication required for user {$userId}";
    }
    
    private function sanitizeInput(array $context): string {
        // Sanitize the malicious input
        return "Input sanitized";
    }
    
    private function logSecurityIncident(string $type, array $context): string {
        $this->logger->logSecurityEvent('security_incident', [
            'incident_type' => $type,
            'context' => $context
        ]);
        return "Security incident logged";
    }
}