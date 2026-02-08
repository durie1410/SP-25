@extends('layouts.app')

@section('title', 'Thanh toán - Thư Viện Online')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
<style>
    /* Force background gradient for checkout page */
    html, body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%) !important;
        background-size: 400% 400% !important;
        animation: gradientShift 15s ease infinite !important;
        min-height: 100vh !important;
    }
    
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(248, 250, 252, 0.75);
        z-index: 0;
        pointer-events: none;
    }
    
    .checkout-page {
        position: relative;
        z-index: 1;
    }
    
    /* Bỏ mũi tên lặp lại trong select */
    .checkout-page select.form-select {
        background-image: none !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
    }
    
    .checkout-page select.form-select::-ms-expand {
        display: none !important;
    }
</style>
@endpush


@section('content')
<div class="container py-5 checkout-page">

<form id="checkoutForm" method="POST" action="javascript:void(0);">
@csrf
        <div class="row">
            <!-- Thông tin khách hàng -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin khách hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                           value="{{ auth()->user()->name ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="customer_email" name="customer_email" 
                                           value="{{ auth()->user()->email ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="customer_phone" name="customer_phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Phương thức thanh toán <span class="text-danger">*</span></label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="momo" selected>Thanh toán Momo (Quét mã)</option>
                                        <option value="cash_on_delivery">Thanh toán khi nhận hàng (COD)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ giao hàng</label>
                            <div class="text-muted">Không yêu cầu địa chỉ — sử dụng thanh toán Momo (quét mã UAT) hoặc nhận hàng (COD).</div>
                            <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Nếu bạn chọn giao hàng sau, nhân viên sẽ liên hệ để lấy địa chỉ và phí vận chuyển.</small>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" 
                                      placeholder="Ghi chú thêm về đơn hàng..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Thông tin thanh toán -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-gift"></i> Chính sách vận chuyển:</h6>
                            <ul class="mb-0">
                                <li><i class="fas fa-check text-success"></i> Miễn phí vận chuyển trong vòng 5km đầu tiên</li>
                                <li><i class="fas fa-check text-success"></i> Từ km thứ 6: 5,000 VNĐ/km</li>
                                <li><i class="fas fa-check text-success"></i> Hỗ trợ khách hàng 24/7</li>
                            </ul>
                        </div>
                        
                        <div id="paymentInfo" class="mt-3" style="display: none;">
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-mobile-alt"></i> Thanh toán qua Momo</h6>
                                <p class="mb-1"><strong>Số Momo:</strong> 090-123-4567</p>
                                <p class="mb-1"><strong>Tên:</strong> Thư Viện Online</p>
                                <p class="mb-1"><strong>Nội dung chuyển tiền:</strong> <span id="momoContent"></span></p>
                                <div class="mt-2">
                                    <div class="text-muted small">QR Momo sẽ hiển thị sau khi bạn gửi đơn (UAT).</div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="codInfo" class="mt-3" style="display: none;">
                            <div class="alert alert-success">
                                <h6><i class="fas fa-truck"></i> Thông tin thanh toán khi nhận hàng:</h6>
                                <p class="mb-1"><i class="fas fa-check-circle text-success"></i> Bạn sẽ thanh toán khi nhận hàng</p>
                                <p class="mb-1"><i class="fas fa-info-circle"></i> Đơn hàng sẽ được xử lý và giao hàng trong thời gian sớm nhất</p>
                                <p class="mb-0"><i class="fas fa-shield-alt"></i> Bạn chỉ cần thanh toán khi đã kiểm tra và nhận hàng</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tóm tắt đơn hàng -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <!-- Danh sách sản phẩm -->
                        <div class="mb-3">
                            @foreach($checkoutItems as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0">{{ $item->purchasableBook->ten_sach }}</h6>
                                    <small class="text-muted">{{ $item->purchasableBook->tac_gia }}</small>
                                    <br>
                                    <small class="text-muted">Số lượng: {{ $item->quantity }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold">{{ number_format($item->total_price, 0, ',', '.') }} VNĐ</span>
                                </div>
                            </div>
                            @if(!$loop->last)
                            <hr class="my-2">
                            @endif
                            @endforeach
                        </div>

                        <!-- Tổng kết -->
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <span id="subtotal-display">{{ number_format($selectedTotal, 0, ',', '.') }} VNĐ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí vận chuyển:</span>
                                <span id="shipping-amount-display" class="text-muted">Vui lòng nhập địa chỉ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Thuế:</span>
                                <span class="text-success">Miễn phí</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Tổng cộng:</strong>
                                <strong class="text-primary" id="total-amount-display">{{ number_format($selectedTotal, 0, ',', '.') }} VNĐ</strong>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button id="placeOrderBtn" type="submit" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Thanh toán / Đặt hàng
                            </button>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại trang chủ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>


</form>
</div>

<!-- MODAL MOMO QR -->
<div class="modal fade" id="momoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thanh toán MoMo (UAT)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="fw-bold">Quét mã QR MoMo để thanh toán</p>
                <img id="momoQr" class="img-fluid mb-3" style="max-width:240px">
                <a id="momoPayUrl" href="#" target="_blank" class="btn btn-danger">
                    Mở MoMo
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('checkoutForm');

    if (!form) {
        console.error('❌ Không tìm thấy checkoutForm');
        return;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // ===== CODE CỦA BẠN GIỮ NGUYÊN TỪ ĐÂY =====
        const formData = new FormData(this);
        const paymentMethod = formData.get('payment_method');

        const orderRes = await fetch("{{ route('orders.store') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });

        const orderData = await orderRes.json();

        if (!orderData.success) {
            alert(orderData.message || 'Lỗi tạo đơn hàng');
            return;
        }

        if (paymentMethod !== 'momo') {
            window.location.href = "{{ route('orders.index') }}";
            return;
        }

        const momoRes = await fetch("{{ route('momo.create') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                order_id: orderData.order_id
            })
        });

        const momoData = await momoRes.json();

        if (!momoData.success || !momoData.payUrl) {
            alert('Không tạo được link thanh toán MoMo');
            return;
        }

        document.getElementById('momoQr').src =
            'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' +
            encodeURIComponent(momoData.payUrl);

        document.getElementById('momoPayUrl').href = momoData.payUrl;

        new bootstrap.Modal(document.getElementById('momoModal')).show();
    });
});
</script>

@endpush
