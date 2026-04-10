<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pdo = DB::connection()->getPdo();
$columns = $pdo->query("SHOW COLUMNS FROM users LIKE 'locked_at'")->fetchAll();
if (empty($columns)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN locked_at TIMESTAMP NULL AFTER email_verified_at");
    echo "✅ Đã thêm cột locked_at vào bảng users\n";
} else {
    echo "⚠️ Cột locked_at đã tồn tại\n";
}
