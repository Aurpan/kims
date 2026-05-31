-- Remove color column from product_variants and update reorder_point default
ALTER TABLE product_variants
    DROP INDEX unique_variant,
    DROP COLUMN color,
    MODIFY COLUMN reorder_point INT NOT NULL DEFAULT 2,
    ADD UNIQUE KEY unique_variant (product_id, size);
