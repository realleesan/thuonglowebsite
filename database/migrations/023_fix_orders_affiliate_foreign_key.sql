-- Migration: Fix orders.affiliate_id foreign key
-- Created: 2026-04-10
-- Description: Change affiliate_id foreign key from users(id) to affiliates(id)

-- Drop existing foreign key constraint
ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_2;

-- Add correct foreign key constraint
ALTER TABLE orders 
ADD CONSTRAINT fk_orders_affiliate_id 
FOREIGN KEY (affiliate_id) REFERENCES affiliates(id) ON DELETE SET NULL;
