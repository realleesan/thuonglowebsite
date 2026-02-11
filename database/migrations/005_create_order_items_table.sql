-- Migration: Create order_items table
-- Created: 2026-02-09
-- Description: Individual items within each order

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100) NULL,
    product_type VARCHAR(50) NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    
    -- Product snapshot data (in case product is deleted/changed)
    product_data JSON NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;