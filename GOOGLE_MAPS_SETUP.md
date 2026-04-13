# Hướng dẫn cấu hình Google Maps API

## Bước 1: Lấy Google Maps API Key

1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo một project mới hoặc chọn project hiện có
3. Vào **APIs & Services** > **Library**
4. Tìm và bật các API sau:
   - **Maps JavaScript API**
   - **Places API**
   - **Geocoding API**

## Bước 2: Tạo API Key

1. Vào **APIs & Services** > **Credentials**
2. Click **Create Credentials** > **API Key**
3. Copy API key vừa tạo
4. (Tùy chọn) Giới hạn API key:
   - Click vào API key vừa tạo
   - Trong **API restrictions**, chọn **Restrict key**
   - Chọn các API: Maps JavaScript API, Places API, Geocoding API
   - Trong **Application restrictions**, có thể giới hạn theo domain/IP

## Bước 3: Cấu hình trong .env

Thêm dòng sau vào file `.env`:

```
GOOGLE_MAPS_API_KEY=YOUR_API_KEY_HERE
```

Thay `YOUR_API_KEY_HERE` bằng API key bạn vừa lấy.

## Bước 4: Clear cache (nếu cần)

```bash
php artisan config:clear
php artisan cache:clear
```

## Tính năng

Sau khi cấu hình xong, form địa chỉ sẽ có các tính năng:

1. **Autocomplete**: Khi nhập địa chỉ, hệ thống sẽ gợi ý các địa chỉ ở Việt Nam
2. **Tự động điền**: Khi chọn địa chỉ, Tỉnh/Thành phố và Quận/Huyện sẽ được tự động điền
3. **Bản đồ tương tác**: Click "Hiển thị bản đồ" để chọn địa chỉ trên bản đồ
4. **Kéo thả marker**: Có thể kéo marker trên bản đồ để chọn vị trí chính xác

## Lưu ý

- Google Maps API có giới hạn miễn phí: $200/tháng (tương đương khoảng 28,000 requests)
- Đối với hầu hết các website nhỏ, mức miễn phí này là đủ
- Nếu vượt quá, bạn sẽ bị tính phí theo giá của Google
