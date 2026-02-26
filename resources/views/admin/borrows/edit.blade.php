@extends('layouts.admin')

@section('title', 'Sửa Phiếu Mượn - Admin')

@section('content')

<style>
/* Modal nền mờ */
.modal-bg {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(5px);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1055;
}
.status-badge {
    display: inline-block;
    padding: 0.25em 0.5em;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 0.25rem;
    color: #fff;
    text-align: center;
}

.status-Cho-duyet { background-color: #6c757d; }    /* xám */
.status-Chua-nhan { background-color: #0d6efd; }    /* xanh dương */
.status-Dang-muon { background-color: #0dcaf0; }    /* xanh nhạt */
.status-Da-tra { background-color: #198754; }       /* xanh lá */
.status-Qua-han { background-color: #ffc107; color: #000; } /* vàng */
.status-Mat-sach { background-color: #dc3545; }     /* đỏ */
.status-Hong { background-color: #fd7e14; }        /* cam */
.status-Khong-xac-dinh { background-color: #6c757d; } /* xám */

/* Hộp modal */
.modal-box {
    background: #fff;
    border-radius: 10px;
    padding: 25px 30px;
    max-width: 480px;
    width: 90%;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    animation: showModal 0.3s ease;
}

@keyframes showModal {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}

.modal-box h5 { margin-top: 0; font-weight: 700; color: #007bff; }
.modal-box ul { margin-top: 10px; padding-left: 18px; }
.modal-box button {
    background: #6c757d; color: #fff; border: none;
    padding: 8px 14px; border-radius: 6px;
    margin-top: 15px; cursor: pointer; transition: 0.2s;
}
.modal-box button:hover { background: #5a6268; }

/* Dropdown autocomplete */
.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    display: none;
    float: left;
    min-width: 100%;
    padding: 0;
    margin: 2px 0 0;
    font-size: 0.875rem;
    color: #212529;
    text-align: left;
    list-style: none;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 0.25rem;
    max-height: 200px;
    overflow-y: auto;
}
.dropdown-item { cursor: pointer; }
</style>

<div class="container py-4">
    <div class="card shadow-sm border-0">
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
            <div class="alert alert-info">
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
                <div class="card mb-3">
                    <div class="card-header">Sách mượn
                        <span class="badge badge-info">Tổng: {{ $borrow->items->count() }} sách</span>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tên sách</th>
                                    <th>Tác giả</th>
                                    <th>vị trí</th>
                                    <th>Tiền thuê</th>
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
                                    <td>{{ number_format($item->tien_thue) }}₫</td>
                                    <td>{{ $item->ngay_hen_tra->format('d/m/Y') }}</td>
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

                {{-- Tổng tiền --}}
                {{-- nếu có thẻ thì tiền thuê=miễn phí --}}
                @if($borrow->reader && $borrow->reader->id)
                    <p class="mb-1"><strong>Tổng tiền thuê:</strong> <span id="totalRent">MIỄN PHÍ</span></p>
                @else
                    <p class="mb-1"><strong>Tổng tiền thuê:</strong> <span id="totalRent">{{ number_format($borrow->items->sum('tien_thue')) }}₫</span></p>
                @endif
                <p class="mb-1"><strong>Tổng thanh toán:</strong> <span class="fw-bold text-success" id="finalAmount">{{ number_format($borrow->tong_tien) }}₫</span></p>
                <input type="hidden" name="tong_tien" id="tongTienInput" value="{{ $borrow->tong_tien }}">

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// --- Tính tổng tiền ---
function calculateTotal() {
    let finalAmount = 0;
    document.querySelectorAll('tbody tr td:nth-child(4)').forEach(td => {
        const raw = (td.innerText || '').replace(/[^\d.-]/g, '');
        finalAmount += parseFloat(raw || 0);
    });

    document.getElementById('finalAmount').innerText = new Intl.NumberFormat().format(finalAmount) + '₫';
    document.getElementById('tongTienInput').value = finalAmount;
}

window.addEventListener('DOMContentLoaded', () => {
    calculateTotal();
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
