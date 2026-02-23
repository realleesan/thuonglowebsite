-- =====================================================================
-- Migration: 016_add_dashboard_indexes.sql
-- Thêm indexes để tối ưu query cho admin dashboard
-- Sử dụng Stored Procedure để kiểm tra index đã tồn tại trước khi thêm (tránh lỗi #1061)
-- =====================================================================

DELIMITER //

DROP PROCEDURE IF EXISTS AddIndexIfNotExists //

CREATE PROCEDURE AddIndexIfNotExists(
    IN tableName VARCHAR(64),
    IN indexName VARCHAR(64),
    IN columnList VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = tableName 
        AND index_name = indexName
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tableName, '` ADD INDEX `', indexName, '` (', columnList, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //

DELIMITER ;

-- Orders indexes: tối ưu query doanh thu, top products
CALL AddIndexIfNotExists('orders', 'idx_orders_status_created', '`status`, `created_at`');

-- Products indexes: count active, low stock query
CALL AddIndexIfNotExists('products', 'idx_products_status', '`status`');
CALL AddIndexIfNotExists('products', 'idx_products_stock_status', '`stock`, `status`');

-- Users indexes: new users by week query
CALL AddIndexIfNotExists('users', 'idx_users_created_at', '`created_at`');

-- News indexes: published news count
CALL AddIndexIfNotExists('news', 'idx_news_status', '`status`');

-- Events indexes: upcoming events query
CALL AddIndexIfNotExists('events', 'idx_events_start_date', '`start_date`');

-- Contacts indexes: notifications query
CALL AddIndexIfNotExists('contacts', 'idx_contacts_status', '`status`');

-- Dọn dẹp procedure sau khi chạy xong
DROP PROCEDURE IF EXISTS AddIndexIfNotExists;
