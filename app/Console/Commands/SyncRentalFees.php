<?php

namespace App\Console\Commands;

use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Models\Book;
use App\Models\Inventory;
use App\Services\PricingService;
use Illuminate\Console\Command;

class SyncRentalFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borrow:sync-rental-fees
                            {--dry-run : Chỉ hiển thị những gì sẽ thay đổi mà không lưu vào database}
                            {--fix : Thực hiện tính lại tiền thuê cho các item có tien_thue = 0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ tiền thuê từ borrow_items lên borrows header và tính lại cho các item bị thiếu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $fix = $this->option('fix');

        $this->info('=== Bắt đầu đồng bộ tiền thuê ===');
        $this->info('');

        // Bước 1: Tính lại tiền thuê cho các borrow_items có tien_thue = 0
        if ($fix) {
            $this->fixMissingRentalFees($dryRun);
        }

        // Bước 2: Đồng bộ tổng tiền từ borrow_items lên borrows header
        $this->syncBorrowTotals($dryRun);

        $this->info('');
        $this->info('=== Hoàn tất ===');

        return Command::SUCCESS;
    }

    /**
     * Tính lại tiền thuê cho các item bị thiếu
     */
    protected function fixMissingRentalFees(bool $dryRun)
    {
        $this->info('--- Bước 1: Tính lại tiền thuê cho các item bị thiếu ---');

        // Sửa logic query: lấy đúng item có tien_thue = 0 hoặc null
        $items = BorrowItem::where(function ($query) {
                $query->where('tien_thue', 0)
                    ->orWhereNull('tien_thue');
            })
            ->whereHas('book')
            ->with(['book', 'inventory', 'borrow'])
            ->get();

        $this->info("Tìm thấy {$items->count()} item có tiền thuê = 0 hoặc null");

        if ($items->isEmpty()) {
            $this->info('Không có item nào cần tính lại tiền thuê.');
            return;
        }

        $fixed = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $book = $item->book;
            $inventory = $item->inventory;

            // Nếu không có book, bỏ qua
            if (!$book) {
                $skipped++;
                continue;
            }

            // Kiểm tra giá sách
            $bookPrice = floatval($book->gia ?? 0);
            if ($bookPrice <= 0) {
                $skipped++;
                continue;
            }

            // Tính phí thuê
            $fees = PricingService::calculateFees(
                $book,
                $inventory,
                $item->ngay_muon,
                $item->ngay_hen_tra,
                $item->borrow && $item->borrow->reader ? true : false
            );

            if ($fees['tien_thue'] > 0) {
                $oldValue = $item->tien_thue;
                $newValue = $fees['tien_thue'];

                if (!$dryRun) {
                    $item->update([
                        'tien_thue' => $newValue,
                        'tien_coc' => $fees['tien_coc'],
                    ]);
                }

                $this->line("  Item #{$item->id}: {$book->ten_sach}");
                $this->line("    - Giá sách: " . number_format($bookPrice) . "đ");
                $this->line("    - Tiền thuê: {$oldValue} -> " . number_format($newValue) . "đ");

                $fixed++;
            } else {
                $skipped++;
            }
        }

        $this->info("Đã xử lý: {$fixed} items, Bỏ qua: {$skipped} items");
        $this->info('');
    }

    /**
     * Đồng bộ tổng tiền từ borrow_items lên borrows header
     */
    protected function syncBorrowTotals(bool $dryRun)
    {
        $this->info('--- Bước 2: Đồng bộ tổng tiền lên borrows header ---');

        // Lấy tất cả borrows có items
        $borrows = Borrow::whereHas('borrowItems')->with('borrowItems')->get();

        $this->info("Tìm thấy {$borrows->count()} phiếu mượn cần đồng bộ");

        $synced = 0;
        $unchanged = 0;

        foreach ($borrows as $borrow) {
            // Tính tổng từ borrow_items
            $tienThueFromItems = $borrow->borrowItems->sum('tien_thue');
            $tienCocFromItems = $borrow->borrowItems->sum('tien_coc');
            $tienShipFromItems = $borrow->borrowItems->sum('tien_ship');
            $tongTien = $tienThueFromItems + $tienCocFromItems + $tienShipFromItems;

            // So sánh với giá trị hiện tại trong borrows
            $tienThueChanged = floatval($borrow->tien_thue ?? 0) !== floatval($tienThueFromItems);
            $tienCocChanged = floatval($borrow->tien_coc ?? 0) !== floatval($tienCocFromItems);
            $tienShipChanged = floatval($borrow->tien_ship ?? 0) !== floatval($tienShipFromItems);
            $tongTienChanged = floatval($borrow->tong_tien ?? 0) !== floatval($tongTien);

            if ($tienThueChanged || $tienCocChanged || $tienShipChanged || $tongTienChanged) {
                $this->line("  Phiếu #{$borrow->id} ({$borrow->borrow_code}):");
                $this->line("    - Tiền thuê: " . number_format($borrow->tien_thue ?? 0) . " -> " . number_format($tienThueFromItems) . "đ");
                $this->line("    - Tiền cọc: " . number_format($borrow->tien_coc ?? 0) . " -> " . number_format($tienCocFromItems) . "đ");
                $this->line("    - Tiền ship: " . number_format($borrow->tien_ship ?? 0) . " -> " . number_format($tienShipFromItems) . "đ");
                $this->line("    - Tổng tiền: " . number_format($borrow->tong_tien ?? 0) . " -> " . number_format($tongTien) . "đ");

                if (!$dryRun) {
                    $borrow->update([
                        'tien_thue' => $tienThueFromItems,
                        'tien_coc' => $tienCocFromItems,
                        'tien_ship' => $tienShipFromItems,
                        'tong_tien' => $tongTien,
                    ]);
                }

                $synced++;
            } else {
                $unchanged++;
            }
        }

        $action = $dryRun ? 'sẽ' : 'đã';
        $this->info("Đã {$action} đồng bộ: {$synced} phiếu, Không thay đổi: {$unchanged} phiếu");

        if ($dryRun) {
            $this->warn('Đây là chế độ dry-run. Sử dụng --fix để thực hiện thay đổi.');
        }
    }
}
