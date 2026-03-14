@extends('layouts.admin')

@section('title', 'Quản lý đặt trước - Admin')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-bookmark"></i>
            Quản lý đặt trước
        </h1>
        <p class="page-subtitle">Duyệt yêu cầu đặt trước và thông báo khách đến quầy nhận sách</p>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Lọc</h3>
    </div>
    <form method="GET" style="padding: 16px 20px; display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
        <div style="min-width: 220px; flex: 1;">
            <select name="status" class="form-control">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đang chờ</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Quá hạn</option>
                <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Đã sẵn sàng</option>
                <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Đã hoàn thành</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
        </div>
        <div style="min-width: 200px; flex: 1;">
            <select name="inventory_status" class="form-control">
                <option value="">-- Bản sao --</option>
                <option value="assigned" {{ request('inventory_status') === 'assigned' ? 'selected' : '' }}>Đã gán bản sao</option>
                <option value="unassigned" {{ request('inventory_status') === 'unassigned' ? 'selected' : '' }}>Chưa gán bản sao</option>
            </select>
        </div>
        <div style="min-width: 200px; flex: 1;">
            <select name="pickup_window" class="form-control">
                <option value="">-- Ngày lấy --</option>
                <option value="today" {{ request('pickup_window') === 'today' ? 'selected' : '' }}>Lấy hôm nay</option>
                <option value="upcoming" {{ request('pickup_window') === 'upcoming' ? 'selected' : '' }}>Sắp tới</option>
                <option value="past" {{ request('pickup_window') === 'past' ? 'selected' : '' }}>Đã qua</option>
            </select>
        </div>
        <div style="min-width: 220px; flex: 1;">
            <input type="text" name="reader_keyword" class="form-control" placeholder="Độc giả: tên / thẻ / email" value="{{ request('reader_keyword') }}">
        </div>
        <div style="min-width: 200px; flex: 1;">
            <input type="text" name="reservation_code" class="form-control" placeholder="Mã đơn (RSV...)" value="{{ request('reservation_code') }}">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.inventory-reservations.index') }}" class="btn btn-secondary">
            <i class="fas fa-redo"></i> Reset
        </a>
    </form>
</div>

