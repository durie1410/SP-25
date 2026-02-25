# 🔧 HƯỚNG DẪN SỬA LỖI "XÁC THỰC CHỮ KÝ THẤT BẠI" - VNPAY

## 📋 Mô tả lỗi

Khi thanh toán qua VNPay, trang hiển thị lỗi:
```
Thanh toán thất bại
Lý do: Xác thực chữ ký thất bại
```

## 🎯 Nguyên nhân

Lỗi này xảy ra khi **HASH_SECRET** trong file `.env` không khớp với Hash Secret trên tài khoản VNPay của bạn.

## ✅ CÁCH SỬA NHANH NHẤT (Khuyến nghị)

### Bước 1: Chạy file fix tự động

Double-click vào file **`fix_vnpay_now.bat`** trong thư mục gốc project.

Hoặc mở PowerShell/CMD và chạy:
```bash
cd D:\laragon\www\quanlythuviennn
fix_vnpay_now.bat
```

### Bước 2: Kiểm tra cấu hình

Mở trình duyệt và truy cập: **http://quanlythuviennn.test/vnpay-debug**

Trang này sẽ hiển thị:
- ✅ Cấu hình hiện tại
- ⚠️ Các vấn đề (nếu có)
- 🔧 Hướng dẫn sửa chi tiết

### Bước 3: Thử thanh toán lại

Quay lại trang thanh toán và thử lại.

---

## 🛠️ CÁCH SỬA THỦ CÔNG

### Nếu cách tự động không hoạt động:

#### 1. Mở file `.env` trong thư mục gốc project

#### 2. Tìm hoặc thêm các dòng sau:

```env
VNPAY_TMN_CODE=E6I8Z7HX
VNPAY_HASH_SECRET=LYS57TC0V5NARXASTFT3Y0D50NHNPWEZ
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
```

**LƯU Ý:** 
- Các giá trị trên là cho môi trường **SANDBOX (Test)**
- Nếu bạn đang dùng tài khoản VNPay thật, hãy lấy thông tin từ trang quản trị VNPay

#### 3. Xóa cache Laravel

Mở PowerShell/CMD và chạy:
```bash
php artisan config:clear
php artisan cache:clear
```

#### 4. Kiểm tra config đã được áp dụng

```bash
php artisan tinker --execute="echo config('services.vnpay.tmn_code');"
```

Nếu hiển thị `E6I8Z7HX` → Thành công! ✅

---

## 🔍 KIỂM TRA VÀ DEBUG

### Cách 1: Trang debug đẹp (Khuyến nghị)
Truy cập: **http://quanlythuviennn.test/vnpay-debug**

### Cách 2: API JSON
Truy cập: **http://quanlythuviennn.test/test-vnpay-config**

### Cách 3: Xem log chi tiết
Mở file: `storage/logs/laravel.log`

Tìm các dòng chứa:
- `VNPay Validate Signature`
- `VNPay Callback Received`

Log sẽ hiển thị:
```
[2025-12-03 10:00:00] local.ERROR: VNPay Signature Validation FAILED
{
    "reason": "Hash không khớp - có thể do HASH_SECRET sai",
    "suggestion": "Kiểm tra lại VNPAY_HASH_SECRET trong file .env",
    ...
}
```

---

## 🌐 LẤY THÔNG TIN TỪ VNPAY

### Nếu bạn có tài khoản VNPay thật:

1. Đăng nhập vào trang quản trị VNPay
2. Vào mục **"Thông tin tích hợp"** hoặc **"API Configuration"**
3. Copy các thông tin:
   - **TMN Code** (Mã website)
   - **Hash Secret** (Secret Key / Checksum Key)
4. Cập nhật vào file `.env`:
   ```env
   VNPAY_TMN_CODE=your_tmn_code_here
   VNPAY_HASH_SECRET=your_hash_secret_here
   ```
5. Chạy: `php artisan config:clear`

### Môi trường Production:

Đổi URL từ sandbox sang production:
```env
VNPAY_URL=https://vnpayment.vn/paymentv2/vpcpay.html
```

---

## ❓ CÁC LỖI THƯỜNG GẶP

### 1. Hash Secret có khoảng trắng thừa
```env
# ❌ SAI
VNPAY_HASH_SECRET= ABC123XYZ

# ✅ ĐÚNG
VNPAY_HASH_SECRET=ABC123XYZ
```

### 2. Dùng Hash Secret của môi trường sai
- Sandbox có Hash Secret riêng
- Production có Hash Secret riêng
- Phải dùng đúng môi trường

### 3. Chưa clear cache sau khi sửa .env
Luôn chạy sau khi sửa `.env`:
```bash
php artisan config:clear
```

### 4. File .env không có quyền ghi
Kiểm tra quyền của file `.env`:
- Windows: Click phải → Properties → bỏ "Read-only"

---

## 📞 HỖ TRỢ

Nếu vẫn gặp vấn đề:

1. Kiểm tra log: `storage/logs/laravel.log`
2. Truy cập trang debug: http://quanlythuviennn.test/vnpay-debug
3. Chụp ảnh màn hình lỗi và thông tin từ trang debug

---

## 🎉 KẾT QUẢ

Sau khi sửa xong:
- ✅ Không còn lỗi "Xác thực chữ ký thất bại"
- ✅ Thanh toán VNPay hoạt động bình thường
- ✅ Log không còn lỗi signature validation

---

**Chúc bạn thành công!** 🚀

