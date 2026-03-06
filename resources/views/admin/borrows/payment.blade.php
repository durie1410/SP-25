@extends('layouts.admin')

@section('title', 'Thanh toán phiếu mượn')

@section('content')
<div class="admin-table">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-credit-card"></i> Thanh toán phiếu mượn #{{ $borrow->id }}</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.borrows.show', $borrow->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
            <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Thông tin thanh toán
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Độc giả:</strong>
                        <span>{{ optional($borrow->reader)->ho_ten ?? ($borrow->ten_nguoi_muon ?? 'N/A') }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Số sách:</strong>
                        <span>{{ $borrow->items->count() }} sách</span>
                    </div>
                    <div class="mb-2">
                        <strong>Tiền thuê:</strong>
                        <span class="fw-bold text-danger">{{ number_format($borrow->tien_thue ?? 0) }}₫</span>
                    </div>

                    @if(session('momo_qr_url') && session('momo_pay_url'))
                        <div class="alert alert-warning mt-3 mb-0">
                            <div class="fw-bold mb-2"><i class="fas fa-qrcode me-1"></i> Mã thanh toán MoMo</div>
                            <div class="d-flex flex-column align-items-center gap-2">
                                <img src="{{ session('momo_qr_url') }}" alt="MoMo QR" style="width: 220px; height: 220px; border: 1px solid #dee2e6; border-radius: 8px; background: #fff; padding: 6px;">
                                <div class="small text-muted">Mã đơn: <strong>{{ session('momo_order_id') }}</strong></div>
                                <a href="{{ session('momo_pay_url') }}" target="_blank" class="btn btn-sm btn-danger">
                                    <i class="fas fa-external-link-alt me-1"></i> Mở trang thanh toán MoMo
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($successPayment)
                        <div class="alert alert-success mt-3 mb-0">
                            <div class="fw-bold"><i class="fas fa-check-circle me-1"></i> Đã thanh toán</div>
                            <div class="small text-muted">
                                Số tiền: {{ number_format($successPayment->amount ?? 0) }}₫
                                @if($successPayment->updated_at)
                                    - {{ $successPayment->updated_at->format('d/m/Y H:i') }}
                                @endif
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('admin.borrows.index') }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-list"></i> Về danh sách
                                </a>
                            </div>
                        </div>
                    @elseif(!$pendingPayment)
                        <div class="alert alert-warning mt-3 mb-0">
                            Không có giao dịch thanh toán đang chờ. Vui lòng quay lại và duyệt lại phiếu (hoặc tạo thanh toán).
                        </div>
                    @else
                        <div class="alert alert-info mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">Giao dịch đang chờ thanh toán</div>
                                    <div class="small text-muted">Số tiền cần thu: {{ number_format($pendingPayment->amount ?? 0) }}₫</div>
                                </div>
                                <span class="badge bg-warning text-dark">PENDING</span>
                            </div>
                        </div>

                        <form action="{{ route('admin.borrows.confirm-cash-payment', $borrow->id) }}" method="POST" class="mt-3">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phương thức thanh toán</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="offline">Tiền mặt</option>
                                    <option value="online">Quét mã</option>
                                </select>
                                <div class="form-text">
                                    Chọn <strong>Quét mã</strong>: hệ thống sẽ tạo mã MoMo để khách thanh toán.<br>
                                    Chọn <strong>Tiền mặt</strong>: hệ thống xác nhận đã thu tiền ngay.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Tiếp tục xử lý thanh toán cho phiếu mượn #{{ $borrow->id }}?')">
                                <i class="fas fa-money-bill-wave me-1"></i> Xác nhận đã thanh toán
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <i class="fas fa-book me-2"></i> Danh sách sách (tóm tắt)
                </div>
                <div class="card-body">
                    @forelse($borrow->items as $item)
                        <div class="d-flex gap-2 align-items-start mb-3">
                            <div style="width:40px; height:55px; background:#f1f5f9; border-radius:6px; overflow:hidden; flex:0 0 auto;">
                                @if(optional($item->book)->hinh_anh)
                                    <img src="{{ asset('storage/' . $item->book->hinh_anh) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center" style="width:100%; height:100%; color:#94a3b8;">📘</div>
                                @endif
                            </div>
                            <div style="min-width:0;">
                                <div class="fw-bold text-truncate">{{ $item->book->ten_sach ?? 'N/A' }}</div>
                                <div class="small text-muted">Trạng thái: {{ $item->trang_thai }}</div>
                                <div class="small text-muted">Thuê: {{ number_format($item->tien_thue ?? 0) }}₫</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Không có sách trong phiếu.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

