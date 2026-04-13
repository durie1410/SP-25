# 🔧 Hướng dẫn sửa lỗi: Column not found 'ngay_sinh'

## Vấn đề
Khi cập nhật thông tin tài khoản, xuất hiện lỗi:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'ngay_sinh' in 'field list'
```

## ✅ Giải pháp nhanh nhất

### Cách 1: Truy cập trang sửa lỗi tự động (KHUYẾN NGHỊ)

1. Mở trình duyệt và truy cập:
   ```
   http://quanlythuviennn.test/fix-users-table.html
   ```
   hoặc
   ```
   http://localhost/quanlythuviennn/public/fix-users-table.html
   ```

2. Nhấn nút **"🚀 Chạy sửa lỗi ngay"**

3. Đợi vài giây, bạn sẽ thấy thông báo thành công

4. Quay lại trang cập nhật thông tin tài khoản và thử lại

### Cách 2: Truy cập route trực tiếp

Mở trình duyệt và truy cập:
```
http://quanlythuviennn.test/fix-users-table-columns
```

Bạn sẽ thấy kết quả dưới dạng JSON.

### Cách 3: Chạy Artisan Command

Mở terminal/command prompt và chạy:
```bash
cd d:\laragon\www\quanlythuviennn
php artisan users:fix-columns
```

### Cách 4: Chạy script PHP

Mở terminal/command prompt và chạy:
```bash
cd d:\laragon\www\quanlythuviennn
php fix_users_table_now.php
```

### Cách 5: Chạy SQL trực tiếp trong phpMyAdmin

1. Mở phpMyAdmin
2. Chọn database `quanlythuviennn`
3. Vào tab SQL
4. Chạy các lệnh sau:

```sql
-- Thêm cột ngay_sinh
ALTER TABLE `users` 
ADD COLUMN `ngay_sinh` DATE NULL AFTER `so_cccd`;

-- Thêm cột gioi_tinh
ALTER TABLE `users` 
ADD COLUMN `gioi_tinh` ENUM('Nam', 'Nu', 'Khac') NULL AFTER `ngay_sinh`;
```

## Kiểm tra kết quả

Sau khi chạy một trong các cách trên:

1. Mở lại trang: `http://quanlythuviennn.test/account`
2. Điền đầy đủ thông tin:
   - Số điện thoại
   - Địa chỉ
   - Tỉnh/Thành phố (chọn từ dropdown)
   - Quận/Huyện (chọn từ dropdown)
   - Ngày sinh
   - Giới tính
3. Nhấn nút **"Cập nhật"**
4. Nếu không còn lỗi, đã thành công! ✅

## Lưu ý

- Các cột `ngay_sinh` và `gioi_tinh` là **nullable** (có thể để trống)
- Giá trị `gioi_tinh` chỉ chấp nhận: `'Nam'`, `'Nu'`, hoặc `'Khac'`
- Sau khi thêm cột, dữ liệu cũ sẽ không bị mất
- Nếu đã chạy một cách và thành công, không cần chạy các cách khác

## Tính năng mới

Sau khi sửa lỗi, bạn có thể sử dụng tính năng **tự động điền địa chỉ**:
- Nhập địa chỉ đầy đủ, hệ thống sẽ tự động nhận diện Tỉnh/Thành phố và Quận/Huyện
- Hoặc chọn trực tiếp từ dropdown
- **Không cần Google Maps API key!**

