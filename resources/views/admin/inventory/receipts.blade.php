@extends('layouts.admin')

@section('title', 'Phiếu Nhập Kho - Admin')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-boxes" style="color: #22c55e;"></i>
            Phiếu Nhập Kho
        </h1>
        <p class="page-subtitle">Quản lý và theo dõi tất cả phiếu nhập kho</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.inventory.receipts.create') }}" class="btn btn-primary" style="background: #22c55e; color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i>
            Tạo Phiếu Nhập Mới
        </a>
        <a href="{{ route('admin.inventory-reports.import') }}" class="btn btn-info" style="background: #06b6d4; color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-chart-bar"></i>
            Báo Cáo Nhập Kho
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<!-- Thống kê nhanh -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; aspect-ratio: 1; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">TỔNG PHIẾU NHẬP</h6>
            <div style="width: 44px; height: 44px; background: #dbeafe; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-file-invoice" style="font-size: 22px; color: #3b82f6;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ $receipts->total() }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Phiếu nhập trong hệ thống</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #22c55e; font-size: 12px; margin-top: auto;">
            <i class="fas fa-arrow-up"></i>
            <span>Đang hoạt động</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; aspect-ratio: 1; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">CHỜ PHÊ DUYỆT</h6>
            <div style="width: 44px; height: 44px; background: #fef3c7; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-clock" style="font-size: 22px; color: #f59e0b;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ \App\Models\InventoryReceipt::where('status', 'pending')->count() }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Phiếu đang chờ xử lý</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #f59e0b; font-size: 12px; margin-top: auto;">
            <i class="fas fa-hourglass-half"></i>
            <span>Cần xử lý</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; aspect-ratio: 1; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">ĐÃ PHÊ DUYỆT</h6>
            <div style="width: 44px; height: 44px; background: #d1fae5; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-check-circle" style="font-size: 22px; color: #22c55e;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ \App\Models\InventoryReceipt::where('status', 'approved')->count() }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Phiếu đã được duyệt</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #22c55e; font-size: 12px; margin-top: auto;">
            <i class="fas fa-check"></i>
            <span>Hoạt động bình thường</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; aspect-ratio: 1; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">TỔNG SÁCH NHẬP</h6>
            <div style="width: 44px; height: 44px; background: #dbeafe; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-book" style="font-size: 22px; color: #06b6d4;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ \App\Models\InventoryReceipt::sum('quantity') }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Sách đã nhập vào kho</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #06b6d4; font-size: 12px; margin-top: auto;">
            <i class="fas fa-arrow-up"></i>
            <span>Tổng số lượng</span>
        </div>
    </div>
