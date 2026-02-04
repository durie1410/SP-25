<?php

namespace App\Http\Controllers;

use App\Models\BorrowCart;
use App\Models\BorrowCartItem;
use App\Models\Book;
use App\Models\Reader;
use App\Models\Inventory;
use App\Models\Wallet;
use App\Models\Voucher;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BorrowCartController extends Controller
{
    /**
     * Tính phí thuê và tiền cọc cho một item
     */
    private function calculateItemFees($book, $borrowDays)
    {
        // Check if book is null
        if (!$book) {
            return ['tien_coc' => 0, 'tien_thue' => 0];
        }

        $reader = auth()->user()->reader ?? null;
        $hasCard = $reader ? true : false;

        // Lấy inventory thực tế để tính chính xác
        $inventory = Inventory::where('book_id', $book->id)
            ->where('status', 'Co san')
            ->first();

        if (!$inventory) {
            // Nếu không có inventory, tạo sample với condition mặc định
            $inventory = new Inventory();
            $inventory->condition = 'Trung binh';
            $inventory->status = 'Co san';
        }

        $ngayMuon = now();
        $ngayHenTra = now()->addDays($borrowDays);

        return \App\Services\PricingService::calculateFees(
            $book,
            $inventory,
            $ngayMuon,
            $ngayHenTra,
            $hasCard
        );
    }

    /**
     * Lấy hoặc tạo giỏ sách cho user
     */
    private function getOrCreateCart()
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        $cart = BorrowCart::where('user_id', $user->id)->first();

        if (!$cart) {
            $reader = Reader::where('user_id', $user->id)->first();
            $cart = BorrowCart::create([
                'user_id' => $user->id,
                'reader_id' => $reader?->id,
                'total_items' => 0,
            ]);
        }

        return $cart;
    }

    /**
     * Tính lại phí thuê cho các item trong giỏ hàng có phí thuê = 0
     */
    private function recalculateFeesForCartItems($cart)
    {
        $updated = false;

        foreach ($cart->items as $item) {
            if (!$item->book) {
                continue;
            }

            // Tính lại phí nếu phí thuê = 0 và sách có giá > 0
            if (($item->tien_thue == 0 || $item->tien_thue === null) && ($item->book->gia ?? 0) > 0) {
                $fees = $this->calculateItemFees($item->book, $item->borrow_days ?? 14);
                $item->tien_coc = $fees['tien_coc'];
                $item->tien_thue = $fees['tien_thue'];
                $item->save();
                $updated = true;
            }
        }

        return $updated;
    }

    /**
     * Tạo số thẻ độc giả unique
     */
    private function generateUniqueReaderCardNumber()
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $soTheDocGia = 'RD' . strtoupper(Str::random(6));
            $exists = Reader::where('so_the_doc_gia', $soTheDocGia)->exists();
            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        // Nếu vẫn trùng sau 10 lần, thêm timestamp để đảm bảo unique
        if ($exists) {
            $soTheDocGia = 'RD' . strtoupper(Str::random(4)) . time();
        }

        return $soTheDocGia;
    }

    /**
     * Hiển thị giỏ sách
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem giỏ sách');
        }

        $cart = $this->getOrCreateCart();

        if (!$cart) {
            $cart = new BorrowCart();
            $cart->setRelation('items', collect());
        } else {
            $cart->load(['items.book', 'items.book.category']);

            // Dọn các item "mồ côi" (sách đã bị xóa khỏi hệ thống)
            $removedOrphanItems = false;
            foreach ($cart->items as $item) {
                if (!$item->book) {
                    $item->delete();
                    $removedOrphanItems = true;
                }
            }

            if ($removedOrphanItems) {
                // Reload lại quan hệ sau khi xóa
                $cart->load(['items.book', 'items.book.category']);
                // Cập nhật lại tổng số lượng
                $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);
            }

            // Tính lại phí thuê cho các item có phí thuê = 0 hoặc chưa được tính
            $this->recalculateFeesForCartItems($cart);
        }

        // Lấy reader để tự động tính phí từ địa chỉ
        $reader = auth()->user()->reader ?? null;

        return view('borrow-cart.index', compact('cart', 'reader'));
    }

    /**
     * Thêm sách vào giỏ sách
     */
    public function add(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'nullable|integer|min:1|max:10',
            'borrow_days' => 'nullable|integer|min:1|max:30',
            'distance' => 'nullable|numeric|min:0|max:10',
            'note' => 'nullable|string|max:1000',
        ]);

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thêm sách vào giỏ sách'
            ], 401);
        }

        $book = Book::findOrFail($request->book_id);

        // Kiểm tra số lượng có sẵn
        $availableCopies = Inventory::where('book_id', $book->id)
            ->where('status', 'Co san')
            ->count();

        $quantity = (int) ($request->input('quantity', 1));
        $borrowDays = (int) ($request->input('borrow_days', 14));
        // Khoảng cách luôn là 0 - không cho nhập thủ công
        $distance = 0;
        $note = $request->input('note', '');

        if ($quantity > $availableCopies) {
            return response()->json([
                'success' => false,
                'message' => "Chỉ còn {$availableCopies} cuốn sách có sẵn. Vui lòng chọn lại số lượng."
            ], 400);
        }

        try {
            $cart = $this->getOrCreateCart();

            // Tính phí thuê và tiền cọc
            $fees = $this->calculateItemFees($book, $borrowDays);

            // Kiểm tra xem sách đã có trong giỏ sách chưa
            $existingItem = $cart->items()->where('book_id', $book->id)->first();

            if ($existingItem) {
                // Cập nhật số lượng nếu đã có
                $newQuantity = $existingItem->quantity + $quantity;
                if ($newQuantity > $availableCopies) {
                    return response()->json([
                        'success' => false,
                        'message' => "Số lượng tối đa có thể mượn là {$availableCopies} cuốn."
                    ], 400);
                }
                $existingItem->update([
                    'quantity' => $newQuantity,
                    'borrow_days' => $borrowDays,
                    'distance' => $distance,
                    'note' => $note ?: $existingItem->note,
                    'tien_coc' => $fees['tien_coc'],
                    'tien_thue' => $fees['tien_thue'],
                ]);
                $item = $existingItem;
            } else {
                // Tạo mới item
                $item = $cart->items()->create([
                    'book_id' => $book->id,
                    'quantity' => $quantity,
                    'borrow_days' => $borrowDays,
                    'distance' => $distance,
                    'note' => $note,
                    'tien_coc' => $fees['tien_coc'],
                    'tien_thue' => $fees['tien_thue'],
                ]);
            }

            // Cập nhật tổng số items
            $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm sách vào giỏ sách',
                'cart_count' => $cart->getTotalItemsAttribute(),
                'data' => [
                    'item_id' => $item->id,
                    'book_id' => $book->id,
                    'quantity' => $item->quantity,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding to borrow cart', [
                'message' => $e->getMessage(),
                'book_id' => $book->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thêm sách vào giỏ sách: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật item trong giỏ sách
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1|max:10',
            'borrow_days' => 'nullable|integer|min:1|max:30',
            'distance' => 'nullable|numeric|min:0|max:1000', // Cho phép khoảng cách lớn hơn
            'note' => 'nullable|string|max:1000',
            'is_selected' => 'nullable|boolean',
        ]);

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $item = BorrowCartItem::whereHas('cart', function ($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $book = $item->book;

        // Check if book exists
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Sách không tồn tại. Vui lòng xóa khỏi giỏ sách.'
            ], 404);
        }

        $availableCopies = Inventory::where('book_id', $book->id)
            ->where('status', 'Co san')
            ->count();

        $needRecalculateFees = false;

        if ($request->has('quantity')) {
            $quantity = (int) $request->input('quantity');
            if ($quantity > $availableCopies) {
                return response()->json([
                    'success' => false,
                    'message' => "Chỉ còn {$availableCopies} cuốn sách có sẵn.",
                    'available' => $availableCopies
                ], 400);
            }
            $item->quantity = $quantity;
        }

        if ($request->has('borrow_days')) {
            $item->borrow_days = (int) $request->input('borrow_days');
            $needRecalculateFees = true;
        }

        // Cho phép cập nhật khoảng cách từ request
        if ($request->has('distance')) {
            $item->distance = max(0, floatval($request->input('distance')));
        }

        if ($request->has('note')) {
            $item->note = $request->input('note');
        }

        if ($request->has('is_selected')) {
            $item->is_selected = $request->input('is_selected');
        }

        // Tính lại phí nếu borrow_days thay đổi hoặc phí thuê = 0
        if ($needRecalculateFees || ($item->tien_thue == 0 && ($book->gia ?? 0) > 0)) {
            $fees = $this->calculateItemFees($book, $item->borrow_days);
            $item->tien_coc = $fees['tien_coc'];
            $item->tien_thue = $fees['tien_thue'];
        }

        $item->save();

        // Cập nhật tổng số items
        $cart = $item->cart;
        $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật giỏ sách',
            'cart_count' => $cart->getTotalItemsAttribute(),
            'item' => [
                'tien_thue' => $item->tien_thue,
                'tien_coc' => $item->tien_coc,
                'borrow_days' => $item->borrow_days,
            ],
        ]);
    }

    /**
     * Xóa item khỏi giỏ sách
     */
    public function remove($id)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $item = BorrowCartItem::whereHas('cart', function ($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $cart = $item->cart;
        $item->delete();

        // Cập nhật tổng số items
        $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sách khỏi giỏ sách',
            'cart_count' => $cart->getTotalItemsAttribute(),
        ]);
    }

    /**
     * Xóa toàn bộ giỏ sách
     */
    public function clear()
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $cart = $this->getOrCreateCart();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['total_items' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ sách',
        ]);
    }

    /**
     * Lấy số lượng items trong giỏ sách (API)
     */
    public function count()
    {
        if (!auth()->check()) {
            return response()->json(['count' => 0]);
        }

        $cart = $this->getOrCreateCart();
        return response()->json([
            'count' => $cart ? $cart->getTotalItemsAttribute() : 0
        ]);
    }

    /**
     * Hiển thị trang checkout cho mượn sách   *///
    public function showCheckout(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đặt mượn sách');
        }

        $user = auth()->user();

        // Kiểm tra thông tin user có đầy đủ không
        if (!$user->hasCompleteProfile()) {
            $missingFields = $user->getMissingFields();
            return redirect()->route('account')
                ->with('error', 'Vui lòng cập nhật đầy đủ thông tin cá nhân trước khi mượn sách. Các trường còn thiếu: ' . implode(', ', $missingFields));
        }

        // Tự động tạo reader nếu chưa có (từ thông tin user)
        $reader = $user->reader;
        if (!$reader) {
            // Tạo số thẻ độc giả unique
            $soTheDocGia = $this->generateUniqueReaderCardNumber();

            // Tạo reader từ thông tin user
            $reader = Reader::create([
                'user_id' => $user->id,
                'ho_ten' => $user->name,
                'email' => $user->email,
                'so_dien_thoai' => $user->phone,
                'so_cccd' => $user->so_cccd,
                'ngay_sinh' => $user->ngay_sinh,
                'gioi_tinh' => $user->gioi_tinh,
                'dia_chi' => $user->address,
                'so_the_doc_gia' => $soTheDocGia,
                'ngay_cap_the' => now(),
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => 'Hoat dong',
            ]);
        }

        if ($reader->trang_thai !== 'Hoat dong') {
            return redirect()->back()->with('error', 'Thẻ độc giả của bạn đã bị khóa hoặc tạm dừng.');
        }

        if ($reader->ngay_het_han < now()->toDateString()) {
            return redirect()->back()->with('error', 'Thẻ độc giả của bạn đã hết hạn.');
        }

        $fromUrl = $request->has('book_id') || $request->has('items_json') || $request->has('items');

        $items = [];
        $cart = null;
        $totalTienCoc = 0;
        $totalTienThue = 0;
        $totalTienShip = 0;

        if ($fromUrl) {
            // --- LUỒNG MƯỢN TRỰC TIẾP ---
            $raw = $request->input('items_json') ?? $request->input('items') ?? null;
            $rawItems = [];

            if ($raw) {
                $rawItems = is_string($raw) ? json_decode($raw, true) : (array) $raw;
            }

            if (empty($rawItems) && $request->has('book_id')) {
                $rawItems[] = [
                    'book_id' => $request->input('book_id'),
                    'quantity' => $request->input('quantity', 1),
                    'borrow_days' => $request->input('borrow_days', 14),
                    'distance' => $request->input('distance', 0),
                    'note' => $request->input('note', ''),
                ];
            }

            $maxDistance = 0; // Lưu khoảng cách xa nhất để tính ship fee

            foreach ($rawItems as $rawItem) {
                $book = Book::find($rawItem['book_id'] ?? null);
                if (!$book)
                    continue;

                $quantity = max(1, intval($rawItem['quantity'] ?? 1));
                $borrowDays = max(1, intval($rawItem['borrow_days'] ?? 14));
                // Đọc khoảng cách từ request, mặc định là 0
                $distance = max(0, floatval($rawItem['distance'] ?? 0));
                $note = $rawItem['note'] ?? '';

                // Cập nhật khoảng cách xa nhất để tính ship fee
                if ($distance > $maxDistance) {
                    $maxDistance = $distance;
                }

                $fees = $this->calculateItemFees($book, $borrowDays);

                $items[] = [
                    'book' => $book,
                    'quantity' => $quantity,
                    'borrow_days' => $borrowDays,
                    'distance' => $distance,
                    'note' => $note,
                    'tien_coc' => $fees['tien_coc'],
                    'tien_thue' => $fees['tien_thue'],
                    'total' => ($fees['tien_coc'] + $fees['tien_thue']) * $quantity,
                ];

                $totalTienCoc += $fees['tien_coc'] * $quantity;
                $totalTienThue += $fees['tien_thue'] * $quantity;
            }

            if ($maxDistance > 0) {
                $shippingService = new ShippingService();
                $totalTienShip = $shippingService->calculateShippingFee($maxDistance);
            } else {
                // Mặc định là 20k cho Hà Nội nếu chưa có địa chỉ cụ thể
                $totalTienShip = 20000;
            }

        } else {
            // --- LUỒNG MƯỢN TỪ GIỎ HÀNG ---
            $cart = $this->getOrCreateCart();
            if (!$cart || $cart->items()->count() === 0) {
                return redirect()->route('borrow-cart.index')->with('error', 'Giỏ sách của bạn đang trống');
            }

            $selectedCount = $cart->items()->where('is_selected', true)->count();
            if ($selectedCount === 0) {
                $cart->items()->update(['is_selected' => true]);
            }

            $cart->load([
                'items' => function ($query) {
                    $query->where('is_selected', true);
                },
                'items.book'
            ]);

            if ($cart->items->count() === 0) {
                return redirect()->route('borrow-cart.index')->with('error', 'Giỏ sách của bạn đang trống');
            }

            $maxDistance = 0;
            foreach ($cart->items as $cartItem) {
                $book = $cartItem->book;

                // Skip if book doesn't exist
                if (!$book) {
                    continue;
                }

                $quantity = $cartItem->quantity;
                $borrowDays = $cartItem->borrow_days;
                $distance = $cartItem->distance;
                $note = $cartItem->note ?? '';

                // Cập nhật khoảng cách xa nhất
                if ($distance > $maxDistance) {
                    $maxDistance = $distance;
                }

                $fees = $this->calculateItemFees($book, $borrowDays);

                $items[] = [
                    'book' => $book,
                    'quantity' => $quantity,
                    'borrow_days' => $borrowDays,
                    'distance' => $distance,
                    'note' => $note,
                    'tien_coc' => $fees['tien_coc'],
                    'tien_thue' => $fees['tien_thue'],
                    'total' => ($fees['tien_coc'] + $fees['tien_thue']) * $quantity,
                ];

                $totalTienCoc += $fees['tien_coc'] * $quantity;
                $totalTienThue += $fees['tien_thue'] * $quantity;
            }

            // Tính phí ship dựa trên khoảng cách xa nhất từ cart items
            $totalTienShip = 0;
            if ($maxDistance > 0) {
                // Nếu có khoảng cách từ cart items, tính phí ship dựa trên đó
                $shippingService = new ShippingService();
                $totalTienShip = $shippingService->calculateShippingFee($maxDistance);
            } else if ($reader && !empty($reader->dia_chi)) {
                // Nếu không có khoảng cách, thử tính từ địa chỉ reader
                $diaChi = $reader->dia_chi;
                $addressParts = array_map('trim', explode(',', $diaChi));
                $addressParts = array_filter($addressParts);
                $addressParts = array_values($addressParts);

                if (count($addressParts) >= 2) {
                    // Có địa chỉ đầy đủ, tính phí ship
                    $shippingService = new ShippingService();
                    $shippingResult = $shippingService->calculateShipping($diaChi);

                    if ($shippingResult['success']) {
                        $totalTienShip = $shippingResult['shipping_fee'] ?? 0;
                    }
                }
            }
            // Mặc định phí ship tối thiểu là 20k cho Hà Nội
            if ($totalTienShip < 20000) {
                $totalTienShip = 20000;
            }
        }

        $tongTien = $totalTienCoc + $totalTienThue + $totalTienShip;

        // Ưu tiên địa chỉ từ request nếu có
        $reqTinh = $request->input('address_tinh');
        $reqXa = $request->input('address_xa');
        $reqSoNha = $request->input('address_sonha');
        $reqShipFee = $request->input('manual_shipping_fee');

        if ($reqShipFee !== null) {
            $totalTienShip = floatval($reqShipFee);
            $tongTien = $totalTienCoc + $totalTienThue + $totalTienShip;
        }

        // Lấy số dư ví của user
        $wallet = Wallet::getOrCreateForUser(auth()->id());
        $wallet->refresh(); // Đảm bảo lấy số dư mới nhất
        $walletBalance = $wallet->balance ?? 0;

        // Lấy danh sách voucher có sẵn cho khách chọn
        $today = now()->toDateString();
        $availableVouchers = Voucher::where('kich_hoat', 1)
            ->where('trang_thai', 'active')
            ->where(function($query) use ($today) {
                $query->whereNull('ngay_bat_dau')
                      ->orWhere('ngay_bat_dau', '<=', $today);
            })
            ->where(function($query) use ($today) {
                $query->whereNull('ngay_ket_thuc')
                      ->orWhere('ngay_ket_thuc', '>=', $today);
            })
            ->where('so_luong', '>', 0)
            ->where(function($query) use ($reader) {
                // Voucher dành cho tất cả hoặc voucher dành riêng cho user này
                $query->whereNull('reader_id')
                      ->orWhere('reader_id', $reader->id ?? null);
            })
            ->orderBy('gia_tri', 'desc')
            ->get();

        return view('borrow-cart.checkout', compact(
            'cart',
            'reader',
            'items',
            'totalTienCoc',
            'totalTienThue',
            'availableVouchers',
            'totalTienShip',
            'tongTien',
            'fromUrl',
            'walletBalance',
            'reqTinh',
            'reqXa',
            'reqSoNha'
        ));
    }

    /**
     * Áp dụng mã giảm giá
     */
    public function applyVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $voucherCode = trim($request->input('voucher_code'));
        $totalAmount = floatval($request->input('total_amount'));

        // Tìm voucher
        $voucher = Voucher::where('ma', $voucherCode)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại'
            ], 404);
        }

        // Kiểm tra voucher có kích hoạt không
        if ($voucher->kich_hoat != 1 || $voucher->trang_thai !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không còn hiệu lực'
            ], 400);
        }

        // Kiểm tra ngày hiệu lực
        $today = now()->toDateString();
        if ($voucher->ngay_bat_dau && $voucher->ngay_bat_dau > $today) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá chưa có hiệu lực'
            ], 400);
        }

        if ($voucher->ngay_ket_thuc && $voucher->ngay_ket_thuc < $today) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá đã hết hạn'
            ], 400);
        }

        // Kiểm tra số lượng còn lại
        if ($voucher->so_luong <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá đã hết lượt sử dụng'
            ], 400);
        }

        // Kiểm tra đơn tối thiểu
        if ($totalAmount < $voucher->don_toi_thieu) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng tối thiểu ' . number_format($voucher->don_toi_thieu, 0, ',', '.') . '₫ để áp dụng mã này'
            ], 400);
        }

        // Kiểm tra voucher có dành cho user cụ thể không
        $userId = auth()->id();
        if ($voucher->reader_id && $voucher->reader_id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá này không áp dụng cho tài khoản của bạn'
            ], 403);
        }

        // Tính toán giảm giá
        $discountAmount = 0;
        if ($voucher->loai === 'percentage') {
            $discountAmount = ($totalAmount * $voucher->gia_tri) / 100;
        } else { // fixed
            $discountAmount = min($voucher->gia_tri, $totalAmount); // Không giảm quá tổng tiền
        }

        $finalAmount = max(0, $totalAmount - $discountAmount);

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công',
            'voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->ma,
                'type' => $voucher->loai,
                'discount_value' => $voucher->gia_tri,
            ],
            'discount_amount' => $discountAmount,
            'original_amount' => $totalAmount,
            'final_amount' => $finalAmount,
        ]);
    }

    /**
     * Xử lý thanh toán online (VNPay, bank_transfer) - Lưu vào session, tạo đơn sau khi thanh toán thành công
     */
    private function handleOnlinePayment(Request $request, $reader, $items, $checkoutSource, $paymentMethod, $tinhThanh, $xa, $soNha, $notes)
    {
        try {
            // Tính toán phí và tổng tiền (không tạo đơn)
            $allBorrowItemsData = [];
            $totalTienCoc = 0;
            $totalTienThue = 0;
            $totalTienShip = 0;
            $ngayMuon = now()->toDateString();

            // Tính phí vận chuyển
            $manualShippingFee = $request->input('manual_shipping_fee');
            if ($manualShippingFee !== null) {
                $totalTienShip = floatval($manualShippingFee);
            } else {
                $totalTienShip = 20000; // Mặc định cho Hà Nội
            }

            // Xử lý voucher
            $voucherId = $request->input('voucher_id');
            $voucher = null;
            if ($voucherId) {
                $voucher = Voucher::find($voucherId);
                if ($voucher && $voucher->kich_hoat == 1 && $voucher->trang_thai === 'active') {
                    $today = now()->toDateString();
                    if (
                        (!$voucher->ngay_bat_dau || $voucher->ngay_bat_dau <= $today) &&
                        (!$voucher->ngay_ket_thuc || $voucher->ngay_ket_thuc >= $today) &&
                        $voucher->so_luong > 0
                    ) {
                        // Voucher hợp lệ
                    } else {
                        $voucher = null;
                    }
                } else {
                    $voucher = null;
                }
            }

            // Tính phí cho từng item
            foreach ($items as $item) {
                $book = $item->book;
                if (!$book) continue;

                $quantity = $item->quantity;
                $borrowDays = $item->borrow_days;
                $note = isset($item->note) ? $item->note : '';

                // Kiểm tra tồn kho
                $availableInventories = Inventory::where('book_id', $book->id)
                    ->where('status', 'Co san')
                    ->limit($quantity)
                    ->get();

                if ($availableInventories->count() < $quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Sách '{$book->ten_sach}' chỉ còn {$availableInventories->count()} cuốn có sẵn."
                    ], 400);
                }

                $itemData = [];
                foreach ($availableInventories as $index => $inventory) {
                    $fees = \App\Services\PricingService::calculateFees(
                        $book,
                        $inventory,
                        now(),
                        now()->addDays($borrowDays),
                        true
                    );

                    $itemShipFee = ($index === 0) ? $totalTienShip : 0;
                    
                    $itemData[] = [
                        'book_id' => $book->id,
                        'inventorie_id' => $inventory->id,
                        'quantity' => 1,
                        'borrow_days' => $borrowDays,
                        'tien_coc' => $fees['tien_coc'],
                        'tien_thue' => $fees['tien_thue'],
                        'tien_ship' => $itemShipFee,
                        'note' => $note ?: "Yêu cầu mượn - {$quantity} cuốn - {$borrowDays} ngày",
                    ];

                    $totalTienCoc += $fees['tien_coc'];
                    $totalTienThue += $fees['tien_thue'];
                }
                $allBorrowItemsData = array_merge($allBorrowItemsData, $itemData);
            }

            // Tính tổng tiền và áp dụng voucher
            $tongTienTruocGiam = $totalTienCoc + $totalTienThue + $totalTienShip;
            $discountAmount = 0;
            
            if ($voucher && $tongTienTruocGiam >= $voucher->don_toi_thieu) {
                if ($voucher->loai === 'percentage') {
                    $discountAmount = ($tongTienTruocGiam * $voucher->gia_tri) / 100;
                } else {
                    $discountAmount = min($voucher->gia_tri, $tongTienTruocGiam);
                }
            }
            
            $tongTienSauGiam = max(0, $tongTienTruocGiam - $discountAmount);

            // Lưu tất cả thông tin vào session để tạo đơn sau khi thanh toán thành công
            $checkoutData = [
                'reader_id' => $reader->id,
                'reader_name' => $request->input('reader_name'),
                'reader_phone' => $request->input('reader_phone'),
                'reader_email' => $request->input('reader_email'),
                'reader_cccd' => $request->input('reader_cccd'),
                'reader_birthday' => $request->input('reader_birthday'),
                'reader_gender' => $request->input('reader_gender'),
                'tinh_thanh' => $tinhThanh,
                'xa' => $xa,
                'so_nha' => $soNha,
                'notes' => $notes,
                'checkout_source' => $checkoutSource,
                'items' => $allBorrowItemsData,
                'total_tien_coc' => $totalTienCoc,
                'total_tien_thue' => $totalTienThue,
                'total_tien_ship' => $totalTienShip,
                'tong_tien' => $tongTienSauGiam,
                'voucher_id' => $voucher ? $voucher->id : null,
                'discount_amount' => $discountAmount,
                'ngay_muon' => $ngayMuon,
            ];

            session(['pending_checkout_data' => $checkoutData]);

            // Tạo payment với trạng thái pending (chưa có borrow_id)
            $transactionCode = 'BRW_PENDING_' . time();
            $payment = \App\Models\BorrowPayment::create([
                'borrow_id' => null, // Sẽ cập nhật sau khi tạo đơn
                'amount' => $tongTienSauGiam,
                'payment_type' => 'deposit',
                'payment_method' => ($paymentMethod === 'vnpay') ? 'online' : 'online',
                'payment_status' => 'pending',
                'transaction_code' => $transactionCode,
                'note' => ($paymentMethod === 'vnpay' ? 'Thanh toán online qua VNPay' : 'Thanh toán chuyển khoản ngân hàng') . ' - Tiền cọc, phí thuê và phí vận chuyển',
            ]);

            // Lưu payment_id vào session để xử lý callback
            session(['vnpay_payment_id' => $payment->id]);
            session(['vnpay_transaction_code' => $transactionCode]);

            // Nếu là VNPay, redirect đến cổng thanh toán
            if ($paymentMethod === 'vnpay') {
                $vnpayService = app(\App\Services\VnPayService::class);
                
                $orderInfo = sprintf(
                    "Thanh toán mượn sách - %s",
                    $request->input('reader_name', 'Khách hàng')
                );

                $paymentData = [
                    'amount' => $tongTienSauGiam,
                    'order_info' => $orderInfo,
                    'order_id' => $transactionCode,
                    'order_type' => 'billpayment',
                    'bank_code' => ''
                ];

                $paymentUrl = $vnpayService->createPaymentUrl($paymentData, $request);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'payment_required' => true,
                        'payment_url' => $paymentUrl,
                        'message' => 'Đang chuyển đến trang thanh toán VNPay...'
                    ]);
                }

                return redirect($paymentUrl);
            }

            // Nếu là bank_transfer, hiển thị thông tin chuyển khoản
            // (Có thể redirect đến trang hướng dẫn chuyển khoản)
            return redirect()->route('borrow-cart.checkout')
                ->with('bank_transfer_info', [
                    'amount' => $tongTienSauGiam,
                    'transaction_code' => $transactionCode,
                ])
                ->with('info', 'Vui lòng chuyển khoản theo thông tin bên dưới. Đơn hàng sẽ được tạo sau khi thanh toán thành công.');

        } catch (\Exception $e) {
            Log::error('Error handling online payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Checkout - Tạo yêu cầu mượn từ giỏ sách
     */
    public function processCheckout(Request $request)
    {
        // Validate input - Các trường địa chỉ bắt buộc, thêm thông tin khách hàng chi tiết
        $request->validate([
            'reader_name' => 'required|string|max:255',
            'reader_phone' => 'required|string|max:20',
            'reader_email' => 'required|email',
            'reader_cccd' => 'nullable|string|max:20',
            'reader_birthday' => 'required|date|before:today',
            'reader_gender' => 'required|in:Nam,Nu,Khac',
            'payment_method' => 'required|in:bank_transfer,vnpay,wallet,cod',
            'tinh_thanh' => 'required|string|max:100|in:Hà Nội',
            'xa' => 'required|string|max:200',
            'so_nha' => 'required|string|max:200',
            'notes' => 'nullable|string|max:1000',
            'book_id' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
            'borrow_days' => 'nullable|integer|min:1',
            'items_json' => 'nullable|string',
            'checkout_source' => 'nullable|string',
            'manual_shipping_fee' => 'nullable|numeric|min:0',
            'agree_terms' => 'required|accepted',
        ], [
            'agree_terms.required' => 'Vui lòng đồng ý với chính sách và điều khoản để tiếp tục.',
            'agree_terms.accepted' => 'Bạn phải đồng ý với chính sách và điều khoản để tiếp tục.',
        ]);

        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đặt mượn sách');
        }

        $user = auth()->user();

        // Kiểm tra thông tin user có đầy đủ không
        if (!$user->hasCompleteProfile()) {
            $missingFields = $user->getMissingFields();
            return redirect()->route('account')
                ->with('error', 'Vui lòng cập nhật đầy đủ thông tin cá nhân trước khi mượn sách. Các trường còn thiếu: ' . implode(', ', $missingFields));
        }

        // Tự động tạo reader nếu chưa có (từ thông tin user)
        $reader = $user->reader;
        if (!$reader) {
            // Tạo số thẻ độc giả unique
            $soTheDocGia = $this->generateUniqueReaderCardNumber();

            // Ghép địa chỉ đầy đủ từ form
            $tinhThanh = $request->input('tinh_thanh');
            $xa = $request->input('xa');
            $soNha = $request->input('so_nha', '');
            $diaChi = trim(($soNha ? $soNha . ', ' : '') . $xa . ', ' . $tinhThanh);

            // Tạo reader từ thông tin form và user
            $reader = Reader::create([
                'user_id' => $user->id,
                'ho_ten' => $request->input('reader_name'),
                'email' => $request->input('reader_email'),
                'so_dien_thoai' => $request->input('reader_phone'),
                'so_cccd' => $request->input('reader_cccd'),
                'ngay_sinh' => $request->input('reader_birthday'),
                'gioi_tinh' => $request->input('reader_gender'),
                'dia_chi' => $diaChi,
                'so_the_doc_gia' => $soTheDocGia,
                'ngay_cap_the' => now(),
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => 'Hoat dong',
            ]);
        } else {
            // Cập nhật thông tin reader từ form
            $reader->ho_ten = $request->input('reader_name');
            $reader->email = $request->input('reader_email');
            $reader->so_dien_thoai = $request->input('reader_phone');
            if ($request->has('reader_cccd')) {
                $reader->so_cccd = $request->input('reader_cccd');
            }
            $reader->ngay_sinh = $request->input('reader_birthday');
            $reader->gioi_tinh = $request->input('reader_gender');

            // Cập nhật địa chỉ
            $tinhThanh = $request->input('tinh_thanh');
            $xa = $request->input('xa');
            $soNha = $request->input('so_nha', '');
            $reader->dia_chi = trim(($soNha ? $soNha . ', ' : '') . $xa . ', ' . $tinhThanh);

            $reader->save();
        }

        if ($reader->trang_thai !== 'Hoat dong') {
            return redirect()->back()->with('error', 'Thẻ độc giả của bạn đã bị khóa hoặc tạm dừng. Vui lòng liên hệ thư viện.');
        }

        if ($reader->ngay_het_han < now()->toDateString()) {
            return redirect()->back()->with('error', 'Thẻ độc giả của bạn đã hết hạn. Vui lòng gia hạn thẻ.');
        }

        // Xác định nguồn checkout
        $checkoutSource = $request->input('checkout_source', 'cart');
        $items = collect();

        // Kiểm tra mượn từ URL params (items_json)
        if ($checkoutSource === 'url' && $request->has('items_json')) {
            try {
                $itemsData = json_decode($request->input('items_json'), true);
                if (is_array($itemsData) && count($itemsData) > 0) {
                    foreach ($itemsData as $itemData) {
                        $book = Book::find($itemData['book_id'] ?? null);
                        if ($book) {
                            $items->push((object) [
                                'book' => $book,
                                'quantity' => $itemData['quantity'] ?? 1,
                                'borrow_days' => $itemData['borrow_days'] ?? 14,
                                'distance' => $itemData['distance'] ?? 0,
                                'note' => $itemData['note'] ?? ''
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error parsing items_json', ['error' => $e->getMessage()]);
            }
        }

        // Nếu không có items từ URL, kiểm tra mượn trực tiếp từ book_id
        if ($items->isEmpty() && $request->has('book_id')) {
            $book = Book::find($request->input('book_id'));
            if ($book) {
                $items->push((object) [
                    'book' => $book,
                    'quantity' => $request->input('quantity', 1),
                    'borrow_days' => $request->input('borrow_days', 14),
                    'distance' => $request->input('distance', 0),
                    'note' => ''
                ]);
            }
        }

        // Nếu vẫn không có items, lấy từ giỏ sách
        if ($items->isEmpty()) {
            $cart = $this->getOrCreateCart();
            if (!$cart || $cart->items()->count() === 0) {
                return redirect()->route('borrow-cart.index')
                    ->with('error', 'Giỏ sách của bạn đang trống. Vui lòng thêm sách vào giỏ trước khi mượn.');
            }

            $cartItems = $cart->items()->where('is_selected', true)->with('book')->get();
            if ($cartItems->count() === 0) {
                // Nếu không có item nào được chọn, chọn tất cả
                $cart->items()->update(['is_selected' => true]);
                $cartItems = $cart->items()->with('book')->get();
            }

            if ($cartItems->count() === 0) {
                return redirect()->route('borrow-cart.index')
                    ->with('error', 'Giỏ sách của bạn đang trống. Vui lòng thêm sách vào giỏ trước khi mượn.');
            }

            $items = $cartItems;
        }

        // Kiểm tra cuối cùng
        if ($items->isEmpty()) {
            return redirect()->route('borrow-cart.index')
                ->with('error', 'Không có sách nào để mượn. Vui lòng thêm sách vào giỏ.');
        }

        try {
            // Lấy thông tin thanh toán và địa chỉ (đã loại bỏ huyen)
            $paymentMethod = $request->input('payment_method');
            $tinhThanh = $request->input('tinh_thanh', '');
            $xa = $request->input('xa', '');
            $soNha = $request->input('so_nha', '');
            $notes = $request->input('notes');
            
            // Với VNPay và bank_transfer: Lưu thông tin vào session, không tạo đơn ngay
            // Chỉ tạo đơn khi thanh toán thành công trong callback
            if (in_array($paymentMethod, ['vnpay', 'bank_transfer'])) {
                return $this->handleOnlinePayment($request, $reader, $items, $checkoutSource, $paymentMethod, $tinhThanh, $xa, $soNha, $notes);
            }
            
            // Với wallet và cod: Tạo đơn ngay vì đã thanh toán hoặc sẽ thanh toán sau
            DB::beginTransaction();

            $allBorrowItems = [];
            $totalTienCoc = 0;
            $totalTienThue = 0;
            $totalTienShip = 0;
            $ngayMuon = now()->toDateString();

            // Xử lý voucher nếu có
            $voucherId = $request->input('voucher_id');
            $voucher = null;
            $discountAmount = 0;

            if ($voucherId) {
                $voucher = Voucher::find($voucherId);
                if ($voucher && $voucher->kich_hoat == 1 && $voucher->trang_thai === 'active') {
                    // Validate lại voucher trước khi áp dụng
                    $today = now()->toDateString();
                    if (
                        (!$voucher->ngay_bat_dau || $voucher->ngay_bat_dau <= $today) &&
                        (!$voucher->ngay_ket_thuc || $voucher->ngay_ket_thuc >= $today) &&
                        $voucher->so_luong > 0
                    ) {
                        // Voucher hợp lệ, sẽ tính discount sau khi có tổng tiền
                    } else {
                        $voucher = null; // Voucher không còn hợp lệ
                    }
                } else {
                    $voucher = null;
                }
            }

            // Tạo Borrow (đã loại bỏ trường huyen)
            $borrow = \App\Models\Borrow::create([
                'reader_id' => $reader->id,
                'ten_nguoi_muon' => $request->input('reader_name'),
                'so_dien_thoai' => $request->input('reader_phone'),
                'tinh_thanh' => $tinhThanh,
                'huyen' => '', // Không còn sử dụng, để trống
                'xa' => $xa,
                'so_nha' => $soNha,
                'ngay_muon' => $ngayMuon,
                'trang_thai' => 'Cho duyet',
                'tien_coc' => 0,
                'tien_thue' => 0,
                'tien_ship' => 0,
                'tong_tien' => 0,
                'voucher_id' => $voucher ? $voucher->id : null,
                'ghi_chu' => trim($notes ?: ($checkoutSource === 'url' ? 'Mượn trực tiếp' : 'Đặt mượn từ giỏ sách')),
            ]);

            // Tính phí vận chuyển từ địa chỉ khách hàng
            // Ưu tiên sử dụng phí ship đã tính tự động từ form nếu có
            $manualShippingFee = $request->input('manual_shipping_fee');

            if ($manualShippingFee !== null) {
                // Sử dụng giá trị đã tính tự động từ form
                $totalTienShip = floatval($manualShippingFee);
                $distance = 0; // Không cần lưu khoảng cách nữa
            } else {
                // Tự động tính từ địa chỉ (nếu có Google Maps API Key)
                $shippingService = new ShippingService();

                // Ghép địa chỉ đầy đủ
                $fullAddress = '';
                if ($soNha)
                    $fullAddress .= $soNha . ', ';
                if ($xa)
                    $fullAddress .= $xa . ', ';
                if ($tinhThanh)
                    $fullAddress .= $tinhThanh . ', Việt Nam';

                if (!empty($fullAddress)) {
                    $shippingResult = $shippingService->calculateShipping($fullAddress);
                    $totalTienShip = $shippingResult['success'] ? $shippingResult['shipping_fee'] : 0;
                    $distance = $shippingResult['success'] ? $shippingResult['distance'] : 0;
                } else {
                    $totalTienShip = 0;
                    $distance = 0;
                }
            }

            $shipFeeAssigned = false;

            foreach ($items as $item) {
                $book = $item->book;

                // Skip if book doesn't exist
                if (!$book) {
                    continue;
                }

                $quantity = $item->quantity;
                $borrowDays = $item->borrow_days;
                $distance = isset($item->distance) ? $item->distance : 0;
                $note = isset($item->note) ? $item->note : '';
                $ngayHenTra = now()->addDays($borrowDays)->toDateString();

                // Kiểm tra tồn kho
                $availableInventories = Inventory::where('book_id', $book->id)
                    ->where('status', 'Co san')
                    ->limit($quantity)
                    ->get();

                if ($availableInventories->count() < $quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Sách '{$book->ten_sach}' chỉ còn {$availableInventories->count()} cuốn có sẵn."
                    ], 400);
                }

                foreach ($availableInventories as $index => $inventory) {
                    $fees = \App\Services\PricingService::calculateFees(
                        $book,
                        $inventory,
                        now(), // Sử dụng Carbon object thay vì string
                        now()->addDays($borrowDays), // Sử dụng Carbon object thay vì string
                        true // hasCard
                    );

                    // Chỉ gán phí ship cho item đầu tiên (tránh trùng lặp)
                    $itemShipFee = (!$shipFeeAssigned && $index === 0) ? $totalTienShip : 0;
                    if ($itemShipFee > 0) {
                        $shipFeeAssigned = true;
                    }

                    $borrowItem = \App\Models\BorrowItem::create([
                        'borrow_id' => $borrow->id,
                        'book_id' => $book->id,
                        'inventorie_id' => $inventory->id,
                        'ngay_muon' => $ngayMuon,
                        'ngay_hen_tra' => $ngayHenTra,
                        'trang_thai' => 'Cho duyet',
                        'tien_coc' => $fees['tien_coc'],
                        'tien_thue' => $fees['tien_thue'],
                        'tien_ship' => $itemShipFee,
                        'ghi_chu' => $note ?: "Yêu cầu mượn - {$quantity} cuốn - {$borrowDays} ngày",
                    ]);

                    $allBorrowItems[] = $borrowItem;
                    $totalTienCoc += $fees['tien_coc'];
                    $totalTienThue += $fees['tien_thue'];
                }
            }

            // Tính tổng tiền trước khi áp dụng voucher
            $tongTienTruocGiam = $totalTienCoc + $totalTienThue + $totalTienShip;

            // Áp dụng voucher nếu có
            if ($voucher) {
                // Kiểm tra đơn tối thiểu
                if ($tongTienTruocGiam >= $voucher->don_toi_thieu) {
                    if ($voucher->loai === 'percentage') {
                        $discountAmount = ($tongTienTruocGiam * $voucher->gia_tri) / 100;
                    } else { // fixed
                        $discountAmount = min($voucher->gia_tri, $tongTienTruocGiam);
                    }

                    // Giảm số lượng voucher
                    $voucher->so_luong = max(0, $voucher->so_luong - 1);
                    $voucher->save();
                } else {
                    // Không đủ điều kiện, bỏ voucher
                    $voucher = null;
                    $borrow->voucher_id = null;
                    $discountAmount = 0;
                }
            } else {
                $discountAmount = 0;
            }

            $tongTienSauGiam = max(0, $tongTienTruocGiam - $discountAmount);

            // Cập nhật tổng tiền
            // Nếu totalTienShip = 0, tính lại từ items
            if ($totalTienShip == 0) {
                $totalTienShip = $borrow->items()->sum('tien_ship');
            }
            
            $borrow->update([
                'tien_coc' => $totalTienCoc,
                'tien_thue' => $totalTienThue,
                'tien_ship' => $totalTienShip,
                'tong_tien' => $tongTienSauGiam,
                'voucher_id' => $voucher ? $voucher->id : null,
            ]);

            // Xử lý thanh toán bằng ví trước khi tạo payment record
            $totalAmount = $tongTienSauGiam;

            if ($paymentMethod === 'wallet') {
                $wallet = Wallet::getOrCreateForUser(auth()->id());
                $wallet->refresh(); // Đảm bảo lấy số dư mới nhất

                if ($wallet->balance < $totalAmount) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Số dư ví không đủ. Số dư hiện tại: ' . number_format((float) $wallet->balance, 0, ',', '.') . '₫. Vui lòng nạp thêm tiền vào ví.'
                    ], 400);
                }

                // Trừ tiền từ ví
                try {
                    $wallet->pay(
                        $totalAmount,
                        "Thanh toán mượn sách - Đơn #{$borrow->id}",
                        $borrow
                    );
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Wallet payment error', [
                        'error' => $e->getMessage(),
                        'borrow_id' => $borrow->id,
                        'user_id' => auth()->id(),
                        'amount' => $totalAmount
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Có lỗi khi thanh toán bằng ví: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Thanh toán
            $paymentMethodEnum = ($paymentMethod === 'cod') ? 'offline' : 'online';
            $transactionCode = 'BRW' . $borrow->id . '_' . time();
            $paymentNote = match ($paymentMethod) {
                'cod' => 'Thanh toán khi nhận hàng (COD)',
                'vnpay' => 'Thanh toán online qua VNPay',
                'bank_transfer' => 'Thanh toán chuyển khoản ngân hàng',
                'wallet' => 'Thanh toán qua ví điện tử',
                default => 'Thanh toán online'
            };

            // Xác định payment_status dựa trên phương thức thanh toán
            // VNPay → chờ xác nhận từ cổng thanh toán → pending
            // Bank Transfer, Wallet → đã thanh toán trước → success
            // Offline (COD) → thanh toán khi nhận hàng → pending
            $paymentStatus = ($paymentMethod === 'vnpay') ? 'pending' : (($paymentMethodEnum === 'online') ? 'success' : 'pending');

            $payment = \App\Models\BorrowPayment::create([
                'borrow_id' => $borrow->id,
                'amount' => $totalTienCoc + $totalTienThue + $totalTienShip,
                'payment_type' => 'deposit',
                'payment_method' => $paymentMethodEnum,
                'payment_status' => $paymentStatus,
                'transaction_code' => $transactionCode,
                'note' => $paymentNote . ' - Tiền cọc, phí thuê và phí vận chuyển',
            ]);

            // Nếu từ giỏ hàng, xóa item đã chọn
            if ($checkoutSource === 'cart') {
                $cart = $this->getOrCreateCart();
                if ($cart) {
                    $cart->items()->where('is_selected', true)->delete();
                    $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);
                }
            }

            DB::commit();

            // Nếu chọn thanh toán VNPay, chuyển hướng đến cổng thanh toán
            if ($paymentMethod === 'vnpay') {
                try {
                    // Lưu payment_id vào session để xử lý callback
                    session(['vnpay_payment_id' => $payment->id]);

                    // Khởi tạo VnPayService
                    $vnpayService = app(\App\Services\VnPayService::class);

                    // Tạo thông tin thanh toán
                    $orderInfo = sprintf(
                        "Thanh toán mượn sách - Đơn #%d - %s",
                        $borrow->id,
                        $request->input('reader_name', 'Khách hàng')
                    );

                    $paymentData = [
                        'amount' => $totalTienCoc + $totalTienThue + $totalTienShip,
                        'order_info' => $orderInfo,
                        'order_id' => $transactionCode,
                        'order_type' => 'billpayment',
                        'bank_code' => '' // Không truyền bank_code để hiển thị trang chọn phương thức
                    ];

                    // Tạo URL thanh toán VNPay
                    $paymentUrl = $vnpayService->createPaymentUrl($paymentData, $request);

                    // Nếu request là AJAX, trả về JSON với payment_url
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'payment_required' => true,
                            'payment_url' => $paymentUrl,
                            'message' => 'Đang chuyển đến trang thanh toán VNPay...'
                        ]);
                    }

                    // Nếu không, redirect trực tiếp
                    return redirect($paymentUrl);

                } catch (\Exception $e) {
                    Log::error('VNPay Payment URL Creation Error', [
                        'error' => $e->getMessage(),
                        'borrow_id' => $borrow->id
                    ]);

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Có lỗi khi tạo link thanh toán VNPay. Vui lòng thử lại sau.'
                        ], 500);
                    }

                    return redirect()->route('orders.index')
                        ->with('error', 'Có lỗi khi tạo link thanh toán VNPay. Vui lòng thử lại sau.');
                }
            }

            // Trả kết quả cho các phương thức thanh toán khác
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã tạo yêu cầu mượn sách thành công. Vui lòng chờ quản trị viên duyệt.',
                    'redirect_url' => route('orders.index')
                ]);
            }

            return redirect()->route('orders.index')
                ->with('success', 'Đã tạo yêu cầu mượn sách thành công. Vui lòng chờ quản trị viên duyệt.');


        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error checking out borrow', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo yêu cầu mượn: ' . (config('app.debug') ? $e->getMessage() : 'Vui lòng thử lại sau')
            ], 500);
        }
    }


}








