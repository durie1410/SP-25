@extends('layouts.admin')

@php
use Illuminate\Support\Str;
@endphp

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

    <div class="reservation-list">
        @forelse($reservations as $groupCode => $group)
            @php
                $firstReservation = $group->first();
                $groupLabel = $firstReservation->reservation_code
                    ?: 'RSV' . str_pad((string) $firstReservation->id, 6, '0', STR_PAD_LEFT);
                $readerName = $firstReservation->reader->ho_ten ?? ($firstReservation->user->name ?? 'N/A');
                $readerCard = $firstReservation->reader->so_the_doc_gia ?? '';
                $groupTotal = $group->sum(fn($item) => (float) ($item->total_fee ?? 0));
                $groupId = 'group-' . Str::slug($groupCode);

                // Helper function to check if item is overdue
                $isItemOverdue = fn($item) => in_array($item->status, ['pending', 'ready', 'overdue'], true)
                    && (
                        ($item->pickup_date && $item->pickup_date->lt(now()->startOfDay()))
                        || ($item->status === 'ready' && $item->ready_at && $item->ready_at->lt(now()->subHours(2)))
                    );

                // Chỉ ready và không quá hạn mới có thể Fulfill
                $hasReadyItems = $group->contains(fn($item) => $item->status === 'ready' && !$isItemOverdue($item));
                // Chỉ pending hoặc ready (chưa quá hạn) mới có thể hủy - fulfilled/cancelled không hủy được nữa
                $hasCancellableItems = $group->contains(fn($item) => in_array($item->status, ['pending', 'ready', 'overdue'], true)
                    && $item->status !== 'cancelled');
            @endphp

            <!-- Dòng tổng đơn - Always visible -->
            <div class="reservation-group-card accordion-toggle" data-target="{{ $groupId }}" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 10px; cursor: pointer;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-chevron-right toggle-icon" style="transition: transform 0.2s; color: #64748b;"></i>
                        @if($hasReadyItems)
                            <input type="checkbox" class="select-all-checkbox fulfill-select-all" data-group="{{ $groupId }}" onclick="event.stopPropagation();" style="width: 18px; height: 18px; cursor: pointer;" title="Chọn tất cả để Fulfill">
                        @else
                            <input type="checkbox" disabled style="width: 18px; height: 18px; opacity: 0.4; cursor: not-allowed;" title="Chỉ sách Ready mới có thể tích để Fulfill">
                        @endif
                        <span class="badge badge-info">{{ $groupLabel }}</span>
                        <div>
                            <div style="font-weight: 600; color: #1f2937;">{{ $readerName }}</div>
                            @if($readerCard)
                                <div style="font-size: 12px; color: #64748b;">Thẻ {{ $readerCard }}</div>
                            @endif
                        </div>
                        <span class="badge badge-secondary">{{ $group->count() }} cuốn</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="font-weight: 600; color: #0f766e;">
                            Tổng phí: <span class="group-total-fee" data-group="{{ $groupId }}">{{ number_format($groupTotal, 0, ',', '.') }}đ</span>
                        </div>
                        @if($hasReadyItems)
                            <button type="button" class="btn btn-sm btn-primary fulfill-selected-btn" data-group="{{ $groupId }}" onclick="event.stopPropagation();">
                                <i class="fas fa-check"></i> Fulfill
                            </button>
                        @else
                            <button type="button" class="btn btn-sm btn-secondary" disabled style="opacity: 0.4; cursor: not-allowed;" title="Không có sách nào sẵn sàng để Fulfill">
                                <i class="fas fa-check"></i> Fulfill
                            </button>
                        @endif

                        @if($hasCancellableItems)
                            <button type="button" class="btn btn-sm btn-danger cancel-selected-btn" data-group="{{ $groupId }}" onclick="event.stopPropagation();">
                                <i class="fas fa-times"></i> Hủy
                            </button>
                        @else
                            <button type="button" class="btn btn-sm btn-secondary" disabled style="opacity: 0.4; cursor: not-allowed;" title="Đơn đã hoàn thành hoặc đã hủy">
                                <i class="fas fa-times"></i> Hủy
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Chi tiết các cuốn sách - Hidden by default -->
            <div class="accordion-content {{ $groupId }}" style="display: none; margin-bottom: 15px; padding-left: 20px;">
                @foreach($group as $r)
                    @php
                        // Kiểm tra quá hạn: pickup_date trong quá khứ HOẶC ready quá 2 giờ
                        $isOverduePickup = in_array($r->status, ['pending', 'ready'], true)
                            && (
                                ($r->pickup_date && $r->pickup_date->lt(now()->startOfDay()))
                                || ($r->status === 'ready' && $r->ready_at && $r->ready_at->lt(now()->subHours(2)))
                            );

                        $badgeClass = $isOverduePickup ? 'badge-danger' : match($r->status) {
                            'pending' => 'badge-warning',
                            'ready' => 'badge-success',
                            'fulfilled' => 'badge-info',
                            'cancelled' => 'badge-danger',
                            'overdue' => 'badge-danger',
                            default => 'badge-secondary',
                        };

                        $statusLabel = $isOverduePickup ? 'Quá hạn' : $r->getStatusLabel();
                    @endphp
                    <div class="reservation-item-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 8px;">
                        <div style="display: grid; grid-template-columns: 40px repeat(auto-fit, minmax(100px, 1fr)); gap: 10px; align-items: center;">
                            <div>
                                @if($r->status === 'ready' && !$isOverduePickup)
                                    <input type="checkbox" name="reservation_ids[]" value="{{ $r->id }}" class="reservation-checkbox fulfill-checkbox group-{{ $groupId }}" style="width: 18px; height: 18px; cursor: pointer;" title="Tích để Fulfill">
                                @elseif(in_array($r->status, ['pending', 'overdue']) && !$isOverduePickup)
                                    <input type="checkbox" name="cancel_ids[]" value="{{ $r->id }}" class="cancel-checkbox group-{{ $groupId }}" style="width: 18px; height: 18px; cursor: pointer;" title="Tích để Hủy">
                                @elseif($r->status === 'ready' && $isOverduePickup)
                                    {{-- Ready quá hạn: có thể hủy --}}
                                    <input type="checkbox" name="cancel_ids[]" value="{{ $r->id }}" class="cancel-checkbox group-{{ $groupId }}" style="width: 18px; height: 18px; cursor: pointer;" title="Tích để Hủy">
                                @else
                                    <input type="checkbox" disabled style="width: 18px; height: 18px; opacity: 0.4; cursor: not-allowed;" title="Không thể chọn">
                                @endif
                            </div>
                            <div>
                                <div style="font-size: 11px; color: #64748b;">ID</div>
                                <span class="badge badge-secondary">#{{ $r->id }}</span>
                            </div>
                            <div>
                                <div style="font-size: 11px; color: #64748b;">Sách</div>
                                <div style="font-weight: 600; font-size: 13px;">{{ $r->book->ten_sach ?? 'N/A' }}</div>
                                <div style="font-size: 11px; color: #64748b;">{{ $r->book->tac_gia ?? 'Không rõ' }}</div>
                            </div>
                            <div>
                                <div style="font-size: 11px; color: #64748b;">Độc giả</div>
                                <div style="font-weight: 600; font-size: 13px;">{{ $r->reader->ho_ten ?? ($r->user->name ?? 'N/A') }}</div>
                                <div style="font-size: 11px; color: #64748b;">{{ $r->reader->so_the_doc_gia ?? 'Khách' }}</div>
                            </div>
                            <div>
                                <div style="font-size: 11px; color: #64748b;">Lịch</div>
                                @if($r->pickup_date)
                                    <div style="font-size: 12px;">
                                        <div><strong>Lấy:</strong> {{ \Carbon\Carbon::parse($r->pickup_date)->format('d/m/Y') }} @if($r->pickup_time) - {{ $r->pickup_time }} @endif</div>
                                        <div><strong>Trả:</strong> {{ \Carbon\Carbon::parse($r->return_date)->format('d/m/Y') }}</div>
                                        @if($r->status === 'ready' && $r->ready_at)
                                            @php
                                                $readyHours = now()->diffInMinutes($r->ready_at);
                                                $readyHoursDisplay = $readyHours < 60 ? $readyHours . ' phút' : round($readyHours / 60, 1) . ' giờ';
                                            @endphp
                                            <div style="color: #64748b; font-size: 11px;">Ready: {{ $readyHoursDisplay }}</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                            <div>
                                <div style="font-size: 11px; color: #64748b;">Trạng thái</div>
                                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                @if($r->status === 'ready')
                                    <div style="margin-top: 6px;">
                                        @if($r->customer_confirmed_at)
                                            <span class="badge badge-success" style="font-size: 10px;">Khách đã xác nhận</span>
                                        @else
                                            <span class="badge badge-warning" style="font-size: 10px;">Chờ khách xác nhận</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div style="font-size: 11px; color: #64748b;">Bản sao</div>
                                @if($r->inventory)
                                    <span class="badge badge-success">#{{ $r->inventory->id }}</span>
                                @else
                                    <span class="badge badge-secondary">Chưa gán</span>
                                @endif
                            </div>
                            <div>
                                <div style="font-size: 11px; color: #64748b;">Phí</div>
                                @php
                                    $days = 1;
                                    if($r->pickup_date && $r->return_date) {
                                        $pickup = \Carbon\Carbon::parse($r->pickup_date);
                                        $return = \Carbon\Carbon::parse($r->return_date);
                                        $days = max(1, (int)$pickup->diffInDays($return) + 1);
                                    }
                                    // Lấy daily_fee từ sách, mặc định 5000
                                    $dailyFeeFromBook = (int) ($r->book?->daily_fee ?? 5000);
                                @endphp
                                <div style="font-weight: 700; color: #f97316;">{{ number_format($r->total_fee ?? 0, 0, ',', '.') }}đ</div>
                                <div style="font-size: 10px; color: #64748b;">{{ $days }} ngày × {{ number_format($dailyFeeFromBook, 0, ',', '.') }}đ</div>
                            </div>
                            <div>
                                <div style="font-size: 11px; color: #64748b;">Thao tác</div>
                                <div class="action-group">
                                    <a href="{{ route('admin.inventory-reservations.proof', $r->id) }}" class="btn btn-sm btn-outline-primary" style="padding: 4px 8px; font-size: 11px;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($r->status === 'pending' && !$isOverduePickup)
                                        <form method="POST" action="{{ route('admin.inventory-reservations.ready', $r->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" style="padding: 4px 8px; font-size: 11px;">Ready</button>
                                        </form>
                                    @endif
                                    @if($r->status === 'ready' && !$isOverduePickup)
                                        <form method="POST" action="{{ route('admin.inventory-reservations.fulfill', $r->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary" style="padding: 4px 8px; font-size: 11px;">Fulfill</button>
                                        </form>
                                    @endif
                                    @if(in_array($r->status, ['overdue']) || ($r->status === 'ready' && $isOverduePickup))
                                        <form method="POST" action="{{ route('admin.inventory-reservations.cancel', $r->id) }}" style="display:inline;" onsubmit="return confirm('Xác nhận hủy yêu cầu này?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 8px; font-size: 11px;">Hủy</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                Chưa có yêu cầu đặt trước nào.
            </div>
        @endforelse
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

