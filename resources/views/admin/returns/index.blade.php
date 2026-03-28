@extends('layouts.admin')

@section('title', 'Trả sách theo khách')

@section('content')
<div class="returns-page">
    <div class="returns-header">
        <div>
            <h2 class="returns-title"><i class="fas fa-undo"></i> Trả sách theo khách</h2>
            <p class="returns-subtitle">Chọn khách, đối chiếu sách và xác nhận trả với các khoản phí phát sinh</p>
        </div>
        <div class="returns-actions">
            <a href="{{ route('admin.fine-payments.index') }}" class="btn btn-outline-danger">
                <i class="fas fa-exclamation-triangle"></i> Thanh toán phạt
            </a>
        </div>
    </div>

    <div class="returns-grid">
        <div class="returns-main">
            <div class="card returns-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search"></i> Tìm khách theo tên</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.returns.index') }}" class="returns-filter-form">
                        <div>
                            <label class="form-label">Tên khách</label>
                            <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Nhập tên khách..." />
                        </div>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Tìm
                        </button>
                        @if(request('reader_id'))
                        <a class="btn btn-outline-secondary" href="{{ route('admin.returns.index') }}">
                            Xóa chọn
                        </a>
                        @endif
                    </form>

                    @if(!empty($readers) && count($readers) > 0)
                        <div class="returns-reader-list">
                            <div class="returns-reader-head">
                                <span>Khách hàng</span>
                                <span>Mã thẻ</span>
                                <span>SĐT</span>
                                <span></span>
                            </div>
                            @foreach($readers as $r)
                                <div class="returns-reader-row">
                                    <div>
                                        <div class="reader-name">{{ $r->ho_ten }}</div>
                                    </div>
                                    <div class="reader-meta">{{ $r->so_the_doc_gia ?? '---' }}</div>
                                    <div class="reader-meta">{{ $r->so_dien_thoai ?? '---' }}</div>
                                    <div>
                                        <a class="btn btn-sm btn-success" href="{{ route('admin.returns.index', ['reader_id' => $r->id]) }}">
                                            Chọn
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif(request('search'))
                        <div class="alert alert-warning mt-3 mb-0">Không tìm thấy khách phù hợp.</div>
                    @endif
                </div>
            </div>

            @if($selectedReader)
            <div class="card returns-card">
                <div class="card-header returns-card-header">
                    <div>
                        <div class="returns-reader-title">Khách: {{ $selectedReader->ho_ten }}</div>
                        <div class="returns-reader-sub">#{{ $selectedReader->id }}</div>
                    </div>
                    <a href="{{ route('admin.fine-payments.index', ['reader_id' => $selectedReader->id]) }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-money-check-alt"></i> Xem phạt của khách
                    </a>
                </div>
                <div class="card-body">
                    @if(empty($borrowItems) || count($borrowItems) === 0)
                        <div class="alert alert-info mb-0">Khách hiện không có quyển nào đang mượn.</div>
                    @else
                        <form method="POST" action="{{ route('admin.returns.process') }}" id="returnForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="reader_id" value="{{ $selectedReader->id }}" />

                            <div class="returns-table">
                                <div class="returns-table-head">
                                    <span>Chọn</span>
                                    <span>Sách</span>
                                    <span>Phiếu</span>
                                    <span>Hẹn trả</span>
                                    <span>Tình trạng</span>
                                    <span>Ảnh trả sách</span>
                                    <span>Phí gia hạn</span>
                                    <span>Phạt dự kiến</span>
                                </div>
                                <div class="returns-table-body">
                                    @foreach($borrowItems as $i => $item)
                                        @php
                                            $due = $item->ngay_hen_tra ? \Carbon\Carbon::parse($item->ngay_hen_tra)->format('d/m/Y') : '---';
                                            $bookPrice = (float) ($item->book->gia ?? 0);
                                            $bookType = $item->book->loai_sach ?? 'binh_thuong';
                                            $startCondition = $item->inventory->condition ?? 'Trung binh';
                                            $damageFineDamaged = \App\Services\PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition);
                                            $damageFineLight = (int) round($damageFineDamaged * 0.5);
                                            $damageFineLost = \App\Services\PricingService::calculateLostBookFine($bookPrice, $bookType, $startCondition);
                                            $extensionFee = ((int) ($item->so_lan_gia_han ?? 0)) * 5 * 5000;
                                            $pendingFineAmount = (int) round((float) $item->pendingFines->sum('amount'));
                                            $isAwaitingPayment = $item->trang_thai !== 'Dang muon' && $pendingFineAmount > 0;
                                        @endphp
                                        <div class="returns-row {{ $isAwaitingPayment ? 'returns-row--awaiting-payment' : '' }}">
                                            <div class="text-center">
                                                <input type="checkbox" class="form-check-input js-select-item" name="items[{{ $i }}][selected]" value="1" data-index="{{ $i }}" {{ $isAwaitingPayment ? 'disabled' : '' }}>
                                                <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                                            </div>
                                            <div>
                                                <div class="book-name">{{ $item->book->ten_sach ?? '---' }}</div>
                                                <div class="book-meta">ID item: {{ $item->id }}</div>
                                                @if($isAwaitingPayment)
                                                    <div class="book-meta text-danger fw-semibold mt-1">Đã nhận sách, chờ thanh toán phạt: {{ number_format($pendingFineAmount) }}₫</div>
                                                @endif
                                            </div>
                                            <div class="returns-pill">#{{ $item->borrow_id }}</div>
                                            <div class="returns-date">{{ $due }}</div>
                                            <div>
                                                @if($isAwaitingPayment)
                                                    <span class="badge bg-warning text-dark">Đã nhận sách</span>
                                                @else
                                                    <select class="form-select form-select-sm js-condition"
                                                            name="items[{{ $i }}][condition]"
                                                            data-damage-binh-thuong="0"
                                                            data-damage-hong-nhe="{{ (int) $damageFineLight }}"
                                                            data-damage-hong="{{ (int) $damageFineDamaged }}"
                                                            data-damage-mat="{{ (int) $damageFineLost }}"
                                                            disabled>
                                                        <option value="binh_thuong">Bình thường</option>
                                                        <option value="hong_nhe">Hỏng nhẹ</option>
                                                        <option value="hong_nang">Hỏng nặng</option>
                                                        <option value="mat_sach">Mất sách</option>
                                                    </select>
                                                @endif
                                            </div>
                                            <div class="returns-proof">
                                                @if($isAwaitingPayment)
                                                    @php
                                                        $proofs = is_array($item->return_proof_images ?? null)
                                                            ? $item->return_proof_images
                                                            : (is_string($item->return_proof_images) ? json_decode($item->return_proof_images, true) : []);
                                                        $proofs = is_array($proofs) ? $proofs : [];
                                                    @endphp
                                                    @if(!empty($proofs))
                                                        <div class="proof-grid">
                                                            @foreach($proofs as $proof)
                                                                @php
                                                                    $proofUrl = preg_match('/^https?:\/\//i', $proof)
                                                                        ? $proof
                                                                        : asset('storage/' . ltrim(str_replace(['\\', 'storage/'], ['/', ''], $proof), '/'));
                                                                @endphp
                                                                <a href="{{ $proofUrl }}" target="_blank">
                                                                    <img src="{{ $proofUrl }}" alt="Ảnh trả sách">
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-muted">Chưa có ảnh</span>
                                                    @endif
                                                    <div class="proof-actions">
                                                        <label class="proof-upload">
                                                            <input type="file" name="proof_images[{{ $item->id }}][]" accept="image/*" multiple>
                                                            <span>Tải thêm</span>
                                                        </label>
                                                        <button type="submit" class="btn btn-sm btn-outline-primary" name="action" value="attach_proof">
                                                            Lưu ảnh
                                                        </button>
                                                    </div>
                                                @else
                                                    <label class="proof-upload">
                                                        <input type="file" name="proof_images[{{ $item->id }}][]" accept="image/*" multiple>
                                                        <span>Tải ảnh</span>
                                                    </label>
                                                @endif
                                            </div>
                                            <div class="returns-fee">
                                                @php
                                                    $rentAmount = (int) ($extensionFee ?? 0);
                                                @endphp
                                                <span class="js-rent" data-value="{{ $rentAmount }}">{{ number_format($rentAmount) }}</span>₫
                                                @if(($item->so_lan_gia_han ?? 0) > 0)
                                                    <div class="fee-note">({{ $item->so_lan_gia_han }} lần)</div>
                                                @endif
                                            </div>
                                            <div class="returns-fine">
                                                @if($isAwaitingPayment)
                                                    <span class="text-danger">{{ number_format($pendingFineAmount) }}₫</span>
                                                @else
                                                    <span class="js-fine" data-item-id="{{ $item->id }}" data-due="{{ $item->ngay_hen_tra }}">0</span>₫
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="returns-actions-row">
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Xác nhận trả các quyển đã chọn?')">
                                    <i class="fas fa-check"></i> Xác nhận trả
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="returns-sidebar">
            <div class="card returns-summary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-receipt"></i> Tổng kết phát sinh</h3>
                </div>
                <div class="card-body">
                    <div class="summary-total">
                        <div class="summary-label">Tổng phí gia hạn</div>
                        <div class="summary-value" id="totalRent">0₫</div>
                    </div>
                    <div class="summary-total summary-secondary">
                        <div class="summary-label">Tổng phạt dự kiến</div>
                        <div class="summary-value is-danger" id="totalFine">0₫</div>
                    </div>

                    <div class="summary-note">
                        - Tiền thuê cơ bản đã thanh toán ở luồng mượn.<br>
                        - Khi trả chỉ thu thêm <strong>phí gia hạn</strong> (nếu có) và các khoản <strong>phạt</strong>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.returns-row--awaiting-payment {
    background: #fff7ed;
}

