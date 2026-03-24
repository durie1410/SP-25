@extends('account._layout')

@section('title', 'Lịch sử đặt trước')
@section('breadcrumb', 'Lịch sử đặt trước')

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
    }

    body {
        background:
            radial-gradient(circle at top left, rgba(15, 118, 110, 0.08), transparent 26%),
            radial-gradient(circle at top right, rgba(234, 88, 12, 0.08), transparent 24%),
            linear-gradient(180deg, #f7faf7 0%, #eef3ea 100%);
    }

    .history-page {
        max-width: 1100px;
        margin: 22px auto 40px;
        padding: 0 18px 40px;
    }

    .page-header {
        margin-bottom: 28px;
        padding: 24px 28px;
        border: 1px solid rgba(219, 228, 220, 0.9);
        border-radius: 28px;
        background: linear-gradient(135deg, rgba(255, 253, 247, 0.96), rgba(240, 253, 250, 0.92));
        box-shadow: 0 22px 50px rgba(15, 23, 42, 0.08);
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--reserve-text);
        margin: 0 0 8px 0;
    }

    .page-subtitle {
        color: var(--reserve-muted);
        margin: 0;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--reserve-primary);
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 20px;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .history-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .history-item {
        display: flex;
        padding: 20px;
        border-bottom: 1px solid var(--reserve-border);
        gap: 20px;
    }

    .history-item:last-child {
        border-bottom: none;
    }

    .book-cover {
        width: 80px;
        height: 110px;
        object-fit: cover;
        border-radius: 8px;
        background: #f5f5f5;
    }

    .book-info {
        flex: 1;
    }

    .book-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--reserve-text);
        margin: 0 0 4px 0;
    }

    .book-author {
        color: var(--reserve-muted);
        font-size: 14px;
        margin-bottom: 12px;
    }

    .reservation-code {
        font-family: monospace;
        background: var(--reserve-primary-soft);
        color: var(--reserve-primary);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-ready {
        background: #d1fae5;
        color: #065f46;
    }

    .status-fulfilled {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-overdue {
        background: #fee2e2;
        color: #991b1b;
    }

    .schedule-info {
        display: flex;
        gap: 24px;
        margin-top: 12px;
        font-size: 14px;
    }

    .schedule-item {
        display: flex;
        flex-direction: column;
    }

    .schedule-label {
        color: var(--reserve-muted);
        font-size: 12px;
    }

    .schedule-value {
        font-weight: 600;
        color: var(--reserve-text);
    }

    .fee-info {
        text-align: right;
        min-width: 120px;
    }

    .fee-label {
        font-size: 12px;
        color: var(--reserve-muted);
    }

    .fee-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--reserve-accent);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--reserve-muted);
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        gap: 8px;
        list-style: none;
        margin: 0;
    }

    .pagination li {
        list-style: none;
    }

    .pagination a, .pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
    }

    .pagination a {
        background: white;
        color: var(--reserve-text);
        border: 1px solid var(--reserve-border);
    }

    .pagination a:hover {
        background: var(--reserve-primary-soft);
    }

    .pagination span {
        background: var(--reserve-primary);
        color: white;
    }

    .approved-notice {
        background: #dbeafe;
        border: 1px solid #93c5fd;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 12px;
        font-size: 14px;
        color: #1e40af;
    }

    .ready-actions {
        margin-top: 10px;
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .ready-btn {
        border: 1px solid transparent;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .ready-btn-confirm {
        background: linear-gradient(135deg, #059669, #10b981);
        color: #fff;
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.25);
    }

    .ready-btn-confirm:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.32);
    }

    .ready-btn-cancel {
        background: #fff;
        color: #dc2626;
        border-color: #fca5a5;
    }

    .ready-btn-cancel:hover {
        background: #fef2f2;
        border-color: #f87171;
    }
</style>
@endpush

