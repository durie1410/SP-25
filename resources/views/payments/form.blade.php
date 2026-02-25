{{-- 
    VnPay Payment Form Component
    Sử dụng: @include('payments.form', ['borrow_id' => $borrow->id, 'amount' => $totalAmount, 'payment_type' => 'deposit'])
--}}

<div class="vnpay-payment-form">
    <style>
        .vnpay-payment-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin: 20px 0;
        }

        .payment-form-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .payment-form-header img {
            height: 40px;
            margin-right: 15px;
        }

        .payment-form-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
        }

        .payment-amount-display {
            background: linear-gradient(135deg, #0066cc, #0052a3);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
        }

        .payment-amount-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .payment-amount-value {
            font-size: 36px;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .bank-select-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .bank-option {
            position: relative;
        }

        .bank-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .bank-option label {
            display: block;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }

        .bank-option input[type="radio"]:checked + label {
            border-color: #0066cc;
            background: #e3f2fd;
            box-shadow: 0 2px 8px rgba(0,102,204,0.2);
        }

        .bank-option label:hover {
            border-color: #0066cc;
            transform: translateY(-2px);
        }

        .bank-logo {
            height: 30px;
            margin-bottom: 5px;
        }

        .bank-name {
            font-size: 12px;
            color: #555;
            font-weight: 500;
        }

        .payment-note {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #856404;
        }

        .payment-note i {
            margin-right: 8px;
        }

        .btn-submit-payment {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #0066cc, #0052a3);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit-payment:hover {
            background: linear-gradient(135deg, #0052a3, #003d7a);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,102,204,0.3);
        }

        .btn-submit-payment:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .security-badge {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .security-badge i {
            color: #4CAF50;
            margin-right: 5px;
        }

        .security-badge span {
            font-size: 13px;
            color: #666;
        }
    </style>

    <form action="{{ route('vnpay.create-payment') }}" method="POST" id="vnpayPaymentForm">
        @csrf
        
        <input type="hidden" name="borrow_id" value="{{ $borrow_id ?? '' }}">
        <input type="hidden" name="borrow_item_id" value="{{ $borrow_item_id ?? '' }}">
        <input type="hidden" name="amount" value="{{ $amount ?? 0 }}">
        <input type="hidden" name="payment_type" value="{{ $payment_type ?? 'deposit' }}">

        <div class="payment-form-header">
            <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png" alt="VnPay">
            <h3 class="payment-form-title">Thanh toán qua VnPay</h3>
        </div>

        <div class="payment-amount-display">
            <div class="payment-amount-label">Số tiền thanh toán</div>
            <div class="payment-amount-value">{{ number_format($amount ?? 0, 0, ',', '.') }}đ</div>
        </div>

        <div class="payment-note">
            <i class="fas fa-info-circle"></i>
            <strong>Lưu ý:</strong> Bạn sẽ được chuyển sang cổng thanh toán VnPay để hoàn tất giao dịch.
            Giao dịch an toàn và bảo mật.
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-university"></i> Chọn ngân hàng (tùy chọn)
            </label>
            <div class="bank-select-grid">
                <div class="bank-option">
                    <input type="radio" name="bank_code" value="" id="bank_all" checked>
                    <label for="bank_all">
                        <div class="bank-name">Tất cả</div>
                    </label>
                </div>
                <div class="bank-option">
                    <input type="radio" name="bank_code" value="VNPAYQR" id="bank_vnpayqr">
                    <label for="bank_vnpayqr">
                        <div class="bank-name">VnPay QR</div>
                    </label>
                </div>
                <div class="bank-option">
                    <input type="radio" name="bank_code" value="VNBANK" id="bank_vnbank">
                    <label for="bank_vnbank">
                        <div class="bank-name">ATM/Tài khoản</div>
                    </label>
                </div>
                <div class="bank-option">
                    <input type="radio" name="bank_code" value="INTCARD" id="bank_intcard">
                    <label for="bank_intcard">
                        <div class="bank-name">Thẻ quốc tế</div>
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-submit-payment" id="btnSubmitPayment">
            <i class="fas fa-lock"></i>
            <span>Thanh toán ngay</span>
        </button>

        <div class="security-badge">
            <i class="fas fa-shield-alt"></i>
            <span>Giao dịch được bảo mật bởi VnPay</span>
        </div>
    </form>

    <script>
        document.getElementById('vnpayPaymentForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnSubmitPayment');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Đang xử lý...</span>';
        });
    </script>
</div>

