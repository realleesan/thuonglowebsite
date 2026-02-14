<?php
/**
 * Authentication System Integration Test
 * Tests integration with existing MVC structure
 * Requirements: 8.1, 8.3, 8.5
 */

require_once __DIR__ . '/../app/services/AuthService.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../core/database.php';

class AuthSystemIntegrationTest {
    private AuthService $authService;
    private AuthController $authController;
    private AuthMiddleware $authMiddleware;
    private array $testResults = [];
    
    public function __construct() {
        $this->authService = new AuthService();
        $this->authController = new AuthController();
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Run all integration tests
     */
    public function runAllTests(): array {
        echo "=== Authentication System Integration Tests ===\n\n";
        
        $this->testServiceInterfaceCompliance();
        $this->testDatabaseIntegration();
        $this->testControllerIntegration();
        $this->testMiddlewareIntegration();
        $this->testSessionIntegration();
        $this->testSecurityIntegration();
        $this->testErrorHandlingIntegration();
        
        return $this->generateReport();
    }
    
    /**
     * Test service interface compliance
     * Requirement: 8.3
     */
    private function testServiceInterfaceCompliance(): void {
        echo "Testing Service Interface Compliance...\n";
        
        try {
            // Test that AuthService implements ServiceInterface
            $this->assert(
                $this->authService instanceof ServiceInterface,
                "AuthService implements ServiceInterface"
            );
            
            // Test getData method exists and works
            $result = $this->authService->getData('getCurrentUser');
            $this->assert(
                is_array($result),
                "getData method returns array"
            );
            
            // Test getModel method exists and works
            $model = $this->authService->getModel('UsersModel');
            $this->assert(
                $model instanceof UsersModel || $model === null,
                "getModel method returns correct type"
            );
            
            // Test handleError method exists and works
            $error = new Exception("Test error");
            $result = $this->authService->handleError($error);
            $this->assert(
                is_array($result) && isset($result['success']) && !$result['success'],
                "handleError method returns proper error format"
            );
            
            echo "✓ Service Interface Compliance: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Service Interface Compliance: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['service_interface'] = false;
        }
    }
    
    /**
     * Test database integration
     * Requirement: 8.5
     */
    private function testDatabaseIntegration(): void {
        echo "Testing Database Integration...\n";
        
        try {
            // Test database connection reuse
            $db1 = Database::getInstance();
            $db2 = Database::getInstance();
            $this->assert(
                $db1 === $db2,
                "Database connection is reused (singleton pattern)"
            );
            
            // Test that AuthService uses existing database infrastructure
            $usersModel = $this->authService->getModel('UsersModel');
            $this->assert(
                $usersModel !== null,
                "AuthService can access UsersModel"
            );
            
            // Test database operations work
            $testEmail = 'integration_test_' . time() . '@example.com';
            $userData = [
                'name' => 'Integration Test User',
                'email' => $testEmail,
                'phone' => '0123456789',
                'password' => 'TestPassword123!',
                'password_confirmation' => 'TestPassword123!'
            ];
            
            $result = $this->authService->register($userData);
            $this->assert(
                $result['success'] === true,
                "User registration works with database"
            );
            
            // Clean up test user
            if (isset($result['data']['user']['id'])) {
                $usersModel->delete($result['data']['user']['id']);
            }
            
            echo "✓ Database Integration: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Database Integration: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['database_integration'] = false;
        }
    }
    
    /**
     * Test controller integration
     * Requirement: 8.1
     */
    private function testControllerIntegration(): void {
        echo "Testing Controller Integration...\n";
        
        try {
            // Test that AuthController uses AuthService
            $reflection = new ReflectionClass($this->authController);
            $properties = $reflection->getProperties();
            
            $hasAuthService = false;
            foreach ($properties as $property) {
                if ($property->getName() === 'authService') {
                    $hasAuthService = true;
                    break;
                }
            }
            
            $this->assert(
                $hasAuthService,
                "AuthController has AuthService property"
            );
            
            // Test controller methods exist
            $methods = ['login', 'register', 'logout', 'forgot', 'reset'];
            foreach ($methods as $method) {
                $this->assert(
                    method_exists($this->authController, $method),
                    "AuthController has {$method} method"
                );
            }
            
            // Test controller follows MVC pattern
            $this->assert(
                method_exists($this->authController, 'render'),
                "AuthController has render method for views"
            );
            
            echo "✓ Controller Integration: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Controller Integration: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['controller_integration'] = false;
        }
    }
    
    /**
     * Test middleware integration
     */
    private function testMiddlewareIntegration(): void {
        echo "Testing Middleware Integration...\n";
        
        try {
            // Test middleware methods exist
            $this->assert(
                method_exists($this->authMiddleware, 'checkAuth'),
                "AuthMiddleware has checkAuth method"
            );
            
            $this->assert(
                method_exists($this->authMiddleware, 'checkRole'),
                "AuthMiddleware has checkRole method"
            );
            
            // Test middleware helper functions exist
            $this->assert(
                function_exists('requireAuth'),
                "requireAuth global function exists"
            );
            
            $this->assert(
                function_exists('requireRole'),
                "requireRole global function exists"
            );
            
            $this->assert(
                function_exists('getCurrentUser'),
                "getCurrentUser global function exists"
            );
            
            echo "✓ Middleware Integration: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Middleware Integration: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['middleware_integration'] = false;
        }
    }
    
    /**
     * Test session integration
     */
    private function testSessionIntegration(): void {
        echo "Testing Session Integration...\n";
        
        try {
            // Test session management
            $sessionManager = new SessionManager();
            
            // Test session methods exist
            $this->assert(
                method_exists($sessionManager, 'createSession'),
                "SessionManager has createSession method"
            );
            
            $this->assert(
                method_exists($sessionManager, 'destroySession'),
                "SessionManager has destroySession method"
            );
            
            $this->assert(
                method_exists($sessionManager, 'isValid'),
                "SessionManager has isValid method"
            );
            
            // Test CSRF token functionality
            $this->assert(
                method_exists($sessionManager, 'getCsrfToken'),
                "SessionManager has getCsrfToken method"
            );
            
            $this->assert(
                method_exists($sessionManager, 'verifyCsrfToken'),
                "SessionManager has verifyCsrfToken method"
            );
            
            echo "✓ Session Integration: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Session Integration: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['session_integration'] = false;
        }
    }
    
    /**
     * Test security integration
     */
    private function testSecurityIntegration(): void {
        echo "Testing Security Integration...\n";
        
        try {
            // Test security services exist
            $securityLogger = new SecurityLogger();
            $securityMonitor = new SecurityMonitor();
            $inputValidator = new InputValidator();
            $passwordHasher = new PasswordHasher();
            
            // Test security logging
            $this->assert(
                method_exists($securityLogger, 'logAuthAttempt'),
                "SecurityLogger has logAuthAttempt method"
            );
            
            // Test security monitoring
            $this->assert(
                method_exists($securityMonitor, 'monitorAuthAttempts'),
                "SecurityMonitor has monitorAuthAttempts method"
            );
            
            // Test input validation
            $this->assert(
                method_exists($inputValidator, 'sanitizeInput'),
                "InputValidator has sanitizeInput method"
            );
            
            // Test password hashing
            $this->assert(
                method_exists($passwordHasher, 'hash'),
                "PasswordHasher has hash method"
            );
            
            // Test that logs directory exists or can be created
            $logsDir = __DIR__ . '/../logs';
            if (!is_dir($logsDir)) {
                mkdir($logsDir, 0755, true);
            }
            $this->assert(
                is_dir($logsDir) && is_writable($logsDir),
                "Logs directory exists and is writable"
            );
            
            echo "✓ Security Integration: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Security Integration: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['security_integration'] = false;
        }
    }
    
    /**
     * Test error handling integration
     */
    private function testErrorHandlingIntegration(): void {
        echo "Testing Error Handling Integration...\n";
        
        try {
            // Test error handler exists
            $errorHandler = new AuthErrorHandler();
            
            // Test error handling methods
            $this->assert(
                method_exists($errorHandler, 'handleValidationError'),
                "AuthErrorHandler has handleValidationError method"
            );
            
            $this->assert(
                method_exists($errorHandler, 'handleAuthenticationError'),
                "AuthErrorHandler has handleAuthenticationError method"
            );
            
            $this->assert(
                method_exists($errorHandler, 'handleSystemError'),
                "AuthErrorHandler has handleSystemError method"
            );
            
            // Test error response format
            $result = $errorHandler->handleValidationError(['test' => 'error']);
            $this->assert(
                is_array($result) && isset($result['success']) && !$result['success'],
                "Error handler returns proper format"
            );
            
            echo "✓ Error Handling Integration: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Error Handling Integration: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['error_handling_integration'] = false;
        }
    }
    
    /**
     * Assert helper method
     */
    private function assert(bool $condition, string $message): void {
        if (!$condition) {
            throw new Exception("Assertion failed: {$message}");
        }
        $this->testResults[$message] = true;
    }
    
    /**
     * Generate test report
     */
    private function generateReport(): array {
        $passed = 0;
        $total = count($this->testResults);
        
        foreach ($this->testResults as $result) {
            if ($result) $passed++;
        }
        
        $report = [
            'total_tests' => $total,
            'passed' => $passed,
            'failed' => $total - $passed,
            'success_rate' => $total > 0 ? round(($passed / $total) * 100, 2) : 0,
            'details' => $this->testResults
        ];
        
        echo "=== Integration Test Report ===\n";
        echo "Total Tests: {$report['total_tests']}\n";
        echo "Passed: {$report['passed']}\n";
        echo "Failed: {$report['failed']}\n";
        echo "Success Rate: {$report['success_rate']}%\n\n";
        
        if ($report['failed'] > 0) {
            echo "Failed Tests:\n";
            foreach ($this->testResults as $test => $result) {
                if (!$result) {
                    echo "- {$test}\n";
                }
            }
            echo "\n";
        }
        
        return $report;
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AuthSystemIntegrationTest();
    $report = $test->runAllTests();
    
    // Exit with appropriate code
    exit($report['failed'] > 0 ? 1 : 0);
}