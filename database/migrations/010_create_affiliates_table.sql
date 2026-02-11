-- Migration: Create affiliates table
-- Created: 2026-02-09
-- Description: Affiliate program management

CREATE TABLE IF NOT EXISTS affiliates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    referral_code VARCHAR(50) NOT NULL UNIQUE,
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    total_sales DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_commission DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    paid_commission DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    pending_commission DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'active', 'inactive', 'suspended') NOT NULL DEFAULT 'pending',
    payment_method ENUM('bank_transfer', 'momo', 'vnpay', 'cash') NULL,
    payment_details JSON NULL,
    approved_at TIMESTAMP NULL,
    approved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_referral_code (referral_code),
    INDEX idx_status (status),
    INDEX idx_commission_rate (commission_rate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;