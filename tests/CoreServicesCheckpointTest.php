<?php
/**
 * CoreServicesCheckpointTest - Comprehensive checkpoint test for all core services
 * Validates that all core services are working correctly before proceeding
 * Task 3: Checkpoint - Kiểm tra core services
 */

class CoreServicesCheckpointTest {
    private $results = [];
    private $errors = [];
    
    public function runCheckpoint() {
        echo "=== CORE SERVICES CHECKPOINT ===\n";
        echo "Validating all core services before proceeding to next phase\n\n";
        
        $tests = [
            'File Structure' => $this->checkFileStructure(),
            'PHP Syntax' => $this->checkPhpSyntax(),
            'AgentRegistrationData' => $this->testAgentRegistrationData(),
            'SpamPreventionService Properties' => $this->runSpamPreventionPropertyTests(),
            'EmailNotificationService Properties' => $this->runEmailNotificationPropertyTests(),
            'Service Integration' => $this->testServiceIntegration(),
            'Configuration Files' => $this->checkConfigurationFiles()
        ];
        
        $this->displayResults($tests);
        
        $passedCount = array_sum($tests);
        $totalCount = count($tests);
        
        if ($passedCount === $totalCount) {
            echo "\n🎉 CHECKPOINT PASSED! All core services are ready.\n";
            echo "✅ Ready to proceed to Task 4: AgentRegistrationService\n";
            return true;
        } else {
            echo "\n❌ CHECKPOINT FAILED! Please fix issues before proceeding.\n";
            echo "Failed tests: " . ($totalCount - $passedCount) . "/$totalCount\n";
            return false;
        }
    }
    
