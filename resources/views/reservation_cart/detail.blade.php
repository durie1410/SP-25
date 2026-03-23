@extends('layouts.app')

@section('title', 'Chi tiết đặt trước')

@push('styles')
<style>
    :root {
        --reserve-primary: #0f766e;
        --reserve-primary-soft: #ccfbf1;
        --reserve-accent: #ea580c;
        --reserve-bg: #f5f7f2;
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

    .detail-page {
        max-width: 800px;
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
        font-size: 24px;
        font-weight: 700;
        color: var(--reserve-text);
        margin: 0 0 4px 0;
    }

    .page-subtitle {
        color: var(--reserve-muted);
        margin: 0;
        font-size: 14px;
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

    .detail-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .detail-section {
        padding: 24px;
        border-bottom: 1px solid var(--reserve-border);
    }

    .detail-section:last-child {
        border-bottom: none;
    }

    .section-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--reserve-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 16px 0;
    }

    .book-display {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }

    .book-cover {
        width: 100px;
        height: 140px;
        object-fit: cover;
        border-radius: 10px;
        background: #f5f5f5;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .book-info {
        flex: 1;
    }

    .book-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--reserve-text);
        margin: 0 0 6px 0;
    }

    .book-author {
        color: var(--reserve-muted);
        font-size: 14px;
        margin: 0 0 12px 0;
    }

    .reservation-code-box {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--reserve-primary-soft);
        color: var(--reserve-primary);
        padding: 8px 14px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 14px;
        font-weight: 700;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-ready { background: #d1fae5; color: #065f46; }
    .status-fulfilled { background: #dbeafe; color: #1e40af; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .status-overdue { background: #fee2e2; color: #991b1b; }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .info-item.full-width {
        grid-column: 1 / -1;
    }

    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--reserve-muted);
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 600;
        color: var(--reserve-text);
    }

    .info-value.highlight {
        color: var(--reserve-primary);
    }

    .fee-display {
        text-align: center;
        padding: 20px;
        background: linear-gradient(135deg, rgba(234, 88, 12, 0.05), rgba(234, 88, 12, 0.02));
        border-radius: 12px;
    }

    .fee-label {
        font-size: 13px;
        color: var(--reserve-muted);
        margin-bottom: 6px;
    }

    .fee-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--reserve-accent);
    }

    .action-section {
        padding: 24px;
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-cancel {
        background: #fee2e2;
        color: #991b1b;
        border: none;
        padding: 12px 28px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s, transform 0.15s;
    }

    .btn-cancel:hover {
        background: #fecaca;
        transform: translateY(-1px);
    }

    .btn-back {
        background: white;
        color: var(--reserve-text);
        border: 1px solid var(--reserve-border);
        padding: 12px 28px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: background 0.2s, transform 0.15s;
    }

    .btn-back:hover {
        background: #f8faf9;
        transform: translateY(-1px);
    }

    .notice-box {
        background: #dbeafe;
        border: 1px solid #93c5fd;
        border-radius: 10px;
        padding: 14px 16px;
        font-size: 14px;
        color: #1e40af;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 8px;
    }

    .notice-box.warning {
        background: #fef3c7;
        border-color: #fcd34d;
        color: #92400e;
    }

    .notice-box.danger {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
    }

    .admin-note-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
        font-size: 14px;
        color: #475569;
        margin-top: 8px;
    }

    .admin-note-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--reserve-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
</style>
@endpush

