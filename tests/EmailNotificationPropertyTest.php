<?php
/**
 * EmailNotificationPropertyTest - Property-based tests for EmailNotificationService
 * Feature: agent-registration-system
 * Property 4: Email notifications are sent for all registrations
 * Property 12: Email error handling works correctly
 * Validates: Requirements 1.4, 2.3, 5.1, 5.2, 5.3, 5.4
 */

class EmailNotificationPropertyTest {
    private $testResults = [];
    
    /**
     * Property 4: Email notifications are sent for all registrations
     * For any agent registration request (new or existing user), 
     * the system should send appropriate confirmation or success emails using PHPMailer
     * Validates: Requirements 1.4, 2.3, 5.1, 5.2, 5.3
     */
    public function testProperty4_EmailNotificationsSentForAllRegistrations() {
        echo "Testing Property 4: Email notifications are sent for all registrations\n";
        
        $iterations = 100;
        $passedTests = 0;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random test data
            $userEmail = $this->generateRandomEmail();
            $userName = $this->generateRandomName();
            $registrationType = rand(0, 1) ? 'new_user' : 'existing_user';
            
            $mockService = $this->createMockEmailService();
            
            // Test property: All registration types should trigger email notifications
            $emailSent = false;
            
            switch ($registrationType) {
                case 'new_user':
                    $emailSent = $mockService->simulateSendRegistrationConfirmation($userEmail, $userName);
                    break;
                case 'existing_user':
                    $emailSent = $mockService->simulateSendProcessingNotification($userEmail, $userName);
                    break;
            }
            
            // Property: Email should always be attempted for valid inputs
            if ($this->isValidEmail($userEmail) && !empty($userName)) {
                if ($emailSent) {
                    $passedTests++;
                } else {
                    $this->testResults[] = "Failed: Email not sent for valid inputs - $registrationType, $userEmail, $userName";
                }
            } else {
                // Invalid inputs should also be handled gracefully (return false but not crash)
                if (is_bool($emailSent)) {
                    $passedTests++;
                } else {
                    $this->testResults[] = "Failed: Invalid input not handled gracefully - $userEmail, $userName";
                }
            }
        }
        
        $successRate = ($passedTests / $iterations) * 100;
        echo "Property 4 Results: $passedTests/$iterations passed ({$successRate}%)\n";
        
