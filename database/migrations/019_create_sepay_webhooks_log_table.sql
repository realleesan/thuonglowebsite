-- Migration: Create sepay_webhooks_log table
-- Created: 2026-02-17
-- Description: Log all webhooks received from SePay for debugging and audit

CREATE TABLE IF NOT EXISTS sepay_webhooks_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Webhook identification
    webhook_type ENUM('payment_in', 'payment_out', 'unknown') NOT NULL COMMENT 'Type of webhook (payment in from user, payment out to affiliate)',
    transaction_id VARCHAR(100) NULL COMMENT 'SePay transaction ID',
    
    -- Transaction details
    reference_code VARCHAR(100) NULL COMMENT 'Reference code (DH[OrderId] or RUT[WithdrawCode])',
    amount DECIMAL(15,2) NULL COMMENT 'Transaction amount',
    content TEXT NULL COMMENT 'Transaction content/description',
    bank_account VARCHAR(50) NULL COMMENT 'Bank account involved',
    
    -- Status
    status VARCHAR(50) NULL COMMENT 'Transaction status from SePay',
    success TINYINT(1) DEFAULT 0 COMMENT 'Whether transaction was successful',
    
    -- Processing
    processed TINYINT(1) DEFAULT 0 COMMENT 'Whether webhook has been processed',
    processed_at TIMESTAMP NULL COMMENT 'When webhook was processed',
    processing_error TEXT NULL COMMENT 'Error message if processing failed',
    
    -- Related records
    order_id INT NULL COMMENT 'Related order ID if payment_in',
    withdrawal_id INT NULL COMMENT 'Related withdrawal request ID if payment_out',
    
    -- Raw data
    raw_data JSON NOT NULL COMMENT 'Complete raw webhook data from SePay',
    headers JSON NULL COMMENT 'HTTP headers from webhook request',
    ip_address VARCHAR(45) NULL COMMENT 'IP address of webhook sender',
    
    -- Signature verification
    signature VARCHAR(255) NULL COMMENT 'Webhook signature',
    signature_verified TINYINT(1) DEFAULT 0 COMMENT 'Whether signature was verified',
    
    -- Timestamps
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When webhook was received',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders_demo(id) ON DELETE SET NULL,   
    
    INDEX idx_webhook_type (webhook_type),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_reference_code (reference_code),
    INDEX idx_processed (processed),
    INDEX idx_success (success),
    INDEX idx_order_id (order_id),
    INDEX idx_withdrawal_id (withdrawal_id),
    INDEX idx_received_at (received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
