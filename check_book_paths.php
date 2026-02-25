<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;
use Illuminate\Support\Facades\Storage;

echo "Kiểm tra đường dẫn ảnh trong database:\n\n";

$books = Book::whereIn('ten_sach', ['Truyen Kieu', 'Truyện Kiều', 'Kinh Te Hoc Vi Mo', 'Kinh tế học vi mô'])
    ->orWhere('ten_sach', 'like', '%Truyện Kiều%')
    ->orWhere('ten_sach', 'like', '%Truyen Kieu%')
    ->orWhere('ten_sach', 'like', '%Kinh tế học vi mô%')
    ->orWhere('ten_sach', 'like', '%Kinh Te Hoc Vi Mo%')
    ->get(['id', 'ten_sach', 'hinh_anh']);

foreach ($books as $book) {
    echo "ID: {$book->id}\n";
    echo "Tên: {$book->ten_sach}\n";
    echo "Ảnh trong DB: {$book->hinh_anh}\n";
    
    // Kiểm tra file có tồn tại không
    $existsInStorage = Storage::disk('public')->exists($book->hinh_anh);
    $existsInPublic = file_exists(public_path('storage/' . $book->hinh_anh));
    
    echo "  Tồn tại trong storage: " . ($existsInStorage ? '✅' : '❌') . "\n";
    echo "  Tồn tại trong public: " . ($existsInPublic ? '✅' : '❌') . "\n";
    
    if ($existsInStorage) {
        $url = Storage::disk('public')->url($book->hinh_anh);
        echo "  URL: {$url}\n";
    }
    echo "\n";
}










