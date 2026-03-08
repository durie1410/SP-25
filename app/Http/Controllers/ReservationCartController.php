<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowItem;
use App\Models\InventoryReservation;
use App\Models\ReservationCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'borrow_item_id' => 'nullable|exists:borrow_items,id',
        ]);

        if (!empty($data['borrow_item_id'])) {
            $borrowItem = BorrowItem::with('borrow')
                ->findOrFail($data['borrow_item_id']);

            if (!$borrowItem->borrow || (int) $borrowItem->borrow->reader_id !== (int) $reader->id) {
                return redirect()->route('orders.index')
                    ->with('error', 'Bạn không có quyền thuê lại cuốn sách này.');
            }

            $isReturned = $borrowItem->trang_thai === 'Da tra' || !empty($borrowItem->ngay_tra_thuc_te);

            if (!$isReturned) {
                return redirect()->route('orders.detail', $borrowItem->borrow_id)
                    ->with('error', 'Bạn chỉ có thể thuê lại sau khi đã trả xong cuốn sách này.');
            }
        }

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

    public function addAndRedirect(Request $request)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return redirect()->route('account')
                ->with('error', 'Bạn cần đăng ký thông tin độc giả trước khi đặt trước.');
        }

        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $cart = ReservationCart::firstOrCreate(
            ['user_id' => $user->id],
            ['reader_id' => $reader->id]
        );

        if (!$cart->reader_id) {
            $cart->update(['reader_id' => $reader->id]);
        }

        $added = $cart->addBook((int) $data['book_id']);

        return redirect()->route('reservation-cart.index')
            ->with($added ? 'success' : 'info', $added
                ? 'Đã thêm sách vào giỏ đặt trước. Bạn có thể tiếp tục chọn ngày mượn và gửi yêu cầu.'
                : 'Sách này đã có sẵn trong giỏ đặt trước.');
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
            DB::beginTransaction();

            foreach ($cart->items as $item) {
                $quantity = (int) ($item->quantity ?? 1);

                $pickupDate = $item->pickup_date ? \Carbon\Carbon::parse($item->pickup_date) : null;
                $returnDate = $item->return_date ? \Carbon\Carbon::parse($item->return_date) : null;

                $borrowDays = 1;
                if ($pickupDate && $returnDate && $returnDate->greaterThan($pickupDate)) {
                    $borrowDays = max(1, $pickupDate->diffInDays($returnDate));
                } else {
                    $borrowDays = (int) ($item->days ?? 1);
                }

                $dailyFee = (float) ($item->daily_fee ?? 5000);
                $tienThue = $dailyFee * $borrowDays;

                // Tạo N bản ghi dựa trên số lượng
                for ($i = 0; $i < $quantity; $i++) {
                    \App\Models\InventoryReservation::create([
                        'book_id' => $item->book_id,
                        'user_id' => $user->id,
                        'reader_id' => $reader->id,
                        'pickup_date' => $item->pickup_date,
                        'return_date' => $item->return_date,
                        'total_fee' => $tienThue,
                        'notes' => $request->notes,
                        'status' => 'pending',
                    ]);
                }
            }

            // Xóa giỏ hàng sau khi gửi thành công
            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return redirect()->route('reservation-cart.index')
                ->with('success', 'Yêu cầu đặt trước của bạn đã được gửi thành công. Thủ thư sẽ sớm kiểm tra và phản hồi.');

        } catch (\Exception $e) {
            DB::rollBack();
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
