-- ============================================================
-- Script tạo đánh giá mẫu cho sản phẩm
-- CHẠY FILE NÀY SAU KHI products đã được tạo
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Xóa reviews cũ
TRUNCATE TABLE product_reviews;

-- Insert reviews (sử dụng tiếng Việt không dấu để tránh lỗi charset)
INSERT INTO product_reviews (product_id, reviewer_name, rating, title, content, status) VALUES 
-- Reviews cho sản phẩm 1
(1, 'Anh Minh - Cong ty van tai', 5, 'Data rat chinh xac', 'Data nguon hang rat chinh xac, giup cong ty toi tim duoc nhieu doi tac moi. Se mua them lan nua.', 'approved'),
(1, 'Chi Huong - Logistics Co', 4, 'Dich vu tot', 'Du lieu day du, nhan vien ho tro nhiet tinh. Mong co them nhieu data ve nganh van chuyen.', 'approved'),
(1, 'Anh Tuan - Shipper VN', 5, 'Rat hai long', 'Day la lan thu 3 toi mua data o day. Chat luong luon on dinh, gia ca hop ly.', 'approved'),

-- Reviews cho sản phẩm 2
(2, 'Chi Lan - Shop thoi trang', 5, 'Data nganh quan ao rat tot', 'Toi kinh doanh quan ao online, mua data nay de tim nguon hang. Rat hieu ich!', 'approved'),
(2, 'Anh Hung - Thoi trang Nam', 4, 'Ho tro nhiet tinh', 'Data day du thong tin, nhan vien tu van rat nhiet tinh. Se gioi thieu cho ban be.', 'approved'),

-- Reviews cho sản phẩm 3
(3, 'Anh Son - Cong ty dien tu', 5, 'Data dien tu chat luong', 'Data nganh dien tu rat chinh xac, giup toi tim duoc nguon hang smartphone chinh hang.', 'approved'),
(3, 'Chi Mai - Cua hang laptop', 5, 'Rat hai long', 'Mua data de tim nha cung cap laptop. Ket qua rat tot, da ky hop dong voi 2 nha cung cap moi.', 'approved'),

-- Reviews cho sản phẩm 4
(4, 'Anh Duc - Nha hang', 4, 'Data thuc pham huu ich', 'Data giup toi tim duoc nguon rau sach organic. Rat hai long voi chat luong.', 'approved'),
(4, 'Chi Thao - Sieu thi mini', 5, 'Tuyet voi', 'Data thuc pham rat day du, thong tin ve sinh an toan thuc pham deu co. Se mua them.', 'approved'),

-- Reviews cho sản phẩm 5
(5, 'Anh Bao - Cong ty van tai', 5, 'Data logistics tot nhat', 'Toi lam trong nganh logistics, day la data nganh van tai tot nhat toi tung mua.', 'approved'),
(5, 'Chi Linh - Kho bai', 5, 'Rat chuyen nghiep', 'Data giup tim duoc doi tac kho bai uy tin. Cam on LogiConnect da ho tro nhiet tinh.', 'approved');

SELECT 'Tao danh gia thanh cong! So luong: ' AS message, COUNT(*) AS total FROM product_reviews;
