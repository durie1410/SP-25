@extends('layouts.app')

@section('title', 'Xác Thực Email - Thư Viện Online')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-envelope"></i> Xác Thực Email</h4>
                </div>
                <div class="card-body">
                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> Link xác thực mới đã được gửi đến email của bạn!
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Cảm ơn bạn đã đăng ký!</h5>
                        <p>Trước khi tiếp tục, vui lòng kiểm tra email của bạn để xác thực địa chỉ email.</p>
                        <p>Nếu bạn không nhận được email, chúng tôi có thể gửi lại email xác thực.</p>
                    </div>

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gửi Lại Email Xác Thực
                            </button>
                            <a href="{{ route('home') }}" class="btn btn-link text-center">
                                <i class="fas fa-home"></i> Về Trang Chủ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

