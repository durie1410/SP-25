<?php

namespace App\Http\Controllers;

use App\Models\BorrowItem;
use App\Models\Book;
use App\Models\Fine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BorrowItemController extends Controller
{
    /**
     * Hiển thị form chỉnh sửa chi tiết mượn sách
     */
    public function edit($id)
    {
        $item = BorrowItem::with(['book', 'borrow.reader'])->findOrFail($id);
        $books = Book::all();

        return view('admin.borrowsitem.edit', compact('item', 'books'));
    }

    /**
     * Cập nhật chi tiết mượn sách
     */
public function update(Request $request, $id)
{
    $item = BorrowItem::findOrFail($id);

    // Validate dữ liệu, bỏ 'trang_thai' khỏi form để tránh ghi đè
    $validated = $request->except('trang_thai');
    $validated += $request->validate([
        'book_id' => 'required|exists:books,id',
        'ngay_muon' => 'required|date',
        'ngay_hen_tra' => 'required|date|after_or_equal:ngay_muon',
        'tien_coc' => 'required|numeric|min:0',
        'tien_thue' => 'nullable|numeric|min:0',
        'tien_ship' => 'required|numeric|min:0',
        'trang_thai_coc' => 'required|in:cho_xu_ly,da_thu,da_hoan,tru_vao_phat',
        'so_lan_gia_han' => 'nullable|integer|min:0',
        'ghi_chu' => 'nullable|string',
    ]);

    // 1️⃣ Cập nhật BorrowItem
    $item->update($validated);

    // 2️⃣ Tự động đánh dấu quá hạn
    if ($item->ngay_hen_tra && now()->diffInDays($item->ngay_hen_tra, false) < 0) {
        $item->trang_thai = 'Qua han';
        $item->save();
    }

    // 3️⃣ Cập nhật tổng thông tin phiếu mượn
    $borrow = $item->borrow;
    if ($borrow) {
        $borrow->tien_thue = $borrow->borrowItems()->sum('tien_thue');
        $borrow->tien_coc  = $borrow->borrowItems()->sum('tien_coc');
        $borrow->tien_ship = $borrow->borrowItems()->sum('tien_ship');
        $borrow->tong_tien = $borrow->tien_thue + $borrow->tien_coc + $borrow->tien_ship;

        // Cập nhật trạng thái phiếu dựa trên borrow_items
        $statuses = $borrow->borrowItems()->pluck('trang_thai')->toArray();
        if (in_array('Mat sach', $statuses)) {
            $borrow->trang_thai = 'Mat sach';
        } elseif (in_array('Qua han', $statuses)) {
            $borrow->trang_thai = 'Qua han';
        } elseif (in_array('Cho duyet', $statuses) || in_array('Chua nhan', $statuses)) {
            $borrow->trang_thai = 'Cho duyet';
        } elseif (in_array('Dang muon', $statuses)) {
            $borrow->trang_thai = 'Dang muon';
        } elseif (in_array('Huy', $statuses) && count(array_unique($statuses)) === 1) {
            // Nếu TẤT CẢ items đều bị hủy
            $borrow->trang_thai = 'Huy';
        } else {
            $borrow->trang_thai = 'Da tra';
        }

        $borrow->save();
    }

    return redirect()
        ->route('admin.borrowitems.show', $item->id)
        ->with('success', 'Cập nhật chi tiết mượn sách thành công.');
}


    /**
     * Hiển thị thông tin chi tiết 1 BorrowItem
     */
   public function show($id)
{
    $borrowItem = BorrowItem::with(['borrow.reader', 'book'])->findOrFail($id);

    $today = Carbon::today(); // giờ = 00:00:00
    $borrowDate = $borrowItem->ngay_muon->copy()->startOfDay();
    $dueDate = $borrowItem->ngay_hen_tra->copy()->startOfDay();

    // Số ngày còn lại (dương = còn hạn, 0 = hết hạn hôm nay, âm = quá hạn)
    $borrowItem->days_remaining = $dueDate->diffInDays($today, false) * -1;

    return view('admin.borrowsitem.show', compact('borrowItem', 'borrowDate', 'dueDate'));
}

public function approve($id)
{
    try {
        $item = \App\Models\BorrowItem::findOrFail($id);

        \Log::info('=== BẮT ĐẦU DUYỆT ===', [
            'item_id' => $id,
            'trang_thai_ban_dau' => $item->trang_thai,
            'borrow_id' => $item->borrow_id,
            'inventorie_id' => $item->inventorie_id
        ]);

        if ($item->trang_thai !== 'Cho duyet') {
            \Log::warning('Trạng thái không hợp lệ', ['trang_thai' => $item->trang_thai]);
            return back()->with('error', 'Trạng thái không hợp lệ để duyệt. Trạng thái hiện tại: ' . $item->trang_thai);
        }

        // Sử dụng DB transaction
        \DB::beginTransaction();

        try {
            // Cập nhật trạng thái item
            $affected = \DB::table('borrow_items')
                ->where('id', $item->id)
                ->update([
                    'trang_thai' => 'Dang muon',
                    'ngay_muon' => $item->ngay_muon ?? now(),
                    'updated_at' => now()
                ]);

            \Log::info('Đã update item', ['affected_rows' => $affected]);

            // Kiểm tra lại
            $checkItem = \DB::table('borrow_items')->where('id', $id)->first();
            \Log::info('Kiểm tra sau update', ['trang_thai' => $checkItem->trang_thai]);

            // Cập nhật inventory nếu có (chú ý: tên cột là inventorie_id, không phải inventory_id)
            if (isset($checkItem->inventorie_id) && $checkItem->inventorie_id) {
                $invAffected = \DB::table('inventories')
                    ->where('id', $checkItem->inventorie_id)
                    ->update([
                        'status' => 'Dang muon',
                        'updated_at' => now()
                    ]);
                \Log::info('Đã update inventory', ['affected_rows' => $invAffected]);
            }

            // Cập nhật trạng thái Borrow
            $statuses = \DB::table('borrow_items')
                ->where('borrow_id', $checkItem->borrow_id)
                ->pluck('trang_thai')
                ->toArray();
            
            \Log::info('Tất cả trạng thái items', ['statuses' => $statuses]);
            
            // Xác định trạng thái mới cho Borrow
            $newBorrowStatus = 'Da tra';
            if (in_array('Mat sach', $statuses)) {
                $newBorrowStatus = 'Mat sach';
            } elseif (in_array('Qua han', $statuses)) {
                $newBorrowStatus = 'Qua han';
            } elseif (in_array('Cho duyet', $statuses) || in_array('Chua nhan', $statuses)) {
                $newBorrowStatus = 'Cho duyet';
            } elseif (in_array('Dang muon', $statuses)) {
                $newBorrowStatus = 'Dang muon';
            }
            
            $borrowAffected = \DB::table('borrows')
                ->where('id', $checkItem->borrow_id)
                ->update([
                    'trang_thai' => $newBorrowStatus,
                    'updated_at' => now()
                ]);
                
            \Log::info('Đã update borrow', [
                'borrow_id' => $checkItem->borrow_id,
                'new_status' => $newBorrowStatus,
                'affected_rows' => $borrowAffected
            ]);

            \DB::commit();
            \Log::info('=== COMMIT THÀNH CÔNG ===');
            
            // Clear cache
            \Cache::flush();
            
        } catch (\Exception $innerEx) {
            \DB::rollBack();
            \Log::error('Lỗi trong transaction', [
                'error' => $innerEx->getMessage(),
                'trace' => $innerEx->getTraceAsString()
            ]);
            throw $innerEx;
        }

        // Redirect về trang trước với thông báo thành công
        $referer = request()->header('referer');
        if ($referer && str_contains($referer, '/admin/borrows/') && str_contains($referer, '/edit')) {
            return redirect()->route('admin.borrows.edit', $checkItem->borrow_id)
                ->with('success', '✅ Đã duyệt thành công! Sách chuyển sang trạng thái "Đang mượn".')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } else {
            return redirect()->route('admin.borrows.index')
                ->with('success', '✅ Đã duyệt thành công! Sách chuyển sang trạng thái "Đang mượn".')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Lỗi khi duyệt borrow item: ' . $e->getMessage(), [
            'item_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Có lỗi xảy ra khi duyệt: ' . $e->getMessage());
    }
}
public function changeStatus($id)
{
    try {
        $item = BorrowItem::with('borrow', 'inventory')->findOrFail($id);

        // Kiểm tra trạng thái hiện tại phải là 'Chua nhan' mới được chuyển
        if ($item->trang_thai !== 'Chua nhan') {
            return back()->with('error', 'Chỉ có thể chuyển từ trạng thái "Chưa nhận" sang "Đang mượn". Trạng thái hiện tại: ' . $item->trang_thai);
        }

        // Sử dụng DB transaction để đảm bảo tính nhất quán
        \DB::beginTransaction();

        // Chuyển trạng thái sang "Dang muon"
        $updated = $item->update([
            'trang_thai' => 'Dang muon',
        ]);

        if (!$updated) {
            \DB::rollBack();
            return back()->with('error', 'Không thể cập nhật trạng thái item.');
        }

        // Reload lại item để đảm bảo có dữ liệu mới nhất
        $item->refresh();

        // Cập nhật trạng thái inventory nếu có
        if ($item->inventory && $item->inventory->status !== 'Dang muon') {
            $item->inventory->update([
                'status' => 'Dang muon'
            ]);
        }

        // Cập nhật trạng thái của Borrow dựa trên các items
        $borrow = $item->borrow;
        if ($borrow) {
            // Reload lại relationship để lấy dữ liệu mới nhất
            $borrow->refresh();
            $borrow->load('items');
            $statuses = $borrow->items->pluck('trang_thai')->toArray();
            
            // Xác định trạng thái của Borrow dựa trên tất cả items
            if (in_array('Mat sach', $statuses)) {
                $borrow->trang_thai = 'Mat sach';
            } elseif (in_array('Qua han', $statuses)) {
                $borrow->trang_thai = 'Qua han';
            } elseif (in_array('Cho duyet', $statuses) || in_array('Chua nhan', $statuses)) {
                // Nếu còn item chờ duyệt hoặc chưa nhận, giữ trạng thái Cho duyet
                $borrow->trang_thai = 'Cho duyet';
            } elseif (in_array('Dang muon', $statuses)) {
                // Nếu có ít nhất 1 item đang mượn, chuyển sang Dang muon
                $borrow->trang_thai = 'Dang muon';
            } else {
                // Tất cả items đã trả
                $borrow->trang_thai = 'Da tra';
            }
            $borrow->save();
        }

        \DB::commit();

        return back()->with('success', 'Đã chuyển sang trạng thái "Đang mượn" thành công!');
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Lỗi khi chuyển trạng thái borrow item: ' . $e->getMessage(), [
            'item_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Có lỗi xảy ra khi chuyển trạng thái: ' . $e->getMessage());
    }
}











