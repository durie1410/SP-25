@extends('layouts.admin')

@section('title', 'Sửa Phiếu Mượn - Admin')

@section('content')

<style>
.borrow-edit-page {
    padding-top: 8px;
}

.borrow-edit-card {
    border: 0;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 18px 38px rgba(15, 23, 42, 0.1);
}

.borrow-edit-card .card-header {
    border: 0;
    padding: 16px 20px;
    background: linear-gradient(135deg, #0f766e, #0ea5e9);
}

.borrow-edit-card .card-body {
    padding: 22px;
    background: linear-gradient(180deg, #ffffff, #f8fafc);
}

.borrow-edit-card .form-label {
    font-weight: 700;
    font-size: 13px;
    color: #334155;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.borrow-edit-card .form-control,
.borrow-edit-card .form-select {
    border-radius: 12px;
    border: 1px solid #dbe2ea;
    min-height: 44px;
    box-shadow: 0 1px 1px rgba(15, 23, 42, 0.02);
}

.borrow-edit-card .form-control:focus,
.borrow-edit-card .form-select:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.15);
}

.reader-selected-box {
    border-radius: 12px;
    border: 1px solid #bfe5ff;
    background: #ecf8ff;
    color: #0f4c81;
    padding: 12px 14px;
}

.borrow-books-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
}

