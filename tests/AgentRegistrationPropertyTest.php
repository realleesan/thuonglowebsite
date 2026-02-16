<?php
/**
 * Property-Based Test for AgentRegistrationService
 * Feature: agent-registration-system
 * Property 7: Gmail validation is enforced
 * Validates: Requirements 2.2
 */

require_once __DIR__ . '/../app/services/AgentRegistrationService.php';
require_once __DIR__ . '/../app/services/AgentRegistrationData.php';

class AgentRegistrationPropertyTest {
    private AgentRegistrationService $service;
    
    public function setUp(): void {
        $this->service = new AgentRegistrationService();
    }
    
    /**
     * Assert helper
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
    
    /**
     * Property 7: Gmail validation is enforced
     * For any agent registration submission, the system should require and validate a Gmail address
     * Validates: Requirements 2.2
     * 
     * @test
     */
    public function testGmailValidationIsEnforcedForAllRegistrations() {
        // Test with 100 iterations as specified in design
        for ($i = 0; $i < 100; $i++) {
            // Generate random email addresses - mix of Gmail and non-Gmail
            $testEmails = $this->generateTestEmails();
            
            foreach ($testEmails as $emailData) {
                $email = $emailData['email'];
                $shouldBeValid = $emailData['should_be_valid'];
                
                // Test with AgentRegistrationData validation
                $agentData = new AgentRegistrationData([
                    'email' => $email,
                    'request_type' => 'existing_user',
                    'user_id' => 1
                ]);
                
                $isGmailValid = $agentData->validateGmail();
                $validationErrors = $agentData->validate();
                $hasGmailError = $this->hasGmailValidationError($validationErrors);
                
                if ($shouldBeValid) {
                    $this->assertTrue($isGmailValid, 
                        "Gmail validation should pass for valid Gmail: {$email}");
                    $this->assertFalse($hasGmailError, 
                        "Should not have Gmail validation error for: {$email}");
                } else {
                    $this->assertFalse($isGmailValid, 
                        "Gmail validation should fail for non-Gmail: {$email}");
                    $this->assertTrue($hasGmailError, 
                        "Should have Gmail validation error for: {$email}");
                }
                
                // Test with service method for existing users
                $userId = rand(1, 1000);
                $agentDataArray = [
                    'email' => $email,
                    'additional_info' => ['test' => 'data']
                ];
                
                // Mock the user exists check by creating a simple test
                // Since we can't easily mock the database, we'll test the validation logic
                $registrationData = new AgentRegistrationData(array_merge($agentDataArray, [
                    'user_id' => $userId,
                    'request_type' => 'existing_user'
                ]));
                
                $errors = $registrationData->validate();
                $hasError = !empty($errors);
                
                if ($shouldBeValid) {
                    // For valid Gmail, there should be no Gmail-related errors
                    $this->assertFalse($this->hasGmailValidationError($errors), 
                        "Valid Gmail should not produce Gmail validation errors: {$email}");
                } else {
                    // For invalid emails, there should be Gmail-related errors
                    $this->assertTrue($hasError && $this->hasGmailValidationError($errors), 
                        "Invalid email should produce Gmail validation errors: {$email}");
                }
            }
        }
    }
    
    /**
     * Generate test email addresses for property testing
     */
    private function generateTestEmails(): array {
        $validGmails = [
            'test@gmail.com',
            'user123@gmail.com',
            'agent.test@gmail.com',
            'test_user@gmail.com',
            'testuser+tag@gmail.com',
            'a@gmail.com',
            '123@gmail.com'
        ];
        
        $invalidEmails = [
            'test@yahoo.com',
            'user@hotmail.com',
            'agent@outlook.com',
            'test@company.com',
            'user@domain.net',
            'invalid-email',
            'test@',
            '@gmail.com',
            'test@gmail',
            'test@gmail.co',
            'test@GMAIL.COM', // Case sensitivity test
            '',
            'test@gmail.com.fake'
        ];
        
        $testEmails = [];
        
        // Add valid Gmail addresses
        foreach ($validGmails as $email) {
            $testEmails[] = [
                'email' => $email,
                'should_be_valid' => true
            ];
        }
        
        // Add invalid email addresses
        foreach ($invalidEmails as $email) {
            $testEmails[] = [
                'email' => $email,
                'should_be_valid' => false
            ];
        }
        
        // Add some random variations
        for ($i = 0; $i < 10; $i++) {
            // Random valid Gmail
            $randomUser = 'user' . rand(1, 9999);
            $testEmails[] = [
                'email' => $randomUser . '@gmail.com',
                'should_be_valid' => true
            ];
            
            // Random invalid domain
            $domains = ['yahoo.com', 'hotmail.com', 'outlook.com', 'test.com', 'example.org'];
            $randomDomain = $domains[array_rand($domains)];
            $testEmails[] = [
                'email' => $randomUser . '@' . $randomDomain,
                'should_be_valid' => false
            ];
        }
        
        return $testEmails;
    }
    
