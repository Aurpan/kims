-- Remove the legacy status column; delivery_status is now the single status field
ALTER TABLE orders DROP COLUMN status;
