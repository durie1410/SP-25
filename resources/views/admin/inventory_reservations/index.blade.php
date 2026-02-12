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
                <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Đã sẵn sàng</option>
                <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Đã hoàn thành</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
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
                @forelse($reservations as $r)
                    <tr>
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
                                $badgeClass = match($r->status) {
                                    'pending' => 'badge-warning',
                                    'ready' => 'badge-success',
                                    'fulfilled' => 'badge-info',
                                    'cancelled' => 'badge-danger',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $r->getStatusLabel() }}</span>
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
                                @if($r->status === 'pending')
                                    <form method="POST" action="{{ route('admin.inventory-reservations.ready', $r->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success confirm-submit-btn" data-confirm-message="Xác nhận sách sẵn sàng tại quầy? Hệ thống sẽ tự gán 1 bản copy đang có sẵn và gửi thông báo cho độc giả.">
                                            <i class="fas fa-check"></i> Ready
                                        </button>
                                    </form>
                                @endif

                                @if(in_array($r->status, ['pending', 'ready'], true))
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
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 40px; color: var(--text-muted);">
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

            if (confirm(message)) {
                const form = button.closest('form');
                if (form) form.submit();
            }
        });
    });
});
</script>
@endpush
