@extends('account._layout')

@section('title', 'Chi tiết đơn mượn #' . $borrow->id)
@section('breadcrumb', 'Chi tiết đơn mượn')

@push('styles')
    <style>
        .detail-container {
            /* Remove max-width and margins as it's now inside .account-content */
            margin: 0;
            padding: 0;
        }

        .detail-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .detail-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .detail-card {
            background: white;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .detail-section {
            padding: 25px;
            border-bottom: 1px solid #eee;
        }

        .detail-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }

        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            width: 200px;
            flex-shrink: 0;
        }

        .info-value {
            color: #333;
            flex: 1;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 14px;
        }

        .status-Cho-duyet {
            background-color: #ffc107;
            color: #000;
        }

        .status-Dang-muon {
            background-color: #2196f3;
            color: #fff;
        }

        .status-Da-tra {
            background-color: #28a745;
            color: #fff;
        }

        .status-Huy {
            background-color: #dc3545;
            color: #fff;
        }

        .status-Qua-han {
            background-color: #ff5722;
            color: #fff;
        }

        .book-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .book-image {
            width: 100px;
            height: 140px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .book-info {
            flex: 1;
        }

        .book-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .book-author {
            color: #666;
            margin-bottom: 8px;
        }

        .book-meta {
            font-size: 14px;
            color: #888;
        }

        .price-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .price-row:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 18px;
            color: #d82329;
            padding-top: 15px;
            border-top: 2px solid #333;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 0 0 10px 10px;
        }

        .btn-custom {
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
            color: white;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
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
            align-items: center;
            justify-content: center;
        }

        .modal-custom.active {
            display: flex;
        }

        .modal-dialog-custom {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: modalSlide 0.3s ease-out;
            margin: 20px;
        }

        @keyframes modalSlide {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header-custom {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body-custom {
            padding: 1.5rem;
        }

        .modal-footer-custom {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        textarea.form-control-custom {
            width: 100%;
            min-height: 120px;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .btn-close-custom {
            background: none;
            border: none;
            font-size: 1.5rem;
            line-height: 1;
            cursor: pointer;
            color: #6b7280;
        }

        .btn-secondary-custom {
            background: #f3f4f6;
            color: #374151;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="detail-container">
        <div class="detail-header">
            <h1><i class="fas fa-file-alt"></i> Chi tiết đơn mượn #BRW{{ str_pad($borrow->id, 6, '0', STR_PAD_LEFT) }}</h1>
            <p style="margin: 0; opacity: 0.9;">Ngày tạo: {{ $borrow->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="detail-card">
            <!-- Thông tin khách hàng -->
            <div class="detail-section">
                <div class="section-title"><i class="fas fa-user"></i> Thông tin khách hàng</div>
                <div class="info-row">
                    <div class="info-label">Họ và tên:</div>
                    <div class="info-value">{{ $borrow->ten_nguoi_muon }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Số điện thoại:</div>
                    <div class="info-value">{{ $borrow->so_dien_thoai }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Địa chỉ:</div>
                    <div class="info-value">
                        {{ $borrow->so_nha ? $borrow->so_nha . ', ' : '' }}
                        {{ $borrow->xa ? $borrow->xa . ', ' : '' }}
                        {{ $borrow->huyen ? $borrow->huyen . ', ' : '' }}
                        {{ $borrow->tinh_thanh }}
                    </div>
                </div>
                @if($borrow->reader)
                    <div class="info-row">
                        <div class="info-label">Mã độc giả:</div>
                        <div class="info-value">{{ $borrow->reader->so_the_doc_gia }}</div>
                    </div>
                @endif
            </div>

            <!-- Trạng thái đơn -->
            <div class="detail-section">
                <div class="section-title"><i class="fas fa-info-circle"></i> Trạng thái đơn mượn</div>
                <div class="info-row">
                    <div class="info-label">Trạng thái:</div>
                    <div class="info-value">
                        @php
                            $detailStatus = $borrow->trang_thai_chi_tiet;
                        @endphp
                        @if($detailStatus === 'giao_hang_that_bai')
                            <span class="status-badge" style="background-color: #dc3545; color: #fff;">❌ Giao hàng Thất bại</span>
                        @elseif($detailStatus === 'dang_van_chuyen_tra_ve')
                            <span class="status-badge" style="background-color: #cff4fc; color: #055160;">🚚 Vận chuyển trả về</span>
                        @elseif($detailStatus === 'da_nhan_va_kiem_tra')
                            <span class="status-badge" style="background-color: #fff3cd; color: #664d03;">📦 Đã nhận & Kiểm tra</span>
                        @elseif($detailStatus === 'hoan_tat_don_hang')
                            <span class="status-badge" style="background-color: #d4edda; color: #155724;">✅ Đã hoàn tiền</span>
                        @elseif($detailStatus === 'dang_chuan_bi_sach')
                            <span class="status-badge" style="background-color: #e0f2fe; color: #0369a1;">📦 Đang chuẩn bị sách</span>
                        @elseif($detailStatus === 'cho_ban_giao_van_chuyen')
                            <span class="status-badge" style="background-color: #fef9c3; color: #854d0e;">🚚 Chờ bàn giao vận chuyển</span>
                        @elseif($detailStatus === 'dang_giao_hang')
                            <span class="status-badge" style="background-color: #cffafe; color: #155e75;">🚚 Đang giao hàng</span>
                        @elseif($detailStatus === 'giao_hang_thanh_cong')
                            <span class="status-badge" style="background-color: #e0f2fe; color: #1d4ed8;">✅ Đã giao hàng</span>
                        @elseif($borrow->trang_thai === 'Cho duyet')
                            @if($detailStatus === \App\Models\Borrow::STATUS_DON_HANG_MOI)
                                <span class="status-badge" style="background-color: #d4edda; color: #155724;">✅ Đã được duyệt</span>
                            @else
                            <span class="status-badge status-Cho-duyet">⏳ Đang chờ xử lí</span>
                            @endif
                        @elseif($borrow->trang_thai === 'Dang muon')
                            <span class="status-badge status-Dang-muon">📖 Đang mượn</span>
                        @elseif($borrow->trang_thai === 'Da tra')
                            @if($detailStatus === 'hoan_tat_don_hang')
                                <span class="status-badge" style="background-color: #d4edda; color: #155724;">✅ Đã hoàn tiền</span>
                            @else
                            <span class="status-badge status-Da-tra">✅ Đã trả</span>
                            @endif
                        @elseif($borrow->trang_thai === 'Huy')
                            <span class="status-badge status-Huy">❌ Đã hủy</span>
                        @elseif($borrow->trang_thai === 'Qua han')
                            <span class="status-badge status-Qua-han">⚠️ Quá hạn</span>
                        @else
                            <span class="status-badge">{{ $borrow->trang_thai }}</span>
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Ngày mượn:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($borrow->ngay_muon)->format('d/m/Y') }}</div>
                </div>
                @if($borrow->ghi_chu)
                    <div class="info-row">
                        <div class="info-label">Ghi chú:</div>
                        <div class="info-value">{{ $borrow->ghi_chu }}</div>
                    </div>
                @endif
                
                @php
                    // ShippingLog đã bị xóa, không còn thông tin giao hàng thất bại
                    $failureReason = null;
                    $failureProofImage = null;
                @endphp
                
                @if($borrow->trang_thai_chi_tiet === 'giao_hang_that_bai' && $failureReason)
                <div class="info-row" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #dc3545;">
                    <div style="width: 100%;">
                        <div class="info-label" style="color: #dc3545; font-weight: 600; margin-bottom: 15px; font-size: 16px;">Lý do giao hàng thất bại:</div>
                        <div style="padding: 15px; background: {{ $failureReason === 'loi_khach_hang' ? '#fff3cd' : '#d4edda' }}; border-radius: 8px; border-left: 4px solid {{ $failureReason === 'loi_khach_hang' ? '#ffc107' : '#28a745' }};">
                            <strong style="color: {{ $failureReason === 'loi_khach_hang' ? '#856404' : '#155724' }}; font-size: 15px;">
                                {{ $failureReason === 'loi_khach_hang' ? 'Lỗi do Khách hàng' : 'Lỗi do Sách/Thư viện' }}
                            </strong>
                            @if($failureReason === 'loi_khach_hang')
                            <div style="margin-top: 12px; font-size: 0.95em; color: #856404;">
                                <p style="margin: 6px 0;">• <strong>Lý do:</strong> Đổi ý, không nghe máy, từ chối nhận hàng...</p>
                                <p style="margin: 6px 0;">• <strong>Hoàn:</strong> Phí thuê (100%)</p>
                            </div>
                            @else
                            <div style="margin-top: 12px; font-size: 0.95em; color: #155724;">
                                <p style="margin: 6px 0;">• <strong>Lý do:</strong> Sách rách, bẩn, sai tên sách, thiếu sách...</p>
                                <p style="margin: 6px 0;">• <strong>Hoàn:</strong> 100% phí thuê</p>
                                <p style="margin: 6px 0; font-weight: 600;">→ Khách được hoàn toàn bộ 100%</p>
                            </div>
                            @endif
                        </div>
                        @if($failureProofImage)
                        <div style="margin-top: 12px;">
                            <span class="info-label" style="display: block; margin-bottom: 6px;">Ảnh minh chứng:</span>
                            <img src="{{ $failureProofImage }}" alt="Ảnh minh chứng giao hàng thất bại" style="max-width: 240px; border-radius: 6px; border: 1px solid #ddd;">
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Sách mượn -->
            <div class="detail-section">
                <div class="section-title"><i class="fas fa-book"></i> Sách đã mượn ({{ is_countable($borrow->items ?? null) ? count($borrow->items) : 0 }} cuốn)
                </div>
                @foreach($borrow->items as $item)
                    <div class="book-item">
                        @if($item->book)
                            <img src="{{ $item->book->image_url ?? asset('images/default-book.png') }}"
                                alt="{{ $item->book->ten_sach }}" class="book-image">
                            <div class="book-info">
                                <div class="book-title">{{ $item->book->ten_sach }}</div>
                                <div class="book-author">Tác giả: {{ $item->book->tac_gia ?? 'Chưa cập nhật' }}</div>
                                <div class="book-meta">
                                    <div>📅 Ngày hẹn trả: {{ \Carbon\Carbon::parse($item->ngay_hen_tra)->format('d/m/Y') }}</div>
                                    @if($item->ngay_tra_thuc_te)
                                        <div>✅ Đã trả ngày: {{ \Carbon\Carbon::parse($item->ngay_tra_thuc_te)->format('d/m/Y') }}</div>
                                    @endif
                                    @if($item->inventory)
                                        <div>🏷️ Mã sách: {{ $item->inventory->barcode ?? 'N/A' }}</div>
                                    @endif
                                </div>
                                <div style="margin-top: 10px;">
                                    <strong>Trạng thái:</strong>
                                    @if($item->trang_thai === 'Cho duyet')
                                        <span class="status-badge status-Cho-duyet">⏳ Chờ duyệt</span>
                                    @elseif($item->trang_thai === 'Dang muon')
                                        <span class="status-badge status-Dang-muon">📖 Đang mượn</span>
                                    @elseif($item->trang_thai === 'Da tra')
                                        <span class="status-badge status-Da-tra">✅ Đã trả</span>
                                    @elseif($item->trang_thai === 'Huy')
                                        <span class="status-badge status-Huy">❌ Đã hủy</span>
                                    @else
                                        <span class="status-badge">{{ $item->trang_thai }}</span>
                                    @endif
                                </div>

                                @if($item->anh_bia_truoc || $item->anh_bia_sau || $item->anh_gay_sach)
                                    <div style="margin-top: 12px;">
                                        <strong style="display:block; margin-bottom: 6px;">Ảnh xác nhận nhận sách:</strong>
                                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                            @if($item->anh_bia_truoc)
                                                <a href="{{ $item->anh_bia_truoc }}" target="_blank">
                                                    <img src="{{ $item->anh_bia_truoc }}" alt="Bìa trước" style="width:70px; height:70px; object-fit:cover; border:1px solid #ddd; border-radius:6px;">
                                                </a>
                                            @endif
                                            @if($item->anh_bia_sau)
                                                <a href="{{ $item->anh_bia_sau }}" target="_blank">
                                                    <img src="{{ $item->anh_bia_sau }}" alt="Bìa sau" style="width:70px; height:70px; object-fit:cover; border:1px solid #ddd; border-radius:6px;">
                                                </a>
                                            @endif
                                            @if($item->anh_gay_sach)
                                                <a href="{{ $item->anh_gay_sach }}" target="_blank">
                                                    <img src="{{ $item->anh_gay_sach }}" alt="Gáy sách" style="width:70px; height:70px; object-fit:cover; border:1px solid #ddd; border-radius:6px;">
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($item->ghi_chu_nhan_sach)
                                    <div style="margin-top: 10px;">
                                        <strong>Ghi chú nhận sách:</strong>
                                        <div>{{ $item->ghi_chu_nhan_sach }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Thanh toán -->
            <div class="detail-section">
                <div class="section-title"><i class="fas fa-money-bill-wave"></i> Thông tin thanh toán</div>
                @php
                    // Đồng bộ hiển thị tài chính từ borrow_items để tránh lệch tiền thuê = 0
                    $tienCocDisplay = ($borrow->items && $borrow->items->count() > 0)
                        ? (float) $borrow->items->sum('tien_coc')
                        : (float) ($borrow->tien_coc ?? 0);

                    $tienThueDisplay = ($borrow->items && $borrow->items->count() > 0)
                        ? (float) $borrow->items->sum('tien_thue')
                        : (float) ($borrow->tien_thue ?? 0);

                    $shippingFeeDisplay = ($borrow->items && $borrow->items->count() > 0)
                        ? (float) $borrow->items->sum('tien_ship')
                        : (float) ($borrow->tien_ship ?? 0);
                @endphp
                <div class="price-summary">
                    <div class="price-row">
                        <span>Tiền thuê:</span>
                        <span>{{ number_format($tienThueDisplay, 0, ',', '.') }}₫</span>
                    </div>
                    @if($borrow->voucher)
                        <div class="price-row">
                            <span>Giảm giá ({{ $borrow->voucher->ma_voucher }}):</span>
                            <span>-{{ number_format($borrow->voucher->gia_tri, 0, ',', '.') }}{{ $borrow->voucher->loai === 'phan_tram' ? '%' : '₫' }}</span>
                        </div>
                    @endif
                    @if($borrow->trang_thai_chi_tiet === 'giao_hang_that_bai' && $failureReason === 'loi_khach_hang')
                        @php
                            // Tính toán chi tiết cho trường hợp lỗi khách hàng (dựa trên số liệu đã đồng bộ từ items)
                            $tienCoc = $tienCocDisplay;
                            $tienThue = $tienThueDisplay;
                            $tienShip = $shippingFeeDisplay;
                            $tongTienGoc = $tienCoc + $tienThue + $tienShip;
                            
                            // Tính phí phạt
                            $phiPhat = $tienCoc * 0.20; // 20% tiền cọc
                            $tienCocHoan = $tienCoc * 0.80; // 80% tiền cọc
                            $tongTienKhachMat = $phiPhat + $tienShip; // Phí phạt + phí ship
                            $tongTienHoan = $tienThue + $tienCocHoan; // Phí thuê + 80% cọc
                            $tongTienCuoi = $tongTienGoc - $tongTienKhachMat; // Tổng sau khi trừ
                        @endphp
                        <div class="price-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px dashed #ffc107;">
                            <div style="width: 100%;">
                                <div style="color: #dc3545; font-weight: 600; margin-bottom: 10px;">Chi tiết hoàn tiền (Lỗi khách hàng):</div>
                                <div style="padding: 12px; background: #fff3cd; border-radius: 6px; margin-bottom: 10px;">
                                    <div style="margin-bottom: 8px;">
                                        <span style="color: #28a745;">✓ Hoàn phí thuê:</span>
                                        <span style="float: right; font-weight: 600;">{{ number_format($tienThue, 0, ',', '.') }}₫</span>
                                    </div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="color: #28a745;">✓ Hoàn tiền cọc (80%):</span>
                                        <span style="float: right; font-weight: 600;">{{ number_format($tienCocHoan, 0, ',', '.') }}₫</span>
                                    </div>
                                    <div style="margin-bottom: 8px; color: #dc3545;">
                                        <span>✗ Trừ phí phạt (20% cọc):</span>
                                        <span style="float: right; font-weight: 600;">- {{ number_format($phiPhat, 0, ',', '.') }}₫</span>
                                    </div>
                                    <div style="margin-bottom: 8px; color: #dc3545;">
                                        <span>✗ Không hoàn phí ship:</span>
                                        <span style="float: right; font-weight: 600;">- {{ number_format($tienShip, 0, ',', '.') }}₫</span>
                                    </div>
                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e0e0e0;">
                                        <span style="font-weight: 600;">Tổng khách mất:</span>
                                        <span style="float: right; color: #dc3545; font-weight: 600;">{{ number_format($tongTienKhachMat, 0, ',', '.') }}₫</span>
                                    </div>
                                    <div style="margin-top: 8px;">
                                        <span style="font-weight: 600;">Tổng hoàn lại:</span>
                                        <span style="float: right; color: #28a745; font-weight: 600;">{{ number_format($tongTienHoan, 0, ',', '.') }}₫</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="price-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #e9ecef;">
                            <span style="text-decoration: line-through; color: #999;">Tổng tiền ban đầu:</span>
                            <span style="text-decoration: line-through; color: #999;">{{ number_format($tongTienGoc, 0, ',', '.') }}₫</span>
                        </div>
                        <div class="price-row">
                            <span style="font-weight: 600; color: #dc3545;">Tổng tiền sau khi trừ:</span>
                            <span style="font-weight: 600; color: #dc3545;">{{ number_format($tongTienCuoi, 0, ',', '.') }}₫</span>
                        </div>
                    @elseif($borrow->trang_thai_chi_tiet === 'giao_hang_that_bai' && $failureReason === 'loi_thu_vien')
                        @php
                            $tienCoc = $tienCocDisplay;
                            $tienThue = $tienThueDisplay;
                            $tienShip = $shippingFeeDisplay;
                            $tongTienHoan = $tienCoc + $tienThue + $tienShip;
                        @endphp
                        <div class="price-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px dashed #28a745;">
                            <div style="width: 100%;">
                                <div style="color: #28a745; font-weight: 600; margin-bottom: 10px;">Chi tiết hoàn tiền (Lỗi thư viện):</div>
                                <div style="padding: 12px; background: #d4edda; border-radius: 6px;">
                                    <div style="margin-bottom: 8px;">
                                        <span style="color: #28a745;">✓ Hoàn 100% phí thuê:</span>
                                        <span style="float: right; font-weight: 600;">{{ number_format($tienThue, 0, ',', '.') }}₫</span>
                                    </div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="color: #28a745;">✓ Hoàn 100% tiền cọc:</span>
                                        <span style="float: right; font-weight: 600;">{{ number_format($tienCoc, 0, ',', '.') }}₫</span>
                                    </div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="color: #28a745;">✓ Hoàn 100% phí ship:</span>
                                        <span style="float: right; font-weight: 600;">{{ number_format($tienShip, 0, ',', '.') }}₫</span>
                                    </div>
                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e0e0e0;">
                                        <span style="font-weight: 600;">Tổng hoàn lại:</span>
                                        <span style="float: right; color: #28a745; font-weight: 600;">{{ number_format($tongTienHoan, 0, ',', '.') }}₫</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="price-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #e9ecef;">
                            <span style="font-weight: 600; color: #28a745;">Tổng tiền hoàn lại:</span>
                            <span style="font-weight: 600; color: #28a745;">{{ number_format($tongTienHoan, 0, ',', '.') }}₫</span>
                        </div>
                    @else
                    <div class="price-row">
                        <span>Tổng cộng:</span>
                        <span>
                            @php
                                // Tính lại tổng tiền = cọc + thuê + ship (dựa trên số liệu display)
                                $tienCoc = $tienCocDisplay;
                                $tienThue = $tienThueDisplay;
                                $tienShip = $shippingFeeDisplay; // Đã tính ở trên
                                $tongTien = $tienCoc + $tienThue + $tienShip;
                            @endphp
                            {{ number_format($tongTien, 0, ',', '.') }}₫
                        </span>
                    </div>
                    @endif
                </div>

                @if($borrow->payments->count() > 0)
                    <div style="margin-top: 20px;">
                        <strong>Phương thức thanh toán:</strong>
                        @php $payment = $borrow->payments->first(); @endphp
                        @if($payment->payment_method === 'online')
                            <span>💳 Thanh toán online</span>
                        @else
                            <span>💰 Thanh toán khi nhận hàng</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Action buttons -->
            <div class="action-buttons">
                <a href="{{ route('orders.index') }}" class="btn-custom btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                @php
                    // Không cho phép hủy khi đang vận chuyển
                    $canCancel = $borrow->trang_thai === 'Cho duyet' 
                        && !in_array($borrow->trang_thai_chi_tiet, [
                            'cho_ban_giao_van_chuyen',
                            'dang_giao_hang',
                            'giao_hang_thanh_cong',
                            'dang_van_chuyen_tra_ve'
                        ]);
                @endphp
                @if($canCancel)
                    <button class="btn-custom btn-cancel" onclick="showCancelModal()">
                        <i class="fas fa-times-circle"></i> Hủy đơn mượn
                    </button>
                @elseif(in_array($borrow->trang_thai_chi_tiet, ['cho_ban_giao_van_chuyen', 'dang_giao_hang', 'giao_hang_thanh_cong']))
                    <div style="padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; color: #856404; font-size: 14px;">
                        <strong>❌ Không thể hủy đơn:</strong> Đơn hàng đã được bàn giao cho đơn vị vận chuyển.
                    </div>
                @endif
                
                {{-- Hiển thị nút "Nhận sách" khi đang giao hàng --}}
                @if(in_array($borrow->trang_thai_chi_tiet, ['dang_giao_hang', 'giao_hang_thanh_cong']) && !$borrow->customer_confirmed_delivery)
                    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border: 2px solid #2196f3; border-radius: 8px;">
                        <h4 style="margin-top: 0; color: #1976d2; margin-bottom: 15px;">
                            <i class="fas fa-box-open"></i> Xác nhận nhận sách
                        </h4>
                        <p style="color: #555; margin-bottom: 15px;">
                            Bạn đã nhận được sách chưa? Vui lòng xác nhận sau khi đã kiểm tra sách.
                        </p>
                        <form action="{{ route('account.borrows.confirm-delivery', $borrow->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn đã nhận được sách và muốn xác nhận không?');">
                            @csrf
                            <button type="submit" class="btn-custom" style="background: #4caf50; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer;">
                                <i class="fas fa-check-circle"></i> Tôi đã nhận được sách
                            </button>
                        </form>
                    </div>
                @elseif($borrow->customer_confirmed_delivery)
                    <div style="margin-top: 20px; padding: 15px; background: #d4edda; border: 2px solid #28a745; border-radius: 8px;">
                        <p style="margin: 0; color: #155724; font-weight: 600;">
                            <i class="fas fa-check-circle"></i> Bạn đã xác nhận nhận sách vào 
                            @if($borrow->customer_confirmed_delivery_at)
                                @php
                                    $confirmedAt = $borrow->customer_confirmed_delivery_at;
                                    if (!$confirmedAt instanceof \Carbon\Carbon) {
                                        $confirmedAt = \Carbon\Carbon::parse($confirmedAt);
                                    }
                                @endphp
                                {{ $confirmedAt->format('d/m/Y H:i') }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Hủy Đơn -->
    <div id="cancelModal" class="modal-custom">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h5 style="margin: 0; font-size: 1.125rem; font-weight: 600;">Xác nhận hủy đơn mượn</h5>
                <button type="button" class="btn-close-custom" onclick="hideCancelModal()">&times;</button>
            </div>
            <div class="modal-body-custom">
                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">Vui lòng cho chúng tôi biết lý do bạn muốn hủy đơn mượn này.</p>
                <textarea id="cancelReason" class="form-control-custom" placeholder="Nhập lý do hủy đơn (tối thiểu 10 ký tự)..."></textarea>
                <div id="errorMessage" style="color: #dc3545; margin-top: 0.5rem; display: none; font-size: 0.75rem; font-weight: 500;"></div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-custom" onclick="hideCancelModal()">Đóng</button>
                <button type="button" class="btn-custom btn-cancel" onclick="confirmCancel()">Xác nhận hủy</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const borrowId = {{ $borrow->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function showCancelModal() {
            document.getElementById('cancelModal').classList.add('active');
            document.getElementById('cancelReason').value = '';
            document.getElementById('errorMessage').style.display = 'none';
        }

        function hideCancelModal() {
            document.getElementById('cancelModal').classList.remove('active');
        }

        function confirmCancel() {
            const reason = document.getElementById('cancelReason').value.trim();
            const errorDiv = document.getElementById('errorMessage');

            // Validate
            if (reason.length < 10) {
                errorDiv.textContent = 'Lí do hủy đơn phải có ít nhất 10 ký tự';
                errorDiv.style.display = 'block';
                return;
            }

            // Disable button
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

            // Send request
            fetch(`/borrows/${borrowId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    cancellation_reason: reason
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if(window.showGlobalModal) {
                            window.showGlobalModal('Thành công', 'Đã hủy đơn mượn thành công!', 'success');
                        } else {
                            if(window.showGlobalModal) {
                                window.showGlobalModal('Thành công', 'Đã hủy đơn mượn thành công!', 'success');
                            } else if(window.alert) {
                                window.alert('Thành công', 'Đã hủy đơn mượn thành công!');
                            } else {
                                alert('✅ Đã hủy đơn mượn thành công!');
                            }
                        }
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        errorDiv.textContent = data.message || 'Có lỗi xảy ra khi hủy đơn mượn';
                        errorDiv.style.display = 'block';
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check"></i> Xác nhận hủy';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorDiv.textContent = 'Có lỗi xảy ra khi hủy đơn mượn';
                    errorDiv.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> Xác nhận hủy';
                });
        }

        // Close modal when clicking outside
        document.getElementById('cancelModal').addEventListener('click', function (e) {
            if (e.target === this) {
                hideCancelModal();
            }
        });
    </script>
@endpush