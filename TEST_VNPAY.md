# 🧪 KỊCH BẢN TEST VNPAY

## Mục tiêu
Kiểm tra xem lỗi "Xác thực chữ ký thất bại" đã được sửa chưa.

## Điều kiện tiên quyết
- ✅ Đã chạy `fix_vnpay_now.bat` 
- ✅ Đã clear cache
- ✅ Server đang chạy

---

## Test Case 1: Kiểm tra cấu hình

### Bước 1: Mở trang debug
URL: `http://quanlythuviennn.test/vnpay-debug`

### Kết quả mong đợi:
- ✅ TMN Code: `E6I8Z7HX` (status badge màu xanh)
- ✅ Hash Secret: "✓ Đã cấu hình đúng (32 ký tự)"
- ✅ VNPay URL: `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`
- ✅ Banner màu xanh: "Cấu hình VNPay hoàn hảo!"

### Nếu thất bại:
- ⚠️ Chạy lại `fix_vnpay_now.bat`
- ⚠️ Kiểm tra file `.env`
- ⚠️ Chạy `php artisan config:clear`

---

## Test Case 2: Kiểm tra API JSON

### Bước 1: Mở API endpoint
URL: `http://quanlythuviennn.test/test-vnpay-config`

### Kết quả mong đợi:
```json
{
  "status": "VnPay Configuration Check",
  "tmn_code": "E6I8Z7HX",
  "hash_secret": "✅ Đã cấu hình",
  "url": "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html",
  ...
}
```

---

## Test Case 3: Test thanh toán thực tế

### Bước 1: Tạo giao dịch thanh toán
1. Đăng nhập vào hệ thống
2. Thêm sách vào giỏ mượn
3. Tiến hành checkout và chọn thanh toán VNPay

### Bước 2: Thanh toán trên VNPay
1. Chọn ngân hàng test (ví dụ: NCB)
2. Nhập thông tin thẻ test:
   - Số thẻ: `9704198526191432198`
   - Tên: `NGUYEN VAN A`
   - Ngày phát hành: `07/15`
   - Mật khẩu OTP: `123456`

### Bước 3: Kiểm tra callback

#### Kịch bản A: Thanh toán thành công
- URL redirect: `.../payment/success/...`
- Trang hiển thị: "Thanh toán thành công" với icon check màu xanh
- Database: `payment_status = 'success'`
- Log: Không có lỗi signature validation

#### Kịch bản B: Hủy thanh toán
- URL redirect: `.../payment/failed`
- Trang hiển thị: "Thanh toán thất bại"
- Lý do: "Khách hàng hủy giao dịch" (KHÔNG phải "Xác thực chữ ký thất bại")

### Kết quả mong đợi:
- ✅ KHÔNG còn lỗi "Xác thực chữ ký thất bại"
- ✅ Callback từ VNPay được xử lý đúng
- ✅ Log không có lỗi signature validation

---

## Test Case 4: Kiểm tra logging

### Bước 1: Mở file log
File: `storage/logs/laravel.log`

### Bước 2: Tìm các entry liên quan VNPay
Tìm kiếm:
- `VNPay Callback Received`
- `VNPay Validate Signature`
- `VNPay Signature Validation Failed` (không nên có)

### Kết quả mong đợi:
```
[2025-12-03 10:00:00] local.INFO: VNPay Callback Received
{
    "hash_secret_configured": true,
    "hash_secret_length": 32,
    ...
}

[2025-12-03 10:00:01] local.INFO: VNPay Validate Signature
{
    "is_valid": true,
    "hash_match_result": "✅ KHỚP",
    ...
}
```

**KHÔNG nên thấy:**
```
[2025-12-03 10:00:01] local.ERROR: VNPay Signature Validation FAILED
```

---

## Test Case 5: Test trang thanh toán thất bại

### Bước 1: Truy cập trực tiếp
URL: `http://quanlythuviennn.test/payment/failed?error=Xác thực chữ ký thất bại`

### Kết quả mong đợi:
- Hiển thị thông báo lỗi
- Hiển thị box màu vàng với hướng dẫn sửa:
  - Link đến `/vnpay-debug`
  - Hướng dẫn chạy `fix_vnpay_now.bat`
  - Đề cập file `README_VNPAY_FIX.txt`

---

## Checklist tổng quát

### Trước khi test:
- [ ] Đã chạy `fix_vnpay_now.bat`
- [ ] Đã kiểm tra trang `/vnpay-debug` → Màu xanh
- [ ] Server đang chạy
- [ ] Database đã được migrate

### Trong quá trình test:
- [ ] Test Case 1: Trang debug hiển thị đúng ✅
- [ ] Test Case 2: API JSON trả về đúng ✅
- [ ] Test Case 3: Thanh toán thành công không lỗi ✅
- [ ] Test Case 4: Log không có lỗi signature ✅
- [ ] Test Case 5: Trang failed có hướng dẫn ✅

### Sau khi test:
- [ ] Xóa các file test (nếu cần):
  - `check_and_fix_vnpay.php`
  - Route `/vnpay-debug` (trước khi deploy production)
  - Route `/test-vnpay-config` (trước khi deploy production)

---

## Kết quả cuối cùng

### ✅ Pass nếu:
- Không còn lỗi "Xác thực chữ ký thất bại"
- Callback VNPay được xử lý đúng
- Log không có lỗi
- Trang debug hiển thị "Cấu hình hoàn hảo"

### ❌ Fail nếu:
- Vẫn còn lỗi "Xác thực chữ ký thất bại"
- Log hiển thị `VNPay Signature Validation FAILED`
- Trang debug báo lỗi cấu hình

---

## Xử lý khi fail

1. Kiểm tra lại file `.env`:
   ```bash
   # Windows
   type .env | findstr VNPAY
   
   # Linux/Mac
   grep VNPAY .env
   ```

2. Xem log chi tiết:
   ```bash
   # Windows
   Get-Content storage/logs/laravel.log -Tail 100
   
   # Linux/Mac
   tail -100 storage/logs/laravel.log
   ```

3. Test config trong tinker:
   ```bash
   php artisan tinker
   >>> config('services.vnpay.tmn_code')
   >>> config('services.vnpay.hash_secret')
   >>> strlen(config('services.vnpay.hash_secret'))
   ```

4. Nếu vẫn lỗi, kiểm tra:
   - File `.env` có bị cache không?
   - Có permission issue không?
   - Hash Secret có khoảng trắng thừa không?

---

**Prepared by:** AI Assistant  
**Date:** 2025-12-03  
**Version:** 1.0

