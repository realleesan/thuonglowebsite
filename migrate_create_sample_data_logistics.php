-- ============================================================
-- Bước 0: Set charset cho phép tiếng Việt
-- ============================================================
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================================
-- Bước 1: Thêm các cột mới cho logistics data (nếu chưa có)
-- ============================================================

-- Thêm các cột logistics vào bảng products (bỏ qua nếu đã tồn tại)
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

-- ============================================================
-- Bước 2: Xóa dữ liệu cũ
-- ============================================================

-- Trước tiên xóa các bảng có foreign key reference đến products
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE product_reviews;
DELETE FROM order_items WHERE product_id > 0;
DELETE FROM products;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Bước 3: INSERT 5 sản phẩm Data Nguồn Hàng Logistics
-- ============================================================

-- Sản phẩm 1: Gói 100 Data Nguồn Hàng Random
INSERT INTO products (id, name, slug, short_description, description, price, sale_price, category_id, image, status, created_at, stock, record_count, data_size, data_type, data_format, data_source, reliability, benefits, data_structure, supplier_name, supplier_title, supplier_bio, supplier_avatar, supplier_social) VALUES
(1, 'Gói 100 Data Nguồn Hàng Random', 'goi-100-data-nguon-hang-random', 
'Gói data ngẫu nhiên từ nhiều ngành nghề khác nhau. Phù hợp cho doanh nghiệp cần dữ liệu đa dạng để tiếp cận nhiều lĩnh vực.',
'Gói data nguồn hàng ngẫu nhiên (random) bao gồm 100 thông tin nhà cung cấp từ nhiều ngành nghề khác nhau như: thời trang, điện tử, thực phẩm, vận tải, xây dựng... Dữ liệu được kiểm chứng và cập nhật định kỳ hàng tháng.',
1500000, 990000, 1, 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&h=400&fit=crop', 'active', NOW(), 100, 100, '15 KB', 'Nguồn hàng đa ngành', 'Excel, CSV', 'Việt Nam', '90%',
'["Dữ liệu chính xác, được kiểm chứng","Cập nhật định kỳ hàng tháng","Hỗ trợ kỹ thuật 24/7","Bảo mật thông tin khách hàng","Giá cạnh tranh nhất thị trường","Đa dạng ngành nghề"]',
'[{"title":"Thông tin cơ bản","items":[{"title":"Tên nhà phân phối","type":"Text"},{"title":"Địa chỉ","type":"Text"},{"title":"Số điện thoại","type":"Number"},{"title":"Email","type":"Email"}]},{"title":"Thông tin kinh doanh","items":[{"title":"Ngành hàng","type":"Text"},{"title":"Doanh thu ước tính","type":"Number"},{"title":"Quy mô","type":"Text"}]},{"title":"Liên hệ","items":[{"title":"Người liên hệ","type":"Text"},{"title":"Số điện thoại liên hệ","type":"Number"},{"title":"Zalo","type":"Text"}]}]',
'Công ty TNHH Data Logistics VN',
'Đối tác chiến lược',
'Chúng tôi là đơn vị chuyên cung cấp data nguồn hàng và logistics uy tín hàng đầu Việt Nam. Với hơn 5 năm kinh nghiệm, chúng tôi đã phục vụ hàng trăm doanh nghiệp trong ngành.',
'https://ui-avatars.com/api/?name=Data+Logistics+VN&background=356DF1&color=fff&size=150',
'{"website":"https://datalogistics.vn","hotline":"19001234"}');

