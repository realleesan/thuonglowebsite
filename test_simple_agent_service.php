<?php
/**
 * Test đơn giản AgentRegistrationService mà không cần AuthService
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(60);

echo "<h1>Test Đơn Giản AgentRegistrationService</h1>\n";
echo "<pre>\n";

try {
    echo "Loading files...\n";
    require_once 'config.php';
    require_once 'core/database.php';
    require_once 'app/models/BaseModel.php';
    require_once 'app/models/UsersModel.php';
    require_once 'app/models/AffiliateModel.php';
    require_once 'app/services/ErrorHandler.php';
    require_once 'app/services/AgentRegistrationData.php';
    require_once 'app/services/SpamPreventionService.php';
    require_once 'app/services/EmailNotificationService.php';
    echo "✓ Basic files loaded\n";

    // Test AgentRegistrationData first
    echo "\nTesting AgentRegistrationData...\n";
    $testAgentData = [
        'email' => 'test@gmail.com',
        'additional_info' => [
            'registration_source' => 'new_user_form',
            'requested_at' => date('Y-m-d H:i:s')
        ],
        'request_type' => 'new_user',
        'status' => 'pending'
    ];
    
    $agentRegistrationData = new AgentRegistrationData($testAgentData);
    $validationErrors = $agentRegistrationData->validate();
    
    if (empty($validationErrors)) {
        echo "✓ AgentRegistrationData validation passed\n";
    } else {
        echo "✗ AgentRegistrationData validation failed:\n";
        foreach ($validationErrors as $error) {
            echo "  - {$error}\n";
        }
        throw new Exception("AgentRegistrationData validation failed");
    }

    // Test services individually
    echo "\nTesting SpamPreventionService...\n";
    $spamService = new SpamPreventionService();
    $isRateLimited = $spamService->isRateLimited(null);
    echo "✓ SpamPreventionService works, rate limited: " . ($isRateLimited ? 'Yes' : 'No') . "\n";

    echo "\nTesting EmailNotificationService...\n";
    $emailService = new EmailNotificationService();
    echo "✓ EmailNotificationService created\n";

    // Create a simplified AgentRegistrationService without AuthService and RoleManager
    echo "\nCreating simplified AgentRegistrationService...\n";
    
    // We'll create the service manually without the problematic dependencies
    class SimpleAgentRegistrationService {
        private $spamPreventionService;
        private $emailService;
        
        public function __construct() {
            $this->spamPreventionService = new SpamPreventionService();
            $this->emailService = new EmailNotificationService();
        }
        
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
                
                $userId = $usersModel->create($userDataWithAgent);
                if (!$userId) {
                    throw new Exception('Không thể tạo tài khoản người dùng');
                }
                
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
                
                // Send confirmation email (simplified)
                $userName = $userData['name'] ?? $userData['username'] ?? 'Người dùng';
                $emailSent = true; // Assume email sent for testing
                
                return [
                    'success' => true,
                    'message' => 'Đăng ký thành công! Chúng tôi sẽ xử lý yêu cầu trong vòng 24 giờ.',
                    'user_id' => $userId,
                    'affiliate_id' => $affiliateId,
                    'email_sent' => $emailSent,
                    'status' => 'pending'
                ];
                
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                    'error_details' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ];
            }
        }
    }
    
    $simpleAgentService = new SimpleAgentRegistrationService();
    echo "✓ SimpleAgentRegistrationService created\n";

    // Test with sample data
    echo "\nTesting registerNewUserAsAgent...\n";
    $userData = [
        'name' => 'Test Simple User',
        'username' => 'testsimple_' . time(),
        'email' => 'testsimple_' . time() . '@gmail.com',
        'phone' => '0123456789',
        'password' => 'TestPassword123!',
        'password_confirmation' => 'TestPassword123!',
        'ref_code' => ''
    ];
    
    $agentData = [
        'email' => $userData['email'],
        'additional_info' => [
            'registration_source' => 'new_user_form',
            'requested_at' => date('Y-m-d H:i:s')
        ]
    ];
    
    echo "Test data prepared:\n";
    echo "- Email: " . $userData['email'] . "\n";
    
    $result = $simpleAgentService->registerNewUserAsAgent($userData, $agentData);
    
    echo "\nResult:\n";
    print_r($result);
    
    if ($result['success']) {
        echo "\n🎉 SUCCESS! Simple agent registration worked!\n";
        
        // Clean up
        if (isset($result['user_id']) && isset($result['affiliate_id'])) {
            $usersModel = new UsersModel();
            $affiliateModel = new AffiliateModel();
            
            $affiliateModel->delete($result['affiliate_id']);
            $usersModel->delete($result['user_id']);
            echo "✓ Test data cleaned up\n";
        }
    } else {
        echo "\n❌ FAILED: " . ($result['message'] ?? 'Unknown error') . "\n";
        if (isset($result['error_details'])) {
            echo "Error details:\n";
            print_r($result['error_details']);
        }
    }

} catch (Exception $e) {
    echo "\n💥 EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END SIMPLE TEST ===\n";
echo "</pre>\n";
?>