ALTER TABLE orders
  MODIFY COLUMN delivery_status
    ENUM('pending','package_ready','courier_pickup','personal_pickup','in_transit','delivered','on_hold','cancelled','returned')
    NOT NULL DEFAULT 'pending';
