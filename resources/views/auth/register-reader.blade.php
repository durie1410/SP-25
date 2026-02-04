@extends('layouts.app')

@section('title', 'Đăng Ký Độc Giả - Thư Viện Online')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus"></i> Đăng ký độc giả
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('register.reader') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="so_dien_thoai" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('so_dien_thoai') is-invalid @enderror" 
                                           id="so_dien_thoai" name="so_dien_thoai" value="{{ old('so_dien_thoai') }}" required>
                                    @error('so_dien_thoai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="so_cccd" class="form-label">Số CCCD <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('so_cccd') is-invalid @enderror" 
                                           id="so_cccd" name="so_cccd" value="{{ old('so_cccd') }}" 
                                           placeholder="Nhập số CCCD đầy đủ" required>
                                    @error('so_cccd')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ngay_sinh" class="form-label">Ngày sinh <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('ngay_sinh') is-invalid @enderror" 
                                           id="ngay_sinh" name="ngay_sinh" value="{{ old('ngay_sinh') }}" required>
                                    @error('ngay_sinh')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gioi_tinh" class="form-label">Giới tính <span class="text-danger">*</span></label>
                                    <select class="form-control @error('gioi_tinh') is-invalid @enderror" 
                                            id="gioi_tinh" name="gioi_tinh" required>
                                        <option value="">-- Chọn giới tính --</option>
                                        <option value="Nam" {{ old('gioi_tinh') == 'Nam' ? 'selected' : '' }}>Nam</option>
                                        <option value="Nu" {{ old('gioi_tinh') == 'Nu' ? 'selected' : '' }}>Nữ</option>
                                        <option value="Khac" {{ old('gioi_tinh') == 'Khac' ? 'selected' : '' }}>Khác</option>
                                    </select>
                                    @error('gioi_tinh')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="dia_chi" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('dia_chi') is-invalid @enderror" 
                                              id="dia_chi" name="dia_chi" rows="3" required>{{ old('dia_chi') }}</textarea>
                                    @error('dia_chi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Sau khi đăng ký, bạn sẽ được cấp thẻ độc giả tự động</li>
                                <li>Thẻ độc giả có hiệu lực trong 1 năm</li>
                                <li>Bạn có thể mượn tối đa 5 cuốn sách cùng lúc</li>
                                <li>Thời gian mượn mỗi cuốn sách là 14 ngày</li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> Đăng ký độc giả
                            </button>
                            <a href="{{ route('home') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập ngay</a></p>
                            <p class="mt-2"><a href="{{ route('register') }}">Đăng ký tài khoản thông thường</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

