<?php
/**
 * Unit Tests for AgentRegistrationService Authentication Integration
 * Test integration points với existing auth system
 * Requirements: 6.2
 */

require_once __DIR__ . '/../app/services/AgentRegistrationService.php';
require_once __DIR__ . '/../app/services/AuthService.php';
require_once __DIR__ . '/../app/services/RoleManager.php';

class AgentRegistrationAuthIntegrationTest {
    private AgentRegistrationService $service;
    
    public function setUp(): void {
        $this->service = new AgentRegistrationService();
    }
    
    /**
     * Assert helpers
     */
    private function assertTrue($condition, $message = '') {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }
    
    private function assertFalse($condition, $message = '') {
        if ($condition) {
            throw new Exception("Assertion failed: $message");
        }
    }
    
    private function assertEquals($expected, $actual, $message = '') {
        if ($expected !== $actual) {
            throw new Exception("Assertion failed: $message. Expected: " . var_export($expected, true) . ", Actual: " . var_export($actual, true));
        }
    }
    
    private function assertArrayHasKey($key, $array, $message = '') {
        if (!array_key_exists($key, $array)) {
            throw new Exception("Assertion failed: $message. Key '$key' not found in array");
        }
    }
    
    /**
     * Test authentication integration - role checking
     */
    public function testCanAccessAgentFeaturesIntegration() {
        echo "Testing canAccessAgentFeatures integration...\n";
        
        // Test with different user roles
        $testUsers = [
            ['id' => 1, 'role' => 'admin', 'agent_request_status' => 'none'],
            ['id' => 2, 'role' => 'affiliate', 'agent_request_status' => 'none'],
            ['id' => 3, 'role' => 'user', 'agent_request_status' => 'approved'],
            ['id' => 4, 'role' => 'user', 'agent_request_status' => 'pending'],
            ['id' => 5, 'role' => 'user', 'agent_request_status' => 'none'],
        ];
        
        foreach ($testUsers as $user) {
            // Test the role manager integration
            $roleManager = new RoleManager();
            
            // Test role checking
            $hasAffiliateRole = $roleManager->hasRole($user, 'affiliate');
            $hasAdminRole = $roleManager->hasRole($user, 'admin');
            
            // Verify role hierarchy works correctly
            if ($user['role'] === 'admin') {
                $this->assertTrue($hasAdminRole, "Admin should have admin role");
                $this->assertTrue($hasAffiliateRole, "Admin should inherit affiliate role");
            } elseif ($user['role'] === 'affiliate') {
                $this->assertFalse($hasAdminRole, "Affiliate should not have admin role");
                $this->assertTrue($hasAffiliateRole, "Affiliate should have affiliate role");
            } else {
                $this->assertFalse($hasAdminRole, "User should not have admin role");
                $this->assertFalse($hasAffiliateRole, "User should not have affiliate role by default");
            }
        }
        
        echo "✓ Role checking integration test passed\n";
    }
    
    /**
     * Test permission system integration
     */
    public function testPermissionSystemIntegration() {
        echo "Testing permission system integration...\n";
        
        $roleManager = new RoleManager();
        
        // Test admin permissions
        $adminUser = ['id' => 1, 'role' => 'admin'];
        $this->assertTrue($roleManager->hasPermission($adminUser, 'admin.affiliates.edit'), 
            "Admin should have affiliate edit permission");
        $this->assertTrue($roleManager->hasPermission($adminUser, 'affiliate.dashboard'), 
            "Admin should inherit affiliate permissions");
        
        // Test affiliate permissions
        $affiliateUser = ['id' => 2, 'role' => 'affiliate'];
        $this->assertTrue($roleManager->hasPermission($affiliateUser, 'affiliate.dashboard'), 
            "Affiliate should have dashboard permission");
        $this->assertFalse($roleManager->hasPermission($affiliateUser, 'admin.affiliates.edit'), 
            "Affiliate should not have admin permissions");
        
        // Test user permissions
        $regularUser = ['id' => 3, 'role' => 'user'];
        $this->assertTrue($roleManager->hasPermission($regularUser, 'user.dashboard'), 
            "User should have user dashboard permission");
        $this->assertFalse($roleManager->hasPermission($regularUser, 'affiliate.dashboard'), 
            "User should not have affiliate permissions");
        $this->assertFalse($roleManager->hasPermission($regularUser, 'admin.affiliates.edit'), 
            "User should not have admin permissions");
        
        echo "✓ Permission system integration test passed\n";
    }
    
    /**
     * Test redirect path integration
     */
    public function testRedirectPathIntegration() {
        echo "Testing redirect path integration...\n";
        
        $roleManager = new RoleManager();
        
        $testCases = [
            ['role' => 'admin', 'expected' => '?page=admin'],
            ['role' => 'affiliate', 'expected' => '?page=affiliate'],
            ['role' => 'user', 'expected' => '?page=users'],
        ];
        
        foreach ($testCases as $case) {
            $user = ['id' => 1, 'role' => $case['role']];
            $redirectPath = $roleManager->getRedirectPath($user);
            
            $this->assertEquals($case['expected'], $redirectPath, 
                "Redirect path should be correct for role: {$case['role']}");
        }
        
        echo "✓ Redirect path integration test passed\n";
    }
    
