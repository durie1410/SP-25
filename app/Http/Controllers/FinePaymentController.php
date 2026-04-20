<?php

namespace App\Http\Controllers;

use App\Models\BookDeleteRequest;
use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Models\BorrowPayment;
use App\Models\Fine;
use App\Models\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FinePaymentController extends Controller
{
    /**
     * Thanh toán phạt bằng tiền mặt theo phiếu mượn
     */
    public function payCash(Borrow $borrow)
    {
        $pendingFines = $borrow->fines()->where('status', 'pending')->get();
        if ($pendingFines->isEmpty()) {
            return back()->with('error', 'Không có khoản phạt nào cần thanh toán.');
        }

        $totalAmount = $pendingFines->sum('amount');

        try {
            DB::beginTransaction();

            BorrowPayment::create([
                'borrow_id' => $borrow->id,
                'amount' => $totalAmount,
                'payment_type' => 'damage_fee',
                'payment_method' => 'offline',
                'payment_status' => 'success',
                'transaction_code' => 'CASH-' . time(),
                'note' => 'Thanh toán phạt tại quầy (Tiền mặt)',
            ]);

            Fine::whereIn('id', $pendingFines->pluck('id'))->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);

            $finalizeResult = $this->finalizeReturnedItemsAfterFinePayment(
                $pendingFines->pluck('borrow_item_id')->filter()->unique()->values()->all()
            );

            DB::commit();
            return back()->with('success', 'Đã xác nhận thanh toán phạt bằng tiền mặt thành công. ' .
                $this->buildFinalizeSummaryMessage($finalizeResult));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error payCash fine: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Thanh toán tất cả khoản phạt của độc giả bằng tiền mặt
     */
    public function payCashByReader(Request $request, Reader $reader)
    {
        $request->validate([
            'payment_method' => 'required|in:online,offline',
        ]);

        $pendingFines = Fine::where('reader_id', $reader->id)->where('status', 'pending')->get();

        // Tái tạo sessionProofsByItemId từ session (để kiểm tra ảnh)
        $sessionProofsByItemId = collect();
        $pendingReturn = session('pending_return');
        $returnItemIds = [];
        $returnItemsData = [];
        if ($pendingReturn && (int) ($pendingReturn['reader_id'] ?? 0) === $reader->id) {
            $returnItemsData = collect($pendingReturn['items'] ?? [])->keyBy('id')->toArray();
            $returnItemIds = collect($pendingReturn['items'] ?? [])->pluck('id')->filter()->values()->all();
            $sessionProofsByItemId = collect($returnItemsData)->mapWithKeys(function ($item) {
                $proofs = $item['return_proof_images'] ?? [];
                if (!is_array($proofs)) {
                    if (is_string($proofs)) {
                        $decoded = json_decode($proofs, true);
                        $proofs = is_array($decoded) ? $decoded : [];
                    } else {
                        $proofs = [];
                    }
                }
                return [$item['id'] ?? null => array_values(array_filter($proofs))];
            })->filter(function ($v, $k) {
                return !empty($k);
            });
        }

        // Kiểm tra bắt buộc có ảnh minh chứng cho từng sách trước khi thanh toán
        $missingProofItems = [];

        if (!empty($returnItemIds)) {
            $items = \App\Models\BorrowItem::with(['book'])
                ->whereIn('id', $returnItemIds)
                ->get()
                ->keyBy('id');

            foreach ($returnItemsData as $itemId => $itemData) {
                $item = $items->get($itemId);
                if (!$item) continue;

                // Ảnh đã lưu trong DB
                $dbProofs = $item->return_proof_images ?? [];
                if (is_string($dbProofs)) {
                    $dbProofs = json_decode($dbProofs, true) ?? [];
                }
                $dbProofs = is_array($dbProofs) ? $dbProofs : [];

                // Ảnh từ session
                $sessionProofs = $itemData['return_proof_images'] ?? [];
                $sessionProofs = is_array($sessionProofs) ? $sessionProofs : [];

                $hasProof = !empty($dbProofs) || !empty($sessionProofs);
                if (!$hasProof) {
                    $missingProofItems[] = $item->book?->ten_sach ?? "Item #{$itemId}";
                }
            }
        }

        // Kiểm tra Fine records đã có trong DB
        foreach ($pendingFines as $fine) {
            $item = $fine->borrowItem;
            if (!$item) continue;
            $dbProofs = $item->return_proof_images ?? [];
            if (is_string($dbProofs)) {
                $dbProofs = json_decode($dbProofs, true) ?? [];
            }
            $dbProofs = is_array($dbProofs) ? $dbProofs : [];

            $sessionProofs = $sessionProofsByItemId ?? collect();
            $sessionProofs = $sessionProofs->get($item->id, []);
            $sessionProofs = is_array($sessionProofs) ? $sessionProofs : [];

            $hasProof = !empty($dbProofs) || !empty($sessionProofs);
            if (!$hasProof) {
                $missingProofItems[] = $item->book?->ten_sach ?? "Fine #{$fine->id}";
            }
        }

        if (!empty($missingProofItems)) {
            return back()->with('error', 'Vui lòng tải ảnh minh chứng cho: ' . implode(', ', $missingProofItems));
        }

        // Không có phạt + không có sách chờ trả → báo lỗi
        if ($pendingFines->isEmpty() && empty($returnItemIds)) {
            return back()->with('error', 'Không có khoản phạt nào cần thanh toán và không có sách nào được chọn để trả.');
        }

        $paymentMethod = $request->payment_method;

        // Giống luồng thanh toán mượn: chọn online thì tạo mã MoMo và hiển thị QR trên trang
        if ($paymentMethod === 'online') {
            try {
                $amount = (int) $pendingFines->sum('amount');

                // Cộng thêm phạt ước tính từ sách đang chờ trả
                if (!empty($returnItemIds)) {
                    $items = \App\Models\BorrowItem::with(['book', 'inventory'])
                        ->whereIn('id', $returnItemIds)
                        ->get()
                        ->keyBy('id');
                    foreach (session('pending_return.items') ?? [] as $itemData) {
                        $item = $items->get($itemData['id']);
                        if (!$item) continue;
                        $condition = $itemData['condition'] ?? 'binh_thuong';
                        $lateFine = 0;
                        if ($item->ngay_hen_tra && \Carbon\Carbon::parse($item->ngay_hen_tra)->lt(\Carbon\Carbon::today())) {
                            $lateFine = \App\Services\PricingService::calculateLateReturnFine($item->ngay_hen_tra, \Carbon\Carbon::today(), 1);
                        }
                        $damageFine = 0;
                        if ($condition !== 'binh_thuong') {
                            $bookPrice = (float) ($item->book->gia ?? 0);
                            $bookType = $item->book->loai_sach ?? 'binh_thuong';
                            $startCondition = $item->inventory->condition ?? 'Trung binh';
                            if ($condition === 'mat_sach') {
                                $damageFine = (int) \App\Services\PricingService::calculateLostBookFine($bookPrice, $bookType, $startCondition);
                            } elseif ($condition === 'hong_nhe') {
                                $damageFine = (int) round(\App\Services\PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition) * 0.5);
                            } else {
                                $damageFine = (int) \App\Services\PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition);
                            }
                        }
                        $amount += $lateFine + $damageFine;
                    }
                }

                $endpoint    = config('services.momo.endpoint');
                $partnerCode = config('services.momo.partner_code');
                $accessKey   = config('services.momo.access_key');
                $secretKey   = config('services.momo.secret_key');

                $redirectUrl = route('admin.borrows.fine-momo.return');
                $ipnUrl      = route('admin.borrows.fine-momo.ipn');

                $orderId   = 'FINE_READER_' . $reader->id . '_' . time();
                $requestId = (string) time();
                $orderInfo = 'Thanh_toan_phat_doc_gia_' . str_replace(' ', '_', $reader->ho_ten);
                $extraData = base64_encode(json_encode([
                    'reader_id' => $reader->id,
                    'type' => 'fine_reader',
                    'return_item_ids' => $returnItemIds,
                ]));

                $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=captureWallet";
                $signature = hash_hmac('sha256', $rawHash, $secretKey);

                $response = Http::post($endpoint, [
                    'partnerCode' => $partnerCode,
                    'accessKey'   => $accessKey,
                    'requestId'   => $requestId,
                    'amount'      => (string) $amount,
                    'orderId'     => $orderId,
                    'orderInfo'   => $orderInfo,
                    'redirectUrl' => $redirectUrl,
                    'ipnUrl'      => $ipnUrl,
                    'extraData'   => $extraData,
                    'requestType' => 'captureWallet',
                    'signature'   => $signature,
                    'lang'        => 'vi',
                ]);

                $result = $response->json();
                if (!isset($result['payUrl'])) {
                    return back()->with('error', 'Không thể tạo thanh toán MoMo: ' . ($result['message'] ?? 'Unknown error'));
                }

                DB::beginTransaction();
                
                // Lưu tình trạng sách từ session vào DB TRƯỚC khi MoMo IPN xử lý
                // (IPN không có session context, nên phải lấy từ DB)
                if (!empty($returnItemIds)) {
                    foreach ($returnItemIds as $itemId) {
                        $itemData = $returnItemsData[$itemId] ?? [];
                        $condition = $itemData['condition'] ?? 'binh_thuong';
                        BorrowItem::where('id', $itemId)->update([
                            'tinh_trang_sach_cuoi' => $condition,
                            'updated_at' => now(),
                        ]);
                    }
                }
                
                $finesByBorrow = $pendingFines->groupBy('borrow_id');
                foreach ($finesByBorrow as $borrowId => $borrowFines) {
                    BorrowPayment::create([
                        'borrow_id' => $borrowId,
                        'amount' => $borrowFines->sum('amount'),
                        'payment_type' => 'damage_fee',
                        'payment_method' => 'online',
                        'payment_status' => 'pending',
                        'transaction_code' => $orderId,
                        'note' => 'Thanh toán phạt MoMo (Độc giả) - Đang chờ',
                    ]);
                }
                DB::commit();

                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($result['payUrl']);

                return redirect()
                    ->route('admin.fine-payments.index', ['reader_id' => $reader->id])
                    ->with('momo_pay_url', $result['payUrl'])
                    ->with('momo_order_id', $orderId)
                    ->with('momo_qr_url', $qrUrl)
                    ->with('success', 'Đã tạo mã MoMo. Vui lòng quét mã để thanh toán.');
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Error create momo in payCashByReader: ' . $e->getMessage());
                return back()->with('error', 'Không thể tạo thanh toán MoMo: ' . $e->getMessage());
            }
        }

        try {
            DB::beginTransaction();

            // Thanh toán phạt (nếu có)
            if (!$pendingFines->isEmpty()) {
                $finesByBorrow = $pendingFines->groupBy('borrow_id');
                foreach ($finesByBorrow as $borrowId => $borrowFines) {
                    BorrowPayment::create([
                        'borrow_id' => $borrowId,
                        'amount' => $borrowFines->sum('amount'),
                        'payment_type' => 'damage_fee',
                        'payment_method' => 'offline',
                        'payment_status' => 'success',
                        'transaction_code' => 'CASH-' . $reader->id . '-' . time(),
                        'note' => 'Thanh toán phạt tại quầy (Tiền mặt) cho độc giả: ' . $reader->ho_ten,
                    ]);
                }

                Fine::whereIn('id', $pendingFines->pluck('id'))->update([
                    'status' => 'paid',
                    'paid_date' => now(),
                ]);
            }

            // Tạo Fine records + BorrowPayment cho sách từ session (chưa có Fine)
            if (!empty($returnItemIds)) {
                $items = BorrowItem::with(['book', 'borrow'])
                    ->whereIn('id', $returnItemIds)
                    ->whereIn('trang_thai', ['Dang muon', 'Qua han'])
                    ->get();

                foreach ($items as $item) {
                    if (!$item->borrow) continue;
                    $itemData = $returnItemsData[$item->id] ?? [];
                    $condition = $itemData['condition'] ?? 'binh_thuong';
                    $today = now();

                    // Tính phạt quá hạn
                    $lateFine = 0;
                    if ($item->ngay_hen_tra && \Carbon\Carbon::parse($item->ngay_hen_tra)->lt(\Carbon\Carbon::today())) {
                        $lateFine = \App\Services\PricingService::calculateLateReturnFine($item->ngay_hen_tra, \Carbon\Carbon::today(), 1);
                    }

                    // Tính phạt hỏng / mất
                    $damageFine = 0;
                    if ($condition !== 'binh_thuong') {
                        $bookPrice = (float) ($item->book->gia ?? 0);
                        $bookType = $item->book->loai_sach ?? 'binh_thuong';
                        $startCondition = $item->inventory->condition ?? 'Trung binh';
                        if ($condition === 'mat_sach') {
                            $damageFine = (int) \App\Services\PricingService::calculateLostBookFine($bookPrice, $bookType, $startCondition);
                        } elseif ($condition === 'hong_nhe') {
                            $damageFine = (int) round(\App\Services\PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition) * 0.5);
                        } else {
                            $damageFine = (int) \App\Services\PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition);
                        }
                    }

                    $totalFine = $lateFine + $damageFine;

                    // Chỉ tạo Fine nếu có phạt > 0
                    if ($totalFine > 0) {
                        $fineTypes = [];
                        if ($lateFine > 0) $fineTypes[] = 'late_return';
                        if ($damageFine > 0) $fineTypes[] = ($condition === 'mat_sach' ? 'lost_book' : 'damaged_book');

                        foreach ($fineTypes as $fineType) {
                            $fineAmount = $fineType === 'late_return' ? $lateFine : $damageFine;
                            Fine::create([
                                'borrow_item_id' => $item->id,
                                'borrow_id' => $item->borrow_id,
                                'reader_id' => $reader->id,
                                'type' => $fineType,
                                'amount' => $fineAmount,
                                'status' => 'paid',
                                'paid_date' => $today,
                                'due_date' => $today->toDateString(),
                                'description' => ($fineType === 'late_return'
                                    ? 'Phạt trễ hạn: ' . ($item->book?->ten_sach ?? '')
                                    : 'Phạt ' . ($condition === 'mat_sach' ? 'mất' : 'hỏng') . ' sách: ' . ($item->book?->ten_sach ?? '')),
                                'created_by' => Auth::id() ?? 1,
                            ]);
                        }

                        BorrowPayment::create([
                            'borrow_id' => $item->borrow_id,
                            'amount' => $totalFine,
                            'payment_type' => 'damage_fee',
                            'payment_method' => 'offline',
                            'payment_status' => 'success',
                            'transaction_code' => 'CASH-RETURN-' . $reader->id . '-' . $item->id . '-' . time(),
                            'note' => 'Thanh toán phạt trả sách tại quầy (Tiền mặt) cho: ' . $reader->ho_ten,
                        ]);
                    }
                }
            }

            // Xử lý trả sách từ session (nếu có)
            $allItemIds = $returnItemIds;
            if (!$pendingFines->isEmpty()) {
                $finesItemIds = $pendingFines->pluck('borrow_item_id')->filter()->unique()->values()->all();
                $allItemIds = array_unique(array_merge($allItemIds, $finesItemIds));
            }

            $finalizeResult = $this->finalizeReturnedItemsAfterFinePayment($allItemIds);

            // Xóa session pending return
            session()->forget('pending_return');

            DB::commit();

            $msg = $pendingFines->isEmpty() && empty($returnItemIds)
                ? 'Đã ghi nhận trả sách thành công.'
                : 'Đã thanh toán tất cả khoản phạt và trả sách thành công. ' .
                  $this->buildFinalizeSummaryMessage($finalizeResult);

            return redirect()->route('admin.returns.index', ['reader_id' => $reader->id])
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error payCashByReader: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Tạo link thanh toán MoMo cho tiền phạt theo phiếu mượn
     */
    public function createMomoPayment(Borrow $borrow)
    {
        $pendingFines = $borrow->fines()->where('status', 'pending')->get();
        if ($pendingFines->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không có khoản phạt nào cần thanh toán.']);
        }

        $amount = (int) $pendingFines->sum('amount');

        $endpoint    = config('services.momo.endpoint');
        $partnerCode = config('services.momo.partner_code');
        $accessKey   = config('services.momo.access_key');
        $secretKey   = config('services.momo.secret_key');

        $redirectUrl = route('admin.borrows.fine-momo.return');
        $ipnUrl      = route('admin.borrows.fine-momo.ipn');

        $orderId   = 'FINE_' . $borrow->id . '_' . time();
        $requestId = (string) time();
        $orderInfo = 'Thanh_toan_phat_phieu_muon_' . $borrow->id;
        $extraData = base64_encode(json_encode(['borrow_id' => $borrow->id, 'type' => 'fine_borrow']));

        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=captureWallet";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        try {
            $response = Http::post($endpoint, [
                'partnerCode' => $partnerCode,
                'accessKey'   => $accessKey,
                'requestId'   => $requestId,
                'amount'      => (string) $amount,
                'orderId'     => $orderId,
                'orderInfo'   => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl'      => $ipnUrl,
                'extraData'   => $extraData,
                'requestType' => 'captureWallet',
                'signature'   => $signature,
                'lang'        => 'vi',
            ]);

            $result = $response->json();

            if (isset($result['payUrl'])) {
                BorrowPayment::create([
                    'borrow_id' => $borrow->id,
                    'amount' => $amount,
                    'payment_type' => 'damage_fee',
                    'payment_method' => 'online',
                    'payment_status' => 'pending',
                    'transaction_code' => $orderId,
                    'note' => 'Thanh toán phạt qua MoMo (Đang chờ)',
                ]);

                return response()->json(['success' => true, 'payUrl' => $result['payUrl']]);
            }

            return response()->json(['success' => false, 'message' => 'MoMo Error: ' . ($result['message'] ?? 'Unknown error')]);
        } catch (\Exception $e) {
            Log::error('Momo Fine Payment Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi kết nối MoMo.']);
        }
    }

    /**
     * Tạo link thanh toán MoMo cho tất cả tiền phạt của độc giả
     */
    public function createMomoPaymentByReader(Reader $reader)
    {
        $pendingFines = Fine::where('reader_id', $reader->id)->where('status', 'pending')->get();

        // Lấy sách đang chờ trả từ session (tương tự payCashByReader)
        $pendingReturn = session('pending_return');
        $returnItemIds = [];
        $returnItemsData = [];
        if ($pendingReturn && (int) ($pendingReturn['reader_id'] ?? 0) === $reader->id) {
            $returnItemsData = collect($pendingReturn['items'] ?? [])->keyBy('id')->toArray();
            $returnItemIds = collect($pendingReturn['items'] ?? [])->pluck('id')->filter()->values()->all();
        }

        $dbFineAmount = (int) $pendingFines->sum('amount');
        $sessionFineAmount = 0;

        if (!empty($returnItemIds)) {
            $items = BorrowItem::with(['book', 'inventory'])
                ->whereIn('id', $returnItemIds)
                ->get()
                ->keyBy('id');
            foreach ($returnItemsData as $itemId => $itemData) {
                $item = $items->get($itemId);
                if (!$item) continue;
                $condition = $itemData['condition'] ?? 'binh_thuong';
                $lateFine = 0;
                if ($item->ngay_hen_tra && \Carbon\Carbon::parse($item->ngay_hen_tra)->lt(\Carbon\Carbon::today())) {
                    $lateFine = \App\Services\PricingService::calculateLateReturnFine($item->ngay_hen_tra, \Carbon\Carbon::today(), 1);
                }
                $damageFine = 0;
                if ($condition !== 'binh_thuong') {
                    $bookPrice = (float) ($item->book->gia ?? 0);
                    $bookType = $item->book->loai_sach ?? 'binh_thuong';
                    $startCondition = $item->inventory->condition ?? 'Trung binh';
                    if ($condition === 'mat_sach') {
                        $damageFine = (int) \App\Services\PricingService::calculateLostBookFine($bookPrice, $bookType, $startCondition);
                    } elseif ($condition === 'hong_nhe') {
                        $damageFine = (int) round(\App\Services\PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition) * 0.5);
                    } else {
                        $damageFine = (int) \App\Services\PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition);
                    }
                }
                $sessionFineAmount += $lateFine + $damageFine;
            }
        }

        // Không có gì để thanh toán → báo lỗi
        if ($dbFineAmount === 0 && $sessionFineAmount === 0) {
            return response()->json(['success' => false, 'message' => 'Không có khoản phạt nào cần thanh toán.']);
        }

        $amount = $dbFineAmount + $sessionFineAmount;

        $endpoint    = config('services.momo.endpoint');
        $partnerCode = config('services.momo.partner_code');
        $accessKey   = config('services.momo.access_key');
        $secretKey   = config('services.momo.secret_key');

        $redirectUrl = route('admin.borrows.fine-momo.return');
        $ipnUrl      = route('admin.borrows.fine-momo.ipn');

        $orderId   = 'FINE_READER_' . $reader->id . '_' . time();
        $requestId = (string) time();
        $orderInfo = 'Thanh_toan_phat_doc_gia_' . str_replace(' ', '_', $reader->ho_ten);
        $extraData = base64_encode(json_encode([
            'reader_id' => $reader->id,
            'type' => 'fine_reader',
            'return_item_ids' => $returnItemIds,
        ]));

        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=captureWallet";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        try {
            $response = Http::post($endpoint, [
                'partnerCode' => $partnerCode,
                'accessKey'   => $accessKey,
                'requestId'   => $requestId,
                'amount'      => (string) $amount,
                'orderId'     => $orderId,
                'orderInfo'   => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl'      => $ipnUrl,
                'extraData'   => $extraData,
                'requestType' => 'captureWallet',
                'signature'   => $signature,
                'lang'        => 'vi',
            ]);

            $result = $response->json();

            if (isset($result['payUrl'])) {
                // Lưu tình trạng sách từ session vào DB TRƯỚC khi MoMo IPN xử lý
                // (IPN không có session context, nên phải lấy từ DB)
                if (!empty($returnItemIds)) {
                    foreach ($returnItemIds as $itemId) {
                        $itemData = $returnItemsData[$itemId] ?? [];
                        $condition = $itemData['condition'] ?? 'binh_thuong';
                        BorrowItem::where('id', $itemId)->update([
                            'tinh_trang_sach_cuoi' => $condition,
                            'updated_at' => now(),
                        ]);
                    }
                }
                
                // Ghi nhận payment cho Fine từ DB (nếu có)
                $finesByBorrow = $pendingFines->groupBy('borrow_id');
                foreach ($finesByBorrow as $borrowId => $borrowFines) {
                    BorrowPayment::create([
                        'borrow_id' => $borrowId,
                        'amount' => $borrowFines->sum('amount'),
                        'payment_type' => 'damage_fee',
                        'payment_method' => 'online',
                        'payment_status' => 'pending',
                        'transaction_code' => $orderId,
                        'note' => 'Thanh toán phạt MoMo (Độc giả)',
                    ]);
                }

                // Ghi nhận payment cho sách chờ trả từ session (Fine chưa tạo trong DB)
                if (!empty($returnItemIds) && $sessionFineAmount > 0) {
                    $items = BorrowItem::with('borrow')
                        ->whereIn('id', $returnItemIds)
                        ->get()
                        ->groupBy('borrow_id');
                    foreach ($items as $borrowId => $borrowItems) {
                        BorrowPayment::create([
                            'borrow_id' => $borrowId,
                            'amount' => $sessionFineAmount,
                            'payment_type' => 'damage_fee',
                            'payment_method' => 'online',
                            'payment_status' => 'pending',
                            'transaction_code' => $orderId,
                            'note' => 'Thanh toán phạt MoMo (Sách chờ trả)',
                        ]);
                        break; // chỉ tạo 1 payment cho tất cả session items
                    }
                }

                return response()->json([
                    'success' => true,
                    'payUrl' => $result['payUrl'],
                    'orderId' => $orderId,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'MoMo Error: ' . ($result['message'] ?? 'Unknown error')]);
        } catch (\Exception $e) {
            Log::error('Momo Reader Fine Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi kết nối MoMo.']);
        }
    }

    /**
     * MoMo Return (redirect về site)
     */
    public function momoReturn(Request $request)
    {
        $resultCode = $request->resultCode;
        $orderId = $request->orderId;
        $extraData = json_decode(base64_decode($request->extraData ?? ''), true) ?: [];

        if ($resultCode == 0) {
            try {
                $this->processFineMomoPayment($orderId, $extraData, 'return');
            } catch (\Throwable $e) {
                Log::warning('MoMo fine return fallback failed', [
                    'orderId' => $orderId,
                    'extraData' => $extraData,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (($extraData['type'] ?? null) === 'fine_reader') {
            $readerId = $extraData['reader_id'] ?? null;

            if ($resultCode == 0) {
return redirect()->route('admin.returns.index', ['reader_id' => $readerId])
                    ->with('success', 'Thanh toán MoMo thành công! ');
            }
            return redirect()->route('admin.fine-payments.index', ['reader_id' => $readerId])
                ->with('error', 'Thanh toán MoMo thất bại hoặc đã bị hủy.');
        }

        $borrowId = $extraData['borrow_id'] ?? (explode('_', $orderId)[1] ?? null);

        if ($resultCode == 0) {
            return redirect()->route('admin.borrows.show', $borrowId)
                ->with('success', 'Thanh toán MoMo thành công!');
        }
        return redirect()->route('admin.borrows.show', $borrowId)
            ->with('error', 'Thanh toán MoMo thất bại hoặc đã bị hủy.');
    }

    /**
     * MoMo IPN (server-to-server)
     */
    public function momoIpn(Request $request)
    {
        Log::info('MOMO FINE IPN received', $request->all());

        $orderId = $request->orderId;
        $resultCode = $request->resultCode;
        $extraData = json_decode(base64_decode($request->extraData ?? ''), true) ?: [];

        if ((int) $resultCode !== 0) {
            return response()->json(['message' => 'Received but failed'], 200);
        }

        try {
            $this->processFineMomoPayment($orderId, $extraData, 'ipn');
            return response()->json(['message' => 'Success']);
        } catch (\Exception $e) {
            Log::error('MOMO IPN Error: ' . $e->getMessage(), ['orderId' => $orderId, 'extraData' => $extraData]);
            return response()->json(['message' => 'Error'], 200);
        }
    }

    /**
     * Sau khi thanh toán phạt trả sách:
     * - Sách bình thường => đưa về kho.
     * - Sách hỏng/mất => giữ trạng thái, chờ admin xử lý thủ công.
     */
    private function finalizeReturnedItemsAfterFinePayment(array $borrowItemIds): array
    {
        if (empty($borrowItemIds)) {
            return [
                'returned_to_stock' => 0,
                'moved_to_delete_review' => 0,
            ];
        }

        $result = [
            'returned_to_stock' => 0,
            'moved_to_delete_review' => 0,
        ];

        $items = BorrowItem::with(['inventory', 'book'])
            ->whereIn('id', $borrowItemIds)
            ->whereIn('trang_thai', ['Dang muon', 'Qua han'])
            ->get();

        foreach ($items as $item) {
            // Cập nhật trạng thái sách thành "Đã trả" / "Mất" / "Hỏng"
            if (in_array($item->trang_thai, ['Dang muon', 'Qua han'])) {
                $condition = trim((string) ($item->tinh_trang_sach_cuoi ?? ''));
                $itemStatus = match ($condition) {
                    'mat_sach' => 'Mat sach',
                    'hong_nhe', 'hong_nang' => 'Hong',
                    default => 'Da tra',
                };

                $item->update([
                    'trang_thai' => $itemStatus,
                    'ngay_tra_thuc_te' => now()->toDateString(),
                ]);

                // Cập nhật inventory.status tương ứng với trạng thái sách
                if ($item->inventory) {
                    $invStatus = match ($condition) {
                        'mat_sach' => 'Mat',
                        'hong_nhe', 'hong_nang' => 'Hong',
                        default => null,
                    };
                    if ($invStatus) {
                        $item->inventory->update(['status' => $invStatus]);
                    }
                }
            }

            if (!$item->inventory || !$item->book) {
                continue;
            }

            $condition = trim((string) ($item->tinh_trang_sach_cuoi ?? ''));

            if ($condition === 'binh_thuong') {
                $item->inventory->update([
                    'status' => 'Co san',
                    'storage_type' => 'Kho',
                ]);
                $result['returned_to_stock']++;
                continue;
            }

            if (!in_array($condition, ['hong_nhe', 'hong_nang', 'mat_sach'], true)) {
                continue;
            }

            // Tạo yêu cầu xóa cho sách hỏng/mất để admin duyệt
            $exists = BookDeleteRequest::where('inventory_id', $item->inventory->id)
                ->where('status', 'pending')
                ->exists();

            if (!$exists) {
                $lyDo = $condition === 'mat_sach'
                    ? 'Mất sách khi trả'
                    : 'Sách hỏng khi trả';
                $prefix = $condition === 'mat_sach' ? '[BAO MAT]' : '[BAO HONG]';

                BookDeleteRequest::create([
                    'book_id'       => $item->book_id,
                    'inventory_id'  => $item->inventory->id,
                    'borrow_item_id' => $item->id,
                    'requested_by'  => Auth::id() ?? 1,
                    'status'        => 'pending',
                    'reason'        => $prefix . ' ' . $lyDo . '. Item #' . $item->id,
                ]);
            }

            // Không cập nhật inventory thành 'Co san' — sách hỏng/mất không phải "có sẵn"
            // Giữ nguyên status hiện tại (sẽ được xử lý khi duyệt xóa)
            $result['moved_to_delete_review']++;
        }

        // Cập nhật trạng thái Borrow nếu tất cả items đã trả
        $borrowIds = $items->pluck('borrow_id')->unique()->filter();
        foreach ($borrowIds as $borrowId) {
            $borrow = Borrow::with('items')->find($borrowId);
            if (!$borrow) continue;

            $borrow->recalculateTotals();
            $remaining = $borrow->items()->whereIn('trang_thai', ['Dang muon', 'Qua han'])->count();
            if ($remaining === 0) {
                $borrow->update(['trang_thai' => 'Da tra']);
            }
        }

        return $result;
    }

    private function processFineMomoPayment(string $orderId, array $extraData, string $source = 'ipn'): void
    {
        DB::beginTransaction();
        try {
            if (($extraData['type'] ?? null) === 'fine_reader') {
                $readerId = $extraData['reader_id'] ?? null;
                if (!$readerId) {
                    throw new \Exception('Missing reader_id');
                }

                $returnItemIds = $extraData['return_item_ids'] ?? [];

                BorrowPayment::where('transaction_code', $orderId)
                    ->update([
                        'payment_status' => 'success',
                        'note' => 'Thanh toán phạt qua MoMo thành công (Độc giả)',
                    ]);

                $allItemIds = $returnItemIds;

                if (!empty($returnItemIds)) {
                    $items = BorrowItem::with(['book', 'borrow', 'inventory'])
                        ->whereIn('id', $returnItemIds)
                        ->whereIn('trang_thai', ['Dang muon', 'Qua han'])
                        ->get()
                        ->keyBy('id');

                    foreach ($returnItemIds as $itemId) {
                        $item = $items->get($itemId);
                        if (!$item || !$item->borrow) {
                            continue;
                        }

                        $condition = trim((string) ($item->tinh_trang_sach_cuoi ?? 'binh_thuong'));
                        if ($condition === '') {
                            $condition = 'binh_thuong';
                        }
                        $today = now();

                        $lateFine = 0;
                        if ($item->ngay_hen_tra && \Carbon\Carbon::parse($item->ngay_hen_tra)->lt($today->copy()->startOfDay())) {
                            $lateFine = \App\Services\PricingService::calculateLateReturnFine($item->ngay_hen_tra, $today, 1);
                        }

                        $damageFine = 0;
                        if ($condition !== 'binh_thuong') {
                            $bookPrice = (float) ($item->book->gia ?? 0);
                            $bookType = $item->book->loai_sach ?? 'binh_thuong';
                            $startCondition = $item->inventory->condition ?? 'Trung binh';
                            if ($condition === 'mat_sach') {
                                $damageFine = (int) \App\Services\PricingService::calculateLostBookFine($bookPrice, $bookType, $startCondition);
                            } elseif ($condition === 'hong_nhe') {
                                $damageFine = (int) round(\App\Services\PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition) * 0.5);
                            } else {
                                $damageFine = (int) \App\Services\PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition);
                            }
                        }

                        if ($lateFine > 0 || $damageFine > 0) {
                            $type = $lateFine > 0 && $damageFine > 0 ? 'late_return'
                                : ($damageFine > 0 ? ($condition === 'mat_sach' ? 'lost_book' : 'damaged_book') : 'late_return');

                            Fine::updateOrCreate(
                                ['borrow_item_id' => $itemId, 'type' => $type, 'status' => 'pending'],
                                [
                                    'borrow_id' => $item->borrow_id,
                                    'reader_id' => $readerId,
                                    'amount' => $lateFine + $damageFine,
                                    'description' => ($lateFine > 0 && $damageFine > 0)
                                        ? 'Phạt trễ hạn + hỏng/mất: ' . ($item->book?->ten_sach ?? '')
                                        : ($lateFine > 0
                                            ? 'Phạt trễ hạn: ' . ($item->book?->ten_sach ?? '')
                                            : 'Phạt hỏng/mất: ' . ($item->book?->ten_sach ?? '')),
                                    'due_date' => $today->toDateString(),
                                    'created_by' => Auth::id() ?? 1,
                                    'status' => 'paid',
                                    'paid_date' => $today,
                                ]
                            );
                        } else {
                            Fine::updateOrCreate(
                                ['borrow_item_id' => $itemId, 'type' => 'late_return', 'status' => 'pending'],
                                [
                                    'borrow_id' => $item->borrow_id,
                                    'reader_id' => $readerId,
                                    'amount' => 0,
                                    'description' => 'Trả sách: ' . ($item->book?->ten_sach ?? ''),
                                    'due_date' => $today->toDateString(),
                                    'created_by' => Auth::id() ?? 1,
                                ]
                            );
                        }
                    }

                    session()->forget('pending_return');
                }

                Fine::where('reader_id', $readerId)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'paid',
                        'paid_date' => now(),
                    ]);

                $allItemIds = array_unique(array_merge(
                    $allItemIds,
                    Fine::where('reader_id', $readerId)
                        ->where('status', 'paid')
                        ->whereNotNull('borrow_item_id')
                        ->pluck('borrow_item_id')
                        ->unique()
                        ->values()
                        ->all()
                ));

                BorrowPayment::where('transaction_code', $orderId)
                    ->update([
                        'payment_status' => 'success',
                        'note' => 'Thanh toán phạt qua MoMo thành công (Độc giả)',
                    ]);

                if (!empty($allItemIds)) {
                    $finalizeResult = $this->finalizeReturnedItemsAfterFinePayment($allItemIds);
                    Log::info('Finalize returned items after fine_reader payment', $finalizeResult);
                }

                DB::commit();
                return;
            }

            $borrowId = $extraData['borrow_id'] ?? null;
            if ($borrowId) {
                $pendingBorrowItemIds = Fine::where('borrow_id', $borrowId)
                    ->where('status', 'pending')
                    ->whereNotNull('borrow_item_id')
                    ->pluck('borrow_item_id')
                    ->unique()
                    ->values()
                    ->all();

                $returnItemIdsFromExtra = $extraData['return_item_ids'] ?? [];

                BorrowPayment::where('borrow_id', $borrowId)
                    ->where('transaction_code', $orderId)
                    ->update([
                        'payment_status' => 'success',
                        'note' => 'Thanh toán phạt qua MoMo thành công',
                    ]);

                Fine::where('borrow_id', $borrowId)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'paid',
                        'paid_date' => now(),
                    ]);

                $allBorrowItemIds = array_unique(array_merge($pendingBorrowItemIds, $returnItemIdsFromExtra));
                if (!empty($allBorrowItemIds)) {
                    $finalizeResult = $this->finalizeReturnedItemsAfterFinePayment($allBorrowItemIds);
                    Log::info('Finalize returned items after fine_borrow payment', $finalizeResult);
                }

                DB::commit();
                return;
            }

            throw new \Exception('Payment type not recognized: ' . ($extraData['type'] ?? 'unknown'));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function buildFinalizeSummaryMessage(array $result): string
    {
        $returnedToStock = (int) ($result['returned_to_stock'] ?? 0);
        $movedToDeleteReview = (int) ($result['moved_to_delete_review'] ?? 0);

        if ($returnedToStock === 0 && $movedToDeleteReview === 0) {
            return 'Không có sách nào cần cập nhật thêm sau thanh toán.';
        }

        return "Đã đưa {$returnedToStock} sách về kho, {$movedToDeleteReview} sách sang duyệt xóa.";
    }
}