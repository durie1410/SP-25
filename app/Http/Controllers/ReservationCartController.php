<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowItem;
use App\Models\InventoryReservation;
use App\Models\ReservationCart;
use Carbon\Carbon;
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
            'quantity' => 'nullable|integer|min:1|max:100',
            'split_items' => 'nullable|boolean',
        ]);

        $book = Book::findOrFail($data['book_id']);
        $requestedQuantity = max(1, (int) ($data['quantity'] ?? 1));
        $splitItems = (bool) ($data['split_items'] ?? false);

        $cart = ReservationCart::firstOrCreate(
            ['user_id' => $user->id],
            ['reader_id' => $reader->id]
        );

        if (!$cart->reader_id) {
            $cart->update(['reader_id' => $reader->id]);
        }

        $availableStock = $this->getAvailableStock($book);
        $existingQuantity = (int) $cart->items()
            ->where('book_id', (int) $data['book_id'])
            ->sum('quantity');

        if ($availableStock > 0 && ($existingQuantity + $requestedQuantity) > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "Bạn chỉ có thể đặt tối đa {$availableStock} cuốn vì kho hiện còn {$availableStock} cuốn.",
            ], 422);
        }

        if (!empty($data['borrow_item_id'])) {
            $borrowItem = BorrowItem::with('borrow')->findOrFail($data['borrow_item_id']);

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

        $added = null;
        if ($splitItems) {
            for ($i = 0; $i < $requestedQuantity; $i++) {
                $added = $cart->addBook((int) $data['book_id'], 1);
            }
        } else {
            $added = $cart->addBook((int) $data['book_id'], $requestedQuantity);
        }

        return response()->json([
            'success' => true,
            'added' => (bool) $added,
            'count' => $cart->fresh()->item_count,
            'quantity' => $existingQuantity + $requestedQuantity,
            'message' => $requestedQuantity > 1
                ? ($splitItems
                    ? "Đã thêm {$requestedQuantity} cuốn thành các dòng riêng để bạn chọn ngày mượn khác nhau nếu cần."
                    : "Đã thêm {$requestedQuantity} cuốn vào cùng một dòng để bạn dùng chung ngày trả.")
                : 'Đã thêm vào giỏ đặt trước. Nếu muốn mượn cùng đầu sách với số ngày khác nhau, bạn có thể thêm nhiều lần để tách riêng từng dòng.',
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

        $book = Book::findOrFail($data['book_id']);
        $cart = ReservationCart::firstOrCreate(
            ['user_id' => $user->id],
            ['reader_id' => $reader->id]
        );

        if (!$cart->reader_id) {
            $cart->update(['reader_id' => $reader->id]);
        }

        $availableStock = $this->getAvailableStock($book);
        $existingQuantity = (int) $cart->items()->where('book_id', (int) $data['book_id'])->sum('quantity');

        if ($availableStock > 0 && ($existingQuantity + 1) > $availableStock) {
            return redirect()->route('reservation-cart.index')
                ->with('error', "Bạn chỉ có thể đặt tối đa {$availableStock} cuốn vì kho hiện còn {$availableStock} cuốn.");
        }

        $cart->addBook((int) $data['book_id']);

        return redirect()->route('reservation-cart.index')
            ->with('success', 'Đã thêm sách vào giỏ đặt trước thành một dòng riêng. Bạn có thể chọn ngày mượn khác với các dòng còn lại.');
    }

    public function remove(Request $request, $itemId)
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

        $cart->removeBook((int) $itemId);

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

        if ($cart->reader_id !== $reader->id) {
            $cart->update(['reader_id' => $reader->id]);
        }

        try {
            DB::beginTransaction();

            foreach ($cart->items as $item) {

            }

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
        $count = $cart ? $cart->item_count : 0;

        return response()->json(['count' => $count]);
    }

    public function updateQuantity(Request $request, $itemId)
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

        $item = $cart->items()->with('book')->where('id', $itemId)->first();
        if (!$item || !$item->book) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sách trong giỏ.',
            ], 404);
        }

        $availableStock = $this->getAvailableStock($item->book);
        $otherQuantity = (int) $cart->items()
            ->where('book_id', $item->book_id)
            ->where('id', '!=', $item->id)
            ->sum('quantity');

        if ($availableStock > 0 && ($otherQuantity + (int) $request->quantity) > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "Bạn chỉ có thể đặt tối đa {$availableStock} cuốn vì kho hiện còn {$availableStock} cuốn.",
            ], 422);
        }

        $result = $cart->updateQuantity((int) $itemId, (int) $request->quantity);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function updateDates(Request $request, $itemId)
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

        $result = $cart->updateDates((int) $itemId, $request->pickup_date, $request->return_date);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function updateDays(Request $request, $itemId)
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

        $item = $cart->items()->where('id', $itemId)->first();
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Sách không có trong giỏ.',
            ], 404);
        }

        $item->update(['days' => $request->days]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật số ngày thành công.',
            'days' => $item->days,
            'item_price' => $item->fresh()->total_price ?? 0,
            'total_price' => $cart->fresh()->total_price ?? 0,
        ]);
    }

    private function getAvailableStock(Book $book): int
    {
        $inventoryStock = (int) $book->inventories()
            ->where('storage_type', 'Kho')
            ->where('status', 'Co san')
            ->count();

        return $inventoryStock > 0
            ? $inventoryStock
            : max(0, (int) ($book->so_luong ?? 0));
    }
}
