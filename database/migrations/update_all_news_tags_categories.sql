-- Update all news with tags and correct category_id
-- This ensures all news have tags for filter testing

-- Get category IDs
SET @cat_thuong_mai_xb = (SELECT id FROM categories WHERE slug = 'thuong-mai-xb' AND type = 'news' LIMIT 1);
SET @cat_chinh_sach = (SELECT id FROM categories WHERE slug = 'chinh-sach-hai-quan' AND type = 'news' LIMIT 1);
SET @cat_thi_truong = (SELECT id FROM categories WHERE slug = 'thi-truong-trung-quoc' AND type = 'news' LIMIT 1);
SET @cat_kinh_nghiem = (SELECT id FROM categories WHERE slug = 'kinh-nghiem-kinh-doanh' AND type = 'news' LIMIT 1);

-- Update all news to have proper category_id and tags
-- Distribute news across all 4 categories

-- Category: Thương mại XB (IDs: 1, 5, 9, 13, 17, 21, 25, 29)
UPDATE `news` SET 
    `category_id` = @cat_thuong_mai_xb,
    `tags` = 'data-nguon-hang,van-chuyen,trung-quoc'
WHERE `id` IN (1, 5, 9, 13, 17, 21, 25, 29);

-- Category: Chính sách hải quan (IDs: 2, 6, 10, 14, 18, 22, 26, 30)
UPDATE `news` SET 
    `category_id` = @cat_chinh_sach,
    `tags` = 'hai-quan,chinh-sach,nhap-khau'
WHERE `id` IN (2, 6, 10, 14, 18, 22, 26, 30);

-- Category: Thị trường Trung Quốc (IDs: 3, 7, 11, 15, 19, 23, 27, 31)
UPDATE `news` SET 
    `category_id` = @cat_thi_truong,
    `tags` = 'trung-quoc,thi-truong,kinh-doanh'
WHERE `id` IN (3, 7, 11, 15, 19, 23, 27, 31);

-- Category: Kinh nghiệm kinh doanh (IDs: 4, 8, 12, 16, 20, 24, 28, 32)
UPDATE `news` SET 
    `category_id` = @cat_kinh_nghiem,
    `tags` = 'kinh-nghiem,kinh-doanh,xuat-khau'
WHERE `id` IN (4, 8, 12, 16, 20, 24, 28, 32);

-- For any remaining news without category, assign to Thương mại XB
UPDATE `news` SET 
    `category_id` = @cat_thuong_mai_xb,
    `tags` = 'data-nguon-hang,trung-quoc'
WHERE `category_id` IS NULL OR `category_id` = 0;

-- Display results
SELECT 'All news updated with categories and tags!' AS message;
SELECT 
    c.name AS category_name,
    COUNT(n.id) AS news_count
FROM categories c
LEFT JOIN news n ON c.id = n.category_id
WHERE c.type = 'news'
GROUP BY c.id, c.name
ORDER BY c.id;

-- Show sample of updated news
SELECT id, title, category_id, tags FROM news ORDER BY id LIMIT 10;