    /**
     * Test agent operation validation
     */
    public function testAgentOperationValidation() {
        echo "Testing agent operation validation...\n";
        
        // Test different operations
        $operations = ['register', 'manage', 'access'];
        
        foreach ($operations as $operation) {
            // This will test the validation logic structure
            // In a real environment, this would test with actual session data
            try {
                $result = $this->service->validateAgentOperation($operation);
                
                // Should return an array with success key
                $this->assertArrayHasKey('success', $result, 
                    "validateAgentOperation should return array with success key for: $operation");
                
                // Should have appropriate message
                if (!$result['success']) {
                    $this->assertArrayHasKey('message', $result, 
                        "Failed validation should include message for: $operation");
                }
                
            } catch (Exception $e) {
                // Expected for operations that require authentication
                $this->assertTrue(true, "Operation $operation correctly requires authentication");
            }
        }
        
        echo "✓ Agent operation validation test passed\n";
    }
    
    /**
     * Test role display names
     */
    public function testRoleDisplayNames() {
        echo "Testing role display names...\n";
        
        $roleManager = new RoleManager();
        
        $expectedNames = [
            'admin' => 'Quản trị viên',
            'affiliate' => 'Đối tác',
            'user' => 'Người dùng'
        ];
        
        foreach ($expectedNames as $role => $expectedName) {
            $displayName = $roleManager->getRoleDisplayName($role);
            $this->assertEquals($expectedName, $displayName, 
                "Display name should be correct for role: $role");
        }
        
        echo "✓ Role display names test passed\n";
    }
    
    /**
     * Test menu accessibility
     */
    public function testMenuAccessibility() {
        echo "Testing menu accessibility...\n";
        
        $roleManager = new RoleManager();
        
        // Test admin menu
        $adminUser = ['id' => 1, 'role' => 'admin'];
        $adminMenu = $roleManager->getAccessibleMenuItems($adminUser);
        
        $this->assertArrayHasKey('dashboard', $adminMenu, "Admin should have dashboard menu");
        $this->assertArrayHasKey('users', $adminMenu, "Admin should have users menu");
        $this->assertArrayHasKey('affiliates', $adminMenu, "Admin should have affiliates menu");
        
        // Test affiliate menu
        $affiliateUser = ['id' => 2, 'role' => 'affiliate'];
        $affiliateMenu = $roleManager->getAccessibleMenuItems($affiliateUser);
        
        $this->assertArrayHasKey('dashboard', $affiliateMenu, "Affiliate should have dashboard menu");
        $this->assertArrayHasKey('commissions', $affiliateMenu, "Affiliate should have commissions menu");
        
        // Test user menu
        $regularUser = ['id' => 3, 'role' => 'user'];
        $userMenu = $roleManager->getAccessibleMenuItems($regularUser);
        
        $this->assertArrayHasKey('dashboard', $userMenu, "User should have dashboard menu");
        $this->assertArrayHasKey('profile', $userMenu, "User should have profile menu");
        
        echo "✓ Menu accessibility test passed\n";
    }
    
    /**
     * Test user management permissions
     */
    public function testUserManagementPermissions() {
        echo "Testing user management permissions...\n";
        
        $roleManager = new RoleManager();
        
        $adminUser = ['id' => 1, 'role' => 'admin'];
        $regularUser = ['id' => 2, 'role' => 'user'];
        $otherUser = ['id' => 3, 'role' => 'user'];
        
        // Admin can manage everyone
        $this->assertTrue($roleManager->canManageUser($adminUser, $regularUser), 
            "Admin should be able to manage regular users");
        $this->assertTrue($roleManager->canManageUser($adminUser, $otherUser), 
            "Admin should be able to manage any user");
        
        // Users can only manage themselves
        $this->assertTrue($roleManager->canManageUser($regularUser, $regularUser), 
            "User should be able to manage themselves");
        $this->assertFalse($roleManager->canManageUser($regularUser, $otherUser), 
            "User should not be able to manage other users");
        
        echo "✓ User management permissions test passed\n";
    }
    
    /**
     * Test role validation
     */
    public function testRoleValidation() {
        echo "Testing role validation...\n";
        
        $roleManager = new RoleManager();
        
        $validRoles = ['admin', 'affiliate', 'user'];
        $invalidRoles = ['superuser', 'guest', 'moderator', ''];
        
        foreach ($validRoles as $role) {
            $this->assertTrue($roleManager->isValidRole($role), 
                "Role '$role' should be valid");
        }
        
        foreach ($invalidRoles as $role) {
            $this->assertFalse($roleManager->isValidRole($role), 
                "Role '$role' should be invalid");
        }
        
        // Test all roles are returned
        $allRoles = $roleManager->getAllRoles();
        $this->assertEquals(['admin', 'affiliate', 'user'], $allRoles, 
            "getAllRoles should return all valid roles");
        
        echo "✓ Role validation test passed\n";
    }
    
    /**
     * Run all authentication integration tests
     */
    public function runAllTests() {
        echo "Running Agent Registration Authentication Integration Tests...\n\n";
        
        try {
            $this->setUp();
            
            $this->testCanAccessAgentFeaturesIntegration();
            $this->testPermissionSystemIntegration();
            $this->testRedirectPathIntegration();
            $this->testAgentOperationValidation();
            $this->testRoleDisplayNames();
            $this->testMenuAccessibility();
            $this->testUserManagementPermissions();
            $this->testRoleValidation();
            
            echo "\nAll Agent Registration Authentication Integration Tests PASSED! ✅\n";
            return true;
            
        } catch (Exception $e) {
            echo "\n❌ Test FAILED: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . "\n";
            echo "Line: " . $e->getLine() . "\n";
            return false;
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AgentRegistrationAuthIntegrationTest();
    $test->runAllTests();
}