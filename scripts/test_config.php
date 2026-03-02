<?php
/**
 * Configuration Test Script
 * Verify all payment-related configurations are set correctly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$isBrowser = isset($_SERVER['HTTP_HOST']);

if ($isBrowser) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Configuration Test</title>";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;} .box{background:white;padding:20px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;} table{border-collapse:collapse;width:100%;margin:10px 0;} td,th{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f0f0f0;} .masked{color:#999;font-style:italic;}</style>";
    echo "</head><body>";
    echo "<h1>⚙️ Configuration Test</h1>";
} else {
    echo "=== Configuration Test ===\n\n";
}

try {
    // Load config
    $config = require __DIR__ . '/../config.php';
    
    if ($isBrowser) echo "<div class='box'>";
    echo $isBrowser ? "<h2>✓ Configuration Loaded</h2>" : "✓ Configuration loaded\n\n";
    
    // Test 1: SePay Configuration
    if ($isBrowser) echo "</div><div class='box'><h2>1. SePay Configuration</h2>";
    else echo "=== 1. SePay Configuration ===\n";
    
    $sepay = $config['sepay'] ?? [];
    $sepayTests = [
        'enabled' => ['Expected' => 'true', 'Actual' => $sepay['enabled'] ? 'true' : 'false'],
        'api_key' => ['Expected' => 'Set', 'Actual' => !empty($sepay['api_key']) && $sepay['api_key'] !== 'YOUR_SEPAY_API_KEY_HERE' ? '✓ Set' : '❌ Not set'],
        'api_secret' => ['Expected' => 'Set', 'Actual' => !empty($sepay['api_secret']) && $sepay['api_secret'] !== 'YOUR_SEPAY_API_SECRET_HERE' ? '✓ Set' : '❌ Not set'],
        'account_number' => ['Expected' => 'Set', 'Actual' => !empty($sepay['account_number']) && $sepay['account_number'] !== 'YOUR_ACCOUNT_NUMBER_HERE' ? '✓ Set' : '❌ Not set'],
        'webhook_secret' => ['Expected' => 'Set', 'Actual' => !empty($sepay['webhook_secret']) && $sepay['webhook_secret'] !== 'YOUR_WEBHOOK_SECRET_HERE' ? '✓ Set' : '❌ Not set'],
        'payment_timeout' => ['Expected' => '120', 'Actual' => $sepay['payment_timeout'] ?? 'Not set'],
        'order_prefix' => ['Expected' => 'DH', 'Actual' => $sepay['order_prefix'] ?? 'Not set'],
        'withdrawal_prefix' => ['Expected' => 'RUT', 'Actual' => $sepay['withdrawal_prefix'] ?? 'Not set'],
    ];
    
    if ($isBrowser) {
        echo "<table><tr><th>Setting</th><th>Expected</th><th>Actual</th><th>Status</th></tr>";
        foreach ($sepayTests as $key => $test) {
            $status = (strpos($test['Actual'], '✓') !== false || $test['Actual'] == $test['Expected']) ? '✓' : '❌';
            $statusClass = $status === '✓' ? 'success' : 'error';
            echo "<tr><td>$key</td><td>{$test['Expected']}</td><td>{$test['Actual']}</td><td class='$statusClass'>$status</td></tr>";
        }
        echo "</table>";
    } else {
        foreach ($sepayTests as $key => $test) {
            $status = (strpos($test['Actual'], '✓') !== false || $test['Actual'] == $test['Expected']) ? '✓' : '❌';
            echo "$status $key: {$test['Actual']}\n";
        }
    }
    
    // Test 2: Email Configuration
    if ($isBrowser) echo "</div><div class='box'><h2>2. Email Configuration</h2>";
    else echo "\n=== 2. Email Configuration ===\n";
    
    $email = $config['email'] ?? [];
    $emailTests = [
        'enabled' => ['Expected' => 'true', 'Actual' => $email['enabled'] ? 'true' : 'false'],
        'smtp_host' => ['Expected' => 'Set', 'Actual' => !empty($email['smtp_host']) ? $email['smtp_host'] : '❌ Not set'],
        'smtp_port' => ['Expected' => '587', 'Actual' => $email['smtp_port'] ?? 'Not set'],
        'smtp_username' => ['Expected' => 'Set', 'Actual' => !empty($email['smtp_username']) && $email['smtp_username'] !== 'your-email@gmail.com' ? '✓ Set' : '❌ Not set'],
        'smtp_password' => ['Expected' => 'Set', 'Actual' => !empty($email['smtp_password']) && $email['smtp_password'] !== 'your-app-password' ? '✓ Set' : '❌ Not set'],
        'from_email' => ['Expected' => 'Set', 'Actual' => $email['from_email'] ?? 'Not set'],
        'from_name' => ['Expected' => 'Set', 'Actual' => $email['from_name'] ?? 'Not set'],
    ];
    
    if ($isBrowser) {
        echo "<table><tr><th>Setting</th><th>Expected</th><th>Actual</th><th>Status</th></tr>";
        foreach ($emailTests as $key => $test) {
            $status = (strpos($test['Actual'], '✓') !== false || ($test['Actual'] !== '❌ Not set' && $test['Actual'] !== 'Not set')) ? '✓' : '❌';
            $statusClass = $status === '✓' ? 'success' : 'error';
            echo "<tr><td>$key</td><td>{$test['Expected']}</td><td>{$test['Actual']}</td><td class='$statusClass'>$status</td></tr>";
        }
        echo "</table>";
    } else {
        foreach ($emailTests as $key => $test) {
            $status = (strpos($test['Actual'], '✓') !== false || ($test['Actual'] !== '❌ Not set' && $test['Actual'] !== 'Not set')) ? '✓' : '❌';
            echo "$status $key: {$test['Actual']}\n";
        }
    }
    
    // Test 3: Commission Configuration
    if ($isBrowser) echo "</div><div class='box'><h2>3. Commission Configuration</h2>";
    else echo "\n=== 3. Commission Configuration ===\n";
    
    $commission = $config['commission'] ?? [];
    
    if ($isBrowser) {
        echo "<table><tr><th>Setting</th><th>Value</th></tr>";
        echo "<tr><td>Enabled</td><td>" . ($commission['enabled'] ? 'Yes' : 'No') . "</td></tr>";
        echo "<tr><td>Default Rate</td><td>" . ($commission['default_rate'] ?? 'Not set') . "%</td></tr>";
        echo "<tr><td>Min Order</td><td>" . number_format($commission['min_order_for_commission'] ?? 0) . " VND</td></tr>";
        echo "<tr><td>Auto Credit</td><td>" . ($commission['auto_credit'] ? 'Yes' : 'No') . "</td></tr>";
        echo "</table>";
    } else {
        echo "Enabled: " . ($commission['enabled'] ? 'Yes' : 'No') . "\n";
        echo "Default Rate: " . ($commission['default_rate'] ?? 'Not set') . "%\n";
        echo "Min Order: " . number_format($commission['min_order_for_commission'] ?? 0) . " VND\n";
        echo "Auto Credit: " . ($commission['auto_credit'] ? 'Yes' : 'No') . "\n";
    }
    
    // Test 4: Withdrawal Configuration
    if ($isBrowser) echo "</div><div class='box'><h2>4. Withdrawal Configuration</h2>";
    else echo "\n=== 4. Withdrawal Configuration ===\n";
    
    $withdrawal = $config['withdrawal'] ?? [];
    
    if ($isBrowser) {
        echo "<table><tr><th>Setting</th><th>Value</th></tr>";
        echo "<tr><td>Enabled</td><td>" . ($withdrawal['enabled'] ? 'Yes' : 'No') . "</td></tr>";
        echo "<tr><td>Min Amount</td><td>" . number_format($withdrawal['min_amount'] ?? 0) . " VND</td></tr>";
        echo "<tr><td>Max Amount</td><td>" . number_format($withdrawal['max_amount'] ?? 0) . " VND</td></tr>";
        echo "<tr><td>Fee</td><td>" . ($withdrawal['fee'] ?? 0) . " VND</td></tr>";
        echo "<tr><td>Require Bank Verification</td><td>" . ($withdrawal['require_bank_verification'] ? 'Yes' : 'No') . "</td></tr>";
        echo "<tr><td>OTP Expiry</td><td>" . ($withdrawal['otp_expiry'] ?? 0) . " seconds</td></tr>";
        echo "</table>";
    } else {
        echo "Enabled: " . ($withdrawal['enabled'] ? 'Yes' : 'No') . "\n";
        echo "Min Amount: " . number_format($withdrawal['min_amount'] ?? 0) . " VND\n";
        echo "Max Amount: " . number_format($withdrawal['max_amount'] ?? 0) . " VND\n";
        echo "Fee: " . ($withdrawal['fee'] ?? 0) . " VND\n";
        echo "Require Bank Verification: " . ($withdrawal['require_bank_verification'] ? 'Yes' : 'No') . "\n";
    }
    
    // Test 5: Check .env file
    if ($isBrowser) echo "</div><div class='box'><h2>5. Environment File</h2>";
    else echo "\n=== 5. Environment File ===\n";
    
    $envExists = file_exists(__DIR__ . '/../.env');
    $envExampleExists = file_exists(__DIR__ . '/../.env.example');
    
    if ($envExists) {
        $msg = "✓ .env file exists";
        echo $isBrowser ? "<p class='success'>$msg</p>" : "$msg\n";
    } else {
        $msg = "❌ .env file not found";
        echo $isBrowser ? "<p class='error'>$msg</p>" : "$msg\n";
        if ($envExampleExists) {
            $msg = "Copy .env.example to .env and update with your credentials";
            echo $isBrowser ? "<p class='info'>$msg</p>" : "$msg\n";
        }
    }
    
    if ($envExampleExists) {
        $msg = "✓ .env.example file exists";
        echo $isBrowser ? "<p class='success'>$msg</p>" : "$msg\n";
    }
    
    // Summary
    if ($isBrowser) echo "</div><div class='box'><h2>Summary</h2>";
    else echo "\n=== Summary ===\n";
    
    $allConfigured = true;
    $warnings = [];
    
    // Check critical settings
    if (empty($sepay['api_key']) || $sepay['api_key'] === 'YOUR_SEPAY_API_KEY_HERE') {
        $allConfigured = false;
        $warnings[] = "SePay API Key not configured";
    }
    
    if (empty($email['smtp_username']) || $email['smtp_username'] === 'your-email@gmail.com') {
        $warnings[] = "Email SMTP not configured (optional for testing)";
    }
    
    if ($allConfigured) {
        $msg = "✅ All critical configurations are set!";
        echo $isBrowser ? "<p class='success'><strong>$msg</strong></p>" : "$msg\n";
    } else {
        $msg = "⚠️ Some configurations need attention:";
        echo $isBrowser ? "<p class='warning'><strong>$msg</strong></p>" : "$msg\n";
        
        if ($isBrowser) {
            echo "<ul>";
            foreach ($warnings as $warning) {
                echo "<li>$warning</li>";
            }
            echo "</ul>";
        } else {
            foreach ($warnings as $warning) {
                echo "  - $warning\n";
            }
        }
    }
    
    if ($isBrowser) {
        echo "<h3>Next Steps:</h3>";
        echo "<ol>";
        echo "<li>Copy <code>.env.example</code> to <code>.env</code></li>";
        echo "<li>Update <code>.env</code> with your SePay credentials</li>";
        echo "<li>Update <code>.env</code> with your SMTP credentials (optional)</li>";
        echo "<li>Run migrations: <code>php scripts/migrate.php</code></li>";
        echo "<li>Test SePay connection: <code>php scripts/test_sepay_connection.php</code></li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "\nNext Steps:\n";
        echo "1. Copy .env.example to .env\n";
        echo "2. Update .env with your SePay credentials\n";
        echo "3. Update .env with your SMTP credentials (optional)\n";
        echo "4. Run migrations: php scripts/migrate.php\n";
        echo "5. Test SePay connection: php scripts/test_sepay_connection.php\n";
    }
    
    if ($isBrowser) echo "</body></html>";
    
} catch (Exception $e) {
    $msg = "❌ Error: " . $e->getMessage();
    echo $isBrowser ? "<p class='error'>$msg</p></body></html>" : "$msg\n";
    exit(1);
}
