<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sách: {{ $book->ten_sach }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
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

        .book-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 8px;
        }

        .favorite-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #fecaca;
            background: #fff;
            color: #dc2626;
            border-radius: 999px;
            width: 48px;
            height: 48px;
            padding: 0;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
            flex-shrink: 0;
        }

        .favorite-toggle-btn:hover {
            background: #fff1f2;
        }

        .favorite-toggle-btn.active {
            background: #fee2e2;
            border-color: #f87171;
        }

        .favorite-toggle-btn i {
            font-size: 20px;
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

        .rating-summary-inline {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin: 12px 0 18px;
        }

        .rating-score {
            font-size: 1.4rem;
            font-weight: 800;
            color: #111827;
        }

        .stars {
            color: orange;
            letter-spacing: 2px;
        }

        .rating-caption {
            color: #6b7280;
            font-size: 0.95rem;
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

        .review-form {
            padding: 18px;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff, #fffaf5);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .review-form-wrapper {
            margin-bottom: 20px;
        }

        .review-form-wrapper.is-hidden {
            display: none;
        }

        .inline-review-editor {
            margin-top: 16px;
        }

        .inline-review-editor.is-hidden {
            display: none;
        }

        .review-form-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .review-form-title {
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .review-form-subtitle {
            margin: 4px 0 0;
            font-size: 0.92rem;
            color: #6b7280;
        }

        .verified-review-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 0.82rem;
            font-weight: 700;
            border: 1px solid #a7f3d0;
        }

        .star-rating-input {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 6px;
            margin-bottom: 12px;
        }

        .star-rating-input input {
            display: none;
        }

        .star-rating-input label {
            cursor: pointer;
            font-size: 2rem;
            line-height: 1;
            color: #d1d5db;
            transition: transform 0.15s ease, color 0.15s ease;
        }

        .star-rating-input label:hover,
        .star-rating-input label:hover ~ label,
        .star-rating-input input:checked ~ label {
            color: #f59e0b;
        }

        .star-rating-input label:hover {
            transform: scale(1.06);
        }

        .rating-helper {
            margin-bottom: 10px;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .review-locked-box,
        .review-login-box {
            padding: 18px;
            border-radius: 14px;
            margin-bottom: 18px;
        }

        .review-locked-box {
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #fdba74;
        }

        .review-login-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .reviews-summary-card {
            display: grid;
            grid-template-columns: minmax(180px, 220px) 1fr;
            gap: 18px;
            margin: 24px 0;
            padding: 20px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid #eef2f7;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .reviews-summary-score {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-right: 1px solid #f1f5f9;
            padding-right: 18px;
        }

        .reviews-summary-score strong {
            font-size: 2.5rem;
            line-height: 1;
            color: #111827;
        }

        .reviews-summary-score span {
            margin-top: 8px;
            color: #6b7280;
            font-size: 0.92rem;
        }

        .reviews-breakdown {
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: center;
        }

        .reviews-breakdown-row {
            display: grid;
            grid-template-columns: 56px 1fr 40px;
            gap: 10px;
            align-items: center;
            font-size: 0.92rem;
            color: #475569;
        }

        .reviews-breakdown-bar {
            height: 8px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .reviews-breakdown-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #f59e0b, #f97316);
        }

        .review-card {
            padding: 18px;
            background: #fff;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
            margin-bottom: 16px;
        }

        .review-card.review-card-own {
            border-color: #bbf7d0;
            background: linear-gradient(180deg, #ffffff, #f0fdf4);
        }

        .review-card-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .review-card-user {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .review-card-date {
            color: #64748b;
            font-size: 0.85rem;
        }

        .review-card-text {
            margin: 12px 0 0;
            color: #334155;
            line-height: 1.7;
            white-space: pre-line;
        }

        .review-card-actions {
            margin-top: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .review-edit-link {
            border: none;
            background: none;
            padding: 0;
            color: #0f766e;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .review-edit-link:hover {
            color: #0d9488;
            text-decoration: underline;
        }

        .review-owner-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #ecfeff;
            border: 1px solid #a5f3fc;
            color: #155e75;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .review-owner-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .review-edit-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .review-edit-limit {
            color: #64748b;
            font-size: 0.84rem;
        }

        .review-expired-note {
            color: #b45309;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .review-note-box {
            padding: 16px 18px;
            border-radius: 14px;
            margin-bottom: 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
        }

        .review-inline-error {
            margin-bottom: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 0.92rem;
        }

        .review-form-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .review-cancel-btn {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #475569;
        }

        .review-replies {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px dashed #e2e8f0;
        }

        .review-reply-item {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 10px;
        }

        .review-reply-item:last-child {
            margin-bottom: 0;
        }

        .review-reply-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 0.82rem;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .reviews-summary-card {
                grid-template-columns: 1fr;
            }

            .reviews-summary-score {
                border-right: none;
                border-bottom: 1px solid #f1f5f9;
                padding-right: 0;
                padding-bottom: 18px;
            }
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

        :root {
            --detail-accent: #0d9488;
            --detail-accent-2: #2563eb;
            --detail-text: #0f172a;
            --detail-muted: #64748b;
            --detail-border: #e2e8f0;
            --detail-surface: rgba(255, 255, 255, 0.9);
            --detail-shadow: 0 22px 44px rgba(15, 23, 42, 0.08);
            --detail-shadow-soft: 0 16px 32px rgba(15, 23, 42, 0.06);
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(13, 148, 136, 0.08), transparent 24%),
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 22%),
                #f6f8fc;
            color: var(--detail-text);
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(.95) translateY(10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .btn-preview:hover {
            background: #0d9488 !important;
            color: #fff !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(13,148,136,0.25);
        }

        #previewModalBody::-webkit-scrollbar {
            width: 6px;
        }
        #previewModalBody::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        #previewModalBody::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        #previewModalBody::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        }

        .content-wrapper {
            max-width: 1280px;
            gap: 28px;
            padding: 30px 20px 48px;
            align-items: flex-start;
        }

        .main-content {
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(226, 232, 240, 0.78);
            border-radius: 30px;
            padding: 26px;
            box-shadow: var(--detail-shadow);
            backdrop-filter: blur(16px);
        }

        .sidebar {
            gap: 22px;
        }

        .breadcrumb.premium-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--detail-muted);
        }

        .breadcrumb.premium-breadcrumb a,
        .breadcrumb.premium-breadcrumb span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(226, 232, 240, 0.9);
            text-decoration: none;
            color: inherit;
            font-weight: 600;
        }

        .breadcrumb.premium-breadcrumb a:hover {
            color: var(--detail-accent);
            background: #f0fdfa;
        }

        .book-detail-section {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .book-summary {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 28px;
            padding: 24px;
            box-shadow: var(--detail-shadow-soft);
            gap: 24px;
            position: relative;
            overflow: hidden;
        }

        .book-summary::before {
            content: '';
            position: absolute;
            inset: 0 auto auto 0;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(13, 148, 136, 0.12), transparent 68%);
            pointer-events: none;
        }

        .book-cover {
            width: 240px;
            border-radius: 22px;
            box-shadow: 0 22px 36px rgba(15, 23, 42, 0.16);
            border: 1px solid rgba(226, 232, 240, 0.78);
        }

        /* Main detail cover image (dedicated wrapper to avoid white margins) */
        .main-book-cover-wrap {
            width: min(300px, 100%);
            height:400px;
            aspect-ratio: 2 / 3;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #eff6ff, #e2e8f0);
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            padding: 0;
            box-sizing: border-box;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
            margin: 4px 0;
            flex-shrink: 0;
        }

        .main-book-cover {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transform: scale(0.9);
        }

        @media (max-width: 768px) {
            .main-book-cover-wrap {
                width: 100%;
                max-width: 180px;
                margin: 0 auto;
            }
        }

        .info-and-buy {
            position: relative;
            z-index: 1;
        }

        .book-title-row {
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 12px;
        }

        .book-main-title {
            margin: 0;
            font-size: clamp(2rem, 3vw, 2.8rem);
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--detail-text);
        }

        .favorite-toggle-btn {
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
            border-color: rgba(226, 232, 240, 0.9);
        }

        .book-quick-facts {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 0 0 14px;
        }

        .quick-fact {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--detail-muted);
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(226, 232, 240, 0.92);
        }

        .quick-fact i {
            color: var(--detail-accent);
        }

        .info-and-buy > p {
            color: var(--detail-muted);
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .info-and-buy > p strong {
            color: var(--detail-text);
        }

        .rating-summary-inline {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 18px;
            background: linear-gradient(135deg, #fff7ed, #ffffff);
            border: 1px solid rgba(251, 191, 36, 0.32);
            margin: 8px 0 18px;
        }

        .rating-score {
            color: #b45309;
            font-weight: 800;
        }

        .rating-caption {
            color: var(--detail-muted);
        }

        .rental-highlight {
            margin: 0 0 20px;
            padding: 16px 18px;
            background: linear-gradient(135deg, #ecfeff, #eff6ff);
            border-radius: 18px;
            border: 1px solid rgba(6, 182, 212, 0.26);
        }

        .rental-highlight-title {
            font-size: 0.92rem;
            color: var(--detail-text);
            font-weight: 800;
            margin-bottom: 6px;
        }

        .rental-highlight-price {
            font-size: 0.98rem;
            color: #0369a1;
            line-height: 1.7;
        }

        .buy-options,
        .description-section,
        .metadata-table,
        .comment-section,
        .related-books-section,
        .sidebar-block {
            background: var(--detail-surface);
            border: 1px solid rgba(226, 232, 240, 0.84);
            border-radius: 24px;
            box-shadow: var(--detail-shadow-soft);
        }

        .buy-options {
            padding: 22px;
        }

        .buy-options label {
            font-size: 1rem;
            font-weight: 800;
            color: var(--detail-text);
            margin-bottom: 16px;
        }

        .option-row {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 18px;
            padding: 14px 16px;
            margin-bottom: 14px;
        }

        .option-row .type,
        .total-price span:first-child {
            color: var(--detail-text);
            font-weight: 700;
        }

        .total-price {
            border-top: 1px dashed rgba(148, 163, 184, 0.45);
            margin-top: 18px;
            padding-top: 16px;
        }

        .final-price,
        .option-row .price {
            color: var(--detail-accent);
            font-weight: 800;
        }

        .action-buttons .btn,
        .review-form-actions button,
        .review-card-actions button {
            border-radius: 16px;
            box-shadow: 0 14px 26px rgba(13, 148, 136, 0.14);
        }

        .tab-section {
            background: transparent;
            border: none;
            padding: 0;
            gap: 12px;
        }

        .tab-link {
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.92);
            padding: 12px 18px;
            font-weight: 700;
            color: var(--detail-muted);
        }

        .tab-link.active,
        .tab-link:hover {
            color: #ffffff;
            background: linear-gradient(135deg, var(--detail-accent), #14b8a6);
            border-color: transparent;
            box-shadow: 0 14px 24px rgba(13, 148, 136, 0.18);
        }

        .description-section,
        .metadata-table,
        .comment-section,
        .related-books-section {
            padding: 24px;
        }

        .description-section {
            color: var(--detail-muted);
            line-height: 1.9;
            font-size: 1rem;
        }

        .metadata-table h2,
        .comment-section h2,
        .related-books-section h2,
        .sidebar-block h3 {
            font-size: 1.35rem;
            line-height: 1.3;
            margin-bottom: 18px;
            color: var(--detail-text);
        }

        .book-metadata {
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid rgba(226, 232, 240, 0.88);
        }

        .book-metadata td {
            padding: 15px 16px;
            border-color: rgba(226, 232, 240, 0.72);
        }

        .book-metadata .label {
            color: var(--detail-text);
            background: #f8fafc;
            font-weight: 700;
        }

        .review-form-wrapper,
        .review-card {
            border-radius: 20px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
        }

        .review-card {
            background: #ffffff;
        }

        .review-card-user,
        .review-form-title {
            color: var(--detail-text);
        }

        .review-card-date,
        .review-form-subtitle,
        .item-details .stats {
            color: var(--detail-muted);
        }

        .review-card-text {
            color: #334155;
            line-height: 1.75;
        }

        .book-carousel-wrapper {
            padding: 6px 2px 2px;
        }

        .related-books-section .book-item {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.84);
            border-radius: 20px;
            padding: 14px;
            box-shadow: 0 12px 20px rgba(15, 23, 42, 0.04);
        }

        .related-books-section .book-item:not(:last-child) {
            border-bottom: 1px solid rgba(226, 232, 240, 0.84);
        }

        .sidebar-block {
            padding: 22px;
        }

        .sidebar-thumb {
            width: 66px;
            height: 92px;
            border-radius: 12px;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
        }

        .item-details a {
            color: var(--detail-text);
        }

        .item-details a:hover {
            color: var(--detail-accent);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 18px;
                border-radius: 24px;
            }

            .book-summary {
                padding: 18px;
                border-radius: 22px;
            }

            .book-main-title {
                font-size: 1.8rem;
            }

            .description-section,
            .metadata-table,
            .comment-section,
            .related-books-section,
            .buy-options,
            .sidebar-block {
                padding: 18px;
                border-radius: 20px;
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
                    <span class="logo-part1">THUÊ SÁCH</span>
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
                            <a href="{{ route('account.favorite-books') }}" class="dropdown-item">
                                <span>❤️</span> Sách yêu thích
                            </a>
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
            <p class="breadcrumb premium-breadcrumb">
                <a href="{{ route('home') }}"><i class="fas fa-house"></i> Trang chủ</a>
                <span>{{ Str::limit($book->ten_sach, 50) }}</span>
            </p>

            <section class="book-detail-section">
                <div class="book-summary">
                    <div class="main-book-cover-wrap">
                        <img src="{{ $book->image_url }}"
                            alt="Bìa sách {{ $book->ten_sach }}" class="main-book-cover"
                            onerror="this.onerror=null; this.src='{{ asset('images/default-book.png') }}';">
                    </div>

                    <div class="info-and-buy">
                        <div class="book-title-row">
                            <h1 class="book-main-title">{{ $book->ten_sach }}</h1>
                            @auth
                                <button type="button"
                                        id="favoriteToggleButton"
                                        class="favorite-toggle-btn {{ $isFavorited ? 'active' : '' }}"
                                        aria-label="{{ $isFavorited ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' }}"
                                        title="{{ $isFavorited ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' }}"
                                        onclick="toggleFavorite({{ $book->id }}, this)">
                                    <i class="{{ $isFavorited ? 'fas' : 'far' }} fa-heart"></i>
                                </button>
                            @else
                                <button type="button"
                                        class="favorite-toggle-btn"
                                        aria-label="Đăng nhập để thêm vào yêu thích"
                                        title="Đăng nhập để thêm vào yêu thích"
                                        onclick="window.location.href='{{ route('login') }}'">
                                    <i class="far fa-heart"></i>
                                </button>
                            @endauth
                        </div>
                        <div class="book-quick-facts">
                            <span class="quick-fact"><i class="fas fa-eye"></i> {{ $book->formatted_views }} lượt xem</span>
                            <span class="quick-fact"><i class="fas fa-book"></i> {{ $book->formatted_quantity }} cuốn</span>
                            @if($book->publisher)
                                <span class="quick-fact"><i class="fas fa-building"></i> {{ $book->publisher->ten_nha_xuat_ban }}</span>
                            @endif
                        </div>
                        <p>Tác giả: <strong>{{ $book->formatted_author }}</strong></p>
                        @if($book->nam_xuat_ban)
                            <p>Năm xuất bản: <strong>{{ $book->formatted_year }}</strong></p>
                        @endif

                        <div class="rating-summary-inline">
                            <span class="rating-score">{{ number_format($stats['average_rating'] ?? 0, 1) }}/5</span>
                            <span class="stars">
                                @php $roundedAverage = round($stats['average_rating'] ?? 0); @endphp
                                @for($i = 1; $i <= 5; $i++)
                                    {{ $i <= $roundedAverage ? '★' : '☆' }}
                                @endfor
                            </span>
                            <span class="rating-caption">{{ $stats['total_reviews'] ?? 0 }} đánh giá xác thực · {{ $book->formatted_views }} lượt xem</span>
                        </div>

                        <div class="rental-highlight">
                            <div class="rental-highlight-title">
                                💰 Giá thuê tham khảo
                            </div>
                            @php
                                $dailyFee = 5000;
                            @endphp
                            <div class="rental-highlight-price">
                                Từ <strong>{{ number_format($dailyFee, 0, ',', '.') }}₫/ngày</strong>
                                <span style="color:#64748b; font-weight:400;">(tiền thuê thực tế sẽ tính theo số ngày mượn từng cuốn trong giỏ hàng)</span>
                            </div>
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

                                <div class="action-buttons" style="display: flex; flex-wrap: wrap; gap: 10px;">
                                    <button class="btn-preview" onclick="openPreviewModal()" style="flex: 1; min-width: 130px; padding: 12px 16px; border: 2px solid #0d9488; border-radius: 12px; background: white; color: #0d9488; font-weight: 700; font-size: 0.9em; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all .2s;">
                                        📖 Đọc thử
                                    </button>
                                    @auth
                                        <button class="btn btn-buy btn-add-to-cart" onclick="addToCart()" style="flex: 1; background: #6C63FF; border-radius: 12px;">
                                            <span style="font-size: 1.2em;">🛒</span> Thêm vào giỏ sách
                                        </button>
                                        <button class="btn btn-buy" onclick="borrowNow()" style="flex: 1; border-radius: 12px;">
                                            <span style="font-size: 1.2em;">📖</span> Mượn ngay
                                        </button>
                                    @else
                                        <button class="btn btn-buy"
                                            onclick="Swal.fire({icon:'warning',title:'Vui lòng đăng nhập',text:'Bạn cần đăng nhập để mượn sách!'}).then(()=>{window.location.href='{{ route('login') }}';})"
                                            style="opacity: 0.7; cursor: pointer; width: 100%; border-radius: 12px;">
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
                                            onchange="validateReservationQuantity(); updateTotalPrice();">
                                        <button type="button" onclick="changeQuantity('paper', 1)"
                                            style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; background: white; cursor: pointer;">+</button>
                                    </div>
                                    <span class="price"
                                        id="paper-price">{{ number_format($book->gia ?? 111000, 0, ',', '.') }}₫</span>
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

                                <div class="action-buttons" style="display: flex; flex-wrap: wrap; gap: 10px;">
                                    <button class="btn-preview" onclick="openPreviewModal()" style="flex: 1; min-width: 140px; padding: 12px 16px; border: 2px solid #0d9488; border-radius: 12px; background: white; color: #0d9488; font-weight: 700; font-size: 0.95em; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all .2s;">
                                        📖 Đọc thử
                                    </button>
                                    <button class="btn btn-buy" onclick="addToReservationCart()" style="flex: 2; background: #0d9488; border-radius: 12px;">
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
                            <td class="label">Giá thuê (tham khảo):</td>
                            <td>Từ {{ number_format(5000, 0, ',', '.') }}₫/ngày</td>
                        </tr>
                        <tr>
                            <td class="label">Số lượng:</td>
                            <td>{{ $book->formatted_quantity }} cuốn</td>
                            <td class="label">Đánh giá:</td>
                            <td>{{ number_format($stats['average_rating'] ?? 0, 1) }}/5 ({{ $stats['total_reviews'] ?? 0 }} đánh giá)</td>
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
                    <h2>Đánh giá & bình luận</h2>
                    @php
                        $recentReviews = $book->reviews;
                        $reviewFormBorrowItemId = (int) old('borrow_item_id', 0);
                        $reviewEditWindowDays = max(1, (int) ceil(($reviewEditWindowHours ?? 168) / 24));
                    @endphp

                    @auth
                        @if($canReview)
                            @if($reviewDraftBorrowItem)
                                <div class="review-form-wrapper">
                                <form class="comment-form review-form" action="{{ route('books.comments.store', $book->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="borrow_item_id"
                                        value="{{ old('borrow_item_id', $reviewDraftBorrowItem->id) }}">

                                    @if(($errors->has('rating') || $errors->has('content') || $errors->has('borrow_item_id')) && $reviewFormBorrowItemId === (int) $reviewDraftBorrowItem->id)
                                        <div class="review-inline-error">
                                            {{ $errors->first('content') ?: $errors->first('rating') ?: $errors->first('borrow_item_id') }}
                                        </div>
                                    @endif

                                    <div class="review-form-header">
                                        <div>
                                            <p class="review-form-title">Viết đánh giá cho lượt thuê chưa đánh giá</p>
                                            <p class="review-form-subtitle">Chỉ người đã thuê xong mới được gửi đánh giá. Bạn có thể sửa trong {{ $reviewEditWindowDays }} ngày kể từ lúc gửi.</p>
                                        </div>
                                        <span class="verified-review-badge">✓ Người thuê đã xác thực</span>
                                    </div>

                                    <div class="rating-helper">Chọn số sao của bạn</div>
                                    <div class="star-rating-input">
                                        @for($i = 5; $i >= 1; $i--)
                                            <input type="radio" id="create-rating-{{ $i }}" name="rating" value="{{ $i }}"
                                                {{ (int) old('rating', 0) === $i ? 'checked' : '' }} required>
                                            <label for="create-rating-{{ $i }}" title="{{ $i }} sao">★</label>
                                        @endfor
                                    </div>

                                    <textarea name="content" placeholder="Chia sẻ trải nghiệm thực tế của bạn về cuốn sách này..." maxlength="1500"
                                        oninput="updateCharCount(this)" required>{{ $reviewFormBorrowItemId === (int) $reviewDraftBorrowItem->id ? old('content', '') : '' }}</textarea>
                                    <p class="char-count">
                                        <span class="js-char-count">{{ strlen($reviewFormBorrowItemId === (int) $reviewDraftBorrowItem->id ? old('content', '') : '') }}</span>/1500
                                    </p>
                                    <div class="review-form-actions">
                                        <button type="submit" class="btn btn-comment">Gửi đánh giá</button>
                                    </div>
                                </form>
                                </div>
                            @else
                                <div class="review-note-box">
                                    Bạn đã đánh giá các lượt thuê của mình cho sách này. Các đánh giá của bạn được đưa lên đầu danh sách để tiện sửa nhanh trong {{ $reviewEditWindowDays }} ngày đầu.
                                </div>
                            @endif
                        @else
                            <div class="review-locked-box">
                                <strong>Chưa thể đánh giá</strong>
                                <p style="margin: 8px 0 0;">{{ $reviewEligibilityMessage }}</p>
                            </div>
                        @endif
                    @else
                        <div class="review-login-box">
                            <p>Vui lòng <a href="{{ route('login') }}" style="color: #cc0000; font-weight: bold;">đăng
                                    nhập</a> để đánh giá sau khi thuê sách.</p>
                        </div>
                    @endauth

                    @if($stats['total_reviews'] > 0)
                        <div class="reviews-summary-card">
                            <div class="reviews-summary-score">
                                <strong>{{ number_format($stats['average_rating'] ?? 0, 1) }}</strong>
                                <div class="stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        {{ $i <= round($stats['average_rating'] ?? 0) ? '★' : '☆' }}
                                    @endfor
                                </div>
                                <span>{{ $stats['total_reviews'] ?? 0 }} đánh giá từ người đã thuê</span>
                            </div>
                            <div class="reviews-breakdown">
                                @foreach($ratingBreakdown as $star => $count)
                                    @php
                                        $percent = ($stats['total_reviews'] ?? 0) > 0 ? ($count / $stats['total_reviews']) * 100 : 0;
                                    @endphp
                                    <div class="reviews-breakdown-row">
                                        <span>{{ $star }} sao</span>
                                        <div class="reviews-breakdown-bar">
                                            <div class="reviews-breakdown-fill" style="width: {{ $percent }}%;"></div>
                                        </div>
                                        <span>{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($recentReviews->count() > 0)
                            <div style="margin-top: 30px;">
                                <h3 style="margin-bottom: 15px;">Đánh giá gần đây ({{ $stats['total_reviews'] }})</h3>
                                @foreach($recentReviews->take(10) as $review)
                                    @php
                                        $isOwnReview = auth()->check() && auth()->id() === $review->user_id;
                                        $canEditThisReview = $isOwnReview && $review->canBeEditedBy(auth()->id());
                                        $isInlineEditorOpen = $canEditThisReview && ($reviewFormBorrowItemId === (int) ($review->borrow_item_id ?? 0));
                                    @endphp

                                    <div class="review-card {{ $isOwnReview ? 'review-card-own' : '' }}">
                                        <div class="review-card-header">
                                            <div class="review-card-user">
                                                <strong>{{ $review->user->name ?? 'Người dùng' }}</strong>
                                                <span class="verified-review-badge">✓ Đã thuê sách</span>
                                            </div>
                                            <span class="review-card-date">{{ $review->created_at->format('d/m/Y H:i') }}</span>
                                        </div>

                                        <div class="stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                {{ $i <= $review->rating ? '★' : '☆' }}
                                            @endfor
                                        </div>

                                        @if(!empty($review->comment))
                                            <p class="review-card-text">{{ $review->comment }}</p>
                                        @endif

                                        @if($isOwnReview)
                                            <div class="review-owner-row">
                                                <span class="review-owner-label">🟢 Đánh giá của bạn</span>
                                                <div class="review-edit-meta">
                                                    @if($canEditThisReview)
                                                        <span class="review-edit-limit">Có thể sửa đến {{ optional($review->edit_deadline)->format('d/m/Y H:i') }}</span>
                                                        <button type="button" class="review-edit-link"
                                                            onclick="toggleInlineReviewEditor({{ $review->id }}, true)">✏️ Sửa đánh giá</button>
                                                    @else
                                                        <span class="review-expired-note">Đã hết thời gian sửa đánh giá</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($canEditThisReview)
                                                <div id="inline-review-editor-{{ $review->id }}"
                                                    class="inline-review-editor {{ $isInlineEditorOpen ? '' : 'is-hidden' }}"
                                                    data-review-id="{{ $review->id }}">
                                                    <form class="comment-form review-form" action="{{ route('books.comments.store', $book->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="borrow_item_id" value="{{ $review->borrow_item_id }}">

                                                        @if(($errors->has('rating') || $errors->has('content') || $errors->has('borrow_item_id')) && $reviewFormBorrowItemId === (int) ($review->borrow_item_id ?? 0))
                                                            <div class="review-inline-error">
                                                                {{ $errors->first('content') ?: $errors->first('rating') ?: $errors->first('borrow_item_id') }}
                                                            </div>
                                                        @endif

                                                        <div class="review-form-header">
                                                            <div>
                                                                <p class="review-form-title">Cập nhật đánh giá của bạn</p>
                                                                <p class="review-form-subtitle">Bạn đang sửa đúng tại đánh giá này. Thời hạn sửa đến {{ optional($review->edit_deadline)->format('d/m/Y H:i') }}.</p>
                                                            </div>
                                                            <span class="verified-review-badge">✓ Người thuê đã xác thực</span>
                                                        </div>

                                                        <div class="rating-helper">Chọn số sao của bạn</div>
                                                        <div class="star-rating-input">
                                                            @for($i = 5; $i >= 1; $i--)
                                                                <input type="radio" id="review-{{ $review->id }}-rating-{{ $i }}" name="rating" value="{{ $i }}"
                                                                    {{ (int) old('borrow_item_id') === (int) ($review->borrow_item_id ?? 0)
                                                                        ? ((int) old('rating', $review->rating) === $i ? 'checked' : '')
                                                                        : ((int) $review->rating === $i ? 'checked' : '') }} required>
                                                                <label for="review-{{ $review->id }}-rating-{{ $i }}" title="{{ $i }} sao">★</label>
                                                            @endfor
                                                        </div>

                                                        <textarea name="content" placeholder="Chia sẻ trải nghiệm thực tế của bạn về cuốn sách này..." maxlength="1500"
                                                            oninput="updateCharCount(this)" required>{{ (int) old('borrow_item_id') === (int) ($review->borrow_item_id ?? 0) ? old('content', $review->comment ?? '') : ($review->comment ?? '') }}</textarea>
                                                        <p class="char-count">
                                                            <span class="js-char-count">{{ strlen((int) old('borrow_item_id') === (int) ($review->borrow_item_id ?? 0) ? old('content', $review->comment ?? '') : ($review->comment ?? '')) }}</span>/1500
                                                        </p>
                                                        <div class="review-form-actions">
                                                            <button type="submit" class="btn btn-comment">Cập nhật đánh giá</button>
                                                            <button type="button" class="btn review-cancel-btn" onclick="toggleInlineReviewEditor({{ $review->id }}, false)">Ẩn phần sửa</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        @endif

                                        @if($review->comments && $review->comments->whereNull('parent_id')->count() > 0)
                                            <div class="review-replies">
                                                @foreach($review->comments->whereNull('parent_id') as $comment)
                                                    <div class="review-reply-item">
                                                        <div class="review-reply-meta">
                                                            <strong>{{ $comment->user->name ?? 'Người dùng' }}</strong>
                                                            <span>•</span>
                                                            <span>{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                                        </div>
                                                        <div>{{ $comment->content }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
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
            const form = textarea.closest('form');
            const counter = form ? form.querySelector('.js-char-count') : null;

            if (counter) {
                counter.textContent = textarea.value.length;
            }
        }

        function toggleInlineReviewEditor(reviewId, shouldOpen) {
            document.querySelectorAll('.inline-review-editor').forEach((editor) => {
                if (String(editor.dataset.reviewId) !== String(reviewId)) {
                    editor.classList.add('is-hidden');
                }
            });

            const editor = document.getElementById(`inline-review-editor-${reviewId}`);
            if (!editor) return;

            if (!shouldOpen) {
                editor.classList.add('is-hidden');
                return;
            }

            editor.classList.remove('is-hidden');

            const textarea = editor.querySelector('textarea[name="content"]');
            if (textarea) {
                updateCharCount(textarea);
            }
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
                    Swal.fire({icon:'warning',title:'Giới hạn số lượng',text:`Chỉ còn ${maxBorrowQuantity} cuốn sách có sẵn.`,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                }
            } else {
                // Chế độ mua: sử dụng stock_quantity
                const stockQuantity = {{ $stats['stock_quantity'] ?? 0 }};
                if (currentQuantity > stockQuantity) {
                    currentQuantity = stockQuantity;
                    Swal.fire({icon:'warning',title:'Giới hạn số lượng',text:`Chỉ còn ${stockQuantity} cuốn trong kho.`,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                }
            }

            quantityInput.value = currentQuantity;
            updateTotalPrice();
        }

        function validateReservationQuantity() {
            const quantityInput = document.getElementById('paper-quantity');
            if (!quantityInput) return 1;

            const stockQuantity = {{ $stats['stock_quantity'] ?? 0 }};
            let quantity = parseInt(quantityInput.value) || 1;

            if (quantity < 1) {
                quantity = 1;
            }

            if (stockQuantity > 0 && quantity > stockQuantity) {
                quantity = stockQuantity;
                Swal.fire({icon:'warning',title:'Giới hạn số lượng',text:`Chỉ còn ${stockQuantity} cuốn trong kho.`,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
            }

            quantityInput.value = quantity;
            return quantity;
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

            const paperQuantity = parseInt(document.getElementById('paper-quantity')?.value) || 1;
            const paperTotal = basePrice * paperQuantity;
            totalPrice += paperTotal;

            const paperPriceElement = document.getElementById('paper-price');
            if (paperPriceElement) {
                paperPriceElement.textContent = new Intl.NumberFormat('vi-VN').format(paperTotal) + '₫';
            }

            if (paperQuantity < 1) {
                document.getElementById('paper-quantity').value = 1;
            }

            // Cập nhật giá tổng
            const totalPriceElement = document.getElementById('total-price');
            if (totalPriceElement) {
                totalPriceElement.textContent = new Intl.NumberFormat('vi-VN').format(Math.round(totalPrice)) + '₫';
            }
        }

        function showFavoriteMessage(message, type = 'success') {
            if (typeof window.showToast === 'function') {
                window.showToast(type === 'success' ? 'Thành công' : 'Thông báo', message, type);
            } else {
                Swal.fire({icon: type === 'success' ? 'success' : 'info', title: type === 'success' ? 'Thành công' : 'Thông báo', text: message, confirmButtonText: 'Đã hiểu', confirmButtonColor: '#6C63FF'});
            }
        }

        function toggleFavorite(bookId, button) {
            fetch('{{ route('account.favorites.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ book_id: bookId })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showFavoriteMessage(data.message || 'Không thể cập nhật sách yêu thích.', 'error');
                    return;
                }

                button.classList.toggle('active', !!data.is_favorited);
                const icon = button.querySelector('i');

                if (icon) {
                    icon.className = `${data.is_favorited ? 'fas' : 'far'} fa-heart`;
                }

                button.setAttribute('aria-label', data.is_favorited ? 'Bỏ yêu thích' : 'Thêm vào yêu thích');
                button.setAttribute('title', data.is_favorited ? 'Bỏ yêu thích' : 'Thêm vào yêu thích');

                showFavoriteMessage(data.message || 'Đã cập nhật sách yêu thích.');
            })
            .catch(() => {
                showFavoriteMessage('Có lỗi xảy ra khi cập nhật sách yêu thích.', 'error');
            });
        }

        function openPreviewModal() {
            var modal = document.getElementById('previewModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closePreviewModal() {
            var modal = document.getElementById('previewModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePreviewModal();
        });

        async function addToReservationCart() {
            @guest
                Swal.fire({icon:'warning',title:'Vui lòng đăng nhập',text:'Bạn cần đăng nhập để đặt trước sách!',confirmButtonText:'Đăng nhập',confirmButtonColor:'#6C63FF'}).then(() => { window.location.href = '{{ route("login") }}'; });
                return;
            @endguest

            const quantity = validateReservationQuantity();
            const stockQuantity = {{ $stats['stock_quantity'] ?? 0 }};

            if (stockQuantity === 0) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Thông báo', 'Hiện không có cuốn nào sẵn sàng. Bạn vẫn có thể đặt trước.', 'info');
                }
            }

            if (quantity > stockQuantity && stockQuantity > 0) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Thông báo', `Bạn chọn ${quantity} cuốn nhưng kho chỉ còn ${stockQuantity} cuốn.`, 'warning');
                }

                return;
            }

            let splitReservationItems = false;

            if (quantity > 1) {
                const result = await Swal.fire({
                    title: `Bạn đang thêm ${quantity} cuốn cùng một đầu sách.`,
                    text: 'Bạn có muốn trả 2 cuốn cùng một ngày không?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Cùng ngày trả',
                    cancelButtonText: 'Tách riêng',
                    confirmButtonColor: '#6C63FF',
                    cancelButtonColor: '#f8f9fa',
                    reverseButtons: true,
                });
                splitReservationItems = !result.isConfirmed;
            }

            fetch('{{ route("reservation-cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ book_id: {{ $book->id }}, quantity, split_items: splitReservationItems })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('Thành công', data.message || 'Đã thêm vào giỏ đặt trước.', 'success');
                    } else {
                        Swal.fire({icon:'success',title:'Thành công',text:data.message || 'Đã thêm vào giỏ đặt trước.',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                    }

                    if (typeof window.loadReservationCartCount === 'function') {
                        window.loadReservationCartCount();
                    }
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast('Có lỗi', data.message || 'Không thể thêm vào giỏ.', 'error');
                    } else {
                        Swal.fire({icon:'error',title:'Có lỗi xảy ra',text:data.message || 'Không thể thêm vào giỏ.',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
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
                    Swal.fire({icon:'error',title:'Lỗi kết nối',text:'Không thể thêm vào giỏ. Vui lòng thử lại.',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
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
                Swal.fire({icon:'warning',title:'Chưa nhập mã',text:'Vui lòng nhập mã giảm giá!',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
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
                const validCodes = ['ThuêSách2024', 'FREESHIP', 'DISCOUNT10'];

                if (validCodes.includes(code.toUpperCase())) {
                    Swal.fire({icon:'success',title:'Thành công',text:'Áp dụng mã giảm giá thành công!\n\nLưu ý: Chức năng giảm giá đang được phát triển.',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                    discountInput.value = '';
                } else {
                    Swal.fire({icon:'error',title:'Mã không hợp lệ',text:'Mã giảm giá không hợp lệ hoặc đã hết hạn!',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
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
                Swal.fire({icon:'warning',title:'Giới hạn số lượng',text:`Chỉ còn ${availableCopies} cuốn sách có sẵn.`,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
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
                Swal.fire({icon:'warning',title:'Giới hạn số lượng',text:`Chỉ còn ${availableCopies} cuốn sách có sẵn.`,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
            }

            // Cập nhật tóm tắt đơn hàng
            if (isBorrowMode) {
                updateBorrowSummary();
            }
        }

        // Hàm mượn sách ngay
        function borrowNow() {
            @guest
                Swal.fire({icon:'warning',title:'Vui lòng đăng nhập',text:'Bạn cần đăng nhập để mượn sách!',confirmButtonText:'Đăng nhập',confirmButtonColor:'#6C63FF'}).then(() => { window.location.href = '{{ route("login") }}'; });
                return;
            @endguest

            const availableCopies = {{ $stats['available_copies'] ?? 0 }};

            if (availableCopies <= 0) {
                Swal.fire({icon:'info',title:'Hết sách',text:'Hiện tại không còn sách có sẵn để mượn. Vui lòng thử lại sau.',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                return;
            }

            // Hiển thị modal để nhập số ngày mượn
            // Kiểm tra đăng ký độc giả sẽ được thực hiện ở trang checkout
            showBorrowModal();
        }

        // Thêm sách vào giỏ sách
        async function addToCart() {
            @guest
                Swal.fire({icon:'warning',title:'Vui lòng đăng nhập',text:'Bạn cần đăng nhập để thêm sách vào giỏ sách!',confirmButtonText:'Đăng nhập',confirmButtonColor:'#6C63FF'}).then(() => { window.location.href = '{{ route("login") }}'; });
                return;
            @endguest

            const availableCopies = {{ $stats['available_copies'] ?? 0 }};

            if (availableCopies <= 0) {
                Swal.fire({icon:'info',title:'Hết sách',text:'Hiện tại không còn sách có sẵn để mượn. Vui lòng thử lại sau.',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                return;
            }

            const quantity = parseInt(document.getElementById('borrow-quantity')?.value) || 1;
            const borrowDays = 14; // Mặc định 14 ngày
            const distance = 0; // Mặc định 0 km

            if (quantity > availableCopies) {
                Swal.fire({icon:'warning',title:'Vượt quá số lượng',text:`Chỉ còn ${availableCopies} cuốn sách có sẵn. Vui lòng chọn lại số lượng.`,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                return;
            }

            // Hỏi xác nhận TRƯỚC KHI thêm vào giỏ
            const result = await Swal.fire({
                title: 'Xác nhận thêm vào giỏ',
                text: 'Bạn có muốn thêm sách này vào giỏ sách không?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Có, thêm vào',
                cancelButtonText: 'Không',
                confirmButtonColor: '#6C63FF',
                cancelButtonColor: '#f8f9fa',
                reverseButtons: true,
            });

            if (!result.isConfirmed) return;

            // Hiển thị loading
            const btn = document.querySelector('.btn-add-to-cart');
            const originalText = btn ? btn.innerHTML : '';

            try {
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span style="font-size: 1.2em;">⏳</span> Đang thêm...';
                }

                const response = await fetch('{{ route("reservation-cart.add") }}', {
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
                });

                const data = await response.json();

                if (data.success) {
                    updateCartCount(data.cart_count);
                    Swal.fire({icon:'success',title:'Thành công',text:data.message || 'Đã thêm vào giỏ sách.',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                } else {
                    if (data.redirect) {
                        const r = await Swal.fire({icon:'warning',title:'Cần đăng ký',text:data.message + '\n\nBạn có muốn đăng ký ngay không?',showCancelButton:true,confirmButtonText:'Đăng ký ngay',cancelButtonText:'Không',confirmButtonColor:'#6C63FF'});
                        if (r.isConfirmed) window.location.href = data.redirect;
                    } else {
                        Swal.fire({icon:'error',title:'Có lỗi xảy ra',text:data.message || 'Không thể thêm vào giỏ sách.',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({icon:'error',title:'Lỗi kết nối',text:'Có lỗi xảy ra khi thêm sách vào giỏ sách.',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            }
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
        function displayUnifiedBorrowSummary(borrowQuantity, days, distance, totalRentalFee, totalDeposit, shippingFee, payableNow, returnDate) {
            const formatCurrency = (amount) => {
                return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
            };

            // 1. Info section
            let infoHtml = `
                <div class="borrow-info-section">
                    <h3><i class="fas fa-info-circle" style="color:#3b82f6"></i> Thông tin mượn</h3>
                    <div class="info-row">
                        <span>Số lượng mượn:</span>
                        <span class="info-value text-primary">${borrowQuantity} cuốn</span>
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
                        <span>Phí thuê (${borrowQuantity} cuốn × ${days} ngày):</span>
                        <span>${formatCurrency(totalRentalFee)}</span>
                    </div>
                    <div class="price-row">
                        <span>Tiền cọc (${borrowQuantity} cuốn):</span>
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
                Swal.fire({icon:'error',title:'Lỗi dữ liệu',text:'Không có thông tin mượn sách!',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                return;
            }

            const days = parseInt(daysInput.value) || 14;
            // Khoảng cách luôn là 0 ở modal
            const distance = 0;

            if (days < 0 || days > 30) {
                Swal.fire({icon:'warning',title:'Số ngày không hợp lệ',text:'Số ngày mượn phải từ 0 đến 30 ngày!',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                return;
            }

            if (borrowQuantity > availableCopies) {
                Swal.fire({icon:'warning',title:'Vượt quá số lượng',text:`Số lượng mượn vượt quá số lượng có sẵn. Chỉ còn ${availableCopies} cuốn.`,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
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
                Swal.fire({icon:'error',title:'Lỗi dữ liệu',text:'Không có thông tin mượn sách!',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                return;
            }

            if (daysInputs.length > availableCopies) {
                Swal.fire({icon:'warning',title:'Vượt quá số lượng',text:`Số lượng mượn vượt quá số lượng có sẵn. Chỉ còn ${availableCopies} cuốn.`,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                return;
            }

            // ✅ KHÔNG GỘI API, CHỈ REDIRECT CHECKOUT VỚI THÔNG SỐ
            const items = [];
            daysInputs.forEach((daysInput, index) => {
                const days = parseInt(daysInput.value) || 14;
                // Khoảng cách luôn là 0 - không cho nhập thủ công
                const distance = 0;

                if (days < 1 || days > 30) {
                    Swal.fire({icon:'warning',title:'Số ngày không hợp lệ',text:`Quyển ${index + 1}: Số ngày mượn phải từ 1 đến 30 ngày!`,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                    return;
                }

                items.push({
                    book_id: {{ $book->id }},
                    borrow_days: days,
                    distance: distance
                });
            });

            if (items.length === 0) {
                Swal.fire({icon:'error',title:'Lỗi dữ liệu',text:'Không có thông tin mượn sách hợp lệ!',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
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
                Swal.fire({icon:'warning',title:'Số lượng không hợp lệ',text:`Số lượng mượn không hợp lệ. Vui lòng chọn từ 1 đến ${availableCopies} cuốn.`,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
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
                            Swal.fire({icon:'warning',title:'Vui lòng đăng nhập',text:data.message || 'Bạn cần đăng nhập để mượn sách!',confirmButtonText:'Đăng nhập',confirmButtonColor:'#6C63FF'}).then(() => {
                                if (data.redirect) window.location.href = data.redirect;
                                else window.location.href = '{{ route("login") }}';
                            });
                            confirmBtn.disabled = false;
                            confirmBtn.textContent = 'Xác nhận mượn sách';
                            return;
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (!data) {
                        console.error('No data returned from server');
                        Swal.fire({icon:'error',title:'Lỗi server',text:'Không nhận được phản hồi từ server!',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = 'Xác nhận mượn sách';
                        return;
                    }

                    if (data.success) {
                        console.log('Borrow created successfully:', data.data);
                        closeBorrowModal();

                        const quantity = data.data?.quantity || 1;
                        const message = (data.message || 'Đã gửi yêu cầu mượn sách thành công!') +
                            '\n\nSố lượng mượn: ' + quantity + ' cuốn' +
                            '\nMã phiếu mượn: ' + (data.data?.borrow_id || 'N/A') +
                            '\nMã chi tiết: ' + (data.data?.borrow_item_id || 'N/A') +
                            '\n\nYêu cầu đã được gửi và sẽ hiển thị trong trang "Quản lý mượn sách" của admin.';

                        Swal.fire({icon:'success',title:'Thành công',text:message,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'}).then(() => {
                            window.location.href = '{{ route("reservation-cart.index") }}';
                        });
                    } else {
                        console.error('Borrow creation failed:', data.message);
                        Swal.fire({icon:'error',title:'Có lỗi xảy ra',text:data.message || 'Có lỗi khi gửi yêu cầu mượn sách!',confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'}).then(() => {
                            if (data.redirect) window.location.href = data.redirect;
                        });
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = 'Xác nhận mượn sách';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    Swal.fire({icon:'error',title:'Lỗi kết nối',text:'Có lỗi xảy ra: ' + error.message,confirmButtonText:'Đã hiểu',confirmButtonColor:'#6C63FF'});
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

    <!-- Modal Đọc Thử -->
    <div id="previewModal" style="display:none; position:fixed; inset:0; z-index:10000; align-items:center; justify-content:center;">
        <div style="position:absolute; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px);" onclick="closePreviewModal()"></div>
        <div style="position:relative; width:90%; max-width:680px; max-height:85vh; background:#fff; border-radius:20px; box-shadow:0 25px 60px rgba(0,0,0,0.25); display:flex; flex-direction:column; animation:modalFadeIn .25s ease;">
            <!-- Header -->
            <div style="padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <div>
                    <h2 style="margin:0; font-size:1.2rem; color:#111827; font-weight:700;">📖 Đọc thử - {{ $book->ten_sach }}</h2>
                    <p style="margin:4px 0 0; font-size:0.85rem; color:#6b7280;">Xem trước nội dung sách</p>
                </div>
                <button onclick="closePreviewModal()" style="width:36px; height:36px; border-radius:50%; border:none; background:#f3f4f6; cursor:pointer; font-size:1.2rem; color:#6b7280; display:flex; align-items:center; justify-content:center; transition:all .2s;">&times;</button>
            </div>
            <!-- Body -->
            <div id="previewModalBody" style="flex:1; overflow-y:auto; padding:24px; font-size:0.95rem; line-height:1.8; color:#374151;">
                {!! $book->preview_content ?? '<p style="text-align:center; color:#9ca3af; padding:40px;">Nội dung xem trước đang được cập nhật...</p>' !!}
            </div>
            <!-- Footer -->
            <div style="padding:16px 24px; border-top:1px solid #e5e7eb; display:flex; gap:12px; justify-content:flex-end; flex-shrink:0; background:#f9fafb; border-radius:0 0 20px 20px;">
                <button onclick="closePreviewModal()" style="padding:10px 24px; border-radius:10px; border:1px solid #d1d5db; background:#fff; color:#374151; font-weight:600; cursor:pointer; font-size:0.9rem; transition:all .2s;">
                    Đóng
                </button>
                <button onclick="addToReservationCart(); closePreviewModal();" style="padding:10px 24px; border-radius:10px; border:none; background:#0d9488; color:#fff; font-weight:600; cursor:pointer; font-size:0.9rem; box-shadow:0 4px 12px rgba(13,148,136,0.3); transition:all .2s;">
                    📌 Đặt trước sách này
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Đặt Trước đã được xóa (thay bằng chức năng thêm vào giỏ sách) -->
</body>

</html>