<?php
/**
 * Security Hardening Test
 * Comprehensive security validation tests
 * Requirements: 6.5, 7.1, 7.4
 */

require_once __DIR__ . '/../app/services/SecurityHeaders.php';
require_once __DIR__ . '/../app/services/InputValidator.php';
require_once __DIR__ . '/../app/services/SessionManager.php';

class SecurityHardeningTest {
    private array $testResults = [];
    private SecurityHeaders $securityHeaders;
    private InputValidator $inputValidator;
    
    public function __construct() {
        $this->securityHeaders = new SecurityHeaders();
        $this->inputValidator = new InputValidator();
    }
    
    /**
     * Run all security hardening tests
     */
    public function runAllTests(): array {
        echo "=== Security Hardening Tests ===\n\n";
        
        $this->testSecurityHeaders();
        $this->testCsrfProtection();
        $this->testInputValidation();
        $this->testSessionSecurity();
        $this->testCookieSecurity();
        $this->testInformationDisclosure();
        
        return $this->generateReport();
    }
    
    /**
     * Test security headers implementation
     */
    private function testSecurityHeaders(): void {
        echo "Testing Security Headers...\n";
        
        try {
            // Test security headers class exists
            $this->assert(
                class_exists('SecurityHeaders'),
                "SecurityHeaders class exists"
            );
            
            // Test security headers methods
            $this->assert(
                method_exists($this->securityHeaders, 'setSecurityHeaders'),
                "SecurityHeaders has setSecurityHeaders method"
            );
            
            $this->assert(
                method_exists($this->securityHeaders, 'configureSecureSessions'),
                "SecurityHeaders has configureSecureSessions method"
            );
            
            $this->assert(
                method_exists($this->securityHeaders, 'setSecureCookieDefaults'),
                "SecurityHeaders has setSecureCookieDefaults method"
            );
            
            // Test CSRF token methods
            $this->assert(
                method_exists($this->securityHeaders, 'generateCsrfToken'),
                "SecurityHeaders has generateCsrfToken method"
            );
            
            $this->assert(
                method_exists($this->securityHeaders, 'verifyCsrfToken'),
                "SecurityHeaders has verifyCsrfToken method"
            );
            
            echo "✓ Security Headers: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Security Headers: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['security_headers'] = false;
        }
    }
    
    /**
     * Test CSRF protection
     */
    private function testCsrfProtection(): void {
        echo "Testing CSRF Protection...\n";
        
        try {
            // Ensure clean session state
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            
            // Start fresh session for CSRF testing
            session_start();
            
            // Test CSRF token generation
            $token1 = $this->securityHeaders->generateCsrfToken();
            $this->assert(
                !empty($token1) && strlen($token1) === 64,
                "CSRF token generated with correct length"
            );
            
            // Test CSRF token verification (should work immediately after generation)
            $isValid = $this->securityHeaders->verifyCsrfToken($token1);
            $this->assert(
                $isValid,
                "CSRF token verification works"
            );
            
            // Test invalid token rejection
            $this->assert(
                !$this->securityHeaders->verifyCsrfToken('invalid_token'),
                "Invalid CSRF token rejected"
            );
            
            // Test form CSRF token
            $formToken = $this->securityHeaders->getCsrfTokenForForm();
            $this->assert(
                !empty($formToken),
                "Form CSRF token generated"
            );
            
            // Form token verification uses different logic
            $formValid = $this->securityHeaders->verifyCsrfTokenFromForm($formToken);
            $this->assert(
                $formValid,
                "Form CSRF token verification works"
            );
            
            echo "✓ CSRF Protection: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ CSRF Protection: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['csrf_protection'] = false;
        }
    }
    
