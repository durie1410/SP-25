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

<!-- Momo QR Modal -->
<div class="modal fade" id="momoModal" tabindex="-1" aria-labelledby="momoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="momoModalLabel">Thanh toán Momo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p id="momoModalText">Vui lòng quét mã Momo để thanh toán.</p>
                <img id="momoModalQr" src="" alt="Momo QR" style="max-width:220px;" class="img-fluid my-2" />
                <div class="mt-2">
                    <strong>Số Momo:</strong> <span id="momoModalNumber"></span><br>
                    <strong>Nội dung:</strong> <span id="momoModalContent"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button id="momoPaidBtn" type="button" class="btn btn-success">Đã thanh toán</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    function initCheckout() {
        console.log('Initializing checkout...');
        
        const checkoutForm = document.getElementById('checkoutForm');
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        const paymentMethodSelect = document.getElementById('payment_method');
        const paymentInfo = document.getElementById('paymentInfo');
        const codInfo = document.getElementById('codInfo');
        const momoContent = document.getElementById('momoContent');
        
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

        // Shipping is not required for Momo UAT QR flow; set shippingFee = 0
        const totalAmountDisplay = document.getElementById('total-amount-display');
        const subtotalDisplay = document.getElementById('subtotal-display');
        let shippingFee = 0;
        let subtotal = {{ $selectedTotal }};

        function updateTotal() {
            const total = subtotal + shippingFee;
            if (totalAmountDisplay) totalAmountDisplay.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VNĐ';
            if (subtotalDisplay) subtotalDisplay.textContent = new Intl.NumberFormat('vi-VN').format(subtotal) + ' VNĐ';
        }

        // Initialize totals
        updateTotal();

    // Xử lý thay đổi phương thức thanh toán
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            if (this.value === 'momo') {
                if (paymentInfo) paymentInfo.style.display = 'block';
                if (codInfo) codInfo.style.display = 'none';
                if (momoContent) momoContent.textContent = 'Thanh toan don hang - ' + new Date().toISOString().slice(0,10);
            } else if (this.value === 'cash_on_delivery') {
                if (paymentInfo) paymentInfo.style.display = 'none';
                if (codInfo) codInfo.style.display = 'block';
            } else {
                if (paymentInfo) paymentInfo.style.display = 'none';
                if (codInfo) codInfo.style.display = 'none';
            }
        });
    } else {
        console.warn('Payment method select not found; skipping payment method change binding');
    }

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
        
        // IMPORTANT: Check if Momo is selected, show warning if not
        if (paymentMethod !== 'momo') {
            showToast('warning', 'Note: Bạn chọn ' + (paymentMethod === 'cash_on_delivery' ? 'COD' : paymentMethod) + ' - sẽ không hiển thị mã QR. Hãy chọn "Thanh toán Momo" để quét mã.');
            console.warn('⚠️ User selected:', paymentMethod, '- not Momo');
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
        
        // Debug: Log payment method trước khi gửi
        const paymentMethodValue = formData.get('payment_method');
        console.log('=== FORM SUBMITTED ===');
        console.log('payment_method value:', paymentMethodValue);
        console.log('Type:', typeof paymentMethodValue);
        console.log('Is "momo"?', paymentMethodValue === 'momo');
        console.log('All form data:');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}: ${value}`);
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
            console.log('=== RESPONSE RECEIVED ===');
            console.log('Response status:', response.status);
            console.log('Response statusText:', response.statusText);
            
            // Kiểm tra content type
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('❌ Response is NOT JSON:');
                console.error(text.substring(0, 500));
                showToast('error', 'Server response không phải JSON. Xem console để chi tiết.');
                button.innerHTML = originalText;
                button.disabled = false;
                return;
            }
            
            const data = await response.json();
            console.log('=== PARSED JSON ===');
            console.log('Full response:', JSON.stringify(data, null, 2));
            console.log('success:', data.success);
            console.log('momo_qr_url:', data.momo_qr_url);
            console.log('Has momo_qr_url?', !!data.momo_qr_url);
            console.log('momo_number:', data.momo_number);
            console.log('momo_content:', data.momo_content);
            
            if (!response.ok) {
                // Xử lý lỗi validation hoặc lỗi khác
                let errorMessage = data.message || 'Có lỗi xảy ra';
                
                // Nếu có validation errors, hiển thị chi tiết
                if (data.errors) {
                    const errorList = Object.values(data.errors).flat().join(', ');
                    errorMessage = errorList || errorMessage;
                }
                
                console.error('❌ Error response:', errorMessage);
                showToast('error', errorMessage);
                button.innerHTML = originalText;
                button.disabled = false;
                return;
            }
            
            if (data.success) {
                console.log('✓ Order created successfully!');
                console.log('Order#:', data.order_number);
                
                // DEBUG: Print entire response
                console.log('📋 ENTIRE RESPONSE OBJECT:');
                console.table(data);
                
                showToast('success', data.message || 'Đặt hàng thành công!');

                // If server returned Momo QR info, show modal instead of redirecting
                if (data.momo_qr_url) {
                    console.log('✓ Showing Momo QR modal...');
                    try {
                        const momoModal = new bootstrap.Modal(document.getElementById('momoModal'));
                        document.getElementById('momoModalQr').src = data.momo_qr_url;
                        document.getElementById('momoModalNumber').textContent = data.momo_number || '';
                        document.getElementById('momoModalContent').textContent = data.momo_content || '';
                        document.getElementById('momoModalText').textContent = data.message || 'Quét mã Momo để thanh toán';
                        momoModal.show();

                        // Handler for "Đã thanh toán" - simply redirect to orders index
                        document.getElementById('momoPaidBtn').onclick = function() {
                            window.location.href = '{{ route("orders.index") }}';
                        };
                        console.log('✓ Momo modal shown');
                    } catch (e) {
                        console.error('❌ Failed to show modal:', e);
                        window.location.href = data.redirect_url || '{{ route("orders.index") }}';
                    }
                    return;
                }
                
                // For COD: redirect
                console.log('→ Redirecting to orders index...');
                setTimeout(() => {
                    window.location.href = data.redirect_url || '{{ route("orders.index") }}';
                }, 1000);
                return;
                
                // Show error and re-enable button; do not auto-redirect to home
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('❌ Fetch Error:', error.message);
            showToast('error', 'Có lỗi kết nối: ' + error.message);
            button.innerHTML = originalText;
            button.disabled = false;
        });
    });
    
    // Thêm event listener cho nút đặt hàng để kích hoạt submit đã bind (bảo đảm không submit native)
    placeOrderBtn.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Place order button clicked! dispatching submit event...');
        if (checkoutForm) {
            const evt = new Event('submit', { cancelable: true });
            checkoutForm.dispatchEvent(evt);
        } else {
            console.error('checkoutForm not found when clicking placeOrderBtn');
        }
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

resources/views/orders/checkout.blade.php