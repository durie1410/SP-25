@extends('layouts.admin')

@section('title', 'Chi Tiết Sách Trong Kho - LibNet Admin')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-eye"></i>
            Chi tiết sách trong kho
        </h1>
        <p class="page-subtitle">Danh sách tất cả quyển sách: <strong>{{ $book->ten_sach }}</strong></p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>
</div>

<!-- Thống kê -->
<div style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
    <!-- Card 1: Tổng sách trong kho -->
    <div style="flex: 1; min-width: 240px; max-width: calc(25% - 12px);">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: 100%; border: 1px solid #e5e7eb;">
            <div class="card-body" style="padding: 20px; position: relative;">
                <!-- Icon ở góc trên phải -->
                <div style="position: absolute; top: 20px; right: 20px; width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-book" style="font-size: 24px; color: #3b82f6;"></i>
                </div>
                
                <!-- Title -->
                <div style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px;">
                    Tổng sách trong kho
                </div>
                
                <!-- Số lớn -->
                <div style="font-size: 36px; font-weight: bold; color: #1f2937; margin-bottom: 15px;">
                    {{ $stats['total'] }}
                </div>
                
                <!-- Thông tin bổ sung -->
                <div style="font-size: 13px; color: #6b7280; margin-bottom: 5px;">
                    Sách trong kho
                </div>
                <div style="font-size: 12px; color: #3b82f6; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-arrow-up" style="font-size: 10px;"></i>
                    <span>Tổng số lượng</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 2: Còn lại trong kho -->
    <div style="flex: 1; min-width: 240px; max-width: calc(25% - 12px);">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: 100%; border: 1px solid #e5e7eb;">
            <div class="card-body" style="padding: 20px; position: relative;">
                <!-- Icon ở góc trên phải -->
                <div style="position: absolute; top: 20px; right: 20px; width: 50px; height: 50px; background: rgba(34, 197, 94, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-check-circle" style="font-size: 24px; color: #22c55e;"></i>
                </div>
                
                <!-- Title -->
                <div style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px;">
                    Còn lại trong kho
                </div>
                
                <!-- Số lớn -->
                <div style="font-size: 36px; font-weight: bold; color: #1f2937; margin-bottom: 15px;">
                    {{ $stats['remaining'] }}
                </div>
                
                <!-- Thông tin bổ sung -->
                <div style="font-size: 13px; color: #6b7280; margin-bottom: 5px;">
                    Có sẵn: {{ $stats['available'] }}
                </div>
                <div style="font-size: 12px; color: #22c55e; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-check-circle" style="font-size: 10px;"></i>
                    <span>Hoạt động bình thường</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 3: Đã cho mượn -->
    <div style="flex: 1; min-width: 240px; max-width: calc(25% - 12px);">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: 100%; border: 1px solid #e5e7eb;">
            <div class="card-body" style="padding: 20px; position: relative;">
                <!-- Icon ở góc trên phải -->
                <div style="position: absolute; top: 20px; right: 20px; width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-hand-holding" style="font-size: 24px; color: #f59e0b;"></i>
                </div>
                
                <!-- Title -->
                <div style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px;">
                    Đã cho mượn
                </div>
                
                <!-- Số lớn -->
                <div style="font-size: 36px; font-weight: bold; color: #1f2937; margin-bottom: 15px;">
                    {{ $stats['borrowed'] }}
                </div>
                
                <!-- Thông tin bổ sung -->
                <div style="font-size: 13px; color: #6b7280; margin-bottom: 5px;">
                    Sách đang được mượn
                </div>
                <div style="font-size: 12px; color: #f59e0b; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-arrow-up" style="font-size: 10px;"></i>
                    <span>Đang mượn</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 4: Tổng đã nhập -->
    <div style="flex: 1; min-width: 240px; max-width: calc(25% - 12px);">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: 100%; border: 1px solid #e5e7eb;">
            <div class="card-body" style="padding: 20px; position: relative;">
                <!-- Icon ở góc trên phải -->
                <div style="position: absolute; top: 20px; right: 20px; width: 50px; height: 50px; background: rgba(6, 182, 212, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-file-invoice" style="font-size: 24px; color: #06b6d4;"></i>
                </div>
                
                <!-- Title -->
                <div style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px;">
                    Tổng đã nhập
                </div>
                
                <!-- Số lớn -->
                <div style="font-size: 36px; font-weight: bold; color: #1f2937; margin-bottom: 15px;">
                    {{ $stats['total'] }}
                </div>
                
                <!-- Thông tin bổ sung -->
                <div style="font-size: 13px; color: #6b7280; margin-bottom: 5px;">
                    {{ $stats['total_receipts'] }} phiếu nhập
                </div>
                <div style="font-size: 12px; color: #06b6d4; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-info-circle" style="font-size: 10px;"></i>
                    <span>Tổng đã nhập</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Thông tin sách và người nhập -->