<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <h3 class="card-title"><i class="fas fa-list"></i> Danh sách yêu cầu</h3>
        <span class="badge badge-info">Tổng: {{ $reservations->total() }}</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 48px;"></th>
                    <th>ID</th>
                    <th>Sách</th>
                    <th>Độc giả</th>
                    <th>Giá thuê</th>
                    <th>Ngày lấy/trả</th>
                    <th>Trạng thái</th>
                    <th>Bản sao</th>
                    <th>Ghi chú</th>
                    <th>Thao tác</th>
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
                    <tr class="table-light">
                        <td colspan="10" style="font-weight:600; color: var(--text-primary);">
                            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:12px;">
                                <span class="badge badge-info">{{ $groupLabel }}</span>
                                <span>Độc giả: {{ $readerName }} {{ $readerCard ? '(' . $readerCard . ')' : '' }}</span>
                                <span style="color: var(--text-muted);">Số sách: {{ $group->count() }}</span>
                                <span style="color: #e67e22; font-weight:700;">Tổng: <span class="group-total" data-group="{{ $groupCode }}">{{ number_format($groupTotal, 0, ',', '.') }}đ</span></span>
                                <form method="POST" action="{{ route('admin.inventory-reservations.fulfill-group') }}" class="group-fulfill-form confirm-submit-btn" data-group="{{ $groupCode }}" style="margin-left:auto; display:flex; gap:8px; align-items:center;">
                                    @csrf
                                    <input type="hidden" name="reservation_ids" value="" class="group-selected-ids">
                                    <input type="hidden" name="all_ids" value="{{ $group->pluck('id')->implode(',') }}" class="group-all-ids">
                                    <button type="submit" class="btn btn-sm btn-primary group-fulfill-btn" data-confirm-message="Fulfill toàn bộ đơn đã chọn?" data-requires-selection="1">
                                        <i class="fas fa-check"></i> Fulfill đơn
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    @foreach($group as $r)
                        <tr>
                            <td>
                                <input type="checkbox" class="group-item-checkbox" data-group="{{ $groupCode }}" value="{{ $r->id }}" data-fee="{{ (float) ($r->total_fee ?? 0) }}" {{ $r->status === 'ready' ? 'checked' : '' }} {{ $r->status !== 'ready' ? 'disabled' : '' }}>
                            </td>
                            <td><span class="badge badge-secondary">#{{ $r->id }}</span></td>
                            <td>
                                <div style="font-weight:700; color: var(--text-primary);">{{ $r->book->ten_sach ?? 'N/A' }}</div>
                                <div style="font-size:12px; color: var(--text-muted);">BK{{ str_pad((string)($r->book_id ?? 0), 6, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td>
                                <div style="font-weight:600;">{{ $r->reader->ho_ten ?? ($r->user->name ?? 'N/A') }}</div>
                                <div style="font-size:12px; color: var(--text-muted);">{{ $r->reader->so_the_doc_gia ?? '' }}</div>
                            </td>
                            <td>
                                <div style="font-weight:700; color: #e67e22;">{{ number_format($r->total_fee ?? 0, 0, ',', '.') }}đ</div>
                            </td>
                            <td>
                                @if($r->pickup_date)
                                    <div style="font-size:13px;"><strong>Lấy:</strong> {{ \Carbon\Carbon::parse($r->pickup_date)->format('d/m/Y') }}</div>
                                    <div style="font-size:13px;"><strong>Trả:</strong> {{ \Carbon\Carbon::parse($r->return_date)->format('d/m/Y') }}</div>
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
                                    <span class="badge badge-success">#{{ $r->inventory->id }}</span>
                                    <div style="font-size:12px; color: var(--text-muted);">{{ $r->inventory->barcode ?? '' }}</div>
                                @else
                                    <span class="badge badge-secondary">Chưa gán</span>
                                @endif
                            </td>
                            <td style="max-width: 260px;">
                                <div style="font-size:13px;">{{ $r->notes }}</div>
                                @if($r->admin_note)
                                    <div style="font-size:12px; color: var(--text-muted); margin-top: 6px;">
                                        <strong>Admin:</strong> {{ $r->admin_note }}
                                    </div>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    @if($r->status === 'pending' && !$isOverduePickup)
                                        <form method="POST" action="{{ route('admin.inventory-reservations.ready', $r->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success confirm-submit-btn" data-confirm-message="Xác nhận sách sẵn sàng tại quầy? Hệ thống sẽ tự gán 1 bản copy đang có sẵn và gửi thông báo cho độc giả.">
                                                <i class="fas fa-check"></i> Ready
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($r->status, ['pending', 'ready'], true) && !$isOverduePickup)
                                        <form method="POST" action="{{ route('admin.inventory-reservations.fulfill', $r->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary confirm-submit-btn" data-confirm-message="Đánh dấu độc giả đã nhận sách tại quầy?">
                                                <i class="fas fa-hand-holding"></i> Fulfill
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.inventory-reservations.cancel', $r->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger confirm-submit-btn" data-confirm-message="Hủy yêu cầu đặt trước này?">
                                                <i class="fas fa-times"></i> Hủy
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($r->status, ['pending', 'ready'], true) && $isOverduePickup)
                                        <form method="POST" action="{{ route('admin.inventory-reservations.cancel', $r->id) }}" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="mark_overdue" value="1">
                                            <input type="hidden" name="admin_note" value="Quá hạn nhận sách: đã qua ngày lấy nhưng khách chưa đến nhận.">
                                            <button type="submit" class="btn btn-sm btn-warning confirm-submit-btn" data-confirm-message="Ngày lấy đã qua nhưng khách chưa nhận. Đánh dấu quá hạn cho yêu cầu này?">
                                                <i class="fas fa-clock"></i> Quá hạn
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

    const formatCurrency = (value) => new Intl.NumberFormat('vi-VN').format(value) + 'đ';

    const groups = new Map();
    document.querySelectorAll('.group-item-checkbox').forEach((checkbox) => {
        const group = checkbox.dataset.group;
        if (!groups.has(group)) {
            groups.set(group, []);
        }
        groups.get(group).push(checkbox);
    });

    groups.forEach((checkboxes, group) => {
        const totalEl = document.querySelector(`.group-total[data-group="${group}"]`);
        const hiddenInput = document.querySelector(`.group-fulfill-form[data-group="${group}"] .group-selected-ids`);
        const allInput = document.querySelector(`.group-fulfill-form[data-group="${group}"] .group-all-ids`);
        const fulfillBtn = document.querySelector(`.group-fulfill-form[data-group="${group}"] .group-fulfill-btn`);

        const recalc = () => {
            let total = 0;
            const selectedIds = [];
            checkboxes.forEach((cb) => {
                if (cb.checked) {
                    total += Number(cb.dataset.fee || 0);
                    selectedIds.push(cb.value);
                }
            });

            if (totalEl) {
                totalEl.textContent = formatCurrency(total);
            }
            if (hiddenInput) {
                hiddenInput.value = selectedIds.join(',');
            }
            if (allInput && !allInput.value) {
                allInput.value = checkboxes.map((cb) => cb.value).join(',');
            }
            if (fulfillBtn) {
                fulfillBtn.disabled = selectedIds.length === 0;
            }
        };

        checkboxes.forEach((cb) => cb.addEventListener('change', recalc));
        recalc();
    });
});
</script>
@endpush
