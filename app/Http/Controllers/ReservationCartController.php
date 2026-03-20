<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowItem;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\ReservationCart;
use App\Services\NotificationService;
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

        $maxBorrowBooks = (int) config('library.borrow_max_books', 5);

        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
            'borrow_item_id' => 'nullable|exists:borrow_items,id',
            'quantity' => 'nullable|integer|min:1|max:' . $maxBorrowBooks,
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

        $currentTotalQuantity = (int) $cart->items()->sum('quantity');
        if (($currentTotalQuantity + $requestedQuantity) > $maxBorrowBooks) {
            return response()->json([
                'success' => false,
                'message' => "Bạn chỉ được đặt tối đa {$maxBorrowBooks} cuốn trong một đơn.",
            ], 422);
        }

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

    public function splitItem(Request $request, $itemId)
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

        $item = $cart->items()->where('id', (int) $itemId)->first();

        if (!$item) {
            return back()->with('error', 'Không tìm thấy sách trong giỏ.');
        }

        $quantity = max(1, (int) ($item->quantity ?? 1));
        if ($quantity < 2) {
            return back()->with('info', 'Dòng này đã là 1 cuốn, không cần tách thêm.');
        }

        DB::transaction(function () use ($cart, $item, $quantity) {
            for ($copy = 0; $copy < $quantity; $copy++) {
                $cart->items()->create([
                    'book_id' => $item->book_id,
                    'quantity' => 1,
                    'days' => $item->days,
                    'daily_fee' => $item->daily_fee,
                    'pickup_date' => $item->pickup_date,
                    'return_date' => $item->return_date,
                ]);
            }

            $item->delete();
        });

        return back()->with('success', 'Đã tách số lượng thành từng cuốn riêng để bạn chọn đặt trước từng cuốn.');
    }

    public function submit(Request $request)
    {
        // DEBUG
        \Log::info('DEBUG submit request', [
            'all_input' => $request->all(),
            'pickup_time' => $request->input('pickup_time'),
            'selected_item_ids' => $request->input('selected_item_ids'),
        ]);

        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return redirect()->route('account')
                ->with('error', 'Bạn cần đăng ký thông tin độc giả trước khi sử dụng chức năng đặt trước.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'selected_item_ids' => 'required|array|min:1',
            'selected_item_ids.*' => 'integer',
            'pickup_time' => 'nullable|date_format:H:i',
        ]);

        $openHour = config('library.open_hour', '08:00');
        $closeHour = config('library.close_hour', '20:00');

        $cart = ReservationCart::with(['items.book'])->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Giỏ đặt trước đang trống.');
        }

        if ($cart->reader_id !== $reader->id) {
            $cart->update(['reader_id' => $reader->id]);
        }

        $selectedItemIds = collect($request->input('selected_item_ids', []))
            ->map(fn ($itemId) => (int) $itemId)
            ->filter(fn ($itemId) => $itemId > 0)
            ->unique()
            ->values();

        $selectedItems = $cart->items->whereIn('id', $selectedItemIds->all())->values();

        if ($selectedItems->isEmpty()) {
            return back()->with('error', 'Vui lòng chọn ít nhất 1 cuốn sách hợp lệ để đặt trước.');
        }

        $minBorrowDays = (int) config('library.borrow_min_days', 1);
        $maxBorrowDays = (int) config('library.borrow_max_days', 14);
        $minBorrowBooks = (int) config('library.borrow_min_books', 1);
        $maxBorrowBooks = (int) config('library.borrow_max_books', 5);

        $totalQuantity = (int) $selectedItems->sum('quantity');
        if ($totalQuantity < $minBorrowBooks) {
            return back()->with('error', "Bạn cần đặt tối thiểu {$minBorrowBooks} cuốn.");
        }

        if ($totalQuantity > $maxBorrowBooks) {
            return back()->with('error', "Bạn chỉ được đặt tối đa {$maxBorrowBooks} cuốn trong một đơn.");
        }

        $pickupTime = $request->input('pickup_time') ?: ($cart->items->first()?->pickup_time);
        if (empty($pickupTime)) {
            return back()->with('error', 'Vui lòng chọn giờ lấy cho đơn đặt trước.');
        }

        if ($pickupTime < $openHour || $pickupTime > $closeHour) {
            return back()->with('error', "Giờ lấy sách phải trong khoảng {$openHour} - {$closeHour}.");
        }

        // Cập nhật pickup_time cho tất cả items
        $cart->items()->update(['pickup_time' => $pickupTime]);

        // Lấy dữ liệu ngày từ request và kiểm tra
        $itemsData = $request->input('items', []);
        foreach ($selectedItems as $item) {
            $itemId = $item->id;
            $pickupDate = $itemsData[$itemId]['pickup_date'] ?? null;
            $returnDate = $itemsData[$itemId]['return_date'] ?? null;

            // Cập nhật ngày vào database
            if ($pickupDate && $returnDate) {
                $item->update([
                    'pickup_date' => $pickupDate,
                    'return_date' => $returnDate,
                ]);
            }

            // Kiểm tra ngày
            if (empty($pickupDate) || empty($returnDate)) {
                return back()->with('error', 'Vui lòng chọn đầy đủ ngày lấy và ngày trả cho các sách đã chọn.');
            }

            $pickup = Carbon::parse($pickupDate)->startOfDay();
            $return = Carbon::parse($returnDate)->startOfDay();
            // Mượn + trả cùng ngày = 1 ngày
            $days = max(1, $pickup->diffInDays($return) + 1);

            if ($days < $minBorrowDays || $days > $maxBorrowDays) {
                return back()->with('error', "Thời gian mượn phải từ {$minBorrowDays} đến {$maxBorrowDays} ngày.");
            }
        }

        try {
            $reservationCode = 'RSV' . now()->format('ymdHis') . strtoupper(substr(md5($user->id . microtime(true)), 0, 4));

            $result = $cart->submitReservations(
                $request->input('notes'),
                $selectedItemIds->all(),
                $pickupTime,
                $reservationCode
            );

            $submittedCopies = (int) ($result['submitted_copies'] ?? 0);

            return redirect()->route('reservation-cart.index')
                ->with('success', $submittedCopies > 0
                    ? "Đã gửi {$submittedCopies} yêu cầu đặt trước. Các sách chưa chọn vẫn được giữ lại trong giỏ."
                    : 'Không có sách nào được gửi đi.');
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
        $count = $cart ? $cart->item_count : 0;

        return response()->json(['count' => $count]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return redirect()->route('account')
                ->with('error', 'Bạn cần đăng ký thông tin độc giả để xem lịch sử đặt trước.');
        }

        // Lấy danh sách đặt trước của user (group theo mã đơn để hiển thị theo cụm)
        $reservations = \App\Models\InventoryReservation::with(['book', 'inventory'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('reservation_cart.history', compact('reservations'));
    }

    public function confirmReadyGroup(Request $request, string $reservationCode)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return back()->with('error', 'Bạn cần đăng ký thông tin độc giả.');
        }

        $reservations = InventoryReservation::where('user_id', $user->id)
            ->where('reservation_code', $reservationCode)
            ->where('status', 'ready')
            ->get();

        if ($reservations->isEmpty()) {
            return back()->with('error', 'Không tìm thấy đơn sẵn sàng để xác nhận.');
        }

        foreach ($reservations as $reservation) {
            $reservation->update([
                'customer_confirmed_at' => now(),
            ]);
        }

        return back()->with('success', 'Bạn đã xác nhận sẽ đến nhận sách.');
    }

    public function cancelReadyGroup(Request $request, string $reservationCode)
    {
        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return back()->with('error', 'Bạn cần đăng ký thông tin độc giả.');
        }

        $reservations = InventoryReservation::where('user_id', $user->id)
            ->where('reservation_code', $reservationCode)
            ->where('status', 'ready')
            ->get();

        if ($reservations->isEmpty()) {
            return back()->with('error', 'Không tìm thấy đơn sẵn sàng để hủy.');
        }

        foreach ($reservations as $reservation) {
            $reservation->cancel('Khách xác nhận không đến nhận sách.', auth()->id());
        }

        return back()->with('success', 'Đã hủy đơn sẵn sàng theo yêu cầu của bạn.');
    }

    public function updateQuantity(Request $request, $itemId)
    {
        \Log::info('DEBUG updateQuantity called', [
            'itemId' => $itemId,
            'quantity' => $request->quantity,
            'all_input' => $request->all()
        ]);

        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng ký thông tin độc giả.',
            ], 422);
        }

        $maxBorrowBooks = (int) config('library.borrow_max_books', 5);

        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $maxBorrowBooks,
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
        \Log::info('DEBUG updateDates called', [
            'itemId' => $itemId,
            'request' => $request->all()
        ]);

        $user = $request->user();
        $reader = $user?->reader;

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng ký thông tin độc giả.',
            ], 422);
        }

        $minBorrowDays = (int) config('library.borrow_min_days', 1);
        $maxBorrowDays = (int) config('library.borrow_max_days', 14);

        $request->validate([
            'pickup_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'pickup_time' => 'nullable|date_format:H:i',
            'return_date' => 'required|date|date_format:Y-m-d|after:pickup_date',
        ]);

        $openHour = config('library.open_hour', '08:00');
        $closeHour = config('library.close_hour', '20:00');
        if ($request->filled('pickup_time')) {
            if ($request->pickup_time < $openHour || $request->pickup_time > $closeHour) {
                return response()->json([
                    'success' => false,
                    'message' => "Giờ lấy sách phải trong khoảng {$openHour} - {$closeHour}.",
                ], 422);
            }
        }

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

        $pickup = Carbon::parse($request->pickup_date)->startOfDay();
        $return = Carbon::parse($request->return_date)->startOfDay();
        $days = max(1, $pickup->diffInDays($return));

        if ($days < $minBorrowDays || $days > $maxBorrowDays) {
            return response()->json([
                'success' => false,
                'message' => "Thời gian mượn phải từ {$minBorrowDays} đến {$maxBorrowDays} ngày.",
            ], 422);
        }

        $result = $cart->updateDates(
            (int) $itemId,
            $request->pickup_date,
            $request->return_date,
            $request->pickup_time
        );

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
