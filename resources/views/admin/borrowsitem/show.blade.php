@extends('layouts.admin')

@section('content')
<div class="container my-5">
    <div class="card shadow-lg border-0">
        <!-- Header -->
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0 fw-bold">chi tiết đơn mượn</h3>
            <span>#{{ $borrowItem->id }}</span>
        </div>

        <div class="card-body p-4">
            <!-- Library Info -->
            <div class="mb-4 d-flex justify-content-between">
                <div class="text-end">
                    <p><strong>Ngày mượn:</strong> {{ $borrowItem->ngay_muon->format('d/m/Y') }}</p>
                    <p><strong>Ngày hẹn trả:</strong> {{ $borrowItem->ngay_hen_tra->format('d/m/Y') }}</p>
                </div>
            </div>

            <!-- Book Info -->
            <div class="mb-4 border p-3 rounded shadow-sm bg-light">
                <h5 class="fw-bold mb-3 text-primary">Thông tin sách</h5>
                <p><strong>Tên sách:</strong> {{ $borrowItem->book->ten_sach }}</p>
                <p><strong>Tác giả:</strong> {{ $borrowItem->book->tac_gia }}</p>
                <p><strong>Năm xuất bản:</strong> {{ $borrowItem->book->nam_xuat_ban }}</p>
            </div>

            <!-- Cost Table -->
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Mục</th>
                        <th class="text-end">Số tiền (₫)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $index = 1; @endphp
                    @if($borrowItem->borrow && is_null($borrowItem->borrow->reader_id))
                    <tr>
                        <th>{{ $index++ }}</th>
                        <th>Tiền thuê</th>
                        <th class="text-end">{{ number_format($borrowItem->tien_thue) }}</th>
                    </tr>
                    @endif
                </tbody>
                
            </table>
            <br><br>
<tfoot class="table-light fw-bold fs-5">
                    <tr>
                        <td colspan="2" class="text-end">Tổng cộng</td>
                        <td class="text-end text-success">{{ number_format($borrowItem->tien_thue) }}₫</td>
                    </tr>
                </tfoot>
            <!-- Status Badges -->
            <div class="mt-4 d-flex justify-content-between flex-wrap">
                <div>
                    <p><strong>Trạng thái mượn:</strong> 
                        <span class="badge bg-gradient-info text-white px-3 py-2">{{ $borrowItem->trang_thai }}</span>
                    </p>
                    <p><strong>Ngày mượn:</strong> {{ $borrowItem->ngay_muon->format('d/m/Y') }}</p>
                    <p><strong>Ngày hẹn trả:</strong> {{ $borrowItem->ngay_hen_tra->format('d/m/Y') }}</p>
                </div>
                <div class="text-end">
                    <p><strong>Số ngày:</strong>
                        @if($borrowItem->days_remaining > 0)
                            <span class="text-success">quá hạn {{ $borrowItem->days_remaining }} ngày</span>
                        @elseif($borrowItem->days_remaining == 0)
                            <span class="text-warning">Hết hạn hôm nay</span>
                        @else
                            <span class="text-danger">còn {{ abs($borrowItem->days_remaining) }} ngày</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="mt-5 text-muted border-top pt-3">
                <p class="mb-0 fst-italic">Lưu ý: Vui lòng trả sách đúng hạn để tránh phí quá hạn. Xin cảm ơn!</p>
            </div>
        </div>
    </div>
</div>

@endsection
