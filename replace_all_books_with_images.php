<?php

/**
 * Script thay thế TẤT CẢ sách bằng ảnh và tên từ thư mục
 * 
 * Sử dụng: php replace_all_books_with_images.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Mapping thư mục với category_id
$categoryMapping = [
    'lich_su' => 'Lịch sử',
    'cong_nghe' => 'Công nghệ',
    'giao_duc' => 'Giáo dục',
    'khoa_hoc' => 'Khoa học',
    'kinh_te' => 'Kinh tế',
    'tieu_thuyet' => 'Tiểu thuyết',
    'van_hoc' => 'Văn học',
];

// Đường dẫn gốc chứa các thư mục ảnh
$basePath = 'C:/Users/Admin/Pictures';

// Thư mục đích trong storage
$storagePath = 'books';

// Đảm bảo thư mục storage tồn tại
if (!Storage::disk('public')->exists($storagePath)) {
    Storage::disk('public')->makeDirectory($storagePath, 0755, true);
}

/**
 * Chuyển đổi tên file thành tên sách chuẩn
 */
function fileNameToBookName($fileName) {
    // Loại bỏ phần mở rộng
    $name = pathinfo($fileName, PATHINFO_FILENAME);
    
    // Thay _ và - bằng khoảng trắng
    $name = str_replace(['_', '-'], ' ', $name);
    
    // Loại bỏ khoảng trắng thừa
    $name = preg_replace('/\s+/', ' ', $name);
    $name = trim($name);
    
    // Xử lý các từ viết tắt và từ đặc biệt
    $specialWords = [
        'ai' => 'AI',
        'hd' => 'HD',
        'st' => 'ST',
        'stem' => 'STEM',
        'big data' => 'Big Data',
    ];
    
    foreach ($specialWords as $key => $value) {
        $name = preg_replace('/\b' . preg_quote($key, '/') . '\b/i', $value, $name);
    }
    
    // Viết hoa chữ cái đầu mỗi từ (nhưng giữ nguyên các từ đã được xử lý đặc biệt)
    $words = explode(' ', $name);
    $result = [];
    foreach ($words as $word) {
        // Nếu từ đã là chữ hoa hoặc viết tắt, giữ nguyên
        if (strtoupper($word) === $word && strlen($word) <= 5) {
            $result[] = $word;
        } else {
            // Viết hoa chữ cái đầu, giữ nguyên phần còn lại
            $result[] = mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($word, 1, null, 'UTF-8');
        }
    }
    $name = implode(' ', $result);
    
    return $name;
}

/**
 * Tìm category_id từ tên category
 */
function findCategoryId($categoryName) {
    $category = Category::where('ten_the_loai', 'like', "%{$categoryName}%")->first();
    if ($category) {
        return $category->id;
    }
    
    // Nếu không tìm thấy, tìm theo từ khóa
    $keywords = [
        'Lịch sử' => ['lịch sử', 'history'],
        'Công nghệ' => ['công nghệ', 'technology', 'tech'],
        'Giáo dục' => ['giáo dục', 'education'],
        'Khoa học' => ['khoa học', 'science'],
        'Kinh tế' => ['kinh tế', 'economy', 'economics'],
        'Tiểu thuyết' => ['tiểu thuyết', 'novel', 'fiction'],
        'Văn học' => ['văn học', 'literature'],
    ];
    
    if (isset($keywords[$categoryName])) {
        foreach ($keywords[$categoryName] as $keyword) {
            $category = Category::where('ten_the_loai', 'like', "%{$keyword}%")->first();
            if ($category) {
                return $category->id;
            }
        }
    }
    
    // Mặc định trả về category_id = 1 nếu không tìm thấy
    return 1;
}

/**
 * Xử lý một file ảnh
 */