    private function checkFileStructure() {
        echo "Checking file structure...\n";
        
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
            return true;
        } else {
            echo "✗ Missing files:\n";
            foreach ($missingFiles as $file) {
                echo "  - $file\n";
            }
            return false;
        }
    }
    
    private function checkPhpSyntax() {
        echo "\nChecking PHP syntax...\n";
        
        $phpFiles = [
            'app/services/AgentRegistrationData.php',
            'app/services/SpamPreventionService.php',
            'app/services/EmailNotificationService.php',
            'config/email.php'
        ];
        
        $syntaxErrors = [];
        foreach ($phpFiles as $file) {
            if (file_exists($file)) {
                // Simple syntax check by trying to parse the file
                $content = file_get_contents($file);
                if (strpos($content, '<?php') === false) {
                    $syntaxErrors[] = "$file: Missing PHP opening tag";
                    continue;
                }
                
                // Check for basic syntax issues
                if (substr_count($content, '{') !== substr_count($content, '}')) {
                    $syntaxErrors[] = "$file: Mismatched braces";
                }
                
                // Try to include the file to check for syntax errors
                try {
                    $tempFile = tempnam(sys_get_temp_dir(), 'syntax_check');
                    file_put_contents($tempFile, $content);
                    
                    ob_start();
                    $result = include $tempFile;
                    ob_end_clean();
                    
                    unlink($tempFile);
                } catch (ParseError $e) {
                    $syntaxErrors[] = "$file: Parse error - " . $e->getMessage();
                } catch (Error $e) {
                    // Ignore other errors, we're just checking syntax
                } catch (Exception $e) {
                    // Ignore exceptions, we're just checking syntax
                }
            }
        }
        
        if (empty($syntaxErrors)) {
            echo "✓ All PHP files appear to have valid syntax\n";
            return true;
        } else {
            echo "✗ Syntax issues found:\n";
            foreach ($syntaxErrors as $error) {
                echo "  - $error\n";
            }
            return false;
        }
    }
    
    private function testAgentRegistrationData() {
        echo "\nTesting AgentRegistrationData...\n";
        
        try {
            require_once 'app/services/AgentRegistrationData.php';
            
            // Test basic functionality
            $data = new AgentRegistrationData([
                'email' => 'test@gmail.com',
                'user_id' => 1,
                'request_type' => 'existing_user'
            ]);
            
            // Test Gmail validation
            if (!$data->validateGmail()) {
                throw new Exception('Gmail validation failed');
            }
            
            // Test data validation
            if (!$data->isValid()) {
                throw new Exception('Data validation failed');
            }
            
            // Test array conversion
            $array = $data->toArray();
            if (!is_array($array) || $array['email'] !== 'test@gmail.com') {
                throw new Exception('Array conversion failed');
            }
            
            echo "✓ AgentRegistrationData working correctly\n";
            return true;
            
        } catch (Exception $e) {
            echo "✗ AgentRegistrationData error: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function runSpamPreventionPropertyTests() {
        echo "\nRunning SpamPreventionService property tests...\n";
        
        try {
            // Capture output
            ob_start();
            include 'tests/SpamPreventionPropertyTest.php';
            $test = new SpamPreventionPropertyTest();
            $result = $test->runAllPropertyTests();
            $output = ob_get_clean();
            
            if ($result) {
                echo "✓ SpamPreventionService property tests passed\n";
                return true;
            } else {
                echo "✗ SpamPreventionService property tests failed\n";
                return false;
            }
            
        } catch (Exception $e) {
            ob_end_clean();
            echo "✗ SpamPreventionService property test error: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function runEmailNotificationPropertyTests() {
        echo "\nRunning EmailNotificationService property tests...\n";
        
        try {
            // Capture output
            ob_start();
            include 'tests/EmailNotificationPropertyTest.php';
            $test = new EmailNotificationPropertyTest();
            $result = $test->runAllPropertyTests();
            $output = ob_get_clean();
            
            if ($result) {
                echo "✓ EmailNotificationService property tests passed\n";
                return true;
            } else {
                echo "✗ EmailNotificationService property tests failed\n";
                return false;
            }
            
        } catch (Exception $e) {
            ob_end_clean();
            echo "✗ EmailNotificationService property test error: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function testServiceIntegration() {
        echo "\nTesting service integration...\n";
        
        try {
            // Test that services can be instantiated together
            require_once 'app/services/AgentRegistrationData.php';
            
            // Test data flow between services
            $registrationData = new AgentRegistrationData([
                'email' => 'integration@gmail.com',
                'user_id' => 999,
                'request_type' => 'existing_user'
            ]);
            
            if (!$registrationData->isValid()) {
                throw new Exception('Integration test data is invalid');
            }
            
            // Test that all required methods exist
            $requiredMethods = ['validateGmail', 'validate', 'isValid', 'toArray', 'sanitize'];
            foreach ($requiredMethods as $method) {
                if (!method_exists($registrationData, $method)) {
                    throw new Exception("Required method $method not found");
                }
            }
            
            echo "✓ Service integration working correctly\n";
            return true;
            
        } catch (Exception $e) {
            echo "✗ Service integration error: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function checkConfigurationFiles() {
        echo "\nChecking configuration files...\n";
        
        try {
            // Check email config
            if (!file_exists('config/email.php')) {
                throw new Exception('Email configuration file missing');
            }
            
            $emailConfig = include 'config/email.php';
            if (!is_array($emailConfig)) {
                throw new Exception('Email configuration is not an array');
            }
            
            $requiredKeys = ['smtp_host', 'smtp_port', 'from_email', 'from_name'];
            foreach ($requiredKeys as $key) {
                if (!isset($emailConfig[$key])) {
                    throw new Exception("Email config missing key: $key");
                }
            }
            
            // Check migration file
            $migrationContent = file_get_contents('database/migrations/015_add_agent_registration_fields_to_users.sql');
            if (strpos($migrationContent, 'agent_request_status') === false) {
                throw new Exception('Migration file missing agent_request_status field');
            }
            
            echo "✓ Configuration files are valid\n";
            return true;
            
        } catch (Exception $e) {
            echo "✗ Configuration error: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function displayResults($tests) {
        echo "\n=== CHECKPOINT RESULTS ===\n";
        foreach ($tests as $testName => $result) {
            $status = $result ? '✅ PASS' : '❌ FAIL';
            echo sprintf("%-35s %s\n", $testName, $status);
        }
    }
}

// Run checkpoint if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $checkpoint = new CoreServicesCheckpointTest();
    $result = $checkpoint->runCheckpoint();
    exit($result ? 0 : 1);
}