<?php
/**
 * Script để gán role STAFF cho user
 * Cách dùng: php assign_staff_role.php <user_id>
 * 
 * Ví dụ: php assign_staff_role.php 2
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Lấy user_id từ argument
$userId = $argv[1] ?? null;

if (!$userId) {
    echo "❌ Vui lòng cung cấp user ID\n";
    echo "Cách dùng: php assign_staff_role.php <user_id>\n";
    echo "Ví dụ: php assign_staff_role.php 2\n";
    exit(1);
}

$user = User::find($userId);

if (!$user) {
    echo "❌ Không tìm thấy user với ID: {$userId}\n";
    exit(1);
}

try {
    // Gán role staff
    $user->assignRole('staff');
    $user->update(['role' => 'staff']);
    
    echo "✅ Thành công! User '{$user->name}' ({$user->email}) đã được gán role STAFF\n";
    echo "📋 Quyền hiện tại:\n";
    
    $permissions = $user->getPermissionNames();
    foreach ($permissions as $permission) {
        echo "  • {$permission}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}
