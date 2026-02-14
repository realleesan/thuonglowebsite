<?php
/**
 * AuthController
 * Handles HTTP requests for authentication
 * Requirements: 1.5, 4.2, 5.5, 7.4, 8.1
 */

require_once __DIR__ . '/../services/AuthService.php';

class AuthController {
    private AuthService $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    /**
     * Display login form
     */
    public function login(): void {
        // If already authenticated, redirect to dashboard
        if ($this->authService->isAuthenticated()) {
            $this->redirect($this->authService->getRedirectPath());
            return;
        }
        
        // Get any flash messages
        $error = $_SESSION['flash_error'] ?? null;
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        
        // Get CSRF token
        $csrfToken = $this->authService->getCsrfToken();
        
        $this->renderView('auth/login', [
            'csrf_token' => $csrfToken,
            'error' => $error,
            'success' => $success,
            'page_title' => 'Đăng nhập',
            'form_action' => '?page=login&action=process'
        ]);
    }
    
    /**
     * Process login request
     */
    public function processLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?page=login');
            return;
        }
        
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->authService->verifyCsrfToken($csrfToken)) {
            $this->setFlashMessage('error', 'Token bảo mật không hợp lệ');
            $this->redirect('?page=login');
            return;
        }
        
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $result = $this->authService->authenticate($login, $password);
        
        if ($result['success']) {
            // Successful login
            $this->setFlashMessage('success', $result['message']);
            $this->redirect($result['redirect'] ?? '/');
        } else {
            // Failed login
            $this->setFlashMessage('error', $result['message']);
            $this->redirect('?page=login');
        }
    }
    
    /**
     * Display registration form
     */
    public function register(): void {
        // If already authenticated, redirect to dashboard
        if ($this->authService->isAuthenticated()) {
            $this->redirect($this->authService->getRedirectPath());
            return;
        }
        
        // Get any flash messages
        $error = $_SESSION['flash_error'] ?? null;
        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_error'], $_SESSION['flash_errors']);
        
        // Get CSRF token
        $csrfToken = $this->authService->getCsrfToken();
        
        $this->renderView('auth/register', [
            'csrf_token' => $csrfToken,
            'error' => $error,
            'errors' => $errors,
            'page_title' => 'Đăng ký',
            'form_action' => '?page=register&action=process',
            'login_url' => '?page=login'
        ]);
    }
    
    /**
     * Process registration request
     */
    public function processRegister(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?page=register');
            return;
        }
        
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->authService->verifyCsrfToken($csrfToken)) {
            $this->setFlashMessage('error', 'Token bảo mật không hợp lệ');
            $this->redirect('?page=register');
            return;
        }
        
        $userData = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'password' => $_POST['password'] ?? '',
            'password_confirmation' => $_POST['confirm_password'] ?? '',
            'address' => $_POST['address'] ?? '',
        ];
        
        $result = $this->authService->register($userData);
        
        if ($result['success']) {
            // Successful registration
            $this->setFlashMessage('success', $result['message']);
            $this->redirect($result['redirect'] ?? '?page=login');
        } else {
            // Failed registration
            if (isset($result['errors']) && is_array($result['errors'])) {
                $_SESSION['flash_errors'] = $result['errors'];
            } else {
                $this->setFlashMessage('error', $result['message']);
            }
            $this->redirect('?page=register');
        }
    }
    
    /**
     * Display forgot password form
     */
    public function forgot(): void {
        // If already authenticated, redirect to dashboard
        if ($this->authService->isAuthenticated()) {
            $this->redirect($this->authService->getRedirectPath());
            return;
        }
        
        // Get any flash messages
        $error = $_SESSION['flash_error'] ?? null;
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        
        // Get CSRF token
        $csrfToken = $this->authService->getCsrfToken();
        
        $this->renderView('auth/forgot', [
            'csrf_token' => $csrfToken,
            'error' => $error,
            'success' => $success,
            'page_title' => 'Quên mật khẩu',
            'form_action' => '?page=forgot&action=process',
            'login_url' => '?page=login'
        ]);
    }
    
    /**
     * Process forgot password request
     */
    public function processForgot(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?page=forgot');
            return;
        }
        
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->authService->verifyCsrfToken($csrfToken)) {
            $this->setFlashMessage('error', 'Token bảo mật không hợp lệ');
            $this->redirect('?page=forgot');
            return;
        }
        
        $email = $_POST['email'] ?? '';
        
        $result = $this->authService->initiatePasswordReset($email);
        
        // Always show success message to prevent email enumeration
        $this->setFlashMessage('success', $result['message']);
        $this->redirect('?page=forgot');
    }
    
    /**
     * Display reset password form
     */
    public function resetPassword(): void {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->setFlashMessage('error', 'Token không hợp lệ');
            $this->redirect('?page=forgot');
            return;
        }
        
        // If already authenticated, redirect to dashboard
        if ($this->authService->isAuthenticated()) {
            $this->redirect($this->authService->getRedirectPath());
            return;
        }
        
        // Get any flash messages
        $error = $_SESSION['flash_error'] ?? null;
        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_error'], $_SESSION['flash_errors']);
        
        // Get CSRF token
        $csrfToken = $this->authService->getCsrfToken();
        
        $this->renderView('auth/reset', [
            'csrf_token' => $csrfToken,
            'token' => $token,
            'error' => $error,
            'errors' => $errors,
            'page_title' => 'Đặt lại mật khẩu'
        ]);
    }
    
    /**
     * Process reset password request
     */
    public function processReset(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?page=forgot');
            return;
        }
        
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->authService->verifyCsrfToken($csrfToken)) {
            $this->setFlashMessage('error', 'Token bảo mật không hợp lệ');
            $this->redirect('?page=forgot');
            return;
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $result = $this->authService->resetPassword($token, $password);
        
        if ($result['success']) {
            // Successful reset
            $this->setFlashMessage('success', $result['message']);
            $this->redirect($result['redirect'] ?? '?page=login');
        } else {
            // Failed reset
            if (isset($result['errors']) && is_array($result['errors'])) {
                $_SESSION['flash_errors'] = $result['errors'];
            } else {
                $this->setFlashMessage('error', $result['message']);
            }
            $this->redirect('?page=reset&token=' . urlencode($token));
        }
    }
    
    /**
     * Process logout request
     */
    public function logout(): void {
        $result = $this->authService->logout();
        
        if ($result) {
            $this->setFlashMessage('success', 'Đăng xuất thành công');
        }
        
        $this->redirect('?page=login');
    }
    
    /**
     * Authentication middleware - check if user is authenticated
     */
    public function checkAuth(): bool {
        if (!$this->authService->isAuthenticated()) {
            $this->setFlashMessage('error', 'Vui lòng đăng nhập để tiếp tục');
            $this->redirect('?page=login');
            return false;
        }
        
        return true;
    }
    
    /**
     * Role-based access control middleware
     */
    public function requireRole(string $role): bool {
        if (!$this->checkAuth()) {
            return false;
        }
        
        if (!$this->authService->hasRole($role)) {
            $this->setFlashMessage('error', 'Bạn không có quyền truy cập trang này');
            $this->redirect($this->authService->getRedirectPath());
            return false;
        }
        
        return true;
    }
    
    /**
     * Permission-based access control middleware
     */
    public function requirePermission(string $permission): bool {
        if (!$this->checkAuth()) {
            return false;
        }
        
        if (!$this->authService->hasPermission($permission)) {
            $this->setFlashMessage('error', 'Bạn không có quyền thực hiện hành động này');
            $this->redirect($this->authService->getRedirectPath());
            return false;
        }
        
        return true;
    }
    
    /**
     * Admin access control middleware
     */
    public function requireAdmin(): bool {
        return $this->requireRole('admin');
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
    
    // ========== Helper Methods ==========
    
    /**
     * Render view with data
     */
    private function renderView(string $view, array $data = []): void {
        // Set up view data for layout
        $viewData = $data;
        
        // Set layout variables
        $content = __DIR__ . "/../views/{$view}.php";
        $title = $data['page_title'] ?? 'Thuong Lo';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        
        // Set current page for CSS/JS loading
        if (strpos($view, 'login') !== false) {
            $currentPage = 'login';
            $breadcrumbs = [
                ['title' => 'Trang chủ', 'url' => './'],
                ['title' => 'Đăng nhập']
            ];
        } elseif (strpos($view, 'register') !== false) {
            $currentPage = 'register';
            $breadcrumbs = [
                ['title' => 'Trang chủ', 'url' => './'],
                ['title' => 'Đăng ký']
            ];
        } elseif (strpos($view, 'forgot') !== false) {
            $currentPage = 'forgot';
            $breadcrumbs = [
                ['title' => 'Trang chủ', 'url' => './'],
                ['title' => 'Đăng nhập', 'url' => '?page=login'],
                ['title' => 'Quên mật khẩu']
            ];
        } else {
            $currentPage = 'auth';
            $breadcrumbs = [
                ['title' => 'Trang chủ', 'url' => './'],
                ['title' => 'Xác thực']
            ];
        }
        
        // Check if view file exists
        if (!file_exists($content)) {
            throw new Exception("View file not found: {$view}");
        }
        
        // Include master layout
        $masterLayout = __DIR__ . '/../views/_layout/master.php';
        if (!file_exists($masterLayout)) {
            throw new Exception("Master layout not found: {$masterLayout}");
        }
        
        include $masterLayout;
    }
    
    /**
     * Redirect to URL
     */
    private function redirect(string $url): void {
        // Ensure URL starts with /
        if (substr($url, 0, 1) !== '/' && substr($url, 0, 4) !== 'http') {
            $url = '/' . $url;
        }
        
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Set flash message
     */
    private function setFlashMessage(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION["flash_{$type}"] = $message;
    }
    
    /**
     * Get flash message
     */
    public function getFlashMessage(string $type): ?string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $message = $_SESSION["flash_{$type}"] ?? null;
        unset($_SESSION["flash_{$type}"]);
        return $message;
    }
    
    /**
     * AJAX response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Handle AJAX authentication request
     */
    public function ajaxLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verify CSRF token
        $csrfToken = $input['csrf_token'] ?? '';
        if (!$this->authService->verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Token bảo mật không hợp lệ'], 403);
            return;
        }
        
        $login = $input['login'] ?? '';
        $password = $input['password'] ?? '';
        
        $result = $this->authService->authenticate($login, $password);
        
        $this->jsonResponse($result);
    }
    
    /**
     * Handle AJAX registration request
     */
    public function ajaxRegister(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verify CSRF token
        $csrfToken = $input['csrf_token'] ?? '';
        if (!$this->authService->verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Token bảo mật không hợp lệ'], 403);
            return;
        }
        
        $result = $this->authService->register($input);
        
        $this->jsonResponse($result);
    }
    
    /**
     * Get session info (for AJAX)
     */
    public function sessionInfo(): void {
        $user = $this->authService->getCurrentUser();
        
        $info = [
            'authenticated' => $this->authService->isAuthenticated(),
            'user' => $user,
            'time_remaining' => $this->authService->getSessionTimeRemaining(),
            'needs_renewal' => $this->authService->sessionNeedsRenewal(),
            'csrf_token' => $this->authService->getCsrfToken(),
        ];
        
        $this->jsonResponse($info);
    }
    
    /**
     * Extend session (for AJAX)
     */
    public function extendSession(): void {
        if (!$this->authService->isAuthenticated()) {
            $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
            return;
        }
        
        $this->authService->extendSession();
        
        $this->jsonResponse([
            'success' => true,
            'time_remaining' => $this->authService->getSessionTimeRemaining()
        ]);
    }
}