@section('content')
<div class="detail-page">
    <a href="{{ route('reservation-cart.history') }}" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Quay lại lịch sử đặt trước
    </a>

    <div class="detail-card">
        {{-- Thông tin sách --}}
        <div class="detail-section">
            <p class="section-title">Sách đặt trước</p>
            <div class="book-display">
                <img src="{{ $reservation->book && $reservation->book->hinh_anh ? asset('storage/' . $reservation->book->hinh_anh) : 'https://via.placeholder.com/100x140?text=No+Image' }}"
                     alt="{{ $reservation->book->ten_sach ?? 'Sách' }}"
                     class="book-cover">
                <div class="book-info">
                    <h2 class="book-title">{{ $reservation->book->ten_sach ?? 'N/A' }}</h2>
                    <p class="book-author">Tác giả: {{ $reservation->book->tac_gia ?? 'Không rõ' }}</p>
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <div class="reservation-code-box">
                            <i class="fas fa-ticket-alt"></i>
                            {{ $reservation->reservation_code ?? 'N/A' }}
                        </div>
                        <span class="status-badge status-{{ $reservation->status }}">
                            {{ $reservation->getStatusLabel() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Thông báo trạng thái --}}
        @if($reservation->status === 'ready')
            <div class="detail-section">
                <div class="notice-box">
                    <i class="fas fa-bell" style="margin-top: 2px;"></i>
                    <div>
                        <strong>Sách đã sẵn sàng!</strong><br>
                        Vui lòng đến thư viện để nhận sách theo ngày và giờ đã hẹn. Nếu không nhận trong vòng <strong>2 giờ</strong> kể từ giờ hẹn, yêu cầu sẽ tự động bị hủy.
                    </div>
                </div>
            </div>
        @elseif($reservation->status === 'fulfilled')
            <div class="detail-section">
                <div class="notice-box" style="background: #d1fae5; border-color: #6ee7b7; color: #065f46;">
                    <i class="fas fa-check-circle" style="margin-top: 2px;"></i>
                    <div>
                        <strong>Đã hoàn thành!</strong><br>
                        Sách đã được nhận thành công. Cảm ơn bạn đã sử dụng dịch vụ của thư viện.
                    </div>
                </div>
            </div>
        @elseif($reservation->status === 'cancelled')
            <div class="detail-section">
                <div class="notice-box danger">
                    <i class="fas fa-times-circle" style="margin-top: 2px;"></i>
                    <div>
                        <strong>Yêu cầu đã bị hủy.</strong>
                        @if($reservation->admin_note)
                            <br>Lý do: {{ $reservation->admin_note }}
                        @endif
                    </div>
                </div>
            </div>
        @elseif($reservation->status === 'overdue')
            <div class="detail-section">
                <div class="notice-box danger">
                    <i class="fas fa-exclamation-triangle" style="margin-top: 2px;"></i>
                    <div>
                        <strong>Yêu cầu đã quá hạn nhận sách.</strong><br>
                        Vui lòng liên hệ thư viện để được hỗ trợ hoặc tạo yêu cầu đặt trước mới.
                    </div>
                </div>
            </div>
        @elseif($reservation->status === 'pending')
            <div class="detail-section">
                <div class="notice-box warning">
                    <i class="fas fa-clock" style="margin-top: 2px;"></i>
                    <div>
                        Đơn đang chờ xử lý. Thư viện sẽ sắp xếp và thông báo khi sách sẵn sàng.
                    </div>
                </div>
            </div>
        @endif

        {{-- Lịch trình --}}
        <div class="detail-section">
            <p class="section-title">Lịch hẹn</p>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Ngày lấy sách</span>
                    <span class="info-value highlight">
                        {{ $reservation->pickup_date ? \Carbon\Carbon::parse($reservation->pickup_date)->format('d/m/Y') : 'Chưa xác định' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Giờ lấy sách</span>
                    <span class="info-value highlight">
                        {{ $reservation->pickup_time ?? '08:00' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Ngày trả sách</span>
                    <span class="info-value">
                        {{ $reservation->return_date ? \Carbon\Carbon::parse($reservation->return_date)->format('d/m/Y') : 'Chưa xác định' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Mã bản sao</span>
                    <span class="info-value">
                        {{ $reservation->inventory_id ? '#' . $reservation->inventory_id : 'Chưa gán' }}
                    </span>
                </div>
                @if($reservation->ready_at)
                    <div class="info-item">
                        <span class="info-label">Thời gian sẵn sàng</span>
                        <span class="info-value">
                            {{ $reservation->ready_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                @endif
                @if($reservation->fulfilled_at)
                    <div class="info-item">
                        <span class="info-label">Thời gian hoàn thành</span>
                        <span class="info-value">
                            {{ $reservation->fulfilled_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Phí --}}
        <div class="detail-section">
            <p class="section-title">Chi phí</p>
            <div class="fee-display">
                <div class="fee-label">Tổng phí đặt trước</div>
                <div class="fee-value">{{ number_format($reservation->total_fee ?? 0, 0, ',', '.') }}đ</div>
            </div>
        </div>

        {{-- Ghi chú từ quản trị --}}
        @if($reservation->admin_note)
            <div class="detail-section">
                <p class="section-title">Ghi chú</p>
                <div class="admin-note-box">
                    <div class="admin-note-label">Từ quản trị viên</div>
                    {{ $reservation->admin_note }}
                </div>
            </div>
        @endif

        {{-- Hành động --}}
        @if(in_array($reservation->status, ['pending', 'ready', 'overdue']))
            <div class="action-section">
                <a href="{{ route('reservation-cart.history') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
                <button type="button" class="btn-cancel" onclick="cancelReservation({{ $reservation->id }})">
                    <i class="fas fa-times"></i>
                    Hủy yêu cầu
                </button>
            </div>
        @else
            <div class="action-section">
                <a href="{{ route('reservation-cart.history') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh sách
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function cancelReservation(reservationId) {
    if (!confirm('Bạn có chắc chắn muốn hủy yêu cầu đặt trước này?')) {
        return;
    }

    fetch(`/reservation-cart/cancel/${reservationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Đã hủy yêu cầu thành công.');
            window.location.href = '{{ route('reservation-cart.history') }}';
        } else {
            alert(data.message || 'Không thể hủy yêu cầu.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Đã xảy ra lỗi. Vui lòng thử lại.');
    });
}
</script>
@endpush
@endsection
