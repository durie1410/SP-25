@extends('layouts.admin')

@section('title', 'Cho Mượn Sách Mới - Admin')

@section('content')
<div class="admin-table">
    <h3><i class="fas fa-plus"></i> Cho mượn sách mới</h3>

    <form action="{{ route('admin.borrows.storeItem', $borrow->id) }}" method="POST" id="borrowForm">
        @csrf

        <div class="row">
            <div class="col-md-6">

                {{-- Tìm sách --}}
                <div class="mb-3">
                    <label class="form-label mt-2">Sách <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" id="bookSearch" class="form-control" placeholder="Tìm kiếm sách..." autocomplete="off">
                        <input type="hidden" name="book_id" id="bookId" required>
                        <div id="bookDropdown" class="dropdown-menu w-100" style="display:none; max-height:200px; overflow-y:auto;"></div>
                    </div>

                    <div id="selectedBook" class="mt-2" style="display:none;">
                        <div class="alert alert-info">

                            <div class="book-details mb-3">
                                <div id="bookInfo" class="mb-2"></div>
                                <p> <span id="bookPrice"></span></p>
                            </div>

                                                        <div class="payment-info">
                                <div class="mb-3">
                                    <label class="form-label">Loại mượn</label>
                                    <select name="borrow_type" id="borrowType" class="form-control">
                                        <option value="take_home" selected>Mượn về</option>
                                        <option value="onsite">Đọc tại chỗ</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label for="tienThueInput" class="form-label">Tiền thuê (₫)</label>
                                    <input type="text" name="tien_thue" id="tienThueInput" class="form-control" value="0" readonly>
                                </div>

                                <div class="mb-2">
                                    <label for="trangThaiCoc" class="form-label">Trạng thái thanh toán</label>
                                    <select name="trang_thai_coc" class="form-control">
                                        <option value="cho_xu_ly">Chờ xử lý</option>
                                        <option value="da_thu">Đã thu</option>
                                        <option value="da_hoan">Đã hoàn</option>
                                    </select>
                                </div>
                            </div>

                            <div id="inventoryList" class="mt-3">
                                <p><strong>Chọn vị trí:</strong></p>
                                <div id="inventoryOptions"></div>

                                <div class="mt-2">
                                    <p>Tổng số bản thể đã chọn: <span id="selectedCount">0</span></p>
                                </div>
                            </div>

                            <div class="mt-3 border-top pt-2">
                                <p class="fw-bold">
                                    Tổng tiền: <span id="tongTien" class="text-success">0₫</span>
                                </p>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="clearBook()">Chọn sách khác</button>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Ngày mượn --}}
        <div class="row">
  <div class="mb-3">
    <label class="form-label">Ngày mượn <span class="text-danger">*</span></label>
    <input type="date" name="ngay_muon" id="ngayMuonInput" class="form-control" required>
    <div id="ngayMuonError" class="text-danger mt-1" style="display:none;">Ngày mượn phải lớn hơn hôm nay</div>
</div>

        </div>

        {{-- Hạn trả --}}
        <div class="row">
            <div class="col-md-6">

                <div class="mb-3">
                    <label class="form-label">Hạn trả <span class="text-danger">*</span></label>
                    <input type="date" name="ngay_hen_tra" id="ngayHenTraInput" value="{{ now()->addDays(14)->toDateString() }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="trang_thai" class="form-control">
                        <option value="cho duyet">chờ duyệt</option>
                        <option value="chua nhan">chưa nhận</option>
                        <option value="Dang muon">Đang mượn</option>
                    </select>
                </div>

            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="ghi_chu" class="form-control" rows="6" placeholder="Ghi chú thêm..."></textarea>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Cho mượn
            </button>

            <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

    </form>
</div>
@endsection

@push('styles')
<style>
#inventoryOptions { max-height:150px; overflow-y:auto; border:1px solid #ddd; padding:10px; border-radius:5px; background:#f8f9fa; }
#inventoryOptions .form-check { margin-bottom:5px; }
.book-details { border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:15px; }
.payment-info { margin-bottom:15px; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const ngayMuonInput = document.getElementById('ngayMuonInput');
    const ngayMuonError = document.getElementById('ngayMuonError');

    // Tính ngày hôm nay
    const today = new Date();
    today.setDate(today.getDate()); // hôm nay
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;

    // Thêm min là ngày mai để chọn bằng datepicker
    const minDate = new Date();
    minDate.setDate(minDate.getDate() + 1);
    const yyyyMin = minDate.getFullYear();
    const mmMin = String(minDate.getMonth() + 1).padStart(2, '0');
    const ddMin = String(minDate.getDate()).padStart(2, '0');
    ngayMuonInput.setAttribute('min', `${yyyyMin}-${mmMin}-${ddMin}`);

    // Kiểm tra khi submit form
    const form = document.getElementById('borrowForm');
    form.addEventListener('submit', function(e) {
        const selectedDate = new Date(ngayMuonInput.value);
        const todayZero = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        if (selectedDate <= todayZero) {
            e.preventDefault(); // ngăn submit
            ngayMuonError.style.display = 'block';
            ngayMuonInput.focus();
        } else {
            ngayMuonError.style.display = 'none';
        }
    });
});

