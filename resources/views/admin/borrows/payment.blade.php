@extends('layouts.admin')

@section('title', 'Thanh toán phiếu mượn')

@section('content')
<div class="payment-page">
    <div class="payment-header">
        <div>
            <h2 class="payment-title"><i class="fas fa-credit-card"></i> Thanh toán phiếu mượn #{{ $borrow->id }}</h2>
            <p class="payment-subtitle">Xác nhận thanh toán và đối chiếu ảnh chứng minh từ đặt trước</p>
        </div>
        <div class="payment-actions">
            <a href="{{ route('admin.borrows.show', $borrow->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
            <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="payment-grid">
        <div class="payment-main">
            <div class="card payment-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Thông tin giao dịch</h3>
                </div>
                <div class="card-body">
                    <div class="payment-info-grid">
                        <div>
                            <div class="payment-info-label">Độc giả</div>
                            <div class="payment-info-value">{{ optional($borrow->reader)->ho_ten ?? ($borrow->ten_nguoi_muon ?? 'N/A') }}</div>
                        </div>
                        <div>
                            <div class="payment-info-label">Số sách</div>
                            <div class="payment-info-value">{{ $borrow->items->count() }} sách</div>
                        </div>
                        <div>
                            <div class="payment-info-label">Trạng thái giao dịch</div>
                            <div class="payment-info-value">
                                @if($successPayment)
                                    <span class="badge bg-success">Đã thanh toán</span>
                                @elseif($pendingPayment)
                                    <span class="badge bg-warning text-dark">Đang chờ</span>
                                @else
                                    <span class="badge bg-secondary">Chưa tạo</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(!empty($momoQrUrl) && !empty($momoPayUrl))
                        <div class="momo-box">
                            <div class="momo-title"><i class="fas fa-qrcode"></i> Mã thanh toán MoMo</div>
                            <div class="momo-content">
                                <img src="{{ $momoQrUrl }}" alt="MoMo QR">
                                <div class="momo-meta">Mã đơn: <strong>{{ $momoOrderId }}</strong></div>
                                <a href="{{ $momoPayUrl }}" target="_blank" class="btn btn-sm btn-danger">
                                    <i class="fas fa-external-link-alt"></i> Mở trang thanh toán MoMo
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="proof-section">
                        <div class="proof-title">
                            <i class="fas fa-camera"></i> Ảnh chứng minh từ đặt trước
                        </div>
                        <p class="proof-note">Ảnh được lấy từ yêu cầu đặt trước. Trang này chỉ hiển thị, không cho phép thêm hoặc chụp ảnh.</p>

                        <div class="proof-table">
                            <div class="proof-table-head">
                                <span>Ảnh</span>
                                <span>Sách</span>
                                <span>Trạng thái</span>
                                <span>Thuê</span>
                                <span>Ảnh chứng minh</span>
                            </div>
                            <div class="proof-table-body">
                                @forelse($borrow->items as $item)
                                    @php
                                        $proofImages = $item->reservation ? $item->reservation->getProofImages() : [];
                                    @endphp
                                    <div class="proof-row">
                                        <div class="book-thumb">
                                            @if(optional($item->book)->hinh_anh)
                                                <img src="{{ $item->book->image_url }}" alt="">
                                            @else
                                                <span>📘</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="book-name">{{ $item->book->ten_sach ?? 'N/A' }}</div>
                                            <div class="book-meta">{{ $item->book->tac_gia ?? 'Không rõ' }}</div>
                                        </div>
                                        <div class="status-text">{{ $item->trang_thai }}</div>
                                        <div class="fee-text">{{ number_format($item->tien_thue ?? 0) }}₫</div>
                                        <div class="proof-grid">
                                            @if(!empty($proofImages))
                                                @foreach($proofImages as $img)
                                                    <a href="{{ asset('storage/' . $img) }}" target="_blank">
                                                        <img src="{{ asset('storage/' . $img) }}" alt="Proof">
                                                    </a>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Chưa có ảnh</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="proof-empty">Không có sách trong phiếu.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="payment-sidebar">
            <div class="card payment-summary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-receipt"></i> Tổng kết thanh toán</h3>
                </div>
                <div class="card-body">
                    <div class="summary-total">
                        <div class="summary-label">Tổng tiền cần thu</div>
                        <div class="summary-value">{{ number_format($pendingPayment->amount ?? ($borrow->tien_thue ?? 0)) }}₫</div>
                    </div>

                    <div class="summary-block">
                        <div class="summary-section-title">Phương thức thanh toán</div>
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method_choice" value="offline" checked>
                                <span class="method-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <span class="method-text">Tiền mặt</span>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method_choice" value="online">
                                <span class="method-icon"><i class="fas fa-qrcode"></i></span>
                                <span class="method-text">MoMo</span>
                            </label>
                        </div>
                    </div>

                    @if($successPayment)
                        <div class="alert alert-success mt-3 mb-0">
                            <div class="fw-bold"><i class="fas fa-check-circle me-1"></i> Đã thanh toán</div>
                            <div class="small text-muted">
                                Số tiền: {{ number_format($successPayment->amount ?? 0) }}₫
                                @if($successPayment->updated_at)
                                    - {{ $successPayment->updated_at->format('d/m/Y H:i') }}
                                @endif
                            </div>
                        </div>
                    @elseif(!$pendingPayment)
                        <div class="alert alert-warning mt-3 mb-0">
                            Không có giao dịch thanh toán đang chờ. Vui lòng quay lại và duyệt lại phiếu (hoặc tạo thanh toán).
                        </div>
                    @else
                        <form id="paymentWithImagesForm" action="{{ route('admin.borrows.confirm-cash-payment', $borrow->id) }}" method="POST" class="mt-3">
                            @csrf
                            <input type="hidden" id="payment_method" name="payment_method" value="offline">

                            <button type="submit" class="btn btn-primary w-100"
                                    onclick="const chosen=document.querySelector('input[name=payment_method_choice]:checked')?.value||'offline';document.getElementById('payment_method').value=chosen;return confirm(chosen==='offline' ? 'Xác nhận đã thu TIỀN MẶT cho phiếu mượn #{{ $borrow->id }}?' : 'Tạo mã MoMo cho phiếu mượn #{{ $borrow->id }}?');">
                                <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                            </button>

                            <div class="summary-note">Hệ thống sẽ ghi nhận giao dịch theo phương thức bạn chọn.</div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.payment-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.payment-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 4px;
}