// <?php

// namespace App\Http\Controllers;

// use App\Models\BorrowCart;
// use App\Models\BorrowCartItem;
// use App\Models\Book;
// use App\Models\Reader;
// use App\Models\Inventory;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;

// class BorrowCartController extends Controller
// {
//     /**
//      * Tính phí thuê và tiền cọc cho một item
//      */
//     private function calculateItemFees($book, $borrowDays)
//     {
//         $reader = auth()->user()->reader ?? null;
//         $hasCard = $reader ? true : false;

//         // Lấy inventory thực tế để tính chính xác
//         $inventory = Inventory::where('book_id', $book->id)
//             ->where('status', 'Co san')
//             ->first();

//         if (!$inventory) {
//             // Nếu không có inventory, tạo sample với condition mặc định
//             $inventory = new Inventory();
//             $inventory->condition = 'Trung binh';
//             $inventory->status = 'Co san';
//         }

//         $ngayMuon = now();
//         $ngayHenTra = now()->addDays($borrowDays);

//         return \App\Services\PricingService::calculateFees(
//             $book,
//             $inventory,
//             $ngayMuon,
//             $ngayHenTra,
//             $hasCard
//         );
//     }

//     /**
//      * Lấy hoặc tạo giỏ sách cho user
//      */
//     private function getOrCreateCart()
//     {
//         $user = auth()->user();
//         if (!$user) {
//             return null;
//         }

