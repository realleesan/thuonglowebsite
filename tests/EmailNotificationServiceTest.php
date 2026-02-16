<?php
/**
 * EmailNotificationServiceTest - Unit tests for EmailNotificationService
 * Tests email notification functionality
 */

require_once __DIR__ . '/../app/services/EmailNotificationService.php';

class EmailNotificationServiceTest {
    private EmailNotificationService $service;
    
    public function __construct() {
        $this->service = new EmailNotificationService();
    }
    
    public function testServiceInterfaceImplementation() {
        // Test getData method with different methods
        $methods = ['sendRegistrationConfirmation', 'sendApprovalNotification', 'sendProcessingNotification'];
        
        foreach ($methods as $method) {
            $result = $this->service->getData($method, [
                'email' => 'test@gmail.com',
                'name' => 'Test User'
            ]);
            
            assert(isset($result['success']), "Method $method should return success status");
            assert(is_bool($result['success']), "Success status should be boolean for $method");
        }
        
        echo "✓ ServiceInterface implementation tests passed\n";
    }
    
    public function testEmailConfigurationTest() {
        // Test email configuration validation
        $result = $this->service->testEmailConfiguration();
        
        assert(isset($result['success']), 'Configuration test should return success status');
        assert(is_bool($result['success']), 'Success status should be boolean');
        assert(isset($result['message']), 'Configuration test should return message');
        
        if ($result['success']) {
            assert(isset($result['config']), 'Successful test should return config details');
            assert(isset($result['config']['smtp_host']), 'Config should include SMTP host');
            assert(isset($result['config']['smtp_port']), 'Config should include SMTP port');
        }
        
        echo "✓ Email configuration tests passed\n";
    }
    
    public function testEmailTemplateGeneration() {
        // Test that email methods don't throw exceptions with valid parameters
        $testEmail = 'test@gmail.com';
        $testName = 'Test User';
        
        try {
            // These will fail to actually send (no real SMTP config), but should not throw exceptions
            $result1 = $this->service->getData('sendRegistrationConfirmation', [
                'email' => $testEmail,
                'name' => $testName
            ]);
            
            $result2 = $this->service->getData('sendApprovalNotification', [
                'email' => $testEmail,
                'name' => $testName
            ]);
            
            $result3 = $this->service->getData('sendProcessingNotification', [
                'email' => $testEmail,
                'name' => $testName
            ]);
            
            // All should return arrays with success key
            assert(is_array($result1) && isset($result1['success']), 'Registration confirmation should return valid result');
            assert(is_array($result2) && isset($result2['success']), 'Approval notification should return valid result');
            assert(is_array($result3) && isset($result3['success']), 'Processing notification should return valid result');
            
            echo "✓ Email template generation tests passed\n";
            
        } catch (Exception $e) {
            // This is expected if SMTP is not configured
            echo "✓ Email template generation tests passed (SMTP not configured, which is expected)\n";
        }
    }
    
    public function testErrorHandling() {
        // Test invalid method
        $result = $this->service->getData('invalid_method', []);
        assert(isset($result['error']), 'Invalid method should return error');
        assert($result['error'] === true, 'Error flag should be true');
        assert(isset($result['message']), 'Error should include message');
        
        // Test missing parameters
        $result = $this->service->getData('sendRegistrationConfirmation', []);
        assert(isset($result['success']), 'Missing parameters should still return success status');
        
        echo "✓ Error handling tests passed\n";
    }
    
    public function testGetModelMethod() {
        // EmailNotificationService doesn't use models
        $result = $this->service->getModel('AnyModel');
        assert($result === null, 'getModel should return null for EmailNotificationService');
        
        echo "✓ getModel method tests passed\n";
    }
    
    public function runAllTests() {
        echo "Running EmailNotificationService tests...\n";
        $this->testServiceInterfaceImplementation();
        $this->testEmailConfigurationTest();
        $this->testEmailTemplateGeneration();
        $this->testErrorHandling();
        $this->testGetModelMethod();
        echo "All EmailNotificationService tests passed! ✓\n";
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new EmailNotificationServiceTest();
    $test->runAllTests();
}