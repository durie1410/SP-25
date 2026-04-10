<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use App\Models\BorrowItem;
use App\Models\Borrow;
use App\Models\Fine;
use App\Models\BookDeleteRequest;
use App\Services\PricingService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReturnController extends Controller
{
    /**
     * Giao diện màn hình trả sách
     */
    public function index(Request $request)
    {
        // Xóa session cũ khi user vào trang trả sách
        // để tránh bị merge trùng ảnh khi nhấn "Chuyển thanh toán" lần nhiều
        if ($request->filled('reader_id') && session('pending_return')) {
            session()->forget('pending_return');
        }

        $readers = [];
        $selectedReader = null;
        $borrowItems = [];
        $returnedItems = [];
        $pendingDeleteInventoryIds = [];

        if ($request->filled('search')) {
            $keyword = $request->search;
            $readers = Reader::where('ho_ten', 'like', "%{$keyword}%")
                ->orWhere('so_the_doc_gia', 'like', "%{$keyword}%")
                ->get();
        }

        if ($request->filled('reader_id')) {
            $selectedReader = Reader::with(['user'])->findOrFail($request->reader_id);
            $borrowItems = BorrowItem::with(['book', 'borrow', 'inventory', 'pendingFines'])
                ->whereHas('borrow', function ($q) use ($selectedReader) {
                    $q->where('reader_id', $selectedReader->id);
                })
                ->where(function ($q) {
                    $q->whereIn('trang_thai', ['Dang muon', 'Qua han'])
                        ->orWhereHas('pendingFines', function ($fineQuery) {
                            $fineQuery->where('status', 'pending');
                        });
                })
                ->orderByDesc('id')
                ->get();

            $returnedItems = BorrowItem::with(['book', 'borrow', 'inventory'])
                ->whereHas('borrow', function ($q) use ($selectedReader) {
                    $q->where('reader_id', $selectedReader->id);
                })
                ->whereIn('trang_thai', ['Da tra', 'Hong', 'Mat sach'])
                ->where(function ($q) {
                    $q->whereHas('inventory', function ($invQuery) {
                        $invQuery->where('status', '!=', 'Co san');
                    })->orWhereNull('inventorie_id');
                })
                ->orderByDesc('ngay_tra_thuc_te')
                ->orderByDesc('id')
                ->get();

            $inventoryIds = $returnedItems->pluck('inventorie_id')->filter()->unique()->values();
            if ($inventoryIds->isNotEmpty()) {
                $pendingDeleteInventoryIds = BookDeleteRequest::whereIn('inventory_id', $inventoryIds)
                    ->where('status', 'pending')
                    ->pluck('inventory_id')
                    ->all();

                $approvedDeleteInventoryIds = BookDeleteRequest::whereIn('inventory_id', $inventoryIds)
                    ->where('status', 'approved')
                    ->pluck('inventory_id')
                    ->all();

                if (!empty($pendingDeleteInventoryIds) || !empty($approvedDeleteInventoryIds)) {
                    $hiddenIds = array_unique(array_merge($pendingDeleteInventoryIds, $approvedDeleteInventoryIds));
                    $returnedItems = $returnedItems->reject(function ($item) use ($hiddenIds) {
                        return $item->inventorie_id && in_array($item->inventorie_id, $hiddenIds);
                    })->values();
                }
            }
        }

        return view('admin.returns.index', compact('readers', 'selectedReader', 'borrowItems', 'returnedItems', 'pendingDeleteInventoryIds'));
    }

    /**
     * Lưu tạm thông tin trả sách vào session, chuyển sang trang thanh toán.
     * Nếu có ảnh minh chứng được tải lên, lưu luôn trước khi chuyển trang.
     */
    public function prepareReturn(Request $request)
    {
        $request->validate([
            'reader_id' => 'required|exists:readers,id',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:borrow_items,id',
            'items.*.selected' => 'nullable|in:1',
            'items.*.condition' => 'nullable|in:binh_thuong,hong_nhe,hong_nang,mat_sach',
            'proof_images' => 'nullable|array',
            'proof_images.*' => 'nullable|array|max:6',
            'proof_images.*.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $reader = Reader::findOrFail($request->reader_id);

        $itemsData = collect($request->items ?? []);
        $selectedItems = $itemsData->filter(function ($it) {
            return isset($it['selected']) && (string) $it['selected'] === '1';
        })->values();

        if ($selectedItems->isEmpty()) {
            return back()->with('error', 'Vui lòng tick chọn ít nhất 1 quyển sách để trả.');
        }

        $allItemIds = $itemsData->pluck('id')->filter()->all();

        $borrowItems = !empty($allItemIds)
            ? BorrowItem::with('book')->whereIn('id', $allItemIds)->get()->keyBy('id')
            : collect();

        // Bắt buộc có ảnh minh chứng cho từng sách đã chọn
        $missingProofItems = [];
        foreach ($selectedItems as $itemData) {
            $itemId = $itemData['id'] ?? null;
            if (!$itemId) {
                continue;
            }

            $item = $borrowItems->get($itemId);
            $existingProofs = [];
            if ($item) {
                $raw = $item->return_proof_images ?? [];
                if (is_array($raw)) {
                    $existingProofs = $raw;
                } elseif (is_string($raw)) {
                    $decoded = json_decode($raw, true);
                    $existingProofs = is_array($decoded) ? $decoded : [];
                }
            }

            $files = $request->file("proof_images.{$itemId}", []);
            $hasUpload = !empty($files) && !(count($files) === 1 && !$files[0]);

            if (empty($existingProofs) && !$hasUpload) {
                $missingProofItems[] = $item?->book?->ten_sach ?: "Item #{$itemId}";
            }
        }

        if (!empty($missingProofItems)) {
            return back()->with('error', 'Vui lòng tải ảnh minh chứng cho: ' . implode(', ', $missingProofItems));
        }

        // Lưu ảnh minh chứng nếu có tải lên
        // hasFile() top-level không hoạt động với cấu trúc proof_images[374][files]
        // nên check bằng cách duyệt allItemIds
        $hasAnyFile = false;
        foreach ($allItemIds as $itemId) {
            $files = $request->file("proof_images.{$itemId}", []);
            if (!empty($files) && !(count($files) === 1 && !$files[0])) {
                $hasAnyFile = true;
                break;
            }
        }

        if ($hasAnyFile) {
            try {
                DB::beginTransaction();

                foreach ($allItemIds as $itemId) {
                    $proofFiles = $request->file("proof_images.{$itemId}", []);
                    if (empty($proofFiles) || (count($proofFiles) === 1 && !$proofFiles[0])) {
                        continue;
                    }

                    $item = $borrowItems->get($itemId);
                    if (!$item) continue;

                    $existing = is_array($item->return_proof_images)
                        ? $item->return_proof_images
                        : (is_string($item->return_proof_images) ? json_decode($item->return_proof_images, true) : []);
                    $existing = is_array($existing) ? $existing : [];

                    $uploaded = [];
                    foreach ($proofFiles as $proofFile) {
                        if (!$proofFile) continue;
                        $upload = FileUploadService::uploadImage($proofFile, 'return_proofs', [
                            'max_size' => 4096,
                            'resize' => true,
                            'width' => 1400,
                            'height' => 1400,
                            'disk' => 'public',
                        ]);
                        if (!empty($upload['path'])) {
                            $uploaded[] = $upload['path'];
                        }
                    }

                    if (!empty($uploaded)) {
                        $item->update([
                            'return_proof_images' => array_values(array_unique(array_merge($existing, $uploaded))),
                        ]);
                        // Cập nhật lại vào collection hiện có để dùng cho session phía dưới
                        $item->return_proof_images = array_values(array_unique(array_merge($existing, $uploaded)));
                        $borrowItems->put($itemId, $item);
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Lỗi upload ảnh: ' . $e->getMessage());
            }
        }

        // Đồng bộ lại dữ liệu item sau khi upload (đảm bảo ảnh mới nhất)
        if (!empty($allItemIds)) {
            $borrowItems = BorrowItem::with('borrow')->whereIn('id', $allItemIds)->get()->keyBy('id');
        }

        // Lưu lại tình trạng sách đã chọn vào DB trước khi chuyển thanh toán
        $selectedConditions = $selectedItems->mapWithKeys(function ($it) {
            $itemId = $it['id'] ?? null;
            if (!$itemId) {
                return [];
            }
            return [$itemId => $it['condition'] ?? 'binh_thuong'];
        });

        if ($selectedConditions->isNotEmpty()) {
            try {
                DB::beginTransaction();
                foreach ($selectedConditions as $itemId => $condition) {
                    $item = $borrowItems->get($itemId);
                    if (!$item || !$item->borrow || (int) $item->borrow->reader_id !== (int) $reader->id) {
                        throw new \Exception('Sách không thuộc khách đã chọn.');
                    }
                    $item->update(['tinh_trang_sach_cuoi' => $condition]);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Không thể lưu tình trạng sách: ' . $e->getMessage());
            }
        }

        // Lưu tạm vào session: id + condition đã chọn + ảnh đã upload
        $sessionItems = $selectedItems->map(function ($it) use ($borrowItems) {
            $itemId = $it['id'] ?? null;
            $item = $itemId ? $borrowItems->get($itemId) : null;

            $proofs = [];
            if ($item) {
                $rawProofs = $item->return_proof_images ?? [];
                if (is_array($rawProofs)) {
                    $proofs = $rawProofs;
                } elseif (is_string($rawProofs)) {
                    $decoded = json_decode($rawProofs, true);
                    $proofs = is_array($decoded) ? $decoded : [];
                }
            }

            return [
                'id' => $itemId,
                'selected' => '1',
                'condition' => $it['condition'] ?? 'binh_thuong',
                'return_proof_images' => array_values(array_filter($proofs)),
            ];
        })->filter(fn ($row) => !empty($row['id']))->values();

        $returnData = [
            'reader_id' => $reader->id,
            'items' => $sessionItems->toArray(),
        ];

        session(['pending_return' => $returnData]);

        return redirect()->route('admin.fine-payments.index', ['reader_id' => $reader->id])
            ->with('info', 'Đã chọn ' . $selectedItems->count() . ' quyển sách. Vui lòng thanh toán phạt (nếu có) để hoàn tất trả sách.');
    }


    /**
     * Xử lý trả sách (tick chọn nhiều quyển)
     */
    public function processReturn(Request $request)
    {
        // Xóa ảnh minh chứng không cần validation items/images
        if (in_array($request->input('action'), ['delete_proof', 'delete_all_proofs'])) {
            $request->validate([
                'reader_id' => 'required|exists:readers,id',
                'item_id' => 'required|exists:borrow_items,id',
                'path' => 'nullable|string',
            ]);
        } else {
            $request->validate([
                'reader_id' => 'required|exists:readers,id',
                'items' => 'required|array',
                'items.*.id' => 'required|exists:borrow_items,id',
                'items.*.selected' => 'nullable|in:1',
                'items.*.condition' => 'nullable|in:binh_thuong,hong_nhe,hong_nang,mat_sach',
                'proof_images' => 'nullable|array',
                'proof_images.*' => 'nullable|array|max:6',
                'proof_images.*.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            ]);
        }

        $reader = Reader::findOrFail($request->reader_id);
        $returnDate = now();
        $processedBorrows = [];
        $action = $request->input('action', 'return');

        // Xóa 1 ảnh minh chứng trả sách
        if ($action === 'delete_proof') {
            $itemId = $request->input('item_id');
            $pathToDelete = $request->input('path');

            if (!$itemId || !$pathToDelete) {
                return back()->with('error', 'Thiếu thông tin xóa ảnh.');
            }

            $item = BorrowItem::where('id', $itemId)
                ->whereHas('borrow', fn($q) => $q->where('reader_id', $reader->id))
                ->first();

            if (!$item) {
                return back()->with('error', 'Không tìm thấy sách.');
            }

            $existingProofImages = is_array($item->return_proof_images)
                ? $item->return_proof_images
                : (is_string($item->return_proof_images) ? json_decode($item->return_proof_images, true) : []);
            $existingProofImages = is_array($existingProofImages) ? $existingProofImages : [];

            $newProofImages = array_values(array_filter($existingProofImages, fn($p) => $p !== $pathToDelete));

            $item->update(['return_proof_images' => $newProofImages]);

            // Xóa file vật lý
            $diskPath = ltrim(str_replace(['\\', 'storage/'], ['/', ''], $pathToDelete), '/');
            if (\Storage::disk('public')->exists($diskPath)) {
                \Storage::disk('public')->delete($diskPath);
            }

            return redirect()->route('admin.returns.index', ['reader_id' => $reader->id])
                ->with('success', 'Đã xóa ảnh minh chứng.');
        }

        // Xóa tất cả ảnh minh chứng của 1 item
        if ($action === 'delete_all_proofs') {
            $itemId = $request->input('item_id');
            if (!$itemId) {
                return back()->with('error', 'Thiếu thông tin sách.');
            }

            $item = BorrowItem::where('id', $itemId)
                ->whereHas('borrow', fn($q) => $q->where('reader_id', $reader->id))
                ->first();

            if (!$item) {
                return back()->with('error', 'Không tìm thấy sách.');
            }

            $existingProofImages = is_array($item->return_proof_images)
                ? $item->return_proof_images
                : (is_string($item->return_proof_images) ? json_decode($item->return_proof_images, true) : []);
            $existingProofImages = is_array($existingProofImages) ? $existingProofImages : [];

            // Xóa file vật lý
            foreach ($existingProofImages as $path) {
                $diskPath = ltrim(str_replace(['\\', 'storage/'], ['/', ''], $path), '/');
                if (\Storage::disk('public')->exists($diskPath)) {
                    \Storage::disk('public')->delete($diskPath);
                }
            }

            $item->update(['return_proof_images' => []]);

            return redirect()->route('admin.returns.index', ['reader_id' => $reader->id])
                ->with('success', 'Đã xóa tất cả ảnh minh chứng.');
        }

        $selectedItems = collect($request->items)->filter(function ($it) {
            return isset($it['selected']) && (string) $it['selected'] === '1';
        })->values();

        // Xử lý upload ảnh độc lập — không cần tick checkbox
        if ($action === 'attach_proof') {
            try {
                DB::beginTransaction();
                $uploaded = false;

                // Lấy tất cả borrow_items của reader (không cần selected)
                $readerBorrowItems = BorrowItem::with(['borrow'])
                    ->whereHas('borrow', fn($q) => $q->where('reader_id', $reader->id))
                    ->whereIn('trang_thai', ['Dang muon', 'Qua han'])
                    ->get()
                    ->keyBy('id');

                // Duyệt tất cả item từ form (không lọc selected)
                foreach ($request->items as $itemData) {
                    $itemId = $itemData['id'] ?? null;
                    if (!$itemId || !$readerBorrowItems->has($itemId)) {
                        continue;
                    }

                    $item = $readerBorrowItems->get($itemId);
                    $proofFiles = $request->file("proof_images.{$item->id}", []);

                    if (empty($proofFiles) || (count($proofFiles) === 1 && !$proofFiles[0])) {
                        continue;
                    }

                    $existingProofImages = is_array($item->return_proof_images)
                        ? $item->return_proof_images
                        : (is_string($item->return_proof_images) ? json_decode($item->return_proof_images, true) : []);
                    $existingProofImages = is_array($existingProofImages) ? $existingProofImages : [];

                    $uploadedProofImages = [];
                    foreach ($proofFiles as $proofFile) {
                        if (!$proofFile) continue;

                        $upload = FileUploadService::uploadImage($proofFile, 'return_proofs', [
                            'max_size' => 4096,
                            'resize' => true,
                            'width' => 1400,
                            'height' => 1400,
                            'disk' => 'public',
                        ]);

                        if (!empty($upload['path'])) {
                            $uploadedProofImages[] = $upload['path'];
                        }
                    }

                    if (!empty($uploadedProofImages)) {
                        $item->update([
                            'return_proof_images' => array_values(array_unique(array_merge($existingProofImages, $uploadedProofImages))),
                        ]);
                        $uploaded = true;
                    }
                }

                DB::commit();

                if ($uploaded) {
                    return redirect()->route('admin.returns.index', ['reader_id' => $reader->id])
                        ->with('success', 'Đã lưu ảnh minh chứng.');
                }

                return redirect()->route('admin.returns.index', ['reader_id' => $reader->id])
                    ->with('error', 'Không có ảnh nào được tải lên.');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('admin.returns.index', ['reader_id' => $reader->id])
                    ->with('error', 'Lỗi upload ảnh: ' . $e->getMessage());
            }
        }

        if ($selectedItems->isEmpty()) {
            return back()->with('error', 'Vui lòng tick chọn ít nhất 1 quyển sách để trả.');
        }

        try {
            DB::beginTransaction();

            foreach ($selectedItems as $itemData) {
                $item = BorrowItem::with(['book', 'inventory', 'borrow'])->findOrFail($itemData['id']);

                if (!$item->borrow || (int) $item->borrow->reader_id !== (int) $reader->id) {
                    throw new \Exception('Sách không thuộc khách đã chọn.');
                }

                $existingProofImages = is_array($item->return_proof_images)
                    ? $item->return_proof_images
                    : (is_string($item->return_proof_images) ? json_decode($item->return_proof_images, true) : []);
                $existingProofImages = is_array($existingProofImages) ? $existingProofImages : [];

                $uploadedProofImages = [];
                foreach ($request->file("proof_images.{$item->id}", []) as $proofFile) {
                    if (!$proofFile) {
                        continue;
                    }

                    $upload = FileUploadService::uploadImage($proofFile, 'return_proofs', [
                        'max_size' => 4096,
                        'resize' => true,
                        'width' => 1400,
                        'height' => 1400,
                        'disk' => 'public',
                    ]);

                    if (!empty($upload['path'])) {
                        $uploadedProofImages[] = $upload['path'];
                    }
                }

                $returnProofImages = array_values(array_unique(array_merge($existingProofImages, $uploadedProofImages)));

                if (!in_array($item->trang_thai, ['Dang muon', 'Qua han'])) {
                    continue;
                }

                $condition = $itemData['condition'] ?? 'binh_thuong';

                // Cập nhật ảnh minh chứng (không đổi trạng thái)
                $item->update([
                    'return_proof_images' => $returnProofImages,
                    'tinh_trang_sach_cuoi' => $condition,
                ]);

                // Tính phạt
                $lateFine = 0;
                if ($item->ngay_hen_tra && Carbon::parse($item->ngay_hen_tra)->startOfDay() < $returnDate->copy()->startOfDay()) {
                    $lateFine = PricingService::calculateLateReturnFine($item->ngay_hen_tra, $returnDate, 1);
                }

                $damageFine = 0;
                if ($condition !== 'binh_thuong') {
                    $bookPrice = $item->book->gia ?? 0;
                    $bookType = $item->book->loai_sach ?? 'binh_thuong';
                    $startCondition = $item->inventory->condition ?? 'Trung binh';

                    if ($condition === 'mat_sach') {
                        $damageFine = PricingService::calculateLostBookFine($bookPrice, $bookType, $startCondition);
                    } elseif ($condition === 'hong_nhe') {
                        $damageFine = (int) round(PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition) * 0.5);
                    } else {
                        $damageFine = (int) PricingService::calculateDamagedBookFine($bookPrice, $bookType, $startCondition);
                    }
                }

                // Lưu tạm tổng phạt vào borrow_item (chưa cập nhật trạng thái)
                $item->updateQuietly(['tien_phat' => $lateFine + $damageFine]);

                // Tạo Fine records (chờ thanh toán)
                if ($lateFine > 0) {
                    Fine::firstOrCreate(
                        ['borrow_item_id' => $item->id, 'type' => 'late_return', 'status' => 'pending'],
                        [
                            'borrow_id' => $item->borrow_id,
                            'reader_id' => $reader->id,
                            'amount' => $lateFine,
                            'description' => 'Phạt trễ hạn sách: ' . ($item->book?->ten_sach ?? 'Không xác định'),
                            'due_date' => $returnDate->toDateString(),
                            'created_by' => auth()->id() ?? 1,
                        ]
                    );
                }

                if ($damageFine > 0) {
                    $damageLabel = match ($condition) {
                        'hong_nhe' => 'hỏng nhẹ',
                        'hong_nang' => 'hỏng nặng',
                        'mat_sach' => 'mất',
                        default => 'hỏng',
                    };

                    Fine::firstOrCreate(
                        ['borrow_item_id' => $item->id, 'type' => $condition === 'mat_sach' ? 'lost_book' : 'damaged_book', 'status' => 'pending'],
                        [
                            'borrow_id' => $item->borrow_id,
                            'reader_id' => $reader->id,
                            'amount' => $damageFine,
                            'description' => "Phạt {$damageLabel} sách: " . ($item->book?->ten_sach ?? 'Không xác định'),
                            'due_date' => $returnDate->toDateString(),
                            'created_by' => auth()->id() ?? 1,
                        ]
                    );
                }

                $processedBorrows[$item->borrow_id] = $item->borrow_id;
            }

            DB::commit();

            if ($action === 'attach_proof') {
                return redirect()->route('admin.returns.index', ['reader_id' => $reader->id])
                    ->with('success', 'Đã lưu ảnh minh chứng.');
            }

            // Lưu ảnh minh chứng vào session để hiển thị ở trang thanh toán phạt
            $sessionItems = $selectedItems->map(function ($it) use ($borrowItems) {
                $itemId = $it['id'] ?? null;
                $item = $itemId ? $borrowItems->get($itemId) : null;
                $proofs = [];
                if ($item) {
                    $rawProofs = $item->return_proof_images ?? [];
                    if (is_array($rawProofs)) {
                        $proofs = $rawProofs;
                    } elseif (is_string($rawProofs)) {
                        $decoded = json_decode($rawProofs, true);
                        $proofs = is_array($decoded) ? $decoded : [];
                    }
                }
                return [
                    'id' => $itemId,
                    'selected' => '1',
                    'condition' => $it['condition'] ?? 'binh_thuong',
                    'return_proof_images' => array_values(array_filter($proofs)),
                ];
            })->filter(fn ($row) => !empty($row['id']))->values();

            $returnData = [
                'reader_id' => $reader->id,
                'items' => $sessionItems->toArray(),
            ];
            session(['pending_return' => $returnData]);

            return redirect()->route('admin.fine-payments.index', ['reader_id' => $reader->id])
                ->with('success', 'Đã ghi nhận trả sách. Vui lòng thanh toán các khoản phạt phát sinh (nếu có).');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi xử lý trả sách: ' . $e->getMessage());
        }
    }

    private function downgradeInventoryCondition(?string $condition): string
    {
        $current = trim((string) $condition);

        return match ($current) {
            'Moi' => 'Tot',
            'Tot' => 'Trung binh',
            'Trung binh' => 'Cu',
            'Cu' => 'Cu',
            'Hong' => 'Hong',
            default => 'Trung binh',
        };
    }

    public function approveReturnedToStock(Request $request, BorrowItem $item)
    {
        $request->validate([
            'reader_id' => 'required|exists:readers,id',
        ]);

        $item->load(['borrow', 'inventory']);
        if (!$item->borrow || (int) $item->borrow->reader_id !== (int) $request->reader_id) {
            return back()->with('error', 'Sách không thuộc khách đã chọn.');
        }

        if (!in_array($item->trang_thai, ['Da tra', 'Hong', 'Mat sach'])) {
            return back()->with('error', 'Sách chưa ở trạng thái đã trả.');
        }

        // Tái tạo inventory nếu thiếu (sách đã bị xóa khỏi kho)
        $inventory = $item->inventory;
        if (!$inventory) {
            $condition = $item->tinh_trang_sach_cuoi ?? 'binh_thuong';
            $invCondition = $condition === 'mat_sach' ? 'Hong' : ($condition === 'hong_nang' ? 'Cu' : 'Trung binh');
            $maxBarcode = \App\Models\Inventory::max('barcode') ?? 'INV000000';
            $nextNum = (int) preg_replace('/\D/', '', $maxBarcode) + 1;
            $newBarcode = 'INV' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            $inventory = \App\Models\Inventory::create([
                'book_id' => $item->book_id,
                'barcode' => $newBarcode,
                'status' => 'Co san',
                'storage_type' => 'Kho',
                'condition' => $invCondition,
                'location' => 'Kho chính',
                'created_by' => auth()->id() ?? 1,
            ]);
            $item->update(['inventorie_id' => $inventory->id]);
        }

        $hasPendingDelete = BookDeleteRequest::where('inventory_id', $inventory->id)
            ->where('status', 'pending')
            ->exists();
        if ($hasPendingDelete) {
            return back()->with('error', 'Sách đang có yêu cầu xóa, không thể duyệt về kho.');
        }

        try {
            DB::beginTransaction();

            $inventory->update([
                'status' => 'Co san',
                'storage_type' => 'Kho',
            ]);

            $item->update([
                'trang_thai' => 'Da tra',
                'ngay_tra_thuc_te' => $item->ngay_tra_thuc_te ?? now()->toDateString(),
            ]);

            DB::commit();
            return back()->with('success', 'Đã duyệt sách về kho.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi duyệt về kho: ' . $e->getMessage());
        }
    }

    public function deleteReturnedItem(Request $request, BorrowItem $item)
    {
        $request->validate([
            'reader_id' => 'required|exists:readers,id',
        ]);

        $item->load(['borrow', 'inventory', 'book']);
        if (!$item->borrow || (int) $item->borrow->rephpader_id !== (int) $request->reader_id) {
            return back()->with('error', 'Sách không thuộc khách đã chọn.');
        }

        if (!in_array($item->trang_thai, ['Da tra', 'Hong', 'Mat sach'], true)) {
            return back()->with('error', 'Sách chưa ở trạng thái đã trả để xử lý.');
        }

        if (!$item->inventory) {
            return back()->with('error', 'Không tìm thấy sách trong kho để xóa.');
        }

        $hasPendingDelete = BookDeleteRequest::where('inventory_id', $item->inventory->id)
            ->where('status', 'pending')
            ->exists();
        if ($hasPendingDelete) {
            return back()->with('info', 'Sách đã có yêu cầu xóa đang chờ duyệt.');
        }

        $condition = trim((string) ($item->tinh_trang_sach_cuoi ?? ''));
        $isLost = $condition === 'mat_sach' || $item->trang_thai === 'Mat sach';
        $reasonLabel = $isLost ? 'mất sách' : 'hỏng sách';

        try {
            DB::beginTransaction();

            BookDeleteRequest::create([
                'book_id' => $item->book_id,
                'inventory_id' => $item->inventory->id,
                'borrow_item_id' => $item->id,
                'requested_by' => Auth::id() ?? 1,
                'status' => 'pending',
                'reason' => '[TRA SACH] ' . $reasonLabel,
            ]);

            DB::commit();
            return back()->with('success', 'Đã gửi yêu cầu xóa sách. Vui lòng chờ duyệt.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi xóa sách: ' . $e->getMessage());
        }
    }
}
