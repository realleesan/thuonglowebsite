<?php
/**
 * Property-Based Test for Registration Form
 * Feature: agent-registration-system
 * Property 2: Registration form displays role options
 * Validates: Requirements 1.2
 */

require_once __DIR__ . '/../app/controllers/AuthController.php';

class RegistrationFormPropertyTest {
    private AuthController $controller;
    
    public function setUp(): void {
        $this->controller = new AuthController();
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
    
    private function assertContains($needle, $haystack, $message = '') {
        if (strpos($haystack, $needle) === false) {
            throw new Exception("Assertion failed: $message. String '$needle' not found in content");
        }
    }
    
    private function assertNotContains($needle, $haystack, $message = '') {
        if (strpos($haystack, $needle) !== false) {
            throw new Exception("Assertion failed: $message. String '$needle' should not be found in content");
        }
    }
    
    /**
     * Property 2: Registration form displays role options
     * For any new user completing the registration form, the system should display both regular user and agent role options
     * Validates: Requirements 1.2
     * 
     * @test
     */
    public function testRegistrationFormDisplaysRoleOptions() {
        echo "Testing Property 2: Registration form displays role options...\n";
        
        // Test with 50 iterations to ensure consistency
        for ($i = 0; $i < 50; $i++) {
            // Capture the registration form output
            ob_start();
            
            try {
                // Mock the session and request environment
                $this->mockRegistrationEnvironment();
                
                // Get the registration form content
                $formContent = $this->getRegistrationFormContent();
                
                // Verify role selection elements are present
                $this->assertContains('name="account_type"', $formContent, 
                    "Registration form should contain account_type field");
                
                // Verify both role options are present
                $this->assertContains('value="user"', $formContent, 
                    "Registration form should contain user role option");
                $this->assertContains('value="agent"', $formContent, 
                    "Registration form should contain agent role option");
                
                // Verify role labels are present
                $this->assertContains('Người dùng', $formContent, 
                    "Registration form should display user role label");
                $this->assertContains('Đại lý', $formContent, 
                    "Registration form should display agent role label");
                
                // Verify role descriptions are present
                $this->assertContains('Tài khoản thông thường', $formContent, 
                    "Registration form should display user role description");
                $this->assertContains('Đăng ký làm đại lý', $formContent, 
                    "Registration form should display agent role description");
                
                // Verify agent-specific fields are present but hidden by default
                $this->assertContains('id="agentInfo"', $formContent, 
                    "Registration form should contain agent info section");
                $this->assertContains('style="display: none;"', $formContent, 
                    "Agent info section should be hidden by default");
                $this->assertContains('agent_email', $formContent, 
                    "Registration form should contain agent email field");
                
                // Verify Gmail validation message is present
                $this->assertContains('@gmail.com', $formContent, 
                    "Registration form should mention Gmail requirement");
                
                // Verify radio button structure
                $this->assertContains('type="radio"', $formContent, 
                    "Registration form should use radio buttons for role selection");
                $this->assertContains('checked', $formContent, 
                    "One role option should be checked by default (user)");
                
                // Verify form accessibility
                $this->assertContains('role-selection', $formContent, 
                    "Registration form should have proper CSS classes for styling");
                $this->assertContains('role-option', $formContent, 
                    "Registration form should have role option containers");
                
            } catch (Exception $e) {
                ob_end_clean();
                throw $e;
            }
            
            ob_end_clean();
        }
        
        echo "✓ Property 2 test passed - Registration form consistently displays role options\n";
    }
    
    /**
     * Test role option structure and attributes
     */
    public function testRoleOptionStructureAndAttributes() {
        echo "Testing role option structure and attributes...\n";
        
        $formContent = $this->getRegistrationFormContent();
        
        // Test radio button attributes
        $this->assertContains('id="role_user"', $formContent, 
            "User role radio should have correct ID");
        $this->assertContains('id="role_agent"', $formContent, 
            "Agent role radio should have correct ID");
        
        // Test labels are properly associated
        $this->assertContains('for="role_user"', $formContent, 
            "User role label should be associated with radio button");
        $this->assertContains('for="role_agent"', $formContent, 
            "Agent role label should be associated with radio button");
        
        // Test default selection (user should be checked)
        $userRadioPattern = '/id="role_user"[^>]*checked/';
        $agentRadioPattern = '/id="role_agent"[^>]*checked/';
        
        $this->assertTrue(preg_match($userRadioPattern, $formContent), 
            "User role should be checked by default");
        $this->assertFalse(preg_match($agentRadioPattern, $formContent), 
            "Agent role should not be checked by default");
        
        echo "✓ Role option structure test passed\n";
    }
    
    /**
     * Test agent info section properties
     */
    public function testAgentInfoSectionProperties() {
        echo "Testing agent info section properties...\n";
        
        $formContent = $this->getRegistrationFormContent();
        
        // Test agent info section is present but hidden
        $this->assertContains('class="form-group agent-info"', $formContent, 
            "Agent info should have proper CSS classes");
        
        // Test agent email field properties
        $this->assertContains('type="email"', $formContent, 
            "Agent email field should be email type");
        $this->assertContains('pattern=".*@gmail\.com$"', $formContent, 
            "Agent email field should have Gmail pattern validation");
        $this->assertContains('title="Chỉ chấp nhận địa chỉ Gmail', $formContent, 
            "Agent email field should have helpful title attribute");
        
        // Test required attribute handling
        $this->assertNotContains('required', $formContent, 
            "Agent email should not be required by default (added by JavaScript)");
        
        // Test error display element
        $this->assertContains('id="agent_email_error"', $formContent, 
            "Agent email error display element should be present");
        
        echo "✓ Agent info section properties test passed\n";
    }
    
    /**
     * Test form validation elements
     */
    public function testFormValidationElements() {
        echo "Testing form validation elements...\n";
        
        $formContent = $this->getRegistrationFormContent();
        
        // Test CSRF protection
        $this->assertContains('name="csrf_token"', $formContent, 
            "Form should include CSRF token");
        $this->assertContains('type="hidden"', $formContent, 
            "CSRF token should be hidden field");
        
        // Test form method and action
        $this->assertContains('method="POST"', $formContent, 
            "Form should use POST method");
        $this->assertContains('action=', $formContent, 
            "Form should have action attribute");
        
        // Test required field indicators
        $this->assertContains('<span class="required">*</span>', $formContent, 
            "Form should indicate required fields");
        
        // Test form ID for JavaScript
        $this->assertContains('id="registerForm"', $formContent, 
            "Form should have ID for JavaScript interaction");
        
        echo "✓ Form validation elements test passed\n";
    }
    
    /**
     * Test responsive design elements
     */
    public function testResponsiveDesignElements() {
        echo "Testing responsive design elements...\n";
        
        $formContent = $this->getRegistrationFormContent();
        
        // Test CSS classes for responsive design
        $this->assertContains('role-options', $formContent, 
            "Form should have role-options container for responsive layout");
        $this->assertContains('role-info', $formContent, 
            "Role options should have info containers");
        
        // Test form structure for mobile compatibility
        $this->assertContains('form-group', $formContent, 
            "Form should use consistent form-group structure");
        $this->assertContains('form-control', $formContent, 
            "Form inputs should use form-control class");
        
        echo "✓ Responsive design elements test passed\n";
    }
    
    /**
     * Test accessibility features
     */
    public function testAccessibilityFeatures() {
        echo "Testing accessibility features...\n";
        
        $formContent = $this->getRegistrationFormContent();
        
        // Test label associations
        $this->assertContains('<label for=', $formContent, 
            "Form should have proper label associations");
        
        // Test aria attributes (if present)
        $labelCount = substr_count($formContent, '<label');
        $inputCount = substr_count($formContent, '<input');
        
        $this->assertTrue($labelCount > 0, "Form should have labels");
        $this->assertTrue($inputCount > 0, "Form should have inputs");
        
        // Test help text
        $this->assertContains('form-help', $formContent, 
            "Form should provide help text for complex fields");
        
        echo "✓ Accessibility features test passed\n";
    }
    
    /**
     * Mock registration environment for testing
     */
    private function mockRegistrationEnvironment(): void {
        // Mock session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear any existing flash messages
        unset($_SESSION['flash_errors']);
        unset($_SESSION['flash_error']);
        
        // Mock POST data to avoid issues
        $_POST = [];
        
        // Mock server variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '?page=register';
    }
    
    /**
     * Get registration form content for testing
     */
    private function getRegistrationFormContent(): string {
        // Read the registration view file directly
        $viewPath = __DIR__ . '/../app/views/auth/register.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("Registration view file not found: $viewPath");
        }
        
        // Mock view data that would be passed to the view
        $viewData = [
            'csrf_token' => 'test_token_' . uniqid(),
            'form_action' => '?page=register&action=process',
            'login_url' => '?page=login',
            'page_title' => 'Đăng ký'
        ];
        
        // Mock additional variables that might be used in the view
        $refCodeFromUrl = '';
        
        // Capture the view output
        ob_start();
        include $viewPath;
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
    
    /**
     * Run all property tests for registration form
     */
    public function runAllPropertyTests() {
        echo "Running Registration Form Property Tests...\n\n";
        
        try {
            $this->setUp();
            
            $this->testRegistrationFormDisplaysRoleOptions();
            $this->testRoleOptionStructureAndAttributes();
            $this->testAgentInfoSectionProperties();
            $this->testFormValidationElements();
            $this->testResponsiveDesignElements();
            $this->testAccessibilityFeatures();
            
            echo "\nAll Registration Form Property Tests PASSED! ✅\n";
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
    $test = new RegistrationFormPropertyTest();
    $test->runAllPropertyTests();
}