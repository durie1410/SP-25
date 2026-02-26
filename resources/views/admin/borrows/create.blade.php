@extends('layouts.admin')

@section('title', 'Cho Mượn Sách Mới - Admin')

@section('content')
<div class="admin-table">
    <h3><i class="fas fa-plus"></i> phiếu mới</h3>

    <form action="{{ route('admin.borrows.store') }}" method="POST" id="borrowForm">
        @csrf
        <input type="hidden" name="reservation_id" value="{{ request('reservation_id') }}">

        {{-- Thông tin người mượn --}}
       <div class="mb-3">
                    <label class="form-label">Độc giả <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" id="readerSearch" class="form-control" placeholder="Tìm kiếm độc giả..." autocomplete="off">
                        <input type="hidden" name="reader_id" id="readerId" value="{{ $prefillReader->id ?? '' }}" required>
                        <div id="readerDropdown" class="dropdown-menu w-100" style="display:none; max-height:200px; overflow-y:auto;"></div>
                    </div>
                    <div id="selectedReader" class="mt-2" style="display: {{ isset($prefillReader) ? 'block' : 'none' }};">
                        <div class="alert alert-info">
                            <strong>Đã chọn:</strong> <span id="readerName">{{ isset($prefillReader) ? ($prefillReader->ho_ten . ' (' . $prefillReader->so_the_doc_gia . ')') : '' }}</span>
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
        <div class="row mb-3">
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
        {{-- Chọn sách --}}
        <div class="row mb-3">
            {{-- <div class="col-md-6">
                <label class="form-label">Sách</label>
                <select name="book_id" class="form-control">
                    <option value="">-- Chọn sách --</option>
                    @foreach($books as $book)
                        <option value="{{ $book->id }}">{{ $book->ten_sach }} ({{ $book->tac_gia }})</option>
                    @endforeach
                </select>
            </div> --}}

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
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Ngày mượn</label>
                <input type="date" name="ngay_muon" class="form-control" value="{{ request('ngay_muon', now()->toDateString()) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Trạng thái</label>
                <select name="trang_thai" class="form-control">
                    <option value="Cho duyet">Chờ duyệt</option>
                    <option value="Dang muon">Đang mượn</option>
                    <option value="Da tra">Đã trả</option>
                    <option value="Qua han">Quá hạn</option>
                    <option value="Mat sach">Mất sách</option>
                    <option value="Huy">Hủy</option>
                </select>
            </div>
        </div>

        {{-- Ghi chú --}}
        <div class="mb-3">
            <label class="form-label">Ghi chú</label>
            <textarea name="ghi_chu" class="form-control" rows="3"></textarea>
        </div>

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
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Cho mượn</button>
            <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
  let readerTimeout, bookTimeout;

// --- Tìm kiếm độc giả ---
document.getElementById('readerSearch').addEventListener('input', function() {
    const query = this.value.trim();
    clearTimeout(readerTimeout);
    readerTimeout = setTimeout(() => {
        if(query.length >= 2) searchReaders(query);
        else hideReaderDropdown();
    }, 300);
});

function searchReaders(query) {
    // Sử dụng route chính xác đã được định nghĩa trong web.php
    fetch(`{{ route('admin.autocomplete.readers') }}?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => showReaderDropdown(data))
        .catch(err => {
            console.error('Error fetching readers:', err);
            // Fallback nếu route name không hoạt động
            fetch(`/admin/autocomplete/readers?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => showReaderDropdown(data));
        });
}

function showReaderDropdown(readers) {
    const dropdown = document.getElementById('readerDropdown');
    dropdown.innerHTML = '';
    if(readers.length === 0) {
        dropdown.innerHTML = '<div class="dropdown-item text-muted">Không tìm thấy độc giả</div>';
    } else {
        readers.forEach(r => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.style.cursor = 'pointer';
            item.innerHTML = `<div class="fw-bold">${r.ho_ten}</div><small class="text-muted">Mã thẻ: ${r.so_the_doc_gia} | Email: ${r.email}</small>`;
            item.onclick = () => selectReader(r);
            dropdown.appendChild(item);
        });
    }
    dropdown.style.display = 'block';
}

function selectReader(reader) {
    document.getElementById('readerId').value = reader.id;
    document.getElementById('readerName').textContent = `${reader.ho_ten} (${reader.so_the_doc_gia})`;
    
    // Tự động điền thông tin địa chỉ và số điện thoại
    if (reader) {
        document.getElementById('tenNguoiMuon').value = reader.ho_ten || '';
        document.getElementById('soDienThoai').value = reader.so_dien_thoai || '';
        
        // Ghép địa chỉ đầy đủ để hiển thị vào ô duy nhất
        const parts = [reader.so_nha, reader.xa, reader.huyen, reader.tinh_thanh].filter(p => p && p.trim() !== '');
        const fullAddress = parts.length > 0 ? parts.join(', ') : (reader.dia_chi || '');
        
        document.getElementById('diaChi').value = fullAddress;
        
        // Cập nhật các hidden fields để backend vẫn nhận đủ dữ liệu tách biệt
        document.getElementById('tinhThanh').value = reader.tinh_thanh || '';
        document.getElementById('huyen').value = reader.huyen || '';
        document.getElementById('xa').value = reader.xa || '';
        document.getElementById('soNha').value = reader.so_nha || '';
    }

    document.getElementById('readerSearch').value = '';
    document.getElementById('selectedReader').style.display = 'block';
    hideReaderDropdown();
    
    const bookId = document.getElementById('bookId').value;
    if (bookId) {
        const bookPriceText = document.getElementById('bookPrice').textContent.replace(/[^\d]/g, '');
        const bookPrice = parseInt(bookPriceText) || 0;
        // Phí thuê = giá sách theo số ngày
        const deposit = bookPrice;
        document.getElementById('depositInput').value = deposit;
    }
}