//         $cart = BorrowCart::where('user_id', $user->id)->first();

//         if (!$cart) {
//             $reader = Reader::where('user_id', $user->id)->first();
//             $cart = BorrowCart::create([
//                 'user_id' => $user->id,
//                 'reader_id' => $reader?->id,
//                 'total_items' => 0,
//             ]);
//         }

//         return $cart;
//     }

//     /**
//      * Hiển thị giỏ sách
//      */
//     public function index()
//     {
//         if (!auth()->check()) {
//             return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem giỏ sách');
//         }

//         $cart = $this->getOrCreateCart();

//         if (!$cart) {
//             $cart = new BorrowCart();
//             $cart->items = collect();
//         } else {
//             $cart->load(['items.book', 'items.book.category']);
//         }

//         return view('borrow-cart.index', compact('cart'));
//     }

//     /**
//      * Thêm sách vào giỏ sách
//      */
//     public function add(Request $request)
//     {
//         $request->validate([
//             'book_id' => 'required|exists:books,id',
//             'quantity' => 'nullable|integer|min:1|max:10',
//             'borrow_days' => 'nullable|integer|min:1|max:30',
//             'distance' => 'nullable|numeric|min:0',
//             'note' => 'nullable|string|max:1000',
//         ]);

//         if (!auth()->check()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Vui lòng đăng nhập để thêm sách vào giỏ sách'
//             ], 401);
//         }

