<?php

/**
 * AffiliateController - Xử lý đăng ký đại lý cho existing users
 * 
 * Controller này xử lý các yêu cầu liên quan đến đăng ký đại lý cho người dùng hiện tại,
 * bao gồm hiển thị popup đăng ký, xử lý form submission, và quản lý trạng thái.
 * 
 * Requirements: 2.1, 2.2, 2.3, 2.4
 */

require_once __DIR__ . '/../services/AgentRegistrationService.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AgentErrorHandler.php';

class AffiliateController {
    private AgentRegistrationService $agentService;
    private AuthService $authService;
    private AgentErrorHandler $errorHandler;
    
    public function __construct() {
        $this->agentService = new AgentRegistrationService();
        $this->authService = new AuthService();
        $this->errorHandler = new AgentErrorHandler();
    }
    
    /**
     * Hiển thị popup đăng ký đại lý cho existing users
     * Requirements: 2.1
     */
    public function showRegistrationPopup(): void {
        // Validate user authentication
        $validation = $this->agentService->validateAgentOperation('register');
        if (!$validation['success']) {
            if (isset($validation['redirect'])) {
                $this->redirect($validation['redirect']);
                return;
            }
            $this->setFlashMessage('error', $validation['message'] ?? 'Không thể truy cập trang này');
            $this->redirect('?page=users&module=dashboard');
            return;
        }
        
        $currentUser = $validation['user'];
        $userId = $currentUser['id'];
        
        // Check existing request status
        $statusResult = $this->agentService->getRegistrationStatus($userId);
        
        if (!$statusResult['success']) {
            $this->setFlashMessage('error', 'Không thể kiểm tra trạng thái đăng ký');
            $this->redirect('?page=users&module=dashboard');
            return;
        }
        
        // If user has existing request, show status instead of form
        if ($statusResult['has_existing_request']) {
            $requestDetails = $statusResult['request_details'];
            $this->showProcessingMessage($requestDetails['status'], $requestDetails);
            return;
        }
        
        // If user can't submit (rate limited), show message
        if (!$statusResult['can_submit']) {
            // Show rate limit message page
            $this->renderView('affiliate/rate_limit_message', [
                'page_title' => 'Quá nhiều yêu cầu - Đăng ký đại lý',
                'message' => 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau.'
            ]);
            return;
        }
        
        // Get CSRF token
        $csrfToken = $this->authService->getCsrfToken();
        
        // Debug: Get current user email from session as fallback
        $userEmail = $currentUser['email'] ?? $_SESSION['user_email'] ?? '';
        
        // If still empty, try to get from database
        if (empty($userEmail) && isset($currentUser['id'])) {
            require_once __DIR__ . '/../models/UsersModel.php';
            $usersModel = new UsersModel();
            $dbUser = $usersModel->find($currentUser['id']);
            $userEmail = $dbUser['email'] ?? '';
        }
        
        // Render full page view instead of popup
        $this->renderView('affiliate/registration_page', [
            'csrf_token' => $csrfToken,
            'user' => $currentUser,
            'form_action' => '?page=agent&action=register',
            'current_email' => $userEmail,
            'page_title' => 'Đăng ký trở thành đại lý - ThuongLo.com'
        ]);
    }
    
    /**
     * Xử lý form submission từ popup
     * Requirements: 2.2, 2.3
     */
    public function processRegistration(): void {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->setFlashMessage('error', 'Phương thức không được phép');
                $this->redirect('?page=agent');
                return;
            }
            
            // Validate user authentication
            $validation = $this->agentService->validateAgentOperation('register');
            if (!$validation['success']) {
                $this->errorHandler->logSuccess('agent_registration_auth_failed', [
                    'reason' => $validation['message'] ?? 'Authentication failed'
                ]);
                $this->setFlashMessage('error', $validation['message'] ?? 'Không thể xác thực người dùng');
                $this->redirect('?page=agent');
                return;
            }
            
            $currentUser = $validation['user'];
            $userId = $currentUser['id'];
            
