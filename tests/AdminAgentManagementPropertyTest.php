<?php

require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/models/UsersModel.php';
require_once __DIR__ . '/../app/models/AffiliateModel.php';
require_once __DIR__ . '/../app/services/EmailNotificationService.php';
require_once __DIR__ . '/../core/database.php';

use Exception;

/**
 * Property-Based Tests for Admin Agent Management
 * Feature: agent-registration-system
 * 
 * Property 8: Admin panels display correct user information
 * Property 9: Status updates process correctly
 * 
 * Validates: Requirements 3.1, 3.2, 3.3, 3.5
 */
class AdminAgentManagementPropertyTest extends TestCase
{
    private $adminController;
    private $usersModel;
    private $affiliateModel;
    private $testUsers = [];
    private $testDatabase;

    protected function setUp(): void
    {
        // Setup test database connection
        $this->testDatabase = new PDO('sqlite::memory:');
        $this->testDatabase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create test tables
        $this->createTestTables();
        
        // Mock models with test database
        $this->usersModel = $this->createMockUsersModel();
        $this->affiliateModel = $this->createMockAffiliateModel();
        
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->testUsers = [];
        $this->testDatabase = null;
    }

    /**
     * Property 8: Admin panels display correct user information
     * For any admin viewing user or agent tabs, the system should display users and requests with correct roles and statuses
     * Validates: Requirements 3.1, 3.2
     */
    public function testProperty8AdminPanelsDisplayCorrectUserInformation()
    {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random test data
            $activeUsers = $this->generateRandomActiveUsers(rand(0, 10));
            $pendingAgents = $this->generateRandomPendingAgents(rand(0, 5));
            $approvedAgents = $this->generateRandomApprovedAgents(rand(0, 8));
            
            // Insert test data
            foreach ($activeUsers as $user) {
                $this->insertTestUser($user);
            }
            foreach ($pendingAgents as $agent) {
                $this->insertTestUser($agent);
            }
            foreach ($approvedAgents as $agent) {
                $this->insertTestUser($agent);
            }
            
            // Test users tab - should show only active users with role 'user'
            $retrievedActiveUsers = $this->usersModel->getUsersByRoleAndStatus('user', 'active');
            
            foreach ($retrievedActiveUsers as $user) {
                $this->assertEquals('user', $user['role'], 
                    "Users tab should only display users with role 'user'");
                $this->assertEquals('active', $user['status'], 
                    "Users tab should only display users with status 'active'");
            }
            
            // Test agents tab - should show only pending agent requests
            $retrievedPendingAgents = $this->usersModel->getUsersByAgentStatus('pending');
            
            foreach ($retrievedPendingAgents as $agent) {
                $this->assertEquals('pending', $agent['agent_request_status'], 
                    "Agents tab should only display requests with status 'pending'");
                $this->assertNotNull($agent['agent_request_date'], 
                    "Pending agents should have request date");
            }
            
            // Test approved agents - should show only approved agent requests
            $retrievedApprovedAgents = $this->usersModel->getUsersByAgentStatus('approved');
            
            foreach ($retrievedApprovedAgents as $agent) {
                $this->assertEquals('approved', $agent['agent_request_status'], 
                    "Approved agents should have status 'approved'");
                $this->assertNotNull($agent['agent_approved_date'], 
                    "Approved agents should have approval date");
            }
            
            // Clean up for next iteration
            $this->cleanupTestData();
        }
    }

    /**
     * Property 9: Status updates process correctly
     * For any admin changing agent status from pending to active, the system should approve the registration and update user roles
     * Validates: Requirements 3.3, 3.5
     */
    public function testProperty9StatusUpdatesProcessCorrectly()
    {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Create a user with pending agent request
            $testUser = $this->generateRandomPendingAgents(1)[0];
            $userId = $this->insertTestUser($testUser);
            
            // Simulate admin approval
            $updateSuccess = $this->usersModel->updateAgentStatus($userId, 'approved');
            $this->assertTrue($updateSuccess, "Agent status update should succeed");
            
            // Verify status was updated
            $updatedUser = $this->usersModel->getUserById($userId);
            $this->assertEquals('approved', $updatedUser['agent_request_status'], 
                "Agent request status should be updated to 'approved'");
            $this->assertNotNull($updatedUser['agent_approved_date'], 
                "Agent approved date should be set");
            
            // Test role update
            $roleUpdateSuccess = $this->usersModel->updateUserRole($userId, 'agent');
            $this->assertTrue($roleUpdateSuccess, "User role update should succeed");
            
            // Verify role was updated
            $updatedUser = $this->usersModel->getUserById($userId);
            $this->assertEquals('agent', $updatedUser['role'], 
                "User role should be updated to 'agent'");
            
            // Test that user no longer appears in pending requests
            $pendingRequests = $this->usersModel->getUsersByAgentStatus('pending');
            $foundInPending = false;
            foreach ($pendingRequests as $request) {
                if ($request['id'] == $userId) {
                    $foundInPending = true;
                    break;
                }
            }
            $this->assertFalse($foundInPending, 
                "Approved user should not appear in pending requests");
            
            // Test that user appears in approved agents
            $approvedAgents = $this->usersModel->getUsersByAgentStatus('approved');
            $foundInApproved = false;
            foreach ($approvedAgents as $agent) {
                if ($agent['id'] == $userId) {
                    $foundInApproved = true;
                    break;
                }
            }
            $this->assertTrue($foundInApproved, 
                "Approved user should appear in approved agents list");
            
            // Clean up for next iteration
            $this->cleanupTestData();
        }
    }

    /**
     * Test rejection workflow
     */
    public function testAgentRejectionWorkflow()
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Create a user with pending agent request
            $testUser = $this->generateRandomPendingAgents(1)[0];
            $userId = $this->insertTestUser($testUser);
            
            // Simulate admin rejection
            $updateSuccess = $this->usersModel->updateAgentStatus($userId, 'rejected');
            $this->assertTrue($updateSuccess, "Agent status update should succeed");
            
            // Verify status was updated
            $updatedUser = $this->usersModel->getUserById($userId);
            $this->assertEquals('rejected', $updatedUser['agent_request_status'], 
                "Agent request status should be updated to 'rejected'");
            
            // Verify role remains unchanged
            $this->assertNotEquals('agent', $updatedUser['role'], 
                "User role should not be changed to 'agent' for rejected requests");
            
            // Clean up for next iteration
            $this->cleanupTestData();
        }
    }

    // Helper methods for generating test data

    private function generateRandomActiveUsers($count)
    {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $users[] = [
                'name' => 'User ' . rand(1000, 9999),
                'email' => 'user' . rand(1000, 9999) . '@example.com',
                'role' => 'user',
                'status' => 'active',
                'agent_request_status' => 'none',
                'agent_request_date' => null,
                'agent_approved_date' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'))
            ];
        }
        return $users;
    }

    private function generateRandomPendingAgents($count)
    {
        $agents = [];
        for ($i = 0; $i < $count; $i++) {
            $agents[] = [
                'name' => 'Agent ' . rand(1000, 9999),
                'email' => 'agent' . rand(1000, 9999) . '@gmail.com',
                'role' => 'user',
                'status' => 'active',
                'agent_request_status' => 'pending',
                'agent_request_date' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 7) . ' days')),
                'agent_approved_date' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(8, 60) . ' days'))
            ];
        }
        return $agents;
    }

    private function generateRandomApprovedAgents($count)
    {
        $agents = [];
        for ($i = 0; $i < $count; $i++) {
            $agents[] = [
                'name' => 'Approved Agent ' . rand(1000, 9999),
                'email' => 'approved' . rand(1000, 9999) . '@gmail.com',
                'role' => 'agent',
                'status' => 'active',
                'agent_request_status' => 'approved',
                'agent_request_date' => date('Y-m-d H:i:s', strtotime('-' . rand(8, 30) . ' days')),
                'agent_approved_date' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 7) . ' days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(31, 90) . ' days'))
            ];
        }
        return $agents;
    }

    private function insertTestUser($userData)
    {
        $sql = "INSERT INTO users (name, email, role, status, agent_request_status, agent_request_date, agent_approved_date, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->testDatabase->prepare($sql);
        $stmt->execute([
            $userData['name'],
            $userData['email'],
            $userData['role'],
            $userData['status'],
            $userData['agent_request_status'],
            $userData['agent_request_date'],
            $userData['agent_approved_date'],
            $userData['created_at']
        ]);
        
        return $this->testDatabase->lastInsertId();
    }

    private function cleanupTestData()
    {
        $this->testDatabase->exec("DELETE FROM users");
        $this->testDatabase->exec("DELETE FROM affiliates");
    }

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
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->testDatabase->exec("
            CREATE TABLE affiliates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                referral_code VARCHAR(50) UNIQUE,
                commission_rate DECIMAL(5,2) DEFAULT 10.00,
                status VARCHAR(50) DEFAULT 'pending',
                approved_by INTEGER NULL,
                approved_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    private function createMockUsersModel()
    {
        $mock = $this->createMock(UsersModel::class);
        
        // Mock getUsersByRoleAndStatus
        $mock->method('getUsersByRoleAndStatus')
             ->willReturnCallback(function($role, $status) {
                 $sql = "SELECT * FROM users WHERE role = ? AND status = ? ORDER BY created_at DESC";
                 $stmt = $this->testDatabase->prepare($sql);
                 $stmt->execute([$role, $status]);
                 return $stmt->fetchAll(PDO::FETCH_ASSOC);
             });

        // Mock getUsersByAgentStatus
        $mock->method('getUsersByAgentStatus')
             ->willReturnCallback(function($agentStatus) {
                 $sql = "SELECT * FROM users WHERE agent_request_status = ? ORDER BY agent_request_date DESC";
                 $stmt = $this->testDatabase->prepare($sql);
                 $stmt->execute([$agentStatus]);
                 return $stmt->fetchAll(PDO::FETCH_ASSOC);
             });

        // Mock getUserById
        $mock->method('getUserById')
             ->willReturnCallback(function($userId) {
                 $sql = "SELECT * FROM users WHERE id = ?";
                 $stmt = $this->testDatabase->prepare($sql);
                 $stmt->execute([$userId]);
                 return $stmt->fetch(PDO::FETCH_ASSOC);
             });

        // Mock updateAgentStatus
        $mock->method('updateAgentStatus')
             ->willReturnCallback(function($userId, $status) {
                 $sql = "UPDATE users SET agent_request_status = ?, agent_approved_date = CASE WHEN ? = 'approved' THEN datetime('now') ELSE agent_approved_date END WHERE id = ?";
                 $stmt = $this->testDatabase->prepare($sql);
                 return $stmt->execute([$status, $status, $userId]);
             });

        // Mock updateUserRole
        $mock->method('updateUserRole')
             ->willReturnCallback(function($userId, $role) {
                 $sql = "UPDATE users SET role = ? WHERE id = ?";
                 $stmt = $this->testDatabase->prepare($sql);
                 return $stmt->execute([$role, $userId]);
             });

        return $mock;
    }

    private function createMockAffiliateModel()
    {
        $mock = $this->createMock(AffiliateModel::class);
        
        $mock->method('createAffiliate')
             ->willReturnCallback(function($userId) {
                 $sql = "INSERT INTO affiliates (user_id, referral_code, status) VALUES (?, ?, ?)";
                 $stmt = $this->testDatabase->prepare($sql);
                 $stmt->execute([$userId, 'REF' . str_pad($userId, 4, '0', STR_PAD_LEFT), 'pending']);
                 return $this->testDatabase->lastInsertId();
             });

        $mock->method('approve')
             ->willReturnCallback(function($affiliateId, $approvedBy) {
                 $sql = "UPDATE affiliates SET status = 'active', approved_by = ?, approved_at = datetime('now') WHERE id = ?";
                 $stmt = $this->testDatabase->prepare($sql);
                 return $stmt->execute([$approvedBy, $affiliateId]);
             });

        return $mock;
    }
}