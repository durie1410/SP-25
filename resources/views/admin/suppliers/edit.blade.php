@extends('layouts.admin')

@section('title', 'Cập nhật nhà cung cấp')

@section('content')
<div class="container-fluid supplier-form-page">
    <div class="supplier-form-hero mb-4">
        <div>
            <h2><i class="fas fa-pen-to-square me-2"></i> Cập nhật nhà cung cấp</h2>
            <p>Chỉnh sửa thông tin đối tác để đồng bộ dữ liệu nhập kho và liên hệ.</p>
        </div>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-light btn-return">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <div class="card supplier-form-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Hồ sơ nhà cung cấp #{{ $supplier->id }}</h5>
            <span class="badge {{ ($supplier->status ?? 'active') === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }} px-3 py-2">
                {{ ($supplier->status ?? 'active') === 'active' ? 'Hoạt động' : 'Ngừng hợp tác' }}
            </span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.suppliers.update', $supplier->id) }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-8">
                    <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $supplier->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Trạng thái hợp tác <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $supplier->status) === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ old('status', $supplier->status) === 'inactive' ? 'selected' : '' }}>Ngừng hợp tác</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $supplier->phone) }}" placeholder="Nhập số điện thoại liên hệ">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $supplier->email) }}" placeholder="Nhập email liên hệ">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Địa chỉ</label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3" placeholder="Nhập địa chỉ đầy đủ">{{ old('address', $supplier->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu cập nhật
                    </button>
                    <a href="{{ route('admin.suppliers.show', $supplier->id) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye"></i> Xem chi tiết
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.supplier-form-hero {
    background: linear-gradient(135deg, #1d4ed8 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 20px 22px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.supplier-form-hero h2 {
    margin: 0 0 4px;
}

.supplier-form-hero p {
    margin: 0;
    color: rgba(255, 255, 255, 0.85);
}

.btn-return {
    font-weight: 600;
    border-radius: 10px;
}

.supplier-form-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
}

.supplier-form-card .card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
    .supplier-form-hero {
        padding: 16px;
    }

    .btn-return {
        width: 100%;
    }
}
</style>
@endpush
