-- Add type column to categories table to distinguish between product and news categories
-- Simple version without checking information_schema

-- Try to add type column (will show error if already exists, that's OK)
ALTER TABLE `categories` ADD COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'product' AFTER `description`;

-- Update existing categories to have type = 'product' (default for existing data)
UPDATE `categories` SET `type` = 'product' WHERE `type` = '';

-- Display result
SELECT 'Type column added successfully!' AS message;
SELECT id, name, slug, type FROM categories LIMIT 10;
