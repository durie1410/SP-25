@extends('account._layout')

@section('title', 'Lịch sử đơn mượn')
@section('breadcrumb', 'Lịch sử đơn mượn')

@section('content')
    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-700: #374151;
            --gray-800: #1f2937;
        }

        .purchase-history-section {
            padding: 1.5rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .purchase-history-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .purchase-history-title::before {
            content: '';
            display: block;
            width: 4px;
            height: 24px;
            background: var(--primary);
            border-radius: 2px;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 8px;
        }

        .purchase-history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1.5rem;
        }

        .purchase-history-table th {
            background: var(--gray-100);
            color: var(--gray-700);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid var(--gray-200);
        }

        .purchase-history-table td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            color: var(--gray-800);
            border-bottom: 1px solid var(--gray-100);
            transition: background 0.2s ease;
        }

        .purchase-history-table tr:hover td {
            background-color: #f9fafb;
        }

        .order-code {
            font-family: 'Monaco', 'Consolas', monospace;
            font-weight: 600;
            color: var(--primary);
            background: #eff6ff;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .order-date {
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .order-amount {
            font-weight: 700;
            color: var(--gray-800);
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-borrowing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-returned {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-overdue {
            background: #fff1f2;
            color: #be123c;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-view {
            background: #fff;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-view:hover {
            background: var(--primary);
            color: #fff;
        }

        .btn-cancel {
            background: #fff;
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .btn-cancel:hover {
            background: var(--danger);
            color: #fff;
        }

        .btn-return {
            background: #fff;
            color: var(--success);
            border: 1px solid var(--success);
        }

        .btn-return:hover {
            background: var(--success);
            color: #fff;
        }

        .btn-request {
            background: #fff;
            color: var(--info);
            border: 1px solid var(--info);
        }

        .btn-request:hover {
            background: var(--info);
            color: #fff;
        }

        .btn-confirm {
            background: #fff !important;
            color: #198754 !important;
            border: 1px solid #198754 !important;
        }

        .btn-confirm:hover {
            background: #198754 !important;
            color: #fff !important;
        }

        .btn-reject {
            background: #fff !important;
            color: #f59e0b !important;
            border: 1px solid #f59e0b !important;
        }

        .btn-reject:hover {
            background: #f59e0b !important;
            color: #fff !important;
        }

        .img-preview-container {
            margin-top: 1rem;
            border: 2px dashed var(--gray-200);
            border-radius: 8px;
            padding: 0.5rem;
            text-align: center;
            position: relative;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .img-preview-container img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 4px;
        }

        .form-group-custom {
            margin-bottom: 1.25rem;
        }

        .form-label-custom {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-select-custom {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            font-size: 0.875rem;
            background-color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: #fff;
            border-radius: 12px;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }

        .empty-state h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--gray-500);
            margin-bottom: 2rem;
        }

        .btn-primary-modern {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-primary-modern:hover {
            background: #1d4ed8;
        }

        /* Modal Styles */
        .modal-custom {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-custom.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-dialog-custom {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: modalSlide 0.3s ease-out;
        }

        @keyframes modalSlide {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header-custom {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body-custom {
            padding: 1.5rem;
        }

        .modal-footer-custom {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        textarea.form-control-custom {
            width: 100%;
            min-height: 120px;
            padding: 0.75rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        textarea.form-control-custom:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
    </style>

    <div class="purchase-history-section">
        <h2 class="purchase-history-title">Lịch sử đơn mượn</h2>

        @if($orders->count() > 0)
            <div class="table-container">
                <table class="purchase-history-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã đơn</th>
                            <th>Ngày mượn</th>
                            <th>Số tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th style="text-align: right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $index => $order)
                                <tr>
                                    <td style="color: var(--gray-500); font-size: 0.875rem;">{{ $orders->firstItem() + $index }}</td>
                                    <td>
                                        <span class="order-code">#BRW{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>
                                        <span class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td>
                                        @php
                                            // Tính lại tổng tiền = cọc + thuê (không ship) - ưu tiên sum từ items để tránh tiền thuê = 0
                                            if ($order->items && $order->items->count() > 0) {
                                                $tienCoc = (float) $order->items->sum('tien_coc');
                                                $tienThue = (float) $order->items->sum('tien_thue');
                                            } else {
                                                $tienCoc = $order->tien_coc ?? 0;
                                                $tienThue = $order->tien_thue ?? 0;
                                            }
                                            
                                            // Tính lại tổng tiền (không cộng ship)
                                            $tongTienDisplay = $tienCoc + $tienThue;
                                        @endphp
                                        <span class="order-amount">{{ number_format($tongTienDisplay, 0, ',', '.') }}₫</span>
                                    </td>
                                    <td>
                                        @php
                                            $payment = $order->payments->first();
                                            $paymentMethod = $payment ? $payment->payment_method : null;
                                            $paymentNote = $payment ? $payment->note : '';
                                        @endphp
                                        <div class="payment-method">
                                            @if($paymentMethod === 'online')
                                                @if(str_contains($paymentNote, 'VNPay'))
                                                    <span style="color: #2196f3;">💳 VNPay</span>
                                                @elseif(str_contains($paymentNote, 'chuyển khoản'))
                                                    <span style="color: #17a2b8;">🏦 CK Ngân hàng</span>
                                                @elseif(str_contains($paymentNote, 'ví điện tử'))
                                                    <span style="color: #ff9800;">👛 Ví điện tử</span>
                                                @else
                                                    <span style="color: #2196f3;">💳 Online</span>
                                                @endif
                                            @elseif($paymentMethod === 'offline')
                                                <span style="color: #28a745;">💰 Khi nhận hàng</span>
                                            @else
                                                <span style="color: #6c757d;">Chưa xác định</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($order->trang_thai === 'Cho duyet')
                                            @php
                                                $detailStatusChoDuyet = $order->trang_thai_chi_tiet;
                                            @endphp
                                            @if($detailStatusChoDuyet === \App\Models\Borrow::STATUS_DANG_CHUAN_BI_SACH)
                                                <span class="status-badge" style="background: #e0f2fe; color: #0369a1;">
                                                    📦 Đang chuẩn bị sách
                                                </span>
                                            @elseif($detailStatusChoDuyet === \App\Models\Borrow::STATUS_CHO_BAN_GIAO_VAN_CHUYEN)
                                                <span class="status-badge" style="background: #fef9c3; color: #854d0e;">
                                                    🚚 Chờ bàn giao vận chuyển
                                                </span>
                                            @elseif($detailStatusChoDuyet === \App\Models\Borrow::STATUS_DANG_GIAO_HANG)
                                                <span class="status-badge" style="background: #cffafe; color: #155e75;">
                                                    🚚 Đang giao hàng
                                                </span>
                                            @elseif($detailStatusChoDuyet === \App\Models\Borrow::STATUS_DON_HANG_MOI)
                                                <span class="status-badge" style="background: #d4edda; color: #155724;">
                                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Đã được duyệt
                                                </span>
                                            @else
                                            <span class="status-badge status-pending">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                                Chờ duyệt
                                            </span>
                                            @endif
                                        @elseif($order->trang_thai === 'Dang muon')
                                            @php
                                                $detailStatus = $order->trang_thai_chi_tiet;
                                            @endphp

                                            {{-- Giai đoạn trước khi thực sự bắt đầu mượn (chuẩn bị / giao hàng / chờ xác nhận) --}}
                                            @if($detailStatus === \App\Models\Borrow::STATUS_DANG_CHUAN_BI_SACH)
                                                <span class="status-badge" style="background: #e0f2fe; color: #0369a1;">
                                                    📦 Đang chuẩn bị sách
                                                </span>
                                            @elseif($detailStatus === \App\Models\Borrow::STATUS_CHO_BAN_GIAO_VAN_CHUYEN)
                                                <span class="status-badge" style="background: #fef9c3; color: #854d0e;">
                                                    🚚 Chờ bàn giao vận chuyển
                                                </span>
                                            @elseif($detailStatus === \App\Models\Borrow::STATUS_DANG_GIAO_HANG)
                                                <span class="status-badge" style="background: #cffafe; color: #155e75;">
                                                    🚚 Đang giao hàng
                                                </span>
                                            @elseif($detailStatus === \App\Models\Borrow::STATUS_GIAO_HANG_THANH_CONG && !$order->customer_confirmed_delivery)
                                                <span class="status-badge" style="background: #e0f2fe; color: #1d4ed8;">
                                                    ✅ Đã giao hàng - Chờ bạn xác nhận
                                                </span>
                                            @elseif($detailStatus === \App\Models\Borrow::STATUS_GIAO_HANG_THAT_BAI)
                                                <span class="status-badge" style="background: #fee2e2; color: #dc2626;">
                                                    ❌ Giao hàng Thất bại
                                                </span>
                                            @elseif($detailStatus === \App\Models\Borrow::STATUS_DANG_VAN_CHUYEN_TRA_VE)
                                                <span class="status-badge" style="background: #cff4fc; color: #055160;">
                                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7h-3v7h3V7z" />
                                                        <path d="M14 7V5h3.06a1 1 0 01.99.85l.89 4.38a1 1 0 01-.15.86l-1.39 2.15a2.5 2.5 0 01-1.34.76H15.95a2.5 2.5 0 01-4.9 0H11" />
                                                    </svg>
                                                    Vận chuyển trả về
                                                </span>
                                            @elseif($detailStatus === \App\Models\Borrow::STATUS_DA_NHAN_VA_KIEM_TRA)
                                                <span class="status-badge" style="background: #fff3cd; color: #664d03;">
                                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                    Đã nhận & Kiểm tra
                                                </span>
                                            @elseif($detailStatus === \App\Models\Borrow::STATUS_HOAN_TAT_DON_HANG)
                                                <span class="status-badge" style="background: #d4edda; color: #155724;">
                                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Đã hoàn tiền
                                                </span>

                                            {{-- Giai đoạn sau khi đã mượn (đang mượn / chờ trả / đang trả / chờ hoàn tất) --}}
                                            @elseif($detailStatus === \App\Models\Borrow::STATUS_CHO_TRA_SACH)
                                                <span class="status-badge" style="background: #fff3cd; color: #856404;">
                                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Chờ trả sách
                                                </span>
                                            @elseif($detailStatus === \App\Models\Borrow::STATUS_DA_MUON_DANG_LUU_HANH)
                                                <span class="status-badge status-borrowing">
                                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.435.292-3.483.804a1 1 0 00-.517.876V15.803a1 1 0 001.49.876A6 6 0 017.18 16c1.103 0 2.113.188 3.013.53a1 1 0 00.814 0c.9-.342 1.91-.53 3.013-.53 1.103 0 2.113.188 3.013.53a1 1 0 001.49-.876V5.68a1 1 0 00-.517-.876A7.968 7.968 0 0014.5 4c-1.255 0-2.435.292-3.483.804V14.5a1 1 0 01-1.016 1.016A7.96 7.96 0 009 14.5V4.804z"></path>
                                                    </svg>
                                                    Đang mượn
                                                </span>
                                            @else
                                                {{-- Fallback: nếu vì lý do gì đó không khớp chi tiết, hiển thị trạng thái tổng quát --}}
                                                <span class="status-badge status-borrowing">
                                                    📖 Đang mượn
                                                </span>
                                            @endif
                                        @elseif($order->trang_thai === 'Da tra')
                                            @if($order->trang_thai_chi_tiet === \App\Models\Borrow::STATUS_HOAN_TAT_DON_HANG)
                                                <span class="status-badge" style="background: #d4edda; color: #155724;">
                                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Đã hoàn tiền
                                                </span>
                                            @else
                                            <span class="status-badge status-returned">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                                Đã trả
                                            </span>
                                            @endif
                                        @elseif($order->trang_thai === 'Huy')
                                            <span class="status-badge status-cancelled">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                                Đã hủy
                                            </span>
                                        @elseif($order->trang_thai === 'Qua han')
                                            <span class="status-badge status-overdue">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                                Quá hạn
                                            </span>
                                        @else
                                            <span class="status-badge"
                                                style="background: var(--gray-200); color: var(--gray-700);">{{ $order->trang_thai }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons" style="justify-content: flex-end;">
                                            <a href="{{ route('orders.detail', $order->id) }}" class="btn-action btn-view"
                                                title="Xem chi tiết">
                                                Chi tiết
                                            </a>

                            @php
                                // Trạng thái chờ xác nhận giao hàng
                                $needsConfirmation = ($order->trang_thai_chi_tiet === \App\Models\Borrow::STATUS_DANG_GIAO_HANG || $order->trang_thai_chi_tiet === \App\Models\Borrow::STATUS_GIAO_HANG_THANH_CONG)
                                    && !$order->customer_confirmed_delivery && !$order->customer_rejected_delivery;

                                $canConfirmReturn = ($order->trang_thai_chi_tiet === \App\Models\Borrow::STATUS_CHO_TRA_SACH);
                            @endphp

                                            @if($needsConfirmation)
                                                <form action="{{ route('account.borrows.confirm-delivery', $order->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn-action btn-confirm"
                                                        onclick="return confirm('Bạn có chắc chắn đã nhận sách?')"
                                                        title="Xác nhận đã nhận sách">
                                                        Nhận sách
                                                    </button>
                                                </form>
                                                {{-- Ở trạng thái Đang giao hàng: không cho từ chối --}}
                                            @endif

                                            @if($canConfirmReturn)
                                            <button type="button" class="btn-action btn-return" onclick="showReturnModal({{ $order->id }})">
                                                Trả sách
                                            </button>
                                        @endif

                                            @php
                                                // Chỉ cho phép hủy khi đơn còn ở trạng thái mới (chưa chuyển sang chuẩn bị / giao hàng)
                                                $canCancelOrder = $order->trang_thai === 'Cho duyet'
                                                    && ($order->trang_thai_chi_tiet === null
                                                        || $order->trang_thai_chi_tiet === \App\Models\Borrow::STATUS_DON_HANG_MOI);
                                            @endphp

                                            @if($canCancelOrder)
                                                <button type="button" class="btn-action btn-cancel"
                                                    onclick="showCancelModal(event, {{ $order->id }})">
                                                    Hủy đơn
                                                </button>
                                            @elseif($order->trang_thai === 'Cho duyet' 
                                                && in_array($order->trang_thai_chi_tiet, [
                                                    \App\Models\Borrow::STATUS_DANG_CHUAN_BI_SACH,
                                                    \App\Models\Borrow::STATUS_CHO_BAN_GIAO_VAN_CHUYEN,
                                                    \App\Models\Borrow::STATUS_DANG_GIAO_HANG,
                                                    \App\Models\Borrow::STATUS_GIAO_HANG_THANH_CONG,
                                                ]))
                                                <span style="display:block; margin-top:6px; font-size:12px; color:#6b7280;">
                                                    ❌ Đơn đang được xử lý/giao hàng, không thể hủy.
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            @if($orders->hasPages())
                <div class="pagination-wrapper mt-4">
                    {{ $orders->links() }}
                </div>
            @endif

        @else
            <div class="empty-state">
                <div class="empty-icon">📂</div>
                <h4>Bạn chưa có đơn mượn nào</h4>
                <p>Hãy khám phá thư viện của chúng tôi và bắt đầu chuyến hành trình đọc sách của bạn!</p>
                <a href="{{ route('books.public') }}" class="btn-primary-modern">
                    Mượn sách ngay
                </a>
            </div>
        @endif
    </div>

    <!-- Modal Hủy Đơn -->
    <div id="cancelModal" class="modal-custom" tabindex="-1">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h5 class="m-0 font-weight-bold" style="font-size: 1.125rem;">Xác nhận hủy đơn mượn</h5>
                <button type="button" class="btn-close" onclick="hideCancelModal()"
                    style="border: none; background: none; font-size: 1.5rem; line-height: 1;">&times;</button>
            </div>
            <div class="modal-body-custom">
                <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 1rem;">Vui lòng cho chúng tôi biết lý do
                    bạn muốn hủy đơn mượn này.</p>
                <textarea id="cancelReason" class="form-control-custom"
                    placeholder="Nhập lý do hủy đơn (tối thiểu 10 ký tự)..."></textarea>
                <div id="errorMessage" class="text-danger mt-2"
                    style="display: none; font-size: 0.75rem; font-weight: 500;"></div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-action" onclick="hideCancelModal()"
                    style="background: var(--gray-100); color: var(--gray-700);">Đóng</button>
                <button type="button" class="btn-action btn-cancel" onclick="confirmCancel()">Xác nhận hủy</button>
            </div>
        </div>
    </div>

    <!-- Modal Hoàn Trả Sách (Upload Ảnh) -->
    <div id="returnModal" class="modal-custom" tabindex="-1">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h5 class="m-0 font-weight-bold" style="font-size: 1.125rem;">Xác nhận hoàn trả sách</h5>
                <button type="button" class="btn-close" onclick="hideReturnModal()"
                    style="border: none; background: none; font-size: 1.5rem; line-height: 1;">&times;</button>
            </div>
            <form id="returnForm" enctype="multipart/form-data">
                <div class="modal-body-custom">
                    <div class="form-group-custom">
                        <label class="form-label-custom">Tình trạng sách thực tế:</label>
                        <select id="returnCondition" class="form-select-custom" required>
                            <option value="binh_thuong">Bình thường</option>
                            <option value="hong_nhe">Hỏng nhẹ (Rách trang, bẩn...)</option>
                            <option value="hong_nang">Hỏng nặng (Mất trang, hư bìa...)</option>
                            <option value="mat_sach">Mất hoàn toàn</option>
                        </select>
                    </div>

                    <div class="form-group-custom">
                        <label class="form-label-custom">Hình ảnh minh chứng (tối thiểu 1 ảnh):</label>
                        <input type="file" id="returnImage" name="anh_hoan_tra[]" accept="image/*" class="form-control"
                            onchange="previewImages(event)" required multiple>
                        <div class="img-preview-container" id="imagePreview"
                            style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-start; min-height: 120px; border: 2px dashed #e2e8f0; border-radius: 8px; padding: 10px;">
                            <span class="text-muted" style="font-size: 0.75rem; width: 100%; text-align: center;">Chưa có
                                ảnh chọn</span>
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <label class="form-label-custom">Ghi chú:</label>
                        <textarea id="returnNote" class="form-control-custom"
                            placeholder="Mô tả chi tiết tình trạng sách..."></textarea>
                    </div>
                    <div id="returnError" class="text-danger mt-2"
                        style="display: none; font-size: 0.75rem; font-weight: 500;"></div>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn-action" onclick="hideReturnModal()"
                        style="background: var(--gray-100); color: var(--gray-700);">Đóng</button>
                    <button type="button" class="btn-action btn-return" onclick="confirmReturnSubmit(event)">Xác nhận hoàn
                        trả</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Từ Chối Nhận Sách -->
    <div id="rejectDeliveryModal" class="modal-custom" tabindex="-1">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h5 class="m-0 font-weight-bold" style="font-size: 1.125rem;">Xác nhận từ chối nhận sách</h5>
                <button type="button" class="btn-close" onclick="hideRejectDeliveryModal()"
                    style="border: none; background: none; font-size: 1.5rem; line-height: 1;">&times;</button>
            </div>
            <div class="modal-body-custom">
                <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 1rem;">Vui lòng cho biết lý do bạn từ chối
                    nhận đơn mượn này.</p>
                <textarea id="rejectReason" class="form-control-custom"
                    placeholder="Nhập lý do từ chối (tối thiểu 5 ký tự)..."></textarea>
                <div id="rejectErrorMessage" class="text-danger mt-2"
                    style="display: none; font-size: 0.75rem; font-weight: 500;"></div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-action" onclick="hideRejectDeliveryModal()"
                    style="background: var(--gray-100); color: var(--gray-700);">Đóng</button>
                <button type="button" class="btn-action btn-reject" onclick="confirmRejectDelivery(event)">Xác nhận từ
                    chối</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentBorrowId = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- Hủy đơn ---
        function showCancelModal(event, borrowId) {
            currentBorrowId = borrowId;
            document.getElementById('cancelModal').style.display = 'flex';
            document.getElementById('cancelModal').classList.add('show');
            document.getElementById('cancelReason').value = '';
            document.getElementById('errorMessage').style.display = 'none';
        }

        function hideCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
            document.getElementById('cancelModal').classList.remove('show');
            currentBorrowId = null;
        }

        function confirmCancel(event) {
            const reason = document.getElementById('cancelReason').value.trim();
            const errorDiv = document.getElementById('errorMessage');

            if (reason.length < 10) {
                errorDiv.textContent = 'Lí do hủy đơn phải có ít nhất 10 ký tự';
                errorDiv.style.display = 'block';
                return;
            }

            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>...';

            fetch(`/borrows/${currentBorrowId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cancellation_reason: reason })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        errorDiv.textContent = data.message;
                        errorDiv.style.display = 'block';
                        btn.disabled = false;
                        btn.textContent = 'Xác nhận hủy';
                    }
                })
                .catch(() => {
                    errorDiv.textContent = 'Có lỗi xảy ra';
                    errorDiv.style.display = 'block';
                    btn.disabled = false;
                });
        }

        // --- Xác nhận hoàn trả (Upload ảnh) ---
        function showReturnModal(borrowId) {
            currentBorrowId = borrowId;
            document.getElementById('returnModal').style.display = 'flex';
            document.getElementById('returnModal').classList.add('show');
            document.getElementById('imagePreview').innerHTML = '<span class="text-muted" style="font-size: 0.75rem;">Chưa có ảnh chọn</span>';
            document.getElementById('returnError').style.display = 'none';
            document.getElementById('returnImage').value = '';
        }

        function hideReturnModal() {
            document.getElementById('returnModal').style.display = 'none';
            document.getElementById('returnModal').classList.remove('show');
        }

        function previewImages(event) {
            const output = document.getElementById('imagePreview');
            output.innerHTML = '';

            const files = event.target.files;
            if (files.length === 0) {
                output.innerHTML = '<span class="text-muted" style="font-size: 0.75rem; width: 100%; text-align: center;">Chưa có ảnh chọn</span>';
                return;
            }

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function () {
                    const imgContainer = document.createElement('div');
                    imgContainer.style.width = '100px';
                    imgContainer.style.height = '100px';
                    imgContainer.style.overflow = 'hidden';
                    imgContainer.style.borderRadius = '4px';
                    imgContainer.style.border = '1px solid #ddd';

                    const img = document.createElement('img');
                    img.src = reader.result;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';

                    imgContainer.appendChild(img);
                    output.appendChild(imgContainer);
                };
                reader.readAsDataURL(file);
            });
        }

        function confirmReturnSubmit(event) {
            const condition = document.getElementById('returnCondition').value;
            const note = document.getElementById('returnNote').value;
            const imageInput = document.getElementById('returnImage');
            const errorDiv = document.getElementById('returnError');

            if (imageInput.files.length === 0) {
                errorDiv.textContent = 'Vui lòng chọn ít nhất 1 ảnh minh chứng';
                errorDiv.style.display = 'block';
                return;
            }

            const formData = new FormData();
            formData.append('tinh_trang_sach', condition);
            formData.append('ghi_chu', note);

            for (let i = 0; i < imageInput.files.length; i++) {
                formData.append('anh_hoan_tra[]', imageInput.files[i]);
            }

            formData.append('_token', csrfToken);

            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>...';

            fetch(`/account/borrows/${currentBorrowId}/return-book`, {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (response.ok) location.reload();
                    else throw new Error('Return confirmation failed');
                })
                .catch(error => {
                    errorDiv.textContent = 'Lỗi: ' + error.message;
                    errorDiv.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = 'Xác nhận hoàn trả';
                });
        }

        // --- Từ chối nhận sách ---
        function showRejectDeliveryModal(borrowId) {
            currentBorrowId = borrowId;
            document.getElementById('rejectDeliveryModal').style.display = 'flex';
            document.getElementById('rejectDeliveryModal').classList.add('show');
            document.getElementById('rejectReason').value = '';
            document.getElementById('rejectErrorMessage').style.display = 'none';
        }

        function hideRejectDeliveryModal() {
            document.getElementById('rejectDeliveryModal').style.display = 'none';
            document.getElementById('rejectDeliveryModal').classList.remove('show');
        }

        function confirmRejectDelivery(event) {
            const reason = document.getElementById('rejectReason').value.trim();
            const errorDiv = document.getElementById('rejectErrorMessage');

            if (reason.length < 5) {
                errorDiv.textContent = 'Lí do từ chối phải có ít nhất 5 ký tự';
                errorDiv.style.display = 'block';
                return;
            }

            const btn = event.target;
            btn.disabled = true;

            fetch(`/account/borrows/${currentBorrowId}/reject-delivery`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason: reason })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        errorDiv.textContent = data.message;
                        errorDiv.style.display = 'block';
                        btn.disabled = false;
                    }
                })
                .catch(() => {
                    errorDiv.textContent = 'Có lỗi xảy ra';
                    errorDiv.style.display = 'block';
                    btn.disabled = false;
                });
        }

        // Close on overlay click
        window.onclick = function (event) {
            if (event.target.classList.contains('modal-custom')) {
                hideCancelModal();
                hideReturnModal();
                hideRejectDeliveryModal();
            }
        }
    </script>
@endpush