-- Sản phẩm 2: Gói 100 Data Ngành Quần Áo
INSERT INTO products (name, slug, short_description, description, price, sale_price, category_id, image, status, created_at, stock, record_count, data_size, data_type, data_format, data_source, reliability, benefits, data_structure, supplier_name, supplier_title, supplier_bio, supplier_avatar, supplier_social) VALUES
('Gói 100 Data Nguồn Hàng Ngành Quần Áo', 'goi-100-data-nguon-hang-nganh-quan-ao', 
'Gói data chuyên ngành thời trang - quần áo. Bao gồm thông tin nhà cung cấp, đại lý, xưởng sản xuất trong ngành.',
'Gói data chuyên biệt cho ngành thời trang - quần áo với 100 thông tin nhà cung cấp uy tín. Bao gồm: xưởng sản xuất, đại lý phân phối, nhà cung cấp nguyên liệu, showroom... Dữ liệu được phân loại theo từng phân khúc thị trường.',
1200000, 790000, 1, 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&h=400&fit=crop', 'active', NOW(), 100, 100, '12 KB', 'Nguồn hàng Ngành Quần Áo', 'Excel, CSV', 'Việt Nam', '95%',
'["Data chuyên ngành thời trang","Phân loại theo phân khúc thị trường","Thông tin đầy đủ từ sản xuất đến phân phối","Hỗ trợ tư vấn kinh doanh","Cập nhật dữ liệu theo mùa","Báo cáo thị trường đi kèm"]',
'[{"title":"Nhà cung cấp","items":[{"title":"Tên công ty","type":"Text"},{"title":"Địa chỉ xưởng/showroom","type":"Text"},{"title":"Số điện thoại","type":"Number"},{"title":"Email","type":"Email"},{"title":"Website","type":"Text"}]},{"title":"Sản phẩm","items":[{"title":"Loại sản phẩm","type":"Text"},{"title":"Phân khúc","type":"Text"},{"title":"Giá sỉ","type":"Number"}]},{"title":"Liên hệ kinh doanh","items":[{"title":"Người liên hệ","type":"Text"},{"title":"Zalo","type":"Number"},{"title":"Facebook","type":"Text"}]}]',
'Fashion Supply Vietnam',
'Chuyên gia ngành thời trang',
'Đơn vị hàng đầu về cung cấp data ngành thời trang Việt Nam. Chúng tôi có database khổng lồ về các nhà sản xuất, đại lý và nhà phân phối thời trang.',
'https://ui-avatars.com/api/?name=Fashion+Supply&background=FF6B6B&color=fff&size=150',
'{"website":"https://fashionsupply.vn","hotline":"19005678"}');

-- Sản phẩm 3: Gói 100 Data Ngành Điện Tử
INSERT INTO products (name, slug, short_description, description, price, sale_price, category_id, image, status, created_at, stock, record_count, data_size, data_type, data_format, data_source, reliability, benefits, data_structure, supplier_name, supplier_title, supplier_bio, supplier_avatar, supplier_social) VALUES
('Gói 100 Data Nguồn Hàng Ngành Điện Tử', 'goi-100-data-nguon-hang-nganh-dien-tu', 
'Gói data chuyên ngành điện tử - công nghệ. Bao gồm nhà cung cấp linh kiện, thiết bị điện tử, smartphone, laptop.',
'Gói data chuyên sâu ngành điện tử với 100 thông tin nhà cung cấp uy tín. Bao gồm: nhà sản xuất linh kiện, đại lý phân phối thiết bị điện tử, nhà cung cấp smartphone, laptop, phụ kiện công nghệ.',
1800000, 1290000, 1, 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600&h=400&fit=crop', 'active', NOW(), 100, 100, '18 KB', 'Nguồn hàng Ngành Điện Tử', 'Excel, CSV', 'Việt Nam, Trung Quốc', '92%',
'["Database ngành điện tử lớn nhất","Thông tin đối tác chính hãng","Hỗ trợ kỹ thuật chuyên sâu","Cập nhật công nghệ mới nhất","Tư vấn nguồn hàng nhập khẩu","Bảo hành dữ liệu"]',
'[{"title":"Nhà cung cấp","items":[{"title":"Tên công ty","type":"Text"},{"title":"Loại hình","type":"Text"},{"title":"Địa chỉ","type":"Text"},{"title":"Hotline","type":"Number"}]},{"title":"Sản phẩm cung cấp","items":[{"title":"Danh mục sản phẩm","type":"Text"},{"title":"Thương hiệu","type":"Text"},{"title":"Bảo hành","type":"Text"}]},{"title":"Hỗ trợ","items":[{"title":"Kỹ thuật","type":"Text"},{"title":"Tư vấn","type":"Text"},{"title":"Bảo hành","type":"Text"}]}]',
'TechSource Vietnam',
'Chuyên gia công nghệ',
'Đơn vị chuyên cung cấp data và tư vấn nguồn hàng điện tử công nghệ hàng đầu. Chúng tôi kết nối doanh nghiệp Việt Nam với các nhà cung cấp uy tín.',
'https://ui-avatars.com/api/?name=TechSource+VN&background=00D9A5&color=fff&size=150',
'{"website":"https://techsource.vn","hotline":"19008899"}');

