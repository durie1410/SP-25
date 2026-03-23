@extends('layouts.app')

@section('title', 'Giỏ đặt trước')

@push('styles')
<style>
    :root {
        --reserve-primary: #0f766e;
        --reserve-primary-soft: #ccfbf1;
        --reserve-accent: #ea580c;
        --reserve-bg: #f5f7f2;
        --reserve-surface: #fffdf7;
        --reserve-border: #dbe4dc;
        --reserve-text: #0f172a;
        --reserve-muted: #5f6b68;
        --reserve-danger: #dc2626;
        --reserve-success: #0f9f6e;
        --radius-xl: 24px;
        --radius-lg: 18px;
        --radius-md: 12px;
        --reserve-shadow: 0 22px 50px rgba(15, 23, 42, 0.08);
    }

    body {
        background:
            radial-gradient(circle at top left, rgba(15, 118, 110, 0.08), transparent 26%),
            radial-gradient(circle at top right, rgba(234, 88, 12, 0.08), transparent 24%),
            linear-gradient(180deg, #f7faf7 0%, #eef3ea 100%);
    }

    .reservation-cart-page {
        max-width: 1300px;
        margin: 22px auto 40px;
        padding: 0 18px 40px;
    }

    .reservation-page-header {
        margin-bottom: 28px;
        padding: 24px 28px;
        border: 1px solid rgba(219, 228, 220, 0.9);
        border-radius: 28px;
        background: linear-gradient(135deg, rgba(255, 253, 247, 0.96), rgba(240, 253, 250, 0.92));
        box-shadow: var(--reserve-shadow);
        position: relative;
        overflow: hidden;
    }

    .reservation-page-header::after {
        content: '';
        position: absolute;
        inset: auto -60px -60px auto;
        width: 220px;
        height: 220px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(15, 118, 110, 0.13), transparent 65%);
        pointer-events: none;
    }

    .reservation-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: var(--reserve-muted);
        margin-bottom: 10px;
    }

    .reservation-breadcrumb a {
        color: var(--reserve-primary);
        text-decoration: none;
        font-weight: 600;
    }

    .reservation-title {
        font-size: 34px;
        line-height: 1.15;
        font-weight: 800;
        color: var(--reserve-text);
        margin: 0;
    }

    .reservation-header-subtitle {
        max-width: 640px;
        margin: 10px 0 0;
        font-size: 15px;
        line-height: 1.7;
        color: var(--reserve-muted);
        position: relative;
        z-index: 1;
    }


    .reservation-cart-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.75fr) minmax(320px, 0.95fr);
        gap: 26px;
        align-items: flex-start;
    }

    .reservation-left-col {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .reservation-card {
        background: rgba(255, 255, 255, 0.92);
        border-radius: var(--radius-xl);
        padding: 22px 24px;
        border: 1px solid var(--reserve-border);
        box-shadow: var(--reserve-shadow);
        backdrop-filter: blur(8px);
    }

    .reservation-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .reservation-card-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--reserve-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .reservation-date-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-top: 6px;
    }

    .reservation-date-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 160px;
    }

    .reservation-date-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--reserve-muted);
    }

    .reservation-date-group input[type="date"] {
        border-radius: var(--radius-md);
        border: 1px solid var(--reserve-border);
        padding: 12px 14px;
        font-size: 14px;
        background: #fff;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .reservation-items-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 8px;
    }

    .reservation-item {
        display: grid;
        grid-template-columns: 34px 92px minmax(0, 1fr) 220px;
        gap: 18px;
        align-items: start;
        background: linear-gradient(180deg, #fffefb 0%, #ffffff 100%);
        border-radius: var(--radius-lg);
        border: 1px solid var(--reserve-border);
        padding: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .reservation-item.is-unselected {
        opacity: 0.68;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.04);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(255, 255, 255, 0.92));
    }

    .reservation-item:hover {
        transform: translateY(-2px);
        border-color: rgba(15, 118, 110, 0.22);
        box-shadow: 0 20px 36px rgba(15, 23, 42, 0.1);
    }

    .reservation-item-select {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 10px;
    }

    .reservation-item-checkbox,
    .reservation-select-all-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: var(--reserve-primary);
    }

    .reservation-item-img-box {
        width: 92px;
        height: 128px;
        border-radius: 16px;
        overflow: hidden;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.16);
    }

    .reservation-item-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .reservation-item-info {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 0;
    }

    .reservation-item-title {
        font-size: 22px;
        line-height: 1.35;
        font-weight: 800;
        margin: 0;
        color: var(--reserve-text);
    }

    .reservation-item-variant {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.1);
        color: var(--reserve-primary);
        font-size: 12px;
        font-weight: 700;
    }

    .reservation-item-author {
        font-size: 14px;
        color: var(--reserve-muted);
    }

    .reservation-item-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 13px;
        color: var(--reserve-muted);
    }

    .reservation-item-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #f4f7f2;
        border: 1px solid rgba(219, 228, 220, 0.8);
    }

    .reservation-item-meta span strong {
        color: var(--reserve-text);
    }

    .reservation-fee-breakdown {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        width: fit-content;
        padding: 8px 12px;
        border-radius: 14px;
        background: #eef6f2;
        border: 1px solid rgba(15, 118, 110, 0.1);
        font-size: 12px;
        color: var(--reserve-muted);
    }

    .reservation-fee-breakdown strong {
        color: var(--reserve-text);
    }

    .reservation-item-actions {
        display: flex;
        flex-direction: column;
        gap: 14px;
        align-items: stretch;
        min-width: 220px;
        padding: 16px;
        border-radius: 18px;
        background: linear-gradient(180deg, #f8fbf7 0%, #ffffff 100%);
        border: 1px solid rgba(219, 228, 220, 0.9);
    }

    .reservation-select-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 16px;
        border: 1px solid rgba(15, 118, 110, 0.12);
        border-radius: var(--radius-lg);
        background: linear-gradient(135deg, #f0fdfa, #f8fafc);
        margin-bottom: 12px;
    }

    .reservation-select-all {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 600;
        color: var(--reserve-text);
    }

    .reservation-select-hint {
        font-size: 13px;
        color: var(--reserve-muted);
    }

    .reservation-quantity-control {
        display: inline-flex;
        align-items: center;
        border: 1px solid var(--reserve-border);
        border-radius: 999px;
        overflow: hidden;
        background: #fff;
        width: fit-content;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .reservation-quantity-btn {
        width: 38px;
        height: 38px;
        border: none;
        background: #fff;
        color: var(--reserve-text);
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
    }

    .reservation-quantity-btn:hover {
        background: #f0fdfa;
    }

    .reservation-quantity-input {
        width: 54px;
        height: 38px;
        border: none;
        border-left: 1px solid var(--reserve-border);
        border-right: 1px solid var(--reserve-border);
        text-align: center;
        font-size: 14px;
        font-weight: 700;
    }

    .reservation-quantity-input::-webkit-outer-spin-button,
    .reservation-quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }


    .reservation-days-pill {
        font-size: 13px;
        padding: 6px 11px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: var(--reserve-primary);
        font-weight: 600;
    }

    .reservation-side-label {
        font-size: 12px;
        color: var(--reserve-muted);
        font-weight: 600;
    }

    .reservation-remove-btn {
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        justify-content: center;
        width: 100%;
        font-weight: 600;
    }

    .reservation-summary {
        position: sticky;
        top: 92px;
    }

    .reservation-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        font-size: 15px;
        color: var(--reserve-muted);
    }

    .reservation-summary-row.total {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed var(--reserve-border);
        font-size: 20px;
        font-weight: 800;
        color: var(--reserve-text);
    }

    .reservation-total-price {
        color: var(--reserve-accent);
    }

    .reservation-summary-note {
        margin-top: 14px;
        padding: 14px 15px;
        border-radius: var(--radius-md);
        background: #f7faf8;
        border: 1px solid rgba(219, 228, 220, 0.9);
        font-size: 13px;
        line-height: 1.7;
        color: var(--reserve-muted);
    }

    .reservation-submit-btn {
        width: 100%;
        margin-top: 18px;
        border-radius: 999px;
        padding: 14px 18px;
        font-weight: 700;
        font-size: 15px;
        background: linear-gradient(135deg, var(--reserve-primary), #0d9488) !important;
        border: none !important;
        box-shadow: 0 14px 30px rgba(15, 118, 110, 0.28);
    }

    .reservation-empty {
        text-align: center;
        padding: 70px 32px;
        background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(255,253,247,0.96));
        border-radius: var(--radius-xl);
        border: 1px dashed var(--reserve-border);
        box-shadow: var(--reserve-shadow);
    }

    .reservation-empty-icon {
        font-size: 60px;
        margin-bottom: 18px;
        color: #cbd5f5;
    }

    .reservation-empty-title {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 6px;
        color: var(--reserve-text);
    }

    .reservation-empty-text {
        color: var(--reserve-muted);
        margin-bottom: 18px;
    }

    .reservation-empty-btn {
        border-radius: 999px;
        padding: 12px 22px;
        font-weight: 600;
    }

    .reservation-item-price-stack {
        text-align: left;
        padding: 12px 14px;
        border-radius: 14px;
        background: #fff7ed;
        border: 1px solid rgba(234, 88, 12, 0.12);
    }

    .reservation-item-price-value {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--reserve-accent);
    }

    .reservation-split-btn {
        align-self: flex-start;
        border: none;
        background: transparent;
        padding: 0;
        color: var(--reserve-primary);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .reservation-split-btn:hover {
        color: #115e59;
    }

    .reservation-split-form.is-hidden {
        display: none;
    }

    @media (max-width: 1024px) {
        .reservation-cart-grid {
            grid-template-columns: minmax(0, 1fr);
        }
        .reservation-summary {
            position: static;
        }

        .reservation-item {
            grid-template-columns: 34px 84px minmax(0, 1fr);
        }

        .reservation-item-actions {
            grid-column: 2 / -1;
            margin-top: 4px;
        }
    }

    @media (max-width: 768px) {
        .reservation-page-header {
            padding: 20px;
            border-radius: 24px;
        }

        .reservation-title {
            font-size: 28px;
        }

        .reservation-select-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }

        .reservation-item {
            grid-template-columns: 28px 72px minmax(0, 1fr);
            align-items: flex-start;
            padding: 16px;
        }

        .reservation-item-img-box {
            width: 72px;
            height: 104px;
        }

        .reservation-item-title {
            font-size: 18px;
        }

        .reservation-date-row {
            grid-template-columns: 1fr;
        }

        .reservation-item-actions {
            grid-column: 1 / -1;
            margin-top: 8px;
        }

        .reservation-submit-btn {
            border-radius: 12px;
        }
    }

    @media (max-width: 560px) {
        .reservation-cart-page {
            padding: 0 12px 32px;
        }

        .reservation-card {
            padding: 18px;
        }

        .reservation-item {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .reservation-item-select {
            justify-content: flex-start;
            padding-top: 0;
        }

        .reservation-item-img-box {
            width: 84px;
            height: 118px;
        }

        .reservation-item-actions {
            grid-column: auto;
            padding: 14px;
        }
    }
</style>
@endpush

@section('content')
<div class="reservation-cart-page">
    <div class="reservation-page-header">
        <div class="reservation-breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <i class="fas fa-chevron-right" style="font-size:10px;"></i>
            <span>Giỏ đặt trước</span>
        </div>
        <h1 class="reservation-title">
            <i class="fas fa-calendar-check text-primary me-2"></i> Giỏ đặt trước
        </h1>
        <p class="reservation-header-subtitle">
            Chọn những cuốn muốn gửi yêu cầu ngay bây giờ, phần còn lại vẫn giữ nguyên trong giỏ để bạn đặt sau.
        </p>
    </div>

@foreach (['success','error','info'] as $msg)
    @if(session($msg))
        <div class="alert alert-{{ $msg == 'error' ? 'danger' : $msg }}">
            {{ session($msg) }}
        </div>
    @endif
@endforeach

@if($itemsWithStock->count() === 0)
        <div class="reservation-empty">
            <div class="reservation-empty-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="reservation-empty-title">Giỏ đặt trước đang trống</div>
            <div class="reservation-empty-text">
                Hãy chọn những cuốn sách bạn muốn mượn trước, chúng tôi sẽ giữ cho bạn trong khoảng thời gian đã đặt.
            </div>
            <a href="{{ route('books.public') }}" class="btn btn-primary reservation-empty-btn">
                <i class="fas fa-book-open"></i> Khám phá kho sách
        </a>
    </div>
@else
        @if(request('configure_dates'))
            <div class="reservation-card" style="margin-bottom: 18px; border: 1px solid rgba(13, 148, 136, 0.22); background: linear-gradient(135deg, #f0fdfa, #eff6ff);">
                <div class="reservation-card-header" style="border-bottom: none; padding-bottom: 10px;">
                    <h3 class="reservation-card-title" style="color: #0f172a;">
                        <i class="fas fa-calendar-check"></i>
                        Chọn thời gian riêng cho từng cuốn
                    </h3>
                </div>
                <div style="padding: 0 22px 20px; color: #475569; line-height: 1.7;">
                    Bạn vừa chọn mượn nhiều cuốn cùng một đầu sách với <strong>thời gian trả khác nhau</strong>.
                    Mỗi dòng bên dưới tương ứng với một cuốn trong giỏ, bạn có thể chọn <strong>ngày lấy</strong> và <strong>ngày trả</strong> riêng cho từng cuốn.
                </div>
            </div>
        @endif
        <div class="reservation-cart-grid">
            <div class="reservation-left-col">
                {{-- DANH SÁCH SÁCH ĐẶT TRƯỚC --}}
                <div class="reservation-card">
                    <div class="reservation-card-header">
                        <h3 class="reservation-card-title">
                            <i class="fas fa-list-ul"></i>
                            Sách trong giỏ đặt trước ({{ $itemsWithStock->sum('quantity') }})
                        </h3>
                    </div>

                    <div class="reservation-select-toolbar">
                        <label class="reservation-select-all" for="reservation-select-all">
                            <input
                                type="checkbox"
                                id="reservation-select-all"
                                class="reservation-select-all-checkbox"
                                checked
                                onchange="toggleSelectAllReservationItems(this)"
                            >
                            <span>Chọn tất cả sách trong giỏ</span>
                        </label>
                        <div class="reservation-select-hint">
                            Chỉ các cuốn được tick mới được gửi đặt trước.
                        </div>
                    </div>

                    <div class="reservation-items-list">
                        @foreach($itemsWithStock as $item)
                            @php
                                $sameBookItems = $itemsWithStock->where('book_id', $item->book_id)->values();
                                $sameBookIndex = $sameBookItems->search(fn($cartItem) => $cartItem->id === $item->id);
                                // Lấy daily_fee từ cart item, nếu null thì lấy từ sách
                                $dailyFee = (int) ($item->daily_fee ?? $item->book?->daily_fee ?? 5000);
                                $quantity = max(1, (int) ($item->quantity ?? 1));
                                $availableStock = (int) ($item->availableStock ?? 0);

                                // Lấy ngày từ DB (có thể là string hoặc object)
                                $pickupDateStr = null;
                                $returnDateStr = null;

                                if(!empty($item->pickup_date)) {
                                    $pickupDateStr = is_object($item->pickup_date)
                                        ? $item->pickup_date->format('Y-m-d')
                                        : $item->pickup_date;
                                }
                                if(!empty($item->return_date)) {
                                    $returnDateStr = is_object($item->return_date)
                                        ? $item->return_date->format('Y-m-d')
                                        : $item->return_date;
                                }

                                // Tính số ngày: mượn và trả cùng ngày = 1 ngày
                                // mượn hôm nay, trả ngày mai = 2 ngày
                                $computedDays = 0;
                                $computedTotal = 0;
                                if($pickupDateStr && $returnDateStr) {
                                    $pickup = new \DateTime($pickupDateStr);
                                    $return = new \DateTime($returnDateStr);
                                    // Cộng 1 để tính cả ngày mượn
                                    $computedDays = (int)$pickup->diff($return)->days + 1;
                                    $computedTotal = $computedDays * $dailyFee * $quantity;
                                }
                            @endphp
                            <div class="reservation-item" data-item-id="{{ $item->id }}" data-item-total="{{ $computedTotal }}" data-quantity="{{ $quantity }}" data-daily-fee="{{ $dailyFee }}">
                                <div class="reservation-item-select">
                                    <input
                                        type="checkbox"
                                        class="reservation-item-checkbox"
                                        name="selected_item_ids[]"
                                        value="{{ $item->id }}"
                                        form="reservation-submit-form"
                                        checked
                                        onchange="handleReservationSelectionChange()"
                                    >
                                    <!-- Hidden inputs for dates -->
                                    <input type="hidden" name="items[{{ $item->id }}][pickup_date]" value="{{ $item->pickup_date ? (is_object($item->pickup_date) ? $item->pickup_date->format('Y-m-d') : $item->pickup_date) : '' }}" form="reservation-submit-form">
                                    <input type="hidden" name="items[{{ $item->id }}][return_date]" value="{{ $item->return_date ? (is_object($item->return_date) ? $item->return_date->format('Y-m-d') : $item->return_date) : '' }}" form="reservation-submit-form">
                                </div>

                                <div class="reservation-item-img-box">
                                    <img src="{{ $item->book->image_url ?? asset('images/default-book.png') }}"
                                         alt="{{ $item->book->ten_sach }}">
                                </div>

                                <div class="reservation-item-info">
                                    <h4 class="reservation-item-title">
                                        {{ $item->book->ten_sach }}
                                    </h4>

                                    @if($sameBookItems->count() > 1)
                                        <div class="reservation-item-variant">
                                            <i class="fas fa-copy"></i>
                                            Bản đặt trước #{{ ($sameBookIndex !== false ? $sameBookIndex + 1 : 1) }} cho cùng đầu sách
                                        </div>
                                    @endif

                                    <div class="reservation-item-author">
                                        Tác giả: <strong>{{ $item->book->tac_gia ?? 'Không rõ' }}</strong>
                                    </div>

                                    <div class="reservation-item-meta">
                                        <span>
                                            <i class="fas fa-layer-group me-1"></i>
                                            Số lượng: <strong class="reservation-quantity-value">{{ $quantity }}</strong>
                                        </span>
                                        <span>
                                            <i class="fas fa-calendar-day me-1"></i>
                                            Số ngày mượn:
                                            <span class="reservation-days-pill">
                                                <span class="days-display" data-item-id="{{ $item->id }}">{{ $computedDays }}</span> ngày
                                            </span>
                                        </span>
                                    </div>

                                    <div class="reservation-item-meta" style="margin-top: 8px;">
                                        <span class="reservation-fee-breakdown" data-daily-fee="{{ $dailyFee }}" data-item-id="{{ $item->id }}">
                                            <span class="fee-breakdown-text" data-item-id="{{ $item->id }}">
                                                {{ $computedDays }} ngày × {{ number_format($dailyFee, 0, ',', '.') }}₫/ngày × {{ max(1, (int) ($item->quantity ?? 1)) }} cuốn
                                            </span>
                                            <span class="fee-breakdown-total" data-item-id="{{ $item->id }}">
                                                = <strong>{{ number_format($computedTotal, 0, ',', '.') }}₫</strong>
                                            </span>
                                        </span>
                                    </div>

                                    <div class="reservation-date-row">
                                        <div class="reservation-date-group" style="min-width: 0;">
                                            <span class="reservation-date-label">Ngày lấy</span>
                                            <input
                                                type="date"
                                                class="form-control pickup-date"
                                                data-item-id="{{ $item->id }}"
                                                min="{{ now()->format('Y-m-d') }}"
                                                max="{{ now()->addDays(config('library.borrow_max_days', 14))->format('Y-m-d') }}"
                                                value="{{ $item->pickup_date ? \Carbon\Carbon::parse($item->pickup_date)->format('Y-m-d') : '' }}"
                                                onchange="handleItemDateChange(this)"
                                            >
                                        </div>
                                        <div class="reservation-date-group" style="min-width: 0;">
                                            <span class="reservation-date-label">Ngày trả</span>
                                            <input
                                                type="date"
                                                class="form-control return-date"
                                                data-item-id="{{ $item->id }}"
                                                min="{{ $item->pickup_date ? \Carbon\Carbon::parse($item->pickup_date)->addDay()->format('Y-m-d') : now()->addDay()->format('Y-m-d') }}"
                                                max="{{ $item->pickup_date ? \Carbon\Carbon::parse($item->pickup_date)->addDays(config('library.borrow_max_days', 14))->format('Y-m-d') : now()->addDays(config('library.borrow_max_days', 14))->format('Y-m-d') }}"
                                                value="{{ $item->return_date ? \Carbon\Carbon::parse($item->return_date)->format('Y-m-d') : '' }}"
                                                onchange="handleItemDateChange(this)"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="reservation-item-actions">
                                    <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-start;">
                                        <div class="reservation-side-label">Số lượng đặt trước</div>
                                        <div class="reservation-quantity-control">
                                            <button type="button" class="reservation-quantity-btn" onclick="changeReservationQuantity({{ $item->id }}, -1)">-</button>
                                            <input
                                                type="number"
                                                id="reservation-quantity-{{ $item->id }}"
                                                class="reservation-quantity-input"
                                                value="{{ $quantity }}"
                                                min="1"
                                                max="{{ $availableStock }}"
                                                data-available-stock="{{ $availableStock }}"
                                                onchange="updateReservationQuantityInput({{ $item->id }})"
                                            >
                                            <button type="button" class="reservation-quantity-btn" onclick="changeReservationQuantity({{ $item->id }}, 1)">+</button>
                                        </div>
                                        @if($availableStock > 0 && $quantity > $availableStock)
                                            <span class="stock-warning" style="color: #dc2626; font-size: 12px; margin-top: 4px;">
                                                <i class="fas fa-exclamation-triangle"></i> Kho chỉ còn {{ $availableStock }} cuốn
                                            </span>
                                        @elseif($availableStock > 0 && $availableStock <= 3)
                                            <span class="stock-warning" style="color: #d97706; font-size: 12px; margin-top: 4px;">
                                                <i class="fas fa-info-circle"></i> Chỉ còn {{ $availableStock }} cuốn trong kho
                                            </span>
                                        @endif
                                        @if($quantity > 1)
                                            <form method="POST" action="{{ route('reservation-cart.split-item', $item->id) }}" class="reservation-split-form {{ $quantity > 1 ? '' : 'is-hidden' }}" data-item-id="{{ $item->id }}">
                                                @csrf
                                                <button type="submit" class="reservation-split-btn">
                                                    Tách thành từng cuốn riêng
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="reservation-item-price-stack">
                                        <div class="reservation-side-label" style="margin-bottom: 4px;">Tạm tính</div>
                                        <span class="item-price reservation-item-price-value" data-item-id="{{ $item->id }}">
                                            {{ number_format($computedTotal, 0, ',', '.') }}₫
                                        </span>
                                    </div>

                                    <form method="POST" action="{{ route('reservation-cart.remove', $item->id) }}">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm reservation-remove-btn" type="submit">
                                            <i class="fas fa-times"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- TÓM TẮT --}}
            <div class="reservation-summary">
                <div class="reservation-card">
                    <div class="reservation-card-header">
                        <h3 class="reservation-card-title">
                            <i class="fas fa-receipt"></i>
                            Tóm tắt giỏ đặt trước
                        </h3>
                    </div>

                    <div class="reservation-summary-row">
                        <span>Sách đã chọn</span>
                        <span><strong id="selected-books-count">{{ $itemsWithStock->sum('quantity') }}</strong> cuốn</span>
                    </div>

                    <div class="reservation-summary-row total">
                        <span>Tổng tạm tính đã chọn</span>
                        <span class="reservation-total-price" id="total-price">
                            @php
                                $total = 0;
                                foreach($itemsWithStock as $item) {
                                    $pickupDateStr = null;
                                    $returnDateStr = null;

                                    if(!empty($item->pickup_date)) {
                                        $pickupDateStr = is_object($item->pickup_date)
                                            ? $item->pickup_date->format('Y-m-d')
                                            : $item->pickup_date;
                                    }
                                    if(!empty($item->return_date)) {
                                        $returnDateStr = is_object($item->return_date)
                                            ? $item->return_date->format('Y-m-d')
                                            : $item->return_date;
                                    }

                                    if(!empty($pickupDateStr) && !empty($returnDateStr)) {
                                        $pickup = new \DateTime($pickupDateStr);
                                        $return = new \DateTime($returnDateStr);
                                        // Cùng ngày = 1 ngày, mượn hôm nay trả ngày mai = 2 ngày
                                        $days = (int)$pickup->diff($return)->days + 1;
                                        $quantity = max(1, (int)($item->quantity ?? 1));
                                        // Lấy daily_fee từ cart item, nếu null thì lấy từ sách
                                        $dailyFee = (int)($item->daily_fee ?? $item->book?->daily_fee ?? 5000);
                                        $total += $days * $dailyFee * $quantity;
                                    }
                                }
                            @endphp
                            {{ number_format($total,0,',','.') }}₫
                        </span>
