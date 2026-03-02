-- Migration: Add SePay payment fields to orders table
-- Created: 2026-02-17
-- Description: Add fields for SePay integration and payment tracking

ALTER TABLE orders
ADD COLUMN sepay_transaction_id VARCHAR(100) NULL COMMENT 'SePay transaction ID from webhook',
ADD COLUMN sepay_qr_code TEXT NULL COMMENT 'SePay QR code data/URL',
ADD COLUMN qr_generated_at TIMESTAMP NULL COMMENT 'When QR code was generated',
ADD COLUMN qr_expired_at TIMESTAMP NULL COMMENT 'When QR code expires (120s timeout)',
ADD COLUMN payment_timeout INT DEFAULT 120 COMMENT 'Payment timeout in seconds',
ADD COLUMN payment_completed_at TIMESTAMP NULL COMMENT 'When payment was completed',
ADD COLUMN payment_failed_at TIMESTAMP NULL COMMENT 'When payment failed',
ADD COLUMN payment_error_message TEXT NULL COMMENT 'Error message if payment failed',
ADD COLUMN webhook_received_at TIMESTAMP NULL COMMENT 'When webhook was received from SePay',
ADD COLUMN is_expired TINYINT(1) DEFAULT 0 COMMENT 'Whether order has expired (timeout)',

ADD INDEX idx_sepay_transaction_id (sepay_transaction_id),
ADD INDEX idx_qr_expired_at (qr_expired_at),
ADD INDEX idx_is_expired (is_expired),
ADD INDEX idx_payment_completed_at (payment_completed_at);
