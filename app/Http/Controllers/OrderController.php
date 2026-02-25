<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchasableBook;
use App\Models\Book;
use App\Models\Inventory;
use App\Models\Borrow;
use App\Models\Reader;
use App\Models\Wallet;
use App\Models\BorrowPayment;
 use App\Services\MomoService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Hiển thị trang checkout
     */
    public function checkout(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để mua hàng!');
        }

        // Nếu không có query params cho mua lẻ, thử lấy dữ liệu từ session (ví dụ: từ giỏ đặt trước)
        $sessionCheckout = Session::get('checkout_items');
        if (!$request->has('book_id') || !$request->has('paper_quantity')) {
            if (empty($sessionCheckout)) {
                return redirect()->back()->with('error', 'Vui lòng chọn sách và số lượng để mua hàng');
            }
        }

        try {
            // Nếu có dữ liệu session cho checkout (ví dụ reservation cart), sử dụng nó
            if (!empty($sessionCheckout) && !empty($sessionCheckout['reservation_cart'])) {
                $checkoutItems = collect();
                $selectedTotal = $sessionCheckout['total'] ?? 0;

                foreach ($sessionCheckout['items'] as $it) {
                    $checkoutItems->push((object) [
                        'purchasableBook' => (object) [
                            'ten_sach' => $it['ten_sach'] ?? '',
                            'tac_gia' => $it['tac_gia'] ?? ''
                        ],
                        'quantity' => $it['quantity'] ?? 1,
                        'total_price' => $it['total_price'] ?? 0,
                    ]);
                }

                // Lưu lại session để controller store sử dụng nếu cần
                Session::put('checkout_items', $sessionCheckout);

                return view('orders.checkout', compact('checkoutItems', 'selectedTotal'));
            }

            $bookId = $request->book_id;
            $paperQuantity = (int) $request->paper_quantity;
            
            // Validate số lượng
            if ($paperQuantity < 1 || $paperQuantity > 10) {
                return redirect()->back()->with('error', 'Số lượng sách không hợp lệ (1-10 cuốn)');
            }
            
            // Lấy hoặc tạo PurchasableBook
            $purchasableBook = $this->getOrCreatePurchasableBook($bookId, 'paper');
            
            // Kiểm tra tồn kho
            if (!$purchasableBook->isInStock()) {
                return redirect()->back()->with('error', 'Sách này đã hết hàng');
            }
            
            if ($purchasableBook->so_luong_ton < $paperQuantity) {
                return redirect()->back()->with('error', "Sách chỉ còn {$purchasableBook->so_luong_ton} bản trong kho");
            }
            
            // Tạo item để hiển thị trong checkout
            $checkoutItem = (object) [
                'purchasable_book_id' => $purchasableBook->id,
                'purchasableBook' => $purchasableBook,
                'quantity' => $paperQuantity,
                'price' => $purchasableBook->gia,
                'total_price' => $purchasableBook->gia * $paperQuantity,
            ];
            
            $checkoutItems = collect([$checkoutItem]);
            $selectedTotal = $checkoutItem->total_price;
            
            // Lưu thông tin vào session để sử dụng khi đặt hàng
            Session::put('checkout_items', [
                'book_id' => $purchasableBook->id,
                'quantity' => $paperQuantity,
            ]);

            return view('orders.checkout', compact('checkoutItems', 'selectedTotal'));
        } catch (\Exception $e) {
            \Log::error('Checkout error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý đặt hàng
     */
    public function store(Request $request)
    {
        // Log để debug
        \Log::info('OrderController@store called', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
        ]);
        
        // Đảm bảo chỉ xử lý POST request
        // Nếu là GET request, redirect về trang index
        if (!$request->isMethod('POST')) {
            \Log::warning('OrderController@store called with wrong method - redirecting to index', [
                'method' => $request->method(),
                'expected' => 'POST',
                'url' => $request->fullUrl()
            ]);
            
            // Nếu là GET request, redirect về trang orders.index
            if ($request->isMethod('GET')) {
                return redirect()->route('orders.index');
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed. Only POST requests are accepted.'
            ], 405);
        }
        
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_address' => 'nullable|string|min:0|max:500',
                'payment_method' => 'required|in:cash_on_delivery,momo,bank_transfer',
                'notes' => 'nullable|string|max:1000',
            ], [
                'customer_name.required' => 'Vui lòng nhập họ và tên',
                'customer_email.required' => 'Vui lòng nhập email',
                'customer_email.email' => 'Email không hợp lệ',
                'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
                'payment_method.in' => 'Phương thức thanh toán không hợp lệ',
            ]);
            
            // Log received payment method
            \Log::info('Order validation passed', [
                'payment_method_received' => $request->payment_method,
                'validated_payment_method' => $validated['payment_method'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        }

        // Lấy thông tin từ session (đã lưu khi vào trang checkout)
        $checkoutData = Session::get('checkout_items');
        
        // Nếu không có session data, tạo order test để test Momo payment
        // Tìm cuốn sách bất kỳ để tạo test order
        if (empty($checkoutData) || !isset($checkoutData['book_id']) || !isset($checkoutData['quantity'])) {
            \Log::warning('OrderController@store: No checkout data found, attempting to create test order', [
                'user_id' => Auth::id(),
                'payment_method' => $request->payment_method,
            ]);
            
            // Lấy cuốn sách đầu tiên từ purchasable_books để test
            $purchasableBook = PurchasableBook::active()->first();
            if (!$purchasableBook) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không tìm thấy sách có thể mua. Vui lòng thêm sách trước.',
                        'redirect_url' => route('home')
                    ], 400);
                }
                return redirect()->route('home')->with('error', 'Không tìm thấy sách trong hệ thống.');
            }
            
            // Use test quantity
            $quantity = 1;
            \Log::info('Using test book for order', [
                'purchasable_book_id' => $purchasableBook->id,
                'book_name' => $purchasableBook->ten_sach,
            ]);
        } else {
            // Lấy PurchasableBook
            $purchasableBook = PurchasableBook::findOrFail($checkoutData['book_id']);
            $quantity = $checkoutData['quantity'];
        }
        
        // Kiểm tra số lượng tồn kho trước khi đặt hàng
        if (!$purchasableBook->isInStock() || $purchasableBook->so_luong_ton < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Sách '{$purchasableBook->ten_sach}' không đủ hàng trong kho"
            ], 400);
        }
        
        // Tính tổng tiền
        $selectedTotal = $purchasableBook->gia * $quantity;
        
        // Tính phí vận chuyển tự động từ địa chỉ khách hàng
        $shippingService = new ShippingService();
        $shippingResult = $shippingService->calculateShipping($request->customer_address ?? '');
        
        $shippingAmount = $shippingResult['success'] ? $shippingResult['shipping_fee'] : 0;
        $distance = $shippingResult['success'] ? $shippingResult['distance'] : 0;
        
        // Tính tổng tiền bao gồm phí vận chuyển
        $totalAmount = $selectedTotal + $shippingAmount;
        
        // Tạo item để xử lý
        $orderItem = (object) [
            'purchasable_book_id' => $purchasableBook->id,
            'purchasableBook' => $purchasableBook,
            'quantity' => $quantity,
            'price' => $purchasableBook->gia,
            'total_price' => $selectedTotal,
        ];

        DB::beginTransaction();
        
        try {
            // Xác định payment_status dựa trên payment_method
            $paymentStatus = 'pending';
            if (in_array($request->payment_method, ['cash_on_delivery', 'bank_transfer', 'momo'])) {
                // Với COD/Chuyển khoản/Momo, payment_status là 'pending' cho đến khi xác nhận
                $paymentStatus = 'pending';
            }
            
            // Tạo đơn hàng
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => Auth::id(),
                'session_id' => Session::getId(),
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'subtotal' => $selectedTotal,
                'tax_amount' => 0,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => $paymentStatus,
                'payment_method' => $request->payment_method,
                'notes' => trim(($request->notes ?? '') . ($distance > 0 ? " (Khoảng cách: {$distance}km)" : '')),
            ]);

            // Tạo order item và cập nhật số lượng tồn kho
            OrderItem::create([
                'order_id' => $order->id,
                'purchasable_book_id' => $orderItem->purchasable_book_id,
                'book_title' => $orderItem->purchasableBook->ten_sach,
                'book_author' => $orderItem->purchasableBook->tac_gia,
                'price' => $orderItem->price,
                'quantity' => $orderItem->quantity,
                'total_price' => $orderItem->total_price,
            ]);

            // Giảm số lượng tồn kho từ PurchasableBook
            $orderItem->purchasableBook->decreaseStock($orderItem->quantity);
            $orderItem->purchasableBook->incrementSales();
            
            // Giảm số lượng từ bảng books và inventories
            // Tìm Book tương ứng với PurchasableBook (dựa trên tên sách)
            $book = Book::where('ten_sach', $orderItem->purchasableBook->ten_sach)
                ->first();
            
            if ($book) {
                // Lấy số lượng cần giảm
                $quantityToDecrease = $orderItem->quantity;
                
                // Lấy các inventories có sẵn trong kho (storage_type = 'Kho' và status = 'Co san')
                $availableInventories = Inventory::where('book_id', $book->id)
                    ->where('storage_type', 'Kho')
                    ->where('status', 'Co san')
                    ->limit($quantityToDecrease)
                    ->get();
                
                // Cập nhật status của inventories từ 'Co san' sang 'Thanh ly' (đã bán)
                foreach ($availableInventories as $inventory) {
                    $inventory->update(['status' => 'Thanh ly']);
                }
                
                // Giảm so_luong từ bảng books
                // Nếu có inventories, chỉ giảm phần còn lại từ so_luong
                $inventoryCount = $availableInventories->count();
                $remainingQuantity = $quantityToDecrease - $inventoryCount;
                
                if ($remainingQuantity > 0) {
                    $book->decrement('so_luong', $remainingQuantity);
                }
                
                // Đảm bảo so_luong không âm
                if ($book->so_luong < 0) {
                    $book->update(['so_luong' => 0]);
                }
            }
            
            // Xóa session chứa thông tin checkout
            Session::forget('checkout_items');

            DB::commit();
            
            // Log thành công
            \Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $order->user_id
            ]);

            // Nếu là AJAX request, trả JSON với redirect_url
            // Nếu không phải AJAX, redirect trực tiếp
            if ($request->ajax() || $request->wantsJson()) {
                // Log payment method before checking
                \Log::info('Checking payment method for response', [
                    'payment_method' => $request->payment_method,
                    'is_momo' => $request->payment_method === 'momo',
                    'trimmed' => trim($request->payment_method),
                ]);
                
                // If payment via Momo, return QR info so frontend can display it instead of redirecting
if ($request->payment_method === 'momo') {

    // Gọi sang MomoController để tạo payment


$momoService = new MomoService();
$payUrl = $momoService->createPayment($order);

return response()->json([
    'success' => true,
    'payUrl' => $payUrl
]);
}

                
                \Log::info('Returning COD/other response', [
                    'payment_method' => $request->payment_method,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Đặt hàng thành công! Mã đơn hàng: ' . $order->order_number,
                    'order_number' => $order->order_number,
                    'redirect_url' => route('orders.index')
                ], 200)->header('Content-Type', 'application/json')
                  ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
                  ->header('Pragma', 'no-cache')
                  ->header('Expires', '0');
            }
            
            // Redirect trực tiếp cho non-AJAX requests
            return redirect()->route('orders.index')
                ->with('success', 'Đặt hàng thành công! Mã đơn hàng: ' . $order->order_number);

        } catch (\Exception $e) {
            DB::rollback();
            
            // Log lỗi chi tiết
            \Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đặt hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    /**
     * Hiển thị chi tiết đơn mượn cho khách hàng
     */
    public function show($id)
    {
        try {
            $borrow = Borrow::with(['items.book', 'items.inventory', 'reader', 'payments', 'voucher'])
                ->findOrFail($id);
            
            // Đảm bảo tien_ship được đồng bộ từ items nếu borrow->tien_ship = 0
            if (($borrow->tien_ship ?? 0) == 0 && $borrow->items && $borrow->items->count() > 0) {
                $tienShipFromItems = $borrow->items->sum('tien_ship');
                if ($tienShipFromItems > 0) {
                    $borrow->tien_ship = $tienShipFromItems;
                    // 重新计算总金额
                    $borrow->tong_tien = ($borrow->tien_coc ?? 0) + ($borrow->tien_thue ?? 0) + $tienShipFromItems;
                    $borrow->save();
                }
            }
            
            // Kiểm tra quyền truy cập - chỉ reader của đơn mượn mới được xem
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem chi tiết đơn mượn');
            }

            $reader = Auth::user()->reader;
            if (!$reader || $borrow->reader_id !== $reader->id) {
                abort(403, 'Bạn không có quyền xem đơn mượn này');
            }

            return view('orders.show', compact('borrow'));
            
        } catch (\Exception $e) {
            \Log::error('Error viewing borrow details', [
                'borrow_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('orders.index')
                ->with('error', 'Không tìm thấy đơn mượn này');
        }
    }

    /**
     * Hiển thị danh sách đơn mượn sách của user
     */
    public function index(Request $request)
    {
        // Log để debug
        \Log::info('OrderController@index called', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'is_api' => $request->is('api/*'),
            'expects_json' => $request->expectsJson(),
            'wants_json' => $request->wantsJson(),
            'accept' => $request->header('Accept'),
            'user_agent' => $request->header('User-Agent'),
        ]);
        
        // Đảm bảo chỉ xử lý GET request
        if (!$request->isMethod('GET')) {
            \Log::error('OrderController@index called with wrong method', [
                'method' => $request->method(),
                'expected' => 'GET'
            ]);
            abort(405, 'Method not allowed');
        }
        
        // Load relationship reader để sidebar hiển thị "Sách đang mượn" ngay
        if (Auth::check()) {
            Auth::user()->load('reader');
        }
        
        // Lấy đơn mượn sách (nếu user có reader)
        if (Auth::check()) {
            $reader = Auth::user()->reader;
            if ($reader) {
                // Lấy tất cả đơn mượn của reader, bao gồm cả giao_hang_that_bai
                $orders = Borrow::with(['items.book', 'reader', 'librarian', 'payments'])
                    ->where('reader_id', $reader->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                
                // Đồng bộ tổng tiền cho mỗi đơn (bao gồm ship)
                foreach ($orders as $order) {
                    $tienCoc = $order->tien_coc ?? 0;
                    $tienThue = $order->tien_thue ?? 0;
                    $tienShip = $order->tien_ship ?? 0;
                    
                    // Nếu ship = 0, tính từ items
                    if ($tienShip == 0 && $order->items && $order->items->count() > 0) {
                        $tienShip = $order->items->sum('tien_ship');
                    }
                    // Nếu vẫn = 0, mặc định 20k
                    if ($tienShip == 0) {
                        $tienShip = 20000;
                    }
                    
                    // Tính lại tổng tiền = cọc + thuê + ship
                    $tongTienRecalculated = $tienCoc + $tienThue + $tienShip;
                    
                    // Cập nhật vào order object để hiển thị (không lưu DB)
                    $order->tong_tien_display = $tongTienRecalculated;
                }
            } else {
                // User chưa có thẻ độc giả
                $orders = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect(),
                    0,
                    10,
                    1
                );
            }
        } else {
            // Chưa đăng nhập
            $orders = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10,
                1
            );
        }
        
        // Chỉ trả JSON nếu request từ API route (api/*)
        if ($request->is('api/*')) {
            \Log::info('Returning JSON for API request');
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        }
        
        // Force trả về HTML view cho browser với header rõ ràng
        \Log::info('Returning HTML view for web request', [
            'orders_count' => $orders->count(),
            'view_path' => 'orders.index'
        ]);
        
        // Force HTML response - không bao giờ trả JSON cho web route
        $response = response()->view('orders.index', compact('orders'));
        
        // Set headers để force HTML
        $response->header('Content-Type', 'text/html; charset=utf-8');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');
        
        return $response;
    }


    /**
     * Kiểm tra quyền truy cập đơn hàng
     */
    private function canAccessOrder($order)
    {
        if (Auth::check()) {
            return $order->user_id === Auth::id();
        } else {
            return $order->session_id === Session::getId();
        }
    }

    /**
     * Lấy hoặc tạo PurchasableBook từ Book
     */
    private function getOrCreatePurchasableBook($bookId, $type = 'paper')
    {
        // Kiểm tra xem book_id có phải là PurchasableBook không
        $purchasableBook = PurchasableBook::find($bookId);
        if ($purchasableBook) {
            return $purchasableBook;
        }
        
        // Nếu không phải, tìm Book và tạo PurchasableBook tương ứng
        $book = Book::findOrFail($bookId);
        
        // Tìm PurchasableBook đã tồn tại với cùng identifier (dựa trên tên sách)
        $purchasableBook = PurchasableBook::where('ten_sach', $book->ten_sach)
            ->first();
        
        if ($purchasableBook) {
            // Đồng bộ số lượng tồn kho từ inventories
            $availableStockForPurchase = Inventory::where('book_id', $book->id)
                ->where('storage_type', 'Kho')
                ->where('status', 'Co san')
                ->count();
            
            // Nếu không có trong inventories, sử dụng so_luong từ bảng books
            $stockQuantity = $availableStockForPurchase > 0 ? $availableStockForPurchase : ($book->so_luong ?? 0);
            
            // Cập nhật số lượng tồn kho
            $purchasableBook->update(['so_luong_ton' => $stockQuantity]);
            
            return $purchasableBook;
        }
        
        // Tạo mới PurchasableBook
        $price = $book->gia ?? 111000;
        
        // Load publisher nếu có
        $book->load('publisher');
        
        // Tính số lượng tồn kho từ inventories
        $availableStockForPurchase = Inventory::where('book_id', $book->id)
            ->where('storage_type', 'Kho')
            ->where('status', 'Co san')
            ->count();
        
        // Nếu không có trong inventories, sử dụng so_luong từ bảng books
        $stockQuantity = $availableStockForPurchase > 0 ? $availableStockForPurchase : ($book->so_luong ?? 0);
        
        $purchasableBook = PurchasableBook::create([
            'ten_sach' => $book->ten_sach,
            'tac_gia' => $book->tac_gia ?? 'Chưa cập nhật',
            'mo_ta' => $book->mo_ta,
            'hinh_anh' => $book->hinh_anh,
            'gia' => $price,
            'nha_xuat_ban' => $book->publisher ? $book->publisher->ten_nha_xuat_ban : null,
            'nam_xuat_ban' => $book->nam_xuat_ban,
            'isbn' => $book->isbn ?? null,
            'so_trang' => $book->so_trang ?? null,
            'ngon_ngu' => 'Tiếng Việt',
            'dinh_dang' => 'PAPER',
            'kich_thuoc_file' => null,
            'trang_thai' => 'active',
            'so_luong_ton' => $stockQuantity,
            'so_luong_ban' => 0,
            'danh_gia_trung_binh' => 0,
            'so_luot_xem' => 0,
        ]);
        
        return $purchasableBook;
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Kiểm tra quyền truy cập
        if (!$this->canAccessOrder($order)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền hủy đơn hàng này'
            ], 403);
        }
        
        // Kiểm tra đơn hàng có thể hủy không
        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này không thể hủy'
            ], 400);
        }
        
        // Validate lý do hủy
        try {
            $validated = $request->validate([
                'cancellation_reason' => 'required|string|min:10|max:500',
            ], [
                'cancellation_reason.required' => 'Vui lòng nhập lý do hủy đơn hàng',
                'cancellation_reason.min' => 'Lý do hủy đơn hàng phải có ít nhất 10 ký tự',
                'cancellation_reason.max' => 'Lý do hủy đơn hàng không được vượt quá 500 ký tự',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Cập nhật trạng thái đơn hàng và lý do hủy
            $order->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
            ]);
            
            // Hoàn lại số lượng tồn kho cho các sản phẩm trong đơn hàng
            foreach ($order->items as $item) {
                $purchasableBook = PurchasableBook::find($item->purchasable_book_id);
                if ($purchasableBook) {
                    // Hoàn lại số lượng cho PurchasableBook
                    $purchasableBook->increaseStock($item->quantity);
                    
                    // Giảm số lượng đã bán (so_luong_ban) khi hủy đơn
                    // Đảm bảo so_luong_ban không bị âm
                    if ($purchasableBook->so_luong_ban >= $item->quantity) {
                        $purchasableBook->decrement('so_luong_ban', $item->quantity);
                    } else {
                        // Nếu so_luong_ban nhỏ hơn số lượng hủy, đặt về 0
                        $purchasableBook->update(['so_luong_ban' => 0]);
                    }
                    
                    // Hoàn lại số lượng cho Book và inventories
                    $book = Book::where('ten_sach', $purchasableBook->ten_sach)
                        ->first();
                    
                    if ($book) {
                        // Hoàn lại inventories từ 'Thanh ly' về 'Co san' nếu có
                        $soldInventories = Inventory::where('book_id', $book->id)
                            ->where('storage_type', 'Kho')
                            ->where('status', 'Thanh ly')
                            ->limit($item->quantity)
                            ->get();
                        
                        $inventoryCount = $soldInventories->count();
                        foreach ($soldInventories as $inventory) {
                            $inventory->update(['status' => 'Co san']);
                        }
                        
                        // Tăng so_luong trong bảng books cho phần còn lại (nếu có)
                        $remainingQuantity = $item->quantity - $inventoryCount;
                        if ($remainingQuantity > 0) {
                            $book->increment('so_luong', $remainingQuantity);
                        }
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được hủy thành công'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Order cancellation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hủy đơn mượn sách (chỉ khi đang chờ xử lí)
     */
    public function cancelBorrow(Request $request, $id)
    {
        try {
            // Validate lí do hủy
            $validated = $request->validate([
                'cancellation_reason' => 'required|string|min:10|max:500',
            ], [
                'cancellation_reason.required' => 'Vui lòng nhập lí do hủy đơn mượn',
                'cancellation_reason.min' => 'Lí do hủy đơn phải có ít nhất 10 ký tự',
                'cancellation_reason.max' => 'Lí do hủy đơn không được vượt quá 500 ký tự',
            ]);
            
            $borrow = Borrow::with(['items.inventory', 'reader'])->findOrFail($id);
            
            // Kiểm tra quyền truy cập - chỉ reader của đơn mượn mới được hủy
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để thực hiện thao tác này'
                ], 401);
            }

            $reader = Auth::user()->reader;
            if (!$reader || $borrow->reader_id !== $reader->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền hủy đơn mượn này'
                ], 403);
            }
            
            // Kiểm tra trạng thái - chỉ có thể hủy khi đang chờ duyệt
            if ($borrow->trang_thai !== 'Cho duyet') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể hủy đơn mượn đang chờ xử lí'
                ], 400);
            }
            
            // Kiểm tra trạng thái chi tiết - không cho phép hủy khi đang vận chuyển
            $trangThaiChiTiet = $borrow->trang_thai_chi_tiet;
            $trangThaiKhongChoPhepHuy = [
                \App\Models\Borrow::STATUS_CHO_BAN_GIAO_VAN_CHUYEN,  // Chờ bàn giao vận chuyển
                \App\Models\Borrow::STATUS_DANG_GIAO_HANG,           // Đang giao hàng
                \App\Models\Borrow::STATUS_DANG_VAN_CHUYEN_TRA_VE,   // Đang vận chuyển trả về
            ];

            if (in_array($trangThaiChiTiet, $trangThaiKhongChoPhepHuy)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy đơn khi đang vận chuyển'
                ], 400);
            }
            
            // Kiểm tra thêm: nếu đã có trạng thái chi tiết và không phải đơn hàng mới thì không cho hủy
            if ($trangThaiChiTiet && $trangThaiChiTiet !== \App\Models\Borrow::STATUS_DON_HANG_MOI) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy đơn này. Đơn đã được xử lý.'
                ], 400);
            }
            
            DB::beginTransaction();
            
            try {
                // Cập nhật trạng thái đơn mượn thành Hủy với lí do
                $cancelNote = 'Đã hủy bởi khách hàng lúc ' . now()->format('d/m/Y H:i') . 
                             '. Lí do: ' . $validated['cancellation_reason'];
                
                $borrow->update([
                    'trang_thai' => 'Huy',
                    'ghi_chu' => ($borrow->ghi_chu ? $borrow->ghi_chu . ' | ' : '') . $cancelNote
                ]);
                
                // Cập nhật trạng thái các BorrowItem (sử dụng update trực tiếp để nhanh hơn)
                $borrow->items()->update(['trang_thai' => 'Huy']);
                
                // Hoàn lại inventory về trạng thái "Co san" nếu đã bị lock
                // Sử dụng query trực tiếp để tránh N+1 và nhanh hơn
                // Lưu ý: cột là 'inventorie_id' (có chữ 'e' ở cuối), không phải 'inventory_id'
                DB::table('inventories')
                    ->join('borrow_items', 'inventories.id', '=', 'borrow_items.inventorie_id')
                    ->where('borrow_items.borrow_id', $borrow->id)
                    ->whereNotNull('borrow_items.inventorie_id') // Chỉ update những item có inventory
                    ->where('inventories.status', 'Cho muon')
                    ->update(['inventories.status' => 'Co san']);
                
                DB::commit();
                
                // Lưu ý: Hoàn tiền sẽ được xử lý tự động bởi BorrowObserver
                // sau khi transaction commit, không block response
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
            \Log::info('Borrow cancelled by customer', [
                'borrow_id' => $borrow->id,
                'reader_id' => $reader->id,
                'reader_name' => $reader->ho_ten,
                'reason' => $validated['cancellation_reason']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã hủy đơn mượn thành công'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['cancellation_reason'][0] ?? 'Dữ liệu không hợp lệ'
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Borrow cancellation failed', [
                'borrow_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy đơn mượn: ' . $e->getMessage()
            ], 500);
        }
    }
}
