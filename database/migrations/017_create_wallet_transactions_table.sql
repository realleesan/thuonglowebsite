-- Migration: Create wallet_transactions table
-- Created: 2026-02-17
-- Description: Track all wallet transactions for affiliates (commission, withdrawal, adjustment)

CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    affiliate_id INT NOT NULL,
    
    -- Transaction details
    type ENUM('commission', 'withdrawal', 'adjustment', 'refund') NOT NULL COMMENT 'Type of transaction',
    amount DECIMAL(15,2) NOT NULL COMMENT 'Transaction amount (positive or negative)',
    balance_before DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Balance before transaction',
    balance_after DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Balance after transaction',
    
    -- Reference information
    reference_type VARCHAR(50) NULL COMMENT 'Type of reference (order, withdrawal_request, etc)',
    reference_id INT NULL COMMENT 'ID of related record',
    order_id INT NULL COMMENT 'Related order ID if applicable',
    withdrawal_id INT NULL COMMENT 'Related withdrawal request ID if applicable',
    
    -- Description and metadata
    description TEXT NULL COMMENT 'Transaction description',
    admin_note TEXT NULL COMMENT 'Admin note for manual adjustments',
    metadata JSON NULL COMMENT 'Additional metadata',
    
    -- Status
    status ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'completed',
    
    -- Audit trail
    created_by INT NULL COMMENT 'User ID who created this transaction (for manual entries)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (affiliate_id) REFERENCES affiliates(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_affiliate_id (affiliate_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_order_id (order_id),
    INDEX idx_withdrawal_id (withdrawal_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
