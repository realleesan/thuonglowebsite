-- Create filter_config table for storing product filter configuration
CREATE TABLE IF NOT EXISTS `filter_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `criteria_type` enum('categories','brands','price_ranges') NOT NULL COMMENT 'Type of filter criteria',
    `item_id` int(11) NOT NULL COMMENT 'ID of the filter item (category_id, brand_id, price_range_id)',
    `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Parent item ID for hierarchical items',
    `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Sort order within the same level',
    `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether this filter item is enabled',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_criteria_type` (`criteria_type`),
    KEY `idx_item_id` (`item_id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_sort_order` (`sort_order`),
    KEY `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create filter_settings table for global filter settings
CREATE TABLE IF NOT EXISTS `filter_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL COMMENT 'Setting key',
    `setting_value` text NOT NULL COMMENT 'Setting value (JSON encoded)',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default filter settings
INSERT INTO `filter_settings` (`setting_key`, `setting_value`) VALUES
('criteria_order', '{"categories":1,"brands":2,"price_ranges":3}'),
('criteria_enabled', '{"categories":true,"brands":true,"price_ranges":true}')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Insert default filter configuration for categories (if categories table exists)
INSERT IGNORE INTO `filter_config` (`criteria_type`, `item_id`, `parent_id`, `sort_order`, `is_enabled`)
SELECT 
    'categories' as criteria_type,
    id as item_id,
    COALESCE(parent_id, 0) as parent_id,
    (SELECT COUNT(*) FROM categories c2 WHERE c2.parent_id = COALESCE(c1.parent_id, 0) AND c2.id <= c1.id) as sort_order,
    1 as is_enabled
FROM categories c1
ORDER BY parent_id, id;

-- Insert default filter configuration for brands (if brands table exists)
INSERT IGNORE INTO `filter_config` (`criteria_type`, `item_id`, `parent_id`, `sort_order`, `is_enabled`)
SELECT 
    'brands' as criteria_type,
    id as item_id,
    0 as parent_id,
    (SELECT COUNT(*) FROM brands b2 WHERE b2.id <= b1.id) as sort_order,
    1 as is_enabled
FROM brands b1
ORDER BY id;

-- Insert default price ranges configuration
INSERT IGNORE INTO `filter_config` (`criteria_type`, `item_id`, `parent_id`, `sort_order`, `is_enabled`) VALUES
('price_ranges', 1, 0, 1, 1),
('price_ranges', 2, 0, 2, 1),
('price_ranges', 3, 0, 3, 1),
('price_ranges', 4, 0, 4, 1),
('price_ranges', 5, 0, 5, 1);
