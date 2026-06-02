-- Add soft delete support to orders table
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS is_deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER updated_at;

CREATE INDEX IF NOT EXISTS idx_is_deleted ON orders (is_deleted);