.borrow-books-card .card-header {
    background: #f1f5f9;
    color: #0f172a;
    font-weight: 700;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.borrow-books-table {
    margin: 0;
}

.borrow-books-table thead th {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: #f8fafc;
    color: #475569;
    border-bottom: 1px solid #e2e8f0;
}

.borrow-books-table tbody tr:hover {
    background: #f8fbff;
}

.borrow-books-table td.money-col {
    text-align: right;
    white-space: nowrap;
    font-weight: 700;
    color: #0f766e;
}

.borrow-books-table td.date-col {
    white-space: nowrap;
    font-weight: 600;
    color: #334155;
}

.status-badge {
    display: inline-block;
    padding: 0.3em 0.65em;
    font-size: 0.78rem;
    font-weight: 700;
    border-radius: 999px;
    color: #fff;
    text-align: center;
}

.status-Cho-duyet { background-color: #6b7280; }
.status-Chua-nhan { background-color: #2563eb; }
.status-Dang-muon { background-color: #0891b2; }
.status-Da-tra { background-color: #16a34a; }
.status-Qua-han { background-color: #f59e0b; color: #111827; }
.status-Mat-sach { background-color: #dc2626; }
.status-Hong { background-color: #ea580c; }
.status-Khong-xac-dinh { background-color: #6b7280; }

.summary-bar {
    margin-top: 14px;
    margin-bottom: 2px;
    padding: 12px 14px;
    border-radius: 12px;
    background: #ecfeff;
    border: 1px solid #bae6fd;
}

.summary-bar strong {
    color: #0f172a;
}

.summary-bar .text-success {
    color: #0284c7 !important;
}

.action-row .btn {
    border-radius: 10px;
    min-width: 120px;
    font-weight: 600;
}

/* Dropdown autocomplete */
#readerDropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    z-index: 1000;
    display: none;
    min-width: 100%;
    padding: 4px;
    margin: 0;
    font-size: 0.875rem;
    list-style: none;
    background-color: #fff;
    border: 1px solid rgba(148, 163, 184, 0.4);
    border-radius: 10px;
    max-height: 230px;
    overflow-y: auto;
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.14);
}

#readerDropdown .dropdown-item {
    cursor: pointer;
    border-radius: 8px;
    padding: 8px 10px;
}

#readerDropdown .dropdown-item:hover {
    background: #f0f9ff;
}

@media (max-width: 768px) {
    .borrow-edit-card .card-body {
        padding: 16px;
    }

    .action-row {
        flex-direction: column;
    }

    .action-row .btn {
        width: 100%;
    }
}
</style>

<div class="container py-4 borrow-edit-page">
    <div class="card shadow-sm borrow-edit-card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Sửa Phiếu Mượn</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.borrows.update', $borrow->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Độc giả (Autocomplete) -->
                  <div class="col-md-6 mb-3">
    <label class="form-label fw-semibold">Độc giả</label>
    @if($borrow->reader_id && $borrow->reader)
        <div class="position-relative">
    <input type="text" id="readerSearch" class="form-control" placeholder="Tìm kiếm độc giả..." autocomplete="off" 
           value="{{ optional($borrow->reader)->ho_ten }}">
    <input type="hidden" name="reader_id" id="readerId" value="{{ $borrow->reader_id ?? '' }}"
           @if($borrow->reader_id) required @endif>
    <div id="readerDropdown" class="dropdown-menu w-100"></div>
</div>

        <div id="selectedReader" class="mt-2">
            <div class="reader-selected-box">
                <strong>Đã chọn:</strong> <span id="readerName">{{ $borrow->reader->ho_ten }} ({{ $borrow->reader->so_the_doc_gia }})</span>
                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearReader()">Xóa</button>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Bạn chưa có thẻ thành viên.
        </div>
    @endif
</div>

            <div class="col-md-6">
                <label class="form-label">Tên người mượn</label>
                <input type="text" name="ten_nguoi_muon" class="form-control" value="{{ $borrow->ten_nguoi_muon }}">
            </div>
                    <!-- Thủ thư -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Thủ thư</label>
                        <select name="librarian_id" class="form-select">
                            <option value="">-- Chọn thủ thư --</option>
                            @foreach($librarians as $librarian)
                                <option value="{{ $librarian->id }}" {{ $borrow->librarian_id == $librarian->id ? 'selected' : '' }}>
                                    {{ $librarian->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Ngày mượn -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Ngày mượn</label>
                        <input type="date" name="ngay_muon" value="{{ $borrow->ngay_muon->format('Y-m-d') }}" class="form-control" required>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tỉnh/Thành</label>
                        <input type="text" name="tinh_thanh" value="{{ $borrow->tinh_thanh }}" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Huyện</label>
                        <input type="text" name="huyen" value="{{ $borrow->huyen }}" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Xã</label>
                        <input type="text" name="xa" value="{{ $borrow->xa }}" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Số nhà</label>
                        <input type="text" name="so_nha" value="{{ $borrow->so_nha }}" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Số điện thoại</label>
                        <input type="text" name="so_dien_thoai" value="{{ $borrow->so_dien_thoai }}" class="form-control" required>
                    </div>
                </div>

                <!-- Ghi chú -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <textarea name="ghi_chu" class="form-control" rows="3">{{ $borrow->ghi_chu }}</textarea>
                </div>

                {{-- Sách mượn --}}
                <div class="card mb-3 borrow-books-card">
                    <div class="card-header">Sách mượn
                        <span class="badge badge-info">Tổng: {{ $borrow->items->count() }} sách</span>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered borrow-books-table">
                            <thead>
                                <tr>
                                    <th>Tên sách</th>
                                    <th>Tác giả</th>
                                    <th>vị trí</th>
                                    <th>Ngày lấy sách</th>
                                    <th>Ngày hẹn trả</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($borrow->items as $item)
                                <tr>
                                    <td>{{ $item->book->ten_sach }}</td>
                                    <td>{{ $item->book->tac_gia }}</td>
<td>
    <span class="badge badge-info">ID: {{ $item->inventory->id }}</span>
    <span class="badge badge-secondary">Vị trí: {{ $item->inventory->location ?? 'Không có' }}</span>
</td>
                                    <td class="date-col">{{ optional($item->ngay_muon)->format('d/m/Y') ?? optional($borrow->ngay_muon)->format('d/m/Y') ?? 'Chưa có' }}</td>
                                    <td class="date-col">{{ optional($item->ngay_hen_tra)->format('d/m/Y') ?? 'Chưa có' }}</td>
<td>
    @php
        $statusClass = str_replace(' ', '-', $item->trang_thai);
    @endphp
    <span class="status-badge status-{{ $statusClass }}">
        @switch($item->trang_thai)
            @case('Cho duyet') Chưa duyệt @break
            @case('Chua nhan') Chưa nhận @break
            @case('Dang muon') Đang mượn @break
            @case('Da tra') Đã trả @break
            @case('Qua han') Quá hạn @break
            @case('Mat sach') Mất sách @break
            @case('Hong') Hỏng @break
            @default Không xác định
        @endswitch
                    </span>
</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Voucher --}}
                @if($vouchers->count() > 0)

                @endif

                <div class="summary-bar">
                    <p class="mb-0"><strong>Tổng thanh toán:</strong> <span class="fw-bold text-success" id="finalAmount">{{ number_format($borrow->tong_tien) }}₫</span></p>
                </div>
                <input type="hidden" name="tong_tien" id="tongTienInput" value="{{ $borrow->tong_tien }}">

                <div class="d-flex justify-content-end gap-2 mt-4 action-row">
                    <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.addEventListener('DOMContentLoaded', () => {
    const initialTongTien = Number(@json($borrow->tong_tien ?? 0));
    const finalAmountEl = document.getElementById('finalAmount');
    const tongTienInputEl = document.getElementById('tongTienInput');

    if (finalAmountEl) {
        finalAmountEl.innerText = new Intl.NumberFormat('vi-VN').format(initialTongTien) + '₫';
    }

    if (tongTienInputEl) {
        tongTienInputEl.value = initialTongTien;
    }
});

// --- Độc giả Autocomplete ---
const readerSearch = document.getElementById('readerSearch');
const readerDropdown = document.getElementById('readerDropdown');
const readerIdInput = document.getElementById('readerId');
const selectedReader = document.getElementById('selectedReader');
const readerNameSpan = document.getElementById('readerName');

let debounceTimer;

readerSearch.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const query = readerSearch.value.trim();
    if (!query) {
        readerDropdown.style.display = 'none';
        return;
    }
    debounceTimer = setTimeout(async () => {
        const res = await fetch(`/admin/autocomplete/readers?q=${query}`);
        const data = await res.json();
        readerDropdown.innerHTML = data.map(r => `<a href="#" class="dropdown-item" data-id="${r.id}" data-name="${r.ho_ten} (${r.so_the_doc_gia})">${r.ho_ten} (${r.so_the_doc_gia})</a>`).join('');
        readerDropdown.style.display = data.length ? 'block' : 'none';
    }, 300);
});

readerDropdown.addEventListener('click', e => {
    e.preventDefault();
    const target = e.target.closest('.dropdown-item');
    if (!target) return;
    readerIdInput.value = target.dataset.id;
    readerNameSpan.innerText = target.dataset.name;
    selectedReader.style.display = 'block';
    readerDropdown.style.display = 'none';
});

function clearReader() {
    readerIdInput.value = '';
    readerSearch.value = '';
    selectedReader.style.display = 'none';
}
</script>
@endpush

@endsection
