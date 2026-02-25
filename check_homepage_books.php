<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;
use Illuminate\Support\Facades\Storage;

echo "========================================\n";
echo "Kiểm tra sách hiển thị trên trang chủ\n";
echo "========================================\n\n";

// Lấy sách featured
$featured_books = Book::where(function($query) {
    $query->where('is_featured', true)
          ->orWhere('is_featured', 1);
})->where('trang_thai', 'active')
  ->limit(10)
  ->get(['id', 'ten_sach', 'hinh_anh', 'is_featured']);

echo "1. Sách Featured (Nổi bật):\n";
echo str_repeat('-', 50) . "\n";
foreach ($featured_books as $book) {
    $imageExists = $book->hinh_anh && Storage::disk('public')->exists($book->hinh_anh);
    $status = $imageExists ? '✅' : '❌';
    echo "{$status} ID: {$book->id} - {$book->ten_sach}\n";
    echo "   Ảnh: " . ($book->hinh_anh ?? 'NULL') . "\n";
    if ($book->hinh_anh && $imageExists) {
        $url = Storage::disk('public')->url($book->hinh_anh);
        echo "   URL: {$url}\n";
    }
    echo "\n";
}

// Lấy sách mới
$new_books = Book::where(function($query) {
    $query->where('is_featured', false)
          ->orWhereNull('is_featured')
          ->orWhere('is_featured', 0);
})->where('trang_thai', 'active')
  ->orderBy('created_at', 'desc')
  ->limit(10)
  ->get(['id', 'ten_sach', 'hinh_anh', 'created_at']);

echo "\n2. Sách Mới:\n";
echo str_repeat('-', 50) . "\n";
foreach ($new_books as $book) {
    $imageExists = $book->hinh_anh && Storage::disk('public')->exists($book->hinh_anh);
    $status = $imageExists ? '✅' : '❌';
    echo "{$status} ID: {$book->id} - {$book->ten_sach}\n";
    echo "   Ảnh: " . ($book->hinh_anh ?? 'NULL') . "\n";
    if ($book->hinh_anh && $imageExists) {
        $url = Storage::disk('public')->url($book->hinh_anh);
        echo "   URL: {$url}\n";
    }
    echo "\n";
}










