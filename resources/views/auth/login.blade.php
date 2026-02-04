@extends('layouts.app')

@section('title', 'Đăng Nhập - Thư Viện Online')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

    body {
        background: #000 !important;
        font-family: "Poppins", sans-serif;
    }

    main {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
        padding: 0;
    }

    .login-container {
        width: 100%;
        max-width: 450px;
        margin: 0 auto;
        padding: 20px;
    }

    .login-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(102, 126, 234, 0.2);
    }

    .login-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 30px;
        text-align: center;
        color: #fff;
        position: relative;
    }

    .login-header h4 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .login-header i {
        font-size: 24px;
    }

    .login-body {
        padding: 40px 30px;
        background: #fff;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
        font-size: 14px;
    }

    .form-group input[type="email"],
    .form-group input[type="password"] {
        width: 100%;
        padding: 15px 20px;
        border: none;
        background: #fff;
        border-radius: 12px;
        font-size: 16px;
        color: #333;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }

    .form-group input[type="email"]:focus,
    .form-group input[type="password"]:focus {
        outline: none;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .form-check {
        margin-bottom: 25px;
        display: flex;
        align-items: center;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        cursor: pointer;
    }

    .form-check-label {
        color: #333;
        font-size: 14px;
        cursor: pointer;
        margin: 0;
    }

    .btn-login {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .divider {
        text-align: center;
        margin: 30px 0;
        color: #888;
        font-size: 14px;
        position: relative;
    }

    .divider::before,
    .divider::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 40%;
        height: 1px;
        background: #ddd;
    }

    .divider::before {
        left: 0;
    }

    .divider::after {
        right: 0;
    }

    .btn-google {
        width: 100%;
        padding: 15px;
        background: #fff;
        border: 2px solid #ddd;
        border-radius: 12px;
        color: #333;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .btn-google:hover {
        border-color: #db4437;
        color: #db4437;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(219, 68, 55, 0.2);
    }

    .btn-google i {
        font-size: 20px;
    }

    .register-links {
        text-align: center;
        margin-top: 30px;
        color: #333;
        font-size: 14px;
    }

    .register-links a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        margin: 0 5px;
    }

    .register-links a:hover {
        text-decoration: underline;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 13px;
        margin-top: 5px;
    }

    .form-group input.is-invalid {
        border: 1px solid #dc3545;
    }

    @media (max-width: 576px) {
        .login-container {
            padding: 15px;
        }

        .login-body {
            padding: 30px 20px;
        }

        .login-header {
            padding: 25px 20px;
        }

        .login-header h4 {
            font-size: 24px;
        }
    }
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h4>
                <span>Đăng Nhập</span>
                <i class="fas fa-arrow-right"></i>
            </h4>
        </div>
        <div class="login-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Ghi nhớ đăng nhập
                    </label>
                </div>

                <button type="submit" class="btn-login">
                    <span>Đăng Nhập</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="divider">hoặc</div>

            <a href="{{ route('auth.google') }}" class="btn-google">
                <i class="fab fa-google"></i>
                <span>Đăng nhập với Google</span>
            </a>

            <div class="register-links">
                <p>Chưa có tài khoản? 
                    <a href="{{ route('register') }}">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection



