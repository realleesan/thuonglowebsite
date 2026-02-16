<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AgentRegistrationService.php';
require_once __DIR__ . '/../services/EmailNotificationService.php';
require_once __DIR__ . '/../services/AgentErrorHandler.php';
require_once __DIR__ . '/../models/UsersModel.php';
require_once __DIR__ . '/../models/AffiliateModel.php';
require_once __DIR__ . '/../../core/view_init.php';

class AdminController {
    private $authService;
    private $agentRegistrationService;
    private $emailNotificationService;
    private $errorHandler;
    private $usersModel;
    private $affiliateModel;

    public function __construct() {
        $this->authService = new AuthService();
        $this->agentRegistrationService = new AgentRegistrationService();
        $this->emailNotificationService = new EmailNotificationService();
        $this->errorHandler = new AgentErrorHandler();
        $this->usersModel = new UsersModel();
        $this->affiliateModel = new AffiliateModel();
    }

    /**
     * Display admin dashboard
     */
    public function dashboard(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        $data = [
            'title' => 'Admin Dashboard',
            'user' => $this->getCurrentUser()
        ];

        $this->renderView('admin/dashboard', $data);
    }

    /**
     * Manage agent registration requests
     * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5
     */
    public function manageAgentRequests(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            // Get users with role "người dùng" and status "hoạt động" for users tab
            $activeUsers = $this->usersModel->getUsersByRoleAndStatus('user', 'active');
            
            // Get agent registration requests with status "chờ duyệt" for agents tab
            $pendingAgentRequests = $this->usersModel->getUsersByAgentStatus('pending');
            
            // Get approved agents for agents tab
            $approvedAgents = $this->usersModel->getUsersByAgentStatus('approved');

            $data = [
                'title' => 'Quản lý đại lý',
                'activeUsers' => $activeUsers,
                'pendingAgentRequests' => $pendingAgentRequests,
                'approvedAgents' => $approvedAgents,
                'user' => $this->getCurrentUser()
            ];

            $this->renderView('admin/agent_management', $data);
        } catch (Exception $e) {
            error_log("Error in manageAgentRequests: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra khi tải danh sách đại lý');
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Approve agent registration request
     * Requirements: 3.3, 3.4, 3.5
     */
    public function approveAgentRequest(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 405);
            return;
        }

        $requestId = $_POST['request_id'] ?? null;
        if (!$requestId) {
            $this->jsonResponse(['error' => 'Request ID is required'], 400);
            return;
        }

        try {
            // Get user by ID
            $user = $this->usersModel->getUserById($requestId);
            if (!$user) {
                $this->jsonResponse(['error' => 'User not found'], 404);
                return;
            }

            // Check if user has pending agent request
            if ($user['agent_request_status'] !== 'pending') {
                $this->jsonResponse(['error' => 'No pending agent request found'], 400);
                return;
            }

            // Update agent status to approved
            $success = $this->usersModel->updateAgentStatus($requestId, 'approved');
            if (!$success) {
                throw new Exception('Failed to update agent status');
            }

            // Update user role to agent
            $roleUpdated = $this->usersModel->updateUserRole($requestId, 'agent');
            if (!$roleUpdated) {
                throw new Exception('Failed to update user role');
            }

            // Create affiliate record and approve it
            $affiliateId = $this->affiliateModel->createAffiliate($requestId);
            if ($affiliateId) {
                $this->affiliateModel->approve($affiliateId, $this->getCurrentUser()['id']);
            }

            // Send approval notification email
            $this->emailNotificationService->sendApprovalNotification($user['email'], $user['name']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Agent request approved successfully'
            ]);

        } catch (Exception $e) {
            error_log("Error in approveAgentRequest: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to approve agent request'], 500);
        }
    }

