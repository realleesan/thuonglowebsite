<?php
/**
 * AgentRegistrationService - Service chính xử lý logic đăng ký đại lý
 * Triển khai logic chính cho đăng ký đại lý (cả new user và existing user)
 * Requirements: 1.3, 2.2, 2.3, 4.1, 4.2
 */

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/AgentRegistrationData.php';
require_once __DIR__ . '/SpamPreventionService.php';
require_once __DIR__ . '/EmailNotificationService.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/RoleManager.php';
require_once __DIR__ . '/../models/UsersModel.php';
require_once __DIR__ . '/../models/AffiliateModel.php';

class AgentRegistrationService extends BaseService {
    private SpamPreventionService $spamPreventionService;
    private EmailNotificationService $emailService;
    private AuthService $authService;
    private RoleManager $roleManager;
    
    public function __construct(?ErrorHandler $errorHandler = null) {
        parent::__construct($errorHandler, 'agent-registration');
        $this->spamPreventionService = new SpamPreventionService();
        $this->emailService = new EmailNotificationService();
        $this->authService = new AuthService();
        $this->roleManager = new RoleManager();
    }
    
    /**
     * Đăng ký đại lý cho người dùng mới
     * Requirements: 1.3, 1.4
     */
    public function registerNewUserAsAgent(array $userData, array $agentData): array {
        try {
            // Validate agent data
            $agentRegistrationData = new AgentRegistrationData(array_merge($agentData, [
                'request_type' => 'new_user',
                'status' => 'pending'
            ]));
            
            $validationErrors = $agentRegistrationData->validate();
            if (!empty($validationErrors)) {
                return [
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validationErrors
                ];
            }
            
            // Check rate limiting for new users (IP-based)
            if ($this->spamPreventionService->isRateLimited(null)) {
                return [
                    'success' => false,
                    'message' => 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau.',
                    'rate_limited' => true
                ];
            }
            
            // Create user account first
            $usersModel = new UsersModel();
            if (!$usersModel) {
                throw new Exception('Không thể khởi tạo UsersModel');
            }
            
            // Prepare user data with agent request status
            $userDataWithAgent = array_merge($userData, [
                'agent_request_status' => 'pending',
                'agent_request_date' => date('Y-m-d H:i:s')
            ]);
            
            $user = $usersModel->register($userDataWithAgent);
            if (!$user || !isset($user['id'])) {
                throw new Exception('Không thể tạo tài khoản người dùng');
            }
            
            $userId = $user['id'];
            
            // Update agent registration data with user ID
            $agentRegistrationData->userId = $userId;
            $agentRegistrationData->submittedAt = date('Y-m-d H:i:s');
            
            // Create affiliate record
            $affiliateModel = new AffiliateModel();
            if (!$affiliateModel) {
                throw new Exception('Không thể khởi tạo AffiliateModel');
            }
            
            $affiliateData = [
                'user_id' => $userId,
                'referral_code' => 'REF' . str_pad($userId, 4, '0', STR_PAD_LEFT),
                'commission_rate' => 10.00,
                'total_sales' => 0.00,
                'total_commission' => 0.00,
                'paid_commission' => 0.00,
                'pending_commission' => 0.00,
                'status' => 'pending',
                'additional_info' => json_encode($agentRegistrationData->additionalInfo),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $affiliateId = $affiliateModel->create($affiliateData);
            if (!$affiliateId) {
                // Rollback user creation if affiliate creation fails
                $usersModel->delete($userId);
                throw new Exception('Không thể tạo bản ghi đại lý');
            }
            
            // Record submission for rate limiting
            $this->spamPreventionService->recordSubmission($userId);
            
            // Send confirmation email
            $userName = $userData['name'] ?? $userData['username'] ?? 'Người dùng';
            $emailSent = $this->emailService->sendRegistrationConfirmation(
                $agentRegistrationData->email, 
                $userName
            );
            
            return [
                'success' => true,
                'message' => 'Đăng ký thành công! Chúng tôi sẽ xử lý yêu cầu trong vòng 24 giờ.',
                'user_id' => $userId,
                'user' => $user,
                'affiliate_id' => $affiliateId,
                'email_sent' => $emailSent,
                'status' => 'pending'
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, [
                'method' => 'registerNewUserAsAgent',
                'user_data' => array_keys($userData),
                'agent_data' => array_keys($agentData)
            ]);
        }
    }
    
    /**
     * Nâng cấp người dùng hiện tại thành đại lý
     * Requirements: 2.2, 2.3
     */
    public function upgradeExistingUserToAgent(int $userId, array $agentData): array {
        try {
            // Check if user exists
            $usersModel = new UsersModel();
            if (!$usersModel) {
                throw new Exception('Không thể khởi tạo UsersModel');
            }
            
            $user = $usersModel->find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ];
            }
            
            // Check for existing pending request
            if ($this->spamPreventionService->hasExistingPendingRequest($userId)) {
                return [
                    'success' => false,
                    'message' => 'Bạn đã có yêu cầu đăng ký đại lý đang chờ xử lý',
                    'existing_request' => true
                ];
            }
            
            // Check rate limiting
            if ($this->spamPreventionService->isRateLimited($userId)) {
                return [
                    'success' => false,
                    'message' => 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau.',
                    'rate_limited' => true
                ];
            }
            
            // Validate agent data
            $agentRegistrationData = new AgentRegistrationData(array_merge($agentData, [
                'user_id' => $userId,
                'request_type' => 'existing_user',
                'status' => 'pending'
            ]));
            
            $validationErrors = $agentRegistrationData->validate();
            if (!empty($validationErrors)) {
                return [
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validationErrors
                ];
            }
            
            // Update user with agent request status
            $updateData = [
                'agent_request_status' => 'pending',
                'agent_request_date' => date('Y-m-d H:i:s')
            ];
            
            $userUpdated = $usersModel->update($userId, $updateData);
            if (!$userUpdated) {
                throw new Exception('Không thể cập nhật trạng thái người dùng');
            }
            
            // Create affiliate record
            $affiliateModel = new AffiliateModel();
            if (!$affiliateModel) {
                throw new Exception('Không thể khởi tạo AffiliateModel');
            }
            
            $affiliateData = [
                'user_id' => $userId,
                'status' => 'pending',
                'additional_info' => json_encode($agentRegistrationData->additionalInfo),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $affiliateId = $affiliateModel->create($affiliateData);
            if (!$affiliateId) {
                // Rollback user update if affiliate creation fails
                $usersModel->update($userId, ['agent_request_status' => 'none']);
                throw new Exception('Không thể tạo bản ghi đại lý');
            }
            
            // Record submission for rate limiting
            $this->spamPreventionService->recordSubmission($userId);
            
            // Send confirmation email
            $userName = $user['name'] ?? $user['username'] ?? 'Người dùng';
            $emailSent = $this->emailService->sendRegistrationConfirmation(
                $agentRegistrationData->email, 
                $userName
            );
            
            return [
                'success' => true,
                'message' => 'Yêu cầu nâng cấp thành đại lý đã được gửi! Chúng tôi sẽ xử lý trong vòng 24 giờ.',
                'user_id' => $userId,
                'affiliate_id' => $affiliateId,
                'email_sent' => $emailSent,
                'status' => 'pending'
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, [
                'method' => 'upgradeExistingUserToAgent',
                'user_id' => $userId,
                'agent_data' => array_keys($agentData)
            ]);
        }
    }
    
