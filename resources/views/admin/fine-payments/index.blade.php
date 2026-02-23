@extends('layouts.admin')

@section('title', 'Thanh toán phạt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-money-check-alt me-2"></i>Thanh toán phạt</h3>
        <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-undo me-1"></i> Trả sách
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Chọn khách</strong>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.fine-payments.index') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Reader ID</label>
                    <input name="reader_id" value="{{ request('reader_id') }}" class="form-control" placeholder="Nhập reader_id (từ màn Trả sách sẽ tự chuyển qua)" />
                    <div class="text-muted small mt-1">Hiện màn này lọc theo reader_id (từ màn Trả sách chuyển qua tự có).</div>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                </div>
                <div class="col-md-3">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('admin.fine-payments.index') }}">Bỏ lọc</a>
                </div>
            </form>
        </div>
    </div>

    @php
        $totalPending = $fines->sum('amount');
    @endphp

    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <div>
                <strong>Danh sách phạt pending</strong>
                @if($reader)
                    <span class="ms-2">— {{ $reader->ho_ten }} (#{{ $reader->id }})</span>
                @endif
            </div>
            <div class="fw-bold">Tổng: {{ number_format($totalPending) }}₫</div>
        </div>
        <div class="card-body">
            @if($fines->count() === 0)
                <div class="alert alert-info mb-0">Không có khoản phạt pending.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Phiếu</th>
                                <th>Sách</th>
                                <th>Loại phạt</th>
                                <th>Số tiền</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fines as $fine)
                                <tr>
                                    <td>#{{ $fine->borrow_id }}</td>
                                    <td>{{ optional(optional($fine->borrowItem)->book)->ten_sach ?? '---' }}</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $fine->type }}</span>
                                    </td>
                                    <td class="text-danger fw-bold">{{ number_format($fine->amount) }}₫</td>
                                    <td>{{ $fine->created_at ? $fine->created_at->format('d/m/Y H:i') : '---' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    @if($reader)
                        <form action="{{ route('admin.fine-payments.pay-cash', $reader->id) }}" method="POST" onsubmit="return confirm('Xác nhận khách đã thanh toán {{ number_format($totalPending) }}₫ tiền mặt?')">
                            @csrf
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-money-bill-wave me-1"></i> Thu tiền mặt
                            </button>
                        </form>

                        <button class="btn btn-danger" type="button" onclick="payReaderFineWithMomo()">
                            <i class="fas fa-mobile-alt me-1"></i> Thanh toán MoMo
                        </button>
                    @else
                        <div class="text-muted">Vui lòng chọn reader_id để thanh toán.</div>
                    @endif
                </div>

                <div class="mt-3">
                    {{ $fines->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL MOMO QR (PHẠT THEO ĐỘC GIẢ) -->
<div class="modal fade" id="readerFineMomoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Thanh toán phạt qua MoMo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="fw-bold">Quét mã QR MoMo để thanh toán</p>
                <img id="readerFineMomoQr" class="img-fluid mb-3" style="max-width:240px" />
                <div>
                    <a id="readerFineMomoPayUrl" href="#" target="_blank" class="btn btn-danger">
                        Mở MoMo
                    </a>
                </div>
                <p class="text-muted small mt-2">Sau khi thanh toán xong, hệ thống sẽ tự cập nhật khi nhận IPN từ MoMo.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function payReaderFineWithMomo() {
        const readerId = "{{ $reader?->id }}";
        if (!readerId) {
            alert('Vui lòng chọn khách trước');
            return;
        }

        try {
            const res = await fetch("{{ $reader ? route('admin.fine-payments.momo.create', $reader->id) : '#' }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            });

            const data = await res.json();
            if (!data.success || !data.payUrl) {
                alert(data.message || 'Không tạo được link thanh toán MoMo');
                return;
            }

            document.getElementById('readerFineMomoQr').src =
                'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' +
                encodeURIComponent(data.payUrl);
            document.getElementById('readerFineMomoPayUrl').href = data.payUrl;

            new bootstrap.Modal(document.getElementById('readerFineMomoModal')).show();
        } catch (e) {
            console.error(e);
            alert('Có lỗi khi tạo thanh toán MoMo');
        }
    }
</script>
@endpush