    /**
     * Update agent status (approve/reject)
     * Requirements: 3.3, 3.5
     */
    public function updateAgentStatus(): void {
        try {
            if (!$this->requireAdmin()) {
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->jsonResponse(['error' => 'Invalid request method'], 405);
                return;
            }

            $requestId = $_POST['request_id'] ?? null;
            $status = $_POST['status'] ?? null;

            if (!$requestId || !$status) {
                $response = $this->errorHandler->handleValidationError([
                    'request_id' => !$requestId ? 'Request ID is required' : null,
                    'status' => !$status ? 'Status is required' : null
                ], [
                    'admin_id' => $this->getCurrentUser()['id'] ?? null,
                    'action' => 'update_agent_status'
                ]);
                $this->jsonResponse($response, $response['code']);
                return;
            }

            if (!in_array($status, ['approved', 'rejected'])) {
                $response = $this->errorHandler->handleValidationError(['status' => 'Invalid status'], [
                    'admin_id' => $this->getCurrentUser()['id'] ?? null,
                    'provided_status' => $status
                ]);
                $this->jsonResponse($response, $response['code']);
                return;
            }

            // Get user by ID
            $user = $this->usersModel->getUserById($requestId);
            if (!$user) {
                $this->jsonResponse(['error' => 'User not found'], 404);
                return;
            }

            // Check if user has pending agent request
            if ($user['agent_request_status'] !== 'pending') {
                $this->jsonResponse(['error' => 'No pending agent request found'], 400);
                return;
            }

            // Update agent status
            $success = $this->usersModel->updateAgentStatus($requestId, $status);
            if (!$success) {
                $response = $this->errorHandler->handleDatabaseError('Failed to update agent status', [
                    'user_id' => $requestId,
                    'status' => $status,
                    'admin_id' => $this->getCurrentUser()['id'] ?? null
                ]);
                $this->jsonResponse($response, $response['code']);
                return;
            }

            if ($status === 'approved') {
                try {
                    // Update user role to agent
                    $roleUpdated = $this->usersModel->updateUserRole($requestId, 'agent');
                    if (!$roleUpdated) {
                        throw new Exception('Failed to update user role');
                    }

                    // Create affiliate record and approve it
                    $affiliateId = $this->affiliateModel->createAffiliate($requestId);
                    if ($affiliateId) {
                        $this->affiliateModel->approve($affiliateId, $this->getCurrentUser()['id']);
                    }

                    // Send approval notification email
                    try {
                        $this->emailNotificationService->sendApprovalNotification($user['email'], $user['name']);
                    } catch (Exception $emailError) {
                        // Log email error but don't fail the operation
                        $this->errorHandler->handleEmailError($emailError, [
                            'user_id' => $requestId,
                            'email' => $user['email'],
                            'notification_type' => 'approval'
                        ]);
                    }

                    $message = 'Agent request approved successfully';
                    
                    $this->errorHandler->logSuccess('agent_approval_completed', [
                        'user_id' => $requestId,
                        'admin_id' => $this->getCurrentUser()['id'] ?? null,
                        'user_email' => $user['email']
                    ]);
                    
                } catch (Exception $e) {
                    $response = $this->errorHandler->handleAdminError($e, [
                        'user_id' => $requestId,
                        'admin_id' => $this->getCurrentUser()['id'] ?? null,
                        'operation' => 'approve_agent'
                    ]);
                    $this->jsonResponse($response, $response['code']);
                    return;
                }
            } else {
                $message = 'Agent request rejected successfully';
                
                $this->errorHandler->logSuccess('agent_rejection_completed', [
                    'user_id' => $requestId,
                    'admin_id' => $this->getCurrentUser()['id'] ?? null,
                    'user_email' => $user['email']
                ]);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => $message
            ]);

        } catch (Exception $e) {
            $response = $this->errorHandler->handleAdminError($e, [
                'admin_id' => $this->getCurrentUser()['id'] ?? null,
                'action' => 'update_agent_status',
                'post_data' => array_keys($_POST)
            ]);
            $this->jsonResponse($response, $response['code']);
        }
    }

    /**
     * Check if current user is admin
     */
    private function requireAdmin(): bool {
        if (!$this->authService->isLoggedIn()) {
            $this->redirect('/auth/login');
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user || $user['role'] !== 'admin') {
            $this->setFlashMessage('error', 'Access denied. Admin privileges required.');
            $this->redirect('/');
            return false;
        }

        return true;
    }

    /**
     * Get current logged in user
     */
    private function getCurrentUser(): ?array {
        return $this->authService->getCurrentUser();
    }

    /**
     * Render view with layout
     */
    private function renderView(string $view, array $data = []): void {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: $view");
        }

        // Extract data for view
        extract($data);
        
        // Start output buffering
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        
        // Include admin layout
        $layoutPath = __DIR__ . '/../views/_layout/admin_master.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Redirect to URL
     */
    private function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    /**
     * Set flash message
     */
    private function setFlashMessage(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_' . $type] = $message;
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}