@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list"></i>
                        Chi tiết trạng thái đơn mượn #{{ $borrow->id }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.borrows.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Thông tin cơ bản --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-user"></i> Thông tin người mượn</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="150">Tên:</th>
                                    <td>{{ $borrow->ten_nguoi_muon }}</td>
                                </tr>
                                <tr>
                                    <th>Số điện thoại:</th>
                                    <td>{{ $borrow->so_dien_thoai }}</td>
                                </tr>
                                <tr>
                                    <th>Địa chỉ:</th>
                                    <td>{{ $borrow->so_nha }}, {{ $borrow->xa }}, {{ $borrow->huyen }}, {{ $borrow->tinh_thanh }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-box"></i> Thông tin đơn hàng</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="150">Mã đơn:</th>
                                    <td>#{{ $borrow->id }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày tạo:</th>
                                    <td>{{ $borrow->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Tổng tiền:</th>
                                    <td><strong class="text-success">{{ number_format($borrow->tong_tien) }} VNĐ</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Trạng thái hiện tại --}}
                    <div class="alert alert-{{ $borrow->getStatusColor() }} mb-4">
                        <h5 class="mb-2">
                            <i class="{{ $borrow->getStatusIcon() }}"></i>
                            Trạng thái hiện tại: <strong>{{ $borrow->getStatusLabel() }}</strong>
                        </h5>
                        <p class="mb-0">{{ $borrow->getStatusDescription() }}</p>
                    </div>

                    {{-- Timeline trạng thái --}}
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-history"></i> Lịch sử trạng thái</h5>
                            <div class="timeline">
                                @foreach($allStatuses as $statusKey => $statusInfo)
                                    @php
                                        $isActive = $borrow->trang_thai_chi_tiet === $statusKey;
                                        $isPast = $statusInfo['step'] < ($allStatuses[$borrow->trang_thai_chi_tiet]['step'] ?? 0);
                                        $isFuture = $statusInfo['step'] > ($allStatuses[$borrow->trang_thai_chi_tiet]['step'] ?? 0);
                                    @endphp
                                    
                                    <div class="timeline-item {{ $isActive ? 'active' : '' }} {{ $isPast ? 'completed' : '' }} {{ $isFuture ? 'future' : '' }}">
                                        <div class="timeline-marker">
                                            <i class="{{ $statusInfo['icon'] }}"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">
                                                <span class="badge badge-{{ $statusInfo['color'] }}">
                                                    Bước {{ $statusInfo['step'] }}
                                                </span>
                                                {{ $statusInfo['label'] }}
                                            </h6>
                                            <p class="text-muted small mb-1">{{ $statusInfo['description'] }}</p>
                                            
                                            {{-- Hiển thị timestamp nếu đã qua bước này --}}
                                            @if($isPast || $isActive)
                                                @php
                                                    $timestampField = match($statusKey) {
                                                        'dang_chuan_bi_sach' => 'ngay_chuan_bi',
                                                        'cho_ban_giao_van_chuyen' => 'ngay_dong_goi_xong',
                                                        'dang_giao_hang' => 'ngay_bat_dau_giao',
                                                        'giao_hang_thanh_cong' => 'ngay_giao_thanh_cong',
                                                        'giao_hang_that_bai' => 'ngay_that_bai_giao_hang',
                                                        'da_muon_dang_luu_hanh' => 'ngay_bat_dau_luu_hanh',
                                                        'cho_tra_sach' => 'ngay_yeu_cau_tra_sach',
                                                        'dang_van_chuyen_tra_ve' => 'ngay_bat_dau_tra',
                                                        'da_nhan_va_kiem_tra' => 'ngay_kiem_tra',
                                                        'hoan_tat_don_hang' => 'ngay_hoan_coc',
                                                        default => null
                                                    };
                                                    $timestamp = $timestampField && $borrow->$timestampField ? $borrow->$timestampField : null;
                                                @endphp
                                                
                                                @if($timestamp)
                                                    <small class="text-success">
                                                        <i class="far fa-clock"></i>
                                                        {{ \Carbon\Carbon::parse($timestamp)->format('d/m/Y H:i') }}
                                                    </small>
                                                @endif
                                            @endif
                                            
                                            {{-- Nút hành động cho trạng thái hiện tại --}}
                                            @if($isActive && !empty($statusInfo['next_statuses']))
                                                <div class="mt-2">
                                                    @foreach($statusInfo['next_statuses'] as $nextStatus)
                                                        @php
                                                            // Ẩn "giao_hang_thanh_cong" và "giao_hang_that_bai" khi đang ở "dang_giao_hang"
                                                            // Vì thành công hay thất bại phụ thuộc vào khách hàng xác nhận
                                                            if ($borrow->trang_thai_chi_tiet === 'dang_giao_hang' && 
                                                                ($nextStatus === 'giao_hang_thanh_cong' || $nextStatus === 'giao_hang_that_bai')) {
                                                                continue; // Bỏ qua, không hiển thị nút này
                                                            }
                                                            
                                                            // Ẩn "dang_van_chuyen_tra_ve" khi đang ở "cho_tra_sach"
                                                            // Vì chỉ khi khách hàng xác nhận hoàn trả sách thì mới chuyển sang trạng thái này
                                                            if ($borrow->trang_thai_chi_tiet === 'cho_tra_sach' && $nextStatus === 'dang_van_chuyen_tra_ve') {
                                                                continue; // Bỏ qua, không hiển thị nút này
                                                            }
                                                            
                                                            $nextStatusInfo = $allStatuses[$nextStatus] ?? null;
                                                            if (!$nextStatusInfo) continue;
                                                            
                                                            $routeName = match($nextStatus) {
                                                                'dang_chuan_bi_sach' => 'admin.borrows.confirm-order',
                                                                'cho_ban_giao_van_chuyen' => 'admin.borrows.complete-packaging',
                                                                'dang_giao_hang' => 'admin.borrows.handover-shipper',
                                                                'giao_hang_thanh_cong' => 'admin.borrows.confirm-delivery-success',
                                                                'giao_hang_that_bai' => 'admin.borrows.report-delivery-failed',
                                                                'cho_tra_sach' => 'admin.borrows.request-return',
                                                                'dang_van_chuyen_tra_ve' => 'admin.borrows.confirm-return-shipping',
                                                                'da_nhan_va_kiem_tra' => 'admin.borrows.confirm-receive-check',
                                                                'hoan_tat_don_hang' => 'borrows.complete-order',
                                                                'da_muon_dang_luu_hanh' => 'admin.borrows.request-return',
                                                                default => null
                                                            };
                                                        @endphp
                                                        
                                                        @if($routeName)
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-{{ $nextStatusInfo['color'] }} mr-1 transition-btn"
                                                                    data-route="{{ route($routeName, $borrow->id) }}"
                                                                    data-status="{{ $nextStatus }}">
                                                                <i class="{{ $nextStatusInfo['icon'] }}"></i>
                                                                {{ $nextStatusInfo['label'] }}
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Thông tin vận chuyển --}}
                    @if($borrow->ma_van_don_di || $borrow->ma_van_don_ve || $borrow->don_vi_van_chuyen)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-truck"></i> Thông tin vận chuyển</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    @if($borrow->don_vi_van_chuyen)
                                        <tr>
                                            <th width="200">Đơn vị vận chuyển:</th>
                                            <td><strong>{{ $borrow->don_vi_van_chuyen }}</strong></td>
                                        </tr>
                                    @endif
                                    @if($borrow->ma_van_don_di)
                                        <tr>
                                            <th>Mã vận đơn đi:</th>
                                            <td><code>{{ $borrow->ma_van_don_di }}</code></td>
                                        </tr>
                                    @endif
                                    @if($borrow->ma_van_don_ve)
                                        <tr>
                                            <th>Mã vận đơn về:</th>
                                            <td><code>{{ $borrow->ma_van_don_ve }}</code></td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Thông tin kiểm tra sách --}}
                    @if($borrow->tinh_trang_sach)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-search"></i> Kiểm tra sách trả về</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th width="200">Tình trạng sách (Khách báo):</th>
                                                <td>
                                                    @php
                                                        $conditionInfo = config('borrow_status.book_conditions.' . $borrow->tinh_trang_sach);
                                                    @endphp
                                                    <span class="badge badge-{{ $conditionInfo['penalty_rate'] > 0 ? 'warning' : 'success' }}">
                                                        {{ $conditionInfo['label'] ?? $borrow->tinh_trang_sach }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @if($borrow->phi_hong_sach > 0)
                                                <tr>
                                                    <th>Phí hỏng sách:</th>
                                                    <td class="text-danger"><strong>{{ number_format($borrow->phi_hong_sach) }} VNĐ</strong></td>
                                                </tr>
                                            @endif
                                            @if($borrow->tien_coc_hoan_tra !== null)
                                                <tr>
                                                    <th>Tiền cọc hoàn trả:</th>
                                                    <td class="text-success"><strong>{{ number_format($borrow->tien_coc_hoan_tra) }} VNĐ</strong></td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-images"></i> Ảnh bằng chứng từ khách hàng:</h6>
                                        @if($borrow->anh_hoan_tra && count($borrow->anh_hoan_tra) > 0)
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($borrow->anh_hoan_tra as $img)
                                                    <a href="{{ asset('storage/' . $img) }}" target="_blank" class="m-1">
                                                        <img src="{{ asset('storage/' . $img) }}" 
                                                             class="img-thumbnail" 
                                                             style="width: 100px; height: 100px; object-fit: cover;"
                                                             alt="Evidence">
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted small">Không có ảnh bằng chứng.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Danh sách sách --}}
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-book"></i> Danh sách sách mượn</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên sách</th>
                                        <th>Ngày mượn</th>
                                        <th>Ngày hẹn trả</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($borrow->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->book->ten_sach ?? 'N/A' }}</td>
                                            <td>{{ $item->ngay_muon ? \Carbon\Carbon::parse($item->ngay_muon)->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $item->ngay_hen_tra ? \Carbon\Carbon::parse($item->ngay_hen_tra)->format('d/m/Y') : '-' }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ $item->trang_thai }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal chuyển trạng thái --}}
