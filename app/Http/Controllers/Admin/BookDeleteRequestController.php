<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookDeleteRequest;
use App\Models\Inventory;
use App\Models\BorrowItem;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookDeleteRequestController extends Controller
{
    /**
     * Staff tạo yêu cầu — tự động nhận biết báo hỏng hay xóa sách
     * Nếu gửi kèm inventory_id → báo hỏng (soft delete)
     * Nếu không → xóa sách
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id'      => 'required|exists:books,id',
            'inventory_id' => 'nullable|exists:inventories,id',
            'reason'       => 'nullable|string|max:1000',
            'proof_images' => 'nullable|array|max:6',
            'proof_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $bookId      = (int) $request->book_id;
        $inventoryId = $request->filled('inventory_id') ? (int) $request->inventory_id : null;
        $reason      = $request->reason;

        // Nếu là báo hỏng (có inventory_id) — kiểm tra đã có pending chưa
        if ($inventoryId) {
            $existing = BookDeleteRequest::where('inventory_id', $inventoryId)
                ->where('status', 'pending')
                ->first();

            if ($existing) {
                return back()->with('info', 'Cuốn sách này đã có báo cáo hỏng đang chờ duyệt.');
            }

            // Tự động gắn prefix để phân biệt
            $reason = '[BAO HONG] ' . ($reason ?: 'Sách bị hỏng cần xử lý');
        } else {
            // Kiểm tra đã có pending cho sách này chưa
            $existing = BookDeleteRequest::where('book_id', $bookId)
                ->whereNull('inventory_id')
                ->where('status', 'pending')
                ->first();

            if ($existing) {
                return back()->with('info', 'Sách này đã có yêu cầu xóa đang chờ duyệt.');
            }
        }

        // Upload ảnh minh chứng
        $proofImages = [];
        if ($request->hasFile('proof_images')) {
            foreach ($request->file('proof_images') as $file) {
                if (!$file) continue;
                $upload = FileUploadService::uploadImage($file, 'return_proofs', [
                    'max_size' => 4096,
                    'resize' => true,
                    'width' => 1400,
                    'height' => 1400,
                    'disk' => 'public',
                ]);
                if (!empty($upload['path'])) {
                    $proofImages[] = $upload['path'];
                }
            }
        }

        BookDeleteRequest::create([
            'book_id'      => $bookId,
            'inventory_id' => $inventoryId,
            'requested_by' => Auth::id(),
            'status'       => 'pending',
            'reason'       => $reason,
            'proof_images' => !empty($proofImages) ? $proofImages : null,
        ]);

        $msg = $inventoryId
            ? 'Đã gửi báo hỏng. Chờ Admin xử lý.'
            : 'Đã gửi yêu cầu xóa sách. Vui lòng chờ Admin duyệt.';

        return back()->with('success', $msg);
    }

    /**
     * Admin xem danh sách yêu cầu
     */
    public function index(Request $request)
    {
        $query = BookDeleteRequest::with(['book', 'inventory.book', 'requester', 'approver', 'borrowItem'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            if ($request->type === 'damage') {
                $query->whereNotNull('inventory_id')
                    ->where('reason', 'LIKE', '%[BAO HONG]%');
            } elseif ($request->type === 'lost') {
                $query->whereNotNull('inventory_id')
                    ->where('reason', 'LIKE', '%[BAO MAT]%');
            } else {
                $query->whereNull('inventory_id');
            }
        }

        $requests = $query->paginate(10);

        return view('admin.inventory.delete-requests', compact('requests'));
    }

    /**
     * Admin duyệt yêu cầu
     * - Có inventory_id → BÁO HỎNG: soft delete (cập nhật status inventory)
     * - Không có inventory_id → XÓA SÁCH: hard delete (xóa book + inventory)
     */
    public function approve(Request $request, $id)
    {
        $deleteRequest = BookDeleteRequest::with('book')->findOrFail($id);

        if ($deleteRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $isDamageReport = $deleteRequest->inventory_id !== null;

        // ===== BÁO HỎNG: soft delete = cập nhật trạng thái inventory =====
        if ($isDamageReport) {
            $inventory = Inventory::find($deleteRequest->inventory_id);
            if (!$inventory) {
                return back()->with('error', 'Không tìm thấy cuốn sách trong kho.');
            }

            // Kiểm tra có đang mượn không
            $borrowed = $inventory->borrowItems()
                ->whereIn('trang_thai', ['Dang muon', 'Qua han'])
                ->exists();

            if ($borrowed) {
                return back()->with('error', 'Không thể báo hỏng: Sách đang được mượn.');
            }

            DB::beginTransaction();
            try {
                // Xác định status mới dựa vào lý do
                $reasonText = strtolower($deleteRequest->reason ?? '');
                if (str_contains($reasonText, 'mất')) {
                    $newStatus = 'Mat';
                } else {
                    $newStatus = 'Hong';
                }

                $inventory->update([
                    'status' => $newStatus,
                    'notes'  => trim(($inventory->notes ? $inventory->notes . "\n" : '') . '[Báo hỏng] ' . ($deleteRequest->reason ?? '')),
                ]);

                // Trừ số lượng trong bảng books
                $book = $inventory->book;
                if ($book && $book->so_luong > 0) {
                    $book->decrement('so_luong');
                }

                $deleteRequest->update([
                    'status'      => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'admin_note'  => $request->admin_note,
                ]);

                DB::commit();
                return back()->with('success', 'Đã xử lý báo hỏng: cuốn sách đã được đánh dấu "' . $newStatus . '".');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Có lỗi khi xử lý: ' . $e->getMessage());
            }
        }

        // ===== XÓA SÁCH: hard delete = xóa book + inventory =====
        $book = $deleteRequest->book;
        if (!$book) {
            return back()->with('error', 'Không tìm thấy sách để xóa.');
        }

        $borrowedCount = BorrowItem::where('book_id', $book->id)
            ->where('trang_thai', 'Dang muon')
            ->count();

        if ($borrowedCount > 0) {
            return back()->with('error', 'Không thể xóa: Sách đang được mượn.');
        }

        DB::beginTransaction();
        try {
            Inventory::where('book_id', $book->id)->delete();

            $deleteRequest->update([
                'status'      => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'admin_note'  => $request->admin_note,
            ]);

            $book->delete();

            DB::commit();
            return back()->with('success', 'Đã duyệt và xóa sách (kèm dữ liệu tồn kho) thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi khi duyệt/xóa: ' . $e->getMessage());
        }
    }

    /**
     * Admin từ chối
     */
    public function reject(Request $request, $id)
    {
        $deleteRequest = BookDeleteRequest::with('inventory')->findOrFail($id);

        if ($deleteRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        // Nếu có inventory → đưa về lại trạng thái "có sẵn" khi từ chối
        if ($deleteRequest->inventory_id) {
            $inventory = Inventory::find($deleteRequest->inventory_id);
            if ($inventory) {
                $inventory->update(['status' => 'Co san']);
            }
        }

        $deleteRequest->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'rejected_at' => now(),
            'admin_note'  => $request->admin_note,
        ]);

        return back()->with('success', 'Đã từ chối yêu cầu. Cuốn sách đã được đưa trở lại kho sẵn sàng.');
    }
}
