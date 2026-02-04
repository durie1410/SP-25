@extends('layouts.app')

@section('title', 'Đăng Nhập / Đăng Ký - Thư Viện Online')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

    *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
        text-decoration: none;
        list-style: none;
    }

    body{
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: linear-gradient(90deg, #e2e2e2, #c9d6ff);
    }

    main {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .container{
        position: relative;
        width: 850px;
        height: 550px;
        background: #fff;
        margin: 20px;
        border-radius: 30px;
        box-shadow: 0 0 30px rgba(0, 0, 0, .2);
        overflow: hidden;
    }

    .container h1{
        font-size: 36px;
        font-weight: 700;
        color: #000;
        margin: 0 0 10px 0;
        padding-top: 10px;
    }

    .container p{
        font-size: 13px;
        color: #888;
        margin: 10px 0 15px 0;
        font-weight: 400;
    }

    form{ width: 100%; }

    .form-box{
        position: absolute;
        right: 0;
        width: 50%;
        height: 100%;
        background: #fff;
        display: flex;
        align-items: center;
        color: #333;
        text-align: center;
        padding: 40px 35px 35px 35px;
        z-index: 1;
        transition: .6s ease-in-out 1.2s, visibility 0s 1s;
        overflow: hidden;
    }

    .container.active .form-box{ right: 50%; }

    .form-box.register{ visibility: hidden; }

    .container.active .form-box.register{ visibility: visible; }

    .form-box.login{ visibility: visible; }

    .container.active .form-box.login{ visibility: hidden; }

    .input-box{
        position: relative;
        margin: 20px 0;
    }

    .input-box input{
        width: 100%;
        padding: 10px 45px 10px 15px;
        background: #eee;
        border-radius: 8px;
        border: none;
        outline: none;
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }

    .input-box input::placeholder{
        color: #888;
        font-weight: 400;
        font-size: 14px;
    }

    .input-box i{
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        color: #888;
    }

    .forgot-link{ margin: -10px 0 10px; }

    .forgot-link a{
        font-size: 14.5px;
        color: #333;
    }

    .btn{
        width: 100%;
        height: 42px;
        background: #7494ec;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, .1);
        border: none;
        cursor: pointer;
        font-size: 15px;
        color: #fff;
        font-weight: 700;
        transition: background 0.3s;
    }

    .btn:hover{
        background: #5a7de8;
    }

    .social-icons{
        display: flex;
        justify-content: center;
        margin-top: 15px;
        margin-bottom: 10px;
    }

    .social-icons a{
        display: inline-flex;
        padding: 8px;
        border: 2px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        color: #333;
        margin: 0 6px;
        transition: all 0.3s;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
    }

    .social-icons a:hover{
        border-color: #7494ec;
        color: #7494ec;
        transform: translateY(-2px);
    }

    .social-text{
        font-size: 13px;
        color: #888;
        margin-top: 15px;
        font-weight: 400;
    }

    .toggle-box{
        position: absolute;
        width: 100%;
        height: 100%;
    }

    .toggle-box::before{
        content: '';
        position: absolute;
        left: -250%;
        width: 300%;
        height: 100%;
        background: #7494ec;
        border-radius: 150px;
        z-index: 2;
        transition: 1.8s ease-in-out;
    }

    .container.active .toggle-box::before{ left: 50%; }

    .toggle-panel{
        position: absolute;
        width: 50%;
        height: 100%;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 2;
        transition: .6s ease-in-out;
    }

    .toggle-panel.toggle-left{ 
        left: 0;
        transition-delay: 1.2s; 
    }

    .container.active .toggle-panel.toggle-left{
        left: -50%;
        transition-delay: .6s;
    }

    .toggle-panel.toggle-right{ 
        right: -50%;
        transition-delay: .6s;
    }

    .container.active .toggle-panel.toggle-right{
        right: 0;
        transition-delay: 1.2s;
    }

    .toggle-panel h1{
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .toggle-panel p{ 
        margin-bottom: 20px;
        font-size: 15px;
        font-weight: 400;
    }

    .toggle-panel .btn{
        width: 160px;
        height: 46px;
        background: transparent;
        border: 2px solid #fff;
        box-shadow: none;
        color: #fff;
    }

    .toggle-panel .btn:hover{
        background: rgba(255, 255, 255, 0.1);
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 13px;
        margin-top: 5px;
        text-align: left;
    }

    .input-box input.is-invalid {
        border: 1px solid #dc3545;
    }

    @media screen and (max-width: 650px){
        .container{ height: calc(100vh - 40px); }

        .form-box{
            bottom: 0;
            width: 100%;
            height: 70%;
        }

        .container.active .form-box{
            right: 0;
            bottom: 30%;
        }

        .toggle-box::before{
            left: 0;
            top: -270%;
            width: 100%;
            height: 300%;
            border-radius: 20vw;
        }

        .container.active .toggle-box::before{
            left: 0;
            top: 70%;
        }

        .container.active .toggle-panel.toggle-left{
            left: 0;
            top: -30%;
        }

        .toggle-panel{ 
            width: 100%;
            height: 30%;
        }

        .toggle-panel.toggle-left{ top: 0; }

        .toggle-panel.toggle-right{
            right: 0;
            bottom: -30%;
        }

        .container.active .toggle-panel.toggle-right{ bottom: 0; }
    }

    @media screen and (max-width: 400px){
        .form-box { padding: 20px; }

        .toggle-panel h1{
        font-size: 32px;
        font-weight: 700;
    }
    }
</style>
@endpush

@section('content')
<div class="container" id="loginRegisterContainer">
    <!-- Form đăng nhập -->
    <div class="form-box login">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <h1>Đăng Nhập</h1>
            <p>Nhập thông tin của bạn để đăng nhập</p>
            
            <div class="input-box">
                <input type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="Email" 
                       class="@error('email') is-invalid @enderror"
                       required>
                <i class="fas fa-envelope"></i>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="input-box">
                <input type="password" 
                       name="password" 
                       placeholder="Mật khẩu" 
                       class="@error('password') is-invalid @enderror"
                       required>
                <i class="fas fa-lock"></i>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="forgot-link">
                <a href="#">Quên mật khẩu?</a>
            </div>
            
            <button type="submit" class="btn">Đăng Nhập</button>
            
            <p class="social-text">hoặc đăng nhập bằng</p>
            
            <div class="social-icons">
                <a href="{{ route('auth.google') }}"><i class="fab fa-google"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-github"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </form>
    </div>

    <!-- Form đăng ký -->
    <div class="form-box register">
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <h1>Đăng Ký</h1>
            <p>Nhập thông tin của bạn để đăng ký</p>
            
            <div class="input-box">
                <input type="text" 
                       name="name" 
                       value="{{ old('name') }}" 
                       placeholder="Tên đăng nhập" 
                       class="@error('name') is-invalid @enderror"
                       required>
                <i class="fas fa-user"></i>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="input-box">
                <input type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="Email" 
                       class="@error('email') is-invalid @enderror"
                       required>
                <i class="fas fa-envelope"></i>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="input-box">
                <input type="password" 
                       name="password" 
                       placeholder="Mật khẩu" 
                       class="@error('password') is-invalid @enderror"
                       required>
                <i class="fas fa-lock"></i>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="input-box">
                <input type="password" 
                       name="password_confirmation" 
                       placeholder="Xác nhận mật khẩu" 
                       required>
                <i class="fas fa-lock"></i>
            </div>
            
            <button type="submit" class="btn">Đăng Ký</button>
            
            <p class="social-text">hoặc đăng ký bằng</p>
            
            <div class="social-icons">
                <a href="{{ route('auth.google') }}"><i class="fab fa-google"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-github"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </form>
    </div>

    <!-- Toggle box với panel chào mừng -->
    <div class="toggle-box">
        <!-- Panel bên trái (hiện khi đăng nhập) -->
        <div class="toggle-panel toggle-left">
            <h1>Chào Mừng!</h1>
            <p>Bạn chưa có tài khoản? Đăng ký ngay để bắt đầu</p>
            <button class="btn register-btn">Đăng Ký</button>
        </div>

        <!-- Panel bên phải (hiện khi đăng ký) -->
        <div class="toggle-panel toggle-right">
            <h1>Chào Mừng Trở Lại!</h1>
            <p>Bạn đã có tài khoản? Đăng nhập ngay</p>
            <button class="btn login-btn">Đăng Nhập</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.container');
        const registerBtn = document.querySelector('.register-btn');
        const loginBtn = document.querySelector('.login-btn');

        if (registerBtn) {
            registerBtn.addEventListener('click', () => {
                container.classList.add('active');
            });
        }

        if (loginBtn) {
            loginBtn.addEventListener('click', () => {
                container.classList.remove('active');
            });
        }

        // Tự động chuyển sang form đăng ký nếu có flag hoặc lỗi từ form đăng ký
        @php
            $shouldShowRegister = false;
            if (isset($showRegister) && $showRegister) {
                $shouldShowRegister = true;
            } elseif ($errors->has('name') || ($errors->has('email') && !$errors->has('password')) || old('name') || (old('email') && !old('password'))) {
                $shouldShowRegister = true;
            }
        @endphp
        @if($shouldShowRegister)
            if (container) {
                container.classList.add('active');
            }
        @endif
    });
</script>

@endsection

