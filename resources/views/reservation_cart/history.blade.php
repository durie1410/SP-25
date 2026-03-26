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

    .notice-inline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
    }

    .notice-inline.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .notice-inline.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .notice-inline.success {
        background: #d1fae5;
        color: #065f46;
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

    <div class="history-card" style="padding: 16px; display: grid; gap: 14px;">
        @php
            $groupedReservations = $groupedReservations ?? collect();
        @endphp

        @forelse($groupedReservations as $group)
            @php
                $first = $group->first();
                $groupOverdue = $group->contains(fn ($item) => $item->is_pickup_overdue);
                $groupStatus = $groupOverdue
                    ? 'overdue'
                    : ($group->contains(fn ($item) => $item->status === 'ready') ? 'ready' : ($first->status ?? 'pending'));
                $groupTotal = (float) $group->sum(fn ($item) => (float) ($item->total_fee ?? 0));
                $groupCode = $first->reservation_code ?: ('RL-' . str_pad((string) $first->id, 6, '0', STR_PAD_LEFT));
                // Cho xác nhận khi có ÍT NHẤT 1 cuốn ready (pending bị bỏ qua, không cần chờ hết)
                $hasReadyItems = $group->contains(fn($item) => $item->status === 'ready' && !$item->is_pickup_overdue);
                $hasPending = $group->contains(fn($item) => $item->status === 'pending');
                $canConfirmReady = $hasReadyItems
                    && !empty($first->reservation_code)
                    && !$groupOverdue
                    && !$group->where('status', 'ready')->every(fn($item) => !empty($item->customer_confirmed_at));
                $hasCancelableGroup = $group->contains(fn($item) => in_array($item->status, ['ready','pending']) && !$item->is_pickup_overdue);
                $allReadyConfirmed  = $hasReadyItems && $group->where('status','ready')->every(fn($item) => !empty($item->customer_confirmed_at));
                $groupId = $first->reservation_code ?: 'id-' . $first->id;
            @endphp

            <div style="border: 1px solid var(--reserve-border); border-radius: 14px; padding: 14px; background: #fff;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; flex-wrap: wrap;">
                    <div>
                        <span class="reservation-code">Mã đơn: {{ $groupCode }}</span>
                        <div style="margin-top: 8px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                            <span class="schedule-label">số lượng: {{ $group->count() }} sách</span>
                        </div>

                        {{-- Số lượng sách theo từng trạng thái ngay dưới mã đơn --}}
                        @php
                            $statusCounts = $group->groupBy(function($res) {
                                return $res->is_pickup_overdue ? 'overdue_pickup' : $res->status;
                            })->map->count();
                            $statusDisplay = [
                                'pending'         => ['label' => 'Đang chờ',        'class' => 'status-pending'],
                                'ready'           => ['label' => 'Đã sẵn sàng',     'class' => 'status-ready'],
                                'fulfilled'       => ['label' => 'Đã hoàn thành',   'class' => 'status-fulfilled'],
                                'cancelled'       => ['label' => 'Đã hủy',          'class' => 'status-cancelled'],
                                'overdue'         => ['label' => 'Quá hạn',         'class' => 'status-overdue'],
                                'overdue_pickup'  => ['label' => 'Quá hạn nhận',    'class' => 'status-overdue'],
                            ];
                        @endphp
                        <div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
                            @foreach($statusCounts as $stKey => $stCount)
                                @php $stInfo = $statusDisplay[$stKey] ?? ['label' => $stKey, 'class' => 'status-pending']; @endphp
                                <span class="status-badge {{ $stInfo['class'] }}" style="font-size: 11px; padding: 2px 10px;">
                                    {{ $stInfo['label'] }}: {{ $stCount }}
                                </span>
                            @endforeach
                        </div>

                    
                    </div>

                    <div class="fee-info">
                        <div class="fee-label">Tổng tiền</div>
                        <div class="fee-value">{{ number_format($groupTotal, 0, ',', '.') }}đ</div>

                        @if(($hasReadyItems || $hasPending || $hasCancelableGroup) && !empty($first->reservation_code))
                            <div class="ready-actions">
                                @if($canConfirmReady || $hasCancelableGroup)
                                    {{-- Form bulk confirm --}}
                                    <form id="bulk-confirm-{{ $groupId }}"
                                          action="{{ route('reservation-cart.history.bulk.confirm') }}"
                                          method="POST" style="display:none;">
                                        @csrf
                                        <input type="hidden" name="ids" class="bulk-ids-input">
                                    </form>
                                    {{-- Form bulk cancel --}}
                                    <form id="bulk-cancel-{{ $groupId }}"
                                          action="{{ route('reservation-cart.history.bulk.cancel') }}"
                                          method="POST" style="display:none;">
                                        @csrf
                                        <input type="hidden" name="ids" class="bulk-ids-input">
                                    </form>
                                @endif

                                @if($canConfirmReady)
                                    <button type="button"
                                            class="ready-btn ready-btn-confirm bulk-confirm-btn"
                                            data-group="{{ $groupId }}"
                                            data-group-confirm-url="{{ route('reservation-cart.history.confirm-ready', $first->reservation_code) }}">
                                        <i class="fas fa-check-circle"></i> Xác nhận sẽ đến nhận
                                    </button>
                                @endif

                                @if($hasCancelableGroup)
                                    <button type="button"
                                            class="ready-btn ready-btn-cancel bulk-cancel-btn"
                                            data-group="{{ $groupId }}"
                                            data-group-cancel-url="{{ route('reservation-cart.history.cancel-ready', $first->reservation_code) }}">
                                        <i class="fas fa-times-circle"></i> Hủy đã chọn
                                    </button>
                                @endif
                            </div>
                            <div id="bulk-hint-{{ $groupId }}" style="font-size:11px; color:#64748b; margin-top:4px; display:none;">
                                ℹ️ Tích vào từng cuốn bên dưới rồi nhấn nút hành động
                            </div>
                            @if($canConfirmReady && $hasPending)
                                <div id="pending-warn-{{ $groupId }}" style="font-size:11px; color:#b45309; margin-top:4px;">
                                    ⚠️ Đơn có sách đang chờ — hãy tích chọn từng cuốn <strong>đã sẵn sàng</strong> để xác nhận riêng
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <details style="margin-top: 12px;">
                    <summary style="cursor: pointer; color: var(--reserve-primary); font-weight: 600; list-style: none;">
                        <i class="fas fa-chevron-down" style="margin-right: 6px;"></i>
                        Xem chi tiết ({{ $group->count() }} sách)
                    </summary>
                    <div style="margin-top: 12px; display: grid; gap: 10px;">
                        @foreach($group as $reservation)
                            @php
                                $itemBadgeClass = $reservation->is_pickup_overdue ? 'status-overdue' : 'status-' . $reservation->status;
                                $itemLabel = $reservation->is_pickup_overdue ? 'Quá hạn nhận sách' : $reservation->getStatusLabel();
                                $canConfirmItem = $reservation->status === 'ready' && !$reservation->is_pickup_overdue && empty($reservation->customer_confirmed_at);
                                $canCancelItem  = in_array($reservation->status, ['ready', 'pending']) && !$reservation->is_pickup_overdue;
                            @endphp
                            <div style="display: flex; gap: 10px; align-items: flex-start; border: 1px solid var(--reserve-border); border-radius: 10px; padding: 12px; background: #fff;">
                                {{-- Checkbox --}}
                                <div style="flex-shrink:0; padding-top:4px;">
                                    @if($canConfirmItem || $canCancelItem)
                                        <input type="checkbox"
                                               id="res-check-{{ $reservation->id }}"
                                               class="res-item-checkbox"
                                               data-id="{{ $reservation->id }}"
                                               data-status="{{ $reservation->status }}"
                                               data-can-confirm="{{ $canConfirmItem ? '1' : '0' }}"
                                               data-group="{{ $groupId }}"
                                               style="width:17px; height:17px; cursor:pointer; accent-color: var(--reserve-primary);">
                                    @else
                                        <input type="checkbox" disabled style="width:17px; height:17px; opacity:0.35; cursor:not-allowed;"
                                               title="{{ $reservation->status === 'cancelled' ? 'Đã hủy' : ($reservation->status === 'fulfilled' ? 'Đã hoàn thành' : 'Không thể thao tác') }}">
                                    @endif
                                </div>

                                <img src="{{ $reservation->book?->image_url ?? ($reservation->book && $reservation->book->hinh_anh ? asset('storage/' . $reservation->book->hinh_anh) : 'https://via.placeholder.com/60x80?text=No') }}"
                                     alt="{{ $reservation->book->ten_sach ?? 'Sách' }}"
                                     style="width: 60px; height: 80px; object-fit: cover; border-radius: 6px; flex-shrink:0;">
                                <div class="book-info" style="flex:1; min-width:0;">
                                    <h4 style="font-size: 15px; font-weight: 600; margin: 0 0 4px 0; color: var(--reserve-text);">
                                        {{ $reservation->book->ten_sach ?? 'N/A' }}
                                    </h4>
                                    <p style="margin: 0 0 8px 0; font-size: 13px; color: var(--reserve-muted);">
                                        {{ $reservation->book->tac_gia ?? 'Không rõ' }}
                                    </p>
                                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                        <span class="status-badge {{ $itemBadgeClass }}">{{ $itemLabel }}</span>
                                        @if($reservation->inventory_id)
                                            <span class="reservation-code">Bản sao #{{ $reservation->inventory_id }}</span>
                                        @endif
                                        <span class="fee-label">{{ number_format($reservation->total_fee ?? 0, 0, ',', '.') }}đ</span>
                                    </div>

                                    {{-- Thông báo trạng thái khi tích --}}
                                    @if($reservation->status === 'pending')
                                        <div style="margin-top:6px; font-size:12px; color:#b45309; background:#fef9c3; border:1px solid #fde68a; border-radius:6px; padding:4px 8px; display:inline-block;">
                                            ⚠️ Sách chưa được duyệt, chưa thể xác nhận đến nhận
                                        </div>
                                    @elseif(!empty($reservation->customer_confirmed_at))
                                        <div style="margin-top:6px; font-size:12px; color:#15803d; display:inline-block;">
                                            ✅ Bạn đã xác nhận sẽ đến nhận
                                        </div>
                                    @endif

                                    {{-- Nút hành động theo từng cuốn --}}
                                    <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;" id="res-actions-{{ $reservation->id }}">
                                        @if($canConfirmItem)
                                            <form action="{{ route('reservation-cart.history.single.confirm', $reservation->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="ready-btn ready-btn-confirm" style="font-size:12px; padding:4px 12px;">
                                                    <i class="fas fa-check-circle"></i> Xác nhận đến nhận
                                                </button>
                                            </form>
                                        @endif
                                        @if($canCancelItem)
                                            <form action="{{ route('reservation-cart.history.single.cancel', $reservation->id) }}" method="POST"
                                                  onsubmit="return confirm('Hủy sách \"{{ addslashes($reservation->book->ten_sach ?? 'này') }}\"?');">
                                                @csrf
                                                <button type="submit" class="ready-btn ready-btn-cancel" style="font-size:12px; padding:4px 12px;">
                                                    <i class="fas fa-times-circle"></i> Hủy
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="schedule-info" style="margin-top: 8px;">
                                        <div class="schedule-item">
                                            <span class="schedule-label">Ngày lấy</span>
                                            <span class="schedule-value">{{ $reservation->pickup_display }}</span>
                                        </div>
                                        <div class="schedule-item">
                                            <span class="schedule-label">Hạn nhận</span>
                                            <span class="schedule-value {{ $reservation->is_pickup_overdue ? 'text-danger' : '' }}">
                                                {{ $reservation->pickup_deadline_display }} (sau 2 giờ)
                                            </span>
                                        </div>
                                        <div class="schedule-item">
                                            <span class="schedule-label">Ngày trả</span>
                                            <span class="schedule-value">{{ $reservation->return_date ? \Carbon\Carbon::parse($reservation->return_date)->format('d/m/Y') : 'N/A' }}</span>
                                        </div>
                                    </div>
                                    @php $proofImgs = $reservation->getProofImages(); @endphp
                                    @if(!empty($proofImgs))
                                        <div style="margin-top: 10px;">
                                            <div style="font-size: 12px; font-weight: 600; color: var(--reserve-muted); margin-bottom: 6px;">📷 Ảnh chứng minh ({{ count($proofImgs) }})</div>
                                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                                @foreach($proofImgs as $idx => $img)
                                                    <img src="{{ asset('storage/' . $img) }}"
                                                         alt="Ảnh {{ $idx + 1 }}"
                                                         style="width: 64px; height: 64px; object-fit: cover; border-radius: 6px; border: 1px solid var(--reserve-border); cursor: pointer;"
                                                         onclick="showReservationGallery({{ json_encode(array_values($proofImgs)) }}, {{ $idx }})">
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            </div>
        @empty
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

@push('scripts')
<script>
function showReservationGallery(images, startIndex) {
    const existing = document.getElementById('res-gallery-modal');
    if (existing) existing.remove();

    let current = startIndex ?? 0;

    const overlay = document.createElement('div');
    overlay.id = 'res-gallery-modal';
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.92);z-index:99999;display:flex;flex-direction:column;align-items:center;justify-content:center;';

    const counter = document.createElement('div');
    counter.style.cssText = 'position:absolute;top:16px;left:50%;transform:translateX(-50%);color:#fff;font-size:15px;font-weight:600;';

    const img = document.createElement('img');
    img.style.cssText = 'max-width:90%;max-height:80vh;object-fit:contain;border-radius:8px;box-shadow:0 4px 32px rgba(0,0,0,0.5);';

    function render() {
        img.src = '/storage/' + images[current];
        counter.textContent = (current + 1) + ' / ' + images.length;
    }
    render();

    const closeBtn = document.createElement('button');
    closeBtn.textContent = '✕';
    closeBtn.style.cssText = 'position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:16px;';
    closeBtn.onclick = () => overlay.remove();

    const prevBtn = document.createElement('button');
    prevBtn.textContent = '‹';
    prevBtn.style.cssText = 'position:absolute;left:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;width:48px;height:48px;border-radius:50%;font-size:28px;cursor:pointer;display:' + (images.length > 1 ? 'flex' : 'none') + ';align-items:center;justify-content:center;';
    prevBtn.onclick = () => { current = (current - 1 + images.length) % images.length; render(); };

    const nextBtn = document.createElement('button');
    nextBtn.textContent = '›';
    nextBtn.style.cssText = 'position:absolute;right:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;width:48px;height:48px;border-radius:50%;font-size:28px;cursor:pointer;display:' + (images.length > 1 ? 'flex' : 'none') + ';align-items:center;justify-content:center;';
    nextBtn.onclick = () => { current = (current + 1) % images.length; render(); };

    overlay.appendChild(counter);
    overlay.appendChild(img);
    overlay.appendChild(closeBtn);
    overlay.appendChild(prevBtn);
    overlay.appendChild(nextBtn);
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });

    document.addEventListener('keydown', function handler(e) {
        if (!document.getElementById('res-gallery-modal')) { document.removeEventListener('keydown', handler); return; }
        if (e.key === 'Escape') overlay.remove();
        if (e.key === 'ArrowLeft') { current = (current - 1 + images.length) % images.length; render(); }
        if (e.key === 'ArrowRight') { current = (current + 1) % images.length; render(); }
    });

    document.body.appendChild(overlay);
}

