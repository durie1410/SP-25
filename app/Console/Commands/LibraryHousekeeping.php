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
        try {
            $affected = \DB::table('loans')
                ->where('status', 'picked_up')
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now)
                ->update(['status' => 'overdue', 'updated_at' => $now]);
            $this->info("Loans marked overdue: {$affected}");
        } catch (\Exception $e) {
            $this->warn("Loans table check skipped: {$e->getMessage()}");
        }

        // Auto no-show for seat reservations past hold window
        try {
            $nowStr = $now->toDateTimeString();
            $noShow = \DB::table('seat_reservations')
                ->where('status', 'pending')
                ->whereNotNull('hold_until')
                ->where('hold_until', '<', $nowStr)
                ->update(['status' => 'no_show', 'updated_at' => $now]);
            $this->info("Seat reservations marked no_show: {$noShow}");
        } catch (\Exception $e) {
            $this->warn("Seat reservations table check skipped: {$e->getMessage()}");
        }

        // ============================================================
        // XỬ LÝ INVENTORY RESERVATIONS
        // ============================================================
        $twoHoursAgo = $now->copy()->subHours(2);

        // 1) READY quá 2 giờ -> TỰ HỦY + thông báo
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
                    $reservationModel->cancel('Tự động hủy: Đã sẵn sàng (ready) quá 2 giờ mà không nhận.', null);
                    $cancelledCount++;
                }
            }

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
                                'reason' => 'Đã sẵn sàng quá 2 giờ mà không nhận sách.',
                            ],
                            ['database', 'email']
                        );
                    }
                }
            }
        }

        $this->info("Inventory reservations auto-cancelled (ready > 2h): {$cancelledCount}");

        // 1b) PENDING quá ngày lấy -> đánh dấu QUÁ HẠN (không hủy, chờ admin xử lý)
        $today = $now->toDateString();
        $pendingOverdue = \DB::table('inventory_reservations')
            ->where('status', 'pending')
            ->whereNotNull('pickup_date')
            ->where('pickup_date', '<', $today)
            ->get();

        $pendingOverdueCount = 0;
        if ($pendingOverdue->isNotEmpty()) {
            foreach ($pendingOverdue as $reservation) {
                $reservationModel = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user'])
                    ->find($reservation->id);

                if ($reservationModel) {
                    $reservationModel->markAsOverdue(
                        'Tự động đánh dấu quá hạn: Đã qua ngày lấy sách nhưng vẫn ở trạng thái pending.',
                        null
                    );
                    $pendingOverdueCount++;
                }
            }
        }
        $this->info("Inventory reservations marked overdue (pending past pickup date): {$pendingOverdueCount}");

        // 2) FULFILLED: quá 2 giờ kể từ pickup_time trong ngày pickup_date -> đánh dấu QUÁ HẠN
        // Chỉ xử lý những fulfilled có pickup_date = hôm nay hoặc trước đó
        $reservationsToMarkOverdue = \DB::table('inventory_reservations')
            ->where('status', 'fulfilled')
            ->whereNotNull('pickup_date')
            ->whereNotNull('pickup_time')
            ->where('pickup_date', '<=', $today)
            ->get();

        $overdueCount = 0;
        if ($reservationsToMarkOverdue->isNotEmpty()) {
            $notificationService = app(NotificationService::class);

            foreach ($reservationsToMarkOverdue as $reservation) {
                $reservationModel = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user'])
                    ->find($reservation->id);

                if (!$reservationModel) {
                    continue;
                }

                // Tính thời điểm hết hạn: pickup_date + pickup_time + 2 giờ
                try {
                    $pickupDateTime = \Carbon\Carbon::parse($reservation->pickup_date . ' ' . $reservation->pickup_time);
                    $deadline = $pickupDateTime->copy()->addHours(2);

                    if ($now->gte($deadline)) {
                        $reservationModel->markAsOverdue(
                            'Tự động đánh dấu quá hạn: Đã qua 2 giờ kể từ giờ lấy sách mà không nhận.',
                            null
                        );
                        $overdueCount++;

                        // Gửi thông báo quá hạn
                        $userId = $reservationModel->reader?->user_id ?? $reservationModel->user_id;
                        if ($userId) {
                            $notificationService->sendNotification(
                                $userId,
                                'reservation_overdue',
                                [
                                    'reader_name' => $reservationModel->reader?->ho_ten ?? ($reservationModel->user?->name ?? 'Bạn'),
                                    'book_title' => $reservationModel->book?->ten_sach ?? 'Sách',
                                    'pickup_date' => $reservationModel->pickup_date ? $reservationModel->pickup_date->format('d/m/Y') : '',
                                    'pickup_time' => $reservationModel->pickup_time ?? '',
                                ],
                                ['database', 'email']
                            );
                        }
                    }
                } catch (\Exception $e) {
                    // Bỏ qua lỗi parse ngày giờ, không ảnh hưởng đến các reservation khác
                    $this->warn("Skipping reservation #{$reservation->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Inventory reservations marked overdue (fulfilled > 2h pickup): {$overdueCount}");

        return 0;
    }
}
