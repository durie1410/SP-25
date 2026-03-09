<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Category;
use App\Models\Borrow;
use App\Models\Reader;
use App\Models\PurchasableBook;
use App\Models\Document;
use App\Models\Author;
use App\Models\Inventory;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{

    public function trangchu()
    {
        // Lấy danh sách categories cho sidebar và phần Chủ đề
        $categories = Category::withCount('books')->orderBy('ten_the_loai')->get();
        $categoriesTop = $categories->sortByDesc('books_count')->take(12);

        // Lấy tất cả book_id từ kho (cả kho và trưng bày) - CHỈ hiển thị sách đã có trong kho
        $bookIdsFromInventory = Inventory::select('book_id')
            ->distinct()
            ->pluck('book_id')
            ->toArray();

        // Nếu không có sách nào trong kho, trả về collections rỗng
        if (empty($bookIdsFromInventory)) {
            $featured_books = collect();
            $top_books = collect();
            $best_collections = collect();
            $top_selling_books = collect();
            $document_books = collect();
            $documents = Document::orderBy('published_date', 'desc')->limit(5)->get();
            $diem_sach_featured = null;
            $diem_sach_list = collect();
            $most_viewed_books = collect();
            $new_books = collect();
            $recommended_books = collect();
            $suggested_books = collect();
        } else {
            // 1. Lấy Sách Nổi bật (is_featured = true) - CHỈ lấy sách có trong kho
            $featured_books = Book::where(function($query) {
                    $query->where('is_featured', true)
                          ->orWhere('is_featured', 1);
                })
                ->where(function($query) {
                    $query->where('trang_thai', 'active')
                          ->orWhereNull('trang_thai');
                })
                ->whereIn('id', $bookIdsFromInventory)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Nếu không có sách nổi bật, lấy sách mới nhất có trong kho
            if ($featured_books->isEmpty()) {
                $featured_books = Book::where(function($query) {
                        $query->where('trang_thai', 'active')
                              ->orWhereNull('trang_thai');
                    })
                    ->whereIn('id', $bookIdsFromInventory)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            }

            // 2. Lấy Sách Hay (Sách mới nhất, không phải nổi bật) - CHỈ lấy sách có trong kho
            $top_books = Book::where(function($query) {
                    $query->where('trang_thai', 'active')
                          ->orWhereNull('trang_thai');
                })
                ->where(function($query) {
                    $query->where('is_featured', false)
                          ->orWhereNull('is_featured')
                          ->orWhere('is_featured', 0);
                })
                ->whereIn('id', $bookIdsFromInventory)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            // 2.5. Lấy Tuyển tập hay nhất (dựa trên đánh giá và lượt xem) - CHỈ lấy sách có trong kho
            $best_collections = Book::where(function($query) {
                    $query->where('trang_thai', 'active')
                          ->orWhereNull('trang_thai');
                })
                ->where(function($query) {
                    $query->where('danh_gia_trung_binh', '>=', 4.0)
                          ->orWhere('so_luot_xem', '>', 100);
                })
                ->whereIn('id', $bookIdsFromInventory)
                ->orderBy('danh_gia_trung_binh', 'desc')
                ->orderBy('so_luot_xem', 'desc')
                ->limit(6)
                ->get();

            // 3. Lấy Sách Mua/Xem nhiều nhất (top selling) - CHỈ lấy sách có trong kho
            $top_selling_books = Book::where('trang_thai', 'active')
                ->whereIn('id', $bookIdsFromInventory)
                ->orderBy('so_luong_ban', 'desc')
                ->orderBy('so_luot_xem', 'desc')
                ->limit(5)
                ->get();

            // 4. Lấy Văn bản Luật (cho phần "Điểm sách")
            $documents = Document::orderBy('published_date', 'desc')
                ->limit(5)
                ->get();

            // Nếu không có documents, lấy sách mới nhất có trong kho làm tài liệu
            if ($documents->isEmpty()) {
                $document_books = Book::where('trang_thai', 'active')
                    ->whereIn('id', $bookIdsFromInventory)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } else {
                $document_books = collect();
            }

            // 4.5. Lấy sách cho phần "Điểm sách" (1 sách lớn + 3 sách nhỏ) - CHỈ lấy sách có trong kho
            // Sách lớn: sách nổi bật hoặc sách mới nhất
            $diem_sach_featured = Book::where(function($query) {
                    $query->where('trang_thai', 'active')
                          ->orWhereNull('trang_thai');
                })
                ->where(function($query) {
                    $query->where('is_featured', true)
                          ->orWhere('is_featured', 1);
                })
                ->whereIn('id', $bookIdsFromInventory)
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->first();

            // Nếu không có sách nổi bật, lấy sách mới nhất có trong kho
            if (!$diem_sach_featured) {
                $diem_sach_featured = Book::where(function($query) {
                        $query->where('trang_thai', 'active')
                              ->orWhereNull('trang_thai');
                    })
                    ->whereIn('id', $bookIdsFromInventory)
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
                    ->first();
            }

            // 3 sách nhỏ: sách mới nhất có trong kho (không trùng với sách lớn)
            $diem_sach_list = collect();
            if ($diem_sach_featured) {
                $diem_sach_list = Book::where(function($query) {
                        $query->where('trang_thai', 'active')
                              ->orWhereNull('trang_thai');
                    })
                    ->where('id', '!=', $diem_sach_featured->id)
                    ->whereIn('id', $bookIdsFromInventory)
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get();
            } else {
                // Nếu không có sách lớn, lấy 4 sách mới nhất
                $diem_sach_list = Book::where(function($query) {
                        $query->where('trang_thai', 'active')
                              ->orWhereNull('trang_thai');
                    })
                    ->whereIn('id', $bookIdsFromInventory)
                    ->orderBy('created_at', 'desc')
                    ->limit(4)
                    ->get();
            }

            // 6. Lấy Sách xem nhiều nhất (most viewed) - CHỈ lấy sách có trong kho
            $most_viewed_books = Book::where(function($query) {
                    $query->where('trang_thai', 'active')
                          ->orWhereNull('trang_thai');
                })
                ->whereIn('id', $bookIdsFromInventory)
                ->orderBy('so_luot_xem', 'desc')
                ->limit(9)
                ->get();

            // 5 & 7. Gộp Sách Mới và Có thể bạn thích - tổng cộng 6 sản phẩm - CHỈ lấy sách có trong kho
            // Lấy 3 sách mới nhất có trong kho
            $new_books_temp = Book::where('trang_thai', 'active')
                ->whereIn('id', $bookIdsFromInventory)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
            
            $new_books_ids = $new_books_temp->pluck('id')->toArray();
            
            // Lấy 3 sách đề xuất có trong kho (không trùng với sách mới)
            $recommended_books_temp = Book::where('trang_thai', 'active')
                ->whereIn('id', $bookIdsFromInventory)
                ->whereNotIn('id', $new_books_ids)
                ->where(function($query) {
                    $query->where('danh_gia_trung_binh', '>=', 4.0)
                          ->orWhere('so_luong_ban', '>', 0);
                })
                ->inRandomOrder()
                ->limit(3)
                ->get();
            
            // Gộp lại và lấy 6 sản phẩm
            $combined_books = $new_books_temp->merge($recommended_books_temp)->unique('id')->take(6);
            
            // Chia cho 2 section: 3 sách mới + 3 sách đề xuất
            $new_books = $combined_books->take(3);
            $recommended_books = $combined_books->skip(3)->take(3);

            // 8. Lấy Sách đề xuất bổ sung (cho phần đề xuất bên dưới) - 3 sản phẩm - CHỈ lấy sách có trong kho
            $suggested_books_ids = $combined_books->pluck('id')->toArray();
            $suggested_books = Book::where('trang_thai', 'active')
                ->whereIn('id', $bookIdsFromInventory)
                ->whereNotIn('id', $suggested_books_ids)
                ->where(function($query) {
                    $query->where('so_luot_xem', '>', 0)
                          ->orWhere('so_luong_ban', '>', 0);
                })
                ->orderBy('so_luot_xem', 'desc')
                ->orderBy('so_luong_ban', 'desc')
                ->limit(3)
                ->get();
        }

        // 9. Lấy danh sách Tác giả (5 tác giả đang hoạt động)
        $authors = Author::active()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Nếu không có tác giả active, lấy tất cả tác giả
        if ($authors->isEmpty()) {
            $authors = Author::orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // 10. Lấy tin tức (documents) - 1 tin nổi bật và 3 tin mới nhất
        $news = Document::orderBy('published_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();
        
        $featuredNews = $news->count() > 0 ? $news->first() : null;
        $otherNews = $news->count() > 1 ? $news->skip(1)->take(3) : collect();

        return view('trangchu', compact(
            'categories',
            'categoriesTop',
            'featured_books',
            'top_books',
            'best_collections',
            'document_books',
            'documents',
            'top_selling_books',
            'new_books',
            'most_viewed_books',
            'recommended_books',
            'suggested_books',
            'diem_sach_featured',
            'diem_sach_list',
            'authors',
            'news',
            'featuredNews',
            'otherNews'
        ));
    }

    public function borrowBook(Request $request)
    {
        // Nếu items tồn tại và là MẢNG RỖNG thì mới coi là giỏ trống
if (is_array($request->items ?? null) && count($request->items) === 0) {
    return response()->json([
        'success' => false,
        'message' => 'Giỏ sách của bạn đang trống'
    ], 400);
}

        // Kiểm tra xem request có items[] không (kiểu mới) hay là kiểu cũ
        $hasItemsArray = $request->has('items') && is_array($request->items);
        
        if ($hasItemsArray) {
            // Validate cho kiểu mới (mảng items)
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.book_id' => 'required|exists:books,id',
                'items.*.borrow_days' => 'required|integer|min:1|max:30',
                'items.*.distance' => 'nullable|numeric|min:0|max:10',
                'note' => 'nullable|string|max:1000',
            ]);
        } else {
            // Validate cho kiểu cũ (book_id, quantity, borrow_days chung)
            $request->validate([
                'book_id' => 'required|exists:books,id',
                'borrow_days' => 'nullable|integer|min:1|max:30',
                'quantity' => 'nullable|integer|min:1|max:10',
                'note' => 'nullable|string|max:1000',
            ]);
        }

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để gửi yêu cầu mượn sách'
            ], 401);
        }

        /** @var User $user */
        $user = auth()->user();
        
        // Kiểm tra thông tin user có đầy đủ không
        if (!$user->hasCompleteProfile()) {
            $missingFields = $user->getMissingFields();
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cập nhật đầy đủ thông tin cá nhân trước khi mượn sách. Các trường còn thiếu: ' . implode(', ', $missingFields),
                'redirect' => route('account')
            ], 400);
        }
        
        // Tự động tạo reader nếu chưa có (từ thông tin user)
        $reader = $user->reader;
        if (!$reader) {
            // Tạo số thẻ độc giả unique
            $soTheDocGia = 'RD' . strtoupper(\Illuminate\Support\Str::random(6));
            $maxAttempts = 10;
            $attempt = 0;
            while (Reader::where('so_the_doc_gia', $soTheDocGia)->exists() && $attempt < $maxAttempts) {
                $soTheDocGia = 'RD' . strtoupper(\Illuminate\Support\Str::random(6));
                $attempt++;
            }
            if ($attempt >= $maxAttempts) {
                $soTheDocGia = 'RD' . strtoupper(\Illuminate\Support\Str::random(4)) . time();
            }
            
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
            return response()->json([
                'success' => false,
                'message' => 'Thẻ độc giả của bạn đã bị khóa hoặc tạm dừng. Vui lòng liên hệ thư viện.'
            ], 400);
        }

        if ($reader->ngay_het_han < now()->toDateString()) {
            return response()->json([
                'success' => false,
                'message' => 'Thẻ độc giả của bạn đã hết hạn. Vui lòng gia hạn thẻ.'
            ], 400);
        }

        try {
            DB::beginTransaction();
            
            if ($hasItemsArray) {
                // XỬ LÝ KIỂU MỚI: Mảng items với thông số khác nhau
                return $this->borrowMultipleItemsWithDifferentParams($request, $reader);
            } else {
                // XỬ LÝ KIỂU CŨ: Giữ nguyên logic cũ
                $book = Book::findOrFail($request->book_id);
                $borrowDays = (int) $request->input('borrow_days', 14);
                $quantity = (int) $request->input('quantity', 1);
                $note = $request->input('note', '');
                $distance = floatval($request->input('distance', 0));
                
                // Kiểm tra số lượng hợp lệ
                $availableCopies = Inventory::where('book_id', $book->id)
                    ->where('status', 'Co san')
                    ->count();
                
                if ($quantity < 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Số lượng mượn phải ít nhất 1 cuốn.'
                    ], 400);
                }
                
                if ($quantity > $availableCopies) {
                    return response()->json([
                        'success' => false,
                        'message' => "Chỉ còn {$availableCopies} cuốn sách có sẵn. Vui lòng chọn lại số lượng."
                    ], 400);
                }
                
                // Tính tiền ship dựa trên khoảng cách (chỉ tính 1 lần)
                $tienShip = 0;
                if ($distance > 5) {
                    $extraKm = $distance - 5;
                    $tienShip = (int) ($extraKm * 5000);
                }
                    
                // Tìm các inventory có sẵn (số lượng theo yêu cầu)
                $availableInventories = Inventory::where('book_id', $book->id)
                    ->where('status', 'Co san')
                    ->limit($quantity)
                    ->get();
                
                // Nếu không đủ số lượng
                if ($availableInventories->count() < $quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Chỉ còn {$availableInventories->count()} cuốn sách có sẵn. Vui lòng chọn lại số lượng."
                    ], 400);
                }
                
                try {
                    // Parse địa chỉ từ reader nếu có (hoặc để null, admin sẽ cập nhật sau)
                    $diaChi = $reader->dia_chi ?? '';
                    $parts = explode(',', $diaChi);
                    
                    // Tính ngày hẹn trả
                    $ngayMuon = now()->toDateString();
                    $ngayHenTra = now()->addDays($borrowDays)->toDateString();
                    
                    // Tính tổng phí thuê và tiền cọc cho tất cả items
                    $hasCard = true; // Reader đã có thẻ
                    $totalTienCoc = 0;
                    $totalTienThue = 0;
                    
                    foreach ($availableInventories as $inventory) {
                        $fees = \App\Services\PricingService::calculateFees(
                            $book,
                            $inventory,
                            $ngayMuon,
                            $ngayHenTra,
                            $hasCard
                        );
                        $totalTienCoc += $fees['tien_coc'];
                        $totalTienThue += $fees['tien_thue'];
                    }
                    
                    // Tạo phiếu mượn
                    $borrow = Borrow::create([
                        'reader_id' => $reader->id,
                        'ten_nguoi_muon' => $reader->ho_ten,
                        'so_dien_thoai' => $reader->so_dien_thoai ?? '',
                        'tinh_thanh' => count($parts) > 2 ? trim($parts[count($parts) - 1]) : '',
                        'huyen' => count($parts) > 1 ? trim($parts[count($parts) - 2]) : '',
                        'xa' => count($parts) > 0 ? trim($parts[0]) : '',
                        'so_nha' => '',
                        'ngay_muon' => $ngayMuon,
                        'trang_thai' => 'Cho duyet',
                        'tien_coc' => $totalTienCoc,
                        'tien_thue' => $totalTienThue,
                        'tien_ship' => $tienShip,
                        'tong_tien' => $totalTienCoc + $totalTienThue + $tienShip,
                        'ghi_chu' => trim($note . (empty($note) ? '' : ' ') . "(Yêu cầu mượn $quantity cuốn - $borrowDays ngày)"),
                    ]);
                    
                    // Tạo nhiều BorrowItem với trạng thái 'Cho duyet' (theo số lượng)
                    $borrowItems = [];
                    foreach ($availableInventories as $index => $inventory) {
                        // Chỉ tính phí ship cho item đầu tiên
                        $itemShipFee = ($index === 0) ? $tienShip : 0;
                        
                        // Tính phí cho từng item
                        $fees = \App\Services\PricingService::calculateFees(
                            $book,
                            $inventory,
                            $ngayMuon,
                            $ngayHenTra,
                            $hasCard
                        );
                        
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
                            'ghi_chu' => trim($note . (empty($note) ? '' : ' ') . "(Yêu cầu mượn $quantity cuốn - $borrowDays ngày)" . ($distance > 0 && $index === 0 ? " - Khoảng cách: {$distance}km" : '')),
                        ]);
                        
                        $borrowItems[] = $borrowItem;
                    }
                    
                    // Lấy BorrowItem đầu tiên để trả về response
                    $borrowItem = $borrowItems[0];

                    // Cập nhật tổng tiền của Borrow (tính từ items)
                    $borrow->recalculateTotals();

                    // Refresh relationship để đảm bảo items được load
                    $borrow->load('items');

                    // Nếu có inventory, cập nhật trạng thái tạm thời (sẽ được xác nhận khi admin duyệt)
                    // Không cập nhật ngay để admin có thể chọn lại inventory khi duyệt

                    DB::commit();

                    // Ghi log phục vụ audit
                    AuditService::logBorrow($borrow, "Borrow request for '{$book->ten_sach}' created by {$reader->ho_ten}");

                    Log::info('Borrow created successfully', [
                        'borrow_id' => $borrow->id,
                        'borrow_item_ids' => collect($borrowItems)->pluck('id')->toArray(),
                        'book_id' => $book->id,
                        'reader_id' => $reader->id,
                        'quantity' => $quantity,
                        'status' => $borrowItem->trang_thai
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => "Đã gửi yêu cầu mượn {$quantity} cuốn sách. Vui lòng chờ quản trị viên duyệt.",
                        'data' => [
                            'borrow_id' => $borrow->id,
                            'borrow_item_id' => $borrowItem->id,
                            'borrow_item_ids' => collect($borrowItems)->pluck('id')->toArray(),
                            'quantity' => $quantity,
                            'ngay_hen_tra' => $ngayHenTra
                        ]
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error creating borrow and borrow item', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'book_id' => $book->id ?? 'N/A',
                        'reader_id' => $reader->id
                    ]);
                    throw $e; // Re-throw để catch block bên ngoài xử lý
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique constraint violation hoặc lỗi database
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            Log::error('Database error creating borrow/reservation:', [
                'code' => $errorCode,
                'message' => $errorMessage,
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? []
            ]);
            
            // Kiểm tra xem có phải lỗi enum không (chỉ khi thực sự là lỗi enum)
            // Lưu ý: Chỉ bắt lỗi enum khi thực sự có lỗi enum, không phải mọi lỗi có chứa "trang_thai"
            $isEnumError = (
                strpos($errorMessage, 'Data truncated') !== false && strpos($errorMessage, 'trang_thai') !== false
            ) || (
                strpos($errorMessage, 'Invalid enum value') !== false
            ) || (
                strpos($errorMessage, 'trang_thai') !== false && 
                (strpos($errorMessage, 'enum') !== false || strpos($errorMessage, 'ENUM') !== false)
            );
            
            if ($isEnumError) {
                // Kiểm tra lại enum trong database
                try {
                    $enumResult = DB::select("SHOW COLUMNS FROM borrow_items WHERE Field = 'trang_thai'");
                    $enumType = !empty($enumResult) ? $enumResult[0]->Type : 'N/A';
                    
                    Log::error('Enum error detected', [
                        'database_enum' => $enumType,
                        'trying_to_insert' => 'Cho duyet',
                        'full_error' => $errorMessage,
                        'sql' => $e->getSql() ?? 'N/A',
                        'bindings' => $e->getBindings() ?? []
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi trạng thái: Enum trong database không khớp. Enum hiện tại: ' . $enumType . '. Vui lòng liên hệ quản trị viên.',
                        'error' => $errorMessage,
                        'debug' => config('app.debug') ? [
                            'enum_in_db' => $enumType,
                            'trying_to_insert' => 'Cho duyet'
                        ] : null
                    ], 400);
                } catch (\Exception $checkException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi trạng thái: ' . $errorMessage,
                        'error' => $errorMessage
                    ], 400);
                }
            }
            
            // Kiểm tra duplicate
            if ($errorCode == 23000 || strpos($errorMessage, 'Duplicate') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã gửi yêu cầu cho sách này trước đó. Vui lòng chờ duyệt.'
                ], 400);
            }
            
            // Trả về lỗi chi tiết hơn cho các lỗi database khác
            Log::error('Database error (non-enum)', [
                'code' => $errorCode,
                'message' => $errorMessage,
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? []
            ]);
            
            $userMessage = config('app.debug') 
                ? 'Lỗi database: ' . $errorMessage
                : 'Có lỗi xảy ra khi lưu dữ liệu. Vui lòng thử lại hoặc liên hệ quản trị viên.';
            
            return response()->json([
                'success' => false,
                'message' => $userMessage,
                'error' => config('app.debug') ? $errorMessage : 'Database error',
                'debug' => config('app.debug') ? [
                    'code' => $errorCode,
                    'sql' => $e->getSql() ?? null,
                    'bindings' => $e->getBindings() ?? []
                ] : null
            ], 400);
        } catch (\Exception $e) {
            Log::error('Create borrow/reservation error:', [
                'message' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'class' => get_class($e)
            ]);
            
            $userMessage = 'Có lỗi xảy ra khi gửi yêu cầu. ';
            if (config('app.debug')) {
                $userMessage .= $e->getMessage();
            } else {
                $userMessage .= 'Vui lòng thử lại hoặc liên hệ quản trị viên.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $userMessage,
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Xử lý mượn nhiều quyển sách với số ngày và km khác nhau
     */
    private function borrowMultipleItemsWithDifferentParams(Request $request, $reader)
    {
        $items = $request->items;
        $note = $request->input('note', '');
        
        // Lấy book_id đầu tiên để validate
        $firstBookId = $items[0]['book_id'];
        $book = Book::findOrFail($firstBookId);
        
        // Kiểm tra tất cả items phải cùng một cuốn sách
        foreach ($items as $item) {
            if ($item['book_id'] != $firstBookId) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Hiện tại chỉ hỗ trợ mượn nhiều bản của cùng một cuốn sách.'
                ], 400);
            }
        }
        
        $quantity = count($items);
        
        // Kiểm tra số lượng có sẵn
        $availableCopies = Inventory::where('book_id', $book->id)
            ->where('status', 'Co san')
            ->count();
        
        if ($quantity > $availableCopies) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Chỉ còn {$availableCopies} cuốn sách có sẵn. Vui lòng chọn lại số lượng."
            ], 400);
        }
        
        // Lấy inventories
        $availableInventories = Inventory::where('book_id', $book->id)
            ->where('status', 'Co san')
            ->limit($quantity)
            ->get();
        
        if ($availableInventories->count() < $quantity) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Chỉ còn {$availableInventories->count()} cuốn sách có sẵn. Vui lòng chọn lại số lượng."
            ], 400);
        }
        
        // Parse địa chỉ từ reader
        $diaChi = $reader->dia_chi ?? '';
        $parts = explode(',', $diaChi);
        
        $ngayMuon = now()->toDateString();
        $hasCard = true;
        
        // Tính tổng phí cho tất cả items
        $totalTienCoc = 0;
        $totalTienThue = 0;
        $totalTienShip = 0;
        
        // Tính phí cho từng item
        $itemsDetails = [];
        foreach ($items as $index => $itemData) {
            $borrowDays = $itemData['borrow_days'];
            $distance = floatval($itemData['distance'] ?? 0);
            $ngayHenTra = now()->addDays($borrowDays)->toDateString();
            
            $inventory = $availableInventories[$index];
            
            // Tính phí thuê và cọc
            $fees = \App\Services\PricingService::calculateFees(
                $book,
                $inventory,
                $ngayMuon,
                $ngayHenTra,
                $hasCard
            );
            
            // Tính phí ship (chỉ tính cho item đầu tiên hoặc item có distance > 5)
            $tienShip = 0;
            if ($distance > 5) {
                $extraKm = $distance - 5;
                $tienShip = (int) ($extraKm * 5000);
            }
            
            $totalTienCoc += $fees['tien_coc'];
            $totalTienThue += $fees['tien_thue'];
            $totalTienShip += $tienShip;
            
            $itemsDetails[] = [
                'inventory' => $inventory,
                'borrow_days' => $borrowDays,
                'distance' => $distance,
                'ngay_hen_tra' => $ngayHenTra,
                'tien_coc' => $fees['tien_coc'],
                'tien_thue' => $fees['tien_thue'],
                'tien_ship' => $tienShip
            ];
        }
        
        // Tạo phiếu mượn
        $borrow = Borrow::create([
            'reader_id' => $reader->id,
            'ten_nguoi_muon' => $reader->ho_ten,
            'so_dien_thoai' => $reader->so_dien_thoai ?? '',
            'tinh_thanh' => count($parts) > 2 ? trim($parts[count($parts) - 1]) : '',
            'huyen' => count($parts) > 1 ? trim($parts[count($parts) - 2]) : '',
            'xa' => count($parts) > 0 ? trim($parts[0]) : '',
            'so_nha' => '',
            'ngay_muon' => $ngayMuon,
            'trang_thai' => 'Cho duyet',
            'tien_coc' => $totalTienCoc,
            'tien_thue' => $totalTienThue,
            'tien_ship' => $totalTienShip,
            'tong_tien' => $totalTienCoc + $totalTienThue + $totalTienShip,
            'ghi_chu' => trim($note . (empty($note) ? '' : ' ') . "(Yêu cầu mượn $quantity cuốn với thông số khác nhau)"),
        ]);
        
        // Tạo BorrowItems cho từng item với thông số riêng
        $borrowItems = [];
        foreach ($itemsDetails as $index => $itemDetail) {
            $borrowItem = \App\Models\BorrowItem::create([
                'borrow_id' => $borrow->id,
                'book_id' => $book->id,
                'inventorie_id' => $itemDetail['inventory']->id,
                'ngay_muon' => $ngayMuon,
                'ngay_hen_tra' => $itemDetail['ngay_hen_tra'],
                'trang_thai' => 'Cho duyet',
                'tien_coc' => $itemDetail['tien_coc'],
                'tien_thue' => $itemDetail['tien_thue'],
                'tien_ship' => $itemDetail['tien_ship'],
                'ghi_chu' => trim($note . (empty($note) ? '' : ' ') . 
                    "(Quyển " . ($index + 1) . " - {$itemDetail['borrow_days']} ngày" . 
                    ($itemDetail['distance'] > 0 ? " - {$itemDetail['distance']}km" : '') . ")"),
            ]);
            
            $borrowItems[] = $borrowItem;
        }
        
        // Cập nhật tổng tiền của Borrow
        $borrow->recalculateTotals();
        $borrow->load('items');
        
        DB::commit();
        
        // Ghi log
        AuditService::logBorrow($borrow, "Borrow request for '{$book->ten_sach}' ({$quantity} copies with different params) created by {$reader->ho_ten}");
        
        Log::info('Multiple items borrow created successfully', [
            'borrow_id' => $borrow->id,
            'borrow_item_ids' => collect($borrowItems)->pluck('id')->toArray(),
            'book_id' => $book->id,
            'reader_id' => $reader->id,
            'quantity' => $quantity
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Đã gửi yêu cầu mượn {$quantity} cuốn sách với thông số khác nhau. Vui lòng chờ quản trị viên duyệt.",
            'data' => [
                'borrow_id' => $borrow->id,
                'borrow_item_ids' => collect($borrowItems)->pluck('id')->toArray(),
                'quantity' => $quantity
            ]
        ]);
    }

    /**
     * Hiển thị trang chính sách giá
     */
    public function pricingPolicy()
    {
        $pricingConfig = config('pricing');
        
        return view('pricing-policy', [
            'pricing' => $pricingConfig
        ]);
    }

    /**
     * Trang hướng dẫn mượn/trả sách cho khách
     */
    public function borrowReturnGuide()
    {
        return view('huong-dan-muon-tra');
    }

}