<div class="modal fade" id="transitionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="transitionForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Chuyển trạng thái</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group" id="ma_van_don_group" style="display:none;">
                        <label>Mã vận đơn</label>
                        <input type="text" name="ma_van_don_di" class="form-control" placeholder="Nhập mã vận đơn">
                    </div>
                    <div class="form-group" id="don_vi_van_chuyen_group" style="display:none;">
                        <label>Đơn vị vận chuyển</label>
                        <input type="text" name="don_vi_van_chuyen" class="form-control" placeholder="VD: GHN, GHTK, ViettelPost...">
                    </div>
                    <div class="form-group" id="tinh_trang_sach_group" style="display:none;">
                        <label>Tình trạng sách <span class="text-danger">*</span></label>
                        <select name="tinh_trang_sach" id="tinh_trang_sach_select" class="form-control">
                            <option value="">-- Chọn tình trạng --</option>
                            <option value="binh_thuong">Bình thường (0% phí)</option>
                            <option value="hong_nhe">Hỏng nhẹ (20% phí)</option>
                            <option value="hong_nang">Hỏng nặng (50% phí)</option>
                            <option value="mat_sach">Mất sách (100% phí)</option>
                        </select>
                    </div>
                    <div class="form-group" id="phi_hong_sach_group" style="display:none;">
                        <label>Phí hỏng sách (VNĐ)</label>
                        <input type="number" name="phi_hong_sach" class="form-control" placeholder="Để trống để tự động tính theo %">
                        <small class="text-muted">Mặc định sẽ tính theo % giá trị sách nếu để trống.</small>
                    </div>
                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="ghi_chu" class="form-control" rows="3" placeholder="Nhập ghi chú..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -25px;
    top: 30px;
    width: 2px;
    height: calc(100% - 30px);
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.timeline-item.completed .timeline-marker {
    background: #28a745;
    color: white;
}

