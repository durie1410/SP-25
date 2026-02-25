<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sách: {{ $book->ten_sach }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* --- Thiết lập chung --- */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f5f5f5;
            color: #333;
        }

        h1,
        h2,
        h3 {
            margin-top: 0;
        }

        .content-wrapper {
            display: flex;
            width: 90%;
            max-width: 1300px;
            margin: 20px auto;
            gap: 20px;
        }

        /* Header sẽ sử dụng style từ style.css */

        /* --- MAIN CONTENT & SIDEBAR LAYOUT --- */
        .main-content {
            flex: 3;
            background-color: white;
            padding: 20px 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .sidebar {
            flex: 1;
            padding-top: 10px;
        }

        /* --- BORROW ORDER SUMMARY --- */
        .borrow-summary-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: #333;
            font-size: 14px;
        }

        .summary-value {
            font-weight: 600;
            color: #2196F3;
            font-size: 14px;
        }

        .summary-value.discount {
            color: #333;
        }

        .discount-input-section {
            margin: 15px 0;
            padding: 15px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }

        .discount-input-wrapper {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .discount-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .discount-input::placeholder {
            color: #999;
        }

        .apply-discount-btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
        }

        .apply-discount-btn:hover {
            background-color: #45a049;
        }

        .summary-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-top: 10px;
        }

        .summary-total-label {
            font-weight: bold;
            color: #333;
            font-size: 16px;
        }

        .summary-total-value {
            font-weight: bold;
            color: #FF6B35;
            font-size: 18px;
        }

        .btn-borrow-now {
            width: 100%;
            padding: 15px;
            background-color: #FF6B35;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        .btn-borrow-now:hover {
            background-color: #e55a2b;
        }

        .btn-borrow-now:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .terms-text {
            font-size: 12px;
            color: #666;
            text-align: center;
            margin-top: 12px;
            line-height: 1.5;
        }

        .terms-text strong {
            color: #333;
            font-weight: 600;
        }

        .summary-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
        }

        .summary-detail-label {
            color: #666;
        }

        .summary-detail-value {
            color: #333;
            font-weight: 500;
        }

        .breadcrumb {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: #666;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: #d9534f;
        }

        /* --- BOOK DETAILS --- */
        .book-detail-section {
            padding: 20px 0;
        }

        .book-summary {
            display: flex;
            gap: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .book-cover {
            width: 200px;
            height: auto;
            flex-shrink: 0;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .info-and-buy {
            flex: 1;
        }

        .info-and-buy h1 {
            font-size: 1.5em;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .info-and-buy p {
            margin: 5px 0;
            color: #666;
        }

        .rating {
            font-size: 0.9em;
            color: #666;
            margin: 10px 0;
        }

        .stars {
            color: orange;
            letter-spacing: 2px;
        }

        /* --- BUY OPTIONS & BUTTONS --- */
        .buy-options {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            background-color: #fcfcfc;
        }

        .buy-options label {
            font-weight: bold;
            display: block;
            margin-bottom: 15px;
        }

        .option-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .option-row .type {
            font-weight: bold;
            font-size: 1.1em;
        }

        .option-row .duration {
            color: #666;
        }

        .option-row input[type="checkbox"] {
            cursor: pointer;
            accent-color: #4CAF50;
        }

        .option-row input[type="checkbox"]:checked {
            accent-color: #4CAF50;
        }

        .price,
        .final-price,
        .total-price,
        .price-breakdown,
        .price-row,
        .book-price {
            display: none !important;
        }

        .total-price {
            display: none !important;
        }

        .total-price span:first-child {
            font-weight: bold;
        }

        .action-buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            border: none;
            transition: opacity 0.2s;
            font-size: 1em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-buy {
            background-color: #cc0000;
            color: white;
            flex: 1;
        }

        .btn-cart {
            background-color: white;
            color: #cc0000;
            border: 1px solid #cc0000;
            flex: 1;
        }

        .btn:hover {
            opacity: 0.9;
        }

        /* --- MODAL PHIẾU MƯỢN PREMIUM --- */
        .borrow-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .borrow-modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .borrow-modal {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            max-width: 550px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            position: relative;
            transform: translateY(20px) scale(0.95);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .borrow-modal-overlay.active .borrow-modal {
            transform: translateY(0) scale(1);
        }

        .borrow-modal-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .borrow-modal-header h2 {
            margin: 0;
            color: #1a1a1a;
            font-size: 1.6em;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .borrow-modal-header .subtitle {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .borrow-info-section {
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.02);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .borrow-info-section h3 {
            color: #1a1a1a;
            font-size: 1.1em;
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            font-size: 1em;
            color: #444;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-value {
            font-weight: 600;
            color: #1a1a1a;
        }

        /* Quantity Controls */
        .quantity-control {
            display: flex;
            align-items: center;
            background: #f0f0f0;
            border-radius: 12px;
            padding: 4px;
            width: fit-content;
        }

        .qty-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: none;
            background: white;
            color: #1a1a1a;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .qty-btn:hover {
            background: #f8f8f8;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .qty-btn:active {
            transform: translateY(0);
        }

        .qty-input {
            width: 50px;
            text-align: center;
            border: none !important;
            background: transparent !important;
            font-weight: 700;
            font-size: 1.1rem;
            color: #1a1a1a;
            outline: none !important;
            margin: 0 10px;
        }

        .qty-input::-webkit-inner-spin-button,
        .qty-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .price-breakdown {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: 20px 0;
            background: rgba(0, 0, 0, 0.02);
            padding: 15px;
            border-radius: 12px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1em;
            color: #555;
        }

        .price-row.total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid rgba(0, 0, 0, 0.05);
            color: #1a1a1a;
            font-weight: 700;
            font-size: 1.2em;
        }

        .price-row.total span:last-child {
            color: #ef4444 !important;
            font-weight: 800;
        }

        .borrow-modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-modal {
            flex: 1;
            padding: 14px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-modal-cancel {
            background: #f0f0f0;
            color: #444;
        }

        .btn-modal-cancel:hover {
            background: #e5e5e5;
        }

        .btn-modal-confirm {
            background: linear-gradient(135deg, #FF416C 0%, #FF4B2B 100%);
            color: white;
            flex: 1.5;
            box-shadow: 0 10px 20px rgba(255, 65, 108, 0.2);
        }

        .btn-modal-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(255, 65, 108, 0.3);
        }

        .btn-modal-confirm:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .close-modal:hover {
            background: #f5f5f5;
            color: #333;
        }

        .loading-spinner {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        /* --- TABS --- */
        .tab-section {
            display: flex;
            gap: 20px;
            margin: 30px 0;
            border-bottom: 2px solid #eee;
        }

        .tab-link {
            padding: 15px 0;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            position: relative;
            transition: color 0.3s;
        }

        .tab-link.active {
            color: #333;
            font-weight: bold;
        }

        .tab-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #ffcc00;
        }

        .description-section {
            padding: 20px 0;
            line-height: 1.8;
            color: #555;
        }

        /* --- METADATA TABLE --- */
        .metadata-table {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .metadata-table h2 {
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .book-metadata {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 0.9em;
        }

        .book-metadata tr {
            border-bottom: 1px dashed #ddd;
        }

        .book-metadata td {
            padding: 10px 5px;
            vertical-align: top;
            width: 25%;
        }

        .book-metadata .label {
            font-weight: bold;
            color: #333;
        }

        /* --- COMMENTS --- */
        .comment-section {
            padding-top: 20px;
            border-top: 1px solid #eee;
            margin-top: 30px;
        }

        .comment-section h2 {
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .comment-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            margin-bottom: 5px;
            min-height: 100px;
            font-family: inherit;
            resize: vertical;
        }

        .char-count {
            font-size: 0.8em;
            color: #999;
            text-align: right;
            margin-bottom: 10px;
        }

        .btn-comment {
            background-color: #f0f0f0;
            color: #666;
            border: 1px solid #ccc;
            padding: 8px 15px;
        }

        /* --- RELATED BOOKS & AUDIOBOOKS SECTIONS --- */
        .full-width-section {
            width: 100%;
            background-color: #f5f5f5;
            padding: 40px 0;
            margin-top: 40px;
        }

        .full-width-section .section-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 60px;
        }

        .related-books-section,
        .audiobooks-section {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .audiobooks-section {
            margin-top: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .view-all-link {
            color: #cc0000;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9em;
        }

        .view-all-link:hover {
            text-decoration: underline;
        }

        .book-carousel-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .book-carousel-wrapper .book-list {
            display: flex;
            flex-direction: row;
            gap: 20px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
            flex: 1;
            padding: 10px 0;
        }

        .book-carousel-wrapper .book-list::-webkit-scrollbar {
            display: none;
        }

        .book-carousel-wrapper .book-item {
            flex: 0 0 180px;
            min-width: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0;
            gap: 8px;
        }

        .book-carousel-wrapper .book-link {
            text-decoration: none;
            color: inherit;
            width: 100%;
        }

        .book-carousel-wrapper .book-cover {
            width: 100%;
            height: 240px;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        .book-carousel-wrapper .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-carousel-wrapper .book-title {
            font-size: 0.9em;
            font-weight: 600;
            color: #333;
            margin: 0;
            line-height: 1.3;
            height: 2.6em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .book-carousel-wrapper .book-author {
            font-size: 0.85em;
            color: #666;
            margin: 0;
        }

        .book-carousel-wrapper .book-rating {
            margin: 5px 0;
        }

        .book-carousel-wrapper .book-rating .stars {
            color: #ffdd00;
            font-size: 0.9em;
        }

        .book-carousel-wrapper .book-price {
            font-size: 0.85em;
            color: #cc0000;
            font-weight: 600;
            margin: 5px 0 0 0;
        }

        .carousel-nav {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 24px;
            color: #333;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }

        .carousel-nav:hover {
            background: #f5f5f5;
            border-color: #cc0000;
            color: #cc0000;
        }

        .carousel-nav:active {
            transform: scale(0.95);
        }

        /* --- SIDEBAR --- */
        .sidebar-block {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar-block h3 {
            font-size: 20px;
            font-weight: bold;
            color: #000;
            margin: 0 0 15px 0;
            padding: 0;
            border-bottom: none;
        }

        .book-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .book-item {
            display: flex;
            align-items: flex-start;
            padding: 12px 0;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .book-item:not(:last-child) {
            border-bottom: 1px solid #f0f0f0;
        }

        .sidebar-thumb {
            width: 60px;
            height: 85px;
            object-fit: cover;
            flex-shrink: 0;
            border-radius: 4px;
        }

        .item-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 5px;
        }

        .item-details a {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            text-decoration: none;
            line-height: 1.4;
            display: block;
            margin: 0;
        }

        .item-details a:hover {
            color: #cc0000;
        }

        .item-details .stats {
            font-size: 13px;
            color: #666;
            margin: 0;
            font-weight: normal;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                flex-direction: column;
            }

            .book-summary {
                flex-direction: column;
            }

            .book-cover {
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>
    @include('components.frontend-header')

    <div style="display:none">
    <header class="main-header">
        <div class="header-top">
            <div class="logo-section">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #e51d2e 0%, #c41e2f 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-right: 8px;">
                    📚
                </div>
                <div class="logo-text">
                    <span class="logo-part1">THƯ VIỆN</span>
                    <span class="logo-part2">LibNet</span>
                </div>
            </div>
            <div class="hotline-section">
                <div class="hotline-item">
                    <span class="hotline-label">Hotline khách lẻ:</span>
                    <a href="tel:0327888669" class="hotline-number">0327888669</a>
                </div>
                <div class="hotline-item">
                    <span class="hotline-label">Hotline khách sỉ:</span>
                    <a href="tel:02439741791" class="hotline-number">02439741791 - 0327888669</a>
                </div>
            </div>
            <div class="user-actions">
                <a href="{{ route('pricing.policy') }}" class="auth-link" style="margin-right: 15px;"
                    title="Chính sách giá">
                    <i class="fas fa-tags"></i> Chính sách giá
                </a>
                @auth
                    <a href="{{ route('reservation-cart.index') }}" class="cart-link" id="reservation-cart-link" title="Giỏ đặt trước">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Giỏ sách</span>
                        <span class="cart-badge" id="reservation-cart-count" style="display: none;">0</span>
                    </a>
                    <div class="user-menu-dropdown" style="position: relative;">
                        <a href="#" class="auth-link user-menu-toggle">
                            <span class="user-icon">👤</span>
                            <span>{{ auth()->user()->name }}</span>
                        </a>
                        <div class="user-dropdown-menu">
                            <div class="dropdown-header"
                                style="padding: 12px 15px; border-bottom: 1px solid #eee; font-weight: 600; color: #333;">
                                <span class="user-icon">👤</span>
                                {{ auth()->user()->name }}
                            </div>
                            @if(auth()->user()->reader)
                                <a href="{{ route('account.borrowed-books') }}" class="dropdown-item">
                                    <span>📚</span> Sách đang mượn
                                </a>
                            @endif
                            <a href="{{ route('account') }}" class="dropdown-item">
                                <span>👤</span> Thông tin tài khoản
                            </a>
                            <a href="{{ route('account.change-password') }}" class="dropdown-item">
                                <span>🔒</span> Đổi mật khẩu
                            </a>
                            <a href="{{ route('orders.index') }}" class="dropdown-item">
                                <span>⏰</span> Lịch sử mua hàng
                            </a>
                            @if(auth()->user()->role === 'admin')
                                <div style="border-top: 1px solid #eee; margin-top: 5px;"></div>
                                <a href="{{ route('dashboard') }}" class="dropdown-item">
                                    <span>📊</span> Dashboard
                                </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item logout-btn">
                                    <span>➡️</span> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                    <style>
                        .user-menu-dropdown {
                            position: relative;
                        }

                        .user-menu-dropdown .user-dropdown-menu {
                            display: none;
                            position: absolute;
                            top: calc(100% + 5px);
                            right: 0;
                            background: white;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                            min-width: 220px;
                            z-index: 1000;
                            overflow: hidden;
                        }

                        .user-menu-dropdown:hover .user-dropdown-menu {
                            display: block;
                        }

                        .user-menu-dropdown .dropdown-item {
                            display: block;
                            padding: 10px 15px;
                            color: #333;
                            text-decoration: none;
                            border-bottom: 1px solid #eee;
                            transition: background-color 0.2s;
                            cursor: pointer;
                        }

                        .user-menu-dropdown .dropdown-item:hover {
                            background-color: #f5f5f5;
                        }

                        .user-menu-dropdown .dropdown-item.logout-btn {
                            border: none;
                            background: none;
                            width: 100%;
                            text-align: left;
                            color: #d32f2f;
                            border-top: 1px solid #eee;
                            margin-top: 5px;
                        }

                        .user-menu-dropdown .dropdown-item.logout-btn:hover {
                            background-color: #ffebee;
                        }

                        .user-menu-dropdown .dropdown-item span {
                            margin-right: 8px;
                        }
                    </style>
                @else
                    <a href="{{ route('reservation-cart.index') }}" class="cart-link" id="reservation-cart-link" title="Giỏ đặt trước">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Giỏ sách</span>
                        <span class="cart-badge" id="reservation-cart-count" style="display: none;">0</span>
                    </a>
                    <a href="{{ route('login') }}" class="auth-link">Đăng nhập</a>
                @endauth
            </div>
        </div>
        <div class="header-nav">
            <div class="search-bar">
                <form action="{{ route('books.public') }}" method="GET" class="search-form">
                    <input type="text" name="keyword" placeholder="Tìm sách, tác giả, sản phẩm mong muốn..."
                        value="{{ request('keyword') }}" class="search-input">
                    <button type="submit" class="search-button">🔍 Tìm kiếm</button>
                </form>
            </div>
        </div>
    </header>

    </div>

    <div class="content-wrapper">
        <main class="main-content">
            <p class="breadcrumb">
                <a href="{{ route('home') }}">🏠</a> /
                <span>{{ Str::limit($book->ten_sach, 50) }}</span>
            </p>

            <section class="book-detail-section">
                <div class="book-summary">
                    <img src="{{ $book->image_url }}"
                        alt="Bìa sách {{ $book->ten_sach }}" class="book-cover"
                        onerror="this.onerror=null; this.src='{{ asset('images/default-book.png') }}';">

                    <div class="info-and-buy">
                        <h1>{{ $book->ten_sach }}</h1>
                        <p>Tác giả: <strong>{{ $book->formatted_author }}</strong></p>
                        @if($book->nam_xuat_ban)
                            <p>Năm xuất bản: <strong>{{ $book->formatted_year }}</strong></p>
                        @endif

                        <div class="rating">
                            {{ $book->formatted_views }} Lượt xem |
                            {{ $book->formatted_sales }} Đã bán
                        </div>

                        <div class="buy-options">
                            @php
                                $isBorrowMode = isset($mode) && $mode === 'borrow';
                            @endphp

                            @if(false && $isBorrowMode)
                                <!-- Hiển thị thông tin giá sách -->
                                @if($book->gia && $book->gia > 0)
                                    <div
                                        style="padding: 15px; background: #fff3e0; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ff9800;">
                                        <strong style="font-size: 1.1em;">💰 Giá sách:</strong>
                                        <span
                                            style="color: #e65100; font-weight: bold; font-size: 1.2em;">{{ $book->formatted_price_short }}</span>
                                    </div>
                                @endif

                                <!-- Hiển thị thông tin số lượng sách có sẵn -->
                                <div
                                    style="padding: 15px; background: #e8f5e9; border-radius: 4px; margin-bottom: 20px; border: 1px solid #4caf50;">
                                    <strong style="font-size: 1.1em;">📚 Sách có sẵn:</strong>
                                    <span
                                        style="color: #2e7d32; font-weight: bold; font-size: 1.1em;">{{ $stats['available_copies'] ?? 0 }}
                                        cuốn</span>
                                </div>

                                <!-- Chọn số lượng mượn -->
                                <div
                                    style="padding: 15px; background: #f5f5f5; border-radius: 4px; margin-bottom: 20px; border: 1px solid #ddd;">
                                    <label
                                        style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 1em;">Số
                                        lượng mượn:</label>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <button type="button" onclick="changeBorrowQuantity(-1)"
                                            style="padding: 8px 15px; border: 1px solid #ddd; border-radius: 4px; background: white; cursor: pointer; font-size: 1.2em; font-weight: bold;">-</button>
                                        <input type="number" id="borrow-quantity" value="1" min="1"
                                            max="{{ $stats['available_copies'] ?? 1 }}"
                                            style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-size: 1.1em; font-weight: bold;"
                                            onchange="validateBorrowQuantity()">
                                        <button type="button" onclick="changeBorrowQuantity(1)"
                                            style="padding: 8px 15px; border: 1px solid #ddd; border-radius: 4px; background: white; cursor: pointer; font-size: 1.2em; font-weight: bold;">+</button>
                                        <span style="color: #666; font-size: 0.9em;">cuốn</span>
                                    </div>
                                </div>

                                <div class="action-buttons" style="display: flex; gap: 10px;">
                                    @auth
                                        <button class="btn btn-buy" onclick="addToCart()" style="flex: 1; background: #6C63FF;">
                                            <span style="font-size: 1.2em;">🛒</span> Thêm vào giỏ sách
                                        </button>
                                        <button class="btn btn-buy" onclick="borrowNow()" style="flex: 1;">
                                            <span style="font-size: 1.2em;">📖</span> Mượn ngay
                                        </button>
                                    @else
                                        <button class="btn btn-buy"
                                            onclick="alert('Vui lòng đăng nhập để mượn sách!'); window.location.href='{{ route('login') }}';"
                                            style="opacity: 0.7; cursor: pointer; width: 100%;">
                                            <span style="font-size: 1.2em;">📖</span> Mượn sách
                                        </button>
                                    @endauth
                                </div>
                            @else
                                <label>Đặt trước</label>

                                <!-- Sách giấy -->
                                <div class="option-row">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="type">📚 Sách giấy</span>
                                        <span style="font-size: 0.9em; color: #666; font-weight: normal;">
                                            (Còn {{ $stats['stock_quantity'] ?? 0 }} cuốn trong kho)
                                        </span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 5px;">
                                        <button type="button" onclick="changeQuantity('paper', -1)"
                                            style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; background: white; cursor: pointer;">-</button>
                                        <input type="number" id="paper-quantity" value="1" min="1"
                                            max="{{ $stats['stock_quantity'] ?? 999 }}"
                                            style="width: 50px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                            onchange="updateTotalPrice()">
                                        <button type="button" onclick="changeQuantity('paper', 1)"
                                            style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; background: white; cursor: pointer;">+</button>
                                    </div>
                                    <span class="price"
                                        id="paper-price">{{ number_format($book->gia ?? 111000, 0, ',', '.') }}₫</span>
                                    <input type="checkbox" id="paper-checkbox" checked onchange="updateTotalPrice()"
                                        style="width: 20px; height: 20px; cursor: pointer;">
                                </div>

                                @if(($stats['stock_quantity'] ?? 0) == 0)
                                    <div
                                        style="padding: 15px; background: #fff3cd; border-radius: 4px; margin: 15px 0; border: 1px solid #ffc107; color: #856404;">
                                        <strong>⚠️ Hết hàng:</strong> Sách này hiện đã hết hàng. Vui lòng quay lại sau!
                                    </div>
                                @endif

                                <div class="total-price">
                                    <span>Thành tiền</span>
                                    <span class="final-price"
                                        id="total-price">{{ number_format($book->gia ?? 111000, 0, ',', '.') }}₫</span>
                                </div>

                                <div class="action-buttons">
                                    <button class="btn btn-buy" onclick="addToReservationCart()" style="width: 100%; background: #0d9488;">
                                        <span style="font-size: 1.2em;">📌</span> Thêm vào giỏ đặt trước
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="tab-section">
                    <a href="#" class="tab-link active" onclick="switchTab('intro'); return false;">Giới thiệu</a>
                </div>

                <div class="description-section" id="intro-content">
                    {{ $book->formatted_description }}
                </div>

                <div class="metadata-table">
                    <h2>Thông tin xuất bản</h2>
                    <table class="book-metadata">
                        <tr>
                            <td class="label">Tác giả:</td>
                            <td>{{ $book->formatted_author }}</td>
                            <td class="label">Năm xuất bản:</td>
                            <td>{{ $book->formatted_year }}</td>
                        </tr>
                        <tr>
                            <td class="label">Nhà xuất bản:</td>
                            <td>{{ $book->publisher->ten_nha_xuat_ban ?? 'Chưa có thông tin' }}</td>
                            <td class="label">Giá sách:</td>
                            <td>—</td>
                        </tr>
                        <tr>
                            <td class="label">Số lượng:</td>
                            <td>{{ $book->formatted_quantity }} cuốn</td>
                            <td class="label">Đánh giá:</td>
                            <td>—</td>
                        </tr>
                        @if($book->so_trang)
                        <tr>
                            <td class="label">Số trang:</td>
                            <td>{{ $book->so_trang }} trang</td>
                            <td class="label">Ngôn ngữ:</td>
                            <td>Tiếng Việt</td>
                        </tr>
                        @endif
                    </table>
                </div>

                <div class="comment-section">
                    <h2>Bình luận</h2>
                    @auth
                        <form class="comment-form" action="{{ route('books.comments.store', $book->id) }}" method="POST">
                            @csrf
                            <textarea name="content" placeholder="Để lại bình luận của bạn..." maxlength="1500"
                                oninput="updateCharCount(this)" required></textarea>
                            <p class="char-count">
                                <span id="char-count">0</span>/1500
                            </p>
                            <button type="submit" class="btn btn-comment">Gửi bình luận</button>
                        </form>
                    @else
                        <div style="padding: 20px; background: #f9f9f9; border-radius: 8px; text-align: center;">
                            <p>Vui lòng <a href="{{ route('login') }}" style="color: #cc0000; font-weight: bold;">đăng
                                    nhập</a> để bình luận.</p>
                        </div>
                    @endauth

                    @if($book->reviews && $book->reviews->count() > 0)
                        <div style="margin-top: 30px;">
                            <h3 style="margin-bottom: 15px;">Bình luận ({{ $book->reviews->count() }})</h3>
                            @foreach($book->reviews->take(5) as $review)
                                @if($review->comments && $review->comments->count() > 0)
                                    @foreach($review->comments->whereNull('parent_id') as $comment)
                                        <div style="padding: 15px; background: #f9f9f9; border-radius: 8px; margin-bottom: 15px;">
                                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                                <strong>{{ $comment->user->name ?? 'Người dùng' }}</strong>
                                                <span
                                                    style="color: #666; font-size: 12px;">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <p style="margin: 0; line-height: 1.6;">{{ $comment->content }}</p>
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </main>

    </div>

    <!-- Cùng chủ đề -->
    @if($same_topic_books && $same_topic_books->count() > 0)
        <div class="related-books-section full-width-section">
            <div class="section-container">
                <div class="section-header">
                    <h2>Cùng chủ đề</h2>
                    <a href="{{ route('books.public', ['category_id' => $book->category_id]) }}" class="view-all-link">Xem
                        toàn bộ →</a>
                </div>
                <div class="book-carousel-wrapper">
                    <button class="carousel-nav carousel-prev"
                        onclick="scrollCarousel('same-topic-carousel', -1)">‹</button>
                    <div class="book-list" id="same-topic-carousel">
                        @foreach($same_topic_books as $relatedBook)
                            <div class="book-item">
                                <a href="{{ route('books.show', $relatedBook->id) }}" class="book-link">
                                    <div class="book-cover">
                                        @if($relatedBook->image_url)
                                            <img src="{{ $relatedBook->image_url }}"
                                                alt="{{ $relatedBook->ten_sach }}">
                                        @else
                                            <svg viewBox="0 0 210 297" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="210" height="297" fill="#f0f0f0" />
                                                <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-size="16"
                                                    fill="#999">📚</text>
                                            </svg>
                                        @endif
                                    </div>
                                    <p class="book-title">{{ Str::limit($relatedBook->ten_sach, 50) }}</p>
                                    @if($relatedBook->tac_gia)
                                        <p class="book-author">{{ $relatedBook->tac_gia }}</p>
                                    @endif
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-nav carousel-next" onclick="scrollCarousel('same-topic-carousel', 1)">›</button>
                </div>
            </div>
        </div>
    @endif


    @include('components.footer')

    <script>
        function switchTab(tab) {
            document.getElementById('intro-content').style.display = tab === 'intro' ? 'block' : 'none';
            document.getElementById('contents-content').style.display = tab === 'contents' ? 'block' : 'none';

            document.querySelectorAll('.tab-link').forEach(link => link.classList.remove('active'));
            event.target.classList.add('active');
        }

        function updateCharCount(textarea) {
            document.getElementById('char-count').textContent = textarea.value.length;
        }

        // Hàm thay đổi số lượng sách giấy
        function changeQuantity(type, change) {
            const quantityInput = document.getElementById('paper-quantity');
            if (!quantityInput) return;
            let currentQuantity = parseInt(quantityInput.value) || 1;
            currentQuantity += change;
            if (currentQuantity < 1) currentQuantity = 1;

            // Kiểm tra giới hạn số lượng tồn kho
            const isBorrowMode = {{ isset($mode) && $mode === 'borrow' ? 'true' : 'false' }};
            const maxQuantity = parseInt(quantityInput.getAttribute('max')) || 999;

            if (isBorrowMode) {
                // Chế độ mượn: sử dụng available_copies
                const availableCopies = {{ $stats['available_copies'] ?? 0 }};
                const maxBorrowQuantity = availableCopies;
                if (currentQuantity > maxBorrowQuantity) {
                    currentQuantity = maxBorrowQuantity;
                    alert(`Chỉ còn ${maxBorrowQuantity} cuốn sách có sẵn.`);
                }
            } else {
                // Chế độ mua: sử dụng stock_quantity
                const stockQuantity = {{ $stats['stock_quantity'] ?? 0 }};
                if (currentQuantity > stockQuantity) {
                    currentQuantity = stockQuantity;
                    alert(`Chỉ còn ${stockQuantity} cuốn sách trong kho.`);
                }
            }

            quantityInput.value = currentQuantity;
            updateTotalPrice();
        }

        // Hàm cập nhật giá tổng
        function updateTotalPrice() {
            // Kiểm tra chế độ mượn sách
            const isBorrowMode = {{ isset($mode) && $mode === 'borrow' ? 'true' : 'false' }};
            if (isBorrowMode) {
                // Ở chế độ mượn, không cần tính giá
                return;
            }

            const basePrice = {{ $book->gia ?? 111000 }};
            let totalPrice = 0;

            // Tính và cập nhật giá sách giấy
            const paperCheckbox = document.getElementById('paper-checkbox');
            if (paperCheckbox && paperCheckbox.checked) {
                const paperQuantity = parseInt(document.getElementById('paper-quantity')?.value) || 1;
                const paperTotal = basePrice * paperQuantity;
                totalPrice += paperTotal;
                const paperPriceElement = document.getElementById('paper-price');
                if (paperPriceElement) {
                    paperPriceElement.textContent = new Intl.NumberFormat('vi-VN').format(paperTotal) + '₫';
                }
            } else {
                const paperPriceElement = document.getElementById('paper-price');
                if (paperPriceElement) {
                    paperPriceElement.textContent = new Intl.NumberFormat('vi-VN').format(basePrice) + '₫';
                }
            }

            // Cập nhật giá tổng
            const totalPriceElement = document.getElementById('total-price');
            if (totalPriceElement) {
                totalPriceElement.textContent = new Intl.NumberFormat('vi-VN').format(Math.round(totalPrice)) + '₫';
            }
        }

        function addToReservationCart() {
            @guest
                if (typeof window.showToast === 'function') {
                    window.showToast('Thông báo', 'Vui lòng đăng nhập để đặt trước sách!', 'warning');
                } else {
                    alert('Vui lòng đăng nhập để đặt trước sách!');
                }
                window.location.href = '{{ route("login") }}';
                return;
            @endguest

            const paperCheckbox = document.getElementById('paper-checkbox');
            const paperChecked = paperCheckbox ? paperCheckbox.checked : false;

            if (!paperChecked) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Thông báo', 'Vui lòng chọn sản phẩm!', 'warning');
                } else {
                    alert('Vui lòng chọn sản phẩm!');
                }
                return;
            }

            const quantity = parseInt(document.getElementById('paper-quantity')?.value) || 1;
            const stockQuantity = {{ $stats['stock_quantity'] ?? 0 }};

            if (stockQuantity === 0) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Thông báo', 'Hiện không có cuốn nào sẵn sàng. Bạn vẫn có thể đặt trước.', 'info');
                }
            }

            if (quantity > stockQuantity && stockQuantity > 0) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Thông báo', `Bạn chọn ${quantity} cuốn nhưng kho chỉ còn ${stockQuantity} cuốn. (Giỏ đặt trước chỉ lưu 1 yêu cầu / sách)`, 'warning');
                }
            }

            fetch('{{ route("reservation-cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ book_id: {{ $book->id }} })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('Thành công', data.message || 'Đã thêm vào giỏ đặt trước.', 'success');
                    } else {
                        alert(data.message || 'Đã thêm vào giỏ đặt trước.');
                    }

                    if (typeof window.loadReservationCartCount === 'function') {
                        window.loadReservationCartCount();
                    }
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast('Có lỗi', data.message || 'Không thể thêm vào giỏ.', 'error');
                    } else {
                        alert(data.message || 'Không thể thêm vào giỏ.');
                    }

                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }
            })
            .catch(() => {
                if (typeof window.showToast === 'function') {
                    window.showToast('Có lỗi', 'Không thể thêm vào giỏ. Vui lòng thử lại.', 'error');
                } else {
                    alert('Không thể thêm vào giỏ. Vui lòng thử lại.');
                }
            });
        }

        function scrollCarousel(carouselId, direction) {
            const carousel = document.getElementById(carouselId);
            if (carousel) {
                const scrollAmount = 200; // Số pixel scroll mỗi lần
                carousel.scrollBy({
                    left: direction * scrollAmount,
                    behavior: 'smooth'
                });
            }
        }


        // Khởi tạo giá khi trang load
        updateTotalPrice();

        // Cập nhật tóm tắt đơn hàng mượn sách
        function updateBorrowSummary() {
            if (!isBorrowMode) return;

            const quantity = parseInt(document.getElementById('borrow-quantity')?.value) || 1;
            const bookPrice = {{ $book->gia ?? 0 }};
            const hasCard = {{ auth()->check() && auth()->user()->reader ? 'true' : 'false' }};

            // Số ngày mượn mặc định (có thể thay đổi khi người dùng mở modal)
            const defaultDays = 14;

            // Tính phí thuê (1% giá sách mỗi ngày, tính cho tất cả sách)
            // Logic thực tế sẽ do API xử lý
            const dailyRate = 0.01; // 1% mỗi ngày
            const rentalFeePerBook = Math.round((bookPrice * dailyRate * defaultDays) / 1000) * 1000;
            const totalRentalFee = rentalFeePerBook * quantity;

            // Tính tiền cọc (100% giá sách - 1:1)
            const depositRate = 1.0;
            const depositPerBook = Math.round(bookPrice * depositRate / 1000) * 1000;
            const totalDeposit = depositPerBook * quantity;

            // Phí ship mặc định 0 (có thể thay đổi khi người dùng nhập khoảng cách)
            const shippingFee = 0;

            // Giảm giá
            const productDiscount = 0;
            const orderDiscount = 0;

            // Tính tổng
            const totalBasic = totalDeposit + shippingFee;
            const subtotal = totalBasic - productDiscount;
            const totalPayment = subtotal - orderDiscount;

            // Cập nhật UI
            updateSummaryDisplay('rental-fee-display', totalRentalFee);
            updateSummaryDisplay('deposit-fee-display', totalDeposit);
            updateSummaryDisplay('shipping-fee-display', shippingFee);
            updateSummaryDisplay('total-basic-display', totalBasic);
            updateSummaryDisplay('product-discount-display', productDiscount, true);
            updateSummaryDisplay('subtotal-display', subtotal);
            updateSummaryDisplay('order-discount-display', orderDiscount, true);
            updateSummaryDisplay('total-payment-display', totalPayment);
        }

        function updateSummaryDisplay(elementId, amount, isDiscount = false) {
            const element = document.getElementById(elementId);
            if (element) {
                const prefix = isDiscount && amount > 0 ? '-' : '';
                element.textContent = prefix + new Intl.NumberFormat('vi-VN').format(amount) + '₫';
            }
        }

        function applyDiscountCode() {
            const discountInput = document.getElementById('discount-code-input');
            const code = discountInput?.value.trim();

            if (!code) {
                alert('Vui lòng nhập mã giảm giá!');
                return;
            }

            // Hiển thị loading
            const btn = event.target;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Đang kiểm tra...';

            // Gọi API kiểm tra mã giảm giá (tạm thời giả lập)
            setTimeout(() => {
                // Giả lập kiểm tra mã giảm giá
                const validCodes = ['LibNet2024', 'FREESHIP', 'DISCOUNT10'];

                if (validCodes.includes(code.toUpperCase())) {
                    alert('Áp dụng mã giảm giá thành công!\n\nLưu ý: Chức năng giảm giá đang được phát triển.');
                    discountInput.value = '';
                } else {
                    alert('Mã giảm giá không hợp lệ hoặc đã hết hạn!');
                }

                btn.disabled = false;
                btn.textContent = originalText;
            }, 500);
        }

        function borrowNowFromSummary() {
            borrowNow();
        }

        // Khởi tạo tóm tắt đơn hàng khi trang load (nếu ở chế độ mượn)
        if (isBorrowMode) {
            document.addEventListener('DOMContentLoaded', function () {
                updateBorrowSummary();
            });

            // Cập nhật ngay lập tức nếu DOM đã load
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                updateBorrowSummary();
            }
        }


        // Kiểm tra chế độ mượn sách
        const isBorrowMode = {{ isset($mode) && $mode === 'borrow' ? 'true' : 'false' }};

        // Hàm thay đổi số lượng mượn
        function changeBorrowQuantity(change) {
            const quantityInput = document.getElementById('borrow-quantity');
            if (!quantityInput) return;

            let currentQuantity = parseInt(quantityInput.value) || 1;
            currentQuantity += change;

            const maxQuantity = parseInt(quantityInput.getAttribute('max')) || 1;
            const availableCopies = {{ $stats['available_copies'] ?? 0 }};

            if (currentQuantity < 1) {
                currentQuantity = 1;
            } else if (currentQuantity > availableCopies) {
                currentQuantity = availableCopies;
                alert(`Chỉ còn ${availableCopies} cuốn sách có sẵn.`);
            }

            quantityInput.value = currentQuantity;

            // Cập nhật tóm tắt đơn hàng
            if (isBorrowMode) {
                updateBorrowSummary();
            }
        }

        // Hàm kiểm tra số lượng mượn hợp lệ
        function validateBorrowQuantity() {
            const quantityInput = document.getElementById('borrow-quantity');
            if (!quantityInput) return;

            let quantity = parseInt(quantityInput.value) || 1;
            const availableCopies = {{ $stats['available_copies'] ?? 0 }};

            if (quantity < 1) {
                quantity = 1;
                quantityInput.value = 1;
            } else if (quantity > availableCopies) {
                quantity = availableCopies;
                quantityInput.value = availableCopies;
                alert(`Chỉ còn ${availableCopies} cuốn sách có sẵn.`);
            }

            // Cập nhật tóm tắt đơn hàng
            if (isBorrowMode) {
                updateBorrowSummary();
            }
        }

        // Hàm mượn sách ngay
        function borrowNow() {
            @guest
                alert('Vui lòng đăng nhập để mượn sách!');
                window.location.href = '{{ route("login") }}';
                return;
            @endguest

            const availableCopies = {{ $stats['available_copies'] ?? 0 }};

            if (availableCopies <= 0) {
                alert('Hiện tại không còn sách có sẵn để mượn. Vui lòng thử lại sau.');
                return;
            }

            // Hiển thị modal để nhập số ngày mượn
            // Kiểm tra đăng ký độc giả sẽ được thực hiện ở trang checkout
            showBorrowModal();
        }

        // Thêm sách vào giỏ sách
        function addToCart() {
            @guest
                alert('Vui lòng đăng nhập để thêm sách vào giỏ sách!');
                window.location.href = '{{ route("login") }}';
                return;
            @endguest

            const availableCopies = {{ $stats['available_copies'] ?? 0 }};

            if (availableCopies <= 0) {
                alert('Hiện tại không còn sách có sẵn để mượn. Vui lòng thử lại sau.');
                return;
            }

            const quantity = parseInt(document.getElementById('borrow-quantity')?.value) || 1;
            const borrowDays = 14; // Mặc định 14 ngày
            const distance = 0; // Mặc định 0 km

            if (quantity > availableCopies) {
                alert(`Chỉ còn ${availableCopies} cuốn sách có sẵn. Vui lòng chọn lại số lượng.`);
                return;
            }

            // Hỏi xác nhận TRƯỚC KHI thêm vào giỏ
            if (!confirm('Bạn có muốn thêm sách này vào giỏ sách không?')) {
                return; // Nếu hủy thì không làm gì cả
            }

            // Hiển thị loading
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span style="font-size: 1.2em;">⏳</span> Đang thêm...';

            fetch('{{ route("reservation-cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    book_id: {{ $book->id }},
                    quantity: quantity,
                    borrow_days: borrowDays,
                    distance: distance,
                    note: ''
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật số lượng trong giỏ sách nếu có icon giỏ sách
                        updateCartCount(data.cart_count);
                    } else {
                        if (data.redirect) {
                            // Nếu có redirect, hỏi người dùng có muốn chuyển đến trang đó không
                            if (confirm(data.message + '\n\nBạn có muốn đăng ký ngay không?')) {
                                window.location.href = data.redirect;
                            }
                        } else {
                            alert(data.message || 'Có lỗi xảy ra khi thêm sách vào giỏ sách');
                        }
                    }
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi thêm sách vào giỏ sách');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        }

        // Cập nhật số lượng trong giỏ sách (nếu có icon giỏ sách)
        function updateCartCount(count) {
            // Cập nhật cả cart-count và borrow-cart-count
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = count;
                cartCountElement.style.display = count > 0 ? 'inline-block' : 'none';
            }

            // Cập nhật borrow-cart-count (icon giỏ sách)
            const borrowCartCountElement = document.getElementById('borrow-cart-count');
            if (borrowCartCountElement) {
                borrowCartCountElement.textContent = count;
                borrowCartCountElement.style.display = count > 0 ? 'flex' : 'none';
            }

            // Hoặc gọi hàm global nếu có
            if (typeof updateBorrowCartCount === 'function') {
                updateBorrowCartCount(count);
            }
        }

        // Helper function for custom qty buttons
        function changeQty(id, delta, min, max) {
            const input = document.getElementById(id);
            if (!input) return;
            let val = parseInt(input.value) || 0;
            val += delta;
            if (val < min) val = min;
            if (val > max) val = max;
            input.value = val;

            // Trigger the change/input event
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Hiển thị modal phiếu mượn
        function showBorrowModal() {
            const modal = document.getElementById('borrowModal');

            // Clear old content
            document.getElementById('borrowModalInfo').innerHTML = '<div class="loading-spinner">Đang tải...</div>';
            document.getElementById('borrowModalPricing').innerHTML = '';
            document.getElementById('borrowModalActions').innerHTML = '';

            // Lấy số ngày từ dropdown
            // Chỉ còn "Số ngày mượn" nằm ở giữa
            let itemsHtml = `
                <div class="borrow-item-card" style="background: rgba(0,0,0,0.02); padding: 20px; border-radius: 15px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <label style="display: block; margin-bottom: 2px; font-weight: 600; font-size: 1em; color: #1a1a1a;">Thay đổi số ngày mượn:</label>
                            <small style="color: #666; font-size: 0.8em;">Nhấn + hoặc - để điều chỉnh</small>
                        </div>
                        <div class="quantity-control">
                            <button type="button" class="qty-btn" onclick="changeQty('unified-days-input', -1, 0, 30)">-</button>
                            <input type="number" id="unified-days-input" class="qty-input" value="14" min="0" max="30" readonly onchange="updateBorrowQuoteUnified()">
                            <button type="button" class="qty-btn" onclick="changeQty('unified-days-input', 1, 0, 30)">+</button>
                        </div>
                    </div>
                </div>
            `;

            modal.classList.add('active');

            // Hiển thị form
            document.getElementById('borrowModalInputs').innerHTML = itemsHtml;

            // Load thông tin giá
            updateBorrowQuoteUnified();

        }

        // Đóng modal
        function closeBorrowModal() {
            document.getElementById('borrowModal').classList.remove('active');
        }


        // Hàm mới: Cập nhật thông tin giá thống nhất cho tất cả quyển
        function updateBorrowQuoteUnified() {
            const daysInput = document.getElementById('unified-days-input');
            const borrowQuantityInput = document.getElementById('borrow-quantity');
            const borrowQuantity = parseInt(borrowQuantityInput?.value) || 1;
            const days = parseInt(daysInput?.value) || 14;

            if (!daysInput) {
                return;
            }

            // Khoảng cách luôn là 0 ở modal (sẽ tính ở trang checkout)
            const distance = 0;

            if (days < 0 || days > 30) {
                document.getElementById('borrowModalInfo').innerHTML =
                    '<div style="text-align: center; padding: 20px; color: #cc0000;">Số ngày mượn phải từ 0 đến 30 ngày.</div>';
                return;
            }

            // Xác định thông tin người dùng
            const kycStatus = '{{ $kycStatus ?? "unverified" }}';
            const userId = {{ auth()->id() ?? 'null' }};
            const deliveryType = distance > 0 ? 'ship' : 'pickup';

            // Gọi API để lấy giá
            const apiUrl = `/api/pricing/quote?book_ids[]={{ $book->id }}&kyc_status=${kycStatus}&delivery_type=${deliveryType}&distance=${distance}&days=${days}`;
            const finalUrl = userId ? `${apiUrl}&user_id=${userId}` : apiUrl;

            fetch(finalUrl)
                .then(response => response.json())
                .then(data => {
                    const rentalFeePerBook = data.items?.[0]?.rental_fee || 0;
                    const depositPerBook = data.items?.[0]?.deposit || 0;
                    const shippingFee = data.shipping_fee || 0;

                    // Tính tổng cho tất cả quyển
                    const totalRentalFee = rentalFeePerBook * borrowQuantity;
                    const totalDeposit = depositPerBook * borrowQuantity;
                    const payableNow = totalRentalFee + totalDeposit + shippingFee;

                    // Tính ngày trả dự kiến
                    const today = new Date();
                    const returnDate = new Date(today);
                    returnDate.setDate(today.getDate() + days);

                    displayUnifiedBorrowSummary(borrowQuantity, days, distance, totalRentalFee, totalDeposit, shippingFee, payableNow, returnDate);
                })
                .catch(error => {
                    console.error('Error fetching pricing:', error);
                    displayUnifiedBorrowSummaryFallback(borrowQuantity, days, distance);
                });
        }

        // Hiển thị tóm tắt phiếu mượn thống nhất
        function displayUnifiedBorrowSummary(quantity, days, distance, totalRentalFee, totalDeposit, shippingFee, payableNow, returnDate) {
            const formatCurrency = (amount) => {
                return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
            };

            // 1. Info section
            let infoHtml = `
                <div class="borrow-info-section">
                    <h3><i class="fas fa-info-circle" style="color:#3b82f6"></i> Thông tin mượn</h3>
                    <div class="info-row">
                        <span>Số lượng mượn:</span>
                        <span class="info-value text-primary">${quantity} cuốn</span>
                    </div>
                    <div class="info-row">
                        <span>Số ngày mượn:</span>
                        <span class="info-value text-primary">${days} ngày</span>
                    </div>
                    <div class="info-row">
                        <span>Ngày trả dự kiến:</span>
                        <span class="info-value" style="color: #ef4444;">${returnDate.toLocaleDateString('vi-VN')}</span>
                    </div>
                </div>
            `;
            document.getElementById('borrowModalInfo').innerHTML = infoHtml;

            // 2. Pricing breakdown
            let pricingHtml = `
                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Phí thuê (${quantity} cuốn × ${days} ngày):</span>
                        <span>${formatCurrency(totalRentalFee)}</span>
                    </div>
                    <div class="price-row">
                        <span>Tiền cọc (${quantity} cuốn):</span>
                        <span>${formatCurrency(totalDeposit)}</span>
                    </div>
                    <div class="price-row">
                        <span>Phí ship dự kiến:</span>
                        <span class="text-muted">Tính sau</span>
                    </div>
                    <div class="price-row total">
                        <span>TỔNG THANH TOÁN:</span>
                        <span>${formatCurrency(payableNow)}</span>
                    </div>
                </div>
            `;
            document.getElementById('borrowModalPricing').innerHTML = pricingHtml;

            // 3. Actions
            let actionsHtml = `
                <div class="borrow-modal-actions">
                    <button class="btn-modal btn-modal-cancel" onclick="closeBorrowModal()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button class="btn-modal btn-modal-confirm" onclick="confirmBorrowUnified()">
                        <i class="fas fa-check"></i> Xác nhận mượn
                    </button>
                </div>
            `;
            document.getElementById('borrowModalActions').innerHTML = actionsHtml;
        }

        // Fallback khi API lỗi
        function displayUnifiedBorrowSummaryFallback(quantity, days, distance) {
            const bookPrice = {{ $book->gia ?? 0 }};
            // Phí thuê: 1% giá sách mỗi ngày (tính cho tất cả sách - logic thực tế do API xử lý)
            const dailyRate = 0.01; // 1% mỗi ngày

            const rentalFeePerBook = Math.round((bookPrice * dailyRate * days) / 1000) * 1000;
            const totalRentalFee = rentalFeePerBook * quantity;

            // Tiền cọc = giá sách (1:1)
            const depositRate = 1.0;
            const depositPerBook = Math.round(bookPrice * depositRate / 1000) * 1000;
            const totalDeposit = depositPerBook * quantity;

            const shippingFee = distance > 5 ? Math.round((distance - 5) * 5000) : 0;
            const payableNow = totalRentalFee + totalDeposit + shippingFee;

            const today = new Date();
            const returnDate = new Date(today);
            returnDate.setDate(today.getDate() + days);

            displayUnifiedBorrowSummary(quantity, days, distance, totalRentalFee, totalDeposit, shippingFee, payableNow, returnDate);
        }

        // Xác nhận mượn sách (phiên bản thống nhất)
        function confirmBorrowUnified() {
            const daysInput = document.getElementById('unified-days-input');
            const borrowQuantityInput = document.getElementById('borrow-quantity');
            const borrowQuantity = parseInt(borrowQuantityInput?.value) || 1;
            const availableCopies = {{ $stats['available_copies'] ?? 0 }};

            if (!daysInput) {
                alert('Không có thông tin mượn sách!');
                return;
            }

            const days = parseInt(daysInput.value) || 14;
            // Khoảng cách luôn là 0 ở modal
            const distance = 0;

            if (days < 0 || days > 30) {
                alert('Số ngày mượn phải từ 0 đến 30 ngày!');
                return;
            }

            if (borrowQuantity > availableCopies) {
                alert(`Số lượng mượn vượt quá số lượng có sẵn. Chỉ còn ${availableCopies} cuốn.`);
                return;
            }

            closeBorrowModal();

            // Tạo danh sách items với cùng thông số
            const items = [];
            for (let i = 0; i < borrowQuantity; i++) {
                items.push({
                    book_id: {{ $book->id }},
                    borrow_days: days,
                    distance: distance
                });
            }

            // Redirect đến checkout với thông tin items
            const params = new URLSearchParams();
            params.append('book_id', {{ $book->id }});
            params.append('quantity', borrowQuantity);
            params.append('items', JSON.stringify(items));

            window.location.href = '{{ route("reservation-cart.index") }}?' + params.toString();
        }

        // Hàm cũ: Cập nhật thông tin giá cho nhiều items với thông số khác nhau (giữ lại để tương thích)
        function updateBorrowQuoteMultiple() {
            const daysInputs = document.querySelectorAll('.item-days-input');
            const distanceInputs = document.querySelectorAll('.item-distance-input');

            if (daysInputs.length === 0) {
                return;
            }

            // Thu thập thông tin các items để gọi API
            const items = [];
            daysInputs.forEach((daysInput, index) => {
                const days = parseInt(daysInput.value) || 14;
                // Khoảng cách luôn là 0 - không cho nhập thủ công
                const distance = 0;
                items.push({ days, distance });
            });

            // Xác định thông tin người dùng
            const kycStatus = '{{ $kycStatus ?? "unverified" }}';
            const userId = {{ auth()->id() ?? 'null' }};

            // Gọi API để lấy giá cho từng item - sử dụng Promise.all để đợi tất cả
            const apiPromises = items.map((item, index) => {
                const { days, distance } = item;
                const deliveryType = distance > 0 ? 'ship' : 'pickup';
                const apiUrl = `/api/pricing/quote?book_ids[]={{ $book->id }}&kyc_status=${kycStatus}&delivery_type=${deliveryType}&distance=${distance}&days=${days}`;
                const finalUrl = userId ? `${apiUrl}&user_id=${userId}` : apiUrl;

                return fetch(finalUrl)
                    .then(response => response.json())
                    .then(data => ({
                        index,
                        days,
                        distance,
                        rentalFee: data.items?.[0]?.rental_fee || 0,
                        deposit: data.items?.[0]?.deposit || 0,
                        shippingFee: data.shipping_fee || 0
                    }))
                    .catch(error => {
                        console.error('Error fetching pricing:', error);
                        return {
                            index,
                            days,
                            distance,
                            rentalFee: 0,
                            deposit: 0,
                            shippingFee: 0
                        };
                    });
            });

            // Đợi tất cả API hoàn thành
            Promise.all(apiPromises).then(results => {
                let totalRentalFee = 0;
                let totalDeposit = 0;
                let maxShippingFee = 0;
                let itemsDetails = '';

                // Xử lý kết quả theo đúng thứ tự
                results.forEach(result => {
                    const { index, days, distance, rentalFee, deposit, shippingFee } = result;

                    totalRentalFee += rentalFee;
                    totalDeposit += deposit;

                    // Chỉ lấy phí ship lớn nhất
                    if (shippingFee > maxShippingFee) {
                        maxShippingFee = shippingFee;
                    }

                    // Tạo chi tiết item
                    const today = new Date();
                    const returnDate = new Date(today);
                    returnDate.setDate(today.getDate() + days);

                    itemsDetails += `
                        <div style="padding: 12px; background: white; border-radius: 6px; margin-bottom: 10px; border: 1px solid #e0e0e0;">
                            <div style="font-weight: bold; color: #333; margin-bottom: 8px;">📚 Quyển ${index + 1}</div>
                            <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.9em;">
                                <span style="color: #666;">Số ngày mượn:</span>
                                <span style="font-weight: 500;">${days} ngày</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.9em;">
                                <span style="color: #666;">Ngày trả dự kiến:</span>
                                <span style="font-weight: 500; color: #cc0000;">${returnDate.toLocaleDateString('vi-VN')}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.9em;">
                                <span style="color: #666;">Khoảng cách:</span>
                                <span style="font-weight: 500;">${distance} km</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.9em; border-top: 1px dashed #ddd; margin-top: 6px; padding-top: 8px;">
                                <span style="color: #666;">Phí thuê:</span>
                                <span style="font-weight: 500;">${new Intl.NumberFormat('vi-VN').format(rentalFee)}₫</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.9em;">
                                <span style="color: #666;">Tiền cọc:</span>
                                <span style="font-weight: 500;">${new Intl.NumberFormat('vi-VN').format(deposit)}₫</span>
                            </div>
                        </div>
                    `;
                });

                displayMultipleItemsSummary(itemsDetails, totalRentalFee, totalDeposit, maxShippingFee, items.length);
            });
        }

        // Hàm hiển thị tổng kết cho nhiều items
        function displayMultipleItemsSummary(itemsDetails, totalRentalFee, totalDeposit, totalShippingFee, quantity) {
            const payableNow = totalRentalFee + totalDeposit + totalShippingFee;

            let content = `
                <div class="borrow-info-section">
                    <h3>📚 Thông tin sách</h3>
                    <div class="info-row">
                        <span class="info-label">Tên sách:</span>
                        <span class="info-value">{{ $book->ten_sach }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số lượng mượn:</span>
                        <span class="info-value">${quantity} cuốn</span>
                    </div>
                </div>

                <div class="borrow-info-section">
                    <h3>📋 Chi tiết từng quyển</h3>
                    ${itemsDetails}
                </div>

                <div class="borrow-info-section">
                    <h3>💰 Tổng chi phí</h3>
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Tổng phí thuê (${quantity} cuốn):</span>
                            <span>${new Intl.NumberFormat('vi-VN').format(totalRentalFee)}₫</span>
                        </div>
                        <div class="price-row">
                            <span>Tổng tiền cọc (${quantity} cuốn):</span>
                            <span>${new Intl.NumberFormat('vi-VN').format(totalDeposit)}₫</span>
                        </div>
                        ${totalShippingFee > 0 ? `
                        <div class="price-row">
                            <span>Phí vận chuyển <small style="color: #666;">(chỉ tính 1 lần)</small>:</span>
                            <span>${new Intl.NumberFormat('vi-VN').format(totalShippingFee)}₫</span>
                        </div>
                        ` : ''}
                        <div class="price-row total">
                            <span>Tổng tiền phải trả ngay:</span>
                            <span>${new Intl.NumberFormat('vi-VN').format(payableNow)}₫</span>
                        </div>
                    </div>
                    <div style="margin-top: 10px; padding: 10px; background: #e3f2fd; border-radius: 4px; border: 1px solid #2196f3; color: #1565c0; font-size: 0.85em; margin-bottom: 8px;">
                        <strong>💡 Phí vận chuyển:</strong> Chỉ tính 1 lần duy nhất cho khoảng cách xa nhất (${quantity} cuốn giao cùng địa chỉ).
                    </div>
                    <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 4px; border: 1px solid #ffc107; color: #856404; font-size: 0.85em;">
                        <strong>⚠️ Lưu ý:</strong> Tiền cọc sẽ được hoàn lại khi bạn trả sách đúng hạn và sách không bị hư hỏng. Phí thuê sẽ được tính khi bạn nhận sách.
                    </div>
                </div>

                <div class="borrow-modal-actions">
                    <button class="btn-modal btn-modal-cancel" onclick="closeBorrowModal()">Hủy</button>
                    <button class="btn-modal btn-modal-confirm" onclick="confirmBorrowMultiple()">Xác nhận mượn sách</button>
                </div>
            `;

            document.getElementById('borrowModalInfo').innerHTML = content;
        }

        // Hàm cũ: Cập nhật thông tin giá khi thay đổi số ngày hoặc khoảng cách (giữ lại cho tương thích)
        function updateBorrowQuote() {
            const days = parseInt(document.getElementById('borrowDaysInput')?.value) || 14;
            // Khoảng cách luôn là 0 - không cho nhập thủ công
            const distance = 0;
            const quantity = parseInt(document.getElementById('borrow-quantity')?.value) || 1;

            if (days < 0 || days > 30) {
                document.getElementById('borrowModalInfo').innerHTML =
                    '<div style="text-align: center; padding: 20px; color: #cc0000;">Số ngày mượn phải từ 0 đến 30 ngày.</div>';
                return;
            }

            // Sử dụng KYC status từ server
            const kycStatus = '{{ $kycStatus ?? "unverified" }}';
            const userId = {{ auth()->id() ?? 'null' }};

            // Xác định delivery_type: nếu có khoảng cách > 0 thì là ship, ngược lại là pickup
            const deliveryType = distance > 0 ? 'ship' : 'pickup';

            // Gọi API để lấy thông tin giá (truyền tham số days để tính phí thuê theo số ngày)
            // Lưu ý: API có thể không hỗ trợ số lượng, nên sẽ tính nhân sau
            const apiUrl = `/api/pricing/quote?book_ids[]={{ $book->id }}&kyc_status=${kycStatus}&delivery_type=${deliveryType}&distance=${distance}&days=${days}`;
            const finalUrl = userId ? `${apiUrl}&user_id=${userId}` : apiUrl;

            fetch(finalUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.items && data.items.length > 0) {
                        displayBorrowQuote(data, days, quantity);
                    } else {
                        // Fallback nếu API không trả về đúng format
                        displayBorrowQuoteFallback(days, kycStatus, quantity);
                    }
                })
                .catch(error => {
                    console.error('Error fetching pricing:', error);
                    // Fallback nếu API lỗi
                    displayBorrowQuoteFallback(days, kycStatus, quantity);
                });
        }

        // Hiển thị phiếu mượn với thông tin từ API
        function displayBorrowQuote(pricingData, days, quantity = 1) {
            const item = pricingData.items[0];
            const rentalFee = item.rental_fee || 10000;
            const deposit = item.deposit || 50000;
            const shippingFee = pricingData.shipping_fee || 0;
            // Nhân với số lượng
            const totalRental = (pricingData.total_rental_fee || rentalFee) * quantity;
            const totalDeposit = (pricingData.total_deposit || deposit) * quantity;
            const payableNow = totalRental + totalDeposit + shippingFee;

            const today = new Date();
            const returnDate = new Date(today);
            returnDate.setDate(today.getDate() + days);

            const formatDate = (date) => {
                return date.toLocaleDateString('vi-VN', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            };

            const formatCurrency = (amount) => {
                return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
            };

            const content = `
                <div class="borrow-info-section">
                    <h3>📚 Thông tin sách</h3>
                    <div class="info-row">
                        <span class="info-label">Tên sách:</span>
                        <span class="info-value">{{ $book->ten_sach }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tác giả:</span>
                        <span class="info-value">{{ $book->tac_gia ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nhà xuất bản:</span>
                        <span class="info-value">{{ $book->publisher->ten_nha_xuat_ban ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Năm xuất bản:</span>
                        <span class="info-value">{{ $book->nam_xuat_ban ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="borrow-info-section">
                    <h3>📅 Thông tin mượn</h3>
                    <div class="info-row">
                        <span class="info-label">Ngày mượn:</span>
                        <span class="info-value">${formatDate(today)}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số ngày mượn:</span>
                        <span class="info-value">${days} ngày</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ngày trả dự kiến:</span>
                        <span class="info-value" style="color: #cc0000;">${formatDate(returnDate)}</span>
                    </div>
                </div>

                <div class="borrow-info-section">
                    <h3>💰 Chi phí mượn sách</h3>
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Phí thuê sách (${quantity} cuốn × ${days} ngày):</span>
                            <span>${formatCurrency(totalRental)}</span>
                        </div>
                        <div class="price-row">
                            <span>Tiền cọc (${quantity} cuốn):</span>
                            <span>${formatCurrency(totalDeposit)}</span>
                        </div>
                        ${shippingFee > 0 ? `
                        <div class="price-row">
                            <span>Phí vận chuyển:</span>
                            <span>${formatCurrency(shippingFee)}</span>
                        </div>
                        ` : ''}
                        <div class="price-row total">
                            <span>Tổng tiền phải trả ngay:</span>
                            <span>${formatCurrency(payableNow)}</span>
                        </div>
                    </div>
                    <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 4px; border: 1px solid #ffc107; color: #856404; font-size: 0.9em;">
                        <strong>Lưu ý:</strong> Tiền cọc sẽ được hoàn lại khi bạn trả sách đúng hạn và sách không bị hư hỏng. Phí thuê sẽ được tính khi bạn nhận sách.
                    </div>
                </div>

                <div class="borrow-modal-actions">
                    <button class="btn-modal btn-modal-cancel" onclick="closeBorrowModal()">Hủy</button>
                    <button class="btn-modal btn-modal-confirm" onclick="confirmBorrow(${days}, ${quantity})">Xác nhận mượn sách</button>
                </div>
            `;

            document.getElementById('borrowModalInfo').innerHTML = content;
        }

        // Fallback nếu API không hoạt động - tính dựa trên giá sách
        function displayBorrowQuoteFallback(days, kycStatus = 'unverified', quantity = 1) {
            // Lấy giá sách từ server
            const bookPrice = {{ $book->gia ?? 0 }};

            // Tỷ lệ phí thuê mỗi ngày (1% giá sách mỗi ngày, tính cho tất cả sách)
            // Logic thực tế sẽ do API xử lý dựa trên condition của inventory
            const dailyRate = 0.01; // 1% mỗi ngày

            // Tính phí thuê = giá sách * tỷ lệ mỗi ngày * số ngày
            const rentalFeePerBook = Math.round((bookPrice * dailyRate * days) / 1000) * 1000;
            const rentalFee = rentalFeePerBook * quantity;

            // Tính tiền cọc dựa trên giá sách (100% giá sách - 1:1)
            const depositRate = 1.0; // 100% giá sách
            const depositPerCopy = Math.round(bookPrice * depositRate);
            const deposit = depositPerCopy * quantity;

            const shippingFee = 0;
            const total = rentalFee + deposit + shippingFee;

            const today = new Date();
            const returnDate = new Date(today);
            returnDate.setDate(today.getDate() + days);

            const formatDate = (date) => {
                return date.toLocaleDateString('vi-VN', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            };

            const formatCurrency = (amount) => {
                return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
            };

            const content = `
                <div class="borrow-info-section">
                    <h3>📚 Thông tin sách</h3>
                    <div class="info-row">
                        <span class="info-label">Tên sách:</span>
                        <span class="info-value">{{ $book->ten_sach }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tác giả:</span>
                        <span class="info-value">{{ $book->tac_gia ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="borrow-info-section">
                    <h3>📅 Thông tin mượn</h3>
                    <div class="info-row">
                        <span class="info-label">Ngày mượn:</span>
                        <span class="info-value">${formatDate(today)}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số lượng mượn:</span>
                        <span class="info-value">${quantity} cuốn</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số ngày mượn:</span>
                        <span class="info-value">${days} ngày</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ngày trả dự kiến:</span>
                        <span class="info-value" style="color: #cc0000;">${formatDate(returnDate)}</span>
                    </div>
                </div>

                <div class="borrow-info-section">
                    <h3>💰 Chi phí mượn sách</h3>
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Phí thuê sách (${quantity} cuốn × ${days} ngày):</span>
                            <span>${formatCurrency(rentalFee)}</span>
                        </div>
                        <div class="price-row">
                            <span>Tiền cọc (${quantity} cuốn):</span>
                            <span>${formatCurrency(deposit)}</span>
                        </div>
                        <div class="price-row total">
                            <span>Tổng tiền phải trả ngay:</span>
                            <span>${formatCurrency(total)}</span>
                        </div>
                    </div>
                    <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 4px; border: 1px solid #ffc107; color: #856404; font-size: 0.9em;">
                        <strong>Lưu ý:</strong> Tiền cọc sẽ được hoàn lại khi bạn trả sách đúng hạn và sách không bị hư hỏng. Phí thuê sẽ được tính khi bạn nhận sách.
                    </div>
                </div>

                <div class="borrow-modal-actions">
                    <button class="btn-modal btn-modal-cancel" onclick="closeBorrowModal()">Hủy</button>
                    <button class="btn-modal btn-modal-confirm" onclick="confirmBorrow(${days}, ${quantity})">Xác nhận mượn sách</button>
                </div>
            `;

            document.getElementById('borrowModalInfo').innerHTML = content;
        }

        // Hàm mới: Xác nhận mượn nhiều quyển với thông số khác nhau
        // Thay thế hàm confirmBorrowMultiple (line ~1350) thành:
        function confirmBorrowMultiple() {
            const daysInputs = document.querySelectorAll('.item-days-input');
            const distanceInputs = document.querySelectorAll('.item-distance-input');
            const availableCopies = {{ $stats['available_copies'] ?? 0 }};

            if (daysInputs.length === 0) {
                alert('Không có thông tin mượn sách!');
                return;
            }

            if (daysInputs.length > availableCopies) {
                alert(`Số lượng mượn vượt quá số lượng có sẵn. Chỉ còn ${availableCopies} cuốn.`);
                return;
            }

            // ✅ KHÔNG GỘI API, CHỈ REDIRECT CHECKOUT VỚI THÔNG SỐ
            const items = [];
            daysInputs.forEach((daysInput, index) => {
                const days = parseInt(daysInput.value) || 14;
                // Khoảng cách luôn là 0 - không cho nhập thủ công
                const distance = 0;

                if (days < 1 || days > 30) {
                    alert(`Quyển ${index + 1}: Số ngày mượn phải từ 7 đến 30 ngày!`);
                    return;
                }

                items.push({
                    book_id: {{ $book->id }},
                    borrow_days: days,
                    distance: distance
                });
            });

            if (items.length === 0) {
                alert('Không có thông tin mượn sách hợp lệ!');
                return;
            }

            closeBorrowModal();

            // ✅ Redirect đến checkout với thông tin items
            const params = new URLSearchParams();
            params.append('book_id', {{ $book->id }});
            params.append('quantity', items.length);
            params.append('items', JSON.stringify(items));

            window.location.href = '{{ route("reservation-cart.index") }}?' + params.toString();
        }

        // Hàm cũ: Xác nhận mượn sách (giữ lại cho tương thích)
        function confirmBorrow(days, quantityFromModal = null) {
            const confirmBtn = event.target;
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Đang xử lý...';

            // Khoảng cách luôn là 0 - không cho nhập thủ công
            const distance = 0;

            // Lấy số lượng mượn (ưu tiên từ tham số, nếu không có thì lấy từ input)
            const quantity = quantityFromModal !== null ? quantityFromModal : (parseInt(document.getElementById('borrow-quantity')?.value) || 1);
            const availableCopies = {{ $stats['available_copies'] ?? 0 }};

            // Kiểm tra số lượng hợp lệ
            if (quantity < 1 || quantity > availableCopies) {
                alert(`Số lượng mượn không hợp lệ. Vui lòng chọn từ 1 đến ${availableCopies} cuốn.`);
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Xác nhận mượn sách';
                return;
            }

            // Gửi yêu cầu mượn sách
            fetch('{{ route("borrow.book") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    book_id: {{ $book->id }},
                    borrow_days: days,
                    distance: distance,
                    quantity: quantity,
                    note: `Yêu cầu mượn sách - ${quantity} cuốn - ${days} ngày`
                })
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (response.status === 401) {
                        return response.json().then(data => {
                            alert(data.message || 'Vui lòng đăng nhập để mượn sách!');
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.href = '{{ route("login") }}';
                            }
                            return;
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (!data) {
                        console.error('No data returned from server');
                        alert('Không nhận được phản hồi từ server!');
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = 'Xác nhận mượn sách';
                        return;
                    }

                    if (data.success) {
                        console.log('Borrow created successfully:', data.data);
                        closeBorrowModal();

                        // Hiển thị thông báo thành công với thông tin chi tiết
                        const quantity = data.data?.quantity || 1;
                        const message = (data.message || 'Đã gửi yêu cầu mượn sách thành công!') +
                            '\n\nSố lượng mượn: ' + quantity + ' cuốn' +
                            '\nMã phiếu mượn: ' + (data.data?.borrow_id || 'N/A') +
                            '\nMã chi tiết: ' + (data.data?.borrow_item_id || 'N/A') +
                            '\n\nYêu cầu đã được gửi và sẽ hiển thị trong trang "Quản lý mượn sách" của admin.';

                        alert(message);

                        // Redirect đến trang sách đang mượn để xem yêu cầu vừa tạo
                        window.location.href = '{{ route("reservation-cart.index") }}';
                    } else {
                        console.error('Borrow creation failed:', data.message);
                        alert(data.message || 'Có lỗi xảy ra khi gửi yêu cầu mượn sách!');
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = 'Xác nhận mượn sách';
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('Có lỗi xảy ra khi gửi yêu cầu mượn sách: ' + error.message);
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Xác nhận mượn sách';
                });
        }


        // Các hàm reservation đã được xóa (thay bằng chức năng thêm vào giỏ sách)



        // Đóng modal khi click bên ngoài
        document.getElementById('borrowModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeBorrowModal();
            }
        });

    </script>

    @auth
        <script>
            // Load số lượng giỏ sách khi trang load
            document.addEventListener('DOMContentLoaded', function () {
                loadReservationCartCount();
            });

            function loadReservationCartCount() {
                fetch('{{ route('reservation-cart.count') }}')
                    .then(response => response.json())
                    .then(data => {
                        const cartCountElement = document.getElementById('borrow-cart-count');
                        if (cartCountElement) {
                            const count = data.count || 0;
                            cartCountElement.textContent = count;
                            cartCountElement.style.display = count > 0 ? 'flex' : 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading cart count:', error);
                    });
            }
        </script>
    @endauth

    <!-- Modal Phiếu Mượn -->
    <div id="borrowModal" class="borrow-modal-overlay">
        <div class="borrow-modal">
            <button class="close-modal" onclick="closeBorrowModal()">&times;</button>
            <div class="borrow-modal-header">
                <h2>📖 PHIẾU MƯỢN SÁCH</h2>
                <div class="subtitle">Vui lòng nhập thông tin mượn sách</div>
            </div>

            <!-- Container cho thông tin tóm tắt (phần trên cùng) -->
            <div id="borrowModalInfo">
                <div class="loading-spinner">Đang tải thông tin...</div>
            </div>

            <!-- Container cho các input (+/- số ngày mượn) nằm ngay dưới thông tin mượn -->
            <div id="borrowModalInputs"></div>

            <!-- Container cho chi tiết giá -->
            <div id="borrowModalPricing"></div>

            <!-- Container cho các nút hành động -->
            <div id="borrowModalActions"></div>
        </div>
    </div>

    <!-- Modal Đặt Trước đã được xóa (thay bằng chức năng thêm vào giỏ sách) -->
</body>

</html>