    /**
     * Kiểm tra yêu cầu đăng ký hiện tại của user
     * Requirements: 4.1, 4.2
     */
    public function checkExistingRequest(int $userId): ?array {
        try {
            $usersModel = new UsersModel();
            if (!$usersModel) {
                return null;
            }
            
            $user = $usersModel->find($userId);
            if (!$user || !isset($user['agent_request_status'])) {
                return null;
            }
            
            if ($user['agent_request_status'] === 'none') {
                return null;
            }
            
            // Get affiliate record for additional details
            $affiliateModel = new AffiliateModel();
            $affiliate = null;
            if ($affiliateModel) {
                $affiliates = $affiliateModel->where('user_id', $userId)->get();
                if (!empty($affiliates)) {
                    $affiliate = $affiliates[0]; // Get the most recent one
                }
            }
            
            return [
                'user_id' => $userId,
                'status' => $user['agent_request_status'],
                'request_date' => $user['agent_request_date'],
                'approved_date' => $user['agent_approved_date'] ?? null,
                'affiliate_id' => $affiliate['id'] ?? null,
                'additional_info' => $affiliate ? json_decode($affiliate['additional_info'], true) : null
            ];
            
        } catch (Exception $e) {
            $this->handleError($e, [
                'method' => 'checkExistingRequest',
                'user_id' => $userId
            ]);
            return null;
        }
    }
    
