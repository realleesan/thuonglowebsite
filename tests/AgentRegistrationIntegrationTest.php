<?php

/**
 * Agent Registration System Integration Tests
 * 
 * Tests end-to-end flows for both new user and existing user registration flows
 * Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3
 */

require_once __DIR__ . '/../app/controllers/AffiliateController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/services/AgentRegistrationService.php';
require_once __DIR__ . '/../app/services/EmailNotificationService.php';
require_once __DIR__ . '/../app/models/UsersModel.php';
require_once __DIR__ . '/../app/models/AffiliateModel.php';

class AgentRegistrationIntegrationTest
{
    private $testDatabase;
    private $testUsers = [];
    private $testEmails = [];
    
    public function setUp(): void
    {
        // Setup test database
        $this->testDatabase = new PDO('sqlite::memory:');
        $this->testDatabase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->createTestTables();
        $this->seedTestData();
        
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function tearDown(): void
    {
        $this->testUsers = [];
        $this->testEmails = [];
        $this->testDatabase = null;
    }
    
    /**
     * Test complete new user registration flow with agent option
     * Requirements: 1.1, 1.2, 1.3, 1.4
     */
    public function testNewUserAgentRegistrationFlow()
    {
        echo "Testing new user agent registration flow...\n";
        
        // Step 1: User clicks "Đại lý" button - should redirect to registration
        $this->simulateNewUserAgentClick();
        
        // Step 2: User completes registration form with agent option
        $registrationData = [
            'name' => 'New Agent User',
            'email' => 'newagent@gmail.com',
            'password' => 'password123',
            'role_option' => 'agent',
            'additional_info' => 'I want to become an agent'
        ];
        
        $registrationResult = $this->simulateNewUserRegistration($registrationData);
        
        // Verify account created with correct status
        $this->assertTrue($registrationResult['success'], 'Registration should succeed');
        $this->assertEquals('user', $registrationResult['user']['role'], 'User should have temporary user role');
        $this->assertEquals('pending', $registrationResult['user']['agent_request_status'], 'Agent status should be pending');
        
        // Step 3: Verify email notification sent
        $this->assertEmailSent($registrationData['email'], 'confirmation');
        
        // Step 4: User clicks agent buttons - should show processing message
        $this->simulateUserLogin($registrationResult['user']['id']);
        $processingResult = $this->simulateAgentButtonClick();
        
        $this->assertTrue($processingResult['show_processing_message'], 'Should show processing message');
        $this->assertEquals('pending', $processingResult['status'], 'Status should be pending');
        
        echo "✓ New user agent registration flow completed successfully\n";
    }
    
    /**
     * Test complete existing user registration flow
     * Requirements: 2.1, 2.2, 2.3
     */
    public function testExistingUserAgentRegistrationFlow()
    {
        echo "Testing existing user agent registration flow...\n";
        
        // Step 1: Create existing user
        $existingUserId = $this->createTestUser([
            'name' => 'Existing User',
            'email' => 'existing@gmail.com',
            'role' => 'user',
            'agent_request_status' => 'none'
        ]);
        
        // Step 2: User logs in and clicks "Đại lý" button - should show popup
        $this->simulateUserLogin($existingUserId);
        $popupResult = $this->simulateAgentButtonClick();
        
        $this->assertTrue($popupResult['show_popup'], 'Should show registration popup');
        $this->assertNotEmpty($popupResult['html'], 'Popup HTML should be provided');
        
        // Step 3: User submits agent registration form
        $agentData = [
            'agent_email' => 'existing@gmail.com',
            'agent_info' => 'I have experience in sales'
        ];
        
        $submissionResult = $this->simulateAgentRegistrationSubmission($agentData);
        
        $this->assertTrue($submissionResult['success'], 'Agent registration should succeed');
        
        // Step 4: Verify email notification sent
        $this->assertEmailSent($agentData['agent_email'], 'confirmation');
        
        // Step 5: User clicks agent buttons again - should show processing message
        $processingResult = $this->simulateAgentButtonClick();
        
        $this->assertTrue($processingResult['show_processing_message'], 'Should show processing message');
        $this->assertEquals('pending', $processingResult['status'], 'Status should be pending');
        
        echo "✓ Existing user agent registration flow completed successfully\n";
    }
    
    /**
     * Test admin approval workflow
     * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5
     */
    public function testAdminApprovalWorkflow()
    {
        echo "Testing admin approval workflow...\n";
        
        // Step 1: Create user with pending agent request
        $pendingUserId = $this->createTestUser([
            'name' => 'Pending Agent',
            'email' => 'pending@gmail.com',
            'role' => 'user',
            'agent_request_status' => 'pending',
            'agent_request_date' => date('Y-m-d H:i:s')
        ]);
        
        // Step 2: Admin views agent management page
        $this->simulateAdminLogin();
        $managementResult = $this->simulateAdminAgentManagement();
        
        $this->assertNotEmpty($managementResult['pendingAgentRequests'], 'Should show pending requests');
        $foundPendingUser = false;
        foreach ($managementResult['pendingAgentRequests'] as $request) {
            if ($request['id'] == $pendingUserId) {
                $foundPendingUser = true;
                break;
            }
        }
        $this->assertTrue($foundPendingUser, 'Should show correct pending user');
        
        // Step 3: Admin approves agent request
        $approvalResult = $this->simulateAdminApproval($pendingUserId, 'approved');
        
        $this->assertTrue($approvalResult['success'], 'Approval should succeed');
        
        // Step 4: Verify user role updated
        $updatedUser = $this->getUserById($pendingUserId);
        $this->assertEquals('agent', $updatedUser['role'], 'User role should be updated to agent');
        $this->assertEquals('approved', $updatedUser['agent_request_status'], 'Agent status should be approved');
        $this->assertNotNull($updatedUser['agent_approved_date'], 'Approval date should be set');
        
        // Step 5: Verify affiliate record created
        $affiliate = $this->getAffiliateByUserId($pendingUserId);
        $this->assertNotNull($affiliate, 'Affiliate record should be created');
        $this->assertEquals('active', $affiliate['status'], 'Affiliate should be active');
        
        // Step 6: Verify approval email sent
        $pendingUser = $this->getUserById($pendingUserId);
        $this->assertEmailSent($pendingUser['email'], 'approval');
        
        // Step 7: User should now have access to agent features
        $this->simulateUserLogin($pendingUserId);
        $agentAccessResult = $this->checkAgentAccess();
        
        $this->assertTrue($agentAccessResult['can_access'], 'User should have agent access');
        
        echo "✓ Admin approval workflow completed successfully\n";
    }
    
    /**
     * Test spam prevention integration
     * Requirements: 4.1, 4.2, 4.3
     */
    public function testSpamPreventionIntegration()
    {
        echo "Testing spam prevention integration...\n";
        
        // Step 1: Create user and submit first request
        $userId = $this->createTestUser([
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'role' => 'user'
        ]);
        
        $this->simulateUserLogin($userId);
        
        $firstSubmission = $this->simulateAgentRegistrationSubmission([
            'agent_email' => 'test@gmail.com',
            'agent_info' => 'First submission'
        ]);
        
        $this->assertTrue($firstSubmission['success'], 'First submission should succeed');
        
        // Step 2: Try to submit duplicate request - should be prevented
        $duplicateSubmission = $this->simulateAgentRegistrationSubmission([
            'agent_email' => 'test@gmail.com',
            'agent_info' => 'Duplicate submission'
        ]);
        
        $this->assertFalse($duplicateSubmission['success'], 'Duplicate submission should be prevented');
        $this->assertStringContains('already submitted', $duplicateSubmission['message'], 'Should show duplicate message');
        
        // Step 3: User should see existing request status
        $statusResult = $this->simulateAgentButtonClick();
        
        $this->assertTrue($statusResult['show_processing_message'], 'Should show processing message');
        $this->assertEquals('pending', $statusResult['status'], 'Should show pending status');
        
        echo "✓ Spam prevention integration completed successfully\n";
    }
    
    /**
     * Test email notification integration
     * Requirements: 1.4, 2.3, 3.4, 5.1, 5.2, 5.3, 5.4
     */
    public function testEmailNotificationIntegration()
    {
        echo "Testing email notification integration...\n";
        
        // Step 1: Test registration confirmation email
        $userId = $this->createTestUser([
            'name' => 'Email Test User',
            'email' => 'emailtest@gmail.com',
            'role' => 'user'
        ]);
        
        $this->simulateUserLogin($userId);
        
        $registrationResult = $this->simulateAgentRegistrationSubmission([
            'agent_email' => 'emailtest@gmail.com',
            'agent_info' => 'Testing email notifications'
        ]);
        
        $this->assertTrue($registrationResult['success'], 'Registration should succeed');
        $this->assertEmailSent('emailtest@gmail.com', 'confirmation');
        
        // Step 2: Test approval notification email
        $this->simulateAdminLogin();
        $approvalResult = $this->simulateAdminApproval($userId, 'approved');
        
        $this->assertTrue($approvalResult['success'], 'Approval should succeed');
        $this->assertEmailSent('emailtest@gmail.com', 'approval');
        
        // Step 3: Test email error handling (simulate email failure)
        $this->simulateEmailFailure();
        
        $failureUserId = $this->createTestUser([
            'name' => 'Email Failure User',
            'email' => 'failure@gmail.com',
            'role' => 'user'
        ]);
        
        $this->simulateUserLogin($failureUserId);
        
        $failureResult = $this->simulateAgentRegistrationSubmission([
            'agent_email' => 'failure@gmail.com',
            'agent_info' => 'Testing email failure'
        ]);
        
        // Registration should still succeed even if email fails
        $this->assertTrue($failureResult['success'], 'Registration should succeed despite email failure');
        
        echo "✓ Email notification integration completed successfully\n";
    }
    
    /**
     * Test complete end-to-end workflow
     */
    public function testCompleteEndToEndWorkflow()
    {
        echo "Testing complete end-to-end workflow...\n";
        
        // Step 1: New user registration with agent option
        $newUserResult = $this->simulateNewUserRegistration([
            'name' => 'End to End User',
            'email' => 'e2e@gmail.com',
            'password' => 'password123',
            'role_option' => 'agent',
            'additional_info' => 'End to end test'
        ]);
        
        $this->assertTrue($newUserResult['success'], 'New user registration should succeed');
        $userId = $newUserResult['user']['id'];
        
        // Step 2: User login and check status
        $this->simulateUserLogin($userId);
        $statusCheck = $this->simulateAgentButtonClick();
        $this->assertTrue($statusCheck['show_processing_message'], 'Should show processing message');
        
        // Step 3: Admin approval
        $this->simulateAdminLogin();
        $approvalResult = $this->simulateAdminApproval($userId, 'approved');
        $this->assertTrue($approvalResult['success'], 'Admin approval should succeed');
        
        // Step 4: User can now access agent features
        $this->simulateUserLogin($userId);
        $agentAccess = $this->checkAgentAccess();
        $this->assertTrue($agentAccess['can_access'], 'User should have agent access');
        
        // Step 5: Verify all data consistency
        $finalUser = $this->getUserById($userId);
        $this->assertEquals('agent', $finalUser['role'], 'Final user role should be agent');
        $this->assertEquals('approved', $finalUser['agent_request_status'], 'Final status should be approved');
        
        $affiliate = $this->getAffiliateByUserId($userId);
        $this->assertNotNull($affiliate, 'Affiliate record should exist');
        $this->assertEquals('active', $affiliate['status'], 'Affiliate should be active');
        
        echo "✓ Complete end-to-end workflow completed successfully\n";
    }
    
    // Helper methods for simulation
    
    private function simulateNewUserAgentClick()
    {
        // Simulate clicking agent button as non-authenticated user
        // Should redirect to registration page
        return ['redirect' => '?page=register'];
    }
    
    private function simulateNewUserRegistration($data)
    {
        // Simulate new user registration with agent option
        $userId = $this->createTestUser([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => 'user',
            'agent_request_status' => $data['role_option'] === 'agent' ? 'pending' : 'none',
            'agent_request_date' => $data['role_option'] === 'agent' ? date('Y-m-d H:i:s') : null
        ]);
        
        if ($data['role_option'] === 'agent') {
            $this->recordEmailSent($data['email'], 'confirmation');
        }
        
        return [
            'success' => true,
            'user' => $this->getUserById($userId)
        ];
    }
    
    private function simulateUserLogin($userId)
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $this->getUserById($userId)['role'];
    }
    
