<?php
/**
 * Fix foreign key constraint for sepay_webhooks_log table
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

header('Content-Type: text/plain');

try {
    $db = Database::getInstance();
    
    echo "Checking sepay_webhooks_log table structure...\n\n";
    
    // Check current foreign keys
    $fkInfo = $db->query("
        SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'sepay_webhooks_log'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    echo "Current Foreign Keys:\n";
    if ($fkInfo) {
        foreach ($fkInfo as $fk) {
            echo "  - {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        }
    }
    
    // Drop existing FK if exists and pointing to wrong table
    if ($fkInfo) {
        foreach ($fkInfo as $fk) {
            if ($fk['REFERENCED_TABLE_NAME'] === 'orders_demo') {
                echo "\nDropping incorrect foreign key: {$fk['CONSTRAINT_NAME']}\n";
                $db->query("ALTER TABLE sepay_webhooks_log DROP FOREIGN KEY {$fk['CONSTRAINT_NAME']}");
            }
        }
    }
    
    // Add correct foreign key
    echo "\nAdding correct foreign key to orders table...\n";
    $db->query("
        ALTER TABLE sepay_webhooks_log
        ADD CONSTRAINT fk_webhook_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE SET NULL
    ");
    
    echo "\n✅ Fixed! Foreign key now references orders.id\n";
    
    // Also fix any other issues
    echo "\nChecking for other issues...\n";
    
    // Check if updated_at column exists
    $columns = $db->query("SHOW COLUMNS FROM sepay_webhooks_log LIKE 'updated_at'");
    if (empty($columns)) {
        echo "Adding updated_at column...\n";
        $db->query("ALTER TABLE sepay_webhooks_log ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
    
    echo "\n✅ Database fixes complete!\n";
    echo "\nPlease test webhook again.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
