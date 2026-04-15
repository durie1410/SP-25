@extends('layouts.admin')

@section('title', 'Chi tiết nhà cung cấp')

@section('content')
<div class="container-fluid supplier-detail-page">
    <div class="supplier-detail-hero mb-4">
        <div>
            <h2><i class="fas fa-building me-2"></i> {{ $supplier->name }}</h2>
            <p>Thông tin chi tiết và trạng thái hợp tác của nhà cung cấp.</p>
        </div>
        <div class="supplier-detail-actions">
            <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-light btn-return">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="supplier-detail-grid mb-3">
        <div class="detail-stat-card">
            <div class="detail-stat-label">Trạng thái hợp tác</div>
            <div class="detail-stat-value">
                @if(($supplier->status ?? 'active') === 'active')
                    <span class="badge bg-success-subtle text-success-emphasis px-3 py-2">Hoạt động</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary-emphasis px-3 py-2">Ngừng hợp tác</span>
                @endif
            </div>
        </div>
        <div class="detail-stat-card">
            <div class="detail-stat-label">Số phiếu nhập liên quan</div>
            <div class="detail-stat-number">{{ number_format($supplier->receipts_count) }}</div>
        </div>
        <div class="detail-stat-card">
            <div class="detail-stat-label">Mã nhà cung cấp</div>
            <div class="detail-stat-number">#{{ $supplier->id }}</div>
        </div>
    </div>

    <div class="card supplier-detail-card">
        <div class="card-header">
            <h5 class="mb-0">Thông tin liên hệ</h5>
        </div>
        <div class="card-body">
            <div class="detail-row">
                <div class="detail-label">Tên nhà cung cấp</div>
                <div class="detail-value">{{ $supplier->name }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Số điện thoại</div>
                <div class="detail-value">{{ $supplier->phone ?: '-' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Email</div>
                <div class="detail-value">{{ $supplier->email ?: '-' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Địa chỉ</div>
                <div class="detail-value">{{ $supplier->address ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.supplier-detail-hero {
    background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
    border-radius: 16px;
    padding: 20px 22px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.supplier-detail-hero h2 {
    margin: 0 0 4px;
}

.supplier-detail-hero p {
    margin: 0;
    color: rgba(255, 255, 255, 0.85);
}

.supplier-detail-actions {
    display: flex;
    gap: 8px;
}

.btn-return {
    font-weight: 600;
}

.supplier-detail-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.detail-stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px;
}

.detail-stat-label {
    color: #64748b;
    font-size: 12px;
    margin-bottom: 6px;
}

.detail-stat-value,
.detail-stat-number {
    font-weight: 700;
    color: #0f172a;
}

.detail-stat-number {
    font-size: 24px;
}

.supplier-detail-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
}

.supplier-detail-card .card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.detail-row {
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    gap: 12px;
    border-bottom: 1px dashed #e2e8f0;
    padding: 12px 0;
}

.detail-row:last-child {
    border-bottom: 0;
}

.detail-label {
    color: #64748b;
    font-weight: 600;
}

.detail-value {
    color: #0f172a;
    word-break: break-word;
}

@media (max-width: 992px) {
    .supplier-detail-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .supplier-detail-hero {
        padding: 16px;
    }

    .supplier-detail-actions {
        width: 100%;
    }

    .supplier-detail-actions .btn {
        flex: 1;
    }

    .detail-row {
        grid-template-columns: 1fr;
        gap: 4px;
    }
}
</style>
@endpush
