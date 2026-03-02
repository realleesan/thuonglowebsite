-- Migration: Create withdrawal_requests table
-- Created: 2026-02-17
-- Description: Track affiliate withdrawal requests and admin processing

CREATE TABLE IF NOT EXISTS withdrawal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    affiliate_id INT NOT NULL,
    
    -- Withdrawal details
    amount DECIMAL(15,2) NOT NULL COMMENT 'Amount to withdraw',
    withdraw_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique withdrawal code (e.g., RUT12345)',
    fee DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Withdrawal fee (currently 0)',
    net_amount DECIMAL(15,2) NOT NULL COMMENT 'Net amount after fee',
    
    -- Bank information (snapshot at time of request)
    bank_name VARCHAR(100) NOT NULL COMMENT 'Bank name',
    bank_account VARCHAR(50) NOT NULL COMMENT 'Bank account number',
    account_holder VARCHAR(255) NOT NULL COMMENT 'Account holder name',
    bank_branch VARCHAR(255) NULL COMMENT 'Bank branch (optional)',
    
    -- Status tracking
    status ENUM('pending', 'processing', 'completed', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    
    -- Admin processing
    admin_note TEXT NULL COMMENT 'Admin note for rejection or other actions',
    processed_by INT NULL COMMENT 'Admin user ID who processed this request',
    processed_at TIMESTAMP NULL COMMENT 'When request was processed',
    
    -- SePay integration
    sepay_transaction_id VARCHAR(100) NULL COMMENT 'SePay transaction ID for payout',
    sepay_qr_code TEXT NULL COMMENT 'SePay QR code for admin to scan',
    qr_generated_at TIMESTAMP NULL COMMENT 'When QR was generated for admin',
    payment_completed_at TIMESTAMP NULL COMMENT 'When admin completed payment',
    
    -- Webhook tracking
    webhook_received_at TIMESTAMP NULL COMMENT 'When payout webhook was received',
    webhook_data JSON NULL COMMENT 'Webhook data from SePay',
    
    -- Timestamps
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When withdrawal was requested',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (affiliate_id) REFERENCES affiliates(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_affiliate_id (affiliate_id),
    INDEX idx_withdraw_code (withdraw_code),
    INDEX idx_status (status),
    INDEX idx_sepay_transaction_id (sepay_transaction_id),
    INDEX idx_requested_at (requested_at),
    INDEX idx_processed_at (processed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
