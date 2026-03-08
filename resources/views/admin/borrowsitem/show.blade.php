@extends('layouts.admin')

@section('content')
<div class="container py-4">
    @php
        $book = $borrowItem->book ?? optional($borrowItem->inventory)->book;
        $totalAmount = (float) ($displayTienThue ?? 0) + (float) ($displayTienPhat ?? 0);

        if (is_null($borrowItem->days_remaining)) {
            $deadlineText = 'Không xác định';
            $deadlineClass = 'secondary';
        } elseif ($borrowItem->days_remaining > 0) {
            $deadlineText = 'Còn ' . $borrowItem->days_remaining . ' ngày';
            $deadlineClass = 'success';
        } elseif ($borrowItem->days_remaining == 0) {
            $deadlineText = 'Hết hạn hôm nay';
            $deadlineClass = 'warning';
        } else {
            $deadlineText = 'Quá hạn ' . abs($borrowItem->days_remaining) . ' ngày';
            $deadlineClass = 'danger';
        }
    @endphp

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="mb-1 fw-bold">Chi tiết phiếu mượn #{{ $borrowItem->id }}</h4>
            <div class="text-muted small">Thông tin sách, trạng thái và chi phí</div>
        </div>
        <a href="{{ route('admin.borrows.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Ngày mượn</div>
                    <div class="fw-bold">{{ $borrowItem->ngay_muon ? $borrowItem->ngay_muon->format('d/m/Y') : '-' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Ngày hẹn trả</div>
                    <div class="fw-bold">{{ $borrowItem->ngay_hen_tra ? $borrowItem->ngay_hen_tra->format('d/m/Y') : '-' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Trạng thái</div>
                    <span class="badge bg-info text-white px-3 py-2 mt-1">{{ $borrowItem->trang_thai }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Theo dõi hạn trả</div>
                    <span class="badge bg-{{ $deadlineClass }} px-3 py-2 mt-1">{{ $deadlineText }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3">Thông tin sách</h5>
            @if($book)
                <div class="row g-2">
                    <div class="col-md-6"><strong>Tên sách:</strong> {{ $book->ten_sach ?? '-' }}</div>
                    <div class="col-md-3"><strong>Tác giả:</strong> {{ $book->tac_gia ?? '-' }}</div>
                    <div class="col-md-3"><strong>Năm xuất bản:</strong> {{ $book->nam_xuat_ban ?? '-' }}</div>
                </div>
            @else
                <div class="alert alert-warning mb-0">Không tìm thấy thông tin sách liên kết với phiếu mượn này.</div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3">Chi phí</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px;">#</th>
                            <th>Mục</th>
                            <th class="text-end" style="width: 220px;">Số tiền (₫)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $index = 1; @endphp
                        <tr>
                            <td>{{ $index++ }}</td>
                            <td>Tiền thuê</td>
                            <td class="text-end fw-semibold">{{ number_format((float) ($displayTienThue ?? 0)) }}</td>
                        </tr>
                        @if((float) ($displayTienPhat ?? 0) > 0)
                            <tr>
                                <td>{{ $index++ }}</td>
                                <td>Tiền phạt</td>
                                <td class="text-end fw-semibold text-danger">{{ number_format((float) ($displayTienPhat ?? 0)) }}</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="2" class="text-end fw-bold">Tổng cộng</td>
                            <td class="text-end fw-bold text-success">{{ number_format($totalAmount) }}₫</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-3">Lưu ý: Vui lòng trả sách đúng hạn để tránh phát sinh phí quá hạn.</div>
</div>

@endsection
