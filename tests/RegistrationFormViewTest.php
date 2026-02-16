<?php
/**
 * Simple Registration Form View Test (No Database Required)
 * Feature: agent-registration-system
 * Property 2: Registration form displays role options
 * Validates: Requirements 1.2
 */

class RegistrationFormViewTest {
    
    /**
     * Assert helpers
     */
    private function assertTrue($condition, $message = '') {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }
    
    private function assertContains($needle, $haystack, $message = '') {
        if (strpos($haystack, $needle) === false) {
            throw new Exception("Assertion failed: $message. String '$needle' not found in content");
        }
    }
    
    /**
     * Test registration form displays role options without database
     */
    public function testRegistrationFormDisplaysRoleOptionsSimple() {
        echo "Testing Property 2: Registration form displays role options (Simple)...\n";
        
        // Read the registration view file directly
        $viewPath = __DIR__ . '/../app/views/auth/register.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("Registration view file not found: $viewPath");
        }
        
        $content = file_get_contents($viewPath);
        
        // Test with 10 iterations to ensure consistency
        for ($i = 0; $i < 10; $i++) {
            // Verify role selection elements are present in the view file
            $this->assertContains('name="account_type"', $content, 
                "Registration form should contain account_type field");
            
            // Verify both role options are present
            $this->assertContains('value="user"', $content, 
                "Registration form should contain user role option");
            $this->assertContains('value="agent"', $content, 
                "Registration form should contain agent role option");
            
            // Verify role labels are present
            $this->assertContains('Người dùng', $content, 
                "Registration form should display user role label");
            $this->assertContains('Đại lý', $content, 
                "Registration form should display agent role label");
            
            // Verify role descriptions are present
            $this->assertContains('Tài khoản thông thường', $content, 
                "Registration form should display user role description");
            $this->assertContains('Đăng ký làm đại lý', $content, 
                "Registration form should display agent role description");
            
            // Verify agent-specific fields are present
            $this->assertContains('id="agentInfo"', $content, 
                "Registration form should contain agent info section");
            $this->assertContains('agent_email', $content, 
                "Registration form should contain agent email field");
            
            // Verify Gmail validation message is present
            $this->assertContains('@gmail.com', $content, 
                "Registration form should mention Gmail requirement");
            
            // Verify radio button structure
            $this->assertContains('type="radio"', $content, 
                "Registration form should use radio buttons for role selection");
            $this->assertContains('checked', $content, 
                "One role option should be checked by default");
            
            // Verify CSS classes for styling
            $this->assertContains('role-selection', $content, 
                "Registration form should have proper CSS classes");
            $this->assertContains('role-option', $content, 
                "Registration form should have role option containers");
        }
        
        echo "✓ Property 2 test passed - Registration form displays role options\n";
    }
    
    /**
     * Test specific role option attributes
     */
    public function testRoleOptionAttributes() {
        echo "Testing role option attributes...\n";
        
        $viewPath = __DIR__ . '/../app/views/auth/register.php';
        $content = file_get_contents($viewPath);
        
        // Test radio button IDs
        $this->assertContains('id="role_user"', $content, 
            "User role radio should have correct ID");
        $this->assertContains('id="role_agent"', $content, 
            "Agent role radio should have correct ID");
        
        // Test labels are properly associated
        $this->assertContains('for="role_user"', $content, 
            "User role label should be associated with radio button");
        $this->assertContains('for="role_agent"', $content, 
            "Agent role label should be associated with radio button");
        
        // Test agent email field attributes
        $this->assertContains('pattern=".*@gmail\.com$"', $content, 
            "Agent email field should have Gmail pattern validation");
        
        echo "✓ Role option attributes test passed\n";
    }
    
    /**
     * Test agent info section structure
     */
    public function testAgentInfoSection() {
        echo "Testing agent info section structure...\n";
        
        $viewPath = __DIR__ . '/../app/views/auth/register.php';
        $content = file_get_contents($viewPath);
        
        // Test agent info section
        $this->assertContains('class="form-group agent-info"', $content, 
            "Agent info should have proper CSS classes");
        $this->assertContains('style="display: none;"', $content, 
            "Agent info section should be hidden by default");
        
        // Test Gmail requirement message
        $this->assertContains('Chỉ chấp nhận địa chỉ Gmail', $content, 
            "Should display Gmail requirement message");
        
        // Test error display element
        $this->assertContains('id="agent_email_error"', $content, 
            "Agent email error display element should be present");
        
        echo "✓ Agent info section test passed\n";
    }
    
    /**
     * Run all simple view tests
     */
    public function runAllTests() {
        echo "Running Registration Form View Tests (No Database Required)...\n\n";
        
        try {
            $this->testRegistrationFormDisplaysRoleOptionsSimple();
            $this->testRoleOptionAttributes();
            $this->testAgentInfoSection();
            
            echo "\nAll Registration Form View Tests PASSED! ✅\n";
            echo "Property 2: Registration form displays role options - VALIDATED\n";
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
    $test = new RegistrationFormViewTest();
    $test->runAllTests();
}