            // Verify CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!$this->authService->verifyCsrfToken($csrfToken)) {
                $this->setFlashMessage('error', 'Token bảo mật không hợp lệ');
                $this->redirect('?page=agent');
                return;
            }
            
            // Get agent data from form
            $agentEmail = $_POST['agent_email'] ?? '';
            $fullName = $_POST['full_name'] ?? '';
            $phoneNumber = $_POST['phone_number'] ?? '';
            $businessType = $_POST['business_type'] ?? '';
            $experienceYears = $_POST['experience_years'] ?? '';
            $businessAddress = $_POST['business_address'] ?? '';
            $targetMarket = $_POST['target_market'] ?? [];
            $motivation = $_POST['motivation'] ?? '';
            
            // Validate input
            $validationErrors = [];
            
            if (empty($agentEmail)) {
                $validationErrors['agent_email'] = 'Email là bắt buộc';
            } elseif (!filter_var($agentEmail, FILTER_VALIDATE_EMAIL)) {
                $validationErrors['agent_email'] = 'Định dạng email không hợp lệ';
            }
            
            if (empty($fullName)) {
                $validationErrors['full_name'] = 'Họ và tên là bắt buộc';
            }
            
            if (empty($phoneNumber)) {
                $validationErrors['phone_number'] = 'Số điện thoại là bắt buộc';
            } elseif (!preg_match('/^[0-9]{10,11}$/', $phoneNumber)) {
                $validationErrors['phone_number'] = 'Số điện thoại phải có 10-11 chữ số';
            }
            
            if (empty($businessType)) {
                $validationErrors['business_type'] = 'Loại hình kinh doanh là bắt buộc';
            }
            
            if (empty($experienceYears)) {
                $validationErrors['experience_years'] = 'Kinh nghiệm bán hàng là bắt buộc';
            }
            
            if (empty($businessAddress)) {
                $validationErrors['business_address'] = 'Địa chỉ kinh doanh là bắt buộc';
            }
            
            if (!empty($validationErrors)) {
                // Preserve form data
                $_SESSION['agent_form_data'] = $_POST;
                $_SESSION['flash_errors'] = $validationErrors;
                $this->redirect('?page=agent');
                return;
            }
            
            // Prepare agent data
            $agentData = [
                'email' => $agentEmail,
                'additional_info' => [
                    'registration_source' => 'existing_user_form',
                    'requested_at' => date('Y-m-d H:i:s'),
                    'full_name' => $fullName,
                    'phone_number' => $phoneNumber,
                    'business_type' => $businessType,
                    'experience_years' => $experienceYears,
                    'business_address' => $businessAddress,
                    'target_market' => is_array($targetMarket) ? implode(', ', $targetMarket) : '',
                    'motivation' => $motivation
                ]
            ];
            
            // Process registration
            $result = $this->agentService->upgradeExistingUserToAgent($userId, $agentData);
            
            if ($result['success']) {
                $this->errorHandler->logSuccess('agent_registration_completed', [
                    'user_id' => $userId,
                    'email' => $agentEmail,
                    'registration_type' => 'existing_user'
                ]);
                
                $this->setFlashMessage('success', 
                    'Yêu cầu đăng ký đại lý đã được gửi thành công! ' .
                    'Chúng tôi sẽ xem xét và phản hồi trong vòng 24 giờ qua email Gmail của bạn.'
                );
                $this->redirect('?page=agent&action=processing');
            } else {
                $this->setFlashMessage('error', $result['message'] ?? 'Có lỗi xảy ra khi xử lý yêu cầu');
                $this->redirect('?page=agent');
            }
            
        } catch (Exception $e) {
            $this->errorHandler->handleRegistrationError($e, [
                'user_id' => $userId ?? null,
                'action' => 'process_registration',
                'post_data' => array_keys($_POST)
            ]);
            
            $this->setFlashMessage('error', 'Có lỗi hệ thống xảy ra. Vui lòng thử lại sau.');
            $this->redirect('?page=agent');
        }
    }
    
    /**
     * Kiểm tra trạng thái đăng ký của user hiện tại
     * Requirements: 2.4, 4.4
     */
    public function checkStatus(): void {
        // Validate user authentication
        $validation = $this->agentService->validateAgentOperation('register');
        if (!$validation['success']) {
            $this->jsonResponse($validation, 403);
            return;
        }
        
        $currentUser = $validation['user'];
        $userId = $currentUser['id'];
        
        // Get registration status
        $statusResult = $this->agentService->getRegistrationStatus($userId);
        
        if (!$statusResult['success']) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Không thể kiểm tra trạng thái đăng ký'
            ], 500);
            return;
        }
        
        // Add user info to response
        $response = array_merge($statusResult, [
            'user' => [
                'id' => $userId,
                'name' => $currentUser['name'] ?? $currentUser['username'] ?? 'Người dùng',
                'role' => $currentUser['role'] ?? 'user'
            ]
        ]);
        
        $this->jsonResponse($response);
    }
    
    /**
     * Hiển thị thông báo xử lý cho users có pending status
     * Requirements: 1.5, 2.4, 4.4
     */
    public function showProcessingMessage(string $status = null, array $requestDetails = null): void {
        // If called directly, get current user status
        if ($status === null) {
            $validation = $this->agentService->validateAgentOperation('register');
            if (!$validation['success']) {
                if (isset($validation['redirect'])) {
                    $this->redirect($validation['redirect']);
                    return;
                }
                $this->setFlashMessage('error', $validation['message'] ?? 'Không thể truy cập trang này');
                $this->redirect('?page=users&module=dashboard');
                return;
            }
            
            $currentUser = $validation['user'];
            $userId = $currentUser['id'];
            
            $statusResult = $this->agentService->getRegistrationStatus($userId);
            if (!$statusResult['success'] || !$statusResult['has_existing_request']) {
                $this->setFlashMessage('error', 'Không có yêu cầu đăng ký nào');
                $this->redirect('?page=users&module=dashboard');
                return;
            }
            
            $requestDetails = $statusResult['request_details'];
            $status = $requestDetails['status'];
        }
        
        // Prepare message based on status
        $messages = [
            'pending' => [
                'title' => 'Yêu cầu đang được xử lý',
                'message' => 'Yêu cầu đăng ký đại lý của bạn đang được xem xét. Chúng tôi sẽ xử lý trong vòng 24 giờ và gửi email thông báo kết quả.',
                'icon' => 'clock',
                'color' => 'warning'
            ],
            'approved' => [
                'title' => 'Chúc mừng! Bạn đã trở thành đại lý',
                'message' => 'Yêu cầu đăng ký đại lý của bạn đã được phê duyệt. Bạn có thể truy cập các tính năng đại lý ngay bây giờ.',
                'icon' => 'check-circle',
                'color' => 'success'
            ],
            'rejected' => [
                'title' => 'Yêu cầu không được phê duyệt',
                'message' => 'Rất tiếc, yêu cầu đăng ký đại lý của bạn không được phê duyệt lúc này. Bạn có thể liên hệ hỗ trợ để biết thêm chi tiết.',
                'icon' => 'x-circle',
                'color' => 'danger'
            ]
        ];
        
        $messageData = $messages[$status] ?? $messages['pending'];
        
        // Add request details if available
        if ($requestDetails) {
            $messageData['request_date'] = $requestDetails['request_date'];
            $messageData['approved_date'] = $requestDetails['approved_date'];
        }
        
        // Always render processing message view (no more JSON responses)
        $this->renderView('affiliate/processing_message', [
            'status' => $status,
            'message_data' => $messageData,
            'request_details' => $requestDetails,
            'page_title' => $messageData['title']
        ]);
    }
    
    /**
     * Handle agent button clicks - redirect based on user status
     * Requirements: 1.1, 2.1
     */
    public function handleAgentButtonClick(): void {
        // Check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            // Redirect new users to registration page
            $this->redirect('?page=register');
            return;
        }
        
        $currentUser = $this->authService->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('?page=login');
            return;
        }
        
        $userId = $currentUser['id'];
        
        // Check if user can access agent features
        if ($this->agentService->canAccessAgentFeatures($userId)) {
            // User is already an approved agent - redirect to agent dashboard
            $this->redirect('?page=affiliate');
            return;
        }
        
        // Check existing request status
        $statusResult = $this->agentService->getRegistrationStatus($userId);
        
        if ($statusResult['success'] && $statusResult['has_existing_request']) {
            // User has existing request - show processing message
            $this->showProcessingMessage();
            return;
        }
        
        // User can register - show popup
        $this->showRegistrationPopup();
    }
    
    /**
     * AJAX endpoint for getting user agent status
     * Requirements: 2.4, 4.4
     */
    public function getAgentStatus(): void {
        $agentStatus = $this->agentService->getCurrentUserAgentStatus();
        $this->jsonResponse($agentStatus);
    }
    
    // ========== Helper Methods ==========
    
    /**
     * Render popup view for AJAX requests
     */
    private function renderPopupView(string $view, array $data = []): void {
        if ($this->isAjaxRequest()) {
            // For AJAX requests, return HTML content
            ob_start();
            $viewFile = __DIR__ . "/../views/{$view}.php";
            if (file_exists($viewFile)) {
                extract($data);
                include $viewFile;
                $html = ob_get_clean();
                
                $this->jsonResponse([
                    'success' => true,
                    'html' => $html,
                    'show_popup' => true
                ]);
            } else {
                ob_end_clean();
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'View file not found'
                ], 500);
            }
        } else {
            // For regular requests, render full page
            $this->renderView($view, $data);
        }
    }
    
    /**
     * Render view with data
     */
    private function renderView(string $view, array $data = []): void {
        // Set up view data for layout
        $viewData = $data;
        
        // Set layout variables
        $content = __DIR__ . "/../views/{$view}.php";
        $title = $data['page_title'] ?? 'Đăng ký đại lý';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        
        // Set current page for CSS/JS loading
        $currentPage = 'agent';
        $breadcrumbs = [
            ['title' => 'Trang chủ', 'url' => './'],
            ['title' => 'Đăng ký đại lý']
        ];
        
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
        // Clean up the URL
        if (strpos($url, 'http') === 0) {
            // Full URL - use as is
            header("Location: {$url}");
        } elseif (strpos($url, '?') === 0) {
            // Query string URL like ?page=users
            // Get current base URL without query string
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $script = $_SERVER['SCRIPT_NAME'];
            $baseUrl = $protocol . '://' . $host . dirname($script);
            // Remove trailing slash if exists to prevent double slash
            $baseUrl = rtrim($baseUrl, '/');
            header("Location: {$baseUrl}/{$url}");
        } else {
            // Relative path
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/') {
                $basePath = '';
            }
            $basePath = rtrim($basePath, '/');
            header("Location: {$protocol}://{$host}{$basePath}/{$url}");
        }
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
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?array {
        return $this->authService->getCurrentUser();
    }
    
    /**
     * Check authentication middleware
     */
    public function checkAuth(): bool {
        if (!$this->authService->isAuthenticated()) {
            $this->setFlashMessage('error', 'Vui lòng đăng nhập để tiếp tục');
            $this->redirect('?page=login');
            return false;
        }
        
        return true;
    }

    public function requireAffiliate(): bool {
        if (!$this->checkAuth()) {
            return false;
        }
        
        $user = $this->getCurrentUser();
        if (!$user || !in_array($user['role'], ['admin', 'affiliate'])) {
            $this->setFlashMessage('error', 'Bạn không có quyền truy cập trang này');
            $this->redirect('./');
            return false;
        }
        
        return true;
    }

    public function dashboard(): void {
        if (!$this->requireAffiliate()) {
            return;
        }

        $data = [
            'title' => 'Affiliate Dashboard',
            'user' => $this->getCurrentUser()
        ];

        $this->renderView('affiliate/dashboard', $data);
    }
    public function requireAffiliate(): bool {
        if (!$this->checkAuth()) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user || !in_array($user['role'], ['admin', 'affiliate'])) {
            $this->setFlashMessage('error', 'Bạn không có quyền truy cập trang này');
            $this->redirect('./');
            return false;
        }

        return true;
    }

    
    /**
     * Get CSRF token for forms
     */
    public function getCsrfToken(): string {
        return $this->authService->getCsrfToken();
    }
}