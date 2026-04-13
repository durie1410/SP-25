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
use Illuminate\Support\Facades\Log;

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
        Log::info('markAsReady START', ['reservation_id' => $id, 'time' => now()]);

        // Lấy reservation TRƯỚC để check quá hạn (không cần lock vì chỉ đọc)
        $reservation = InventoryReservation::find($id);
        if (!$reservation) {
            return back()->with('error', 'Không tìm thấy yêu cầu đặt trước.');
        }

        Log::info('markAsReady - Found reservation', [
            'id' => $id,
            'status' => $reservation->status,
            'book' => $reservation->book_id
        ]);

        if ($reservation->pickup_date && \Carbon\Carbon::parse($reservation->pickup_date)->lt(now()->startOfDay())) {
            return back()->with('error', 'Yêu cầu đã quá hạn ngày lấy. Vui lòng xử lý ở thao tác "Quá hạn".');
        }

        if ($reservation->status === 'ready') {
            return redirect()->route('admin.inventory-reservations.proof', $reservation->id)
                ->with('success', 'Đơn đã sẵn sàng. Bạn có thể xem/thêm ảnh chứng minh tại đây.');
        }

        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này không còn ở trạng thái chờ.');
        }

        if ($reservation->inventory_id && is_null($reservation->ready_at)) {
            return redirect()->route('admin.inventory-reservations.proof', $reservation->id)
                ->with('success', 'Đã gán bản sao. Vui lòng tải ảnh chứng minh để hoàn tất xác nhận Ready.');
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
                Log::info('markAsReady - No inventory', ['id' => $id]);
                return back()->with('error', 'Không có bản sao nào đang "Có sẵn" để xác nhận đặt trước.');
            }

            Log::info('markAsReady - About to update', ['id' => $id, 'status_before' => $reservation->status]);

            // Chỉ gán bản sao và chuyển sang bước chụp ảnh.
            // Trạng thái ready sẽ được chốt sau khi ảnh chứng minh được lưu thành công.
            $affected = InventoryReservation::where('id', $id)
                ->where('status', 'pending')
                ->whereNull('ready_at')
                ->whereNull('inventory_id')
                ->update([
                    'inventory_id' => $inventory->id,
                    'admin_note' => $request->admin_note,
                    'processed_by' => Auth::id(),
                ]);

            Log::info('markAsReady - Update result', ['id' => $id, 'affected' => $affected]);

            if ($affected === 0) {
                DB::rollBack();
                Log::info('markAsReady - Update affected = 0, rolling back', ['id' => $id]);
                return back()->with('error', 'Yêu cầu này không còn ở trạng thái chờ (đã được xử lý bởi request khác).');
            }

            DB::commit();
            Log::info('markAsReady - Committed', ['id' => $id]);

            return redirect()->route('admin.inventory-reservations.proof', $reservation->id)
                ->with('success', 'Đã gán bản sao. Vui lòng tải ảnh chứng minh để hoàn tất xác nhận Ready.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('markAsReady - Exception', ['id' => $id, 'error' => $e->getMessage()]);
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

        if ($reservation->status === 'pending' && !$reservation->inventory_id) {
            return back()->with('error', 'Vui lòng nhấn Ready để gán bản sao trước khi tải ảnh chứng minh.');
        }

        $files = $request->file('proof_images', []);
        if (!is_array($files)) {
            $files = [];
        }

        if (empty($files)) {
            return back()
                ->withInput()
                ->withErrors(['proof_images' => 'Vui lòng tải lên ít nhất 1 ảnh chứng minh.']);
        }

        // Validate cơ bản - KHÔNG dùng 'image|mimes|file' vì chúng gọi isValid()
        // gây lỗi "tải lên thất bại" khi file upload PHP có vấn đề.
        // Thay vào đó kiểm tra thủ công bên dưới.
        $request->validate([
            'proof_images' => 'required|array|min:1',
        ], [
            'proof_images.required' => 'Vui lòng tải lên ít nhất 1 ảnh chứng minh.',
            'proof_images.array' => 'Dữ liệu ảnh chứng minh không hợp lệ.',
            'proof_images.min' => 'Vui lòng tải lên ít nhất 1 ảnh chứng minh.',
        ]);

        $uploadedPaths = [];
        $failedCount = 0;
        $uploadErrors = [];

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSizeKb = 4096;

        foreach ($request->file('proof_images', []) as $index => $file) {
            // Bước 1: Kiểm tra file có tồn tại trong request không
            if (!$file) {
                $failedCount++;
                $uploadErrors[] = "File thứ " . ($index + 1) . " không có trong yêu cầu.";
                continue;
            }

            // Bước 2: Kiểm tra isValid() - lỗi upload PHP
            if (!$file->isValid()) {
                $errorCode = $file->getError();
                $failedCount++;
                $uploadErrors[] = "Trường proof_images.{$index} tải lên thất bại (mã lỗi: {$errorCode}). ";
                Log::warning('File upload PHP error', [
                    'index'      => $index,
                    'error_code' => $errorCode,
                    'name'       => $file->getClientOriginalName(),
                ]);
                continue;
            }

            // Bước 3: Kiểm tra MIME type thủ công
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, $allowedMimes)) {
                $failedCount++;
                $uploadErrors[] = "File \"{$file->getClientOriginalName()}\" không đúng định dạng ảnh (chỉ chấp nhận JPG, PNG, GIF, WebP).";
                continue;
            }

            // Bước 4: Kiểm tra kích thước thủ công (4MB = 4096KB)
            $fileSizeKb = $file->getSize() / 1024;
            if ($fileSizeKb > $maxSizeKb) {
                $failedCount++;
                $uploadErrors[] = "File \"{$file->getClientOriginalName()}\" vượt quá 4MB.";
                continue;
            }

            // Bước 5: Upload qua FileUploadService
            try {
                $result = FileUploadService::uploadImage(
                    $file,
                    'reservation_proofs',
                    [
                        'max_size' => $maxSizeKb,
                        'resize'   => true,
                        'width'    => 1200,
                        'height'   => 1200,
                        'disk'     => 'public',
                    ]
                );
                if (!empty($result['path'])) {
                    $uploadedPaths[] = $result['path'];
                    $this->syncPublicStorageMirror($result['path']);
                }
            } catch (\Exception $e) {
                Log::warning('Upload ảnh thất bại', [
                    'file'  => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
                $uploadErrors[] = "Không thể lưu ảnh \"{$file->getClientOriginalName()}\": " . $e->getMessage();
            }
        }

        // Nếu tất cả đều thất bại
        if (empty($uploadedPaths)) {
            $errorMsg = 'Không có ảnh nào được tải lên thành công.';
            if (!empty($uploadErrors)) {
                $errorMsg .= ' Chi tiết: ' . implode(' ', $uploadErrors);
            }
            return back()->with('error', $errorMsg);
        }

        $existing = is_array($reservation->proof_images) ? $reservation->proof_images : [];
        $reservation->update([
            'proof_images' => array_values(array_unique(array_merge($existing, $uploadedPaths))),
        ]);

        if ($reservation->status === 'pending') {
            $reservation->update([
                'status' => 'ready',
                'processed_by' => Auth::id(),
                'ready_at' => now(),
            ]);

            $reservation->refresh()->load(['book', 'reader.user', 'user']);
            app(NotificationService::class)->sendReservationReadyNotification($reservation);
        }

        return redirect()->route('admin.inventory-reservations.index')
            ->with('success', 'Đã lưu ' . count($uploadedPaths) . ' ảnh chứng minh cho yêu cầu đặt trước.');
    }

    private function syncPublicStorageMirror(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $normalized = ltrim(str_replace('\\', '/', (string) $relativePath), '/');
        $source = storage_path('app/public/' . $normalized);
        $target = public_path('storage/' . $normalized);

        if (!is_file($source)) {
            return;
        }

        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        // Keep public/storage mirror updated in environments without storage symlink.
        @copy($source, $target);
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

        if (count($reservation->getProofImages()) < 1) {
            return redirect()->route('admin.inventory-reservations.proof', $reservation->id)
                ->with('error', 'Không thể xác nhận đơn khi chưa có ảnh chứng minh. Vui lòng tải lên ít nhất 1 ảnh.');
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

                if (count($reservation->getProofImages()) < 1) {
                    $bookTitle = $reservation->book?->ten_sach ?? ('#' . $reservation->id);
                    throw new \Exception('Sách "' . $bookTitle . '" chưa có ảnh chứng minh nên không thể Fulfill.');
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