//         $book = Book::findOrFail($request->book_id);

//         // Kiểm tra số lượng có sẵn
//         $availableCopies = Inventory::where('book_id', $book->id)
//             ->where('status', 'Co san')
//             ->count();

//         $quantity = (int) ($request->input('quantity', 1));
//         $borrowDays = (int) ($request->input('borrow_days', 14));
//         $distance = floatval($request->input('distance', 0));
//         $note = $request->input('note', '');

//         if ($quantity > $availableCopies) {
//             return response()->json([
//                 'success' => false,
//                 'message' => "Chỉ còn {$availableCopies} cuốn sách có sẵn. Vui lòng chọn lại số lượng."
//             ], 400);
//         }

//         try {
//             $cart = $this->getOrCreateCart();

//             // Tính phí thuê và tiền cọc
//             $fees = $this->calculateItemFees($book, $borrowDays);

//             // Kiểm tra xem sách đã có trong giỏ sách chưa
//             $existingItem = $cart->items()->where('book_id', $book->id)->first();

//             if ($existingItem) {
//                 // Cập nhật số lượng nếu đã có
//                 $newQuantity = $existingItem->quantity + $quantity;
//                 if ($newQuantity > $availableCopies) {
//                     return response()->json([
//                         'success' => false,
//                         'message' => "Số lượng tối đa có thể mượn là {$availableCopies} cuốn."
//                     ], 400);
//                 }
//                 $existingItem->update([
//                     'quantity' => $newQuantity,
//                     'borrow_days' => $borrowDays,
//                     'distance' => $distance,
//                     'note' => $note ?: $existingItem->note,
//                     'tien_coc' => $fees['tien_coc'],
//                     'tien_thue' => $fees['tien_thue'],
//                 ]);
//                 $item = $existingItem;
//             } else {
//                 // Tạo mới item
//                 $item = $cart->items()->create([
//                     'book_id' => $book->id,
//                     'quantity' => $quantity,
//                     'borrow_days' => $borrowDays,
//                     'distance' => $distance,
//                     'note' => $note,
//                     'tien_coc' => $fees['tien_coc'],
//                     'tien_thue' => $fees['tien_thue'],
//                 ]);
//             }

