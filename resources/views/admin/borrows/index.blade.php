@extends('layouts.admin')

@section('title', 'Quản Lý Mượn/Trả Sách - WAKA Admin')

@section('content')
<!-- Page Header -->
<div class="page-header">
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
<div class="stats-grid" style="margin-bottom: 25px;">
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
            <div class="stat-title">Đã hủy</div>
            <div class="stat-icon secondary">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="stat-value">{{ $stats['huy'] }}</div>
        <div class="stat-label">Đơn đã hủy</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Bị hỏng</div>
            <div class="stat-icon" style="background: rgba(108, 117, 125, 0.15); color: #6c757d;">
                <i class="fas fa-tools"></i>
            </div>
        </div>
        <div class="stat-value">{{ $stats['hong'] }}</div>
        <div class="stat-label">Sách bị hỏng</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Bị mất</div>
            <div class="stat-icon" style="background: rgba(233, 62, 140, 0.15); color: #e83e8c;">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="stat-value">{{ $stats['mat_sach'] }}</div>
        <div class="stat-label">Sách bị mất</div>
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
<div class="card" style="margin-bottom: 25px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter"></i>
            Tìm kiếm & Lọc
        </h3>
    </div>
    <form action="{{ route('admin.borrows.index') }}" method="GET" style="padding: 25px; display: flex; gap: 15px; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 250px;">
            <input type="text" 
                   name="keyword" 
                   value="{{ request('keyword') }}" 
                   class="form-control" 
                   placeholder="Tìm theo tên độc giả hoặc tên sách...">
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
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Danh sách phiếu mượn
        </h3>
        <span class="badge badge-info">Tổng: {{ $borrows->total() }} phiếu</span>
    </div>
    
    @if($borrows->count() > 0)
    <div class="table-responsive">
            <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">Mã phiếu</th>
                    <th>Độc giả</th>
                    <th>Tên khách hàng</th>
                    <th style="width: 100px;">Tiền thuê</th>
                    <th style="min-width: 200px;">Trạng thái Items</th>
                    <th style="width: 120px;">Tổng tiền</th>
                    <th style="width: 150px;">Phương thức TT</th>
                    <th style="width: 100px;">Chi tiết</th>
                    <th style="width: 180px;">Hành động</th>
                </tr>
            </thead>
         <tbody>
@foreach($borrows as $borrow)
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
        
        // Tổng tiền = tiền thuê (voucher column removed)
        $tongTien = $tienThue;
    @endphp
    
    <td>
        {{ number_format($tienThue) }}₫
    </td>
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
    // Nhóm các BorrowItem theo trạng thái và đếm số lượng
    $statuses = $borrow->items->groupBy('trang_thai')->map->count();
@endphp

@foreach($statuses as $status => $count)
    @php
        switch($status) {
            case 'Dang muon': $text = 'Đang mượn'; $color = '#007bff'; break;
            case 'Qua han': $text = 'Quá hạn'; $color = 'red'; break;
            case 'Da tra': $text = 'Đã trả'; $color = 'green'; break;
            case 'Cho duyet': $text = 'Chờ duyệt'; $color = 'orange'; break;
            case 'Chua nhan': $text = 'Chưa nhận'; $color = '#17a2b8'; break;
            case 'Mat sach': $text = 'Mất sách'; $color = 'darkorange'; break;
            case 'Huy': $text = 'Đã hủy'; $color = '#6c757d'; break;
            default: $text = $status; $color = 'gray';
        }
    @endphp
    <div style="color: {{ $color }};">
        {{ $text }} ({{ $count }} sách)
    </div>
