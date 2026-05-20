-- Migration: Add featured and show_in_filter fields to categories table
-- Created: 2026-05-20
-- Description: Add featured and show_in_filter fields for managing homepage categories display

ALTER TABLE categories 
ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether category is featured on homepage',
ADD COLUMN show_in_filter TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Whether category shows in product filters',
ADD COLUMN type VARCHAR(50) NULL DEFAULT NULL COMMENT 'Category type: product, news, etc.';

-- Add indexes for better performance
ALTER TABLE categories 
ADD INDEX idx_featured (featured),
ADD INDEX idx_show_in_filter (show_in_filter),
ADD INDEX idx_type (type);
