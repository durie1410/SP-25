<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Services\NotificationService;
use App\Services\PricingService;
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
            $query->where('status', $request->status);
        }

        $reservations = $query->paginate(20);

        return view('admin.inventory_reservations.index', compact('reservations'));
    }

    public function markAsReady(Request $request, $id)
    {
        $reservation = InventoryReservation::with(['book', 'reader.user'])->findOrFail($id);

        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này không còn ở trạng thái chờ.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Tự chọn 1 bản copy đang có sẵn
            $inventory = Inventory::where('book_id', $reservation->book_id)
                ->where('status', 'Co san')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                DB::rollBack();
                return back()->with('error', 'Không có bản sao nào đang "Có sẵn" để xác nhận đặt trước.');
            }

            $reservation->update([
                'inventory_id' => $inventory->id,
                'status' => 'ready',
                'admin_note' => $request->admin_note,
                'processed_by' => Auth::id(),
                'ready_at' => now(),
            ]);

            // Gửi thông báo cho user (database + email theo cấu hình mặc định)
            $userId = $reservation->reader?->user_id ?? $reservation->user_id;
            if ($userId) {
                $notification = app(NotificationService::class);
                $notification->sendNotification(
                    $userId,
                    'reservation_ready',
                    [
                        'reader_name' => $reservation->reader?->ho_ten ?? ($reservation->user?->name ?? 'Bạn'),
                        'book_title' => $reservation->book?->ten_sach ?? 'Sách',
                        'ready_date' => now()->format('d/m/Y H:i'),
                        'expiry_date' => now()->addDays(3)->format('d/m/Y'),
                    ],
                    ['database']
                );
            }

            DB::commit();
            return back()->with('success', 'Đã xác nhận: sách sẵn sàng tại quầy.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi khi xử lý: ' . $e->getMessage());
        }
    }

    public function markAsFulfilled(Request $request, $id)
    {
        $reservation = InventoryReservation::with(['book', 'inventory', 'reader'])->findOrFail($id);

        if (!in_array($reservation->status, ['ready', 'pending'], true)) {
            return back()->with('error', 'Yêu cầu này không thể hoàn thành.');
        }

        $request->validate([
            'borrow_type' => 'nullable|in:take_home,onsite',
            'ngay_muon' => 'nullable|date',
            'ngay_hen_tra' => 'nullable|date',
        ]);

        // Nếu đã chuyển sang phiếu mượn rồi thì không tạo lại
        if ($reservation->borrow_id) {
            return back()->with('info', 'Yêu cầu này đã được chuyển sang phiếu mượn trước đó.');
        }

        DB::beginTransaction();
        try {
            // Ensure inventory is assigned. If pending, try auto assign like ready.
            $inventory = $reservation->inventory;
            if (!$inventory) {
                $inventory = Inventory::where('book_id', $reservation->book_id)
                    ->where('status', 'Co san')
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    DB::rollBack();
                    return back()->with('error', 'Không có bản sao nào đang "Có sẵn" để tạo phiếu mượn.');
                }

                $reservation->update([
                    'inventory_id' => $inventory->id,
                    'status' => 'ready',
                    'processed_by' => Auth::id(),
                    'ready_at' => now(),
                ]);
            }

            $borrowType = $request->input('borrow_type', 'take_home');
            $ngayMuon = $request->input('ngay_muon', now()->toDateString());
            $ngayHenTra = $request->input('ngay_hen_tra', now()->addDays(14)->toDateString());

            // Tạo Borrow header (tối thiểu các field bắt buộc)
            $reader = $reservation->reader;
            $borrow = Borrow::create([
                'reader_id' => $reservation->reader_id,
                'librarian_id' => Auth::id(),
                'ten_nguoi_muon' => $reader?->ho_ten ?? ($reservation->user?->name ?? 'Độc giả'),
                'so_dien_thoai' => $reader?->so_dien_thoai ?? ($reservation->user?->so_dien_thoai ?? ''),
                'tinh_thanh' => $reader?->tinh_thanh ?? '',
                'huyen' => $reader?->huyen ?? '',
                'xa' => $reader?->xa ?? '',
                'so_nha' => $reader?->so_nha ?? '',
                'ngay_muon' => $ngayMuon,
                'trang_thai' => 'Dang muon',
            ]);

            // Tính phí theo policy
            $fees = PricingService::calculateFees(
                $reservation->book,
                $inventory,
                $ngayMuon,
                $ngayHenTra,
                (bool) $reader
            );

            BorrowItem::create([
                'borrow_id' => $borrow->id,
                'book_id' => $reservation->book_id,
                'inventorie_id' => $inventory->id,
                'tien_coc' => $borrowType === 'onsite' ? 0 : $fees['tien_coc'],
                'tien_thue' => $fees['tien_thue'],
                'tien_ship' => 0,
                'tien_phat' => 0,
                'ngay_muon' => $ngayMuon,
                'ngay_hen_tra' => $ngayHenTra,
                'trang_thai' => 'Dang muon',
                'borrow_type' => $borrowType,
                'trang_thai_coc' => $borrowType === 'onsite' ? 'da_hoan' : 'da_thu',
                'tien_coc_da_thu' => $borrowType === 'onsite' ? 0 : ($fees['tien_coc'] ?? 0),
                'ngay_thu_coc' => $borrowType === 'onsite' ? null : $ngayMuon,
            ]);

            // Update inventory status
            Inventory::where('id', $inventory->id)->update([
                'status' => 'Dang muon',
                'updated_at' => now(),
            ]);

            $borrow->recalculateTotals();

            // Link back
            $reservation->update([
                'status' => 'fulfilled',
                'borrow_id' => $borrow->id,
                'processed_by' => Auth::id(),
                'fulfilled_at' => now(),
            ]);

            DB::commit();
            return back()->with('success', 'Đã tạo phiếu mượn #' . $borrow->id . ' từ yêu cầu đặt trước.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi khi tạo phiếu mượn: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        $reservation = InventoryReservation::findOrFail($id);

        if (!in_array($reservation->status, ['pending', 'ready'], true)) {
            return back()->with('error', 'Yêu cầu này không thể hủy.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $reservation->update([
            'status' => 'cancelled',
            'admin_note' => $request->admin_note,
            'processed_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Đã hủy yêu cầu đặt trước.');
    }
}
