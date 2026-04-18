@extends('layouts.admin')

@section('title', 'Quản Lý Mượn/Trả Sách - WAKA Admin')

@section('content')
<!-- Page Header -->
<div class="page-header borrows-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-exchange-alt"></i>
            Quản lý mượn/trả sách
        </h1>
        <p class="page-subtitle">Theo dõi và quản lý tất cả hoạt động mượn trả sách</p>
    </div>
        <a href="{{ route('admin.borrows.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
tạo phiếu mượn    </a>
</div>

<!-- Quick Stats -->
<div class="stats-grid borrows-stats" style="margin-bottom: 22px;">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Đang mượn</div>
            <div class="stat-icon primary">
                <i class="fas fa-hand-holding"></i>
            </div>
        </div>
        <div class="stat-value">{{ $stats['dang_muon'] }}</div>
        <div class="stat-label">Phiếu mượn đang hoạt động</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Quá hạn</div>
            <div class="stat-icon danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-value">{{ $stats['qua_han'] }}</div>
        <div class="stat-label">Cần xử lý ngay</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Đã trả</div>
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value">{{ $stats['da_tra'] }}</div>
        <div class="stat-label">Hoàn thành</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Tổng số</div>
            <div class="stat-icon warning">
                <i class="fas fa-list"></i>
            </div>
        </div>
        <div class="stat-value">{{ $stats['tong'] }}</div>
        <div class="stat-label">Tất cả phiếu mượn</div>
    </div>
</div>

