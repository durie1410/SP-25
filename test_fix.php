<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting fix script...\n";

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "  SỬA LỖI BẢNG USERS - THÊM CỘT\n";
echo "========================================\n\n";

try {
    // Kiểm tra cột ngay_sinh
    echo "1. Kiểm tra cột ngay_sinh...\n";
    $result = DB::select("SHOW COLUMNS FROM `users` WHERE Field = 'ngay_sinh'");
    
    if (empty($result)) {
        echo "   → Cột ngay_sinh chưa tồn tại. Đang thêm...\n";
        DB::statement("ALTER TABLE `users` ADD COLUMN `ngay_sinh` DATE NULL AFTER `so_cccd`");
        echo "   ✓ Đã thêm cột ngay_sinh thành công!\n\n";
    } else {
        echo "   ✓ Cột ngay_sinh đã tồn tại.\n\n";
    }
    
    // Kiểm tra cột gioi_tinh
    echo "2. Kiểm tra cột gioi_tinh...\n";
    $result = DB::select("SHOW COLUMNS FROM `users` WHERE Field = 'gioi_tinh'");
    
    if (empty($result)) {
        echo "   → Cột gioi_tinh chưa tồn tại. Đang thêm...\n";
        DB::statement("ALTER TABLE `users` ADD COLUMN `gioi_tinh` ENUM('Nam', 'Nu', 'Khac') NULL AFTER `ngay_sinh`");
        echo "   ✓ Đã thêm cột gioi_tinh thành công!\n\n";
    } else {
        echo "   ✓ Cột gioi_tinh đã tồn tại.\n\n";
    }
    
    // Kiểm tra lại
    echo "3. Kiểm tra lại các cột...\n";
    $columns = DB::select("SHOW COLUMNS FROM `users` WHERE Field IN ('ngay_sinh', 'gioi_tinh')");
    
    if (count($columns) == 2) {
        echo "   ✓ Cả hai cột đã tồn tại!\n";
        foreach ($columns as $col) {
            echo "      - {$col->Field}: {$col->Type}\n";
        }
    } else {
        echo "   ⚠ Có vấn đề! Chỉ tìm thấy " . count($columns) . " cột.\n";
    }
    
    echo "\n========================================\n";
    echo "  HOÀN THÀNH!\n";
    echo "========================================\n";
    echo "\nBây giờ bạn có thể:\n";
    echo "1. Mở lại trang cập nhật thông tin tài khoản\n";
    echo "2. Điền đầy đủ thông tin và nhấn 'Cập nhật'\n";
    echo "3. Lỗi sẽ không còn xuất hiện nữa!\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ LỖI: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nVui lòng kiểm tra:\n";
    echo "1. Database connection trong file .env\n";
    echo "2. Quyền truy cập database\n";
    echo "3. Tên bảng 'users' có tồn tại không\n";
    exit(1);
}

exit(0);

