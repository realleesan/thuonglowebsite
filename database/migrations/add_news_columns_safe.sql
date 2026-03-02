-- Add missing columns to news table (safe version)
-- This will only add columns if they don't exist

-- Add author_name column if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'news' 
AND COLUMN_NAME = 'author_name';

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE `news` ADD COLUMN `author_name` VARCHAR(255) NULL DEFAULT "Admin" AFTER `category_id`',
    'SELECT "Column author_name already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tags column if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'news' 
AND COLUMN_NAME = 'tags';

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE `news` ADD COLUMN `tags` TEXT NULL AFTER `author_name`',
    'SELECT "Column tags already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add views column if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'news' 
AND COLUMN_NAME = 'views';

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE `news` ADD COLUMN `views` INT(11) NOT NULL DEFAULT 0 AFTER `tags`',
    'SELECT "Column views already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
