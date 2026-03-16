@extends('layouts.admin')

@section('title', 'Quản lý đặt trước - Admin')

@section('content')
<div class="page-header reservation-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-bookmark"></i>
            Quản lý đặt trước
        </h1>
        <p class="page-subtitle">Duyệt yêu cầu đặt trước, chuẩn bị sách và xác nhận khách đến nhận</p>
    </div>
</div>

<div class="card filter-card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Lọc</h3>
    </div>
    <form method="GET" class="filter-form">
        <div class="filter-field">
            <select name="status" class="form-control">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đang chờ</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Quá hạn</option>
                <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Đã sẵn sàng</option>
                <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Đã hoàn thành</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
        </div>
        <div class="filter-field">
            <select name="inventory_status" class="form-control">
                <option value="">-- Bản sao --</option>
                <option value="assigned" {{ request('inventory_status') === 'assigned' ? 'selected' : '' }}>Đã gán bản sao</option>
                <option value="unassigned" {{ request('inventory_status') === 'unassigned' ? 'selected' : '' }}>Chưa gán bản sao</option>
            </select>
        </div>
        <div class="filter-field">
            <select name="pickup_window" class="form-control">
                <option value="">-- Ngày lấy --</option>
                <option value="today" {{ request('pickup_window') === 'today' ? 'selected' : '' }}>Lấy hôm nay</option>
                <option value="upcoming" {{ request('pickup_window') === 'upcoming' ? 'selected' : '' }}>Sắp tới</option>
                <option value="past" {{ request('pickup_window') === 'past' ? 'selected' : '' }}>Đã qua</option>
            </select>
        </div>
        <div class="filter-field">
            <input type="text" name="reader_keyword" class="form-control" placeholder="Độc giả: tên / thẻ / email" value="{{ request('reader_keyword') }}">
        </div>
        <div class="filter-field">
            <input type="text" name="reservation_code" class="form-control" placeholder="Mã đơn (RSV...)" value="{{ request('reservation_code') }}">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Lọc
            </button>
            <a href="{{ route('admin.inventory-reservations.index') }}" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Reset
            </a>
        </div>
    </form>
</div>