</div>

                    <div class="reservation-summary-note">
                        <i class="fas fa-info-circle me-1"></i>
                        Chỉ các sách được tick mới được gửi đi. Vui lòng chọn <strong>ngày lấy</strong> và <strong>ngày trả</strong> cho từng sách đã chọn.
                        <div style="margin-top: 8px;">
                            Giờ nhận sách: {{ config('library.open_hour', '08:00') }} - {{ config('library.close_hour', '20:00') }}. Thời gian mượn: {{ config('library.borrow_min_days', 1) }} - {{ config('library.borrow_max_days', 14) }} ngày.
                        </div>
                    </div>

                    <div class="reservation-summary-note" style="margin-top: 12px;">
                        <div style="font-weight: 700; margin-bottom: 8px;">Giờ nhận sách</div>
                        <div class="reservation-date-row" style="margin-bottom: 12px;">
                            <div class="reservation-date-group" style="min-width: 0;">
                                <span class="reservation-date-label">Giờ lấy</span>
                                <div style="display: flex; gap: 8px;">
                                    <select class="form-control" id="pickup-time-hour" onchange="handlePickupTimeChange()">
                                        @for($h = 8; $h <= 20; $h++)
                                            <option value="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}" {{ ($itemsWithStock->first()?->pickup_time && strpos($itemsWithStock->first()->pickup_time, str_pad($h, 2, '0', STR_PAD_LEFT) . ':') === 0) ? 'selected' : '' }}>{{ $h }}h</option>
                                        @endfor
                                    </select>
                                    <select class="form-control" id="pickup-time-minute" onchange="handlePickupTimeChange()">
                                        <option value="00" {{ ($itemsWithStock->first()?->pickup_time && strpos($itemsWithStock->first()->pickup_time, ':00') !== false) ? 'selected' : '' }}>00p</option>
                                        <option value="15" {{ ($itemsWithStock->first()?->pickup_time && strpos($itemsWithStock->first()->pickup_time, ':15') !== false) ? 'selected' : '' }}>15p</option>
                                        <option value="30" {{ ($itemsWithStock->first()?->pickup_time && strpos($itemsWithStock->first()->pickup_time, ':30') !== false) ? 'selected' : '' }}>30p</option>
                                        <option value="45" {{ ($itemsWithStock->first()?->pickup_time && strpos($itemsWithStock->first()->pickup_time, ':45') !== false) ? 'selected' : '' }}>45p</option>
                                    </select>
                                </div>
                                <input type="hidden" id="pickup-time-hidden" value="{{ $itemsWithStock->first()?->pickup_time ?? '' }}">
                            </div>
                        </div>
                        <div style="font-weight: 700; margin-bottom: 8px;">Quy định mượn trả</div>
                        <ul style="margin: 0 0 12px 18px; color: var(--reserve-muted); font-size: 12px; line-height: 1.6;">
                            <li>Giờ nhận sách: {{ config('library.open_hour', '08:00') }} - {{ config('library.close_hour', '20:00') }}.</li>
                            <li>Thời gian mượn: {{ config('library.borrow_min_days', 1) }} - {{ config('library.borrow_max_days', 14) }} ngày.</li>
                            <li>Số lượng: tối thiểu {{ config('library.borrow_min_books', 1) }} cuốn, tối đa {{ config('library.borrow_max_books', 5) }} cuốn/đơn.</li>
                            <li>Trả đúng hạn, giữ sách nguyên vẹn để được hoàn cọc đầy đủ.</li>
                        </ul>
                        <label style="display: flex; gap: 10px; align-items: center; font-size: 12px; color: var(--reserve-text);">
                            <input type="checkbox" id="agree-reservation-rules" class="form-check-input" style="margin-top: 2px;">
                            Tôi đã đọc và hiểu quy định mượn trả
                        </label>
                    </div>

                    <form id="reservation-submit-form"
                          method="POST"
                          action="{{ route('reservation-cart.submit') }}"
                          onsubmit="return validateCartBeforeSubmit()">
                        @csrf
                        <!-- DEBUG -->
                        <input type="hidden" name="pickup_time" id="pickup-time-form" value="{{ $itemsWithStock->first()?->pickup_time ?? '' }}">
                        <input type="hidden" name="debug_pickup_time" value="{{ $itemsWithStock->first()?->pickup_time ?? '' }}">
                        <button class="btn btn-primary reservation-submit-btn" type="submit">
                            Gửi yêu cầu đặt trước <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
