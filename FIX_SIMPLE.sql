-- SCRIPT ĐƠN GIẢN - Chạy trực tiếp trong phpMyAdmin
-- Copy và paste từng lệnh một vào tab SQL

-- Bước 1: Thêm cột ngay_sinh
ALTER TABLE `users` 
ADD COLUMN `ngay_sinh` DATE NULL 
AFTER `so_cccd`;

-- Bước 2: Thêm cột gioi_tinh  
ALTER TABLE `users` 
ADD COLUMN `gioi_tinh` ENUM('Nam', 'Nu', 'Khac') NULL 
AFTER `ngay_sinh`;

-- Nếu lỗi "Unknown column 'so_cccd'", chạy lệnh này thay thế:
-- ALTER TABLE `users` ADD COLUMN `ngay_sinh` DATE NULL AFTER `address`;
-- ALTER TABLE `users` ADD COLUMN `gioi_tinh` ENUM('Nam', 'Nu', 'Khac') NULL AFTER `ngay_sinh`;
