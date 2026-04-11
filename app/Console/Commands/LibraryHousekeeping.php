<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use App\Services\UserLockService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            $affected = DB::table('loans')
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
            $noShow = DB::table('seat_reservations')
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
        $userLockService = app(UserLockService::class);
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
                    $notificationService->sendNotification($userId, 'reservation_cancelled', $data, ['database', 'email']);
                } elseif (!empty($recipientEmail)) {
                    // Chỉ gửi email khi không có user_id (không có tài khoản)
                    $notificationService->sendSimpleEmail(
                        $recipientEmail,
                        'Yêu cầu đặt trước đã bị hủy',
                        'Xin chào {{reader_name}}, yêu cầu đặt trước sách "{{book_title}}" đã bị hủy. Lý do: {{reason}}.',
                        $data
                    );
                }
                if (!$userId && empty($recipientEmail)) {
                    $this->warn("Reservation #{$model->id}: không có thông tin liên hệ để gửi thông báo hủy.");
                }
            } catch (\Throwable $e) {
                $this->warn("FAIL sendCancelNotif for #{$model->id}: " . $e->getMessage());
            }
        };

        // 0) PENDING: pickup_date đã qua nhưng KHÔNG có pickup_time -> TỰ HỦY (reservation 78)
        $pendingPastDateNoTime = DB::table('inventory_reservations')
            ->where('status', 'pending')
            ->whereNotNull('pickup_date')
            ->whereNull('pickup_time')
            ->get();

        $autoCancelledNoTime = 0;
        foreach ($pendingPastDateNoTime as $reservation) {
            try {
                $pickupDate = \Carbon\Carbon::parse($reservation->pickup_date)->startOfDay();
                if ($now->gte($pickupDate->copy()->endOfDay())) {
                    // Query trực tiếp với điều kiện status='pending' để tránh stale Eloquent cache
                    $model = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user', 'inventory'])
                        ->where('id', $reservation->id)
                        ->where('status', 'pending')
                        ->first();
                    if ($model) {
                        // Luôn hủy status trước
                        $model->cancel('Tự động hủy: Đã qua ngày lấy sách mà không nhận.', null);
                        // Gửi thông báo (chống trùng trong 60 phút)
                        $alreadyCancelled = DB::table('notification_logs')
                            ->where('user_id', $model->reader?->user_id ?? $model->user_id)
                            ->where('type', 'reservation_cancelled')
                            ->where('content', 'like', '%' . ($model->book?->ten_sach ?? '') . '%')
                            ->where('created_at', '>=', $now->copy()->subMinutes(60))
                            ->exists();
                        if (!$alreadyCancelled) {
                            $sendCancelNotif($model, 'Đã qua ngày lấy sách mà không nhận.');
                        }
                        $autoCancelledNoTime++;
                    }
                }
            } catch (\Exception $e) { /* bỏ qua lỗi parse */ }
        }
        $this->info("Inventory reservations auto-cancelled (pending, no pickup_time, date past): {$autoCancelledNoTime}");

        // 0b) READY: pickup_date đã qua
        // Có inventory → QUÁ HẠN (khách đã nhận sách). Không có inventory → HỦY (khách không đến)
        $readyPastDate = DB::table('inventory_reservations')
            ->where('status', 'ready')
            ->whereNotNull('pickup_date')
            ->get();

        $autoCancelledReady = 0;
        $autoOverdueReady = 0;
        foreach ($readyPastDate as $reservation) {
            try {
                $shouldProcess = false;
                if (!empty($reservation->pickup_time)) {
                    $pickupDateTime = \Carbon\Carbon::parse($reservation->pickup_date . ' ' . $reservation->pickup_time);
                    $shouldProcess = $now->gte($pickupDateTime);
                } else {
                    $pickupDate = \Carbon\Carbon::parse($reservation->pickup_date)->endOfDay();
                    $shouldProcess = $now->gte($pickupDate);
                }
                if ($shouldProcess) {
                    // Query trực tiếp với điều kiện status='ready' để tránh stale Eloquent cache
                    $model = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user', 'inventory'])
                        ->where('id', $reservation->id)
                        ->where('status', 'ready')
                        ->first();
                    if ($model) {
                        $userId = $model->reader?->user_id ?? $model->user_id;
                        $bookTitle = $model->book?->ten_sach ?? 'Sách';

                        if ($model->inventory_id) {
                            // Có bản sao → QUÁ HẠN
                            DB::table('inventory_reservations')
                                ->where('id', $model->id)
                                ->update([
                                    'status' => 'overdue',
                                    'admin_note' => 'Tự động đánh dấu quá hạn: Đã qua ngày/giờ lấy sách mà không xác nhận nhận.',
                                    'updated_at' => $now,
                                ]);
                            $userLockService->incrementNoShowAndAutoLockByReservation($model);
                            $data = [
                                'reader_name' => $model->reader?->ho_ten ?? ($model->user?->name ?? 'Bạn'),
                                'book_title' => $bookTitle ?: 'Sách',
                                'pickup_date' => $model->pickup_date ? $model->pickup_date->format('d/m/Y') : '',
                                'pickup_time' => $model->pickup_time ?? '',
                            ];
                            // Gửi thông báo (chống trùng trong 60 phút)
                            $alreadyNotified = empty($bookTitle) ? false : DB::table('notification_logs')
                                ->where('user_id', $userId)
                                ->where('type', 'reservation_overdue')
                                ->where('content', 'like', '%' . $bookTitle . '%')
                                ->where('created_at', '>=', $now->copy()->subMinutes(60))
                                ->exists();
                            if (!$alreadyNotified) {
                                if ($userId) {
                                    $notificationService->sendNotification($userId, 'reservation_overdue', $data, ['database', 'email']);
                                } else {
                                    // Chỉ gửi email khi không có user_id
                                    $email = $model->reader?->email ?? $model->user?->email;
                                    if ($email) {
                                        $notificationService->sendSimpleEmail($email, 'Yêu cầu đặt trước đã quá hạn', 'Xin chào {{reader_name}}, yêu cầu đặt trước sách "{{book_title}}" đã quá hạn ngày lấy ({{pickup_date}}). Vui lòng tạo yêu cầu mới nếu vẫn cần.', $data);
                                    }
                                }
                            }
                            $autoOverdueReady++;
                        } else {
                            // Không có bản sao → HỦY sau 2 giờ kể từ giờ hẹn
                            $noInvDeadline = !empty($reservation->pickup_time)
                                ? \Carbon\Carbon::parse($reservation->pickup_date . ' ' . $reservation->pickup_time)->addHours(2)
                                : \Carbon\Carbon::parse($reservation->pickup_date)->endOfDay();
                            if (!$now->gte($noInvDeadline)) {
                                continue; // Chưa đủ 2 giờ, bỏ qua
                            }
                            // Luôn hủy status trước
                            $model->cancel('Tự động hủy: Chưa có bản sao được gán và đã quá 2 giờ kể từ giờ hẹn.', null);
                            // Gửi thông báo (chống trùng trong 60 phút)
                            $alreadyCancelled = empty($bookTitle) ? false : DB::table('notification_logs')
                                ->where('user_id', $userId)
                                ->where('type', 'reservation_cancelled')
                                ->where('content', 'like', '%' . $bookTitle . '%')
                                ->where('created_at', '>=', $now->copy()->subMinutes(60))
                                ->exists();
                            if (!$alreadyCancelled) {
                                $sendCancelNotif($model, 'Chưa có bản sao được gán và đã quá 2 giờ kể từ giờ hẹn.');
                            }
                            $autoCancelledReady++;
                        }
                    }
                }
            } catch (\Exception $e) { /* bỏ qua lỗi parse */ }
        }
        $this->info("Inventory reservations marked overdue (ready past deadline, has inventory): {$autoOverdueReady}");
        $this->info("Inventory reservations auto-cancelled (ready past deadline, no inventory): {$autoCancelledReady}");

        // 1) PENDING: pickup_time đã qua mà chưa Ready -> TỰ HỦY + thông báo
        // Ví dụ: hẹn 8h, đã 8h rồi admin chưa nhấn Ready
        $pendingPastTime = DB::table('inventory_reservations')
            ->where('status', 'pending')
            ->whereNotNull('pickup_date')
            ->whereNotNull('pickup_time')
            ->get();

        $cancelledCount = 0;
        foreach ($pendingPastTime as $reservation) {
            try {
                $pickupDateTime = \Carbon\Carbon::parse($reservation->pickup_date . ' ' . $reservation->pickup_time);
                // Nếu chưa có bản sao → chờ thêm 2 giờ mới hủy
                $cancelDeadline = is_null($reservation->inventory_id)
                    ? $pickupDateTime->copy()->addHours(2)
                    : $pickupDateTime;
                if ($now->gte($cancelDeadline)) {
                    // Query DB trực tiếp với điều kiện status='pending' để tránh stale Eloquent cache
                    $reservationModel = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user', 'inventory'])
                        ->where('id', $reservation->id)
                        ->where('status', 'pending')
                        ->first();
                    if ($reservationModel) {
                        // Luôn hủy status trước
                        $reservationModel->cancel('Tự động hủy: Đã qua giờ lấy sách mà không nhận.', null);
                        // Gửi thông báo (chống trùng trong 60 phút)
                        $alreadyCancelled = DB::table('notification_logs')
                            ->where('user_id', $reservationModel->reader?->user_id ?? $reservationModel->user_id)
                            ->where('type', 'reservation_cancelled')
                            ->where('content', 'like', '%' . ($reservationModel->book?->ten_sach ?? '') . '%')
                            ->where('created_at', '>=', $now->copy()->subMinutes(60))
                            ->exists();
                        if (!$alreadyCancelled) {
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

        // 2) READY (có pickup_time): quá giờ hẹn mà chưa nhận → xử lý
        // QUÁ HẠN: ready + có inventory_id
        // HỦY: ready + không có inventory_id
        $readyWithTime = DB::table('inventory_reservations')
            ->where('status', 'ready')
            ->whereNotNull('pickup_date')
            ->whereNotNull('pickup_time')
            ->get();

        $overdueCount = 0;
        $cancelledCount = 0;
        foreach ($readyWithTime as $reservation) {
            try {
                $pickupDateTime = \Carbon\Carbon::parse($reservation->pickup_date . ' ' . $reservation->pickup_time);
                $deadline = $pickupDateTime->copy()->addHours(2);
                if ($now->gte($deadline)) {
                    // Query trực tiếp với điều kiện status='ready' để tránh stale Eloquent cache
                    $reservationModel = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user', 'inventory'])
                        ->where('id', $reservation->id)
                        ->where('status', 'ready')
                        ->first();
                    if ($reservationModel) {
                        $userId = $reservationModel->reader?->user_id ?? $reservationModel->user_id;
                        $bookTitle = $reservationModel->book?->ten_sach ?? 'Sách';

                        if ($reservationModel->inventory_id) {
                            // Có bản sao → QUÁ HẠN: luôn cập nhật status trước
                            DB::table('inventory_reservations')
                                ->where('id', $reservationModel->id)
                                ->update([
                                    'status' => 'overdue',
                                    'admin_note' => $reservationModel->admin_note
                                        ? $reservationModel->admin_note . "\nTự động đánh dấu quá hạn: Đã quá 2 giờ kể từ giờ hẹn mà không xác nhận nhận sách."
                                        : 'Tự động đánh dấu quá hạn: Đã quá 2 giờ kể từ giờ hẹn mà không xác nhận nhận sách.',
                                    'updated_at' => $now,
                                ]);
                            $userLockService->incrementNoShowAndAutoLockByReservation($reservationModel);
                            $data = [
                                'reader_name' => $reservationModel->reader?->ho_ten ?? ($reservationModel->user?->name ?? 'Bạn'),
                                'book_title' => $bookTitle ?: 'Sách',
                                'pickup_date' => $reservationModel->pickup_date ? $reservationModel->pickup_date->format('d/m/Y') : '',
                                'pickup_time' => $reservationModel->pickup_time ?? '',
                            ];
                            // Gửi thông báo (chống trùng trong 60 phút)
                            $alreadyNotified = empty($bookTitle) ? false : DB::table('notification_logs')
                                ->where('user_id', $userId)
                                ->where('type', 'reservation_overdue')
                                ->where('content', 'like', '%' . $bookTitle . '%')
                                ->where('created_at', '>=', $now->copy()->subMinutes(60))
                                ->exists();
                            if (!$alreadyNotified) {
                                if ($userId) {
                                    $notificationService->sendNotification($userId, 'reservation_overdue', $data, ['database', 'email']);
                                } else {
                                    $email = $reservationModel->reader?->email ?? $reservationModel->user?->email;
                                    if ($email) {
                                        $notificationService->sendSimpleEmail($email, 'Yêu cầu đặt trước đã quá hạn', 'Xin chào {{reader_name}}, yêu cầu đặt trước sách "{{book_title}}" đã quá hạn ngày lấy ({{pickup_date}}). Vui lòng tạo yêu cầu mới nếu vẫn cần.', $data);
                                    }
                                }
                            }
                            $overdueCount++;
                        } else {
                            // Không có bản sao → HỦY: luôn hủy status trước
                            $reservationModel->cancel('Tự động hủy: Đã quá 2 giờ kể từ giờ hẹn mà không đến nhận sách.', null);
                            // Gửi thông báo (chống trùng trong 60 phút)
                            $alreadyCancelled = empty($bookTitle) ? false : DB::table('notification_logs')
                                ->where('user_id', $userId)
                                ->where('type', 'reservation_cancelled')
                                ->where('content', 'like', '%' . $bookTitle . '%')
                                ->where('created_at', '>=', $now->copy()->subMinutes(60))
                                ->exists();
                            if (!$alreadyCancelled) {
                                $sendCancelNotif($reservationModel, 'Đã quá 2 giờ kể từ giờ hẹn mà không đến nhận sách.');
                            }
                            $cancelledCount++;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Bỏ qua lỗi parse ngày giờ
            }
        }
        $this->info("Inventory reservations marked overdue (ready past 2h, has inventory): {$overdueCount}");
        $this->info("Inventory reservations auto-cancelled (ready past 2h, no inventory): {$cancelledCount}");

        // 3) READY (không có pickup_time): ngày hẹn đã qua hết ngày
        // Có inventory_id → QUÁ HẠN. Không có inventory_id → HỦY
        $readyNoTime = DB::table('inventory_reservations')
            ->where('status', 'ready')
            ->whereNotNull('pickup_date')
            ->whereNull('pickup_time')
            ->get();

        $noTimeCount = 0;
        $noTimeCancelled = 0;
        foreach ($readyNoTime as $reservation) {
            try {
                $pickupDate = \Carbon\Carbon::parse($reservation->pickup_date)->startOfDay();
                if ($now->gte($pickupDate->copy()->endOfDay())) {
                    // Query trực tiếp với điều kiện status='ready' để tránh stale Eloquent cache
                    $reservationModel = \App\Models\InventoryReservation::with(['book', 'reader.user', 'user', 'inventory'])
                        ->where('id', $reservation->id)
                        ->where('status', 'ready')
                        ->first();
                    if ($reservationModel) {
                        $userId = $reservationModel->reader?->user_id ?? $reservationModel->user_id;
                        $bookTitle = $reservationModel->book?->ten_sach ?? 'Sách';

                        if ($reservationModel->inventory_id) {
                            // Có bản sao → QUÁ HẠN
                            DB::table('inventory_reservations')
                                ->where('id', $reservationModel->id)
                                ->update([
                                    'status' => 'overdue',
                                    'admin_note' => 'Tự động đánh dấu quá hạn: Đã qua ngày hẹn nhận sách mà không nhận.',
                                    'updated_at' => $now,
                                ]);
                            $userLockService->incrementNoShowAndAutoLockByReservation($reservationModel);
                            $data = [
                                'reader_name' => $reservationModel->reader?->ho_ten ?? ($reservationModel->user?->name ?? 'Bạn'),
                                'book_title' => $bookTitle,
                                'pickup_date' => $reservationModel->pickup_date ? $reservationModel->pickup_date->format('d/m/Y') : '',
                                'pickup_time' => '',
                            ];
                            $alreadyNotified = empty($bookTitle) ? false : DB::table('notification_logs')
                                ->where('user_id', $userId)
                                ->where('type', 'reservation_overdue')
                                ->where('content', 'like', '%' . $bookTitle . '%')
                                ->where('created_at', '>=', $now->copy()->subMinutes(60))
                                ->exists();
                            if (!$alreadyNotified) {
                                if ($userId) {
                                    $notificationService->sendNotification($userId, 'reservation_overdue', $data, ['database']);
                                } else {
                                    $email = $reservationModel->reader?->email ?? $reservationModel->user?->email;
                                    if ($email) {
                                        $notificationService->sendSimpleEmail($email, 'Yêu cầu đặt trước đã quá hạn', 'Xin chào {{reader_name}}, yêu cầu đặt trước sách "{{book_title}}" đã quá hạn ngày lấy ({{pickup_date}}). Vui lòng tạo yêu cầu mới nếu vẫn cần.', $data);
                                    }
                                }
                            }
                            $noTimeCount++;
                        } else {
                            // Không có bản sao → HỦY
                            $reservationModel->cancel('Tự động hủy: Đã qua ngày hẹn nhận sách mà không nhận.', null);
                            $noTimeCancelled++;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Bỏ qua lỗi parse
            }
        }
        $this->info("Inventory reservations marked overdue (ready, no pickup_time, date past, has inventory): {$noTimeCount}");
        $this->info("Inventory reservations auto-cancelled (ready, no pickup_time, date past, no inventory): {$noTimeCancelled}");

        return 0;
    }
}