@endif

</div>

@include('components.footer')

<script>
function formatCurrency(v){
    return new Intl.NumberFormat('vi-VN').format(v) + '₫';
}

function parseCurrency(value){
    if(typeof value === 'number'){
        return value;
    }

    return Number(String(value || '').replace(/[^\d]/g, '')) || 0;
}

function getReservationItemCard(itemId){
    return document.querySelector(`.reservation-item[data-item-id="${itemId}"]`);
}

function getReservationItemCheckboxes(){
    return Array.from(document.querySelectorAll('.reservation-item-checkbox'));
}

function getSelectedReservationCheckboxes(){
    return getReservationItemCheckboxes().filter((checkbox) => checkbox.checked);
}

function updateReservationItemVisualState(){
    getReservationItemCheckboxes().forEach((checkbox) => {
        const itemCard = checkbox.closest('.reservation-item');
        if(!itemCard){
            return;
        }

        itemCard.classList.toggle('is-unselected', !checkbox.checked);
    });
}

function syncReservationSelectAllState(){
    const selectAllCheckbox = document.getElementById('reservation-select-all');
    const itemCheckboxes = getReservationItemCheckboxes();

    if(!selectAllCheckbox || itemCheckboxes.length === 0){
        return;
    }

    const checkedCount = getSelectedReservationCheckboxes().length;
    selectAllCheckbox.checked = checkedCount === itemCheckboxes.length;
    selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < itemCheckboxes.length;
}

