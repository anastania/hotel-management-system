-- Add payment_id and payment_status columns to reservations table
ALTER TABLE reservations
ADD COLUMN payment_id VARCHAR(255) NULL,
ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending',
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
