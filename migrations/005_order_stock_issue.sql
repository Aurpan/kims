-- Track stock availability issues on orders
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS has_stock_issue TINYINT(1) NOT NULL DEFAULT 0 AFTER is_deleted,
    ADD INDEX IF NOT EXISTS idx_has_stock_issue (has_stock_issue);

-- Track whether stock was actually deducted for each order item
ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS stock_deducted TINYINT(1) NOT NULL DEFAULT 1 AFTER is_return;