public function markLost($id)
{
    $item = BorrowItem::findOrFail($id);
    $book = $item->book;
    $inventory = $item->inventory;

    $gia = $book->gia;
    $loai = $book->loai_sach;              // binh_thuong | quy | tham_khao
    $tinhTrang = $inventory->condition;    // Moi, Tot, Trung binh, Cu, Hong
    $tienCoc = $item->tien_coc;            // cọc đã thu lúc mượn

    /** ---- 1. TÍNH TIỀN PHẠT BAN ĐẦU ---- **/
    if ($loai === 'quy') {
        // Sách quý phạt 100%
        $phatGoc = $gia;
    } else {
        switch ($tinhTrang) {
            case 'Moi':
            case 'Tot':
                $phatGoc = round($gia * 0.8);
                break;

            case 'Trung binh':
            case 'Cu':
            case 'Hong':
                $phatGoc = round($gia * 0.7);
                break;

            default:
                $phatGoc = round($gia * 0.7);
        }
    }

    /** ---- 2. Cập nhật BorrowItem ---- **/
    $item->update([
        'trang_thai' => 'Mat sach',
        'tien_phat'  => $phatGoc
    ]);

    /** ---- 4. Cập nhật inventory ---- **/
    if ($inventory) {
        $inventory->update([
            'status' => 'Mat'
        ]);
    }

    /** ---- 5. Trừ số lượng sách ---- **/
    if ($book->so_luong > 0) {
        $book->decrement('so_luong');
    }

    /** ---- 6. Ghi vào bảng fines với đầy đủ thông tin ---- **/
    $request = request();
    
    $damageDescription = $request->input('damage_description', 'Khách hàng báo mất sách, không thể trả lại.');
    
    // Xử lý upload ảnh nếu có
    $damageImages = [];
    if ($request->hasFile('damage_images')) {
        foreach ($request->file('damage_images') as $image) {
            $path = $image->store('fines/damage_images', 'public');
            $damageImages[] = $path;
        }
    }
    
    Fine::create([
        'borrow_id'      => $item->borrow_id,
        'borrow_item_id' => $item->id,
        'reader_id'      => $item->borrow->reader_id,
        'amount'         => $phatGoc,
        'type'           => 'lost_book',
        'description'    => 'Mất sách: '.$book->ten_sach.', tình trạng: '.$tinhTrang,
        'damage_description' => $damageDescription,
        'damage_images' => !empty($damageImages) ? $damageImages : null,
        'damage_severity' => 'mat_sach',
        'damage_type' => 'mat_sach',
        'condition_before' => $tinhTrang,
        'condition_after' => 'Mat',
        'inspection_notes' => $request->input('inspection_notes', 'Báo mất sách bởi admin'),
        'inspected_by' => auth()->id(),
        'inspected_at' => now(),
        'status'         => 'pending',
        'due_date'       => now()->addDays(30),
        'created_by'     => auth()->id()
    ]);

    return back()->with(
        'success',
        "Báo mất sách thành công! Phạt: " . number_format($phatGoc) . "đ"
    );
}









