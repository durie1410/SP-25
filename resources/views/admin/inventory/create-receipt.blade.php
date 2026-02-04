@extends('layouts.admin')

@section('title', 'Tạo Phiếu Nhập Kho - Admin')

@push('styles')
<style>
    /* ===== Modern Receipt UI (two-column) ===== */
    .receipt-shell {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
        align-items: start;
    }

    @media (min-width: 1100px) {
        .receipt-shell {
            grid-template-columns: 420px 1fr;
        }
    }

    .receipt-left {
        position: sticky;
        top: 88px;
        align-self: start;
    }

    @media (max-width: 1099.98px) {
        .receipt-left {
            position: static;
        }
    }

    .receipt-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-card);
        overflow: hidden;
    }

    .receipt-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        background: linear-gradient(180deg, rgba(13,148,136,0.06), rgba(13,148,136,0));
    }

    .receipt-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .receipt-card-title i {
        color: var(--primary-color);
    }

    .receipt-card-body {
        padding: 18px 20px;
    }

    .receipt-form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    @media (min-width: 900px) {
        .receipt-form-grid {
            grid-template-columns: 1fr 1fr 1fr;
        }
    }

    .span-2 {
        grid-column: 1 / -1;
    }

    .receipt-top-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
    }

    .receipt-top-actions .btn {
        height: 40px;
    }

    .books-table {
        width: 100%;
        border-collapse: collapse;
    }

    .books-table thead th {
        background: var(--background-dark);
        border-bottom: 1px solid var(--border-color);
        padding: 12px 14px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--text-muted);
        text-align: left;
        white-space: nowrap;
    }

    .books-table tbody td {
        border-bottom: 1px solid var(--border-color);
        padding: 12px 14px;
        vertical-align: middle;
        color: var(--text-secondary);
        font-size: 13px;
    }

    .books-table tbody tr:hover {
        background: rgba(13, 148, 136, 0.04);
    }

    .book-cell {
        display: flex;
        gap: 12px;
        align-items: center;
        min-width: 280px;
    }

    .book-cover {
        width: 44px;
        height: 62px;
        border-radius: 10px;
        border: 1px solid rgba(226,232,240,0.9);
        object-fit: cover;
        background: #f1f5f9;
        flex: 0 0 auto;
    }

    .book-title {
        margin: 0;
        font-weight: 700;
        color: var(--text-primary);
        font-size: 13px;
        line-height: 1.25;
    }

    .book-sub {
        margin: 2px 0 0;
        color: var(--text-muted);
        font-size: 12px;
    }

    .code-pill {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 999px;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        background: #fff;
        display: inline-block;
        white-space: nowrap;
    }

    .money {
        font-weight: 700;
        color: var(--text-primary);
        white-space: nowrap;
    }

    .empty-state {
        text-align: center;
        padding: 44px 18px;
        color: var(--text-muted);
        border: 2px dashed rgba(226,232,240,0.9);
        border-radius: var(--radius-xl);
        background: rgba(248,250,252,0.7);
        margin: 14px 18px 18px;
    }

    .empty-state i {
        font-size: 42px;
        opacity: 0.45;
        margin-bottom: 12px;
    }

    /* Sticky bottom summary bar */
    .receipt-bottom-bar {
        grid-column: 1 / -1;
        position: sticky;
        bottom: 0;
        z-index: 20;
        background: rgba(255,255,255,0.92);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.12);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        padding: 12px 14px;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .receipt-metrics {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .metric {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 999px;
        border: 1px solid var(--border-color);
        background: #fff;
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .metric i {
        color: var(--primary-color);
    }

    .metric strong {
        color: var(--text-primary);
        font-weight: 800;
    }

    .receipt-bottom-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        margin-left: auto;
    }

    /* Modal cleanup */
    #bookSelectionModal .modal-content {
        border-radius: 16px;
        overflow: hidden;
    }

    #bookSelectionModal .modal-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: #fff;
        border-bottom: none;
    }

    #bookSelectionModal .modal-title {
        font-weight: 700;
    }

    .modal-toolbar {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        background: rgba(248,250,252,0.92);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .modal-toolbar-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }

    @media (min-width: 900px) {
        .modal-toolbar-grid {
            grid-template-columns: 1.6fr 1fr 1fr auto;
            align-items: center;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-file-invoice"></i>
            Tạo phiếu nhập kho
        </h1>
        <p class="page-subtitle">Điền thông tin phiếu và thêm sách vào danh sách bên dưới</p>
    </div>
</div>

    <form action="{{ route('admin.inventory.receipts.store') }}" method="POST" id="receiptForm">
        @csrf
        
    <div class="receipt-shell">
        <!-- Left: Receipt info -->
        <div class="receipt-left">
            <div class="receipt-card">
            <div class="receipt-card-header">
                <h3 class="receipt-card-title">
                    <i class="fas fa-receipt"></i>
                    Thông tin phiếu
                </h3>
                <div class="receipt-top-actions">
                    <span class="badge badge-info">{{ $receiptNumber }}</span>
                    <a href="{{ route('admin.inventory.receipts') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại
                    </a>
                </div>
            </div>
            <div class="receipt-card-body">
                <div class="receipt-form-grid">
                    <div>
                        <label class="form-label">Ngày nhập <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" class="form-control" value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                </div>
                    <div>
                        <label class="form-label">Người nhập</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly style="background:#f8fafc;">
            </div>
                    <div>
                        <label class="form-label">Nhà cung cấp</label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier') }}" placeholder="Nhập tên nhà cung cấp">
                </div>
                    <div class="span-2">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Ghi chú cho phiếu nhập...">{{ old('notes') }}</textarea>
            </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Books -->
        <div class="receipt-card">
            <div class="receipt-card-header">
                <h3 class="receipt-card-title">
                    <i class="fas fa-boxes"></i>
                    Danh sách sách nhập
                </h3>
                <div class="receipt-top-actions">
                    <button type="button" class="btn btn-primary btn-sm" onclick="openBookSelectionModal()">
                        <i class="fas fa-plus"></i>
                        Thêm sách
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllBooks()">
                        <i class="fas fa-broom"></i>
                        Xoá tất cả
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="books-table" id="booksTable">
                    <thead>
                        <tr>
                            <th style="width: 54px;">#</th>
                            <th style="min-width: 320px;">Sách</th>
                            <th style="width: 110px;">Mã</th>
                            <th style="width: 120px;">Số lượng</th>
                            <th style="min-width: 220px;">Vị trí</th>
                            <th style="width: 140px;">Lưu trữ</th>
                            <th style="width: 140px; text-align:right;">Giá nhập</th>
                            <th style="width: 140px; text-align:right;">Thành tiền</th>
                            <th style="width: 80px; text-align:center;">Xoá</th>
                        </tr>
                    </thead>
                    <tbody id="booksTableBody"></tbody>
                </table>
            </div>

            <div id="emptyBooksMessage" class="empty-state">
                <i class="fas fa-book-open"></i>
                <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">Chưa có sách nào</div>
                <div>Nhấn <strong>Thêm sách</strong> để chọn sách và tạo phiếu nhập.</div>
            </div>
        </div>

        <!-- Sticky bottom bar -->
        <div class="receipt-bottom-bar">
            <div class="receipt-metrics">
                <span class="metric"><i class="fas fa-list"></i> Đầu sách: <strong id="summaryTitles">0</strong></span>
                <span class="metric"><i class="fas fa-book"></i> Số lượng: <strong id="summaryQty">0</strong></span>
                <span class="metric"><i class="fas fa-coins"></i> Tổng tiền: <strong id="summaryTotal">0 ₫</strong></span>
            </div>
            <div class="receipt-bottom-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Tạo phiếu nhập
                </button>
            </div>
        </div>

    </div>
</form>

<!-- Modal chọn sách -->
<div class="modal fade" id="bookSelectionModal" tabindex="-1" role="dialog" aria-labelledby="bookSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 92%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookSelectionModalLabel">
                    <i class="fas fa-book"></i>
                    Chọn sách
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div class="modal-toolbar">
                    <div class="modal-toolbar-grid">
                        <input type="text" id="searchBookKeyword" class="form-control" placeholder="Tìm theo tên, tác giả hoặc mã BK..." onkeyup="searchBooks()">
                            <select id="searchBookCategory" class="form-control" onchange="searchBooks()">
                            <option value="">Tất cả thể loại</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->ten_the_loai }}</option>
                                @endforeach
                            </select>
                            <select id="searchBookPublisher" class="form-control" onchange="searchBooks()">
                            <option value="">Tất cả NXB</option>
                                @foreach($publishers as $publisher)
                                    <option value="{{ $publisher->id }}">{{ $publisher->ten_nha_xuat_ban }}</option>
                                @endforeach
                            </select>
                        <div style="display:flex; gap:10px; justify-content:flex-end;">
                            <button type="button" class="btn btn-primary" onclick="searchBooks()">
                                <i class="fas fa-search"></i>
                                Tìm
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetBookSearch()">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="booksListContainer" style="padding: 16px; max-height: 70vh; overflow-y: auto;">
                    <div style="text-align: center; padding: 34px; color: var(--text-muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size: 26px;"></i>
                        <div style="margin-top: 10px;">Đang tải danh sách sách...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="addSelectedBooks()">
                    <i class="fas fa-check"></i>
                    Thêm sách đã chọn
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedBooks = [];
let addedBookIds = new Set();
let bookCounter = 0;

