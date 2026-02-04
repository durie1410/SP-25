@extends('layouts.admin')

@section('title', 'Quản Lý Kho - LibNet Admin')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-boxes"></i>
            Quản lý kho
        </h1>
        <p class="page-subtitle">Quản lý và theo dõi tất cả sách trong kho</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <form action="{{ route('admin.inventory.sync-to-homepage') }}" method="POST" style="display: inline;" id="syncForm">
            @csrf
            <button type="submit" class="btn btn-success" id="syncBtn">
                <i class="fas fa-sync-alt"></i>
                Đồng bộ lên trang chủ
            </button>
        </form>
        
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="white-space: pre-line;">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

<!-- Search and Filter -->
<div class="card" style="margin-bottom: 25px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-search"></i>
            Tìm kiếm & Lọc
        </h3>
    </div>
    <form action="{{ route('admin.inventory.index') }}" method="GET" style="padding: 25px; display: flex; gap: 15px; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 250px;">
            <input type="text" 
                   name="book_title" 
                   value="{{ request('book_title') }}" 
                   class="form-control" 
                   placeholder="Tìm theo tên sách...">
        </div>
        <div style="flex: 1; min-width: 200px;">
            <select name="book_id" class="form-select">
                <option value="">-- Tất cả sách --</option>
                @foreach($books as $book)
                    <option value="{{ $book->id }}" {{ request('book_id') == $book->id ? 'selected' : '' }}>
                        {{ $book->ten_sach }}
                    </option>
                @endforeach
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <input type="text" 
                   name="barcode" 
                   value="{{ request('barcode') }}" 
                   class="form-control" 
                   placeholder="Mã vạch...">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <input type="text" 
                   name="location" 
                   value="{{ request('location') }}" 
                   class="form-control" 
                   placeholder="Vị trí...">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <select name="status" class="form-select">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="Co san" {{ request('status') == 'Co san' ? 'selected' : '' }}>Có sẵn</option>
                <option value="Dang muon" {{ request('status') == 'Dang muon' ? 'selected' : '' }}>Đang mượn</option>
                <option value="Mat" {{ request('status') == 'Mat' ? 'selected' : '' }}>Mất</option>
                <option value="Hong" {{ request('status') == 'Hong' ? 'selected' : '' }}>Hỏng</option>
                <option value="Thanh ly" {{ request('status') == 'Thanh ly' ? 'selected' : '' }}>Thanh lý</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <select name="condition" class="form-select">
                <option value="">-- Tất cả tình trạng --</option>
                <option value="Moi" {{ request('condition') == 'Moi' ? 'selected' : '' }}>Mới</option>
                <option value="Cu" {{ request('condition') == 'Cu' ? 'selected' : '' }}>Cũ</option>
                <option value="Hong" {{ request('condition') == 'Hong' ? 'selected' : '' }}>Hỏng</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <select name="storage_type" class="form-select">
                <option value="">-- Tất cả loại --</option>
                <option value="Kho" {{ request('storage_type') == 'Kho' ? 'selected' : '' }}>Kho</option>
                <option value="Trung bay" {{ request('storage_type') == 'Trung bay' ? 'selected' : '' }}>Trưng bày</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter"></i>
            Lọc
        </button>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
            <i class="fas fa-redo"></i>
            Reset
        </a>
    </form>
</div>