.returns-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.returns-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.returns-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 4px;
}

.returns-subtitle {
    font-size: 14px;
    color: var(--text-muted);
    margin: 0;
}

.returns-actions {
    display: flex;
    gap: 10px;
}

.returns-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.7fr);
    gap: 22px;
    align-items: start;
}

.returns-card {
    border: 1px solid rgba(148, 163, 184, 0.2);
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
}

.returns-filter-form {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 160px 160px;
    gap: 12px;
    align-items: end;
}

.returns-reader-list {
    margin-top: 18px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.returns-reader-head,
.returns-reader-row {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) minmax(0, 1fr) 120px;
    gap: 12px;
    padding: 12px 16px;
    align-items: center;
}

.returns-reader-head {
    background: #f8fafc;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    font-weight: 600;
}

.returns-reader-row {
    border-top: 1px solid #e2e8f0;
}

.reader-name {
    font-weight: 600;
}

.reader-meta {
    color: #64748b;
    font-size: 13px;
}

.returns-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.returns-reader-title {
    font-weight: 700;
}

.returns-reader-sub {
    font-size: 12px;
    color: #64748b;
}

.returns-table {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.returns-table-head,
.returns-row {
    display: grid;
    grid-template-columns: 70px minmax(0, 2fr) 90px 110px 160px minmax(160px, 1.2fr) 130px 140px;
    gap: 12px;
    padding: 12px 16px;
    align-items: center;
}

.returns-table-head {
    background: #f8fafc;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    font-weight: 600;
}

.returns-row {
    border-top: 1px solid #e2e8f0;
}

.returns-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 999px;
    background: #eef2ff;
    color: #4f46e5;
    font-size: 12px;
    font-weight: 600;
    width: fit-content;
}