<!-- Search and Filter -->
<div class="card borrows-filter-card" style="margin-bottom: 22px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter"></i>
            Tìm kiếm & Lọc
        </h3>
    </div>
    <form action="{{ route('admin.borrows.index') }}" method="GET" class="borrows-filter-form">
        <div style="flex: 2; min-width: 250px;">
            <input type="text" 
                   name="keyword" 
                   value="{{ request('keyword') }}" 
                   class="form-control" 
                   placeholder="Tìm theo tên độc giả">
        </div>
        <div style="flex: 1; min-width: 200px;">
            <select name="trang_thai" class="form-select">
                <option value="">-- Lọc trạng thái Items --</option>
                <option value="Cho duyet" {{ request('trang_thai') == 'Cho duyet' ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="Dang muon" {{ request('trang_thai') == 'Dang muon' ? 'selected' : '' }}>Đang mượn</option>
                <option value="Da tra" {{ request('trang_thai') == 'Da tra' ? 'selected' : '' }}>Đã trả</option>
                <option value="dang_van_chuyen_tra_ve" {{ request('trang_thai') == 'dang_van_chuyen_tra_ve' ? 'selected' : '' }}>Đang trả về (Có ảnh)</option>
                <option value="Qua han" {{ request('trang_thai') == 'Qua han' ? 'selected' : '' }}>Quá hạn</option>
                <option value="Mat sach" {{ request('trang_thai') == 'Mat sach' ? 'selected' : '' }}>Mất sách</option>
                <option value="Hong" {{ request('trang_thai') == 'Hong' ? 'selected' : '' }}>Bị hỏng</option>
                <option value="Huy" {{ request('trang_thai') == 'Huy' ? 'selected' : '' }}>Đã hủy</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i>
            Lọc
        </button>
        <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">
            <i class="fas fa-redo"></i>
            Reset
        </a>
    </form>
</div>

<!-- Borrows List -->
<div class="card borrows-table-card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Danh sách phiếu mượn
        </h3>
        <span class="badge badge-info">Tổng: {{ $borrows->total() }} phiếu</span>
    </div>
    
    @if($borrows->count() > 0)
    <div class="table-responsive">
            <table class="table borrows-table">
            <thead>
                <tr>
                    <th style="width: 90px;">Mã phiếu</th>
                    <th>Độc giả</th>
                    <th>Tên khách hàng</th>
                    <th style="min-width: 200px;">Trạng thái Items</th>
                    <th style="width: 110px;">Chi tiết</th>
                    <th style="width: 120px;">Tổng tiền</th>
                    <th style="width: 150px;">Thanh toán</th>
                    <th style="width: 200px;">Hành động</th>
                </tr>
            </thead>
         <tbody>
@php
    $groupedBorrows = $borrows->groupBy(function ($borrow) {
        if (!empty($borrow->borrow_code)) {
            return $borrow->borrow_code;
        }

        $readerKey = $borrow->reader_id ?? ($borrow->ten_nguoi_muon ?: 'guest');
        $borrowDate = $borrow->ngay_muon ? $borrow->ngay_muon->format('Ymd') : 'none';
        $createdKey = $borrow->created_at ? $borrow->created_at->format('YmdHi') : 'none';

        return "reader-{$readerKey}-{$borrowDate}-{$createdKey}";
    });
@endphp

@foreach($groupedBorrows as $group)
    @php
        $firstBorrow = $group->first();
        $readerName = $firstBorrow->reader->ho_ten ?? ($firstBorrow->ten_nguoi_muon ?? 'N/A');
        $readerCard = $firstBorrow->reader->so_the_doc_gia ?? '';
        $groupLabel = $firstBorrow->borrow_code ?: ('BRW' . str_pad((string) $firstBorrow->id, 6, '0', STR_PAD_LEFT));
    @endphp


    @foreach($group as $borrow)
<tr style="{{ $borrow->isOverdue() && $borrow->trang_thai != 'Da tra' ? 'border-left: 3px solid #ff6b6b;' : '' }}">
    <td>
        <span class="badge badge-info">{{ $borrow->id }}</span>
        @if($borrow->anh_hoan_tra)
            <i class="fas fa-camera text-primary ms-1" title="Có ảnh minh chứng hoàn trả"></i>
        @endif
    </td>
<td>
    @if($borrow->reader)
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(0, 255, 153, 0.15); display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-weight: 600;">
                {{ strtoupper(substr($borrow->reader->ho_ten, 0, 1)) }}
            </div>
            <div>
                <div style="font-weight: 500; color: var(--text-primary);">
                    {{ $borrow->reader->ho_ten }}
                </div>
                <div style="font-size: 12px; color: #888;">
                    ID: {{ $borrow->reader->id }}
                </div>
            </div>
        </div>
    @else
        <span>Không có thẻ</span>
    @endif
</td>
  @if($borrow->reader)
    <td>{{ $borrow->reader->ho_ten }}</td>
    @else
<td>{{ $borrow->ten_nguoi_muon }}</td>
    @endif
   
    @php
        // Tính tổng từ items nếu có, nếu không thì dùng giá trị từ borrow
        if ($borrow->relationLoaded('items') && $borrow->items && $borrow->items->count() > 0) {
            $tienThue = $borrow->items->sum(function($item) {
                return floatval($item->tien_thue ?? 0);
            });
        } else {
            $tienThue = floatval($borrow->tien_thue ?? 0);
        }

        // Nếu đơn đã thanh toán thành công, giữ số tiền hiển thị theo giao dịch đã chốt.
        $paidAmount = optional(
            $borrow->payments()->where('payment_status', 'success')->latest()->first()
        )->amount;
        // Tổng tiền hiển thị: ưu tiên số tiền đã chốt khi thanh toán thành công.
        $tongTien = $paidAmount !== null ? floatval($paidAmount) : $tienThue;
    @endphp

    @php
// Kiểm tra nếu có items
if ($borrow->items && $borrow->items->count() > 0) {
    $statuses = $borrow->items->pluck('trang_thai')->toArray();
    
    if (in_array('Mat sach', $statuses)) {
        $status = 'Mat sach';
    } elseif (in_array('Qua han', $statuses)) {
        $status = 'Qua han';
    } elseif (in_array('Cho duyet', $statuses) || in_array('Chua nhan', $statuses)) {
        $status = 'Cho duyet';
    } elseif (in_array('Dang muon', $statuses)) {
        $status = 'Dang muon';
    } elseif (in_array('Huy', $statuses)) {
        $status = 'Huy';
    } else {
        $status = 'Da tra';
    }
} else {
    // Nếu không có items, sử dụng trạng thái của Borrow
    $status = $borrow->trang_thai ?? 'Dang muon';
}
@endphp



<td>
 @php
    // Nhóm các BorrowItem theo trạng thái và đếm số lượng s
    $statuses = $borrow->items->groupBy('trang_thai') 
        ->map(function($group) {
            return $group->count();
        })
        ->toArray();
@endphp

@foreach($statuses as $status => $count)
    @php
        $suffixText = $count . ' sách';

        switch($status) {
            case 'Dang muon': $text = 'Đang mượn'; $color = '#007bff'; break;
 case 'Qua han':
    $text = 'Quá hạn';
    $color = 'red';
    $suffixText = $count . ' sách'; // ✅ dùng lại count
    break;
    
            case 'Da tra': $text = 'Đã trả'; $color = 'green'; break;
            case 'Cho duyet': $text = 'Chờ duyệt'; $color = 'orange'; break;
            case 'Chua nhan': $text = 'Chưa nhận'; $color = '#17a2b8'; break;
            case 'Mat sach': $text = 'Mất sách'; $color = 'darkorange'; break;
            case 'Huy': $text = 'Đã hủy'; $color = '#6c757d'; break;
            default: $text = $status; $color = 'gray';
        }
    @endphp
    <div style="color: {{ $color }};">
        {{ $text }} ({{ $suffixText }})
    </div>
@endforeach

</td>

    <td style="text-align: center;">
        @if($borrow->items && $borrow->items->count() > 0)
        <button class="btn btn-sm btn-info toggle-items" 
                data-borrow-id="{{ $borrow->id }}"
                title="Xem danh sách sách"
                style="white-space: nowrap;">
            <i class="fas fa-chevron-down"></i>
            {{ $borrow->items->count() }} sách
        </button>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        {{ number_format($tongTien) }}₫
    </td>
    <td>
        @php
            $latestPayment = $borrow->payments()->latest()->first();
            $latestPendingPayment = $borrow->payments()->where('payment_status', 'pending')->latest()->first();
        @endphp
        
        @if($latestPendingPayment)
            {{-- Chọn phương thức để thực hiện thanh toán, trạng thái hiện tại: chờ thanh toán --}}
            <select id="payment_method_{{ $borrow->id }}" class="form-select form-select-sm" style="font-size:12px;" required>
                <option value="online" selected>Quét mã</option>
                <option value="offline">Tiền mặt</option>
            </select>
            <div style="font-size: 11px; margin-top: 3px;">
                <span class="text-warning">Chờ thanh toán</span>
            </div>
        @elseif($latestPayment)
            {{-- Hiển thị trạng thái thanh toán --}}
            @if($latestPayment->payment_status === 'pending')
                <span class="text-warning">Chờ thanh toán</span>
            @elseif($latestPayment->payment_status === 'success')
                @php
                    $paymentMethodLabel = $latestPayment->payment_method === 'offline'
                        ? 'Tiền mặt'
                        : ($latestPayment->payment_method === 'online' ? 'MoMo' : 'Không xác định');
                @endphp
                <span class="text-success">Đã thanh toán ({{ $paymentMethodLabel }})</span>
            @elseif($latestPayment->payment_status === 'failed')
                <span class="text-danger">Thất bại</span>
            @else
                <span class="text-muted">{{ ucfirst($latestPayment->payment_status) }}</span>
            @endif
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
            {{-- @if($borrow->trang_thai == 'Dang muon')
                <a href="{{ route('admin.borrows.return', $borrow->id) }}"
                   class="btn btn-sm btn-success"
                   onclick="return confirm('Xác nhận trả sách?')"
                   title="Trả sách">
                    <i class="fas fa-undo"></i>
                </a>
            @endif --}}
            <a href="{{ route('admin.borrows.show', $borrow->id) }}" 
               class="btn btn-sm btn-secondary"
               title="Xem chi tiết">
                <i class="fas fa-eye"></i>
            </a>
            @php
                $isOverdue = $borrow->items->isNotEmpty() && $borrow->items->contains(fn($item) => $item->trang_thai === 'Qua han');
                $isDangMuon = $borrow->trang_thai === 'Dang muon'
                    || ($borrow->items->isNotEmpty() && $borrow->items->contains(fn($item) => $item->trang_thai === 'Dang muon'));
            @endphp
            @if(!($borrow->trang_thai === 'Da tra'
                || ($borrow->items->isNotEmpty() && $borrow->items->every(fn($item) => $item->trang_thai === 'Da tra'))
                || $isOverdue
                || $isDangMuon))
                <a href="{{ route('admin.borrows.edit', $borrow->id) }}" 
                   class="btn btn-sm btn-warning"
                   title="Chỉnh sửa">
                    <i class="fas fa-edit"></i>
                </a>
            @endif
            
@php
    $allChuaNhan = $borrow->items->isNotEmpty() && $borrow->items->every(fn($item) => $item->trang_thai === 'Chua nhan');
    $hasChoDuyet = $borrow->items->isNotEmpty() && $borrow->items->contains(fn($item) => $item->trang_thai === 'Cho duyet');
    $hasDangMuon = $borrow->items->isNotEmpty() && $borrow->items->contains(fn($item) => $item->trang_thai === 'Dang muon');
    $hasQuaHan = $borrow->items->isNotEmpty() && $borrow->items->contains(fn($item) => $item->trang_thai === 'Qua han');
    
    // Kiểm tra trạng thái thanh toán
    $latestPendingPayment = $borrow->payments()->where('payment_status', 'pending')->latest()->first();
    $latestSuccessPayment = $borrow->payments()->where('payment_status', 'success')->latest()->first();
    $hasUnpaidPayment = (bool) $latestPendingPayment;
    $hasPaidPayment = (bool) $latestSuccessPayment;
@endphp

{{-- Nút thanh toán: hiện khi có payment pending --}}
@if($hasUnpaidPayment && auth()->check() && auth()->user()->can('edit-borrows'))
    <form action="{{ route('admin.borrows.confirm-cash-payment', $borrow->id) }}" method="POST" style="display:inline-block;">
        @csrf
        <input type="hidden" name="payment_method" id="payment_method_input_{{ $borrow->id }}" value="online">
        <button type="submit" class="btn btn-sm btn-success mb-0" title="Thanh toán" 
                onclick="document.getElementById('payment_method_input_{{ $borrow->id }}').value = document.getElementById('payment_method_{{ $borrow->id }}').value; return confirm('Tiếp tục xử lý thanh toán cho phiếu mượn này?')">
            <i class="fas fa-money-bill-wave"></i> Thanh toán
        </button>
    </form>
@endif

{{-- Nút duyệt: hiện khi chờ duyệt và chưa có nút thanh toán --}}
@if($hasChoDuyet && !$hasUnpaidPayment && auth()->check() && auth()->user()->can('edit-borrows'))
    <form action="{{ route('admin.borrows.approve', $borrow->id) }}" method="POST" style="display:inline-block;">
        @csrf
        <button type="submit" class="btn btn-sm btn-success mb-0" title="Duyet & thanh toan" onclick="return confirm('Duyet phieu muon va chuyen sang man thanh toan?')">
            <i class="fas fa-check-circle"></i> Duyet & thanh toan
        </button>
    </form>
@endif



@if($allChuaNhan)
    <form action="{{ route('admin.borrows.process', $borrow->id) }}" method="POST" style="display:inline-block;">
        @csrf
        <button type="submit" class="btn btn-sm btn-primary mb-0" title="Xử lý phiếu mượn">
            <i class="fas fa-check-circle"></i>
        </button>
    </form>
@endif
        </div>
    </td>
</tr>

{{-- Row ẩn chứa danh sách sách chi tiết --}}
<tr class="items-detail-row" id="items-row-{{ $borrow->id }}" style="display: none;">
    <td colspan="11" style="padding: 0; background-color: #f8f9fa;">
        <div style="padding: 20px; margin: 0;">
            <div style="background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h5 style="color: var(--primary-color); margin-bottom: 15px; font-weight: 600;">
                    <i class="fas fa-book"></i> Danh sách sách trong phiếu #{{ $borrow->id }}
                </h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover" style="margin-bottom: 0;">
                        <thead style="background-color: #e9ecef;">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>Tên sách</th>
                                <th style="width: 150px;">Tác giả</th>
                                <th style="width: 100px;">Tiền thuê</th>
                                <th style="width: 120px;">Ngày hẹn trả</th>
                                <th style="width: 200px;">Trạng thái</th>
                                <th style="width: 150px;">Ảnh xác nhận sách</th>
                                <th style="width: 150px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrow->items as $item)
                            <tr>
                                <td><strong>{{ $item->id }}</strong></td>
                                <td>
                                    <div style="font-weight: 500;">{{ $item->book->ten_sach ?? 'N/A' }}</div>
                                    @if(optional($item->book)->hinh_anh)
                                        <div style="margin-top: 6px;">
                                            <img src="{{ $item->book->image_url }}" alt="{{ $item->book->ten_sach ?? 'Ảnh sách' }}"
                                                style="width: 42px; height: 58px; object-fit: cover; border: 1px solid #dee2e6; border-radius: 4px;">
                                        </div>
                                    @else
                                        <small class="text-muted">Không có ảnh</small>
                                    @endif
                                </td>
                                <td>{{ $item->book->tac_gia ?? 'N/A' }}</td>
                                <td>{{ number_format($item->tien_thue ?? 0) }}₫</td>
                                <td>
                                    @if($item->ngay_hen_tra)
                                        {{ $item->ngay_hen_tra->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        switch($item->trang_thai) {
                                            case 'Dang muon': 
                                                $text = 'Đang mượn'; 
                                                $bgColor = '#007bff'; 
                                                break;
                                            case 'Qua han': 
                                                $text = 'Quá hạn (' . ($item->ngay_hen_tra ? $item->ngay_hen_tra->diffInDays(now()) . ' ngày' : 'N/A') . ')'; 
                                                $bgColor = '#dc3545'; 
                                                break;
                                            case 'Da tra': 
                                                $text = 'Đã trả'; 
                                                $bgColor = '#28a745'; 
                                                break;
                                            case 'Cho duyet': 
                                                $text = 'Chờ duyệt'; 
                                                $bgColor = '#fd7e14'; 
                                                break;
                                            case 'Chua nhan': 
                                                $text = 'Chưa nhận'; 
                                                $bgColor = '#17a2b8'; 
                                                break;
                                            case 'Mat sach': 
                                                $text = 'Mất sách'; 
                                                $bgColor = '#e83e8c'; 
                                                break;
                                            case 'Hong':
                                                $text = 'Hỏng';
                                                $bgColor = '#6c757d';
                                                break;
                                            case 'Huy':
                                                $text = 'Đã hủy';
                                                $bgColor = '#6c757d';
                                                break;
                                            default: 
                                                $text = $item->trang_thai; 
                                                $bgColor = '#6c757d';
                                        }
                                    @endphp
                                    <span class="badge" style="background-color: {{ $bgColor }}; color: white; font-size: 11px;">
                                        {{ $text }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $itemImages = [];
                                        // Ảnh từ reservation (proof_images - ảnh khách upload)
                                        $reservation = $item->reservation_match;
                                        if ($reservation) {
                                            foreach ($reservation->getProofImages() as $img) {
                                                if (!$img) continue;
                                                if (preg_match('/^https?:\/\//i', $img)) {
                                                    $itemImages[] = $img;
                                                } else {
                                                    $normalized = ltrim(str_replace(['\\', 'storage/'], ['/', ''], (string) $img), '/');
                                                    $itemImages[] = asset('storage/' . $normalized);
                                                }
                                            }
                                        }
                                        // Ảnh bìa sách
                                        if (!empty($item->anh_bia_truoc)) {
                                            $itemImages[] = (strpos($item->anh_bia_truoc, 'http') === 0) ? $item->anh_bia_truoc : asset('storage/' . $item->anh_bia_truoc);
                                        }
                                        if (!empty($item->anh_bia_sau)) {
                                            $itemImages[] = (strpos($item->anh_bia_sau, 'http') === 0) ? $item->anh_bia_sau : asset('storage/' . $item->anh_bia_sau);
                                        }
                                        if (!empty($item->anh_gay_sach)) {
                                            $itemImages[] = (strpos($item->anh_gay_sach, 'http') === 0) ? $item->anh_gay_sach : asset('storage/' . $item->anh_gay_sach);
                                        }
                                    @endphp
                                    @if(count($itemImages) > 0)
                                        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">
                                            @foreach(array_slice($itemImages, 0, 2) as $img)
                                                <a href="{{ $img }}" target="_blank">
                                                    <img src="{{ $img }}" alt="Proof" class="img-thumbnail" style="height: 38px; width: 38px; object-fit: cover;" onerror="this.style.display='none'">
                                                </a>
                                            @endforeach
                                        </div>
                                        @if(count($itemImages) > 2)
                                            <div class="text-muted" style="font-size: 11px;">+{{ count($itemImages) - 2 }} ảnh</div>
                                        @endif
                                    @else
                                        <span class="text-muted" style="font-size: 12px;">Chưa có</span>
                                    @endif
                                     @if(!empty($item->ghi_chu_nhan_sach))
                                                    <div class="text-muted" style="font-size: 12px; margin-top: 8px;">
                                                        <strong>Ghi chú:</strong> {{ $item->ghi_chu_nhan_sach }}
                                                    </div>
                                                @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px; justify-content: center; flex-wrap: wrap;">
                                        {{-- 4. Xem chi tiết (luôn hiển thị) --}}
                                        <a href="{{ route('admin.borrowitems.show', $item->id) }}" 
                                           class="btn btn-sm btn-info"
                                           title="Xem chi tiết sách">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </td>
</tr>

    @endforeach
@endforeach
</tbody>

        </table>
    </div>

        <!-- Pagination -->
        <div style="padding: 20px;">
        {{ $borrows->appends(request()->query())->links('vendor.pagination.admin') }}
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(0, 255, 153, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-exchange-alt" style="font-size: 36px; color: var(--primary-color);"></i>
            </div>
            <h3 style="color: var(--text-primary); margin-bottom: 10px;">Chưa có phiếu mượn nào</h3>
            <p style="color: #888; margin-bottom: 25px;">Hãy tạo phiếu mượn đầu tiên để bắt đầu quản lý.</p>
            <a href="{{ route('admin.borrows.create') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i>
                Tạo phiếu mượn đầu tiên
            </a>
        </div>
    @endif
    </div>
@endsection

@push('styles')
<style>
    .borrows-header {
        margin-bottom: 18px;
    }

    .borrows-stats {
        gap: 18px;
    }

    .borrows-filter-card {
        border: 1px solid rgba(148, 163, 184, 0.2);
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.06);
    }

    .borrows-filter-form {
        padding: 18px 22px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
        align-items: center;
    }

    .borrows-filter-form .btn {
        height: 42px;
    }

    .borrows-table-card {
        border: 1px solid rgba(148, 163, 184, 0.2);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
    }

    .borrows-table thead th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        background: #f8fafc;
    }

    .borrows-table tbody tr {
        transition: background 0.2s ease;
    }

    .borrows-table tbody tr:hover {
        background: #f8fafc;
    }

    .table-light td {
        background: #f1f5f9 !important;
    }

    .toggle-items {
        transition: all 0.3s ease;
        position: relative;
    }
    
    .toggle-items i {
        transition: transform 0.3s ease;
    }
    
    .toggle-items.active {
        background-color: #0ea5e9 !important;
        border-color: #0ea5e9 !important;
    }
    
    .toggle-items.active i {
        transform: rotate(180deg);
    }

    .items-detail-row {
        transition: all 0.3s ease;
    }

    .items-detail-row.show {
        animation: slideInDown 0.3s ease-out;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .items-detail-row .table-hover tbody tr:hover {
        background-color: #f0f8ff;
    }

    .items-detail-row .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        min-width: 32px;
        height: 32px;
    }

    .items-detail-row form {
        margin: 0;
    }

    .items-detail-row [title]:hover::after {
        content: attr(title);
        position: absolute;
        background: rgba(15, 23, 42, 0.9);
        color: white;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        margin-top: 40px;
        margin-left: -50px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing toggle buttons...');
    
    // Lấy tất cả các nút toggle
    const toggleButtons = document.querySelectorAll('.toggle-items');
    console.log('Found ' + toggleButtons.length + ' toggle buttons');
    
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const borrowId = this.getAttribute('data-borrow-id');
            const itemsRow = document.getElementById('items-row-' + borrowId);
            const icon = this.querySelector('i');
            
            console.log('Toggle clicked for borrow ID:', borrowId);
            console.log('Items row:', itemsRow);
            
            if (itemsRow) {
                if (itemsRow.style.display === 'none' || itemsRow.style.display === '') {
                    // Hiển thị
                    itemsRow.style.display = 'table-row';
                    itemsRow.classList.add('show');
                    this.classList.add('active');
                    console.log('Showing items for borrow ID:', borrowId);
                } else {
                    // Ẩn
                    itemsRow.style.display = 'none';
                    itemsRow.classList.remove('show');
                    this.classList.remove('active');
                    console.log('Hiding items for borrow ID:', borrowId);
                }
            } else {
                console.error('Items row not found for borrow ID:', borrowId);
            }
        });
    });
    
    // Auto refresh page after approval (optional)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('approved')) {
        setTimeout(function() {
            window.location.href = window.location.pathname;
        }, 2000);
    }
});
</script>
@endpush
