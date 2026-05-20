-- Migration: Add type field to categories table (safe version)
-- Created: 2026-05-20
-- Description: Add type field since featured and show_in_filter likely already exist

-- Add type column if it doesn't exist (this is the only field likely missing)
ALTER TABLE categories
ADD COLUMN type VARCHAR(50) NULL DEFAULT NULL COMMENT 'Category type: product, news, etc.';

-- Add index for type field
ALTER TABLE categories
ADD INDEX idx_type (type);
