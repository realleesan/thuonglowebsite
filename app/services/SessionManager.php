<?php
/**
 * SessionManager Service
 * Handles secure session management for authentication
 * Implements secure session creation and destruction
 * Requirements: 2.3, 4.1, 4.3, 6.1, 6.2, 6.4
 */

class SessionManager {
    private string $sessionName;
    private int $sessionLifetime;
    private int $sessionTimeout;
    private bool $isStarted = false;
    private ?PasswordHasher $passwordHasher = null;
    
    public function __construct() {
        // Load configuration
        $config = include 'config.php';
        
        $this->sessionName = $config['security']['session_name'] ?? 'THUONGLO_AUTH_SESSION';
        $this->sessionLifetime = $config['security']['session_lifetime'] ?? 3600; // 1 hour
        $this->sessionTimeout = $config['security']['session_timeout'] ?? 1800; // 30 minutes
        
        // Initialize security headers
        require_once __DIR__ . '/SecurityHeaders.php';
        $securityHeaders = new SecurityHeaders();
        $securityHeaders->initializeSecurityMeasures();
        
        // Configure secure session settings
        $this->configureSession();
    }
    
    /**
     * Configure secure session parameters
     * Implements cryptographically secure session settings per Requirement 6.1
     */
    private function configureSession(): void {
        // Only configure if session hasn't started
        if (session_status() === PHP_SESSION_NONE) {
            // Basic session configuration
            ini_set('session.name', $this->sessionName);
            ini_set('session.cookie_lifetime', $this->sessionLifetime);
            ini_set('session.gc_maxlifetime', $this->sessionLifetime);
            
            // Security settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', $this->isHttps() ? 1 : 0);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_trans_sid', 0);
            
            // Entropy settings for secure session ID generation
            ini_set('session.entropy_length', 32);
            ini_set('session.hash_function', 'sha256');
            ini_set('session.hash_bits_per_character', 6);
            
            // Session storage settings
            ini_set('session.save_handler', 'files');
            ini_set('session.serialize_handler', 'php_serialize');
        }
    }
    
    /**
     * Check if connection is HTTPS
     */
    private function isHttps(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Get PasswordHasher instance
     */
    private function getPasswordHasher(): PasswordHasher {
        if ($this->passwordHasher === null) {
            $this->passwordHasher = new PasswordHasher();
        }
        return $this->passwordHasher;
    }
    
    /**
     * Start session if not already started
     * Implements secure session initialization per Requirement 6.1
     */
    public function start(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            // Ensure session save path exists and is writable
            $savePath = session_save_path();
            if (empty($savePath)) {
                $savePath = sys_get_temp_dir();
                session_save_path($savePath);
            }
            
            if (!is_dir($savePath) || !is_writable($savePath)) {
                // Try to create or use alternative path
                $altPath = __DIR__ . '/../../tmp/sessions';
                if (!is_dir($altPath)) {
                    mkdir($altPath, 0755, true);
                }
                if (is_writable($altPath)) {
                    session_save_path($altPath);
                }
            }
            
            $this->isStarted = @session_start();
            
            if ($this->isStarted) {
                // Initialize session security data
                $this->initializeSessionSecurity();
                
                // Validate session integrity
                if (!$this->validateSessionIntegrity()) {
                    $this->destroySession();
                    return false;
                }
            }
            
            return $this->isStarted;
        }
        
        $this->isStarted = true;
        return true;
    }
    
    /**
     * Initialize session security data
     */
    private function initializeSessionSecurity(): void {
        // Initialize CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $this->getPasswordHasher()->generateSecureRandomString(32);
        }
        
        // Initialize session fingerprint for security
        if (!isset($_SESSION['session_fingerprint'])) {
            $_SESSION['session_fingerprint'] = $this->generateSessionFingerprint();
        }
        
