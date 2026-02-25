<?php

/**
 * Script khôi phục database về trạng thái ban đầu
 * 
 * Sử dụng: php restore_old_database.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "Khôi phục database về trạng thái ban đầu\n";
echo "========================================\n\n";

// Bước 1: Khôi phục tất cả sách inactive thành active
echo "Bước 1: Khôi phục sách đã bị ẩn...\n";
echo str_repeat('-', 50) . "\n";

$restored = Book::where('trang_thai', 'inactive')
    ->update(['trang_thai' => 'active']);

echo "Đã khôi phục {$restored} sách từ inactive thành active.\n\n";

// Bước 2: Xóa các sách mới được thêm từ thư mục ảnh (ID >= 103)
// Nhưng giữ lại một số sách cũ quan trọng (ID 5, 6 nếu chúng là sách cũ)
echo "Bước 2: Xóa các sách mới được thêm từ thư mục ảnh...\n";
echo str_repeat('-', 50) . "\n";

// Danh sách ID sách mới cần xóa (từ script add images)
$newBookIds = [
    103, 104, 105, 106, 107, // Lịch sử
    108, 109, 110, 111, 112, // Công nghệ
    113, 114, 115, 116, 117, // Giáo dục
    118, 119, 120, 121, 122, 123, 124, 125, 126, 127, // Khoa học
    128, 129, 130, 131, // Kinh tế (trừ ID 5)
    132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142, // Tiểu thuyết
    143, 144, 145, 146, 147, 148, 149, // Văn học (trừ ID 6)
];

// Kiểm tra xem ID 5 và 6 có phải sách cũ không
$book5 = Book::find(5);
$book6 = Book::find(6);

// Nếu ID 5 và 6 có ảnh từ script mới (có timestamp), cần xóa chúng
if ($book5 && strpos($book5->hinh_anh, '_1768058656') !== false) {
    // Đặt lại ảnh về null hoặc ảnh cũ nếu có
    $book5->hinh_anh = null;
    $book5->save();
    echo "Đã xóa ảnh mới của sách ID 5 (Kinh tế học vi mô)\n";
}

if ($book6 && strpos($book6->hinh_anh, '_1768058656') !== false) {
    // Đặt lại ảnh về null hoặc ảnh cũ nếu có
    $book6->hinh_anh = null;
    $book6->save();
    echo "Đã xóa ảnh mới của sách ID 6 (Truyện Kiều)\n";
}

// Xóa các sách mới
$deleted = Book::whereIn('id', $newBookIds)->delete();
echo "Đã xóa {$deleted} sách mới.\n\n";

// Bước 3: Đặt lại ảnh về null cho các sách còn lại nếu ảnh có timestamp
echo "Bước 3: Xóa ảnh mới của các sách cũ...\n";
echo str_repeat('-', 50) . "\n";

$updated = Book::where('hinh_anh', 'like', '%_1768058656%')
    ->update(['hinh_anh' => null]);

echo "Đã xóa ảnh mới của {$updated} sách cũ.\n\n";

// Tổng kết
echo "========================================\n";
echo "Hoàn thành khôi phục!\n";
echo "========================================\n";

$totalBooks = Book::count();
$activeBooks = Book::where('trang_thai', 'active')->count();
$booksWithImages = Book::whereNotNull('hinh_anh')->count();

echo "Tổng số sách: {$totalBooks}\n";
echo "Sách đang hoạt động: {$activeBooks}\n";
echo "Sách có ảnh: {$booksWithImages}\n";
echo "========================================\n";
echo "\n⚠️  LƯU Ý: Các ảnh đã bị ghi đè trong storage không thể khôi phục tự động.\n";
echo "Nếu bạn có backup ảnh cũ, vui lòng khôi phục thủ công.\n";