public function reportDamage($id)
{
    $item = BorrowItem::with(['book', 'inventory', 'borrow'])->findOrFail($id);

    $book = $item->book;
    $inventory = $item->inventory;

    $gia = $book->gia;
    $loai = $book->loai_sach;           // bình_thuong | quy | tham_khao
    $tinhTrangKhiMuon = $inventory->condition;   // Moi | Tot | Trung binh | Cu | Hong
    $tienCoc = $item->tien_coc;

    /**
     * ----------------------------
     * 1. TÍNH TIỀN PHẠT
     * ----------------------------
     * Bạn yêu cầu:
     *  - Sách bình thường: 80% nếu mới/tốt, 70% nếu cũ
     *  - Sách quý: 100%
     *  - Sách tham khảo: dùng 80%
     */

    if ($loai === 'quy') {
        $phatGoc = $gia; // 100%
    } else {
        switch ($tinhTrangKhiMuon) {
            case 'Moi':
            case 'Tot':
                $phatGoc = round($gia * 0.8);
                break;

            case 'Trung binh':
            case 'Cu':
            case 'Hong':
                $phatGoc = round($gia * 0.7);
                break;

            default:
                $phatGoc = round($gia * 0.7);
        }
    }


    /**
     * ----------------------------
     * 3. CẬP NHẬT borrow_items
     * ----------------------------
     */
    $item->update([
        'trang_thai' => 'Hong',
        'tien_phat' => $phatGoc,
    ]);

    /**
     * ----------------------------
     * 4. CẬP NHẬT inventory
     * ----------------------------
     */
    if ($inventory) {
        $inventory->update([
            'status' => 'Hong',
        ]);
    }

    /**
     * ----------------------------
     * 5. LƯU VÀO fines với đầy đủ thông tin hư hỏng
     * ----------------------------
     */
    $request = request();
    
    // Xác định mức độ hư hỏng
    $damageSeverity = 'trung_binh'; // Mặc định
    $damageType = $request->input('damage_type', 'khac');
    $damageDescription = $request->input('damage_description', 'Sách bị hư hỏng khi trả');
    
    // Xử lý upload ảnh hư hỏng nếu có
    $damageImages = [];
    if ($request->hasFile('damage_images')) {
        foreach ($request->file('damage_images') as $image) {
            $path = $image->store('fines/damage_images', 'public');
            $damageImages[] = $path;
        }
    }
    
    Fine::create([
        'borrow_id'      => $item->borrow_id,
        'borrow_item_id' => $item->id,
        'reader_id'      => $item->borrow->reader_id,
        'amount'         => $phatGoc,
        'type'           => 'damaged_book',
        'description'    => 'Sách hỏng: '.$book->ten_sach.', tình trạng khi mượn: '.$tinhTrangKhiMuon,
        'damage_description' => $damageDescription,
        'damage_images' => !empty($damageImages) ? $damageImages : null,
        'damage_severity' => $damageSeverity,
        'damage_type' => $damageType,
        'condition_before' => $tinhTrangKhiMuon,
        'condition_after' => 'Hong',
        'inspection_notes' => $request->input('inspection_notes', 'Báo hỏng sách bởi admin'),
        'inspected_by' => auth()->id(),
        'inspected_at' => now(),
        'status'         => 'pending',
        'due_date'       => now()->addDays(30),
        'created_by'     => auth()->id(),
    ]);

    return back()->with(
        'success',
        "Đã báo hỏng sách! Tiền phạt: " . number_format($phatGoc) . "đ"
    );
}