-- Sản phẩm 4: Gói 100 Data Ngành Thực Phẩm
INSERT INTO products (name, slug, short_description, description, price, sale_price, category_id, image, status, created_at, stock, record_count, data_size, data_type, data_format, data_source, reliability, benefits, data_structure, supplier_name, supplier_title, supplier_bio, supplier_avatar, supplier_social) VALUES
('Gói 100 Data Nguồn Hàng Ngành Thực Phẩm', 'goi-100-data-nguon-hang-nganh-thuc-pham', 
'Gói data chuyên ngành thực phẩm - đồ uống. Bao gồm nhà cung cấp nguyên liệu, nhà sản xuất, đại lý phân phối thực phẩm.',
'Gói data chuyên ngành thực phẩm với 100 thông tin nhà cung cấp uy tín. Bao gồm: nhà cung cấp nguyên liệu thực phẩm, nhà sản xuất đồ uống, đại lý phân phối thực phẩm sạch, organic food.',
1400000, 890000, 1, 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=600&h=400&fit=crop', 'active', NOW(), 100, 100, '14 KB', 'Nguồn hàng Ngành Thực Phẩm', 'Excel, CSV', 'Việt Nam', '94%',
'["Data ngành thực phẩm an toàn","Kết nối nhà cung cấp uy tín","Thông tin vệ sinh an toàn thực phẩm","Hỗ trợ chứng nhận HACCP","Tư vấn nguồn nguyên liệu sạch","Cập nhật xu hướng tiêu dùng"]',
'[{"title":"Nhà cung cấp","items":[{"title":"Tên nhà cung cấp","type":"Text"},{"title":"Loại sản phẩm","type":"Text"},{"title":"Chứng nhận VSATTP","type":"Text"},{"title":"Địa chỉ","type":"Text"}]},{"title":"Sản phẩm","items":[{"title":"Nguyên liệu","type":"Text"},{"title":"Giá bán sỉ","type":"Number"},{"title":"Số lượng tồn kho","type":"Number"}]},{"title":"Liên hệ","items":[{"title":"Người đại diện","type":"Text"},{"title":"Điện thoại","type":"Number"},{"title":"Email","type":"Email"}]}]',
'FoodChain Vietnam',
'Chuỗi cung ứng thực phẩm',
'Chuyên cung cấp data và tư vấn chuỗi cung ứng thực phẩm an toàn. Chúng tôi kết nối các doanh nghiệp với nhà cung cấp nguyên liệu chất lượng cao.',
'https://ui-avatars.com/api/?name=FoodChain+VN&background=4CAF50&color=fff&size=150',
'{"website":"https://foodchain.vn","hotline":"19004567"}');

-- Sản phẩm 5: Gói 100 Data Ngành Vận Tải - Logistics
INSERT INTO products (name, slug, short_description, description, price, sale_price, category_id, image, status, created_at, stock, record_count, data_size, data_type, data_format, data_source, reliability, benefits, data_structure, supplier_name, supplier_title, supplier_bio, supplier_avatar, supplier_social) VALUES
('Gói 100 Data Nguồn Hàng Ngành Vận Tải', 'goi-100-data-nguon-hang-nganh-van-tai', 
'Gói data chuyên ngành vận tải - logistics. Bao gồm công ty vận chuyển, kho bãi, đơn vị giao hàng, nhà cung cấp dịch vụ logistics.',
'Gói data chuyên sâu ngành vận tải và logistics với 100 thông tin đối tác uy tín. Bao gồm: công ty vận chuyển, đơn vị kho bãi, dịch vụ giao hàng, nhà cung cấp thiết bị logistics.',
2000000, 1490000, 1, 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=600&h=400&fit=crop', 'active', NOW(), 100, 100, '20 KB', 'Nguồn hàng Ngành Vận Tải', 'Excel, CSV', 'Việt Nam', '96%',
'["Data ngành vận tải lớn nhất","Kết nối đối tác logistics uy tín","Thông tin đội xe, kho bãi","Hỗ trợ tối ưu vận chuyển","Báo giá cạnh tranh","Tư vấn giải pháp logistics"]',
'[{"title":"Đơn vị vận chuyển","items":[{"title":"Tên công ty","type":"Text"},{"title":"Loại hình vận chuyển","type":"Text"},{"title":"Phạm vi hoạt động","type":"Text"},{"title":"Số xe tải","type":"Number"}]},{"title":"Kho bãi","items":[{"title":"Địa chỉ kho","type":"Text"},{"title":"Diện tích","type":"Number"},{"title":"Dịch vụ kho bãi","type":"Text"}]},{"title":"Liên hệ","items":[{"title":"Người liên hệ","type":"Text"},{"title":"Hotline","type":"Number"},{"title":"Email","type":"Email"}]}]',
'LogiConnect Vietnam',
'Kết nối logistics',
'Đơn vị hàng đầu về cung cấp data và kết nối các đối tác trong ngành vận tải - logistics. Chúng tôi giúp doanh nghiệp tìm kiếm đối tác vận chuyển uy tín.',
'https://ui-avatars.com/api/?name=LogiConnect+VN&background=9C27B0&color=fff&size=150',
'{"website":"https://logiconnect.vn","hotline":"19007890"}');