    /**
     * Ngăn chặn spam submission
     * Requirements: 4.1, 4.2, 4.3
     */
    public function preventSpamSubmission(int $userId): bool {
        // Check for existing pending request
        if ($this->spamPreventionService->hasExistingPendingRequest($userId)) {
            return true; // Prevent submission
        }
        
        // Check rate limiting
        if ($this->spamPreventionService->isRateLimited($userId)) {
            return true; // Prevent submission
        }
        
        return false; // Allow submission
    }
    
    /**
     * Lấy trạng thái đăng ký của user
     */
    public function getRegistrationStatus(int $userId): array {
        try {
            $existingRequest = $this->checkExistingRequest($userId);
            $rateLimitStatus = $this->spamPreventionService->getRateLimitStatus($userId);
            
            return [
                'success' => true,
                'has_existing_request' => $existingRequest !== null,
                'request_details' => $existingRequest,
                'rate_limit_status' => $rateLimitStatus,
                'can_submit' => !$this->preventSpamSubmission($userId)
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, [
                'method' => 'getRegistrationStatus',
                'user_id' => $userId
            ]);
        }
    }
    
    /**
     * Phê duyệt yêu cầu đăng ký đại lý (dành cho admin)
     * Requirements: 3.3, 3.4, 3.5
     */
    public function approveAgentRequest(int $userId): array {
        try {
            $usersModel = new UsersModel();
            $affiliateModel = new AffiliateModel();
            
            if (!$usersModel || !$affiliateModel) {
                throw new Exception('Không thể khởi tạo models');
            }
            
            // Get user and check current status
            $user = $usersModel->findById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ];
            }
            