.returns-date {
    font-size: 13px;
    color: #0f172a;
}

.returns-fee {
    font-weight: 700;
    color: #0f766e;
}

.fee-note {
    font-size: 12px;
    color: #94a3b8;
}

.returns-fine {
    font-weight: 700;
    color: #ef4444;
}

.returns-proof .proof-upload {
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    padding: 6px 10px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #0f766e;
    cursor: pointer;
}

.returns-proof .proof-upload input {
    display: none;
}

.returns-proof .proof-actions {
    display: flex;
    gap: 8px;
    margin-top: 8px;
    align-items: center;
}

.returns-proof .proof-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.returns-proof .proof-grid img {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.returns-actions-row {
    display: flex;
    justify-content: flex-end;
    margin-top: 16px;
}

.returns-summary {
    position: sticky;
    top: 92px;
    border: 1px solid rgba(148, 163, 184, 0.25);
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.1);
}

.summary-total {
    padding: 12px 0 16px;
    border-bottom: 1px dashed #e2e8f0;
}

.summary-total.summary-secondary {
    margin-top: 14px;
}

.summary-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    margin-bottom: 6px;
}

.summary-value {
    font-size: 24px;
    font-weight: 800;
    color: #0f766e;
}

.summary-value.is-danger {
    color: #ef4444;
}

.summary-note {
    margin-top: 16px;
    font-size: 12px;
    color: #64748b;
    line-height: 1.6;
}

