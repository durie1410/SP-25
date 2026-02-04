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
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="text-align:left; padding: 10px 12px; border-bottom: 1px solid #e2e8f0;">Sách</th>
                            <th style="text-align:left; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; width: 160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">
                                    <div style="font-weight: 700;">{{ $item->book->ten_sach ?? 'N/A' }}</div>
                                    <div style="color:#64748b; font-size: 12px;">{{ $item->book->tac_gia ?? '' }}</div>
                                </td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">
                                    <form method="POST" action="{{ route('reservation-cart.remove', $item->book_id) }}" onsubmit="return confirm('Xoá sách này khỏi giỏ đặt trước?')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Xoá</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('reservation-cart.submit') }}" style="margin-top: 14px; display:flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                @csrf
                <div style="flex: 1; min-width: 260px;">
                    <input type="text" name="notes" class="form-control" placeholder="Ghi chú (tuỳ chọn)..." maxlength="1000">
                </div>
                <button type="submit" class="btn btn-primary">Gửi yêu cầu đặt trước</button>
            </form>
        @endif

        <div style="margin-top: 18px;">
            <a href="{{ route('books.public') }}" class="btn btn-secondary">Tiếp tục xem sách</a>
        </div>
    </div>
</div>
@endsection
