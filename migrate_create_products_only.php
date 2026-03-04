-- ============================================================
-- Script tạo 5 sản phẩm Data Nguồn Hàng Logistics
-- CHẠY FILE NÀY TRƯỚC
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Thêm các cột logistics (bỏ qua nếu đã tồn tại)
ALTER TABLE products ADD COLUMN IF NOT EXISTS record_count INT DEFAULT 0;
ALTER TABLE products ADD COLUMN IF NOT EXISTS data_size VARCHAR(50) DEFAULT '';
ALTER TABLE products ADD COLUMN IF NOT EXISTS data_type VARCHAR(100) DEFAULT '';
ALTER TABLE products ADD COLUMN IF NOT EXISTS data_format VARCHAR(100) DEFAULT '';
ALTER TABLE products ADD COLUMN IF NOT EXISTS data_source VARCHAR(100) DEFAULT '';
ALTER TABLE products ADD COLUMN IF NOT EXISTS reliability VARCHAR(20) DEFAULT '';
ALTER TABLE products ADD COLUMN IF NOT EXISTS benefits TEXT;
ALTER TABLE products ADD COLUMN IF NOT EXISTS data_structure TEXT;
ALTER TABLE products ADD COLUMN IF NOT EXISTS supplier_name VARCHAR(255) DEFAULT '';
ALTER TABLE products ADD COLUMN IF NOT EXISTS supplier_title VARCHAR(255) DEFAULT '';
ALTER TABLE products ADD COLUMN IF NOT EXISTS supplier_bio TEXT;
ALTER TABLE products ADD COLUMN IF NOT EXISTS supplier_avatar VARCHAR(500) DEFAULT '';
ALTER TABLE products ADD COLUMN IF NOT EXISTS supplier_social VARCHAR(500) DEFAULT '';

-- Xóa dữ liệu cũ
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM order_items WHERE product_id > 0;
DELETE FROM products;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert 5 sản phẩm
INSERT INTO products (id, name, slug, short_description, description, price, sale_price, category_id, image, status, created_at, stock, record_count, data_size, data_type, data_format, data_source, reliability, benefits, data_structure, supplier_name, supplier_title, supplier_bio, supplier_avatar, supplier_social) VALUES
(1, 'Gói 100 Data Nguồn Hàng Random', 'goi-100-data-nguon-hang-random', 
'Gói data ngẫu nhiên từ nhiều ngành nghề khác nhau',
'Gói data nguồn hàng ngẫu nhiên bao gồm 100 thông tin nhà cung cấp từ nhiều ngành nghề khác nhau. Dữ liệu được kiểm chứng và cập nhật định kỳ.',
1500000, 990000, 1, 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&h=400&fit=crop', 'active', NOW(), 100, 100, '15 KB', 'Nguồn hàng đa ngành', 'Excel, CSV', 'Việt Nam', '90%',
'["Dữ liệu chính xác","Cập nhật hàng tháng","Hỗ trợ 24/7","Bảo mật thông tin"]',
'[{"title":"Thông tin cơ bản","items":[{"title":"Tên nhà phân phối"},{"title":"Địa chỉ"},{"title":"Số điện thoại"}]}]',
'Công ty TNHH Data Logistics VN',
'Đối tác chiến lược',
'Đơn vị chuyên cung cấp data nguồn hàng logistics uy tín.',
'https://ui-avatars.com/api/?name=Data+Logistics+VN&background=356DF1&color=fff&size=150',
'{"website":"https://datalogistics.vn","hotline":"19001234"}'),

(2, 'Gói 100 Data Ngành Quần Áo', 'goi-100-data-nguon-hang-nganh-quan-ao', 
'Gói data chuyên ngành thời trang - quần áo',
'Gói data chuyên biệt cho ngành thời trang - quần áo với 100 thông tin nhà cung cấp uy tín.',
1200000, 790000, 1, 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&h=400&fit=crop', 'active', NOW(), 100, 100, '12 KB', 'Nguồn hàng Ngành Quần Áo', 'Excel, CSV', 'Việt Nam', '95%',
'["Data chuyên ngành thời trang","Phân loại theo phân khúc","Hỗ trợ tư vấn"]',
'[{"title":"Nhà cung cấp","items":[{"title":"Tên công ty"},{"title":"Địa chỉ"}]}]',
'Fashion Supply Vietnam',
'Chuyên gia ngành thời trang',
'Đơn vị hàng đầu về cung cấp data ngành thời trang.',
'https://ui-avatars.com/api/?name=Fashion+Supply&background=FF6B6B&color=fff&size=150',
'{"website":"https://fashionsupply.vn","hotline":"19005678"}'),

