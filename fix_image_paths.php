<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$updated = DB::table('books')
    ->where('hinh_anh', 'like', 'assets/%')
    ->update([
        'hinh_anh' => DB::raw("REPLACE(hinh_anh, 'assets/', 'books/')")
    ]);

echo "Updated: {$updated} books\n";

// Verify
$books = DB::table('books')
    ->where('hinh_anh', 'like', 'assets/%')
    ->count();
echo "Remaining with assets/: {$books}\n";
