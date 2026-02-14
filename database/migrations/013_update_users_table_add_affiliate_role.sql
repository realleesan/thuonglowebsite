-- Migration: Update users table to add affiliate role
-- Created: 2026-02-14
-- Description: Add affiliate role to users table and update existing agent roles

-- Add affiliate to role enum
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'affiliate', 'agent') NOT NULL DEFAULT 'user';

-- Update existing 'agent' roles to 'affiliate' for consistency with auth system
UPDATE users SET role = 'affiliate' WHERE role = 'agent';