-- ============================================================
-- Bước 4: INSERT đánh giá mẫu cho sản phẩm
-- ============================================================

INSERT INTO product_reviews (product_id, reviewer_name, rating, title, content, status) VALUES 
-- Reviews cho sản phẩm 1
(1, 'Anh Minh - Công ty vận tải', 5, 'Data rất chính xác', 'Data nguồn hàng rất chính xác, giúp công ty tôi tìm được nhiều đối tác mới. Sẽ mua thêm lần nữa.', 'approved'),
(1, 'Chị Hương - Logistics Co', 4, 'Dịch vụ tốt', 'Dữ liệu đầy đủ, nhân viên hỗ trợ nhiệt tình. Mong có thêm nhiều data về ngành vận chuyển.', 'approved'),
(1, 'Anh Tuấn - Shipper VN', 5, 'Rất hài lòng', 'Đây là lần thứ 3 tôi mua data ở đây. Chất lượng luôn ổn định, giá cả hợp lý.', 'approved'),

-- Reviews cho sản phẩm 2
(2, 'Chị Lan - Shop thời trang', 5, 'Data ngành quần áo rất tốt', 'Tôi kinh doanh quần áo online, mua data này để tìm nguồn hàng. Rất hữu ích!', 'approved'),
(2, 'Anh Hùng - Thời trang Nam', 4, 'Hỗ trợ nhiệt tình', 'Data đầy đủ thông tin, nhân viên tư vấn rất nhiệt tình. Sẽ giới thiệu cho bạn bè.', 'approved'),

-- Reviews cho sản phẩm 3
(3, 'Anh Sơn - Công ty điện tử', 5, 'Data điện tử chất lượng', 'Data ngành điện tử rất chính xác, giúp tôi tìm được nguồn hàng smartphone chính hãng.', 'approved'),
(3, 'Chị Mai - Cửa hàng laptop', 5, 'Rất hài lòng', 'Mua data để tìm nhà cung cấp laptop. Kết quả rất tốt, đã ký hợp đồng với 2 nhà cung cấp mới.', 'approved'),

-- Reviews cho sản phẩm 4
(4, 'Anh Đức - Nhà hàng', 4, 'Data thực phẩm hữu ích', 'Data giúp tôi tìm được nguồn rau sạch organic. Rất hài lòng với chất lượng.', 'approved'),
(4, 'Chị Thảo - Siêu thị mini', 5, 'Tuyệt vời', 'Data thực phẩm rất đầy đủ, thông tin vệ sinh an toàn thực phẩm đều có. Sẽ mua thêm.', 'approved'),

-- Reviews cho sản phẩm 5
(5, 'Anh Bảo - Công ty vận tải', 5, 'Data logistics tốt nhất', 'Tôi làm trong ngành logistics, đây là data ngành vận tải tốt nhất tôi từng mua.', 'approved'),
(5, 'Chị Linh - Kho bãi', 5, 'Rất chuyên nghiệp', 'Data giúp tìm được đối tác kho bãi uy tín. Cảm ơn LogiConnect đã hỗ trợ nhiệt tình.', 'approved');
