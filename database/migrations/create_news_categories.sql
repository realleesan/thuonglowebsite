-- Create news categories based on dropdown menu
-- Insert categories into categories table with type = 'news'
-- Run add_type_to_categories.sql first if you get error about 'type' column

-- Insert news categories (will skip if already exists based on unique slug)
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `type`, `parent_id`, `status`, `created_at`, `updated_at`)
VALUES
('Thương mại XB', 'thuong-mai-xb', 'Tin tức về thương mại xuyên biên giới', 'news', NULL, 'active', NOW(), NOW()),
('Chính sách hải quan', 'chinh-sach-hai-quan', 'Tin tức về chính sách hải quan mới nhất', 'news', NULL, 'active', NOW(), NOW()),
('Thị trường Trung Quốc', 'thi-truong-trung-quoc', 'Tin tức về thị trường Trung Quốc', 'news', NULL, 'active', NOW(), NOW()),
('Kinh nghiệm kinh doanh', 'kinh-nghiem-kinh-doanh', 'Chia sẻ kinh nghiệm kinh doanh', 'news', NULL, 'active', NOW(), NOW());

-- Get category IDs for assignment
SET @cat_thuong_mai_xb = (SELECT id FROM categories WHERE slug = 'thuong-mai-xb' AND type = 'news' LIMIT 1);
SET @cat_chinh_sach = (SELECT id FROM categories WHERE slug = 'chinh-sach-hai-quan' AND type = 'news' LIMIT 1);
SET @cat_thi_truong = (SELECT id FROM categories WHERE slug = 'thi-truong-trung-quoc' AND type = 'news' LIMIT 1);
SET @cat_kinh_nghiem = (SELECT id FROM categories WHERE slug = 'kinh-nghiem-kinh-doanh' AND type = 'news' LIMIT 1);

-- Assign categories to existing news articles based on their content/tags
-- News #1: Data nguồn hàng -> Thương mại XB
UPDATE `news` SET `category_id` = @cat_thuong_mai_xb WHERE `id` = 1;

-- News #2: Chính sách hải quan -> Chính sách hải quan
UPDATE `news` SET `category_id` = @cat_chinh_sach WHERE `id` = 2;

-- News #3: Thị trường Trung Quốc -> Thị trường Trung Quốc
UPDATE `news` SET `category_id` = @cat_thi_truong WHERE `id` = 3;

-- News #4: Kinh nghiệm kinh doanh -> Kinh nghiệm kinh doanh
UPDATE `news` SET `category_id` = @cat_kinh_nghiem WHERE `id` = 4;

-- Display results
SELECT 'News categories created successfully!' as message;
SELECT id, name, slug, type FROM categories WHERE type = 'news';
SELECT id, title, category_id FROM news WHERE id <= 4;