function clearReader() {
    document.getElementById('readerId').value = '';
    document.getElementById('selectedReader').style.display = 'none';
    
    // Xóa trắng thông tin địa chỉ và số điện thoại
    document.getElementById('tenNguoiMuon').value = '';
    document.getElementById('soDienThoai').value = '';
    document.getElementById('diaChi').value = '';
    document.getElementById('tinhThanh').value = '';
    document.getElementById('huyen').value = '';
    document.getElementById('xa').value = '';
    document.getElementById('soNha').value = '';
}

function hideReaderDropdown() { document.getElementById('readerDropdown').style.display = 'none'; }

// --- Tìm kiếm sách ---
document.getElementById('bookSearch').addEventListener('input', function() {
    const query = this.value.trim();
    clearTimeout(bookTimeout);
    bookTimeout = setTimeout(() => {
        if(query.length >= 2) searchBooks(query);
        else hideBookDropdown();
    }, 300);
});

function searchBooks(query) {
    fetch(`/admin/autocomplete/books?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => showBookDropdown(data))
        .catch(err => console.error(err));
}

function showBookDropdown(books) {
    const dropdown = document.getElementById('bookDropdown');
    dropdown.innerHTML = '';
    if(books.length === 0) {
        dropdown.innerHTML = '<div class="dropdown-item text-muted">Không tìm thấy sách</div>';
    } else {
        books.forEach(b => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.style.cursor = 'pointer';
            item.innerHTML = `<div class="fw-bold">${b.ten_sach}</div><small class="text-muted">Tác giả: ${b.tac_gia} | Năm: ${b.nam_xuat_ban}</small>`;
            item.onclick = () => selectBook(b);
            dropdown.appendChild(item);
        });
    }
    dropdown.style.display = 'block';
}

function selectBook(book) {
    document.getElementById('bookId').value = book.id;
    document.getElementById('bookName').innerHTML = `
        <div class="fw-bold">${book.ten_sach}</div>
        <small class="text-muted">Tác giả: ${book.tac_gia}</small><br>
        <small>Xuất bản: ${book.nam_xuat_ban}</small>
    `;

    // --- Xử lý giá sách ---
    let price = 0;
    if (book.gia !== undefined && book.gia !== null) {
        price = Number(String(book.gia).replace(/,/g, ''));
        if (isNaN(price)) price = 0;
    }
    document.getElementById('bookPrice').textContent = price.toLocaleString('vi-VN') + '₫';

    // --- Xác định phí thuê ---
    const deposit = price;
    document.getElementById('depositInput').value = deposit;

    // --- Không sử dụng phí ship ---
    document.getElementById('shipInput').value = '';

    document.getElementById('bookSearch').value = '';
    document.getElementById('selectedBook').style.display = 'block';
    hideBookDropdown();
}

function clearBook() {
    document.getElementById('bookId').value = '';
    document.getElementById('selectedBook').style.display = 'none';
    document.getElementById('bookPrice').textContent = '0₫';
    document.getElementById('depositInput').value = '';
    document.getElementById('shipInput').value = '';
}


function clearBook() {
    document.getElementById('bookId').value = '';
    document.getElementById('selectedBook').style.display = 'none';
    document.getElementById('bookPrice').textContent = '0₫';
    document.getElementById('depositPrice').textContent = '0₫';
    document.getElementById('shipPrice').textContent = '0₫';
}

function hideBookDropdown() { document.getElementById('bookDropdown').style.display = 'none'; }

// Click ngoài dropdown đóng
document.addEventListener('click', function(e){
    if(!e.target.closest('#readerSearch') && !e.target.closest('#readerDropdown')) hideReaderDropdown();
    if(!e.target.closest('#bookSearch') && !e.target.closest('#bookDropdown')) hideBookDropdown();
});

// Tự động điền thông tin nếu có prefillReader (trường hợp Fulfill)
@if(isset($prefillReader))
    document.addEventListener('DOMContentLoaded', function() {
        const reader = {
            ho_ten: "{{ $prefillReader->ho_ten }}",
            so_dien_thoai: "{{ $prefillReader->so_dien_thoai }}",
            tinh_thanh: "{{ $prefillReader->tinh_thanh }}",
            huyen: "{{ $prefillReader->huyen }}",
            xa: "{{ $prefillReader->xa }}",
            so_nha: "{{ $prefillReader->so_nha }}",
            dia_chi: "{{ $prefillReader->dia_chi }}"
        };
        
        document.getElementById('tenNguoiMuon').value = reader.ho_ten || '';
        document.getElementById('soDienThoai').value = reader.so_dien_thoai || '';
        
        // Hiển thị fallback từ dia_chi nếu các trường chi tiết rỗng
        document.getElementById('tinhThanh').value = reader.tinh_thanh || (reader.tinh_thanh === '' ? reader.dia_chi : '');
        document.getElementById('huyen').value = reader.huyen || '';
        document.getElementById('xa').value = reader.xa || '';
        document.getElementById('soNha').value = reader.so_nha || '';
    });
@endif
    
</script>
@endpush


