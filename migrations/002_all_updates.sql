-- Consolidated Migration File
-- Contains all feature updates and schema modifications
-- This file consolidates migrations 002-008

-- ============================================
-- Add payment and delivery fields to orders
-- ============================================
ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS payment_method ENUM('cod','bkash','bank') NOT NULL DEFAULT 'cod' AFTER total_amount,
  ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid' AFTER payment_method;

-- Add delivery outcome timestamps to orders table
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS cancelled_at TIMESTAMP NULL AFTER delivered_at,
    ADD COLUMN IF NOT EXISTS returned_at  TIMESTAMP NULL AFTER cancelled_at;

-- Add soft delete support to orders table
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS is_deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER updated_at,
    ADD COLUMN IF NOT EXISTS has_stock_issue TINYINT(1) NOT NULL DEFAULT 0 AFTER is_deleted;

CREATE INDEX IF NOT EXISTS idx_is_deleted ON orders (is_deleted);
CREATE INDEX IF NOT EXISTS idx_has_stock_issue ON orders (has_stock_issue);

-- ============================================
-- Update delivery_status ENUM for all features
-- ============================================
ALTER TABLE orders
  MODIFY COLUMN IF EXISTS delivery_status
    ENUM('pending','waiting_for_print','package_ready','courier_pickup','personal_pickup','in_transit','delivered','on_hold','cancelled','returned')
    NOT NULL DEFAULT 'pending';

-- If delivery_status doesn't exist yet, add it
ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS delivery_status ENUM('pending','waiting_for_print','package_ready','courier_pickup','personal_pickup','in_transit','delivered','on_hold','cancelled','returned') NOT NULL DEFAULT 'pending' AFTER payment_status,
  ADD COLUMN IF NOT EXISTS pickup_person_name VARCHAR(255) NULL AFTER delivery_status;

-- ============================================
-- Drop legacy status column if it exists
-- ============================================
ALTER TABLE orders DROP COLUMN IF EXISTS status;

-- ============================================
-- Add sourcing price to products
-- ============================================
ALTER TABLE products ADD COLUMN IF NOT EXISTS sourcing_price INT NULL AFTER base_price;

-- ============================================
-- Update product_variants (remove color, update reorder_point)
-- ============================================
-- First, drop the old unique constraint if it exists
ALTER TABLE product_variants
    DROP INDEX IF EXISTS unique_variant;

-- Remove color column if it exists
ALTER TABLE product_variants
    DROP COLUMN IF EXISTS color;

-- Update reorder_point default and add new unique constraint
ALTER TABLE product_variants
    MODIFY COLUMN reorder_point INT NOT NULL DEFAULT 2;

-- Add new unique constraint without color
ALTER TABLE product_variants
    ADD UNIQUE KEY IF NOT EXISTS unique_variant (product_id, size);

-- ============================================
-- Add exchange order support
-- ============================================
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS exchange_for_order_id INT NULL AFTER id,
    ADD INDEX IF NOT EXISTS idx_exchange_for_order_id (exchange_for_order_id);

-- ============================================
-- Persist delivery charge on orders
-- ============================================
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS delivery_charge DECIMAL(10,2) NOT NULL DEFAULT 80 AFTER total_amount;

-- ============================================
-- Update order_items table
-- ============================================
-- Add is_return column
ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS is_return TINYINT(1) NOT NULL DEFAULT 0 AFTER line_total;

-- Track whether stock was actually deducted for each order item
ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS stock_deducted TINYINT(1) NOT NULL DEFAULT 1 AFTER is_return;
