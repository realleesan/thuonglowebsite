-- Migration: Create news table
-- Created: 2026-02-09
-- Description: News and blog posts management

CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content LONGTEXT NULL,
    image VARCHAR(255) NULL,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    featured BOOLEAN NOT NULL DEFAULT FALSE,
    author_id INT NOT NULL,
    category_id INT NULL,
    views INT NOT NULL DEFAULT 0,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_author_id (author_id),
    INDEX idx_published_at (published_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;