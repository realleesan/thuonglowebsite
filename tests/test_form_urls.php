<?php
/**
 * Test Form URLs - Phase 3 Task 9.4
 * Test that all form action URLs are working correctly
 */

// Load configuration and initialize URL builder
require_once 'config.php';
require_once 'core/functions.php';

// Initialize URL builder
init_url_builder();

echo "<h1>Form URL Testing - Task 9.4</h1>\n";
echo "<p>Testing all form action URLs to ensure they work correctly with the new URL system.</p>\n\n";

// Test configuration
echo "<h2>1. Configuration Test</h2>\n";
echo "<p>Environment: <strong>" . get_environment() . "</strong></p>\n";
echo "<p>Base URL: <strong>" . base_url() . "</strong></p>\n";
echo "<p>Debug Mode: <strong>" . (is_debug() ? 'Yes' : 'No') . "</strong></p>\n\n";

// Test form URL generation
echo "<h2>2. Form URL Generation Test</h2>\n";

$formTests = [
    'Search Form (Header)' => [
        'function' => 'base_url',
        'params' => [],
        'expected_pattern' => '/^https?:\/\/[^\/]+\/$/'
    ],
    'Checkout Form' => [
        'function' => 'form_url',
        'params' => ['payment'],
        'expected_pattern' => '/^https?:\/\/[^\/]+.*payment/'
    ],
    'Contact Form' => [
        'function' => 'form_url',
        'params' => ['contact'],
        'expected_pattern' => '/^https?:\/\/[^\/]+.*contact/'
    ],
    'Login Form' => [
        'function' => 'form_url',
        'params' => [],
        'expected_pattern' => '/^https?:\/\/[^\/]+/'
    ],
    'Register Form' => [
        'function' => 'form_url',
        'params' => [],
        'expected_pattern' => '/^https?:\/\/[^\/]+/'
    ],
    'Forgot Password Form' => [
        'function' => 'form_url',
        'params' => ['forgot'],
        'expected_pattern' => '/^https?:\/\/[^\/]+.*forgot/'
    ],
    'Agent Registration Form' => [
        'function' => 'form_url',
        'params' => ['agent-register'],
        'expected_pattern' => '/^https?:\/\/[^\/]+.*agent-register/'
    ]
];

$allPassed = true;

foreach ($formTests as $testName => $test) {
    echo "<h3>Testing: $testName</h3>\n";
    
    try {
        // Generate URL
        $url = call_user_func_array($test['function'], $test['params']);
        
        echo "<p><strong>Generated URL:</strong> <code>$url</code></p>\n";
        
        // Test URL format
        if (preg_match($test['expected_pattern'], $url)) {
            echo "<p style='color: green;'>✅ <strong>PASS:</strong> URL format is correct</p>\n";
        } else {
            echo "<p style='color: red;'>❌ <strong>FAIL:</strong> URL format is incorrect</p>\n";
            $allPassed = false;
        }
        
        // Test URL accessibility (basic validation)
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
            echo "<p style='color: green;'>✅ <strong>PASS:</strong> URL is properly formatted with scheme and host</p>\n";
        } else {
            echo "<p style='color: red;'>❌ <strong>FAIL:</strong> URL is missing scheme or host</p>\n";
            $allPassed = false;
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>ERROR:</strong> " . $e->getMessage() . "</p>\n";
        $allPassed = false;
    }
    
    echo "<hr>\n";
}

// Test page URL generation for redirects
echo "<h2>3. Page URL Generation Test (for redirects)</h2>\n";

$pageTests = [
    'Home Page' => 'home',
    'Login Page' => 'login',
    'Register Page' => 'register',
    'Forgot Password Page' => 'forgot',
    'Products Page' => 'products',
    'Payment Success Page' => 'payment_success'
];

foreach ($pageTests as $testName => $page) {
    echo "<h3>Testing: $testName</h3>\n";
    
    try {
        $url = page_url($page);
        echo "<p><strong>Generated URL:</strong> <code>$url</code></p>\n";
        
        // Test URL format
        if (preg_match('/^https?:\/\/[^\/]+/', $url)) {
            echo "<p style='color: green;'>✅ <strong>PASS:</strong> URL format is correct</p>\n";
        } else {
            echo "<p style='color: red;'>❌ <strong>FAIL:</strong> URL format is incorrect</p>\n";
            $allPassed = false;
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>ERROR:</strong> " . $e->getMessage() . "</p>\n";
        $allPassed = false;
    }
    
    echo "<hr>\n";
}

// Test form submission simulation
echo "<h2>4. Form Submission Simulation</h2>\n";

// Simulate form data
$_POST['test_form'] = 'true';
$_GET['page'] = 'test';

echo "<p>Simulating form submission with POST data...</p>\n";
echo "<p>Current REQUEST_URI: <code>" . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "</code></p>\n";

// Test self-submitting form URL
$selfSubmitUrl = form_url();
echo "<p>Self-submitting form URL: <code>$selfSubmitUrl</code></p>\n";

if (!empty($selfSubmitUrl)) {
    echo "<p style='color: green;'>✅ <strong>PASS:</strong> Self-submitting form URL generated</p>\n";
} else {
    echo "<p style='color: red;'>❌ <strong>FAIL:</strong> Self-submitting form URL is empty</p>\n";
    $allPassed = false;
}

// Clean up test data
unset($_POST['test_form']);
unset($_GET['page']);

// Final results
echo "<h2>5. Test Results Summary</h2>\n";

if ($allPassed) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>\n";
    echo "<h3 style='color: #155724;'>✅ ALL TESTS PASSED</h3>\n";
    echo "<p>All form URLs are working correctly with the new URL system.</p>\n";
    echo "<p><strong>Task 9.4 Status:</strong> COMPLETED ✅</p>\n";
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>\n";
    echo "<h3 style='color: #721c24;'>❌ SOME TESTS FAILED</h3>\n";
    echo "<p>Some form URLs are not working correctly. Please review the failed tests above.</p>\n";
    echo "<p><strong>Task 9.4 Status:</strong> NEEDS ATTENTION ⚠️</p>\n";
    echo "</div>\n";
}

echo "\n<h2>6. Next Steps</h2>\n";
echo "<ul>\n";
echo "<li>If all tests passed, Task 9.4 is complete</li>\n";
echo "<li>If any tests failed, review and fix the failing form URLs</li>\n";
echo "<li>Test actual form submissions on the hosting environment</li>\n";
echo "<li>Verify that forms submit to the correct pages and process data correctly</li>\n";
echo "</ul>\n";

echo "\n<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>\n";
?>