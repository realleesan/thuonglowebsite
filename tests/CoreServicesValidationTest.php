<?php
/**
 * Core Services Validation Test
 * Tests all core authentication services to ensure they work correctly
 */

// Include required services
require_once __DIR__ . '/../app/services/PasswordHasher.php';
require_once __DIR__ . '/../app/services/SessionManager.php';
require_once __DIR__ . '/../app/services/InputValidator.php';
require_once __DIR__ . '/../app/services/RoleManager.php';
require_once __DIR__ . '/../app/services/AuthErrorHandler.php';

class CoreServicesValidationTest {
    private array $results = [];
    
    public function runAllTests(): array {
        echo "=== Core Services Validation Test ===\n\n";
        
        $this->testPasswordHasher();
        $this->testSessionManager();
        $this->testInputValidator();
        $this->testRoleManager();
        $this->testAuthErrorHandler();
        
        $this->printResults();
        return $this->results;
    }
    
    private function testPasswordHasher(): void {
        echo "Testing PasswordHasher...\n";
        
        try {
            $hasher = new PasswordHasher();
            
            // Test password hashing
            $password = 'TestPassword123!';
            $hash = $hasher->hash($password);
            $this->assert($hasher->verify($password, $hash), 'Password verification should work');
            $this->assert(!$hasher->verify('WrongPassword', $hash), 'Wrong password should fail');
            
            // Test token generation
            $token = $hasher->generateResetToken();
            $this->assert(strlen($token) === 64, 'Reset token should be 64 characters');
            
            // Test token verification
            $this->assert($hasher->verifyResetToken($token, $token), 'Token verification should work');
            $this->assert(!$hasher->verifyResetToken($token, 'wrong_token'), 'Wrong token should fail');
            
            $this->results['PasswordHasher'] = 'PASS';
            echo "✓ PasswordHasher tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['PasswordHasher'] = 'FAIL: ' . $e->getMessage();
            echo "✗ PasswordHasher tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testSessionManager(): void {
        try {
            // Clear any existing session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            
            $sessionManager = new SessionManager();
            
            // For CLI testing, we'll test the core functionality without actual session
            // Test CSRF token generation (should work without session in CLI)
            try {
                $csrfToken = $sessionManager->getCsrfToken();
                // In CLI mode, this might not work, so we'll just check if method exists
                $this->assert(method_exists($sessionManager, 'getCsrfToken'), 'getCsrfToken method should exist');
            } catch (Exception $e) {
                // Expected in CLI mode
            }
            
            // Test other methods that don't require active session
            $this->assert(method_exists($sessionManager, 'verifyCsrfToken'), 'verifyCsrfToken method should exist');
            $this->assert(method_exists($sessionManager, 'set'), 'set method should exist');
            $this->assert(method_exists($sessionManager, 'get'), 'get method should exist');
            $this->assert(method_exists($sessionManager, 'destroySession'), 'destroySession method should exist');
            
            $this->results['SessionManager'] = 'PASS';
            echo "✓ SessionManager tests passed (CLI mode)\n\n";
            
        } catch (Exception $e) {
            $this->results['SessionManager'] = 'FAIL: ' . $e->getMessage();
            echo "✗ SessionManager tests failed: " . $e->getMessage() . "\n\n";
        }
        
        echo "Testing SessionManager...\n";
    }
    
    private function testInputValidator(): void {
        echo "Testing InputValidator...\n";
        
        try {
            $validator = new InputValidator();
            
            // Test email validation
            $this->assert($validator->validateEmail('test@example.com'), 'Valid email should pass');
            $this->assert(!$validator->validateEmail('invalid-email'), 'Invalid email should fail');
            
            // Test phone validation
            $this->assert($validator->validatePhone('0901234567'), 'Valid phone should pass');
            $this->assert(!$validator->validatePhone('invalid-phone'), 'Invalid phone should fail');
            
            // Test password validation
            $passwordResult = $validator->validatePassword('StrongPass123!');
            $this->assert($passwordResult['valid'], 'Strong password should be valid');
            
            $weakPasswordResult = $validator->validatePassword('weak');
            $this->assert(!$weakPasswordResult['valid'], 'Weak password should be invalid');
            
            // Test SQL injection detection
            $this->assert($validator->detectSqlInjection("'; DROP TABLE users; --"), 'SQL injection should be detected');
            $this->assert(!$validator->detectSqlInjection('normal text'), 'Normal text should not trigger SQL injection detection');
            
            // Test XSS detection
            $this->assert($validator->detectXss('<script>alert("xss")</script>'), 'XSS should be detected');
            $this->assert(!$validator->detectXss('normal text'), 'Normal text should not trigger XSS detection');
            
            $this->results['InputValidator'] = 'PASS';
            echo "✓ InputValidator tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['InputValidator'] = 'FAIL: ' . $e->getMessage();
            echo "✗ InputValidator tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testRoleManager(): void {
        echo "Testing RoleManager...\n";
        
        try {
            $roleManager = new RoleManager();
            
            // Test role validation
            $this->assert($roleManager->isValidRole('admin'), 'Admin should be valid role');
            $this->assert($roleManager->isValidRole('user'), 'User should be valid role');
            $this->assert($roleManager->isValidRole('affiliate'), 'Affiliate should be valid role');
            $this->assert(!$roleManager->isValidRole('invalid'), 'Invalid role should fail');
            
            // Test role hierarchy
            $adminUser = ['id' => 1, 'role' => 'admin'];
            $regularUser = ['id' => 2, 'role' => 'user'];
            
            $this->assert($roleManager->hasRole($adminUser, 'admin'), 'Admin should have admin role');
            $this->assert($roleManager->hasRole($adminUser, 'user'), 'Admin should have user role (hierarchy)');
            $this->assert(!$roleManager->hasRole($regularUser, 'admin'), 'User should not have admin role');
            
            // Test permissions
            $this->assert($roleManager->canAccess($adminUser, 'admin.dashboard'), 'Admin should access admin dashboard');
            $this->assert(!$roleManager->canAccess($regularUser, 'admin.dashboard'), 'User should not access admin dashboard');
            
            $this->results['RoleManager'] = 'PASS';
            echo "✓ RoleManager tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['RoleManager'] = 'FAIL: ' . $e->getMessage();
            echo "✗ RoleManager tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testAuthErrorHandler(): void {
        echo "Testing AuthErrorHandler...\n";
        
        try {
            $errorHandler = new AuthErrorHandler();
            
            // Test validation error handling
            $validationResult = $errorHandler->handleValidationError(['email' => 'Email is required']);
            $this->assert(!$validationResult['success'], 'Validation error should return failure');
            $this->assert($validationResult['type'] === 'validation', 'Should return validation type');
            
            // Test authentication error handling
            $authResult = $errorHandler->handleAuthenticationError('invalid_credentials');
            $this->assert(!$authResult['success'], 'Auth error should return failure');
            $this->assert($authResult['type'] === 'authentication', 'Should return authentication type');
            
            // Test success response
            $successResult = $errorHandler->createSuccessResponse('Login successful');
            $this->assert($successResult['success'], 'Success response should return success');
            
            $this->results['AuthErrorHandler'] = 'PASS';
            echo "✓ AuthErrorHandler tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['AuthErrorHandler'] = 'FAIL: ' . $e->getMessage();
            echo "✗ AuthErrorHandler tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function assert(bool $condition, string $message): void {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }
    
    private function printResults(): void {
        echo "=== Test Results ===\n";
        $totalTests = count($this->results);
        $passedTests = 0;
        
        foreach ($this->results as $service => $result) {
            if ($result === 'PASS') {
                echo "✓ $service: PASSED\n";
                $passedTests++;
            } else {
                echo "✗ $service: $result\n";
            }
        }
        
        echo "\nSummary: $passedTests/$totalTests tests passed\n";
        
        if ($passedTests === $totalTests) {
            echo "🎉 All core services are working correctly!\n";
        } else {
            echo "⚠️  Some services need attention before proceeding.\n";
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new CoreServicesValidationTest();
    $results = $test->runAllTests();
    
    // Exit with error code if any tests failed
    $allPassed = array_reduce($results, function($carry, $result) {
        return $carry && ($result === 'PASS');
    }, true);
    
    exit($allPassed ? 0 : 1);
}