<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('books')->where('hinh_anh', 'books/ong_gia_va_bien_ca_.jpg')->update(['hinh_anh' => 'books/ong-gia-va-bien-ca.jpg']);
DB::table('books')->where('hinh_anh', 'books/Tieu_thuyet_tram_nam_co_don_.jpg')->update(['hinh_anh' => 'books/tieu-thuyet-tram-nam-co-don.jpg']);

echo "Done!\n";

// Verify
$missing = DB::table('books')->where('hinh_anh', 'like', 'books/%')->count();
echo "Books with books/: {$missing}\n";

$all = DB::table('books')->count();
echo "Total books: {$all}\n";
