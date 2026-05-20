-- Migration: Create featured_categories_section table
-- Created: 2026-05-20
-- Description: Store featured categories section settings for homepage

CREATE TABLE IF NOT EXISTS featured_categories_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
