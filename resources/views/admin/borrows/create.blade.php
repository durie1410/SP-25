@extends('layouts.admin')

@section('title', 'Cho Mượn Sách Mới - Admin')

@section('content')
<div class="admin-table borrow-create-page">
    <div class="borrow-create-hero">
        <div>
            <div class="borrow-create-kicker">Quản lý mượn trả</div>
            <h3><i class="fas fa-plus-circle"></i> Tạo Phiếu Mượn Mới</h3>
            <p>Chọn độc giả, thiết lập ngày trả và thêm sách bằng giao diện nhanh để hoàn tất phiếu mượn.</p>
        </div>
        <div class="borrow-hero-badges">
            <span class="borrow-chip"><i class="fas fa-calendar-day"></i> Ngày mượn: Hôm nay</span>
            <span class="borrow-chip"><i class="fas fa-hourglass-half"></i> Tối đa 14 ngày</span>
        </div>
    </div>

    <form action="{{ route('admin.borrows.store') }}" method="POST" id="borrowForm" class="borrow-create-form">
        @csrf
        <input type="hidden" name="reservation_id" value="{{ request('reservation_id') }}">

        <div class="borrow-panel mb-3">
            <div class="borrow-panel-title"><i class="fas fa-user-check"></i> Thông tin độc giả</div>
            {{-- Thông tin người mượn --}}
            <div class="mb-3">
                    <label class="form-label">Độc giả <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" id="readerSearch" class="form-control" placeholder="Tìm kiếm độc giả..." autocomplete="off">
                        <input type="hidden" name="reader_id" id="readerId" value="{{ $prefillReader->id ?? '' }}" required>
                        <div id="readerDropdown" class="dropdown-menu w-100" style="display:none; max-height:200px; overflow-y:auto;"></div>
                    </div>
                    <div id="borrowNotice" class="mt-2" style="display:none;"></div>
                    <div id="selectedReader" class="mt-2" style="display: {{ $prefillReader ? 'block' : 'none' }};">
                        <div class="alert alert-info">
                            <strong>Đã chọn:</strong> <span id="readerName">{{ $prefillReader ? $prefillReader->ho_ten . ' (' . $prefillReader->so_the_doc_gia . ')' : '' }}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearReader()">Xóa</button>
                        </div>
                    </div>
                </div>

            {{-- Địa chỉ --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tên người mượn</label>
                    <input type="text" name="ten_nguoi_muon" id="tenNguoiMuon" class="form-control" value="{{ $prefillReader->ho_ten ?? '' }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="so_dien_thoai" id="soDienThoai" class="form-control" value="{{ $prefillReader->so_dien_thoai ?? '' }}" readonly>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-12">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="dia_chi" id="diaChi" class="form-control" value="{{ $prefillReader->tinh_thanh ?? ($prefillReader->dia_chi ?? '') }}" readonly>
                    {{-- Giữ các hidden input để không làm hỏng logic lưu của Backend nếu cần --}}
                    <input type="hidden" name="tinh_thanh" id="tinhThanh" value="{{ $prefillReader->tinh_thanh ?? ($prefillReader->dia_chi ?? '') }}">
                    <input type="hidden" name="huyen" id="huyen" value="{{ $prefillReader->huyen ?? '' }}">
                    <input type="hidden" name="xa" id="xa" value="{{ $prefillReader->xa ?? '' }}">
                    <input type="hidden" name="so_nha" id="soNha" value="{{ $prefillReader->so_nha ?? '' }}">
                </div>
            </div>
        </div>

        <div class="borrow-panel mb-3">
            <div class="borrow-panel-title"><i class="fas fa-calendar-check"></i> Thời gian mượn trả</div>
            {{-- Thủ thư --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Thủ thư</label>
                    <select name="librarian_id" class="form-control">
                        <option value="">-- Chọn thủ thư --</option>
                        @foreach($librarians as $librarian)
                            <option value="{{ $librarian->id }}" {{ (old('librarian_id') ?? auth()->id()) == $librarian->id ? 'selected' : '' }}>
                                {{ $librarian->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Ngày mượn và trạng thái --}}
            <div class="row mb-1">
                <div class="col-md-6">
                    <label class="form-label">Ngày mượn</label>
                    <input type="date" name="ngay_muon" id="ngayMuonInput" class="form-control" value="{{ now()->toDateString() }}" readonly>
                    <small class="form-text text-muted">Ngày mượn cố định là hôm nay.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ngày trả dự kiến</label>
                    <input type="date" name="ngay_hen_tra" id="ngayHenTraInput" class="form-control" value="{{ request('ngay_hen_tra', now()->addDays((int) config('library.loan_days', 7))->toDateString()) }}">
                    <small class="form-text text-muted">Khoảng mượn tối đa 14 ngày kể từ hôm nay.</small>
                </div>
            </div>
        </div>

        <div class="borrow-panel mb-3">
            <div class="borrow-panel-title"><i class="fas fa-book-open"></i> Sách trong phiếu</div>
            <div class="mb-3">
                <label class="form-label">Thêm sách vào phiếu <span class="text-danger">*</span></label>
                <button type="button" class="btn btn-primary btn-open-book-picker" id="openBookPickerModalBtn">
                    <i class="fas fa-plus"></i> Chọn Sách
                </button>
                <div class="form-text">Ngày lấy mặc định là hôm nay. Bấm Chọn Sách để mở danh sách và thêm số lượng.</div>
            </div>

            <div class="mb-3">
                <div class="borrow-summary-strip">
                    <div class="borrow-metric-card">
                        <div class="metric-label">Số sách đã chọn</div>
                        <div class="metric-value" id="selectedBookCount">0</div>
                    </div>
                    <div class="borrow-metric-card">
                        <div class="metric-label">Số ngày thuê</div>
                        <div class="metric-value" id="selectedBorrowDays">1</div>
                    </div>
                    <div class="borrow-metric-card metric-fee">
                        <div class="metric-label">Tạm tính tiền thuê</div>
                        <div class="metric-value" id="estimatedTotalFee">0₫</div>
                    </div>
                </div>
            </div>

            <div class="mb-1" id="selectedBooksWrapper" style="display:none;">
                <label class="form-label">Danh sách sách đã chọn</label>
                <div id="selectedBooksList" class="d-grid gap-2"></div>
            </div>
        </div>
        <div id="selectedBookInputs"></div>

        {{-- Voucher --}}
        {{-- <div class="mb-3">
            <label class="form-label">Voucher</label>
            <select name="voucher_id" class="form-control">
                <option value="">-- Không sử dụng --</option>
                @foreach($vouchers as $voucher)
                    <option value="{{ $voucher->id }}">{{ $voucher->code }} - Giảm {{ $voucher->discount }}%</option>
                @endforeach
            </select>
        </div> --}}

        {{-- Nút submit --}}
        <div class="d-flex gap-2 borrow-submit-row">
            <button type="submit" class="btn btn-success borrow-submit-btn"><i class="fas fa-save"></i> Xác nhận Cho Mượn</button>
            <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary borrow-back-btn"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>
    </form>

    <div id="bookPickerModal" class="book-picker-modal" aria-hidden="true">
        <div class="book-picker-backdrop" id="bookPickerBackdrop"></div>
        <div class="book-picker-panel" role="dialog" aria-modal="true" aria-labelledby="bookPickerTitle">
            <div class="book-picker-header">
                <h5 id="bookPickerTitle"><i class="fas fa-book"></i> Thêm sách vào phiếu</h5>
                <button type="button" class="book-picker-close" id="closeBookPickerModalBtn" aria-label="Đóng">&times;</button>
            </div>
            <div class="book-picker-body">
                <div class="book-picker-search-row">
                    <input type="text" id="bookSearchInput" class="form-control" placeholder="Nhập tên sách hoặc tác giả...">
                    <button type="button" id="bookSearchBtn" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                </div>
                <div id="bookSearchResult" class="book-picker-result-list"></div>
            </div>
        </div>
    </div>

</div>
@endsection

@php
    $booksForJs = $books->map(function ($book) {
        return [
            'id' => $book->id,
            'ten_sach' => $book->ten_sach,
            'tac_gia' => $book->tac_gia,
            'so_luong' => $book->so_luong,
            'loai_sach' => $book->loai_sach,
            'image_url' => $book->image_url,
        ];
    })->values();
@endphp

@push('styles')
<style>
.borrow-create-page {
    --borrow-bg-soft: linear-gradient(145deg, #f7fbff 0%, #eef6f0 100%);
    --borrow-ink: #0f172a;
    --borrow-line: #dbe6f0;
    font-family: "Be Vietnam Pro", "Segoe UI", sans-serif;
}

.borrow-create-hero {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: flex-start;
    flex-wrap: wrap;
    background: radial-gradient(circle at 6% 12%, #1e293b 0%, #0b3f38 52%, #0f172a 100%);
    border-radius: 18px;
    padding: 18px 20px;
    color: #f8fafc;
    margin-bottom: 16px;
    box-shadow: 0 20px 36px rgba(15, 23, 42, 0.2);
}

.borrow-create-kicker {
    display: inline-block;
    font-size: 12px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.76);
    margin-bottom: 6px;
}

.borrow-create-hero h3 {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
}

.borrow-create-hero p {
    margin: 8px 0 0;
    color: rgba(241, 245, 249, 0.9);
    max-width: 700px;
}

.borrow-hero-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.borrow-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 8px 12px;
    background: rgba(248, 250, 252, 0.12);
    border: 1px solid rgba(248, 250, 252, 0.25);
    font-weight: 600;
}

.borrow-create-form {
    background: var(--borrow-bg-soft);
    border: 1px solid #d3e0ea;
    border-radius: 18px;
    padding: 16px;
}

.borrow-panel {
    background: #ffffffd9;
    border: 1px solid var(--borrow-line);
    border-radius: 14px;
    padding: 14px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}

.borrow-panel-title {
    font-size: 15px;
    font-weight: 800;
    color: var(--borrow-ink);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.borrow-create-page .form-label {
    font-weight: 700;
    color: #1e293b;
}

.borrow-create-page .form-control,
.borrow-create-page .form-select {
    border-radius: 11px;
    border: 1px solid #cfdce9;
    min-height: 42px;
}

.borrow-create-page .form-control:focus,
.borrow-create-page .form-select:focus {
    border-color: #0f766e;
    box-shadow: 0 0 0 0.2rem rgba(15, 118, 110, 0.16);
}

.btn-open-book-picker {
    border-radius: 11px;
    font-weight: 700;
    padding: 9px 16px;
    background: linear-gradient(135deg, #0f766e 0%, #0369a1 100%);
    border-color: transparent;
}

.btn-open-book-picker:hover,
.btn-open-book-picker:focus {
    background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
}

.borrow-summary-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.borrow-metric-card {
    border-radius: 12px;
    border: 1px solid #dbe6f0;
    background: #f8fbff;
    padding: 10px 12px;
}

.metric-label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.metric-value {
    margin-top: 2px;
    font-size: 25px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}

.metric-fee {
    background: linear-gradient(120deg, #fff7ed 0%, #ffedd5 100%);
    border-color: #fdba74;
}

.metric-fee .metric-value {
    color: #9a3412;
}

.borrow-submit-row {
    margin-top: 4px;
}

.borrow-submit-btn,
.borrow-back-btn {
    border-radius: 11px;
    font-weight: 700;
    padding: 10px 16px;
}

.borrow-submit-btn {
    background: linear-gradient(135deg, #16a34a 0%, #0f766e 100%);
    border-color: transparent;
}

.borrow-submit-btn:hover,
.borrow-submit-btn:focus {
    background: linear-gradient(135deg, #22c55e 0%, #0d9488 100%);
}

#selectedReader .alert {
    margin-bottom: 0;
    border-radius: 10px;
    border: 1px solid #bfdbfe;
    background: #eff6ff;
}

#readerDropdown {
    border-radius: 12px;
    border: 1px solid #d0deec;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
}

#readerDropdown .dropdown-item {
    padding-top: 8px;
    padding-bottom: 8px;
}

#readerDropdown .dropdown-item:hover {
    background: #ecfeff;
}

.book-picker-modal {
    position: fixed;
    inset: 0;
    z-index: 1200;
    display: none;
}

.book-picker-modal.is-open {
    display: block;
}

.book-picker-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
}

.book-picker-panel {
    position: relative;
    width: min(920px, calc(100% - 24px));
    max-height: calc(100vh - 26px);
    margin: 13px auto;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.28);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.book-picker-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.book-picker-header h5 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
}

.book-picker-close {
    border: 0;
    background: transparent;
    font-size: 28px;
    line-height: 1;
    color: #64748b;
    cursor: pointer;
}

.book-picker-body {
    padding: 14px;
    overflow: auto;
}

.book-picker-search-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 8px;
    margin-bottom: 12px;
}

.book-picker-result-list {
    display: grid;
    gap: 10px;
}

.book-picker-row {
    display: grid;
    grid-template-columns: 64px minmax(0, 1fr) auto;
    gap: 12px;
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px;
}

.book-picker-thumb {
    width: 64px;
    height: 88px;
    border-radius: 8px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}

.book-picker-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-picker-title {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

.book-picker-meta {
    color: #64748b;
    margin-top: 4px;
    font-size: 14px;
}

.book-picker-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.book-picker-qty {
    width: 92px;
}

.selected-book-item {
    display: grid;
    grid-template-columns: 56px minmax(0, 1fr) auto;
    gap: 10px;
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 10px;
    background: #fff;
}

.selected-book-thumb {
    width: 56px;
    height: 76px;
    border-radius: 7px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}

.selected-book-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@media (max-width: 768px) {
    .borrow-create-hero h3 {
        font-size: 23px;
    }

    .borrow-create-form {
        padding: 10px;
    }

    .borrow-panel {
        padding: 11px;
    }

    .borrow-summary-strip {
        grid-template-columns: 1fr;
    }

    .metric-value {
        font-size: 21px;
    }

    .book-picker-panel {
        width: calc(100% - 12px);
        margin: 6px auto;
        max-height: calc(100vh - 12px);
    }

    .book-picker-row {
        grid-template-columns: 1fr;
    }

    .book-picker-actions {
        justify-content: flex-start;
    }

    .book-picker-search-row {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const readerSearch = document.getElementById('readerSearch');
    const readerId = document.getElementById('readerId');
    const readerDropdown = document.getElementById('readerDropdown');
    const selectedReader = document.getElementById('selectedReader');
    const readerName = document.getElementById('readerName');
    const noticeBox = document.getElementById('borrowNotice');

    const tenNguoiMuon = document.getElementById('tenNguoiMuon');
    const soDienThoai = document.getElementById('soDienThoai');
    const diaChi = document.getElementById('diaChi');
    const tinhThanh = document.getElementById('tinhThanh');
    const huyen = document.getElementById('huyen');
    const xa = document.getElementById('xa');
    const soNha = document.getElementById('soNha');

    const autocompleteUrl = `{{ route('admin.autocomplete.readers') }}`;
    const openBookPickerModalBtn = document.getElementById('openBookPickerModalBtn');
    const bookPickerModalEl = document.getElementById('bookPickerModal');
    const bookPickerBackdrop = document.getElementById('bookPickerBackdrop');
    const closeBookPickerModalBtn = document.getElementById('closeBookPickerModalBtn');
    const bookSearchInput = document.getElementById('bookSearchInput');
    const bookSearchBtn = document.getElementById('bookSearchBtn');
    const bookSearchResult = document.getElementById('bookSearchResult');
    const dailyRentalFee = {{ (int) config('library.rental_per_day', 5000) }};
    const bookCatalog = @json($booksForJs);
    const getLocalTodayIso = () => {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    const ngayMuonInput = document.getElementById('ngayMuonInput');
    const ngayHenTraInput = document.getElementById('ngayHenTraInput');
    const selectedBooksWrapper = document.getElementById('selectedBooksWrapper');
    const selectedBooksList = document.getElementById('selectedBooksList');
    const selectedBookInputs = document.getElementById('selectedBookInputs');
    const selectedBookCount = document.getElementById('selectedBookCount');
    const selectedBorrowDays = document.getElementById('selectedBorrowDays');
    const estimatedTotalFee = document.getElementById('estimatedTotalFee');
    const reservationInput = document.querySelector('input[name="reservation_id"]');
    const borrowForm = document.getElementById('borrowForm');

    let readerTimeout;
    const selectedBooks = new Map(); // key book_id -> { ...book, qty }

    const showNotice = (message, type = 'success') => {
        noticeBox.className = `alert alert-${type} mt-2`;
        noticeBox.textContent = message;
        noticeBox.style.display = 'block';
        setTimeout(() => {
            noticeBox.style.display = 'none';
        }, 2500);
    };

    const hideReaderDropdown = () => {
        readerDropdown.style.display = 'none';
    };

    const formatMoney = (value) => Number(value || 0).toLocaleString('vi-VN') + '₫';

    const openBookPickerModal = () => {
        bookPickerModalEl.classList.add('is-open');
        bookPickerModalEl.setAttribute('aria-hidden', 'false');
        bookSearchInput.focus();
    };

    const closeBookPickerModal = () => {
        bookPickerModalEl.classList.remove('is-open');
        bookPickerModalEl.setAttribute('aria-hidden', 'true');
    };

    const renderBookSearchResults = (query = '') => {
        const keyword = String(query || '').trim().toLowerCase();

        const filtered = bookCatalog.filter((book) => {
            if (!keyword) {
                return true;
            }
            return String(book.ten_sach || '').toLowerCase().includes(keyword)
                || String(book.tac_gia || '').toLowerCase().includes(keyword);
        });

        if (!filtered.length) {
            bookSearchResult.innerHTML = '<div class="text-muted p-3 border rounded">Không tìm thấy sách phù hợp.</div>';
            return;
        }

        bookSearchResult.innerHTML = filtered.map((book) => {
            const selectedQty = selectedBooks.get(Number(book.id))?.qty || 0;
            const availableQty = Math.max(0, Number(book.so_luong || 0));
            const maxQty = availableQty;
            const isOutOfStock = availableQty <= 0;
            const image = book.image_url
                ? `<img src="${book.image_url}" alt="${book.ten_sach}">`
                : '📘';

            return `
                <div class="book-picker-row">
                    <div class="book-picker-thumb">${image}</div>
                    <div>
                        <div class="book-picker-title">${book.ten_sach || 'N/A'}</div>
                        <div class="book-picker-meta">${book.tac_gia || 'Không rõ'} | Đang có trong phiếu: ${selectedQty} | Tồn: ${availableQty}</div>
                    </div>
                    <div class="book-picker-actions">
                        <input type="number" min="1" max="${Math.max(1, maxQty)}" value="1" class="form-control form-control-sm book-picker-qty" data-book-qty-id="${book.id}" ${isOutOfStock ? 'disabled' : ''}>
                        <button type="button" class="btn btn-success btn-sm" data-add-book-id="${book.id}" ${isOutOfStock ? 'disabled' : ''}><i class="fas fa-plus"></i> Thêm</button>
                    </div>
                </div>
            `;
        }).join('');
    };

    openBookPickerModalBtn.addEventListener('click', () => {
        renderBookSearchResults('');
        openBookPickerModal();
    });

    closeBookPickerModalBtn.addEventListener('click', closeBookPickerModal);
    bookPickerBackdrop.addEventListener('click', closeBookPickerModal);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && bookPickerModalEl.classList.contains('is-open')) {
            closeBookPickerModal();
        }
    });

    bookSearchBtn.addEventListener('click', () => {
        renderBookSearchResults(bookSearchInput.value || '');
    });

    bookSearchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            renderBookSearchResults(bookSearchInput.value || '');
        }
    });

    bookSearchResult.addEventListener('click', (event) => {
        const addBtn = event.target.closest('[data-add-book-id]');
        if (!addBtn) {
            return;
        }

        const bookId = Number(addBtn.dataset.addBookId || 0);
        const found = bookCatalog.find((book) => Number(book.id) === bookId);
        if (!found) {
            showNotice('Không tìm thấy thông tin sách đã chọn.', 'danger');
            return;
        }

        const qtyInput = bookSearchResult.querySelector(`[data-book-qty-id="${bookId}"]`);
        const requestedQty = Math.max(1, Number(qtyInput?.value || 1));
        const maxQty = Math.max(0, Number(found.so_luong || 0));
        if (maxQty <= 0) {
            showNotice('Sách này đã hết tồn kho, không thể thêm vào phiếu.', 'danger');
            return;
        }
        const safeQty = Math.min(requestedQty, maxQty);

        selectedBooks.set(bookId, {
            ...found,
            qty: safeQty,
        });
        renderSelectedBooks();
        showNotice('Đã thêm sách vào phiếu.', 'success');
    });

    const getBorrowDays = () => {
        const start = ngayMuonInput?.value ? new Date(ngayMuonInput.value) : null;
        const end = ngayHenTraInput?.value ? new Date(ngayHenTraInput.value) : null;
        if (!start || !end || Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
            return 1;
        }
        const diff = Math.ceil((end - start) / (24 * 60 * 60 * 1000));
        return Math.max(1, diff);
    };

    const updateEstimatedSummary = () => {
        const count = Array.from(selectedBooks.values()).reduce((sum, book) => sum + Number(book.qty || 0), 0);
        const days = getBorrowDays();
        const total = count * days * dailyRentalFee;

        selectedBookCount.textContent = String(count);
        selectedBorrowDays.textContent = String(days);
        estimatedTotalFee.textContent = formatMoney(total);
    };

    const renderSelectedBooks = () => {
        selectedBooksList.innerHTML = '';
        selectedBookInputs.innerHTML = '';

        if (!selectedBooks.size) {
            selectedBooksWrapper.style.display = 'none';
            updateEstimatedSummary();
            return;
        }

        selectedBooksWrapper.style.display = 'block';

        Array.from(selectedBooks.values()).forEach((book) => {
            const item = document.createElement('div');
            item.className = 'selected-book-item';
            const image = book.image_url
                ? `<img src="${book.image_url}" alt="${book.ten_sach || 'Sách'}">`
                : '📘';
            item.innerHTML = `
                <div class="selected-book-thumb">${image}</div>
                <div>
                    <div class="fw-semibold">${book.ten_sach || 'N/A'}</div>
                    <small class="text-muted">${book.tac_gia || 'Không rõ tác giả'} | SL đã chọn: ${book.qty} | Còn: ${book.so_luong ?? 0}</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" data-remove-book-id="${book.id}">Xóa</button>
            `;
            selectedBooksList.appendChild(item);

            for (let i = 0; i < Number(book.qty || 0); i++) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'book_ids[]';
                hidden.value = String(book.id);
                selectedBookInputs.appendChild(hidden);
            }
        });

        updateEstimatedSummary();
        renderBookSearchResults(bookSearchInput.value || '');
    };

    const fillAddress = (reader) => {
        const parts = [reader.so_nha, reader.xa, reader.huyen, reader.tinh_thanh].filter((p) => p && String(p).trim() !== '');
        const fullAddress = parts.length > 0 ? parts.join(', ') : (reader.dia_chi || '');

        tenNguoiMuon.value = reader.ho_ten || '';
        soDienThoai.value = reader.so_dien_thoai || '';
        diaChi.value = fullAddress;
        tinhThanh.value = reader.tinh_thanh || '';
        huyen.value = reader.huyen || '';
        xa.value = reader.xa || '';
        soNha.value = reader.so_nha || '';
    };

    window.selectReader = (reader) => {
        readerId.value = reader.id;
        readerName.textContent = `${reader.ho_ten} (${reader.so_the_doc_gia || 'N/A'})`;
        fillAddress(reader);
        readerSearch.value = '';
        selectedReader.style.display = 'block';
        hideReaderDropdown();
    };

    window.clearReader = () => {
        readerId.value = '';
        selectedReader.style.display = 'none';
        tenNguoiMuon.value = '';
        soDienThoai.value = '';
        diaChi.value = '';
        tinhThanh.value = '';
        huyen.value = '';
        xa.value = '';
        soNha.value = '';
    };

    const showReaderDropdown = (readers) => {
        readerDropdown.innerHTML = '';

        if (!readers.length) {
            readerDropdown.innerHTML = '<div class="dropdown-item text-muted">Không tìm thấy độc giả</div>';
            readerDropdown.style.display = 'block';
            return;
        }

        readers.forEach((reader) => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.style.cursor = 'pointer';
            item.innerHTML = `
                <div class="fw-bold">${reader.ho_ten}</div>
                <small class="text-muted">Mã thẻ: ${reader.so_the_doc_gia || 'N/A'} | SĐT: ${reader.so_dien_thoai || 'N/A'}</small>
            `;
            item.onclick = () => window.selectReader(reader);
            readerDropdown.appendChild(item);
        });

        readerDropdown.style.display = 'block';
    };

    const searchReaders = (query) => {
        fetch(`${autocompleteUrl}?q=${encodeURIComponent(query)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then((res) => res.json())
            .then((data) => {
                showReaderDropdown(Array.isArray(data) ? data : []);
            })
            .catch(() => {
                showReaderDropdown([]);
            });
    };

    readerSearch.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(readerTimeout);
        readerTimeout = setTimeout(() => {
            if (query.length >= 2) {
                searchReaders(query);
            } else {
                hideReaderDropdown();
            }
        }, 250);
    });


    selectedBooksList.addEventListener('click', (event) => {
        const removeBtn = event.target.closest('[data-remove-book-id]');
        if (!removeBtn) {
            return;
        }
        selectedBooks.delete(Number(removeBtn.dataset.removeBookId));
        renderSelectedBooks();
    });

    if (ngayMuonInput && ngayHenTraInput) {
        const refreshFixedBorrowDate = () => {
            const todayIso = getLocalTodayIso();
            ngayMuonInput.value = todayIso;
            ngayMuonInput.min = todayIso;
            ngayMuonInput.max = todayIso;
            return todayIso;
        };

        const enforceDateRange = () => {
            const todayIso = refreshFixedBorrowDate();
            if (ngayMuonInput.value !== todayIso) {
                ngayMuonInput.value = todayIso;
            }

            ngayHenTraInput.min = todayIso;
            const maxReturnDate = new Date(todayIso);
            maxReturnDate.setDate(maxReturnDate.getDate() + 14);
            const maxReturnIso = maxReturnDate.toISOString().split('T')[0];
            ngayHenTraInput.max = maxReturnIso;

            if (ngayHenTraInput.value && ngayMuonInput.value && ngayHenTraInput.value < ngayMuonInput.value) {
                ngayHenTraInput.value = ngayMuonInput.value;
            }
            if (ngayHenTraInput.value && ngayHenTraInput.value > maxReturnIso) {
                ngayHenTraInput.value = maxReturnIso;
            }
            if (!ngayHenTraInput.value) {
                const defaultReturnDate = new Date(todayIso);
                defaultReturnDate.setDate(defaultReturnDate.getDate() + 7);
                const defaultReturnIso = defaultReturnDate.toISOString().split('T')[0];
                ngayHenTraInput.value = defaultReturnIso > maxReturnIso ? maxReturnIso : defaultReturnIso;
            }

            updateEstimatedSummary();
        };

        ngayMuonInput.addEventListener('change', enforceDateRange);
        ngayHenTraInput.addEventListener('change', enforceDateRange);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                enforceDateRange();
            }
        });
        enforceDateRange();
    }

    borrowForm.addEventListener('submit', (event) => {
        const hasReservation = reservationInput && reservationInput.value;
        const todayIso = getLocalTodayIso();
        const maxReturnDate = new Date(todayIso);
        maxReturnDate.setDate(maxReturnDate.getDate() + 14);
        const maxReturnIso = maxReturnDate.toISOString().split('T')[0];

        if (ngayMuonInput && ngayMuonInput.value !== todayIso) {
            event.preventDefault();
            showNotice('Ngày mượn bắt buộc là hôm nay.', 'danger');
            ngayMuonInput.value = todayIso;
            ngayMuonInput.focus();
            return;
        }

        if (ngayHenTraInput && (ngayHenTraInput.value < todayIso || ngayHenTraInput.value > maxReturnIso)) {
            event.preventDefault();
            showNotice('Ngày trả chỉ được từ hôm nay đến tối đa 14 ngày.', 'danger');
            ngayHenTraInput.focus();
            return;
        }

        if (!hasReservation && selectedBooks.size === 0) {
            event.preventDefault();
            showNotice('Vui lòng thêm ít nhất 1 sách vào phiếu mượn.', 'danger');
            if (openBookPickerModalBtn) {
                openBookPickerModalBtn.focus();
            }
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#readerSearch') && !e.target.closest('#readerDropdown')) {
            hideReaderDropdown();
        }
    });

    @if($prefillReader)
    document.addEventListener('DOMContentLoaded', function() {
        window.selectReader({
            id: {{ $prefillReader->id }},
            ho_ten: @json($prefillReader->ho_ten),
            so_the_doc_gia: @json($prefillReader->so_the_doc_gia),
            so_dien_thoai: @json($prefillReader->so_dien_thoai),
            tinh_thanh: @json($prefillReader->tinh_thanh),
            huyen: @json($prefillReader->huyen),
            xa: @json($prefillReader->xa),
            so_nha: @json($prefillReader->so_nha),
            dia_chi: @json($prefillReader->dia_chi),
        });
    });
    @endif

    renderSelectedBooks();
    renderBookSearchResults('');
})();
</script>
@endpush


