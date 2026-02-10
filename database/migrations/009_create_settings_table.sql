-- Migration: Create settings table
-- Created: 2026-02-09
-- Description: Application settings and configuration

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL UNIQUE,
    `value` LONGTEXT NULL,
    `type` ENUM('text', 'textarea', 'number', 'boolean', 'email', 'url', 'json', 'file') NOT NULL DEFAULT 'text',
    `group` VARCHAR(100) NOT NULL DEFAULT 'general',
    description TEXT NULL,
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_key (`key`),
    INDEX idx_group (`group`),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;