-- Migration: Create hero_buttons table
-- Created: 2026-05-13
-- Description: Hero section buttons management

CREATE TABLE IF NOT EXISTS hero_buttons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_section_id INT NOT NULL,
    button_text VARCHAR(255) NOT NULL,
    button_url VARCHAR(500) NOT NULL,
    button_style ENUM('primary', 'secondary', 'outline', 'ghost') NOT NULL DEFAULT 'primary',
    background_color VARCHAR(20) NULL,
    text_color VARCHAR(20) NULL,
    border_color VARCHAR(20) NULL,
    hover_background_color VARCHAR(20) NULL,
    hover_text_color VARCHAR(20) NULL,
    font_size VARCHAR(20) DEFAULT '16px',
    padding VARCHAR(50) DEFAULT '12px 24px',
    border_radius VARCHAR(20) DEFAULT '6px',
    sort_order INT NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (hero_section_id) REFERENCES hero_sections(id) ON DELETE CASCADE,
    INDEX idx_hero_section_id (hero_section_id),
    INDEX idx_sort_order (sort_order),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
