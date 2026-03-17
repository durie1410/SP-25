@extends('layouts.admin')

@section('title', 'Thanh toán phạt')

@section('content')
<div class="fine-payment-page">
    @php
        $totalPending = $fines->sum('amount');
        $firstBorrowId = optional($fines->first())->borrow_id;
    @endphp

    <div class="fine-payment-header">
        <div>
            <h2 class="fine-payment-title"><i class="fas fa-money-check-alt"></i> Thanh toán phạt</h2>
            <p class="fine-payment-subtitle">Tổng hợp khoản phạt pending và xác nhận phương thức thanh toán</p>
        </div>
        <div class="fine-payment-actions">
            <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-undo"></i> Trả sách
            </a>
        </div>
    </div>

    @if(!$reader)
        <div class="card fine-payment-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Tìm độc giả</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.fine-payments.index') }}" class="fine-payment-filter-form">
                    <div>
                        <label class="form-label">Reader ID</label>
                        <input name="reader_id"
                               value="{{ request('reader_id') }}"
                               class="form-control"
                               placeholder="Ví dụ: 27" />
                    </div>
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if($fines->count() === 0)
        <div class="alert alert-info border-0">
            <i class="fas fa-info-circle me-2"></i>Không có khoản phạt pending.
        </div>
    @else
        <div class="fine-payment-grid">
            <div class="fine-payment-main">
                <div class="card fine-payment-card">
                    <div class="card-header fine-payment-card-header">
                        <div>
                            <h3 class="card-title"><i class="fas fa-list"></i> Danh sách khoản phạt</h3>
                            @if(!empty($onlyRecent))
                                <div class="small text-muted mt-1">Chỉ hiển thị khoản phạt của lần trả vừa rồi</div>
                            @endif
                        </div>
                        @if(!empty($onlyRecent))
                            <span class="badge bg-info">Lần trả vừa rồi</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="fine-info-row">
                            <div>
                                <div class="fine-info-label">Độc giả</div>
                                <div class="fine-info-value">
                                    {{ $reader ? $reader->ho_ten : 'N/A' }}
                                    @if($reader)<span class="text-muted">(#{{ $reader->id }})</span>@endif
                                </div>
                            </div>
                            <div>
                                <div class="fine-info-label">Phiếu mượn</div>
                                <div class="fine-info-value">#{{ $firstBorrowId ?? '---' }}</div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table fine-table">
                                <thead>
                                    <tr>
                                        <th>Tên sách</th>
                                        <th style="width:110px;">Phiếu</th>
                                        <th style="width:160px;">Loại phạt</th>
                                        <th style="width:160px;">Ngày</th>
                                        <th class="text-end" style="width:140px;">Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fines as $fine)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ optional(optional($fine->borrowItem)->book)->ten_sach ?? '---' }}</div>
                                                <div class="small text-muted">Fine #{{ $fine->id }}</div>
                                            </td>
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

                @if(session('momo_qr_url') && session('momo_pay_url'))
                    <div class="card fine-payment-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-qrcode"></i> QR thanh toán MoMo</h3>
                        </div>
                        <div class="card-body">
                            <div class="momo-box">
                                <div class="momo-content">
                                    <img src="{{ session('momo_qr_url') }}" alt="MoMo QR">
                                    <div class="momo-meta">Mã đơn: <strong>{{ session('momo_order_id') }}</strong></div>
                                    <a href="{{ session('momo_pay_url') }}" target="_blank" class="btn btn-sm btn-danger">
                                        <i class="fas fa-external-link-alt"></i> Mở MoMo
                                    </a>
                                </div>
                                <div class="small text-muted mt-2 text-center">Sau khi thanh toán xong, hệ thống sẽ tự cập nhật khi nhận IPN từ MoMo.</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="fine-payment-sidebar">
                <div class="card fine-payment-summary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-receipt"></i> Tổng kết thanh toán</h3>
                    </div>
                    <div class="card-body">
                        <div class="summary-total">
                            <div class="summary-label">Tổng tiền cần thu</div>
                            <div class="summary-value">{{ number_format($totalPending) }}₫</div>
                        </div>

                        @if($reader)
                            <div class="summary-block">
                                <div class="summary-section-title">Phương thức thanh toán</div>
                                <div class="payment-methods">
                                    <label class="payment-method">
                                        <input type="radio" name="payment_method_choice" value="offline" checked>
                                        <span class="method-icon"><i class="fas fa-money-bill-wave"></i></span>
                                        <span class="method-text">Tiền mặt</span>
                                    </label>
                                    @if(!empty($momoEnabled))
                                        <label class="payment-method">
                                            <input type="radio" name="payment_method_choice" value="online">
                                            <span class="method-icon"><i class="fas fa-qrcode"></i></span>
                                            <span class="method-text">MoMo</span>
                                        </label>
                                    @endif
                                </div>
                            </div>

                            <form id="paymentForm" action="{{ route('admin.fine-payments.pay-cash', $reader->id) }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" id="paymentMethod" name="payment_method" value="offline">
                                <button type="submit" class="btn btn-primary w-100"
                                        onclick="const chosen=document.querySelector('input[name=payment_method_choice]:checked')?.value||'offline';document.getElementById('paymentMethod').value=chosen;const total={{ $totalPending ?? 0 }};const name='{{ $reader?->ho_ten ?? '' }}';if(chosen==='offline'){return confirm('Xác nhận đã thanh toán ' + total.toLocaleString('vi-VN') + '₫' + (name ? ' cho ' + name : '') + '?');}return confirm('Tạo thanh toán MoMo cho khoản phạt này?');">
                                    <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                                </button>
                                <div class="summary-note">Hệ thống sẽ ghi nhận giao dịch theo phương thức bạn chọn.</div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.fine-payment-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.fine-payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.fine-payment-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 4px;
}

