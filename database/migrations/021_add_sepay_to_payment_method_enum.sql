-- Migration: Add sepay to payment_method ENUM
-- Created: 2026-03-07
-- Description: Add 'sepay' to the ENUM list for payment_method column in orders table

ALTER TABLE orders 
CHANGE COLUMN payment_method payment_method ENUM('bank_transfer', 'momo', 'vnpay', 'zalopay', 'cash', 'credit_card', 'sepay') NULL;
