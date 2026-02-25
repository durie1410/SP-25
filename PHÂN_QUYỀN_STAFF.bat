#!/bin/bash
# HƯỚNG DẪN NHANH - PHÂN QUYỀN STAFF

echo "🚀 Bắt đầu phân quyền STAFF..."
echo ""

echo "📌 Bước 1: Cập nhật permissions và roles"
php artisan db:seed --class=RolePermissionSeeder
echo ""

echo "✅ Bước 1 hoàn thành!"
echo ""

echo "📌 Bước 2: Tạo staff user mẫu và gán role"
php artisan migrate
echo ""

echo "✅ Bước 2 hoàn thành!"
echo ""

echo "📌 Bước 3: Xóa cache"
php artisan cache:clear
php artisan config:clear
echo ""

echo "✅ Bước 3 hoàn thành!"
echo ""

echo "✅✅✅ HOÀN TẤT! ✅✅✅"
echo ""
echo "📊 Các tài khoản STAFF:"
echo "  • Email: staff@library.com"
echo "  • Password: 123456"
echo ""
echo "📋 Quyền của STAFF:"
echo "  ✓ Xem & quản lý đơn hàng"
echo "  ✓ Xem & quản lý sách"
echo "  ✓ Xem báo cáo"
echo "  ✓ Xem & xác nhận đặt chỗ"
echo "  ✓ Phê duyệt đánh giá"
echo ""
