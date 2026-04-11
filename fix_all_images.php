<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$booksDir = __DIR__ . '/storage/app/public/books/';
$files = array_filter(scandir($booksDir), fn($f) => is_file($booksDir . $f));

function normalize($s) {
    $s = strtolower($s);
    $s = preg_replace('/[\s_]+/', '-', $s);
    $s = preg_replace('/-+/', '-', $s);
    $from = 'àáảãạâầấẩẫậăằắẳẵặèéẻẽẹêềếểễệìíỉĩịòóỏõọôồốổỗộơờớởỡợùúủũụưừứửữựỳýỷỹđ';
    $to   = 'aaaaaaaaaaaaaaaaaeeeeeeeeeeeiiiiioooooooooooooooouuuuuuuuuuuyyyyyd';
    $s = strtr($s, $from, $to);
    return $s;
}

$flatMap = [];
foreach ($files as $f) {
    $key = normalize($f);
    $flatMap[$key] = $f;
}

$books = DB::table('books')->where('hinh_anh', 'like', 'books/%')->get(['id', 'ten_sach', 'hinh_anh']);

$updated = 0;
$notFound = [];

foreach ($books as $book) {
    $filename = basename($book->hinh_anh);
    $key = normalize($filename);

    if (isset($flatMap[$key])) {
        $newPath = 'books/' . $flatMap[$key];
        DB::table('books')->where('id', $book->id)->update(['hinh_anh' => $newPath]);
        echo "✅ {$filename} -> {$flatMap[$key]}\n";
        $updated++;
    } else {
        echo "❌ NOT FOUND: {$filename}\n";
        $notFound[] = $filename;
    }
}

echo "\nUpdated: {$updated} / {$books->count()}\n";
echo "Missing: " . count($notFound) . "\n";
