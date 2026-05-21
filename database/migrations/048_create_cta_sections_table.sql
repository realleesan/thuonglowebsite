-- Create cta_sections table
CREATE TABLE IF NOT EXISTS `cta_sections` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `subtitle` varchar(255) DEFAULT NULL,
    `content` text DEFAULT NULL,
    `button_text` varchar(255) NOT NULL DEFAULT 'Đăng ký ngay',
    `button_url` varchar(255) NOT NULL DEFAULT '?page=agent',
    `background_color` varchar(50) DEFAULT '#ECEDEF',
    `image_url` varchar(255) DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default CTA section data
INSERT INTO `cta_sections` (`title`, `subtitle`, `content`, `button_text`, `button_url`, `background_color`, `image_url`, `is_active`) 
VALUES (
    'Trở thành một trong <span class=\"highlight\">500+</span>',
    'Đại Lý Affiliate ThuongLo',
    'Tham gia cùng chúng tôi và kiếm thu nhập thụ động từ việc giới thiệu dịch vụ thương mại xuyên biên giới hàng đầu Việt Nam',
    'Đăng ký ngay',
    '?page=agent',
    '#ECEDEF',
    'home/cta-final-1.png',
    1
);