</div>

    <!-- Search and Filter -->
    <div class="card" style="margin-bottom: 25px; background: white; border: 1px solid #e5e7eb; border-radius: 12px;">
        <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px;">
            <h3 class="card-title" style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
                <i class="fas fa-search" style="color: #22c55e; margin-right: 8px;"></i>
                Tìm kiếm & Lọc
            </h3>
        </div>
        <form method="GET" action="{{ route('admin.inventory.receipts') }}" style="padding: 0 25px 25px 25px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <div>
                    <select name="status" class="form-control" style="width: 100%;">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ phê duyệt</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã phê duyệt</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                    </select>
                </div>
                <div>
                    <select name="book_id" class="form-control" style="width: 100%;">
                        <option value="">-- Tất cả sách --</option>
                        @foreach($books as $book)
                            <option value="{{ $book->id }}" {{ request('book_id') == $book->id ? 'selected' : '' }}>
                                {{ $book->ten_sach }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="Từ ngày" style="width: 100%;">
                </div>
                <div>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="Đến ngày" style="width: 100%;">
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-start;">
                <button type="submit" class="btn btn-primary" style="background: #22c55e; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-filter"></i>
                    Lọc
                </button>
                <a href="{{ route('admin.inventory.receipts') }}" class="btn btn-secondary" style="background: white; color: #1f2937; border: 1px solid #e5e7eb; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-redo"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

<!-- Bảng danh sách -->
<div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px;">
    <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px;">
        <h3 class="card-title" style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
            <i class="fas fa-list" style="color: #22c55e; margin-right: 8px;"></i>
            Danh sách phiếu nhập
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table table-striped" style="margin: 0;">
                <thead>
                    <tr>
                        <th>Số phiếu</th>
                        <th>Ngày nhập</th>
                        <th>Sách</th>
                        <th>Số lượng</th>
                        <th>Vị trí</th>
                        <th>Loại</th>
                        <th>Người nhập</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                        <tr>
                            <td><strong>{{ $receipt->receipt_number }}</strong></td>
                            <td>{{ optional($receipt->receipt_date)->format('d/m/Y') }}</td>
                            <td>
                                @if($receipt->book)
                                    {{ $receipt->book->ten_sach }}
                                @else
                                    <span class="text-muted">[Sách đã xoá hoặc không tồn tại]</span>
                                @endif
                            </td>
                            <td>{{ $receipt->quantity }}</td>
                            <td>{{ $receipt->storage_location }}</td>
                            <td>
                                @if($receipt->storage_type == 'Kho')
                                    <span class="badge badge-info">Kho</span>
                                @else
                                    <span class="badge badge-warning">Trưng bày</span>
                                @endif
                            </td>
                            <td>{{ optional($receipt->receiver)->name ?? 'N/A' }}</td>
                            <td>
                                @if($receipt->status == 'pending')
                                    <span class="badge badge-warning">Chờ phê duyệt</span>
                                @elseif($receipt->status == 'approved')
                                    <span class="badge badge-success">Đã phê duyệt</span>
                                @else
                                    <span class="badge badge-danger">Từ chối</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.inventory.receipts.show', $receipt->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                @if($receipt->status == 'pending' && auth()->user()->isAdmin())
                                    <form action="{{ route('admin.inventory.receipts.approve', $receipt->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Duyệt
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="rejectReceipt({{ $receipt->id }})">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Không có phiếu nhập nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 20px;">
            {{ $receipts->links() }}
        </div>
    </div>
</div>

<!-- Modal từ chối phiếu -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Từ chối phiếu nhập</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Lý do từ chối:</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function rejectReceipt(id) {
    const form = document.getElementById('rejectForm');
    const modal = document.getElementById('rejectModal');
    
    if (form && modal) {
        form.action = '{{ url("admin/inventory-receipts") }}/' + id + '/reject';
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Add backdrop
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.style.cssText = 'position: fixed; top: 0; left: 0; z-index: 1040; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.5);';
            document.body.appendChild(backdrop);
        }
    }
}

// Close modal when clicking close button or backdrop
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('rejectModal');
    if (modal) {
        const closeButtons = modal.querySelectorAll('[data-dismiss="modal"], .close');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                closeModal();
            });
        });
        
        // Close on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
});

function closeModal() {
    const modal = document.getElementById('rejectModal');
    const backdrop = document.querySelector('.modal-backdrop');
    
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
    }
    
    if (backdrop) {
        backdrop.remove();
    }
    
    document.body.classList.remove('modal-open');
}
</script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
    display: block;
}

.modal-dialog {
    position: relative;
    width: auto;
    max-width: 500px;
    margin: 30px auto;
    pointer-events: none;
}

.modal.show .modal-dialog {
    pointer-events: auto;
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 0.3rem;
    outline: 0;
    box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    border-top-left-radius: 0.3rem;
    border-top-right-radius: 0.3rem;
}

.modal-title {
    margin-bottom: 0;
    line-height: 1.5;
}

.modal-body {
    position: relative;
    flex: 1 1 auto;
    padding: 1rem;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
    border-bottom-right-radius: 0.3rem;
    border-bottom-left-radius: 0.3rem;
}

.close {
    padding: 1rem;
    margin: -1rem -1rem -1rem auto;
    background: transparent;
    border: 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.5;
    cursor: pointer;
}

.close:hover {
    opacity: 0.75;
}

body.modal-open {
    overflow: hidden;
}
</style>
@endsection