        // Initialize session creation time
        if (!isset($_SESSION['session_created'])) {
            $_SESSION['session_created'] = time();
        }
    }
    
    /**
     * Generate session fingerprint for security validation
     */
    private function generateSessionFingerprint(): string {
        $data = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
        ];
        
        return hash('sha256', implode('|', $data));
    }
    
    /**
     * Validate session integrity
     */
    private function validateSessionIntegrity(): bool {
        // Check session fingerprint
        if (isset($_SESSION['session_fingerprint'])) {
            $currentFingerprint = $this->generateSessionFingerprint();
            if (!hash_equals($_SESSION['session_fingerprint'], $currentFingerprint)) {
                return false;
            }
        }
        
        // Check session age (prevent session fixation)
        if (isset($_SESSION['session_created'])) {
            $sessionAge = time() - $_SESSION['session_created'];
            if ($sessionAge > $this->sessionLifetime) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Create session for authenticated user
     * Implements secure session creation per Requirements 2.3, 6.1
     */
    public function createSession(array $user): string {
        $this->start();
        
        // Clear any existing session data
        $this->clearSessionData();
        
        // Set user session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['username'] = $user['username'] ?? '';
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['is_authenticated'] = true;
        
        // Agent registration status
        $_SESSION['agent_request_status'] = $user['agent_request_status'] ?? 'none';
        
        // Security data
        $_SESSION['session_token'] = $this->getPasswordHasher()->generateSessionToken();
        $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['login_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Regenerate session ID for security (prevents session fixation)
        $this->regenerateId();
        
        return session_id();
    }
    
    /**
     * Clear session data while preserving security tokens
     */
    private function clearSessionData(): void {
        $preserveKeys = ['csrf_token', 'session_fingerprint', 'session_created'];
        $preserved = [];
        
        foreach ($preserveKeys as $key) {
            if (isset($_SESSION[$key])) {
                $preserved[$key] = $_SESSION[$key];
            }
        }
        
        $_SESSION = $preserved;
    }
    
    /**
     * Destroy current session completely
     * Implements complete session destruction per Requirements 4.1, 4.3
     */
    public function destroySession(): bool {
        $this->start();
        
        // Store CSRF token before clearing
        $csrfToken = $_SESSION['csrf_token'] ?? null;
        
        // Clear all session data
        $_SESSION = [];
        
        // Restore CSRF token for new session
        if ($csrfToken) {
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            // Generate new CSRF token
            $_SESSION['csrf_token'] = $this->getPasswordHasher()->generateSecureRandomString(32);
        }
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Reset internal state but keep session active
        $this->isStarted = true;
        
        return true;
    }
    
    /**
     * Regenerate session ID for security
     * Implements session ID regeneration per Requirement 6.4
     */
    public function regenerateId(bool $deleteOldSession = true): bool {
        if ($this->isStarted) {
            $result = session_regenerate_id($deleteOldSession);
            
            // Update session creation time after regeneration
            if ($result) {
                $_SESSION['session_created'] = time();
            }
            
            return $result;
        }
        return false;
    }
    
    /**
     * Check if session is valid and not expired
     * Implements automatic session timeout per Requirement 6.2
     */
    public function isValid(): bool {
        $this->start();
        
        // Check if user is authenticated
        if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
            return false;
        }
        
        // Check session integrity
        if (!$this->validateSessionIntegrity()) {
            $this->destroySession();
            return false;
        }
        
        // Check timeout
        if (!$this->checkTimeout()) {
            $this->destroySession();
            return false;
        }
        
        return true;
    }
    
    /**
     * Check session timeout
     * Implements automatic session timeout handling per Requirement 6.2
     */
    public function checkTimeout(): bool {
        $this->start();
        
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }
        
        $inactive = time() - $_SESSION['last_activity'];
        
        // Check inactivity timeout
        if ($inactive >= $this->sessionTimeout) {
            return false;
        }
        
        // Check absolute session lifetime
        if (isset($_SESSION['login_time'])) {
            $sessionAge = time() - $_SESSION['login_time'];
            if ($sessionAge >= $this->sessionLifetime) {
                return false;
            }
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Force session timeout (for security purposes)
     */
    public function forceTimeout(): void {
        $this->start();
        $_SESSION['last_activity'] = time() - $this->sessionTimeout - 1;
    }
    
    /**
     * Extend session timeout
     */
    public function extendSession(): void {
        $this->start();
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Get session value
     */
    public function get(string $key, $default = null) {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Set session value
     */
    public function set(string $key, $value): void {
        $this->start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Remove session value
     */
    public function remove(string $key): void {
        $this->start();
        unset($_SESSION[$key]);
    }
    
    /**
     * Get current authenticated user data
     */
    public function getCurrentUser(): ?array {
        if (!$this->isValid()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role' => $_SESSION['user_role'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'login_time' => $_SESSION['login_time'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'session_token' => $_SESSION['session_token'] ?? null,
        ];
    }
    
    /**
     * Set current user data
     * Regenerates session ID when critical data changes per Requirement 6.4
     */
    public function setCurrentUser(array $user): void {
        $this->start();
        
        $oldEmail = $_SESSION['user_email'] ?? null;
        $oldRole = $_SESSION['user_role'] ?? null;
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID if critical data changed
        if ($oldEmail !== $user['email'] || $oldRole !== $user['role']) {
            $this->regenerateId();
        }
    }
    
    /**
     * Update user role (with session regeneration for security)
     */
    public function updateUserRole(string $newRole): void {
        $this->start();
        
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== $newRole) {
            $_SESSION['user_role'] = $newRole;
            $this->regenerateId(); // Security: regenerate on role change
        }
    }
    
    /**
     * Get CSRF token
     */
    public function getCsrfToken(): string {
        $this->start();
        
        // If no CSRF token exists, create one
        if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $this->getPasswordHasher()->generateSecureRandomString(32);
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token (timing-safe comparison)
     */
    public function verifyCsrfToken(string $token): bool {
        $storedToken = $this->getCsrfToken();
        return !empty($storedToken) && hash_equals($storedToken, $token);
    }
    
    /**
     * Regenerate CSRF token
     */
    public function regenerateCsrfToken(): string {
        $this->start();
        $_SESSION['csrf_token'] = $this->getPasswordHasher()->generateSecureRandomString(32);
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get session statistics
     */
    public function getSessionStats(): array {
        $this->start();
        
        return [
            'session_id' => session_id(),
            'is_authenticated' => $_SESSION['is_authenticated'] ?? false,
            'user_id' => $_SESSION['user_id'] ?? null,
            'login_time' => $_SESSION['login_time'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'session_created' => $_SESSION['session_created'] ?? null,
            'time_remaining' => $this->getTimeRemaining(),
            'is_secure' => $this->isHttps(),
        ];
    }
    
    /**
     * Get remaining session time
     */
    public function getTimeRemaining(): int {
        $this->start();
        
        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }
        
        $elapsed = time() - $_SESSION['last_activity'];
        $remaining = $this->sessionTimeout - $elapsed;
        
        return max(0, $remaining);
    }
    
    /**
     * Check if session needs renewal (close to timeout)
     */
    public function needsRenewal(int $warningThreshold = 300): bool {
        return $this->getTimeRemaining() <= $warningThreshold;
    }
    
    /**
     * Invalidate all sessions for a user (useful for password changes)
     */
    public function invalidateUserSessions(int $userId): void {
        // This would require a session storage mechanism that tracks user sessions
        // For now, we'll just invalidate the current session if it matches
        $this->start();
        
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $this->destroySession();
        }
    }
    
    /**
     * Set session flash message
     */
    public function setFlash(string $key, string $message): void {
        $this->start();
        $_SESSION['flash'][$key] = $message;
    }
    
    /**
     * Get and remove flash message
     */
    public function getFlash(string $key): ?string {
        $this->start();
        
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        
        return null;
    }
    
    /**
     * Check if user has flash messages
     */
    public function hasFlash(string $key = null): bool {
        $this->start();
        
        if ($key === null) {
            return !empty($_SESSION['flash']);
        }
        
        return isset($_SESSION['flash'][$key]);
    }
}