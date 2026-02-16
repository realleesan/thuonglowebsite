<?php
/**
 * AgentRegistrationDataTest - Unit tests for AgentRegistrationData class
 * Tests validation logic and data handling
 */

require_once __DIR__ . '/../app/services/AgentRegistrationData.php';

class AgentRegistrationDataTest {
    
    public function testGmailValidation() {
        $data = new AgentRegistrationData(['email' => 'test@gmail.com']);
        assert($data->validateGmail() === true, 'Gmail validation should pass for @gmail.com');
        
        $data = new AgentRegistrationData(['email' => 'test@yahoo.com']);
        assert($data->validateGmail() === false, 'Gmail validation should fail for non-Gmail');
        
        $data = new AgentRegistrationData(['email' => '']);
        assert($data->validateGmail() === false, 'Gmail validation should fail for empty email');
        
        echo "✓ Gmail validation tests passed\n";
    }
    
    public function testDataValidation() {
        // Valid data
        $validData = [
            'user_id' => 1,
            'email' => 'test@gmail.com',
            'request_type' => 'existing_user',
            'status' => 'pending'
        ];
        
        $data = new AgentRegistrationData($validData);
        assert($data->isValid() === true, 'Valid data should pass validation');
        
        // Invalid email
        $invalidData = $validData;
        $invalidData['email'] = 'invalid-email';
        $data = new AgentRegistrationData($invalidData);
        assert($data->isValid() === false, 'Invalid email should fail validation');
        
        // Non-Gmail email
        $invalidData = $validData;
        $invalidData['email'] = 'test@yahoo.com';
        $data = new AgentRegistrationData($invalidData);
        assert($data->isValid() === false, 'Non-Gmail should fail validation');
        
        echo "✓ Data validation tests passed\n";
    }
    
    public function testArrayConversion() {
        $originalData = [
            'user_id' => 1,
            'email' => 'test@gmail.com',
            'additional_info' => ['phone' => '0123456789'],
            'request_type' => 'existing_user',
            'status' => 'pending'
        ];
        
        $data = new AgentRegistrationData($originalData);
        $array = $data->toArray();
        
        assert($array['user_id'] === 1, 'User ID should be preserved');
        assert($array['email'] === 'test@gmail.com', 'Email should be preserved');
        assert(is_string($array['additional_info']), 'Additional info should be JSON string');
        
        // Test fromArray
        $reconstructed = AgentRegistrationData::fromArray($array);
        assert($reconstructed->userId === 1, 'Reconstructed user ID should match');
        assert($reconstructed->email === 'test@gmail.com', 'Reconstructed email should match');
        
        echo "✓ Array conversion tests passed\n";
    }
    
    public function testSanitization() {
        $data = new AgentRegistrationData([
            'email' => '  test@gmail.com  ',
            'additional_info' => [
                'name' => '<script>alert("xss")</script>John',
                'phone' => '  0123456789  '
            ]
        ]);
        
        $data->sanitize();
        
        assert($data->email === 'test@gmail.com', 'Email should be trimmed');
        assert($data->additionalInfo['name'] === '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;John', 'HTML should be escaped');
        assert($data->additionalInfo['phone'] === '0123456789', 'Phone should be trimmed');
        
        echo "✓ Sanitization tests passed\n";
    }
    
    public function runAllTests() {
        echo "Running AgentRegistrationData tests...\n";
        $this->testGmailValidation();
        $this->testDataValidation();
        $this->testArrayConversion();
        $this->testSanitization();
        echo "All tests passed! ✓\n";
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AgentRegistrationDataTest();
    $test->runAllTests();
}