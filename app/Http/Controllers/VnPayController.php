<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\BorrowPayment;
use App\Models\Order;
use App\Services\VnPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VnPayController extends Controller
{
    protected $vnpayService;

    public function __construct(VnPayService $vnpayService)
    {
        $this->vnpayService = $vnpayService;
    }

    /**
     * Tạo thanh toán cho phiếu mượn sách
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createPayment(Request $request)
    {
        try {
            // Nếu có pending payment từ reservation cart (đặt trước), dùng session để tạo URL
            $sessionPaymentId = session('vnpay_payment_id');
            $sessionTxnRef = session('vnpay_transaction_code') ?: session('vnpay_txn_ref');
            if ($sessionPaymentId && $sessionTxnRef) {
                $payment = BorrowPayment::find($sessionPaymentId);
                if ($payment) {
                    $paymentData = [
                        'amount' => $payment->amount,
                        'order_info' => 'Thanh toán đặt trước - ' . ($payment->note ?? ''),
                        'order_id' => $sessionTxnRef,
                        'order_type' => 'billpayment',
                        'bank_code' => $request->bank_code
                    ];

                    // tạo url và redirect
                    $paymentUrl = $this->vnpayService->createPaymentUrl($paymentData, $request);
                    return redirect($paymentUrl);
                }
            }

            $request->validate([
                'borrow_id' => 'required|exists:borrows,id',
                'amount' => 'required|numeric|min:1000',
                'payment_type' => 'required|in:deposit,borrow_fee,shipping_fee,damage_fee',
                'bank_code' => 'nullable|string'
            ]);

            $borrowId = $request->borrow_id;
            $amount = $request->amount;
            $paymentType = $request->payment_type;

            // Lấy thông tin phiếu mượn
            $borrow = Borrow::with('reader')->findOrFail($borrowId);

            // Tạo thông tin thanh toán
            $paymentTypeText = [
                'deposit' => 'Tiền cọc',
                'borrow_fee' => 'Tiền thuê sách',
                'shipping_fee' => 'Phí vận chuyển',
                'damage_fee' => 'Phí đền bù'
            ];

            $orderInfo = sprintf(
                "%s - Phiếu mượn #%d - %s",
                $paymentTypeText[$paymentType],
                $borrowId,
                $borrow->reader->name ?? 'Khách hàng'
            );

            // Tạo mã giao dịch duy nhất
            $txnRef = 'BRW' . $borrowId . '_' . time();

            $paymentData = [
                'amount' => $amount,
                'order_info' => $orderInfo,
                'order_id' => $txnRef,
                'order_type' => 'billpayment', // Hoặc 'other'
                'bank_code' => $request->bank_code
            ];

            // Tạo bản ghi thanh toán với trạng thái pending
            $payment = BorrowPayment::create([
                'borrow_id' => $borrowId,
                'borrow_item_id' => $request->borrow_item_id ?? null,
                'amount' => $amount,
                'payment_type' => $paymentType,
                'payment_method' => 'online',
                'payment_status' => 'pending',
                'transaction_code' => $txnRef,
                'note' => 'Thanh toán qua VnPay'
            ]);

            // Lưu payment_id vào session để xử lý callback
            session(['vnpay_payment_id' => $payment->id]);

            // Tạo URL thanh toán
            $paymentUrl = $this->vnpayService->createPaymentUrl($paymentData, $request);

            return redirect($paymentUrl);

        } catch (\Exception $e) {
            Log::error('VnPay Create Payment Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi tạo thanh toán: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý callback từ VnPay
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        try {
            // Xử lý response từ VnPay
            $result = $this->vnpayService->handleCallback($request);

            Log::info('VnPay Callback', [
                'result' => $result,
                'request_data' => $request->all()
            ]);

            if (!$result['success']) {
                // Thanh toán thất bại, quay lại trang checkout
                return redirect()->route('borrow-cart.checkout')
                    ->with('error', 'Thanh toán không thành công: ' . $result['message'])
                    ->withInput();
            }

            // Lấy thông tin giao dịch
            $txnRef = $result['data']['order_id'];
            $vnpayTranId = $result['data']['transaction_id'];
            $amount = $result['data']['amount'];

            // Tìm bản ghi thanh toán
            $payment = BorrowPayment::where('transaction_code', $txnRef)
                ->where('payment_status', 'pending')
                ->first();

            if (!$payment) {
                Log::error('Payment not found', ['txnRef' => $txnRef]);
                return redirect()->route('borrow-cart.checkout')
                    ->with('error', 'Không tìm thấy thông tin thanh toán. Vui lòng thử lại.');
            }

            // Bắt đầu transaction
            DB::beginTransaction();

            try {
                // Nếu payment chưa có borrow_id, tạo đơn từ session data
                if (!$payment->borrow_id) {
                    $checkoutData = session('pending_checkout_data');
                    
                    if (!$checkoutData) {
                        DB::rollBack();
                        Log::error('Checkout data not found in session', ['txnRef' => $txnRef]);
                        return redirect()->route('borrow-cart.checkout')
                            ->with('error', 'Không tìm thấy thông tin đơn hàng. Vui lòng thử lại.');
                    }

                    // Tạo đơn từ session data
                    $borrow = \App\Models\Borrow::create([
                        'reader_id' => $checkoutData['reader_id'],
                        'ten_nguoi_muon' => $checkoutData['reader_name'],
                        'so_dien_thoai' => $checkoutData['reader_phone'],
                        'tinh_thanh' => $checkoutData['tinh_thanh'],
                        'huyen' => '',
                        'xa' => $checkoutData['xa'],
                        'so_nha' => $checkoutData['so_nha'],
                        'ngay_muon' => $checkoutData['ngay_muon'],
                        'trang_thai' => 'Cho duyet',
                        'tien_coc' => $checkoutData['total_tien_coc'],
                        'tien_thue' => $checkoutData['total_tien_thue'],
                        'tien_ship' => $checkoutData['total_tien_ship'] ?? 0,
                        'tong_tien' => $checkoutData['tong_tien'],
                        'voucher_id' => $checkoutData['voucher_id'],
                        'ghi_chu' => trim($checkoutData['notes'] ?: 'Đặt mượn từ giỏ sách'),
                    ]);

                    // Tạo các borrow items
                    foreach ($checkoutData['items'] as $itemData) {
                        \App\Models\BorrowItem::create([
                            'borrow_id' => $borrow->id,
                            'book_id' => $itemData['book_id'],
                            'inventorie_id' => $itemData['inventorie_id'],
                            'ngay_muon' => $checkoutData['ngay_muon'],
                            'ngay_hen_tra' => now()->addDays($itemData['borrow_days'])->toDateString(),
                            'trang_thai' => 'Cho duyet',
                            'tien_coc' => $itemData['tien_coc'],
                            'tien_thue' => $itemData['tien_thue'],
                            'tien_ship' => $itemData['tien_ship'] ?? 0,
                            'ghi_chu' => $itemData['note'],
                        ]);
                    }
                    
                    // Đảm bảo tien_ship được đồng bộ từ items nếu borrow->tien_ship = 0
                    if ($borrow->tien_ship == 0) {
                        $tienShipFromItems = $borrow->items()->sum('tien_ship');
                        if ($tienShipFromItems > 0) {
                            $borrow->tien_ship = $tienShipFromItems;
                            $borrow->tong_tien = $borrow->tien_coc + $borrow->tien_thue + $borrow->tien_ship;
                            $borrow->save();
                        }
                    }

                    // Giảm số lượng voucher nếu có
                    if ($checkoutData['voucher_id']) {
                        $voucher = \App\Models\Voucher::find($checkoutData['voucher_id']);
                        if ($voucher) {
                            $voucher->so_luong = max(0, $voucher->so_luong - 1);
                            $voucher->save();
                        }
                    }

                    // Xóa items khỏi giỏ hàng nếu từ giỏ hàng
                    if ($checkoutData['checkout_source'] === 'cart') {
                        $cart = \App\Models\BorrowCart::where('user_id', auth()->id())->first();
                        if ($cart) {
                            $cart->items()->where('is_selected', true)->delete();
                            $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);
                        }
                    }

                    // Xóa session data
                    session()->forget('pending_checkout_data');
                } else {
                    $borrow = $payment->borrow;
                }

                // Cập nhật trạng thái thanh toán
                $payment->update([
                    'borrow_id' => $borrow->id,
                    'payment_status' => 'success',
                    'transaction_code' => $vnpayTranId,
                    'note' => $payment->note . ' | VnPay TxnID: ' . $vnpayTranId . ' | Ngân hàng: ' . ($result['data']['bank_code'] ?? 'N/A')
                ]);

                // Cập nhật trạng thái inventory khi thanh toán thành công
                if ($payment->payment_type === 'deposit' && $borrow) {
                    $borrowItems = $borrow->items;
                    foreach ($borrowItems as $item) {
                        if ($item->inventorie_id) {
                            \App\Models\Inventory::where('id', $item->inventorie_id)
                                ->where('status', 'Co san')
                                ->update([
                                    'status' => 'Dang muon',
                                    'updated_at' => now()
                                ]);
                        }
                    }
                }

                DB::commit();

                // Xóa session
                session()->forget('vnpay_payment_id');
                session()->forget('vnpay_transaction_code');

                return redirect()->route('payment.success', ['payment_id' => $payment->id])
                    ->with('success', 'Thanh toán thành công!');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('VnPay Callback Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            // Xóa session data nếu có lỗi
            session()->forget('pending_checkout_data');
            session()->forget('vnpay_payment_id');
            session()->forget('vnpay_transaction_code');

            return redirect()->route('borrow-cart.checkout')
                ->with('error', 'Có lỗi xảy ra khi xử lý thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * Trang thanh toán thành công
     */
    public function success($payment_id)
    {
        $payment = BorrowPayment::with(['borrow.reader', 'borrowItem'])->findOrFail($payment_id);

        return view('payments.success', compact('payment'));
    }

    /**
     * Trang thanh toán thất bại
     */
    public function failed()
    {
        return view('payments.failed');
    }

    /**
     * API: Tạo thanh toán cho giỏ mượn sách
     * Dùng cho trường hợp thanh toán từ giỏ hàng
     */
    public function createPaymentFromCart(Request $request)
    {
        try {
            $request->validate([
                'borrow_cart_id' => 'required|exists:borrow_carts,id',
                'amount' => 'required|numeric|min:1000',
                'bank_code' => 'nullable|string'
            ]);

            $cartId = $request->borrow_cart_id;
            $amount = $request->amount;

            // Lấy thông tin giỏ mượn
            $cart = \App\Models\BorrowCart::with('reader')->findOrFail($cartId);

            $orderInfo = sprintf(
                "Thanh toán giỏ mượn #%d - %s",
                $cartId,
                $cart->reader->name ?? 'Khách hàng'
            );

            // Tạo mã giao dịch
            $txnRef = 'CART' . $cartId . '_' . time();

            $paymentData = [
                'amount' => $amount,
                'order_info' => $orderInfo,
                'order_id' => $txnRef,
                'order_type' => 'billpayment',
                'bank_code' => $request->bank_code
            ];

            // Lưu thông tin vào session
            session([
                'vnpay_cart_id' => $cartId,
                'vnpay_txn_ref' => $txnRef
            ]);

            // Tạo URL thanh toán
            $paymentUrl = $this->vnpayService->createPaymentUrl($paymentData, $request);

            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl
            ]);

        } catch (\Exception $e) {
            Log::error('VnPay Create Cart Payment Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Tạo thanh toán cho giỏ đặt trước (ReservationCart)
     */
    public function createPaymentFromReservationCart(Request $request)
    {
        try {
            $request->validate([
                'reservation_cart_id' => 'required|exists:reservation_carts,id',
                'amount' => 'required|numeric|min:1000',
                'transaction_code' => 'required|string'
            ]);

            $cartId = $request->reservation_cart_id;
            $amount = $request->amount;
            $txnRef = $request->transaction_code;

            $cart = \App\Models\ReservationCart::with('reader')->findOrFail($cartId);

            $orderInfo = sprintf(
                "Thanh toán giỏ đặt trước #%d - %s",
                $cartId,
                $cart->reader->name ?? 'Khách hàng'
            );

            $paymentData = [
                'amount' => $amount,
                'order_info' => $orderInfo,
                'order_id' => $txnRef,
                'order_type' => 'billpayment',
                'bank_code' => $request->bank_code ?? ''
            ];

            // Lưu một số thông tin tạm (không bắt buộc)
            session([
                'vnpay_reservation_cart_id' => $cartId,
                'vnpay_txn_ref' => $txnRef
            ]);

            $paymentUrl = $this->vnpayService->createPaymentUrl($paymentData, $request);

            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl
            ]);

        } catch (\Exception $e) {
            Log::error('VnPay Create Reservation Cart Payment Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}

