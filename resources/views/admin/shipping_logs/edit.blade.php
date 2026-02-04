@extends('layouts.admin')

@section('title', 'Sửa đơn hàng')

@section('content')
<style>
/* Thiết lập cơ bản */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f6f8;
}

.order-detail-wrapper {
    display: flex;
    justify-content: center;
    padding: 20px;
    background-color: #f4f6f8;
}

.invoice-container {
    width: 100%;
    max-width: 9000px;
    background-color: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    padding: 30px;
}

/* Phần tiêu đề */
.invoice-header {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    padding-bottom: 10px;
    margin-bottom: 20px;
    border-bottom: 1px solid #f0f0f0;
}

/* --- Phần Chi tiết đơn hàng --- */
.detail-section {
    padding: 0;
    margin-bottom: 30px;
}

.detail-grid {
    display: grid;
    /* Chia thành 2 cột, mỗi cột là cặp Label - Value */
    grid-template-columns: 1fr 1fr;
    gap: 10px 20px; /* Khoảng cách giữa các item */
}

.detail-item {
    display: flex;
    /* label và value nằm trên 2 dòng */
    flex-direction: column;
    padding: 8px 0;
    /* Dùng border-bottom để ngăn cách giữa các hàng chi tiết */
    border-bottom: 1px solid #f9f9f9;
}

/* Item có độ rộng đầy đủ (dùng cho Ghi chú) */
.detail-item.full-width {
    grid-column: 1 / span 2; /* Chiếm cả 2 cột */
    border-bottom: none; /* Bỏ border nếu là item cuối cùng của section */
}

.label {
    font-size: 13px;
    color: #6a6a6a;
    margin-bottom: 2px;
}

.value {
    font-size: 15px;
    color: #333;
    font-weight: 500;
}

/* Status badge */
.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
}

.status-delivering {
    background-color: #cfe2ff;
    color: #084298;
}

.status-delivered {
    background-color: #d1e7dd;
    color: #0f5132;
}

.status-cancelled {
    background-color: #f8d7da;
    color: #842029;
}

.status-processing {
    background-color: #fff3cd;
    color: #856404;
}

.status-unpaid {
    background-color: #fd7e14;
    color: #ffffff;
}

/* --- Phần Sản phẩm --- */
.product-header {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-top: 20px;
    margin-bottom: 15px;
}

.product-section table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 14px;
}

.product-section th, .product-section td {
    padding: 12px 10px;
    border-bottom: 1px solid #f0f0f0;
    text-align: left;
}

.product-section th {
    background-color: #f7f7f7;
    font-weight: 600;
    color: #555;
    text-transform: uppercase;
}

.col-product { width: 80%; }
.col-quantity { width: 20%; text-align: center; }

.product-section td.col-quantity { text-align: center; }

/* --- Phần Tổng kết --- */
.summary-section {
    width: 350px; /* Chiều rộng giống như trong ảnh */
    margin-left: auto;
    padding-right: 10px;
    font-size: 15px;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
}

.summary-label {
    color: #6a6a6a;
}

.summary-value {
    font-weight: 600;
    color: #333;
}

.total-row {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 2px dashed #e0e0e0;
}

.total-value {
    color: #e51d3b; /* Màu đỏ nổi bật cho Tổng cộng */
    font-size: 18px;
    font-weight: 700;
}

.discount {
    color: #28a745; /* Màu xanh cho giá trị giảm giá */
}

/* --- Phần Footer --- */
.invoice-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
}

.btn-back {
    text-decoration: none;
    color: #4a90e2;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-back:hover {
    color: #357ab8;
}

/* --- Phần Cập nhật trạng thái --- */
.status-update-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.status-update-header {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.status-update-form {
    display: block;
}

.form-group-inline {
    display: flex;
    gap: 15px;
    align-items: flex-end;
}

.form-field {
    flex: 1;
    max-width: 400px;
}

.form-label {
    display: block;
    font-size: 13px;
    color: #6a6a6a;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-select {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #d0d0d0;
    border-radius: 4px;
    font-size: 14px;
    color: #333;
    background-color: #fff;
    transition: border-color 0.3s;
}

.form-select:focus {
    outline: none;
    border-color: #1a73e8;
}

.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #d0d0d0;
    border-radius: 4px;
    font-size: 14px;
    color: #333;
    background-color: #fff;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #1a73e8;
}

