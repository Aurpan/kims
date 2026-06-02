-- Add exchange order support
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS exchange_for_order_id INT NULL AFTER id,
    ADD INDEX IF NOT EXISTS idx_exchange_for_order_id (exchange_for_order_id);

ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS is_return TINYINT(1) NOT NULL DEFAULT 0 AFTER line_total;
