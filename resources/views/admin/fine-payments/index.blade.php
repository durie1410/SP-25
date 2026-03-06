@extends('layouts.admin')

@section('title', 'Thanh toán phạt')

@section('content')
<div class="container-fluid py-3">
    @php
        $totalPending = $fines->sum('amount');
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">
            <i class="fas fa-money-check-alt me-2 text-primary"></i>Thanh toán phạt
        </h3>
        <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-secondary rounded-3">
            <i class="fas fa-undo me-1"></i> Trả sách
        </a>
    </div>

    @if(!$reader)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('admin.fine-payments.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label fw-semibold">Reader ID</label>
                        <input name="reader_id"
                               value="{{ request('reader_id') }}"
                               class="form-control form-control-lg rounded-3"
                               placeholder="Ví dụ: 27" />
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-lg w-100 rounded-3" type="submit">
                            <i class="fas fa-search me-1"></i> Tìm khách
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($fines->count() === 0)
        <div class="alert alert-info rounded-3 shadow-sm border-0">
            <i class="fas fa-info-circle me-2"></i>Không có khoản phạt pending.
        </div>
    @else
        <div class="row g-4 align-items-start">
            <div class="col-xl-4 col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">
                            <i class="fas fa-user me-2 text-primary"></i>Thông tin thanh toán
                        </h5>

                        <div style="display:flex; gap:12px; align-items:stretch;">
                            <div style="width:50%;">
                                <div class="rounded-3 p-3 h-100" style="background:#f8fafc; border:1px solid #eef2f7;">
                                    <div class="text-muted small text-uppercase mb-2">Thông tin độc giả</div>
                                    <div class="mb-2">
                                        <span class="text-muted">Tên:</span>
                                        <span class="fw-semibold">{{ $reader ? $reader->ho_ten : 'N/A' }} @if($reader)<span class="text-muted">(#{{ $reader->id }})</span>@endif</span>
                                    </div>
                                    <div>
                                        <span class="text-muted">Phiếu mượn:</span>
                                        <span class="fw-semibold">#{{ optional($fines->first())->borrow_id ?? '---' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div style="width:50%;">
                                <div class="rounded-3 p-3 h-100" style="background:#fff5f5; border:1px solid #ffe3e3;">
                                    <div class="text-muted small text-uppercase mb-1">Tổng tiền</div>
                                    <div class="fw-bold text-danger mb-2" style="font-size:2rem; line-height:1;">
                                        {{ number_format($totalPending) }}₫
                                    </div>

                                    @if($reader)
                                        <form id="paymentForm" action="{{ route('admin.fine-payments.pay-cash', $reader->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-2">
                                                <label class="form-label fw-semibold mb-1">Phương thức</label>
                                                <select name="payment_method" id="paymentMethod" class="form-select rounded-3" required>
                                                    <option value="offline">Tiền mặt</option>
                                                    @if(!empty($momoEnabled))
                                                        <option value="online">Quét mã MoMo</option>
                                                    @endif
                                                </select>
                                            </div>

                                            <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold" id="submitPaymentBtn">
                                                <i class="fas fa-money-check-dollar me-1"></i> Thanh toán
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(session('momo_qr_url') && session('momo_pay_url'))
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4 text-center">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-qrcode me-2 text-danger"></i>QR thanh toán MoMo
                            </h6>
                            <img src="{{ session('momo_qr_url') }}"
                                 alt="MoMo QR"
                                 class="img-fluid border rounded-3 p-2 mb-2"
                                 style="max-width: 220px; background:#fff;">
                            <div class="small text-muted mb-2">Mã đơn: <strong>{{ session('momo_order_id') }}</strong></div>
                            <a href="{{ session('momo_pay_url') }}" target="_blank" class="btn btn-danger rounded-3 btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i> Mở MoMo
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-xl-8 col-lg-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-list me-2 text-primary"></i>Danh sách khoản phạt
                        </h5>
                        @if(!empty($onlyRecent))
                            <span class="badge bg-info rounded-pill">Lần trả vừa rồi</span>
                        @endif
                    </div>

                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên sách</th>
                                        <th>Phiếu mượn</th>
                                        <th>Loại phạt</th>
                                        <th>Ngày</th>
                                        <th class="text-end">Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fines as $fine)
                                        <tr>
                                            <td class="fw-semibold">{{ optional(optional($fine->borrowItem)->book)->ten_sach ?? '---' }}</td>
                                            <td>#{{ $fine->borrow_id }}</td>
                                            <td><span class="badge bg-warning text-dark">{{ $fine->type }}</span></td>
                                            <td>{{ $fine->created_at ? $fine->created_at->format('d/m/Y H:i') : '---' }}</td>
                                            <td class="text-end fw-bold text-danger">{{ number_format($fine->amount) }}₫</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($fines->hasPages())
                            <div class="mt-3">
                                {{ $fines->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- MODAL MOMO QR (PHẠT THEO ĐỘC GIẢ) -->
<div class="modal fade" id="readerFineMomoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 border-0 shadow-sm">
            <div class="modal-header bg-danger text-white rounded-top-3">
                <h5 class="modal-title">Thanh toán phạt qua MoMo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="fw-bold">Quét mã QR MoMo để thanh toán</p>
                <img id="readerFineMomoQr" class="img-fluid mb-3" style="max-width:240px" />
                <div>
                    <a id="readerFineMomoPayUrl" href="#" target="_blank" class="btn btn-danger rounded-3">
                        Mở MoMo
                    </a>
                </div>
                <p class="text-muted small mt-2">Sau khi thanh toán xong, hệ thống sẽ tự cập nhật khi nhận IPN từ MoMo.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentForm = document.getElementById('paymentForm');
        const paymentMethod = document.getElementById('paymentMethod');

        if (paymentForm && paymentMethod) {
            paymentForm.addEventListener('submit', function(e) {
                const method = paymentMethod.value;
                const totalAmount = {{ $totalPending ?? 0 }};
                const readerName = "{{ $reader?->ho_ten ?? '' }}";

                // Giống luồng thanh toán mượn:
                // - online: submit form để backend tạo QR + trả về cùng trang
                // - offline: chỉ confirm rồi submit
                if (method === 'offline') {
                    if (!confirm('Xác nhận đã thanh toán ' + totalAmount.toLocaleString('vi-VN') + '₫' + (readerName ? ' cho ' + readerName : '') + '?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        }
    });
</script>
@endpush