            if ($user['agent_request_status'] !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Yêu cầu không ở trạng thái chờ duyệt'
                ];
            }
            
            // Update user status
            $userUpdateData = [
                'agent_request_status' => 'approved',
                'agent_approved_date' => date('Y-m-d H:i:s'),
                'role' => 'đại lý'
            ];
            
            $userUpdated = $usersModel->update($userId, $userUpdateData);
            if (!$userUpdated) {
                throw new Exception('Không thể cập nhật trạng thái người dùng');
            }
            
            // Update affiliate status
            $affiliate = $affiliateModel->getByUserId($userId);
            if ($affiliate) {
                $affiliateUpdateData = [
                    'status' => 'hoạt động',
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $affiliateModel->update($affiliate['id'], $affiliateUpdateData);
            }
            
            // Send approval notification email
            $userName = $user['name'] ?? $user['username'] ?? 'Người dùng';
            $userEmail = $user['email'] ?? '';
            
            $emailSent = false;
            if ($userEmail) {
                $emailSent = $this->emailService->sendApprovalNotification($userEmail, $userName);
            }
            
            return [
                'success' => true,
                'message' => 'Yêu cầu đăng ký đại lý đã được phê duyệt',
                'user_id' => $userId,
                'email_sent' => $emailSent
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, [
                'method' => 'approveAgentRequest',
                'user_id' => $userId
            ]);
        }
    }
    
    /**
     * Từ chối yêu cầu đăng ký đại lý (dành cho admin)
     */
    public function rejectAgentRequest(int $userId, string $reason = ''): array {
        try {
            $usersModel = new UsersModel();
            $affiliateModel = new AffiliateModel();
            
            if (!$usersModel || !$affiliateModel) {
                throw new Exception('Không thể khởi tạo models');
            }
            
            // Get user and check current status
            $user = $usersModel->findById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ];
            }
            
            if ($user['agent_request_status'] !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Yêu cầu không ở trạng thái chờ duyệt'
                ];
            }
            
            // Update user status
            $userUpdateData = [
                'agent_request_status' => 'rejected',
                'agent_approved_date' => date('Y-m-d H:i:s')
            ];
            
            $userUpdated = $usersModel->update($userId, $userUpdateData);
            if (!$userUpdated) {
                throw new Exception('Không thể cập nhật trạng thái người dùng');
            }
            
            // Update affiliate status
            $affiliate = $affiliateModel->getByUserId($userId);
            if ($affiliate) {
                $affiliateUpdateData = [
                    'status' => 'rejected',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'additional_info' => json_encode(array_merge(
                        json_decode($affiliate['additional_info'] ?? '{}', true),
                        ['rejection_reason' => $reason]
                    ))
                ];
                
                $affiliateModel->update($affiliate['id'], $affiliateUpdateData);
            }
            
            return [
                'success' => true,
                'message' => 'Yêu cầu đăng ký đại lý đã được từ chối',
                'user_id' => $userId,
                'reason' => $reason
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, [
                'method' => 'rejectAgentRequest',
                'user_id' => $userId,
                'reason' => $reason
            ]);
        }
    }
    
    /**
     * Kiểm tra quyền truy cập agent features cho user hiện tại
     * Requirements: 6.2
     */
    public function canAccessAgentFeatures(?int $userId = null): bool {
        try {
            // If no userId provided, get current user
            if ($userId === null) {
                $currentUser = $this->authService->getCurrentUser();
                if (!$currentUser) {
                    return false;
                }
                $userId = $currentUser['id'];
            }
            
            $usersModel = new UsersModel();
            if (!$usersModel) {
                return false;
            }
            
            $user = $usersModel->findById($userId);
            if (!$user) {
                return false;
            }
            
            // Check if user has agent role or approved agent request
            return $this->roleManager->hasRole($user, 'affiliate') || 
                   (isset($user['agent_request_status']) && $user['agent_request_status'] === 'approved');
                   
        } catch (Exception $e) {
            $this->handleError($e, [
                'method' => 'canAccessAgentFeatures',
                'user_id' => $userId
            ]);
            return false;
        }
    }
    
    /**
     * Lấy trạng thái agent cho user hiện tại
     * Requirements: 6.2
     */
    public function getCurrentUserAgentStatus(): array {
        try {
            $currentUser = $this->authService->getCurrentUser();
            if (!$currentUser) {
                return [
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập',
                    'authenticated' => false
                ];
            }
            
            $userId = $currentUser['id'];
            $registrationStatus = $this->getRegistrationStatus($userId);
            $canAccess = $this->canAccessAgentFeatures($userId);
            
            return [
                'success' => true,
                'authenticated' => true,
                'user_id' => $userId,
                'user_role' => $currentUser['role'] ?? 'user',
                'can_access_agent_features' => $canAccess,
                'registration_status' => $registrationStatus
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, [
                'method' => 'getCurrentUserAgentStatus'
            ]);
        }
    }
    
    /**
     * Kiểm tra quyền admin để quản lý agent requests
     * Requirements: 6.2
     */
    public function canManageAgentRequests(?int $userId = null): bool {
        try {
            // If no userId provided, get current user
            if ($userId === null) {
                $currentUser = $this->authService->getCurrentUser();
                if (!$currentUser) {
                    return false;
                }
                $user = $currentUser;
            } else {
                $usersModel = new UsersModel();
                if (!$usersModel) {
                    return false;
                }
                $user = $usersModel->findById($userId);
                if (!$user) {
                    return false;
                }
            }
            
            // Check if user has admin role and specific permission
            return $this->roleManager->hasRole($user, 'admin') && 
                   $this->roleManager->hasPermission($user, 'admin.affiliates.edit');
                   
        } catch (Exception $e) {
            $this->handleError($e, [
                'method' => 'canManageAgentRequests',
                'user_id' => $userId
            ]);
            return false;
        }
    }
    
    /**
     * Tích hợp với AuthService để tạo user mới với agent role
     * Requirements: 6.2
     */
    public function createUserWithAgentRequest(array $userData, array $agentData): array {
        try {
            // Use AuthService to register user
            $registrationResult = $this->authService->register($userData);
            
            if (!$registrationResult['success']) {
                return $registrationResult;
            }
            
            $userId = $registrationResult['user_id'];
            
            // Now upgrade to agent
            $agentResult = $this->upgradeExistingUserToAgent($userId, $agentData);
            
            if (!$agentResult['success']) {
                // If agent registration fails, the user account is still created
                // This is intentional - user can try agent registration later
                return [
                    'success' => true,
                    'message' => 'Tài khoản đã được tạo nhưng có lỗi khi đăng ký đại lý. Bạn có thể thử lại sau.',
                    'user_created' => true,
                    'agent_registration_failed' => true,
                    'user_id' => $userId,
                    'agent_error' => $agentResult['message']
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Tài khoản và yêu cầu đăng ký đại lý đã được tạo thành công!',
                'user_created' => true,
                'agent_registration_success' => true,
                'user_id' => $userId,
                'affiliate_id' => $agentResult['affiliate_id'],
                'email_sent' => $agentResult['email_sent']
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, [
                'method' => 'createUserWithAgentRequest',
                'user_data' => array_keys($userData),
                'agent_data' => array_keys($agentData)
            ]);
        }
    }
    
    /**
     * Validate user session và permissions cho agent operations
     * Requirements: 6.2
     */
    public function validateAgentOperation(string $operation): array {
        try {
            // Check if user is authenticated
            if (!$this->authService->isAuthenticated()) {
                return [
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để thực hiện thao tác này',
                    'redirect' => '?page=login'
                ];
            }
            
            $currentUser = $this->authService->getCurrentUser();
            if (!$currentUser) {
                return [
                    'success' => false,
                    'message' => 'Không thể xác thực người dùng',
                    'redirect' => '?page=login'
                ];
            }
            
            // Check specific operation permissions
            switch ($operation) {
                case 'register':
                    // Anyone can register for agent
                    return ['success' => true, 'user' => $currentUser];
                    
                case 'manage':
                    // Only admins can manage agent requests
                    if (!$this->canManageAgentRequests()) {
                        return [
                            'success' => false,
                            'message' => 'Bạn không có quyền quản lý yêu cầu đại lý',
                            'redirect' => $this->roleManager->getRedirectPath($currentUser)
                        ];
                    }
                    return ['success' => true, 'user' => $currentUser];
                    
                case 'access':
                    // Check if user can access agent features
                    if (!$this->canAccessAgentFeatures()) {
                        return [
                            'success' => false,
                            'message' => 'Bạn chưa được phê duyệt làm đại lý',
                            'show_registration' => true
                        ];
                    }
                    return ['success' => true, 'user' => $currentUser];
                    
                default:
                    return [
                        'success' => false,
                        'message' => 'Thao tác không hợp lệ'
                    ];
            }
            
        } catch (Exception $e) {
            return $this->handleError($e, [
                'method' => 'validateAgentOperation',
                'operation' => $operation
            ]);
        }
    }
}