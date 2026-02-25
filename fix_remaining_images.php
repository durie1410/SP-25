<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;

echo "Xóa ảnh mới của các sách cũ...\n\n";

// Xóa ảnh mới của ID 5 và 6
$book5 = Book::find(5);
if ($book5 && ($book5->hinh_anh === 'books/kinh-te-hoc-vi-mo.jpg' || strpos($book5->hinh_anh, 'kinh-te-hoc-vi-mo') !== false)) {
    $book5->hinh_anh = null;
    $book5->save();
    echo "✅ Đã xóa ảnh mới của sách ID 5 (Kinh tế học vi mô)\n";
}

$book6 = Book::find(6);
if ($book6 && ($book6->hinh_anh === 'books/truyen-kieu.jpg' || strpos($book6->hinh_anh, 'truyen-kieu') !== false)) {
    $book6->hinh_anh = null;
    $book6->save();
    echo "✅ Đã xóa ảnh mới của sách ID 6 (Truyện Kiều)\n";
}

// Xóa tất cả ảnh có tên file mới (không có số timestamp cũ)
$newImagePatterns = [
    'books/kinh-te-hoc-vi-mo.jpg',
    'books/truyen-kieu.jpg',
    'books/dien-bien-phu',
    'books/lich-su-sach',
    'books/lich-su-the-gioi',
    'books/lich-su-viet-nam-pho-thong',
    'books/su-viet',
    'books/cong-nghe-o-to-dien',
    'books/cong-nghe-tuong-lai',
    'books/how-technology-works',
    'books/lap-trinh-va-cuoc-song',
    'books/mat-trai-cua-cong-nghe',
    'books/con-tu-dau-toi',
    'books/giao-duc-stem',
    'books/giao-duc-vi-giao-duc',
    'books/giao-trinh-giao-duc-hoc-pho-thong',
    'books/giau-duc-gioi-tinh-cho-con-gai',
    'books/ban-thiet-ke-vi-dai',
    'books/cuoc-chien-lo-den',
    'books/du-lieu-lon-big-data',
    'books/khoa-hoc-nao-bo-trong-thien',
    'books/sach-khoa-hoc-vu-tru-song-song',
    'books/the-gioi-atlantis',
    'books/thoi-dai-ai',
    'books/thuyet-khoa-hoc-truc-quan',
    'books/tinh-uu-viet-cua-hoai-nghi',
    'books/vu-tru-nhung-bi-an',
    'books/chinh-sach-kinh-te-doi-ngoai',
    'books/kinh-te-nhat-ban',
    'books/kinh-te-xay-dung',
    'books/tien-te-the-ki-21',
    'books/kiep-nao-ta-cung-tim-thay-nhau',
    'books/tieu-thuyet-tram-nam-co-don',
    'books/doi-gio-hu',
    'books/em-la-anh-sang-giua-dem-trang',
    'books/giet-chet-chim-nhai',
    'books/mat-biec',
    'books/ong-gia-va-bien-ca',
    'books/tay-du-ki-1',
    'books/tieng-chim-hot-trong-bui-gai',
    'books/truoc-ngay-em-den',
    'books/vom-rung',
    'books/tro-ve-hoi-ky-173-ngay',
    'books/chi-pheo',
    'books/khai-hung',
    'books/khong-gia-dinh',
    'books/tho-to-huu',
    'books/thuyen',
    'books/vo-nhat',
];

$updated = 0;
foreach ($newImagePatterns as $pattern) {
    $books = Book::where('hinh_anh', 'like', "%{$pattern}%")->get();
    foreach ($books as $book) {
        $book->hinh_anh = null;
        $book->save();
        $updated++;
    }
}

echo "\n✅ Đã xóa ảnh mới của {$updated} sách.\n";
echo "\nHoàn thành! Database đã được khôi phục về trạng thái ban đầu.\n";