    /**
     * Test input validation enhancements
     */
    private function testInputValidation(): void {
        echo "Testing Input Validation...\n";
        
        try {
            // Test enhanced validation method exists
            $this->assert(
                method_exists($this->inputValidator, 'validateAndSanitizeInput'),
                "Enhanced input validation method exists"
            );
            
            // Test SQL injection detection
            $sqlInjection = "'; DROP TABLE users; --";
            $this->assert(
                $this->inputValidator->detectSqlInjection($sqlInjection),
                "SQL injection detected"
            );
            
            // Test XSS detection
            $xssAttempt = "<script>alert('xss')</script>";
            $this->assert(
                $this->inputValidator->detectXss($xssAttempt),
                "XSS attempt detected"
            );
            
            // Test directory traversal detection (if method exists)
            if (method_exists($this->inputValidator, 'validateAndSanitizeInput')) {
                $traversal = "../../../etc/passwd";
                $result = $this->inputValidator->validateAndSanitizeInput($traversal);
                $this->assert(
                    !$result['valid'] && in_array('directory_traversal', $result['threats_detected']),
                    "Directory traversal detected"
                );
            }
            
            // Test safe input passes validation
            $safeInput = "Hello World 123";
            $result = $this->inputValidator->validateAndSanitizeInput($safeInput);
            $this->assert(
                $result['valid'] && empty($result['threats_detected']),
                "Safe input passes validation"
            );
            
            echo "✓ Input Validation: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Input Validation: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['input_validation'] = false;
        }
    }
    
    /**
     * Test session security
     */
    private function testSessionSecurity(): void {
        echo "Testing Session Security...\n";
        
        try {
            // Test session manager exists
            $this->assert(
                class_exists('SessionManager'),
                "SessionManager class exists"
            );
            
            // Test session security methods
            $sessionManager = new SessionManager();
            
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
            
            // Test CSRF integration
            $this->assert(
                method_exists($sessionManager, 'getCsrfToken'),
                "SessionManager has CSRF token support"
            );
            
            echo "✓ Session Security: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Session Security: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['session_security'] = false;
        }
    }
    
    /**
     * Test cookie security
     */
    private function testCookieSecurity(): void {
        echo "Testing Cookie Security...\n";
        
        try {
            // Test secure cookie methods
            $this->assert(
                method_exists($this->securityHeaders, 'setSecureCookie'),
                "SecurityHeaders has setSecureCookie method"
            );
            
            $this->assert(
                method_exists($this->securityHeaders, 'setSecureCookieDefaults'),
                "SecurityHeaders has setSecureCookieDefaults method"
            );
            
            // Test secure random generation
            $this->assert(
                method_exists($this->securityHeaders, 'generateSecureRandom'),
                "SecurityHeaders has generateSecureRandom method"
            );
            
            $random = $this->securityHeaders->generateSecureRandom(32);
            $this->assert(
                strlen($random) === 32,
                "Secure random string generated with correct length"
            );
            
            // Test secure comparison
            $this->assert(
                method_exists($this->securityHeaders, 'secureCompare'),
                "SecurityHeaders has secureCompare method"
            );
            
            $this->assert(
                $this->securityHeaders->secureCompare('test', 'test'),
                "Secure comparison works for equal strings"
            );
            
            $this->assert(
                !$this->securityHeaders->secureCompare('test', 'different'),
                "Secure comparison works for different strings"
            );
            
            echo "✓ Cookie Security: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Cookie Security: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['cookie_security'] = false;
        }
    }
    
    /**
     * Test information disclosure prevention
     */
    private function testInformationDisclosure(): void {
        echo "Testing Information Disclosure Prevention...\n";
        
        try {
            // Test information disclosure prevention methods
            $this->assert(
                method_exists($this->securityHeaders, 'preventInformationDisclosure'),
                "SecurityHeaders has preventInformationDisclosure method"
            );
            
            $this->assert(
                method_exists($this->securityHeaders, 'setCacheControlHeaders'),
                "SecurityHeaders has setCacheControlHeaders method"
            );
            
            // Test initialization method
            $this->assert(
                method_exists($this->securityHeaders, 'initializeSecurityMeasures'),
                "SecurityHeaders has initializeSecurityMeasures method"
            );
            
            echo "✓ Information Disclosure Prevention: PASSED\n\n";
            
        } catch (Exception $e) {
            echo "✗ Information Disclosure Prevention: FAILED - " . $e->getMessage() . "\n\n";
            $this->testResults['information_disclosure'] = false;
        }
    }
    
    /**
     * Assert helper method
     */
    private function assert(bool $condition, string $message): void {
        if (!$condition) {
            echo "✗ FAILED: {$message}\n";
            $this->testResults[$message] = false;
        } else {
            $this->testResults[$message] = true;
        }
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
        
        echo "=== Security Hardening Test Report ===\n";
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
    $test = new SecurityHardeningTest();
    $report = $test->runAllTests();
    
    // Exit with appropriate code
    exit($report['failed'] > 0 ? 1 : 0);
}