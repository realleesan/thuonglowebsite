-- Migration: Add brand_id to products table
-- Created: 2026-05-04
-- Description: Add brand reference to products

ALTER TABLE products
ADD COLUMN brand_id INT NULL AFTER category_id,
ADD INDEX idx_brand_id (brand_id),
ADD CONSTRAINT fk_products_brand
    FOREIGN KEY (brand_id) REFERENCES brands(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;
