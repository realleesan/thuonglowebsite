-- Migration: Insert initial hero section data
-- Created: 2026-05-13
-- Description: Insert current hero section data from home.php

-- First, clear any existing data to avoid conflicts
DELETE FROM hero_buttons WHERE hero_section_id IN (SELECT id FROM hero_sections WHERE title_main = 'Nền tảng data nguồn hàng và dịch vụ');
DELETE FROM hero_sections WHERE title_main = 'Nền tảng data nguồn hàng và dịch vụ';

-- Insert main hero section (let database auto-generate ID)
INSERT INTO hero_sections (
    title_main,
    title_highlight,
    subtitle,
    background_color,
    text_color,
    highlight_color,
    font_family,
    title_font_size,
    subtitle_font_size,
    image_url,
    image_alt,
    is_active,
    created_at,
    updated_at
) VALUES (
    'Nền tảng data nguồn hàng và dịch vụ',
    'Thương mại xuyên biên giới',
    'ThuongLo là nền tảng hàng đầu cung cấp data nguồn hàng chất lượng, dịch vụ vận chuyển chính ngạch và hỗ trợ toàn diện cho các doanh nghiệp muốn phát triển thương mại xuyên biên giới.',
    '#ffffff',
    '#333333',
    '#356DF1',
    'Arial, sans-serif',
    '48px',
    '18px',
    'home/home-banner-final.png',
    'ThuongLo - Nền tảng thương mại xuyên biên giới',
    1,
    NOW(),
    NOW()
);

-- Insert hero section buttons using a variable approach
SET @hero_section_id = LAST_INSERT_ID();

INSERT INTO hero_buttons (
    hero_section_id,
    button_text,
    button_url,
    button_style,
    background_color,
    text_color,
    sort_order,
    is_active,
    created_at,
    updated_at
) VALUES 
(@hero_section_id, 'Đăng ký miễn phí', '?page=register', 'primary', '#356DF1', '#ffffff', 1, 1, NOW(), NOW()),
(@hero_section_id, 'Xem sản phẩm', '?page=products', 'secondary', '#6c757d', '#ffffff', 2, 1, NOW(), NOW());
