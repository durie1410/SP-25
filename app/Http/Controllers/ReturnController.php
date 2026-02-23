<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use App\Models\BorrowItem;
use App\Models\Borrow;
use App\Models\Fine;
use App\Models\Inventory;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReturnController extends Controller
{
    /**
     * Giao diện màn hình trả sách
     */
    public function index(Request $request)
    {
        $readers = [];
        $selectedReader = null;
        $borrowItems = [];

        if ($request->filled('search')) {
            $keyword = $request->search;
            $readers = Reader::where('ho_ten', 'like', "%{$keyword}%")
                ->orWhere('so_the_doc_gia', 'like', "%{$keyword}%")
                ->get();
        }

        if ($request->filled('reader_id')) {
            $selectedReader = Reader::with(['user'])->findOrFail($request->reader_id);
            $borrowItems = BorrowItem::with(['book', 'borrow'])
                ->whereHas('borrow', function($q) use ($selectedReader) {
                    $q->where('reader_id', $selectedReader->id);
                })
                ->where('trang_thai', 'Dang muon')
                ->get();
        }

        return view('admin.returns.index', compact('readers', 'selectedReader', 'borrowItems'));
    }

    /**
     * Xử lý trả sách (tick chọn nhiều quyển)
     */
    public function processReturn(Request $request)
    {
        $request->validate([
            'reader_id' => 'required|exists:readers,id',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:borrow_items,id',
            'items.*.selected' => 'nullable|in:1',
            'items.*.condition' => 'nullable|in:binh_thuong,hong_nhe,hong_nang,mat_sach',
        ]);

        $reader = Reader::findOrFail($request->reader_id);
        $returnDate = now();
        $processedBorrows = [];

        // Lọc các item được chọn
        $selectedItems = collect($request->items)->filter(function ($it) {
            return isset($it['selected']) && (string) $it['selected'] === '1';
        })->values();

        if ($selectedItems->isEmpty()) {
            return back()->with('error', 'Vui lòng tick chọn ít nhất 1 quyển sách để trả.');
        }

        try {
            DB::beginTransaction();

            foreach ($selectedItems as $itemData) {
                $item = BorrowItem::with(['book', 'inventory', 'borrow'])->findOrFail($itemData['id']);

                // đảm bảo item thuộc đúng độc giả và đang mượn
                if (!$item->borrow || (int) $item->borrow->reader_id !== (int) $reader->id) {
                    throw new \Exception('Sách không thuộc khách đã chọn.');
                }
                if ($item->trang_thai !== 'Dang muon') {
                    continue; // tránh trả trùng
                }

                $condition = $itemData['condition'] ?? 'binh_thuong';
                
                // 1. Tính phạt quá hạn
                $lateFine = 0;
                if ($item->ngay_hen_tra && Carbon::parse($item->ngay_hen_tra)->startOfDay() < $returnDate->startOfDay()) {
                    $lateFine = PricingService::calculateLateReturnFine($item->ngay_hen_tra, $returnDate, 1);
                }

                // 2. Tính phạt hư hỏng/mất
                $damageFine = 0;
                $inventoryStatus = 'Co san';
                $itemStatus = 'Da tra';

                if ($condition !== 'binh_thuong') {
                    $bookPrice = $item->book->gia ?? 0;
                    $bookType = $item->book->loai_sach ?? 'binh_thuong';
                    $startCondition = $item->inventory->condition ?? 'Trung binh';

                    if ($condition === 'mat_sach') {
                        $damageFine = PricingService::calculateLostBookFine($bookPrice, $bookType, $startCondition);
                        $inventoryStatus = 'Mat';
                        $itemStatus = 'Mat sach';
                        if ($item->book) $item->book->decrement('so_luong');
                    } else {
                        $damageFine = PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition);
                        $inventoryStatus = 'Hong';
                        $itemStatus = 'Hong';
                    }
                }

                // 3. Cập nhật BorrowItem
                $item->update([
                    'trang_thai' => $itemStatus,
                    'ngay_tra_thuc_te' => $returnDate->toDateString(),
                    'tinh_trang_sach_cuoi' => $condition,
                    'tien_phat' => $lateFine + $damageFine,
                ]);

                // 4. Cập nhật Inventory
                if ($item->inventory) {
                    $item->inventory->update(['status' => $inventoryStatus]);
                }

                // 5. Tạo các bản ghi Fine pending
                if ($lateFine > 0) {
                    Fine::create([
                        'borrow_id' => $item->borrow_id,
                        'borrow_item_id' => $item->id,
                        'reader_id' => $reader->id,
                        'amount' => $lateFine,
                        'type' => 'late_return',
                        'description' => "Phạt trễ hạn sách: {$item->book->ten_sach}",
                        'status' => 'pending',
                        'due_date' => $returnDate->toDateString(),
                        'created_by' => auth()->id() ?? 1,
                    ]);
                }

                if ($damageFine > 0) {
                    Fine::create([
                        'borrow_id' => $item->borrow_id,
                        'borrow_item_id' => $item->id,
                        'reader_id' => $reader->id,
                        'amount' => $damageFine,
                        'type' => $condition === 'mat_sach' ? 'lost_book' : 'damaged_book',
                        'description' => "Phạt " . ($condition === 'mat_sach' ? 'mất' : 'hỏng') . " sách: {$item->book->ten_sach}",
                        'status' => 'pending',
                        'due_date' => $returnDate->toDateString(),
                        'created_by' => auth()->id(),
                    ]);
                }

                $processedBorrows[$item->borrow_id] = $item->borrow_id;
            }

            // 6. Kiểm tra và cập nhật trạng thái Borrow (Option A)
            foreach ($processedBorrows as $borrowId) {
                $borrow = Borrow::with('items')->find($borrowId);
                if ($borrow) {
                    $borrow->recalculateTotals();
                    
                    // Nếu tất cả items đã trả/hỏng/mất -> chuyển trạng thái borrow
                    $remainingItems = $borrow->items()->where('trang_thai', 'Dang muon')->count();
                    if ($remainingItems === 0) {
                        $borrow->update(['trang_thai' => 'Da tra']);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.fine-payments.index', ['reader_id' => $reader->id])
                ->with('success', 'Đã ghi nhận trả sách. Vui lòng thực hiện thanh toán các khoản phạt phát sinh (nếu có).');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi xử lý trả sách: ' . $e->getMessage());
        }
    }
}
