<?php
/**
 * Kiểm tra quyền của staff user
 */
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$staff = User::where('email', 'staff@library.com')->first();

if (!$staff) {
    echo "❌ Không tìm thấy staff user\n";
    exit(1);
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║           KIỂM TRA QUYỀN STAFF USER                        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "👤 THÔNG TIN USER:\n";
echo "   Tên: {$staff->name}\n";
echo "   Email: {$staff->email}\n";
echo "   Role: {$staff->role}\n";
echo "\n";

echo "🎯 ROLES:\n";
$roles = $staff->getRoleNames();
if ($roles->count() > 0) {
    foreach ($roles as $role) {
        echo "   ✓ $role\n";
    }
} else {
    echo "   ❌ Không có roles\n";
}
echo "\n";

echo "📋 PERMISSIONS (17 quyền):\n";
$permissions = $staff->getPermissionNames()->sort();
$count = 0;
foreach ($permissions as $permission) {
    echo "   ✓ $permission\n";
    $count++;
}
echo "   Tổng cộng: $count quyền\n";
echo "\n";

echo "✅ KIỂM TRA QUYỀN CỤ THỂ:\n";
$checks = [
    'view-borrows' => 'Xem đơn hàng',
    'create-borrows' => 'Tạo đơn hàng',
    'edit-borrows' => 'Sửa đơn hàng',
    'return-books' => 'Xử lý trả sách',
    'view-books' => 'Xem sách',
    'create-books' => 'Thêm sách mới',
    'edit-books' => 'Sửa sách',
    'delete-books' => 'Xóa sách (KHÔNG CÓ)',
    'view-reports' => 'Xem báo cáo',
    'export-reports' => 'Xuất báo cáo',
    'manage-roles' => 'Quản lý roles (KHÔNG CÓ)',
    'delete-users' => 'Xóa người dùng (KHÔNG CÓ)',
];

foreach ($checks as $permission => $description) {
    $has = $staff->can($permission) ? '✓' : '✗';
    echo "   [$has] $permission - $description\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║ ✅ STAFF USER ĐÃ SẴN SÀNG!                               ║\n";
echo "║                                                            ║\n";
echo "║ Đăng nhập với:                                             ║\n";
echo "║   Email: staff@library.com                                 ║\n";
echo "║   Password: 123456                                         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";
