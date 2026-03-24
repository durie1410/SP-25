<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Carbon\Carbon;
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

        // ============================================================
        // XỬ LÝ BORROW_ITEMS QUÁ HẠN - GỬI THÔNG BÁO NGAY
        // ============================================================
        try {
            $notificationService = app(\App\Services\NotificationService::class);

            // Lấy các borrow_items đang mượn và đã quá hạn nhưng chưa được đánh dấu "Qua han"
            $overdueItems = \App\Models\BorrowItem::with(['borrow.reader.user', 'book'])
                ->where('trang_thai', 'Dang muon')
                ->whereDate('ngay_hen_tra', '<', Carbon::today()->toDateString())
                ->get();

            $notifiedCount = 0;
            foreach ($overdueItems as $item) {
                $borrow = $item->borrow;
                $reader = $borrow?->reader;
                $book = $item->book;

                if (!$borrow || !$reader) {
                    continue;
                }

                // Đánh dấu là quá hạn
                $item->update(['trang_thai' => 'Qua han']);

                // Tính số ngày quá hạn
                $daysOverdue = Carbon::parse($item->ngay_hen_tra)->diffInDays(Carbon::today());

                // Chuẩn bị dữ liệu thông báo
                $data = [
                    'reader_name' => $reader->ho_ten,
                    'book_title' => $book?->ten_sach ?? 'Sách thư viện',
                    'due_date' => Carbon::parse($item->ngay_hen_tra)->format('d/m/Y'),
                    'days_overdue' => $daysOverdue,
                    'fine_amount' => $daysOverdue * 5000,
                    'borrow_id' => $borrow->id,
                ];

                // Gửi thông báo
                if ($reader->user_id) {
                    $notificationService->sendNotification(
                        $reader->user_id,
                        'book_overdue',
                        $data,
                        ['database', 'email']
                    );
                } elseif (!empty($reader->email)) {
                    $notificationService->sendSimpleEmail(
                        $reader->email,
                        '⚠️ CẢNH BÁO: Sách "' . ($book?->ten_sach ?? 'Sách thư viện') . '" đã quá hạn trả',
                        "Xin chào {$reader->ho_ten},\n\n" .
                        "Sách bạn đang mượn đã quá hạn trả.\n\n" .
                        "📖 Sách: " . ($book?->ten_sach ?? 'Sách thư viện') . "\n" .
                        "📅 Hạn trả: " . Carbon::parse($item->ngay_hen_tra)->format('d/m/Y') . "\n" .
                        "⏰ Quá hạn: {$daysOverdue} ngày\n" .
                        "💰 Phí phạt tạm tính: " . number_format($daysOverdue * 5000, 0, ',', '.') . " VNĐ\n\n" .
                        "Vui lòng trả sách ngay lập tức để tránh phí phạt tăng cao.\n" .
                        "Liên hệ thư viện nếu có vấn đề.",
                        $data
                    );
                }

                $notifiedCount++;
            }

            $this->info("Borrow items marked overdue & notifications sent: {$notifiedCount}");
        } catch (\Exception $e) {
            $this->warn("Borrow items overdue check skipped: {$e->getMessage()}");
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
        $notificationService = app(NotificationService::class);
        $today = $now->toDateString();

        // ============================================================
        // Helper: gửi thông báo hủy đặt trước
        // ============================================================
        $sendCancelNotif = function ($model, $reason) use ($notificationService) {
            try {
                $userId  = $model->reader?->user_id ?? $model->user_id;
                $data    = [
                    'reader_name' => $model->reader?->ho_ten ?? ($model->user?->name ?? 'Bạn'),
                    'book_title'  => $model->book?->ten_sach ?? 'Sách',
                    'reason'      => $reason,
                ];

                $recipientEmail = $model->reader?->email ?? $model->user?->email;

                if ($userId) {
                    $notificationService->sendNotification($userId, 'reservation_cancelled', $data, ['database']);

                    if (!empty($recipientEmail)) {
                        $notificationService->sendSimpleEmail(
                            $recipientEmail,
                            'Yêu cầu đặt trước đã bị hủy',
                            'Xin chào {{reader_name}}, yêu cầu đặt trước sách "{{book_title}}" đã bị hủy. Lý do: {{reason}}.',
                            $data
                        );
                    }
                } elseif (!empty($recipientEmail)) {
                    $notificationService->sendSimpleEmail(
                        $recipientEmail,
                        'Yêu cầu đặt trước đã bị hủy',
                        'Xin chào {{reader_name}}, yêu cầu đặt trước sách "{{book_title}}" đã bị hủy. Lý do: {{reason}}.',
                        $data
                    );
                }
            } catch (\Throwable $e) {
                $this->warn("FAIL sendCancelNotif for #{$model->id}: " . $e->getMessage());
            }
        };

        // 0) PENDING: pickup_date đã qua nhưng KHÔNG có pickup_time -> TỰ HỦY (reservation 78)
        $pendingPastDateNoTime = \DB::table('inventory_reservations')
            ->where('status', 'pending')
            ->whereNotNull('pickup_date')
            ->whereNull('pickup_time')
            ->get();

        $autoCancelledNoTime = 0;
        foreach ($pendingPastDateNoTime as $reservation) {
            try {
                $pickupDate = \Carbon\Carbon::parse($reservation->pickup_date)->startOfDay();
                if ($now->gte($pickupDate->copy()->endOfDay())) {
                    $model = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user', 'inventory'])
                        ->find($reservation->id);
                    if ($model && $model->status === 'pending') {
                        // Chống trùng: đã gửi cancel cho đơn này trong 60 phút → bỏ qua
                        $alreadyCancelled = \DB::table('notification_logs')
                            ->where('user_id', $model->reader?->user_id ?? $model->user_id)
                            ->where('type', 'reservation_cancelled')
                            ->where('content', 'like', '%' . ($model->book?->ten_sach ?? '') . '%')
                            ->where('created_at', '>=', $now->copy()->subMinutes(60))
                            ->exists();
                        if (!$alreadyCancelled) {
                            $model->cancel('Tự động hủy: Đã qua ngày lấy sách mà không nhận.', null);
                            $sendCancelNotif($model, 'Đã qua ngày lấy sách mà không nhận.');
                        }
                        $autoCancelledNoTime++;
                    }
                }
            } catch (\Exception $e) { /* bỏ qua lỗi parse */ }
        }
        $this->info("Inventory reservations auto-cancelled (pending, no pickup_time, date past): {$autoCancelledNoTime}");

        // 0b) READY: pickup_date đã qua -> TỰ HỦY
        // Có pickup_time: so sánh ngày giờ; không có pickup_time: so sánh ngày
        $readyPastDate = \DB::table('inventory_reservations')
            ->where('status', 'ready')
            ->whereNotNull('pickup_date')
            ->get();

        $autoCancelledReady = 0;
        foreach ($readyPastDate as $reservation) {
            try {
                $shouldCancel = false;
                if (!empty($reservation->pickup_time)) {
                    $pickupDateTime = \Carbon\Carbon::parse($reservation->pickup_date . ' ' . $reservation->pickup_time);
                    $shouldCancel = $now->gte($pickupDateTime);
                } else {
                    $pickupDate = \Carbon\Carbon::parse($reservation->pickup_date)->endOfDay();
                    $shouldCancel = $now->gte($pickupDate);
                }
                if ($shouldCancel) {
                    $model = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user', 'inventory'])
                        ->find($reservation->id);
                    if ($model && $model->status === 'ready') {
                        $alreadyCancelled = \DB::table('notification_logs')
                            ->where('user_id', $model->reader?->user_id ?? $model->user_id)
                            ->where('type', 'reservation_cancelled')
                            ->where('content', 'like', '%' . ($model->book?->ten_sach ?? '') . '%')
                            ->where('created_at', '>=', $now->copy()->subMinutes(60))
                            ->exists();
                        if (!$alreadyCancelled) {
                            $model->cancel('Tự động hủy: Đã qua ngày/giờ lấy sách mà không nhận.', null);
                            $sendCancelNotif($model, 'Đã qua ngày/giờ lấy sách mà không nhận.');
                        }
                        $autoCancelledReady++;
                    }
                }
            } catch (\Exception $e) { /* bỏ qua lỗi parse */ }
        }
        $this->info("Inventory reservations auto-cancelled (ready past pickup date/time): {$autoCancelledReady}");

        // 1) PENDING: pickup_time đã qua mà chưa Ready -> TỰ HỦY + thông báo
        // Ví dụ: hẹn 8h, đã 8h rồi admin chưa nhấn Ready
        $pendingPastTime = \DB::table('inventory_reservations')
            ->where('status', 'pending')
            ->whereNotNull('pickup_date')
            ->whereNotNull('pickup_time')
            ->get();

        $cancelledCount = 0;
        foreach ($pendingPastTime as $reservation) {
            try {
                $pickupDateTime = \Carbon\Carbon::parse($reservation->pickup_date . ' ' . $reservation->pickup_time);
                if ($now->gte($pickupDateTime)) {
                    $reservationModel = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user', 'inventory'])
                        ->find($reservation->id);
                    if ($reservationModel && $reservationModel->status === 'pending') {
                        $alreadyCancelled = \DB::table('notification_logs')
                            ->where('user_id', $reservationModel->reader?->user_id ?? $reservationModel->user_id)
                            ->where('type', 'reservation_cancelled')
                            ->where('content', 'like', '%' . ($reservationModel->book?->ten_sach ?? '') . '%')
                            ->where('created_at', '>=', $now->copy()->subMinutes(60))
                            ->exists();
                        if (!$alreadyCancelled) {
                            $reservationModel->cancel('Tự động hủy: Đã qua giờ lấy sách mà không nhận.', null);
                            $sendCancelNotif($reservationModel, 'Đã qua giờ lấy sách mà không nhận.');
                        }
                        $cancelledCount++;
                    }
                }
            } catch (\Exception $e) {
                // Bỏ qua lỗi parse ngày giờ
            }
        }
        $this->info("Inventory reservations auto-cancelled (pending past pickup time): {$cancelledCount}");

        // 2) FULFILLED: quá 2 giờ kể từ pickup_time mà chưa trả -> QUÁ HẠN + thông báo
        // Chỉ xử lý fulfilled có pickup_time
        $fulfilledPastTime = \DB::table('inventory_reservations')
            ->where('status', 'fulfilled')
            ->whereNotNull('pickup_date')
            ->whereNotNull('pickup_time')
            ->where('pickup_date', '<=', $today)
            ->get();

        $overdueCount = 0;
        foreach ($fulfilledPastTime as $reservation) {
            try {
                $pickupDateTime = \Carbon\Carbon::parse($reservation->pickup_date . ' ' . $reservation->pickup_time);
                $deadline = $pickupDateTime->copy()->addHours(2);
                if ($now->gte($deadline)) {
                    $reservationModel = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user'])
                        ->find($reservation->id);
                    if ($reservationModel) {
                        // Chống gửi trùng: đã gửi notification_overdue cho đơn này trong 60 phút qua → bỏ qua
                        $alreadyNotified = \DB::table('notification_logs')
                            ->where('user_id', $reservationModel->reader?->user_id ?? $reservationModel->user_id)
                            ->where('type', 'reservation_overdue')
                            ->where('content', 'like', '%' . ($reservationModel->book?->ten_sach ?? '') . '%')
                            ->where('created_at', '>=', $now->copy()->subMinutes(60))
                            ->exists();

                        if ($alreadyNotified) {
                            continue; // Đã gửi rồi, bỏ qua
                        }

                        // Gửi thông báo quá hạn đúng 1 lần (database + email fallback)
                        $userId = $reservationModel->reader?->user_id ?? $reservationModel->user_id;
                        $data = [
                            'reader_name' => $reservationModel->reader?->ho_ten ?? ($reservationModel->user?->name ?? 'Bạn'),
                            'book_title' => $reservationModel->book?->ten_sach ?? 'Sách',
                            'pickup_date' => $reservationModel->pickup_date ? $reservationModel->pickup_date->format('d/m/Y') : '',
                            'pickup_time' => $reservationModel->pickup_time ?? '',
                        ];

                        if ($userId) {
                            $notificationService->sendNotification($userId, 'reservation_overdue', $data, ['database']);

                            $recipientEmail = $reservationModel->reader?->email ?? $reservationModel->user?->email;
                            if (!empty($recipientEmail)) {
                                $notificationService->sendSimpleEmail(
                                    $recipientEmail,
                                    'Yêu cầu đặt trước đã quá hạn',
                                    'Xin chào {{reader_name}}, yêu cầu đặt trước sách "{{book_title}}" đã quá hạn ngày lấy ({{pickup_date}}). Vui lòng tạo yêu cầu mới nếu vẫn cần.',
                                    $data
                                );
                            }
                        } else {
                            $recipientEmail = $reservationModel->reader?->email ?? $reservationModel->user?->email;
                            if (!empty($recipientEmail)) {
                                $notificationService->sendSimpleEmail(
                                    $recipientEmail,
                                    'Yêu cầu đặt trước đã quá hạn',
                                    'Xin chào {{reader_name}}, yêu cầu đặt trước sách "{{book_title}}" đã quá hạn ngày lấy ({{pickup_date}}). Vui lòng tạo yêu cầu mới nếu vẫn cần.',
                                    $data
                                );
                            }
                        }

                        $overdueCount++;
                    }
                }
            } catch (\Exception $e) {
                // Bỏ qua lỗi parse ngày giờ
            }
        }
        $this->info("Inventory reservations marked overdue (fulfilled past 2h): {$overdueCount}");

        return 0;
    }
}
