<?php
/**
 * Security Headers Service
 * Implements security headers and cookie settings
 * Requirements: 6.5, 7.4
 */

class SecurityHeaders {
    private array $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../../config.php';
    }
    
    /**
     * Set secure session configuration
     * Requirement: 6.5
     */
    public function configureSecureSessions(): void {
        // Set secure session parameters
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $this->isHttps() ? '1' : '0');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_lifetime', '0'); // Session cookies only
        ini_set('session.gc_maxlifetime', '3600'); // 1 hour
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '100');
        
        // Regenerate session ID periodically
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    }
    
    /**
     * Set security headers
     * Requirement: 6.5
     */
    public function setSecurityHeaders(): void {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = $this->buildContentSecurityPolicy();
        header("Content-Security-Policy: {$csp}");
        
        // Strict Transport Security (only for HTTPS)
        if ($this->isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        
        // Remove server information
        header_remove('X-Powered-By');
        header_remove('Server');
    }
    
    /**
     * Set secure cookie defaults
     */
    public function setSecureCookieDefaults(): void {
        // Set default cookie parameters
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => $this->getDomain(),
            'secure' => $this->isHttps(),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    /**
     * Create secure cookie
     */
    public function setSecureCookie(string $name, string $value, int $expire = 0, array $options = []): bool {
        $defaultOptions = [
            'expires' => $expire,
            'path' => '/',
            'domain' => $this->getDomain(),
            'secure' => $this->isHttps(),
            'httponly' => true,
            'samesite' => 'Strict'
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        return setcookie($name, $value, $options);
    }
    
    /**
     * Enhanced CSRF token generation
     * Requirement: 7.4
     */
    public function generateCsrfToken(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // Generate cryptographically secure token
        $token = bin2hex(random_bytes(32));
        
        // Store token with timestamp
        $_SESSION['csrf_tokens'][$token] = [
            'created' => time(),
            'used' => false
        ];
        
        // Clean old tokens
        $this->cleanExpiredCsrfTokens();
        
        return $token;
    }
    
    /**
     * Enhanced CSRF token verification
     * Requirement: 7.4
     */
    public function verifyCsrfToken(string $token, bool $singleUse = true): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        if (!isset($_SESSION['csrf_tokens'][$token])) {
            return false;
        }
        
        $tokenData = $_SESSION['csrf_tokens'][$token];
        
        // Check if token is expired (1 hour)
        if (time() - $tokenData['created'] > 3600) {
            unset($_SESSION['csrf_tokens'][$token]);
            return false;
        }
        
        // Check if token was already used (for single-use tokens)
        if ($singleUse && $tokenData['used']) {
            return false;
        }
        
        // Mark token as used
        if ($singleUse) {
            $_SESSION['csrf_tokens'][$token]['used'] = true;
        }
        
        return true;
    }
    
    /**
     * Get CSRF token for forms
     */
    public function getCsrfTokenForForm(): string {
        if (!isset($_SESSION['form_csrf_token']) || 
            !isset($_SESSION['form_csrf_created']) ||
            time() - $_SESSION['form_csrf_created'] > 1800) { // 30 minutes
            
            $_SESSION['form_csrf_token'] = $this->generateCsrfToken();
            $_SESSION['form_csrf_created'] = time();
        }
        
        return $_SESSION['form_csrf_token'];
    }
    
    /**
     * Verify CSRF token from form
     */
    public function verifyCsrfTokenFromForm(string $token): bool {
        return isset($_SESSION['form_csrf_token']) && 
               hash_equals($_SESSION['form_csrf_token'], $token) &&
               $this->verifyCsrfToken($token, false);
    }
    
    /**
     * Set cache control headers
     */
    public function setCacheControlHeaders(string $type = 'no-cache'): void {
        switch ($type) {
            case 'no-cache':
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                break;
                
            case 'private':
                header('Cache-Control: private, max-age=0');
                break;
                
            case 'public':
                header('Cache-Control: public, max-age=3600');
                break;
        }
    }
    
    /**
     * Prevent information disclosure
     */
    public function preventInformationDisclosure(): void {
        // Disable error display in production
        if ($this->isProduction()) {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            error_reporting(0);
        }
        
        // Remove PHP version from headers
        header_remove('X-Powered-By');
        
        // Set generic error pages
        $this->setCustomErrorPages();
    }
    
    /**
     * Initialize all security measures
     */
    public function initializeSecurityMeasures(): void {
        $this->configureSecureSessions();
        $this->setSecurityHeaders();
        $this->setSecureCookieDefaults();
        $this->setCacheControlHeaders('no-cache');
        $this->preventInformationDisclosure();
    }
    
    // Private helper methods
    
    private function buildContentSecurityPolicy(): string {
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'", // Allow inline scripts for now
            "style-src 'self' 'unsafe-inline'",  // Allow inline styles
            "img-src 'self' data: https:",
            "font-src 'self'",
            "connect-src 'self'",
            "media-src 'self'",
            "object-src 'none'",
            "child-src 'none'",
            "worker-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
            "manifest-src 'self'"
        ];
        
        return implode('; ', $csp);
    }
    
    private function isHttps(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    private function getDomain(): string {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Remove port if present
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host)[0];
        }
        
        return $host;
    }
    
    private function isProduction(): bool {
        return ($this->config['environment'] ?? 'development') === 'production';
    }
    
    private function cleanExpiredCsrfTokens(): void {
        if (!isset($_SESSION['csrf_tokens'])) {
            return;
        }
        
        $currentTime = time();
        foreach ($_SESSION['csrf_tokens'] as $token => $data) {
            if ($currentTime - $data['created'] > 3600) { // 1 hour
                unset($_SESSION['csrf_tokens'][$token]);
            }
        }
    }
    
    private function setCustomErrorPages(): void {
        // Set custom error handlers to prevent information disclosure
        set_error_handler(function($severity, $message, $file, $line) {
            if ($this->isProduction()) {
                // Log error but don't display
                error_log("Error: {$message} in {$file} on line {$line}");
                return true;
            }
            return false;
        });
        
        set_exception_handler(function($exception) {
            if ($this->isProduction()) {
                // Log exception but show generic error
                error_log("Exception: " . $exception->getMessage());
                http_response_code(500);
                echo "An error occurred. Please try again later.";
            } else {
                throw $exception;
            }
        });
    }
    
    /**
     * Generate secure random string
     */
    public function generateSecureRandom(int $length = 32): string {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Secure string comparison
     */
    public function secureCompare(string $a, string $b): bool {
        return hash_equals($a, $b);
    }
    
    /**
     * Rate limiting headers
     */
    public function setRateLimitHeaders(int $limit, int $remaining, int $resetTime): void {
        header("X-RateLimit-Limit: {$limit}");
        header("X-RateLimit-Remaining: {$remaining}");
        header("X-RateLimit-Reset: {$resetTime}");
        
        if ($remaining <= 0) {
            header("Retry-After: " . ($resetTime - time()));
        }
    }
}