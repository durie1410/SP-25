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

        .book-list-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            align-items: start;
        }

        .book-item {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            position: relative;
            overflow: hidden;
            height: auto;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .book-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.1);
            border-color: rgba(59, 130, 246, 0.25);
        }

        .book-image {
            width: 100% !important;
            max-width: 100%;
            aspect-ratio: 3 / 4;
            height: auto !important;
            object-fit: cover;
            object-position: center;
            border-radius: 10px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
            background: #fff;
            display: block;
            margin: 0;
        }

        .book-info {
            display: flex;
            flex-direction: column;
            gap: 0;
            width: 100%;
            min-width: 0;
        }

        .book-title {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
            line-height: 1.35;
            overflow-wrap: break-word;
        }

        .detail-section .book-author,
        .detail-section .book-meta,
        .detail-section .book-meta div,
        .detail-section .book-action-link,
        .detail-section .history-review-note {
            overflow-wrap: break-word;
        }

        .book-meta {
            display: grid;
            gap: 5px;
            font-size: 13px;
            color: #475569;
            margin-bottom: 0;
        }

        .book-meta div {
            line-height: 1.5;
        }

        .book-status-row {
            margin-top: 8px;
        }

        .book-item .status-badge {
            width: fit-content;
            white-space: nowrap;
            font-size: 12px;
            padding: 6px 10px;
        }

        .book-secondary-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            column-gap: 10px;
            row-gap: 8px;
            margin-top: 10px;
            margin-bottom: 0;
            width: 100%;
        }

        .book-action-link,
        .btn-rerent {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 34px;
            padding: 8px 12px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            text-decoration: none;
        }

        .book-item .book-action-link {
            background: #eef2ff;
            border: 1px solid rgba(99, 102, 241, 0.18);
            color: #4338ca;
        }

        .book-item .book-action-link:hover {
            background: #e0e7ff;
        }

        .book-item .btn-rerent {
            border: 1px solid rgba(37, 99, 235, 0.2);
            color: #fff;
        }

        .book-item .history-review-note {
            margin-top: 10px;
            margin-bottom: 0;
            padding: 10px 12px;
            border-radius: 10px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-left: 3px solid #fb923c;
            color: #9a3412;
            font-size: 13px;
            line-height: 1.5;
            font-weight: 500;
        }

        .book-item .history-review-box {
            margin-top: 10px;
            border-radius: 12px;
            padding: 14px;
        }

        @media (max-width: 1200px) {
            .book-list-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .book-list-grid {
                grid-template-columns: 1fr;
            }

            .book-item {
                padding: 14px;
            }
        }

        .book-author {
        .book-image {
            width: 100% !important;
            min-width: 100%;
            max-width: 100%;
            height: 220px !important;
            min-height: 220px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: block;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .book-info {
            flex: 1;
            min-width: 0;
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
        }

        .book-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
            overflow: visible;
            -webkit-line-clamp: unset;
            -webkit-box-orient: initial;
            word-break: normal;
            overflow-wrap: break-word;
        }

        .detail-section .book-author,
        .detail-section .book-meta,
        .detail-section .book-meta div,
        .detail-section .book-action-link,
        .detail-section .history-review-note {
            word-break: normal;
            overflow-wrap: break-word;
        }

        .detail-section .book-item,
        .detail-section .book-info {
            width: 100%;
        }

        @media (max-width: 1200px) {
            .book-list-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .book-list-grid {
                grid-template-columns: 1fr;
            }

            .book-item {
                padding: 14px;
            }
        }

        .book-author {
            color: #475569;
            margin-bottom: 12px;
            font-size: 15px;
            font-weight: 500;
        }

        .book-meta {
            font-size: 14px;
            color: #64748b;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .book-status-row {
            margin-top: 12px;
        }

        .book-secondary-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
        }

        .book-action-link,
        .btn-rerent {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .book-action-link {
            background: #eef2ff;
            color: #4338ca;
        }

        .book-action-link:hover {
            color: #3730a3;
            transform: translateY(-1px);
        }

        .rerent-form {
            display: inline-flex;
            margin: 0;
        }

        .btn-rerent {
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            box-shadow: 0 10px 18px rgba(37, 99, 235, 0.22);
        }

        .btn-rerent:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 22px rgba(37, 99, 235, 0.28);
        }

        .history-review-box {
            margin-top: 18px;
            padding: 18px;
            border-radius: 14px;
            background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .history-review-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .history-review-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .history-review-box {
            margin-top: 18px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: linear-gradient(135deg, #fffbeb, #fefce8);
            overflow: hidden;
        }

        .history-review-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid #fde68a;
            background: rgba(253, 224, 71, 0.15);
            gap: 10px;
            flex-wrap: wrap;
        }

        .history-review-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #92400e;
        }

        .history-reviewed-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
            white-space: nowrap;
        }

        .history-review-summary {
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .history-review-stars {
            display: flex;
            align-items: center;
            gap: 2px;
            font-size: 24px;
            line-height: 1;
        }

        .history-review-stars .star { color: #e5e7eb; }
        .history-review-stars .star.star-on { color: #f59e0b; }

        .history-review-text {
            margin: 0;
            color: #374151;
            font-size: 14px;
            line-height: 1.7;
            white-space: pre-line;
            font-style: italic;
            padding: 10px 14px;
            background: rgba(255,255,255,0.7);
            border-radius: 10px;
            border-left: 3px solid #f59e0b;
        }

        .history-review-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-inline-edit {
            border: 1.5px solid #10b981;
            border-radius: 10px;
            padding: 7px 14px;
            background: #fff;
            color: #059669;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s, border-color 0.2s, color 0.2s, transform 0.15s;
        }

        .btn-inline-edit:hover {
            background: #ecfdf5;
            border-color: #059669;
            color: #047857;
            transform: translateY(-1px);
        }

        .history-review-edit-meta {
            color: #9ca3af;
            font-size: 12px;
        }

        .history-review-helper {
            margin: 0 0 12px;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.55;
        }

        .history-review-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 18px;
        }

        .history-review-form.is-hidden {
            display: none;
        }

        .history-review-label {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 8px;
            display: block;
        }

        /* Star rating: đơn giản row, JS-driven highlighting */
        .history-star-rating {
            display: flex;
            flex-direction: row;
            gap: 8px;
            align-items: center;
        }

        .history-star-rating input {
            display: none;
        }

        .history-star-rating label {
            font-size: 30px;
            line-height: 1;
            color: #e5e7eb;
            cursor: pointer;
            margin: 0;
            transition: color 0.15s ease, transform 0.15s ease, text-shadow 0.15s ease;
            user-select: none;
            display: inline-block;
        }

        .history-star-rating label.star-on,
        .history-star-rating label.hover-on {
            color: #f59e0b;
        }

        .history-star-rating label.star-on {
            text-shadow: 0 0 8px rgba(245, 158, 11, 0.4);
        }

        .history-star-rating label.hover-on {
            transform: scale(1.2);
            text-shadow: 0 0 14px rgba(245, 158, 11, 0.6);
        }

        /* Read-only stars (summary) */
        .history-review-stars label {
            font-size: 24px;
            color: #e5e7eb;
            cursor: default;
            margin: 0 1px;
        }

        .history-review-stars label.star-on {
            color: #f59e0b;
        }

        .history-review-form textarea {
            width: 100%;
            min-height: 110px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 14px;
            resize: vertical;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .history-review-form textarea:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.12);
        }

        .history-review-note {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .history-review-error {
            margin-bottom: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 13px;
        }

        .btn-submit-review {
            align-self: flex-start;
            border: none;
            border-radius: 12px;
            padding: 11px 22px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.28);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-submit-review:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(245, 158, 11, 0.35);
        }

        .btn-cancel-edit {
            align-self: flex-start;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px 18px;
            background: #fff;
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-cancel-edit:hover {
            border-color: #d1d5db;
            color: #374151;
            background: #f9fafb;
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
                <div class="book-list-grid">
                @foreach($borrow->items as $item)
                    <div class="book-item">
                        @if($item->book)
                            @php
                                $existingReview = $userReviews->get($item->id);
                                $canReviewBook = $item->book->hasCompletedBorrowByUser(auth()->id());
                                $canRerentBook = $item->trang_thai === 'Da tra' || !empty($item->ngay_tra_thuc_te);
                                // rating: dùng old('rating') nếu validate fail, không thì dùng existing, mặc định 5
                                $currentRating = (int) old('rating', optional($existingReview)->rating ?? 5);
                                $activeBorrowItemId = (int) old('borrow_item_id', 0);
                                // form chỉ hiện khi: chưa đánh giá HOẶC (đã đánh giá + còn hạn + đang edit đúng item)
                                $showInlineEdit = (!$existingReview) || ($existingReview && $existingReview->canBeEditedBy(auth()->id()) && $activeBorrowItemId === (int) $item->id);
                                $canEditExistingReview = $existingReview ? $existingReview->canBeEditedBy(auth()->id()) : false;
                            @endphp
                            <img src="{{ $item->book->image_url ?? asset('images/default-book.png') }}"
                                alt="{{ $item->book->ten_sach }}" class="book-image">
                            <div class="book-info">
                                <div class="book-title">{{ $item->book->ten_sach }}</div>
                                <div class="book-author">{{ $item->book->tac_gia ?? 'Chưa cập nhật' }}</div>
                                <div class="book-meta">
                                    <div>📅 Hẹn trả: {{ \Carbon\Carbon::parse($item->ngay_hen_tra)->format('d/m/Y') }}</div>
                                    @if($item->ngay_tra_thuc_te)
                                        <div>✅ Đã trả: {{ \Carbon\Carbon::parse($item->ngay_tra_thuc_te)->format('d/m/Y') }}</div>
                                    @endif
                                    @if($item->inventory)
                                        <div>🏷️ Mã sách: {{ $item->inventory->barcode ?? 'N/A' }}</div>
                                    @endif
                                </div>
                                <div class="book-status-row">
                                    @if($item->trang_thai === 'Cho duyet')
                                        <span class="status-badge status-Cho-duyet">⏳ Chờ duyệt</span>
                                    @elseif($item->trang_thai === 'Dang muon')
                                        <span class="status-badge status-Dang-muon">📖 Đang mượn</span>
                                    @elseif($item->trang_thai === 'Da tra')
                                        <span class="status-badge status-Da-tra">🟢 Đã trả</span>
                                    @elseif($item->trang_thai === 'Huy')
                                        <span class="status-badge status-Huy">❌ Đã hủy</span>
                                    @else
                                        <span class="status-badge">{{ $item->trang_thai }}</span>
                                    @endif
                                </div>

                                @php
                                    $finalCondition = trim((string) ($item->tinh_trang_sach_cuoi ?? ''));
                                    $hasPendingFine = $item->fines()->where('status', 'pending')->exists();
                                    $hasPendingDeleteRequest = false;

                                    if ($item->inventory) {
                                        $hasPendingDeleteRequest = \App\Models\BookDeleteRequest::where('inventory_id', $item->inventory->id)
                                            ->where('status', 'pending')
                                            ->exists();
                                    }
                                @endphp
                                @if(!empty($item->ngay_tra_thuc_te))
                                    <div style="margin-top: 8px;">
                                        @if($finalCondition === 'binh_thuong')
                                            @if($hasPendingFine)
                                                <span class="status-badge status-Cho-duyet">⏳ Chờ thanh toán để chuyển về kho</span>
                                            @else
                                                <span class="status-badge status-Da-tra">📦 Sách bình thường đã về kho</span>
                                            @endif
                                        @elseif(in_array($finalCondition, ['hong_nhe', 'hong_nang', 'mat_sach'], true))
                                            @if($hasPendingFine)
                                                <span class="status-badge status-Cho-duyet">⏳ Chờ thanh toán để chuyển duyệt xóa</span>
                                            @elseif($hasPendingDeleteRequest)
                                                <span class="status-badge status-Huy">🗑️ Đã chuyển sang duyệt xóa</span>
                                            @else
                                                <span class="status-badge status-Cho-duyet">⏳ Đang đồng bộ duyệt xóa</span>
                                            @endif
                                        @endif
                                    </div>
                                @endif

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

                                <div class="book-secondary-actions">
                                    <a href="{{ route('books.show', $item->book->id) }}" class="book-action-link">
                                        <i class="fas fa-eye"></i> Xem sách
                                    </a>
                            
                                </div>

                                @if($canReviewBook)
                                    <div class="history-review-box">
                                        <div class="history-review-header">
                                            <h4>⭐ Đánh giá của bạn</h4>
                                            @if($existingReview)
                                                <span class="history-reviewed-badge">
                                                    <i class="fas fa-check-circle"></i> Đã đánh giá
                                                </span>
                                            @endif
                                        </div>

                                        {{-- ===== ĐÃ CO DIA GIA (khong cho sua) ===== --}}
                                        @if($existingReview && !$canEditExistingReview)
                                            <div class="history-review-summary">
                                                <div class="history-review-stars">
                                                    @for($star = 1; $star <= 5; $star++)
                                                        <span class="star {{ $star <= (int) $existingReview->rating ? 'star-on' : '' }}">★</span>
                                                    @endfor
                                                </div>
                                                @if(!empty($existingReview->comment))
                                                    <p class="history-review-text">{{ $existingReview->comment }}</p>
                                                @endif
                                                <span class="history-review-edit-meta" style="color:#9ca3af; font-size:12px;">Đã hết thời gian sửa đánh giá</span>
                                            </div>

                                        {{-- ===== CHUA CO / CON HAN SUA → FORM ===== --}}
                                        @else
                                            @if($existingReview)
                                            {{-- Read-only summary (collapsed view) --}}
                                            <div class="history-review-summary">
                                                <div class="history-review-stars">
                                                    @for($star = 1; $star <= 5; $star++)
                                                        <span class="star {{ $star <= (int) $existingReview->rating ? 'star-on' : '' }}">★</span>
                                                    @endfor
                                                </div>
                                                @if(!empty($existingReview->comment))
                                                    <p class="history-review-text">{{ $existingReview->comment }}</p>
                                                @endif
                                                <div class="history-review-actions">
                                                    <button type="button" class="btn-inline-edit" onclick="toggleHistoryReviewForm({{ $item->id }}, true)">
                                                        <i class="fas fa-pen"></i> Sửa đánh giá
                                                    </button>
                                                    <span class="history-review-edit-meta">Sửa trước: {{ optional($existingReview->edit_deadline)->format('d/m/Y') }}</span>
                                                </div>
                                            </div>
                                            @endif

                                            {{-- Form (new review OR editing existing) --}}
                                            <form
                                                action="{{ route('books.comments.store', $item->book->id) }}"
                                                method="POST"
                                                id="history-review-form-{{ $item->id }}"
                                                class="history-review-form {{ $showInlineEdit ? '' : 'is-hidden' }}">

                                                @csrf
                                                <input type="hidden" name="borrow_item_id" value="{{ $item->id }}">

                                                @if(($errors->has('rating') || $errors->has('content') || $errors->has('borrow_item_id')) && $activeBorrowItemId === (int) $item->id)
                                                    <div class="history-review-error">
                                                        {{ $errors->first('content') ?: $errors->first('rating') ?: $errors->first('borrow_item_id') }}
                                                    </div>
                                                @endif

                                                {{-- Hàng sao: luôn 5 sao, value 1→5 --}}
                                                <div class="history-review-label" style="margin-bottom:10px;">
                                                    {{ $existingReview ? 'Cập nhật số sao' : 'Chấm sao cho cuốn sách này' }}
                                                </div>
                                                <div class="history-star-rating" style="gap:8px;">
                                                    @for($star = 1; $star <= 5; $star++)
                                                        <input
                                                            type="radio"
                                                            id="rating-{{ $item->id }}-{{ $star }}"
                                                            name="rating"
                                                            value="{{ $star }}"
                                                            {{ $currentRating === $star ? 'checked' : '' }}>
                                                        <label for="rating-{{ $item->id }}-{{ $star }}"
                                                               data-value="{{ $star }}"
                                                               class="star-label {{ $star <= $currentRating ? 'star-on' : '' }}">★</label>
                                                    @endfor
                                                </div>

                                                <div>
                                                    <label class="history-review-label" for="review-content-{{ $item->id }}">
                                                        {{ $existingReview ? 'Cập nhật nhận xét' : 'Nhận xét của bạn' }}
                                                    </label>
                                                    <textarea
                                                        id="review-content-{{ $item->id }}"
                                                        name="content"
                                                        maxlength="1500"
                                                        placeholder="{{ $existingReview ? 'Cập nhật cảm nhận của bạn...' : 'Chia sẻ cảm nhận của bạn...' }}">{{ old('content', $existingReview->comment ?? '') }}</textarea>
                                                </div>

                                                <div class="history-review-actions">
                                                    <button type="submit" class="btn-submit-review">
                                                        <i class="fas fa-paper-plane"></i>
                                                        {{ $existingReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}
                                                    </button>
                                                    @if($existingReview)
                                                        <button type="button" class="btn-cancel-edit" onclick="toggleHistoryReviewForm({{ $item->id }}, false)">
                                                            <i class="fas fa-chevron-up"></i> Ẩn chỉnh sửa
                                                        </button>
                                                    @endif
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <div class="history-review-note">
                                        <i class="fas fa-clock"></i>
                                        Bạn có thể đánh giá cuốn sách này ngay tại lịch sử đơn mượn sau khi đã hoàn tất mượn/trả.
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
                </div>
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

                
            </div>

            <!-- Action buttons -->
            <div class="action-buttons">
             

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

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const borrowId = {{ $borrow->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function toggleHistoryReviewForm(itemId, shouldOpen) {
            const form = document.getElementById(`history-review-form-${itemId}`);
            if (!form) return;

            if (shouldOpen) {
                form.classList.remove('is-hidden');
                return;
            }

            form.classList.add('is-hidden');
        }

        /* Star rating: JS-driven highlighting, simple and reliable */
        document.querySelectorAll('.history-star-rating').forEach(function(container) {
            const labels = Array.from(container.querySelectorAll('label'));
            const inputs = container.querySelectorAll('input[type="radio"]');

            /* Helper: set star-on class for stars 1..N */
            function setStarsOn(n) {
                labels.forEach(function(label) {
                    const val = parseInt(label.getAttribute('data-value'), 10);
                    if (val <= n) {
                        label.classList.add('star-on');
                    } else {
                        label.classList.remove('star-on');
                    }
                });
            }

            /* Initialize from checked radio */
            const checkedInput = container.querySelector('input[type="radio"]:checked');
            setStarsOn(checkedInput ? parseInt(checkedInput.value, 10) : 0);

            /* Hover: fill stars from left to hovered position */
            labels.forEach(function(label) {
                label.addEventListener('mouseenter', function() {
                    const val = parseInt(label.getAttribute('data-value'), 10);
                    labels.forEach(function(l) {
                        const v = parseInt(l.getAttribute('data-value'), 10);
                        if (v <= val) {
                            l.classList.add('hover-on');
                        } else {
                            l.classList.remove('hover-on');
                        }
                    });
                });

                label.addEventListener('mouseleave', function() {
                    labels.forEach(function(l) {
                        l.classList.remove('hover-on');
                    });
                });
            });

            /* Click: update checked + star-on */
            inputs.forEach(function(input) {
                input.addEventListener('change', function() {
                    setStarsOn(parseInt(input.value, 10));
                });
            });
        });
    </script>
@endpush