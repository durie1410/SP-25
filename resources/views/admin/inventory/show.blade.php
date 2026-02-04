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
        <p class="page-subtitle">Thông tin chi tiết về sách trong kho</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>
</div>

<!-- Thông tin sách -->
<div class="row">
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
                            {{ $inventory->barcode }}
                        </code>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tên sách:</strong>
                    </div>
                    <div class="col-md-8">
                        <h5 style="margin: 0; color: var(--text-primary);">{{ $inventory->book->ten_sach ?? 'N/A' }}</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tác giả:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $inventory->book->tac_gia ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Loại lưu trữ:</strong>
                    </div>
                    <div class="col-md-8">
                        @if($inventory->storage_type == 'Kho')
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
                            <i class="fas fa-map-marker-alt"></i> {{ $inventory->location }}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tình trạng:</strong>
                    </div>
                    <div class="col-md-8">
                        @php
                            // Map các giá trị cũ về 3 loại mới
                            $condition = $inventory->condition;
                            if ($condition == 'Tot') {
                                $condition = 'Moi'; // Tốt -> Mới
                            } elseif ($condition == 'Trung binh') {
                                $condition = 'Cu'; // Trung bình -> Cũ
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
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Trạng thái:</strong>
                    </div>
                    <div class="col-md-8">
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
                    </div>
                </div>
                @if($inventory->purchase_price)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Giá mua:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge badge-success">
                            {{ number_format($inventory->purchase_price, 0, ',', '.') }} đ
                        </span>
                    </div>
                </div>
                @endif
                @if($inventory->purchase_date)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Ngày mua:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $inventory->purchase_date->format('d/m/Y') }}
                    </div>
                </div>
                @endif
                @if($inventory->notes)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Ghi chú:</strong>
                    </div>
                    <div class="col-md-8">
                        <p style="margin: 0;">{{ $inventory->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        @if($inventory->hinh_anh)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-image"></i>
                    Ảnh sách
                </h3>
            </div>
            <div class="card-body" style="padding: 25px; text-align: center;">
                <img src="{{ Storage::url($inventory->hinh_anh) }}" 
                     alt="Ảnh sách" 
                     class="img-fluid" 
                     style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            </div>
        </div>
        @endif

        <div class="card" @if($inventory->hinh_anh) style="margin-top: 20px;" @endif>
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
                        <i class="fas fa-user"></i> {{ $inventory->creator->name ?? 'N/A' }}
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Ngày nhập:</strong>
                    <div style="margin-top: 5px;">
                        <i class="fas fa-calendar"></i> {{ $inventory->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Cập nhật lần cuối:</strong>
                    <div style="margin-top: 5px;">
                        <i class="fas fa-clock"></i> {{ $inventory->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        @if($inventory->transactions->count() > 0)
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Lịch sử giao dịch
                </h3>
            </div>
            <div class="card-body" style="padding: 25px; max-height: 400px; overflow-y: auto;">
                @foreach($inventory->transactions->take(5) as $transaction)
                    <div style="padding: 10px; border-bottom: 1px solid #eee; margin-bottom: 10px;">
                        <div style="font-weight: 600; color: var(--text-primary);">
                            {{ $transaction->type }}
                        </div>
                        <div style="font-size: 12px; color: #888; margin-top: 5px;">
                            <i class="fas fa-user"></i> {{ $transaction->performer->name ?? 'N/A' }}
                            <br>
                            <i class="fas fa-calendar"></i> {{ $transaction->created_at->format('d/m/Y H:i') }}
                        </div>
                        @if($transaction->reason)
                        <div style="font-size: 12px; color: #666; margin-top: 5px;">
                            {{ $transaction->reason }}
                        </div>
                        @endif
                    </div>
                @endforeach
                @if($inventory->transactions->count() > 5)
                    <a href="{{ route('admin.inventory.transactions', ['inventory_id' => $inventory->id]) }}" class="btn btn-sm btn-secondary">
                        Xem tất cả
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

