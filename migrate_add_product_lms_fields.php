-- Add new fields to products table for LMS features
-- This migration adds columns for course-specific features

ALTER TABLE products 
ADD COLUMN IF NOT EXISTS what_youll_learn JSON NULL,
ADD COLUMN IF NOT EXISTS curriculum JSON NULL,
ADD COLUMN IF NOT EXISTS instructor_id INT NULL,
ADD COLUMN IF NOT EXISTS instructor_name VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS instructor_title VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS instructor_bio TEXT NULL,
ADD COLUMN IF NOT EXISTS instructor_avatar VARCHAR(500) NULL,
ADD COLUMN IF NOT EXISTS instructor_social JSON NULL,
ADD COLUMN IF NOT EXISTS total_duration VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS total_lessons INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS total_sections INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS skill_level VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS language VARCHAR(100) DEFAULT 'Vietnamese',
ADD COLUMN IF NOT EXISTS certificate TINYINT(1) DEFAULT 1;

-- Create product_reviews table
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NULL,
    reviewer_name VARCHAR(255) NULL,
    reviewer_email VARCHAR(255) NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(500) NULL,
    content TEXT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    INDEX idx_status (status),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create instructors table
CREATE TABLE IF NOT EXISTS instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NULL,
    bio TEXT NULL,
    avatar VARCHAR(500) NULL,
    social JSON NULL,
    students_count INT DEFAULT 0,
    courses_count INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    reviews_count INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample instructor data
INSERT INTO instructors (name, title, bio, avatar, social, students_count, courses_count, rating, reviews_count) VALUES 
('Nguyễn Văn A', 'Chuyên gia đào tạo', 'Với hơn 10 năm kinh nghiệm trong lĩnh vực, tôi đã đào tạo hàng nghìn học viên thành công. Phương pháp giảng dạy thực tế, dễ hiểu và áp dụng được ngay vào công việc.', 'https://ui-avatars.com/api/?name=Nguyen+Van+A&background=356DF1&color=fff&size=150', '{"facebook":"#","twitter":"#","youtube":"#","linkedin":"#"}', 1250, 8, 4.80, 156),
('Trần Thị B', 'Senior Developer', 'Chuyên gia phát triển web với hơn 8 năm kinh nghiệm. Đã làm việc với nhiều dự án lớn trong và ngoài nước.', 'https://ui-avatars.com/api/?name=Tran+Thi+B&background=10B981&color=fff&size=150', '{"facebook":"#","twitter":"#","youtube":"#"}', 980, 5, 4.90, 89),
('Lê Minh C', 'Product Manager', 'Chuyên gia quản lý sản phẩm với kinh nghiệm quản lý nhiều dự án công nghệ thành công.', 'https://ui-avatars.com/api/?name=Le+Minh+C&background=F59E0B&color=fff&size=150', '{"facebook":"#","linkedin":"#"}', 756, 4, 4.75, 67);

-- Insert sample product reviews
INSERT INTO product_reviews (product_id, reviewer_name, rating, title, content, status) VALUES 
(1, 'Nguyễn Minh', 5, 'Khóa học rất bổ ích', 'Khóa học rất bổ ích, giảng viên nhiệt tình và có kiến thức chuyên môn cao. Tôi đã học được rất nhiều điều mới và áp dụng được vào công việc ngay lập tức.', 'approved'),
(1, 'Lê Hải', 4, 'Khóa học chất lượng tốt', 'Khóa học chất lượng tốt, nội dung chi tiết và dễ hiểu. Tuy nhiên tôi mong muốn có thêm nhiều bài tập thực hành hơn.', 'approved'),
(1, 'Phạm Thị D', 5, 'Tuyệt vời!', 'Đây là khóa học tốt nhất mà tôi đã từng học. Nội dung rất thực tế và giảng viên giải thích rất dễ hiểu.', 'approved'),
(1, 'Trần Văn E', 3, 'Khóa học ổn', 'Nội dung cơ bản ổn, nhưng cần cập nhật thêm các xu hướng mới.', 'approved');

-- Update existing products with sample what_youll_learn and curriculum data
UPDATE products SET 
    what_youll_learn = '["Hiểu rõ về sản phẩm và cách sử dụng","Kỹ năng thực hành chuyên sâu","Phát triển tư duy sáng tạo","Giải quyết vấn đề thực tế","Xây dựng nền tảng vững chắc","Hỗ trợ và tư vấn sau khóa học"]',
    curriculum = '[{"title":"Phần 1: Giới thiệu tổng quan","lessons":[{"title":"Giới thiệu khóa học","duration":"5:00"},{"title":"Cách sử dụng tài liệu","duration":"8:30"},{"title":"Yêu cầu và chuẩn bị","duration":"3:15"}]},{"title":"Phần 2: Nền tảng kiến thức","lessons":[{"title":"Khái niệm cơ bản","duration":"15:20"},{"title":"Nguyên lý hoạt động","duration":"12:45"},{"title":"Các thành phần chính","duration":"18:00"},{"title":"Bài tập thực hành","duration":"10:30"}]},{"title":"Phần 3: Thực hành nâng cao","lessons":[{"title":"Kỹ thuật chuyên sâu","duration":"20:15"},{"title":"Xử lý tình huống thực tế","duration":"25:00"},{"title":"Dự án thực tế","duration":"45:00"}]},{"title":"Phần 4: Tổng kết và đánh giá","lessons":[{"title":"Tổng kết kiến thức","duration":"10:00"},{"title":"Bài kiểm tra cuối khóa","duration":"30:00"},{"title":"Hướng dẫn sau khóa học","duration":"8:00"}]}]',
    instructor_id = 1,
    instructor_name = 'Nguyễn Văn A',
    instructor_title = 'Chuyên gia đào tạo',
    total_duration = '3h 17p',
    total_lessons = 14,
    total_sections = 4,
    skill_level = 'Tất cả cấp độ',
    language = 'Tiếng Việt',
    certificate = 1
WHERE status = 'active';