(3, 'Gói 100 Data Ngành Điện Tử', 'goi-100-data-nguon-hang-nganh-dien-tu', 
'Gói data chuyên ngành điện tử - công nghệ',
'Gói data chuyên sâu ngành điện tử với 100 thông tin nhà cung cấp uy tín.',
1800000, 1290000, 1, 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600&h=400&fit=crop', 'active', NOW(), 100, 100, '18 KB', 'Nguồn hàng Ngành Điện Tử', 'Excel, CSV', 'Việt Nam, Trung Quốc', '92%',
'["Database ngành điện tử","Hỗ trợ kỹ thuật","Cập nhật công nghệ"]',
'[{"title":"Nhà cung cấp","items":[{"title":"Tên công ty"},{"title":"Hotline"}]}]',
'TechSource Vietnam',
'Chuyên gia công nghệ',
'Đơn vị chuyên cung cấp data và tư vấn nguồn hàng điện tử.',
'https://ui-avatars.com/api/?name=TechSource+VN&background=00D9A5&color=fff&size=150',
'{"website":"https://techsource.vn","hotline":"19008899"}'),

(4, 'Gói 100 Data Ngành Thực Phẩm', 'goi-100-data-nguon-hang-nganh-thuc-pham', 
'Gói data chuyên ngành thực phẩm - đồ uống',
'Gói data chuyên ngành thực phẩm với 100 thông tin nhà cung cấp uy tín.',
1400000, 890000, 1, 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=600&h=400&fit=crop', 'active', NOW(), 100, 100, '14 KB', 'Nguồn hàng Ngành Thực Phẩm', 'Excel, CSV', 'Việt Nam', '94%',
'["Data ngành thực phẩm an toàn","Kết nối nhà cung cấp uy tín","Hỗ trợ chứng nhận"]',
'[{"title":"Nhà cung cấp","items":[{"title":"Tên nhà cung cấp"},{"title":"Chứng nhận VSATTP"}]}]',
'FoodChain Vietnam',
'Chuỗi cung ứng thực phẩm',
'Chuyên cung cấp data và tư vấn chuỗi cung ứng thực phẩm.',
'https://ui-avatars.com/api/?name=FoodChain+VN&background=4CAF50&color=fff&size=150',
'{"website":"https://foodchain.vn","hotline":"19004567"}'),

(5, 'Gói 100 Data Ngành Vận Tải', 'goi-100-data-nguon-hang-nganh-van-tai', 
'Gói data chuyên ngành vận tải - logistics',
'Gói data chuyên sâu ngành vận tải và logistics với 100 thông tin đối tác uy tín.',
2000000, 1490000, 1, 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=600&h=400&fit=crop', 'active', NOW(), 100, 100, '20 KB', 'Nguồn hàng Ngành Vận Tải', 'Excel, CSV', 'Việt Nam', '96%',
'["Data ngành vận tải lớn nhất","Kết nối đối tác logistics","Hỗ trợ tối ưu vận chuyển"]',
'[{"title":"Đơn vị vận chuyển","items":[{"title":"Tên công ty"},{"title":"Số xe tải"}]}]',
'LogiConnect Vietnam',
'Kết nối logistics',
'Đơn vị hàng đầu về cung cấp data và kết nối đối tác logistics.',
'https://ui-avatars.com/api/?name=LogiConnect+VN&background=9C27B0&color=fff&size=150',
'{"website":"https://logiconnect.vn","hotline":"19007890"}');

SELECT 'Tao san pham thanh cong! So luong: ' AS message, COUNT(*) AS total FROM products;