<div class="row" style="margin-bottom: 25px;">
    @if($firstInventory)
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-book"></i>
                    Thông tin sách
                </h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Mã vạch:</strong>
                    </div>
                    <div class="col-md-8">
                        <code style="background: rgba(0, 255, 153, 0.1); padding: 6px 12px; border-radius: 4px; color: var(--primary-color); font-size: 16px;">
                            {{ $firstInventory->barcode }}
                        </code>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tên sách:</strong>
                    </div>
                    <div class="col-md-8">
                        <h5 style="margin: 0; color: var(--text-primary);">{{ $book->ten_sach }}</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tác giả:</strong>
                    </div>
                    <div class="col-md-8">
                        <i class="fas fa-user"></i> {{ $book->tac_gia ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Loại lưu trữ:</strong>
                    </div>
                    <div class="col-md-8">
                        @if($firstInventory->storage_type == 'Kho')
                            <span class="badge badge-info" style="font-size: 14px; padding: 8px 12px;">
                                <i class="fas fa-warehouse"></i> Kho
                            </span>
                        @else
                            <span class="badge badge-warning" style="font-size: 14px; padding: 8px 12px;">
                                <i class="fas fa-store"></i> Trưng bày
                            </span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Vị trí:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge badge-secondary" style="font-size: 14px; padding: 8px 12px;">
                            <i class="fas fa-map-marker-alt"></i> {{ $firstInventory->location }}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tình trạng:</strong>
                    </div>
                    <div class="col-md-8">
                        @php
                            $condition = $firstInventory->condition;
                            if ($condition == 'Tot') {
                                $condition = 'Moi';
                            } elseif ($condition == 'Trung binh') {
                                $condition = 'Cu';
                            }
                        @endphp
                        @if($condition == 'Moi')
                            <span class="badge badge-success">Mới</span>
                        @elseif($condition == 'Cu')
                            <span class="badge" style="background: #6c757d; color: white;">Cũ</span>
                        @elseif($condition == 'Hong')
                            <span class="badge badge-danger">Hỏng</span>
                        @else
                            <span class="badge badge-secondary">{{ $firstInventory->condition }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Trạng thái:</strong>
                    </div>
                    <div class="col-md-8">
                        @if($firstInventory->status == 'Co san')
                            <span class="badge badge-success">Có sẵn</span>
                        @elseif($firstInventory->status == 'Dang muon')
                            <span class="badge badge-warning">Đang mượn</span>
                        @elseif($firstInventory->status == 'Mat')
                            <span class="badge badge-danger">Mất</span>
                        @elseif($firstInventory->status == 'Hong')
                            <span class="badge badge-danger">Hỏng</span>
                        @else
                            <span class="badge" style="background: #6c757d; color: white;">Thanh lý</span>
                        @endif
                    </div>
                </div>
                @if($firstInventory->purchase_price)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Giá mua:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge badge-success" style="font-size: 16px; padding: 8px 12px;">
                            {{ number_format($firstInventory->purchase_price, 0, ',', '.') }} đ
                        </span>
                    </div>
                </div>
                @endif
                @if($firstInventory->purchase_date)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Ngày mua:</strong>
                    </div>
                    <div class="col-md-8">
                        <i class="fas fa-calendar"></i> {{ $firstInventory->purchase_date->format('d/m/Y') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i>
                    Thông tin người nhập
                </h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="mb-3">
                    <strong>Người nhập:</strong>
                    <div style="margin-top: 5px;">
                        <i class="fas fa-user"></i> {{ $firstInventory->creator->name ?? 'N/A' }}
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Ngày nhập:</strong>
                    <div style="margin-top: 5px;">
                        <i class="fas fa-calendar"></i> {{ $firstInventory->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Cập nhật lần cuối:</strong>
                    <div style="margin-top: 5px;">
                        <i class="fas fa-clock"></i> {{ $firstInventory->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-book"></i>
                    Thông tin sách
                </h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Tên sách:</strong>
                            <div style="margin-top: 5px;">
                                <h5 style="margin: 0; color: var(--text-primary);">{{ $book->ten_sach }}</h5>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>Tác giả:</strong>
                            <div style="margin-top: 5px;">
                                <i class="fas fa-user"></i> {{ $book->tac_gia ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Thể loại:</strong>
                            <div style="margin-top: 5px;">
                                {{ $book->category->ten_the_loai ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>Nhà xuất bản:</strong>
                            <div style="margin-top: 5px;">
                                {{ $book->publisher->ten_nha_xuat_ban ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Danh sách quyển sách -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Danh sách quyển sách ({{ $inventories->count() }} quyển)
        </h3>
        <div style="display: flex; gap: 10px;">
            <span class="badge badge-info">
                <i class="fas fa-warehouse"></i> Kho: {{ $stats['in_kho'] }}
            </span>
            <span class="badge badge-warning">
                <i class="fas fa-store"></i> Trưng bày: {{ $stats['on_display'] }}
            </span>
            @if($stats['damaged'] > 0)
            <span class="badge badge-danger">
                <i class="fas fa-times-circle"></i> Hỏng: {{ $stats['damaged'] }}
            </span>
            @endif
        </div>
    </div>
    
    @if($inventories->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã vạch</th>
                        <th>Vị trí</th>
                        <th>Loại lưu trữ</th>
                        <th>Tình trạng</th>
                        <th>Trạng thái</th>
                        <th>Giá mua</th>
                        <th>Người nhập</th>
                        <th>Ngày nhập</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventories as $inventory)
                        <tr>
                            <td>
                                <code style="background: rgba(0, 255, 153, 0.1); padding: 6px 12px; border-radius: 4px; color: var(--primary-color);">
                                    {{ $inventory->barcode }}
                                </code>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    <i class="fas fa-map-marker-alt"></i> {{ $inventory->location }}
                                </span>
                            </td>
                            <td>
                                @if($inventory->storage_type == 'Kho')
                                    <span class="badge badge-info">
                                        <i class="fas fa-warehouse"></i> Kho
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-store"></i> Trưng bày
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $condition = $inventory->condition;
                                    if ($condition == 'Tot') {
                                        $condition = 'Moi';
                                    } elseif ($condition == 'Trung binh') {
                                        $condition = 'Cu';
                                    }
                                @endphp
                                @if($condition == 'Moi')
                                    <span class="badge badge-success">Mới</span>
                                @elseif($condition == 'Cu')
                                    <span class="badge" style="background: #6c757d; color: white;">Cũ</span>
                                @elseif($condition == 'Hong')
                                    <span class="badge badge-danger">Hỏng</span>
                                @else
                                    <span class="badge badge-secondary">{{ $inventory->condition }}</span>
                                @endif
                            </td>
                            <td>
                                @if($inventory->status == 'Co san')
                                    <span class="badge badge-success">Có sẵn</span>
                                @elseif($inventory->status == 'Dang muon')
                                    <span class="badge badge-warning">Đang mượn</span>
                                @elseif($inventory->status == 'Mat')
                                    <span class="badge badge-danger">Mất</span>
                                @elseif($inventory->status == 'Hong')
                                    <span class="badge badge-danger">Hỏng</span>
                                @else
                                    <span class="badge" style="background: #6c757d; color: white;">Thanh lý</span>
                                @endif
                            </td>
                            <td>
                                @if($inventory->purchase_price)
                                    <span class="badge badge-success">
                                        {{ number_format($inventory->purchase_price, 0, ',', '.') }} đ
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 12px;">
                                    <i class="fas fa-user"></i> {{ $inventory->creator->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12px;">
                                    <i class="fas fa-calendar"></i> {{ $inventory->created_at->format('d/m/Y') }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="{{ route('admin.inventory.show', $inventory->id) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.inventory.edit', $inventory->id) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.inventory.destroy', $inventory->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa quyển sách này?{{ $inventories->count() == 1 ? "\\n\\n⚠️ CẢNH BÁO: Đây là quyển sách cuối cùng! Sách này cũng sẽ bị xóa khỏi quản lý sách!" : "" }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger" 
                                                title="Xóa{{ $inventories->count() == 1 ? " (sẽ xóa cả sách khỏi quản lý sách)" : "" }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(0, 255, 153, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-boxes" style="font-size: 36px; color: var(--primary-color);"></i>
            </div>
            <h3 style="color: var(--text-primary); margin-bottom: 10px;">Chưa có quyển sách nào trong kho</h3>
            <p style="color: #888; margin-bottom: 25px;">Hãy thêm quyển sách đầu tiên vào kho.</p>
            <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm sách vào kho
            </a>
        </div>
    @endif
</div>
@endsection

