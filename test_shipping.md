# Hướng dẫn Test Tính Năng Tính Phí Vận Chuyển Tự Động

## Bước 1: Cấu hình môi trường

1. Đảm bảo đã cấu hình Google Maps API Key trong file `.env`:
   ```
   GOOGLE_MAPS_API_KEY=your_api_key_here
   ```

2. Cấu hình địa chỉ thư viện trong file `.env`:
   ```
   LIBRARY_ADDRESS=123 Đường ABC, Quận XYZ, TP.HCM, Việt Nam
   ```
   Hoặc có thể chỉnh trong `config/pricing.php`:
   ```php
   'library_address' => env('LIBRARY_ADDRESS', 'Địa chỉ thư viện của bạn'),
   ```

3. Clear cache config:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## Bước 2: Test API Endpoint

### Test bằng cURL:
```bash
curl -X POST http://your-domain/api/shipping/calculate \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"address":"123 Nguyễn Văn A, Quận 1, TP.HCM, Việt Nam"}'
```

### Test bằng Postman:
- Method: POST
- URL: `http://your-domain/api/shipping/calculate`
- Headers:
  - Content-Type: application/json
  - Accept: application/json
- Body (raw JSON):
  ```json
  {
    "address": "123 Nguyễn Văn A, Quận 1, TP.HCM, Việt Nam"
  }
  ```

### Kết quả mong đợi:
```json
{
  "success": true,
  "distance": 5.23,
  "shipping_fee": 11500,
  "duration": 1200,
  "message": "Tính phí vận chuyển thành công"
}
```

## Bước 3: Test trên Frontend

1. Truy cập trang checkout: `/orders/checkout?book_id=1&paper_quantity=1`

2. Nhập địa chỉ vào ô "Địa chỉ giao hàng":
   - Ví dụ: "123 Nguyễn Văn A, Quận 1, TP.HCM, Việt Nam"
   
3. Quan sát:
   - Sau 1 giây khi ngừng nhập, hệ thống sẽ tự động tính phí
   - Hiển thị khoảng cách và phí vận chuyển
   - Tổng tiền được cập nhật tự động

4. Kiểm tra:
   - Nếu khoảng cách <= 5km: Phí vận chuyển = 0 VNĐ (Miễn phí)
   - Nếu khoảng cách > 5km: Phí = (khoảng cách - 5) * 5000 VNĐ

## Bước 4: Test Tạo Đơn Hàng

1. Điền đầy đủ thông tin trong form checkout
2. Nhập địa chỉ giao hàng
3. Chọn phương thức thanh toán
4. Click "Đặt hàng"
5. Kiểm tra trong database:
   - Bảng `orders`: 
     - `shipping_amount` phải có giá trị đúng
     - `total_amount` = `subtotal` + `shipping_amount`
     - `notes` có chứa khoảng cách (nếu có)

## Các Trường Hợp Test

### Test Case 1: Địa chỉ gần (< 5km)
- Địa chỉ: "123 Đường ABC, Quận XYZ, TP.HCM" (nếu thư viện cũng ở TP.HCM)
- Kỳ vọng: Phí vận chuyển = 0 VNĐ

### Test Case 2: Địa chỉ xa (> 5km)
- Địa chỉ: "123 Nguyễn Văn A, Quận 1, TP.HCM, Việt Nam"
- Kỳ vọng: Phí vận chuyển > 0 VNĐ

### Test Case 3: Địa chỉ không hợp lệ
- Địa chỉ: "abc"
- Kỳ vọng: Hiển thị lỗi "Không thể tính khoảng cách"

### Test Case 4: Không nhập địa chỉ
- Địa chỉ: ""
- Kỳ vọng: Hiển thị "Vui lòng nhập địa chỉ", không cho submit form

## Xử Lý Lỗi

### Lỗi: "Google Maps API Key chưa được cấu hình"
- Kiểm tra file `.env` có `GOOGLE_MAPS_API_KEY` chưa
- Chạy `php artisan config:clear`

### Lỗi: "Không thể tính khoảng cách"
- Kiểm tra Google Maps API Key có hợp lệ không
- Kiểm tra địa chỉ có đúng định dạng không
- Kiểm tra log: `storage/logs/laravel.log`

### Lỗi: CORS hoặc 404
- Kiểm tra route đã được đăng ký: `php artisan route:list --path=api/shipping`
- Kiểm tra middleware API

## Ghi Chú

- Kết quả tính khoảng cách được cache 24 giờ để giảm số lần gọi API
- Nếu muốn test lại với cùng địa chỉ, cần clear cache:
  ```bash
  php artisan cache:clear
  ```


