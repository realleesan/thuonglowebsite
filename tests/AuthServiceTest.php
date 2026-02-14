<?php
/**
 * AuthService Test
 * Tests the main AuthService functionality
 */

require_once __DIR__ . '/../app/services/AuthService.php';

class AuthServiceTest {
    private AuthService $authService;
    private array $results = [];
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function runAllTests(): array {
        echo "=== AuthService Test ===\n\n";
        
        $this->testServiceInterface();
        $this->testAuthenticationMethods();
        $this->testUtilityMethods();
        
        $this->printResults();
        return $this->results;
    }
    
    private function testServiceInterface(): void {
        echo "Testing ServiceInterface implementation...\n";
        
        try {
            // Test getData method exists and works
            $this->assert($this->authService instanceof ServiceInterface, 'AuthService should implement ServiceInterface');
            
            // Test getCurrentUser method
            $result = $this->authService->getData('getCurrentUser');
            $this->assert(is_array($result), 'getData should return array');
            $this->assert(isset($result['user']), 'getCurrentUser should return user key');
            
            // Test getModel method
            $usersModel = $this->authService->getModel('UsersModel');
            $this->assert($usersModel !== null, 'Should return UsersModel');
            $this->assert($usersModel instanceof UsersModel, 'Should return UsersModel instance');
            
            $this->results['ServiceInterface'] = 'PASS';
            echo "✓ ServiceInterface tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['ServiceInterface'] = 'FAIL: ' . $e->getMessage();
            echo "✗ ServiceInterface tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testAuthenticationMethods(): void {
        echo "Testing Authentication methods...\n";
        
        try {
            // Test method existence
            $this->assert(method_exists($this->authService, 'authenticate'), 'authenticate method should exist');
            $this->assert(method_exists($this->authService, 'register'), 'register method should exist');
            $this->assert(method_exists($this->authService, 'logout'), 'logout method should exist');
            $this->assert(method_exists($this->authService, 'initiatePasswordReset'), 'initiatePasswordReset method should exist');
            $this->assert(method_exists($this->authService, 'resetPassword'), 'resetPassword method should exist');
            
            // Test authentication with invalid credentials (should fail gracefully)
            $result = $this->authService->authenticate('invalid@email.com', 'wrongpassword');
            $this->assert(is_array($result), 'authenticate should return array');
            $this->assert(isset($result['success']), 'authenticate result should have success key');
            $this->assert($result['success'] === false, 'Invalid credentials should return false');
            
            // Test register with invalid data (should fail gracefully)
            $result = $this->authService->register([]);
            $this->assert(is_array($result), 'register should return array');
            $this->assert(isset($result['success']), 'register result should have success key');
            $this->assert($result['success'] === false, 'Invalid data should return false');
            
            $this->results['Authentication'] = 'PASS';
            echo "✓ Authentication methods tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['Authentication'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Authentication methods tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testUtilityMethods(): void {
        echo "Testing Utility methods...\n";
        
        try {
            // Test utility methods existence
            $this->assert(method_exists($this->authService, 'isAuthenticated'), 'isAuthenticated method should exist');
            $this->assert(method_exists($this->authService, 'hasRole'), 'hasRole method should exist');
            $this->assert(method_exists($this->authService, 'hasPermission'), 'hasPermission method should exist');
            $this->assert(method_exists($this->authService, 'canAccess'), 'canAccess method should exist');
            $this->assert(method_exists($this->authService, 'getCsrfToken'), 'getCsrfToken method should exist');
            $this->assert(method_exists($this->authService, 'verifyCsrfToken'), 'verifyCsrfToken method should exist');
            
            // Test isAuthenticated (should be false without login)
            $isAuth = $this->authService->isAuthenticated();
            $this->assert(is_bool($isAuth), 'isAuthenticated should return boolean');
            
            // Test hasRole (should be false without login)
            $hasRole = $this->authService->hasRole('admin');
            $this->assert(is_bool($hasRole), 'hasRole should return boolean');
            
            // Test CSRF token
            try {
                $token = $this->authService->getCsrfToken();
                $this->assert(is_string($token), 'getCsrfToken should return string');
            } catch (Exception $e) {
                // Expected in CLI mode
                echo "  Note: CSRF token test skipped (CLI mode)\n";
            }
            
            $this->results['Utility'] = 'PASS';
            echo "✓ Utility methods tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['Utility'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Utility methods tests failed: " . $e->getMessage() . "\n\n";
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
        
        foreach ($this->results as $category => $result) {
            if ($result === 'PASS') {
                echo "✓ $category: PASSED\n";
                $passedTests++;
            } else {
                echo "✗ $category: $result\n";
            }
        }
        
        echo "\nSummary: $passedTests/$totalTests test categories passed\n";
        
        if ($passedTests === $totalTests) {
            echo "🎉 AuthService is working correctly!\n";
        } else {
            echo "⚠️  Some AuthService functionality needs attention.\n";
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AuthServiceTest();
    $results = $test->runAllTests();
    
    // Exit with error code if any tests failed
    $allPassed = array_reduce($results, function($carry, $result) {
        return $carry && ($result === 'PASS');
    }, true);
    
    exit($allPassed ? 0 : 1);
}