.payment-subtitle {
    font-size: 14px;
    color: var(--text-muted);
    margin: 0;
}

.payment-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.payment-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(280px, 0.7fr);
    gap: 22px;
    align-items: start;
}

.payment-card {
    border: 1px solid rgba(148, 163, 184, 0.2);
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
}

.payment-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}

.payment-info-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    margin-bottom: 4px;
}

.payment-info-value {
    font-weight: 600;
    color: #0f172a;
}

.momo-box {
    border: 1px dashed rgba(239, 68, 68, 0.4);
    background: #fff5f5;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 18px;
}

.momo-title {
    font-weight: 600;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
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

.proof-section {
    margin-top: 16px;
}

.proof-title {
    font-weight: 700;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.proof-note {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 12px;
}

.proof-table {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.proof-table-head {
    background: #f8fafc;
    display: grid;
    grid-template-columns: 70px minmax(0, 1.6fr) 120px 100px minmax(0, 1fr);
    gap: 12px;
    padding: 12px 16px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    font-weight: 600;
}

.proof-row {
    display: grid;
    grid-template-columns: 70px minmax(0, 1.6fr) 120px 100px minmax(0, 1fr);
    gap: 12px;
    padding: 14px 16px;
    border-top: 1px solid #e2e8f0;
    align-items: center;
}

.book-thumb {
    width: 48px;
    height: 64px;
    border-radius: 8px;
    overflow: hidden;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}

.book-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-name {
    font-weight: 600;
    color: #0f172a;
}

.book-meta {
    font-size: 12px;
    color: #94a3b8;
}

.status-text {
    font-size: 13px;
    color: #475569;
}

.fee-text {
    font-weight: 700;
    color: #f97316;
}

.proof-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.proof-grid img {
    width: 46px;
    height: 46px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
}

.proof-empty {
    padding: 20px;
    text-align: center;
    color: #94a3b8;
}

.payment-summary {
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

@media (max-width: 1100px) {
    .payment-grid {
        grid-template-columns: 1fr;
    }

    .payment-summary {
        position: static;
    }

    .proof-table-head,
    .proof-row {
        grid-template-columns: 60px minmax(0, 1fr) 90px 80px minmax(0, 1fr);
    }
}

@media (max-width: 768px) {
    .payment-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .payment-actions {
        width: 100%;
    }

    .payment-actions .btn {
        flex: 1;
    }

    .proof-table-head,
    .proof-row {
        grid-template-columns: 1fr;
    }

    .proof-table-head {
        display: none;
    }

    .proof-row {
        border-top: none;
        border-bottom: 1px solid #e2e8f0;
    }
}
</style>
@endpush
