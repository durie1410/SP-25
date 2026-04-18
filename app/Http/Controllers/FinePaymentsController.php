<?php

namespace App\Http\Controllers;

use App\Models\BorrowItem;
use App\Models\BorrowPayment;
use App\Models\Fine;
use App\Models\Reader;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinePaymentsController extends Controller
{
    /**
     * Danh sách các khoản phạt pending theo độc giả
     */
    public function index(Request $request)
    {
        $reader = null;
        if ($request->filled('reader_id')) {
            $reader = Reader::findOrFail($request->reader_id);
        }

        $query = Fine::with(['borrow', 'borrowItem.book', 'reader'])
            ->where('status', 'pending');

        if ($reader) {
            $query->where('reader_id', $reader->id);
        }

        $fines = $query->orderByDesc('created_at')->paginate(15);

        // Lấy sách đang chờ trả từ session
        $pendingReturn = session('pending_return');
        $pendingReturnItems = [];
        $pendingReturnTotal = 0;

        // Danh sách phạt ước tính từ session (để hiển thị ngay trong bảng, trước khi thanh toán)
        $pendingReturnFines = [];

        $sessionProofsByItemId = collect();

        if ($pendingReturn && $reader && (int) ($pendingReturn['reader_id'] ?? 0) === $reader->id) {
            $returnItemsData = $pendingReturn['items'] ?? [];
            $returnItemsDataById = collect($returnItemsData)->keyBy('id');
            $sessionProofsByItemId = $returnItemsDataById->mapWithKeys(function ($item) {
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
            $itemIds = $returnItemsDataById->keys()->filter()->values()->all();
            if (!empty($itemIds)) {
                $items = BorrowItem::with(['book', 'borrow', 'inventory'])
                    ->whereIn('id', $itemIds)
                    ->get()
                    ->keyBy('id');

                // Map items với condition đã chọn
                $pendingReturnItems = $returnItemsDataById->map(function ($itemData) use ($items) {
                    $item = $items->get($itemData['id']);
                    if (!$item) return null;
                    $condition = $itemData['condition'] ?? 'binh_thuong';
                    $item->selected_condition = $condition;
                    // Gắn thêm ảnh từ session nếu có (phòng DB chưa kịp cập nhật)
                    $sessionProofs = $itemData['return_proof_images'] ?? [];
                    $currentProofs = $item->return_proof_images ?? [];
                    if (is_string($currentProofs)) {
                        $decoded = json_decode($currentProofs, true);
                        $currentProofs = is_array($decoded) ? $decoded : [];
                    }
                    $mergedProofs = array_values(array_unique(array_filter(array_merge(
                        is_array($currentProofs) ? $currentProofs : [],
                        is_array($sessionProofs) ? $sessionProofs : []
                    ))));
                    if (!empty($mergedProofs)) {
                        $item->return_proof_images = $mergedProofs;
                    }
                    return $item;
                })->filter()->values();

                // Tính tổng phạt từ sách đang chờ trả
                foreach ($pendingReturnItems as $item) {
                    $lateFine = 0;
                    if ($item->ngay_hen_tra && Carbon::parse($item->ngay_hen_tra)->lt(Carbon::today())) {
                        $lateFine = PricingService::calculateLateReturnFine($item->ngay_hen_tra, Carbon::today(), 1);
                    }
                    $damageFine = 0;
                    if (($item->selected_condition ?? 'binh_thuong') !== 'binh_thuong') {
                        $bookPrice = (float) ($item->book->gia ?? 0);
                        $bookType = $item->book->loai_sach ?? 'binh_thuong';
                        $startCondition = $item->inventory->condition ?? 'Trung binh';
                        if ($item->selected_condition === 'mat_sach') {
                            $damageFine = (int) PricingService::calculateLostBookFine($bookPrice, $bookType, $startCondition);
                        } elseif ($item->selected_condition === 'hong_nhe') {
                            $damageFine = (int) round(PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition) * 0.5);
                        } else {
                            $damageFine = (int) PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition);
                        }
                    }
                    $itemTotal = $lateFine + $damageFine;
                    $pendingReturnTotal += $itemTotal;

                    // Tạo pseudo-fine object để hiển thị trong bảng phạt ngay
                    if ($itemTotal > 0) {
                        $fineType = match ($item->selected_condition) {
                            'mat_sach' => 'Mất sách',
                            'hong_nang' => 'Hỏng nặng',
                            'hong_nhe' => 'Hỏng nhẹ',
                            default => 'Quá hạn',
                        };
                        if ($lateFine > 0 && $damageFine > 0) {
                            $fineType = 'Quá hạn + ' . $fineType;
                        } elseif ($lateFine > 0) {
                            $fineType = 'Quá hạn';
                        } elseif ($damageFine > 0) {
                            // giữ nguyên fineType
                        }
                        $pendingReturnFines[] = (object) [
                            'id' => null,
                            'borrow_item_id' => $item->id,
                            'borrow_id' => $item->borrow_id,
                            'amount' => $itemTotal,
                            'type' => $fineType,
                            'created_at' => now(),
                            'fine_type' => $item->selected_condition,
                            'late_fine' => $lateFine,
                            'damage_fine' => $damageFine,
                            'borrowItem' => $item,
                        ];
                    }
                }
            }
        }

        // Ghép ảnh từ session vào các khoản phạt (fallback khi pendingReturnItems rỗng)
        if ($sessionProofsByItemId->isNotEmpty()) {
            $fines->getCollection()->transform(function ($fine) use ($sessionProofsByItemId) {
                $item = $fine->borrowItem;
                if (!$item) return $fine;
                $sessionProofs = $sessionProofsByItemId->get($item->id, []);
                if (!empty($sessionProofs)) {
                    $currentProofs = $item->return_proof_images ?? [];
                    if (is_string($currentProofs)) {
                        $decoded = json_decode($currentProofs, true);
                        $currentProofs = is_array($decoded) ? $decoded : [];
                    }
                    $mergedProofs = array_values(array_unique(array_filter(array_merge(
                        is_array($currentProofs) ? $currentProofs : [],
                        $sessionProofs
                    ))));
                    if (!empty($mergedProofs)) {
                        $item->return_proof_images = $mergedProofs;
                        $fine->setRelation('borrowItem', $item);
                    }
                }
                return $fine;
            });
        }

        // Kiểm tra mỗi Fine item có ảnh minh chứng chưa (báo cho view)
        $finesMissingProofs = [];
        foreach ($fines as $fine) {
            $item = $fine->borrowItem;
            if (!$item) continue;
            $raw = $item->return_proof_images ?? [];
            if (is_string($raw)) {
                $raw = json_decode($raw, true) ?? [];
            }
            $sessionProofs = $sessionProofsByItemId->get($item->id, []);
            $hasProof = !empty($raw) || !empty($sessionProofs);
            if (!$hasProof) {
                $finesMissingProofs[] = [
                    'fine_id' => $fine->id,
                    'item_id' => $item->id,
                    'book_name' => optional($item->book)->ten_sach ?? '---',
                    'borrow_id' => $fine->borrow_id,
                ];
            }
        }

        // Kiểm tra mỗi pending return item có ảnh minh chứng chưa
        $pendingMissingProofs = [];
        foreach ($pendingReturnItems as $item) {
            $raw = $item->return_proof_images ?? [];
            if (is_string($raw)) {
                $raw = json_decode($raw, true) ?? [];
            }
            $sessionProofs = $sessionProofsByItemId->get($item->id, []);
            $hasProof = !empty($raw) || !empty($sessionProofs);
            if (!$hasProof) {
                $pendingMissingProofs[] = [
                    'item_id' => $item->id,
                    'book_name' => optional($item->book)->ten_sach ?? '---',
                    'borrow_id' => $item->borrow_id,
                ];
            }
        }

        $hasMissingProofs = !empty($finesMissingProofs) || !empty($pendingMissingProofs);

        $momoEnabled = !empty(config('services.momo.partner_code')) && !empty(config('services.momo.access_key')) && !empty(config('services.momo.secret_key'));

        // Kiểm tra xem MoMo đã thanh toán thành công rồi nhưng chưa finalize trong session này
        $justPaidMomo = false;
        $momoPaidAt = null;
        $momoOrderId = session('momo_order_id');

        if ($momoOrderId && $reader) {
            $recentPayment = BorrowPayment::where('transaction_code', $momoOrderId)
                ->where('payment_status', 'success')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->first();

            if ($recentPayment) {
                $justPaidMomo = true;
                $momoPaidAt = $recentPayment->created_at;
            }
        }

        // Nếu MoMo đã paid nhưng fines vẫn còn pending → IPN chưa chạy → vẫn cho hiện thông báo đợi
        // Nếu MoMo đã paid và fines đã paid hết → đã finalize rồi → ẩn form thanh toán

        // Kiểm tra còn sách đang chờ trả hoặc fines pending không
        $hasPendingItems = $fines->count() > 0 || (!empty($pendingReturnItems) && count($pendingReturnItems) > 0);

        // Nếu MoMo đã paid + fines đã paid + session đã clear → thanh toán hoàn tất
        $momoPaymentCompleted = $justPaidMomo && !$hasPendingItems;

        return view('admin.fine-payments.index', [
            'fines' => $fines,
            'reader' => $reader,
            'pendingReturnItems' => $pendingReturnItems,
            'pendingReturnTotal' => $pendingReturnTotal,
            'pendingReturnFines' => $pendingReturnFines,
            'sessionProofsByItemId' => $sessionProofsByItemId,
            'finesMissingProofs' => $finesMissingProofs,
            'pendingMissingProofs' => $pendingMissingProofs,
            'hasMissingProofs' => $hasMissingProofs,
            'momoEnabled' => $momoEnabled,
            'justPaidMomo' => $justPaidMomo,
            'momoPaidAt' => $momoPaidAt,
            'hasPendingItems' => $hasPendingItems,
            'momoPaymentCompleted' => $momoPaymentCompleted,
        ]);
    }
}