//             // Cập nhật tổng số items
//             $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Đã thêm sách vào giỏ sách',
//                 'cart_count' => $cart->getTotalItemsAttribute(),
//                 'data' => [
//                     'item_id' => $item->id,
//                     'book_id' => $book->id,
//                     'quantity' => $item->quantity,
//                 ]
//             ]);
//         } catch (\Exception $e) {
//             Log::error('Error adding to borrow cart', [
//                 'message' => $e->getMessage(),
//                 'book_id' => $book->id,
//                 'user_id' => auth()->id(),
//             ]);

//             return response()->json([
//                 'success' => false,
//                 'message' => 'Có lỗi xảy ra khi thêm sách vào giỏ sách: ' . $e->getMessage()
//             ], 500);
//         }
//     }

//     /**
//      * Cập nhật item trong giỏ sách
//      */
//     public function update(Request $request, $id)
//     {
//         $request->validate([
//             'quantity' => 'nullable|integer|min:1|max:10',
//             'borrow_days' => 'nullable|integer|min:1|max:30',
//             'distance' => 'nullable|numeric|min:0',
//             'note' => 'nullable|string|max:1000',
//             'is_selected' => 'nullable|boolean',
//         ]);

//         if (!auth()->check()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Vui lòng đăng nhập'
//             ], 401);
//         }