/**
 * Đánh dấu sách quá hạn
 */
public function markOverdue($id)
{
    try {
        $item = BorrowItem::with('borrow.reader', 'book', 'inventory')->findOrFail($id);

        if ($item->trang_thai !== 'Dang muon') {
            return back()->with('error', 'Chỉ có thể đánh dấu quá hạn cho sách đang mượn!');
        }

        \DB::beginTransaction();

        $item->update([
            'trang_thai' => 'Qua han'
        ]);

        $dueDate = \Carbon\Carbon::parse($item->ngay_hen_tra)->startOfDay();
        $today = \Carbon\Carbon::today();
        $daysOverdue = $today->diffInDays($dueDate, false) * -1;

        $fineDay1 = 5000;
        $fineDay2 = 15000;
        $threshold = 3;

        $phatQuaHan = 0;
        if ($daysOverdue > 0) {
            if ($daysOverdue <= $threshold) {
                $phatQuaHan = $daysOverdue * $fineDay1;
            } else {
                $phatQuaHan = ($threshold * $fineDay1) + (($daysOverdue - $threshold) * $fineDay2);
            }

            $item->update([
                'tien_phat' => $phatQuaHan,
            ]);

            Fine::create([
                'borrow_id'      => $item->borrow_id,
                'borrow_item_id' => $item->id,
                'reader_id'      => $item->borrow->reader_id,
                'amount'         => $phatQuaHan,
                'type'           => 'overdue',
                'description'    => 'Trả sách quá hạn: ' . $item->book->ten_sach . ', quá hạn ' . $daysOverdue . ' ngày',
                'status'         => 'pending',
                'due_date'       => now()->addDays(7),
                'created_by'     => auth()->id(),
            ]);
        }

        $borrow = $item->borrow;
        if ($borrow) {
            $borrow->refresh();
            $borrow->load('items');
            $statuses = $borrow->items->pluck('trang_thai')->toArray();

            if (in_array('Mat sach', $statuses)) {
                $borrow->trang_thai = 'Mat sach';
            } elseif (in_array('Qua han', $statuses)) {
                $borrow->trang_thai = 'Qua han';
            } elseif (in_array('Dang muon', $statuses)) {
                $borrow->trang_thai = 'Dang muon';
            }
            $borrow->save();
        }

        \DB::commit();

        $message = 'Đã đánh dấu sách quá hạn!';
        if ($daysOverdue > 0 && $phatQuaHan > 0) {
            $message .= ' Tiền phạt: ' . number_format($phatQuaHan) . 'đ (' . $daysOverdue . ' ngày)';
        }

        return back()->with('success', $message);
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Lỗi khi đánh dấu quá hạn: ' . $e->getMessage());
        return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
    }
}

