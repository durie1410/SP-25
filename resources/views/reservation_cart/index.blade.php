@extends('layouts.app')

@section('title', 'Giỏ đặt trước')

@section('content')
<div class="content-wrapper" style="max-width:1100px;margin:20px auto;">
<div class="main-content" style="background:#fff;padding:20px 24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">

<h2>Giỏ đặt trước</h2>

@foreach (['success','error','info'] as $msg)
    @if(session($msg))
        <div class="alert alert-{{ $msg == 'error' ? 'danger' : $msg }}">
            {{ session($msg) }}
        </div>
    @endif
@endforeach

@if($items->count() === 0)
    <div style="padding:20px;color:#64748b;text-align:center;">
        <div style="margin-bottom:20px;">Giỏ đặt trước đang trống.</div>
        <a href="{{ url('/') }}" class="btn btn-secondary" style="text-decoration:none;">
            <i class="fas fa-home"></i> Trở về trang chủ
        </a>
    </div>
@else

{{-- NGÀY CHUNG --}}
<div style="display:flex;gap:12px;margin-bottom:16px;">
    <div>
        <label>Ngày lấy</label>
        <input type="date" id="pickup-date-global" class="form-control"
               onchange="updateDatesForAll()">
    </div>
    <div>
        <label>Ngày trả</label>
        <input type="date" id="return-date-global" class="form-control"
               onchange="updateDatesForAll()">
    </div>
</div>

<table class="table">
<thead>
<tr>
    <th>Ảnh</th>
    <th>Sách</th>
    <th>SL</th>
    <th>Số ngày</th>
    <th>Giá</th>
    <th>Xoá</th>
</tr>
</thead>
<tbody>
@foreach($items as $item)
<tr>
    <td>
        <img src="{{ $item->book->image_url ?? asset('images/default-book.png') }}"
             style="width:50px;height:70px;">
    </td>
    <td>
        <strong>{{ $item->book->ten_sach }}</strong><br>
        <small>{{ $item->book->tac_gia }}</small>
    </td>
    <td>
        <input type="number"
               value="{{ $item->quantity }}"
               min="1"
               data-book-id="{{ $item->book_id }}"
               onchange="updateQuantity(this)"
               class="form-control"
               style="width:60px;">
    </td>
    <td style="font-weight:600;text-align:center;">
        <span class="days-display"
              data-book-id="{{ $item->book_id }}">
            {{ $item->days ?? 1 }}
        </span>
    </td>
    <td style="font-weight:600;text-align:right;">
        <span class="item-price"
              data-book-id="{{ $item->book_id }}">
            {{ number_format($item->total_price,0,',','.') }}₫
        </span>
    </td>
    <td>
        <form method="POST"
              action="{{ route('reservation-cart.remove',$item->book_id) }}">
            @csrf
            <button class="btn btn-danger btn-sm">X</button>
        </form>
    </td>
</tr>
@endforeach
</tbody>
</table>

<div style="margin:16px 0;font-weight:700;">
    Tổng:
    <span id="total-price">
        {{ number_format($cart->total_price,0,',','.') }}₫
    </span>
</div>

<form method="POST"
      action="{{ route('reservation-cart.submit') }}"
      onsubmit="return validateGlobalDates()">
    @csrf
    <button class="btn btn-primary">Gửi yêu cầu</button>
</form>

@endif
</div>
</div>

<script>
function formatCurrency(v){
    return new Intl.NumberFormat('vi-VN').format(v) + '₫';
}

function updateQuantity(input){
    fetch('{{ route("reservation-cart.update-quantity",":id") }}'
        .replace(':id', input.dataset.bookId),{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
        },
        body:JSON.stringify({quantity:input.value})
    })
    .then(r=>r.json())
    .then(d=>{
        document.querySelector(`.item-price[data-book-id="${input.dataset.bookId}"]`)
            .textContent = formatCurrency(d.item_price);
        document.getElementById('total-price').textContent =
            formatCurrency(d.total_price);
    });
}

function ensureDateStatusEl(){
    let statusMsg = document.getElementById('date-update-status');
    if(statusMsg) return statusMsg;

    statusMsg = document.createElement('div');
    statusMsg.id = 'date-update-status';
    statusMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#22c55e;color:white;padding:10px 20px;border-radius:5px;z-index:9999;display:none;max-width:320px;';
    document.body.appendChild(statusMsg);
    return statusMsg;
}

function updateDatesForAll(){
    const pickup = document.getElementById('pickup-date-global').value;
    const ret = document.getElementById('return-date-global').value;
    if(!pickup || !ret) return;

    const statusMsg = ensureDateStatusEl();
    statusMsg.style.display = 'block';
    statusMsg.style.background = '#3b82f6';
    statusMsg.textContent = 'Đang cập nhật ngày...';

    let successCount = 0;
    let errorCount = 0;
    const totalItems = document.querySelectorAll('.days-display').length;

    document.querySelectorAll('.days-display').forEach(el=>{
        const bookId = el.dataset.bookId;

        fetch('{{ route("reservation-cart.update-dates",":id") }}'
            .replace(':id', bookId),{
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
            },
            body:JSON.stringify({
                pickup_date: pickup,
                return_date: ret
            })
        })
        .then(async (r)=>{
            const data = await r.json().catch(() => ({}));
            if(!r.ok || data.success === false){
                throw new Error(data.message || 'Cập nhật thất bại');
            }
            return data;
        })
        .then(d=>{
            successCount++;
            el.textContent = d.days;
            document.querySelector(`.item-price[data-book-id="${bookId}"]`)
                .textContent = formatCurrency(d.item_price);
            document.getElementById('total-price')
                .textContent = formatCurrency(d.total_price);
        })
        .catch(err => {
            errorCount++;
        })
        .finally(() => {
            if(successCount + errorCount !== totalItems) return;

            if(errorCount > 0){
                statusMsg.style.background = '#ef4444';
                statusMsg.textContent = 'Có ' + errorCount + ' sách cập nhật ngày bị lỗi. Vui lòng chọn lại ngày hợp lệ.';
                setTimeout(() => { statusMsg.style.display = 'none'; }, 4000);
            } else {
                statusMsg.style.background = '#22c55e';
                statusMsg.textContent = 'Đã cập nhật ngày thành công!';
                setTimeout(() => { statusMsg.style.display = 'none'; }, 2000);
            }
        });
    });
}

function validateGlobalDates(){
    const pickup = document.getElementById('pickup-date-global').value;
    const ret = document.getElementById('return-date-global').value;

    if(!pickup || !ret){
        alert('Vui lòng chọn ngày lấy và ngày trả cho tất cả sách trước khi thanh toán.');
        return false;
    }
    return true;
}
</script>
@endsection