let selectedBook = null;
let bookTimeout;
let inventories = [];

const loaiSachMap = { 'binh_thuong': 'Bình thường', 'tham_khao': 'Tham khảo', 'quy': 'Quý' };
const conditionMap = { 'Moi': 'Mới', 'Tot': 'Tốt', 'Trung binh':'Trung bình', 'Cu':'Cũ', 'Hong':'Hỏng' };
const statusMap    = { 'Co san':'Có sẵn','Dang muon':'Đang mượn','Mat':'Mất','Hong':'Hỏng','Thanh ly':'Thanh lý' };
const hasCard = Boolean({{ $borrow->reader_id ? 1 : 0 }});
const borrowTypeSelect = document.getElementById('borrowType');
if (borrowTypeSelect) {
    borrowTypeSelect.addEventListener('change', updateMoneyFields);
}

// -------------------- TÌM SÁCH --------------------
document.getElementById('bookSearch').addEventListener('input', function(){
    const q = this.value.trim();
    clearTimeout(bookTimeout);
    bookTimeout = setTimeout(() => {
        q.length >= 2 ? searchBooks(q) : hideBookDropdown();
    }, 300);
});

function searchBooks(query){
    fetch(`/admin/autocomplete/books?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => showBookDropdown(data));
}

function showBookDropdown(books){
    const dd = document.getElementById('bookDropdown');
    dd.innerHTML = '';
    if (books.length === 0){
        dd.innerHTML = '<div class="dropdown-item text-muted">Không tìm thấy sách</div>';
    } else {
        books.forEach(book => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.style.cursor = 'pointer';
            item.innerHTML = `<div class="fw-bold">${book.ten_sach}</div>
                              <small class="text-muted">Tác giả: ${book.tac_gia}</small>`;
            item.onclick = () => selectBook(book);
            dd.appendChild(item);
        });
    }
    dd.style.display = 'block';
}

// -------------------- CHỌN SÁCH --------------------
function selectBook(book){
    selectedBook = book;
    document.getElementById('bookId').value = book.id;

    document.getElementById('bookInfo').innerHTML = `
        <div class="fw-bold">${book.ten_sach}</div>
        <small class="text-muted">Tác giả: ${book.tac_gia}</small><br>
        <small>Xuất bản: ${book.nam_xuat_ban}</small><br>
        <small>Loại sách: ${loaiSachMap[book.loai_sach]}</small><br>
        <small>Số lượng: ${book.so_luong}</small><br>
<small>giá: ${Number(book.gia).toLocaleString('vi-VN')}₫</small>
    `;

    document.getElementById('selectedBook').style.display = 'block';
    document.getElementById('bookSearch').value = '';
    hideBookDropdown();
    loadInventories(book.id);
}

// -------------------- LOAD INVENTORY --------------------
function loadInventories(bookId){
    fetch(`/admin/books/${bookId}/inventories`)
        .then(res => res.json())
        .then(data => {
            inventories = data;
            const list = document.getElementById('inventoryOptions');
            list.innerHTML = '';
            if (data.length === 0){
                list.innerHTML = '<p class="text-muted">Không có bản thể</p>';
                return;
            }

            data.forEach(inv => {
                const div = document.createElement('div');
                div.className = 'form-check';

                const cannotBorrow = inv.status === 'Hong' ||inv.condition === 'Hong' || inv.status === 'Dang muon' || inv.status === 'Mat' || selectedBook.loai_sach === 'tham_khao';

                div.innerHTML = `
                    <input class="form-check-input" type="checkbox" name="inventory_ids[]" id="inv${inv.id}" value="${inv.id}" ${cannotBorrow ? 'disabled' : ''}>
                    <label class="form-check-label ${cannotBorrow ? 'text-muted' : ''}" for="inv${inv.id}">
                        ${inv.id}- Vị trí: ${inv.location} — ${conditionMap[inv.condition]} — ${statusMap[inv.status]}
                        ${cannotBorrow ? '(Không thể mượn)' : ''}
                    </label>
                `;
                
                if (!cannotBorrow) {
                    div.querySelector('input').addEventListener('change', updateMoneyFields);
                }

                list.appendChild(div);
            });

            updateMoneyFields();
        });
}

// -------------------- TÍNH TIỀN --------------------
function updateMoneyFields(){
    if(!selectedBook) return;

    // Nếu sách tham khảo
    if(selectedBook.loai_sach === 'tham_khao'){
        document.getElementById('tienThueInput').value = 'Không thể mượn online';
        document.getElementById('tongTien').textContent = 'Không thể mượn online';
        return;
    }

    let selectedInventories = Array.from(document.querySelectorAll('input[name="inventory_ids[]"]:checked'));
    document.getElementById('selectedCount').textContent = selectedInventories.length;

    if(selectedInventories.length === 0){
        setInputMoney('tienThueInput',0);
        updateTotal(0);
        return;
    }

    // Tính số ngày mượn
    const ngayMuonInput = document.getElementById('ngayMuonInput');
    const ngayHenTraInput = document.getElementById('ngayHenTraInput');
    let soNgayMuon = 14; // Mặc định 14 ngày
    
    if(ngayMuonInput && ngayHenTraInput && ngayMuonInput.value && ngayHenTraInput.value){
        const ngayMuon = new Date(ngayMuonInput.value);
        const ngayHenTra = new Date(ngayHenTraInput.value);
        const diffTime = ngayHenTra - ngayMuon;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        soNgayMuon = Math.max(1, diffDays);
    }

    const borrowType = document.getElementById('borrowType').value;
    const dailyRate = 0.01; // 1% giá sách mỗi ngày
    const onsiteHourlyRate = 5000; // 5k/giờ

    let totalThue = 0;

    if (borrowType === 'onsite') {
        // Đọc tại chỗ: thuê theo giờ
        totalThue = selectedInventories.length * onsiteHourlyRate; // Tạm tính 1 giờ
        document.getElementById('ngayHenTraInput').value = document.getElementById('ngayMuonInput').value;
    } else {
        // Mượn về: thuê 1%/ngày
        selectedInventories.forEach(inp => {
            const inv = inventories.find(i => i.id == inp.value);
            let price = Number(selectedBook.gia);
            let thue = 0;

            if(inv.status !== 'Hong' && inv.status !== 'Dang muon' && inv.status !== 'Mat'){
                // Thuê = 1% giá sách * số ngày, làm tròn đến hàng nghìn
                thue = Math.round((price * dailyRate * soNgayMuon) / 1000) * 1000;
            }

            totalThue += thue;
        });
    }

    setInputMoney('tienThueInput', totalThue);
    updateTotal(totalThue);
}

// Thêm event listener để tính lại khi thay đổi ngày mượn/hạn trả
document.addEventListener('DOMContentLoaded', function() {
    const ngayMuonInput = document.getElementById('ngayMuonInput');
    const ngayHenTraInput = document.getElementById('ngayHenTraInput');
    
    if(ngayMuonInput) {
        ngayMuonInput.addEventListener('change', updateMoneyFields);
    }
    if(ngayHenTraInput) {
        ngayHenTraInput.addEventListener('change', updateMoneyFields);
    }
});

// -------------------- HỖ TRỢ --------------------
function formatVND(number){ return Number(number).toLocaleString('vi-VN')+'₫'; }
function setInputMoney(id,number){ const inp=document.getElementById(id); inp.dataset.realValue=number; inp.value=formatVND(number); }
function updateTotal(thue){ document.getElementById('tongTien').textContent = formatVND(thue); }
function clearBook(){
    selectedBook=null;
    inventories=[];
    document.getElementById('bookId').value='';
    document.getElementById('selectedBook').style.display='none';
    setInputMoney('tienThueInput',0);
    updateTotal(0);
}
function hideBookDropdown(){ document.getElementById('bookDropdown').style.display='none'; }
document.addEventListener('click', e=>{ if(!e.target.closest('#bookSearch') && !e.target.closest('#bookDropdown')) hideBookDropdown(); });

document.getElementById('borrowForm').addEventListener('submit', function(){
    ['tienThueInput'].forEach(id=>{
        const inp=document.getElementById(id);
        if(isNaN(Number(inp.dataset.realValue))){
            inp.value = 0;
        } else {
            inp.value = inp.dataset.realValue || 0;
        }
    });
});
</script>
@endpush
