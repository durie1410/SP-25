@extends('layouts.admin')

@section('title', 'Trả sách theo khách')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-undo me-2"></i>Trả sách theo khách</h3>
        <a href="{{ route('admin.fine-payments.index') }}" class="btn btn-outline-danger">
            <i class="fas fa-exclamation-triangle me-1"></i> Thanh toán phạt
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Tìm khách theo tên</strong>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.returns.index') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Tên khách</label>
                    <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Nhập tên khách..." />
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fas fa-search me-1"></i> Tìm
                    </button>
                </div>
                @if(request('reader_id'))
                <div class="col-md-3">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('admin.returns.index') }}">
                        Xóa chọn
                    </a>
                </div>
                @endif
            </form>

            @if(!empty($readers) && count($readers) > 0)
                <hr>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Chọn</th>
                                <th>Họ tên</th>
                                <th>Mã thẻ</th>
                                <th>SĐT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readers as $r)
                                <tr>
                                    <td>
                                        <a class="btn btn-sm btn-success" href="{{ route('admin.returns.index', ['reader_id' => $r->id]) }}">
                                            Chọn
                                        </a>
                                    </td>
                                    <td>{{ $r->ho_ten }}</td>
                                    <td>{{ $r->so_the_doc_gia ?? '---' }}</td>
                                    <td>{{ $r->so_dien_thoai ?? '---' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif(request('search'))
                <div class="alert alert-warning mt-3 mb-0">Không tìm thấy khách phù hợp.</div>
            @endif
        </div>
    </div>

    @if($selectedReader)
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <strong>Khách:</strong> {{ $selectedReader->ho_ten }}
                <span class="text-muted">(#{{ $selectedReader->id }})</span>
            </div>
            <a href="{{ route('admin.fine-payments.index', ['reader_id' => $selectedReader->id]) }}" class="btn btn-sm btn-danger">
                <i class="fas fa-money-check-alt me-1"></i> Xem phạt của khách
            </a>
        </div>
        <div class="card-body">
            @if(empty($borrowItems) || count($borrowItems) === 0)
                <div class="alert alert-info mb-0">Khách hiện không có quyển nào đang mượn.</div>
            @else
                <form method="POST" action="{{ route('admin.returns.process') }}" id="returnForm">
                    @csrf
                    <input type="hidden" name="reader_id" value="{{ $selectedReader->id }}" />

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width:60px">Chọn</th>
                                    <th>Sách</th>
                                    <th>Phiếu</th>
                                    <th>Hẹn trả</th>
                                    <th>Tình trạng</th>
                                    <th>Phạt dự kiến</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($borrowItems as $i => $item)
                                    @php
                                        $due = $item->ngay_hen_tra ? \Carbon\Carbon::parse($item->ngay_hen_tra)->format('d/m/Y') : '---';
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input js-select-item" name="items[{{ $i }}][selected]" value="1" data-index="{{ $i }}">
                                            <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $item->book->ten_sach ?? '---' }}</div>
                                            <div class="text-muted small">ID item: {{ $item->id }}</div>
                                        </td>
                                        <td>#{{ $item->borrow_id }}</td>
                                        <td>{{ $due }}</td>
                                        <td>
                                            <select class="form-select form-select-sm js-condition" name="items[{{ $i }}][condition]" disabled>
                                                <option value="binh_thuong">Bình thường</option>
                                                <option value="hong_nhe">Hỏng nhẹ</option>
                                                <option value="hong_nang">Hỏng nặng</option>
                                                <option value="mat_sach">Mất sách</option>
                                            </select>
                                        </td>
                                        <td class="text-danger fw-bold">
                                            <span class="js-fine" data-item-id="{{ $item->id }}" data-due="{{ $item->ngay_hen_tra }}">0</span>₫
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-warning fw-bold" onclick="return confirm('Xác nhận trả các quyển đã chọn?')">
                            <i class="fas fa-check me-1"></i> Xác nhận trả
                        </button>
                    </div>
                </form>

                <div class="alert alert-secondary mt-3 mb-0">
                    <div class="d-flex justify-content-between">
                        <div><strong>Tổng phạt dự kiến (chỉ tính trễ hạn):</strong></div>
                        <div class="fw-bold text-danger"><span id="totalFine">0</span>₫</div>
                    </div>
                    <div class="text-muted small mt-1">Phạt hỏng/mất sẽ được tính khi xác nhận trả theo tình trạng bạn chọn.</div>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function formatMoney(n){
        try { return (n||0).toLocaleString('vi-VN'); } catch(e){ return n; }
    }

    function calcLateFine(dueDateStr){
        if(!dueDateStr) return 0;
        const due = new Date(dueDateStr);
        const today = new Date();
        // normalize start of day
        due.setHours(0,0,0,0);
        today.setHours(0,0,0,0);
        const diffMs = today - due;
        const days = Math.floor(diffMs / (1000*60*60*24));
        if(days <= 0) return 0;
        // Policy giống PricingService: 3 ngày đầu 5k/ngày, từ ngày 4: 15k/ngày
        const threshold = 3;
        const fineDay1 = 5000;
        const fineDay2 = 15000;
        if(days <= threshold) return days * fineDay1;
        return (threshold * fineDay1) + ((days - threshold) * fineDay2);
    }

    function updateTotal(){
        let total = 0;
        document.querySelectorAll('.js-select-item').forEach(cb => {
            const idx = cb.dataset.index;
            const rowFineEl = document.querySelectorAll('.js-fine')[idx];
            if(cb.checked && rowFineEl){
                total += parseInt(rowFineEl.dataset.value || '0', 10);
            }
        });
        document.getElementById('totalFine').textContent = formatMoney(total);
    }

    document.addEventListener('DOMContentLoaded', function(){
        const rows = document.querySelectorAll('.js-fine');
        rows.forEach((el) => {
            const due = el.getAttribute('data-due');
            const fine = calcLateFine(due);
            el.dataset.value = fine;
            el.textContent = formatMoney(fine);
        });

        document.querySelectorAll('.js-select-item').forEach(cb => {
            cb.addEventListener('change', function(){
                const idx = this.dataset.index;
                const conditionEl = document.querySelectorAll('.js-condition')[idx];
                if(conditionEl){
                    conditionEl.disabled = !this.checked;
                }
                updateTotal();
            });
        });

        updateTotal();
    });
</script>
@endpush
