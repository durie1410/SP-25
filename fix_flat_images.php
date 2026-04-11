<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// List all flat images in books folder
$booksDir = __DIR__ . '/storage/app/public/books/';
$files = scandir($booksDir);
$flatFiles = [];
foreach ($files as $f) {
    if (is_file($booksDir . $f)) {
        $flatFiles[strtolower($f)] = $f;
    }
}

// Map book paths to flat files
$books = DB::table('books')->where('hinh_anh', 'like', 'books/%')->get(['id', 'ten_sach', 'hinh_anh']);

echo "Updating image paths:\n";
$updated = 0;

foreach ($books as $book) {
    $oldPath = $book->hinh_anh;
    $filename = basename($oldPath);
    $lowerFilename = strtolower($filename);

    if (isset($flatFiles[$lowerFilename])) {
        $newPath = 'books/' . $flatFiles[$lowerFilename];
        DB::table('books')->where('id', $book->id)->update(['hinh_anh' => $newPath]);
        echo "✅ ID {$book->id}: {$filename} -> {$flatFiles[$lowerFilename]}\n";
        $updated++;
    } else {
        echo "❌ ID {$book->id}: {$filename} - NOT FOUND\n";
    }
}

echo "\nUpdated: {$updated} books\n";

// Verify
$missing = DB::table('books')->where('hinh_anh', 'like', 'books/%')->get();
echo "Books with books/ path: " . $missing->count() . "\n";
