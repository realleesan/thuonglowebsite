-- Migration: Insert default featured products section data
-- Created: 2026-05-19
-- Description: Insert default data for featured products section

INSERT INTO featured_products_section (title, is_active) 
VALUES (
    '<h2 class="section-title">Sản phẩm <span class="highlight">Nổi bật</span></h2>',
    TRUE
)
ON DUPLICATE KEY UPDATE 
    title = VALUES(title),
    is_active = VALUES(is_active);
