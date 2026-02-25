<?php

/**
 * Script để sửa các đơn hàng đã được khách hàng xác nhận nhưng chưa chuyển trạng thái
 * Chạy: php fix_pending_confirmations.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Borrow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "\n";
echo "==========================================\n";
echo "  SỬA CÁC ĐƠN ĐÃ XÁC NHẬN NHƯNG CHƯA CHUYỂN TRẠNG THÁI\n";
echo "==========================================\n\n";

try {
    // Tìm các đơn đã được khách hàng xác nhận nhưng vẫn ở trạng thái giao_hang_thanh_cong
    $pendingBorrows = Borrow::where('customer_confirmed_delivery', true)
        ->where('trang_thai_chi_tiet', 'giao_hang_thanh_cong')
        ->get();
    
    echo "Tìm thấy " . $pendingBorrows->count() . " đơn hàng cần sửa.\n\n";
    
    if ($pendingBorrows->isEmpty()) {
        echo "✓ Không có đơn nào cần sửa!\n";
        exit(0);
    }
    
    foreach ($pendingBorrows as $borrow) {
        echo "Đang xử lý đơn #{$borrow->id}...\n";
        
        try {
            // Chuyển trạng thái trực tiếp
            $borrow->trang_thai_chi_tiet = 'da_muon_dang_luu_hanh';
            $borrow->ngay_bat_dau_luu_hanh = $borrow->customer_confirmed_delivery_at ?? now();
            $borrow->trang_thai = 'Dang muon';
            $borrow->save();
            
            // Cập nhật items
            $borrow->items()->update([
                'trang_thai' => 'Dang muon',
                'ngay_muon' => $borrow->customer_confirmed_delivery_at ?? now(),
            ]);
            
            echo "  ✓ Đã chuyển đơn #{$borrow->id} sang trạng thái 'Đã Mượn (Đang Lưu hành)'\n";
            
        } catch (\Exception $e) {
            echo "  ✗ Lỗi khi xử lý đơn #{$borrow->id}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    echo "==========================================\n";
    echo "  HOÀN TẤT!\n";
    echo "==========================================\n";
    echo "\n✓ Đã xử lý " . $pendingBorrows->count() . " đơn hàng.\n";
    echo "Bây giờ bạn có thể làm mới trang web và kiểm tra lại.\n\n";
    
} catch (\Exception $e) {
    echo "\n";
    echo "==========================================\n";
    echo "  LỖI XẢY RA\n";
    echo "==========================================\n";
    echo "✗ " . $e->getMessage() . "\n";
    echo "\nFile: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\n";
    exit(1);
}
