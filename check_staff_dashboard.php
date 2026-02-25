<?php
/**
 * Kiểm tra staff user có thể vào dashboard được không
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
echo "║     KIỂM TRA STAFF CÓ VÀO ĐƯỢC DASHBOARD KHÔNG             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "👤 STAFF USER:\n";
echo "   Name: {$staff->name}\n";
echo "   Email: {$staff->email}\n";
echo "   Role: {$staff->role}\n";
echo "\n";

echo "✅ KIỂM TRA:\n";

// Check 1: isStaff()
$isStaff = $staff->isStaff();
echo "   [" . ($isStaff ? "✓" : "✗") . "] isStaff() = " . ($isStaff ? "YES" : "NO") . "\n";

// Check 2: hasRole('staff')
$hasRole = $staff->hasRole('staff');
echo "   [" . ($hasRole ? "✓" : "✗") . "] hasRole('staff') = " . ($hasRole ? "YES" : "NO") . "\n";

// Check 3: Can view-dashboard
$canViewDashboard = $staff->can('view-dashboard');
echo "   [" . ($canViewDashboard ? "✓" : "✗") . "] can('view-dashboard') = " . ($canViewDashboard ? "YES" : "NO") . "\n";

// Check 4: Roles
$roles = $staff->getRoleNames();
echo "   [✓] Roles: " . implode(", ", $roles->toArray()) . "\n";

echo "\n";
echo "📋 PERMISSIONS (các permission quan trọng):\n";

$importantPermissions = [
    'view-dashboard',
    'view-borrows',
    'create-borrows',
    'edit-borrows',
    'view-books',
    'view-reports',
];

$hasAll = true;
foreach ($importantPermissions as $perm) {
    $has = $staff->can($perm);
    echo "   [" . ($has ? "✓" : "✗") . "] {$perm}\n";
    if (!$has) $hasAll = false;
}

echo "\n";
if ($isStaff && $hasRole) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║ ✅ OK! STAFF CÓ THỂ VÀO ĐƯỢC DASHBOARD                      ║\n";
    echo "║                                                            ║\n";
    echo "║ Các bước:                                                  ║\n";
    echo "║ 1. Đăng nhập: staff@library.com / 123456                  ║\n";
    echo "║ 2. Truy cập: /dashboard hoặc /admin                       ║\n";
    echo "║ 3. Bạn sẽ vào được admin panel                            ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
} else {
    echo "❌ CÓ VẤN ĐỀ!\n";
    echo "   isStaff() = $isStaff (cần YES)\n";
    echo "   hasRole('staff') = $hasRole (cần YES)\n";
}
echo "\n";
