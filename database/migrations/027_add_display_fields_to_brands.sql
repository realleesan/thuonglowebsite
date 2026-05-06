-- Migration: Add is_featured field to brands table
-- Created: 2026-05-06
-- Description: Add is_featured field for brand featured option
-- Note: show_in_filter field already exists

ALTER TABLE brands 
ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER show_in_filter;

-- Add index for better performance
ALTER TABLE brands ADD INDEX idx_is_featured (is_featured);
