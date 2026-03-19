<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class LibraryHousekeeping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:housekeeping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run daily/hourly operational tasks (mark overdue, future no-show cleanup)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = now();

        // Mark overdue loans: picked_up and due_date < now -> overdue
        $affected = \DB::table('loans')
            ->where('status', 'picked_up')
            ->whereNotNull('due_date')
            ->where('due_date', '<', $now)
            ->update(['status' => 'overdue', 'updated_at' => $now]);

        $this->info("Loans marked overdue: {$affected}");

        // Auto no-show for seat reservations past hold window
        $nowStr = $now->toDateTimeString();
        $noShow = \DB::table('seat_reservations')
            ->where('status', 'pending')
            ->whereNotNull('hold_until')
            ->where('hold_until', '<', $nowStr)
            ->update(['status' => 'no_show', 'updated_at' => $now]);
        $this->info("Seat reservations marked no_show: {$noShow}");

        // Cancel inventory reservations if ready for more than 2 hours
        $twoHoursAgo = $now->copy()->subHours(2);

        // Lấy danh sách reservation cần hủy trước
        $reservationsToCancel = \DB::table('inventory_reservations')
            ->where('status', 'ready')
            ->whereNotNull('ready_at')
            ->where('ready_at', '<', $twoHoursAgo)
            ->get();

        $cancelledCount = 0;
        if ($reservationsToCancel->isNotEmpty()) {
            $notificationService = app(NotificationService::class);

            foreach ($reservationsToCancel as $reservation) {
                $reservationModel = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user', 'inventory'])
                    ->find($reservation->id);

                if ($reservationModel) {
                    // Gọi method cancel của model để giải phóng inventory và assign reservation tiếp theo
                    $reservationModel->cancel('Tự động hủy: Đã ready quá 2 giờ mà không nhận', null);

                    $cancelledCount++;
                }
            }

            // Gửi thông báo sau khi tất cả đã được hủy
            // (để tránh thông báo ready cho reservation tiếp theo bị trùng)
            foreach ($reservationsToCancel as $reservation) {
                $reservationModel = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user'])
                    ->find($reservation->id);

                if ($reservationModel) {
                    $userId = $reservationModel->reader?->user_id ?? $reservationModel->user_id;
                    if ($userId) {
                        $notificationService->sendNotification(
                            $userId,
                            'reservation_cancelled',
                            [
                                'reader_name' => $reservationModel->reader?->ho_ten ?? ($reservationModel->user?->name ?? 'Bạn'),
                                'book_title' => $reservationModel->book?->ten_sach ?? 'Sách',
                                'reason' => 'Đã ready quá 2 giờ mà không nhận',
                            ],
                            ['database', 'email']
                        );
                    }
                }
            }
        }

        $this->info("Inventory reservations auto-cancelled: {$cancelledCount}");

        return 0;
    }
}
