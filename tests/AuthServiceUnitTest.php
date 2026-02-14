<?php
/**
 * AuthService Unit Test (No Database Required)
 * Tests AuthService functionality without database dependency
 */

// Include required services
require_once __DIR__ . '/../app/services/PasswordHasher.php';
require_once __DIR__ . '/../app/services/SessionManager.php';
require_once __DIR__ . '/../app/services/InputValidator.php';
require_once __DIR__ . '/../app/services/RoleManager.php';
require_once __DIR__ . '/../app/services/AuthErrorHandler.php';

class AuthServiceUnitTest {
    private array $results = [];
    
    public function runAllTests(): array {
        echo "=== AuthService Unit Tests (No Database) ===\n\n";
        
        $this->testPasswordHasherIntegration();
        $this->testInputValidatorIntegration();
        $this->testRoleManagerIntegration();
        $this->testAuthErrorHandlerIntegration();
        $this->testServiceInteractions();
        
        $this->printResults();
        return $this->results;
    }
    
    private function testPasswordHasherIntegration(): void {
        echo "Testing PasswordHasher Integration...\n";
        
        try {
            $hasher = new PasswordHasher();
            
            // Test password hashing workflow
            $password = 'UserPassword123!';
            $hash = $hasher->hash($password);
            
            $this->assert(!empty($hash), 'Password hash should not be empty');
            $this->assert($hasher->verify($password, $hash), 'Password verification should work');
            $this->assert(!$hasher->verify('WrongPassword', $hash), 'Wrong password should fail');
            
            // Test reset token workflow
            $resetData = $hasher->generatePasswordResetData('test@example.com');
            $this->assert(isset($resetData['token']), 'Reset data should contain token');
            $this->assert(isset($resetData['expires_at']), 'Reset data should contain expiration');
            
            // Test token validation
            $validation = $hasher->validateResetTokenData($resetData, $resetData['token']);
            $this->assert($validation['valid'], 'Valid token should pass validation');
            
            $this->results['PasswordHasher Integration'] = 'PASS';
            echo "✓ PasswordHasher integration tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['PasswordHasher Integration'] = 'FAIL: ' . $e->getMessage();
            echo "✗ PasswordHasher integration tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testInputValidatorIntegration(): void {
        echo "Testing InputValidator Integration...\n";
        
        try {
            $validator = new InputValidator();
            
            // Test login validation workflow
            $loginData = [
                'login' => 'test@example.com',
                'password' => 'TestPassword123!'
            ];
            
            $loginResult = $validator->validateLogin($loginData);
            $this->assert($loginResult['valid'], 'Valid login data should pass');
            $this->assert(isset($loginResult['data']), 'Validation should return sanitized data');
            
            // Test registration validation workflow
            $registerData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'StrongPassword123!',
                'password_confirmation' => 'StrongPassword123!'
            ];
            
            $registerResult = $validator->validateRegister($registerData);
            $this->assert($registerResult['valid'], 'Valid registration data should pass');
            
            // Test security detection
            $this->assert($validator->detectSqlInjection("'; DROP TABLE users; --"), 'SQL injection should be detected');
            $this->assert($validator->detectXss('<script>alert("xss")</script>'), 'XSS should be detected');
            
            $this->results['InputValidator Integration'] = 'PASS';
            echo "✓ InputValidator integration tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['InputValidator Integration'] = 'FAIL: ' . $e->getMessage();
            echo "✗ InputValidator integration tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testRoleManagerIntegration(): void {
        echo "Testing RoleManager Integration...\n";
        
        try {
            $roleManager = new RoleManager();
            
            // Test role-based access workflow
            $adminUser = ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com'];
            $regularUser = ['id' => 2, 'role' => 'user', 'email' => 'user@example.com'];
            $affiliateUser = ['id' => 3, 'role' => 'affiliate', 'email' => 'affiliate@example.com'];
            
            // Test admin access
            $this->assert($roleManager->canAccess($adminUser, 'admin.dashboard'), 'Admin should access admin dashboard');
            $this->assert($roleManager->canAccess($adminUser, 'user.dashboard'), 'Admin should access user dashboard');
            
            // Test user restrictions
            $this->assert(!$roleManager->canAccess($regularUser, 'admin.dashboard'), 'User should not access admin dashboard');
            $this->assert($roleManager->canAccess($regularUser, 'user.dashboard'), 'User should access user dashboard');
            
            // Test affiliate access
            $this->assert(!$roleManager->canAccess($affiliateUser, 'admin.dashboard'), 'Affiliate should not access admin dashboard');
            $this->assert($roleManager->canAccess($affiliateUser, 'affiliate.dashboard'), 'Affiliate should access affiliate dashboard');
            
            // Test redirect paths
            $adminRedirect = $roleManager->getRedirectPath($adminUser);
            $userRedirect = $roleManager->getRedirectPath($regularUser);
            $affiliateRedirect = $roleManager->getRedirectPath($affiliateUser);
            
            $this->assert($adminRedirect === '/admin/dashboard', 'Admin should redirect to admin dashboard');
            $this->assert($userRedirect === '/users/dashboard', 'User should redirect to user dashboard');
            $this->assert($affiliateRedirect === '/affiliate/dashboard', 'Affiliate should redirect to affiliate dashboard');
            
            $this->results['RoleManager Integration'] = 'PASS';
            echo "✓ RoleManager integration tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['RoleManager Integration'] = 'FAIL: ' . $e->getMessage();
            echo "✗ RoleManager integration tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testAuthErrorHandlerIntegration(): void {
        echo "Testing AuthErrorHandler Integration...\n";
        
        try {
            $errorHandler = new AuthErrorHandler();
            
            // Test error handling workflow
            $validationErrors = ['email' => 'Email is required', 'password' => 'Password is required'];
            $validationResult = $errorHandler->handleValidationError($validationErrors);
            
            $this->assert(!$validationResult['success'], 'Validation error should return failure');
            $this->assert($validationResult['type'] === 'validation', 'Should return validation type');
            $this->assert(count($validationResult['errors']) === 2, 'Should return all validation errors');
            
            // Test authentication error handling
            $authResult = $errorHandler->handleAuthenticationError('invalid_credentials');
            $this->assert(!$authResult['success'], 'Auth error should return failure');
            $this->assert($authResult['type'] === 'authentication', 'Should return authentication type');
            $this->assert(!empty($authResult['message']), 'Should return error message');
            
            // Test security error handling
            $securityResult = $errorHandler->handleSecurityError('csrf_mismatch');
            $this->assert(!$securityResult['success'], 'Security error should return failure');
            $this->assert($securityResult['type'] === 'security', 'Should return security type');
            
            // Test success response
            $successResult = $errorHandler->createSuccessResponse('Operation successful', ['user_id' => 1]);
            $this->assert($successResult['success'], 'Success response should return success');
            $this->assert(isset($successResult['data']['user_id']), 'Success response should include data');
            
            $this->results['AuthErrorHandler Integration'] = 'PASS';
            echo "✓ AuthErrorHandler integration tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['AuthErrorHandler Integration'] = 'FAIL: ' . $e->getMessage();
            echo "✗ AuthErrorHandler integration tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testServiceInteractions(): void {
        echo "Testing Service Interactions...\n";
        
        try {
            // Test services working together
            $hasher = new PasswordHasher();
            $validator = new InputValidator();
            $roleManager = new RoleManager();
            $errorHandler = new AuthErrorHandler();
            
            // Simulate registration workflow
            $registrationData = [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!'
            ];
            
            // Step 1: Validate input
            $validationResult = $validator->validateRegister($registrationData);
            $this->assert($validationResult['valid'], 'Registration data should be valid');
            
            // Step 2: Hash password
            $hashedPassword = $hasher->hash($registrationData['password']);
            $this->assert(!empty($hashedPassword), 'Password should be hashed');
            
            // Step 3: Verify password
            $this->assert($hasher->verify($registrationData['password'], $hashedPassword), 'Password verification should work');
            
            // Simulate login workflow
            $user = [
                'id' => 1,
                'email' => 'newuser@example.com',
                'role' => 'user',
                'password' => $hashedPassword
            ];
            
            // Step 4: Check role permissions
            $this->assert($roleManager->hasRole($user, 'user'), 'User should have user role');
            $this->assert($roleManager->canAccess($user, 'user.dashboard'), 'User should access user dashboard');
            
            // Step 5: Generate success response
            $successResponse = $errorHandler->createSuccessResponse('Login successful', $user);
            $this->assert($successResponse['success'], 'Should create success response');
            
            $this->results['Service Interactions'] = 'PASS';
            echo "✓ Service interaction tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['Service Interactions'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Service interaction tests failed: " . $e->getMessage() . "\n\n";
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
            echo "🎉 All authentication services are working correctly!\n";
            echo "✅ Ready for next phase: AuthController and database integration\n";
        } else {
            echo "⚠️  Some services need attention before proceeding.\n";
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AuthServiceUnitTest();
    $results = $test->runAllTests();
    
    // Exit with error code if any tests failed
    $allPassed = array_reduce($results, function($carry, $result) {
        return $carry && ($result === 'PASS');
    }, true);
    
    exit($allPassed ? 0 : 1);
}