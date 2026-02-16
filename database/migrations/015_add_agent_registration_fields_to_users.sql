-- Migration: Add agent registration fields to users table
-- Created: 2026-02-16
-- Description: Add fields to track agent registration requests and status

-- Add agent registration tracking fields
ALTER TABLE users 
ADD COLUMN agent_request_status ENUM('none', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'none' AFTER status,
ADD COLUMN agent_request_date TIMESTAMP NULL AFTER agent_request_status,
ADD COLUMN agent_approved_date TIMESTAMP NULL AFTER agent_request_date;

-- Add indexes for performance
ALTER TABLE users 
ADD INDEX idx_agent_request_status (agent_request_status),
ADD INDEX idx_agent_request_date (agent_request_date);