//         $item = BorrowCartItem::whereHas('cart', function($query) {
//             $query->where('user_id', auth()->id());
//         })->findOrFail($id);

//         $book = $item->book;
//         $availableCopies = Inventory::where('book_id', $book->id)
//             ->where('status', 'Co san')
//             ->count();

//         $needRecalculateFees = false;

//         if ($request->has('quantity')) {
//             $quantity = (int) $request->input('quantity');
//             if ($quantity > $availableCopies) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => "Chỉ còn {$availableCopies} cuốn sách có sẵn."
//                 ], 400);
//             }
//             $item->quantity = $quantity;
//         }

//         if ($request->has('borrow_days')) {
//             $item->borrow_days = (int) $request->input('borrow_days');
//             $needRecalculateFees = true;
//         }

//         if ($request->has('distance')) {
//             $item->distance = floatval($request->input('distance'));
//         }

//         if ($request->has('note')) {
//             $item->note = $request->input('note');
//         }

//         if ($request->has('is_selected')) {
//             $item->is_selected = $request->input('is_selected');
//         }

//         // Tính lại phí nếu borrow_days thay đổi
//         if ($needRecalculateFees) {
//             $fees = $this->calculateItemFees($book, $item->borrow_days);
//             $item->tien_coc = $fees['tien_coc'];
//             $item->tien_thue = $fees['tien_thue'];
//         }

