@extends('layouts.admin')

@section('title', 'Lịch Sử Giao Dịch Kho - LibNet Admin')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-history"></i>
            Lịch sử giao dịch kho
        </h1>
        <p class="page-subtitle">Theo dõi tất cả các giao dịch trong kho</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
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
    <form action="{{ route('admin.inventory.transactions') }}" method="GET" style="padding: 25px; display: flex; gap: 15px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <select name="type" class="form-select">
                <option value="">-- Tất cả loại giao dịch --</option>
                <option value="Nhap kho" {{ request('type') == 'Nhap kho' ? 'selected' : '' }}>Nhập kho</option>
                <option value="Xuat kho" {{ request('type') == 'Xuat kho' ? 'selected' : '' }}>Xuất kho</option>
                <option value="Chuyen kho" {{ request('type') == 'Chuyen kho' ? 'selected' : '' }}>Chuyển kho</option>
                <option value="Kiem ke" {{ request('type') == 'Kiem ke' ? 'selected' : '' }}>Kiểm kê</option>
                <option value="Thanh ly" {{ request('type') == 'Thanh ly' ? 'selected' : '' }}>Thanh lý</option>
                <option value="Sua chua" {{ request('type') == 'Sua chua' ? 'selected' : '' }}>Sửa chữa</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <select name="performer_id" class="form-select">
                <option value="">-- Tất cả người thực hiện --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('performer_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <input type="date" 
                   name="from_date" 
                   value="{{ request('from_date') }}" 
                   class="form-control" 
                   placeholder="Từ ngày">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <input type="date" 
                   name="to_date" 
                   value="{{ request('to_date') }}" 
                   class="form-control" 
                   placeholder="Đến ngày">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter"></i>
            Lọc
        </button>
        <a href="{{ route('admin.inventory.transactions') }}" class="btn btn-secondary">
            <i class="fas fa-redo"></i>
            Reset
        </a>
    </form>
</div>

<!-- Transactions List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Danh sách giao dịch
        </h3>
        <span class="badge badge-info">Tổng: {{ $transactions->total() }} giao dịch</span>
    </div>
    
    @if($transactions->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Thời gian</th>
                        <th>Loại giao dịch</th>
                        <th>Sách</th>
                        <th>Mã vạch</th>
                        <th>Số lượng</th>
                        <th>Vị trí</th>
                        <th>Tình trạng</th>
                        <th>Trạng thái</th>
                        <th>Người thực hiện</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <span class="badge badge-info">{{ $transaction->id }}</span>
                            </td>
                            <td>
                                <small style="color: #888;">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td>
                                @if($transaction->type == 'Nhap kho')
                                    <span class="badge badge-success">
                                        <i class="fas fa-arrow-down"></i>
                                        Nhập kho
                                    </span>
                                @elseif($transaction->type == 'Xuat kho')
                                    <span class="badge badge-warning">
                                        <i class="fas fa-arrow-up"></i>
                                        Xuất kho
                                    </span>
                                @elseif($transaction->type == 'Chuyen kho')
                                    <span class="badge badge-info">
                                        <i class="fas fa-exchange-alt"></i>
                                        Chuyển kho
                                    </span>
                                @elseif($transaction->type == 'Kiem ke')
                                    <span class="badge badge-primary">
                                        <i class="fas fa-clipboard-check"></i>
                                        Kiểm kê
                                    </span>
                                @elseif($transaction->type == 'Thanh ly')
                                    <span class="badge" style="background: #6c757d; color: white;">
                                        <i class="fas fa-trash"></i>
                                        Thanh lý
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-tools"></i>
                                        Sửa chữa
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="max-width: 250px;">
                                    <div style="font-weight: 600; color: var(--text-primary);">
                                        {{ $transaction->inventory->book->ten_sach ?? 'N/A' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code style="background: rgba(0, 255, 153, 0.1); padding: 4px 8px; border-radius: 4px; color: var(--primary-color);">
                                    {{ $transaction->inventory->barcode ?? 'N/A' }}
                                </code>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $transaction->quantity }}</span>
                            </td>
                            <td>
                                @if($transaction->from_location && $transaction->to_location)
                                    <div style="font-size: 12px;">
                                        <div><i class="fas fa-arrow-right text-muted"></i> {{ $transaction->from_location }}</div>
                                        <div><i class="fas fa-arrow-right text-primary"></i> {{ $transaction->to_location }}</div>
                                    </div>
                                @elseif($transaction->to_location)
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $transaction->to_location }}
                                    </span>
                                @elseif($transaction->from_location)
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $transaction->from_location }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->condition_before && $transaction->condition_after)
                                    <div style="font-size: 12px;">
                                        <div><span class="badge badge-secondary">{{ $transaction->condition_before }}</span></div>
                                        <div><i class="fas fa-arrow-right"></i> <span class="badge badge-primary">{{ $transaction->condition_after }}</span></div>
                                    </div>
                                @elseif($transaction->condition_after)
                                    <span class="badge badge-primary">{{ $transaction->condition_after }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->status_before && $transaction->status_after)
                                    <div style="font-size: 12px;">
                                        <div><span class="badge badge-secondary">{{ $transaction->status_before }}</span></div>
                                        <div><i class="fas fa-arrow-right"></i> 
                                            @if($transaction->status_after == 'Co san')
                                                <span class="badge badge-success">Có sẵn</span>
                                            @elseif($transaction->status_after == 'Dang muon')
                                                <span class="badge badge-warning">Đang mượn</span>
                                            @else
                                                <span class="badge badge-danger">{{ $transaction->status_after }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($transaction->status_after)
                                    @if($transaction->status_after == 'Co san')
                                        <span class="badge badge-success">Có sẵn</span>
                                    @elseif($transaction->status_after == 'Dang muon')
                                        <span class="badge badge-warning">Đang mượn</span>
                                    @else
                                        <span class="badge badge-danger">{{ $transaction->status_after }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small style="color: #888;">
                                    {{ $transaction->performer->name ?? 'N/A' }}
                                </small>
                            </td>
                            <td>
                                @if($transaction->reason || $transaction->notes)
                                    <div style="max-width: 200px; font-size: 12px;">
                                        @if($transaction->reason)
                                            <div><strong>Lý do:</strong> {{ Str::limit($transaction->reason, 50) }}</div>
                                        @endif
                                        @if($transaction->notes)
                                            <div class="text-muted">{{ Str::limit($transaction->notes, 50) }}</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="padding: 20px;">
            {{ $transactions->appends(request()->query())->links('vendor.pagination.admin') }}
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(0, 255, 153, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-history" style="font-size: 36px; color: var(--primary-color);"></i>
            </div>
            <h3 style="color: var(--text-primary); margin-bottom: 10px;">Chưa có giao dịch nào</h3>
            <p style="color: #888; margin-bottom: 25px;">Các giao dịch kho sẽ được hiển thị ở đây.</p>
        </div>
    @endif
</div>
@endsection

