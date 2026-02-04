<?php
/**
 * Simple Form URLs Test - Phase 3 Task 9.4
 */

// Load configuration and initialize URL builder
require_once 'config.php';
require_once 'core/functions.php';

// Initialize URL builder
init_url_builder();

// Test basic functionality
echo "Form URL Testing - Task 9.4\n";
echo "============================\n\n";

// Test environment
echo "Environment: " . get_environment() . "\n";
echo "Base URL: " . base_url() . "\n\n";

// Test form URLs
echo "Form URL Tests:\n";
echo "---------------\n";

$tests = [
    'Search Form' => base_url(),
    'Checkout Form' => form_url('payment'),
    'Contact Form' => form_url('contact'),
    'Login Form' => form_url(),
    'Forgot Password' => form_url('forgot'),
    'Agent Registration' => form_url('agent-register')
];

$allPassed = true;

foreach ($tests as $name => $url) {
    echo "$name: $url\n";
    
    // Basic validation
    if (empty($url)) {
        echo "  ERROR: URL is empty\n";
        $allPassed = false;
    } elseif (!preg_match('/^https?:\/\//', $url)) {
        echo "  ERROR: URL doesn't start with http/https\n";
        $allPassed = false;
    } else {
        echo "  OK: URL format is valid\n";
    }
}

echo "\nPage URL Tests (for redirects):\n";
echo "-------------------------------\n";

$pageTests = [
    'Home' => page_url('home'),
    'Login' => page_url('login'),
    'Register' => page_url('register'),
    'Products' => page_url('products')
];

foreach ($pageTests as $name => $url) {
    echo "$name: $url\n";
    
    if (empty($url)) {
        echo "  ERROR: URL is empty\n";
        $allPassed = false;
    } elseif (!preg_match('/^https?:\/\//', $url)) {
        echo "  ERROR: URL doesn't start with http/https\n";
        $allPassed = false;
    } else {
        echo "  OK: URL format is valid\n";
    }
}

echo "\nOverall Result:\n";
echo "---------------\n";

if ($allPassed) {
    echo "✅ ALL TESTS PASSED - Task 9.4 COMPLETED\n";
} else {
    echo "❌ SOME TESTS FAILED - Task 9.4 NEEDS ATTENTION\n";
}

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";
?>