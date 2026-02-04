<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearLocalBookImagePaths extends Command
{
    protected $signature = 'books:clear-local-image-paths {--dry-run : Chỉ thống kê, không cập nhật dữ liệu}';

    protected $description = 'Xóa các giá trị books.hinh_anh đang lưu đường dẫn local (C:\\, D:\\, E:\\) để tránh ảnh broken.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $query = DB::table('books')
            ->whereNotNull('hinh_anh')
            ->where(function ($q) {
                $q->where('hinh_anh', 'like', 'C:%')
                  ->orWhere('hinh_anh', 'like', 'D:%')
                  ->orWhere('hinh_anh', 'like', 'E:%');
            });

        $count = (clone $query)->count();

        $this->info('Matched rows: ' . $count);

        if ($dryRun) {
            $this->warn('Dry run enabled. No changes applied.');
            return self::SUCCESS;
        }

        $updated = $query->update(['hinh_anh' => null]);

        $this->info('Updated rows: ' . $updated);

        return self::SUCCESS;
    }
}
