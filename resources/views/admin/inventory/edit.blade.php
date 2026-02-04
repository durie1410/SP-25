@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Sách Trong Kho - LibNet Admin')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-edit"></i>
            Chỉnh sửa sách trong kho
        </h1>
        <p class="page-subtitle">Cập nhật thông tin sách trong kho</p>
    </div>
    <div>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
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

<!-- Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-edit"></i>
            Thông tin sách
        </h3>
    </div>
    <form action="{{ route('admin.inventory.update', $inventory->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body" style="padding: 25px;">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Sách</label>
                        <input type="text" 
                               class="form-control" 
                               value="{{ $inventory->book->ten_sach ?? 'N/A' }} - {{ $inventory->book->tac_gia ?? 'N/A' }}"
                               disabled>
                        <small class="form-text text-muted">Không thể thay đổi sách</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Mã vạch</label>
                        <input type="text" 
                               class="form-control" 
                               value="{{ $inventory->barcode }}"
                               disabled>
                        <small class="form-text text-muted">Không thể thay đổi mã vạch</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Loại lưu trữ <span class="text-danger">*</span></label>
                        <select name="storage_type" class="form-control" required>
                            <option value="Kho" {{ old('storage_type', $inventory->storage_type) == 'Kho' ? 'selected' : '' }}>Kho</option>
                            <option value="Trung bay" {{ old('storage_type', $inventory->storage_type) == 'Trung bay' ? 'selected' : '' }}>Trưng bày</option>
                        </select>
                        @error('storage_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Vị trí <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="location" 
                               class="form-control" 
                               value="{{ old('location', $inventory->location) }}"
                               placeholder="Ví dụ: Kệ A1, Tầng 2, Khu vực trưng bày..."
                               required>
                        @error('location')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tình trạng sách <span class="text-danger">*</span></label>
                        <select name="condition" class="form-control" required>
                            <option value="Moi" {{ old('condition', $inventory->condition) == 'Moi' ? 'selected' : '' }}>Mới</option>
                            <option value="Cu" {{ old('condition', $inventory->condition) == 'Cu' ? 'selected' : '' }}>Cũ</option>
                            <option value="Hong" {{ old('condition', $inventory->condition) == 'Hong' ? 'selected' : '' }}>Hỏng</option>
                            @if(!in_array($inventory->condition, ['Moi', 'Cu', 'Hong']))
                                {{-- Giữ lại giá trị cũ nếu không phải 3 loại mới --}}
                                <option value="{{ $inventory->condition }}" selected>{{ $inventory->condition }}</option>
                            @endif
                        </select>
                        @error('condition')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="Co san" {{ old('status', $inventory->status) == 'Co san' ? 'selected' : '' }}>Có sẵn</option>
                            <option value="Dang muon" {{ old('status', $inventory->status) == 'Dang muon' ? 'selected' : '' }}>Đang mượn</option>
                            <option value="Mat" {{ old('status', $inventory->status) == 'Mat' ? 'selected' : '' }}>Mất</option>
                            <option value="Hong" {{ old('status', $inventory->status) == 'Hong' ? 'selected' : '' }}>Hỏng</option>
                            <option value="Thanh ly" {{ old('status', $inventory->status) == 'Thanh ly' ? 'selected' : '' }}>Thanh lý</option>
                        </select>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Giá mua (VNĐ)</label>
                        <input type="number" 
                               name="purchase_price" 
                               class="form-control" 
                               value="{{ old('purchase_price', $inventory->purchase_price) }}"
                               min="0"
                               step="1000"
                               placeholder="0">
                        @error('purchase_price')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Ngày mua</label>
                        <input type="date" 
                               name="purchase_date" 
                               class="form-control" 
                               value="{{ old('purchase_date', $inventory->purchase_date ? $inventory->purchase_date->format('Y-m-d') : '') }}">
                        @error('purchase_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Ghi chú</label>
                <textarea name="notes" 
                          class="form-control" 
                          rows="3"
                          placeholder="Ghi chú về sách...">{{ old('notes', $inventory->notes) }}</textarea>
                @error('notes')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Ảnh sách</label>
                @if($inventory->hinh_anh)
                    <div class="mb-3">
                        <img src="{{ Storage::url($inventory->hinh_anh) }}" 
                             alt="Ảnh sách" 
                             class="img-thumbnail" 
                             style="max-width: 300px; max-height: 300px; object-fit: cover;">
                        <p class="text-muted mt-2">Ảnh hiện tại</p>
                    </div>
                @endif
                <input type="file" 
                       name="hinh_anh" 
                       class="form-control-file" 
                       accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                <small class="form-text text-muted">
                    Chấp nhận định dạng: JPEG, PNG, JPG, GIF, WEBP. Kích thước tối đa: 2MB
                </small>
                @error('hinh_anh')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="card-footer" style="padding: 20px 25px; background: #f8f9fa; border-top: 1px solid #dee2e6;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Cập nhật
            </button>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Hủy
            </a>
        </div>
    </form>
</div>
@endsection