.toggle-icon {
    color: #64748b;
    font-size: 12px;
    margin-right: 4px;
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
    // Accordion toggle
    const accordionToggles = document.querySelectorAll('.accordion-toggle');
    accordionToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            // Don't toggle if clicking on checkbox or buttons
            if (e.target.classList.contains('select-all-checkbox') ||
                e.target.classList.contains('reservation-checkbox') ||
                e.target.closest('button')) return;

            const target = this.getAttribute('data-target');
            const content = document.querySelector('.' + target);
            const icon = this.querySelector('.toggle-icon');

            if (content) {
                if (content.style.display === 'none' || content.style.display === '') {
                    content.style.display = 'block';
                    if (icon) icon.style.transform = 'rotate(90deg)';
                } else {
                    content.style.display = 'none';
                    if (icon) icon.style.transform = 'rotate(0)';
                }
            }
        });
    });

    // Select all (fulfill) - chỉ chọn sách ready
    const fulfillSelectAlls = document.querySelectorAll('.fulfill-select-all');
    fulfillSelectAlls.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const groupId = this.getAttribute('data-group');
            const checkboxes = document.querySelectorAll('.group-' + groupId + '.fulfill-checkbox:not(:disabled)');
            checkboxes.forEach(cb => { cb.checked = this.checked; });
            updateGroupTotalFee(groupId);
        });
    });

    // Individual fulfill checkbox change → update fulfill select-all
    document.addEventListener('change', function(e) {
        const groupId = e.target.classList.toString().match(/group-(\S+)/)?.[1];
        if (!groupId) return;

        if (e.target.classList.contains('fulfill-checkbox')) {
            const groupCheckboxes = document.querySelectorAll('.group-' + groupId + '.fulfill-checkbox:not(:disabled)');
            const selectAll = document.querySelector('.fulfill-select-all[data-group="' + groupId + '"]');
            const allChecked = Array.from(groupCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(groupCheckboxes).some(cb => cb.checked);
            if (selectAll) {
                selectAll.checked = allChecked;
                selectAll.indeterminate = someChecked && !allChecked;
            }
            updateGroupTotalFee(groupId);
        }
    });

    // Tính tổng phí dựa trên các ô được tích
    function updateGroupTotalFee(groupId) {
        const checkedItems = document.querySelectorAll('.group-' + groupId + ':checked');
        let totalFee = 0;
        let totalBooks = 0;

        checkedItems.forEach(checkbox => {
            const card = checkbox.closest('.reservation-item-card');
            if (card) {
                const feeText = card.querySelector('[style*="f97316"]');
                if (feeText) {
                    // Lấy số tiền từ text ( VD: "15.000đ" -> 15000 )
                    const feeMatch = feeText.textContent.match(/[\d.]+/);
                    if (feeMatch) {
                        totalFee += parseInt(feeMatch[0].replace(/\./g, '')) || 0;
                    }
                }
                totalBooks++;
            }
        });

        // Cập nhật hiển thị tổng phí
        const totalFeeEl = document.querySelector(`.group-total-fee[data-group="${groupId}"]`);
        if (totalFeeEl) {
            totalFeeEl.textContent = formatCurrencyVN(totalFee);
        }
    }

    // Hàm format tiền Việt Nam
    function formatCurrencyVN(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    }

    // Fulfill selected buttons - chỉ lấy sách ready (fulfill-checkbox)
    const fulfillButtons = document.querySelectorAll('.fulfill-selected-btn');
    fulfillButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const groupId = this.getAttribute('data-group');
            const checkboxes = document.querySelectorAll('.group-' + groupId + '.fulfill-checkbox:checked');

            if (checkboxes.length === 0) {
                alert('Vui lòng chọn ít nhất 1 cuốn sách để Fulfill.');
                return;
            }

            if (!confirm('Fulfill ' + checkboxes.length + ' cuốn sách đã chọn?')) {
                return;
            }

            // Collect selected IDs
            const selectedIds = Array.from(checkboxes).map(cb => cb.value);

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.inventory-reservations.fulfill-group") }}';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'reservation_ids';
            idsInput.value = selectedIds.join(',');
            form.appendChild(idsInput);

            document.body.appendChild(form);
            form.submit();
        });
    });

    // Cancel selected buttons - chỉ gửi sách pending (cancel-checkbox)
    const cancelButtons = document.querySelectorAll('.cancel-selected-btn');
    cancelButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const groupId = this.getAttribute('data-group');
            const checkboxes = document.querySelectorAll('.group-' + groupId + '.cancel-checkbox:checked');

            if (checkboxes.length === 0) {
                alert('Vui lòng chọn ít nhất 1 cuốn sách để hủy.');
                return;
            }

            if (!confirm('Hủy ' + checkboxes.length + ' yêu cầu đã chọn?')) {
                return;
            }

            // Collect selected IDs
            const selectedIds = Array.from(checkboxes).map(cb => cb.value);

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.inventory-reservations.cancel-multiple") }}';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'reservation_ids';
            idsInput.value = selectedIds.join(',');
            form.appendChild(idsInput);

            document.body.appendChild(form);
            form.submit();
        });
    });

});
</script>
@endpush