//         $item->save();

//         // Cập nhật tổng số items
//         $cart = $item->cart;
//         $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Đã cập nhật giỏ sách',
//             'cart_count' => $cart->getTotalItemsAttribute(),
//         ]);
//     }

//     /**
//      * Xóa item khỏi giỏ sách
//      */
//     public function remove($id)
//     {
//         if (!auth()->check()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Vui lòng đăng nhập'
//             ], 401);
//         }

//         $item = BorrowCartItem::whereHas('cart', function($query) {
//             $query->where('user_id', auth()->id());
//         })->findOrFail($id);

//         $cart = $item->cart;
//         $item->delete();

//         // Cập nhật tổng số items
//         $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Đã xóa sách khỏi giỏ sách',
//             'cart_count' => $cart->getTotalItemsAttribute(),
//         ]);
//     }

//     /**
//      * Xóa toàn bộ giỏ sách
//      */
//     public function clear()
//     {
//         if (!auth()->check()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Vui lòng đăng nhập'
//             ], 401);
//         }

//         $cart = $this->getOrCreateCart();
//         if ($cart) {
//             $cart->items()->delete();
//             $cart->update(['total_items' => 0]);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Đã xóa toàn bộ giỏ sách',
//         ]);
//     }

//     /**
//      * Lấy số lượng items trong giỏ sách (API)
//      */
//     public function count()
//     {
//         if (!auth()->check()) {
//             return response()->json(['count' => 0]);
//         }

//         $cart = $this->getOrCreateCart();
//         return response()->json([
//             'count' => $cart ? $cart->getTotalItemsAttribute() : 0
//         ]);
//     }

//     /**
//      * Hiển thị trang checkout cho mượn sách
//      */

// // ...existing code...

// public function showCheckout(Request $request)
// {
//     if (!auth()->check()) {
//         return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đặt mượn sách');
//     }

//     $reader = Reader::where('user_id', auth()->id())->first();
//     if (!$reader) {
//         return redirect()->route('register.reader.form')
//             ->with('error', 'Bạn chưa có thẻ độc giả. Vui lòng đăng ký thẻ độc giả trước khi mượn sách.');
//     }

//     if ($reader->trang_thai !== 'Hoat dong') {
//         return redirect()->back()->with('error', 'Thẻ độc giả của bạn đã bị khóa hoặc tạm dừng.');
//     }

//     if ($reader->ngay_het_han < now()->toDateString()) {
//         return redirect()->back()->with('error', 'Thẻ độc giả của bạn đã hết hạn.');
//     }

//     $fromUrl = $request->has('book_id') || $request->has('items_json') || $request->has('items');

//     $items = [];
//     $cart = null;
//     $totalTienCoc = 0;
//     $totalTienThue = 0;
//     $totalTienShip = 0;

//     if ($fromUrl) {
//         // --- LUỒNG MƯỢN TRỰC TIẾP ---
//         $raw = $request->input('items_json') ?? $request->input('items') ?? null;
//         $rawItems = [];

//         if ($raw) {
//             $rawItems = is_string($raw) ? json_decode($raw, true) : (array)$raw;
//         }

//         if (empty($rawItems) && $request->has('book_id')) {
//             $rawItems[] = [
//                 'book_id' => $request->input('book_id'),
//                 'quantity' => $request->input('quantity', 1),
//                 'borrow_days' => $request->input('borrow_days', 14),
//                 'distance' => $request->input('distance', 0),
//                 'note' => $request->input('note', ''),
//             ];
//         }

//         foreach ($rawItems as $rawItem) {
//             $book = Book::find($rawItem['book_id'] ?? null);
//             if (!$book) continue;

//             $quantity = max(1, intval($rawItem['quantity'] ?? 1));
//             $borrowDays = max(1, intval($rawItem['borrow_days'] ?? 14));
//             $distance = floatval($rawItem['distance'] ?? 0);
//             $note = $rawItem['note'] ?? '';

//             $fees = $this->calculateItemFees($book, $borrowDays);

//             $items[] = [
//                 'book' => $book,
//                 'quantity' => $quantity,
//                 'borrow_days' => $borrowDays,
//                 'distance' => $distance,
//                 'note' => $note,
//                 'tien_coc' => $fees['tien_coc'],
//                 'tien_thue' => $fees['tien_thue'],
//                 'total' => ($fees['tien_coc'] + $fees['tien_thue']) * $quantity,
//             ];

//             $totalTienCoc += $fees['tien_coc'] * $quantity;
//             $totalTienThue += $fees['tien_thue'] * $quantity;

//             if ($totalTienShip === 0 && $distance > 5) {
//                 $totalTienShip = ceil($distance - 5) * 5000;
//             }
//         }

//     } else {
//         // --- LUỒNG MƯỢN TỪ GIỎ HÀNG ---
//         $cart = $this->getOrCreateCart();
//         if (!$cart || $cart->items()->count() === 0) {
//             return redirect()->route('borrow-cart.index')->with('error', 'Giỏ sách của bạn đang trống');
//         }

//         $selectedCount = $cart->items()->where('is_selected', true)->count();
//         if ($selectedCount === 0) {
//             $cart->items()->update(['is_selected' => true]);
//         }