function recalculateReservationSummary(){
    let totalPrice = 0;
    let totalBooks = 0;

    getSelectedReservationCheckboxes().forEach((checkbox) => {
        const itemCard = checkbox.closest('.reservation-item');
        if(!itemCard){
            return;
        }

        const itemId = itemCard.dataset.itemId;
        const quantity = Number(itemCard.dataset.quantity || 1);

        // Lấy dailyFee từ data attribute
        const feeBox = document.querySelector(`.reservation-fee-breakdown[data-item-id="${itemId}"]`);
        let dailyFee = 5000;
        if(feeBox && feeBox.dataset.dailyFee){
            dailyFee = Number(feeBox.dataset.dailyFee) || 5000;
        }
        // Backup: lấy từ itemCard nếu feeBox không có
        if(!dailyFee || dailyFee === 5000){
            const itemDailyFee = itemCard.dataset.dailyFee;
            if(itemDailyFee){
                dailyFee = Number(itemDailyFee) || 5000;
            }
        }

        // Lấy ngày từ input
        const pickupInput = document.querySelector(`.pickup-date[data-item-id="${itemId}"]`);
        const returnInput = document.querySelector(`.return-date[data-item-id="${itemId}"]`);

        let days = 0;
        if(pickupInput && returnInput && pickupInput.value && returnInput.value){
            const pickup = parseDateString(pickupInput.value);
            const ret = parseDateString(returnInput.value);
            if(pickup && ret){
                // Mượn và trả cùng ngày = 1 ngày, mượn hôm nay trả ngày mai = 2 ngày
                const diffTime = ret.getTime() - pickup.getTime();
                days = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
            }
        }

        const itemTotal = days * dailyFee * quantity;
        totalPrice += itemTotal;
        totalBooks += quantity;
    });

    const totalPriceEl = document.getElementById('total-price');
    const selectedBooksEl = document.getElementById('selected-books-count');

    if(totalPriceEl){
        totalPriceEl.textContent = formatCurrency(totalPrice);
    }

    if(selectedBooksEl){
        selectedBooksEl.textContent = totalBooks;
    }
}

