@extends('layouts.admin')

@section('title', 'Chi Tiết Phiếu Nhập - Admin')

@section('content')
<div class="admin-table">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="fas fa-file-invoice"></i> Chi Tiết Phiếu Nhập: {{ $receipt->receipt_number }}</h3>
        <a href="{{ route('admin.inventory.receipts') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Thông tin phiếu nhập</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Số phiếu:</th>
                            <td><strong>{{ $receipt->receipt_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Ngày nhập:</th>
                            <td>{{ $receipt->receipt_date->format('d/m/Y') }}</td>
                        </tr>
                        @if($receipt->items && $receipt->items->count() > 0)
                            <tr>
                                <th>Số loại sách:</th>
                                <td><strong>{{ $receipt->items->count() }}</strong> loại</td>
                            </tr>
                            <tr>
                                <th>Tổng số lượng:</th>
                                <td><strong>{{ $receipt->items->sum('quantity') }}</strong> cuốn</td>
                            </tr>
                        @else
                            <tr>
                                <th>Sách:</th>
                                <td>{{ $receipt->book ? $receipt->book->ten_sach : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Số lượng:</th>
                                <td><strong>{{ $receipt->quantity }}</strong> cuốn</td>
                            </tr>
                            <tr>
                                <th>Vị trí lưu trữ:</th>
                                <td>{{ $receipt->storage_location }}</td>
                            </tr>
                            <tr>
                                <th>Loại lưu trữ:</th>
                                <td>
                                    @if($receipt->storage_type == 'Kho')
                                        <span class="badge badge-info">Kho</span>
                                    @else
                                        <span class="badge badge-warning">Trưng bày</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Giá đơn vị:</th>
                                <td>{{ $receipt->unit_price ? number_format($receipt->unit_price, 0, ',', '.') . ' VNĐ' : '-' }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Tổng giá:</th>
                            <td><strong>{{ $receipt->total_price ? number_format($receipt->total_price, 0, ',', '.') . ' VNĐ' : '-' }}</strong></td>
                        </tr>
                        <tr>
                            <th>Nhà cung cấp:</th>
                            <td>{{ optional($receipt->supplier)->name ?? $receipt->supplier ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Người nhập:</th>
                            <td>{{ $receipt->receiver->name }}</td>
                        </tr>
                        <tr>
                            <th>Người phê duyệt:</th>
                            <td>{{ $receipt->approver ? $receipt->approver->name : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Trạng thái:</th>
                            <td>
                                @if($receipt->status == 'pending')
                                    <span class="badge badge-warning">Chờ phê duyệt</span>
                                @elseif($receipt->status == 'approved')
                                    <span class="badge badge-success">Đã phê duyệt</span>
                                @else
                                    <span class="badge badge-danger">Từ chối</span>
                                @endif
                            </td>
                        </tr>
                        @if($receipt->notes)
                            <tr>
                                <th>Ghi chú:</th>
                                <td>{{ $receipt->notes }}</td>
                            </tr>
                        @endif
                    </table>

                    @if($receipt->status == 'pending' && auth()->user()->isAdmin())
                        <div class="mt-3">
                            <form action="{{ route('admin.inventory.receipts.approve', $receipt->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Phê duyệt
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" onclick="rejectReceipt({{ $receipt->id }})">
                                <i class="fas fa-times"></i> Từ chối
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Danh sách sách trong phiếu</h5>
                </div>
                <div class="card-body">
                    @if($receipt->items && $receipt->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên sách</th>
                                        <th>Số lượng</th>
                                        <th>Vị trí</th>
                                        <th>Loại</th>
                                        <th>Giá đơn vị</th>
                                        <th>Tổng giá</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receipt->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->book->ten_sach ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $item->book->tac_gia ?? '' }}</small>
                                            </td>
                                            <td><strong>{{ $item->quantity }}</strong></td>
                                            <td>{{ $item->storage_location }}</td>
                                            <td>
                                                @if($item->storage_type == 'Kho')
                                                    <span class="badge badge-info">Kho</span>
                                                @else
                                                    <span class="badge badge-warning">Trưng bày</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->unit_price ? number_format($item->unit_price, 0, ',', '.') . ' VNĐ' : '-' }}</td>
                                            <td><strong>{{ $item->total_price ? number_format($item->total_price, 0, ',', '.') . ' VNĐ' : '-' }}</strong></td>
                                        </tr>
                                        @if($item->notes)
                                            <tr>
                                                <td colspan="7" style="padding-left: 30px; font-size: 12px; color: #6b7280;">
                                                    <i class="fas fa-sticky-note"></i> Ghi chú: {{ $item->notes }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background: #f9fafb; font-weight: bold;">
                                        <td colspan="2">Tổng cộng</td>
                                        <td><strong>{{ $receipt->items->sum('quantity') }}</strong></td>
                                        <td colspan="3"></td>
                                        <td><strong>{{ number_format($receipt->items->sum('total_price'), 0, ',', '.') }} VNĐ</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @elseif($receipt->inventories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mã vạch</th>
                                        <th>Vị trí</th>
                                        <th>Tình trạng</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receipt->inventories as $inventory)
                                        <tr>
                                            <td>{{ $inventory->barcode }}</td>
                                            <td>{{ $inventory->location }}</td>
                                            <td>{{ $inventory->condition }}</td>
                                            <td>
                                                @if($inventory->status == 'Co san')
                                                    <span class="badge badge-success">Có sẵn</span>
                                                @else
                                                    <span class="badge badge-warning">{{ $inventory->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Chưa có sách nào được nhập từ phiếu này</p>
                    @endif
                </div>
            </div>
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