//         $cart->load(['items' => function($query) {
//             $query->where('is_selected', true);
//         }, 'items.book']);

//         if ($cart->items->count() === 0) {
//             return redirect()->route('borrow-cart.index')->with('error', 'Giỏ sách của bạn đang trống');
//         }

//         $shipFeeCalculated = false;
//         foreach ($cart->items as $cartItem) {
//             $book = $cartItem->book;
//             $quantity = $cartItem->quantity;
//             $borrowDays = $cartItem->borrow_days;
//             $distance = $cartItem->distance;
//             $note = $cartItem->note ?? '';

//             $fees = $this->calculateItemFees($book, $borrowDays);

//             $items[] = [
//                 'book' => $book,
//                 'quantity' => $quantity,
//                 'borrow_days' => $borrowDays,
//                 'distance' => $distance,
//                 'note' => $note,
//                 'tien_coc' => $fees['tien_coc'],
//                 'tien_thue' => $fees['tien_thue'],
//                 'total' => ($fees['tien_coc'] + $fees['tien_thue']) * $quantity,
//             ];

//             $totalTienCoc += $fees['tien_coc'] * $quantity;
//             $totalTienThue += $fees['tien_thue'] * $quantity;

//             if (!$shipFeeCalculated && $distance > 5) {
//                 $totalTienShip = ceil($distance - 5) * 5000;
//                 $shipFeeCalculated = true;
//             }
//         }
//     }

//     $tongTien = $totalTienCoc + $totalTienThue + $totalTienShip;

//     return view('borrow-cart.checkout', compact(
//         'cart', 'reader', 'items',
//         'totalTienCoc', 'totalTienThue', 'totalTienShip', 'tongTien', 'fromUrl'
//     ));
// }

// // ...existing code...
// // ...existing code...
//     /**
//      * Process Checkout - Tạo yêu cầu mượn từ giỏ sách
//      */
//     public function processCheckout(Request $request)
//     {
//         // Validate input
//           $request->validate([
//         'items' => 'required|array|min:1',
//         'items.*.book_id' => 'required|exists:books,id',
//         'items.*.quantity' => 'nullable|integer|min:1|max:10',
//         'items.*.borrow_days' => 'nullable|integer|min:1|max:30',
//         'items.*.distance' => 'nullable|numeric|min:0',
//         'items.*.note' => 'nullable|string|max:1000',
//         'reader_name' => 'required|string|max:255',
//         'reader_phone' => 'required|string|max:20',
//         'reader_email' => 'required|email',
//         'payment_method' => 'required|in:bank_transfer,vnpay,wallet,cod',
//         'tinh_thanh' => 'nullable|string|max:100',
//         'huyen' => 'nullable|string|max:100',
//         'xa' => 'nullable|string|max:100',
//         'so_nha' => 'nullable|string|max:100',
//         'notes' => 'nullable|string|max:1000',
//     ]);

//     if (!auth()->check()) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Vui lòng đăng nhập để mượn sách'
//         ], 401);
//     }

//     $reader = Reader::where('user_id', auth()->id())->first();
//     if (!$reader) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Bạn chưa có thẻ độc giả. Vui lòng đăng ký thẻ độc giả trước khi mượn sách.',
//             'redirect' => route('register.reader.form')
//         ], 400);
//     }

//     if ($reader->trang_thai !== 'Hoat dong' || $reader->ngay_het_han < now()->toDateString()) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Thẻ độc giả không hợp lệ hoặc đã hết hạn.'
//         ], 400);
//     }

//     $itemsInput = $request->input('items');
//     $allBorrowItems = [];
//     $totalTienCoc = 0;
//     $totalTienThue = 0;
//     $totalTienShip = 0;
//     $ngayMuon = now()->toDateString();
//     $shipFeeCalculated = false;

//     DB::beginTransaction();
//     try {
//         // Tạo Borrow
//         $borrow = \App\Models\Borrow::create([
//             'reader_id' => $reader->id,
//             'ten_nguoi_muon' => $request->input('reader_name'),
//             'so_dien_thoai' => $request->input('reader_phone'),
//             'tinh_thanh' => $request->input('tinh_thanh', ''),
//             'huyen' => $request->input('huyen', ''),
//             'xa' => $request->input('xa', ''),
//             'so_nha' => $request->input('so_nha', ''),
//             'ngay_muon' => $ngayMuon,
//             'trang_thai' => 'Cho duyet',
//             'tien_coc' => 0,
//             'tien_thue' => 0,
//             'tien_ship' => 0,
//             'tong_tien' => 0,
//             'ghi_chu' => $request->input('notes', 'Đặt mượn trực tiếp'),
//         ]);

//         foreach ($itemsInput as $itemInput) {
//             $book = Book::find($itemInput['book_id']);
//             $quantity = intval($itemInput['quantity'] ?? 1);
//             $borrowDays = intval($itemInput['borrow_days'] ?? 14);
//             $distance = floatval($itemInput['distance'] ?? 0);
//             $note = $itemInput['note'] ?? '';

//             // Lấy inventory có sẵn
//             $availableInventories = Inventory::where('book_id', $book->id)
//                 ->where('status', 'Co san')
//                 ->limit($quantity)
//                 ->get();

//             if ($availableInventories->count() < $quantity) {
//                 DB::rollBack();
//                 return response()->json([
//                     'success' => false,
//                     'message' => "Sách '{$book->ten_sach}' chỉ còn {$availableInventories->count()} cuốn có sẵn."
//                 ], 400);
//             }

//             foreach ($availableInventories as $index => $inventory) {
//                 $fees = \App\Services\PricingService::calculateFees(
//                     $book,
//                     $inventory,
//                     $ngayMuon,
//                     now()->addDays($borrowDays)->toDateString(),
//                     true
//                 );

//                 $tienShip = 0;
//                 if (!$shipFeeCalculated && $distance > 5) {
//                     $extraKm = $distance - 5;
//                     $tienShip = (int) ($extraKm * 5000);
//                     $totalTienShip = $tienShip;
//                     $shipFeeCalculated = true;
//                 }

//                 $borrowItem = \App\Models\BorrowItem::create([
//                     'borrow_id' => $borrow->id,
//                     'book_id' => $book->id,
//                     'inventorie_id' => $inventory->id,
//                     'ngay_muon' => $ngayMuon,
//                     'ngay_hen_tra' => now()->addDays($borrowDays)->toDateString(),
//                     'trang_thai' => 'Cho duyet',
//                     'tien_coc' => $fees['tien_coc'],
//                     'tien_thue' => $fees['tien_thue'],
//                     'tien_ship' => $tienShip,
//                     'ghi_chu' => $note ?: "Mượn trực tiếp - {$quantity} cuốn - {$borrowDays} ngày",
//                 ]);

//                 $allBorrowItems[] = $borrowItem;
//                 $totalTienCoc += $fees['tien_coc'];
//                 $totalTienThue += $fees['tien_thue'];
//             }
//         }

//         // Cập nhật tổng tiền Borrow
//         $borrow->update([
//             'tien_coc' => $totalTienCoc,
//             'tien_thue' => $totalTienThue,
//             'tien_ship' => $totalTienShip,
//             'tong_tien' => $totalTienCoc + $totalTienThue + $totalTienShip,
//         ]);

//         // Tạo payment record
//         $paymentMethodEnum = $request->input('payment_method') === 'cod' ? 'offline' : 'online';
//         $transactionCode = 'BRW' . $borrow->id . '_' . time();
//         $paymentNote = match($request->input('payment_method')) {
//             'cod' => 'Thanh toán COD',
//             'vnpay' => 'Thanh toán online VNPay',
//             'bank_transfer' => 'Chuyển khoản ngân hàng',
//             'wallet' => 'Ví điện tử',
//             default => 'Thanh toán online'
//         };

//         \App\Models\BorrowPayment::create([
//             'borrow_id' => $borrow->id,
//             'amount' => $totalTienCoc + $totalTienThue + $totalTienShip,
//             'payment_type' => 'deposit',
//             'payment_method' => $paymentMethodEnum,
//             'payment_status' => 'pending',
//             'transaction_code' => $transactionCode,
//             'note' => $paymentNote,
//         ]);

//         DB::commit();

//         return response()->json([
//             'success' => true,
//             'message' => 'Đã tạo yêu cầu mượn trực tiếp thành công.',
//             'borrow_id' => $borrow->id,
//             'borrow_item_ids' => collect($allBorrowItems)->pluck('id')->toArray(),
//         ]);

//     } catch (\Exception $e) {
//         DB::rollBack();
//         \Log::error('Error in direct borrow', [
//             'message' => $e->getMessage(),
//             'trace' => $e->getTraceAsString()
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
//         ], 500);
//     }
// }
// }chả thấy lưu hay chuyển trang gì cả