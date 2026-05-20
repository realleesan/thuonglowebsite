-- Migration: Create budget_products_section table
-- Created: 2026-05-20
-- Description: Budget products section management for homepage

CREATE TABLE IF NOT EXISTS budget_products_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
