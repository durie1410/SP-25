@extends('layouts.admin')

@section('title', 'Thanh toán phiếu mượn')

@section('content')
<div class="payment-page">
    <div class="payment-header">
        <div class="payment-hero-content">
            <div class="payment-kicker">Borrow Checkout Desk</div>
            <h2 class="payment-title"><i class="fas fa-credit-card"></i> Thanh toán phiếu mượn #{{ $borrow->id }}</h2>
            <p class="payment-subtitle">Xác nhận giao dịch, kiểm tra tình trạng sách và ảnh chứng minh trước khi hoàn tất.</p>
     
        </div>
        <div class="payment-actions">
            @if(!$successPayment)
                <button type="button" class="btn btn-success" id="openAddBookModalBtn">
                    <i class="fas fa-plus"></i> Thêm sách vào phiếu
                </button>
            @endif
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
                        <div class="payment-metric-box">
                            <div class="payment-info-label">Độc giả</div>
                            <div class="payment-info-value">{{ optional($borrow->reader)->ho_ten ?? ($borrow->ten_nguoi_muon ?? 'N/A') }}</div>
                        </div>
                        <div class="payment-metric-box">
                            <div class="payment-info-label">Số sách</div>
                            <div class="payment-info-value"><span class="metric-emphasis js-borrow-items-count">{{ $borrow->items->count() }}</span> sách</div>
                        </div>
                        <div class="payment-metric-box">
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
                        <p class="proof-note">Bạn có thể upload ảnh chứng minh cho các sách mới thêm trực tiếp tại trang này trước khi bấm xác nhận thanh toán.</p>

                        <div class="proof-table">
                            <div class="proof-table-head">
                                <span>Ảnh</span>
                                <span>Sách</span>
                                <span>Ngày mươn / trả</span>
                                <span>Trạng thái</span>
                                <span>Thuê</span>
                                <span>Ảnh chứng minh</span>
                                <span>Thao tác</span>
                            </div>
                            <div class="proof-table-body" id="proofTableBody">
                                @forelse($borrow->items as $item)
                                    @php
                                        $reservationMatch = $item->reservation_match;
                                        $proofImages = $reservationMatch ? $reservationMatch->getProofImages() : [];
                                        if (empty($proofImages)) {
                                            $proofImages = collect([
                                                $item->anh_bia_truoc,
                                                $item->anh_bia_sau,
                                                $item->anh_gay_sach,
                                            ])->filter()->values()->all();
                                        }

                                        $canUploadBeforePayment = !$successPayment;
                                        $showUploadProof = $canUploadBeforePayment && empty($proofImages);
                                        $isNewlyAdded = (bool) ($item->added_in_payment ?? false);
                                        $canEditLineInPayment = !$successPayment && $isNewlyAdded;
                                    @endphp
                                    <div class="proof-row" data-item-id="{{ $item->id }}">
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
                                        <div class="borrow-range-text">
                                            @if($item->ngay_muon && $item->ngay_hen_tra)
                                                <div>Mượn: {{ \Carbon\Carbon::parse($item->ngay_muon)->format('d/m/Y') }}</div>
                                                @if($canEditLineInPayment)
                                                    <div class="mt-1">
                                                        Trả:
                                                        <input
                                                            type="date"
                                                            class="form-control form-control-sm due-date-input"
                                                            data-item-id="{{ $item->id }}"
                                                            min="{{ $item->ngay_muon ? \Carbon\Carbon::parse($item->ngay_muon)->format('Y-m-d') : now()->format('Y-m-d') }}"
                                                            value="{{ $item->ngay_hen_tra ? \Carbon\Carbon::parse($item->ngay_hen_tra)->format('Y-m-d') : '' }}"
                                                        >
                                                    </div>
                                                @else
                                                    <div>Trả: {{ \Carbon\Carbon::parse($item->ngay_hen_tra)->format('d/m/Y') }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted">Chưa có ngày mượn/trả</span>
                                            @endif
                                        </div>
                                        <div class="status-text">{{ $item->trang_thai }}</div>
                                        <div class="fee-text">{{ number_format($item->tien_thue ?? 0) }}₫</div>
                                        <div class="proof-cell">
                                            <div class="proof-grid">
                                                @if(!empty($proofImages))
                                                    @foreach($proofImages as $img)
                                                        @php
                                                            $imgUrl = (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) 
                                                                ? $img 
                                                                : asset('storage/' . $img);
                                                        @endphp
                                                        <a href="{{ $imgUrl }}" target="_blank">
                                                            <img src="{{ $imgUrl }}" alt="Proof">
                                                        </a>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">Chưa có ảnh</span>
                                                @endif
                                            </div>

                                            @if($showUploadProof)
                                                <div class="upload-proof-box" data-upload-for-item="{{ $item->id }}">
                                                    <div class="upload-proof-title">
                                                        Ảnh chứng minh (1 ảnh)
                                                        @if($canEditLineInPayment)
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </div>
                                                    <div class="upload-proof-inputs">
                                                        <input type="file" class="form-control form-control-sm" name="book_images_proof[{{ $item->id }}]" form="paymentWithImagesForm" accept="image/*" @if($canEditLineInPayment) required @endif>
                                                    </div>
                                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #cbd5e1;">
                                                        <div class="upload-proof-title">
                                                            Ghi chú
                                                            @if($canEditLineInPayment)
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                        </div>
                                                        <input type="text" class="form-control form-control-sm" name="book_notes[{{ $item->id }}]" form="paymentWithImagesForm" placeholder="Ghi chú nhận sách @if($canEditLineInPayment)(bắt buộc)@else(tùy chọn)@endif" style="margin-top: 6px;" @if($canEditLineInPayment) required @endif>
                                                    </div>
                                                </div>
                                            @else
                                                @if(!empty($item->ghi_chu_nhan_sach))
                                                    <div class="text-muted" style="font-size: 12px; margin-top: 8px;">
                                                        <strong>Ghi chú:</strong> {{ $item->ghi_chu_nhan_sach }}
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                        <div>
                                            @if($canEditLineInPayment)
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-item-id="{{ $item->id }}">
                                                    <i class="fas fa-trash-alt"></i> Xóa
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
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
                        <div class="summary-value" id="summaryTotalValue">{{ number_format($pendingPayment->amount ?? ($borrow->tien_thue ?? 0)) }}₫</div>
                    </div>

                    <div class="summary-mini-grid">
                        <div class="mini-stat">
                            <div class="mini-stat-label">Mã phiếu</div>
                            <div class="mini-stat-value">#{{ $borrow->id }}</div>
                        </div>
                        <div class="mini-stat">
                            <div class="mini-stat-label">Số sách</div>
                            <div class="mini-stat-value js-borrow-items-count">{{ $borrow->items->count() }}</div>
                        </div>
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
                        <form id="paymentWithImagesForm" action="{{ route('admin.borrows.confirm-cash-payment', $borrow->id) }}" method="POST" enctype="multipart/form-data" class="mt-3">
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

@if(!$successPayment)
<div class="add-book-modal" id="addBookModal" aria-hidden="true">
    <div class="add-book-modal-backdrop" data-close-modal="true"></div>
    <div class="add-book-modal-content" role="dialog" aria-modal="true" aria-labelledby="addBookModalTitle">
        <div class="add-book-modal-header">
            <h4 id="addBookModalTitle"><i class="fas fa-book"></i> Thêm sách vào phiếu #{{ $borrow->id }}</h4>
            <button type="button" class="btn-close" id="closeAddBookModalBtn" aria-label="Đóng"></button>
        </div>
        <div class="add-book-modal-body">
            <div class="search-row">
                <input type="text" id="searchBookInput" class="form-control" placeholder="Nhập tên sách hoặc tác giả...">
                <button type="button" class="btn btn-primary" id="searchBookBtn">
                    <i class="fas fa-search"></i> Tìm
                </button>
            </div>
            <div id="searchBooksResult" class="search-result-list"></div>
            <div id="searchBooksEmpty" class="search-empty d-none">Không tìm thấy sách phù hợp.</div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.payment-page {
    --pay-ink: #0f172a;
    --pay-muted: #64748b;
    --pay-line: #dbe5ef;
    --pay-soft-bg: linear-gradient(145deg, #f8fbff 0%, #edf7f0 100%);
    font-family: "Be Vietnam Pro", "Segoe UI", sans-serif;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.payment-header {
    background: radial-gradient(circle at 8% 12%, #1e293b 0%, #0b3f38 53%, #0f172a 100%);
    border-radius: 18px;
    padding: 18px 20px;
    box-shadow: 0 20px 38px rgba(15, 23, 42, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
}

.payment-kicker {
    display: inline-block;
    margin-bottom: 7px;
    color: rgba(255, 255, 255, 0.74);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-weight: 700;
}

.payment-title {
    font-size: 28px;
    font-weight: 800;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 4px;
}

.payment-subtitle {
    font-size: 14px;
    color: rgba(241, 245, 249, 0.88);
    margin: 0;
}

.payment-quick-badges {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.payment-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid rgba(248, 250, 252, 0.28);
    background: rgba(248, 250, 252, 0.12);
    color: #f8fafc;
    font-weight: 600;
}

.payment-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.payment-actions .btn {
    border-radius: 11px;
    font-weight: 700;
}

.payment-actions .btn-success {
    background: linear-gradient(135deg, #16a34a 0%, #0f766e 100%);
    border-color: transparent;
}

.payment-actions .btn-success:hover,
.payment-actions .btn-success:focus {
    background: linear-gradient(135deg, #22c55e 0%, #0d9488 100%);
}

.payment-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(280px, 0.7fr);
    gap: 22px;
    align-items: start;
}

.payment-card {
    background: var(--pay-soft-bg);
    border: 1px solid #d7e3ee;
    border-radius: 16px;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
}

.payment-card .card-header,
.payment-summary .card-header {
    border-bottom: 1px solid #dde7f1;
    background: rgba(255, 255, 255, 0.58);
}

.payment-card .card-title,
.payment-summary .card-title {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: var(--pay-ink);
}

.payment-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}

.payment-metric-box {
    border: 1px solid var(--pay-line);
    background: #ffffffc4;
    border-radius: 12px;
    padding: 11px 12px;
}

.payment-info-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    margin-bottom: 4px;
}

.payment-info-value {
    font-weight: 700;
    color: var(--pay-ink);
}

.metric-emphasis {
    font-size: 25px;
    line-height: 1;
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
    border-top: 1px dashed #c6d5e4;
    padding-top: 15px;
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
    border: 1px solid #dce7f1;
    border-radius: 12px;
    overflow-x: auto;
    overflow-y: hidden;
    background: #fff;
}

.proof-table-body {
    display: block;
}

.proof-table-head {
    background: linear-gradient(180deg, #f8fbff 0%, #eef5fb 100%);
    display: grid;
    grid-template-columns: 70px minmax(210px, 1.15fr) 190px 120px 100px minmax(280px, 1.35fr) 95px;
    min-width: 1060px;
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
    grid-template-columns: 70px minmax(210px, 1.15fr) 190px 120px 100px minmax(280px, 1.35fr) 95px;
    min-width: 1060px;
    gap: 12px;
    padding: 14px 16px;
    border-top: 1px solid #e1eaf3;
    align-items: flex-start;
    height: auto;
    min-height: 0;
}

.proof-table-head span {
    white-space: nowrap;
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
    line-height: 1.35;
    word-break: break-word;
}

.book-meta {
    font-size: 12px;
    color: #94a3b8;
}

.status-text {
    font-size: 13px;
    color: #334155;
    font-weight: 600;
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

.proof-cell {
    min-width: 0;
}


.borrow-range-text {
    font-size: 12px;
    color: #334155;
    line-height: 1.4;
}

.due-date-input {
    max-width: 170px;
    margin-top: 4px;
}

.upload-proof-box {
    width: 100%;
    margin-top: 8px;
    padding: 10px;
    border-radius: 10px;
    border: 1px dashed #cbd5e1;
    background: #f8fbff;
}

.upload-proof-title {
    font-size: 12px;
    color: #0f172a;
    padding-top: 8px;
    font-weight: 600;
}

.upload-proof-inputs {
    font-size: 11px;
    display: grid;
    margin-bottom: 6px;
}

.proof-grid img {
    width: 46px;
    gap: 4px;
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
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 16px;
    background: linear-gradient(160deg, #ffffff 0%, #eefaf4 100%);
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

.summary-mini-grid {
    margin-top: 12px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.mini-stat {
    border-radius: 10px;
    border: 1px solid #d9e7f4;
    background: #f8fbff;
    padding: 9px 10px;
}

.mini-stat-label {
    font-size: 11px;
    color: var(--pay-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
}

.mini-stat-value {
    margin-top: 2px;
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
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

.payment-method:has(input:checked) {
    border-color: #0f766e;
    background: #ecfdf5;
    box-shadow: inset 0 0 0 1px rgba(15, 118, 110, 0.18);
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

.add-book-modal {
    position: fixed;
    inset: 0;
    z-index: 1040;
    display: none;
}

.add-book-modal.show {
    display: block;
}

.add-book-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
}

.add-book-modal-content {
    position: relative;
    width: min(760px, calc(100% - 24px));
    max-height: calc(100vh - 40px);
    overflow: hidden;
    margin: 20px auto;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 20px 44px rgba(15, 23, 42, 0.26);
    display: flex;
    flex-direction: column;
}

.add-book-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.add-book-modal-header h4 {
    margin: 0;
    font-size: 18px;
}

.add-book-modal-body {
    padding: 16px;
    overflow: auto;
}

.search-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
    margin-bottom: 12px;
}

.search-result-list {
    display: grid;
    gap: 10px;
}

.search-book-item {
    border: 1px solid #dbe6f0;
    border-radius: 12px;
    padding: 10px;
    display: grid;
    grid-template-columns: 52px minmax(0, 1fr) auto;
    gap: 10px;
    align-items: center;
}

.search-book-thumb {
    width: 52px;
    height: 72px;
    border-radius: 8px;
    overflow: hidden;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}

.search-book-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.search-book-title {
    font-weight: 600;
    color: #0f172a;
}

.search-book-meta {
    font-size: 12px;
    color: #64748b;
}

.search-book-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-book-actions input {
    width: 70px;
}

.search-empty {
    padding: 14px;
    border-radius: 10px;
    text-align: center;
    background: #f8fafc;
    color: #64748b;
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
        grid-template-columns: 60px minmax(180px, 1fr) 165px 100px 90px minmax(250px, 1fr) 84px;
        min-width: 980px;
    }
}

@media (max-width: 768px) {
    .payment-header {
        padding: 14px;
    }

    .payment-title {
        font-size: 23px;
    }

    .summary-mini-grid {
        grid-template-columns: 1fr;
    }

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
        min-width: 0;
    }

    .proof-table-head {
        display: none;
    }

    .proof-row {
        border-top: none;
        border-bottom: 1px solid #e2e8f0;
    }

    .search-row {
        grid-template-columns: 1fr;
    }

    .search-book-item {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@if(!$successPayment)
@push('scripts')
<script>
(() => {
    const borrowId = {{ $borrow->id }};
    const csrfToken = '{{ csrf_token() }}';
    const searchUrl = '{{ route('admin.borrows.payment.books.search', $borrow->id) }}';
    const addUrl = '{{ route('admin.borrows.payment.books.add', $borrow->id) }}';
    const removeUrlTemplate = '{{ route('admin.borrows.payment.items.remove', ['id' => $borrow->id, 'itemId' => '__ITEM_ID__']) }}';
    const updateReturnDateUrlTemplate = '{{ route('admin.borrows.payment.items.update-return-date', ['id' => $borrow->id, 'itemId' => '__ITEM_ID__']) }}';

    const modal = document.getElementById('addBookModal');
    const openBtn = document.getElementById('openAddBookModalBtn');
    const closeBtn = document.getElementById('closeAddBookModalBtn');
    const searchInput = document.getElementById('searchBookInput');
    const searchBtn = document.getElementById('searchBookBtn');
    const searchResult = document.getElementById('searchBooksResult');
    const searchEmpty = document.getElementById('searchBooksEmpty');
    const proofTableBody = document.getElementById('proofTableBody');
    const itemsCountEls = document.querySelectorAll('.js-borrow-items-count');
    const totalValueEl = document.getElementById('summaryTotalValue');

    const formatMoney = (value) => Number(value || 0).toLocaleString('vi-VN') + '₫';

    const showToast = (message, isError = false) => {
        const toast = document.createElement('div');
        toast.className = 'alert ' + (isError ? 'alert-danger' : 'alert-success');
        toast.style.position = 'fixed';
        toast.style.right = '16px';
        toast.style.bottom = '16px';
        toast.style.zIndex = '2000';
        toast.style.maxWidth = '340px';
        toast.style.boxShadow = '0 8px 20px rgba(15, 23, 42, 0.2)';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    };

    const openModal = () => {
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        if (searchInput) {
            searchInput.focus();
        }
    };

    const closeModal = () => {
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
    };

    const renderProofRows = (items) => {
        if (!Array.isArray(items) || items.length === 0) {
            proofTableBody.innerHTML = '<div class="proof-empty">Không có sách trong phiếu.</div>';
            return;
        }

        proofTableBody.innerHTML = items.map((item) => {
            const thumb = item.book_has_image
                ? `<img src="${item.book_image_url}" alt="">`
                : '<span>📘</span>';

            const proofImages = Array.isArray(item.proof_images) && item.proof_images.length
                ? item.proof_images.map((img) => `<a href="${img}" target="_blank"><img src="${img}" alt="Proof"></a>`).join('')
                : '<span class="text-muted">Chưa có ảnh</span>';

            const uploadBlock = item.show_upload_proof ? `
                    <div class="upload-proof-box" data-upload-for-item="${item.id}">
                        <div class="upload-proof-title">
                            Ảnh chứng minh (1 ảnh)
                            ${item.can_edit_in_payment ? '<span class="text-danger">*</span>' : ''}
                        </div>
                        <div class="upload-proof-inputs">
                            <input type="file" class="form-control form-control-sm" name="book_images_proof[${item.id}]" form="paymentWithImagesForm" accept="image/*" ${item.can_edit_in_payment ? 'required' : ''}>
                        </div>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #cbd5e1;">
                            <div class="upload-proof-title">
                                Ghi chú
                                ${item.can_edit_in_payment ? '<span class="text-danger">*</span>' : ''}
                            </div>
                            <input type="text" class="form-control form-control-sm" name="book_notes[${item.id}]" form="paymentWithImagesForm" placeholder="Ghi chú nhận sách ${item.can_edit_in_payment ? '(bắt buộc)' : '(tùy chọn)'}" style="margin-top: 6px;" ${item.can_edit_in_payment ? 'required' : ''}>
                        </div>
                    </div>
                ` : `${item.note ? `<div class="text-muted" style="font-size: 12px; margin-top: 8px;"><strong>Ghi chú:</strong> ${item.note}</div>` : ''}`;

            const returnDatePart = item.can_edit_in_payment
                ? `
                    <div class="mt-1">
                        Trả:
                        <input
                            type="date"
                            class="form-control form-control-sm due-date-input"
                            data-item-id="${item.id}"
                            min="${item.borrow_from_iso || ''}"
                            value="${item.borrow_due_iso || ''}"
                        >
                    </div>
                `
                : `<div>Trả: ${item.borrow_due || ''}</div>`;

            return `
                <div class="proof-row" data-item-id="${item.id}">
                    <div class="book-thumb">${thumb}</div>
                    <div>
                        <div class="book-name">${item.book_title || 'N/A'}</div>
                        <div class="book-meta">${item.book_author || 'Không rõ'}</div>
                    </div>
                    <div class="borrow-range-text">
                        <div>Mượn: ${item.borrow_from || ''}</div>
                        ${returnDatePart}
                    </div>
                    <div class="status-text">${item.status || ''}</div>
                    <div class="fee-text">${item.fee_text || formatMoney(item.fee)}</div>
                    <div class="proof-cell">
                        <div class="proof-grid">${proofImages}</div>
                        ${uploadBlock}
                    </div>
                    <div>
                        ${item.can_edit_in_payment
                            ? `<button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-item-id="${item.id}"><i class="fas fa-trash-alt"></i> Xóa</button>`
                            : '<span class="text-muted">-</span>'}
                    </div>
                </div>
            `;
        }).join('');
    };

    const updateSummaryTotalsOnly = (payload) => {
        if (typeof payload.items_count !== 'undefined') {
            itemsCountEls.forEach((el) => {
                el.textContent = payload.items_count;
            });
        }
        if (totalValueEl) {
            totalValueEl.textContent = payload.total_fee_text || formatMoney(payload.total_fee || 0);
        }
    };

    const updateSummary = (payload) => {
        itemsCountEls.forEach((el) => {
            el.textContent = payload.items_count ?? 0;
        });
        if (totalValueEl) {
            totalValueEl.textContent = payload.total_fee_text || formatMoney(payload.total_fee || 0);
        }
        renderProofRows(payload.items || []);
    };

    const fetchBooks = async () => {
        const keyword = encodeURIComponent((searchInput?.value || '').trim());
        searchBtn.disabled = true;
        searchResult.innerHTML = '<div class="search-empty">Đang tải danh sách sách...</div>';
        searchEmpty.classList.add('d-none');

        try {
            const response = await fetch(`${searchUrl}?keyword=${keyword}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Không thể tìm sách.');
            }

            if (!payload.books.length) {
                searchResult.innerHTML = '';
                searchEmpty.classList.remove('d-none');
                return;
            }

            searchResult.innerHTML = payload.books.map((book) => {
                const disabled = (book.max_addable || 0) <= 0;
                const thumb = book.image_url
                    ? `<img src="${book.image_url}" alt="">`
                    : '<span>📘</span>';

                return `
                    <div class="search-book-item">
                        <div class="search-book-thumb">${thumb}</div>
                        <div>
                            <div class="search-book-title">${book.title || 'N/A'}</div>
                            <div class="search-book-meta">${book.author || 'Không rõ'} | Đang có trong phiếu: ${book.selected_qty || 0} | Tồn: ${book.available_qty || 0}</div>
                        </div>
                        <div class="search-book-actions">
                            <input type="number" min="1" max="${Math.max(1, book.max_addable || 1)}" value="1" class="form-control form-control-sm quantity-input" ${disabled ? 'disabled' : ''}>
                            <button type="button" class="btn btn-sm btn-primary add-book-btn" data-book-id="${book.id}" ${disabled ? 'disabled' : ''}>
                                <i class="fas fa-plus"></i> Thêm
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
            searchEmpty.classList.add('d-none');
        } catch (error) {
            searchResult.innerHTML = `<div class="search-empty">${error.message}</div>`;
        } finally {
            searchBtn.disabled = false;
        }
    };

    const addBook = async (bookId, quantity, button, bookTitle = '') => {
        button.disabled = true;
        try {
            const response = await fetch(addUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    book_id: Number(bookId),
                    quantity: Number(quantity || 1)
                })
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Không thể thêm sách.');
            }

            updateSummary(payload);
            const successMessage = bookTitle
                ? `Đã thêm sách "${bookTitle}" vào phiếu thành công.`
                : (payload.message || 'Đã thêm sách vào phiếu thành công.');
            showToast(successMessage);
            fetchBooks();
        } catch (error) {
            showToast(error.message, true);
        } finally {
            button.disabled = false;
        }
    };

    const removeItem = async (itemId, button) => {
        if (!confirm('Xóa sách này khỏi phiếu mượn?')) {
            return;
        }

        button.disabled = true;
        const removeUrl = removeUrlTemplate.replace('__ITEM_ID__', itemId);

        try {
            const response = await fetch(removeUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Không thể xóa sách.');
            }

            updateSummary(payload);
            showToast(payload.message || 'Đã xóa sách thành công.');
        } catch (error) {
            showToast(error.message, true);
        } finally {
            button.disabled = false;
        }
    };

    const updateReturnDate = async (itemId, dueDate, input) => {
        if (!dueDate) {
            showToast('Vui lòng chọn ngày trả.', true);
            return;
        }

        input.disabled = true;
        const updateUrl = updateReturnDateUrlTemplate.replace('__ITEM_ID__', itemId);

        try {
            const response = await fetch(updateUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ngay_hen_tra: dueDate
                })
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Không thể cập nhật ngày trả.');
            }

            // Không render lại toàn bảng để tránh mất file đã chọn ở các input upload.
            updateSummaryTotalsOnly(payload);

            const updatedItem = Array.isArray(payload.items)
                ? payload.items.find((x) => String(x.id) === String(itemId))
                : null;
            if (updatedItem) {
                const row = proofTableBody.querySelector(`.proof-row[data-item-id="${itemId}"]`);
                if (row) {
                    const feeEl = row.querySelector('.fee-text');
                    if (feeEl) {
                        feeEl.textContent = updatedItem.fee_text || formatMoney(updatedItem.fee || 0);
                    }

                    const borrowTextEl = row.querySelector('.borrow-range-text');
                    if (borrowTextEl) {
                        const firstLine = borrowTextEl.querySelector('div');
                        if (firstLine) {
                            firstLine.textContent = `Mượn: ${updatedItem.borrow_from || ''}`;
                        }
                    }

                    if (updatedItem.borrow_due_iso) {
                        input.value = updatedItem.borrow_due_iso;
                    }
                }
            }

            showToast(payload.message || 'Đã cập nhật ngày trả.');
        } catch (error) {
            showToast(error.message, true);
        } finally {
            input.disabled = false;
        }
    };

    if (openBtn) {
        openBtn.addEventListener('click', () => {
            openModal();
            fetchBooks();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target && event.target.dataset && event.target.dataset.closeModal === 'true') {
                closeModal();
            }
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', fetchBooks);
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                fetchBooks();
            }
        });
    }

    if (searchResult) {
        searchResult.addEventListener('click', (event) => {
            const addBtn = event.target.closest('.add-book-btn');
            if (!addBtn) {
                return;
            }

            const container = addBtn.closest('.search-book-item');
            const quantityInput = container ? container.querySelector('.quantity-input') : null;
            const titleEl = container ? container.querySelector('.search-book-title') : null;
            const quantity = quantityInput ? quantityInput.value : 1;
            const bookTitle = titleEl ? titleEl.textContent.trim() : '';
            addBook(addBtn.dataset.bookId, quantity, addBtn, bookTitle);
        });
    }

    if (proofTableBody) {
        proofTableBody.addEventListener('change', (event) => {
            const dueInput = event.target.closest('.due-date-input');
            if (!dueInput) {
                return;
            }

            updateReturnDate(dueInput.dataset.itemId, dueInput.value, dueInput);
        });

        proofTableBody.addEventListener('click', (event) => {
            const removeBtn = event.target.closest('.remove-item-btn');
            if (!removeBtn) {
                return;
            }

            removeItem(removeBtn.dataset.itemId, removeBtn);
        });

        // Preview ảnh khi chọn file
        proofTableBody.addEventListener('change', (event) => {
            const fileInput = event.target.closest('input[type="file"]');
            if (!fileInput || !fileInput.name.includes('book_images_proof')) {
                return;
            }

            const uploadBox = fileInput.closest('.upload-proof-box');
            if (!uploadBox) {
                return;
            }

            const proofCell = fileInput.closest('.proof-cell');
            if (!proofCell) {
                return;
            }

            const proofGrid = proofCell.querySelector('.proof-grid');
            if (!proofGrid) {
                return;
            }

            const file = fileInput.files[0];
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                // Xóa text "Chưa có ảnh" nếu có
                const mutedSpan = proofGrid.querySelector('.text-muted');
                if (mutedSpan) {
                    mutedSpan.remove();
                }

                // Kiểm tra xem đã có ảnh preview chưa
                let previewImg = proofGrid.querySelector('[data-preview="true"]');
                if (previewImg) {
                    previewImg.src = e.target.result;
                } else {
                    // Tạo ảnh preview mới
                    const container = document.createElement('div');
                    container.style.position = 'relative';
                    container.style.display = 'inline-block';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Preview';
                    img.style.width = '46px';
                    img.style.height = '64px';
                    img.style.borderRadius = '8px';
                    img.style.objectFit = 'cover';
                    img.style.border = '1px solid #e2e8f0';
                    img.setAttribute('data-preview', 'true');

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-sm btn-danger';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '-8px';
                    removeBtn.style.right = '-8px';
                    removeBtn.style.width = '24px';
                    removeBtn.style.height = '24px';
                    removeBtn.style.padding = '0';
                    removeBtn.style.borderRadius = '50%';
                    removeBtn.innerHTML = '✕';
                    removeBtn.addEventListener('click', (ev) => {
                        ev.preventDefault();
                        fileInput.value = '';
                        container.remove();
                        if (proofGrid.children.length === 0) {
                            proofGrid.innerHTML = '<span class="text-muted">Chưa có ảnh</span>';
                        }
                    });

                    container.appendChild(img);
                    container.appendChild(removeBtn);
                    proofGrid.appendChild(container);
                }

                // Upload ảnh lên server ngay
                const formData = new FormData();
                formData.append('book_images_proof', file);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route("admin.borrows.upload-proof-image", $borrow->id) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        showToast('Lỗi tải ảnh: ' + (data.message || 'Unknown error'), true);
                    }
                })
                .catch(err => {
                    showToast('Lỗi kết nối: ' + err.message, true);
                });
            };
            reader.readAsDataURL(file);
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal && modal.classList.contains('show')) {
            closeModal();
        }
    });
})();
</script>
@endpush
@endif
