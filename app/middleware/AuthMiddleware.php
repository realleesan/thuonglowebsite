<?php
/**
 * Authentication Middleware
 * Provides authentication and authorization middleware for protected routes
 * Integrates with existing MVC structure and AuthService
 */

require_once __DIR__ . '/../services/AuthService.php';

class AuthMiddleware {
    private AuthService $authService;
    private array $config;
    
    public function __construct() {
        $this->authService = new AuthService();
        $this->config = require __DIR__ . '/../../config.php';
    }
    
    /**
     * Check if user is authenticated
     * Redirects to login if not authenticated
     */
    public function requireAuth(): bool {
        if (!$this->authService->isAuthenticated()) {
            $this->setFlashMessage('error', 'Vui lòng đăng nhập để tiếp tục');
            $this->redirectToLogin();
            return false;
        }
        
        return true;
    }
    
    /**
     * Require specific role
     * Checks authentication first, then role
     */
    public function requireRole(string $role): bool {
        if (!$this->requireAuth()) {
            return false;
        }
        
        if (!$this->authService->hasRole($role)) {
            $this->setFlashMessage('error', 'Bạn không có quyền truy cập trang này');
            $this->redirectBasedOnRole();
            return false;
        }
        
        return true;
    }
    
    /**
     * Require specific permission
     * Checks authentication first, then permission
     */
    public function requirePermission(string $permission): bool {
        if (!$this->requireAuth()) {
            return false;
        }
        
        if (!$this->authService->hasPermission($permission)) {
            $this->setFlashMessage('error', 'Bạn không có quyền thực hiện hành động này');
            $this->redirectBasedOnRole();
            return false;
        }
        
        return true;
    }
    
    /**
     * Require admin role
     * Shortcut for requireRole('admin')
     */
    public function requireAdmin(): bool {
        return $this->requireRole('admin');
    }
    
    /**
     * Require affiliate role or higher
     * Allows admin and affiliate users
     */
    public function requireAffiliate(): bool {
        if (!$this->requireAuth()) {
            return false;
        }
        
        $user = $this->authService->getCurrentUser();
        if (!$user || !in_array($user['role'], ['admin', 'affiliate'])) {
            $this->setFlashMessage('error', 'Bạn không có quyền truy cập trang này');
            $this->redirectBasedOnRole();
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if user can access specific resource
     * Uses RoleManager for fine-grained access control
     */
    public function canAccess(string $resource): bool {
        if (!$this->requireAuth()) {
            return false;
        }
        
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        $roleManager = $this->authService->getRoleManager();
        return $roleManager->canAccess($user, $resource);
    }
    
    /**
     * Middleware for guest users only (not authenticated)
     * Redirects authenticated users to their dashboard
     */
    public function requireGuest(): bool {
        if ($this->authService->isAuthenticated()) {
            $this->redirectBasedOnRole();
            return false;
        }
        
        return true;
    }
    
    /**
     * CSRF protection middleware
     * Validates CSRF token for POST requests
     */
    public function requireCsrfToken(): bool {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            
            if (!$this->authService->verifyCsrfToken($token)) {
                $this->setFlashMessage('error', 'Token bảo mật không hợp lệ. Vui lòng thử lại');
                $this->redirectBack();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Rate limiting middleware
     * Prevents brute force attacks
     */
    public function requireRateLimit(string $action = 'general', int $maxAttempts = 10, int $timeWindow = 300): bool {
        $identifier = $this->getClientIdentifier();
        $key = "rate_limit_{$action}_{$identifier}";
        
        // Simple rate limiting using session (in production, use Redis or database)
        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }
        
        $now = time();
        $attempts = $_SESSION['rate_limits'][$key] ?? ['count' => 0, 'first_attempt' => $now];
        
        // Reset if time window has passed
        if ($now - $attempts['first_attempt'] > $timeWindow) {
            $attempts = ['count' => 0, 'first_attempt' => $now];
        }
        
        $attempts['count']++;
        $_SESSION['rate_limits'][$key] = $attempts;
        
        if ($attempts['count'] > $maxAttempts) {
            $this->setFlashMessage('error', 'Quá nhiều yêu cầu. Vui lòng thử lại sau');
            $this->redirectBack();
            return false;
        }
        
        return true;
    }
    
    /**
     * Session timeout middleware
     * Checks if session has expired
     */
    public function checkSessionTimeout(): bool {
        if ($this->authService->isAuthenticated()) {
            $sessionManager = $this->authService->getSessionManager();
            
            if (!$sessionManager->checkTimeout()) {
                $this->authService->logout();
                $this->setFlashMessage('warning', 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại');
                $this->redirectToLogin();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Combine multiple middleware checks
     * Usage: combineMiddleware(['auth', 'admin', 'csrf'])
     */
    public function combineMiddleware(array $middlewares): bool {
        foreach ($middlewares as $middleware) {
            switch ($middleware) {
                case 'auth':
                    if (!$this->requireAuth()) return false;
                    break;
                case 'admin':
                    if (!$this->requireAdmin()) return false;
                    break;
                case 'affiliate':
                    if (!$this->requireAffiliate()) return false;
                    break;
                case 'guest':
                    if (!$this->requireGuest()) return false;
                    break;
                case 'csrf':
                    if (!$this->requireCsrfToken()) return false;
                    break;
                case 'timeout':
                    if (!$this->checkSessionTimeout()) return false;
                    break;
                case 'rate_limit':
                    if (!$this->requireRateLimit()) return false;
                    break;
                default:
                    // Handle custom role or permission
                    if (strpos($middleware, 'role:') === 0) {
                        $role = substr($middleware, 5);
                        if (!$this->requireRole($role)) return false;
                    } elseif (strpos($middleware, 'permission:') === 0) {
                        $permission = substr($middleware, 11);
                        if (!$this->requirePermission($permission)) return false;
                    } elseif (strpos($middleware, 'resource:') === 0) {
                        $resource = substr($middleware, 9);
                        if (!$this->canAccess($resource)) return false;
                    }
                    break;
            }
        }
        
        return true;
    }
    
    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?array {
        return $this->authService->getCurrentUser();
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool {
        return $this->authService->hasRole($role);
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool {
        return $this->authService->hasPermission($permission);
    }
    
    /**
     * Get CSRF token for forms
     */
    public function getCsrfToken(): string {
        return $this->authService->getCsrfToken();
    }
    
    // Private helper methods
    
    private function redirectToLogin(): void {
        $this->redirect('/auth/login');
    }
    
    private function redirectBasedOnRole(): void {
        $user = $this->authService->getCurrentUser();
        if ($user) {
            $roleManager = $this->authService->getRoleManager();
            $redirectPath = $roleManager->getRedirectPath($user);
            $this->redirect($redirectPath);
        } else {
            $this->redirectToLogin();
        }
    }
    
    private function redirectBack(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    private function redirect(string $url): void {
        // Handle relative URLs
        if (strpos($url, '/') === 0) {
            $baseUrl = $this->getBaseUrl();
            $url = $baseUrl . $url;
        }
        
        header("Location: $url");
        exit;
    }
    
    private function getBaseUrl(): string {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME']);
        
        return $protocol . '://' . $host . ($path === '/' ? '' : $path);
    }
    
    private function setFlashMessage(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['flash_messages'][$type] = $message;
    }
    
    private function getClientIdentifier(): string {
        // Use IP address and user agent for identification
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        return md5($ip . $userAgent);
    }
}