<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\Reader;
use App\Models\Book;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Inventory;
use App\Models\BorrowItem;
use App\Models\BorrowPayment;

use App\Models\Wallet;
use App\Models\Fine;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BorrowController extends Controller
{
    /* ============================================================
        1) DANH SÁCH PHIẾU MƯỢN + LỌC + TÌM KIẾM
    ============================================================ */
    public function index(Request $request)
    {
        // Sử dụng fresh() để đảm bảo load dữ liệu mới nhất từ database
        $query = Borrow::with(['reader', 'librarian', 'items.book', 'voucher', 'payments']);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('reader', function ($q) use ($keyword) {
                $q->where('ho_ten', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('trang_thai')) {
            if ($request->trang_thai === 'Cho duyet') {
                $query->whereHas('items', function ($q) {
                    $q->where('trang_thai', 'Cho duyet');
                });
            } elseif ($request->trang_thai === 'Huy') {
                $query->whereHas('items', function ($q) {
                    $q->where('trang_thai', 'Huy');
                });
            } elseif ($request->trang_thai === 'Mat sach') {
                $query->whereHas('items', function ($q) {
                    $q->where('trang_thai', 'Mat sach');
                });
            } elseif ($request->trang_thai === 'Hong') {
                $query->whereHas('items', function ($q) {
                    $q->where('trang_thai', 'Hong');
                });
            } elseif ($request->trang_thai === 'dang_van_chuyen_tra_ve') {
                $query->where('trang_thai_chi_tiet', 'dang_van_chuyen_tra_ve');
            } else {
                $query->where('trang_thai', $request->trang_thai);
            }
        }

        // Filter theo trạng thái chi tiết (11 bước vận chuyển)
        if ($request->filled('trang_thai_chi_tiet')) {
            $query->where('trang_thai_chi_tiet', $request->trang_thai_chi_tiet);
        }

        if (!$request->filled('trang_thai')) {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $borrows = $query->paginate(10);

        // Đảm bảo refresh lại items cho mỗi borrow để có dữ liệu mới nhất từ database
        $borrows->getCollection()->transform(function ($borrow) {
            // Reload hoàn toàn từ database để tránh cache
            $borrow = Borrow::with(['reader', 'librarian', 'items.book', 'voucher'])->find($borrow->id);

            return $borrow;
        });

        // Tính toán thống kê từ toàn bộ database thay vì từ collection đã paginate
        $stats = [
            'dang_muon' => Borrow::where('trang_thai', 'Dang muon')->count(),
            'qua_han' => Borrow::where('trang_thai', 'Qua han')->count(),
            'da_tra' => Borrow::where('trang_thai', 'Da tra')->count(),
            'huy' => Borrow::where('trang_thai', 'Huy')->count(),
            'hong' => Borrow::whereHas('items', function ($q) {
                $q->where('trang_thai', 'Hong');
            })->count(),
            'mat_sach' => Borrow::whereHas('items', function ($q) {
                $q->where('trang_thai', 'Mat sach');
            })->count(),
            'tong' => Borrow::count(),
        ];

        return response()
            ->view('admin.borrows.index', compact('borrows', 'stats'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /* ============================================================
        2) TẠO PHIẾU MƯỢN
    ============================================================ */
    public function create(Request $request)
    {
        $readers = Reader::where('trang_thai', 'Hoat dong')->get();
        $books = Book::all();
        $librarians = User::whereIn('role', ['admin', 'staff'])->get();

        // Xử lý prefill nếu có reader_id từ request (ví từ reservation)
        $prefillReader = null;
        if ($request->has('reader_id')) {
            $prefillReader = Reader::select('id', 'ho_ten', 'so_the_doc_gia', 'so_dien_thoai', 'tinh_thanh', 'huyen', 'xa', 'so_nha', 'dia_chi')
                ->find($request->reader_id);
        }

        return view('admin.borrows.create', compact('readers', 'books', 'librarians', 'prefillReader'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reader_id' => 'nullable|exists:readers,id',
            'librarian_id' => 'required|exists:users,id',
            'ten_nguoi_muon' => 'required|string|max:255',
            'so_dien_thoai' => 'required|string|max:20',
            'tinh_thanh' => 'nullable|string|max:255',
            'huyen' => 'nullable|string|max:255',
            'xa' => 'nullable|string|max:255',
            'so_nha' => 'nullable|string|max:255',
            'ngay_muon' => 'required|date',
            'ghi_chu' => 'nullable|string|max:500',
            'reservation_id' => 'nullable|exists:inventory_reservations,id',
        ]);

        $data = $request->all();
        
        // Tránh trùng lặp địa chỉ: Nếu đã gộp vào dia_chi thì xóa các trường lẻ để view không cộng dồn
        if ($request->filled('dia_chi') && empty($data['so_nha'])) {
            $data['so_nha'] = $request->dia_chi;
            $data['tinh_thanh'] = null;
            $data['huyen'] = null;
            $data['xa'] = null;
        }

        DB::beginTransaction();
        try {
            // Mặc định trạng thái chi tiết là đơn hàng mới nếu tạo từ admin
            $data['trang_thai_chi_tiet'] = Borrow::STATUS_DON_HANG_MOI;
            $borrow = Borrow::create($data);

            // Nếu có reservation_id, tạo BorrowItem tương ứng
            if ($request->filled('reservation_id')) {
                $reservation = \App\Models\InventoryReservation::with('book')->find($request->reservation_id);
                if ($reservation) {
                    $rentalFee = (float) ($reservation->total_fee ?? 0);
                    if ($rentalFee <= 0) {
                        // Fallback: tính lại theo số ngày đặt trước nếu total_fee chưa có
                        $pickup = $reservation->pickup_date ? \Carbon\Carbon::parse($reservation->pickup_date) : null;
                        $ret = $reservation->return_date ? \Carbon\Carbon::parse($reservation->return_date) : null;
                        $days = 1;
                        if ($pickup && $ret && $ret->greaterThan($pickup)) {
                            $days = max(1, $pickup->diffInDays($ret));
                        }
                        $dailyFee = (float) ($reservation->book?->daily_fee ?? 5000);
                        $rentalFee = $dailyFee * $days;
                    }

                    // Tạo dòng sách mượn (không tiền cọc, không tiền ship)
                    \App\Models\BorrowItem::create([
                        'borrow_id' => $borrow->id,
                        'book_id' => $reservation->book_id,
                        'inventorie_id' => $reservation->inventory_id, // Có thể null nếu chưa gán
                        'tien_coc' => 0,
                        'tien_thue' => $rentalFee,
                        'tien_ship' => 0,
                        'ngay_muon' => $borrow->ngay_muon,
                        'ngay_hen_tra' => $reservation->return_date ? \Carbon\Carbon::parse($reservation->return_date)->toDateString() : \Carbon\Carbon::parse($borrow->ngay_muon)->addDays(14)->toDateString(),
                        'trang_thai' => 'Cho duyet',
                        'borrow_type' => 'take_home',
                    ]);

                    // Update header totals = chỉ tiền thuê
                    $borrow->update([
                        'tien_coc' => 0,
                        'tien_ship' => 0,
                        'tien_thue' => $rentalFee,
                        'tong_tien' => $rentalFee,
                    ]);

                    // Cập nhật yêu cầu đặt trước
                    $reservation->update([
                        'status' => 'fulfilled',
                        'borrow_id' => $borrow->id,
                        'processed_by' => Auth::id(),
                        'fulfilled_at' => now(),
                    ]);
                }
            }

            // Không sync tiền ship / tiền cọc từ items cho phiếu đặt trước

            DB::commit();
            return redirect()->route('admin.borrows.index')
                ->with('success', 'Cho mượn sách thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error storing borrow: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi khi lưu phiếu mượn: ' . $e->getMessage());
        }
    }


    /* ============================================================
        3) CHỈNH SỬA PHIẾU MƯỢN
    ============================================================ */
    public function edit($id)
    {
        $borrow = Borrow::with([
            'items.book.category',
            'items.inventory',
            'reader',
            'librarian',
            'voucher'
        ])->findOrFail($id);

        $readers = Reader::where('trang_thai', 'Hoat dong')->get();
        $books = Book::all();
        $librarians = User::where('role', 'admin')->get();

        // Lấy danh sách voucher khả dụng
        $vouchers = Voucher::where('kich_hoat', 1)
            ->where('trang_thai', 'active')
            ->whereDate('ngay_bat_dau', '<=', now())
            ->whereDate('ngay_ket_thuc', '>=', now())
            ->where('so_luong', '>', 0);

        // Nếu có reader_id thì lọc theo reader
        if ($borrow->reader_id) {
            $vouchers = $vouchers->where(function ($query) use ($borrow) {
                $query->where('reader_id', $borrow->reader_id)
                    ->orWhereNull('reader_id');
            });
        }

        $vouchers = $vouchers->get();

        return view('admin.borrows.edit', compact(
            'borrow',
            'readers',
            'books',
            'vouchers',
            'librarians'
        ));
    }

    public function update(Request $request, $id)
    {
        $borrow = Borrow::findOrFail($id);

        $data = $request->validate([
            'reader_id' => 'nullable|exists:readers,id',
            'librarian_id' => 'nullable|exists:users,id',
            'ten_nguoi_muon' => 'required|string|max:255',
            'ngay_muon' => 'required|date',
            'so_dien_thoai' => 'required|string|max:20',
            'tinh_thanh' => 'required|string|max:255',
            'huyen' => 'required|string|max:255',
            'xa' => 'required|string|max:255',
            'so_nha' => 'required|string|max:255',
            'ghi_chu' => 'nullable|string',
            'voucher_id' => 'nullable|exists:vouchers,id',
        ]);

        $borrow->update($data);

        // Cập nhật lại tổng tiền
        $borrow->recalculateTotals();

        return redirect()->route('admin.borrows.index')
            ->with('success', 'Cập nhật phiếu mượn thành công!');
    }

    /* ============================================================
        4) XOÁ PHIẾU MƯỢN
    ============================================================ */
    public function destroy($id)
    {
        Borrow::destroy($id);
        return redirect()->route('admin.borrows.index')
            ->with('success', 'Xóa phiếu mượn thành công!');
    }

    /* ============================================================
        5) CHI TIẾT PHIẾU MƯỢN
    ============================================================ */
    public function show($id)
    {
        $borrow = Borrow::with(['reader', 'librarian', 'items.book', 'fines'])
            ->findOrFail($id);
        

        return view('admin.borrows.show', [
            'borrow' => $borrow,
            'borrowItems' => $borrow->items
        ]);
    }

    /* ============================================================
        6) TẠO MÓN MƯỢN (ITEM)
    ============================================================ */
    public function createItem($borrowId)
    {
        $borrow = Borrow::findOrFail($borrowId);
        $books = Book::all();
        $librarians = User::where('role', 'admin')->get();

        return view('admin.borrows.createitem', compact('borrow', 'books', 'librarians'));
    }

    public function storeItem(Request $request, $borrowId)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'inventory_ids' => 'required|array',
            'inventory_ids.*' => 'exists:inventories,id',
            'tien_coc' => 'nullable|numeric|min:0',
            'tien_thue' => 'nullable|numeric|min:0',
            'tien_ship' => 'nullable|numeric|min:0',
            'trang_thai_coc' => 'required|in:cho_xu_ly,da_thu,da_hoan,tru_vao_phat',
            'ngay_muon' => 'required|date',
            'ngay_hen_tra' => 'required|date|after_or_equal:ngay_muon',
            'ghi_chu' => 'nullable|string',
            'librarian_id' => 'nullable|exists:users,id',
        ]);

        $borrow = Borrow::findOrFail($borrowId);
        $book = Book::findOrFail($request->book_id);
        $reader = $borrow->reader;
        $hasCard = $reader ? true : false;

        foreach ($request->inventory_ids as $invId) {
            $inventory = Inventory::findOrFail($invId);

            // Tính phí thuê và tiền cọc dựa trên giá sách và số ngày mượn
            $fees = \App\Services\PricingService::calculateFees(
                $book,
                $inventory,
                $request->ngay_muon,
                $request->ngay_hen_tra,
                $hasCard
            );

            $borrow->items()->create([
                'book_id' => $request->book_id,
                'inventorie_id' => $invId,
                'tien_coc' => $fees['tien_coc'],
                'tien_thue' => $fees['tien_thue'],
                'tien_ship' => $request->tien_ship ?? 0,
                'ngay_muon' => $request->ngay_muon,
                'ngay_hen_tra' => $request->ngay_hen_tra,
                'borrow_type' => $request->input('borrow_type', 'take_home'),
                'ghi_chu' => $request->ghi_chu,
                'librarian_id' => $request->librarian_id ?? auth()->id(),
            ]);
        }

        // cập nhật tổng tiền
        $totals = $borrow->items()
            ->selectRaw('
            SUM(COALESCE(tien_coc,0)) as coc,
            SUM(COALESCE(tien_thue,0)) as thue,
            SUM(COALESCE(tien_ship,0)) as ship
        ')->first();

        $borrow->update([
            'tien_coc' => $totals->coc,
            'tien_thue' => $totals->thue,
            'tien_ship' => $totals->ship,
            'tong_tien' => $totals->coc + $totals->thue + $totals->ship,
        ]);

        return back()->with('success', 'Đã thêm sách vào phiếu mượn!');
    }

    /* ============================================================
        7) CẬP NHẬT TRẠNG THÁI ITEM
    ============================================================ */
    public function returnItem($id)
    {
        $item = BorrowItem::with(['book'])->findOrFail($id);

        $returnDate = now();

        $update = [
            'trang_thai' => 'Da tra',
            'ngay_tra_thuc_te' => $returnDate->toDateString(),
        ];

        // Take-home: tính phạt quá hạn theo tier + hoàn 30% tiền thuê nếu trả sớm
        if (($item->borrow_type ?? 'take_home') === 'take_home' && $item->ngay_muon && $item->ngay_hen_tra) {
            $ngayMuon = \Carbon\Carbon::parse($item->ngay_muon)->startOfDay();
            $ngayHenTra = \Carbon\Carbon::parse($item->ngay_hen_tra)->startOfDay();
            $ngayTra = \Carbon\Carbon::parse($returnDate)->startOfDay();

            $daysExpected = max(1, $ngayMuon->diffInDays($ngayHenTra));
            $daysBorrowed = max(1, $ngayMuon->diffInDays($ngayTra));

            // hoàn tiền thuê theo policy (cách 1: giảm tien_thue thực tế)
            $refund = \App\Services\PricingService::calculateEarlyReturnRefund((float) ($item->tien_thue ?? 0), $daysBorrowed, $daysExpected);
            if ($refund > 0) {
                $update['tien_thue'] = max(0, (float) ($item->tien_thue ?? 0) - $refund);
            }

            // phạt quá hạn theo tier
            $fine = \App\Services\PricingService::calculateLateReturnFine($ngayHenTra, $ngayTra, 1);
            if ($fine > 0) {
                $update['tien_phat'] = $fine;
            }

            // hoàn cọc sau khi trừ phạt (chưa trừ phí hỏng/mất ở flow khác)
            $deposit = (float) ($item->tien_coc ?? 0);
            $depositRefund = max(0, $deposit - ($fine > 0 ? $fine : (float) ($item->tien_phat ?? 0)));
            $update['tien_coc_da_hoan'] = $depositRefund;
            $update['trang_thai_coc'] = 'da_hoan';
            $update['ngay_hoan_coc'] = $returnDate->toDateString();
        }

        $item->update($update);

        // Cập nhật trạng thái inventory từ 'Dang muon' về 'Co san' khi trả sách
        if ($item->inventorie_id) {
            \App\Models\Inventory::where('id', $item->inventorie_id)
                ->where('status', 'Dang muon')
                ->update([
                    'status' => 'Co san',
                    'updated_at' => now()
                ]);

            \Log::info('Updated inventory status after returning book', [
                'inventory_id' => $item->inventorie_id,
                'borrow_item_id' => $item->id
            ]);
        }

        // Cập nhật tổng tiền của Borrow (bao gồm cả tiền phạt theo policy)
        if ($item->borrow) {
            $item->borrow->recalculateTotals();
        }

        return back()->with('success', 'Đã trả sách!');
    }

    /* ============================================================
        8) XỬ LÝ PHIẾU MƯỢN (TẠO PAYMENT + SHIPPING)
    ============================================================ */
    public function processBorrow($id)
    {
        $borrow = Borrow::with('items')->findOrFail($id);

        // Kiểm tra xem có items nào đang ở trạng thái "Chua nhan" không
        $chuaNhanItems = $borrow->items->where('trang_thai', 'Chua nhan');

        if ($chuaNhanItems->isEmpty()) {
            return back()->with('error', 'Không có sách nào đang ở trạng thái "Chưa nhận" để xử lý!');
        }

        // Cập nhật trạng thái tất cả items từ "Chua nhan" sang "Dang muon"
        foreach ($chuaNhanItems as $item) {
            $item->update([
                'trang_thai' => 'Dang muon',
                'ngay_muon' => $item->ngay_muon ?? now(),
            ]);

            // Cập nhật trạng thái inventory từ 'Co san' sang 'Dang muon'
            if ($item->inventorie_id) {
                \App\Models\Inventory::where('id', $item->inventorie_id)
                    ->where('status', 'Co san')
                    ->update([
                        'status' => 'Dang muon',
                        'updated_at' => now()
                    ]);

                \Log::info('Updated inventory status after processing borrow', [
                    'inventory_id' => $item->inventorie_id,
                    'borrow_item_id' => $item->id
                ]);
            }
        }

        // Kiểm tra và tạo payment nếu chưa có (chỉ tạo khi chưa có payment nào)
        $existingPayment = BorrowPayment::where('borrow_id', $borrow->id)->first();

        if (!$existingPayment) {
            // Nếu chưa có payment, tạo mới với COD mặc định
            BorrowPayment::create([
                'borrow_id' => $borrow->id,
                'amount' => $borrow->tong_tien ?? 0,
                'payment_type' => 'deposit',
                'payment_method' => 'offline',
                'payment_status' => 'pending',
                'note' => 'Thanh toán khi nhận hàng (COD) - Tiền cọc, phí thuê và phí vận chuyển',
            ]);
        }

        $borrow->update([
            'trang_thai' => 'Dang muon'
        ]);

        return back()->with('success', 'Đã xử lý phiếu mượn thành công! Các sách đã chuyển sang trạng thái "Đang mượn".');
    }

    /* ============================================================
        9) DUYỆT PHIẾU MƯỢN (CHƯA DI CHUYỂN TRẠNG THÁI ITEMS - PHẢI CHỜ THANH TOÁN)
    ============================================================ */
    public function approve($id)
    {
        // Cho phép người dùng có quyền chỉnh sửa đơn mượn duyệt phiếu
        if (!auth()->check() || !auth()->user()->can('edit-borrows')) {
            return back()->with('error', 'Bạn không có quyền thực hiện thao tác này.');
        }

        $borrow = Borrow::with('items')->findOrFail($id);

        // Kiểm tra xem có items nào đang ở trạng thái "Cho duyet" không
        $pendingItems = $borrow->items->where('trang_thai', 'Cho duyet');

        if ($pendingItems->isEmpty()) {
            return back()->with('error', 'Không có sách nào đang chờ duyệt trong phiếu mượn này!');
        }

        // Kiểm tra và tạo payment nếu chưa có (chỉ tạo khi chưa có payment nào)
        $existingPayment = BorrowPayment::where('borrow_id', $borrow->id)->first();

        if (!$existingPayment) {
            // Nếu chưa có payment, tạo mới với COD mặc định
            BorrowPayment::create([
                'borrow_id' => $borrow->id,
                'amount' => $borrow->tong_tien ?? 0,
                'payment_type' => 'deposit',
                'payment_method' => 'offline',
                'payment_status' => 'pending',
                'note' => 'Thanh toán khi nhận hàng (COD) - Tiền cọc, phí thuê và phí vận chuyển',
            ]);
        }

        // Cập nhật trạng thái của borrow (nhưng vẫn để items ở "Cho duyet")
        // Items sẽ chuyển sang "Dang muon" khi thanh toán được xác nhận
        $borrow->update([
            'trang_thai_chi_tiet' => \App\Models\Borrow::STATUS_DON_HANG_MOI,
        ]);

        return back()->with('success', 'Đã duyệt phiếu mượn thành công! Chờ khách hàng thanh toán để chuyển sách sang trạng thái "Đang mượn".');
    }

    /* ============================================================
        9B) XÁC NHẬN THANH TOÁN (TIỀN MẶT HOẶC QUÉT MÃ)
    ============================================================ */
    public function confirmCashPayment(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->can('edit-borrows')) {
            return back()->with('error', 'Bạn không có quyền thực hiện thao tác này.');
        }

        $request->validate([
            'payment_method' => 'required|in:online,offline'
        ]);

        $borrow = Borrow::with('payments')->findOrFail($id);
        $paymentMethod = $request->payment_method;
        $paymentMethodText = $paymentMethod === 'offline' ? 'tiền mặt' : 'quét mã';

        // Cập nhật payment pending sang success, giữ nguyên amount gốc (không update)
        $payment = $borrow->payments()->where('payment_status', 'pending')->first();
        
        if (!$payment) {
            return back()->with('info', 'Không có thanh toán nào đang chờ xác nhận.');
        }

        $updated = $borrow->payments()
            ->where('payment_status', 'pending')
            ->update([
                'payment_method' => $paymentMethod,
                'payment_status' => 'success',
                'note' => DB::raw("CONCAT(COALESCE(note, ''), ' - Đã xác nhận thanh toán " . $paymentMethodText . " bởi: " . auth()->user()->name . " lúc " . now()->format('d/m/Y H:i') . " - Số tiền: " . number_format($payment->amount ?? 0) . "₫')"),
                'updated_at' => now()
            ]);

        if ($updated > 0) {
            // Cập nhật trạng thái tất cả items từ "Cho duyet" sang "Dang muon" vì đã thanh toán
            $choduyet_items = $borrow->items()->where('trang_thai', 'Cho duyet')->get();
            foreach ($choduyet_items as $item) {
                $item->update([
                    'trang_thai' => 'Dang muon',
                    'ngay_muon' => $item->ngay_muon ?? now(),
                ]);

                // Cập nhật trạng thái inventory từ 'Co san' sang 'Dang muon'
                if ($item->inventorie_id) {
                    \App\Models\Inventory::where('id', $item->inventorie_id)
                        ->where('status', 'Co san')
                        ->update([
                            'status' => 'Dang muon',
                            'updated_at' => now()
                        ]);
                }
            }

            // Cập nhật trạng thái borrow sang "Dang muon"
            $borrow->update([
                'trang_thai' => 'Dang muon',
                'trang_thai_chi_tiet' => \App\Models\Borrow::STATUS_DA_MUON_DANG_LUU_HANH,
            ]);

            // Tự động gán reader_id nếu chưa có (để hiện lịch sử cho user)
            if (!$borrow->reader_id && $borrow->so_dien_thoai) {
                $reader = Reader::where('so_dien_thoai', $borrow->so_dien_thoai)->first();
                
                if ($reader) {
                    $borrow->update(['reader_id' => $reader->id]);
                    \Log::info('Auto-assigned reader to borrow after payment', [
                        'borrow_id' => $borrow->id,
                        'reader_id' => $reader->id
                    ]);
                } else {
                    // Tìm user theo số điện thoại và tạo reader mới nếu có user
                    $user = \App\Models\User::where('phone', $borrow->so_dien_thoai)->first();
                    
                    if ($user) {
                        // Tạo reader mới cho user
                        $reader = Reader::create([
                            'user_id' => $user->id,
                            'ho_ten' => $borrow->ten_nguoi_muon ?? $user->name,
                            'email' => $user->email,
                            'so_dien_thoai' => $borrow->so_dien_thoai,
                            'dia_chi' => $borrow->so_nha . ', ' . $borrow->xa . ', ' . $borrow->tinh_thanh,
                            'tinh_thanh' => $borrow->tinh_thanh,
                            'xa' => $borrow->xa,
                            'so_nha' => $borrow->so_nha,
                            'trang_thai' => 'Hoat dong',
                            'ngay_cap_the' => now(),
                            'ngay_het_han' => now()->addYear(),
                        ]);
                        
                        $borrow->update(['reader_id' => $reader->id]);
                        
                        \Log::info('Auto-created reader and assigned to borrow after payment', [
                            'borrow_id' => $borrow->id,
                            'reader_id' => $reader->id,
                            'user_id' => $user->id
                        ]);
                    }
                }
            }

            return back()->with('success', 'Đã xác nhận thanh toán ' . $paymentMethodText . ' thành công! Số tiền: ' . number_format($payment->amount ?? 0) . '₫');
        } else {
            return back()->with('info', 'Không có thanh toán nào đang chờ xác nhận.');
        }
    }

    /* ============================================================
        10) CLIENT – LỊCH SỬ MƯỢN
    ============================================================ */
    public function clientIndex()
    {
        $reader = Reader::where('user_id', Auth::id())->first();

        if (!$reader) {
            return view('borrow.index', ['borrows' => collect(), 'total' => 0]);
        }

        // Hiển thị theo từng đơn mượn (giống admin)
        // Chỉ hiển thị những đơn đã thanh toán thành công
        $borrows = Borrow::with(['reader', 'items.book', 'payments'])
            ->where('reader_id', $reader->id)
            ->whereHas('payments', function ($q) {
                $q->where('payment_status', 'success');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        // Tính tổng tiền cọc tất cả phiếu mượn của reader (chỉ những đã thanh toán)
        $total = $borrows->sum(fn($borrow) => $borrow->tong_tien ?? 0);

        return view('borrow.index', compact('borrows', 'total'));

    }



    public function clientShow($id)
    {
        $readerId = Auth::id();

        $borrow = Borrow::where('reader_id', $readerId)
            ->with(['items.book'])
            ->findOrFail($id);

        return view('borrow.show', compact('borrow'));
    }

    public function clientCancel($id)
    {
        $readerId = Auth::id();

        $borrow = Borrow::where('reader_id', $readerId)->findOrFail($id);

        // Kiểm tra trạng thái chi tiết - không cho phép hủy khi đang vận chuyển
        $trangThaiChiTiet = $borrow->trang_thai_chi_tiet;
        $trangThaiKhongChoPhepHuy = [
            Borrow::STATUS_CHO_BAN_GIAO_VAN_CHUYEN,  // Chờ bàn giao vận chuyển
            Borrow::STATUS_DANG_GIAO_HANG,           // Đang giao hàng
            Borrow::STATUS_GIAO_HANG_THANH_CONG,     // Giao hàng thành công (chờ khách xác nhận)
            Borrow::STATUS_DANG_VAN_CHUYEN_TRA_VE,   // Đang vận chuyển trả về
        ];

        if (in_array($trangThaiChiTiet, $trangThaiKhongChoPhepHuy)) {
            return back()->with('error', '❌ Không thể hủy đơn khi đang vận chuyển. Đơn hàng đã được bàn giao cho đơn vị vận chuyển.');
        }

        // Chỉ cho phép hủy khi đơn ở trạng thái "Cho duyet" (đơn hàng mới)
        if ($borrow->trang_thai !== 'Cho duyet') {
            return back()->with('error', 'Không thể hủy đơn này. Đơn đã được xử lý.');
        }

        // Kiểm tra thêm: nếu đã có trạng thái chi tiết và không phải đơn hàng mới thì không cho hủy
        if ($trangThaiChiTiet && $trangThaiChiTiet !== Borrow::STATUS_DON_HANG_MOI) {
            return back()->with('error', 'Không thể hủy đơn này. Đơn đã được xử lý.');
        }

        $borrow->trang_thai = 'Huy';
        $borrow->save();

        return back()->with('success', 'Đơn đã được hủy.');
    }

    /* ============================================================
        11) XỬ LÝ 11 TRẠNG THÁI MỚI
    ============================================================ */

    /**
     * Xác nhận đơn hàng mới -> Chuyển sang Đang chuẩn bị sách
     */
    public function confirmOrder($id)
    {
        $borrow = Borrow::findOrFail($id);

        try {
            $borrow->transitionTo(
                Borrow::STATUS_DANG_CHUAN_BI_SACH,
                request('ghi_chu'),
                auth()->id()
            );

            return back()->with('success', 'Đã xác nhận đơn hàng. Bắt đầu chuẩn bị sách.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hoàn thành đóng gói -> Chuyển sang Chờ bàn giao vận chuyển
     */
    public function completePackaging($id)
    {
        $borrow = Borrow::findOrFail($id);

        $request = request();
        $request->validate([
            'ma_van_don_di' => 'nullable|string|max:100',
            'don_vi_van_chuyen' => 'nullable|string|max:100',
        ]);

        try {
            // Cập nhật thông tin vận đơn
            if ($request->filled('ma_van_don_di')) {
                $borrow->ma_van_don_di = $request->ma_van_don_di;
            }
            if ($request->filled('don_vi_van_chuyen')) {
                $borrow->don_vi_van_chuyen = $request->don_vi_van_chuyen;
            }
            $borrow->save();

            $borrow->transitionTo(
                Borrow::STATUS_CHO_BAN_GIAO_VAN_CHUYEN,
                $request->ghi_chu,
                auth()->id()
            );

            return back()->with('success', 'Đã hoàn thành đóng gói. Chờ bàn giao vận chuyển.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bàn giao cho shipper -> Chuyển sang Đang giao hàng
     */
    public function handoverToShipper($id)
    {
        $borrow = Borrow::findOrFail($id);

        try {
            $borrow->transitionTo(
                Borrow::STATUS_DANG_GIAO_HANG,
                request('ghi_chu'),
                auth()->id()
            );

            return back()->with('success', 'Đã bàn giao cho đơn vị vận chuyển. Đang giao hàng.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Xác nhận giao hàng thành công (Admin) - Chờ xác nhận từ khách hàng
     */
    public function confirmDeliverySuccess($id)
    {
        $borrow = Borrow::findOrFail($id);

        try {
            $borrow->transitionTo(
                Borrow::STATUS_GIAO_HANG_THANH_CONG,
                request('ghi_chu'),
                auth()->id()
            );

            // Reset trạng thái xác nhận từ khách hàng (nếu có)
            $borrow->customer_confirmed_delivery = false;
            $borrow->customer_confirmed_delivery_at = null;
            $borrow->save();

            // TODO: Gửi email/SMS thông báo cho khách hàng yêu cầu xác nhận

            return back()->with('success', 'Đã xác nhận giao hàng thành công. Đang chờ khách hàng xác nhận đã nhận sách.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Báo giao hàng thất bại
     */
    public function reportDeliveryFailed($id)
    {
        $borrow = Borrow::findOrFail($id);

        try {
            $borrow->transitionTo(
                Borrow::STATUS_GIAO_HANG_THAT_BAI,
                request('ghi_chu'),
                auth()->id()
            );

            return back()->with('warning', 'Đã báo giao hàng thất bại. Sách sẽ được chuyển hoàn.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Khách hàng xác nhận đã nhận sách
     *
     * Theo quy định mới: KHÔNG bắt buộc upload ảnh, khách chỉ cần bấm nút xác nhận.
     * Ảnh tình trạng sách khi giao sẽ do Admin upload ở màn hình vận chuyển.
     */
    public function customerConfirmDelivery(Request $request, $id)
    {
        $borrow = Borrow::findOrFail($id);

        // Kiểm tra quyền: chỉ có thể xác nhận đơn của chính mình
        $user = auth()->user();
        $reader = $user->reader;

        if (!$reader || $borrow->reader_id != $reader->id) {
            return back()->with('error', 'Bạn không có quyền xác nhận đơn này.');
        }

        // Kiểm tra trạng thái: có thể xác nhận khi đang ở trạng thái dang_giao_hang hoặc giao_hang_thanh_cong
        if (
            $borrow->trang_thai_chi_tiet !== Borrow::STATUS_DANG_GIAO_HANG &&
            $borrow->trang_thai_chi_tiet !== Borrow::STATUS_GIAO_HANG_THANH_CONG
        ) {
            return back()->with('error', 'Không thể xác nhận. Đơn hàng chưa ở trạng thái giao hàng.');
        }

        // Kiểm tra đã xác nhận chưa
        if ($borrow->customer_confirmed_delivery) {
            return back()->with('info', 'Bạn đã xác nhận nhận sách trước đó.');
        }

        try {
            // Refresh lại borrow từ database để đảm bảo có dữ liệu mới nhất
            $borrow->refresh();

            $oldStatus = $borrow->trang_thai_chi_tiet;

            \Log::info('Customer confirming delivery', [
                'borrow_id' => $borrow->id,
                'current_status' => $oldStatus,
                'reader_id' => $borrow->reader_id
            ]);

            // Đánh dấu đã xác nhận (không lưu ảnh từ khách hàng)
            $borrow->customer_confirmed_delivery = true;
            $borrow->customer_confirmed_delivery_at = now();

            // Xử lý theo trạng thái hiện tại
            if ($borrow->trang_thai_chi_tiet === Borrow::STATUS_DANG_GIAO_HANG) {
                // Khi khách hàng xác nhận nhận sách, chuyển trực tiếp sang "Đang mượn"
                $borrow->trang_thai_chi_tiet = Borrow::STATUS_DA_MUON_DANG_LUU_HANH;
                $borrow->trang_thai = 'Dang muon';
                $borrow->ngay_giao_thanh_cong = now();
                $borrow->ngay_muon = now();

                // Lưu lại thay đổi
                $borrow->save();

                // ✅ Tự động cập nhật trạng thái thanh toán khi giao hàng thành công
                // 1. Chuyển tất cả payment COD (offline) từ pending → success
                $borrow->payments()
                    ->where('payment_method', 'offline')
                    ->where('payment_status', 'pending')
                    ->whereIn('payment_type', ['deposit', 'borrow_fee', 'shipping_fee'])
                    ->update([
                        'payment_status' => 'success',
                        'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Đã thanh toán COD khi khách hàng xác nhận nhận sách')"),
                        'updated_at' => now()
                    ]);

                // 2. Chuyển tất cả payment Online (online) từ pending → success
                // (Trường hợp thanh toán online nhưng chưa xác nhận)
                $borrow->payments()
                    ->where('payment_method', 'online')
                    ->where('payment_status', 'pending')
                    ->whereIn('payment_type', ['deposit', 'borrow_fee', 'shipping_fee'])
                    ->update([
                        'payment_status' => 'success',
                        'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Xác nhận thanh toán online khi khách hàng xác nhận nhận sách')"),
                        'updated_at' => now()
                    ]);

            } elseif ($borrow->trang_thai_chi_tiet === Borrow::STATUS_GIAO_HANG_THANH_CONG) {
                // Nếu đã ở trạng thái "Giao hàng Thành công", chuyển sang "Đang mượn" khi khách xác nhận
                $borrow->trang_thai_chi_tiet = Borrow::STATUS_DA_MUON_DANG_LUU_HANH;
                $borrow->trang_thai = 'Dang muon';
                $borrow->ngay_muon = now();
                $borrow->save();

                // ✅ Đảm bảo thanh toán đã được cập nhật (nếu chưa)
                // Cập nhật payment nếu vẫn còn pending
                $borrow->payments()
                    ->where('payment_status', 'pending')
                    ->whereIn('payment_type', ['deposit', 'borrow_fee', 'shipping_fee'])
                    ->update([
                        'payment_status' => 'success',
                        'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Đã thanh toán khi khách hàng xác nhận nhận sách')"),
                        'updated_at' => now()
                    ]);
            }

            \Log::info('Customer confirmed delivery and status transitioned successfully', [
                'borrow_id' => $borrow->id,
                'old_status' => $oldStatus,
                'new_status' => $borrow->trang_thai_chi_tiet,
                'customer_confirmed_at' => $borrow->customer_confirmed_delivery_at
            ]);

            return back()->with('success', 'Bạn đã xác nhận nhận sách thành công. Trạng thái đã chuyển sang "Đang mượn".');
        } catch (\Exception $e) {
            \Log::error('Error in customerConfirmDelivery', [
                'borrow_id' => $borrow->id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Khách hàng từ chối nhận sách
     */
    public function customerRejectDelivery($id)
    {
        $borrow = Borrow::findOrFail($id);

        // Kiểm tra quyền: chỉ có thể từ chối đơn của chính mình
        $user = auth()->user();
        $reader = $user->reader;

        if (!$reader || $borrow->reader_id != $reader->id) {
            return back()->with('error', 'Bạn không có quyền từ chối đơn này.');
        }

        // Kiểm tra trạng thái: có thể từ chối khi đang ở trạng thái dang_giao_hang hoặc giao_hang_thanh_cong
        if (
            $borrow->trang_thai_chi_tiet !== Borrow::STATUS_DANG_GIAO_HANG &&
            $borrow->trang_thai_chi_tiet !== Borrow::STATUS_GIAO_HANG_THANH_CONG
        ) {
            return back()->with('error', 'Không thể từ chối. Đơn hàng chưa ở trạng thái giao hàng.');
        }

        // Kiểm tra đã xác nhận hoặc từ chối chưa
        if ($borrow->customer_confirmed_delivery) {
            return back()->with('error', 'Bạn đã xác nhận nhận sách, không thể từ chối.');
        }

        if ($borrow->customer_rejected_delivery) {
            return back()->with('info', 'Bạn đã từ chối nhận sách trước đó.');
        }

        // Validate lý do từ chối + ảnh bắt buộc (bằng chứng khiếu nại)
        $request = request();
        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
            'rejection_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'rejection_reason.required' => 'Vui lòng nhập lý do từ chối nhận sách.',
            'rejection_reason.min' => 'Lý do từ chối phải có ít nhất 10 ký tự.',
            'rejection_reason.max' => 'Lý do từ chối không được vượt quá 1000 ký tự.',
            'rejection_image.required' => 'Vui lòng upload ảnh minh chứng khi từ chối nhận sách.',
            'rejection_image.image' => 'File minh chứng phải là ảnh.',
            'rejection_image.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, webp.',
            'rejection_image.max' => 'Kích thước ảnh không được vượt quá 5MB.',
        ]);

        try {
            DB::beginTransaction();

            // Refresh lại borrow từ database để đảm bảo có dữ liệu mới nhất
            $borrow->refresh();
            $borrow->load(['reader', 'payments']);

            // Upload ảnh minh chứng khi khách từ chối nhận sách
            $rejectionImageUrl = null;
            if ($request->hasFile('rejection_image')) {
                $uploadResult = FileUploadService::uploadToCloudinary(
                    $request->file('rejection_image'),
                    'reject_delivery'
                );
                $rejectionImageUrl = $uploadResult['url'] ?? null;
            }

            $oldStatus = $borrow->trang_thai_chi_tiet;

            \Log::info('Customer rejecting delivery', [
                'borrow_id' => $borrow->id,
                'current_status' => $oldStatus,
                'reader_id' => $borrow->reader_id,
                'rejection_reason' => $request->rejection_reason
            ]);

            // Đánh dấu đã từ chối
            $borrow->customer_rejected_delivery = true;
            $borrow->customer_rejected_delivery_at = now();
            $borrow->customer_rejection_reason = $request->rejection_reason;
            if ($rejectionImageUrl) {
                // Lưu ảnh minh chứng vào anh_hoan_tra (dùng chung để admin xem bằng chứng)
                $existingImages = is_array($borrow->anh_hoan_tra) ? $borrow->anh_hoan_tra : [];
                $existingImages[] = $rejectionImageUrl;
                $borrow->anh_hoan_tra = $existingImages;
            }

            // Chuyển trạng thái sang "Giao hàng Thất bại"
            $borrow->trang_thai_chi_tiet = Borrow::STATUS_GIAO_HANG_THAT_BAI;
            $borrow->ngay_that_bai_giao_hang = now();

            // Thêm ghi chú về lý do từ chối
            $rejectionNote = "\n[Khách hàng từ chối nhận sách - " . now()->format('d/m/Y H:i') . "]\nLý do: " . $request->rejection_reason;
            $borrow->ghi_chu_that_bai = ($borrow->ghi_chu_that_bai ?? '') . $rejectionNote;

            // Lưu lại thay đổi
            $borrow->save();

            // LƯU Ý: KHÔNG hoàn tiền ngay khi từ chối nhận hàng
            // Tiền sẽ chỉ được hoàn về ví khi đơn hàng được hoàn tất (completeOrder)
            // Logic hoàn tiền được xử lý tự động trong BorrowObserver khi trạng thái chuyển sang HOAN_TAT_DON_HANG

            DB::commit();

            \Log::info('Customer rejected delivery successfully', [
                'borrow_id' => $borrow->id,
                'old_status' => $oldStatus,
                'new_status' => $borrow->trang_thai_chi_tiet,
                'customer_rejected_at' => $borrow->customer_rejected_delivery_at,
                'note' => 'Tiền sẽ được hoàn về ví khi đơn hàng được hoàn tất'
            ]);

            // TODO: Gửi email/SMS thông báo cho admin về việc từ chối nhận sách

            $message = 'Bạn đã từ chối nhận sách. Admin sẽ được thông báo và liên hệ với bạn để xử lý. Tiền sẽ được hoàn về ví của bạn sau khi đơn hàng được hoàn tất. Lý do: ' . $request->rejection_reason;

            return back()->with('warning', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in customerRejectDelivery', [
                'borrow_id' => $borrow->id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Khách hàng tạo yêu cầu trả sách
     */
    public function customerRequestReturn($id)
    {
        $borrow = Borrow::findOrFail($id);

        // Kiểm tra quyền: chỉ có thể request return đơn của chính mình
        $user = auth()->user();
        $reader = $user->reader;

        if (!$reader || $borrow->reader_id != $reader->id) {
            return back()->with('error', 'Bạn không có quyền yêu cầu trả sách cho đơn này.');
        }

        // Kiểm tra trạng thái: chỉ có thể request return khi đang ở trạng thái da_muon_dang_luu_hanh
        if ($borrow->trang_thai_chi_tiet !== Borrow::STATUS_DA_MUON_DANG_LUU_HANH) {
            return back()->with('error', 'Chỉ có thể yêu cầu trả sách khi sách đang ở trạng thái "Đã mượn (Đang lưu hành)".');
        }

        // Kiểm tra đã có yêu cầu trả sách chưa
        if ($borrow->trang_thai_chi_tiet === Borrow::STATUS_CHO_TRA_SACH) {
            return back()->with('info', 'Bạn đã tạo yêu cầu trả sách trước đó. Vui lòng chờ admin xử lý.');
        }

        try {
            $ghiChu = request('ghi_chu', '');

            \Log::info('Customer requesting return', [
                'borrow_id' => $borrow->id,
                'current_status' => $borrow->trang_thai_chi_tiet,
                'reader_id' => $borrow->reader_id,
                'ghi_chu' => $ghiChu
            ]);

            // Chuyển trạng thái sang cho_tra_sach (không tự động chuyển tiếp)
            $borrow->transitionTo(
                Borrow::STATUS_CHO_TRA_SACH,
                $ghiChu,
                auth()->id()
            );

            \Log::info('Customer return request created', [
                'borrow_id' => $borrow->id,
                'new_status' => $borrow->trang_thai_chi_tiet
            ]);

            return back()->with('success', 'Đã tạo yêu cầu trả sách thành công. Vui lòng chờ admin xử lý.');
        } catch (\Exception $e) {
            \Log::error('Error in customerRequestReturn', [
                'borrow_id' => $borrow->id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Khách hàng xác nhận hoàn trả sách (chuyển từ cho_tra_sach sang dang_van_chuyen_tra_ve)
     */
    public function customerReturnBook(Request $request, $id)
    {
        $borrow = Borrow::findOrFail($id);

        // Kiểm tra quyền: chỉ có thể return đơn của chính mình
        $user = auth()->user();
        $reader = $user->reader;

        if (!$reader || $borrow->reader_id != $reader->id) {
            return back()->with('error', 'Bạn không có quyền hoàn trả sách cho đơn này.');
        }

        // Kiểm tra trạng thái: có thể return khi đang ở trạng thái cho_tra_sach hoặc dang_muon
        $allowedStatuses = [Borrow::STATUS_CHO_TRA_SACH, Borrow::STATUS_DA_MUON_DANG_LUU_HANH];
        if (!in_array($borrow->trang_thai_chi_tiet, $allowedStatuses)) {
            return back()->with('error', 'Chỉ có thể hoàn trả sách khi đơn đang ở trạng thái mượn hoặc chờ trả sách.');
        }
        try {
            // Validate tình trạng sách (không bắt buộc ảnh từ khách hàng khi trả sách)
            $request->validate([
                'tinh_trang_sach' => 'required|in:binh_thuong,hong_nhe,hong_nang,mat_sach',
                'ghi_chu' => 'nullable|string|max:1000',
            ], [
                'tinh_trang_sach.required' => 'Vui lòng chọn tình trạng sách.',
                'tinh_trang_sach.in' => 'Tình trạng sách không hợp lệ.',
            ]);

            $tinhTrangSach = $request->input('tinh_trang_sach');
            $ghiChu = $request->input('ghi_chu', 'Khách hàng xác nhận hoàn trả sách');

            // Upload ảnh hoàn trả (Hỗ trợ nhiều ảnh)
            $anhHoanTraPaths = [];
            if ($request->hasFile('anh_hoan_tra')) {
                foreach ($request->file('anh_hoan_tra') as $file) {
                    try {
                        $uploadResult = FileUploadService::uploadToCloudinary(
                            $file,
                            'return_books'
                        );
                        $anhHoanTraPaths[] = $uploadResult['url'];
                    } catch (\Exception $e) {
                        \Log::error('Error uploading return image', [
                            'borrow_id' => $borrow->id,
                            'error' => $e->getMessage(),
                        ]);
                        return back()->with('error', 'Lỗi khi tải ảnh: ' . $e->getMessage());
                    }
                }
            }

            // Lưu trạng thái cũ trước khi thay đổi
            $oldStatus = $borrow->trang_thai_chi_tiet;

            \Log::info('Customer returning book', [
                'borrow_id' => $borrow->id,
                'current_status' => $oldStatus,
                'reader_id' => $borrow->reader_id,
                'tinh_trang_sach' => $tinhTrangSach,
                'anh_hoan_tra_count' => count($anhHoanTraPaths),
                'ghi_chu' => $ghiChu
            ]);

            // Cập nhật tình trạng sách và ảnh hoàn trả
            $borrow->tinh_trang_sach = $tinhTrangSach;
            $borrow->anh_hoan_tra = $anhHoanTraPaths;
            $borrow->save();

            // Nếu sách bị hỏng hoặc mất, tự động tạo fine và cập nhật dữ liệu sách
            if (in_array($tinhTrangSach, ['hong_nhe', 'hong_nang', 'mat_sach'])) {
                $this->handleBookDamageOrLoss($borrow, $tinhTrangSach, $anhHoanTraPaths, $ghiChu, $request);
            }

            // Xác định trạng thái mới dựa trên trạng thái hiện tại
            // Theo config: cho_tra_sach -> dang_van_chuyen_tra_ve
            // da_muon_dang_luu_hanh -> dang_van_chuyen_tra_ve (có thể chuyển trực tiếp)
            $newStatus = Borrow::STATUS_DANG_VAN_CHUYEN_TRA_VE;

            // Chuyển trạng thái
            $borrow->transitionTo(
                $newStatus,
                $ghiChu,
                auth()->id()
            );

            \Log::info('Customer return book confirmed, status changed', [
                'borrow_id' => $borrow->id,
                'old_status' => $oldStatus,
                'new_status' => $borrow->trang_thai_chi_tiet,
                'tinh_trang_sach' => $tinhTrangSach
            ]);

            $message = 'Đã xác nhận hoàn trả sách thành công. Sách sẽ được vận chuyển trả về thư viện.';
            if (in_array($tinhTrangSach, ['hong_nhe', 'hong_nang', 'mat_sach'])) {
                $message .= ' Đã tự động tạo phạt và cập nhật dữ liệu sách.';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error in customerReturnBook', [
                'borrow_id' => $borrow->id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Tạo yêu cầu trả sách (Admin)
     */
    public function requestReturn($id)
    {
        $borrow = Borrow::findOrFail($id);

        try {
            $borrow->transitionTo(
                Borrow::STATUS_CHO_TRA_SACH,
                request('ghi_chu'),
                auth()->id()
            );

            return back()->with('success', 'Đã chuyển đơn sang trạng thái "Chờ Trả sách". Khách hàng sẽ thấy nút hoàn trả.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Xác nhận đang vận chuyển trả về
     */
    public function confirmReturnShipping($id)
    {
        $borrow = Borrow::findOrFail($id);

        $request = request();
        $request->validate([
            'ma_van_don_ve' => 'nullable|string|max:100',
        ]);

        try {
            if ($request->filled('ma_van_don_ve')) {
                $borrow->ma_van_don_ve = $request->ma_van_don_ve;
                $borrow->save();
            }

            $borrow->transitionTo(
                Borrow::STATUS_DANG_VAN_CHUYEN_TRA_VE,
                $request->ghi_chu,
                auth()->id()
            );

            return back()->with('success', 'Sách đang được vận chuyển trả về.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Xác nhận đã nhận và kiểm tra sách
     */
    public function confirmReceiveAndCheck($id)
    {
        $borrow = Borrow::with(['items.book', 'items.inventory', 'reader'])->findOrFail($id);

        $request = request();
        $request->validate([
            'tinh_trang_sach' => 'required|in:binh_thuong,hong_nhe,hong_nang,mat_sach',
            'phi_hong_sach' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Cập nhật tình trạng sách và phí hỏng
            $borrow->tinh_trang_sach = $request->tinh_trang_sach;

            if ($request->filled('phi_hong_sach')) {
                $borrow->phi_hong_sach = $request->phi_hong_sach;
            } else {
                // Tự động tính phí hỏng
                $borrow->phi_hong_sach = $borrow->calculateDamageFee();
            }

            $borrow->save();

            // =====================
            // TÍNH PHẠT QUÁ HẠN (THEO ITEM) + TẠO FINE
            // =====================
            $returnDate = now();
            foreach ($borrow->items as $item) {
                // chỉ tính cho các item đang mượn / đang trong flow trả
                if (!$item->ngay_hen_tra) {
                    continue;
                }

                $dueDate = Carbon::parse($item->ngay_hen_tra)->startOfDay();
                $paidLateFine = \App\Services\PricingService::calculateLateReturnFine($dueDate, $returnDate, 1);

                // Cập nhật tiền phạt lên item (cộng dồn với phạt hỏng/mất nếu đã set ở nơi khác)
                if ($paidLateFine > 0) {
                    $current = (float) ($item->tien_phat ?? 0);
                    $item->tien_phat = max($current, $paidLateFine);
                    $item->save();

                    // Tránh tạo trùng fine quá hạn
                    $existingLateFine = Fine::where('borrow_id', $borrow->id)
                        ->where('borrow_item_id', $item->id)
                        ->where('type', 'late_return')
                        ->where('status', 'pending')
                        ->first();

                    if (!$existingLateFine) {
                        Fine::create([
                            'borrow_id' => $borrow->id,
                            'borrow_item_id' => $item->id,
                            'reader_id' => $borrow->reader_id,
                            'amount' => $paidLateFine,
                            'type' => 'late_return',
                            'description' => 'Phạt trả sách trễ hạn',
                            'status' => 'pending',
                            'due_date' => $returnDate->toDateString(),
                            'created_by' => auth()->id() ?? 1,
                        ]);
                    }
                }
            }

            // Recalc tổng tiền (tiền thuê + tổng tiền phạt từ items)
            $borrow->recalculateTotals();
            
            // Kiểm tra và xử lý trễ lâu (khóa tài khoản)
            $isLongOverdue = false;
            if ($borrow->items->count() > 0) {
                foreach ($borrow->items as $item) {
                    if ($item->ngay_hen_tra) {
                        $dueDate = Carbon::parse($item->ngay_hen_tra);
                        $returnDate = now();
                        $isLongOverdue = \App\Services\PricingService::isLongOverdue($dueDate, $returnDate);
                        
                        if ($isLongOverdue) {
                            break; // Chỉ cần một cuốn trễ lâu là đủ
                        }
                    }
                }
            }
            
            // Nếu trễ lâu, khóa tài khoản và trừ cọc + phí ship
            if ($isLongOverdue && $borrow->reader) {
                $reader = $borrow->reader;
                $reader->trang_thai = 'khoa_muon'; // Khóa mượn
                $reader->save();
                
                // Trừ cọc + phí ship mặc định (20k)
                $defaultShippingFee = config('pricing.shipping.default_fee', 20000);
                $penaltyAmount = $borrow->tien_coc + $defaultShippingFee;
                
                // Cập nhật tien_coc_hoan_tra = 0 (không hoàn cọc)
                $borrow->tien_coc_hoan_tra = 0;
                $borrow->ghi_chu = ($borrow->ghi_chu ?? '') . "\n⚠️ Trễ lâu: Tài khoản đã bị khóa mượn. Trừ cọc + phí ship: " . number_format($penaltyAmount) . " VNĐ";
                $borrow->save();
            } else {
                $borrow->updateRefundAmount();
            }

            $borrow->transitionTo(
                Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                $request->ghi_chu ?? 'Đã nhận và kiểm tra sách. Tình trạng: ' . $borrow->tinh_trang_sach . ($isLongOverdue ? ' (Tài khoản đã bị khóa do trễ lâu)' : ''),
                auth()->id()
            );

            // Cập nhật trạng thái chi tiết cho từng item
            foreach ($borrow->items as $item) {
                $itemStatus = 'Da tra';
                $inventoryStatus = 'Da tra'; // Trạng thái tạm thời trước khi nhập kho/xử lý

                if ($borrow->tinh_trang_sach === 'mat_sach') {
                    $itemStatus = 'Mat sach';
                    $inventoryStatus = 'Mat';
                } elseif (in_array($borrow->tinh_trang_sach, ['hong_nhe', 'hong_nang'])) {
                    $itemStatus = 'Hong';
                    $inventoryStatus = 'Hong';
                }

                $item->update([
                    'trang_thai' => $itemStatus,
                    'tinh_trang_sach_cuoi' => $borrow->tinh_trang_sach,
                    'ngay_tra_thuc_te' => now(),
                ]);

                if ($item->inventory) {
                    $item->inventory->update([
                        'status' => $inventoryStatus
                    ]);
                }

                // Nếu mất sách, trừ số lượng trong kho
                if ($borrow->tinh_trang_sach === 'mat_sach' && $item->book) {
                    $item->book->decrement('so_luong');
                }
            }

            DB::commit();

            return back()->with('success', 'Đã nhận và kiểm tra sách. Phí hỏng (nếu có): ' . number_format($borrow->phi_hong_sach) . ' VNĐ');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hoàn tất đơn hàng
     * 
     * Khi khách đã xác nhận nhận sách và trả sách về, chỉ hoàn tiền cọc (đã trừ phí hỏng sách nếu có)
     * Áp dụng cho mọi hình thức thanh toán (online và offline) vì lúc này khách đã mượn và đã thanh toán tiền thuê
     */
    public function completeOrder($id)
    {
        $borrow = Borrow::with(['reader', 'items', 'fines'])->findOrFail($id);

        // Kiểm tra xem còn phạt chưa thanh toán không
        $pendingFinesCount = $borrow->fines()->where('status', 'pending')->count();
        if ($pendingFinesCount > 0) {
            return back()->with('error', "Không thể hoàn tất đơn hàng. Vui lòng thanh toán dứt điểm {$pendingFinesCount} khoản phạt trước.");
        }

        try {
            DB::beginTransaction();

            // Đảm bảo tien_coc được tính lại từ items (nếu chưa đúng)
            $borrow->recalculateTotals();
            $borrow->refresh();

            // Log thông tin ban đầu để debug
            \Log::info('Starting completeOrder', [
                'borrow_id' => $borrow->id,
                'tien_coc' => $borrow->tien_coc,
                'phi_hong_sach' => $borrow->phi_hong_sach,
                'reader_id' => $borrow->reader_id,
                'has_reader' => $borrow->reader ? true : false,
                'reader_user_id' => $borrow->reader ? $borrow->reader->user_id : null
            ]);

            // Tính toán tiền hoàn cọc (tiền cọc - phí hỏng sách nếu có)
            // Chỉ hoàn tiền cọc, không hoàn tiền thuê vì khách đã mượn và sử dụng sách
            // LƯU Ý: Hoàn tiền sẽ được xử lý tự động bởi BorrowObserver khi transitionTo() được gọi
            
            // Kiểm tra và tính toán hoàn lại phí thuê nếu trả sớm
            $earlyReturnRefund = 0;
            if ($borrow->ngay_muon && $borrow->items->count() > 0) {
                $ngayMuon = Carbon::parse($borrow->ngay_muon);
                $ngayTraThucTe = now();
                
                // Tính số ngày đã mượn
                $daysBorrowed = $ngayMuon->diffInDays($ngayTraThucTe);
                
                // Lấy số ngày dự kiến mượn từ items (lấy max)
                $daysExpected = $borrow->items->max(function($item) {
                    if ($item->ngay_hen_tra) {
                        $ngayMuon = Carbon::parse($borrow->ngay_muon);
                        $ngayHenTra = Carbon::parse($item->ngay_hen_tra);
                        return $ngayMuon->diffInDays($ngayHenTra);
                    }
                    return 0;
                });
                
                // Nếu trả sớm, tính hoàn lại phí thuê
                if ($daysBorrowed < $daysExpected && $daysExpected > 0) {
                    $totalRentalFee = $borrow->tien_thue ?? 0;
                    $earlyReturnRefund = \App\Services\PricingService::calculateEarlyReturnRefund(
                        $totalRentalFee,
                        $daysBorrowed,
                        $daysExpected
                    );
                    
                    // Lưu số tiền hoàn lại phí thuê vào ghi chú
                    if ($earlyReturnRefund > 0) {
                        $borrow->ghi_chu = ($borrow->ghi_chu ?? '') . "\nHoàn lại phí thuê do trả sớm: " . number_format($earlyReturnRefund) . " VNĐ";
                    }
                }
            }
            
            $borrow->updateRefundAmount();
            // Refresh lại để lấy giá trị tien_coc_hoan_tra mới nhất
            $borrow->refresh();
            
            // Cộng thêm số tiền hoàn lại phí thuê vào tien_coc_hoan_tra
            if ($earlyReturnRefund > 0) {
                $borrow->tien_coc_hoan_tra += $earlyReturnRefund;
                $borrow->save();
            }

            $borrow->transitionTo(
                Borrow::STATUS_HOAN_TAT_DON_HANG,
                request('ghi_chu') ?? 'Đơn hàng đã hoàn tất. Tiền cọc đã hoàn trả: ' . number_format($borrow->tien_coc_hoan_tra) . ' VNĐ vào ví',
                auth()->id()
            );

            // Cập nhật inventory về trạng thái 'Co san' (Có sẵn) - Chỉ dành cho sách bình thường
            foreach ($borrow->items as $item) {
                if ($item->inventory) {
                    // Nếu sách bị hỏng hoặc mất, giữ nguyên trạng thái inventory đã được set ở bước kiểm tra
                    if (in_array($item->trang_thai, ['Hong', 'Mat sach'])) {
                        continue;
                    }

                    $item->inventory->update([
                        'status' => 'Co san'
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Đã hoàn tất đơn hàng. Tiền cọc hoàn trả: ' . number_format($borrow->tien_coc_hoan_tra) . ' VNĐ đã được chuyển vào ví của bạn.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error completing order and refunding to wallet: ' . $e->getMessage(), [
                'borrow_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Xem chi tiết trạng thái và lịch sử
     */
    public function statusDetail($id)
    {
        $borrow = Borrow::with([
            'reader',
            'librarian',
            'items.book',
            'nguoiChuanBi',
            'nguoiGiaoHang',
            'nguoiKiemTra',
            'nguoiHoanCoc'
        ])->findOrFail($id);

        // Lấy config trạng thái
        $allStatuses = config('borrow_status.statuses');

        return view('admin.borrows.status_detail', compact('borrow', 'allStatuses'));
    }

    /**
     * Hoàn tiền cho đơn đã hủy (nếu chưa được hoàn)
     */
    public function refundCancelledOrder($id)
    {
        try {
            $borrow = Borrow::with(['reader', 'items', 'payments'])->findOrFail($id);

            // Kiểm tra đơn có phải đã hủy không
            if ($borrow->trang_thai !== 'Huy') {
                return back()->with('error', 'Đơn hàng này chưa bị hủy. Chỉ có thể hoàn tiền cho đơn đã hủy.');
            }

            // Kiểm tra đã có transaction hoàn tiền chưa
            $existingTransaction = \App\Models\WalletTransaction::where('reference_type', Borrow::class)
                ->where('reference_id', $borrow->id)
                ->where('type', 'refund')
                ->first();

            if ($existingTransaction) {
                return back()->with('info', 'Đơn hàng này đã được hoàn tiền rồi. Số tiền: ' . number_format((float) $existingTransaction->amount) . ' VNĐ');
            }

            DB::beginTransaction();

            // Kiểm tra các điều kiện
            if (!$borrow->reader) {
                return back()->with('error', 'Không tìm thấy thông tin độc giả');
            }

            if (!$borrow->reader->user_id) {
                return back()->with('error', 'Độc giả chưa liên kết với tài khoản user');
            }

            // Tính lại tổng tiền từ items
            $borrow->recalculateTotals();
            $borrow->refresh();

            if ($borrow->tien_coc <= 0) {
                return back()->with('error', 'Đơn hàng không có tiền cọc');
            }

            // Chỉ hoàn tiền nếu khách đã thanh toán ONLINE (đã trả tiền rồi)
            // COD/Offline không cần hoàn vì chưa thanh toán
            $hasOnlinePayment = $borrow->payments()
                ->where('payment_method', 'online')
                ->where('payment_status', 'success')
                ->exists();

            // Xác định số tiền cần hoàn
            $refundAmount = 0;
            $refundDescription = '';

            if ($hasOnlinePayment) {
                // Online: hoàn đầy đủ - tiền cọc + tiền thuê + tiền ship (nếu có)
                $refundAmount = $borrow->tien_coc + $borrow->tien_thue + ($borrow->tien_ship ?? 0);
                $refundDescription = 'Hoàn tiền cho đơn mượn #' . $borrow->id . ' (Đơn hàng đã hủy - thanh toán online)';

                $details = [];
                if ($borrow->tien_coc > 0) {
                    $details[] = 'Tiền cọc: ' . number_format($borrow->tien_coc, 0, ',', '.') . ' VNĐ';
                }
                if ($borrow->tien_thue > 0) {
                    $details[] = 'Phí thuê: ' . number_format($borrow->tien_thue, 0, ',', '.') . ' VNĐ';
                }
                if (($borrow->tien_ship ?? 0) > 0) {
                    $details[] = 'Phí ship: ' . number_format($borrow->tien_ship, 0, ',', '.') . ' VNĐ';
                }

                if (!empty($details)) {
                    $refundDescription .= ' - ' . implode(', ', $details);
                }
            } else {
                // COD/Offline: KHÔNG hoàn tiền vì chưa thanh toán
                return back()->with('info', 'Đơn hàng này sử dụng thanh toán khi nhận hàng (COD), chưa thanh toán nên không cần hoàn tiền.');
            }

            if ($refundAmount <= 0) {
                return back()->with('error', 'Không có tiền cần hoàn');
            }

            $wallet = Wallet::getOrCreateForUser($borrow->reader->user_id);
            $balanceBefore = $wallet->balance;

            $wallet->refund(
                $refundAmount,
                $refundDescription,
                $borrow
            );

            $wallet->refresh();
            $balanceAfter = $wallet->balance;

            DB::commit();

            \Log::info('Manually refunded cancelled order', [
                'borrow_id' => $borrow->id,
                'user_id' => $borrow->reader->user_id,
                'amount' => $refundAmount,
                'wallet_id' => $wallet->id,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter
            ]);

            return back()->with('success', 'Đã hoàn tiền thành công! Số tiền: ' . number_format($refundAmount) . ' VNĐ đã được chuyển vào ví của khách hàng.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error refunding cancelled order', [
                'borrow_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Kiểm tra và sửa các đơn hàng đã hoàn thành nhưng chưa được hoàn tiền cọc
     */
    public function checkAndFixRefunds()
    {
        try {
            // Tìm các đơn hàng đã hoàn thành
            $completedBorrows = Borrow::where('trang_thai_chi_tiet', Borrow::STATUS_HOAN_TAT_DON_HANG)
                ->with(['reader', 'items'])
                ->get();

            $results = [
                'total' => $completedBorrows->count(),
                'fixed' => 0,
                'skipped' => 0,
                'errors' => 0,
                'details' => []
            ];

            foreach ($completedBorrows as $borrow) {
                $detail = [
                    'borrow_id' => $borrow->id,
                    'status' => 'skipped',
                    'message' => '',
                    'amount' => 0
                ];

                // Kiểm tra các điều kiện
                if (!$borrow->reader) {
                    $detail['message'] = 'Không có thông tin độc giả';
                    $results['skipped']++;
                    $results['details'][] = $detail;
                    continue;
                }

                if (!$borrow->reader->user_id) {
                    $detail['message'] = 'Độc giả chưa liên kết với user (user_id = null)';
                    $results['skipped']++;
                    $results['details'][] = $detail;
                    continue;
                }

                // Tính lại tổng tiền từ items
                $borrow->recalculateTotals();
                $borrow->refresh();

                if ($borrow->tien_coc <= 0) {
                    $detail['message'] = 'Đơn hàng không có tiền cọc';
                    $results['skipped']++;
                    $results['details'][] = $detail;
                    continue;
                }

                // Tính lại tien_coc_hoan_tra
                $borrow->updateRefundAmount();
                $borrow->refresh();

                if ($borrow->tien_coc_hoan_tra <= 0) {
                    $detail['message'] = 'Tiền cọc hoàn trả <= 0 (có thể do phí hỏng sách)';
                    $results['skipped']++;
                    $results['details'][] = $detail;
                    continue;
                }

                // Kiểm tra xem đã có transaction hoàn tiền chưa
                $existingTransaction = \App\Models\WalletTransaction::where('reference_type', Borrow::class)
                    ->where('reference_id', $borrow->id)
                    ->where('type', 'refund')
                    ->first();

                if ($existingTransaction) {
                    $detail['status'] = 'already_refunded';
                    $detail['message'] = 'Đã có giao dịch hoàn tiền';
                    $detail['amount'] = $existingTransaction->amount;
                    $results['skipped']++;
                    $results['details'][] = $detail;
                    continue;
                }

                // Thử hoàn tiền
                try {
                    DB::beginTransaction();

                    $wallet = Wallet::getOrCreateForUser($borrow->reader->user_id);
                    $balanceBefore = $wallet->balance;

                    $description = 'Hoàn tiền cọc cho đơn mượn #' . $borrow->id;
                    if ($borrow->phi_hong_sach > 0) {
                        $description .= ' (Đã trừ phí hỏng sách: ' . number_format($borrow->phi_hong_sach, 0, ',', '.') . ' VNĐ)';
                    }

                    $wallet->refund(
                        $borrow->tien_coc_hoan_tra,
                        $description,
                        $borrow
                    );

                    $wallet->refresh();
                    $balanceAfter = $wallet->balance;

                    DB::commit();

                    $detail['status'] = 'fixed';
                    $detail['message'] = 'Đã hoàn tiền thành công';
                    $detail['amount'] = $borrow->tien_coc_hoan_tra;
                    $detail['balance_before'] = $balanceBefore;
                    $detail['balance_after'] = $balanceAfter;
                    $detail['user_id'] = $borrow->reader->user_id;

                    $results['fixed']++;

                    \Log::info('Fixed refund for completed order', [
                        'borrow_id' => $borrow->id,
                        'user_id' => $borrow->reader->user_id,
                        'amount' => $borrow->tien_coc_hoan_tra,
                        'wallet_id' => $wallet->id
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    $detail['status'] = 'error';
                    $detail['message'] = 'Lỗi: ' . $e->getMessage();
                    $results['errors']++;

                    \Log::error('Error fixing refund for order', [
                        'borrow_id' => $borrow->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                $results['details'][] = $detail;
            }

            // Nếu request là AJAX hoặc có header Accept: application/json, trả về JSON
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json($results, 200);
            }

            // Nếu không, trả về view HTML
            return view('admin.borrows.check_refunds_result', compact('results'));

        } catch (\Exception $e) {
            \Log::error('Error in checkAndFixRefunds: ' . $e->getMessage());

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý tự động khi khách báo hỏng/mất sách
     * Tạo fine và cập nhật dữ liệu sách
     */
    private function handleBookDamageOrLoss($borrow, $tinhTrangSach, $anhHoanTra, $ghiChu, $request)
    {
        try {
            // Load borrow với items và reader
            $borrow->load(['borrowItems.book', 'borrowItems.inventory', 'reader']);

            foreach ($borrow->borrowItems as $item) {
                if (!$item->book || !$item->inventory) {
                    continue;
                }

                $book = $item->book;
                $inventory = $item->inventory;

                // Xác định loại phạt và mức độ hư hỏng
                $fineType = 'damaged_book';
                $damageSeverity = 'nhe';
                $conditionAfter = 'Hong';

                if ($tinhTrangSach === 'mat_sach') {
                    $fineType = 'lost_book';
                    $damageSeverity = 'mat_sach';
                    $conditionAfter = 'Mat';
                } elseif ($tinhTrangSach === 'hong_nang') {
                    $damageSeverity = 'nang';
                    $conditionAfter = 'Hong';
                } elseif ($tinhTrangSach === 'hong_nhe') {
                    $damageSeverity = 'nhe';
                    $conditionAfter = 'Trung binh'; // Hỏng nhẹ thì tình trạng sau là trung bình
                }

                // Tính tiền phạt
                $gia = $book->gia;
                $loai = $book->loai_sach;
                $tinhTrangKhiMuon = $inventory->condition;

                if ($tinhTrangSach === 'mat_sach') {
                    // Mất sách: phạt 100% nếu sách quý, 80% nếu mới/tốt, 70% nếu cũ
                    if ($loai === 'quy') {
                        $phatGoc = $gia;
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
                } else {
                    // Hỏng sách: tính theo mức độ
                    if ($loai === 'quy') {
                        $phatGoc = $gia; // 100%
                    } else {
                        switch ($tinhTrangKhiMuon) {
                            case 'Moi':
                            case 'Tot':
                                $phatGoc = $damageSeverity === 'nang'
                                    ? round($gia * 0.8)
                                    : round($gia * 0.5); // Hỏng nhẹ phạt ít hơn
                                break;
                            default:
                                $phatGoc = $damageSeverity === 'nang'
                                    ? round($gia * 0.7)
                                    : round($gia * 0.4);
                        }
                    }
                }

                // Xác định loại hư hỏng
                $damageType = 'khac';
                $damageDescription = $ghiChu ?: 'Khách hàng báo ' . ($tinhTrangSach === 'mat_sach' ? 'mất sách' : 'sách bị hỏng');

                if ($request->filled('damage_type')) {
                    $damageType = $request->damage_type;
                }
                if ($request->filled('damage_description')) {
                    $damageDescription = $request->damage_description;
                }

                // Xử lý ảnh: copy từ anh_hoan_tra sang damage_images
                $damageImages = [];
                if (is_array($anhHoanTra) && count($anhHoanTra) > 0) {
                    foreach ($anhHoanTra as $imgPath) {
                        // Nếu là đường dẫn Cloudinary (URL), không cần copy
                        if (strpos($imgPath, 'http') === 0) {
                            $damageImages[] = $imgPath;
                            continue;
                        }

                        // Copy ảnh vào thư mục fines/damage_images
                        $sourcePath = storage_path('app/public/' . $imgPath);
                        if (file_exists($sourcePath)) {
                            $newPath = 'fines/damage_images/' . basename($imgPath);
                            $destinationPath = storage_path('app/public/' . $newPath);

                            // Tạo thư mục nếu chưa có
                            $dir = dirname($destinationPath);
                            if (!is_dir($dir)) {
                                mkdir($dir, 0755, true);
                            }

                            if (copy($sourcePath, $destinationPath)) {
                                $damageImages[] = $newPath;
                            }
                        }
                    }
                } elseif (is_string($anhHoanTra)) {
                    // Fallback for single image as string
                    $sourcePath = storage_path('app/public/' . $anhHoanTra);
                    if (file_exists($sourcePath)) {
                        $newPath = 'fines/damage_images/' . basename($anhHoanTra);
                        $destinationPath = storage_path('app/public/' . $newPath);
                        $dir = dirname($destinationPath);
                        if (!is_dir($dir))
                            mkdir($dir, 0755, true);
                        if (copy($sourcePath, $destinationPath)) {
                            $damageImages[] = $newPath;
                        }
                    }
                }

                // Cập nhật BorrowItem
                $item->update([
                    'trang_thai' => $tinhTrangSach === 'mat_sach' ? 'Mat sach' : 'Hong',
                    'tien_phat' => $phatGoc,
                ]);

                // Cập nhật Inventory
                if ($inventory) {
                    $inventory->update([
                        'status' => $tinhTrangSach === 'mat_sach' ? 'Mat' : 'Hong',
                        'condition' => $conditionAfter,
                    ]);
                }

                // Nếu mất sách, trừ số lượng sách
                if ($tinhTrangSach === 'mat_sach' && $book->so_luong > 0) {
                    $book->decrement('so_luong');
                }

                // Kiểm tra xem đã có fine cho item này chưa (tránh tạo trùng)
                $existingFine = Fine::where('borrow_item_id', $item->id)
                    ->where('type', $fineType)
                    ->where('status', 'pending')
                    ->first();

                if (!$existingFine) {
                    $firstImg = is_array($damageImages) && count($damageImages) > 0 ? $damageImages[0] : (is_string($damageImages) ? $damageImages : null);

                    Fine::create([
                        'borrow_id' => $borrow->id,
                        'borrow_item_id' => $item->id,
                        'reader_id' => $borrow->reader_id,

                        'amount' => $phatGoc,
                        'img' => $firstImg,
                        'damage_images' => $damageImages,
                        'damage_severity' => $damageSeverity,
                        'type' => $fineType,
                        'status' => 'pending',

                        'description' => $damageDescription,
                        'notes' => $ghiChu,
                        'created_by' => auth()->id() ?? 1,
                    ]);



                    \Log::info('Auto-created fine for damaged/lost book', [
                        'borrow_id' => $borrow->id,
                        'borrow_item_id' => $item->id,
                        'fine_type' => $fineType,
                        'amount' => $phatGoc,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in handleBookDamageOrLoss', [
                'borrow_id' => $borrow->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Không throw exception để không làm gián đoạn quá trình trả sách
        }
    }

    /* ============================================================
        GIA HẠN PHIẾU MƯỢN (ADMIN - THÊM 5 NGÀY + 25,000Đ)
    ============================================================ */
    public function extend($id)
    {
        if (!auth()->check() || !auth()->user()->can('edit-borrows')) {
            return back()->with('error', 'Bạn không có quyền thực hiện thao tác này.');
        }

        $borrow = Borrow::with('items')->findOrFail($id);

        // Kiểm tra có sách đang mượn không
        $itemsDangMuon = collect($borrow->items)->filter(function($item) {
            return $item->trang_thai === 'Dang muon';
        });
        
        if ($itemsDangMuon->isEmpty()) {
            return back()->with('error', 'Không có sách nào đang mượn để gia hạn.');
        }

        $extendedCount = 0;
        $totalExtensionFee = 0;
        $days = 5; // Gia hạn 5 ngày
        $dailyFee = 5000; // 5000đ/ngày

        foreach ($itemsDangMuon as $item) {
            if ($item->canExtend()) {
                if ($item->extend($days, $dailyFee)) {
                    $extendedCount++;
                    $totalExtensionFee += ($days * $dailyFee);
                }
            }
        }

        if ($extendedCount > 0) {
            // Cập nhật tổng tiền thuê của phiếu mượn
            $borrow->update([
                'tien_thue' => ($borrow->tien_thue ?? 0) + $totalExtensionFee,
                'tong_tien' => ($borrow->tong_tien ?? 0) + $totalExtensionFee,
                // Reset cờ yêu cầu gia hạn của khách (nếu có)
                'customer_extension_requested' => false,
                'customer_extension_days' => null,
                'customer_extension_requested_at' => null,
            ]);

            return back()->with('success', "Đã gia hạn thành công {$extendedCount} sách. Thêm {$days} ngày và " . number_format($totalExtensionFee) . "₫ tiền thuê.");
        } else {
            return back()->with('error', 'Không thể gia hạn. Các sách đã quá hạn hoặc đã gia hạn đủ số lần cho phép.');
        }
    }

    /* ============================================================
        KHÁCH HÀNG GỬI YÊU CẦU GIA HẠN (CHỜ ADMIN DUYỆT)
       - Không thay đổi hạn trả / tiền ngay lập tức
       - Admin dùng nút Gia hạn trong backend để xử lý khi phù hợp
    ============================================================ */
    public function customerExtendBorrow(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !$user->reader) {
            return redirect()->route('account.borrowed-books')
                ->with('error', 'Bạn cần có thẻ độc giả để gửi yêu cầu gia hạn.');
        }

        $borrow = Borrow::with('items')
            ->where('id', $id)
            ->where('reader_id', $user->reader->id)
            ->first();

        if (!$borrow) {
            return back()->with('error', 'Phiếu mượn không tồn tại hoặc không thuộc về bạn.');
        }

        // Chỉ cho phép gửi yêu cầu khi đơn đang mượn và chưa quá hạn theo policy chung
        if (!$borrow->canExtend()) {
            return back()->with('error', 'Không thể gửi yêu cầu gia hạn (đơn đã quá hạn hoặc đã gia hạn đủ số lần).');
        }

        // Nếu đã có yêu cầu gia hạn đang chờ, không cho gửi thêm
        if ($borrow->customer_extension_requested) {
            return back()->with('info', 'Bạn đã gửi yêu cầu gia hạn trước đó. Vui lòng chờ thư viện xử lý.');
        }

        $days = 5; // cố định +5 ngày

        $borrow->update([
            'customer_extension_requested' => true,
            'customer_extension_days' => $days,
            'customer_extension_requested_at' => now(),
        ]);

        return back()->with(
            'success',
            "Đã gửi yêu cầu gia hạn thêm {$days} ngày. Thư viện sẽ kiểm tra (ví dụ: có độc giả khác đặt mượn) và duyệt nếu phù hợp. Phí gia hạn sẽ được tính cùng lúc khi bạn trả sách."
        );
    }

}
