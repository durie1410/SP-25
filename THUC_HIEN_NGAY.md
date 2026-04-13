# 🔧 SỬA LỖI NGAY - Thêm cột xác nhận khách hàng

## ⚠️ LỖI HIỆN TẠI
Bạn đang gặp lỗi: `Unknown column 'customer_confirmed_delivery' in 'field list'`

## ✅ GIẢI PHÁP NHANH NHẤT

### Cách 1: Chạy SQL trực tiếp trong phpMyAdmin (KHUYẾN NGHỊ)

1. Mở phpMyAdmin (thường là http://localhost/phpmyadmin)
2. Chọn database của bạn
3. Vào tab "SQL"
4. Copy và chạy các lệnh sau:

```sql
ALTER TABLE borrows 
ADD COLUMN customer_confirmed_delivery TINYINT(1) NOT NULL DEFAULT 0 
COMMENT 'Khách hàng đã xác nhận nhận sách';

ALTER TABLE borrows 
ADD COLUMN customer_confirmed_delivery_at TIMESTAMP NULL DEFAULT NULL 
COMMENT 'Thời gian khách hàng xác nhận nhận sách';
```

5. Click "Go" hoặc nhấn Ctrl+Enter

### Cách 2: Chạy file SQL

Đã tạo file `FIX_NOW.sql` trong thư mục gốc. Bạn có thể:
1. Mở file `FIX_NOW.sql`
2. Copy nội dung
3. Chạy trong phpMyAdmin hoặc MySQL client

### Cách 3: Sử dụng Artisan Tinker

```bash
php artisan tinker
```

Sau đó chạy:

```php
DB::statement("ALTER TABLE borrows ADD COLUMN customer_confirmed_delivery TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Khách hàng đã xác nhận nhận sách'");
DB::statement("ALTER TABLE borrows ADD COLUMN customer_confirmed_delivery_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Thời gian khách hàng xác nhận nhận sách'");
exit
```

## ✅ SAU KHI SỬA

1. Làm mới trang web (F5)
2. Lỗi sẽ biến mất
3. Tính năng xác nhận 2 chiều sẽ hoạt động bình thường

## 📝 KIỂM TRA

Sau khi chạy SQL, kiểm tra lại bằng cách:

```sql
SHOW COLUMNS FROM borrows LIKE 'customer_confirmed%';
```

Bạn sẽ thấy 2 cột:
- customer_confirmed_delivery
- customer_confirmed_delivery_at
