-- Add new fields to products table for Logistics/Data Source products
-- Skip columns that already exist, only add missing ones

-- Add missing columns to products table (will skip if already exists)
-- Run each separately to avoid errors

-- Create product_reviews table if not exists with utf8mb4 charset
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NULL,
    reviewer_name VARCHAR(255) CHARACTER SET utf8mb4 NULL,
    reviewer_email VARCHAR(255) NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(500) CHARACTER SET utf8mb4 NULL,
    content TEXT CHARACTER SET utf8mb4 NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    INDEX idx_status (status),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update sample products with logistics data
UPDATE products SET 
    benefits = '["Du lieu chinh xac, duoc kiem chung","Cap nhat dinh ky theo yeu cuu","Ho tro ky thuat 24/7","Bao mat thong tin khach hang","Gia canh tranh nhat thi truong","Da dang nguon hang logistics"]',
    data_structure = '[{"title":"Thong tin co ban","items":[{"title":"Ten nha phan phoi","type":"Text"},{"title":"Dia chi","type":"Text"},{"title":"So dien thoai","type":"Number"},{"title":"Email","type":"Email"}]},{"title":"Thong tin kinh doanh","items":[{"title":"Nganh hang","type":"Text"},{"title":"Doanh thu uoc tinh","type":"Number"},{"title":"Quy mo","type":"Text"}]},{"title":"Lien he","items":[{"title":"Nguoi lien he","type":"Text"},{"title":"So dien thoai lien he","type":"Number"},{"title":"Zalo","type":"Text"}]}]',
    supplier_name = 'Cong ty TNHH Logistics Viet Nam',
    supplier_title = 'Doi tac chien luoc',
    supplier_bio = 'Chung toi la don vi chuyen cung cap data nguon hang va logistics uy tin hang dau Viet Nam. Voi hon 5 nam kinh nghiem, chung toi da phuc vu hang tram doanh nghiep trong nganh.',
    supplier_avatar = 'https://ui-avatars.com/api/?name=Logistics+VN&background=356DF1&color=fff&size=150',
    supplier_social = '{"website":"#","hotline":"19001234"}',
    record_count = 15000,
    data_size = '25 MB',
    data_type = 'Nguon hang Logistics',
    data_format = 'Excel, CSV',
    data_source = 'Viet Nam',
    reliability = '95%'
WHERE status = 'active';

-- Insert sample reviews
INSERT INTO product_reviews (product_id, reviewer_name, rating, title, content, status) VALUES 
(1, 'Anh Minh - Cong ty van tai', 5, 'Data rat chinh xac', 'Data nguon hang rat chinh xac, giup cong ty toi tim duoc nhieu doi tac moi. Se mua them lan nua.', 'approved'),
(1, 'Chi Huong - Logistics Co', 4, 'Dich vu tot', 'Du lieu day du, nhan vien ho tro nhiet tinh. Mong co them nhieu data ve nganh van chuyen.', 'approved'),
(1, 'Anh Tuan - Shipper VN', 5, 'Rat hai long', 'Day la lan thu 3 toi mua data o day. Chat luong luon on dinh, gia ca hop ly.', 'approved');