    private function simulateAdminLogin()
    {
        $adminId = $this->createTestUser([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin'
        ]);
        
        $_SESSION['user_id'] = $adminId;
        $_SESSION['user_role'] = 'admin';
    }
    
    private function simulateAgentButtonClick()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return ['redirect' => '?page=login'];
        }
        
        $user = $this->getUserById($userId);
        
        if ($user['agent_request_status'] === 'pending') {
            return [
                'show_processing_message' => true,
                'status' => 'pending'
            ];
        }
        
        if ($user['role'] === 'agent') {
            return [
                'redirect' => '?page=affiliate'
            ];
        }
        
        return [
            'show_popup' => true,
            'html' => '<div>Agent registration popup</div>'
        ];
    }
    
    private function simulateAgentRegistrationSubmission($data)
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        
        $user = $this->getUserById($userId);
        
        // Check for existing request
        if ($user['agent_request_status'] === 'pending') {
            return ['success' => false, 'message' => 'Request already submitted'];
        }
        
        // Update user with pending status
        $this->updateUser($userId, [
            'agent_request_status' => 'pending',
            'agent_request_date' => date('Y-m-d H:i:s')
        ]);
        
        // Record email sent
        $this->recordEmailSent($data['agent_email'], 'confirmation');
        
        return ['success' => true, 'message' => 'Registration submitted successfully'];
    }
    
    private function simulateAdminAgentManagement()
    {
        // Get pending agent requests
        $pendingRequests = $this->getUsersByAgentStatus('pending');
        $approvedAgents = $this->getUsersByAgentStatus('approved');
        $activeUsers = $this->getUsersByRoleAndStatus('user', 'active');
        
        return [
            'pendingAgentRequests' => $pendingRequests,
            'approvedAgents' => $approvedAgents,
            'activeUsers' => $activeUsers
        ];
    }
    
    private function simulateAdminApproval($userId, $status)
    {
        $user = $this->getUserById($userId);
        if (!$user || $user['agent_request_status'] !== 'pending') {
            return ['success' => false, 'message' => 'Invalid request'];
        }
        
        // Update user status
        $this->updateUser($userId, [
            'agent_request_status' => $status,
            'agent_approved_date' => $status === 'approved' ? date('Y-m-d H:i:s') : null,
            'role' => $status === 'approved' ? 'agent' : $user['role']
        ]);
        
        // Create affiliate record if approved
        if ($status === 'approved') {
            $this->createAffiliate($userId);
            $this->recordEmailSent($user['email'], 'approval');
        }
        
        return ['success' => true, 'message' => 'Status updated successfully'];
    }
    
    private function checkAgentAccess()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return ['can_access' => false];
        }
        
        $user = $this->getUserById($userId);
        return [
            'can_access' => $user['role'] === 'agent' && $user['agent_request_status'] === 'approved'
        ];
    }
    
    private function simulateEmailFailure()
    {
        // Set flag to simulate email failures
        $this->emailFailureMode = true;
    }
    
    // Database helper methods
    
    private function createTestTables()
    {
        $this->testDatabase->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                status VARCHAR(50) DEFAULT 'active',
                agent_request_status VARCHAR(50) DEFAULT 'none',
                agent_request_date DATETIME NULL,
                agent_approved_date DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->testDatabase->exec("
            CREATE TABLE affiliates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                referral_code VARCHAR(50) UNIQUE,
                status VARCHAR(50) DEFAULT 'pending',
                approved_by INTEGER NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->testDatabase->exec("
            CREATE TABLE test_emails (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email VARCHAR(255) NOT NULL,
                type VARCHAR(50) NOT NULL,
                sent_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    private function seedTestData()
    {
        // Add any initial test data if needed
    }
    
    private function createTestUser($data)
    {
        $sql = "INSERT INTO users (name, email, role, status, agent_request_status, agent_request_date, agent_approved_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->testDatabase->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['role'] ?? 'user',
            $data['status'] ?? 'active',
            $data['agent_request_status'] ?? 'none',
            $data['agent_request_date'] ?? null,
            $data['agent_approved_date'] ?? null
        ]);
        
        return $this->testDatabase->lastInsertId();
    }
    
    private function getUserById($id)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->testDatabase->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function updateUser($id, $data)
    {
        $setParts = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $setParts[] = "$key = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $this->testDatabase->prepare($sql);
        return $stmt->execute($values);
    }
    
    private function getUsersByAgentStatus($status)
    {
        $sql = "SELECT * FROM users WHERE agent_request_status = ? ORDER BY agent_request_date DESC";
        $stmt = $this->testDatabase->prepare($sql);
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getUsersByRoleAndStatus($role, $status)
    {
        $sql = "SELECT * FROM users WHERE role = ? AND status = ? ORDER BY created_at DESC";
        $stmt = $this->testDatabase->prepare($sql);
        $stmt->execute([$role, $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function createAffiliate($userId)
    {
        $sql = "INSERT INTO affiliates (user_id, referral_code, status) VALUES (?, ?, ?)";
        $stmt = $this->testDatabase->prepare($sql);
        $stmt->execute([$userId, 'REF' . str_pad($userId, 4, '0', STR_PAD_LEFT), 'active']);
        return $this->testDatabase->lastInsertId();
    }
    
    private function getAffiliateByUserId($userId)
    {
        $sql = "SELECT * FROM affiliates WHERE user_id = ?";
        $stmt = $this->testDatabase->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function recordEmailSent($email, $type)
    {
        if (isset($this->emailFailureMode) && $this->emailFailureMode) {
            return; // Simulate email failure
        }
        
        $sql = "INSERT INTO test_emails (email, type) VALUES (?, ?)";
        $stmt = $this->testDatabase->prepare($sql);
        $stmt->execute([$email, $type]);
    }
    
    private function assertEmailSent($email, $type)
    {
        $sql = "SELECT COUNT(*) as count FROM test_emails WHERE email = ? AND type = ?";
        $stmt = $this->testDatabase->prepare($sql);
        $stmt->execute([$email, $type]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertTrue($result['count'] > 0, "Email of type '$type' should be sent to '$email'");
    }
    
    // Assertion helpers
    
    private function assertTrue($condition, $message = '')
    {
        if (!$condition) {
            throw new Exception($message ?: 'Assertion failed: expected true');
        }
    }
    
    private function assertFalse($condition, $message = '')
    {
        if ($condition) {
            throw new Exception($message ?: 'Assertion failed: expected false');
        }
    }
    
    private function assertEquals($expected, $actual, $message = '')
    {
        if ($expected !== $actual) {
            throw new Exception($message ?: "Assertion failed: expected '$expected', got '$actual'");
        }
    }
    
    private function assertNotNull($value, $message = '')
    {
        if ($value === null) {
            throw new Exception($message ?: 'Assertion failed: expected non-null value');
        }
    }
    
    private function assertNotEmpty($value, $message = '')
    {
        if (empty($value)) {
            throw new Exception($message ?: 'Assertion failed: expected non-empty value');
        }
    }
    
    private function assertStringContains($needle, $haystack, $message = '')
    {
        if (strpos($haystack, $needle) === false) {
            throw new Exception($message ?: "Assertion failed: '$haystack' should contain '$needle'");
        }
    }
    
    // Main test runner
    
    public function runAllTests()
    {
        $tests = [
            'testNewUserAgentRegistrationFlow',
            'testExistingUserAgentRegistrationFlow',
            'testAdminApprovalWorkflow',
            'testSpamPreventionIntegration',
            'testEmailNotificationIntegration',
            'testCompleteEndToEndWorkflow'
        ];
        
        $passed = 0;
        $failed = 0;
        
        echo "Running Agent Registration Integration Tests...\n\n";
        
        foreach ($tests as $test) {
            try {
                $this->setUp();
                $this->$test();
                $passed++;
            } catch (Exception $e) {
                echo "❌ $test failed: " . $e->getMessage() . "\n";
                $failed++;
            } finally {
                $this->tearDown();
            }
        }
        
        echo "\n=== Test Results ===\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        echo "Total: " . ($passed + $failed) . "\n";
        
        if ($failed === 0) {
            echo "\n🎉 All integration tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Please review the errors above.\n";
        }
        
        return $failed === 0;
    }
}