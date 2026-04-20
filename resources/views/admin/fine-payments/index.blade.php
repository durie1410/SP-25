@extends('layouts.admin')

@section('title', 'Thanh toán phạt')

@section('content')
<div class="fine-payment-page">
    @php
        $totalPending = ($fines->sum('amount') ?? 0) + ($pendingReturnTotal ?? 0);
        $firstBorrowId = optional($fines->first())->borrow_id;
    @endphp

    <div class="fine-payment-header">
        <div>
            <h2 class="fine-payment-title"><i class="fas fa-money-check-alt"></i> Thanh toán phạt</h2>
        </div>
        <div class="fine-payment-actions">
 
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

    {{-- Sách đang chờ trả (từ session) --}}
 

    @if($fines->count() === 0 && (empty($pendingReturnItems) || $pendingReturnItems->count() === 0))
        <div class="alert alert-info border-0">
            <i class="fas fa-info-circle me-2"></i>Không có khoản phạt pending và không có sách nào được chọn để trả.
        </div>
    @elseif(!empty($hasMissingProofs))
        {{-- Cảnh báo thiếu ảnh minh chứng --}}
        <div class="alert alert-danger border-0 d-flex align-items-start gap-3">
            <div>
                <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Chưa tải đủ ảnh minh chứng!</div>
                <div class="small">Vui lòng tải ảnh minh chứng cho <strong>tất cả các sách</strong> bên dưới trước khi xác nhận thanh toán.</div>
                @if(!empty($finesMissingProofs))
                    <div class="mt-2 small">
                        <i class="fas fa-times-circle text-danger me-1"></i>
                        <span class="text-danger fw-semibold">Thiếu ảnh cho khoản phạt:</span>
                        @foreach($finesMissingProofs as $m)
                            <span class="badge bg-danger me-1">{{ $m['book_name'] }}</span>
                        @endforeach
                    </div>
                @endif
                @if(!empty($pendingMissingProofs))
                    <div class="mt-1 small">
                        <i class="fas fa-times-circle text-danger me-1"></i>
                        <span class="text-danger fw-semibold">Thiếu ảnh cho sách chờ trả:</span>
                        @foreach($pendingMissingProofs as $m)
                            <span class="badge bg-danger me-1">{{ $m['book_name'] }}</span>
                        @endforeach
                    </div>
                @endif
                <div class="mt-2">
                    <a href="{{ route('admin.returns.index', ['reader_id' => $reader?->id]) }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-camera"></i> Quay lại trang trả sách để tải ảnh
                    </a>
                </div>
            </div>
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
                                    {{-- Phạt ước tính từ sách chờ trả (chưa tạo Fine record) --}}
                                    @foreach($pendingReturnFines ?? [] as $fine)
                                        <tr class="table-warning">
                                            <td>
                                                <h3><div class="fw-semibold">{{ optional(optional($fine->borrowItem)->book)->ten_sach ?? '---' }}</div></h3>
                                                <div class="small text-muted">Chờ trả sách</div>
                                            </td>
                                            <td>#{{ $fine->borrow_id }}</td>
                                            <td>
                                                <span class="badge bg-danger">{{ $fine->type }}</span>
                                            </td>
                                            <td>{{ $fine->created_at ? $fine->created_at->format('d/m/Y H:i') : '---' }}</td>
                                            <td class="text-end fw-bold text-danger">{{ number_format($fine->amount) }}₫</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Ảnh minh chứng từ đặt trước / khi trả sách --}}
                        <div class="fine-proof-section mt-4">
                            <div class="fine-proof-title">
                                <i class="fas fa-camera"></i> Ảnh minh chứng minh trả sách
                            </div>
                        
                  

                            <div class="fine-proof-list">
                                @php
            // Lấy danh sách borrow_item_id đã hiển thị (tránh trùng khi cả fine + pendingReturn cùng 1 item)
            $shownItemIds = [];

            // Helper tách ảnh từ path
            $makeProofUrl = function($img) {
                if (!$img) return null;
                if (preg_match('/^https?:\/\//i', $img)) return $img;
                $normalized = ltrim(str_replace(['\\', 'storage/'], ['/', ''], (string) $img), '/');
                return asset('storage/' . $normalized);
            };

            $sessionProofsByItemId = $sessionProofsByItemId ?? collect();
        @endphp

                                {{-- Ảnh từ sách chờ trả (từ session) --}}
                                @if(!empty($pendingReturnItems))
                                    @foreach($pendingReturnItems as $item)
                                        @php
                                            $book = optional($item)->book;
                                            $shownItemIds[] = $item->id;

                                            // Ảnh khi nhận sách: ưu tiên reservation đúng book_id, fallback về ảnh đã lưu trực tiếp trên borrow_item
                                            $reservationMatch = $item->reservation_match;
                                            $borrowProofsRaw = !empty($reservationMatch)
                                                ? ($reservationMatch->getProofImages() ?? [])
                                                : [];
                                            if (empty($borrowProofsRaw)) {
                                                $borrowProofsRaw = collect([
                                                    $item->anh_bia_truoc,
                                                    $item->anh_bia_sau,
                                                    $item->anh_gay_sach,
                                                ])->filter()->values()->all();
                                            }
                                            $borrowProofs = collect($borrowProofsRaw)->map($makeProofUrl)->filter()->values()->all();

                                            // Ảnh trả sách
                                            $raw = is_array($item->return_proof_images ?? null)
                                                ? $item->return_proof_images
                                                : (is_string($item->return_proof_images ?? null) ? json_decode($item->return_proof_images, true) : []);
                                            $sessionProofs = collect($sessionProofsByItemId->get($item->id, []))->filter()->values()->all();
                                            $returnProofs = collect(is_array($raw) ? $raw : [])
                                                ->merge($sessionProofs)
                                                ->unique()
                                                ->map($makeProofUrl)
                                                ->filter()
                                                ->values()
                                                ->all();

                                            $hasAnyProof = !empty($borrowProofs) || !empty($returnProofs);
                                        @endphp
                                        <div class="fine-proof-row">
                                            <div class="fine-proof-book">
                                                <div class="fine-proof-thumb">
                                                    @if($book && $book->hinh_anh)
                                                        <img src="{{ $book->image_url ?? asset('images/default-book.png') }}" alt="">
                                                    @else
                                                        <span>📘</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fine-proof-book-name">{{ $book->ten_sach ?? '---' }}</div>
                                                    <div class="fine-proof-book-meta">Phiếu #{{ $item->borrow_id }} · Chờ trả sách</div>
                                                </div>
                                            </div>
                                            <div class="fine-proof-images">
                                                @if($hasAnyProof)
                                                    @if(!empty($borrowProofs))
                                                        <div class="fine-proof-group">
                                                            <div class="fine-proof-group-label">Ảnh khi nhận sách</div>
                                                            <div class="fine-proof-grid">
                                                                @foreach($borrowProofs as $url)
                                                                    <a href="{{ $url }}" target="_blank"><img src="{{ $url }}" alt="Ảnh nhận sách"></a>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if(!empty($returnProofs))
                                                        <div class="fine-proof-group">
                                                            <div class="fine-proof-group-label">Ảnh khi trả sách</div>
                                                            <div class="fine-proof-grid">
                                                                @foreach($returnProofs as $url)
                                                                    <a href="{{ $url }}" target="_blank"><img src="{{ $url }}" alt="Ảnh trả sách"></a>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">Chưa có ảnh minh chứng cho sách này.</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- Ảnh từ Fine records đã có trong DB (không trùng với pendingReturnItems) --}}
                                @foreach($fines as $fine)
                                    @php
                                        $item = $fine->borrowItem;
                                        if (!$item || in_array($item->id, $shownItemIds)) continue;
                                        $book = optional($item)->book;

                                        $reservationMatch = $item->reservation_match;
                                        $borrowProofsRaw = !empty($reservationMatch)
                                            ? ($reservationMatch->getProofImages() ?? [])
                                            : [];
                                        if (empty($borrowProofsRaw)) {
                                            $borrowProofsRaw = collect([
                                                $item->anh_bia_truoc,
                                                $item->anh_bia_sau,
                                                $item->anh_gay_sach,
                                            ])->filter()->values()->all();
                                        }
                                        $borrowProofs = collect($borrowProofsRaw)->map($makeProofUrl)->filter()->values()->all();
                                         $raw = is_array($item->return_proof_images ?? null)
                                             ? $item->return_proof_images
                                             : (is_string($item->return_proof_images ?? null) ? json_decode($item->return_proof_images, true) : []);
                                         $sessionProofs = collect($sessionProofsByItemId->get($item->id, []))->filter()->values()->all();
                                         $returnProofs = collect(is_array($raw) ? $raw : [])
                                             ->merge($sessionProofs)
                                             ->unique()
                                             ->map($makeProofUrl)
                                             ->filter()
                                             ->values()
                                             ->all();
                                        $hasAnyProof = !empty($borrowProofs) || !empty($returnProofs);
                                    @endphp
                                    <div class="fine-proof-row">
                                        <div class="fine-proof-book">
                                            <div class="fine-proof-thumb">
                                                @if($book && $book->hinh_anh)
                                                    <img src="{{ $book->image_url ?? asset('images/default-book.png') }}" alt="">
                                                @else
                                                    <span>📘</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fine-proof-book-name">{{ $book->ten_sach ?? '---' }}</div>
                                                <div class="fine-proof-book-meta">Phiếu #{{ $fine->borrow_id }}{{ $fine->id ? ' · Fine #'.$fine->id : '' }}</div>
                                            </div>
                                        </div>
                                        <div class="fine-proof-images">
                                            @if($hasAnyProof)
                                                @if(!empty($borrowProofs))
                                                    <div class="fine-proof-group">
                                                        <div class="fine-proof-group-label">Ảnh khi nhận sách</div>
                                                        <div class="fine-proof-grid">
                                                            @foreach($borrowProofs as $url)
                                                                <a href="{{ $url }}" target="_blank"><img src="{{ $url }}" alt="Ảnh nhận sách"></a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                @if(!empty($returnProofs))
                                                    <div class="fine-proof-group">
                                                        <div class="fine-proof-group-label">Ảnh khi trả sách</div>
                                                        <div class="fine-proof-grid">
                                                            @foreach($returnProofs as $url)
                                                                <a href="{{ $url }}" target="_blank"><img src="{{ $url }}" alt="Ảnh trả sách"></a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted small">Chưa có ảnh minh chứng.</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($fines->hasPages())
                            <div class="mt-3">
                                {{ $fines->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                @if(session('momo_qr_url') && session('momo_pay_url') && empty($hasMissingProofs))
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
                                @if(!empty($hasMissingProofs))
                                    <button type="button" class="btn btn-secondary w-100" disabled
                                            title="Cần tải đủ ảnh minh chứng cho tất cả sách trước khi thanh toán">
                                        <i class="fas fa-lock"></i> Cần tải ảnh minh chứng
                                    </button>
                                    <div class="summary-note text-danger">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Thiếu ảnh minh chứng cho một số sách. Không thể thanh toán.
                                    </div>
                                @else
                                    <button type="button" id="btnConfirmPayment" class="btn btn-primary w-100">
                                        <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                                    </button>
                                    <div class="summary-note">Hệ thống sẽ ghi nhận giao dịch theo phương thức bạn chọn.</div>
                                @endif
                            </form>
                            {{-- Khối hiển thị QR MoMo (ẩn mặc định, hiện khi tạo thành công) --}}
                            <div id="momoQrSection" class="mt-3" style="display:none;">
                                <div class="momo-box">
                                    <div class="momo-content">
                                        <img id="momoQrImg" src="" alt="MoMo QR">
                                        <div class="momo-meta">Mã đơn: <strong id="momoOrderId"></strong></div>
                                        <a id="momoPayLink" href="" target="_blank" class="btn btn-sm btn-danger">
                                            <i class="fas fa-external-link-alt"></i> Mở MoMo
                                        </a>
                                      
                                </div>
                            </div>
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

.fine-proof-section {
    margin-top: 18px;
}

.fine-proof-title {
    font-weight: 700;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.fine-proof-note {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 12px;
}

.fine-proof-list {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.fine-proof-row {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 2fr);
    gap: 16px;
    padding: 14px 16px;
    border-top: 1px solid #e2e8f0;
    align-items: flex-start;
}

.fine-proof-row:first-child {
    border-top: none;
}

.fine-proof-book {
    display: flex;
    align-items: center;
    gap: 10px;
}

.fine-proof-thumb {
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

.fine-proof-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.fine-proof-book-name {
    font-weight: 600;
    color: #0f172a;
}

.fine-proof-book-meta {
    font-size: 12px;
    color: #94a3b8;
}

.fine-proof-images {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.fine-proof-group-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    margin-bottom: 4px;
}

.fine-proof-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.fine-proof-grid img {
    width: 52px;
    height: 52px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Tham chiếu DOM ---
    const qrSection    = document.getElementById('momoQrSection');
    const qrImg        = document.getElementById('momoQrImg');
    const orderIdEl    = document.getElementById('momoOrderId');
    const payLink      = document.getElementById('momoPayLink');
    const btnConfirm   = document.getElementById('btnConfirmPayment');
    const btnMomoDone  = document.getElementById('btnMomoDone');

    // Ẩn QR khi load trang
    if (qrSection) qrSection.style.display = 'none';

    // ======================================================
    //  Nút "Xác nhận thanh toán" — xử lý cả MoMo lẫn Tiền mặt
    // ======================================================
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            const chosen = document.querySelector('input[name="payment_method_choice"]:checked')?.value || 'offline';

            if (chosen === 'online') {
                // -------- MoMo --------
                if (!confirm('Tạo thanh toán MoMo cho khoản phạt này?')) return;

                btnConfirm.disabled = true;
                btnConfirm.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo mã MoMo...';

                const csrfToken = (document.querySelector('meta[name="csrf-token"]') || document.querySelector('input[name="_token"]'))?.value || '';
                const readerId  = {!! $reader->id ?? 'null' !!};
                const readerName = {!! json_encode($reader?->ho_ten ?? '') !!};

                fetch(window.location.origin + window.location.pathname.replace(/\/admin\/fine-payments.*/, '') + '/admin/fine-payments/' + readerId + '/momo/create', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success && data.payUrl) {
                        // Hiện QR
                        const qrUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data='
                                     + encodeURIComponent(data.payUrl);
                        const orderId = data.orderId || '';
                        if (qrImg)     qrImg.src      = qrUrl;
                        if (orderIdEl) orderIdEl.textContent = orderId;
                        if (payLink)   payLink.href   = data.payUrl;
                        if (qrSection) {
                            qrSection.style.display = 'block';
                            qrSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        // Đổi nút thành "Đã thanh toán xong"
                        btnConfirm.style.display = 'none';
                    } else {
                        alert('Không thể tạo thanh toán MoMo: ' + (data.message || 'Lỗi không xác định'));
                        btnConfirm.disabled = false;
                        btnConfirm.innerHTML = '<i class="fas fa-check-circle"></i> Xác nhận thanh toán';
                    }
                })
                .catch(function (err) {
                    alert('Lỗi kết nối: ' + err.message);
                    btnConfirm.disabled = false;
                    btnConfirm.innerHTML = '<i class="fas fa-check-circle"></i> Xác nhận thanh toán';
                });

            } else {
                // -------- Tiền mặt --------
                const total = {!! $totalPending ?? 0 !!};
                const name  = {!! json_encode($reader?->ho_ten ?? '') !!};
                if (confirm('Xác nhận đã thanh toán '
                    + total.toLocaleString('vi-VN') + '₫'
                    + (name ? ' cho ' + name : '') + '?')) {
                    const form = document.getElementById('paymentForm');
                    if (form) {
                        document.getElementById('paymentMethod').value = 'offline';
                        form.submit();
                    }
                }
            }
        });
    }

    // ======================================================
    //  Nút "Đã thanh toán xong" (hiện sau khi quét QR)
    // ======================================================
    if (btnMomoDone) {
        btnMomoDone.addEventListener('click', function () {
            if (confirm('Xác nhận độc giả đã thanh toán MoMo thành công?')) {
                const successForm = document.getElementById('momoSuccessForm');
                if (successForm) successForm.submit();
            }
        });
    }
});
</script>
@endpush
