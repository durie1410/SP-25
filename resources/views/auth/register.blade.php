@extends('layouts.app')

@section('title', 'Đăng Ký - Thư Viện Online')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4><i class="fas fa-user-plus"></i> Đăng Ký</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Đăng Ký
                            </button>
                        </div>
                    </form>

                    <!-- Divider -->
                    <div class="text-center my-3">
                        <span class="text-muted">hoặc</span>
                    </div>

                    <!-- Google OAuth Button -->
                    <div class="d-grid">
                        <a href="{{ route('auth.google') }}" class="btn btn-outline-danger">
                            <i class="fab fa-google"></i> Đăng ký với Google
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


