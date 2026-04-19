<?php
/**
 * Migration: Add PayOS payout columns to withdrawal_requests table
 * Run this script to add columns needed for PayOS payout integration
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/Database.php';

// Get database connection
$db = Database::getInstance();

echo "=== PayOS Migration for withdrawal_requests table ===\n\n";

try {
    // Check if columns already exist
    $existingColumns = $db->query("SHOW COLUMNS FROM withdrawal_requests");
    $columnNames = array_column($existingColumns, 'Field');
    
    $columnsToAdd = [];
    
    // payos_payout_id - stores PayOS payout ID
    if (!in_array('payos_payout_id', $columnNames)) {
        $columnsToAdd[] = "ADD COLUMN payos_payout_id VARCHAR(100) NULL AFTER payment_completed_at";
        echo "[+] Adding column: payos_payout_id\n";
    } else {
        echo "[✓] Column already exists: payos_payout_id\n";
    }
    
    // payos_status - stores payout status (PROCESSING, COMPLETED, FAILED, CANCELLED)
    if (!in_array('payos_status', $columnNames)) {
        $columnsToAdd[] = "ADD COLUMN payos_status VARCHAR(50) NULL AFTER payos_payout_id";
        echo "[+] Adding column: payos_status\n";
    } else {
        echo "[✓] Column already exists: payos_status\n";
    }
    
    // payos_response - stores full PayOS API response
    if (!in_array('payos_response', $columnNames)) {
        $columnsToAdd[] = "ADD COLUMN payos_response JSON NULL AFTER payos_status";
        echo "[+] Adding column: payos_response\n";
    } else {
        echo "[✓] Column already exists: payos_response\n";
    }
    
    // payos_webhook_data - stores webhook callback data
    if (!in_array('payos_webhook_data', $columnNames)) {
        $columnsToAdd[] = "ADD COLUMN payos_webhook_data JSON NULL AFTER payos_response";
        echo "[+] Adding column: payos_webhook_data\n";
    } else {
        echo "[✓] Column already exists: payos_webhook_data\n";
    }
    
    // payos_webhook_received_at - stores when webhook was received
    if (!in_array('payos_webhook_received_at', $columnNames)) {
        $columnsToAdd[] = "ADD COLUMN payos_webhook_received_at DATETIME NULL AFTER payos_webhook_data";
        echo "[+] Adding column: payos_webhook_received_at\n";
    } else {
        echo "[✓] Column already exists: payos_webhook_received_at\n";
    }
    
    // Execute ALTER TABLE if there are columns to add
    if (!empty($columnsToAdd)) {
        $sql = "ALTER TABLE withdrawal_requests " . implode(", ", $columnsToAdd);
        $db->execute($sql);
        echo "\n✅ Migration completed successfully!\n";
    } else {
        echo "\n✅ All columns already exist. No changes needed.\n";
    }
    
    // Create index on payos_payout_id for faster lookups
    $existingIndexes = $db->query("SHOW INDEX FROM withdrawal_requests WHERE Key_name = 'idx_payos_payout_id'");
    if (empty($existingIndexes)) {
        $db->execute("CREATE INDEX idx_payos_payout_id ON withdrawal_requests(payos_payout_id)");
        echo "[+] Created index: idx_payos_payout_id\n";
    } else {
        echo "[✓] Index already exists: idx_payos_payout_id\n";
    }
    
    echo "\n=== Migration Summary ===\n";
    echo "Table: withdrawal_requests\n";
    echo "Added columns for PayOS payout integration:\n";
    echo "- payos_payout_id: PayOS payout transaction ID\n";
    echo "- payos_status: Payout status (PROCESSING, COMPLETED, FAILED, CANCELLED)\n";
    echo "- payos_response: Full API response from PayOS\n";
    echo "- payos_webhook_data: Webhook callback data\n";
    echo "- payos_webhook_received_at: Timestamp when webhook was received\n";
    echo "\nYou can now use PayOS payout API for affiliate withdrawals.\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Error trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
