<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\InventoryReceipt;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Services\CacheService;
use App\Services\FileUploadService;
use App\Http\Requests\BookRequest;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request)
    {
        // Thêm cache-control headers để ngăn browser cache dữ liệu cũ
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $query = Book::with('category');

        // Lọc theo thể loại (nếu có)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Tìm kiếm theo tên sách hoặc tác giả
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('ten_sach', 'like', "%{$keyword}%")
                  ->orWhere('tac_gia', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo sách có/không có trong kho
        if ($request->filled('inventory_status')) {
            $bookIdsInInventory = Inventory::select('book_id')->distinct()->pluck('book_id')->toArray();
            
            if ($request->inventory_status === 'has_inventory') {
                // Chỉ hiển thị sách có trong kho
                $query->whereIn('id', $bookIdsInInventory);
            } elseif ($request->inventory_status === 'no_inventory') {
                // Chỉ hiển thị sách không có trong kho
                if (!empty($bookIdsInInventory)) {
                    $query->whereNotIn('id', $bookIdsInInventory);
                }
            }
        }

        // Lấy danh sách sách sau khi lọc với phân trang
        $books = $query->orderBy('id', 'asc')->paginate(10);

        // Lấy danh sách thể loại để hiển thị dropdown (cached)
        $categories = CacheService::getCategories();
        
        // Tính toán thống kê
        $totalBooks = Book::count();
        $bookIdsInInventory = Inventory::select('book_id')->distinct()->pluck('book_id')->toArray();
        $booksWithInventory = count($bookIdsInInventory);
        $booksWithoutInventory = $totalBooks - $booksWithInventory;

        return view('admin.books.index', compact('books', 'categories', 'totalBooks', 'booksWithInventory', 'booksWithoutInventory'));
    }

    public function show($id)
    {
        $book = Book::with([
            'category',
            'reviews.user',
            'reviews.comments.user',
            'inventories',
            'favorites.user'
        ])->findOrFail($id);

        // Lấy sách liên quan (cùng thể loại)
        $relatedBooks = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->with('category')
            ->limit(4)
            ->get();

        // Lấy lịch sử mượn (BorrowItem) của cuốn sách này
        $borrowItems = \App\Models\BorrowItem::where('book_id', $book->id)
            ->with([
                'borrow.reader',
                'borrow.librarian',
                'inventory'
            ])
            ->orderBy('ngay_muon', 'desc')
            ->get();

        // Thống kê sách
        $stats = [
            'total_reviews' => $book->reviews()->count(),
            'average_rating' => $book->reviews()->avg('rating') ?? 0,
            'total_favorites' => $book->favorites()->count(),
            'total_copies' => $book->inventories()->count(),
            'available_copies' => $book->inventories()->where('status', 'Co san')->count(),
            'borrowed_copies' => $book->inventories()->where('status', 'Dang muon')->count(),
            'total_borrows' => $borrowItems->count(),
        ];

        // Kiểm tra user hiện tại có yêu thích sách này không
        $isFavorited = false;
        if (auth()->check()) {
            $isFavorited = $book->favorites()->where('user_id', auth()->id())->exists();
        }

        // Kiểm tra user hiện tại có đánh giá sách này không
        $userReview = null;
        if (auth()->check()) {
            $userReview = $book->reviews()->where('user_id', auth()->id())->first();
        }

        return view('admin.books.show', compact('book', 'relatedBooks', 'stats', 'isFavorited', 'userReview', 'borrowItems'));
    }

    public function create()
    {
        $categories = CacheService::getActiveCategories();
        $publishers = \App\Models\Publisher::where('trang_thai', 'active')->orderBy('ten_nha_xuat_ban')->get();
        return view('admin.books.create', compact('categories', 'publishers'));
    }

    public function store(BookRequest $request)
    {
        // Validation đã được xử lý trong BookRequest

        $path = null;
        if ($request->hasFile('hinh_anh')) {
            try {
                $result = FileUploadService::uploadImage(
                    $request->file('hinh_anh'),
                    'books',
                    [
                        'max_size' => 2048, // 2MB
                        'resize' => true,
                        'width' => 800,
                        'height' => 800,
                    ]
                );
                $path = $result['path'];
            } catch (\Exception $e) {
                \Log::error('Upload error:', ['message' => $e->getMessage()]);
                return redirect()->back()
                    ->withErrors(['hinh_anh' => $e->getMessage()])
                    ->withInput();
            }
        }

        try {
            $book = Book::create([
                'ten_sach' => $request->ten_sach,
                'category_id' => $request->category_id,
                'nha_xuat_ban_id' => $request->nha_xuat_ban_id,
                'tac_gia' => $request->tac_gia,
                'nam_xuat_ban' => $request->nam_xuat_ban,
                'hinh_anh' => $path,
                'mo_ta' => $request->mo_ta,
                'gia' => $request->gia,
                'trang_thai' => $request->trang_thai,
                'danh_gia_trung_binh' => 0,
                'so_luong_ban' => 0,
                'so_luot_xem' => 0,
            ]);

            // Log book creation
            AuditService::logCreated($book, "Book '{$book->ten_sach}' created");
            
            // Clear dashboard cache
            CacheService::clearDashboard();

            return redirect()->route('admin.books.index')->with('success', 'Thêm sách thành công!');
        } catch (\Exception $e) {
            \Log::error('Create Book error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Lỗi khi tạo sách: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $book = Book::findOrFail($id);
        $categories = CacheService::getActiveCategories();
        $publishers = \App\Models\Publisher::where('trang_thai', 'active')->orderBy('ten_nha_xuat_ban')->get();
        return view('admin.books.edit', compact('book', 'categories', 'publishers'));
    }

    public function update(Request $request, $id)
    {
        try {
            $book = Book::findOrFail($id);

            // Store old values before update for audit log
            $oldValues = $book->getAttributes();

            $request->validate([
                'ten_sach' => 'required|max:255',
                'category_id' => 'required',
                'tac_gia' => 'required',
                'nam_xuat_ban' => 'required|digits:4',
                'hinh_anh' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
                'gia' => 'nullable|numeric|min:0',
                'trang_thai' => 'required|in:active,inactive',
                'nha_xuat_ban_id' => 'nullable|exists:publishers,id',
                'so_luong_them' => 'nullable|integer|min:0',
            ]);

            // Giữ nguyên ảnh cũ nếu không upload ảnh mới
            $path = $book->hinh_anh;
            $oldImagePath = $book->hinh_anh; // Lưu path cũ để xóa SAU KHI commit thành công
            $newImagePath = null;
            
            if ($request->hasFile('hinh_anh')) {
                try {
                    // Upload ảnh mới sử dụng FileUploadService
                    // KHÔNG xóa ảnh cũ ngay - chỉ xóa SAU KHI transaction commit thành công
                    $result = FileUploadService::uploadImage(
                        $request->file('hinh_anh'),
                        'books',
                        [
                            'max_size' => 2048, // 2MB
                            'resize' => true,
                            'width' => 800,
                            'height' => 800,
                        ]
                    );
                    
                    // Đảm bảo path không rỗng
                    if (empty($result['path'])) {
                        throw new \Exception('Không thể lấy đường dẫn ảnh sau khi upload.');
                    }
                    
                    $newImagePath = $result['path'];
                    $path = $newImagePath;
                    
                    // Verify file exists after upload
                    if (!\Storage::disk('public')->exists($path)) {
                        throw new \Exception('File ảnh không tồn tại sau khi upload.');
                    }
                    
                    // Log for debugging
                    \Log::info('Image uploaded successfully', [
                        'path' => $path,
                        'filename' => $result['filename'] ?? 'unknown',
                        'url' => $result['url'] ?? 'unknown',
                        'book_id' => $book->id,
                        'file_exists' => \Storage::disk('public')->exists($path),
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Upload error:', [
                        'message' => $e->getMessage(),
                        'book_id' => $book->id,
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return redirect()->back()
                        ->withErrors(['hinh_anh' => 'Lỗi khi upload ảnh: ' . $e->getMessage()])
                        ->withInput();
                }
            }
            
            // Log path before saving
            \Log::info('Preparing to save book update', [
                'book_id' => $book->id,
                'has_new_image' => $request->hasFile('hinh_anh'),
                'old_image_path' => $oldImagePath,
                'new_image_path' => $newImagePath,
                'hinh_anh_path' => $path,
                'path_exists' => $path ? \Storage::disk('public')->exists($path) : false,
            ]);

            $updateData = [
                'ten_sach' => $request->ten_sach,
                'category_id' => $request->category_id,
                'tac_gia' => $request->tac_gia,
                'nam_xuat_ban' => $request->nam_xuat_ban,
                'hinh_anh' => $path, // Ensure path is saved - can be old path or new path
                'mo_ta' => $request->mo_ta,
                'gia' => $request->gia,
                'trang_thai' => $request->trang_thai,
                // KHÔNG CẬP NHẬT so_luong từ form edit
                // Số lượng chỉ được cập nhật thông qua việc duyệt phiếu nhập kho
            ];

            // Thêm nha_xuat_ban_id nếu có trong request
            if ($request->filled('nha_xuat_ban_id')) {
                $updateData['nha_xuat_ban_id'] = $request->nha_xuat_ban_id;
            }

            // Lấy số lượng muốn thêm vào kho
            $soLuongThem = $request->so_luong_them ?? 0;

            DB::beginTransaction();
            try {
                // Log data before update for debugging
                \Log::info('About to update book', [
                    'book_id' => $book->id,
                    'update_data' => $updateData,
                    'old_hinh_anh' => $book->hinh_anh,
                    'new_hinh_anh' => $updateData['hinh_anh'] ?? null,
                ]);
                
                // Cập nhật thông tin sách (KHÔNG BAO GỒM số lượng)
                // Force update updated_at to ensure timestamp changes for cache busting
                $updateData['updated_at'] = now();
                $book->update($updateData);
                
                // Verify update immediately
                \Log::info('After update, before refresh', [
                    'book_id' => $book->id,
                    'hinh_anh_after_update' => $book->hinh_anh,
                    'getChanges' => $book->getChanges(),
                    'getDirty' => $book->getDirty(),
                ]);
                
                // Log update BEFORE refresh - to capture changes properly
                // Note: Must be done before any other operations that might fail
                AuditService::logUpdated($book, $oldValues, "Book '{$book->ten_sach}' updated");

                // Nếu có số lượng muốn thêm, tạo phiếu nhập kho (chờ duyệt)
                // Số lượng sách sẽ KHÔNG được cập nhật ngay, chỉ khi duyệt phiếu
                if ($soLuongThem > 0) {
                    // Tạo số phiếu
                    $receiptNumber = InventoryReceipt::generateReceiptNumber();

                    // Lấy vị trí mặc định hoặc vị trí đầu tiên có sẵn
                    $defaultLocation = 'Kệ 1, Tầng 1, Vị trí 1';
                    $existingLocation = Inventory::where('book_id', $book->id)
                        ->where('storage_type', 'Kho')
                        ->first();
                    
                    if ($existingLocation) {
                        $defaultLocation = $existingLocation->location;
                    }

                    // Tạo phiếu nhập kho (chờ duyệt)
                    $receipt = InventoryReceipt::create([
                        'receipt_number' => $receiptNumber,
                        'receipt_date' => now()->toDateString(),
                        'book_id' => $book->id,
                        'quantity' => $soLuongThem,
                        'storage_location' => $defaultLocation,
                        'storage_type' => 'Kho',
                        'unit_price' => $book->gia ?? 0,
                        'total_price' => ($book->gia ?? 0) * $soLuongThem,
                        'supplier' => 'Cập nhật từ quản lý sách',
                        'received_by' => Auth::id(),
                        'approved_by' => null, // Chưa được duyệt
                        'status' => 'pending', // Chờ duyệt
                        'notes' => "Phiếu nhập kho được tạo từ form chỉnh sửa sách. Số lượng: {$soLuongThem} cuốn. Vui lòng duyệt phiếu để sách được nhập vào kho và cập nhật số lượng.",
                    ]);
                }

                DB::commit();
                
                // SAU KHI commit thành công, xóa ảnh cũ nếu có ảnh mới
                if ($newImagePath && $oldImagePath && $oldImagePath !== $newImagePath) {
                    if (\Storage::disk('public')->exists($oldImagePath)) {
                        try {
                            FileUploadService::deleteFile($oldImagePath, 'public');
                            \Log::info('Old image deleted after successful update', [
                                'old_path' => $oldImagePath,
                                'new_path' => $newImagePath,
                                'book_id' => $book->id,
                            ]);
                        } catch (\Exception $e) {
                            // Log error nhưng không rollback vì database đã commit
                            \Log::warning('Failed to delete old image after update', [
                                'old_path' => $oldImagePath,
                                'error' => $e->getMessage(),
                                'book_id' => $book->id,
                            ]);
                        }
                    }
                }

                // Refresh book model to get latest data including updated image
                $book->refresh();
                
                // Log the updated book data for debugging - always log to help debug
                \Log::info('Book updated successfully', [
                    'book_id' => $book->id,
                    'hinh_anh' => $book->hinh_anh,
                    'had_new_image' => $request->hasFile('hinh_anh'),
                    'image_exists' => $book->hinh_anh ? \Storage::disk('public')->exists($book->hinh_anh) : false,
                    'image_url' => $book->hinh_anh ? \Storage::disk('public')->url($book->hinh_anh) : null,
                    'asset_url' => $book->hinh_anh ? asset('storage/' . ltrim(str_replace('\\', '/', $book->hinh_anh), '/')) : null,
                ]);
                
                // Clear dashboard cache
                CacheService::clearDashboard();

                // Tạo thông báo success
                $successMessage = 'Cập nhật sách thành công!';
                if ($soLuongThem > 0) {
                    $successMessage .= " Đã tạo phiếu nhập kho số lượng {$soLuongThem} cuốn (chờ duyệt). Vui lòng vào 'Phiếu nhập kho' để duyệt phiếu.";
                }

                // Redirect back to index page with current page if available
                $redirectUrl = route('admin.books.index');
                if ($request->has('redirect_page')) {
                    $redirectUrl .= '?page=' . $request->redirect_page;
                }
                // Thêm book_id để highlight sách vừa cập nhật
                return redirect($redirectUrl)
                    ->with('success', $successMessage)
                    ->with('updated_book_id', $book->id);
            } catch (\Exception $e) {
                DB::rollBack();
                
                // Nếu có ảnh mới được upload nhưng transaction thất bại, xóa ảnh mới
                if ($newImagePath && \Storage::disk('public')->exists($newImagePath)) {
                    try {
                        FileUploadService::deleteFile($newImagePath, 'public');
                        \Log::info('New image deleted after transaction rollback', [
                            'new_path' => $newImagePath,
                            'book_id' => $book->id,
                        ]);
                    } catch (\Exception $deleteException) {
                        \Log::error('Failed to delete new image after rollback', [
                            'new_path' => $newImagePath,
                            'error' => $deleteException->getMessage(),
                            'book_id' => $book->id,
                        ]);
                    }
                }
                
                \Log::error('Update Book error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return redirect()->back()
                    ->with('error', 'Có lỗi xảy ra khi cập nhật sách: ' . $e->getMessage())
                    ->withInput();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Update Book error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Lỗi khi cập nhật sách: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function hide($id)
    {
        $book = Book::findOrFail($id);
        $book->update(['trang_thai' => 'inactive']);
        
        // Log book hiding
        AuditService::logUpdated($book, "Book '{$book->ten_sach}' hidden");
        
        return redirect()->route('admin.books.index')->with('success', 'Ẩn sách thành công!');
    }

    public function unhide($id)
    {
        $book = Book::findOrFail($id);
        $book->update(['trang_thai' => 'active']);
        
        // Log book unhiding
        AuditService::logUpdated($book, "Book '{$book->ten_sach}' unhidden");
        
        return redirect()->route('admin.books.index')->with('success', 'Hiển thị sách thành công!');
    }

    // Giữ lại method destroy để tương thích (nếu cần)
    public function destroy($id)
    {
        Book::destroy($id);
        return redirect()->route('admin.books.index')->with('success', 'Xóa sách thành công!');
    }

    /**
     * Xóa tất cả sách không còn trong kho
     */
    public function deleteBooksWithoutInventory()
    {
        try {
            DB::beginTransaction();

            // Lấy tất cả book_id có trong inventory
            $bookIdsInInventory = Inventory::select('book_id')
                ->distinct()
                ->pluck('book_id')
                ->toArray();

            // Tìm tất cả Book không có trong inventory
            $query = Book::query();
            if (!empty($bookIdsInInventory)) {
                $query->whereNotIn('id', $bookIdsInInventory);
            }
            
            $booksToDelete = $query->get();
            $count = $booksToDelete->count();

            if ($count == 0) {
                return back()->with('info', 'Không có sách nào cần xóa. Tất cả sách đều có trong kho.');
            }

            // Xóa từng sách và log
            $deletedTitles = [];
            foreach ($booksToDelete as $book) {
                $deletedTitles[] = $book->ten_sach;
                
                // Log trước khi xóa
                \Log::info("Xóa Book #{$book->id} ({$book->ten_sach}) vì không còn trong kho");
                
                // Xóa Book
                $book->delete();
            }

            DB::commit();

            $message = "Đã xóa thành công {$count} sách không còn trong kho!";
            if ($count <= 10) {
                $message .= "\n\nDanh sách sách đã xóa:\n" . implode("\n", $deletedTitles);
            }

            return redirect()->route('admin.books.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete books without inventory error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Có lỗi xảy ra khi xóa sách: ' . $e->getMessage());
        }
    }

    /**
     * Sắp xếp lại ID sách để liên tục từ 1
     */
    public function resetIds(Request $request)
    {
        try {
            // Chạy command để sắp xếp lại ID
            \Artisan::call('books:reset-ids', ['--force' => true]);
            
            $output = \Artisan::output();
            
            return redirect()->route('admin.books.index')
                ->with('success', 'Đã sắp xếp lại ID sách thành công! ID hiện tại liên tục từ 1.');
        } catch (\Exception $e) {
            \Log::error('Reset book IDs error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Có lỗi xảy ra khi sắp xếp lại ID: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint để lấy danh sách sách cho modal chọn sách
     */
    public function apiList(Request $request)
    {
        $query = Book::with(['category', 'publisher']);

        // Tìm kiếm theo tên sách hoặc tác giả
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('ten_sach', 'like', "%{$keyword}%")
                  ->orWhere('tac_gia', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo thể loại
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Lọc theo nhà xuất bản
        if ($request->filled('publisher_id')) {
            $query->where('nha_xuat_ban_id', $request->publisher_id);
        }

        $books = $query->orderBy('ten_sach')->get();

        return response()->json([
            'success' => true,
            'books' => $books
        ]);
    }
}
