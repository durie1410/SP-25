<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryReservation::with(['book', 'inventory', 'user', 'reader', 'processedBy'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'ready' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('status', 'overdue');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('inventory_status')) {
            if ($request->inventory_status === 'assigned') {
                $query->whereNotNull('inventory_id');
            } elseif ($request->inventory_status === 'unassigned') {
                $query->whereNull('inventory_id');
            }
        }

        if ($request->filled('pickup_window')) {
            $today = now()->toDateString();
            if ($request->pickup_window === 'today') {
                $query->whereDate('pickup_date', $today);
            } elseif ($request->pickup_window === 'upcoming') {
                $query->whereDate('pickup_date', '>=', $today);
            } elseif ($request->pickup_window === 'past') {
                $query->whereDate('pickup_date', '<', $today);
            }
        }

        if ($request->filled('reader_keyword')) {
            $keyword = trim((string) $request->reader_keyword);
            $query->where(function ($sub) use ($keyword) {
                $sub->whereHas('reader', function ($readerQuery) use ($keyword) {
                    $readerQuery->where('ho_ten', 'like', "%{$keyword}%")
                        ->orWhere('so_the_doc_gia', 'like', "%{$keyword}%");
                })
                ->orWhereHas('user', function ($userQuery) use ($keyword) {
                    $userQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            });
        }

        if ($request->filled('reservation_code')) {
            $code = trim((string) $request->reservation_code);
            $query->where('reservation_code', 'like', "%{$code}%");
        }

        // Phân trang theo nhóm thay vì theo từng cuốn
        $all = $query->get();
        $grouped = $all->groupBy(function ($reservation) {
            if (!empty($reservation->reservation_code)) {
                return $reservation->reservation_code;
            }
            $pickup = $reservation->pickup_date ? $reservation->pickup_date->format('Ymd') : 'none';
            $return = $reservation->return_date ? $reservation->return_date->format('Ymd') : 'none';
            $time = $reservation->pickup_time ?: 'none';
            $readerKey = $reservation->reader_id ?? $reservation->user_id ?? 'guest';
            return "reader-{$readerKey}-{$pickup}-{$return}-{$time}";
        });

        $totalGroups = $grouped->count();
        $perPage = 20;
        $currentPage = (int) $request->input('page', 1);
        $pagedGroups = $grouped->slice(($currentPage - 1) * $perPage, $perPage);

        $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedGroups,
            $totalGroups,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->except('page')]
        );

        return view('admin.inventory_reservations.index', [
            'reservations' => $paginatedGroups,
        ]);
    }

    public function markAsReady(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        // Log bắt đầu
        \Log::info('markAsReady START', ['reservation_id' => $id, 'time' => now()]);

        // Lấy reservation TRƯỚC để check quá hạn (không cần lock vì chỉ đọc)
        $reservation = InventoryReservation::find($id);
        if (!$reservation) {
            return back()->with('error', 'Không tìm thấy yêu cầu đặt trước.');
        }

        \Log::info('markAsReady - Found reservation', [
            'id' => $id,
            'status' => $reservation->status,
            'book' => $reservation->book_id
        ]);

        if ($reservation->pickup_date && \Carbon\Carbon::parse($reservation->pickup_date)->lt(now()->startOfDay())) {
            return back()->with('error', 'Yêu cầu đã quá hạn ngày lấy. Vui lòng xử lý ở thao tác "Quá hạn".');
        }

        DB::beginTransaction();
        try {
            // Tự chọn 1 bản copy đang có sẵn (lock để không ai khác lấy mất)
            $inventory = Inventory::where('book_id', $reservation->book_id)
                ->where('status', 'Co san')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                DB::rollBack();
                \Log::info('markAsReady - No inventory', ['id' => $id]);
                return back()->with('error', 'Không có bản sao nào đang "Có sẵn" để xác nhận đặt trước.');
            }

            // Kiểm tra status lần 1 trước khi update
            if ($reservation->status !== 'pending') {
                DB::rollBack();
                \Log::info('markAsReady - Status not pending', ['id' => $id, 'status' => $reservation->status]);
                return back()->with('error', 'Yêu cầu này không còn ở trạng thái chờ.');
            }

            \Log::info('markAsReady - About to update', ['id' => $id, 'status_before' => $reservation->status]);

            // UPDATE với điều kiện status = 'pending'
            // dùng ready_at làm cờ chống trùng: nếu ready_at đã có giá trị → đã xử lý rồi
            $affected = InventoryReservation::where('id', $id)
                ->where('status', 'pending')
                ->whereNull('ready_at')
                ->update([
                    'inventory_id' => $inventory->id,
                    'status' => 'ready',
                    'admin_note' => $request->admin_note,
                    'processed_by' => Auth::id(),
                    'ready_at' => now(),
                ]);

            \Log::info('markAsReady - Update result', ['id' => $id, 'affected' => $affected]);

            if ($affected === 0) {
                DB::rollBack();
                \Log::info('markAsReady - Update affected = 0, rolling back', ['id' => $id]);
                return back()->with('error', 'Yêu cầu này không còn ở trạng thái chờ (đã được xử lý bởi request khác).');
            }

            DB::commit();
            \Log::info('markAsReady - Committed', ['id' => $id]);

            // Kiểm tra lại ready_at sau commit — nếu đã có giá trị thì bỏ qua gửi notification
            $reservation->refresh();
            if (!$reservation->ready_at) {
                \Log::info('markAsReady - SKIPPING notification (ready_at is null after refresh)', ['id' => $id]);
            } else {
                $reservation->load(['book', 'reader.user', 'user']);
                \Log::info('markAsReady - SENDING notification', ['id' => $id]);
                app(NotificationService::class)->sendReservationReadyNotification($reservation);
            }

            return redirect()->route('admin.inventory-reservations.proof', $reservation->id)
                ->with('success', 'Đã xác nhận: sách sẵn sàng tại quầy. Vui lòng chụp ảnh chứng minh.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('markAsReady - Exception', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Có lỗi khi xử lý: ' . $e->getMessage());
        }
    }

    public function showProofForm($id)
    {
        $reservation = InventoryReservation::with(['book', 'inventory', 'reader', 'user'])->findOrFail($id);

        return view('admin.inventory_reservations.proof', compact('reservation'));
    }

    public function storeProofImages(Request $request, $id)
    {
        $reservation = InventoryReservation::with(['book', 'inventory', 'reader', 'user'])->findOrFail($id);

        $request->validate([
            'proof_images' => 'required|array|min:1',
            'proof_images.*' => 'file|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $uploadedPaths = [];
        foreach ($request->file('proof_images', []) as $file) {
            if (!$file) {
                continue;
            }

            $result = FileUploadService::uploadImage(
                $file,
                'reservation_proofs',
                [
                    'max_size' => 4096,
                    'resize' => true,
                    'width' => 1200,
                    'height' => 1200,
                    'disk' => 'public',
                ]
            );
            $uploadedPaths[] = $result['path'] ?? null;
        }

        $uploadedPaths = array_values(array_filter($uploadedPaths));
        if (empty($uploadedPaths)) {
            return back()->with('error', 'Không có ảnh hợp lệ để lưu.');
        }

        $existing = is_array($reservation->proof_images) ? $reservation->proof_images : [];
        $reservation->update([
            'proof_images' => array_values(array_unique(array_merge($existing, $uploadedPaths))),
        ]);

        return redirect()->route('admin.inventory-reservations.index')
            ->with('success', 'Đã lưu ảnh chứng minh cho yêu cầu đặt trước.');
    }

    public function markAsFulfilled(Request $request, $id)
    {
        $reservation = InventoryReservation::with(['book', 'inventory', 'reader'])->findOrFail($id);

        if (!in_array($reservation->status, ['ready', 'pending'], true)) {
            return back()->with('error', 'Yêu cầu này không thể hoàn thành.');
        }

        if ($reservation->pickup_date && \Carbon\Carbon::parse($reservation->pickup_date)->lt(now()->startOfDay())) {
            return back()->with('error', 'Yêu cầu đã quá hạn ngày lấy. Vui lòng xử lý ở thao tác "Quá hạn".');
        }

        if ($reservation->pickup_date && $reservation->pickup_date->isSameDay(now())) {
            $openHour = config('library.open_hour', '08:00');
            $closeHour = config('library.close_hour', '20:00');
            $nowTime = now()->format('H:i');
            $targetTime = $reservation->pickup_time ?: $openHour;

            if ($nowTime < $openHour || $nowTime > $closeHour) {
                return back()->with('error', "Chỉ được phát sách trong giờ {$openHour} - {$closeHour}.");
            }
        }

        // Chuyển hướng sang trang tạo phiếu mượn kèm dữ liệu pre-fill
        return redirect()->route('admin.borrows.create', [
            'reader_id' => $reservation->reader_id,
            'book_id' => $reservation->book_id,
            'reservation_id' => $reservation->id,
            'ngay_muon' => $reservation->pickup_date ? $reservation->pickup_date->format('Y-m-d') : now()->format('Y-m-d'),
            'ngay_hen_tra' => $reservation->return_date ? $reservation->return_date->format('Y-m-d') : now()->addDays(14)->format('Y-m-d'),
        ]);
    }

    public function fulfillGroup(Request $request)
    {
        $reservationIds = collect(explode(',', (string) $request->input('reservation_ids', '')))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $allIds = collect(explode(',', (string) $request->input('all_ids', '')))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($reservationIds->isEmpty()) {
            return back()->with('error', 'Vui lòng chọn ít nhất 1 cuốn để Fulfill.');
        }

        $reservations = InventoryReservation::with(['book', 'inventory', 'reader', 'reader.user'])
            ->whereIn('id', $reservationIds->all())
            ->get();

        if ($reservations->isEmpty()) {
            return back()->with('error', 'Không tìm thấy yêu cầu đặt trước hợp lệ.');
        }

        $first = $reservations->first();
        $reader = $first->reader;
        if (!$reader) {
            return back()->with('error', 'Không tìm thấy độc giả cho đơn đặt trước.');
        }

        $pickupDate = $first->pickup_date ? $first->pickup_date->format('Y-m-d') : now()->format('Y-m-d');
        $returnDate = $first->return_date ? $first->return_date->format('Y-m-d') : now()->addDays(14)->format('Y-m-d');
        $borrowCode = $first->reservation_code ?: ('RSV' . now()->format('ymdHis'));

        DB::beginTransaction();
        try {
            $borrow = Borrow::create([
                'reader_id' => $reader->id,
                'librarian_id' => auth()->id(),
                'ten_nguoi_muon' => $reader->ho_ten,
                'so_dien_thoai' => $reader->so_dien_thoai,
                'tinh_thanh' => $reader->tinh_thanh,
                'huyen' => $reader->huyen,
                'xa' => $reader->xa,
                'so_nha' => $reader->so_nha,
                'ngay_muon' => $pickupDate,
                'trang_thai' => 'Cho duyet',
                'trang_thai_chi_tiet' => Borrow::STATUS_DON_HANG_MOI,
                'borrow_code' => $borrowCode,
            ]);

            $totalFee = 0;
            foreach ($reservations as $reservation) {
                if ($reservation->status !== 'ready') {
                    throw new \Exception('Có sách chưa ở trạng thái sẵn sàng.');
                }

                // Lấy giá đã lưu từ khi đặt trước
                $rentalFee = (float) $reservation->total_fee;
                $totalFee += $rentalFee;

                BorrowItem::create([
                    'borrow_id' => $borrow->id,
                    'book_id' => $reservation->book_id,
                    'inventorie_id' => $reservation->inventory_id,
                    'tien_coc' => 0,
                    'tien_thue' => $rentalFee,
                    'tien_ship' => 0,
                    'ngay_muon' => $pickupDate,
                    'ngay_hen_tra' => $returnDate,
                    'trang_thai' => 'Cho duyet',
                    'borrow_type' => 'take_home',
                ]);

                $reservation->update([
                    'status' => 'fulfilled',
                    'borrow_id' => $borrow->id,
                    'processed_by' => auth()->id(),
                    'fulfilled_at' => now(),
                    'inventory_id' => null, // Xóa inventory_id để schedule không đánh overdue
                ]);
            }

            $borrow->update([
                'tien_coc' => 0,
                'tien_ship' => 0,
                'tien_thue' => $totalFee,
                'tong_tien' => $totalFee,
            ]);

            if ($allIds->isNotEmpty()) {
                $toCancel = InventoryReservation::whereIn('id', $allIds->all())
                    ->whereNotIn('id', $reservationIds->all())
                    ->get();

                foreach ($toCancel as $reservation) {
                    $reservation->cancel('Hủy theo yêu cầu loại khỏi đơn.', auth()->id());
                }
            }

            DB::commit();
            return redirect()->route('admin.borrows.index')->with('success', 'Đã Fulfill đơn đặt trước thành 1 phiếu mượn.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Fulfill thất bại: ' . $e->getMessage());
        }
    }

    public function cancelMultiple(Request $request)
    {
        $reservationIds = collect(explode(',', (string) $request->input('reservation_ids', '')))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($reservationIds->isEmpty()) {
            return back()->with('error', 'Vui lòng chọn ít nhất 1 cuốn để hủy.');
        }

        $reservations = InventoryReservation::with(['book', 'inventory', 'reader', 'reader.user', 'user'])
            ->whereIn('id', $reservationIds->all())
            ->get();

        if ($reservations->isEmpty()) {
            return back()->with('error', 'Không tìm thấy yêu cầu đặt trước hợp lệ.');
        }

        DB::beginTransaction();
        try {
            $adminNote = 'Hủy theo yêu cầu. ' . ($request->input('admin_note', ''));
            $cancelledCount = 0;

            foreach ($reservations as $reservation) {
                if (!in_array($reservation->status, ['pending', 'ready', 'fulfilled', 'overdue'], true)) {
                    throw new \Exception('Yêu cầu #' . $reservation->id . ' không thể hủy (trạng thái: ' . $reservation->status . ').');
                }

                // Gọi method cancel để giải phóng inventory
                $reservation->cancel($adminNote, Auth::id());

                $cancelledCount++;
            }

            DB::commit();

            // Gửi thông báo sau khi commit để tránh trùng
            foreach ($reservations as $reservation) {
                $userId = $reservation->reader?->user_id ?? $reservation->user_id;
                if ($userId) {
                    app(NotificationService::class)->sendNotification(
                        $userId,
                        'reservation_cancelled',
                        [
                            'reader_name' => $reservation->reader?->ho_ten ?? ($reservation->user?->name ?? 'Bạn'),
                            'book_title' => $reservation->book?->ten_sach ?? 'Sách',
                            'reason' => $adminNote,
                        ],
                        ['database']
                    );
                }
            }

            return back()->with('success', 'Đã hủy ' . $cancelledCount . ' yêu cầu đặt trước.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Hủy thất bại: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        $reservation = InventoryReservation::with(['book', 'reader', 'user'])->findOrFail($id);

        if (!in_array($reservation->status, ['pending', 'ready', 'fulfilled', 'overdue'], true)) {
            return back()->with('error', 'Yêu cầu này không thể hủy.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
            'mark_overdue' => 'nullable|boolean',
        ]);

        $isMarkOverdue = (bool) $request->boolean('mark_overdue');
        $adminNote = $request->admin_note;

        if ($isMarkOverdue && empty($adminNote)) {
            $adminNote = 'Quá hạn nhận sách: đã qua ngày lấy nhưng khách chưa đến nhận.';
        }

        if ($isMarkOverdue) {
            // markAsOverdue() đã gửi 1 notification_log + email bên trong rồi
            $reservation->markAsOverdue($adminNote, Auth::id());

            return back()->with('success', 'Đã đánh dấu quá hạn cho yêu cầu đặt trước.');
        }

        $reservation->cancel($adminNote, Auth::id());

        return back()->with('success', 'Đã hủy yêu cầu đặt trước.');
    }
}
