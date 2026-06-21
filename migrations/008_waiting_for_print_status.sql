-- Add 'waiting_for_print' to delivery_status ENUM
ALTER TABLE orders
  MODIFY COLUMN delivery_status
    ENUM('pending','waiting_for_print','package_ready','courier_pickup','personal_pickup','in_transit','delivered','on_hold','cancelled','returned')
    NOT NULL DEFAULT 'pending';
