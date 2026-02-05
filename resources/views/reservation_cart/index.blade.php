@extends('layouts.app')

@section('title', 'Giỏ đặt trước')

@section('content')
<div class="content-wrapper" style="max-width: 1100px; margin: 20px auto;">
    <div class="main-content" style="background: #fff; padding: 20px 24px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06);">
        <h2 style="margin: 0 0 14px;">Giỏ đặt trước</h2>

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 12px;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom: 12px;">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info" style="margin-bottom: 12px;">{{ session('info') }}</div>
        @endif

        @if($items->count() === 0)
            <div style="padding: 20px; color: #64748b;">
                Giỏ đặt trước đang trống.
            </div>
        @else
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="text-align:center; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; width: 60px;">Ảnh</th>
                            <th style="text-align:left; padding: 10px 12px; border-bottom: 1px solid #e2e8f0;">Sách</th>
                            <th style="text-align:center; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; width: 80px;">Số Lượng</th>
                            <th style="text-align:center; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; width: 110px;">Ngày Lấy</th>
                            <th style="text-align:center; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; width: 110px;">Ngày Trả</th>
                            <th style="text-align:center; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; width: 70px;">Số Ngày</th>
                            <th style="text-align:right; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; width: 120px;">Giá (5k/ngày)</th>
                            <th style="text-align:center; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; width: 80px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 12px; text-align: center;">
                                    <img src="{{ $item->book->image_url ?? asset('images/default-book.png') }}" alt="{{ $item->book->ten_sach }}" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                </td>
                                <td style="padding: 12px;">
                                    <div style="font-weight: 700;">{{ $item->book->ten_sach ?? 'N/A' }}</div>
                                    <div style="color:#64748b; font-size: 12px;">{{ $item->book->tac_gia ?? '' }}</div>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <input type="number" class="form-control quantity-input" min="1" max="100" value="{{ $item->quantity ?? 1 }}" data-book-id="{{ $item->book_id }}" onchange="updateQuantity(this)" style="font-size: 12px; padding: 6px; width: 60px;">
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <input type="date" class="form-control pickup-date" value="{{ $item->pickup_date ?? '' }}" data-book-id="{{ $item->book_id }}" onchange="updateDates(this)" style="font-size: 12px; padding: 6px;">
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <input type="date" class="form-control return-date" value="{{ $item->return_date ?? '' }}" data-book-id="{{ $item->book_id }}" onchange="updateDates(this)" style="font-size: 12px; padding: 6px;">
                                </td>
                                <td style="padding: 12px; text-align: center; font-weight: 600;">
                                    <span class="days-display" data-book-id="{{ $item->book_id }}">{{ $item->days ?? 1 }}</span>
                                </td>
                                <td style="padding: 12px; text-align: right; font-weight: 600;">
                                    <span class="item-price" data-book-id="{{ $item->book_id }}">{{ number_format($item->total_price ?? 0, 0, ',', '.') }}₫</span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <form method="POST" action="{{ route('reservation-cart.remove', $item->book_id) }}" style="display:inline;" onsubmit="return confirm('Xoá sách này khỏi giỏ đặt trước?')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm" style="font-size: 12px; padding: 4px 8px;">Xoá</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="background:#f8fafc; padding: 16px; border-radius: 8px; margin: 20px 0; border-right: 4px solid #0d9488;">
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 16px;">
                    <span style="font-weight: 600;">Tổng cộng:</span>
                    <span style="font-weight: 700; color: #0d9488; font-size: 18px;" id="total-price">
                        {{ number_format($cart->total_price ?? 0, 0, ',', '.') }}₫
                    </span>
                </div>
                <div style="color: #64748b; font-size: 12px; margin-top: 8px;">
                    <strong>Ghi chú:</strong> Giá được tính dựa trên số ngày mượn × số lượng × 5.000₫/ngày/cuốn. Bạn <strong>bắt buộc</strong> phải chọn số lượng, ngày lấy và ngày trả.
                </div>
            </div>

            <form method="POST" action="{{ route('reservation-cart.submit') }}" style="margin-top: 14px; display:flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                @csrf
                <div style="flex: 1; min-width: 260px;">
                    <input type="text" name="notes" class="form-control" placeholder="Ghi chú (tuỳ chọn)..." maxlength="1000">
                </div>
                <button type="submit" class="btn btn-primary" onclick="return validateAllDates()">Gửi yêu cầu đặt trước</button>
            </form>
        @endif

        <div style="margin-top: 18px;">
            <a href="{{ route('books.public') }}" class="btn btn-secondary">Tiếp tục xem sách</a>
        </div>
    </div>
</div>

<script>
function formatCurrency(value) {
    return new Intl.NumberFormat('vi-VN').format(Math.round(value)) + '₫';
}

function updateQuantity(input) {
    const bookId = input.getAttribute('data-book-id');
    const quantity = parseInt(input.value);

    if (quantity < 1) {
        input.value = 1;
        alert('Số lượng phải >= 1');
        return;
    }

    fetch('{{ route("reservation-cart.update-quantity", ":bookId") }}'.replace(':bookId', bookId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update item price
            const itemPrice = document.querySelector(`.item-price[data-book-id="${bookId}"]`);
            if (itemPrice) {
                itemPrice.textContent = formatCurrency(data.item_price);
            }

            // Update total price
            const totalPrice = document.getElementById('total-price');
            if (totalPrice) {
                totalPrice.textContent = formatCurrency(data.total_price);
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    });
}

function updateDates(input) {
    const bookId = input.getAttribute('data-book-id');
    const pickupInput = document.querySelector(`.pickup-date[data-book-id="${bookId}"]`);
    const returnInput = document.querySelector(`.return-date[data-book-id="${bookId}"]`);
    
    const pickupDate = pickupInput.value;
    const returnDate = returnInput.value;

    if (!pickupDate || !returnDate) {
        alert('Vui lòng chọn cả ngày lấy và ngày trả');
        return;
    }

    const pickup = new Date(pickupDate);
    const returnDay = new Date(returnDate);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (pickup < today) {
        alert('Ngày lấy không được là ngày quá khứ');
        pickupInput.value = '';
        return;
    }

    if (returnDay <= pickup) {
        alert('Ngày trả phải sau ngày lấy');
        returnInput.value = '';
        return;
    }

    fetch('{{ route("reservation-cart.update-dates", ":bookId") }}'.replace(':bookId', bookId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            pickup_date: pickupDate,
            return_date: returnDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update days display
            const daysDisplay = document.querySelector(`.days-display[data-book-id="${bookId}"]`);
            if (daysDisplay) {
                daysDisplay.textContent = data.days;
            }

            // Update item price
            const itemPrice = document.querySelector(`.item-price[data-book-id="${bookId}"]`);
            if (itemPrice) {
                itemPrice.textContent = formatCurrency(data.item_price);
            }

            // Update total price
            const totalPrice = document.getElementById('total-price');
            if (totalPrice) {
                totalPrice.textContent = formatCurrency(data.total_price);
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    });
}

function validateAllDates() {
    const pickupDates = document.querySelectorAll('.pickup-date');
    const returnDates = document.querySelectorAll('.return-date');
    
    for (let i = 0; i < pickupDates.length; i++) {
        if (!pickupDates[i].value) {
            alert('Vui lòng chọn ngày lấy cho tất cả sách');
            return false;
        }
        if (!returnDates[i].value) {
            alert('Vui lòng chọn ngày trả cho tất cả sách');
            return false;
        }
    }
    return true;
}
</script>
@endsection
