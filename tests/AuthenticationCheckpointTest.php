<?php
/**
 * Authentication Checkpoint Test
 * Tests authentication system components without database dependency
 * For Task 10: Checkpoint - Authentication flow validation
 */

class AuthenticationCheckpointTest {
    private array $results = [];
    
    public function runAllTests(): array {
        echo "=== Authentication Checkpoint Test ===\n\n";
        
        $this->testCoreServices();
        $this->testSecurityComponents();
        $this->testControllerStructure();
        $this->testIntegrationReadiness();
        
        $this->printResults();
        return $this->results;
    }
    
    private function testCoreServices(): void {
        echo "Testing Core Services...\n";
        
        try {
            // Test PasswordHasher
            require_once __DIR__ . '/../app/services/PasswordHasher.php';
            $passwordHasher = new PasswordHasher();
            
            $password = 'TestPassword123!';
            $hash = $passwordHasher->hash($password);
            $this->assert($passwordHasher->verify($password, $hash), 'Password hashing should work');
            $this->assert(!$passwordHasher->verify('WrongPassword', $hash), 'Wrong password should fail');
            
            $token = $passwordHasher->generateResetToken();
            $this->assert(strlen($token) === 64, 'Reset token should be 64 characters');
            
            // Test SessionManager (without actual session)
            require_once __DIR__ . '/../app/services/SessionManager.php';
            $sessionManager = new SessionManager();
            $this->assert(method_exists($sessionManager, 'start'), 'SessionManager should have start method');
            $this->assert(method_exists($sessionManager, 'createSession'), 'SessionManager should have createSession method');
            
            // Test InputValidator
            require_once __DIR__ . '/../app/services/InputValidator.php';
            $validator = new InputValidator();
            
            $this->assert($validator->validateEmail('test@example.com'), 'Valid email should pass');
            $this->assert(!$validator->validateEmail('invalid-email'), 'Invalid email should fail');
            $this->assert($validator->detectSqlInjection("'; DROP TABLE users; --"), 'SQL injection should be detected');
            $this->assert($validator->detectXss('<script>alert("xss")</script>'), 'XSS should be detected');
            
            // Test RoleManager
            require_once __DIR__ . '/../app/services/RoleManager.php';
            $roleManager = new RoleManager();
            
            $adminUser = ['id' => 1, 'role' => 'admin'];
            $regularUser = ['id' => 2, 'role' => 'user'];
            
            $this->assert($roleManager->hasRole($adminUser, 'admin'), 'Admin should have admin role');
            $this->assert($roleManager->hasRole($adminUser, 'user'), 'Admin should have user role (hierarchy)');
            $this->assert(!$roleManager->hasRole($regularUser, 'admin'), 'User should not have admin role');
            
            $this->results['CoreServices'] = 'PASS';
            echo "✓ Core services tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['CoreServices'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Core services tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testSecurityComponents(): void {
        echo "Testing Security Components...\n";
        
        try {
            // Test AuthErrorHandler
            require_once __DIR__ . '/../app/services/AuthErrorHandler.php';
            $errorHandler = new AuthErrorHandler();
            
            $validationResult = $errorHandler->handleValidationError(['email' => 'Required']);
            $this->assert(!$validationResult['success'], 'Validation error should return failure');
            $this->assert($validationResult['type'] === 'validation', 'Should return validation type');
            
            $authResult = $errorHandler->handleAuthenticationError('invalid_credentials');
            $this->assert(!$authResult['success'], 'Auth error should return failure');
            $this->assert($authResult['type'] === 'authentication', 'Should return authentication type');
            
            // Test SecurityException
            require_once __DIR__ . '/../app/services/SecurityException.php';
            try {
                throw new SecurityException('Test security exception', 'test_type');
            } catch (SecurityException $e) {
                $this->assert($e->getSecurityType() === 'test_type', 'SecurityException should store security type');
            }
            
            $this->results['SecurityComponents'] = 'PASS';
            echo "✓ Security components tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['SecurityComponents'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Security components tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testControllerStructure(): void {
        echo "Testing Controller Structure...\n";
        
        try {
            // Test AuthController structure (without instantiation)
            require_once __DIR__ . '/../app/controllers/AuthController.php';
            $reflection = new ReflectionClass('AuthController');
            
            $requiredMethods = [
                'login', 'processLogin', 'register', 'processRegister',
                'forgot', 'processForgot', 'resetPassword', 'processReset',
                'logout', 'checkAuth', 'requireRole', 'requirePermission'
            ];
            
            foreach ($requiredMethods as $method) {
                $this->assert($reflection->hasMethod($method), "AuthController should have {$method} method");
            }
            
            // Test middleware methods
            $middlewareMethods = ['checkAuth', 'requireRole', 'requirePermission', 'requireAdmin'];
            foreach ($middlewareMethods as $method) {
                $this->assert($reflection->hasMethod($method), "AuthController should have {$method} middleware");
            }
            
            $this->results['ControllerStructure'] = 'PASS';
            echo "✓ Controller structure tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['ControllerStructure'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Controller structure tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testIntegrationReadiness(): void {
        echo "Testing Integration Readiness...\n";
        
        try {
            // Test that all required files exist
            $requiredFiles = [
                'app/services/AuthService.php',
                'app/services/PasswordHasher.php',
                'app/services/SessionManager.php',
                'app/services/InputValidator.php',
                'app/services/RoleManager.php',
                'app/services/AuthErrorHandler.php',
                'app/services/SecurityException.php',
                'app/controllers/AuthController.php'
            ];
            
            foreach ($requiredFiles as $file) {
                $this->assert(file_exists(__DIR__ . '/../' . $file), "Required file {$file} should exist");
            }
            
            // Test that views exist
            $requiredViews = [
                'app/views/auth/login.php',
                'app/views/auth/register.php',
                'app/views/auth/forgot.php',
                'app/views/auth/reset.php'
            ];
            
            foreach ($requiredViews as $view) {
                $this->assert(file_exists(__DIR__ . '/../' . $view), "Required view {$view} should exist");
            }
            
            // Test database migration files exist
            $migrationFiles = [
                'database/migrations/011_create_password_reset_tokens_table.sql',
                'database/migrations/012_create_login_attempts_table.sql'
            ];
            
            foreach ($migrationFiles as $migration) {
                $this->assert(file_exists(__DIR__ . '/../' . $migration), "Migration {$migration} should exist");
            }
            
            $this->results['IntegrationReadiness'] = 'PASS';
            echo "✓ Integration readiness tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['IntegrationReadiness'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Integration readiness tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function assert(bool $condition, string $message): void {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }
    
    private function printResults(): void {
        echo "=== Authentication Checkpoint Results ===\n";
        $totalTests = count($this->results);
        $passedTests = 0;
        
        foreach ($this->results as $testName => $result) {
            if ($result === 'PASS') {
                echo "✓ $testName: PASSED\n";
                $passedTests++;
            } else {
                echo "✗ $testName: $result\n";
            }
        }
        
        echo "\nSummary: $passedTests/$totalTests test categories passed\n";
        
        if ($passedTests === $totalTests) {
            echo "\n🎉 Authentication System Checkpoint PASSED!\n";
            echo "\n✅ Ready for next phase:\n";
            echo "- All core services implemented and working\n";
            echo "- Security components validated\n";
            echo "- Controller structure complete\n";
            echo "- All required files in place\n";
            echo "- Database migrations ready\n";
            echo "\n📋 Next steps:\n";
            echo "1. Start database server\n";
            echo "2. Run database migrations\n";
            echo "3. Test with live database\n";
            echo "4. Implement authentication middleware\n";
        } else {
            echo "\n⚠️  Authentication system needs attention before proceeding.\n";
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AuthenticationCheckpointTest();
    $results = $test->runAllTests();
    
    // Exit with error code if any tests failed
    $allPassed = array_reduce($results, function($carry, $result) {
        return $carry && ($result === 'PASS');
    }, true);
    
    exit($allPassed ? 0 : 1);
}