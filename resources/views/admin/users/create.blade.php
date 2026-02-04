@extends('layouts.admin')

@section('title', 'Thêm Người Dùng Mới - Admin')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="page-title">
                <i class="fas fa-user-plus me-3"></i>
                Thêm Người Dùng Mới
            </h1>
            <p class="page-subtitle">Tạo tài khoản người dùng mới trong hệ thống</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<div class="admin-table">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                           required minlength="8">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Mật khẩu phải có ít nhất 8 ký tự</small>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">-- Chọn vai trò --</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                        <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Thủ thư (Staff)</option>
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Người dùng</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Lưu
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </form>
</div>

<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .page-subtitle {
        font-size: 1rem;
        margin: 10px 0 0 0;
        opacity: 0.9;
    }
    
    .form-label {
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
</style>
@endsection


