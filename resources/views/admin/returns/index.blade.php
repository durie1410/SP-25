@extends('layouts.admin')

@section('title', 'Trả sách theo khách')

@section('content')
<div class="returns-page">
    <div class="returns-header">


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
                 
                </div>
                <div class="card-body">
                    @if(empty($borrowItems) || count($borrowItems) === 0)
                        <div class="alert alert-info mb-0">Khách hiện không có quyển nào đang mượn.</div>
                    @else
                        <form method="POST" action="{{ route('admin.returns.prepare') }}" id="returnForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="reader_id" value="{{ $selectedReader->id }}" />
                            @foreach($borrowItems as $i => $item)
                                <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                                <input type="hidden" name="items[{{ $i }}][condition]" value="binh_thuong" class="js-condition-value">
                            @endforeach

                            <div class="returns-table">
                                <div class="returns-table-head">
                                    <span>Chọn</span>
                                    <span>Sách</span>
                                    <span>Ngày hẹn trả</span>
                                    <span>Tình trạng</span>
                                    <span>Ảnh trả sách</span>
                                    <span>Phí & Phạt</span>
                                </div>
                                <div class="returns-table-body">
                                    @foreach($borrowItems as $i => $item)
                                        @php
                                            $due = $item->ngay_hen_tra ? \Carbon\Carbon::parse($item->ngay_hen_tra)->format('d/m/Y') : '---';
                                            $isOverdue = $item->ngay_hen_tra && \Carbon\Carbon::parse($item->ngay_hen_tra)->lt(\Carbon\Carbon::today());
                                            $bookPrice = (float) ($item->book->gia ?? 0);
                                            $bookType = $item->book->loai_sach ?? 'binh_thuong';
                                            $startCondition = $item->inventory->condition ?? 'Trung binh';
                                            $damageFineDamaged = (int) \App\Services\PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition);
                                            $damageFineLight = (int) round($damageFineDamaged * 0.5);
                                            $damageFineLost = (int) \App\Services\PricingService::calculateLostBookFine($bookPrice, $bookType, $startCondition);
                                            $pendingFineAmount = (int) round((float) $item->pendingFines->sum('amount'));
                                            $isAwaitingPayment = $item->trang_thai !== 'Dang muon' && $pendingFineAmount > 0;
                                            $p = $item->return_proof_images ?? null;
                                            $proofs = is_array($p) ? $p : (is_string($p) ? (array) json_decode($p, true) : []);
                                        @endphp
                                        <div class="returns-row {{ $isAwaitingPayment ? 'returns-row--awaiting-payment' : '' }} {{ $isOverdue && !$isAwaitingPayment ? 'returns-row--overdue' : '' }}">
                                            {{-- Col 1: Checkbox --}}
                                            <div class="text-center">
                                                <input type="checkbox" class="form-check-input js-select-item"
                                                    name="items[{{ $i }}][selected]" value="1"
                                                    data-index="{{ $i }}"
                                                    {{ $isAwaitingPayment ? 'disabled' : '' }}>
                                            </div>

                                            {{-- Col 2: Thông tin sách --}}
                                            <div class="returns-book">
                                                <div class="book-name">{{ $item->book->ten_sach ?? '---' }}</div>
                                                <div class="book-meta">
















                                                    {{-- #{{ $item->borrow_id }} &bull; Item #{{ $item->id }} --}}
















                                                    @if($isOverdue && !$isAwaitingPayment)
                                                        <span class="badge bg-danger ms-1">Quá hạn</span>
                                                    @elseif($item->trang_thai === 'Qua han')
                                                        <span class="badge bg-danger ms-1">Quá hạn</span>
                                                    @endif
                                                </div>
                                                @if($isAwaitingPayment)
                                                    <div class="book-meta text-danger fw-semibold mt-1">
                                                        Đã nhận sách, chờ thanh toán: {{ number_format($pendingFineAmount) }}₫
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Col 3: Ngày hẹn trả --}}
                                            <div class="returns-date {{ $isOverdue && !$isAwaitingPayment ? 'text-danger fw-semibold' : '' }}">
                                                {{ $due }}
                                            </div>

                                            {{-- Col 4: Tình trạng sách --}}
                                            <div>
                                                @if($isAwaitingPayment)
                                                    <span class="badge bg-warning text-dark">Đã nhận sách</span>
                                                @else
                                                    <select class="form-select form-select-sm js-condition"
                                                        name="items[{{ $i }}][condition]"
                                                        data-damage-binh-thuong="0"
                                                        data-damage-hong-nhe="{{ $damageFineLight }}"
                                                        data-damage-hong="{{ $damageFineDamaged }}"
                                                        data-damage-mat="{{ $damageFineLost }}">
                                                        <option value="binh_thuong">Bình thường</option>
                                                        <option value="hong_nhe">Hỏng nhẹ (+{{ number_format($damageFineLight) }}₫)</option>
                                                        <option value="hong_nang">Hỏng nặng (+{{ number_format($damageFineDamaged) }}₫)</option>
                                                        <option value="mat_sach">Mất (+{{ number_format($damageFineLost) }}₫)</option>
                                                    </select>
                                                @endif
                                            </div>

                                            {{-- Col 5: Ảnh minh chứng --}}
                                            <div class="returns-proof">
                                                <div class="proof-grid">
                                                    @foreach($proofs as $proof)
                                                        @php
                                                            $normalizedPath = ltrim(str_replace(['\\', 'storage/'], ['/', ''], (string) $proof), '/');
                                                            $proofUrl = preg_match('/^https?:\/\//i', $proof)
                                                                ? $proof
                                                                : asset('storage/' . $normalizedPath);
                                                        @endphp
                                                        <div class="proof-thumb-wrap">
                                                            <a href="{{ $proofUrl }}" target="_blank">
                                                                <img src="{{ $proofUrl }}" alt="Ảnh trả sách" onerror="this.style.display='none'">
                                                            </a>
                                                            <button type="button" class="proof-delete-btn"
                                                                onclick="if(confirm('Xóa ảnh này?')) { fetch('{{ route('admin.returns.process') }}', {method:'POST', body: new URLSearchParams({_token:'{{ csrf_token() }}', reader_id:'{{ $selectedReader->id }}', action:'delete_proof', item_id:'{{ $item->id }}', path:'{{ $proof }}'}), headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>location.reload()); }"
                                                                title="Xóa ảnh">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="proof-actions">
                                                    @if(count($proofs) > 0)
                                                        <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1"
                                                            onclick="if(confirm('Xóa tất cả ảnh minh chứng của sách này?')) { fetch('{{ route('admin.returns.process') }}', {method:'POST', body: new URLSearchParams({_token:'{{ csrf_token() }}', reader_id:'{{ $selectedReader->id }}', action:'delete_all_proofs', item_id:'{{ $item->id }}'}), headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>location.reload()); }">
                                                            <i class="fas fa-trash-alt"></i> Xóa all
                                                        </button>
                                                    @endif
                                                    <label class="proof-upload">
                                                        <input type="file" name="proof_images[{{ $item->id }}][]" accept="image/*" multiple>
                                                        <span><i class="fas fa-cloud-upload-alt"></i> Tải ảnh</span>
                                                    </label>
                                                </div>
                                            </div>

                                            {{-- Col 6: Phí gia hạn + Phạt dự kiến --}}
                                            <div class="returns-fee-fine">
                                                <div class="fee-row">
                                                    <span class="fee-label">Phạt dự kiến</span>
                                                    @if($isAwaitingPayment)
                                                        <span class="fee-value text-danger fw-bold">{{ number_format($pendingFineAmount) }}₫</span>
                                                    @else
                                                        <span class="fee-value text-danger fw-bold js-fine" data-item-id="{{ $item->id }}" data-due="{{ $item->ngay_hen_tra }}">—</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="returns-actions-row">
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Xác nhận trả các quyển đã chọn?')">
                                    <i class="fas fa-check"></i> Chuyển thanh toán
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card returns-card returns-card--returned">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Sách đã trả</h3>
                </div>
                <div class="card-body">
                    @php
                        $pendingDeleteInventoryIds = $pendingDeleteInventoryIds ?? [];
                        $conditionLabels = [
                            'binh_thuong' => 'Bình thường',
                            'hong_nhe' => 'Hỏng nhẹ',
                            'hong_nang' => 'Hỏng nặng',
                            'mat_sach' => 'Mất sách',
                        ];
                        $statusLabels = [
                            'Da tra' => 'Đã trả',
                            'Hong' => 'Hỏng',
                            'Mat sach' => 'Mất sách',
                        ];
                        $inventoryStatusLabels = [
                            'Co san' => 'Có sẵn',
                            'Dang muon' => 'Đang mượn',
                            'Hong' => 'Hỏng',
                            'Mat' => 'Mất',
                            'Thanh ly' => 'Thanh lý',
                        ];
                    @endphp
                    @if(empty($returnedItems) || count($returnedItems) === 0)
                        <div class="alert alert-info mb-0">Chưa có sách đã trả cần sử lí.</div>
                    @else
                        <div class="returned-table">
                            <div class="returned-table-head">
                                <span>Sách</span>
                                <span>Ảnh sách</span>
                                <span>Ảnh minh chứng</span>
                                <span>Ngày trả</span>
                                <span>Tình trạng</span>
                                <span>Thao tác</span>
                            </div>
                            <div class="returned-table-body">
                                @foreach($returnedItems as $item)
                                    @php
                                        $returnDate = $item->ngay_tra_thuc_te ? \Carbon\Carbon::parse($item->ngay_tra_thuc_te)->format('d/m/Y') : '---';
                                        $condition = $item->tinh_trang_sach_cuoi ?? 'binh_thuong';
                                        $conditionLabel = $conditionLabels[$condition] ?? $condition;
                                        $statusLabel = $statusLabels[$item->trang_thai] ?? $item->trang_thai;
                                        $inventoryStatus = $item->inventory->status ?? '---';
                                        $inventoryStatusLabel = $inventoryStatusLabels[$inventoryStatus] ?? $inventoryStatus;
                                        $canApprove = in_array($item->trang_thai, ['Da tra', 'Hong', 'Mat sach']);
                                        $canDelete = $item->inventory && (in_array($item->trang_thai, ['Hong', 'Mat sach']) || in_array($condition, ['hong_nhe', 'hong_nang', 'mat_sach']));
                                        $hasPendingDelete = $item->inventorie_id && in_array($item->inventorie_id, $pendingDeleteInventoryIds);
                                        $bookImageUrl = ($item->book && $item->book->hinh_anh)
                                            ? ($item->book->image_url ?? asset('images/default-book.png'))
                                            : asset('images/default-book.png');
                                        $p = $item->return_proof_images ?? null;
                                        $proofs = is_array($p) ? $p : (is_string($p) ? (array) json_decode($p, true) : []);
                                    @endphp
                                    <div class="returned-row">
                                        <div class="returns-book">
                                            <div class="book-name">{{ $item->book->ten_sach ?? '---' }}</div>

















                                            {{--  --}}
                                            {{-- <div class="book-meta">#{{ $item->borrow_id }} &bull; Item #{{ $item->id }}</div> --}}
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                      
                                        </div>
                                        <div class="returned-book-thumb">
                                            <img src="{{ $bookImageUrl }}" alt="{{ $item->book->ten_sach ?? 'Book' }}">
                                        </div>
                                        <div class="returned-proof">
                                            @if(!empty($proofs))
                                                <div class="proof-grid">
                                                    @foreach($proofs as $proof)
                                                        @php
                                                            $normalizedPath = ltrim(str_replace(['\\', 'storage/'], ['/', ''], (string) $proof), '/');
                                                            $proofUrl = preg_match('/^https?:\/\//i', $proof)
                                                                ? $proof
                                                                : asset('storage/' . $normalizedPath);
                                                        @endphp
                                                        <div class="proof-thumb-wrap">
                                                            <a href="{{ $proofUrl }}" target="_blank">
                                                                <img src="{{ $proofUrl }}" alt="Ảnh trả sách">
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted small">Chưa có ảnh</span>
                                            @endif
                                        </div>
                                        <div class="returns-date">{{ $returnDate }}</div>
                                        <div>
                                            <span class="badge bg-secondary">{{ $conditionLabel }}</span>
                                        </div>
                                        <div class="returned-actions">
                                            @if(!in_array($condition, ['hong_nhe', 'hong_nang', 'mat_sach']))
                                                <form method="POST" action="{{ route('admin.returns.approve-stock', $item->id) }}">
                                                    @csrf
                                                    <input type="hidden" name="reader_id" value="{{ $selectedReader->id }}">
                                                    <button type="submit" class="btn btn-sm btn-success"
                                                        {{ (!$canApprove || $hasPendingDelete) ? 'disabled' : '' }}
                                                        onclick="return confirm('Duyệt sách này về kho?')">
                                                        Duyệt về kho
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.returns.delete-returned', $item->id) }}">
                                                @csrf
                                                <input type="hidden" name="reader_id" value="{{ $selectedReader->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    {{ (!$canDelete || $hasPendingDelete) ? 'disabled' : '' }}
                                                    onclick="return confirm('Xóa sách này khỏi kho?')">
                                                    Xóa
                                                </button>
                                            </form>
                                            @if($hasPendingDelete)
                                                <span class="text-muted small">Đã có yêu cầu xóa</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
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
                        <div class="summary-label">Tổng phạt dự kiến</div>
                        <div class="summary-value is-danger" id="totalFine">0₫</div>
                    </div>

                    <div class="summary-note">
                        - Khi trả chỉ thu các khoản <strong>phạt</strong> (quá hạn, hỏng, mất).
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

.returned-table {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.returns-table-head,
.returns-row {
    display: grid;
    grid-template-columns: 44px 1fr 110px 200px 140px 160px;
    gap: 10px;
    padding: 10px 12px;
    align-items: start;
}

.returned-table-head,
.returned-row {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 8px;
    padding: 8px 10px;
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

.returned-table-head {
    background: #f8fafc;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    font-weight: 600;
}

.returned-table-head span {
    text-align: center;
}

.returned-table-head span:nth-child(1) {
    text-align: left;
}

.returns-table-head span:nth-child(1),
.returns-table-head span:nth-child(3),
.returns-table-head span:nth-child(6) {
    text-align: center;
}

.returns-row {
    align-items: center;
    border-top: 1px solid #e2e8f0;
}

.returned-row {
    align-items: center;
    border-top: 1px solid #e2e8f0;
}

.returned-row > div {
    display: flex;
    justify-content: center;
}

.returned-row > div:nth-child(1) {
    justify-content: flex-start;
}

.returned-status {
    font-size: 11px;
    color: #64748b;
    text-align: center;
}

.returned-book-thumb {
    width: 40px;
    height: 56px;
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}

.returned-book-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.returned-proof .proof-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
}

.returned-proof .proof-thumb-wrap {
    width: 40px;
    height: 56px;
}

.returned-proof .proof-thumb-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    display: block;
}

.returned-actions {
    display: flex;
    gap: 6px;
    flex-wrap: nowrap;
    align-items: center;
}

.returned-actions form {
    margin: 0;
}

.returns-row > div:nth-child(2) {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.returns-row > div:nth-child(4) {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.returns-row > div:nth-child(5) {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.returns-row > div:nth-child(6) {
    display: flex;
    flex-direction: column;
    gap: 4px;
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
    text-align: center;
}

.returns-fee-fine {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.fee-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.fee-label {
    color: #64748b;
}

.fee-value {
    font-weight: 700;
    color: #0f766e;
    white-space: nowrap;
}

.returns-row--overdue {
    background: #fff7ed;
}

.returns-proof .proof-upload {
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    padding: 6px 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 12px;
    color: #0f766e;
    cursor: pointer;
    width: 100%;
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
    gap: 4px;
}

.returns-proof .proof-thumb-wrap {
    position: relative;
    display: inline-block;
}

.returns-proof .proof-thumb-wrap {
    width: 48px;
    height: 48px;
}

.returns-proof .proof-thumb-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    display: block;
}

.returns-proof .proof-delete-form {
    position: absolute;
    top: -6px;
    right: -6px;
    margin: 0;
}

.returns-proof .proof-delete-btn {
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 10px;
    line-height: 18px;
    text-align: center;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.returns-proof .proof-delete-btn:hover {
    background: #dc2626;
}
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

@media (max-width: 1200px) {
    .returns-table-head,
    .returns-row {
        grid-template-columns: 44px 1fr 110px 170px 120px 140px;
    }

    .returned-table-head,
    .returned-row {
        grid-template-columns: 1fr 48px 120px 90px 120px 80px 180px;
    }
}

@media (max-width: 992px) {
    .returns-table-head,
    .returns-row {
        grid-template-columns: 44px 1fr 100px 160px;
    }

    .returns-table-head span:nth-child(5),
    .returns-table-head span:nth-child(6),
    .returns-row > div:nth-child(5),
    .returns-row > div:nth-child(6) {
        display: none;
    }

    .returned-table-head,
    .returned-row {
        grid-template-columns: 1fr 48px 90px 120px 180px;
    }

    .returned-table-head span:nth-child(3),
    .returned-row > div:nth-child(3),
    .returned-table-head span:nth-child(6),
    .returned-row > div:nth-child(6) {
        display: none;
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
        padding: 12px;
    }

    .returns-row > div:nth-child(n) {
        display: flex !important;
    }

    .returned-table-head {
        display: none;
    }

    .returned-row {
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 12px;
    }

    .returned-row > div:nth-child(n) {
        display: flex !important;
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
        const finePerDay = 5000;
        return days * finePerDay;
    }

    function updateTotals(){
        let totalFine = 0;

        document.querySelectorAll('.js-select-item:checked').forEach(cb => {
            const row = cb.closest('.returns-row');
            if (!row) return;

            const fineEl = row.querySelector('.js-fine');
            const conditionEl = row.querySelector('.js-condition');

            const late = fineEl ? parseInt(fineEl.dataset.late || '0', 10) : 0;
            const damage = conditionEl ? calcDamageFine(conditionEl) : 0;

            totalFine += late + damage;
        });

        const totalFineEl = document.getElementById('totalFine');
        if (totalFineEl) totalFineEl.textContent = formatMoney(totalFine) + '₫';
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
        // Tính phạt quá hạn ban đầu cho mỗi dòng
        const rows = document.querySelectorAll('.js-fine');
        rows.forEach((el) => {
            const due = el.getAttribute('data-due');
            const fine = calcLateFine(due);
            el.dataset.late = fine;
            el.dataset.value = fine;
            el.textContent = (fine > 0) ? formatMoney(fine) + '₫' : '—';
        });

        // Khi tick checkbox
        document.querySelectorAll('.js-select-item').forEach(cb => {
            cb.addEventListener('change', function(){
                const row = this.closest('.returns-row');
                if (!row) return;

                const fineEl = row.querySelector('.js-fine');
                const conditionEl = row.querySelector('.js-condition');

                if(fineEl){
                    const late = parseInt(fineEl.dataset.late || '0', 10);
                    const damage = conditionEl ? calcDamageFine(conditionEl) : 0;
                    const total = late + damage;
                    fineEl.dataset.value = total;
                    fineEl.textContent = (total > 0) ? formatMoney(total) + '₫' : '—';
                }
                updateTotals();
            });
        });

        // Khi đổi tình trạng sách
        document.querySelectorAll('.js-condition').forEach((sel) => {
            sel.addEventListener('change', function(){
                const row = this.closest('.returns-row');
                if (!row) return;
                const cb = row.querySelector('.js-select-item');
                const fineEl = row.querySelector('.js-fine');

                if(!cb || !cb.checked || !fineEl) return;

                const late = parseInt(fineEl.dataset.late || '0', 10);
                const damage = calcDamageFine(this);
                const total = late + damage;
                fineEl.dataset.value = total;
                fineEl.textContent = (total > 0) ? formatMoney(total) + '₫' : '—';
                updateTotals();
            });
        });

        updateTotals();

        // === Preview ảnh ngay khi chọn file ===
        document.querySelectorAll('.returns-proof input[type="file"]').forEach(input => {
            input.addEventListener('change', function () {
                const proofDiv = this.closest('.returns-proof');
                if (!proofDiv) return;

                // Xóa preview cũ
                proofDiv.querySelectorAll('.proof-preview-temp').forEach(el => el.remove());

                Array.from(this.files).forEach(file => {
                    if (!file.type.startsWith('image/')) return;

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.cssText = 'width:calc(33.333% - 4px);height:48px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0;';

                        const grid = proofDiv.querySelector('.proof-grid') || proofDiv;
                        grid.prepend(img);
                    };
                    reader.readAsDataURL(file);
                });
            });
        });
    });
</script>
@endpush
