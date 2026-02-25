<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;
use Illuminate\Support\Facades\Storage;

echo "========================================\n";
echo "Kiểm tra sách và ảnh\n";
echo "========================================\n\n";

// Kiểm tra storage link
$publicStorage = public_path('storage');
$appStorage = storage_path('app/public');

echo "1. Kiểm tra Storage Link:\n";
if (is_link($publicStorage)) {
    echo "   ✅ Storage link đã tồn tại: {$publicStorage}\n";
} else {
    echo "   ❌ Storage link chưa tồn tại!\n";
    echo "   Chạy lệnh: php artisan storage:link\n";
}

echo "\n2. Kiểm tra sách có ảnh:\n";
$books = Book::whereNotNull('hinh_anh')->limit(10)->get(['id', 'ten_sach', 'hinh_anh']);

foreach ($books as $book) {
    $imageExists = Storage::disk('public')->exists($book->hinh_anh);
    $status = $imageExists ? '✅' : '❌';
    echo "   {$status} ID: {$book->id} - {$book->ten_sach}\n";
    echo "      Ảnh: {$book->hinh_anh}\n";
    if ($imageExists) {
        $url = Storage::disk('public')->url($book->hinh_anh);
        echo "      URL: {$url}\n";
    }
    echo "\n";
}

echo "\n3. Tổng số sách có ảnh: " . Book::whereNotNull('hinh_anh')->count() . "\n";
echo "   Tổng số sách: " . Book::count() . "\n";