/**
 * Cập nhật trạng thái sách (dùng cho dropdown menu)
 */
public function updateStatus(Request $request, $id)
{
    try {
        $item = BorrowItem::with('borrow.reader', 'book', 'inventory')->findOrFail($id);
        $newStatus = $request->input('status');

        \DB::beginTransaction();

        switch ($newStatus) {
            case 'Qua han':
                // Cập nhật trạng thái item
                $item->update(['trang_thai' => 'Qua han']);

                // Tính số ngày quá hạn
                $dueDate = \Carbon\Carbon::parse($item->ngay_hen_tra)->startOfDay();
                $today = \Carbon\Carbon::today();
                $daysOverdue = $today->diffInDays($dueDate, false) * -1;

                $fineDay1 = 5000;
                $fineDay2 = 15000;
                $threshold = 3;

                // Nếu đã quá hạn thì tính phạt
                if ($daysOverdue > 0) {
                    if ($daysOverdue <= $threshold) {
                        $phatQuaHan = $daysOverdue * $fineDay1;
                    } else {
                        $phatQuaHan = ($threshold * $fineDay1) + (($daysOverdue - $threshold) * $fineDay2);
                    }

                    // Lưu vào bảng fines
                    Fine::create([
                        'borrow_id'      => $item->borrow_id,
                        'borrow_item_id' => $item->id,
                        'reader_id'      => $item->borrow->reader_id,
                        'amount'         => $phatQuaHan,
                        'type'           => 'overdue',
                        'description'    => 'Trả sách quá hạn: ' . $item->book->ten_sach . ', quá hạn ' . $daysOverdue . ' ngày',
                        'status'         => 'pending',
                        'due_date'       => now()->addDays(7),
                        'created_by'     => auth()->id(),
                    ]);

                    // Đồng bộ tiền phạt vào borrow_items
                    $item->update([
                        'tien_phat' => $phatQuaHan,
                    ]);
                }

                $message = 'Đã đánh dấu sách quá hạn!';
                if ($daysOverdue > 0) {
                    $message .= ' Tiền phạt: ' . number_format($phatQuaHan) . 'đ (' . $daysOverdue . ' ngày)';
                }
                break;

            case 'Da tra':
                $returnDate = now();
                $update = [
                    'trang_thai' => 'Da tra',
                    'ngay_tra_thuc_te' => $returnDate->toDateString(),
                ];

                if (($item->borrow_type ?? 'take_home') === 'take_home' && $item->ngay_muon && $item->ngay_hen_tra) {
                    $ngayMuon = \Carbon\Carbon::parse($item->ngay_muon)->startOfDay();
                    $ngayHenTra = \Carbon\Carbon::parse($item->ngay_hen_tra)->startOfDay();
                    $ngayTra = \Carbon\Carbon::parse($returnDate)->startOfDay();

                    $daysExpected = max(1, $ngayMuon->diffInDays($ngayHenTra));
                    $daysBorrowed = max(1, $ngayMuon->diffInDays($ngayTra));

                    $refund = \App\Services\PricingService::calculateEarlyReturnRefund((float) ($item->tien_thue ?? 0), $daysBorrowed, $daysExpected);
                    if ($refund > 0) {
                        $update['tien_thue'] = max(0, (float) ($item->tien_thue ?? 0) - $refund);
                    }

                    $fine = \App\Services\PricingService::calculateLateReturnFine($ngayHenTra, $ngayTra, 1);
                    if ($fine > 0) {
                        $update['tien_phat'] = $fine;
                    }

                    $deposit = (float) ($item->tien_coc ?? 0);
                    $depositRefund = max(0, $deposit - ($fine > 0 ? $fine : (float) ($item->tien_phat ?? 0)));
                    $update['tien_coc_da_hoan'] = $depositRefund;
                    $update['trang_thai_coc'] = 'da_hoan';
                    $update['ngay_hoan_coc'] = $returnDate->toDateString();
                }

                $item->update($update);

                if ($item->inventory) {
                    $item->inventory->update(['status' => 'Co san']);
                }

                $message = 'Đã đánh dấu sách đã trả!';
                break;

            case 'Hong':
                $book = $item->book;
                $inventory = $item->inventory;
                $gia = $book->gia;
                $loai = $book->loai_sach;
                $tinhTrangKhiMuon = $inventory->condition ?? 'Trung binh';

                // Tính tiền phạt
                if ($loai === 'quy') {
                    $phatGoc = $gia; // 100%
                } else {
                    switch ($tinhTrangKhiMuon) {
                        case 'Moi':
                        case 'Tot':
                            $phatGoc = round($gia * 0.8);
                            break;
                        default:
                            $phatGoc = round($gia * 0.7);
                    }
                }

                // Cập nhật item
                $item->update([
                    'trang_thai' => 'Hong',
                    'tien_phat' => $phatGoc,
                ]);

                // Cập nhật inventory
                if ($inventory) {
                    $inventory->update(['status' => 'Hong']);
                }

                // Lưu vào fines với đầy đủ thông tin hư hỏng
                $damageDescription = $request->input('damage_description', 'Sách bị hư hỏng khi trả');
                $damageType = $request->input('damage_type', 'khac');
                
                // Xử lý upload ảnh nếu có
                $damageImages = [];
                if ($request->hasFile('damage_images')) {
                    foreach ($request->file('damage_images') as $image) {
                        $path = $image->store('fines/damage_images', 'public');
                        $damageImages[] = $path;
                    }
                }
                
                Fine::create([
                    'borrow_id'      => $item->borrow_id,
                    'borrow_item_id' => $item->id,
                    'reader_id'      => $item->borrow->reader_id,
                    'amount'         => $phatGoc,
                    'type'           => 'damaged_book',
                    'description'    => 'Sách hỏng: ' . $book->ten_sach . ', tình trạng khi mượn: ' . $tinhTrangKhiMuon,
                    'damage_description' => $damageDescription,
                    'damage_images' => !empty($damageImages) ? $damageImages : null,
                    'damage_severity' => 'trung_binh',
                    'damage_type' => $damageType,
                    'condition_before' => $tinhTrangKhiMuon,
                    'condition_after' => 'Hong',
                    'inspection_notes' => $request->input('inspection_notes', 'Đánh dấu hỏng bởi admin'),
                    'inspected_by' => auth()->id(),
                    'inspected_at' => now(),
                    'status'         => 'pending',
                    'due_date'       => now()->addDays(30),
                    'created_by'     => auth()->id(),
                ]);

                $message = 'Đã báo hỏng sách! Tiền phạt: ' . number_format($phatGoc) . 'đ';
                break;

            default:
                \DB::rollBack();
                return back()->with('error', 'Trạng thái không hợp lệ!');
        }

        // Cập nhật trạng thái Borrow
        $borrow = $item->borrow;
        if ($borrow) {
            $borrow->refresh();
            $borrow->load('items');
            $statuses = $borrow->items->pluck('trang_thai')->toArray();
            
            if (in_array('Mat sach', $statuses)) {
                $borrow->trang_thai = 'Mat sach';
            } elseif (in_array('Hong', $statuses)) {
                $borrow->trang_thai = 'Hong';
            } elseif (in_array('Qua han', $statuses)) {
                $borrow->trang_thai = 'Qua han';
            } elseif (in_array('Cho duyet', $statuses) || in_array('Chua nhan', $statuses)) {
                $borrow->trang_thai = 'Cho duyet';
            } elseif (in_array('Dang muon', $statuses)) {
                $borrow->trang_thai = 'Dang muon';
            } else {
                $borrow->trang_thai = 'Da tra';
            }

            $borrow->recalculateTotals();
        }

        \DB::commit();

        return back()->with('success', $message);
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Lỗi khi cập nhật trạng thái: ' . $e->getMessage());
        return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
    }
}

}
