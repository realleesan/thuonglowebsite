-- Migration: Add display fields to categories table
-- Created: 2026-04-19
-- Description: Add show_in_menu, featured, show_in_filter fields to categories

ALTER TABLE categories
ADD COLUMN show_in_menu TINYINT(1) NOT NULL DEFAULT 1 AFTER sort_order,
ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0 AFTER show_in_menu,
ADD COLUMN show_in_filter TINYINT(1) NOT NULL DEFAULT 1 AFTER featured,
ADD INDEX idx_show_in_menu (show_in_menu),
ADD INDEX idx_featured (featured),
ADD INDEX idx_show_in_filter (show_in_filter);
