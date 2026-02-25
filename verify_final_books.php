<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;
use Illuminate\Support\Facades\Storage;

echo "========================================\n";
echo "Kiểm tra kết quả cuối cùng\n";
echo "========================================\n\n";

// Đếm sách active
$activeBooks = Book::where('trang_thai', 'active')->count();
$inactiveBooks = Book::where('trang_thai', 'inactive')->count();
$totalBooks = Book::count();

echo "Tổng số sách: {$totalBooks}\n";
echo "Sách đang hoạt động (active): {$activeBooks}\n";
echo "Sách đã ẩn (inactive): {$inactiveBooks}\n\n";

// Kiểm tra sách active có ảnh
$booksWithImages = Book::where('trang_thai', 'active')
    ->whereNotNull('hinh_anh')
    ->count();

$booksWithoutImages = Book::where('trang_thai', 'active')
    ->whereNull('hinh_anh')
    ->count();

echo "Sách active có ảnh: {$booksWithImages}\n";
echo "Sách active không có ảnh: {$booksWithoutImages}\n\n";

// Hiển thị một số sách active
echo "Danh sách sách đang hoạt động:\n";
echo str_repeat('-', 50) . "\n";

$activeBooksList = Book::where('trang_thai', 'active')
    ->orderBy('id')
    ->get(['id', 'ten_sach', 'hinh_anh', 'category_id']);

foreach ($activeBooksList as $book) {
    $imageExists = $book->hinh_anh && Storage::disk('public')->exists($book->hinh_anh);
    $status = $imageExists ? '✅' : '❌';
    echo "{$status} ID: {$book->id} - {$book->ten_sach}\n";
}










