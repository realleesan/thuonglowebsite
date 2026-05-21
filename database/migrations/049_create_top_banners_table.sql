-- Create top_banners table
CREATE TABLE IF NOT EXISTS `top_banners` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `content` varchar(255) NOT NULL,
    `button_text` varchar(100) DEFAULT NULL,
    `button_url` varchar(255) DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default Top Banner data
INSERT INTO `top_banners` (`content`, `button_text`, `button_url`, `is_active`) 
VALUES ('Chào mừng đến với ThuongLo! Nền tảng data nguồn hàng và dịch vụ thương mại xuyên biên giới hàng đầu.', 'Khám phá ngay!', '?page=products', 1);
