-- Migration: Add username field to users table
-- Created: 2026-02-15
-- Description: Add username field for user identification

ALTER TABLE users 
ADD COLUMN username VARCHAR(50) UNIQUE NULL AFTER name,
ADD INDEX idx_username (username);

-- Update existing users with default usernames based on email
UPDATE users 
SET username = SUBSTRING_INDEX(email, '@', 1) 
WHERE username IS NULL;