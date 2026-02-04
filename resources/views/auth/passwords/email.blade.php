@extends('layouts.app')

@section('title', 'Quên Mật Khẩu - Thư Viện Online')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-key"></i> Quên Mật Khẩu</h4>
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p class="text-muted">Nhập email của bạn để nhận link đặt lại mật khẩu.</p>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gửi Link Đặt Lại Mật Khẩu
                            </button>
                            <a href="{{ route('login') }}" class="btn btn-link text-center">
                                <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

