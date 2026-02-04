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
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-credit-card text-primary"></i> Thanh toán</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Thanh toán</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <form id="checkoutForm" method="POST" action="{{ route('orders.store') }}" novalidate>
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
                                        <option value="">Chọn phương thức thanh toán</option>
                                        <option value="cash_on_delivery">Thanh toán khi nhận hàng</option>
                                        <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="customer_address" class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="customer_address" name="customer_address" rows="3" 
                                      placeholder="Nhập địa chỉ chi tiết để giao hàng..." required></textarea>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Hệ thống sẽ tự động tính phí vận chuyển dựa trên khoảng cách từ địa chỉ của bạn đến thư viện.
                            </small>
                            <div id="shipping-info" class="mt-2" style="display: none;">
                                <div class="alert alert-info mb-0 py-2">
                                    <small>
                                        <i class="fas fa-map-marker-alt"></i> Khoảng cách: <span id="shipping-distance">0</span> km | 
                                        Phí vận chuyển: <span id="shipping-fee-display" class="fw-bold">0</span> VNĐ
                                    </small>
                                </div>
                            </div>
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
                                <h6><i class="fas fa-bank"></i> Thông tin chuyển khoản:</h6>
                                <p class="mb-1"><strong>Ngân hàng:</strong> Vietcombank</p>
                                <p class="mb-1"><strong>Số tài khoản:</strong> 1234567890</p>
                                <p class="mb-1"><strong>Chủ tài khoản:</strong> Thư Viện Online</p>
                                <p class="mb-0"><strong>Nội dung:</strong> <span id="transferContent"></span></p>
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
                            <button type="submit" class="btn btn-primary btn-lg" id="placeOrderBtn">
                                <i class="fas fa-shopping-cart"></i> Đặt hàng
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


<!-- Toast thông báo -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="orderToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="fas fa-shopping-cart text-success me-2"></i>
            <strong class="me-auto">Thông báo</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            <!-- Nội dung thông báo sẽ được thêm vào đây -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Đảm bảo handler được attach ngay lập tức, không đợi DOMContentLoaded
