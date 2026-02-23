-- =====================================================================
-- Migration: 017_create_admin_infra_tables.sql
-- Tạo bảng cho hệ thống thông báo và menu động của Admin
-- =====================================================================

-- Bảng thông báo Admin
CREATE TABLE IF NOT EXISTS `admin_notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(50) NOT NULL COMMENT 'order, user, stock, contact, system',
    `message` TEXT NOT NULL,
    `icon` VARCHAR(50) DEFAULT 'fas fa-info-circle',
    `link` VARCHAR(255) DEFAULT '#',
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_is_read` (`is_read`),
    INDEX `idx_notif_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Menu Admin
CREATE TABLE IF NOT EXISTS `admin_menus` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(50) DEFAULT 'fas fa-circle',
    `url` VARCHAR(255) NOT NULL,
    `sort_order` INT DEFAULT 0,
    `role_required` VARCHAR(50) DEFAULT 'admin',
    `status` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_url` (`url`),
    FOREIGN KEY (`parent_id`) REFERENCES `admin_menus`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed dữ liệu mẫu cho Menu (Sử dụng INSERT IGNORE để tránh trùng lặp khi chạy lại)
INSERT IGNORE INTO `admin_menus` (`name`, `icon`, `url`, `sort_order`) VALUES
('Dashboard', 'fas fa-tachometer-alt', '?page=admin&module=dashboard', 1),
('Sản phẩm', 'fas fa-box', '?page=admin&module=products', 2),
('Danh mục', 'fas fa-list', '?page=admin&module=categories', 3),
('Tin tức', 'fas fa-newspaper', '?page=admin&module=news', 4),
('Sự kiện', 'fas fa-calendar-alt', '?page=admin&module=events', 5),
('Đơn hàng', 'fas fa-shopping-cart', '?page=admin&module=orders', 6),
('Người dùng', 'fas fa-users', '?page=admin&module=users', 7),
('Đại lý', 'fas fa-user-tie', '?page=admin&module=affiliates', 8),
('Liên hệ', 'fas fa-envelope', '?page=admin&module=contact', 9),
('Doanh thu', 'fas fa-chart-line', '?page=admin&module=revenue', 10),
('Cài đặt', 'fas fa-cog', '?page=admin&module=settings', 11);