// ---- Bulk checkbox → group action buttons ----
document.addEventListener('DOMContentLoaded', function () {
    // When any checkbox changes, update that group's button state
    document.querySelectorAll('.res-item-checkbox').forEach(function (cb) {
        cb.addEventListener('change', updateGroupButtons);
    });

    function updateGroupButtons() {
        // Get all unique group IDs
        const groups = new Set();
        document.querySelectorAll('.res-item-checkbox').forEach(cb => groups.add(cb.dataset.group));

        groups.forEach(function (gid) {
            const checkboxes = document.querySelectorAll('.res-item-checkbox[data-group="' + gid + '"]');
            const checkedBoxes = document.querySelectorAll('.res-item-checkbox[data-group="' + gid + '"]:checked');
            const checkedReadyIds   = [];
            const checkedCancelIds  = [];

            checkedBoxes.forEach(function (cb) {
                if (cb.dataset.canConfirm === '1') checkedReadyIds.push(cb.dataset.id);
                checkedCancelIds.push(cb.dataset.id); // any checked (ready or pending) can be cancelled
            });

            const confirmBtn = document.querySelector('.bulk-confirm-btn[data-group="' + gid + '"]');
            const cancelBtn  = document.querySelector('.bulk-cancel-btn[data-group="' + gid + '"]');
            const hint       = document.getElementById('bulk-hint-' + gid);
            const confirmForm = document.getElementById('bulk-confirm-' + gid);
            const cancelForm  = document.getElementById('bulk-cancel-' + gid);

            const anyChecked = checkedBoxes.length > 0;
            const hasCheckedPending = Array.from(checkedBoxes).some(cb => cb.dataset.status === 'pending');

            // Show hint when any checkbox is ticked
            if (hint) hint.style.display = anyChecked ? 'block' : 'none';

            // Check if group has any pending items
            const hasPendingInGroup = Array.from(
                document.querySelectorAll('.res-item-checkbox[data-group="' + gid + '"]')
            ).some(cb => cb.dataset.status === 'pending');

            if (confirmBtn) {
                if (!anyChecked) {
                    if (hasPendingInGroup) {
                        // Has pending + no selection → block and warn
                        confirmBtn.onclick = function (e) {
                            e.preventDefault();
                            alert('Đơn này còn sách đang chờ duyệt.\nVui lòng tích chọn từng cuốn đã sẵn sàng để xác nhận riêng, hoặc chờ tất cả sách được duyệt.');
                        };
                        confirmBtn.title = 'Đơn còn sách chưa được duyệt';
                        confirmBtn.style.opacity = '0.6';
                    } else {
                        // All ready, no pending → confirm all
                        confirmBtn.onclick = function () {
                            const url = confirmBtn.dataset.groupConfirmUrl;
                            if (confirmForm && url) {
                                confirmForm.action = url;
                                confirmForm.querySelector('.bulk-ids-input').value = '';
                                confirmForm.submit();
                            }
                        };
                        confirmBtn.title = '';
                        confirmBtn.style.opacity = '';
                    }
                } else if (checkedReadyIds.length === 0) {
                    // Only pending selected — block confirm
                    confirmBtn.onclick = function (e) {
                        e.preventDefault();
                        alert('Sách bạn chọn chưa được duyệt. Chỉ những cuốn "Đã sẵn sàng" mới có thể xác nhận đến lấy.');
                    };
                    confirmBtn.title = 'Sách chưa sẵn sàng';
                    confirmBtn.style.opacity = '0.5';
                } else {
                    // Has ready IDs selected, but if ANY pending also selected → block entirely
                    confirmBtn.onclick = function () {
                        if (hasCheckedPending) {
                            alert('Bạn đang tích cả sách đang chờ lẫn sách đã sẵn sàng.\nVui lòng chỉ tích những cuốn "Đã sẵn sàng" mới có thể xác nhận đến lấy.');
                            return;
                        }
                        if (confirmForm) {
                            confirmForm.action = '{{ route("reservation-cart.history.bulk.confirm") }}';
                            confirmForm.querySelector('.bulk-ids-input').value = checkedReadyIds.join(',');
                            confirmForm.submit();
                        }
                    };
                    confirmBtn.title = '';
                    confirmBtn.style.opacity = '';
                }
            }

            if (cancelBtn) {
                cancelBtn.onclick = function () {
                    const ids = anyChecked ? checkedCancelIds : null;
                    if (!ids) {
                        // No selection: cancel full group via group route
                        if (!confirm('Bạn chắc chắn muốn hủy toàn bộ đơn này?')) return;
                        const url = cancelBtn.dataset.groupCancelUrl;
                        if (cancelForm && url) {
                            cancelForm.action = url;
                            cancelForm.querySelector('.bulk-ids-input').value = '';
                            cancelForm.submit();
                        }
                        return;
                    }
                    if (!confirm('Hủy ' + ids.length + ' sách đã chọn?')) return;
                    if (cancelForm) {
                        cancelForm.action = '{{ route("reservation-cart.history.bulk.cancel") }}';
                        cancelForm.querySelector('.bulk-ids-input').value = ids.join(',');
                        cancelForm.submit();
                    }
                };
            }
        });
    }

    // Initial call in case of page state restore
    updateGroupButtons();
});
</script>
@endpush