.btn-update {
    padding: 10px 25px;
    background-color: #1a73e8;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-update:hover {
    background-color: #1557b0;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}

.badge-primary {
    background-color: #007bff;
    color: white;
}
</style>

<div class="order-detail-wrapper">
    <div class="invoice-container">
        
        {{-- Header --}}
        <div class="invoice-header">
            Sửa đơn hàng #ORD-{{ str_pad($log->borrow->id ?? $log->id, 6, '0', STR_PAD_LEFT) }}
        </div>

        {{-- Chi tiết đơn hàng --}}
        <div class="detail-section">
            <div class="detail-grid">
                {{-- Họ tên --}}
                <div class="detail-item">
                    <div class="label">Họ tên:</div>
                    <div class="value">{{ $log->borrow->ten_nguoi_muon ?? ($log->borrow->reader->name ?? '—') }}</div>
                </div>

                {{-- SĐT --}}
                <div class="detail-item">
                    <div class="label">SĐT:</div>
                    <div class="value">{{ $log->borrow->so_dien_thoai ?? ($log->borrow->reader->sdt ?? '—') }}</div>
                </div>

                {{-- Email --}}
                <div class="detail-item">
                    <div class="label">Email:</div>
                    <div class="value">{{ $log->borrow->reader->email ?? '—' }}</div>
                </div>

                {{-- Địa chỉ giao hàng --}}
                <div class="detail-item">
                    <div class="label">Địa chỉ giao hàng:</div>
                    <div class="value">
                        {{ $log->borrow->so_nha ?? '' }} {{ $log->borrow->xa ?? '' }}, {{ $log->borrow->huyen ?? '' }}, Tỉnh/TP: {{ $log->borrow->tinh_thanh ?? '' }}
                    </div>
                </div>

                {{-- Phí vận chuyển --}}
                <div class="detail-item">
                    <div class="label">Phí vận chuyển:</div>
                    <div class="value">
                        @php
                            // Tính tổng phí ship từ items
                            $totalShipFromItems = 0;
                            if ($log->borrow && $log->borrow->items) {
                                foreach ($log->borrow->items as $item) {
                                    $totalShipFromItems += $item->tien_ship ?? 0;
                                }
                            }
                            // Ưu tiên lấy từ borrow, nếu = 0 thì lấy từ items
                            $shippingFeeDisplay = ($log->borrow->tien_ship ?? 0) > 0 ? ($log->borrow->tien_ship ?? 0) : $totalShipFromItems;
                            // Nếu vẫn = 0, mặc định là 20k
                            if ($shippingFeeDisplay == 0) {
                                $shippingFeeDisplay = 20000;
                            }
                        @endphp
                        {{ number_format($shippingFeeDisplay, 0) }}₫
                    </div>
                </div>

                {{-- Phương thức thanh toán --}}
                <div class="detail-item">
                    <div class="label">Phương thức thanh toán:</div>
                    <div class="value">
                        @if($log->borrow && $log->borrow->payments->count() > 0)
                            @php
                                $payment = $log->borrow->payments->first();
                                $paymentMethodMap = [
                                    'tien_mat' => 'Tiền mặt',
                                    'chuyen_khoan' => 'Chuyển khoản',
                                    'vnpay' => 'VNPay',
                                    'momo' => 'MoMo',
                                    'cod' => 'Thanh toán khi nhận hàng (COD)'
                                ];
                            @endphp
                            {{ $paymentMethodMap[$payment->phuong_thuc ?? 'cod'] ?? 'Thanh toán khi nhận hàng (COD)' }}
                        @else
                            Thanh toán khi nhận hàng (COD)
                        @endif
                    </div>
                </div>

                {{-- Phương thức vận chuyển --}}
                <div class="detail-item">
                    <div class="label">Phương thức vận chuyển:</div>
                    <div class="value">Giao hàng tiêu chuẩn</div>
                </div>

                {{-- Trạng thái --}}
                <div class="detail-item">
                    <div class="label">Trạng thái:</div>
                    <div class="value">
                        @php
                            $statusInfo = config('borrow_status.statuses.' . $log->status);
                            if (!$statusInfo) {
                                $statusInfo = ['label' => $log->status, 'color' => 'secondary', 'icon' => 'fa-question'];
                            }
                        @endphp
                        <span class="badge badge-{{ $statusInfo['color'] }}">
                            {{ $statusInfo['label'] }}
                        </span>
                    </div>
                </div>

                {{-- Trạng thái thanh toán --}}
                <div class="detail-item">
                    <div class="label">Trạng thái thanh toán:</div>
                    <div class="value">
                        @php
                            $isPaid = false;
                            $paymentMethod = 'N/A';
                            if($log->borrow && $log->borrow->payments->count() > 0) {
                                // Kiểm tra tất cả các khoản thanh toán
                                $allPaid = $log->borrow->payments
                                    ->whereIn('payment_type', ['deposit', 'borrow_fee', 'shipping_fee'])
                                    ->every(function($payment) {
                                        return $payment->payment_status === 'success';
                                    });
                                $isPaid = $allPaid && $log->borrow->payments->whereIn('payment_type', ['deposit', 'borrow_fee', 'shipping_fee'])->count() > 0;
                                
                                // Lấy phương thức thanh toán
                                $firstPayment = $log->borrow->payments->first();
                                if ($firstPayment) {
                                    $paymentMethod = $firstPayment->payment_method === 'online' ? 'Online (VNPay/MoMo)' : 'Thanh toán khi nhận hàng (COD)';
                                }
                            }
                        @endphp
                        <span class="status-badge {{ $isPaid ? 'status-delivered' : 'status-unpaid' }}">
                            {{ $isPaid ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                        </span>
                        <span style="font-size: 13px; color: #6a6a6a; margin-left: 10px;">
                            ({{ $paymentMethod }})
                        </span>
                    </div>
                </div>

                {{-- Ghi chú (full width) --}}
                <div class="detail-item full-width">
                    <div class="label">Ghi chú:</div>
                    <div class="value">{{ $log->shipper_note ?: ($log->receiver_note ?: 'Không có') }}</div>
                </div>
            </div>
        </div>

        {{-- Thay đổi trạng thái đơn hàng --}}
        <div class="status-update-section">
            <div class="status-update-header">Thay đổi trạng thái đơn hàng</div>
            
            {{-- Thông báo tự động cập nhật --}}
            <div style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 12px 15px; margin-bottom: 20px; border-radius: 4px;">
                <div style="font-size: 13px; color: #1976D2; line-height: 1.6;">
                    <i class="fas fa-info-circle"></i> <strong>Cập nhật tự động trạng thái thanh toán:</strong>
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        <li><strong>Giao hàng thành công</strong> → Tự động xác nhận tất cả các khoản thanh toán (Online + COD) sang <strong>"Đã thanh toán"</strong></li>
                        <li>COD + <strong>Giao hàng thất bại</strong> → Tự động chuyển về <strong>"Chưa thanh toán"</strong></li>
                    </ul>
                </div>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px; background: #d4edda; color: #155724; border-radius: 6px; border: 1px solid #c3e6cb;">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px; background: #f8d7da; color: #721c24; border-radius: 6px; border: 1px solid #f5c6cb;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('admin.shipping_logs.update_status', $log->id) }}" method="POST" class="status-update-form" enctype="multipart/form-data">
                @csrf
                <div class="form-group-inline">
                    <div class="form-field">
                        <label for="status" class="form-label">Chọn trạng thái mới</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="">-- Chọn trạng thái --</option>
                            @php
                                $statusConfig = config('borrow_status.statuses');
                                $currentStatusKey = $log->status;
                                $currentStatus = $statusConfig[$currentStatusKey] ?? null;
                                
                                // Lấy danh sách trạng thái có thể chuyển tiếp
                                $availableStatuses = [];
                                
                                // Thêm trạng thái hiện tại (cho phép giữ nguyên)
                                // Bỏ qua "cho_ban_giao_van_chuyen" - không hiển thị trạng thái này
                                if ($currentStatus && $currentStatusKey !== 'cho_ban_giao_van_chuyen') {
                                    $availableStatuses[$currentStatusKey] = $currentStatus;
                                }
                                
                                // Thêm các trạng thái tiếp theo được phép
                                if ($currentStatus && isset($currentStatus['next_statuses'])) {
                                    foreach ($currentStatus['next_statuses'] as $nextStatusKey) {
                                        // Ẩn "giao_hang_thanh_cong" khi đang ở "dang_giao_hang"
                                        // Vì thành công phụ thuộc vào khách hàng xác nhận
                                        if ($currentStatusKey === 'dang_giao_hang' && $nextStatusKey === 'giao_hang_thanh_cong') {
                                            continue; // Bỏ qua, không hiển thị option này
                                        }
                                        // Cho phép "giao_hang_that_bai" hiển thị khi đang ở "dang_giao_hang"
                                        // Admin có thể bấm "Giao hàng thất bại" và chọn lý do
                                        
                                        // Ẩn "dang_van_chuyen_tra_ve" khi đang ở "cho_tra_sach"
                                        // Vì chỉ khi khách hàng xác nhận hoàn trả sách thì mới chuyển sang trạng thái này
                                        if ($currentStatusKey === 'cho_tra_sach' && $nextStatusKey === 'dang_van_chuyen_tra_ve') {
                                            continue; // Bỏ qua, không hiển thị option này
                                        }
                                        
                                        // Ẩn "da_nhan_va_kiem_tra" khi đang ở "giao_hang_that_bai"
                                        // Vì sách phải được vận chuyển trả về trước, sau đó mới có thể nhận và kiểm tra
                                        if ($currentStatusKey === 'giao_hang_that_bai' && $nextStatusKey === 'da_nhan_va_kiem_tra') {
                                            continue; // Bỏ qua, không hiển thị option này
                                        }
                                        
                                        // Ẩn "cho_ban_giao_van_chuyen" - bỏ trạng thái này
                                        if ($nextStatusKey === 'cho_ban_giao_van_chuyen') {
                                            continue; // Bỏ qua, không hiển thị option này
                                        }
                                        
                                        // Tìm trong statuses chính
                                        if (isset($statusConfig[$nextStatusKey])) {
                                            $availableStatuses[$nextStatusKey] = $statusConfig[$nextStatusKey];
                                        }
                                        // Tìm trong special_statuses (như 'huy')
                                        else if ($nextStatusKey === 'huy') {
                                            $specialStatus = config('borrow_status.special_statuses.huy');
                                            $availableStatuses[$nextStatusKey] = array_merge($specialStatus, ['step' => 'X']);
                                        }
                                    }
                                }
                            @endphp
                            @forelse($availableStatuses as $statusKey => $statusInfo)
                                <option value="{{ $statusKey }}" {{ $log->status === $statusKey ? 'selected' : '' }}>
                                    {{ $statusInfo['label'] }}
                                </option>
                            @empty
                                <option value="{{ $currentStatusKey }}" selected>
                                    {{ $currentStatus['label'] ?? 'Trạng thái hiện tại' }}
                                </option>
                            @endforelse
                        </select>
                    </div>
                    <button type="submit" class="btn-update" id="update-status-btn">Cập nhật trạng thái</button>
                </div>
                
                <script>
                // Kiểm tra form submit + bật/tắt required theo trạng thái
                document.addEventListener('DOMContentLoaded', function() {
                    const statusSelect = document.getElementById('status');
                    const failureForm = document.getElementById('failure-form');
                    const failureRadios = document.querySelectorAll('input[name="failure_reason"]');
                    const failureImage = document.getElementById('failure_proof_image');

                    function toggleFailureRequired() {
                        const statusValue = statusSelect ? statusSelect.value : '';
                        const isFail = statusValue === 'giao_hang_that_bai';
                        if (!failureRadios) return;

                        failureRadios.forEach(r => {
                            r.required = isFail;
                        });

                        if (failureImage) {
                            const required = isFail && Array.from(failureRadios).some(r => r.checked && r.value === 'loi_thu_vien');
                            failureImage.required = required;
                        }
                    }

                    if (statusSelect) {
                        statusSelect.addEventListener('change', toggleFailureRequired);
                        // init
                        toggleFailureRequired();
                    }

                    const statusForm = document.querySelector('.status-update-form');
                    if (statusForm) {
                        statusForm.addEventListener('submit', function(e) {
                            const statusValue = statusSelect ? statusSelect.value : '';
                            
                            // Kiểm tra nếu chưa chọn trạng thái
                            if (!statusValue || statusValue === '') {
                                e.preventDefault();
                                alert('Vui lòng chọn trạng thái mới!');
                                return false;
                            }
                            
                            // Kiểm tra nếu chọn giao_hang_that_bai nhưng chưa chọn failure_reason
                            if (statusValue === 'giao_hang_that_bai') {
                                const failureReason = document.querySelector('input[name="failure_reason"]:checked');
                                if (!failureReason) {
                                    e.preventDefault();
                                    alert('Vui lòng chọn lý do thất bại!');
                                    return false;
                                }
                                
                                // Nếu lỗi do thư viện, kiểm tra ảnh
                                if (failureReason.value === 'loi_thu_vien') {
                                    if (failureImage) {
                                        const hasNewImage = failureImage.files && failureImage.files.length > 0;
                                        const existingImage = document.querySelector('#failure-image-field img');
                                        if (!hasNewImage && !existingImage) {
                                            e.preventDefault();
                                            alert('Vui lòng tải ảnh minh chứng cho lỗi do thư viện!');
                                            return false;
                                        }
                                    }
                                }
                            }
                            
                            // Nếu không có lỗi, cho phép submit (không gọi preventDefault)
                        });
                    }
                });
                </script>
                
                {{-- Form cho giao hàng thất bại --}}
                <div id="failure-form" style="display: none; margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border: 1px solid #ffc107;">
                    <h6 style="margin-bottom: 15px; color: #856404; font-weight: 600;">Thông tin giao hàng thất bại</h6>
                    
                    <div class="form-field" style="margin-bottom: 20px;">
                        <label class="form-label" style="font-weight: 600; color: #856404;">Lý do thất bại <span style="color: red;">*</span></label>
                        <div style="margin-top: 10px;">
                            <label style="display: flex; align-items: center; margin-bottom: 12px; cursor: pointer; padding: 10px; background: white; border-radius: 4px; border: 2px solid #e0e0e0;">
                                <input type="radio" name="failure_reason" value="loi_khach_hang" style="margin-right: 10px;" {{ old('failure_reason', $log->failure_reason) === 'loi_khach_hang' ? 'checked' : '' }}>
                                <div style="flex: 1;">
                                    <strong style="color: #333;">Lựa chọn A: Lỗi do Khách hàng</strong>
                                    <div style="font-size: 13px; color: #666; margin-top: 4px;">
                                        <strong>Lý do:</strong> Đổi ý, không nghe máy, từ chối nhận hàng...<br>
                                        <div style="margin-top: 6px; padding: 8px; background: #fff3cd; border-radius: 4px; border-left: 3px solid #dc3545;">
                                            <strong style="color: #dc3545;">Tiền hoàn:</strong><br>
                                            • Hoàn: <strong>Phí thuê</strong><br>
                                            • Hoàn: <strong>80% tiền cọc</strong> (trừ 20% phí phạt)<br>
                                            • <span style="color: #dc3545;">Khách mất: Phí ship (100%)</span><br>
                                            • <span style="color: #dc3545;">Khách mất: 20% tiền cọc (phí phạt)</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer; padding: 10px; background: white; border-radius: 4px; border: 2px solid #e0e0e0;">
                                <input type="radio" name="failure_reason" value="loi_thu_vien" style="margin-right: 10px;" {{ old('failure_reason', $log->failure_reason) === 'loi_thu_vien' ? 'checked' : '' }}>
                                <div style="flex: 1;">
                                    <strong style="color: #333;">Lựa chọn B: Lỗi do Sách/Thư viện</strong>
                                    <div style="font-size: 13px; color: #666; margin-top: 4px;">
                                        <strong>Lý do:</strong> Sách rách, bẩn, sai tên sách, thiếu sách...<br>
                                        <div style="margin-top: 6px; padding: 8px; background: #d1e7dd; border-radius: 4px; border-left: 3px solid #28a745;">
                                            <strong style="color: #28a745;">Tiền hoàn:</strong><br>
                                            • Hoàn: <strong>100% tiền cọc</strong><br>
                                            • Hoàn: <strong>100% phí thuê</strong><br>
                                            • Hoàn: <strong>100% phí ship</strong><br>
                                            <span style="color: #28a745;">→ Khách được hoàn toàn bộ 100%</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-field" id="failure-image-field" style="margin-bottom: 15px; display: none;">
                        <label class="form-label">
                            <span id="failure-image-label">Ảnh minh chứng</span>
                            <span id="failure-image-required" style="color: red; display: none;">*</span>
                        </label>
                        <input type="file" name="failure_proof_image" id="failure_proof_image" class="form-control" accept="image/*">
                        <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;" id="failure-image-hint"></small>
                        @if($log->failure_proof_image)
                            <div style="margin-top: 10px;">
                                <img src="{{ asset('storage/' . $log->failure_proof_image) }}" alt="Ảnh minh chứng" style="max-width: 200px; border-radius: 4px;">
                            </div>
                        @endif
                    </div>
                </div>
                
                {{-- Form cho thanh toán cọc --}}
                <div id="refund-form" style="display: none; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <h6 style="margin-bottom: 15px; color: #495057;">Thông tin thanh toán cọc</h6>
                    
                    <div class="form-field" style="margin-bottom: 15px;">
                        <label class="form-label">Tình trạng sách <span style="color: red;">*</span></label>
                        <select name="tinh_trang_sach" class="form-select">
                            <option value="">-- Chọn tình trạng --</option>
                            <option value="binh_thuong">Bình thường (Hoàn 100%)</option>
                            <option value="hong_nhe">Hỏng nhẹ (Trừ 10% giá sách)</option>
                            <option value="hong_nang">Hỏng nặng (Trừ 50% giá sách)</option>
                            <option value="mat_sach">Mất sách (Trừ 100% giá sách)</option>
                        </select>
                    </div>
                    
                    <div class="form-field" style="margin-bottom: 15px;">
                        <label class="form-label">Ghi chú kiểm tra</label>
                        <textarea name="ghi_chu_kiem_tra" class="form-control" rows="2" placeholder="Mô tả tình trạng sách..."></textarea>
                    </div>
                    
                    <div class="form-field">
                        <label class="form-label">Ghi chú hoàn cọc</label>
                        <textarea name="ghi_chu_hoan_coc" class="form-control" rows="2" placeholder="Thông tin về việc hoàn cọc..."></textarea>
                    </div>
                </div>
            </form>
        </div>
        
        <script>
        document.getElementById('status').addEventListener('change', function() {
            const refundForm = document.getElementById('refund-form');
            const failureForm = document.getElementById('failure-form');
            const failureImageField = document.getElementById('failure-image-field');
            
            // Hiển thị form khi chọn trạng thái "da_nhan_va_kiem_tra" hoặc "hoan_tat_don_hang"
            if (this.value === 'da_nhan_va_kiem_tra' || this.value === 'hoan_tat_don_hang') {
                refundForm.style.display = 'block';
            } else {
                refundForm.style.display = 'none';
            }
            
            // Hiển thị form khi chọn trạng thái "giao_hang_that_bai"
            if (this.value === 'giao_hang_that_bai') {
                failureForm.style.display = 'block';
            } else {
                failureForm.style.display = 'none';
                failureImageField.style.display = 'none';
            }
        });
        
        // Xử lý khi chọn lý do thất bại
        // Lưu ý: $totalTienCoc sẽ được định nghĩa sau trong vòng lặp, nên tạm thời sử dụng giá trị từ borrow
        const totalTienCocFromBorrow = {!! json_encode((float) ($log->borrow->tien_coc ?? 0)) !!};
        const tienCocHoanDisplay = document.getElementById('tien-coc-hoan-display');
        
        function updateRefundDeposit(failureReason) {
            if (!tienCocHoanDisplay) return;
            
            // Lấy giá trị từ DOM nếu có, nếu không thì dùng từ borrow
            let totalCoc = totalTienCocFromBorrow;
            const cocDisplayElement = document.querySelector('.summary-line:has(.summary-label:contains("Tiền cọc")) .summary-value');
            if (cocDisplayElement) {
                const cocText = cocDisplayElement.textContent.replace(/[^\d]/g, '');
                if (cocText) {
                    totalCoc = parseFloat(cocText);
                }
            }
            
            if (failureReason === 'loi_khach_hang') {
                // Lỗi do khách hàng: Hoàn 80% tiền cọc (trừ 20% phí phạt)
                const refundDeposit = totalCoc * 0.80;
                tienCocHoanDisplay.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: flex-end;">
                        <div style="text-decoration: line-through; color: #999; font-size: 14px;">${formatVnd(totalCoc)}</div>
                        <div style="color: #28a745;">${formatVnd(refundDeposit)}</div>
                    </div>
                `;
            } else if (failureReason === 'loi_thu_vien') {
                // Lỗi do thư viện: Hoàn 100% tiền cọc
                tienCocHoanDisplay.innerHTML = `<div style="color: #28a745;">${formatVnd(totalCoc)}</div>`;
            } else {
                // Trường hợp khác: Hiển thị giá trị mặc định
                const defaultRefund = {!! json_encode((float) ($tienCocHoan ?? 0)) !!};
                tienCocHoanDisplay.innerHTML = formatVnd(defaultRefund);
            }
        }
        
        document.querySelectorAll('input[name="failure_reason"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const failureImageField = document.getElementById('failure-image-field');
                const failureImageInput = document.getElementById('failure_proof_image');
                const failureImageRequired = document.getElementById('failure-image-required');
                const failureImageLabel = document.getElementById('failure-image-label');
                const failureImageHint = document.getElementById('failure-image-hint');
                
                if (this.value === 'loi_khach_hang') {
                    // Lỗi do khách hàng: Ảnh không bắt buộc
                    failureImageField.style.display = 'block';
                    failureImageRequired.style.display = 'none';
                    failureImageInput.removeAttribute('required');
                    failureImageLabel.textContent = 'Ảnh minh chứng (Chụp ảnh minh chứng khách không nhận)';
                    failureImageHint.textContent = 'Yêu cầu: Chụp ảnh minh chứng khách không nhận.';
                    // Cập nhật tiền cọc hoàn trả: 80%
                    updateRefundDeposit('loi_khach_hang');
                } else if (this.value === 'loi_thu_vien') {
                    // Lỗi do thư viện: Ảnh bắt buộc
                    failureImageField.style.display = 'block';
                    failureImageRequired.style.display = 'inline';
                    failureImageInput.setAttribute('required', 'required');
                    failureImageLabel.textContent = 'Ảnh minh chứng';
                    failureImageHint.textContent = 'Yêu cầu: Bắt buộc upload ảnh chỗ sách bị vấn đề để làm bằng chứng nhập kho sách lỗi.';
                    // Cập nhật tiền cọc hoàn trả: 100%
                    updateRefundDeposit('loi_thu_vien');
                }
            });
        });
        
        // Khởi tạo form nếu đã có giá trị
        @if($log->status === 'giao_hang_that_bai')
            document.getElementById('failure-form').style.display = 'block';
            @if($log->failure_reason === 'loi_khach_hang')
                document.querySelector('input[name="failure_reason"][value="loi_khach_hang"]').checked = true;
                document.getElementById('failure-image-field').style.display = 'block';
                document.getElementById('failure-image-required').style.display = 'none';
                document.getElementById('failure-image-label').textContent = 'Ảnh minh chứng (Chụp ảnh minh chứng khách không nhận)';
                document.getElementById('failure-image-hint').textContent = 'Yêu cầu: Chụp ảnh minh chứng khách không nhận.';
            @elseif($log->failure_reason === 'loi_thu_vien')
                document.querySelector('input[name="failure_reason"][value="loi_thu_vien"]').checked = true;
                document.getElementById('failure-image-field').style.display = 'block';
                document.getElementById('failure-image-required').style.display = 'inline';
                document.getElementById('failure_proof_image').setAttribute('required', 'required');
                document.getElementById('failure-image-label').textContent = 'Ảnh minh chứng';
                document.getElementById('failure-image-hint').textContent = 'Yêu cầu: Bắt buộc upload ảnh chỗ sách bị vấn đề để làm bằng chứng nhập kho sách lỗi.';
            @endif
        @endif
        </script>

        {{-- Sản phẩm --}}
        <div class="product-section">
            
            @if(isset($fines) && $fines->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ảnh</th>
                            <th>Loại</th>
                            <th>Trạng thái</th>
                            <th>hành động</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fines as $fine)
                            <tr>
                                <td>#{{ $fine->id }}</td>
                                <td><img src="{{ asset($fine->img) }}" alt="Ảnh" style="width: 50px; height: auto;"></td>
                                <td>{{ $typeMap[$fine->type] ?? $fine->type }}</td>
                                <td>
                                    <span class="status-badge {{ $fine->status == 'paid' ? 'status-delivered' : ($fine->status == 'pending' ? 'status-unpaid' : '') }}">
                                        {{ $statusMap[$fine->status] ?? $fine->status }}
                                    </span>
                                </td>
                                <td>
                                     {{--nếu là paid thì ẩn nut miễn phạt chuyển thành đã xử lí  --}}
                                    @if($fine->status != 'paid')
                                        <form method="POST" action="{{ route('admin.fines.waive', $fine->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-sm" title="Miễn phạt" onclick="return confirm('Xác nhận miễn phạt?')">
                                                Miễn phạt
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge badge-success">Đã xử lí</span>
                                    @endif
                                </td>
                                
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
            <div class="product-header">Sản phẩm</div>
            
            <table>
                <thead>
                    <tr>
                        <th class="col-product">Tên</th>
                        <th class="col-quantity">Số lượng</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalTienCoc = 0;
                        $totalTienThue = 0;
                        $totalTienShip = 0;
                    @endphp
                    @forelse($log->borrow->items as $item)
                        @php
                            $itemTienCoc = $item->tien_coc ?? 0;
                            $itemTienThue = $item->tien_thue ?? 0;
                            $itemTienShip = $item->tien_ship ?? 0;
                            
                            $totalTienCoc += $itemTienCoc;
                            $totalTienThue += $itemTienThue;
                            $totalTienShip += $itemTienShip;
                        @endphp
                        <tr>
                            <td class="col-product">{{ $item->book->ten_sach ?? 'Sách không xác định' }}</td>
                            <td class="col-quantity">1</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 40px; color: #999;">
                                Không có sản phẩm nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Tổng kết --}}
            @php
                // Tạm tính = tổng phí thuê từ tất cả items
                $subtotal = $totalTienThue;
                // Phí vận chuyển: ưu tiên lấy từ borrow, nếu = 0 thì lấy từ tổng items
                $shippingFeeFromBorrow = $log->borrow->tien_ship ?? 0;
                $shippingFee = $shippingFeeFromBorrow > 0 ? $shippingFeeFromBorrow : $totalTienShip;
                // Nếu vẫn = 0, mặc định là 20k
                if ($shippingFee == 0) {
                    $shippingFee = 20000;
                }
                $discount = 0;
                // Tổng cộng = tien_coc + tien_thue + tien_ship (từ borrow)
                // Sử dụng tong_tien từ borrow để đảm bảo đúng với dữ liệu thực tế
                $grandTotal = $log->borrow->tong_tien ?? ($totalTienCoc + $totalTienThue + $shippingFee);

                // Phí hỏng sách và tiền cọc hoàn trả (nếu có)
                $phiHong = $log->borrow->phi_hong_sach ?? 0;
                $tienCocHoan = $log->borrow->tien_coc_hoan_tra ?? max(0, $totalTienCoc - $phiHong);
                // Lưu ý: Tổng tiền KHÔNG trừ phí phạt; tổng cuối giữ nguyên để đối soát
                $finalGrand = $grandTotal;

                // Tổng giá trị sách (dùng để ước lượng phí hỏng khi chọn tình trạng)
                $totalBookValue = 0;
                foreach($log->borrow->items as $item) {
                    $totalBookValue += $item->book->gia ?? 0;
                }
            @endphp

            <div class="summary-section" id="order-summary">
                <div class="summary-line">
                    <div class="summary-label">Tiền thuê:</div>
                    <div class="summary-value">{{ number_format($subtotal, 0) }}₫</div>
                </div>

                @if($totalTienCoc > 0)
                <div class="summary-line">
                    <div class="summary-label">Tiền cọc (đã thu):</div>
                    <div class="summary-value">{{ number_format($totalTienCoc, 0) }}₫</div>
                </div>
                @endif

                {{-- Hiển thị thông tin giao hàng thất bại --}}
                @if($log->status === 'giao_hang_that_bai' && $log->failure_reason)
                    @php
                        $borrow = $log->borrow;
                        $totalDeposit = $borrow->tien_coc ?? 0;
                        $totalBorrowFee = $borrow->tien_thue ?? 0;
                        $totalShippingFee = $borrow->tien_ship ?? 0;
                    @endphp
                    
                    @if($log->failure_reason === 'loi_khach_hang')
                        @php
                            $penaltyAmount = $totalDeposit * 0.20;
                            $refundDeposit = $totalDeposit * 0.80;
                        @endphp
                        <div class="summary-line" style="margin-top: 15px; padding-top: 15px; border-top: 2px dashed #ffc107;">
                            <div class="summary-label" style="color: #dc3545; font-weight: 600;">Chi tiết hoàn tiền (Lỗi khách hàng):</div>
                        </div>
                        <div class="summary-line" style="padding-left: 15px;">
                            <div class="summary-label">✓ Hoàn phí thuê:</div>
                            <div class="summary-value" style="color: #28a745;">{{ number_format($totalBorrowFee, 0) }}₫</div>
                        </div>
                        <div class="summary-line" style="padding-left: 15px;">
                            <div class="summary-label">✓ Hoàn tiền cọc (80%):</div>
                            <div class="summary-value" style="color: #28a745;">{{ number_format($refundDeposit, 0) }}₫</div>
                        </div>
                        <div class="summary-line" style="padding-left: 15px;">
                            <div class="summary-label">✗ Trừ phí phạt (20% cọc):</div>
                            <div class="summary-value" style="color: #dc3545;">- {{ number_format($penaltyAmount, 0) }}₫</div>
                        </div>
                        <div class="summary-line" style="padding-left: 15px;">
                            <div class="summary-label">✗ Không hoàn phí ship:</div>
                            <div class="summary-value" style="color: #dc3545;">- {{ number_format($totalShippingFee, 0) }}₫</div>
                        </div>
                        <div class="summary-line" style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e0e0e0;">
                            <div class="summary-label" style="font-weight: 600;">Tổng khách mất:</div>
                            <div class="summary-value" style="color: #dc3545; font-weight: 600;">{{ number_format($penaltyAmount + $totalShippingFee, 0) }}₫</div>
                        </div>
                    @elseif($log->failure_reason === 'loi_thu_vien')
                        <div class="summary-line" style="margin-top: 15px; padding-top: 15px; border-top: 2px dashed #28a745;">
                            <div class="summary-label" style="color: #28a745; font-weight: 600;">Chi tiết hoàn tiền (Lỗi thư viện):</div>
                        </div>
                        <div class="summary-line" style="padding-left: 15px;">
                            <div class="summary-label">✓ Hoàn 100% phí thuê:</div>
                            <div class="summary-value" style="color: #28a745;">{{ number_format($totalBorrowFee, 0) }}₫</div>
                        </div>
                        <div class="summary-line" style="padding-left: 15px;">
                            <div class="summary-label">✓ Hoàn 100% tiền cọc:</div>
                            <div class="summary-value" style="color: #28a745;">{{ number_format($totalDeposit, 0) }}₫</div>
                        </div>
                        <div class="summary-line" style="padding-left: 15px;">
                            <div class="summary-label">✓ Hoàn 100% phí ship:</div>
                            <div class="summary-value" style="color: #28a745;">{{ number_format($totalShippingFee, 0) }}₫</div>
                        </div>
                        <div class="summary-line" style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e0e0e0;">
                            <div class="summary-label" style="font-weight: 600;">Tổng hoàn lại:</div>
                            <div class="summary-value" style="color: #28a745; font-weight: 600;">{{ number_format($totalDeposit + $totalBorrowFee + $totalShippingFee, 0) }}₫</div>
                        </div>
                    @endif
                @endif

                {{-- Hiện phí phạt nếu đã có hoặc khi chọn tình trạng --}}
                <div class="summary-line" id="phi-hong-line" style="display: {{ $phiHong > 0 ? 'flex' : 'none' }};">
                    <div class="summary-label">Phí phạt:</div>
                    <div class="summary-value text-danger" id="phi-hong-display">{{ number_format($phiHong, 0) }}₫</div>
                </div>

                {{-- Tiền cọc hoàn trả sau trừ phạt (nếu có) --}}


                <div class="summary-line">
                    <div class="summary-label">Phí vận chuyển:</div>
                    <div class="summary-value">{{ number_format($shippingFee, 0) }}₫</div>
                </div>

                <div class="summary-line">
                    <div class="summary-label">Giảm:</div>
                    <div class="summary-value discount">- {{ number_format($discount, 0) }}₫</div>
                </div>

      

                <div class="summary-line total-row" style="border-top:2px solid #e9ecef; padding-top:10px; margin-top:6px;">
                    <div class="summary-label">Tổng tiền mượn() :</div>
                    <div class="total-value" id="final-total-display" style="background:#f8f9fa; padding:8px 12px; border-radius:6px; font-size:18px;">
                        @php
                            // Tính lại tổng tiền = cọc + thuê + ship
                            $tienCocForTotal = $totalTienCoc;
                            $tienThueForTotal = $totalTienThue;
                            $tienShipForTotal = $shippingFee; // Đã tính ở trên
                            $tongTienRecalculated = $tienCocForTotal + $tienThueForTotal + $tienShipForTotal;
                        @endphp
                        @if($log->status === 'giao_hang_that_bai' && $log->failure_reason === 'loi_khach_hang')
                            @php
                                $penaltyAmount = ($log->borrow->tien_coc ?? 0) * 0.20;
                                $totalDeducted = $penaltyAmount + $tienShipForTotal;
                                $finalTotalAfterDeduct = $tongTienRecalculated - $totalDeducted;
                            @endphp
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <div style="text-decoration: line-through; color: #999; font-size: 14px;">{{ number_format($tongTienRecalculated, 0) }}₫</div>
                                <div style="color: #dc3545;">{{ number_format($finalTotalAfterDeduct, 0) }}₫</div>
                                <div style="font-size: 11px; color: #dc3545; margin-top: 4px;">(Đã trừ: {{ number_format($totalDeducted, 0) }}₫)</div>
                            </div>
                        @else
                            {{ number_format($tongTienRecalculated, 0) }}₫
                        @endif
                    </div>
                </div>
                <div class="summary-line" id="tien-coc-hoan-line" style="display: {{ $tienCocHoan >= 0 ? 'flex' : 'none' }};">
                    <div class="summary-label">Tiền cọc hoàn trả:</div>
                    <div class="summary-value" id="tien-coc-hoan-display">
                        @if($log->status === 'giao_hang_that_bai' && $log->failure_reason === 'loi_khach_hang')
                            @php
                                $refundDeposit = ($log->borrow->tien_coc ?? 0) * 0.80;
                            @endphp
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <div style="text-decoration: line-through; color: #999; font-size: 14px;">{{ number_format($totalTienCoc, 0) }}₫</div>
                                <div style="color: #28a745;">{{ number_format($refundDeposit, 0) }}₫</div>
                </div>
                        @else
                            {{ number_format($tienCocHoan, 0) }}₫
                        @endif
                    </div>
                </div>
                <div style="font-size:12px; color:#6a6a6a; margin-top:8px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                    @if($log->status === 'giao_hang_that_bai' && $log->failure_reason === 'loi_khach_hang')
                        @php
                            $penaltyAmountNote = ($log->borrow->tien_coc ?? 0) * 0.20;
                            $refundDepositNote = ($log->borrow->tien_coc ?? 0) * 0.80;
                        @endphp
                        <strong style="color: #dc3545;">Ghi chú (Lỗi khách hàng):</strong><br>
                        • <strong>Trừ 20% tiền cọc</strong> ({{ number_format($penaltyAmountNote, 0) }}₫) - Phí phạt do khách hàng từ chối nhận hàng<br>
                        • <strong>Trừ 100% phí vận chuyển</strong> ({{ number_format($shippingFee, 0) }}₫) - Khách hàng mất phí ship<br>
                        • <strong>Hoàn 100% phí thuê</strong> ({{ number_format($totalTienThue, 0) }}₫)<br>
                        • <strong>Hoàn 80% tiền cọc</strong> ({{ number_format($refundDepositNote, 0) }}₫)<br>
                        <span style="color: #dc3545; font-weight: 600;">→ Tổng khách hàng mất: {{ number_format($penaltyAmountNote + $shippingFee, 0) }}₫</span>
                    @elseif($log->status === 'giao_hang_that_bai' && $log->failure_reason === 'loi_thu_vien')
                        <strong style="color: #28a745;">Ghi chú (Lỗi thư viện):</strong><br>
                        • <strong>Hoàn 100% tiền cọc</strong> ({{ number_format($totalTienCoc, 0) }}₫)<br>
                        • <strong>Hoàn 100% phí thuê</strong> ({{ number_format($totalTienThue, 0) }}₫)<br>
                        • <strong>Hoàn 100% phí vận chuyển</strong> ({{ number_format($shippingFee, 0) }}₫)<br>
                        <span style="color: #28a745; font-weight: 600;">→ Khách hàng được hoàn toàn bộ: {{ number_format($totalTienCoc + $totalTienThue + $shippingFee, 0) }}₫</span>
                    @else
                        <strong>Ghi chú:</strong> <strong>Phí phạt hiển thị riêng và không làm giảm "Tổng phải thu".</strong> Tiền cọc đã thu và tiền cọc hoàn trả hiển thị tách biệt để đối soát.
                    @endif
                </div>
            </div>

            <script>
                // Biến từ server để tính toán live khi chọn tình trạng
                const totalBookValue = {!! json_encode((float) $totalBookValue) !!};
                const originalTienCoc = {!! json_encode((float) ($totalTienCoc ?? ($log->borrow->tien_coc ?? 0))) !!};
                const originalGrand = {!! json_encode((float) $grandTotal) !!};

                function formatVnd(value) {
                    return new Intl.NumberFormat('vi-VN').format(Math.round(value)) + '₫';
                }
                
                // Lắng nghe sự kiện thay đổi lý do thất bại (sử dụng hàm updateRefundDeposit đã định nghĩa ở trên)
                document.querySelectorAll('input[name="failure_reason"]').forEach(function(radio) {
                    radio.addEventListener('change', function() {
                        updateRefundDeposit(this.value);
                    });
                });
                
                // Khởi tạo khi trang load nếu đã có lý do thất bại
                @if($log->status === 'giao_hang_that_bai' && $log->failure_reason)
                    updateRefundDeposit('{{ $log->failure_reason }}');
                @endif

                // Cập nhật hiển thị khi thay đổi tình trạng sách
                const condSelect = document.querySelector('select[name="tinh_trang_sach"]');
                if (condSelect) {
                    condSelect.addEventListener('change', function() {
                        const cond = this.value;
                        let phiHong = 0;
                        switch(cond) {
                            case 'hong_nhe':
                                phiHong = totalBookValue * 0.1; break;
                            case 'hong_nang':
                                phiHong = totalBookValue * 0.5; break;
                            case 'mat_sach':
                                phiHong = totalBookValue; break;
                            default:
                                phiHong = 0;
                        }

                        // Tiền cọc hoàn trả
                        let tienCocHoan = Math.max(0, originalTienCoc - phiHong);

                        // Tổng không trừ phí phạt — giữ nguyên tổng để đối soát
                        let finalTotal = originalGrand;

                        // Hiển thị các dòng
                        const phiLine = document.getElementById('phi-hong-line');
                        const phiDisplay = document.getElementById('phi-hong-display');
                        const tienCocLine = document.getElementById('tien-coc-hoan-line');
                        const tienCocDisplay = document.getElementById('tien-coc-hoan-display');
                        const originalDisplay = document.getElementById('original-total-display');
                        const finalDisplay = document.getElementById('final-total-display');

                        if (phiLine && phiDisplay) {
                            if (phiHong > 0) {
                                phiLine.style.display = 'flex';
                                phiDisplay.textContent = formatVnd(phiHong);
                            } else {
                                phiLine.style.display = 'none';
                            }
                        }

                        if (tienCocLine && tienCocDisplay) {
                            tienCocLine.style.display = 'flex';
                            tienCocDisplay.textContent = formatVnd(tienCocHoan);
                        }

                        if (originalDisplay) {
                            originalDisplay.textContent = formatVnd(originalGrand);
                        }

                        if (finalDisplay) {
                            finalDisplay.textContent = formatVnd(finalTotal);
                        }
                    });
                }
            </script>
            <div class="invoice-footer">
            <a href="{{ route('admin.shipping_logs.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>
                Quay lại danh sách đơn hàng
            </a>
        </div>
        </div>

        {{-- Footer --}}
        

    </div>
</div>
@endsection

