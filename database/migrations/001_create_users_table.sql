-- Migration: Create users table
-- Created: 2026-02-09
-- Description: Main users table for authentication and user management

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'agent') NOT NULL DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active',
    address TEXT NULL,
    avatar VARCHAR(255) NULL,
    points INT NOT NULL DEFAULT 0,
    level VARCHAR(50) DEFAULT 'Bronze',
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;