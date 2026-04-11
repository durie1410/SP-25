@extends('layouts.admin')

@section('title', 'Chi tiết sách - ' . $book->ten_sach)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.books.index') }}">Quản lý sách</a></li>
            <li class="breadcrumb-item active">{{ $book->ten_sach }}</li>
        </ol>
    </nav>

    <!-- Header với thông tin cơ bản -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-book"></i> Chi tiết sách</h2>
                <div class="btn-group">
                    @can('edit-books')
                    <a href="{{ route('admin.books.edit', $book->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    @endcan
                    <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Ảnh bìa sách -->
                        <div class="col-md-3">
                            <div class="text-center">
                                @if($book->hinh_anh)
                                    @php
                                        $imagePath = ltrim(str_replace('\\', '/', $book->hinh_anh), '/');
                                        $imageUrl = asset('storage/' . $imagePath) . '?t=' . $book->updated_at->timestamp;
                                    @endphp
                                    <img src="{{ $imageUrl }}" 
                                         alt="{{ $book->ten_sach }}" 
                                         class="img-fluid rounded shadow"
                                         style="max-height: 300px; object-fit: cover;"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="bg-light rounded align-items-center justify-content-center" 
                                         style="height: 300px; display: none;">
                                        <i class="fas fa-book fa-3x text-muted"></i>
                                    </div>
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="height: 300px;">
                                        <i class="fas fa-book fa-3x text-muted"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Thông tin sách -->
                        <div class="col-md-6">
                            <h1 class="h3 mb-3">{{ $book->ten_sach }}</h1>
                            
                            <div class="mb-3">
                                <strong>Tác giả:</strong> {{ $book->tac_gia }}
                            </div>
                            
                            <div class="mb-3">
                                <strong>Thể loại:</strong> 
                                <span class="badge bg-primary">{{ $book->category->ten_the_loai }}</span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Năm xuất bản:</strong> {{ $book->nam_xuat_ban }}
                            </div>

  

                            <!-- Mô tả -->
                            @if($book->mo_ta)
                            <div class="mb-3">
                                <strong>Mô tả:</strong>
                                <p class="text-muted">{{ $book->mo_ta }}</p>
                            </div>
                            @endif
                        </div>

                        <!-- Thống kê và hành động -->
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-chart-bar"></i> Thống kê</h5>
                                    
                                    <div class="row text-center mb-3">
                                        <div class="col-6 mb-3">
                                            <div class="border-end">
                                                <div class="h3 text-primary mb-1">{{ $stats['total_copies'] }}</div>
                                                <small class="text-muted">Tổng bản</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="h3 text-success mb-1">{{ $stats['available_copies'] }}</div>
                                            <small class="text-muted">Có sẵn</small>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="border-end">
                                                <div class="h3 text-warning mb-1">{{ $stats['borrowed_copies'] }}</div>
                                                <small class="text-muted">Đang mượn</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="h3 text-info mb-1">{{ $stats['total_borrows'] ?? 0 }}</div>
                                            <small class="text-muted">Lượt mượn</small>
                                        </div>
                                        <div class="col-12 mb-2">
                                            <div class="border-top pt-2">
                                                <div class="h4 text-secondary mb-1">{{ $stats['total_reviews'] }}</div>
                                                <small class="text-muted">Đánh giá</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hành động -->
                                    <div class="mt-3 border-top pt-3">
                                        @if(auth()->check())
                                        <button class="btn btn-outline-danger btn-sm w-100 mb-2" 
                                                onclick="toggleFavorite({{ $book->id }})">
                                            <i class="fas fa-heart {{ $isFavorited ? 'text-danger' : 'text-muted' }}"></i>
                                            {{ $isFavorited ? 'Bỏ yêu thích' : 'Yêu thích' }}
                                        </button>
                                        @endif

                                        @can('create-borrows')
                                        @if($stats['available_copies'] > 0)
                                        <button class="btn btn-success btn-sm w-100 mb-2" 
                                                onclick="showBorrowModal()">
                                            <i class="fas fa-book-open"></i> Cho mượn
                                        </button>
                                        @endif
                                        @endcan

                                        @can('create-reservations')
                                        @if($stats['available_copies'] == 0)
                                        <button class="btn btn-info btn-sm w-100 mb-2" 
                                                onclick="showReservationModal()">
                                            <i class="fas fa-bookmark"></i> Đặt trước
                                        </button>
                                        @endif
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs cho các chức năng chi tiết -->
    <div class="row">
        <div class="col-12">
            <div class="card">
             
                <div class="card-body">
                    <div class="tab-content" id="bookTabsContent">
                        <!-- Tab Quản lý kho -->
                        <div class="tab-pane fade show active" id="inventory" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Danh sách bản copy trong kho</h5>
                               
                            </div>

                            @if($book->inventories->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Mã vạch</th>
                                            <th>Vị trí</th>
                                            <th>Tình trạng</th>
                                            <th>Trạng thái</th>
                                            <th>Giá mua</th>
                                            <th>Ngày mua</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($book->inventories as $inventory)
                                        <tr>
                                            <td>
                                                <code>{{ $inventory->barcode }}</code>
                                            </td>
                                            <td>{{ $inventory->location }}</td>
                                            <td>
                                                @php
                                                    // Map các giá trị cũ về 3 loại mới
                                                    $condition = $inventory->condition;
                                                    if ($condition == 'Tot') {
                                                        $condition = 'Moi'; // Tốt -> Mới
                                                    } elseif ($condition == 'Trung binh') {
                                                        $condition = 'Cu'; // Trung bình -> Cũ
                                                    }
                                                    
                                                    $conditionLabels = [
                                                        'Moi' => 'Mới',
                                                        'Cu' => 'Cũ',
                                                        'Hong' => 'Hỏng'
                                                    ];
                                                    
                                                    $conditionColors = [
                                                        'Moi' => 'success',
                                                        'Cu' => 'secondary',
                                                        'Hong' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $conditionColors[$condition] ?? 'secondary' }}">
                                                    {{ $conditionLabels[$condition] ?? $inventory->condition }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'Co san' => 'success',
                                                        'Dang muon' => 'warning',
                                                        'Mat' => 'danger',
                                                        'Hong' => 'danger',
                                                        'Thanh ly' => 'secondary'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$inventory->status] ?? 'secondary' }}">
                                                    {{ $inventory->status }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($inventory->purchase_price)
                                                    {{ number_format($inventory->purchase_price) }} VNĐ
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($inventory->purchase_date)
                                                    {{ \Carbon\Carbon::parse($inventory->purchase_date)->format('d/m/Y') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.inventory.index') }}?inventory_id={{ $inventory->id }}" 
                                                       class="btn btn-info" 
                                                       title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @can('edit-books')
                                                    <a href="{{ route('admin.inventory.edit', $inventory->id) }}" 
                                                       class="btn btn-primary" 
                                                       title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-4">
                                <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Chưa có bản copy nào trong kho</p>
                                @can('create-books')
                                <button class="btn btn-primary" onclick="showAddInventoryModal()">
                                    <i class="fas fa-plus"></i> Thêm bản copy đầu tiên
                                </button>
                                @endcan
                            </div>
                            @endif
                        </div>

                        <!-- Tab Đánh giá -->
                    
</div>

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input .star-label {
    font-size: 1.5rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-input input[type="radio"]:checked ~ .star-label,
.rating-input .star-label:hover,
.rating-input .star-label:hover ~ .star-label {
    color: #ffc107;
}

.card-img-top {
    transition: transform 0.3s;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}
</style>

<script>
// Toggle favorite
function toggleFavorite(bookId) {
    fetch(`/api/favorites/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ book_id: bookId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Có lỗi xảy ra: ' + data.message);
        }
    });
}

// Show borrow modal
function showBorrowModal() {
    loadReaders('borrowForm select[name="reader_id"]');
    new bootstrap.Modal(document.getElementById('borrowModal')).show();
}

// Show reservation modal
function showReservationModal() {
    loadReaders('reservationForm select[name="reader_id"]');
    new bootstrap.Modal(document.getElementById('reservationModal')).show();
}

// Show add inventory modal
function showAddInventoryModal() {
    new bootstrap.Modal(document.getElementById('addInventoryModal')).show();
}

// Load readers for dropdown
function loadReaders(selector) {
    fetch('/api/readers')
    .then(response => response.json())
    .then(data => {
        const select = document.querySelector(selector);
        select.innerHTML = '<option value="">-- Chọn độc giả --</option>';
        data.data.forEach(reader => {
            const option = document.createElement('option');
            option.value = reader.id;
            option.textContent = `${reader.ho_ten} (${reader.so_the_doc_gia})`;
            select.appendChild(option);
        });
    });
}

// Submit borrow
function submitBorrow() {
    const form = document.getElementById('borrowForm');
    const formData = new FormData(form);
    
    fetch('/admin/borrows', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('borrowModal')).hide();
            location.reload();
        } else {
            alert('Có lỗi xảy ra: ' + data.message);
        }
    });
}

// Submit reservation
function submitReservation() {
    const form = document.getElementById('reservationForm');
    const formData = new FormData(form);
    
    fetch('/admin/reservations', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('reservationModal')).hide();
            location.reload();
        } else {
            alert('Có lỗi xảy ra: ' + data.message);
        }
    });
}

// Submit add inventory
function submitAddInventory() {
    const form = document.getElementById('addInventoryForm');
    const formData = new FormData(form);
    
    fetch('/admin/inventory', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addInventoryModal')).hide();
            location.reload();
        } else {
            alert('Có lỗi xảy ra: ' + data.message);
        }
    });
}

// Submit review
document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/api/reviews', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Có lỗi xảy ra: ' + data.message);
        }
    });
});
</script>
@endsection


