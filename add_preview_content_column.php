<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$col = DB::select("SHOW COLUMNS FROM books LIKE 'preview_content'");
if (empty($col)) {
    DB::statement("ALTER TABLE books ADD COLUMN preview_content LONGTEXT NULL AFTER mo_ta");
    echo "✅ Đã thêm cột preview_content\n";
} else {
    echo "⚠️ Cột preview_content đã tồn tại\n";
}