function formatMoney(v) {
    const n = Number(v) || 0;
    return new Intl.NumberFormat('vi-VN').format(n) + ' ₫';
}

function showToastError(messageHtml) {
    if (typeof window.showToast === 'function') {
        window.showToast('Có lỗi', messageHtml, 'error', { duration: 5200 });
        return;
    }
    alert(messageHtml.replace(/<[^>]*>/g, ''));
}

function resetBookSearch() {
    const kw = document.getElementById('searchBookKeyword');
    const cat = document.getElementById('searchBookCategory');
    const pub = document.getElementById('searchBookPublisher');
    if (kw) kw.value = '';
    if (cat) cat.value = '';
    if (pub) pub.value = '';
    loadBooksFromServer('', '', '');
}

function openBookSelectionModal() {
    const modal = document.getElementById('bookSelectionModal');
    if (!modal) return;

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else if (typeof $ !== 'undefined') {
            $('#bookSelectionModal').modal('show');
        } else {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }

        loadBooks();
}

function loadBooks(keyword = '', categoryId = '', publisherId = '') {
    loadBooksFromServer(keyword, categoryId, publisherId);
}

function loadBooksFromServer(keyword = '', categoryId = '', publisherId = '') {
    const container = document.getElementById('booksListContainer');
    const books = @json($allBooks ?? $books);
    
    let filteredBooks = books;
    
    if (keyword) {
        const lowerKeyword = keyword.toLowerCase();
        filteredBooks = filteredBooks.filter(book => {
            const ten = (book.ten_sach || '').toLowerCase();
            const tacGia = (book.tac_gia || '').toLowerCase();
            const maSach = ('bk' + String(book.id).padStart(6, '0')).toLowerCase();
            return ten.includes(lowerKeyword) || tacGia.includes(lowerKeyword) || maSach.includes(lowerKeyword);
        });
    }
    
    if (categoryId) {
        filteredBooks = filteredBooks.filter(book => book.category_id == categoryId);
    }
    
    if (publisherId) {
        filteredBooks = filteredBooks.filter(book => book.nha_xuat_ban_id == publisherId);
    }
    
    if (filteredBooks.length > 0) {
        renderBooksList(filteredBooks);
    } else {
        container.innerHTML = '<div style="text-align:center; padding: 28px; color: var(--text-muted);"><i class="fas fa-book-open"></i><div style="margin-top:10px;">Không tìm thấy sách nào</div></div>';
    }
}

