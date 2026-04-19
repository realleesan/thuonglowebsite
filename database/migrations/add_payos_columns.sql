-- Migration: Add PayOS payout columns to withdrawal_requests table
-- Run this SQL script to add columns needed for PayOS payout integration
-- NOTE: If columns already exist, this will throw errors. Please check first or run each ALTER separately.

-- Add payos_payout_id column (stores PayOS payout transaction ID)
ALTER TABLE withdrawal_requests 
ADD COLUMN payos_payout_id VARCHAR(100) NULL 
AFTER payment_completed_at;

-- Add payos_status column (stores payout status: PROCESSING, COMPLETED, FAILED, CANCELLED)
ALTER TABLE withdrawal_requests 
ADD COLUMN payos_status VARCHAR(50) NULL 
AFTER payos_payout_id;

-- Add payos_response column (stores full PayOS API response as JSON)
ALTER TABLE withdrawal_requests 
ADD COLUMN payos_response JSON NULL 
AFTER payos_status;

-- Add payos_webhook_data column (stores webhook callback data as JSON)
ALTER TABLE withdrawal_requests 
ADD COLUMN payos_webhook_data JSON NULL 
AFTER payos_response;

-- Add payos_webhook_received_at column (stores when webhook was received)
ALTER TABLE withdrawal_requests 
ADD COLUMN payos_webhook_received_at DATETIME NULL 
AFTER payos_webhook_data;

-- Create index on payos_payout_id for faster lookups
CREATE INDEX idx_payos_payout_id 
ON withdrawal_requests(payos_payout_id);

-- Create index on payos_status for filtering by status
CREATE INDEX idx_payos_status 
ON withdrawal_requests(payos_status);
