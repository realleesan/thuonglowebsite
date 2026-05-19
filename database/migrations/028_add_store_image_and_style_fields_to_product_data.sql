-- Add store_image and style_classification fields to product_data table
-- Created: 2026-05-19
-- Purpose: Add support for store image upload and style classification

ALTER TABLE `product_data` 
ADD COLUMN `store_image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `wechat_qr`,
ADD COLUMN `style_classification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `store_image`;

-- Add indexes for better performance  
ALTER TABLE `product_data` 
ADD INDEX `idx_store_image` (`store_image`),
ADD INDEX `idx_style_classification` (`style_classification`);
