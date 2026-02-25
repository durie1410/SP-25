@extends('account._layout')

@section('content')
<!-- CSS Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- JS Bootstrap 5 (Popper + JS) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-4">
    <h3 class="mb-4">Lịch sử đơn mượn</h3>

    @foreach($borrows as $borrow)
    <div class="card mb-3 shadow-sm">
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-0"><strong>Đơn #{{ $borrow->id }}</strong> - {{ $borrow->created_at->format('d/m/Y H:i') }}</h6>
                </div>
                <div class="col-md-6 text-end">
                    <span class="badge bg-info">{{ $borrow->trang_thai }}</span>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Thông tin đơn mượn -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><small><strong>Tên người mượn:</strong> {{ $borrow->ten_nguoi_muon ?? '-' }}</small></p>
                    <p class="mb-1"><small><strong>Điện thoại:</strong> {{ $borrow->so_dien_thoai ?? '-' }}</small></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><small><strong>Ngày trả dự kiến:</strong> {{ $borrow->ngay_hen_tra ? \Carbon\Carbon::parse($borrow->ngay_hen_tra)->format('d/m/Y') : '-' }}</small></p>
                    <p class="mb-1"><small><strong>Tổng tiền:</strong> <span class="text-primary">{{ number_format($borrow->tong_tien, 0, ',', '.') }} VND</span></small></p>
                </div>
            </div>

            <!-- Danh sách sách trong đơn -->
            <h6><strong>Sách trong đơn:</strong></h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Tên sách</th>
                            <th>Tác giả</th>
                            <th>Tiền cọc</th>
                            <th>Tiền thuê</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($borrow->items as $item)
                        <tr>
                            <td>{{ $item->book->ten_sach ?? $item->book->title }}</td>
                            <td>{{ $item->book->author ?? '-' }}</td>
                            <td>{{ number_format($item->tien_coc, 0, ',', '.') }} VND</td>
                            <td>{{ number_format($item->tien_thue, 0, ',', '.') }} VND</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Không có sách trong đơn</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Trạng thái thanh toán -->
            @if($borrow->payments->isNotEmpty())
            <h6 class="mt-3"><strong>Trạng thái thanh toán:</strong></h6>
            <div class="alert alert-info mb-0">
                @php
                    $lastPayment = $borrow->payments->sortByDesc('created_at')->first();
                @endphp
                <small>
                    <strong>Trạng thái:</strong> 
                    @if($lastPayment->payment_status === 'success')
                        <span class="badge bg-success">✓ Đã thanh toán</span>
                    @elseif($lastPayment->payment_status === 'pending')
                        <span class="badge bg-warning">⏳ Chờ thanh toán</span>
                    @else
                        <span class="badge bg-danger">✗ {{ $lastPayment->payment_status }}</span>
                    @endif
                    <br>
                    <strong>Số tiền:</strong> {{ number_format($lastPayment->amount, 0, ',', '.') }} VND
                    <br>
                    <strong>Phương thức:</strong> {{ $lastPayment->payment_method === 'offline' ? 'Tiền mặt/Quét mã' : 'Online' }}
                </small>
            </div>
            @else
            <div class="alert alert-warning mb-0">
                <small>Chưa có thanh toán nào</small>
            </div>
            @endif
        </div>

        <div class="card-footer">
            <a href="{{ route('borrow.clientShow', $borrow->id) }}" class="btn btn-sm btn-primary">Chi tiết</a>
        </div>
    </div>
    @endforeach

    @if($borrows->isEmpty())
        <p class="text-center text-muted mt-4">Chưa có đơn mượn nào.</p>
    @else
        <div class="card shadow-sm mt-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h5>Tổng tiền:</h5>
                <h5 class="text-primary">{{ number_format($total, 0, ',', '.') }} VND</h5>
            </div>
        </div>
    @endif

    <!-- Phân trang -->
    <div class="mt-4">
        {{ $borrows->links() }}
    </div>
</div>
@endsection

