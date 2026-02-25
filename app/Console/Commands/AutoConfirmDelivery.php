<?php

namespace App\Console\Commands;

use App\Models\Borrow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoConfirmDelivery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borrow:auto-confirm-delivery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động xác nhận nhận sách sau 3 giờ nếu khách không phản hồi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang kiểm tra các đơn hàng cần tự động xác nhận...');

        // Tìm các đơn hàng đang ở trạng thái "Giao hàng thành công" và chưa được khách xác nhận
        // Và đã quá 3 giờ kể từ khi chuyển sang trạng thái "Chờ khách xác nhận"
        $threeHoursAgo = Carbon::now()->subHours(3);
        
        $borrows = Borrow::where('trang_thai_chi_tiet', Borrow::STATUS_GIAO_HANG_THANH_CONG)
            ->where('customer_confirmed_delivery', false)
            ->where('customer_rejected_delivery', false)
            ->whereNotNull('ngay_cho_xac_nhan_nhan')
            ->where('ngay_cho_xac_nhan_nhan', '<=', $threeHoursAgo)
            ->get();

        $count = 0;

        foreach ($borrows as $borrow) {
            try {
                // Tự động xác nhận nhận sách
                $borrow->customer_confirmed_delivery = true;
                $borrow->customer_confirmed_delivery_at = now();
                $borrow->ghi_chu = ($borrow->ghi_chu ?? '') . "\n[Hệ thống tự động xác nhận do quá thời gian phản hồi (3 giờ)]";
                
                // Chuyển sang trạng thái "Đang mượn"
                $borrow->trang_thai_chi_tiet = Borrow::STATUS_DA_MUON_DANG_LUU_HANH;
                $borrow->trang_thai = 'Dang muon';
                $borrow->ngay_muon = now();
                
                $borrow->save();

                // Cập nhật thanh toán
                $borrow->payments()
                    ->where('payment_status', 'pending')
                    ->whereIn('payment_type', ['deposit', 'borrow_fee', 'shipping_fee'])
                    ->update([
                        'payment_status' => 'success',
                        'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Hệ thống tự động xác nhận thanh toán do quá thời gian phản hồi')"),
                        'updated_at' => now()
                    ]);

                $count++;
                $this->info("✓ Đã tự động xác nhận đơn hàng #{$borrow->id}");
            } catch (\Exception $e) {
                $this->error("✗ Lỗi khi xác nhận đơn hàng #{$borrow->id}: " . $e->getMessage());
            }
        }

        $this->info("Hoàn thành! Đã tự động xác nhận {$count} đơn hàng.");
        return 0;
    }
}