function renderBooksList(books) {
    const container = document.getElementById('booksListContainer');
    
    const locationsInStock = @json($locationsInStock);
    const defaultLocation = locationsInStock.length > 0 ? locationsInStock[0].location : 'Kệ 1, Tầng 1';
    
    let html = `
        <div class="table-responsive">
            <table class="table" style="margin:0;">
                <thead>
                    <tr>
                        <th style="width: 44px;">
                            <input type="checkbox" id="selectAllBooks" onchange="toggleSelectAll()">
                        </th>
                        <th>Sách</th>
                        <th style="width: 170px;">Vị trí</th>
                        <th style="width: 110px; text-align:center;">Tồn</th>
                        <th style="width: 120px; text-align:center;">SL nhập</th>
                        <th style="width: 140px; text-align:right;">Giá</th>
                        <th style="width: 150px; text-align:right;">Thành tiền</th>
                        <th style="width: 70px; text-align:center;">+</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    books.forEach(book => {
        const selectedBook = selectedBooks.find(b => b.id === book.id);
        const isSelected = !!selectedBook;
        const isAdded = addedBookIds.has(book.id);

        const soLuongConLai = book.so_luong_con_lai || 0;
        const maSach = 'BK' + String(book.id).padStart(6, '0');
        const donGia = book.gia || 0;

        const selectedLocation = selectedBook ? selectedBook.location : '';
        const selectedQuantity = selectedBook ? selectedBook.quantity : 1;

        const thanhTien = donGia * selectedQuantity;
        const imageUrl = book.image_url || book.hinh_anh_url || (book.hinh_anh ? ('{{ asset('storage') }}/' + String(book.hinh_anh).replace(/\\\\/g,'/').replace(/^\/+/,'')) : '') || '';
        
        html += `
            <tr data-book-id="${book.id}" style="${isSelected ? 'background: rgba(16,185,129,0.08);' : isAdded ? 'background: rgba(245,158,11,0.12); opacity: 0.65;' : ''}">
                <td>
                    <input type="checkbox" 
                           class="book-checkbox" 
                           value="${book.id}"
                           ${isSelected ? 'checked' : ''} 
                           ${isAdded ? 'disabled' : ''}
                           onchange="toggleBookSelection(${book.id})">
                </td>
                <td>
                    <div class="book-cell">
                        ${imageUrl ? `<img class="book-cover" src="${imageUrl}" alt="${book.ten_sach || ''}">` : `<div class="book-cover"></div>`}
                        <div>
                            <p class="book-title">${book.ten_sach || 'Chưa có tên'}</p>
                            <p class="book-sub">${book.tac_gia || 'Chưa có tác giả'} • <span class="code-pill">${maSach}</span></p>
                        </div>
                    </div>
                </td>
                <td>
                    <select class="form-control form-control-sm book-location" ${isAdded ? 'disabled' : ''} onchange="updateBookLocation(${book.id}, this.value)">
                        <option value="">Chọn vị trí</option>
                        ${locationsInStock.map(l => `<option value="${l.location}" ${(selectedLocation || defaultLocation) === l.location ? 'selected' : ''}>${l.location}</option>`).join('')}
                    </select>
                </td>
                <td style="text-align:center;">
                    <span class="badge ${soLuongConLai > 0 ? 'badge-success' : 'badge-secondary'}">${soLuongConLai}</span>
                </td>
                <td style="text-align:center;">
                    <input type="number" class="form-control form-control-sm book-quantity" value="${selectedQuantity}" min="1" ${isAdded ? 'disabled' : ''} style="width: 90px; margin: 0 auto;" onchange="updateBookQuantity(${book.id}, this.value)">
                </td>
                <td style="text-align:right;">${formatMoney(donGia)}</td>
                <td style="text-align:right;" id="thanhTien-${book.id}">${formatMoney(thanhTien)}</td>
                <td style="text-align:center;">
                    ${isAdded ? '<span class="badge badge-warning">Đã thêm</span>' : `<button type="button" class="btn btn-sm btn-success" onclick="addSingleBook(${book.id})"><i class="fas fa-plus"></i></button>`}
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

function toggleBookSelection(bookId) {
    const allBooks = @json($allBooks ?? $books);
    const book = allBooks.find(b => b.id === bookId);
    if (!book) return;
    
    if (addedBookIds.has(bookId)) {
        showToastError('Sách này đã được thêm vào phiếu. Hãy xoá khỏi bảng trước khi thêm lại.');
        const checkbox = document.querySelector(`.book-checkbox[value="${bookId}"]`);
        if (checkbox) checkbox.checked = false;
        return;
    }
    
    const row = document.querySelector(`#bookSelectionModal tr[data-book-id="${bookId}"]`);
    const checkbox = row ? row.querySelector('.book-checkbox') : null;
    const locationSelect = row ? row.querySelector('.book-location') : null;
    const quantityInput = row ? row.querySelector('.book-quantity') : null;
    
    const index = selectedBooks.findIndex(b => b.id === bookId);
    if (index > -1) {
        selectedBooks.splice(index, 1);
        if (row) {
            row.style.background = '';
            if (checkbox) checkbox.checked = false;
        }
    } else {
        const location = locationSelect ? locationSelect.value : '';
        const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
        
        selectedBooks.push({
            id: book.id,
            ten_sach: book.ten_sach,
            tac_gia: book.tac_gia,
            gia: book.gia || 0,
            location: location,
            quantity: quantity
        });

        if (row) {
            row.style.background = 'rgba(16,185,129,0.08)';
            if (checkbox) checkbox.checked = true;
        }
    }
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllBooks');
    const checkboxes = document.querySelectorAll('.book-checkbox:not(:disabled)');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        if (selectAllCheckbox.checked) {
            toggleBookSelection(parseInt(checkbox.value));
        } else {
            const index = selectedBooks.findIndex(b => b.id === parseInt(checkbox.value));
            if (index > -1) {
                selectedBooks.splice(index, 1);
                const row = checkbox.closest('tr');
                if (row) row.style.background = '';
            }
        }
    });
}

