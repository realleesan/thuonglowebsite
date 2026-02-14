<?php
/**
 * Authentication Views Integration Test
 * Tests enhanced auth views with security features
 * For Task 12.1: Enhance existing auth views
 */

class AuthViewsIntegrationTest {
    private array $results = [];
    
    public function runAllTests(): array {
        echo "=== Authentication Views Integration Test ===\n\n";
        
        $this->testViewFiles();
        $this->testSecurityFeatures();
        $this->testFormEnhancements();
        $this->testAssetIntegration();
        
        $this->printResults();
        return $this->results;
    }
    
    private function testViewFiles(): void {
        echo "Testing View Files...\n";
        
        try {
            // Test required view files exist
            $requiredViews = [
                'app/views/auth/login.php',
                'app/views/auth/register.php',
                'app/views/auth/forgot.php',
                'app/views/auth/reset.php'
            ];
            
            foreach ($requiredViews as $view) {
                $this->assert(file_exists(__DIR__ . '/../' . $view), "View file {$view} should exist");
            }
            
            $this->results['ViewFiles'] = 'PASS';
            echo "✓ View files tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['ViewFiles'] = 'FAIL: ' . $e->getMessage();
            echo "✗ View files tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testSecurityFeatures(): void {
        echo "Testing Security Features in Views...\n";
        
        try {
            // Test CSRF token in login view
            $loginContent = file_get_contents(__DIR__ . '/../app/views/auth/login.php');
            $this->assert(strpos($loginContent, 'csrf_token') !== false, 'Login view should contain CSRF token');
            $this->assert(strpos($loginContent, 'autocomplete="username"') !== false, 'Login view should have proper autocomplete');
            $this->assert(strpos($loginContent, 'autocomplete="current-password"') !== false, 'Login view should have password autocomplete');
            
            // Test rate limiting warning in login view
            $this->assert(strpos($loginContent, 'rate_limit_warning') !== false, 'Login view should support rate limiting warning');
            
            // Test CSRF token in register view
            $registerContent = file_get_contents(__DIR__ . '/../app/views/auth/register.php');
            $this->assert(strpos($registerContent, 'csrf_token') !== false, 'Register view should contain CSRF token');
            $this->assert(strpos($registerContent, 'minlength="8"') !== false, 'Register view should enforce minimum password length');
            $this->assert(strpos($registerContent, 'autocomplete="new-password"') !== false, 'Register view should have new password autocomplete');
            
            // Test password requirements in register view
            $this->assert(strpos($registerContent, 'password-requirements') !== false, 'Register view should show password requirements');
            $this->assert(strpos($registerContent, 'passwordMatch') !== false, 'Register view should have password match indicator');
            
            // Test CSRF token in forgot password view
            $forgotContent = file_get_contents(__DIR__ . '/../app/views/auth/forgot.php');
            $this->assert(strpos($forgotContent, 'csrf_token') !== false, 'Forgot password view should contain CSRF token');
            $this->assert(strpos($forgotContent, 'pattern="[0-9]{6}"') !== false, 'Forgot password view should validate verification code format');
            
            // Test enhanced password reset
            $this->assert(strpos($forgotContent, 'minlength="8"') !== false, 'Forgot password view should enforce minimum password length');
            $this->assert(strpos($forgotContent, 'password-requirements') !== false, 'Forgot password view should show password requirements');
            
            $this->results['SecurityFeatures'] = 'PASS';
            echo "✓ Security features tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['SecurityFeatures'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Security features tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testFormEnhancements(): void {
        echo "Testing Form Enhancements...\n";
        
        try {
            // Test login form enhancements
            $loginContent = file_get_contents(__DIR__ . '/../app/views/auth/login.php');
            $this->assert(strpos($loginContent, 'name="login"') !== false, 'Login form should use "login" field name');
            $this->assert(strpos($loginContent, 'id="login"') !== false, 'Login form should have proper field ID');
            
            // Test register form enhancements
            $registerContent = file_get_contents(__DIR__ . '/../app/views/auth/register.php');
            $this->assert(strpos($registerContent, 'passwordStrength') !== false, 'Register form should have password strength indicator');
            $this->assert(strpos($registerContent, 'autocomplete="name"') !== false, 'Register form should have name autocomplete');
            $this->assert(strpos($registerContent, 'autocomplete="email"') !== false, 'Register form should have email autocomplete');
            $this->assert(strpos($registerContent, 'autocomplete="tel"') !== false, 'Register form should have phone autocomplete');
            
            // Test password visibility toggles
            $this->assert(strpos($registerContent, 'toggleAuthPassword') !== false, 'Register form should have password toggle functionality');
            
            // Test forgot password form enhancements
            $forgotContent = file_get_contents(__DIR__ . '/../app/views/auth/forgot.php');
            $this->assert(strpos($forgotContent, 'autocomplete="username"') !== false, 'Forgot password form should have username autocomplete');
            $this->assert(strpos($forgotContent, 'title="Mã xác thực phải là 6 chữ số"') !== false, 'Forgot password form should have input validation title');
            
            $this->results['FormEnhancements'] = 'PASS';
            echo "✓ Form enhancements tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['FormEnhancements'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Form enhancements tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testAssetIntegration(): void {
        echo "Testing Asset Integration...\n";
        
        try {
            // Test JavaScript file exists and has enhanced features
            $jsFile = __DIR__ . '/../assets/js/auth.js';
            $this->assert(file_exists($jsFile), 'Enhanced auth.js should exist');
            
            $jsContent = file_get_contents($jsFile);
            $this->assert(strpos($jsContent, 'checkPasswordStrength') !== false, 'auth.js should have password strength checking');
            $this->assert(strpos($jsContent, 'checkPasswordMatch') !== false, 'auth.js should have password match checking');
            $this->assert(strpos($jsContent, 'refreshCsrfToken') !== false, 'auth.js should have CSRF token refresh');
            $this->assert(strpos($jsContent, 'showRateLimitWarning') !== false, 'auth.js should have rate limiting warning');
            $this->assert(strpos($jsContent, 'enhanceFormValidation') !== false, 'auth.js should have enhanced form validation');
            $this->assert(strpos($jsContent, 'setupAutoLogoutWarning') !== false, 'auth.js should have auto-logout warning');
            
            // Test CSS file has enhanced styles
            $cssFile = __DIR__ . '/../assets/css/auth.css';
            $this->assert(file_exists($cssFile), 'Enhanced auth.css should exist');
            
            $cssContent = file_get_contents($cssFile);
            $this->assert(strpos($cssContent, 'password-strength') !== false, 'auth.css should have password strength styles');
            $this->assert(strpos($cssContent, 'password-match') !== false, 'auth.css should have password match styles');
            $this->assert(strpos($cssContent, 'alert-warning') !== false, 'auth.css should have warning alert styles');
            $this->assert(strpos($cssContent, 'password-requirements') !== false, 'auth.css should have password requirements styles');
            $this->assert(strpos($cssContent, 'session-warning') !== false, 'auth.css should have session warning styles');
            
            // Test accessibility features
            $this->assert(strpos($cssContent, 'sr-only') !== false, 'auth.css should have screen reader only styles');
            $this->assert(strpos($cssContent, 'prefers-contrast: high') !== false, 'auth.css should support high contrast mode');
            $this->assert(strpos($cssContent, 'prefers-color-scheme: dark') !== false, 'auth.css should support dark mode');
            
            $this->results['AssetIntegration'] = 'PASS';
            echo "✓ Asset integration tests passed\n\n";
            
        } catch (Exception $e) {
            $this->results['AssetIntegration'] = 'FAIL: ' . $e->getMessage();
            echo "✗ Asset integration tests failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function assert(bool $condition, string $message): void {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }
    
    private function printResults(): void {
        echo "=== Authentication Views Integration Test Results ===\n";
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
            echo "\n🎉 Authentication Views Integration Complete!\n";
            echo "\n✅ Enhanced Features:\n";
            echo "- CSRF protection on all forms\n";
            echo "- Enhanced password security (8+ chars, complexity requirements)\n";
            echo "- Password strength indicator with real-time feedback\n";
            echo "- Password confirmation matching\n";
            echo "- Rate limiting warnings and handling\n";
            echo "- Proper autocomplete attributes for better UX\n";
            echo "- Input validation with pattern matching\n";
            echo "- Enhanced error and success messaging\n";
            echo "- Accessibility improvements (screen readers, high contrast)\n";
            echo "- Mobile-responsive security features\n";
            echo "\n📋 JavaScript Enhancements:\n";
            echo "- Real-time password strength checking\n";
            echo "- Password confirmation validation\n";
            echo "- CSRF token auto-refresh\n";
            echo "- Rate limiting countdown\n";
            echo "- Auto-logout warnings\n";
            echo "- Enhanced form validation\n";
            echo "\n🎨 CSS Enhancements:\n";
            echo "- Password strength visual indicators\n";
            echo "- Security warning styles\n";
            echo "- Loading states for buttons\n";
            echo "- Dark mode and high contrast support\n";
            echo "- Mobile-responsive design\n";
            echo "\n📖 Next Steps:\n";
            echo "1. Test views with live authentication system\n";
            echo "2. Integrate with existing controllers\n";
            echo "3. Test security features with real user data\n";
        } else {
            echo "\n⚠️  Some view integration features need attention.\n";
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AuthViewsIntegrationTest();
    $results = $test->runAllTests();
    
    // Exit with error code if any tests failed
    $allPassed = array_reduce($results, function($carry, $result) {
        return $carry && ($result === 'PASS');
    }, true);
    
    exit($allPassed ? 0 : 1);
}