    /**
     * Check if validation errors contain Gmail-specific error
     */
    private function hasGmailValidationError(array $errors): bool {
        foreach ($errors as $error) {
            if (strpos($error, 'Gmail') !== false || 
                strpos($error, '@gmail.com') !== false ||
                strpos($error, 'gmail') !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Test Gmail validation with edge cases
     * @test
     */
    public function testGmailValidationEdgeCases() {
        $edgeCases = [
            // Case sensitivity
            ['email' => 'test@GMAIL.COM', 'should_be_valid' => false], // Should be case sensitive
            ['email' => 'test@Gmail.com', 'should_be_valid' => false],
            ['email' => 'test@gmail.COM', 'should_be_valid' => false],
            
            // Subdomain attempts
            ['email' => 'test@mail.gmail.com', 'should_be_valid' => false],
            ['email' => 'test@sub.gmail.com', 'should_be_valid' => false],
            
            // Similar domains
            ['email' => 'test@gmail.co', 'should_be_valid' => false],
            ['email' => 'test@gmail.net', 'should_be_valid' => false],
            ['email' => 'test@gmai.com', 'should_be_valid' => false],
            
            // Valid variations
            ['email' => 'test@gmail.com', 'should_be_valid' => true],
            ['email' => 'a@gmail.com', 'should_be_valid' => true],
            ['email' => 'test.user@gmail.com', 'should_be_valid' => true],
            ['email' => 'test+tag@gmail.com', 'should_be_valid' => true],
        ];
        
        foreach ($edgeCases as $case) {
            $agentData = new AgentRegistrationData([
                'email' => $case['email'],
                'request_type' => 'existing_user',
                'user_id' => 1
            ]);
            
            $isValid = $agentData->validateGmail();
            
            $this->assertEquals($case['should_be_valid'], $isValid, 
                "Gmail validation failed for edge case: {$case['email']}");
        }
    }
    
    /**
     * Test that Gmail validation is consistently applied across different registration types
     * @test
     */
    public function testGmailValidationConsistencyAcrossRegistrationTypes() {
        $testEmails = [
            'valid@gmail.com' => true,
            'invalid@yahoo.com' => false,
            'test@hotmail.com' => false,
            'user@gmail.com' => true
        ];
        
        foreach ($testEmails as $email => $shouldBeValid) {
            // Test for new user registration
            $newUserData = new AgentRegistrationData([
                'email' => $email,
                'request_type' => 'new_user'
            ]);
            
            // Test for existing user registration
            $existingUserData = new AgentRegistrationData([
                'email' => $email,
                'request_type' => 'existing_user',
                'user_id' => 1
            ]);
            
            $newUserValid = $newUserData->validateGmail();
            $existingUserValid = $existingUserData->validateGmail();
            
            // Gmail validation should be consistent regardless of registration type
            $this->assertEquals($shouldBeValid, $newUserValid, 
                "Gmail validation inconsistent for new user with email: {$email}");
            $this->assertEquals($shouldBeValid, $existingUserValid, 
                "Gmail validation inconsistent for existing user with email: {$email}");
            $this->assertEquals($newUserValid, $existingUserValid, 
                "Gmail validation should be consistent between registration types for: {$email}");
        }
    }
    
    /**
     * Run all property tests
     */
    public function runAllPropertyTests() {
        echo "Running Agent Registration Property Tests...\n";
        
        try {
            $this->setUp();
            
            echo "Testing Property 7: Gmail validation is enforced...\n";
            $this->testGmailValidationIsEnforcedForAllRegistrations();
            echo "✓ Property 7 test passed\n";
            
            echo "Testing Gmail validation edge cases...\n";
            $this->testGmailValidationEdgeCases();
            echo "✓ Edge cases test passed\n";
            
            echo "Testing Gmail validation consistency...\n";
            $this->testGmailValidationConsistencyAcrossRegistrationTypes();
            echo "✓ Consistency test passed\n";
            
            echo "\nAll Agent Registration Property Tests PASSED! ✅\n";
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
    $test = new AgentRegistrationPropertyTest();
    $test->runAllPropertyTests();
}