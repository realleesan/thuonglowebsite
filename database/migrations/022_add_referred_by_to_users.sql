-- Migration: Add referred_by column to users table
-- Created: 2026-04-05
-- Description: Track which affiliate referred a user for the affiliate program

-- Add referred_by column to users table
ALTER TABLE users 
ADD COLUMN referred_by INT NULL COMMENT 'Affiliate user_id who referred this user' AFTER role,
ADD INDEX idx_referred_by (referred_by);

-- Add foreign key constraint (optional, references affiliates.user_id)
ALTER TABLE users
ADD CONSTRAINT fk_users_referred_by FOREIGN KEY (referred_by) REFERENCES affiliates(user_id) ON DELETE SET NULL;
