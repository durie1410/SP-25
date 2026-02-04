<?php

namespace App\Http\Controllers;

use App\Models\ShippingLog;
use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Models\Fine;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ShippingLogController extends Controller
{
    /**
     * Hiển thị tất cả shipping logs
     */
    public function index(Request $request)
    {
        $query = ShippingLog::with(['borrow.reader', 'borrow.items.book', 'borrow.payments'])
            ->orderBy('id', 'desc');

        // Tìm kiếm theo mã đơn hàng hoặc tên người đặt
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('borrow', function($q2) use ($search) {
                      $q2->where('id', 'like', "%{$search}%")
                         ->orWhereHas('reader', function($q3) use ($search) {
                             $q3->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(20);

        // Đếm số lượng theo từng trạng thái (11 trạng thái mới)
        $statusCounts = [
            'all' => ShippingLog::count(),
            'don_hang_moi' => ShippingLog::where('status', 'don_hang_moi')->count(),
            'dang_chuan_bi_sach' => ShippingLog::where('status', 'dang_chuan_bi_sach')->count(),
            'cho_ban_giao_van_chuyen' => ShippingLog::where('status', 'cho_ban_giao_van_chuyen')->count(),
            'dang_giao_hang' => ShippingLog::where('status', 'dang_giao_hang')->count(),
            'giao_hang_thanh_cong' => ShippingLog::where('status', 'giao_hang_thanh_cong')->count(),
            'giao_hang_that_bai' => ShippingLog::where('status', 'giao_hang_that_bai')->count(),
            'da_muon_dang_luu_hanh' => ShippingLog::where('status', 'da_muon_dang_luu_hanh')->count(),
            'cho_tra_sach' => ShippingLog::where('status', 'cho_tra_sach')->count(),
            'dang_van_chuyen_tra_ve' => ShippingLog::where('status', 'dang_van_chuyen_tra_ve')->count(),
            'da_nhan_va_kiem_tra' => ShippingLog::where('status', 'da_nhan_va_kiem_tra')->count(),
            'hoan_tat_don_hang' => ShippingLog::where('status', 'hoan_tat_don_hang')->count(),
        ];

        return view('admin.shipping_logs.index', compact('logs', 'statusCounts'));
    }

    /**
     * Hiển thị shipping logs theo 1 Borrow
     */
    public function showByBorrow($borrowId)
    {
        $borrow = Borrow::with([
            'items.book',
            'shippingLogs.item.book'
        ])->findOrFail($borrowId);

        return view('admin.shipping_logs.by_borrow', compact('borrow'));
    }

    /**
     * Hiển thị shipping logs theo 1 BorrowItem
     */
public function show($id)
{
    // load shipping log cùng với borrow -> reader, items -> book, payments
    $log = \App\Models\ShippingLog::with([
        'borrow.reader',
        'borrow.items.book',
        'borrow.payments'           // nếu Borrow có payments relation
    ])->findOrFail($id);

    // Load fines for this borrow (if any)
    $fines = collect();
    if ($log->borrow) {
        $fines = Fine::with(['borrowItem.book', 'reader', 'creator'])
            ->where('borrow_id', $log->borrow->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    return view('admin.shipping_logs.show', compact('log', 'fines'));
} 

    /**
     * Hiển thị trang sửa đơn hàng với form cập nhật trạng thái
     */
    public function edit($id)
    {
        // load shipping log cùng với borrow -> reader, items -> book, payments
        $log = \App\Models\ShippingLog::with([
            'borrow.reader',
            'borrow.items.book',
            'borrow.payments'
        ])->findOrFail($id);

        // Load fines for this borrow (if any)
        $fines = collect();
        if ($log->borrow) {
            $fines = Fine::with(['borrowItem.book', 'reader', 'creator'])
                ->where('borrow_id', $log->borrow->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.shipping_logs.edit', compact('log', 'fines'));
    }

    /**
     * Thêm Shipping Log mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'borrow_id'      => 'required|exists:borrows,id',
            'borrow_item_id' => 'required|exists:borrow_items,id',
            'status'         => 'required|string',
            'shipper_note'   => 'nullable|string',
            'receiver_note'  => 'nullable|string',
            'proof_image'    => 'nullable|image|max:2048'
        ]);

        $data = $request->all();

        // Lưu ảnh minh chứng
        if ($request->hasFile('proof_image')) {
            try {
                $result = FileUploadService::uploadImage(
                    $request->file('proof_image'),
                    'shipping_proofs', // Directory name
                    [
                        'max_size' => 2048, // 2MB
                        'resize' => true,
                        'width' => 800,
                        'height' => 800,
                        'disk' => 'public',
                    ]
                );
                $data['proof_image'] = $result['path'];
            } catch (\Exception $e) {
                \Log::error('Upload proof image error:', ['message' => $e->getMessage()]);
                return redirect()->back()
                    ->withErrors(['proof_image' => $e->getMessage()])
                    ->withInput();
            }
        }

        ShippingLog::create($data);

        return back()->with('success', 'Đã thêm log giao hàng.');
    }

    /**
     * Cập nhật trạng thái giao hàng
     */
public function updateStatus(Request $request, $id)
{
    try {
        \Log::info('updateStatus called', [
            'id' => $id,
            'status' => $request->status,
            'all_data' => $request->all()
        ]);
        
    $log = ShippingLog::findOrFail($id);
        
        if (!$log) {
            \Log::error('ShippingLog not found', ['id' => $id]);
            return redirect()->back()->withErrors(['error' => 'Không tìm thấy đơn hàng!']);
        }

    // Validate với 11 trạng thái mới
        $validated = $request->validate([
        'status' => 'required|string|in:don_hang_moi,dang_chuan_bi_sach,cho_ban_giao_van_chuyen,dang_giao_hang,giao_hang_thanh_cong,giao_hang_that_bai,da_muon_dang_luu_hanh,cho_tra_sach,dang_van_chuyen_tra_ve,da_nhan_va_kiem_tra,hoan_tat_don_hang',
        'tinh_trang_sach' => 'nullable|in:binh_thuong,hong_nhe,hong_nang,mat_sach',
        'ghi_chu_kiem_tra' => 'nullable|string',
        'ghi_chu_hoan_coc' => 'nullable|string',
        'receiver_note' => 'nullable|string',
        'delivered_at'  => 'nullable|date',
        'proof_image'   => 'nullable|image|max:2048',
        'ma_van_don' => 'nullable|string|max:100',
        'don_vi_van_chuyen' => 'nullable|string|max:100',
            'failure_reason' => 'nullable|in:loi_khach_hang,loi_thu_vien',
            'failure_proof_image' => 'nullable|image|max:2048',
        ]);
        
        \Log::info('Validation passed', ['validated' => $validated]);
    
    // Nếu trạng thái là giao_hang_that_bai, yêu cầu failure_reason
    if ($request->status === 'giao_hang_that_bai') {
        $request->validate([
            'failure_reason' => 'required|in:loi_khach_hang,loi_thu_vien',
        ]);
        
        // Nếu lỗi do thư viện, yêu cầu ảnh
        if ($request->failure_reason === 'loi_thu_vien') {
            $request->validate([
                'failure_proof_image' => 'required|image|max:2048',
            ]);
        }
    }

    // Nếu trạng thái là 'da_giao', có thể yêu cầu ảnh minh chứng (tùy chọn)
    // if ($request->status === 'da_giao' && !$request->hasFile('proof_image') && !$log->proof_image) {
    //     return back()->withErrors(['proof_image' => 'Phải tải ảnh minh chứng trước khi đánh dấu đã giao.']);
    // }

    // Nếu có file, lưu vào storage
    if ($request->hasFile('proof_image')) {
        try {
            // Xóa ảnh cũ nếu có
            if ($log->proof_image && Storage::disk('public')->exists($log->proof_image)) {
                FileUploadService::deleteFile($log->proof_image, 'public');
            }
            
            // Upload ảnh mới sử dụng FileUploadService
            $result = FileUploadService::uploadImage(
                $request->file('proof_image'),
                'shipping_proofs', // Directory name - không được rỗng
                [
                    'max_size' => 2048, // 2MB
                    'resize' => true,
                    'width' => 800,
                    'height' => 800,
                    'disk' => 'public',
                ]
            );
            $log->proof_image = $result['path'];
        } catch (\Exception $e) {
            \Log::error('Upload proof image error:', ['message' => $e->getMessage()]);
            return redirect()->back()
                ->withErrors(['proof_image' => $e->getMessage()])
                ->withInput();
        }
    }
    
    // Xử lý ảnh minh chứng giao hàng thất bại
    if ($request->hasFile('failure_proof_image')) {
        try {
            // Xóa ảnh cũ nếu có
            if ($log->failure_proof_image && Storage::disk('public')->exists($log->failure_proof_image)) {
                FileUploadService::deleteFile($log->failure_proof_image, 'public');
            }
            
            // Upload ảnh mới sử dụng FileUploadService
            $result = FileUploadService::uploadImage(
                $request->file('failure_proof_image'),
                'failure_proofs', // Directory name
                [
                    'max_size' => 2048, // 2MB
                    'resize' => true,
                    'width' => 800,
                    'height' => 800,
                    'disk' => 'public',
                ]
            );
            $log->failure_proof_image = $result['path'];
        } catch (\Exception $e) {
            \Log::error('Upload failure proof image error:', ['message' => $e->getMessage()]);
            return redirect()->back()
                ->withErrors(['failure_proof_image' => $e->getMessage()])
                ->withInput();
        }
    }

    // Cập nhật các trường
    $log->status = $request->status;
    
    // Cập nhật thông tin vận đơn nếu có
    if ($request->filled('ma_van_don')) {
        $log->ma_van_don = $request->ma_van_don;
    }
    if ($request->filled('don_vi_van_chuyen')) {
        $log->don_vi_van_chuyen = $request->don_vi_van_chuyen;
    }
    
    // Xử lý theo từng trạng thái (11 trạng thái mới)
    switch ($request->status) {
        case 'don_hang_moi':
            // Đơn hàng mới tạo
            break;
            
        case 'dang_chuan_bi_sach':
            $log->ngay_chuan_bi = now();
            $log->nguoi_chuan_bi_id = auth()->id();
            // Cập nhật trạng thái chi tiết của borrow
            if ($log->borrow) {
                $log->borrow->update([
                    'trang_thai_chi_tiet' => 'dang_chuan_bi_sach',
                    'trang_thai' => 'Cho duyet' // Đảm bảo trang_thai là 'Cho duyet' để user端能正确显示
                ]);
            }
            break;
            
        case 'cho_ban_giao_van_chuyen':
            $log->ngay_dong_goi_xong = now();
            if ($log->borrow) {
                $log->borrow->update([
                    'trang_thai_chi_tiet' => 'cho_ban_giao_van_chuyen',
                    'trang_thai' => 'Cho duyet'
                ]);
            }
            break;
            
        case 'dang_giao_hang':
            $log->ngay_bat_dau_giao = now();
            if ($log->borrow) {
                $log->borrow->update([
                    'trang_thai_chi_tiet' => 'dang_giao_hang',
                    'trang_thai' => 'Dang muon' // Chuyển sang Dang muon khi bắt đầu giao hàng
                ]);
            }
            break;
            
        case 'giao_hang_thanh_cong':
            $log->ngay_giao_thanh_cong = now();
            $log->delivered_at = now();
            // Chỉ cập nhật trang_thai_chi_tiet, KHÔNG tự động chuyển sang "Dang muon"
            // Chỉ khi khách hàng xác nhận đã nhận sách thì mới chuyển sang "da_muon_dang_luu_hanh"
            if ($log->borrow) {
                $log->borrow->update([
                    'trang_thai_chi_tiet' => 'giao_hang_thanh_cong'
                ]);
                
                // Reset trạng thái xác nhận từ khách hàng (nếu có)
                $log->borrow->customer_confirmed_delivery = false;
                $log->borrow->customer_confirmed_delivery_at = null;
                // Lưu thời gian chuyển sang trạng thái "Chờ khách xác nhận"
                $log->borrow->ngay_cho_xac_nhan_nhan = now();
                $log->borrow->save();
                
                // ✅ Tự động cập nhật trạng thái thanh toán
                // 1. Chuyển tất cả payment COD (offline) từ pending → success
                $log->borrow->payments()
                    ->where('payment_method', 'offline')
                    ->where('payment_status', 'pending')
                    ->whereIn('payment_type', ['deposit', 'borrow_fee', 'shipping_fee'])
                    ->update([
                        'payment_status' => 'success',
                        'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Đã thanh toán COD khi giao hàng thành công')"),
                        'updated_at' => now()
                    ]);
                
                // 2. Chuyển tất cả payment Online (online) từ pending → success
                // (Trường hợp thanh toán online nhưng chưa xác nhận)
                $log->borrow->payments()
                    ->where('payment_method', 'online')
                    ->where('payment_status', 'pending')
                    ->whereIn('payment_type', ['deposit', 'borrow_fee', 'shipping_fee'])
                    ->update([
                        'payment_status' => 'success',
                        'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Xác nhận thanh toán online thành công')"),
                        'updated_at' => now()
                    ]);
            }
            break;
            
        case 'giao_hang_that_bai':
            $log->ngay_that_bai_giao_hang = now();
            $log->failure_reason = $request->failure_reason;
            
            if ($log->borrow) {
                $log->borrow->update(['trang_thai_chi_tiet' => 'giao_hang_that_bai']);
                
                // Xử lý hoàn tiền theo lý do thất bại
                if ($request->failure_reason === 'loi_khach_hang') {
                    // Lỗi do khách hàng: 
                    // - Hoàn 80% tiền cọc (trừ 20% phí phạt)
                    // - Hoàn 100% phí thuê
                    // - Không hoàn phí ship (khách mất 100% phí ship)
                    // - Khách mất 20% tiền cọc (phí phạt)
                    
                    $borrow = $log->borrow;
                    $totalDeposit = $borrow->tien_coc ?? 0;
                    $penaltyAmount = $totalDeposit * 0.20; // 20% phí phạt
                    $refundDeposit = $totalDeposit - $penaltyAmount; // 80% hoàn lại
                    
                    // 1. Xử lý tiền cọc: Hoàn 80%, trừ 20% phí phạt
                    $depositPayments = $borrow->payments()
                        ->where('payment_type', 'deposit')
                        ->where('payment_status', 'success')
                        ->get();
                    
                    $totalRefundDeposit = 0;
                    foreach ($depositPayments as $payment) {
                        $paymentAmount = $payment->amount ?? 0;
                        $refundAmount = $paymentAmount * 0.80; // Hoàn 80%
                        $penalty = $paymentAmount * 0.20; // Phí phạt 20%
                        $totalRefundDeposit += $refundAmount;
                        
                        // Cập nhật payment: chỉ hoàn 80%
                        $payment->update([
                            'payment_status' => 'refunded',
                            'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Hoàn 80% tiền cọc (', " . number_format($refundAmount, 0, '.', '') . " VNĐ) do giao hàng thất bại (Lỗi khách hàng). Trừ 20% phí phạt: ', " . number_format($penalty, 0, '.', '') . " VNĐ')"),
                            'updated_at' => now()
                        ]);
                    }
                    
                    // 2. Xử lý phí thuê: Hoàn 100%
                    $borrow->payments()
                        ->where('payment_type', 'borrow_fee')
                        ->where('payment_status', 'success')
                        ->update([
                            'payment_status' => 'refunded',
                            'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Hoàn 100% phí thuê do giao hàng thất bại (Lỗi khách hàng)')"),
                            'updated_at' => now()
                        ]);
                    
                    // 3. Xử lý phí ship: Không hoàn (khách mất 100%)
                    $borrow->payments()
                        ->where('payment_type', 'shipping_fee')
                        ->where('payment_status', 'success')
                        ->update([
                            'payment_status' => 'failed',
                            'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Không hoàn phí ship do giao hàng thất bại (Lỗi khách hàng). Khách mất 100% phí ship.')"),
                            'updated_at' => now()
                        ]);
                    
                    // 4. Lưu thông tin phí phạt và tiền hoàn vào borrow
                    $borrow->phi_hong_sach = ($borrow->phi_hong_sach ?? 0) + $penaltyAmount;
                    $borrow->tien_coc_hoan_tra = $totalRefundDeposit; // Tổng tiền cọc hoàn lại (80%)
                    $borrow->save();
                    
                } elseif ($request->failure_reason === 'loi_thu_vien') {
                    // Lỗi do thư viện: Hoàn 100% (Cọc + Phí thuê + Phí ship)
                    // Hoàn tất cả các khoản thanh toán
                $log->borrow->payments()
                    ->where('payment_status', 'success')
                    ->whereIn('payment_type', ['deposit', 'borrow_fee', 'shipping_fee'])
                    ->update([
                            'payment_status' => 'refunded',
                            'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Hoàn 100% do giao hàng thất bại (Lỗi thư viện)')"),
                        'updated_at' => now()
                    ]);
                }
            }
            break;
            
        case 'da_muon_dang_luu_hanh':
            $log->ngay_bat_dau_luu_hanh = now();
            if ($log->borrow) {
                $log->borrow->update([
                    'trang_thai' => 'Dang muon',
                    'trang_thai_chi_tiet' => 'da_muon_dang_luu_hanh'
                ]);
            }
            break;
            
        case 'cho_tra_sach':
            $log->ngay_yeu_cau_tra_sach = now();
            if ($log->borrow) {
                $log->borrow->update(['trang_thai_chi_tiet' => 'cho_tra_sach']);
            }
            break;
            
        case 'dang_van_chuyen_tra_ve':
            $log->ngay_bat_dau_tra = now();
            if ($log->borrow) {
                $log->borrow->update(['trang_thai_chi_tiet' => 'dang_van_chuyen_tra_ve']);
            }
            break;
            
        case 'da_nhan_va_kiem_tra':
            $log->ngay_nhan_tra = now();
            $log->ngay_kiem_tra = now();
            $log->nguoi_kiem_tra_id = auth()->id();
            
            // Xử lý tình trạng sách
            if ($request->filled('tinh_trang_sach')) {
                $log->tinh_trang_sach = $request->tinh_trang_sach;
                
                // Tính phí hỏng sách
                $phiHong = $this->calculateDamageFee($log, $request->tinh_trang_sach);
                $log->phi_hong_sach = $phiHong;
                
                // Tính tiền cọc hoàn trả
                $tienCoc = $log->borrow ? $log->borrow->tien_coc : 0;
                $log->tien_coc_hoan_tra = max(0, $tienCoc - $phiHong);
                
                // Cập nhật vào borrow
                if ($log->borrow) {
                    $log->borrow->update([
                        'tinh_trang_sach' => $request->tinh_trang_sach,
                        'phi_hong_sach' => $phiHong,
                        'tien_coc_hoan_tra' => $log->tien_coc_hoan_tra,
                        'trang_thai_chi_tiet' => 'da_nhan_va_kiem_tra'
                    ]);
                }
            }
            
            if ($request->filled('ghi_chu_kiem_tra')) {
                $log->ghi_chu_kiem_tra = $request->ghi_chu_kiem_tra;
            }
            break;
            
        case 'hoan_tat_don_hang':
            $log->ngay_hoan_coc = now();
            $log->nguoi_hoan_coc_id = auth()->id();
            $earlyRefundBorrowFee = 0;
            
            if ($request->filled('ghi_chu_hoan_coc')) {
                $log->ghi_chu_hoan_coc = $request->ghi_chu_hoan_coc;
            }
            
            if ($log->borrow) {
                // Kiểm tra trả sớm: nếu hoàn tất trước hạn trả -> hoàn 30% phí thuê
                try {
                    $borrow = $log->borrow;
                    $dueDate = $borrow->items->min('ngay_hen_tra');
                    if ($dueDate) {
                        $due = \Carbon\Carbon::parse($dueDate)->endOfDay();
                        if (now()->lt($due)) {
                            // Hoàn 30% phí thuê cho các payment borrow_fee đã success
                            $borrowFeePayments = $borrow->payments()
                                ->where('payment_type', 'borrow_fee')
                                ->where('payment_status', 'success')
                                ->get();
                            foreach ($borrowFeePayments as $payment) {
                                $refundAmount = ($payment->amount ?? 0) * 0.3;
                                $earlyRefundBorrowFee += $refundAmount;
                                $payment->update([
                                    'payment_status' => 'refunded',
                                    'note' => \DB::raw("CONCAT(COALESCE(note, ''), ' - Hoàn 30% phí thuê do trả sách sớm (', " . number_format($refundAmount, 0, '.', '') . " VNĐ)')"),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Early return refund error', [
                        'borrow_id' => $log->borrow->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }

                $log->borrow->items()->update([
                    'trang_thai' => 'Da tra', 
                    'ngay_tra_thuc_te' => now()
                ]);
                $log->borrow->update([
                    'trang_thai' => 'Da tra',
                    'trang_thai_chi_tiet' => 'hoan_tat_don_hang'
                ]);
                
                // Cập nhật inventory về trạng thái 'Co san' (Có sẵn)
                foreach ($log->borrow->items as $item) {
                    if ($item->inventory) {
                        $item->inventory->update(['status' => 'Co san']);
                    }
                }

                // ✅ Tự động đánh dấu các khoản phạt (fines) chưa thanh toán thành 'paid'
                // Ghi lại paid_date và ghi chú nhỏ để dễ tra cứu
                $updatedFinesCount = $log->borrow->fines()->where('status', 'pending')->count();
                if ($updatedFinesCount > 0) {
                    $log->borrow->fines()
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'paid',
                            'paid_date' => now(),
                            'notes' => \DB::raw("CONCAT(COALESCE(notes, ''), ' - Tự động đánh dấu đã thanh toán khi hoàn tất đơn hàng')"),
                            'updated_at' => now()
                        ]);
                }
            }
            break;
    }
    
    if ($request->filled('receiver_note')) {
        $log->receiver_note = $request->receiver_note;
    }
    
    if ($request->filled('delivered_at')) {
        $log->delivered_at = $request->delivered_at;
    }
    
    $log->save();

    // KHÔNG tự động cập nhật items và trang_thai sang "Dang muon" khi giao hàng
    // Chỉ khi khách hàng xác nhận đã nhận sách thì mới cập nhật
    // Code cũ đã được loại bỏ để đảm bảo flow đúng

    // Tạo thông báo với thông tin chi tiết
    $message = 'Cập nhật trạng thái đơn hàng thành công!';
    
    if ($request->status === 'giao_hang_thanh_cong') {
        if ($log->borrow) {
            $updatedPayments = $log->borrow->payments()
                ->where('payment_status', 'success')
                ->whereIn('payment_type', ['deposit', 'borrow_fee', 'shipping_fee'])
                ->get();
                
            $codCount = $updatedPayments->where('payment_method', 'offline')->count();
            $onlineCount = $updatedPayments->where('payment_method', 'online')->count();
            
            if ($codCount > 0 || $onlineCount > 0) {
                $details = [];
                if ($codCount > 0) $details[] = "{$codCount} khoản COD";
                if ($onlineCount > 0) $details[] = "{$onlineCount} khoản Online";
                $message .= ' Đã tự động xác nhận thanh toán: ' . implode(' và ', $details) . '.';
            }
        }
    } elseif ($request->status === 'giao_hang_that_bai') {
        $message .= ' Các khoản thanh toán COD đã được chuyển về trạng thái "Chưa thanh toán".';
    } elseif ($request->status === 'hoan_tat_don_hang') {
        if (isset($updatedFinesCount) && $updatedFinesCount > 0) {
            $message .= " Đã tự động đánh dấu {$updatedFinesCount} khoản phạt là đã thanh toán.";
        }
        if (isset($earlyRefundBorrowFee) && $earlyRefundBorrowFee > 0) {
            $message .= ' Hoàn trả sớm: hoàn thêm 30% phí thuê (' . number_format($earlyRefundBorrowFee, 0, ',', '.') . ' VNĐ).';
        }
    }

    return redirect()->route('admin.shipping_logs.edit', $log->id)->with('success', $message);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation error in updateStatus', [
            'errors' => $e->errors(),
            'id' => $id,
            'request_data' => $request->all()
        ]);
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();
            
    } catch (\Exception $e) {
        \Log::error('Error updating status', [
            'id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);
        
        return redirect()->back()
            ->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage()])
            ->withInput();
    }
}

    /**
     * Tính phí hỏng sách
     */
    protected function calculateDamageFee(ShippingLog $log, $condition)
    {
        if ($condition === 'binh_thuong') {
            return 0;
        }

        $totalBookValue = 0;
        if ($log->borrow) {
            foreach ($log->borrow->items as $item) {
                if ($item->book) {
                    $totalBookValue += $item->book->gia ?? 0;
                }
            }
        }

        switch ($condition) {
            case 'hong_nhe':
                return $totalBookValue * 0.1; // 10%
            case 'hong_nang':
                return $totalBookValue * 0.5; // 50%
            case 'mat_sach':
                return $totalBookValue; // 100%
            default:
                return 0;
        }
    }

    /**
     * Xoá log
     */
    public function destroy($id)
    {
        $log = ShippingLog::findOrFail($id);
        $log->delete();

        return back()->with('success', 'Đã xoá shipping log.');
    }
}