function updateBookLocation(bookId, location) {
    const index = selectedBooks.findIndex(b => b.id === bookId);
    if (index > -1) selectedBooks[index].location = location;
}

function updateBookQuantity(bookId, quantity) {
    const index = selectedBooks.findIndex(b => b.id === bookId);
    if (index > -1) {
        const q = parseInt(quantity) || 1;
        selectedBooks[index].quantity = q;
    }

    const allBooks = @json($allBooks ?? $books);
    const book = allBooks.find(b => b.id === bookId);
    if (book) {
        const donGia = book.gia || 0;
        const thanhTien = donGia * (parseInt(quantity) || 1);
        const cell = document.getElementById(`thanhTien-${bookId}`);
        if (cell) cell.textContent = formatMoney(thanhTien);
    }
}

function addSingleBook(bookId) {
    const allBooks = @json($allBooks ?? $books);
    const book = allBooks.find(b => b.id === bookId);
    if (!book) return;
    
    const row = document.querySelector(`#bookSelectionModal tr[data-book-id="${bookId}"]`);
    const locationSelect = row ? row.querySelector('.book-location') : null;
    const quantityInput = row ? row.querySelector('.book-quantity') : null;
    
    const location = locationSelect ? locationSelect.value : '';
    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
    
    if (!location) {
        showToastError('Vui lòng chọn vị trí trước khi thêm sách.');
        return;
    }
    
    if (!selectedBooks.some(b => b.id === bookId)) {
        selectedBooks.push({ id: book.id, ten_sach: book.ten_sach, tac_gia: book.tac_gia, gia: book.gia || 0, location, quantity });
    }

    addBookToTable({ id: book.id, ten_sach: book.ten_sach, tac_gia: book.tac_gia, gia: book.gia || 0, location, quantity });
}

