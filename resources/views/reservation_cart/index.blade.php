@extends('layouts.app')

@section('title', 'Giỏ đặt trước')

@push('styles')
<style>
    :root {
        --reserve-primary: #2563eb;
        --reserve-bg: #f8fafc;
        --reserve-border: #e2e8f0;
        --reserve-text: #0f172a;
        --reserve-muted: #64748b;
        --reserve-danger: #ef4444;
        --reserve-success: #10b981;
        --radius-xl: 18px;
        --radius-lg: 14px;
        --radius-md: 10px;
    }

    body {
        background-color: var(--reserve-bg);
    }

    .reservation-cart-page {
        max-width: 1300px;
        margin: 32px auto;
        padding: 0 16px 40px;
    }

    .reservation-page-header {
        margin-bottom: 28px;
    }

    .reservation-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: var(--reserve-muted);
        margin-bottom: 10px;
    }

    .reservation-breadcrumb a {
        color: var(--reserve-primary);
        text-decoration: none;
    }

    .reservation-title {
        font-size: 26px;
        font-weight: 800;
        color: var(--reserve-text);
        margin: 0;
    }


    .reservation-cart-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.8fr) minmax(0, 1fr);
        gap: 24px;
        align-items: flex-start;
    }

    .reservation-left-col {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .reservation-card {
        background: #ffffff;
        border-radius: var(--radius-xl);
        padding: 20px 22px;
        border: 1px solid var(--reserve-border);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }

    .reservation-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .reservation-card-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--reserve-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .reservation-date-row {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
    }

    .reservation-date-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 160px;
    }

    .reservation-date-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--reserve-muted);
    }

    .reservation-date-group input[type="date"] {
        border-radius: var(--radius-md);
        border: 1px solid var(--reserve-border);
        padding: 10px 12px;
        font-size: 14px;
    }

    .reservation-items-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-top: 4px;
    }

    .reservation-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 18px;
        align-items: center;
        background: #ffffff;
        border-radius: var(--radius-lg);
        border: 1px solid var(--reserve-border);
        padding: 14px 16px;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.04);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .reservation-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.10);
    }

    .reservation-item-img-box {
        width: 70px;
        height: 96px;
        border-radius: 10px;
        overflow: hidden;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
    }

    .reservation-item-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .reservation-item-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .reservation-item-title {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        color: var(--reserve-text);
    }

    .reservation-item-author {
        font-size: 13px;
        color: var(--reserve-muted);
    }

    .reservation-item-meta {
        display: flex;
        flex-direction: column;
        gap: 4px;
        font-size: 12px;
        color: var(--reserve-muted);
    }

    .reservation-item-meta span strong {
        color: var(--reserve-text);
    }

    .reservation-fee-breakdown {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #f1f5f9;
        font-size: 11px;
        color: var(--reserve-muted);
    }

    .reservation-fee-breakdown strong {
        color: var(--reserve-text);
    }

    .reservation-item-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: flex-end;
        min-width: 140px;
    }

    .reservation-qty-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .reservation-qty-label {
        font-size: 12px;
        color: var(--reserve-muted);
    }

    .reservation-qty-input {
        width: 70px;
    }

    .reservation-days-pill {
        font-size: 13px;
        padding: 4px 10px;
        border-radius: 999px;
        background: #eff6ff;
        color: var(--reserve-primary);
        font-weight: 600;
    }

    .reservation-price {
        font-size: 15px;
        font-weight: 700;
        color: var(--reserve-danger);
    }

    .reservation-remove-btn {
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .reservation-summary {
        position: sticky;
        top: 90px;
    }

    .reservation-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-size: 14px;
        color: var(--reserve-muted);
    }

    .reservation-summary-row.total {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed var(--reserve-border);
        font-size: 18px;
        font-weight: 800;
        color: var(--reserve-text);
    }

    .reservation-total-price {
        color: var(--reserve-danger);
    }

    .reservation-summary-note {
        margin-top: 10px;
        padding: 10px 12px;
        border-radius: var(--radius-md);
        background: #f1f5f9;
        font-size: 12px;
        color: var(--reserve-muted);
    }

    .reservation-submit-btn {
        width: 100%;
        margin-top: 18px;
        border-radius: 999px;
        padding: 12px 16px;
        font-weight: 700;
        font-size: 15px;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.40);
    }

    .reservation-empty {
        text-align: center;
        padding: 70px 32px;
        background: #ffffff;
        border-radius: var(--radius-xl);
        border: 1px dashed var(--reserve-border);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
    }

    .reservation-empty-icon {
        font-size: 60px;
        margin-bottom: 18px;
        color: #cbd5f5;
    }

    .reservation-empty-title {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 6px;
        color: var(--reserve-text);
    }

    .reservation-empty-text {
        color: var(--reserve-muted);
        margin-bottom: 18px;
    }

    .reservation-empty-btn {
        border-radius: 999px;
        padding: 10px 20px;
        font-weight: 600;
    }

    @media (max-width: 1024px) {
        .reservation-cart-grid {
            grid-template-columns: minmax(0, 1fr);
        }
        .reservation-summary {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .reservation-item {
            grid-template-columns: auto 1fr;
            align-items: flex-start;
        }
        .reservation-item-actions {
            grid-column: 1 / -1;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
        }
        .reservation-submit-btn {
            border-radius: 12px;
        }
    }
</style>
@endpush

@section('content')
<div class="reservation-cart-page">
    <div class="reservation-page-header">
        <div class="reservation-breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <i class="fas fa-chevron-right" style="font-size:10px;"></i>
            <span>Giỏ đặt trước</span>
        </div>
        <h1 class="reservation-title">
            <i class="fas fa-calendar-check text-primary me-2"></i> Giỏ đặt trước
        </h1>
    </div>

@foreach (['success','error','info'] as $msg)
    @if(session($msg))
        <div class="alert alert-{{ $msg == 'error' ? 'danger' : $msg }}">
            {{ session($msg) }}
        </div>
    @endif
@endforeach

@if($items->count() === 0)
        <div class="reservation-empty">
            <div class="reservation-empty-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="reservation-empty-title">Giỏ đặt trước đang trống</div>
            <div class="reservation-empty-text">
                Hãy chọn những cuốn sách bạn muốn mượn trước, chúng tôi sẽ giữ cho bạn trong khoảng thời gian đã đặt.
            </div>
            <a href="{{ route('books.public') }}" class="btn btn-primary reservation-empty-btn">
                <i class="fas fa-book-open"></i> Khám phá kho sách
        </a>
    </div>
@else
        @if(request('configure_dates'))
            <div class="reservation-card" style="margin-bottom: 18px; border: 1px solid rgba(13, 148, 136, 0.22); background: linear-gradient(135deg, #f0fdfa, #eff6ff);">
                <div class="reservation-card-header" style="border-bottom: none; padding-bottom: 10px;">
                    <h3 class="reservation-card-title" style="color: #0f172a;">
                        <i class="fas fa-calendar-check"></i>
                        Chọn thời gian riêng cho từng cuốn
                    </h3>
                </div>
                <div style="padding: 0 22px 20px; color: #475569; line-height: 1.7;">
                    Bạn vừa chọn mượn nhiều cuốn cùng một đầu sách với <strong>thời gian trả khác nhau</strong>.
                    Mỗi dòng bên dưới tương ứng với một cuốn trong giỏ, bạn có thể chọn <strong>ngày lấy</strong> và <strong>ngày trả</strong> riêng cho từng cuốn.
                </div>
            </div>
        @endif
        <div class="reservation-cart-grid">
            <div class="reservation-left-col">
                {{-- DANH SÁCH SÁCH ĐẶT TRƯỚC --}}
                <div class="reservation-card">
                    <div class="reservation-card-header">
                        <h3 class="reservation-card-title">
                            <i class="fas fa-list-ul"></i>
                            Sách trong giỏ đặt trước ({{ $items->sum('quantity') }})
                        </h3>
                    </div>

                    <div class="reservation-items-list">
@foreach($items as $item)
    @php
        $maxReservationQty = (int) (($item->book->inventories()->where('storage_type', 'Kho')->where('status', 'Co san')->count()) ?: ($item->book->so_luong ?? 0));
        $sameBookItems = $items->where('book_id', $item->book_id)->values();
        $sameBookIndex = $sameBookItems->search(fn($cartItem) => $cartItem->id === $item->id);
    @endphp
                            <div class="reservation-item">
                                <div class="reservation-item-img-box">
        <img src="{{ $item->book->image_url ?? asset('images/default-book.png') }}"
                                         alt="{{ $item->book->ten_sach }}">
                                </div>

                                <div class="reservation-item-info">
                                    <h4 class="reservation-item-title">
                                        {{ $item->book->ten_sach }}
                                    </h4>
                                    @if($sameBookItems->count() > 1)
                                        <div class="reservation-item-author" style="margin-bottom: 6px; color: #0d9488; font-weight: 600;">
                                            Bản đặt trước #{{ ($sameBookIndex !== false ? $sameBookIndex + 1 : 1) }} cho cùng đầu sách
                                        </div>
                                    @endif
                                    <div class="reservation-item-author">
                                        Tác giả: <strong>{{ $item->book->tac_gia ?? 'Không rõ' }}</strong>
                                    </div>
                                    <div class="reservation-item-meta">
                                        <span>
                                            <i class="fas fa-calendar-day me-1"></i>
                                            Số ngày mượn:
                                            <span class="reservation-days-pill">
                                                <span class="days-display"
                                                    data-item-id="{{ $item->id }}">
                                                    {{ $item->days ?? 1 }}
                                                </span> ngày
                                            </span>
                                        </span>
                                        <span class="reservation-fee-breakdown">
                                            <span>
                                                {{ $item->days ?? 1 }} ngày
                                                × {{ number_format($item->daily_fee ?? 5000,0,',','.') }}₫/ngày
                                                × SL {{ $item->quantity ?? 1 }}
                                            </span>
                                            <span>
                                                = <strong>{{ number_format($item->total_price,0,',','.') }}₫</strong>
                                            </span>
                                        </span>
                                    </div>

                                    <div class="reservation-date-row" style="margin-top: 4px;">
                                        <div class="reservation-date-group" style="min-width: 0;">
                                            <span class="reservation-date-label">Ngày lấy</span>
                                            <input
                                                type="date"
                                                class="form-control pickup-date"
                                                data-item-id="{{ $item->id }}"
                                                value="{{ $item->pickup_date ? \Carbon\Carbon::parse($item->pickup_date)->format('Y-m-d') : '' }}"
                                                onchange="handleItemDateChange(this)"
                                            >
                                        </div>
                                        <div class="reservation-date-group" style="min-width: 0;">
                                            <span class="reservation-date-label">Ngày trả</span>
                                            <input
                                                type="date"
                                                class="form-control return-date"
                                                data-item-id="{{ $item->id }}"
                                                value="{{ $item->return_date ? \Carbon\Carbon::parse($item->return_date)->format('Y-m-d') : '' }}"
                                                onchange="handleItemDateChange(this)"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="reservation-item-actions">
                                    <div class="reservation-qty-wrapper">
                                        <span class="reservation-qty-label">Số lượng</span>
        <input type="number"
               value="{{ $item->quantity }}"
               min="1"
             @if($maxReservationQty > 0) max="{{ $maxReservationQty }}" @endif
                             data-item-id="{{ $item->id }}"
               onchange="updateQuantity(this)"
                                               class="form-control reservation-qty-input">
                                    </div>

                                    <div class="reservation-price">
        <span class="item-price"
              data-item-id="{{ $item->id }}">
            {{ number_format($item->total_price,0,',','.') }}₫
        </span>
                                    </div>

        <form method="POST"
              action="{{ route('reservation-cart.remove',$item->id) }}">
            @csrf
                                        <button class="btn btn-outline-danger btn-sm reservation-remove-btn" type="submit">
                                            <i class="fas fa-times"></i> Xóa
                                        </button>
        </form>
                                </div>
                            </div>
@endforeach
                    </div>
                </div>
            </div>

            {{-- TÓM TẮT --}}
            <div class="reservation-summary">
                <div class="reservation-card">
                    <div class="reservation-card-header">
                        <h3 class="reservation-card-title">
                            <i class="fas fa-receipt"></i>
                            Tóm tắt giỏ đặt trước
                        </h3>
                    </div>

                    <div class="reservation-summary-row">
                        <span>Tổng sách</span>
                        <span><strong>{{ $items->sum('quantity') }}</strong> cuốn</span>
                    </div>

                    <div class="reservation-summary-row total">
                        <span>Tổng tạm tính</span>
                        <span class="reservation-total-price" id="total-price">
                            {{ number_format($cart->total_price,0,',','.') }}₫
                        </span>
</div>

                    <div class="reservation-summary-note">
                        <i class="fas fa-info-circle me-1"></i>
                        Vui lòng chọn <strong>ngày lấy</strong> và <strong>ngày trả</strong> cho từng sách trong giỏ.
                        Tiền thuê của mỗi cuốn sẽ được tính riêng theo số ngày mượn.
                    </div>

                    <form method="POST"
                          action="{{ route('reservation-cart.submit') }}"
                          onsubmit="return validateCartBeforeSubmit()">
                        @csrf
                        <button class="btn btn-primary reservation-submit-btn" type="submit">
                            Gửi yêu cầu đặt trước <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
@endif

</div>

@include('components.footer')

<script>
function formatCurrency(v){
    return new Intl.NumberFormat('vi-VN').format(v) + '₫';
}

function updateQuantity(input){
    const previousQuantity = input.defaultValue || input.value;

    fetch('{{ route("reservation-cart.update-quantity",":id") }}'
        .replace(':id', input.dataset.itemId),{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
        },
        body:JSON.stringify({quantity:input.value})
    })
    .then(r=>r.json())
    .then(d=>{
        if(!d.success){
            alert(d.message || 'Không thể cập nhật số lượng.');
            input.value = previousQuantity;
            return;
        }

        input.defaultValue = d.quantity;
        document.querySelector(`.item-price[data-item-id="${input.dataset.itemId}"]`)
            .textContent = formatCurrency(d.item_price);
        document.getElementById('total-price').textContent =
            formatCurrency(d.total_price);
    })
    .catch(() => {
        alert('Không thể cập nhật số lượng.');
        input.value = previousQuantity;
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

// Kiểm tra ngày lấy / ngày trả hợp lệ ở phía client
function validateReservationDates(pickup, ret, showAlert = true){
    if(!pickup || !ret){
        if(showAlert){
            alert('Vui lòng chọn đầy đủ ngày lấy và ngày trả.');
        }
        return false;
    }

    const today = new Date();
    today.setHours(0,0,0,0);

    const pickupDate = new Date(pickup);
    const returnDate = new Date(ret);

    if(pickupDate < today){
        if(showAlert){
            alert('Ngày lấy sách không được ở quá khứ.');
        }
        return false;
    }

    if(returnDate <= pickupDate){
        if(showAlert){
            alert('Ngày trả sách phải sau ngày lấy sách.');
        }
        return false;
    }

    return true;
}

function handleItemDateChange(input){
    const itemId = input.dataset.itemId;
    const pickupInput = document.querySelector(`.pickup-date[data-item-id="${itemId}"]`);
    const returnInput = document.querySelector(`.return-date[data-item-id="${itemId}"]`);

    if(!pickupInput || !returnInput){
        return;
    }

    const pickup = pickupInput.value;
    const ret = returnInput.value;

    // Chỉ gọi API khi cả hai ngày đã được chọn
    if(!pickup || !ret){
        return;
    }

    if(!validateReservationDates(pickup, ret, true)){
        // Nếu không hợp lệ thì reset input vừa sửa
        input.value = '';
        return;
    }

    const statusMsg = ensureDateStatusEl();
    statusMsg.style.display = 'block';
    statusMsg.style.background = '#3b82f6';
    statusMsg.textContent = 'Đang cập nhật ngày cho sách...';

    fetch('{{ route("reservation-cart.update-dates",":id") }}'
        .replace(':id', itemId),{
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
        document.querySelector(`.days-display[data-item-id="${itemId}"]`)
            .textContent = d.days;
        document.querySelector(`.item-price[data-item-id="${itemId}"]`)
            .textContent = formatCurrency(d.item_price);
        document.getElementById('total-price')
            .textContent = formatCurrency(d.total_price);

        statusMsg.style.background = '#22c55e';
        statusMsg.textContent = 'Đã cập nhật ngày cho sách này!';
        setTimeout(() => { statusMsg.style.display = 'none'; }, 2000);
    })
    .catch(err => {
        statusMsg.style.background = '#ef4444';
        statusMsg.textContent = err.message || 'Cập nhật ngày thất bại. Vui lòng thử lại.';
        setTimeout(() => { statusMsg.style.display = 'none'; }, 4000);
    });
}

function validateCartBeforeSubmit(){
    const pickups = document.querySelectorAll('.pickup-date');
    const returns = document.querySelectorAll('.return-date');

    if(pickups.length === 0){
        return true;
    }

    for(let i = 0; i < pickups.length; i++){
        const pickup = pickups[i].value;
        const itemId = pickups[i].dataset.itemId;
        const retInput = document.querySelector(`.return-date[data-item-id="${itemId}"]`);
        const ret = retInput ? retInput.value : '';

        if(!pickup || !ret){
            alert('Vui lòng chọn đầy đủ ngày lấy và ngày trả cho tất cả sách trong giỏ.');
            return false;
        }

        if(!validateReservationDates(pickup, ret, false)){
            alert('Ngày lấy / ngày trả của một số sách không hợp lệ. Vui lòng kiểm tra lại.');
            return false;
        }
    }

    return true;
}
</script>
@endsection