<!-- Inventory List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Danh sách sách trong kho
        </h3>
        <span class="badge badge-info">Tổng: {{ $inventories->total() }} cuốn sách ({{ $totalQuantity ?? 0 }} quyển)</span>
    </div>
    
    @if($inventories->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Sách</th>
                        <th>Thông tin sách</th>
                        <th>Số lượng</th>
                        <th>Loại</th>
                        <th>Vị trí</th>
                        <th>Trạng thái</th>
                        <th>Tổng giá trị</th>
                        <th>Người nhập</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventories as $item)
                        @php
                            $book = $item->book;
                            $firstInventory = $item->first_inventory;
                            $allInventories = $item->all_inventories;
                            
                            // Tính toán tổng hợp
                            $inKho = $allInventories->where('storage_type', 'Kho')->count();
                            $onDisplay = $allInventories->where('storage_type', 'Trung bay')->count();
                            $available = $allInventories->where('status', 'Co san')->count();
                            $borrowed = $allInventories->where('status', 'Dang muon')->count();
                            $damaged = $allInventories->filter(function($inv) {
                                return $inv->status == 'Hong' || $inv->condition == 'Hong';
                            })->count();
                            
                            // Tính tổng giá trị
                            $totalValue = $allInventories->sum('purchase_price');
                            
                            // Lấy vị trí (có thể có nhiều vị trí)
                            $locations = $allInventories->pluck('location')->unique()->values();
                        @endphp
                        <tr>
                            <td>
                                <span class="badge badge-info">{{ $book->id ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div style="max-width: 300px;">
                                    <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 5px;">
                                        {{ $book->ten_sach ?? 'N/A' }}
                                    </div>
                                    <div style="font-size: 12px; color: #888;">
                                        <i class="fas fa-user"></i> {{ $book->tac_gia ?? 'N/A' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span class="badge badge-success" style="font-size: 14px; padding: 6px 10px;">
                                        <i class="fas fa-book"></i> Tổng: <strong>{{ $item->total_quantity }}</strong> quyển
                                    </span>
                                    <div style="font-size: 11px; color: #666; margin-top: 4px;">
                                        <div><i class="fas fa-warehouse"></i> Kho: {{ $inKho }}</div>
                                        <div><i class="fas fa-store"></i> Trưng bày: {{ $onDisplay }}</div>
                                        <div><i class="fas fa-check-circle" style="color: #28a745;"></i> Có sẵn: {{ $available }}</div>
                                        <div><i class="fas fa-hand-holding" style="color: #ffc107;"></i> Đang mượn: {{ $borrowed }}</div>
                                        @if($damaged > 0)
                                        <div><i class="fas fa-times-circle" style="color: #dc3545;"></i> Hỏng: {{ $damaged }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    @if($inKho > 0)
                                        <span class="badge badge-info">
                                            <i class="fas fa-warehouse"></i> Kho: {{ $inKho }}
                                        </span>
                                    @endif
                                    @if($onDisplay > 0)
                                        <span class="badge badge-warning">
                                            <i class="fas fa-store"></i> Trưng bày: {{ $onDisplay }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 200px;">
                                    @foreach($locations->take(3) as $location)
                                        <span class="badge badge-secondary" style="display: block; margin-bottom: 3px;">
                                            <i class="fas fa-map-marker-alt"></i> {{ $location }}
                                        </span>
                                    @endforeach
                                    @if($locations->count() > 3)
                                        <small style="color: #888;">+ {{ $locations->count() - 3 }} vị trí khác</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    @if($available > 0)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Có sẵn: {{ $available }}
                                        </span>
                                    @endif
                                    @if($borrowed > 0)
                                        <span class="badge badge-warning">
                                            <i class="fas fa-hand-holding"></i> Đang mượn: {{ $borrowed }}
                                        </span>
                                    @endif
                                    @if($damaged > 0)
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times-circle"></i> Hỏng: {{ $damaged }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($totalValue > 0)
                                    <span class="badge badge-success">
                                        {{ number_format($totalValue, 0, ',', '.') }} đ
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($firstInventory && $firstInventory->creator)
                                    <div style="font-size: 12px;">
                                        <div style="font-weight: 600; color: var(--text-primary);">
                                            <i class="fas fa-user"></i>
                                            {{ $firstInventory->creator->name ?? 'N/A' }}
                                        </div>
                                        <div style="color: #888; margin-top: 3px;">
                                            <i class="fas fa-calendar"></i>
                                            {{ $firstInventory->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-direction: column;">
                                    @if($firstInventory && $book)
                                        <a href="{{ route('admin.inventory.show-by-book', $book->id) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="Xem chi tiết tất cả quyển sách">
                                            <i class="fas fa-eye"></i> Xem chi tiết
                                        </a>
                                        <a href="{{ route('admin.inventory.edit', $firstInventory->id) }}" 
                                           class="btn btn-sm btn-warning" 
                                           title="Sửa thông tin kho">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        @if(auth()->user()->isAdmin())
                                            <form action="{{ route('admin.inventory.destroy-by-book', $book->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa tất cả {{ $item->total_quantity }} quyển sách \"{{ $book->ten_sach }}\" khỏi kho?\\n\\n⚠️ CẢNH BÁO: Sách này cũng sẽ bị xóa khỏi quản lý sách!\\n\\nHành động này không thể hoàn tác!');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        title="Xóa tất cả quyển sách khỏi kho và xóa sách khỏi quản lý sách">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.inventory.delete-requests.store') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Gửi yêu cầu xóa sách \"{{ $book->ten_sach }}\" để Admin duyệt?');" title="Đề xuất xóa">
                                                    <i class="fas fa-paper-plane"></i> Đề xuất xóa
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="padding: 20px;">
            {{ $inventories->appends(request()->query())->links('vendor.pagination.admin') }}
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(0, 255, 153, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-boxes" style="font-size: 36px; color: var(--primary-color);"></i>
            </div>
            <h3 style="color: var(--text-primary); margin-bottom: 10px;">Chưa có sách nào trong kho</h3>
            <p style="color: #888; margin-bottom: 25px;">Hãy thêm sách đầu tiên vào kho để bắt đầu quản lý.</p>
            <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i>
                Thêm sách vào kho
            </a>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý đồng bộ trang chủ
    const syncForm = document.getElementById('syncForm');
    const syncBtn = document.getElementById('syncBtn');
    
    if (syncForm && syncBtn) {
        syncForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Disable button và hiển thị loading
            syncBtn.disabled = true;
            syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đồng bộ...';
            
            // Gửi request
            fetch('{{ route("admin.inventory.sync-to-homepage") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hiển thị thông báo thành công
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success';
                    alertDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    syncForm.parentElement.insertBefore(alertDiv, syncForm);
                    
                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    
                    // Xóa thông báo sau 5 giây
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                } else {
                    // Hiển thị thông báo lỗi
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                    syncForm.parentElement.insertBefore(alertDiv, syncForm);
                    
                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    
                    // Xóa thông báo sau 5 giây
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Hiển thị thông báo lỗi
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Có lỗi xảy ra khi đồng bộ hóa. Vui lòng thử lại.';
                syncForm.parentElement.insertBefore(alertDiv, syncForm);
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Xóa thông báo sau 5 giây
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            })
            .finally(() => {
                // Enable lại button
                syncBtn.disabled = false;
                syncBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Đồng bộ lên trang chủ';
            });
        });
    }
});
</script>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.inventory.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nhập kho từ Excel</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Chọn file Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted">
                            Định dạng: book_id, barcode, location, condition, status, purchase_price, purchase_date
                        </small>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Lưu ý:</strong> File Excel phải có định dạng đúng. Nếu không có mã vạch, hệ thống sẽ tự động tạo.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Nhập file
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