@section('content')
<div class="history-page">
    <a href="{{ route('reservation-cart.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Quay lại giỏ đặt trước
    </a>

    <div class="page-header">
        <h1 class="page-title">📋 Lịch sử đặt trước</h1>
        <p class="page-subtitle">Theo dõi các yêu cầu đặt trước của bạn</p>
    </div>


                            <div class="schedule-item">
                                <span class="schedule-label">Ngày lấy</span>
                                <span class="schedule-value">{{ $first->pickup_display }}</span>
                            </div>
                            <div class="schedule-item">
                                <span class="schedule-label">Hạn nhận</span>
                                <span class="schedule-value {{ $groupOverdue ? 'text-danger' : '' }}">
                                    {{ $first->pickup_deadline_display }} (sau 2 giờ)
                                </span>
                            </div>
                            <div class="schedule-item">
                                <span class="schedule-label">Ngày trả</span>
                                <span class="schedule-value">{{ $first->return_date ? \Carbon\Carbon::parse($first->return_date)->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                        </div>

                        @if($groupOverdue)
                            <div style="margin-top: 8px; color: #991b1b; font-weight: 700; font-size: 13px;">
                                ⛔ Đã quá hạn nhận sách
                            </div>
                        @endif
                    </div>
                    <div class="fee-info">
                        <div class="fee-label">Tổng tiền</div>
                        <div class="fee-value">{{ number_format($groupTotal, 0, ',', '.') }}đ</div>

                        @if($groupStatus === 'ready' && !$groupHasOverdue && !empty($first->reservation_code))
                            <div class="ready-actions">
                                @if($canConfirmReady)
                                    <form action="{{ route('reservation-cart.history.confirm-ready', $first->reservation_code) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="ready-btn ready-btn-confirm">
                                            <i class="fas fa-check-circle"></i> Xác nhận sẽ đến nhận
                                        </button>
                                    </form>
                                @elseif($groupOverdue)
                                    <button type="button" class="ready-btn ready-btn-confirm" disabled style="opacity: .45; cursor: not-allowed;">
                                        <i class="fas fa-clock"></i> Đã quá hạn nhận sách
                                    </button>
                                @else
                                    <span class="status-badge status-ready">Đã xác nhận nhận sách</span>
                                @endif

                                <form action="{{ route('reservation-cart.history.cancel-ready', $first->reservation_code) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn không đến nhận đơn này?');">
                                    @csrf
                                    <button type="submit" class="ready-btn ready-btn-cancel">
                                        <i class="fas fa-times-circle"></i> Không nhận nữa
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </summary>

                <div style="margin-top:14px; display:grid; gap:12px;">
                    @foreach($group as $reservation)
                        <div style="display:flex; gap:14px; border:1px solid var(--reserve-border); border-radius:10px; padding:12px; background:#fff;">
                            <img src="{{ $reservation->book->image_url ?? asset('images/default-book.png') }}"
                                 alt="{{ $reservation->book->ten_sach ?? 'Sách' }}"
                                 class="book-cover">
                            <div class="book-info">
                                <h3 class="book-title" style="font-size:16px;">{{ $reservation->book->ten_sach ?? 'N/A' }}</h3>
                                <p class="book-author">Tác giả: {{ $reservation->book->tac_gia ?? 'Không rõ' }}</p>
                                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:8px;">
                                    @php
                                        $itemIsOverdue = in_array($reservation->status, ['pending', 'ready'], true)
                                            && (
                                                ($reservation->pickup_date && $reservation->pickup_date->lt(now()->startOfDay()))
                                                || ($reservation->status === 'ready' && $reservation->ready_at && $reservation->ready_at->lt(now()->subHours(2)))
                                            );
                                        $itemStatus = $itemIsOverdue ? 'overdue' : $reservation->status;
                                        $itemStatusLabel = $itemIsOverdue ? 'Quá hạn' : $reservation->getStatusLabel();
                                    @endphp
                                    <span class="status-badge status-{{ $itemStatus }}">{{ $itemStatusLabel }}</span>
                                    @if($reservation->inventory_id)
                                        <span class="reservation-code">Bản sao #{{ $reservation->inventory_id }}</span>
                                    @endif
                                    <span class="fee-label">{{ number_format($reservation->total_fee ?? 0, 0, ',', '.') }}đ</span>
                                </div>


            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <p>Bạn chưa có yêu cầu đặt trước nào.</p>
                <a href="{{ route('books.public') }}" class="btn btn-primary" style="margin-top: 16px;">
                    Khám phá sách
                </a>
            </div>
        @endforelse

        @if($reservations->hasPages())
            <div class="pagination">
                {{ $reservations->links('vendor.pagination.default') }}
            </div>
        @endif
    </div>
</div>
@endsection
