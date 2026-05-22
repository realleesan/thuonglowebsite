-- Migration: Create agent_contents table for dynamic agent pages
-- Path: database/migrations/050_create_agent_contents_table.sql

CREATE TABLE IF NOT EXISTS agent_contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_key VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    content MEDIUMTEXT,
    image VARCHAR(255) DEFAULT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default content for the 4 required agent pages
INSERT INTO agent_contents (page_key, title, content, meta_title, meta_description) VALUES
('chuong_trinh', 'Chương trình đại lý', '<h2>Chương trình đại lý Thuong Lo</h2><p>Chào mừng bạn đến với chương trình hợp tác đại lý cùng Thuong Lo. Hãy đăng ký ngay hôm nay để nhận các chính sách và nguồn thu nhập hấp dẫn.</p>', 'Chương trình đại lý - Thuong Lo', 'Chương trình đại lý hợp tác kinh doanh cùng Thuong Lo'),
('huong_dan', 'Hướng dẫn đăng ký đại lý', '<h2>Hướng dẫn đăng ký đại lý Thuong Lo</h2><p>Để trở thành đại lý chính thức của Thuong Lo, vui lòng làm theo các bước hướng dẫn chi tiết dưới đây...</p>', 'Hướng dẫn đăng ký đại lý - Thuong Lo', 'Hướng dẫn các bước chi tiết đăng ký làm đại lý tại Thuong Lo'),
('chinh_sach', 'Chính sách đại lý', '<h2>Chính sách đại lý Thuong Lo</h2><p>Thuong Lo cung cấp chính sách chia sẻ hoa hồng và chiết khấu cực kỳ cạnh tranh dành cho các đại lý hoạt động tích cực...</p>', 'Chính sách đại lý - Thuong Lo', 'Chính sách chiết khấu, quyền lợi và hoa hồng dành cho đại lý Thuong Lo'),
('tai_nguyen', 'Tài nguyên - tài liệu đại lý', '<h2>Tài nguyên & Tài liệu dành cho đại lý</h2><p>Dưới đây là các tài nguyên hỗ trợ tiếp thị, tài liệu sản phẩm, hình ảnh và video dành riêng cho đại lý để tối ưu hiệu quả bán hàng...</p>', 'Tài nguyên - tài liệu đại lý - Thuong Lo', 'Tài nguyên tiếp thị, tài liệu đào tạo và hình ảnh dành cho đại lý Thuong Lo')
ON DUPLICATE KEY UPDATE page_key=page_key;
