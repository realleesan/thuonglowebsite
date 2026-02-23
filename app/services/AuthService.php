<?php
/**
 * AuthService - Main Authentication Service
 * Implements business logic for authentication system
 * Requirements: 1.1, 1.2, 2.1, 2.2, 3.4, 3.5, 8.3
 */

require_once __DIR__ . '/ServiceInterface.php';
require_once __DIR__ . '/PasswordHasher.php';
require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/InputValidator.php';
require_once __DIR__ . '/RoleManager.php';
require_once __DIR__ . '/AuthErrorHandler.php';
require_once __DIR__ . '/SecurityLogger.php';
require_once __DIR__ . '/SecurityMonitor.php';
require_once __DIR__ . '/../models/UsersModel.php';

class AuthService implements ServiceInterface {
    private UsersModel $usersModel;
    private SessionManager $sessionManager;
    private PasswordHasher $passwordHasher;
    private RoleManager $roleManager;
    private InputValidator $validator;
    private AuthErrorHandler $errorHandler;
    private SecurityLogger $securityLogger;
    private SecurityMonitor $securityMonitor;
    
    public function __construct() {
        $this->usersModel = new UsersModel();
        $this->sessionManager = new SessionManager();
        $this->passwordHasher = new PasswordHasher();
        $this->roleManager = new RoleManager();
        $this->validator = new InputValidator();
        $this->errorHandler = new AuthErrorHandler();
        $this->securityLogger = new SecurityLogger();
        $this->securityMonitor = new SecurityMonitor();
    }
    
    /**
     * ServiceInterface implementation
     */
    public function getData(string $method, array $params = []): array {
        try {
            switch ($method) {
                case 'authenticate':
                    return $this->authenticate($params['login'] ?? '', $params['password'] ?? '');
                case 'register':
                    return $this->register($params);
                case 'logout':
                    return ['success' => $this->logout()];
                case 'getCurrentUser':
                    return ['user' => $this->getCurrentUser()];
                case 'initiatePasswordReset':
                    return $this->initiatePasswordReset($params['email'] ?? '');
                case 'resetPassword':
                    return $this->resetPassword($params['token'] ?? '', $params['password'] ?? '');
                default:
                    throw new Exception("Method $method not found");
            }
        } catch (Exception $e) {
            return $this->handleError($e, ['method' => $method, 'params' => $params]);
        }
    }
    
    public function getModel(string $modelName) {
        switch ($modelName) {
            case 'UsersModel':
                return $this->usersModel;
            default:
                return null;
        }
    }
    
    public function handleError(\Exception $e, array $context = []): array {
        return $this->errorHandler->handleSystemError($e, $context);
    }
    
    // ========== Main Authentication Methods ==========
    