@media (max-width: 1200px) {
    .returns-grid {
        grid-template-columns: 1fr;
    }

    .returns-summary {
        position: static;
    }

    .returns-filter-form {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .returns-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .returns-reader-head,
    .returns-reader-row {
        grid-template-columns: 1fr;
    }

    .returns-table-head {
        display: none;
    }

    .returns-row {
        grid-template-columns: 1fr;
        gap: 8px;
    }
}
</style>
@endpush

@push('scripts')
<script>
    function formatMoney(n){
        try { return (n||0).toLocaleString('vi-VN'); } catch(e){ return n; }
    }

    function calcLateFine(dueDateStr){
        if(!dueDateStr) return 0;
        const due = new Date(dueDateStr);
        const today = new Date();
        due.setHours(0,0,0,0);
        today.setHours(0,0,0,0);
        const diffMs = today - due;
        const days = Math.floor(diffMs / (1000*60*60*24));
        if(days <= 0) return 0;
        const threshold = 3;
        const fineDay1 = 5000;
        const fineDay2 = 15000;
        if(days <= threshold) return days * fineDay1;
        return (threshold * fineDay1) + ((days - threshold) * fineDay2);
    }

    function updateTotals(){
        let totalFine = 0;
        let totalRent = 0;
        const fineEls = document.querySelectorAll('.js-fine');
        const rentEls = document.querySelectorAll('.js-rent');

        document.querySelectorAll('.js-select-item').forEach(cb => {
            const idx = parseInt(cb.dataset.index || '0', 10);
            if (!cb.checked) return;

            const rowFineEl = fineEls[idx];
            const rowRentEl = rentEls[idx];

            if (rowFineEl) {
                totalFine += parseInt(rowFineEl.dataset.value || '0', 10);
            }
            if (rowRentEl) {
                totalRent += parseInt(rowRentEl.dataset.value || '0', 10);
            }
        });
        const totalFineEl = document.getElementById('totalFine');
        const totalRentEl = document.getElementById('totalRent');
        if (totalFineEl) totalFineEl.textContent = formatMoney(totalFine) + '₫';
        if (totalRentEl) totalRentEl.textContent = formatMoney(totalRent) + '₫';
    }

    function calcDamageFine(conditionEl){
        if(!conditionEl) return 0;
        const val = conditionEl.value || 'binh_thuong';
        if(val === 'mat_sach'){
            return parseInt(conditionEl.dataset.damageMat || '0', 10);
        }
        if(val === 'hong_nhe'){
            return parseInt(conditionEl.dataset.damageHongNhe || '0', 10);
        }
        if(val === 'hong_nang'){
            return parseInt(conditionEl.dataset.damageHong || '0', 10);
        }
        return 0;
    }

    document.addEventListener('DOMContentLoaded', function(){
        const rows = document.querySelectorAll('.js-fine');
        rows.forEach((el) => {
            const due = el.getAttribute('data-due');
            const fine = calcLateFine(due);
            el.dataset.late = fine;
            el.dataset.value = fine;
            el.textContent = formatMoney(fine);
        });

        document.querySelectorAll('.js-select-item').forEach(cb => {
            cb.addEventListener('change', function(){
                const idx = this.dataset.index;
                const conditionEl = document.querySelectorAll('.js-condition')[idx];
                if(conditionEl){
                    conditionEl.disabled = !this.checked;
                }

                const fineEl = document.querySelectorAll('.js-fine')[idx];
                if(fineEl){
                    const late = parseInt(fineEl.dataset.late || '0', 10);
                    const damage = conditionEl ? calcDamageFine(conditionEl) : 0;
                    const total = late + damage;
                    fineEl.dataset.value = total;
                    fineEl.textContent = formatMoney(total);
                }
                updateTotals();
            });
        });

        document.querySelectorAll('.js-condition').forEach((sel, idx) => {
            sel.addEventListener('change', function(){
                const cb = document.querySelectorAll('.js-select-item')[idx];
                if(!cb || !cb.checked) return;
                const fineEl = document.querySelectorAll('.js-fine')[idx];
                if(!fineEl) return;
                const late = parseInt(fineEl.dataset.late || '0', 10);
                const damage = calcDamageFine(this);
                const total = late + damage;
                fineEl.dataset.value = total;
                fineEl.textContent = formatMoney(total);
                updateTotals();
            });
        });

        updateTotals();
    });
</script>
@endpush
