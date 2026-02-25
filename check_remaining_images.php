<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;

echo "Kiểm tra sách còn ảnh từ script mới:\n\n";

// Tìm các sách có ảnh với timestamp hoặc tên file mới
$booksWithNewImages = Book::where(function($query) {
    $query->where('hinh_anh', 'like', '%_1768058656%')
          ->orWhere('hinh_anh', 'like', 'books/%')
          ->orWhereNotNull('hinh_anh');
})->get(['id', 'ten_sach', 'hinh_anh']);

echo "Sách có ảnh:\n";
foreach ($booksWithNewImages as $book) {
    echo "ID: {$book->id} - {$book->ten_sach}\n";
    echo "  Ảnh: {$book->hinh_anh}\n";
    
    // Kiểm tra xem có phải ảnh mới không
    if (strpos($book->hinh_anh, '_1768058656') !== false || 
        (strpos($book->hinh_anh, 'books/') === 0 && !strpos($book->hinh_anh, '/'))) {
        echo "  ⚠️  Đây là ảnh mới, cần xóa\n";
    }
    echo "\n";
}