<div class="card reservation-card">
    <div class="card-header reservation-card-header">
        <div>
            <h3 class="card-title"><i class="fas fa-list"></i> Danh sách yêu cầu</h3>
            <div class="text-muted" style="font-size: 13px;">Theo dõi trạng thái từng cuốn sách đặt trước</div>
        </div>
        <span class="badge badge-info">Tổng: {{ $reservations->total() }}</span>
    </div>

    <div class="table-responsive">
        <table class="table reservation-table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Sách</th>
                    <th>Độc giả</th>
                    <th>Lịch hẹn</th>
                    <th>Trạng thái</th>
                    <th>Bản sao</th>
                    <th>Phí</th>
                    <th>Ảnh</th>
                    <th style="width: 210px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $groupedReservations = $reservations->groupBy(function ($reservation) {
                        if (!empty($reservation->reservation_code)) {
                            return $reservation->reservation_code;
                        }

                        $pickup = $reservation->pickup_date ? $reservation->pickup_date->format('Ymd') : 'none';
                        $return = $reservation->return_date ? $reservation->return_date->format('Ymd') : 'none';
                        $time = $reservation->pickup_time ?: 'none';
                        $readerKey = $reservation->reader_id ?? $reservation->user_id ?? 'guest';

                        return "reader-{$readerKey}-{$pickup}-{$return}-{$time}";
                    });
                @endphp

                @forelse($groupedReservations as $groupCode => $group)
                    @php
                        $firstReservation = $group->first();
                        $groupLabel = $firstReservation->reservation_code ?: 'Đơn lẻ';
                        $readerName = $firstReservation->reader->ho_ten ?? ($firstReservation->user->name ?? 'N/A');
                        $readerCard = $firstReservation->reader->so_the_doc_gia ?? '';
                        $groupTotal = $group->sum(fn($item) => (float) ($item->total_fee ?? 0));
                    @endphp
                    <tr class="table-light reservation-group-row">
                        <td colspan="10">
                            <div class="group-row-content">
                                <div class="group-main">
                                    <span class="badge badge-info">{{ $groupLabel }}</span>
                                    <div class="group-reader">
                                        <div class="group-reader-name">{{ $readerName }}</div>
                                        @if($readerCard)
                                            <div class="group-reader-card">Thẻ {{ $readerCard }}</div>
                                        @endif
                                    </div>
                                    <div class="group-meta">{{ $group->count() }} cuốn</div>
                                </div>
                                <div class="group-summary">
                                    <span class="group-total-label">Tổng phí:</span>
                                    <span class="group-total" data-group="{{ $groupCode }}">{{ number_format($groupTotal, 0, ',', '.') }}đ</span>
                                </div>
                                <form method="POST" action="{{ route('admin.inventory-reservations.fulfill-group') }}" class="group-fulfill-form confirm-submit-btn" data-group="{{ $groupCode }}">
                                    @csrf
                                    <input type="hidden" name="reservation_ids" value="{{ $group->pluck('id')->implode(',') }}" class="group-selected-ids">
                                    <button type="submit" class="btn btn-sm btn-primary group-fulfill-btn" data-confirm-message="Fulfill toàn bộ đơn?">
                                        <i class="fas fa-check"></i> Fulfill đơn
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    @foreach($group as $r)
                        @php
                            $proofImages = is_array($r->proof_images)
                                ? $r->proof_images
                                : (is_string($r->proof_images) ? json_decode($r->proof_images, true) : []);
                            $proofImages = is_array($proofImages) ? $proofImages : [];
                            $proofCount = count($proofImages);
                        @endphp
                        <tr class="reservation-row">
                            <td><span class="badge badge-secondary">#{{ $r->id }}</span></td>
                            <td>
                                <div class="book-info">
                                    <div class="book-title">{{ $r->book->ten_sach ?? 'N/A' }}</div>
                                    <div class="book-meta">
                                        <span>BK{{ str_pad((string)($r->book_id ?? 0), 6, '0', STR_PAD_LEFT) }}</span>
                                        <span>{{ $r->book->tac_gia ?? 'Không rõ' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="reader-info">
                                    <div class="reader-name">{{ $r->reader->ho_ten ?? ($r->user->name ?? 'N/A') }}</div>
                                    <div class="reader-meta">{{ $r->reader->so_the_doc_gia ?? 'Khách online' }}</div>
                                </div>
                            </td>
                            <td>
                                @if($r->pickup_date)
                                    <div class="schedule-info">
                                        <div><strong>Lấy:</strong> {{ \Carbon\Carbon::parse($r->pickup_date)->format('d/m/Y') }}</div>
                                        <div><strong>Trả:</strong> {{ \Carbon\Carbon::parse($r->return_date)->format('d/m/Y') }}</div>
                                        @if($r->pickup_time)
                                            <div class="schedule-time">Giờ: {{ $r->pickup_time }}</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $isOverduePickup = in_array($r->status, ['pending', 'ready'], true)
                                        && $r->pickup_date
                                        && $r->pickup_date->lt(now()->startOfDay());

                                    $badgeClass = $isOverduePickup ? 'badge-danger' : match($r->status) {
                                        'pending' => 'badge-warning',
                                        'ready' => 'badge-success',
                                        'fulfilled' => 'badge-info',
                                        'cancelled' => 'badge-danger',
                                        default => 'badge-secondary',
                                    };

                                    $statusLabel = $isOverduePickup ? 'Quá hạn' : $r->getStatusLabel();
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                @if($r->inventory)
                                    <div class="inventory-info">
                                        <span class="badge badge-success">#{{ $r->inventory->id }}</span>
                                        <div class="inventory-meta">{{ $r->inventory->barcode ?? '' }}</div>
                                        <div class="inventory-meta">{{ $r->inventory->status ?? 'N/A' }}</div>
                                    </div>
                                @else
                                    <span class="badge badge-secondary">Chưa gán</span>
                                @endif
                            </td>
                            <td>
                                <div class="fee-amount">{{ number_format($r->total_fee ?? 0, 0, ',', '.') }}đ</div>
                            </td>
                            <td>
                                <div class="proof-info {{ $proofCount ? 'has-proof' : '' }}">
                                    <span>{{ $proofCount }} ảnh</span>
                                    <small>{{ $proofCount ? 'Đã có' : 'Chưa có' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="{{ route('admin.inventory-reservations.proof', $r->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </a>

                                    @if($r->status === 'pending' && !$isOverduePickup)
                                        <form method="POST" action="{{ route('admin.inventory-reservations.ready', $r->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success confirm-submit-btn" data-confirm-message="Xác nhận sách sẵn sàng tại quầy? Hệ thống sẽ tự gán 1 bản copy đang có sẵn và gửi thông báo cho độc giả.">
                                                <i class="fas fa-check"></i> Ready
                                            </button>
                                        </form>
                                    @endif

                                    @if($r->status === 'ready' && !$isOverduePickup)
                                        <form method="POST" action="{{ route('admin.inventory-reservations.fulfill', $r->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary confirm-submit-btn" data-confirm-message="Đánh dấu độc giả đã nhận sách tại quầy?">
                                                <i class="fas fa-hand-holding"></i> Fulfill
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($r->status, ['pending', 'ready'], true))
                                        <form method="POST" action="{{ route('admin.inventory-reservations.cancel', $r->id) }}" style="display:inline;">
                                            @csrf
                                            @if($isOverduePickup)
                                                <input type="hidden" name="mark_overdue" value="1">
                                                <input type="hidden" name="admin_note" value="Quá hạn nhận sách: đã qua ngày lấy nhưng khách chưa đến nhận.">
                                            @endif
                                            <button type="submit" class="btn btn-sm btn-warning confirm-submit-btn" data-confirm-message="{{ $isOverduePickup ? 'Ngày lấy đã qua nhưng khách chưa nhận. Đánh dấu quá hạn cho yêu cầu này?' : 'Hủy yêu cầu đặt trước này?' }}">
                                                <i class="fas fa-{{ $isOverduePickup ? 'clock' : 'times' }}"></i> {{ $isOverduePickup ? 'Quá hạn' : 'Hủy' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center; padding: 40px; color: var(--text-muted);">
                            Chưa có yêu cầu đặt trước nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="padding: 16px 20px;">
        {{ $reservations->appends(request()->query())->links('vendor.pagination.admin') }}
    </div>
</div>
@endsection

@push('styles')
<style>
.reservation-header {
    margin-bottom: 16px;
}

.filter-card {
    border: 1px solid rgba(148, 163, 184, 0.25);
    box-shadow: 0 16px 30px rgba(15, 23, 42, 0.06);
    overflow: visible;
    position: relative;
    z-index: 999;
}

.filter-card:hover {
    transform: none;
}

.main-content {
    position: relative;
    z-index: 0;
}

.filter-form {
    padding: 18px 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 12px;
    align-items: center;
    position: relative;
    z-index: 1000;
}

.filter-field,
.filter-actions {
    position: relative;
    z-index: 1001;
}

.filter-card .form-control,
.filter-card .form-select {
    position: relative;
    z-index: 1002;
    pointer-events: auto;
}

.filter-form select.form-control {
    cursor: pointer;
}

.filter-form select.form-control:focus,
.filter-form select.form-control:active {
    z-index: 1003;
}

.card.reservation-card {
    position: relative;
    z-index: 1;
}

.filter-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.reservation-card {
    border: 1px solid rgba(148, 163, 184, 0.25);
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
}

.reservation-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.reservation-table thead th {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    background: #f8fafc;
}

.reservation-row {
    background: #fff;
    border-bottom: 1px solid #edf2f7;
}

.reservation-row td {
    vertical-align: top;
}

.reservation-group-row td {
    padding: 14px 18px;
}

.group-row-content {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.group-main {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.group-reader-name {
    font-weight: 600;
    color: #1f2937;
}

.group-reader-card {
    font-size: 12px;
    color: #64748b;
}

.group-meta {
    font-size: 12px;
    color: #64748b;
}

.group-summary {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    color: #0f766e;
}

.book-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.book-meta {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 12px;
    color: #64748b;
}

.reader-name {
    font-weight: 600;
    color: #1f2937;
}

.reader-meta {
    font-size: 12px;
    color: #94a3b8;
}

.schedule-info {
    font-size: 13px;
    color: #334155;
    display: grid;
    gap: 3px;
}

.schedule-time {
    font-size: 12px;
    color: #64748b;
}

.inventory-info {
    display: grid;
    gap: 4px;
    font-size: 12px;
    color: #64748b;
}

.inventory-meta {
    font-size: 12px;
    color: #94a3b8;
}

.fee-amount {
    font-weight: 700;
    color: #f97316;
}

.proof-info {
    font-size: 12px;
    color: #94a3b8;
    display: grid;
    gap: 2px;
}

.proof-info.has-proof {
    color: #0f766e;
    font-weight: 600;
}

.action-group {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.action-group .btn {
    white-space: nowrap;
}


@media (max-width: 992px) {
    .reservation-table {
        min-width: 980px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const confirmButtons = document.querySelectorAll('.confirm-submit-btn');

    confirmButtons.forEach(btn => {
        btn.addEventListener('click', function (event) {
            event.preventDefault();

            const button = event.currentTarget;
            const message = button.getAttribute('data-confirm-message') || "Bạn có chắc chắn muốn thực hiện hành động này?";
            const form = button.closest('form');

            if (button.dataset.requiresSelection === '1' && form) {
                const selected = form.querySelector('.group-selected-ids');
                if (!selected || !selected.value) {
                    alert('Vui lòng chọn ít nhất 1 cuốn để Fulfill.');
                    return;
                }
            }

            if (confirm(message)) {
                if (form) form.submit();
            }
        });
    });

});
</script>
@endpush
