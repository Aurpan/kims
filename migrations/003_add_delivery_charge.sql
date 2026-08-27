-- ============================================
-- Persist delivery charge on orders
-- ============================================
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS delivery_charge DECIMAL(10,2) NOT NULL DEFAULT 80 AFTER total_amount;
