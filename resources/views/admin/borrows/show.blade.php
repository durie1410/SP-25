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
                            <span class="badge bg-danger">Quá hạn</span>
                            @break
                        @default
                            <span class="badge bg-warning text-dark">{{ $borrow->trang_thai }}</span>
                    @endswitch
                </p>
                
                @if($borrow->trang_thai_chi_tiet)
                <p class="mb-1">
                    <strong>Trạng thái chi tiết:</strong>
                    @php
                        $statusClass = 'status-' . $borrow->trang_thai_chi_tiet;
                    @endphp
                    <span class="badge {{ $statusClass }} status-detail-badge">
                        {{ $borrow->trang_thai_chi_tiet_label ?? $borrow->trang_thai_chi_tiet }}
                    </span>
                </p>
                @endif
                
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
                    <strong>Tiền thuê:</strong>
                    <span class="fw-bold">{{ number_format($borrow->tien_thue ?? 0) }}₫</span>
                </p>

                <p class="mb-1">
                    <strong>Tổng tiền:</strong>
                    <span class="fw-bold text-success">
                        @php
                            $tienThueTotal = $borrow->tien_thue ?? 0;
                            $tienPhatTotal = $borrow->items->sum('tien_phat') ?? 0;
                            $tongTienTotal = $tienThueTotal + $tienPhatTotal;
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
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrow->items as $index => $item)
                    <tr>
                        <td>{{ $item->book->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($item->book->hinh_anh)
                                    <img src="{{ asset('storage/' . $item->book->hinh_anh) }}" alt="" 
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
                                <div><strong>Thuê:</strong> {{ number_format($item->tien_thue) }}₫</div>
                                @if($item->tien_phat > 0)
                                    <div class="text-danger"><strong>Phạt:</strong> {{ number_format($item->tien_phat) }}₫</div>
                                @endif
                            </div>
                        </td>
                        <td>{{ $item->ngay_hen_tra->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $statusClass = str_replace(' ', '-', $item->trang_thai);
                            @endphp
                            <span class="status-badge status-{{ $statusClass }}">
                                {{ $item->trang_thai }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($borrow->trang_thai_chi_tiet === \App\Models\Borrow::STATUS_DANG_VAN_CHUYEN_TRA_VE)
    <div class="card mt-4 border-warning shadow-sm">
        <div class="card-header bg-warning text-dark fw-bold">
            <i class="fas fa-check-double me-2"></i> Xử lý nhận trả sách
        </div>
        <div class="card-body">
            <p>Sách hiện đang được vận chuyển trả về. Sau khi nhận được sách vật lý, vui lòng kiểm tra và xác nhận.</p>
            <button type="button" class="btn btn-warning fw-bold" data-bs-toggle="modal" data-bs-target="#confirmCheckModal">
                <i class="fas fa-check-circle me-1"></i> Xác nhận đã nhận & Kiểm tra sách
            </button>
        </div>
    </div>
    @endif

    <div class="mt-3">
        <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<!-- Modal Xác nhận & Kiểm tra sách -->
<div class="modal fade" id="confirmCheckModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark">Xác nhận & Kiểm tra sách</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.borrows.confirm-receive-check', $borrow->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tình trạng sách thực tế:</label>
                        <select name="tinh_trang_sach" class="form-select" required id="admin_tinh_trang_sach">
                            <option value="binh_thuong" {{ $borrow->tinh_trang_sach == 'binh_thuong' ? 'selected' : '' }}>Bình thường (Trả cọc đầy đủ)</option>
                            <option value="hong_nhe" {{ $borrow->tinh_trang_sach == 'hong_nhe' ? 'selected' : '' }}>Hỏng nhẹ (Khấu trừ một phần cọc)</option>
                            <option value="hong_nang" {{ $borrow->tinh_trang_sach == 'hong_nang' ? 'selected' : '' }}>Hỏng nặng (Khấu trừ phần lớn cọc)</option>
                            <option value="mat_sach" {{ $borrow->tinh_trang_sach == 'mat_sach' ? 'selected' : '' }}>Mất sách (Không hoàn trả cọc)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Phí hỏng sách (₫):</label>
                        <input type="number" name="phi_hong_sach" class="form-control" placeholder="Để trống để tự động tính" value="{{ $borrow->phi_hong_sach }}">
                        <small class="text-muted">Nếu để trống, hệ thống sẽ tự tính dựa trên bảng giá hỏng hóc.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ghi chú kiểm tra:</label>
                        <textarea name="ghi_chu" class="form-control" rows="3" placeholder="Ghi chú chi tiết về tình trạng sách khi nhận được..."></textarea>
                    </div>

                    <div class="alert alert-info py-2 px-3 mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Tiền cọc ban đầu:</span>
                            <span class="fw-bold">{{ number_format($borrow->tien_coc) }}₫</span>
                        </div>
                        <div id="fine-impact-container" style="display: none;">
                            <div class="d-flex justify-content-between mb-1 text-danger">
                                <span>Phí hỏng sách:</span>
                                <span class="fw-bold">-<span id="display-fine-amount">0</span>₫</span>
                            </div>
                            <hr class="my-1">
                            <div class="d-flex justify-content-between fw-bold text-success">
                                <span>Tiền cọc hoàn trả:</span>
                                <span><span id="display-refund-amount">{{ number_format($borrow->tien_coc) }}</span>₫</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning fw-bold">Xác nhận & Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const phiHongInput = document.querySelector('input[name="phi_hong_sach"]');
        const impactContainer = document.getElementById('fine-impact-container');
        const displayFine = document.getElementById('display-fine-amount');
        const displayRefund = document.getElementById('display-refund-amount');
        const tienCoc = {{ $borrow->tien_coc }};

        function updateImpact() {
            const fine = parseInt(phiHongInput.value) || 0;
            if (fine > 0) {
                impactContainer.style.display = 'block';
                displayFine.textContent = fine.toLocaleString();
                displayRefund.textContent = Math.max(0, tienCoc - fine).toLocaleString();
            } else {
                impactContainer.style.display = 'none';
            }
        }

        if (phiHongInput) {
            phiHongInput.addEventListener('input', updateImpact);
            updateImpact(); // Initial call
        }
    });
</script>
@endpush
@endsection

