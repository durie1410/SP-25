@extends('layouts.admin')

@section('title', 'Chi Tiết Phiếu Mượn')

@section('content')
<style>
    .status-badge {
    display: inline-block;
    padding: 0.25em 0.5em;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 0.25rem;
    color: #fff;
    text-align: center;
}

.status-Cho-duyet { background-color: #6c757d; }    /* xám */
.status-Chua-nhan { background-color: #0d6efd; }    /* xanh dương */
.status-Dang-muon { background-color: #0dcaf0; }    /* xanh nhạt */
.status-Da-tra { background-color: #198754; }       /* xanh lá */
.status-Qua-han { background-color: #ffc107; color: #000; } /* vàng */
.status-Mat-sach { background-color: #dc3545; }     /* đỏ */
.status-Hong { background-color: #fd7e14; }        /* cam */
.status-Huy { background-color: #6c757d; }         /* xám */
.status-Khong-xac-dinh { background-color: #6c757d; } /* xám */

/* Status badges cho trạng thái chi tiết */
.status-detail-badge {
    font-size: 0.95rem;
    padding: 0.5em 1em;
    border-radius: 0.3rem;
}
.status-cho_xu_ly { 
    background-color: #6c757d !important; 
    color: #fff !important;
}
.status-dang_chuan_bi { 
    background-color: #0dcaf0 !important; 
    color: #000 !important;
}
.status-dang_giao { 
    background-color: #0d6efd !important; 
    color: #fff !important;
}
.status-da_giao_thanh_cong { 
    background-color: #198754 !important; 
    color: #fff !important;
}
.status-giao_that_bai { 
    background-color: #dc3545 !important; 
    color: #fff !important;
}
.status-tra_lai_sach { 
    background-color: #fd7e14 !important; 
    color: #fff !important;
}
.status-dang_gui_lai { 
    background-color: #0d6efd !important; 
    color: #fff !important;
}
.status-da_nhan_hang { 
    background-color: #198754 !important; 
    color: #fff !important;
}
.status-dang_kiem_tra { 
    background-color: #ffc107 !important; 
    color: #000 !important;
}
.status-thanh_toan_coc { 
    background-color: #20c997 !important; 
    color: #fff !important;
}
.status-hoan_thanh { 
    background-color: #198754 !important; 
    color: #fff !important;
}

</style>
<div class="admin-table">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="fas fa-file-alt"></i> Chi tiết phiếu mượn</h3>
        <div>
            <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-info-circle me-2"></i> Thông tin chung
    </div>
    <div class="card-body bg-light">
        <div class="row mb-2">
            <div class="col-md-6">
                <p class="mb-1"><strong>Mã phiếu:</strong> #{{ $borrow->id }}</p>
                <p class="mb-1">
                    <strong>Độc giả:</strong>
                    {{ optional($borrow->reader)->ho_ten ?? 'Không có thẻ thành viên' }}
                    <small class="text-muted">
                        ({{ optional($borrow->reader)->so_the_doc_gia ?? 'N/A' }})
                    </small>
                </p>
                @if(!empty($borrow->ten_nguoi_muon))
                <p class="mb-1"><strong>Tên người mượn:</strong> {{ $borrow->ten_nguoi_muon }}</p>
                @endif
                <div>
                    <p class="mb-1"><strong>địa chỉ người mượn: </strong>{{ $borrow->tinh_thanh }}, {{ $borrow->huyen }}, {{ $borrow->xa }}, {{ $borrow->so_nha }}
                </div>
                <p class="mb-1">
                    <strong>Thủ thư:</strong>
                    {{ optional($borrow->librarian)->name ?? 'Không xác định' }}
                </p>
            </div>

            <div class="col-md-6">
                <p class="mb-1">
                    <strong>Ngày mượn:</strong>
                    {{ $borrow->ngay_muon ? $borrow->ngay_muon->format('d/m/Y') : '---' }}
                </p>
                <p class="mb-1">
                    <strong>Trạng thái:</strong>
                    @switch($borrow->trang_thai)
                        @case('Dang muon')
                            <span class="badge bg-primary">Đang mượn</span>
                            @break
                        @case('Da tra')
                            <span class="badge bg-success">Đã trả</span>
                            @break
                        @case('Qua han')
                            <span class="badge bg-danger">Quá hạn ({{ max(0, (int) ($borrow->days_overdue ?? 0)) }} ngày)</span>
                            @break
                        @default
                            <span class="badge bg-warning text-dark">{{ $borrow->trang_thai }}</span>
                    @endswitch
                </p>
                
           
                
                @if($borrow->tinh_trang_sach)
                <p class="mb-1">
                    <strong>Tình trạng sách:</strong>
                    <span class="badge 
                        @if($borrow->tinh_trang_sach == 'binh_thuong') bg-success
                        @elseif($borrow->tinh_trang_sach == 'hong_nhe') bg-warning text-dark
                        @elseif($borrow->tinh_trang_sach == 'hong_nang') bg-orange
                        @else bg-danger
                        @endif">
                        {{ $borrow->tinh_trang_sach_label }}
                    </span>
                </p>
                @endif
                
                @if($borrow->phi_hong_sach > 0)
                <p class="mb-1">
                    <strong>Phí hỏng sách:</strong>
                    <span class="text-danger fw-bold">{{ number_format($borrow->phi_hong_sach) }}₫</span>
                </p>
                @endif

             

                <p class="mb-1">
                    <strong>Tổng tiền:</strong>
                    <span class="fw-bold text-success">
                        @php
                            // Luôn tính từ items để đảm bảo tính chính xác
                            $tienThueTotal = $borrow->items->sum('tien_thue') ?? 0;
                            $tienCocTotal = $borrow->items->sum('tien_coc') ?? 0;
                            $tienShipTotal = $borrow->items->sum('tien_ship') ?? 0;
                            $tienPhatTotal = $borrow->items->sum('tien_phat') ?? 0;
                            $tongTienTotal = $tienThueTotal + $tienCocTotal + $tienShipTotal + $tienPhatTotal;
                        @endphp
                        {{ number_format($tongTienTotal, 0) }}₫
                    </span>
                </p>

                @if($borrow->anh_hoan_tra)
                <div class="mt-3">
                    <p class="mb-2"><strong>Ảnh minh chứng hoàn trả:</strong></p>
                    <div class="d-flex flex-wrap gap-2">
                        @if(is_array($borrow->anh_hoan_tra))
                            @foreach($borrow->anh_hoan_tra as $img)
                                @php
                                    $url = stripslashes($img);
                                    $fullUrl = (strpos($url, 'http') === 0) ? $url : asset('storage/' . $url);
                                @endphp
                                <a href="{{ $fullUrl }}" target="_blank">
                                    <img src="{{ $fullUrl }}" alt="Ảnh minh chứng" class="img-thumbnail" style="height: 120px; width: 120px; object-fit: cover; cursor: pointer;">
                                </a>
                            @endforeach
                        @else
                            @php
                                $url = stripslashes($borrow->anh_hoan_tra);
                                $fullUrl = (strpos($url, 'http') === 0) ? $url : asset('storage/' . $url);
                            @endphp
                            <a href="{{ $fullUrl }}" target="_blank">
                                <img src="{{ $fullUrl }}" alt="Ảnh minh chứng" class="img-thumbnail" style="max-height: 200px; cursor: pointer;">
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                @if($borrow->anh_bia_truoc || $borrow->anh_bia_sau || $borrow->anh_gay_sach)
                <div class="mt-3" id="upload-anh-nhan-sach">
                    <p class="mb-2"><strong>Ảnh xác nhận khách nhận sách tại quầy:</strong></p>
                    <div class="d-flex flex-wrap gap-2">
                        @if($borrow->anh_bia_truoc)
                            @php $borrowFrontUrl = (strpos($borrow->anh_bia_truoc, 'http') === 0) ? $borrow->anh_bia_truoc : asset('storage/' . $borrow->anh_bia_truoc); @endphp
                            <a href="{{ $borrowFrontUrl }}" target="_blank">
                                <img src="{{ $borrowFrontUrl }}" alt="Ảnh bìa trước" class="img-thumbnail" style="height: 120px; width: 120px; object-fit: cover; cursor: pointer;" onerror="this.src='/images/no-image.png'; this.onerror=null;">
                            </a>
                        @endif
                        @if($borrow->anh_bia_sau)
                            @php $borrowBackUrl = (strpos($borrow->anh_bia_sau, 'http') === 0) ? $borrow->anh_bia_sau : asset('storage/' . $borrow->anh_bia_sau); @endphp
                            <a href="{{ $borrowBackUrl }}" target="_blank">
                                <img src="{{ $borrowBackUrl }}" alt="Ảnh bìa sau" class="img-thumbnail" style="height: 120px; width: 120px; object-fit: cover; cursor: pointer;" onerror="this.src='/images/no-image.png'; this.onerror=null;">
                            </a>
                        @endif
                        @if($borrow->anh_gay_sach)
                            @php $borrowSpineUrl = (strpos($borrow->anh_gay_sach, 'http') === 0) ? $borrow->anh_gay_sach : asset('storage/' . $borrow->anh_gay_sach); @endphp
                            <a href="{{ $borrowSpineUrl }}" target="_blank">
                                <img src="{{ $borrowSpineUrl }}" alt="Ảnh gáy sách" class="img-thumbnail" style="height: 120px; width: 120px; object-fit: cover; cursor: pointer;" onerror="this.src='/images/no-image.png'; this.onerror=null;">
                            </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($borrow->ghi_chu || $borrow->ghi_chu_yeu_cau_tra)
        <div class="mt-3">
            @if($borrow->ghi_chu)
                <p class="mb-0"><strong>Ghi chú đơn hàng:</strong></p>
                <div class="alert alert-secondary mt-1 p-2">
                    <em>{{ $borrow->ghi_chu }}</em>
                </div>
            @endif

            @if($borrow->ghi_chu_yeu_cau_tra)
                <p class="mb-0 text-danger"><strong>📢 Ghi chú yêu cầu trả sách của khách:</strong></p>
                <div class="alert alert-warning mt-1 p-2 border-danger">
                    <i class="fas fa-comment-dots me-2"></i><strong>{{ $borrow->reader->ho_ten ?? 'Khách' }}:</strong> 
                    <em>"{{ $borrow->ghi_chu_yeu_cau_tra }}"</em>
                    <br>
                    <small class="text-muted"><i class="fas fa-clock"></i> {{ $borrow->ngay_yeu_cau_tra_sach ? $borrow->ngay_yeu_cau_tra_sach->format('d/m/H H:i') : 'N/A' }}</small>
                </div>
            @endif
        </div>
        @endif
    </div>
</div>

@if($borrow->trang_thai_chi_tiet === \App\Models\Borrow::STATUS_CHO_TRA_SACH)
<div class="card mb-4 border-primary shadow-sm">
    <div class="card-header bg-primary text-white fw-bold">
        <i class="fas fa-reply-all me-2"></i> Xác nhận yêu cầu trả sách
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <p class="mb-1">Khách hàng đã gửi yêu cầu trả sách. Bạn có thể xác nhận để chuyển sang trạng thái chờ nhận hàng.</p>
                <small class="text-muted">Lưu ý: Sau khi xác nhận, khách hàng có thể thực hiện tải ảnh minh chứng và gửi hàng.</small>
            </div>
            <form action="{{ route('admin.borrows.confirm-return-shipping', $borrow->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="fas fa-check-circle me-1"></i> Chấp nhận & Xác nhận yêu cầu
                </button>
            </form>
        </div>
    </div>
</div>
@endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-book me-2"></i> Danh sách sách mượn</span>
            <span class="badge bg-info">Tổng: {{ $borrow->items->count() }} sách</span>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr class="bg-light">
                        <th>ID</th>
                        <th>Thông tin sách</th>
                        <th>Tài chính</th>
                        <th>Hẹn trả</th>
                        <th>Trạng thái</th>
                        <th>ảnh xác nhận sách</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrow->items as $index => $item)
                    <tr>
                        <td>{{ $item->book->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if(optional($item->book)->image_url)
                                    <img src="{{ $item->book->image_url }}" alt="" 
                                         style="width: 40px; height: 60px; object-fit: cover; margin-right: 10px;" class="img-thumbnail">
                                @endif
                                <div>
                                    <div class="fw-bold">{{ $item->book->ten_sach }}</div>
                                    <small class="text-muted">Tác giả: {{ $item->book->tac_gia }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div><strong>Thuê:</strong> {{ number_format($item->tien_thue ?? 0) }}₫</div>
                                @if($item->tien_phat > 0)
                                    <div class="text-danger"><strong>Phạt:</strong> {{ number_format($item->tien_phat) }}₫</div>
                                @endif
                            </div>
                        </td>
                        <td>{{ $item->ngay_hen_tra ? $item->ngay_hen_tra->format('d/m/Y') : '---' }}</td>
                        <td>
                            @php
                                $statusClass = str_replace(' ', '-', $item->trang_thai);
                                $itemStatusText = $item->trang_thai;

                                if ($item->trang_thai === 'Qua han' && !empty($item->ngay_hen_tra)) {
                                    $itemOverdueDays = \Carbon\Carbon::parse($item->ngay_hen_tra)
                                        ->startOfDay()
                                        ->diffInDays(now()->startOfDay());
                                    $itemStatusText = 'Quá hạn (' . ($itemOverdueDays ?? 0) . ' ngày)';
                                }
                            @endphp
                            <span class="status-badge status-{{ $statusClass }}">
                                {{ $itemStatusText }}
                            </span>
                        </td>
                 
                        <td>
                            @php
                                $showImages = [];
                                // Ảnh từ reservation (proof_images - ảnh khách upload)
                                $reservation = $item->reservation_match;
                                if ($reservation) {
                                    foreach ($reservation->getProofImages() as $img) {
                                        if (!$img) continue;
                                        if (preg_match('/^https?:\/\//i', $img)) {
                                            $showImages[] = $img;
                                        } else {
                                            $normalized = ltrim(str_replace(['\\', 'storage/'], ['/', ''], (string) $img), '/');
                                            $showImages[] = asset('storage/' . $normalized);
                                        }
                                    }
                                }
                                // Ảnh bìa sách
                                if (!empty($item->anh_bia_truoc)) {
                                    $showImages[] = (strpos($item->anh_bia_truoc, 'http') === 0) ? $item->anh_bia_truoc : asset('storage/' . $item->anh_bia_truoc);
                                }
                                if (!empty($item->anh_bia_sau)) {
                                    $showImages[] = (strpos($item->anh_bia_sau, 'http') === 0) ? $item->anh_bia_sau : asset('storage/' . $item->anh_bia_sau);
                                }
                                if (!empty($item->anh_gay_sach)) {
                                    $showImages[] = (strpos($item->anh_gay_sach, 'http') === 0) ? $item->anh_gay_sach : asset('storage/' . $item->anh_gay_sach);
                                }
                            @endphp
                            @if(count($showImages) > 0)
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @foreach($showImages as $img)
                                        <a href="{{ $img }}" target="_blank">
                                            <img src="{{ $img }}" alt="Ảnh xác nhận" class="img-thumbnail" style="height: 56px; width: 56px; object-fit: cover;" onerror="this.src='/images/no-image.png'; this.onerror=null;">
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small">Chưa có</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @php
        $pendingFines = $borrow->fines->where('status', 'pending');
        $totalPendingFine = $pendingFines->sum('amount');
    @endphp

    @if($pendingFines->count() > 0)
    <div class="card mt-4 border-danger shadow-sm">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Khoản phạt chưa thanh toán</span>
            <span class="badge bg-white text-danger fw-bold fs-6">{{ number_format($totalPendingFine) }}₫</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Loại phạt</th>
                            <th>Nội dung</th>
                            <th>Số tiền</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingFines as $fine)
                        <tr>
                            <td>
                                <span class="badge bg-outline-danger text-danger border border-danger">
                                    {{ $fine->type == 'late_return' ? 'Trả trễ' : ($fine->type == 'damaged_book' ? 'Hỏng sách' : 'Mất sách') }}
                                </span>
                            </td>
                            <td>{{ $fine->description }}</td>
                            <td class="fw-bold text-danger">{{ number_format($fine->amount) }}₫</td>
                            <td>{{ $fine->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end gap-2 mt-2">
                <form action="{{ route('borrows.fine-pay-cash', $borrow->id) }}" method="POST" onsubmit="return confirm('Xác nhận độc giả đã thanh toán {{ number_format($totalPendingFine) }}₫ tiền mặt?')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-money-bill-wave me-1"></i> Thu tiền mặt
                    </button>
                </form>
                
                <button type="button" class="btn btn-danger" onclick="payFineWithMomo()">
                    <i class="fas fa-mobile-alt me-1"></i> Thanh toán MoMo
                </button>
            </div>
        </div>
    </div>
    @endif

    <div class="mt-3">
        <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>
@endsection