    /**
     * Authenticate user with credentials
     * Requirements: 2.1, 2.2
     */
    public function authenticate(string $login, string $password): array {
        try {
            // Log authentication attempt
            $this->securityLogger->logAuthAttempt('login_attempt', [
                'login' => $login,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            // Validate input
            $validation = $this->validator->validateLogin(['login' => $login, 'password' => $password]);
            if (!$validation['valid']) {
                $this->securityLogger->logAuthAttempt('login_failed', [
                    'login' => $login,
                    'reason' => 'validation_failed',
                    'errors' => $validation['errors']
                ]);
                return $this->errorHandler->handleValidationError($validation['errors']);
            }
            
            // Use sanitized data
            $login = $validation['data']['login'];
            
            // Monitor for suspicious patterns
            $clientId = $this->getClientIdentifier();
            
            // Attempt authentication
            $user = $this->usersModel->authenticate($login, $password);
            
            if (!$user) {
                // Log failed authentication
                $this->securityLogger->logAuthAttempt('login_failed', [
                    'login' => $login,
                    'reason' => 'invalid_credentials'
                ]);
                
                // Monitor for brute force attacks
                $analysis = $this->securityMonitor->monitorAuthAttempts($clientId, 'failed');
                if ($analysis['threat_level'] === 'high') {
                    $this->securityLogger->logSuspiciousActivity('brute_force_detected', [
                        'login' => $login,
                        'client_id' => $clientId,
                        'analysis' => $analysis
                    ]);
                }
                
                $this->errorHandler->logFailedAuth($login);
                return $this->errorHandler->handleAuthenticationError('invalid_credentials');
            }
            
            // Check user status
            if ($user['status'] !== 'active') {
                $statusType = $user['status'] === 'banned' ? 'account_banned' : 'account_inactive';
                
                $this->securityLogger->logAuthAttempt('login_failed', [
                    'login' => $login,
                    'user_id' => $user['id'],
                    'reason' => $statusType,
                    'user_status' => $user['status']
                ]);
                
                return $this->errorHandler->handleAuthenticationError($statusType);
            }
            
            // Create session
            $sessionId = $this->sessionManager->createSession($user);
            
            // Log successful authentication
            $this->securityLogger->logAuthAttempt('login_success', [
                'login' => $login,
                'user_id' => $user['id'],
                'user_role' => $user['role'],
                'session_id' => $sessionId
            ]);
            
            // Log session creation
            $this->securityLogger->logSessionEvent('created', [
                'user_id' => $user['id'],
                'session_id' => $sessionId
            ]);
            
            // Monitor session activity
            $sessionData = [
                'user_id' => $user['id'],
                'session_id' => $sessionId,
                'ip' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ];
            $this->securityMonitor->monitorSessionActivity($sessionData);
            
            $this->errorHandler->logSuccessfulAuth($user);
            
            // Get redirect path based on role
            $redirectPath = $this->roleManager->getRedirectPath($user);
            
            return $this->errorHandler->createSuccessResponse(
                'Đăng nhập thành công! Chào mừng bạn quay trở lại',
                [
                    'user' => $user,
                    'session_id' => $sessionId,
                    'redirect_path' => $redirectPath
                ],
                $redirectPath
            );
            
        } catch (Exception $e) {
            // Log system error
            $this->securityLogger->logSystemError('authentication_error', [
                'login' => $login,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (strpos($e->getMessage(), 'locked') !== false) {
                return $this->errorHandler->handleAuthenticationError('account_locked');
            }
            
            return $this->errorHandler->handleSystemError($e, ['login' => $login]);
        }
    }
    
    /**
     * Register new user
     * Requirements: 1.1, 1.2
     */
    public function register(array $userData): array {
        try {
            // Log registration attempt
            $this->securityLogger->logAuthAttempt('registration_attempt', [
                'email' => $userData['email'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            // Validate input
            $validation = $this->validator->validateRegister($userData);
            if (!$validation['valid']) {
                $this->securityLogger->logAuthAttempt('registration_failed', [
                    'email' => $userData['email'] ?? 'unknown',
                    'reason' => 'validation_failed',
                    'errors' => $validation['errors']
                ]);
                return $this->errorHandler->handleValidationError($validation['errors']);
            }
            
            // Use sanitized data
            $userData = $validation['data'];
            
            // Monitor for suspicious registration patterns
            $clientId = $this->getClientIdentifier();
            $this->securityMonitor->monitorAuthAttempts($clientId, 'registration');
            
            // Register user
            $user = $this->usersModel->register($userData);
            
            // Auto-login after successful registration
            $this->sessionManager->createSession($user);
            
            // Log successful registration and auto-login
            $this->securityLogger->logAuthAttempt('registration_success', [
                'email' => $userData['email'],
                'user_id' => $user['id'],
                'user_role' => $user['role'],
                'auto_login' => true
            ]);
            
            return $this->errorHandler->createSuccessResponse(
                'Đăng ký thành công! Chào mừng bạn đến với ThuongLo.com',
                ['user' => $user, 'auto_login' => true],
                '/'
            );
            
        } catch (Exception $e) {
            // Log system error
            $this->securityLogger->logSystemError('registration_error', [
                'email' => $userData['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (strpos($e->getMessage(), 'Email already exists') !== false) {
                $this->securityLogger->logAuthAttempt('registration_failed', [
                    'email' => $userData['email'] ?? 'unknown',
                    'reason' => 'duplicate_email'
                ]);
                return $this->errorHandler->handleValidationError([
                    'email' => 'Email đã tồn tại: Email này đã được sử dụng bởi tài khoản khác'
                ]);
            } elseif (strpos($e->getMessage(), 'Username already exists') !== false) {
                $this->securityLogger->logAuthAttempt('registration_failed', [
                    'email' => $userData['email'] ?? 'unknown',
                    'reason' => 'duplicate_username'
                ]);
                return $this->errorHandler->handleValidationError([
                    'username' => 'Tên đăng nhập đã tồn tại: Tên đăng nhập này đã được sử dụng'
                ]);
            } elseif (strpos($e->getMessage(), 'Phone number already exists') !== false) {
                $this->securityLogger->logAuthAttempt('registration_failed', [
                    'email' => $userData['email'] ?? 'unknown',
                    'reason' => 'duplicate_phone'
                ]);
                return $this->errorHandler->handleValidationError([
                    'phone' => 'Số điện thoại đã tồn tại: Số điện thoại này đã được sử dụng'
                ]);
            } elseif (strpos($e->getMessage(), 'already exists') !== false) {
                $this->securityLogger->logAuthAttempt('registration_failed', [
                    'email' => $userData['email'] ?? 'unknown',
                    'reason' => 'duplicate_data'
                ]);
                return $this->errorHandler->handleValidationError([
                    'general' => 'Thông tin đã tồn tại: Vui lòng kiểm tra lại email, tên đăng nhập hoặc số điện thoại'
                ]);
            }
            
            return $this->errorHandler->handleSystemError($e, $userData);
        }
    }
    
    /**
     * Initiate password reset process
     * Requirements: 3.1, 3.5
     */
    public function initiatePasswordReset(string $email): array {
        try {
            // Log password reset request
            $this->securityLogger->logPasswordReset('request', [
                'email' => $email,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            // Validate email
            $validation = $this->validator->validatePasswordReset(['email' => $email]);
            if (!$validation['valid']) {
                $this->securityLogger->logPasswordReset('request_failed', [
                    'email' => $email,
                    'reason' => 'validation_failed',
                    'errors' => $validation['errors']
                ]);
                return $this->errorHandler->handleValidationError($validation['errors']);
            }
            
            $email = $validation['data']['email'];
            
            // Always return success to prevent email enumeration
            // But only send email if user exists
            $token = $this->usersModel->createPasswordResetToken($email);
            
            if ($token) {
                $this->securityLogger->logPasswordReset('token_generated', [
                    'email' => $email,
                    'token_generated' => true
                ]);
                
                // In a real application, you would send email here
                // For now, we'll just log it
                error_log("Password reset token for $email: $token");
            } else {
                $this->securityLogger->logPasswordReset('token_not_generated', [
                    'email' => $email,
                    'reason' => 'user_not_found'
                ]);
            }
            
            return $this->errorHandler->createSuccessResponse(
                'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu'
            );
            
        } catch (Exception $e) {
            // Log system error
            $this->securityLogger->logSystemError('password_reset_request_error', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorHandler->handleSystemError($e, ['email' => $email]);
        }
    }
    
    /**
     * Reset password with token
     * Requirements: 3.2, 3.4
     */
    public function resetPassword(string $token, string $newPassword): array {
        try {
            // Log password reset attempt
            $this->securityLogger->logPasswordReset('reset_attempt', [
                'token_provided' => !empty($token),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            // Validate input
            $validation = $this->validator->validateNewPassword([
                'token' => $token,
                'password' => $newPassword,
                'password_confirmation' => $newPassword
            ]);
            
            if (!$validation['valid']) {
                $this->securityLogger->logPasswordReset('reset_failed', [
                    'reason' => 'validation_failed',
                    'errors' => $validation['errors']
                ]);
                return $this->errorHandler->handleValidationError($validation['errors']);
            }
            
            // Validate token
            $tokenData = $this->usersModel->validatePasswordResetToken($token);
            if (!$tokenData) {
                $this->securityLogger->logPasswordReset('reset_failed', [
                    'reason' => 'invalid_token'
                ]);
                return $this->errorHandler->handleAuthenticationError('invalid_token');
            }
            
            // Get user by email
            $user = $this->usersModel->findByLogin($tokenData['email']);
            if (!$user) {
                $this->securityLogger->logPasswordReset('reset_failed', [
                    'reason' => 'user_not_found',
                    'email' => $tokenData['email']
                ]);
                return $this->errorHandler->handleAuthenticationError('invalid_token');
            }
            
            // Update password
            $result = $this->usersModel->updatePasswordSecure($user['id'], $newPassword, true);
            
            if (!$result) {
                $this->securityLogger->logPasswordReset('reset_failed', [
                    'reason' => 'password_update_failed',
                    'user_id' => $user['id']
                ]);
                throw new Exception('Failed to update password');
            }
            
            // Clear the token
            $this->usersModel->clearPasswordResetToken($user['id']);
            
            // Log successful password reset
            $this->securityLogger->logPasswordReset('reset_success', [
                'user_id' => $user['id'],
                'email' => $user['email']
            ]);
            
            // Log security event for password change
            $this->securityLogger->logSecurityEvent('password_changed', [
                'user_id' => $user['id'],
                'method' => 'password_reset',
                'ip' => $this->getClientIp()
            ]);
            
            return $this->errorHandler->createSuccessResponse(
                'Mật khẩu đã được đặt lại thành công',
                [],
                '/auth/login'
            );
            
        } catch (Exception $e) {
            // Log system error
            $this->securityLogger->logSystemError('password_reset_error', [
                'token_provided' => !empty($token),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorHandler->handleSystemError($e, ['token_provided' => !empty($token)]);
        }
    }
    
    /**
     * Logout current user
     * Requirements: 4.1, 4.3
     */
    public function logout(): bool {
        try {
            // Get current user for logging
            $user = $this->getCurrentUser();
            $sessionId = session_id();
            
            // Log logout attempt
            if ($user) {
                $this->securityLogger->logAuthAttempt('logout_attempt', [
                    'user_id' => $user['id'],
                    'session_id' => $sessionId
                ]);
            }
            
            // Destroy session
            $result = $this->sessionManager->destroySession();
            
            // Log logout result
            if ($user) {
                if ($result) {
                    $this->securityLogger->logAuthAttempt('logout_success', [
                        'user_id' => $user['id'],
                        'session_id' => $sessionId
                    ]);
                    
                    $this->securityLogger->logSessionEvent('destroyed', [
                        'user_id' => $user['id'],
                        'session_id' => $sessionId,
                        'reason' => 'user_logout'
                    ]);
                } else {
                    $this->securityLogger->logAuthAttempt('logout_failed', [
                        'user_id' => $user['id'],
                        'session_id' => $sessionId,
                        'reason' => 'session_destroy_failed'
                    ]);
                }
                
                $this->errorHandler->logLogout($user);
            }
            
            return $result;
            
        } catch (Exception $e) {
            // Log system error
            $this->securityLogger->logSystemError('logout_error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            error_log("Logout error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?array {
        return $this->sessionManager->getCurrentUser();
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool {
        $user = $this->getCurrentUser();
        return $user ? $this->roleManager->hasRole($user, $role) : false;
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool {
        $user = $this->getCurrentUser();
        return $user ? $this->roleManager->hasPermission($user, $permission) : false;
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool {
        return $this->sessionManager->isValid();
    }
    
    /**
     * Alias for isAuthenticated() - check if user is logged in
     */
    public function isLoggedIn(): bool {
        return $this->isAuthenticated();
    }
    
    /**
     * Get user role
     */
    public function getUserRole(): ?string {
        $user = $this->getCurrentUser();
        return $user['role'] ?? null;
    }
    
    /**
     * Check if current user can access resource
     */
    public function canAccess(string $resource): bool {
        $user = $this->getCurrentUser();
        return $user ? $this->roleManager->canAccess($user, $resource) : false;
    }
    
    /**
     * Get redirect path for current user
     */
    public function getRedirectPath(): string {
        $user = $this->getCurrentUser();
        return $user ? $this->roleManager->getRedirectPath($user) : '?page=login';
    }
    
    /**
     * Extend current session
     */
    public function extendSession(): void {
        $this->sessionManager->extendSession();
    }
    
    /**
     * Get session time remaining
     */
    public function getSessionTimeRemaining(): int {
        return $this->sessionManager->getTimeRemaining();
    }
    
    /**
     * Check if session needs renewal
     */
    public function sessionNeedsRenewal(): bool {
        return $this->sessionManager->needsRenewal();
    }
    
    /**
     * Get CSRF token
     */
    public function getCsrfToken(): string {
        return $this->sessionManager->getCsrfToken();
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken(string $token): bool {
        return $this->sessionManager->verifyCsrfToken($token);
    }
    
    /**
     * Get user security info
     */
    public function getUserSecurityInfo(): ?array {
        $user = $this->getCurrentUser();
        return $user ? $this->usersModel->getUserSecurityInfo($user['id']) : null;
    }
    
    /**
     * Clean up expired data
     */
    public function cleanupExpiredData(): void {
        $this->usersModel->cleanupExpiredData();
    }
    
    /**
     * Force logout user (for admin purposes)
     */
    public function forceLogout(): bool {
        return $this->sessionManager->destroySession();
    }
    
    // ========== Helper Methods ==========
    
    /**
     * Get client identifier for security monitoring
     */
    private function getClientIdentifier(): string {
        $ip = $this->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        return md5($ip . $userAgent);
    }
    
    /**
     * Get client IP address
     */
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
    
    /**
     * Update user profile
     */
    public function updateProfile(array $data): array {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->errorHandler->handleAuthenticationError('session_expired');
            }
            
            // Validate input (basic validation)
            $allowedFields = ['name', 'phone', 'address'];
            $updateData = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $this->validator->sanitizeInput($data[$field]);
                }
            }
            
            if (empty($updateData)) {
                return $this->errorHandler->handleValidationError(['general' => 'Không có dữ liệu để cập nhật']);
            }
            
            // Update user
            $result = $this->usersModel->update($user['id'], $updateData);
            
            if (!$result) {
                throw new Exception('Failed to update profile');
            }
            
            // Update session data
            $updatedUser = $this->usersModel->find($user['id']);
            $this->sessionManager->setCurrentUser($updatedUser);
            
            return $this->errorHandler->createSuccessResponse(
                'Cập nhật thông tin thành công',
                ['user' => $updatedUser]
            );
            
        } catch (Exception $e) {
            return $this->errorHandler->handleSystemError($e, $data);
        }
    }
}