.fine-payment-subtitle {
    font-size: 14px;
    color: var(--text-muted);
    margin: 0;
}

.fine-payment-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.fine-payment-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(280px, 0.7fr);
    gap: 22px;
    align-items: start;
}

.fine-payment-card {
    border: 1px solid rgba(148, 163, 184, 0.2);
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
}

.fine-payment-filter-form {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 160px;
    gap: 12px;
    align-items: end;
}

.fine-payment-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.fine-info-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
    margin-bottom: 14px;
}

.fine-info-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    margin-bottom: 4px;
}

.fine-info-value {
    font-weight: 600;
    color: #0f172a;
}

.fine-table thead th {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    background: #f8fafc;
}

.fine-payment-summary {
    position: sticky;
    top: 92px;
    border: 1px solid rgba(148, 163, 184, 0.25);
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.1);
}

.summary-total {
    padding: 12px 0 16px;
    border-bottom: 1px dashed #e2e8f0;
}

.summary-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    margin-bottom: 6px;
}

.summary-value {
    font-size: 28px;
    font-weight: 800;
    color: #0f766e;
}

.summary-block {
    margin-top: 18px;
}

.summary-section-title {
    font-weight: 600;
    margin-bottom: 10px;
}

.payment-methods {
    display: grid;
    gap: 10px;
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.payment-method input {
    accent-color: #0f766e;
}

.payment-method:hover {
    border-color: rgba(15, 118, 110, 0.4);
    background: #f0fdfa;
}

.method-icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 118, 110, 0.1);
    color: #0f766e;
}

.method-text {
    font-weight: 600;
}

.summary-note {
    font-size: 12px;
    color: #64748b;
    margin-top: 10px;
    text-align: center;
}

.momo-box {
    border: 1px dashed rgba(239, 68, 68, 0.4);
    background: #fff5f5;
    border-radius: 12px;
    padding: 16px;
}

.momo-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.momo-content img {
    width: 200px;
    height: 200px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #fff;
    padding: 6px;
}

.momo-meta {
    font-size: 12px;
    color: #475569;
}

@media (max-width: 1100px) {
    .fine-payment-grid {
        grid-template-columns: 1fr;
    }

    .fine-payment-summary {
        position: static;
    }
}

@media (max-width: 640px) {
    .fine-payment-filter-form {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
