-- Add delivery outcome timestamps to orders table
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS cancelled_at TIMESTAMP NULL AFTER delivered_at,
    ADD COLUMN IF NOT EXISTS returned_at  TIMESTAMP NULL AFTER cancelled_at;