@endforeach

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
            {{-- Hiển thị dropdown chọn phương thức khi chưa thanh toán --}}
            <select id="payment_method_{{ $borrow->id }}" class="form-select form-select-sm" style="font-size:12px;" required>
                <option value="online" selected>Quét mã</option>
                <option value="offline">Tiền mặt</option>
            </select>
            <div style="font-size: 11px; margin-top: 3px;">
                <span class="text-warning">Chờ thanh toán</span>
            </div>
        @elseif($latestPayment)
            {{-- Hiển thị phương thức và trạng thái khi đã có thanh toán --}}
            @if($latestPayment->payment_method === 'offline')
                <span class="badge badge-warning">
                    <i class="fas fa-money-bill-wave"></i> Tiền mặt
                </span>
            @elseif($latestPayment->payment_method === 'online')
                <span class="badge badge-info">
                    <i class="fas fa-qrcode"></i> Quét mã
                </span>
            @endif
            <div style="font-size: 11px; margin-top: 3px;">
                @if($latestPayment->payment_status === 'pending')
                    <span class="text-warning">Chờ thanh toán</span>
                @elseif($latestPayment->payment_status === 'success')
                    <span class="text-success">Đã thanh toán</span>
                @elseif($latestPayment->payment_status === 'failed')
                    <span class="text-danger">Thất bại</span>
                @endif
            </div>
        @else
            <span class="text-muted">-</span>
        @endif
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
        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
            {{-- @if($borrow->trang_thai == 'Dang muon')
                <a href="{{ route('admin.borrows.return', $borrow->id) }}" 
                   class="btn btn-sm btn-success" 
                   onclick="return confirm('Xác nhận trả sách?')"
                   title="Trả sách">
                    <i class="fas fa-undo"></i>
                </a>
                {{-- @if($borrow->canExtend())
                    <button type="button" 
                            class="btn btn-sm btn-warning" 
                            data-bs-toggle="modal" 
                            data-bs-target="#extendModal{{ $borrow->id }}"
                            title="Gia hạn">
                        <i class="fas fa-clock"></i>
                    </button>
                @endif 
            @endif --}}
            <a href="{{ route('admin.borrows.show', $borrow->id) }}" 
               class="btn btn-sm btn-secondary"
               title="Xem chi tiết">
                <i class="fas fa-eye"></i>
            </a>
            <a href="{{ route('admin.borrows.edit', $borrow->id) }}" 
               class="btn btn-sm btn-warning"
               title="Chỉnh sửa">
                <i class="fas fa-edit"></i>
            </a>
            
@php
    $allChuaNhan = $borrow->items->isNotEmpty() && $borrow->items->every(fn($item) => $item->trang_thai === 'Chua nhan');
    $hasChoDuyet = $borrow->items->isNotEmpty() && $borrow->items->contains(fn($item) => $item->trang_thai === 'Cho duyet');
    $hasDangMuon = $borrow->items->isNotEmpty() && $borrow->items->contains(fn($item) => $item->trang_thai === 'Dang muon');
    
    // Kiểm tra trạng thái thanh toán
    $latestPendingPayment = $borrow->payments()->where('payment_status', 'pending')->latest()->first();
    $latestSuccessPayment = $borrow->payments()->where('payment_status', 'success')->latest()->first();
    $hasUnpaidPayment = (bool) $latestPendingPayment;
    $hasPaidPayment = (bool) $latestSuccessPayment;
@endphp

{{-- Nút thu tiền: hiện khi có payment pending --}}
@if($hasUnpaidPayment && auth()->check() && auth()->user()->can('edit-borrows'))
    <form action="{{ route('admin.borrows.confirm-cash-payment', $borrow->id) }}" method="POST" style="display:inline-block;">
        @csrf
        <input type="hidden" name="payment_method" id="payment_method_input_{{ $borrow->id }}" value="online">
        <button type="submit" class="btn btn-sm btn-success mb-0" title="Xác nhận đã thu tiền" 
                onclick="document.getElementById('payment_method_input_{{ $borrow->id }}').value = document.getElementById('payment_method_{{ $borrow->id }}').value; return confirm('Xác nhận đã thu tiền từ khách hàng?')">
            <i class="fas fa-money-bill-wave"></i> Thu tiền
        </button>
    </form>
@endif

{{-- Nút duyệt: hiện khi chờ duyệt và chưa có nút thanh toán --}}
@if($hasChoDuyet && !$hasUnpaidPayment && auth()->check() && auth()->user()->can('edit-borrows'))
    <form action="{{ route('admin.borrows.approve', $borrow->id) }}" method="POST" style="display:inline-block;">
        @csrf
        <button type="submit" class="btn btn-sm btn-success mb-0" title="Duyệt phiếu mượn" onclick="return confirm('Xác nhận duyệt phiếu mượn này?')">
            <i class="fas fa-check-circle"></i>
        </button>
    </form>
@endif

{{-- Nút trả sách: hiện khi đã thanh toán thành công và có sách đang mượn --}}
@if($hasPaidPayment && $hasDangMuon && auth()->check() && auth()->user()->can('return-books'))
    <a href="{{ route('admin.borrows.show', $borrow->id) }}" 
       class="btn btn-sm btn-info mb-0"
       title="Xem chi tiết để trả sách">
        <i class="fas fa-undo"></i> Trả sách
    </a>
@endif

{{-- Nút gia hạn: hiện khi đã thanh toán và có sách đang mượn --}}
@if($hasPaidPayment && $hasDangMuon && auth()->check() && auth()->user()->can('edit-borrows'))
    <form action="{{ route('admin.borrows.extend', $borrow->id) }}" method="POST" style="display:inline-block;">
        @csrf
        <button type="submit" class="btn btn-sm btn-warning mb-0" title="Gia hạn 5 ngày (+25,000₫)" onclick="return confirm('Xác nhận gia hạn 5 ngày và cộng 25,000₫ tiền thuê?')">
            <i class="fas fa-clock"></i> Gia hạn
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
                                <th style="width: 130px;">Trạng thái</th>
                                <th style="width: 150px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrow->items as $item)
                            <tr>
                                <td><strong>{{ $item->id }}</strong></td>
                                <td>
                                    <div style="font-weight: 500;">{{ $item->book->ten_sach ?? 'N/A' }}</div>
                                    @if($item->book && $item->book->hinh_anh)
                                    <small class="text-muted">
                                        <i class="fas fa-image"></i> Có ảnh
                                    </small>
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
                                                $text = 'Quá hạn'; 
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
                                    <div style="display: flex; gap: 5px; justify-content: center; flex-wrap: wrap;">
                                        @if($item->trang_thai == 'Cho duyet')
                                            {{-- Nút duyệt cho sách chờ duyệt --}}
                                            {{-- <form action="{{ route('admin.borrowitems.approve', $item->id) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Xác nhận duyệt sách: {{ $item->book->ten_sach ?? 'N/A' }}?')">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-success"
                                                        title="Duyệt sách này">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>
                                            </form> --}}
                                        @endif
                                        
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

<!-- Extension Modals -->
    {{-- @foreach($borrows as $borrow)
        @if($borrow->canExtend())
    <div class="modal fade" id="extendModal{{ $borrow->id }}" tabindex="-1" style="display: none;">
        <div class="modal-dialog" style="max-width: 500px; margin: 100px auto;">
            <div class="modal-content" style="background: var(--background-card); border: 1px solid rgba(0, 255, 153, 0.2); border-radius: 15px; color: var(--text-primary);">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding: 20px 25px;">
                    <h5 class="modal-title" style="color: var(--primary-color); font-weight: 600;">
                        <i class="fas fa-clock"></i>
                        Gia hạn mượn sách
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="background: transparent; border: none; color: #888; font-size: 24px; cursor: pointer; opacity: 0.6; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">
                        <i class="fas fa-times"></i>
                    </button>
                    </div>
                    <form action="{{ route('admin.borrows.extend', $borrow->id) }}" method="POST">
                        @csrf
                    <div class="modal-body" style="padding: 25px;">
                        <div class="form-group">
                                <label class="form-label">Độc giả:</label>
                            <div style="padding: 10px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; margin-top: 5px; color: var(--text-primary);">
                                {{ $borrow->reader->ho_ten }}
                            </div>
                        </div>
                        <div class="form-group">
                                <label class="form-label">Sách:</label>
                            <div style="padding: 10px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; margin-top: 5px; color: var(--text-primary);">
                                {{ $borrow->book->ten_sach }}
                            </div>
                        </div>
                        <div class="form-group">
                                <label class="form-label">Hạn trả hiện tại:</label>
                            <div style="padding: 10px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; margin-top: 5px; color: var(--primary-color);">
                                <i class="fas fa-calendar"></i>
                                {{ $borrow->ngay_hen_tra->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="form-group">
                                <label class="form-label">Số ngày gia hạn:</label>
                            <select name="days" class="form-select" required style="margin-top: 5px;">
                                    <option value="7">7 ngày</option>
                                    <option value="14">14 ngày</option>
                                </select>
                            </div>
                        <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Lần gia hạn:</label>
                            <div style="padding: 10px; background: rgba(0, 255, 153, 0.1); border-radius: 8px; margin-top: 5px; color: var(--primary-color); font-weight: 600;">
                                {{ $borrow->so_lan_gia_han + 1 }}/2
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.1); padding: 20px 25px; display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i>
                            Xác nhận gia hạn
                        </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach --}}
@endsection

@push('styles')
<style>
    .modal.fade {
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
    }
    
    .modal-dialog {
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Styles for toggle button */
    .toggle-items {
        transition: all 0.3s ease;
        position: relative;
    }
    
    .toggle-items i {
        transition: transform 0.3s ease;
    }
    
    .toggle-items.active {
        background-color: #0056b3 !important;
        border-color: #0056b3 !important;
    }
    
    .toggle-items.active i {
        transform: rotate(180deg);
    }

    /* Animation for items detail row */
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

    /* Table hover effect */
    .items-detail-row .table-hover tbody tr:hover {
        background-color: #f0f8ff;
    }

    /* Action buttons styling */
    .items-detail-row .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        min-width: 32px;
        height: 32px;
    }

    .items-detail-row form {
        margin: 0;
    }

    /* Tooltip effect for action buttons */
    .items-detail-row [title]:hover::after {
        content: attr(title);
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
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