function syncSplitButtonVisibility(itemId, quantity){
    const splitForm = document.querySelector(`.reservation-split-form[data-item-id="${itemId}"]`);
    if(!splitForm){
        return;
    }

    splitForm.classList.toggle('is-hidden', Number(quantity || 0) <= 1);
}

function handleReservationSelectionChange(){
    updateReservationItemVisualState();
    syncReservationSelectAllState();
    recalculateReservationSummary();
}

function toggleSelectAllReservationItems(source){
    getReservationItemCheckboxes().forEach((checkbox) => {
        checkbox.checked = source.checked;
    });

    handleReservationSelectionChange();
}

function parseDateString(value){
    if(!value){
        return null;
    }

    const parts = value.split('-').map(Number);
    if(parts.length !== 3 || parts.some(Number.isNaN)){
        return null;
    }

    return new Date(parts[0], parts[1] - 1, parts[2]);
}

function nextDateString(value){
    const date = parseDateString(value);
    if(!date){
        return value;
    }

    date.setDate(date.getDate() + 1);

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function ensureDateStatusEl(){
    let statusMsg = document.getElementById('date-update-status');
    if(statusMsg) return statusMsg;

    statusMsg = document.createElement('div');
    statusMsg.id = 'date-update-status';
    statusMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#22c55e;color:white;padding:10px 20px;border-radius:5px;z-index:9999;display:none;max-width:320px;';
    document.body.appendChild(statusMsg);
    return statusMsg;
}

// Kiểm tra ngày lấy / ngày trả hợp lệ ở phía client
function validateReservationDates(pickup, ret, showAlert = true){
    if(!pickup || !ret){
        if(showAlert){
            alert('Vui lòng chọn đầy đủ ngày lấy và ngày trả.');
        }
        return false;
    }

    const today = new Date();
    today.setHours(0,0,0,0);

    const pickupDate = parseDateString(pickup);
    const returnDate = parseDateString(ret);

    if(!pickupDate || !returnDate){
        if(showAlert){
            alert('Ngày không đúng định dạng. Vui lòng chọn lại.');
        }
        return false;
    }

    if(pickupDate < today){
        if(showAlert){
            alert('Ngày lấy sách không được ở quá khứ.');
        }
        return false;
    }

    if(returnDate <= pickupDate){
        if(showAlert){
            alert('Ngày trả sách phải sau ngày lấy sách.');
        }
        return false;
    }

    const diffTime = returnDate.getTime() - pickupDate.getTime();
    const diffDays = Math.max(1, Math.round(diffTime / (1000 * 60 * 60 * 24)));
    const minDays = Number('{{ config('library.borrow_min_days', 1) }}');
    const maxDays = Number('{{ config('library.borrow_max_days', 14) }}');

    if(diffDays < minDays || diffDays > maxDays){
        if(showAlert){
            alert(`Thời gian mượn phải từ ${minDays} đến ${maxDays} ngày.`);
        }
        return false;
    }

    return true;
}

function handleItemDateChange(input){
    const itemId = input.dataset.itemId;
    const pickupInput = document.querySelector(`.pickup-date[data-item-id="${itemId}"]`);
    const returnInput = document.querySelector(`.return-date[data-item-id="${itemId}"]`);
    const maxDays = Number('{{ config('library.borrow_max_days', 14) }}');

    // Nếu không tìm thấy input, thoát
    if(!pickupInput || !returnInput){
        return;
    }

    // Cập nhật min/max cho ngày trả khi ngày lấy thay đổi
    if(input.classList.contains('pickup-date') && pickupInput.value){
        returnInput.min = nextDateString(pickupInput.value);

        const pickupDate = parseDateString(pickupInput.value);
        if(pickupDate){
            const maxReturn = new Date(pickupDate);
            maxReturn.setDate(maxReturn.getDate() + maxDays);
            const year = maxReturn.getFullYear();
            const month = String(maxReturn.getMonth() + 1).padStart(2, '0');
            const day = String(maxReturn.getDate()).padStart(2, '0');
            returnInput.max = `${year}-${month}-${day}`;
        }
    }

    // Kiểm tra ngày trả có hợp lệ không
    if(returnInput.value && returnInput.value < returnInput.min){
        returnInput.value = '';
    }

    // Lấy giá trị ngày
    const pickup = pickupInput.value;
    const ret = returnInput.value;

    // Cập nhật hidden inputs cho form submit (luôn luôn cập nhật)
    const pickupHidden = document.querySelector(`input[name="items[${itemId}][pickup_date]"]`);
    const returnHidden = document.querySelector(`input[name="items[${itemId}][return_date]"]`);
    if(pickupHidden) pickupHidden.value = pickup;
    if(returnHidden) returnHidden.value = ret;

    // Nếu đã chọn đủ 2 ngày thì validate
    if(pickup && ret){
        if(!validateReservationDates(pickup, ret, true)){
            input.value = '';
            return;
        }
    }

    // Luôn gọi updateItemPriceDisplay để cập nhật UI khi ngày thay đổi
    updateItemPriceDisplay(itemId);
}

// Tính toán và cập nhật giá tiền tại FE
function updateItemPriceDisplay(itemId){
    const itemCard = document.querySelector(`.reservation-item[data-item-id="${itemId}"]`);
    if(!itemCard) return;

    const pickupInput = document.querySelector(`.pickup-date[data-item-id="${itemId}"]`);
    const returnInput = document.querySelector(`.return-date[data-item-id="${itemId}"]`);
    const quantityEl = itemCard.querySelector('.reservation-quantity-value');
    const feeBox = document.querySelector(`.reservation-fee-breakdown[data-item-id="${itemId}"]`);

    // Lấy dailyFee từ data attribute, nếu không có thì mặc định 5000
    let dailyFee = 5000;
    if(feeBox && feeBox.dataset.dailyFee){
        dailyFee = Number(feeBox.dataset.dailyFee) || 5000;
    }
    // Backup: lấy từ itemCard nếu feeBox không có
    if(!dailyFee || dailyFee === 5000){
        const itemDailyFee = itemCard.dataset.dailyFee;
        if(itemDailyFee){
            dailyFee = Number(itemDailyFee) || 5000;
        }
    }
    const quantity = quantityEl ? Number(quantityEl.textContent || 1) : 1;

    let days = 0;
    if(pickupInput && returnInput && pickupInput.value && returnInput.value){
        const pickup = parseDateString(pickupInput.value);
        const ret = parseDateString(returnInput.value);
        if(pickup && ret){
            // Mượn + trả cùng ngày = 1 ngày, mượn hôm nay trả ngày mai = 2 ngày
            const diffTime = ret.getTime() - pickup.getTime();
            days = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
        }
    }

    const itemTotal = days * dailyFee * quantity;

    // Cập nhật số ngày
    const daysEl = document.querySelector(`.days-display[data-item-id="${itemId}"]`);
    if(daysEl){
        daysEl.textContent = days;
    }

    // Cập nhật tiền
    const itemPriceEl = document.querySelector(`.item-price[data-item-id="${itemId}"]`);
    if(itemPriceEl){
        itemPriceEl.textContent = formatCurrency(itemTotal);
    }

    // Cập nhật dataset
    itemCard.dataset.itemTotal = itemTotal;

    // Cập nhật công thức - sử dụng lại feeBox đã lấy ở trên
    if(feeBox){
        const feeText = feeBox.querySelector(`.fee-breakdown-text[data-item-id="${itemId}"]`);
        const feeTotal = feeBox.querySelector(`.fee-breakdown-total[data-item-id="${itemId}"]`);

        if(feeText){
            feeText.textContent = `${days} ngày × ${formatCurrency(dailyFee)}/ngày × ${quantity} cuốn`;
        }
        if(feeTotal){
            feeTotal.innerHTML = `= <strong>${formatCurrency(itemTotal)}</strong>`;
        }
    }

    // Cập nhật tổng tiền
    recalculateReservationSummary();
}

function changeReservationQuantity(itemId, delta){
    const input = document.getElementById(`reservation-quantity-${itemId}`);
    if(!input){
        return;
    }

    const availableStock = parseInt(input.dataset.availableStock || input.max || 999, 10);
    const previousValue = Math.max(1, parseInt(input.value || '1', 10));
    const nextValue = Math.max(1, Math.min(availableStock, previousValue + delta));
    input.value = nextValue;
    updateReservationQuantityDisplay(itemId, nextValue);
}

function updateReservationQuantityDisplay(itemId, quantity){
    const input = document.getElementById(`reservation-quantity-${itemId}`);
    const itemCard = getReservationItemCard(itemId);

    if(!input || !itemCard){
        return;
    }

    const availableStock = parseInt(input.dataset.availableStock || input.max || 999, 10);
    const qty = Math.max(1, Math.min(availableStock, parseInt(input.value || '1', 10)));
    input.value = qty;
    itemCard.dataset.quantity = qty;

    // Cập nhật label
    const quantityLabel = itemCard.querySelector('.reservation-quantity-value');
    if(quantityLabel){
        quantityLabel.textContent = qty;
    }

    // Gửi API để lưu số lượng xuống database
    fetch(`/reservation-cart/update-quantity/${itemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ quantity: qty })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success){
            // Cập nhật lại giá tiền
            updateItemPriceDisplay(itemId);
        }
    })
    .catch(error => {
        console.error('Error updating quantity:', error);
    });
}

function updateReservationQuantityInput(itemId, previousValue = null){
    updateReservationQuantityDisplay(itemId);
}

function handlePickupTimeChange(){
    const hourSelect = document.getElementById('pickup-time-hour');
    const minuteSelect = document.getElementById('pickup-time-minute');
    const hiddenInput = document.getElementById('pickup-time-hidden');
    const formInput = document.getElementById('pickup-time-form');

    const hour = hourSelect.value;
    const minute = minuteSelect.value;
    const timeStr = hour + ':' + minute;

    // Cập nhật cả 2 hidden inputs
    if(hiddenInput){
        hiddenInput.value = timeStr;
    }
    if(formInput){
        formInput.value = timeStr;
    }
    // Chỉ cập nhật hidden input, không gọi API
}

// Không cần hàm này nữa - không gọi API khi nhập

function handleGlobalPickupTimeChange(input){
    const pickupTime = input.value;
    const hiddenInput = document.getElementById('pickup-time-hidden');

    // Validate giờ trong khoảng cho phép (8h - 20h)
    if(pickupTime){
        const openHour = "{{ config('library.open_hour', '08:00') }}";
        const closeHour = "{{ config('library.close_hour', '20:00') }}";
        if(pickupTime < openHour || pickupTime > closeHour){
            alert(`Giờ nhận sách phải trong khoảng ${openHour} - ${closeHour}`);
            input.value = '';
            if(hiddenInput) hiddenInput.value = '';
            return;
        }
    }

    if(hiddenInput){
        hiddenInput.value = pickupTime;
    }

    if(!pickupTime){
        return;
    }

    const pickupInputs = document.querySelectorAll('.pickup-date');
    pickupInputs.forEach(pickupInput => {
        const itemId = pickupInput.dataset.itemId;
        const returnInput = document.querySelector(`.return-date[data-item-id="${itemId}"]`);
        const pickup = pickupInput.value;
        const ret = returnInput ? returnInput.value : '';

        if(!pickup || !ret){
            return;
        }

        if(!validateReservationDates(pickup, ret, false)){
            return;
        }

        updateReservationItemDates(itemId, pickup, ret, pickupTime, pickupInput);
    });
}

function validateCartBeforeSubmit(){
    const selectedCheckboxes = getSelectedReservationCheckboxes();

    if(selectedCheckboxes.length === 0){
        alert('Vui lòng chọn ít nhất 1 cuốn sách để đặt trước.');
        return false;
    }

    // Lấy giờ từ dropdown
    const hourSelect = document.getElementById('pickup-time-hour');
    const minuteSelect = document.getElementById('pickup-time-minute');

    if(!hourSelect || !minuteSelect || !hourSelect.value || !minuteSelect.value){
        alert('Vui lòng chọn giờ lấy cho đơn đặt trước.');
        return false;
    }

    // Validate giờ trong khoảng cho phép (8h - 20h)
    const hour = parseInt(hourSelect.value);
    if(hour < 8 || hour > 20){
        alert('Giờ nhận sách phải trong khoảng 8h - 20h');
        return false;
    }

    const pickupTime = hourSelect.value + ':' + minuteSelect.value;

    // Cập nhật hidden input trong form
    const hiddenInput = document.getElementById('pickup-time-form');
    if(hiddenInput){
        hiddenInput.value = pickupTime;
    }

    // Kiểm tra ngày
    for(let i = 0; i < selectedCheckboxes.length; i++){
        const itemId = selectedCheckboxes[i].value;
        const pickupInput = document.querySelector(`.pickup-date[data-item-id="${itemId}"]`);
        const retInput = document.querySelector(`.return-date[data-item-id="${itemId}"]`);
        const pickup = pickupInput ? pickupInput.value : '';
        const ret = retInput ? retInput.value : '';

        if(!pickup || !ret){
            alert('Vui lòng chọn đầy đủ ngày lấy và ngày trả cho các sách đã chọn.');
            return false;
        }
    }

    // Check agreement
    const agreed = document.getElementById('agree-reservation-rules');
    if(!agreed || !agreed.checked){
        alert('Vui lòng tick "Tôi đã đọc và hiểu quy định mượn trả" trước khi gửi yêu cầu.');
        return false;
    }

    // Submit form - sẽ gọi API
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    handleReservationSelectionChange();
    getReservationItemCheckboxes().forEach((checkbox) => {
        const itemCard = checkbox.closest('.reservation-item');
        if(!itemCard){
            return;
        }

        syncSplitButtonVisibility(checkbox.value, Number(itemCard.dataset.quantity || 1));
    });
});
</script>
@endsection
