<?php
/**
 * Migration: Create product_data and product_data_access tables
 * 
 * Tables:
 * - product_data: Lưu trữ data thực tế (supplier info, wechat, phone, etc.)
 * - product_data_access: Theo dõi phiên xem data của user
 */

require_once __DIR__ . '/config.php';

$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "Starting migration: Create product_data tables\n";

// Create product_data table
$sql1 = "
CREATE TABLE IF NOT EXISTS product_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    address TEXT,
    wechat_account VARCHAR(100),
    phone VARCHAR(20),
    wechat_qr VARCHAR(500),
    row_index INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

try {
    $pdo->exec($sql1);
    echo "✓ Created table: product_data\n";
} catch (PDOException $e) {
    echo "✗ Error creating product_data: " . $e->getMessage() . "\n";
}

// Create product_data_access table
$sql2 = "
CREATE TABLE IF NOT EXISTS product_data_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    access_token VARCHAR(64) UNIQUE,
    expires_at TIMESTAMP NULL,
    viewed_rows TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_product (user_id, product_id),
    INDEX idx_access_token (access_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

try {
    $pdo->exec($sql2);
    echo "✓ Created table: product_data_access\n";
} catch (PDOException $e) {
    echo "✗ Error creating product_data_access: " . $e->getMessage() . "\n";
}

// Add data_view_duration column to products table if not exists
$sql3 = "ALTER TABLE products ADD COLUMN IF NOT EXISTS data_view_duration INT DEFAULT 15 COMMENT 'Thoi gian xem data (phut)'";

try {
    $pdo->exec($sql3);
    echo "✓ Added column: data_view_duration to products\n";
} catch (PDOException $e) {
    echo "✗ Note: data_view_duration may already exist: " . $e->getMessage() . "\n";
}

echo "\nMigration completed successfully!\n";