function addSelectedBooks() {
    if (selectedBooks.length === 0) {
        showToastError('Vui lòng chọn ít nhất một cuốn sách.');
        return;
    }
    
    const booksWithoutLocation = selectedBooks.filter(book => !book.location);
    if (booksWithoutLocation.length > 0) {
        showToastError('Vui lòng chọn vị trí cho tất cả sách đã chọn.');
        return;
    }
    
    selectedBooks.forEach(book => {
        if (!addedBookIds.has(book.id)) addBookToTable(book);
    });
    
    selectedBooks = [];
    
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalElement = document.getElementById('bookSelectionModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) modal.hide();
    } else if (typeof $ !== 'undefined') {
        $('#bookSelectionModal').modal('hide');
    } else {
        const modal = document.getElementById('bookSelectionModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }
    }

    updateSummary();
}

function addBookToTable(book) {
    if (addedBookIds.has(book.id)) return;
    
    bookCounter++;
    addedBookIds.add(book.id);
    
    const tbody = document.getElementById('booksTableBody');
    const emptyMessage = document.getElementById('emptyBooksMessage');
    if (emptyMessage) emptyMessage.style.display = 'none';
    
    const locationsInStock = @json($locationsInStock);
    const defaultLocation = book.location || (locationsInStock.length > 0 ? locationsInStock[0].location : 'Kệ 1, Tầng 1');
    const quantity = book.quantity || 1;
    const maSach = 'BK' + String(book.id).padStart(6, '0');
    
    const tr = document.createElement('tr');
    tr.setAttribute('data-book-id', book.id);

    tr.innerHTML = `
        <td>${bookCounter}</td>
        <td>
            <div class="book-cell">
                <div class="book-cover"></div>
                <div>
                    <p class="book-title">${book.ten_sach || 'Chưa có tên'}</p>
                    <p class="book-sub">${book.tac_gia || 'Chưa có tác giả'}</p>
            <input type="hidden" name="books[${bookCounter}][book_id]" value="${book.id}">
                </div>
            </div>
        </td>
        <td><span class="code-pill">${maSach}</span></td>
        <td>
            <input type="number" name="books[${bookCounter}][quantity]" class="form-control book-row-quantity" value="${quantity}" min="1" required style="width: 100px;" onchange="updateRowThanhTien(${bookCounter}); updateSummary();">
        </td>
        <td>
            <select name="books[${bookCounter}][storage_location]" class="form-control" required onchange="updateSummary()">
                <option value="">Chọn vị trí</option>
                ${locationsInStock.map(l => `<option value="${l.location}" ${defaultLocation === l.location ? 'selected' : ''}>${l.location}</option>`).join('')}
            </select>
        </td>
        <td>
            <select name="books[${bookCounter}][storage_type]" class="form-control" required onchange="updateSummary()">
                <option value="Kho" selected>Kho</option>
                <option value="Trung bay">Trưng bày</option>
            </select>
        </td>
        <td style="text-align:right;">
            <input type="number" name="books[${bookCounter}][unit_price]" class="form-control book-row-price" value="${book.gia || 0}" min="0" step="1000" style="width: 140px; margin-left:auto;" onchange="updateRowThanhTien(${bookCounter}); updateSummary();">
        </td>
        <td style="text-align:right;" class="money">
            <span class="book-row-thanh-tien" id="rowThanhTien-${bookCounter}">0 ₫</span>
        </td>
        <td style="text-align:center;">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeBookFromTable(${book.id})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(tr);
    
    updateRowThanhTien(bookCounter);
    updateSummary();

    const modalRow = document.querySelector(`#bookSelectionModal tr[data-book-id="${book.id}"]`);
    if (modalRow) {
        modalRow.style.background = 'rgba(245,158,11,0.12)';
        modalRow.style.opacity = '0.65';
        const checkbox = modalRow.querySelector('.book-checkbox');
        if (checkbox) checkbox.disabled = true;
        const locationSelect = modalRow.querySelector('.book-location');
        if (locationSelect) locationSelect.disabled = true;
        const quantityInput = modalRow.querySelector('.book-quantity');
        if (quantityInput) quantityInput.disabled = true;
    }
}

function removeBookFromTable(bookId) {
    const row = document.querySelector(`#booksTable tr[data-book-id="${bookId}"]`);
    if (row) {
        row.remove();
        addedBookIds.delete(bookId);
        
        const modalRow = document.querySelector(`#bookSelectionModal tr[data-book-id="${bookId}"]`);
        if (modalRow) {
            modalRow.style.background = '';
            modalRow.style.opacity = '1';
            const checkbox = modalRow.querySelector('.book-checkbox');
            if (checkbox) checkbox.disabled = false;
            const locationSelect = modalRow.querySelector('.book-location');
            if (locationSelect) locationSelect.disabled = false;
            const quantityInput = modalRow.querySelector('.book-quantity');
            if (quantityInput) quantityInput.disabled = false;
        }
        
        const index = selectedBooks.findIndex(b => b.id === bookId);
        if (index > -1) selectedBooks.splice(index, 1);
        
        updateRowNumbers();
        updateSummary();

        const tbody = document.getElementById('booksTableBody');
        if (tbody.children.length === 0) {
            const emptyMessage = document.getElementById('emptyBooksMessage');
            if (emptyMessage) emptyMessage.style.display = 'block';
        }
    }
}

function clearAllBooks() {
    const tbody = document.getElementById('booksTableBody');
    if (tbody) tbody.innerHTML = '';
    addedBookIds = new Set();
    selectedBooks = [];
    bookCounter = 0;

    const emptyMessage = document.getElementById('emptyBooksMessage');
    if (emptyMessage) emptyMessage.style.display = 'block';

    updateSummary();
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('#booksTableBody tr');
    rows.forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
    });
    bookCounter = rows.length;
}

