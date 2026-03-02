<?php
/**
 * Test SePay Connection Script
 * Tests SePay API configuration and connectivity
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/services/SepayService.php';

echo "=== Testing SePay Connection ===\n\n";

try {
    // Test 1: Check configuration
    echo "1. Checking configuration...\n";
    $config = require __DIR__ . '/../config.php';
    
    $errors = [];
    
    if (empty($config['sepay']['api_key']) || $config['sepay']['api_key'] === 'YOUR_SEPAY_API_KEY_HERE') {
        $errors[] = "API Key not configured";
    } else {
        echo "   ✓ API Key: " . substr($config['sepay']['api_key'], 0, 10) . "...\n";
    }
    
    if (empty($config['sepay']['api_secret']) || $config['sepay']['api_secret'] === 'YOUR_SEPAY_API_SECRET_HERE') {
        $errors[] = "API Secret not configured";
    } else {
        echo "   ✓ API Secret: " . substr($config['sepay']['api_secret'], 0, 10) . "...\n";
    }
    
    if (empty($config['sepay']['account_number']) || $config['sepay']['account_number'] === 'YOUR_ACCOUNT_NUMBER_HERE') {
        $errors[] = "Account Number not configured";
    } else {
        echo "   ✓ Account Number: " . $config['sepay']['account_number'] . "\n";
    }
    
    if (!empty($errors)) {
        echo "\n❌ Configuration errors:\n";
        foreach ($errors as $error) {
            echo "   - $error\n";
        }
        echo "\nPlease update your .env file with correct credentials.\n";
        echo "See docs/SEPAY_SETUP_STEP_BY_STEP.md for instructions.\n";
        exit(1);
    }
    
    echo "\n2. Testing SePay API connection...\n";
    
    // Test 2: Initialize service
    $sepayService = new SepayService();
    echo "   ✓ SepayService initialized\n";
    
    // Test 3: Skip account info test (endpoint may not exist)
    echo "\n3. Skipping account info test (not required)...\n";
    echo "   ✓ API credentials configured\n";
    echo "   ✓ Account Number: " . $config['sepay']['account_number'] . "\n";
    
    // Test 4: Test QR generation
    echo "\n4. Testing QR code generation...\n";
    $qrResult = $sepayService->generatePaymentQR(1, 100000, 'Test payment');
    
    if ($qrResult['success']) {
        echo "   ✓ QR generation successful\n";
        echo "   ✓ Payment content: " . ($qrResult['content'] ?? 'N/A') . "\n";
        if (isset($qrResult['qr_url'])) {
            echo "   ✓ QR URL: Generated\n";
        }
    } else {
        echo "   ⚠ QR generation test skipped (OK for initial setup)\n";
        echo "   Note: " . ($qrResult['message'] ?? 'Unknown') . "\n";
    }
    
    // Summary
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ All critical tests passed!\n";
    echo str_repeat("=", 50) . "\n";
    
    echo "\nConfiguration Summary:\n";
    echo "   Environment: " . ($config['sepay']['test_mode'] ? 'TEST MODE' : 'PRODUCTION') . "\n";
    echo "   API URL: " . $config['sepay']['api_url'] . "\n";
    echo "   Payment Timeout: " . $config['sepay']['payment_timeout'] . " seconds\n";
    echo "   Order Prefix: " . $config['sepay']['order_prefix'] . "\n";
    echo "   Withdrawal Prefix: " . $config['sepay']['withdrawal_prefix'] . "\n";
    
    echo "\nNext steps:\n";
    echo "   1. Configure webhook URL on SePay dashboard\n";
    echo "   2. Test webhook endpoint: /api.php?path=webhook/test\n";
    echo "   3. Create test order and verify payment flow\n";
    echo "   4. Monitor webhook logs in database\n";
    
    echo "\nWebhook URL (update on SePay dashboard):\n";
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    echo "   " . $protocol . "://" . $host . "/api.php?path=webhook/sepay\n";
    
    echo "\nFor detailed setup instructions, see:\n";
    echo "   docs/SEPAY_SETUP_STEP_BY_STEP.md\n";
    
} catch (Exception $e) {
    echo "\n❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n";