function processImageFile($filePath, $fileName, $categoryName) {
    global $storagePath;
    
    echo "Đang xử lý: {$fileName}...\n";
    
    // Kiểm tra file có tồn tại không
    if (!file_exists($filePath)) {
        echo "  ❌ File không tồn tại: {$filePath}\n";
        return false;
    }
    
    // Lấy phần mở rộng file
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Chỉ xử lý file ảnh
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $allowedExtensions)) {
        echo "  ⚠️  Bỏ qua file không phải ảnh: {$fileName}\n";
        return false;
    }
    
    // Tạo tên sách từ tên file
    $bookName = fileNameToBookName($fileName);
    
    // Tìm category_id
    $categoryId = findCategoryId($categoryName);
    
    // Tạo tên file mới (sử dụng tên gốc để tránh trùng lặp)
    $baseFileName = Str::slug(pathinfo($fileName, PATHINFO_FILENAME));
    $newFileName = $baseFileName . '.' . $extension;
    $storageFilePath = $storagePath . '/' . $newFileName;
    
    // Xóa file cũ nếu đã tồn tại
    if (Storage::disk('public')->exists($storageFilePath)) {
        Storage::disk('public')->delete($storageFilePath);
    }
    
    // Copy file vào storage
    try {
        $fileContent = file_get_contents($filePath);
        Storage::disk('public')->put($storageFilePath, $fileContent);
        
        echo "  ✅ Đã copy ảnh vào storage: {$storageFilePath}\n";
    } catch (\Exception $e) {
        echo "  ❌ Lỗi khi copy file: " . $e->getMessage() . "\n";
        return false;
    }
    
    // Tìm hoặc tạo sách trong database
    try {
        // Tìm sách theo tên (không phân biệt hoa thường)
        $book = Book::whereRaw('LOWER(ten_sach) = ?', [strtolower($bookName)])->first();
        
        if ($book) {
            // Cập nhật sách đã tồn tại
            $book->hinh_anh = $storageFilePath;
            $book->category_id = $categoryId;
            $book->trang_thai = 'active';
            $book->save();
            echo "  ✅ Đã cập nhật sách: {$bookName} (ID: {$book->id})\n";
        } else {
            // Tạo sách mới
            $book = Book::create([
                'ten_sach' => $bookName,
                'category_id' => $categoryId,
                'tac_gia' => 'Chưa cập nhật',
                'nam_xuat_ban' => date('Y'),
                'hinh_anh' => $storageFilePath,
                'mo_ta' => "Sách về {$categoryName}",
                'gia' => 0,
                'danh_gia_trung_binh' => 0,
                'so_luong_ban' => 0,
                'so_luot_xem' => 0,
                'is_featured' => false,
                'trang_thai' => 'active',
            ]);
            echo "  ✅ Đã tạo sách mới: {$bookName} (ID: {$book->id})\n";
        }
        
        return true;
    } catch (\Exception $e) {
        echo "  ❌ Lỗi khi lưu database: " . $e->getMessage() . "\n";
        return false;
    }
}

// Bắt đầu xử lý
echo "========================================\n";
echo "Thay thế TẤT CẢ sách bằng ảnh từ thư mục\n";
echo "========================================\n\n";

// Bước 1: Xóa tất cả sách cũ (hoặc đánh dấu inactive)
echo "Bước 1: Xóa/Ẩn các sách cũ không có trong danh sách mới...\n";
echo str_repeat('-', 50) . "\n";

// Thu thập danh sách tên sách mới từ các file ảnh
$newBookNames = [];

foreach ($categoryMapping as $folderName => $categoryName) {
    $folderPath = $basePath . '/' . $folderName;
    
    if (!is_dir($folderPath)) {
        continue;
    }
    
    $files = scandir($folderPath);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $filePath = $folderPath . '/' . $file;
        
        if (is_file($filePath)) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($extension, $allowedExtensions)) {
                $bookName = fileNameToBookName($file);
                $newBookNames[] = strtolower($bookName);
            }
        }
    }
}

// Đánh dấu các sách không có trong danh sách mới là inactive
$booksToHide = Book::whereNotIn(DB::raw('LOWER(ten_sach)'), $newBookNames)
    ->where('trang_thai', 'active')
    ->update(['trang_thai' => 'inactive']);

echo "Đã ẩn {$booksToHide} sách cũ không có trong danh sách mới.\n\n";

// Bước 2: Xử lý tất cả ảnh từ thư mục
echo "Bước 2: Thêm/Cập nhật sách từ ảnh...\n";
echo str_repeat('-', 50) . "\n\n";

$totalProcessed = 0;
$totalSuccess = 0;
$totalFailed = 0;

foreach ($categoryMapping as $folderName => $categoryName) {
    $folderPath = $basePath . '/' . $folderName;
    
    echo "\n📁 Xử lý thư mục: {$folderName} ({$categoryName})\n";
    echo str_repeat('-', 50) . "\n";
    
    if (!is_dir($folderPath)) {
        echo "  ⚠️  Thư mục không tồn tại: {$folderPath}\n";
        continue;
    }
    
    // Đọc tất cả file trong thư mục
    $files = scandir($folderPath);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $filePath = $folderPath . '/' . $file;
        
        if (is_file($filePath)) {
            $totalProcessed++;
            if (processImageFile($filePath, $file, $categoryName)) {
                $totalSuccess++;
            } else {
                $totalFailed++;
            }
        }
    }
}

// Tổng kết
echo "\n\n========================================\n";
echo "Hoàn thành!\n";
echo "========================================\n";
echo "Tổng số file đã xử lý: {$totalProcessed}\n";
echo "Thành công: {$totalSuccess}\n";
echo "Thất bại: {$totalFailed}\n";
echo "Sách đã ẩn: {$booksToHide}\n";
echo "========================================\n";