function updateRowThanhTien(index) {
    const quantityInput = document.querySelector(`input[name="books[${index}][quantity]"]`);
    const priceInput = document.querySelector(`input[name="books[${index}][unit_price]"]`);
    const span = document.getElementById(`rowThanhTien-${index}`);
    if (!quantityInput || !priceInput || !span) return;

    const qty = parseInt(quantityInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    span.textContent = formatMoney(qty * price);
}

function updateSummary() {
    const rows = document.querySelectorAll('#booksTableBody tr');

    const titles = rows.length;
    let totalQty = 0;
    let totalMoney = 0;

    rows.forEach((row) => {
        const qtyInput = row.querySelector('input[name*="[quantity]"]');
        const priceInput = row.querySelector('input[name*="[unit_price]"]');
        const qty = parseInt(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        totalQty += qty;
        totalMoney += qty * price;
    });

    const elTitles = document.getElementById('summaryTitles');
    const elQty = document.getElementById('summaryQty');
    const elTotal = document.getElementById('summaryTotal');

    if (elTitles) elTitles.textContent = String(titles);
    if (elQty) elQty.textContent = String(totalQty);
    if (elTotal) elTotal.textContent = formatMoney(totalMoney);
}

function searchBooks() {
    const keyword = document.getElementById('searchBookKeyword').value;
    const categoryId = document.getElementById('searchBookCategory').value;
    const publisherId = document.getElementById('searchBookPublisher').value;
    loadBooksFromServer(keyword, categoryId, publisherId);
}

document.getElementById('receiptForm').addEventListener('submit', function(e) {
    if (addedBookIds.size === 0) {
        e.preventDefault();
        showToastError('Vui lòng thêm ít nhất một cuốn sách vào phiếu nhập.');
        return false;
    }
    
    const rows = document.querySelectorAll('#booksTableBody tr');
    let isValid = true;

    rows.forEach(row => {
        const quantity = row.querySelector('input[name*="[quantity]"]')?.value;
        const location = row.querySelector('select[name*="[storage_location]"]')?.value;
        if (!quantity || quantity < 1) isValid = false;
        if (!location) isValid = false;
    });
    
    if (!isValid) {
        e.preventDefault();
        showToastError('Vui lòng điền đầy đủ thông tin cho tất cả sách (số lượng và vị trí).');
        return false;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    updateSummary();

    const modal = document.getElementById('bookSelectionModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            loadBooksFromServer();
        });
        
        if (typeof $ !== 'undefined') {
            $('#bookSelectionModal').on('show.bs.modal', function() {
                loadBooksFromServer();
            });
        }
    }
});
</script>
@endsection