.timeline-item.active .timeline-marker {
    background: #007bff;
    color: white;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
}

.timeline-item.future .timeline-marker {
    background: #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}

.timeline-item.active .timeline-content {
    background: #e7f3ff;
    border-left: 3px solid #007bff;
}

.timeline-item.completed .timeline-content {
    opacity: 0.8;
}
</style>

<script>
$(document).ready(function() {
    $('.transition-btn').click(function() {
        var route = $(this).data('route');
        var status = $(this).data('status');
        
        $('#transitionForm').attr('action', route);
        
        // Ẩn tất cả các trường đặc biệt
        $('#ma_van_don_group, #don_vi_van_chuyen_group, #tinh_trang_sach_group').hide();
        
        // Hiển thị trường cần thiết theo trạng thái
        if (status === 'cho_ban_giao_van_chuyen') {
            $('#ma_van_don_group, #don_vi_van_chuyen_group').show();
        } else if (status === 'dang_van_chuyen_tra_ve') {
            $('#ma_van_don_group').find('input').attr('name', 'ma_van_don_ve');
            $('#ma_van_don_group').show();
        } else if (status === 'da_nhan_va_kiem_tra') {
            $('#tinh_trang_sach_group, #phi_hong_sach_group').show();
            $('#tinh_trang_sach_group select').prop('required', true);
            
            // Set giá trị mặc định nếu khách đã báo trước
            var currentCondition = "{{ $borrow->tinh_trang_sach }}";
            if (currentCondition) {
                $('#tinh_trang_sach_select').val(currentCondition);
            }
        }
        
        $('#transitionModal').modal('show');
    });
});
</script>
@endsection

