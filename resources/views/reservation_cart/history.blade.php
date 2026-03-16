@extends('layouts.app')

@section('title', 'Lịch sử đặt trước')

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
        padding: 20px;
        gap: 8px;
    }

    .pagination a, .pagination span {
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

    <div class="history-card">
        @forelse($reservations as $reservation)
            <div class="history-item">
                <img src="{{ $reservation->book->anh_dai_dien ?? 'https://via.placeholder.com/80x110' }}"
                     alt="{{ $reservation->book->ten_sach ?? 'Sách' }}"
                     class="book-cover">

                <div class="book-info">
                    <h3 class="book-title">{{ $reservation->book->ten_sach ?? 'N/A' }}</h3>
                    <p class="book-author">Tác giả: {{ $reservation->book->tac_gia ?? 'Không rõ' }}</p>

                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <span class="reservation-code">{{ $reservation->reservation_code }}</span>
                        <span class="status-badge status-{{ $reservation->status }}">
                            {{ $reservation->getStatusLabel() }}
                        </span>
                    </div>

                    @if(in_array($reservation->status, ['ready', 'fulfilled']))
                        <div class="approved-notice">
                            <i class="fas fa-info-circle"></i>
                            Đơn hàng đã được duyệt, không thể chỉnh sửa. Vui lòng đến thư viện để nhận sách.
                        </div>
                    @endif

                    <div class="schedule-info">
                        <div class="schedule-item">
                            <span class="schedule-label">Ngày lấy</span>
                            <span class="schedule-value">
                                {{ $reservation->pickup_date ? \Carbon\Carbon::parse($reservation->pickup_date)->format('d/m/Y') : 'N/A' }}
                                @if($reservation->pickup_time)
                                    lúc {{ $reservation->pickup_time }}
                                @endif
                            </span>
                        </div>
                        <div class="schedule-item">
                            <span class="schedule-label">Ngày trả</span>
                            <span class="schedule-value">
                                {{ $reservation->return_date ? \Carbon\Carbon::parse($reservation->return_date)->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        @if($reservation->inventory)
                            <div class="schedule-item">
                                <span class="schedule-label">Mã bản sao</span>
                                <span class="schedule-value">#{{ $reservation->inventory_id }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="fee-info">
                    <div class="fee-label">Tiền cọc</div>
                    <div class="fee-value">{{ number_format($reservation->total_fee ?? 0, 0, ',', '.') }}đ</div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <p>Bạn chưa có yêu cầu đặt trước nào.</p>
                <a href="{{ route('books.index') }}" class="btn btn-primary" style="margin-top: 16px;">
                    Khám phá sách
                </a>
            </div>
        @endforelse

        @if($reservations->hasPages())
            <div class="pagination">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
