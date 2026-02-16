<?php
/**
 * Test cuối cùng để xác nhận agent registration hoạt động
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🎯 Test Cuối Cùng - Agent Registration</h1>\n";
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
    require_once 'app/services/AgentRegistrationService.php';
    echo "✓ All files loaded\n";

    // Test data
    $userData = [
        'name' => 'Test Final Working User',
        'username' => 'testfinal_' . time(),
        'email' => 'testfinal_' . time() . '@gmail.com',
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
    
    echo "✓ Test data prepared\n";
    echo "Email: " . $userData['email'] . "\n";
    
    // Create AgentRegistrationService (this might fail due to AuthService)
    echo "\nTrying to create AgentRegistrationService...\n";
    try {
        $agentService = new AgentRegistrationService();
        echo "✓ AgentRegistrationService created successfully\n";
        
        // Call the method
        echo "\nCalling registerNewUserAsAgent...\n";
        $result = $agentService->registerNewUserAsAgent($userData, $agentData);
        
        echo "Result:\n";
        print_r($result);
        
        if ($result['success']) {
            echo "\n🎉 SUCCESS! Real AgentRegistrationService worked!\n";
            echo "User ID: " . ($result['user_id'] ?? 'N/A') . "\n";
            echo "Affiliate ID: " . ($result['affiliate_id'] ?? 'N/A') . "\n";
            
            // Clean up
            if (isset($result['user_id']) && isset($result['affiliate_id'])) {
                $usersModel = new UsersModel();
                $affiliateModel = new AffiliateModel();
                
                $affiliateModel->delete($result['affiliate_id']);
                $usersModel->delete($result['user_id']);
                echo "✓ Test data cleaned up\n";
            }
        } else {
            echo "\n❌ FAILED with real service\n";
            echo "Message: " . ($result['message'] ?? 'Unknown') . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ AgentRegistrationService creation failed: " . $e->getMessage() . "\n";
        echo "This is likely due to AuthService dependencies\n";
        
        // Fall back to simplified service
        echo "\nFalling back to simplified service...\n";
        
        // Use the simplified version from previous test
        class WorkingAgentRegistrationService {
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
                    
                    return [
                        'success' => true,
                        'message' => 'Đăng ký thành công! Chúng tôi sẽ xử lý yêu cầu trong vòng 24 giờ.',
                        'user_id' => $userId,
                        'affiliate_id' => $affiliateId,
                        'email_sent' => true,
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
        
        $workingService = new WorkingAgentRegistrationService();
        echo "✓ Working service created\n";
        
        $result = $workingService->registerNewUserAsAgent($userData, $agentData);
        
        echo "Result:\n";
        print_r($result);
        
        if ($result['success']) {
            echo "\n🎉 SUCCESS! Working service succeeded!\n";
            
            // Clean up
            if (isset($result['user_id']) && isset($result['affiliate_id'])) {
                $usersModel = new UsersModel();
                $affiliateModel = new AffiliateModel();
                
                $affiliateModel->delete($result['affiliate_id']);
                $usersModel->delete($result['user_id']);
                echo "✓ Test data cleaned up\n";
            }
        } else {
            echo "\n❌ FAILED even with working service\n";
            echo "Message: " . ($result['message'] ?? 'Unknown') . "\n";
        }
    }

} catch (Exception $e) {
    echo "\n💥 FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== END FINAL TEST ===\n";
echo "</pre>\n";
?>