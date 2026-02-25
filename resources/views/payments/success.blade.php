<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Thanh toán thành công - Thư viện</title>
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

        .success-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon i {
            font-size: 60px;
            color: white;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
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

        .payment-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: left;
        }

        .payment-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .payment-detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #555;
        }

        .detail-value {
            color: #1a1a1a;
            font-weight: 500;
        }

        .detail-value.amount {
            font-size: 24px;
            color: #4CAF50;
            font-weight: 700;
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
            color: #0066cc;
            border: 2px solid #0066cc;
        }

        .btn-secondary:hover {
            background: #0066cc;
            color: white;
        }

        .payment-info-note {
            margin-top: 20px;
            padding: 15px;
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            border-radius: 4px;
            text-align: left;
            font-size: 14px;
            color: #1976D2;
        }
    </style>
</head>
<body>
    @include('components.frontend-header')

    <div class="payment-result-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <h1 class="payment-result-title">Thanh toán thành công!</h1>
        <p class="payment-result-message">
            Giao dịch của bạn đã được xử lý thành công. Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.
        </p>

        <div class="payment-details">
            <div class="payment-detail-row">
                <span class="detail-label">Mã giao dịch:</span>
                <span class="detail-value">{{ $payment->transaction_code }}</span>
            </div>

            <div class="payment-detail-row">
                <span class="detail-label">Loại thanh toán:</span>
                <span class="detail-value">
                    @switch($payment->payment_type)
                        @case('deposit')
                            Tiền cọc
                            @break
                        @case('borrow_fee')
                            Tiền thuê sách
                            @break
                        @case('shipping_fee')
                            Phí vận chuyển
                            @break
                        @case('damage_fee')
                            Phí đền bù
                            @break
                        @default
                            Khác
                    @endswitch
                </span>
            </div>

            <div class="payment-detail-row">
                <span class="detail-label">Phương thức:</span>
                <span class="detail-value">
                    <i class="fas fa-credit-card"></i> VnPay
                </span>
            </div>

            <div class="payment-detail-row">
                <span class="detail-label">Thời gian:</span>
                <span class="detail-value">{{ $payment->created_at->format('d/m/Y H:i:s') }}</span>
            </div>

            <div class="payment-detail-row">
                <span class="detail-label">Số tiền:</span>
                <span class="detail-value amount">{{ number_format($payment->amount, 0, ',', '.') }}đ</span>
            </div>

            @if($payment->borrow)
            <div class="payment-detail-row">
                <span class="detail-label">Phiếu mượn:</span>
                <span class="detail-value">
                    #{{ $payment->borrow->id }} - {{ $payment->borrow->reader->name ?? 'N/A' }}
                </span>
            </div>
            @endif
        </div>

        <div class="payment-info-note">
            <i class="fas fa-info-circle"></i>
            <strong>Lưu ý:</strong> Vui lòng lưu lại mã giao dịch để tra cứu và đối chiếu. 
            Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi.
        </div>

        <div class="action-buttons">
            @if($payment->borrow)
                <a href="{{ route('orders.index') }}" class="btn btn-primary">
                    <i class="fas fa-file-alt"></i> Xem phiếu mượn
                </a>
            @endif
            <a href="{{ route('home') }}" class="btn btn-secondary">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
        </div>
    </div>

    @include('components.footer')
</body>
</html>

