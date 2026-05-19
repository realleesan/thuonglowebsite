-- Migration: Create site_settings table for dynamic logo and favicon management
-- Created: 2024-05-19

CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Unique key for setting (e.g., logo_header, logo_footer, favicon)',
    `setting_value` TEXT NULL COMMENT 'Setting value (file path or text)',
    `setting_type` ENUM('image', 'text', 'json', 'number') DEFAULT 'text' COMMENT 'Type of setting',
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'Group settings (e.g., logo, appearance, general)',
    `description` VARCHAR(255) NULL COMMENT 'Description of the setting',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_setting_key` (`setting_key`),
    INDEX `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Site settings for dynamic configuration';

-- Insert default logo and favicon settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `description`) VALUES
('logo_header', 'logo/logo.svg', 'image', 'logo', 'Logo hiển thị ở header người dùng'),
('logo_footer', 'logo/logo.svg', 'image', 'logo', 'Logo hiển thị ở footer người dùng'),
('logo_admin_full', 'logo/logo.svg', 'image', 'logo', 'Logo đầy đủ cho admin sidebar'),
('logo_admin_mini', 'logo/logo_mini.svg', 'image', 'logo', 'Logo mini cho admin sidebar khi thu gọn'),
('logo_affiliate_full', 'logo/logo.svg', 'image', 'logo', 'Logo đầy đủ cho affiliate sidebar'),
('logo_affiliate_mini', 'logo/logo_mini.svg', 'image', 'logo', 'Logo mini cho affiliate sidebar khi thu gọn'),
('favicon', 'logo/logo_mini.svg', 'image', 'logo', 'Favicon của website'),
('site_name', 'ThuongLo', 'text', 'general', 'Tên website'),
('site_description', 'Nền tảng data nguồn hàng và dịch vụ thương mại xuyên biên giới hàng đầu', 'text', 'general', 'Mô tả website');
