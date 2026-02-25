<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;
use Illuminate\Support\Facades\Storage;

echo "========================================\n";
echo "Kiểm tra sách mới được thêm từ thư mục\n";
echo "========================================\n\n";

// Các sách mới được thêm (ID từ 103 trở đi)
$newBooks = Book::where('id', '>=', 103)->orderBy('id')->get(['id', 'ten_sach', 'hinh_anh', 'category_id']);

echo "Số sách mới: " . $newBooks->count() . "\n\n";

foreach ($newBooks as $book) {
    $imageExists = Storage::disk('public')->exists($book->hinh_anh);
    $status = $imageExists ? '✅' : '❌';
    echo "{$status} ID: {$book->id} - {$book->ten_sach}\n";
    echo "   Category ID: {$book->category_id}\n";
    echo "   Ảnh: {$book->hinh_anh}\n";
    if ($imageExists) {
        $url = Storage::disk('public')->url($book->hinh_anh);
        echo "   URL: {$url}\n";
    } else {
        echo "   ⚠️  File ảnh không tồn tại!\n";
    }
    echo "\n";
}

// Kiểm tra sách có thể đang dùng ảnh placeholder
echo "\n========================================\n";
echo "Kiểm tra sách có thể đang dùng ảnh placeholder\n";
echo "========================================\n\n";

$placeholderBooks = Book::where('hinh_anh', 'like', '%placeholder%')
    ->orWhere('hinh_anh', 'like', '%default%')
    ->orWhereNull('hinh_anh')
    ->get(['id', 'ten_sach', 'hinh_anh']);

if ($placeholderBooks->count() > 0) {
    echo "Tìm thấy " . $placeholderBooks->count() . " sách có thể đang dùng ảnh placeholder:\n\n";
    foreach ($placeholderBooks as $book) {
        echo "ID: {$book->id} - {$book->ten_sach}\n";
        echo "   Ảnh: " . ($book->hinh_anh ?? 'NULL') . "\n\n";
    }
} else {
    echo "✅ Không tìm thấy sách nào đang dùng ảnh placeholder.\n";
}










