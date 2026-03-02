<?php
/**
 * Migration: Create carts and wishlists tables
 * Run this file to create the required tables for cart and wishlist functionality
 */

// Define security constant
define('THUONGLO_INIT', true);

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

echo "Creating carts and wishlists tables...\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    // Create carts table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `carts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `quantity` int(11) NOT NULL DEFAULT 1,
            `price` decimal(15,2) NOT NULL DEFAULT 0,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_product_id` (`product_id`),
            UNIQUE KEY `idx_user_product` (`user_id`, `product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ carts table created or already exists\n";
    
    // Create wishlists table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `wishlists` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `notes` text COLLATE utf8mb4_unicode_ci,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_product_id` (`product_id`),
            UNIQUE KEY `idx_user_product` (`user_id`, `product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ wishlists table created or already exists\n";
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
