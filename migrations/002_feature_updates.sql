-- Feature Updates Migration
-- Adds new fields for payment method, payment status, delivery status, and pickup person name

ALTER TABLE orders
  ADD COLUMN payment_method ENUM('cod','bkash','bank') NOT NULL DEFAULT 'cod' AFTER total_amount,
  ADD COLUMN payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid' AFTER payment_method,
  ADD COLUMN delivery_status ENUM('pending','courier_pickup','personal_pickup','delivered','on_hold','cancelled') NOT NULL DEFAULT 'pending' AFTER payment_status,
  ADD COLUMN pickup_person_name VARCHAR(255) NULL AFTER delivery_status;