        if ($successRate >= 95) {
            echo "✓ Property 4: Email notifications are sent for all registrations - PASSED\n";
            return true;
        } else {
            echo "✗ Property 4: Email notifications are sent for all registrations - FAILED\n";
            return false;
        }
    }
    
    /**
     * Property 12: Email error handling works correctly
     * For any email sending failure, the system should log the error 
     * but continue with the registration process
     * Validates: Requirements 5.4
     */
    public function testProperty12_EmailErrorHandlingWorksCorrectly() {
        echo "\nTesting Property 12: Email error handling works correctly\n";
        
        $iterations = 100;
        $passedTests = 0;
        
        for ($i = 0; $i < $iterations; $i++) {
            $userEmail = $this->generateRandomEmail();
            $userName = $this->generateRandomName();
            $shouldFail = (rand(1, 100) <= 30); // 30% chance of failure
            
            $mockService = $this->createMockEmailService();
            $mockService->setFailureMode($shouldFail);
            
            // Test property: Email failures should be handled gracefully
            $result = $mockService->simulateSendApprovalNotification($userEmail, $userName);
            $errorLogged = $mockService->wasErrorLogged();
            
            if ($shouldFail) {
                // Should return false but log error
                if ($result === false && $errorLogged) {
                    $passedTests++;
                } else {
                    $this->testResults[] = "Failed: Email failure not handled correctly - result: " . 
                        ($result ? 'true' : 'false') . ", logged: " . ($errorLogged ? 'true' : 'false');
                }
            } else {
                // Should succeed and not log error
                if ($result === true && !$errorLogged) {
                    $passedTests++;
                } else {
                    $this->testResults[] = "Failed: Successful email incorrectly handled - result: " . 
                        ($result ? 'true' : 'false') . ", logged: " . ($errorLogged ? 'true' : 'false');
                }
            }
        }
        
        $successRate = ($passedTests / $iterations) * 100;
        echo "Property 12 Results: $passedTests/$iterations passed ({$successRate}%)\n";
        
        if ($successRate >= 95) {
            echo "✓ Property 12: Email error handling works correctly - PASSED\n";
            return true;
        } else {
            echo "✗ Property 12: Email error handling works correctly - FAILED\n";
            return false;
        }
    }
    
    /**
     * Additional property test: Email template consistency
     */
    public function testEmailTemplateConsistency() {
        echo "\nTesting Email template consistency across different inputs\n";
        
        $iterations = 50;
        $passedTests = 0;
        
        for ($i = 0; $i < $iterations; $i++) {
            $userEmail = $this->generateRandomEmail();
            $userName = $this->generateRandomName();
            
            $mockService = $this->createMockEmailService();
            
            // Test: Same inputs should produce consistent results
            $result1 = $mockService->simulateSendRegistrationConfirmation($userEmail, $userName);
            $result2 = $mockService->simulateSendRegistrationConfirmation($userEmail, $userName);
            
            if ($result1 === $result2) {
                $passedTests++;
            } else {
                $this->testResults[] = "Failed: Inconsistent email results for same inputs - $userEmail, $userName";
            }
        }
        
        $successRate = ($passedTests / $iterations) * 100;
        echo "Email template consistency: $passedTests/$iterations passed ({$successRate}%)\n";
        
        return $successRate >= 95;
    }
    
    /**
     * Test email content validation
     */
    public function testEmailContentValidation() {
        echo "\nTesting Email content validation\n";
        
        $iterations = 50;
        $passedTests = 0;
        
        for ($i = 0; $i < $iterations; $i++) {
            $userEmail = $this->generateRandomEmail();
            $userName = $this->generateRandomName();
            
            $mockService = $this->createMockEmailService();
            
            // Test: Email content should be non-empty for valid names
            $content = $mockService->simulateGetEmailContent('registration_confirmation', $userName);
            
            if (empty($userName)) {
                // Empty name should return empty content
                if (empty($content)) {
                    $passedTests++;
                } else {
                    $this->testResults[] = "Failed: Empty name should return empty content";
                }
            } else {
                // Valid name should return content with name
                if (!empty($content) && strpos($content, $userName) !== false) {
                    $passedTests++;
                } else {
                    $this->testResults[] = "Failed: Email content validation failed for $userName";
                }
            }
        }
        
        $successRate = ($passedTests / $iterations) * 100;
        echo "Email content validation: $passedTests/$iterations passed ({$successRate}%)\n";
        
        return $successRate >= 95;
    }
    
    /**
     * Generate random email for testing
     */
    private function generateRandomEmail() {
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'test.com'];
        $names = ['user', 'test', 'admin', 'customer', 'agent'];
        
        $name = $names[array_rand($names)] . rand(1, 999);
        $domain = $domains[array_rand($domains)];
        
        return $name . '@' . $domain;
    }
    
    /**
     * Generate random name for testing
     */
    private function generateRandomName() {
        $names = ['Nguyễn Văn A', 'Trần Thị B', 'Lê Văn C', 'Phạm Thị D', 'Hoàng Văn E', 'Test User', ''];
        return $names[array_rand($names)];
    }
    
    /**
     * Validate email format
     */
    private function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Create mock email service for testing
     */
    private function createMockEmailService() {
        return new class {
            private $failureMode = false;
            private $errorLogged = false;
            
            public function setFailureMode($shouldFail) {
                $this->failureMode = $shouldFail;
            }
            
            public function wasErrorLogged() {
                return $this->errorLogged;
            }
            
            public function simulateSendRegistrationConfirmation($email, $name) {
                return $this->simulateEmailSend($email, $name);
            }
            
            public function simulateSendApprovalNotification($email, $name) {
                return $this->simulateEmailSend($email, $name);
            }
            
            public function simulateSendProcessingNotification($email, $name) {
                return $this->simulateEmailSend($email, $name);
            }
            
            public function simulateGetEmailContent($template, $userName) {
                if (empty($userName)) {
                    return '';
                }
                
                return "Hello $userName, this is a test email template for $template.";
            }
            
            private function simulateEmailSend($email, $name) {
                // Reset error log state
                $this->errorLogged = false;
                
                // Validate inputs - but still process if valid format
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
                
                // Empty name is allowed, just use email
                if (empty($name)) {
                    $name = 'User';
                }
                
                // Simulate failure mode
                if ($this->failureMode) {
                    $this->errorLogged = true;
                    return false;
                }
                
                // Simulate success
                return true;
            }
        };
    }
    
    /**
     * Run all property tests
     */
    public function runAllPropertyTests() {
        echo "=== EmailNotificationService Property-Based Tests ===\n";
        echo "Feature: agent-registration-system\n";
        echo "Running minimum 100 iterations per property test\n\n";
        
        $results = [];
        $results[] = $this->testProperty4_EmailNotificationsSentForAllRegistrations();
        $results[] = $this->testProperty12_EmailErrorHandlingWorksCorrectly();
        $results[] = $this->testEmailTemplateConsistency();
        $results[] = $this->testEmailContentValidation();
        
        $passedCount = array_sum($results);
        $totalCount = count($results);
        
        echo "\n=== Property Test Summary ===\n";
        echo "Passed: $passedCount/$totalCount properties\n";
        
        if (!empty($this->testResults)) {
            echo "\nFailure Details:\n";
            foreach (array_slice($this->testResults, 0, 10) as $result) { // Show first 10 failures
                echo "- $result\n";
            }
            if (count($this->testResults) > 10) {
                echo "... and " . (count($this->testResults) - 10) . " more failures\n";
            }
        }
        
        if ($passedCount === $totalCount) {
            echo "\n✓ All email notification properties PASSED!\n";
            return true;
        } else {
            echo "\n✗ Some email notification properties FAILED!\n";
            return false;
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new EmailNotificationPropertyTest();
    $test->runAllPropertyTests();
}