<?php
/**
 * CoreServicesBasicTest - Basic tests for core services without database dependency
 * Tests basic functionality of SpamPreventionService and EmailNotificationService
 */

// Test SpamPreventionService basic functionality
echo "Testing SpamPreventionService basic functionality...\n";

// Test class exists and can be instantiated
if (class_exists('SpamPreventionService')) {
    echo "✓ SpamPreventionService class exists\n";
} else {
    echo "✗ SpamPreventionService class not found\n";
}

// Test EmailNotificationService basic functionality
echo "\nTesting EmailNotificationService basic functionality...\n";

// Test class exists
if (class_exists('EmailNotificationService')) {
    echo "✓ EmailNotificationService class exists\n";
} else {
    echo "✗ EmailNotificationService class not found\n";
}

// Test AgentRegistrationData
echo "\nTesting AgentRegistrationData...\n";

require_once __DIR__ . '/../app/services/AgentRegistrationData.php';

if (class_exists('AgentRegistrationData')) {
    echo "✓ AgentRegistrationData class exists\n";
    
    // Test basic functionality
    $data = new AgentRegistrationData([
        'email' => 'test@gmail.com',
        'user_id' => 1,
        'request_type' => 'existing_user'
    ]);
    
    // Test Gmail validation
    assert($data->validateGmail() === true, 'Gmail validation should work');
    echo "✓ Gmail validation works\n";
    
    // Test non-Gmail
    $data2 = new AgentRegistrationData(['email' => 'test@yahoo.com']);
    assert($data2->validateGmail() === false, 'Non-Gmail should fail validation');
    echo "✓ Non-Gmail validation works\n";
    
    // Test validation
    assert($data->isValid() === true, 'Valid data should pass validation');
    echo "✓ Data validation works\n";
    
    // Test array conversion
    $array = $data->toArray();
    assert(is_array($array), 'toArray should return array');
    assert($array['email'] === 'test@gmail.com', 'Email should be preserved');
    echo "✓ Array conversion works\n";
    
} else {
    echo "✗ AgentRegistrationData class not found\n";
}

// Test file structure
echo "\nTesting file structure...\n";

$requiredFiles = [
    'app/services/SpamPreventionService.php',
    'app/services/EmailNotificationService.php',
    'app/services/AgentRegistrationData.php',
    'config/email.php',
    'database/migrations/015_add_agent_registration_fields_to_users.sql'
];

foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . '/../' . $file)) {
        echo "✓ $file exists\n";
    } else {
        echo "✗ $file missing\n";
    }
}

echo "\nBasic core services tests completed!\n";