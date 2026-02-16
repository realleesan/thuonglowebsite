<?php
/**
 * SpamPreventionService - Service ngăn chặn spam và rate limiting
 * Triển khai logic ngăn chặn spam và rate limiting cho agent registration
 * Requirements: 4.1, 4.2, 4.3, 4.4
 */

require_once __DIR__ . '/ServiceInterface.php';
require_once __DIR__ . '/../models/UsersModel.php';

class SpamPreventionService implements ServiceInterface {
    private UsersModel $usersModel;
    private array $rateLimitConfig;
    
    public function __construct() {
        $this->usersModel = new UsersModel();
        
        // Rate limiting configuration
        $this->rateLimitConfig = [
            'max_requests_per_hour' => 3,
            'max_requests_per_day' => 5,
            'lockout_duration_minutes' => 60,
            'session_key_prefix' => 'agent_registration_attempts_'
        ];
    }
    
    /**
     * ServiceInterface implementation
     */
    public function getData(string $method, array $params = []): array {
        try {
            switch ($method) {
                case 'checkRateLimit':
                    return ['is_rate_limited' => $this->isRateLimited($params['user_id'] ?? null)];
                case 'checkExistingRequest':
                    return ['has_existing_request' => $this->hasExistingPendingRequest($params['user_id'] ?? null)];
                case 'recordSubmission':
                    $this->recordSubmission($params['user_id'] ?? null);
                    return ['success' => true];
                default:
                    throw new Exception("Unknown method: $method");
            }
        } catch (Exception $e) {
            return $this->handleError($e, ['method' => $method, 'params' => $params]);
        }
    }
    
    public function getModel(string $modelName) {
        if ($modelName === 'UsersModel') {
            return $this->usersModel;
        }
        return null;
    }
    
    public function handleError(\Exception $e, array $context = []): array {
        error_log("SpamPreventionService Error: " . $e->getMessage() . " Context: " . json_encode($context));
        return [
            'error' => true,
            'message' => 'Có lỗi xảy ra khi kiểm tra spam prevention',
            'is_rate_limited' => false,
            'has_existing_request' => false
        ];
    }
    
    /**
     * Kiểm tra xem user có bị rate limit không
     * Requirements: 4.3
     */
    public function isRateLimited(?int $userId): bool {
        if (!$userId) {
            // For anonymous users, use IP-based rate limiting
            return $this->isIpRateLimited();
        }
        
        // Check database for user-based rate limiting
        return $this->isUserRateLimited($userId);
    }
    
    /**
     * Ghi lại submission attempt
     * Requirements: 4.3
     */
    public function recordSubmission(?int $userId): void {
        $timestamp = date('Y-m-d H:i:s');
        
        if ($userId) {
            // Record in session for user-based tracking
            $this->recordUserSubmission($userId, $timestamp);
        } else {
            // Record in session for IP-based tracking
            $this->recordIpSubmission($timestamp);
        }
    }
    
    /**
     * Kiểm tra xem user có pending request không
     * Requirements: 4.1, 4.2
     */
    public function hasExistingPendingRequest(?int $userId): bool {
        if (!$userId) {
            return false;
        }
        
        try {
            $user = $this->usersModel->findById($userId);
            if (!$user) {
                return false;
            }
            
            // Check if user has pending agent request
            return isset($user['agent_request_status']) && 
                   $user['agent_request_status'] === 'pending';
        } catch (Exception $e) {
            error_log("Error checking existing request: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kiểm tra rate limit dựa trên IP
     */
    private function isIpRateLimited(): bool {
        $clientIp = $this->getClientIp();
        $sessionKey = $this->rateLimitConfig['session_key_prefix'] . 'ip_' . md5($clientIp);
        
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $attempts = $_SESSION[$sessionKey] ?? [];
        $now = time();
        
        // Clean old attempts (older than 1 day)
        $attempts = array_filter($attempts, function($timestamp) use ($now) {
            return ($now - $timestamp) < 86400; // 24 hours
        });
        
        // Check hourly limit
        $hourlyAttempts = array_filter($attempts, function($timestamp) use ($now) {
            return ($now - $timestamp) < 3600; // 1 hour
        });
        
        if (count($hourlyAttempts) >= $this->rateLimitConfig['max_requests_per_hour']) {
            return true;
        }
        
        // Check daily limit
        if (count($attempts) >= $this->rateLimitConfig['max_requests_per_day']) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Kiểm tra rate limit dựa trên user ID
     */
    private function isUserRateLimited(int $userId): bool {
        $sessionKey = $this->rateLimitConfig['session_key_prefix'] . 'user_' . $userId;
        
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $attempts = $_SESSION[$sessionKey] ?? [];
        $now = time();
        
        // Clean old attempts
        $attempts = array_filter($attempts, function($timestamp) use ($now) {
            return ($now - $timestamp) < 86400; // 24 hours
        });
        
        // Check hourly limit
        $hourlyAttempts = array_filter($attempts, function($timestamp) use ($now) {
            return ($now - $timestamp) < 3600; // 1 hour
        });
        
        if (count($hourlyAttempts) >= $this->rateLimitConfig['max_requests_per_hour']) {
            return true;
        }
        
        // Check daily limit
        if (count($attempts) >= $this->rateLimitConfig['max_requests_per_day']) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Ghi lại user submission
     */
    private function recordUserSubmission(int $userId, string $timestamp): void {
        $sessionKey = $this->rateLimitConfig['session_key_prefix'] . 'user_' . $userId;
        
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = [];
        }
        
        $_SESSION[$sessionKey][] = time();
    }
    
    /**
     * Ghi lại IP submission
     */
    private function recordIpSubmission(string $timestamp): void {
        $clientIp = $this->getClientIp();
        $sessionKey = $this->rateLimitConfig['session_key_prefix'] . 'ip_' . md5($clientIp);
        
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = [];
        }
        
        $_SESSION[$sessionKey][] = time();
    }
    
    /**
     * Lấy IP của client
     */
    private function getClientIp(): string {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Lấy thông tin rate limit status cho user
     */
    public function getRateLimitStatus(?int $userId): array {
        $sessionKey = $userId ? 
            $this->rateLimitConfig['session_key_prefix'] . 'user_' . $userId :
            $this->rateLimitConfig['session_key_prefix'] . 'ip_' . md5($this->getClientIp());
        
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $attempts = $_SESSION[$sessionKey] ?? [];
        $now = time();
        
        // Clean old attempts
        $attempts = array_filter($attempts, function($timestamp) use ($now) {
            return ($now - $timestamp) < 86400;
        });
        
        $hourlyAttempts = array_filter($attempts, function($timestamp) use ($now) {
            return ($now - $timestamp) < 3600;
        });
        
        return [
            'hourly_attempts' => count($hourlyAttempts),
            'daily_attempts' => count($attempts),
            'max_hourly' => $this->rateLimitConfig['max_requests_per_hour'],
            'max_daily' => $this->rateLimitConfig['max_requests_per_day'],
            'is_rate_limited' => $this->isRateLimited($userId),
            'reset_time' => $now + 3600 // Next hour
        ];
    }
    
    /**
     * Reset rate limit cho user (admin function)
     */
    public function resetRateLimit(?int $userId): bool {
        $sessionKey = $userId ? 
            $this->rateLimitConfig['session_key_prefix'] . 'user_' . $userId :
            $this->rateLimitConfig['session_key_prefix'] . 'ip_' . md5($this->getClientIp());
        
        if (!isset($_SESSION)) {
            session_start();
        }
        
        unset($_SESSION[$sessionKey]);
        return true;
    }
}