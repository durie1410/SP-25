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

        // Chuyển hướng sang trang tạo phiếu mượn kèm dữ liệu pre-fill
        return redirect()->route('admin.borrows.create', [
            'reader_id' => $reservation->reader_id,
            'book_id' => $reservation->book_id,
            'reservation_id' => $reservation->id,
            'ngay_muon' => $reservation->pickup_date ? $reservation->pickup_date->format('Y-m-d') : now()->format('Y-m-d'),
            'ngay_hen_tra' => $reservation->return_date ? $reservation->return_date->format('Y-m-d') : now()->addDays(14)->format('Y-m-d'),
        ]);
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
