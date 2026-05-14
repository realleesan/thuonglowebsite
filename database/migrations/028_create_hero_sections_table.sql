-- Migration: Create hero_sections table
-- Created: 2026-05-13
-- Description: Hero section management for homepage

CREATE TABLE IF NOT EXISTS hero_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_main VARCHAR(500) NOT NULL,
    title_highlight VARCHAR(500) NOT NULL,
    subtitle TEXT NULL,
    background_color VARCHAR(20) DEFAULT '#ffffff',
    text_color VARCHAR(20) DEFAULT '#333333',
    highlight_color VARCHAR(20) DEFAULT '#356DF1',
    font_family VARCHAR(100) DEFAULT 'Arial, sans-serif',
    title_font_size VARCHAR(20) DEFAULT '48px',
    subtitle_font_size VARCHAR(20) DEFAULT '18px',
    image_url VARCHAR(500) NULL,
    image_alt VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
