-- Migration: Add wallet and bank fields to affiliates table
-- Created: 2026-02-17
-- Description: Add wallet balance and bank information for withdrawal
-- Includes: balance fields, bank information, OTP for bank changes

ALTER TABLE affiliates
ADD COLUMN balance DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Available balance (can withdraw)',
ADD COLUMN pending_withdrawal DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount pending withdrawal (frozen)',
ADD COLUMN total_withdrawn DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total amount withdrawn',
ADD COLUMN bank_name VARCHAR(100) NULL COMMENT 'Bank name for withdrawal',
ADD COLUMN bank_account VARCHAR(50) NULL COMMENT 'Bank account number',
ADD COLUMN account_holder VARCHAR(255) NULL COMMENT 'Account holder name',
ADD COLUMN bank_branch VARCHAR(255) NULL COMMENT 'Bank branch (optional)',
ADD COLUMN bank_verified TINYINT(1) DEFAULT 0 COMMENT 'Whether bank info is verified',
ADD COLUMN bank_verified_at TIMESTAMP NULL COMMENT 'When bank info was verified',
ADD COLUMN bank_change_otp VARCHAR(10) NULL COMMENT 'OTP for changing bank info',
ADD COLUMN bank_change_otp_expires_at TIMESTAMP NULL COMMENT 'OTP expiration time',
ADD COLUMN bank_last_changed_at TIMESTAMP NULL COMMENT 'Last time bank info was changed',
ADD INDEX idx_balance (balance),
ADD INDEX idx_pending_withdrawal (pending_withdrawal),
ADD INDEX idx_bank_verified (bank_verified);

-- Update existing records to have balance equal to pending_commission
UPDATE affiliates 
SET balance = pending_commission 
WHERE balance = 0 AND pending_commission > 0;
