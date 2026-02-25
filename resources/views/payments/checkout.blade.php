<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Thanh toán - Thư viện</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin-top: 30px;
        }

        .checkout-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-info-label {
            color: #666;
            font-weight: 500;
        }

        .order-info-value {
            color: #1a1a1a;
            font-weight: 600;
        }

        .order-summary {
            position: sticky;
            top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-size: 16px;
        }

        .summary-row.total {
            border-top: 2px solid #f0f0f0;
            margin-top: 15px;
            padding-top: 20px;
            font-size: 20px;
            font-weight: 700;
            color: #0066cc;
        }

        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    @include('components.frontend-header')

    <div class="checkout-container">
        <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 10px;">
            <i class="fas fa-credit-card"></i> Thanh toán
        </h1>
        
        <div style="color: #666; margin-bottom: 20px;">
            <a href="{{ route('home') }}" style="color: #0066cc; text-decoration: none;">Trang chủ</a>
            <span> / </span>
            <span>Thanh toán</span>
        </div>

        <div class="checkout-grid">
            {{-- Left: Payment Form --}}
            <div>
                <div class="checkout-section">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i> Thông tin đơn hàng
                    </h2>
                    
                    @if(isset($borrow))
                        <div class="order-info-row">
                            <span class="order-info-label">Mã phiếu mượn:</span>
                            <span class="order-info-value">#{{ $borrow->id }}</span>
                        </div>
                        <div class="order-info-row">
                            <span class="order-info-label">Người mượn:</span>
                            <span class="order-info-value">{{ $borrow->reader->name ?? 'N/A' }}</span>
                        </div>
                        <div class="order-info-row">
                            <span class="order-info-label">Ngày mượn:</span>
                            <span class="order-info-value">{{ $borrow->created_at->format('d/m/Y') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Payment Method Selection --}}
                @include('payments.form', [
                    'borrow_id' => $borrow->id ?? null,
                    'amount' => $amount ?? 0,
                    'payment_type' => $payment_type ?? 'deposit'
                ])
            </div>

            {{-- Right: Order Summary --}}
            <div>
                <div class="checkout-section order-summary">
                    <h2 class="section-title">
                        <i class="fas fa-receipt"></i> Tóm tắt thanh toán
                    </h2>

                    <div class="summary-row">
                        <span>Loại thanh toán:</span>
                        <span>
                            @switch($payment_type ?? 'deposit')
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

                    <div class="summary-row">
                        <span>Số tiền:</span>
                        <span>{{ number_format($amount ?? 0, 0, ',', '.') }}đ</span>
                    </div>

                    <div class="summary-row total">
                        <span>Tổng thanh toán:</span>
                        <span>{{ number_format($amount ?? 0, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.footer')
</body>
</html>

