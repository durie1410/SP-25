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
            'pickup_date' => 'required|date|after:' . \Carbon\Carbon::now()->addDays(2)->format('Y-m-d'),
        ], [
            'pickup_date.required' => 'Vui lòng chọn ngày lấy sách.',
            'pickup_date.after' => 'Ngày lấy sách phải cách hôm nay tối thiểu 3 ngày.',
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
            // Lưu pickup_date vào cart
            $cart->update(['pickup_date' => $request->pickup_date]);
            
            $result = $cart->submitReservations($request->notes);
            $createdCount = is_array($result) && array_key_exists('created', $result) ? count($result['created']) : 0;
            $skippedCount = is_array($result) && array_key_exists('skipped', $result) ? (int) $result['skipped'] : 0;

            if ($createdCount > 0) {
                $message = 'Đã gửi ' . $createdCount . ' yêu cầu đặt trước. Vui lòng chờ thông báo.';
                if ($skippedCount > 0) {
                    $message .= ' Bỏ qua ' . $skippedCount . ' sách đang chờ xử lý.';
                }
            } else {
                $message = 'Không có yêu cầu nào được tạo. Tất cả sách đều đang chờ xử lý hoặc đã được đặt trước.';
            }

            return redirect()->route('reservation-cart.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi khi gửi yêu cầu: ' . $e->getMessage());
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
