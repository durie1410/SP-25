# Hướng dẫn sửa lỗi: Column not found 'ngay_sinh' và 'gioi_tinh'

## Vấn đề
Lỗi: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'ngay_sinh' in 'field list'`

## Giải pháp

### Cách 1: Chạy Migration (Khuyến nghị)
```bash
php artisan migrate
```

Hoặc chạy migration cụ thể:
```bash
php artisan migrate --path=database/migrations/2025_12_16_120000_ensure_user_profile_columns.php
```

### Cách 2: Chạy SQL trực tiếp trong database

Mở phpMyAdmin hoặc MySQL client và chạy các lệnh sau:

```sql
-- Kiểm tra xem cột đã tồn tại chưa
SHOW COLUMNS FROM `users` LIKE 'ngay_sinh';
SHOW COLUMNS FROM `users` LIKE 'gioi_tinh';

-- Thêm cột ngay_sinh nếu chưa có
ALTER TABLE `users` 
ADD COLUMN `ngay_sinh` DATE NULL AFTER `so_cccd`;

-- Thêm cột gioi_tinh nếu chưa có
ALTER TABLE `users` 
ADD COLUMN `gioi_tinh` ENUM('Nam', 'Nu', 'Khac') NULL AFTER `ngay_sinh`;
```

### Cách 3: Sử dụng script PHP

Chạy file script:
```bash
php add_columns_now.php
```

## Kiểm tra kết quả

Sau khi chạy migration hoặc SQL, kiểm tra lại bằng cách:

1. Mở trang cập nhật thông tin tài khoản
2. Điền đầy đủ thông tin và nhấn "Cập nhật"
3. Nếu không còn lỗi, đã thành công!

## Lưu ý

- Các cột `ngay_sinh` và `gioi_tinh` là nullable (có thể để trống)
- Giá trị `gioi_tinh` chỉ chấp nhận: 'Nam', 'Nu', hoặc 'Khac'
- Sau khi thêm cột, dữ liệu cũ sẽ không bị mất

