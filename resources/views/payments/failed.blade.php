<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Thanh toán thất bại - Thư viện</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .payment-result-container {
            max-width: 800px;
            margin: 80px auto;
            padding: 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            text-align: center;
        }

        .failed-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #f44336, #ef5350);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: shake 0.5s ease-out;
        }

        .failed-icon i {
            font-size: 60px;
            color: white;
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-10px);
            }
            75% {
                transform: translateX(10px);
            }
        }

        .payment-result-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 15px;
        }

        .payment-result-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
        }

        .error-message {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: left;
        }

        .error-message strong {
            color: #c62828;
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .error-message p {
            color: #555;
            margin: 0;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0066cc, #0052a3);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0052a3, #003d7a);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,102,204,0.3);
        }

        .btn-secondary {
            background: white;
            color: #666;
            border: 2px solid #ddd;
        }

        .btn-secondary:hover {
            background: #f5f5f5;
            border-color: #999;
        }

        .support-info {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: left;
        }

        .support-info h3 {
            font-size: 18px;
            color: #1a1a1a;
            margin-bottom: 15px;
        }

        .support-info p {
            color: #555;
            margin: 8px 0;
            line-height: 1.6;
        }

        .support-info i {
            color: #0066cc;
            margin-right: 8px;
            width: 20px;
        }
    </style>
</head>
<body>
    @include('components.frontend-header')

    <div class="payment-result-container">
        <div class="failed-icon">
            <i class="fas fa-times"></i>
        </div>

        <h1 class="payment-result-title">Thanh toán thất bại</h1>
        <p class="payment-result-message">
            Rất tiếc, giao dịch của bạn không thể hoàn tất. Vui lòng thử lại sau.
        </p>

        @if(session('error'))
        <div class="error-message">
            <strong><i class="fas fa-exclamation-triangle"></i> Lý do:</strong>
            <p>{{ session('error') }}</p>
            
            @if(str_contains(session('error'), 'chữ ký') || str_contains(session('error'), 'Xác thực'))
            <div style="margin-top: 15px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                <strong style="color: #856404; display: block; margin-bottom: 8px;">💡 Hướng dẫn sửa lỗi này:</strong>
                <ol style="margin: 8px 0 0 20px; color: #856404; line-height: 1.8;">
                    <li>Mở file <code style="background: #fff; padding: 2px 6px; border-radius: 3px;">fix_vnpay_now.bat</code> trong thư mục project</li>
                    <li>Hoặc truy cập: <a href="{{ url('/vnpay-debug') }}" style="color: #0066cc; text-decoration: underline;">Trang kiểm tra VNPay</a></li>
                    <li>Xem hướng dẫn chi tiết trong file: <code style="background: #fff; padding: 2px 6px; border-radius: 3px;">README_VNPAY_FIX.txt</code></li>
                </ol>
            </div>
            @endif
        </div>
        @endif

        <div class="action-buttons">
            <a href="{{ url()->previous() }}" class="btn btn-primary">
                <i class="fas fa-redo"></i> Thử lại
            </a>
            <a href="{{ route('home') }}" class="btn btn-secondary">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
        </div>

        <div class="support-info">
            <h3><i class="fas fa-headset"></i> Cần hỗ trợ?</h3>
            <p><i class="fas fa-phone"></i> Hotline: <strong>1900-xxxx</strong></p>
            <p><i class="fas fa-envelope"></i> Email: <strong>support@library.vn</strong></p>
            <p><i class="fas fa-clock"></i> Thời gian hỗ trợ: <strong>8:00 - 22:00 hàng ngày</strong></p>
        </div>
    </div>

    @include('components.footer')
</body>
</html>

