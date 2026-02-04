<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookDeleteRequest;
use App\Models\Inventory;
use App\Models\BorrowItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookDeleteRequestController extends Controller
{
    /**
     * Nhân viên tạo yêu cầu xóa sách theo book_id
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        $bookId = (int) $request->book_id;

        // Nếu đã có yêu cầu pending cho sách này thì không tạo thêm
        $existing = BookDeleteRequest::where('book_id', $bookId)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('info', 'Sách này đã có yêu cầu xóa đang chờ duyệt.');
        }

        BookDeleteRequest::create([
            'book_id' => $bookId,
            'requested_by' => Auth::id(),
            'status' => 'pending',
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Đã gửi yêu cầu xóa sách. Vui lòng chờ Admin duyệt.');
    }

    /**
     * Admin xem danh sách yêu cầu xóa
     */
    public function index(Request $request)
    {
        $query = BookDeleteRequest::with(['book', 'requester', 'approver'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20);

        return view('admin.inventory.delete-requests', compact('requests'));
    }

    /**
     * Admin duyệt yêu cầu và thực thi xóa
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

        $book = $deleteRequest->book;
        if (!$book) {
            return back()->with('error', 'Không tìm thấy sách để xóa.');
        }

        // Ràng buộc an toàn: nếu đang mượn thì chặn
        $borrowedCount = BorrowItem::where('book_id', $book->id)
            ->where('trang_thai', 'Dang muon')
            ->count();

        if ($borrowedCount > 0) {
            return back()->with('error', 'Không thể xóa: Sách đang được mượn.');
        }

        DB::beginTransaction();
        try {
            // Rule tối ưu: nếu còn tồn kho thì xóa toàn bộ inventory liên quan trước khi xóa book
            Inventory::where('book_id', $book->id)->delete();

            $deleteRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'admin_note' => $request->admin_note,
            ]);

            // Xóa sách
            $book->delete();

            DB::commit();
            return back()->with('success', 'Đã duyệt yêu cầu và xóa sách (kèm dữ liệu tồn kho) thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi khi duyệt/xóa: ' . $e->getMessage());
        }
    }

    /**
     * Admin từ chối yêu cầu xóa
     */
    public function reject(Request $request, $id)
    {
        $deleteRequest = BookDeleteRequest::findOrFail($id);

        if ($deleteRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $deleteRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'rejected_at' => now(),
            'admin_note' => $request->admin_note,
        ]);

        return back()->with('success', 'Đã từ chối yêu cầu xóa sách.');
    }
}
