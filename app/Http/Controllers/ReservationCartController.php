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
}
