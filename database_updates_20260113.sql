-- Database updates for Library Management System
-- Date: 2026-01-13
-- Purpose: Fix refund issues for failed deliveries

-- 1. Add 'refunded' to borrow_payments.payment_status ENUM
-- This fixes the "Data truncated for column 'payment_status'" error
ALTER TABLE borrow_payments MODIFY COLUMN payment_status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending';

-- 2. Ensure all relevant columns exist in borrows table for refund tracking
-- (These should already exist if using latest migrations, but here they are for reference)
-- ALTER TABLE borrows ADD COLUMN phi_hong_sach DECIMAL(10, 2) DEFAULT 0;
-- ALTER TABLE borrows ADD COLUMN tien_coc_hoan_tra DECIMAL(10, 2) DEFAULT 0;
-- ALTER TABLE borrows ADD COLUMN ngay_that_bai_giao_hang TIMESTAMP NULL;

-- 3. Update existing incorrect shipping fees if necessary
-- (Optional: based on previous task where shipping fee was missing)
UPDATE borrows SET tien_ship = 20000 WHERE (tien_ship IS NULL OR tien_ship = 0) AND trang_thai != 'Huy';
