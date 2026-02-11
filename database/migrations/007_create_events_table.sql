-- Migration: Create events table
-- Created: 2026-02-09
-- Description: Events and workshops management

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    content LONGTEXT NULL,
    image VARCHAR(255) NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    location VARCHAR(255) NULL,
    address TEXT NULL,
    price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    max_participants INT NULL,
    current_participants INT NOT NULL DEFAULT 0,
    status ENUM('draft', 'upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
    featured BOOLEAN NOT NULL DEFAULT FALSE,
    organizer_id INT NOT NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date),
    INDEX idx_organizer_id (organizer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;