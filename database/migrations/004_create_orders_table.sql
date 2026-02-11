-- Migration: Create orders table
-- Created: 2026-02-09
-- Description: Main orders table for tracking customer purchases

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'partial') NOT NULL DEFAULT 'pending',
    payment_method ENUM('bank_transfer', 'momo', 'vnpay', 'zalopay', 'cash', 'credit_card') NULL,
    payment_reference VARCHAR(255) NULL,
    
    -- Pricing
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    shipping_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    
    -- Shipping info
    shipping_name VARCHAR(255) NULL,
    shipping_email VARCHAR(255) NULL,
    shipping_phone VARCHAR(20) NULL,
    shipping_address TEXT NULL,
    shipping_city VARCHAR(100) NULL,
    shipping_state VARCHAR(100) NULL,
    shipping_postal_code VARCHAR(20) NULL,
    shipping_country VARCHAR(100) DEFAULT 'Vietnam',
    
    -- Billing info (if different from shipping)
    billing_name VARCHAR(255) NULL,
    billing_email VARCHAR(255) NULL,
    billing_phone VARCHAR(20) NULL,
    billing_address TEXT NULL,
    billing_city VARCHAR(100) NULL,
    billing_state VARCHAR(100) NULL,
    billing_postal_code VARCHAR(20) NULL,
    billing_country VARCHAR(100) DEFAULT 'Vietnam',
    
    -- Additional info
    notes TEXT NULL,
    admin_notes TEXT NULL,
    coupon_code VARCHAR(50) NULL,
    affiliate_id INT NULL,
    commission_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    
    -- Timestamps
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (affiliate_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created_at (created_at),
    INDEX idx_affiliate_id (affiliate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;