<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ReservationCart;
use Illuminate\Http\Request;

class ReservationCartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return redirect()->route('account')
                ->with('error', 'Bạn cần đăng ký thông tin độc giả trước khi sử dụng chức năng đặt trước.');
        }

        $cart = ReservationCart::firstOrCreate(
            ['user_id' => $user->id],
            ['reader_id' => $reader->id]
        );

        // đảm bảo reader_id đúng nếu trước đó thiếu
        if (!$cart->reader_id) {
            $cart->update(['reader_id' => $reader->id]);
        }

        $items = $cart->items()->with('book')->orderBy('created_at', 'desc')->get();

        return view('reservation_cart.index', compact('cart', 'items'));
    }

    public function add(Request $request)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng ký thông tin độc giả trước khi đặt trước.',
                'redirect' => route('account'),
            ], 422);
        }

        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $cart = ReservationCart::firstOrCreate(
            ['user_id' => $user->id],
            ['reader_id' => $reader->id]
        );

        // unique book per cart
        $added = $cart->addBook((int) $data['book_id']);

        return response()->json([
            'success' => true,
            'added' => (bool) $added,
            'count' => $cart->items()->count(),
            'message' => $added ? 'Đã thêm vào giỏ.' : 'Sách đã có trong giỏ.',
        ]);
    }

    public function remove(Request $request, $bookId)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return redirect()->route('account')
                ->with('error', 'Bạn cần đăng ký thông tin độc giả trước khi sử dụng chức năng đặt trước.');
        }

        $cart = ReservationCart::where('user_id', $user->id)->first();
        if (!$cart) {
            return back()->with('info', 'Giỏ đang trống.');
        }

        $cart->removeBook((int) $bookId);

        return back()->with('success', 'Đã xoá khỏi giỏ.');
    }

    public function submit(Request $request)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return redirect()->route('account')
                ->with('error', 'Bạn cần đăng ký thông tin độc giả trước khi sử dụng chức năng đặt trước.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $cart = ReservationCart::with('items')->where('user_id', $user->id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Giỏ đặt trước đang trống.');
        }

        // đảm bảo reader_id khớp
        if ($cart->reader_id !== $reader->id) {
            $cart->update(['reader_id' => $reader->id]);
        }

        try {
                // Kiểm tra các item đã có pickup_date/return_date
                $itemsMissingDates = $cart->items->filter(function ($it) {
                    return empty($it->pickup_date) || empty($it->return_date);
                })->count();

                if ($itemsMissingDates > 0) {
                    return back()->with('error', 'Vui lòng chọn ngày lấy và ngày trả cho tất cả sách trước khi thanh toán.');
                }

                // Lấy ngày lấy sớm nhất từ các item để kiểm tra yêu cầu 3 ngày
                $earliestPickup = $cart->items->pluck('pickup_date')->filter()->min();
                if (!$earliestPickup) {
                    return back()->with('error', 'Không tìm thấy ngày lấy hợp lệ.');
                }

                if (\Carbon\Carbon::parse($earliestPickup)->lt(\Carbon\Carbon::now()->addDays(2))) {
                    return back()->with('error', 'Ngày lấy sách phải cách hôm nay tối thiểu 3 ngày.');
                }

                // Lưu pickup_date vào cart (dùng earliest)
                $cart->update(['pickup_date' => $earliestPickup]);

            // Chuẩn bị thông tin thanh toán tạm (giống flow từ giỏ mượn)
            $itemsData = [];
            $totalTienThue = 0;
            $totalTienCoc = 0;
            $totalTienShip = 0;

            foreach ($cart->items as $item) {
                $book = $item->book;
                $borrowDays = (int) ($item->days ?? 1);
                $quantity = (int) ($item->quantity ?? 1);
                $tienThue = ($item->daily_fee ?? 5000) * $borrowDays * $quantity;

                $itemsData[] = [
                    'book_id' => $item->book_id,
                    'inventorie_id' => $item->inventorie_id ?? null,
                    'borrow_days' => $borrowDays,
                    'tien_coc' => 0,
                    'tien_thue' => $tienThue,
                    'tien_ship' => 0,
                    'note' => $item->notes ?? null,
                ];

                $totalTienThue += $tienThue;
            }

            $tongTien = $totalTienCoc + $totalTienThue + $totalTienShip;

            $checkoutData = [
                'reader_id' => $reader->id,
                'reader_name' => $reader->name ?? ($user->name ?? ''),
                'reader_phone' => $reader->phone ?? ($user->phone ?? ''),
                'tinh_thanh' => $reader->tinh_thanh ?? '',
                'xa' => $reader->xa ?? '',
                'so_nha' => $reader->so_nha ?? '',
                'notes' => $request->notes ?? '',
                'checkout_source' => 'reservation_cart',
                'items' => $itemsData,
                'total_tien_coc' => $totalTienCoc,
                'total_tien_thue' => $totalTienThue,
                'total_tien_ship' => $totalTienShip,
                'tong_tien' => $tongTien,
                'voucher_id' => null,
                'discount_amount' => 0,
                'ngay_muon' => $request->pickup_date,
            ];

            // Lưu thông tin checkout vào session để chuyển sang trang checkout (orders.checkout)
            $sessionItems = [];
            foreach ($cart->items as $item) {
                $sessionItems[] = [
                    'ten_sach' => $item->book->ten_sach ?? '',
                    'tac_gia' => $item->book->tac_gia ?? '',
                    'quantity' => $item->quantity ?? 1,
                    'total_price' => ($item->daily_fee ?? 5000) * ($item->days ?? 1) * ($item->quantity ?? 1),
                ];
            }

            \Illuminate\Support\Facades\Session::put('checkout_items', [
                'reservation_cart' => true,
                'items' => $sessionItems,
                'total' => $tongTien,
            ]);

            return redirect()->route('checkout');

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi khi chuyển đến trang thanh toán: ' . $e->getMessage());
        }
    }

    public function count(Request $request)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return response()->json(['count' => 0]);
        }

        $cart = ReservationCart::where('user_id', $user->id)->first();
        $count = $cart ? $cart->items()->count() : 0;

        return response()->json(['count' => $count]);
    }

    public function updateQuantity(Request $request, $bookId)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng ký thông tin độc giả.',
            ], 422);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $cart = ReservationCart::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ không tồn tại.',
            ], 404);
        }

        $result = $cart->updateQuantity($bookId, $request->quantity);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function updateDates(Request $request, $bookId)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng ký thông tin độc giả.',
            ], 422);
        }

        $request->validate([
            'pickup_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'return_date' => 'required|date|date_format:Y-m-d|after:pickup_date',
        ]);

        $cart = ReservationCart::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ không tồn tại.',
            ], 404);
        }

        $result = $cart->updateDates($bookId, $request->pickup_date, $request->return_date);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function updateDays(Request $request, $bookId)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng ký thông tin độc giả.',
            ], 422);
        }

        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $cart = ReservationCart::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ không tồn tại.',
            ], 404);
        }

        $item = $cart->items()->where('book_id', $bookId)->first();
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Sách không có trong giỏ.',
            ], 404);
        }

        $item->update(['days' => $request->days]);

        $totalPrice = $cart->total_price ?? 0;
        $itemPrice = $item->total_price ?? 0;

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật số ngày thành công.',
            'days' => $item->days,
            'item_price' => $itemPrice,
            'total_price' => $totalPrice,
        ]);
    }
}
