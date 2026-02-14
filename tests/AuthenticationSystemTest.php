<?php
/**
 * Authentication System Integration Test
 * Tests the complete authentication system without database
 */

// Include all required components
require_once __DIR__ . '/../app/services/AuthService.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

class AuthenticationSystemTest {
    private array $results = [];
    
    public function runAllTests(): array {
        echo "=== Authentication System Integration Test ===\n\n";
        
        $this->testAuthServiceIntegration();
        $this->testAuthControllerIntegration();
        $this->testSecurityFeatures();
        $this->testErrorHandling();
        $this->testMiddlewareFunctions();
        
        $this->printResults();
        return $this->results;
    }
    
    private function testAuthServiceIntegration(): void {
        echo "Testing AuthService Integration...\n";
        
        try {
            // Test AuthService can be instantiated (without database)
            // We'll mock the database dependency by catching the exception
            try {
                $authService = new AuthService();
                // If we get here, database is available
                $this->assert(true, 'AuthService should be instantiable');
            } catch (Exception $e) {
                // Expected if database is not available
                $this->assert(strpos($e->getMessage(), 'Database') !== false, 'Should fail with database error');
            }
            
            // Test ServiceInterface compliance
            $reflection = new ReflectionClass('AuthService');
            $this->assert($reflection->implementsInterface('ServiceInterface'), 'AuthService should implement ServiceInterface');
            
            // Test required methods exist
            $requiredMethods = ['getData', 'getModel', 'handleError'];
            foreach ($requiredMethods as $method) {
                $this->assert($reflection->hasMethod($method), "AuthService should have {$method} method");
            }
            
            // Test authentication methods exist
            $authMethods = ['authenticate', 'register', 'logout', 'getCurrentUser', 'hasRole', 'hasPermission'];
            foreach ($authMethods as $method) {
                $this->assert($reflection->hasMethod($method), "AuthService should have {$method} method");
            }
            
            $this->results['AuthService Integration'] = 'PASS';
            echo "✓ AuthService integration tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['AuthService Integration'] = 'FAIL: ' . $e->getMessage();
            echo "✗ AuthService integration tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testAuthControllerIntegration(): void {
        echo "Testing AuthController Integration...\n";
        
        try {
            // Test AuthController can be instantiated (without database)
            try {
                $authController = new AuthController();
                $this->assert(true, 'AuthController should be instantiable');
            } catch (Exception $e) {
                // Expected if database is not available
                $this->assert(strpos($e->getMessage(), 'Database') !== false, 'Should fail with database error');
            }
            
            // Test required methods exist
            $reflection = new ReflectionClass('AuthController');
            
            $controllerMethods = [
                'login', 'processLogin', 'register', 'processRegister',
                'forgot', 'processForgot', 'resetPassword', 'processReset',
                'logout', 'checkAuth', 'requireRole', 'requirePermission'
            ];
            
            foreach ($controllerMethods as $method) {
                $this->assert($reflection->hasMethod($method), "AuthController should have {$method} method");
            }
            
            // Test middleware methods exist
            $middlewareMethods = ['checkAuth', 'requireRole', 'requirePermission', 'requireAdmin'];
            foreach ($middlewareMethods as $method) {
                $this->assert($reflection->hasMethod($method), "AuthController should have {$method} middleware method");
            }
            
            // Test helper methods exist
            $helperMethods = ['getCurrentUser', 'hasRole', 'hasPermission', 'getCsrfToken'];
            foreach ($helperMethods as $method) {
                $this->assert($reflection->hasMethod($method), "AuthController should have {$method} helper method");
            }
            
            $this->results['AuthController Integration'] = 'PASS';
            echo "✓ AuthController integration tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['AuthController Integration'] = 'FAIL: ' . $e->getMessage();
            echo "✗ AuthController integration tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testSecurityFeatures(): void {
        echo "Testing Security Features...\n";
        
        try {
            // Test all security service classes exist
            $securityClasses = [
                'PasswordHasher' => 'Password hashing and token generation',
                'SessionManager' => 'Session management and CSRF protection',
                'InputValidator' => 'Input validation and sanitization',
                'RoleManager' => 'Role-based access control',
                'AuthErrorHandler' => 'Error handling and security logging',
                'SecurityException' => 'Security exception handling'
            ];
            
            foreach ($securityClasses as $className => $description) {
                $this->assert(class_exists($className), "{$className} class should exist for {$description}");
            }
            
            // Test PasswordHasher security features
            if (class_exists('PasswordHasher')) {
                $hasher = new PasswordHasher();
                $reflection = new ReflectionClass('PasswordHasher');
                
                $securityMethods = ['hash', 'verify', 'generateResetToken', 'verifyResetToken'];
                foreach ($securityMethods as $method) {
                    $this->assert($reflection->hasMethod($method), "PasswordHasher should have {$method} method");
                }
            }
            
            // Test InputValidator security features
            if (class_exists('InputValidator')) {
                $validator = new InputValidator();
                $reflection = new ReflectionClass('InputValidator');
                
                $validationMethods = ['detectSqlInjection', 'detectXss', 'sanitizeInput'];
                foreach ($validationMethods as $method) {
                    $this->assert($reflection->hasMethod($method), "InputValidator should have {$method} method");
                }
                
                // Test actual security detection
                $this->assert($validator->detectSqlInjection("'; DROP TABLE users; --"), 'Should detect SQL injection');
                $this->assert($validator->detectXss('<script>alert("xss")</script>'), 'Should detect XSS');
            }
            
            $this->results['Security Features'] = 'PASS';
            echo "✓ Security features tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['Security Features'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Security features tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testErrorHandling(): void {
        echo "Testing Error Handling...\n";
        
        try {
            // Test AuthErrorHandler functionality
            if (class_exists('AuthErrorHandler')) {
                $errorHandler = new AuthErrorHandler();
                
                // Test validation error handling
                $validationResult = $errorHandler->handleValidationError(['email' => 'Required']);
                $this->assert(!$validationResult['success'], 'Validation error should return failure');
                $this->assert($validationResult['type'] === 'validation', 'Should return validation type');
                
                // Test authentication error handling
                $authResult = $errorHandler->handleAuthenticationError('invalid_credentials');
                $this->assert(!$authResult['success'], 'Auth error should return failure');
                $this->assert($authResult['type'] === 'authentication', 'Should return authentication type');
                
                // Test security error handling
                $securityResult = $errorHandler->handleSecurityError('csrf_mismatch');
                $this->assert(!$securityResult['success'], 'Security error should return failure');
                $this->assert($securityResult['type'] === 'security', 'Should return security type');
                
                // Test success response
                $successResult = $errorHandler->createSuccessResponse('Success');
                $this->assert($successResult['success'], 'Success response should return success');
            }
            
            // Test SecurityException
            if (class_exists('SecurityException')) {
                try {
                    throw new SecurityException('Test security exception', 'test_type');
                } catch (SecurityException $e) {
                    $this->assert($e->getSecurityType() === 'test_type', 'SecurityException should store security type');
                    $this->assert($e->getMessage() === 'Test security exception', 'SecurityException should store message');
                }
            }
            
            $this->results['Error Handling'] = 'PASS';
            echo "✓ Error handling tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['Error Handling'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Error handling tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testMiddlewareFunctions(): void {
        echo "Testing Middleware Functions...\n";
        
        try {
            // Test RoleManager functionality
            if (class_exists('RoleManager')) {
                $roleManager = new RoleManager();
                
                // Test role hierarchy
                $adminUser = ['id' => 1, 'role' => 'admin'];
                $regularUser = ['id' => 2, 'role' => 'user'];
                
                $this->assert($roleManager->hasRole($adminUser, 'admin'), 'Admin should have admin role');
                $this->assert($roleManager->hasRole($adminUser, 'user'), 'Admin should have user role (hierarchy)');
                $this->assert(!$roleManager->hasRole($regularUser, 'admin'), 'User should not have admin role');
                
                // Test permissions
                $this->assert($roleManager->canAccess($adminUser, 'admin.dashboard'), 'Admin should access admin dashboard');
                $this->assert(!$roleManager->canAccess($regularUser, 'admin.dashboard'), 'User should not access admin dashboard');
                
                // Test redirect paths
                $adminRedirect = $roleManager->getRedirectPath($adminUser);
                $userRedirect = $roleManager->getRedirectPath($regularUser);
                
                $this->assert($adminRedirect === '/admin/dashboard', 'Admin should redirect to admin dashboard');
                $this->assert($userRedirect === '/users/dashboard', 'User should redirect to user dashboard');
            }
            
            $this->results['Middleware Functions'] = 'PASS';
            echo "✓ Middleware functions tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['Middleware Functions'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Middleware functions tests failed: " . $e->getMessage() . "\n\n";
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
        
        foreach ($this->results as $component => $result) {
            if ($result === 'PASS') {
                echo "✓ $component: PASSED\n";
                $passedTests++;
            } else {
                echo "✗ $component: $result\n";
            }
        }
        
        echo "\nSummary: $passedTests/$totalTests tests passed\n";
        
        if ($passedTests === $totalTests) {
            echo "🎉 Complete Authentication System is ready!\n";
            echo "✅ All components integrated successfully\n";
            echo "🔐 Security features validated\n";
            echo "🛡️  Error handling and middleware working\n";
            echo "\n📋 Ready for database integration and live testing\n";
        } else {
            echo "⚠️  Some components need attention before deployment.\n";
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AuthenticationSystemTest();
    $results = $test->runAllTests();
    
    // Exit with error code if any tests failed
    $allPassed = array_reduce($results, function($carry, $result) {
        return $carry && ($result === 'PASS');
    }, true);
    
    exit($allPassed ? 0 : 1);
}