(function() {
    function initCheckout() {
        console.log('Initializing checkout...');
        
        const checkoutForm = document.getElementById('checkoutForm');
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        const paymentMethodSelect = document.getElementById('payment_method');
        const paymentInfo = document.getElementById('paymentInfo');
        const codInfo = document.getElementById('codInfo');
        const transferContent = document.getElementById('transferContent');
        
        // Kiểm tra các element có tồn tại không
        if (!checkoutForm || !placeOrderBtn) {
            console.log('Elements not ready yet, waiting...');
            return false;
        }
        
        console.log('All elements found:', {
            form: !!checkoutForm,
            button: !!placeOrderBtn,
            paymentMethod: !!paymentMethodSelect
        });
        
        // Khởi tạo toast
        let orderToast;
        try {
            const toastElement = document.getElementById('orderToast');
            if (toastElement) {
                orderToast = new bootstrap.Toast(toastElement);
            }
        } catch (e) {
            console.error('Error initializing toast:', e);
        }

        // Tính phí vận chuyển tự động
        const customerAddressInput = document.getElementById('customer_address');
        const shippingInfo = document.getElementById('shipping-info');
        const shippingDistance = document.getElementById('shipping-distance');
        const shippingFeeDisplay = document.getElementById('shipping-fee-display');
        const shippingAmountDisplay = document.getElementById('shipping-amount-display');
        const totalAmountDisplay = document.getElementById('total-amount-display');
        const subtotalDisplay = document.getElementById('subtotal-display');
        
        let shippingFee = 0;
        let subtotal = {{ $selectedTotal }};
        let calculateTimeout = null;

        // Khởi tạo hiển thị ban đầu
        if (customerAddressInput && customerAddressInput.value.trim().length >= 10) {
            // Nếu đã có địa chỉ, tính ngay
            calculateShippingFee(customerAddressInput.value.trim());
        } else {
            // Nếu chưa có địa chỉ, hiển thị mặc định
            shippingAmountDisplay.textContent = 'Vui lòng nhập địa chỉ';
            shippingAmountDisplay.className = 'text-muted';
            updateTotal();
        }

        // Hàm tính phí vận chuyển
        function calculateShippingFee(address) {
            if (!address || address.trim().length < 10) {
                shippingInfo.style.display = 'none';
                shippingFee = 0;
                shippingAmountDisplay.textContent = 'Vui lòng nhập địa chỉ';
                shippingAmountDisplay.className = 'text-muted';
                updateTotal();
                return;
            }

            shippingAmountDisplay.textContent = 'Đang tính...';
            shippingAmountDisplay.className = 'text-info';

            fetch('/api/shipping/calculate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                   document.querySelector('input[name="_token"]')?.value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ address: address })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    shippingFee = data.shipping_fee || 0;
                    const distance = data.distance || 0;
                    
                    shippingDistance.textContent = distance.toFixed(2);
                    shippingFeeDisplay.textContent = new Intl.NumberFormat('vi-VN').format(shippingFee);
                    shippingAmountDisplay.textContent = shippingFee > 0 
                        ? new Intl.NumberFormat('vi-VN').format(shippingFee) + ' VNĐ'
                        : 'Miễn phí';
                    shippingAmountDisplay.className = shippingFee > 0 ? 'text-primary' : 'text-success';
                    shippingInfo.style.display = 'block';
                } else {
                    shippingFee = 0;
                    shippingAmountDisplay.textContent = 'Không thể tính phí';
                    shippingAmountDisplay.className = 'text-warning';
                    shippingInfo.style.display = 'none';
                }
                updateTotal();
            })
            .catch(error => {
                console.error('Error calculating shipping:', error);
                shippingFee = 0;
                shippingAmountDisplay.textContent = 'Lỗi tính phí';
                shippingAmountDisplay.className = 'text-danger';
                shippingInfo.style.display = 'none';
                updateTotal();
            });
        }

        // Hàm cập nhật tổng tiền
        function updateTotal() {
            const total = subtotal + shippingFee;
            totalAmountDisplay.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VNĐ';
        }

        // Lắng nghe sự kiện nhập địa chỉ (debounce để tránh gọi API quá nhiều)
        if (customerAddressInput) {
            customerAddressInput.addEventListener('input', function() {
                clearTimeout(calculateTimeout);
                const address = this.value.trim();
                
                // Chờ 1 giây sau khi người dùng ngừng nhập
                calculateTimeout = setTimeout(() => {
                    calculateShippingFee(address);
                }, 1000);
            });

            // Tính phí ngay khi blur (rời khỏi ô input)
            customerAddressInput.addEventListener('blur', function() {
                clearTimeout(calculateTimeout);
                calculateShippingFee(this.value.trim());
            });
        }

    // Xử lý thay đổi phương thức thanh toán
    paymentMethodSelect.addEventListener('change', function() {
        if (this.value === 'bank_transfer') {
            paymentInfo.style.display = 'block';
            codInfo.style.display = 'none';
            transferContent.textContent = 'Thanh toan don hang - ' + new Date().toISOString().slice(0,10);
        } else if (this.value === 'cash_on_delivery') {
            paymentInfo.style.display = 'none';
            codInfo.style.display = 'block';
        } else {
            paymentInfo.style.display = 'none';
            codInfo.style.display = 'none';
        }
    });

    // Xử lý submit form
    checkoutForm.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        console.log('Form submitted! Event prevented.');
        
        const button = placeOrderBtn;
        const originalText = button.innerHTML;
        
        // Kiểm tra validation trước khi submit
        const customerName = document.getElementById('customer_name').value.trim();
        const customerEmail = document.getElementById('customer_email').value.trim();
        const paymentMethod = document.getElementById('payment_method').value;
        
        // Validate các trường bắt buộc
        if (!customerName) {
            showToast('error', 'Vui lòng nhập họ và tên');
            document.getElementById('customer_name').focus();
            return;
        }
        
        if (!customerEmail) {
            showToast('error', 'Vui lòng nhập email');
            document.getElementById('customer_email').focus();
            return;
        }
        
        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(customerEmail)) {
            showToast('error', 'Email không hợp lệ');
            document.getElementById('customer_email').focus();
            return;
        }
        
        if (!paymentMethod) {
            showToast('error', 'Vui lòng chọn phương thức thanh toán');
            document.getElementById('payment_method').focus();
            return;
        }
        
        // Kiểm tra sản phẩm trước khi submit - sử dụng dữ liệu từ backend
        const checkoutItemsCount = {{ $checkoutItems->count() ?? 0 }};
        
        // Kiểm tra xem có sản phẩm được chọn không
        if (checkoutItemsCount === 0) {
            showToast('error', 'Không có sản phẩm nào được chọn. Vui lòng quay lại và chọn sản phẩm.');
            setTimeout(() => {
                window.location.href = '{{ route("home") }}';
            }, 2000);
            return;
        }
        
        // Hiển thị loading
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        button.disabled = true;
        
        // Lấy dữ liệu form
        const formData = new FormData(this);
        
        // Log form data để debug
        console.log('Form data:');
        for (let [key, value] of formData.entries()) {
            console.log(key, ':', value);
        }
        
        // Lấy CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                          document.querySelector('input[name="_token"]')?.value;
        
        console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');
        
        if (!csrfToken) {
            console.error('CSRF token not found!');
            showToast('error', 'Không tìm thấy token bảo mật. Vui lòng tải lại trang.');
            button.innerHTML = originalText;
            button.disabled = false;
            return;
        }
        
        const orderUrl = '{{ route("orders.store") }}';
        console.log('Sending request to:', orderUrl);
        
        // Gửi request
        fetch(orderUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async response => {
            console.log('Response received!');
            console.log('Response status:', response.status);
            console.log('Response statusText:', response.statusText);
            console.log('Response headers:', Object.fromEntries(response.headers.entries()));
            
            // Kiểm tra content type
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Response is not JSON:', text);
                console.error('Response length:', text.length);
                showToast('error', 'Phản hồi từ server không đúng định dạng. Vui lòng thử lại. Chi tiết: ' + text.substring(0, 200));
                button.innerHTML = originalText;
                button.disabled = false;
                return;
            }
            
            const data = await response.json();
            console.log('Response data:', data);
            
            if (!response.ok) {
                // Xử lý lỗi validation hoặc lỗi khác
                let errorMessage = data.message || 'Có lỗi xảy ra';
                
                // Nếu có validation errors, hiển thị chi tiết
                if (data.errors) {
                    const errorList = Object.values(data.errors).flat().join(', ');
                    errorMessage = errorList || errorMessage;
                }
                
                console.error('Error response:', errorMessage);
                showToast('error', errorMessage);
                
                // Nếu không có sản phẩm, redirect về trang chủ
                if (data.message && (data.message.includes('trống') || data.message.includes('sản phẩm'))) {
                    if (data.redirect_url) {
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 2000);
                    } else {
                        setTimeout(() => {
                            window.location.href = '{{ route("home") }}';
                        }, 2000);
                    }
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
                return;
            }
            
            if (data.success) {
                console.log('Order created successfully!');
                console.log('Order number:', data.order_number);
                console.log('Redirect URL:', data.redirect_url);
                showToast('success', data.message || 'Đặt hàng thành công!');
                
                // Redirect ngay lập tức về trang lịch sử mua hàng
                const redirectUrl = data.redirect_url || '{{ route("orders.index") }}';
                console.log('Redirecting to:', redirectUrl);
                
                // Redirect ngay lập tức, không đợi
                window.location.href = redirectUrl;
            } else {
                console.error('Order creation failed:', data.message);
                showToast('error', data.message || 'Có lỗi xảy ra khi đặt hàng');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            console.error('Error name:', error.name);
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            showToast('error', 'Có lỗi xảy ra khi kết nối đến server: ' + error.message);
            button.innerHTML = originalText;
            button.disabled = false;
        });
    });
    
    // Thêm event listener cho nút đặt hàng để log
    placeOrderBtn.addEventListener('click', function(e) {
        console.log('Place order button clicked!');
        // Form submit handler sẽ xử lý, không cần preventDefault ở đây
    });

        // Hàm hiển thị toast
        function showToast(type, message) {
            try {
                console.log('Showing toast:', type, message);
                const toastElement = document.getElementById('orderToast');
                const toastMessage = document.getElementById('toastMessage');
                
                if (!toastElement || !toastMessage) {
                    console.error('Toast elements not found!');
                    if(window.showGlobalModal) window.showGlobalModal('Thông báo', message, 'info');
                    else if(window.alert) window.alert('Thông báo', message);
                    else alert(message); // Fallback to alert
                    return;
                }
                
                toastMessage.textContent = message;
                
                const toastHeader = toastElement.querySelector('.toast-header');
                if (toastHeader) {
                    const icon = toastHeader.querySelector('i');
                    if (icon) {
                        if (type === 'success') {
                            icon.className = 'fas fa-check-circle text-success me-2';
                            toastElement.classList.remove('bg-danger');
                        } else {
                            icon.className = 'fas fa-exclamation-circle text-danger me-2';
                            toastElement.classList.add('bg-danger');
                        }
                    }
                }
                
                if (orderToast) {
                    orderToast.show();
                } else {
                    console.error('Toast instance not found!');
                    if(window.showGlobalModal) window.showGlobalModal('Thông báo', message, 'info');
                    else if(window.alert) window.alert('Thông báo', message);
                    else alert(message); // Fallback to alert
                }
            } catch (error) {
                console.error('Error showing toast:', error);
                alert(message); // Fallback to alert
            }
        }
        
        return true; // Đã khởi tạo thành công
    }
    
    // Thử khởi tạo ngay lập tức
    if (document.readyState === 'loading') {
        // DOM chưa load xong, đợi DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            if (!initCheckout()) {
                // Nếu vẫn chưa sẵn sàng, thử lại sau 100ms
                setTimeout(function() {
                    initCheckout();
                }, 100);
            }
        });
    } else {
        // DOM đã load xong, khởi tạo ngay
        if (!initCheckout()) {
            // Nếu chưa sẵn sàng, thử lại sau 100ms
            setTimeout(function() {
                initCheckout();
            }, 100);
        }
    }
})();
</script>
@endpush

