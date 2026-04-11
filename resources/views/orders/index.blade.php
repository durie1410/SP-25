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
                            <th>Số lượng</th>
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
                                        <span style="font-weight: 600; color: var(--primary);">
                                            {{ $order->items ? $order->items->count() : 0 }} cuốn
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $items = $order->items ?? collect();
                                            $statusCounts = $items->groupBy('trang_thai')->map->count();
                                            $labels = [
                                                'Dang muon' => 'Đang mượn',
                                                'Da tra' => 'Đã trả',
                                                'Qua han' => 'Quá hạn',
                                                'Hong' => 'Hỏng',
                                                'Mat sach' => 'Mất sách',
                                                'Cho tra' => 'Chờ trả',
                                                'Tra that bai' => 'Trả thất bại',
                                            ];
                                            $colors = [
                                                'Dang muon' => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
                                                'Da tra' => ['bg' => '#d4edda', 'text' => '#155724'],
                                                'Qua han' => ['bg' => '#fee2e2', 'text' => '#dc2626'],
                                                'Hong' => ['bg' => '#fee2e2', 'text' => '#b91c1c'],
                                                'Mat sach' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                                'Cho tra' => ['bg' => '#fff3cd', 'text' => '#856404'],
                                                'Tra that bai' => ['bg' => '#fce7f3', 'text' => '#9d174d'],
                                            ];
                                            $uniqueStatuses = $statusCounts->count();
                                        @endphp

                                        @if($uniqueStatuses > 1)
                                            <div style="display:flex; flex-direction:column; gap:4px;">
                                                @foreach($statusCounts as $stt => $cnt)
                                                    @php
                                                        $lbl = $labels[$stt] ?? $stt;
                                                        $color = $colors[$stt] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                                                    @endphp
                                                    <span style="display:inline-flex; align-items:center; gap:4px; font-size:11px; padding:2px 8px; border-radius:20px; background:{{ $color['bg'] }}; color:{{ $color['text'] }}; font-weight:500; white-space:nowrap;">
                                                        {{ $lbl }} {{ $cnt }} cuốn
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            @php
                                                $onlyStt = $statusCounts->keys()->first();
                                                $lbl = $labels[$onlyStt] ?? $onlyStt;
                                                $color = $colors[$onlyStt] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                                            @endphp
                                            @if($order->trang_thai === 'Cho duyet')
                                                @php $detailStatus = $order->trang_thai_chi_tiet; @endphp
                                                @if($detailStatus === \App\Models\Borrow::STATUS_DANG_CHUAN_BI_SACH)
                                                    <span class="status-badge" style="background:#e0f2fe; color:#0369a1;">📦 Đang chuẩn bị sách</span>
                                                @elseif($detailStatus === \App\Models\Borrow::STATUS_CHO_BAN_GIAO_VAN_CHUYEN)
                                                    <span class="status-badge" style="background:#fef9c3; color:#854d0e;">🚚 Chờ bàn giao vận chuyển</span>
                                                @elseif($detailStatus === \App\Models\Borrow::STATUS_DANG_GIAO_HANG)
                                                    <span class="status-badge" style="background:#cffafe; color:#155e75;">🚚 Đang giao hàng</span>
                                                @elseif($detailStatus === \App\Models\Borrow::STATUS_DON_HANG_MOI)
                                                    <span class="status-badge" style="background:#d4edda; color:#155724;">✓ Đã được duyệt</span>
                                                @else
                                                    <span class="status-badge status-pending">⏳ Chờ duyệt</span>
                                                @endif
                                            @elseif($onlyStt === 'Dang muon')
                                                <span class="status-badge" style="background:{{ $color['bg'] }}; color:{{ $color['text'] }};">📖 {{ $lbl }}</span>
                                            @elseif($onlyStt === 'Da tra')
                                                <span class="status-badge" style="background:{{ $color['bg'] }}; color:{{ $color['text'] }};">✓ {{ $lbl }}</span>
                                            @elseif($onlyStt === 'Qua han')
                                                <span class="status-badge" style="background:{{ $color['bg'] }}; color:{{ $color['text'] }};">⚠️ {{ $lbl }}</span>
                                            @elseif($onlyStt === 'Hong' || $onlyStt === 'Mat sach')
                                                <span class="status-badge" style="background:{{ $color['bg'] }}; color:{{ $color['text'] }};">🔴 {{ $lbl }}</span>
                                            @elseif($onlyStt === 'Cho tra')
                                                <span class="status-badge" style="background:{{ $color['bg'] }}; color:{{ $color['text'] }};">⏳ {{ $lbl }}</span>
                                            @else
                                                <span class="status-badge" style="background:{{ $color['bg'] }}; color:{{ $color['text'] }}; font-size:11px; padding:3px 10px;">{{ $lbl }}</span>
                                            @endif
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
                    {{ $orders->links('vendor.pagination.default') }}
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