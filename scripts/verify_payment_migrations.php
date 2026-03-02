<?php
/**
 * Verify Payment Migrations Script
 * Check if all payment-related tables and columns are created correctly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../core/database.php';

$isBrowser = isset($_SERVER['HTTP_HOST']);

if ($isBrowser) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Payment Migrations Verification</title>";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} table{border-collapse:collapse;margin:10px 0;} td,th{border:1px solid #ddd;padding:8px;text-align:left;}</style>";
    echo "</head><body>";
    echo "<h1>Payment Migrations Verification</h1>";
} else {
    echo "=== Payment Migrations Verification ===\n\n";
}

try {
    $db = Database::getInstance();
    
    if (!$db->testConnection()) {
        throw new Exception("Database connection failed!");
    }
    
    echo $isBrowser ? "<p class='success'>✓ Database connected</p>" : "✓ Database connected\n\n";
    
    // Define expected structure
    $expectedStructure = [
        'orders' => [
            'sepay_transaction_id',
            'sepay_qr_code',
            'qr_generated_at',
            'qr_expired_at',
            'payment_timeout',
            'payment_completed_at',
            'payment_failed_at',
            'payment_error_message',
            'webhook_received_at',
            'is_expired'
        ],
        'affiliates' => [
            'balance',
            'pending_withdrawal',
            'total_withdrawn',
            'bank_name',
            'bank_account',
            'account_holder',
            'bank_branch',
            'bank_verified',
            'bank_verified_at',
            'bank_change_otp',
            'bank_change_otp_expires_at',
            'bank_last_changed_at'
        ],
        'wallet_transactions' => [
            'id',
            'affiliate_id',
            'type',
            'amount',
            'balance_before',
            'balance_after',
            'reference_type',
            'reference_id',
            'order_id',
            'withdrawal_id',
            'description',
            'admin_note',
            'metadata',
            'status',
            'created_by',
            'created_at',
            'updated_at'
        ],
        'withdrawal_requests' => [
            'id',
            'affiliate_id',
            'amount',
            'withdraw_code',
            'fee',
            'net_amount',
            'bank_name',
            'bank_account',
            'account_holder',
            'bank_branch',
            'status',
            'admin_note',
            'processed_by',
            'processed_at',
            'sepay_transaction_id',
            'sepay_qr_code',
            'qr_generated_at',
            'payment_completed_at',
            'webhook_received_at',
            'webhook_data',
            'requested_at',
            'created_at',
            'updated_at'
        ],
        'sepay_webhooks_log' => [
            'id',
            'webhook_type',
            'transaction_id',
            'reference_code',
            'amount',
            'content',
            'bank_account',
            'status',
            'success',
            'processed',
            'processed_at',
            'processing_error',
            'order_id',
            'withdrawal_id',
            'raw_data',
            'headers',
            'ip_address',
            'signature',
            'signature_verified',
            'received_at',
            'created_at'
        ]
    ];
    
    $allPassed = true;
    
    foreach ($expectedStructure as $tableName => $expectedColumns) {
        if ($isBrowser) {
            echo "<h2>Table: $tableName</h2>";
        } else {
            echo "=== Table: $tableName ===\n";
        }
        
        // Check if table exists
        $tableExists = $db->query("SHOW TABLES LIKE '$tableName'");
        
        if (empty($tableExists)) {
            $allPassed = false;
            $msg = "❌ Table '$tableName' does not exist!";
            echo $isBrowser ? "<p class='error'>$msg</p>" : "$msg\n";
            continue;
        }
        
        $msg = "✓ Table exists";
        echo $isBrowser ? "<p class='success'>$msg</p>" : "$msg\n";
        
        // Get actual columns
        $columns = $db->query("DESCRIBE $tableName");
        $actualColumns = array_column($columns, 'Field');
        
        // Check each expected column
        $missingColumns = [];
        foreach ($expectedColumns as $column) {
            if (!in_array($column, $actualColumns)) {
                $missingColumns[] = $column;
            }
        }
        
        if (empty($missingColumns)) {
            $msg = "✓ All expected columns present (" . count($expectedColumns) . " columns)";
            echo $isBrowser ? "<p class='success'>$msg</p>" : "$msg\n";
        } else {
            $allPassed = false;
            $msg = "❌ Missing columns: " . implode(', ', $missingColumns);
            echo $isBrowser ? "<p class='error'>$msg</p>" : "$msg\n";
        }
        
        // Show table structure
        if ($isBrowser) {
            echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>{$col['Field']}</td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Key']}</td>";
                echo "<td>{$col['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            foreach ($columns as $col) {
                echo "  - {$col['Field']} ({$col['Type']})\n";
            }
        }
        
        echo $isBrowser ? "" : "\n";
    }
    
    // Summary
    if ($isBrowser) {
        echo "<hr><h2>Summary</h2>";
    } else {
        echo "\n=== Summary ===\n";
    }
    
    if ($allPassed) {
        $msg = "✅ All payment migrations verified successfully!";
        echo $isBrowser ? "<p class='success'><strong>$msg</strong></p>" : "$msg\n";
        $msg = "You can now proceed to create Services and Models.";
        echo $isBrowser ? "<p>$msg</p>" : "$msg\n";
    } else {
        $msg = "❌ Some migrations are missing or incomplete.";
        echo $isBrowser ? "<p class='error'><strong>$msg</strong></p>" : "$msg\n";
        $msg = "Please run: php scripts/migrate.php";
        echo $isBrowser ? "<p>$msg</p>" : "$msg\n";
    }
    
    if ($isBrowser) {
        echo "</body></html>";
    }
    
} catch (Exception $e) {
    $msg = "❌ Error: " . $e->getMessage();
    echo $isBrowser ? "<p class='error'>$msg</p></body></html>" : "$msg\n";
    exit(1);
}
