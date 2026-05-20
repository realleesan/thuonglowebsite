-- Migration: Add missing fields to categories table (safe version)
-- Created: 2026-05-20
-- Description: Add only fields that don't exist yet

-- Add show_in_filter column if it doesn't exist
ALTER TABLE categories 
ADD COLUMN IF NOT EXISTS show_in_filter TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Whether category shows in product filters';

-- Add type column if it doesn't exist  
ALTER TABLE categories
ADD COLUMN IF NOT EXISTS type VARCHAR(50) NULL DEFAULT NULL COMMENT 'Category type: product, news, etc.';

-- Add indexes for better performance (only if they don't exist)
ALTER TABLE categories 
ADD INDEX IF NOT EXISTS idx_show_in_filter (show_in_filter),
ADD INDEX IF NOT EXISTS idx_type (type);
