<?php
/**
 * SimpleCheckpointTest - Simple checkpoint test for core services
 * Task 3: Checkpoint - Kiểm tra core services
 */

echo "=== CORE SERVICES CHECKPOINT ===\n";
echo "Validating all core services before proceeding to next phase\n\n";

$results = [];

// 1. Check file structure
echo "1. Checking file structure...\n";
$requiredFiles = [
    'database/migrations/015_add_agent_registration_fields_to_users.sql',
    'app/services/AgentRegistrationData.php',
    'app/services/SpamPreventionService.php',
    'app/services/EmailNotificationService.php',
    'config/email.php',
    'tests/AgentRegistrationDataTest.php',
    'tests/SpamPreventionPropertyTest.php',
    'tests/EmailNotificationPropertyTest.php'
];

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        $missingFiles[] = $file;
    }
}

if (empty($missingFiles)) {
    echo "✓ All required files exist\n";
    $results['File Structure'] = true;
} else {
    echo "✗ Missing files: " . implode(', ', $missingFiles) . "\n";
    $results['File Structure'] = false;
}

// 2. Test AgentRegistrationData basic functionality
echo "\n2. Testing AgentRegistrationData...\n";
try {
    if (!class_exists('AgentRegistrationData')) {
        require_once 'app/services/AgentRegistrationData.php';
    }
    
    $data = new AgentRegistrationData([
        'email' => 'test@gmail.com',
        'user_id' => 1,
        'request_type' => 'existing_user'
    ]);
    
    if ($data->validateGmail() && $data->isValid()) {
        echo "✓ AgentRegistrationData working correctly\n";
        $results['AgentRegistrationData'] = true;
    } else {
        echo "✗ AgentRegistrationData validation failed\n";
        $results['AgentRegistrationData'] = false;
    }
} catch (Exception $e) {
    echo "✗ AgentRegistrationData error: " . $e->getMessage() . "\n";
    $results['AgentRegistrationData'] = false;
}

// 3. Run property tests
echo "\n3. Running property tests...\n";

// SpamPreventionService property tests
echo "Running SpamPreventionService property tests...\n";
ob_start();
$spamTestResult = false;
try {
    include_once 'tests/SpamPreventionPropertyTest.php';
    if (class_exists('SpamPreventionPropertyTest')) {
        $test = new SpamPreventionPropertyTest();
        $spamTestResult = $test->runAllPropertyTests();
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
ob_end_clean();

if ($spamTestResult) {
    echo "✓ SpamPreventionService property tests passed\n";
    $results['SpamPrevention Properties'] = true;
} else {
    echo "✗ SpamPreventionService property tests failed\n";
    $results['SpamPrevention Properties'] = false;
}

// EmailNotificationService property tests
echo "Running EmailNotificationService property tests...\n";
ob_start();
$emailTestResult = false;
try {
    include_once 'tests/EmailNotificationPropertyTest.php';
    if (class_exists('EmailNotificationPropertyTest')) {
        $test = new EmailNotificationPropertyTest();
        $emailTestResult = $test->runAllPropertyTests();
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
ob_end_clean();

if ($emailTestResult) {
    echo "✓ EmailNotificationService property tests passed\n";
    $results['EmailNotification Properties'] = true;
} else {
    echo "✗ EmailNotificationService property tests failed\n";
    $results['EmailNotification Properties'] = false;
}

// 4. Check configuration
echo "\n4. Checking configuration files...\n";
try {
    if (file_exists('config/email.php')) {
        $emailConfig = include 'config/email.php';
        if (is_array($emailConfig) && isset($emailConfig['smtp_host'], $emailConfig['from_email'])) {
            echo "✓ Email configuration is valid\n";
            $results['Configuration'] = true;
        } else {
            echo "✗ Email configuration is invalid\n";
            $results['Configuration'] = false;
        }
    } else {
        echo "✗ Email configuration file missing\n";
        $results['Configuration'] = false;
    }
} catch (Exception $e) {
    echo "✗ Configuration error: " . $e->getMessage() . "\n";
    $results['Configuration'] = false;
}

// 5. Check migration file
echo "\n5. Checking migration file...\n";
if (file_exists('database/migrations/015_add_agent_registration_fields_to_users.sql')) {
    $migrationContent = file_get_contents('database/migrations/015_add_agent_registration_fields_to_users.sql');
    if (strpos($migrationContent, 'agent_request_status') !== false) {
        echo "✓ Migration file contains required fields\n";
        $results['Migration'] = true;
    } else {
        echo "✗ Migration file missing required fields\n";
        $results['Migration'] = false;
    }
} else {
    echo "✗ Migration file missing\n";
    $results['Migration'] = false;
}

// Display results
echo "\n=== CHECKPOINT RESULTS ===\n";
$passedCount = 0;
$totalCount = count($results);

foreach ($results as $testName => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    echo sprintf("%-30s %s\n", $testName, $status);
    if ($result) $passedCount++;
}

echo "\n";
if ($passedCount === $totalCount) {
    echo "🎉 CHECKPOINT PASSED! All core services are ready.\n";
    echo "✅ Ready to proceed to Task 4: AgentRegistrationService\n";
    exit(0);
} else {
    echo "❌ CHECKPOINT FAILED! Please fix issues before proceeding.\n";
    echo "Failed tests: " . ($totalCount - $passedCount) . "/$totalCount\n";
    exit(1);
}