<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryReceipt;
use App\Models\InventoryReceiptItem;
use App\Models\DisplayAllocation;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Models\User;
use App\Models\PurchasableBook;
use App\Exports\InventoryExport;
use App\Services\FileUploadService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Query để group theo book_id và đếm tổng số lượng
        $query = Inventory::select('book_id')
            ->selectRaw('COUNT(*) as total_quantity')
            ->selectRaw('MIN(id) as first_inventory_id')
            ->selectRaw('MAX(CASE WHEN receipt_id IS NOT NULL THEN created_at ELSE NULL END) as latest_receipt_date')
            ->selectRaw('MAX(created_at) as latest_created_at')
            ->with(['book']);

        // Lọc theo sách
        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo tình trạng
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Lọc theo vị trí
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Tìm kiếm theo mã vạch
        if ($request->filled('barcode')) {
            $query->where('barcode', 'like', "%{$request->barcode}%");
        }

        // Tìm kiếm theo tên sách
        if ($request->filled('book_title')) {
            $query->whereHas('book', function($bookQuery) use ($request) {
                $bookQuery->where('ten_sach', 'like', "%{$request->book_title}%");
            });
        }

        // Lọc theo loại lưu trữ (Kho hoặc Trưng bày)
        if ($request->filled('storage_type')) {
            $query->where('storage_type', $request->storage_type);
        }

        // Group theo book_id
        $query->groupBy('book_id');

        // Sắp xếp: sách từ phiếu nhập kho (có receipt_id) hiển thị trước, sau đó sắp xếp theo ngày tạo mới nhất
        $inventories = $query->orderByRaw('CASE WHEN latest_receipt_date IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('latest_receipt_date', 'desc')
            ->orderBy('latest_created_at', 'desc')
            ->paginate(20);
        
        // Tính tổng số quyển từ tất cả inventories (không chỉ trang hiện tại)
        $baseQuery = Inventory::query();
        
        // Áp dụng các filter tương tự
        if ($request->filled('book_id')) {
            $baseQuery->where('book_id', $request->book_id);
        }
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }
        if ($request->filled('condition')) {
            $baseQuery->where('condition', $request->condition);
        }
        if ($request->filled('location')) {
            $baseQuery->where('location', 'like', "%{$request->location}%");
        }
        if ($request->filled('barcode')) {
            $baseQuery->where('barcode', 'like', "%{$request->barcode}%");
        }
        if ($request->filled('book_title')) {
            $baseQuery->whereHas('book', function($bookQuery) use ($request) {
                $bookQuery->where('ten_sach', 'like', "%{$request->book_title}%");
            });
        }
        if ($request->filled('storage_type')) {
            $baseQuery->where('storage_type', $request->storage_type);
        }
        
        $totalQuantity = $baseQuery->count();
        
        // Transform items sau khi paginate
        $inventories->getCollection()->transform(function ($item) {
            // Ưu tiên lấy inventory từ phiếu nhập kho (có receipt_id) và mới nhất
            $firstInventory = Inventory::with(['book', 'creator', 'receipt'])
                ->where('book_id', $item->book_id)
                ->whereNotNull('receipt_id')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Nếu không có từ phiếu nhập kho, lấy inventory đầu tiên
            if (!$firstInventory) {
                $firstInventory = Inventory::with(['book', 'creator'])
                    ->where('book_id', $item->book_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
            
            // Đếm số lượng theo các tiêu chí
            $allInventories = Inventory::where('book_id', $item->book_id)->get();
            
            return (object) [
                'book_id' => $item->book_id,
                'book' => $firstInventory->book ?? null,
                'total_quantity' => $item->total_quantity,
                'first_inventory' => $firstInventory,
                'all_inventories' => $allInventories,
            ];
        });
        
        $books = Book::all();

        return view('admin.inventory.index', compact('inventories', 'books', 'totalQuantity'));
    }

    public function create()
    {
        $books = Book::all();
        $receiptNumber = InventoryReceipt::generateReceiptNumber();
        $categories = \App\Models\Category::all();
        $publishers = \App\Models\Publisher::all();
        
        // Lấy danh sách vị trí đã sử dụng kèm số lượng sách, phân loại theo storage_type
        $locationsInStock = Inventory::where('storage_type', 'Kho')
            ->select('location', DB::raw('COUNT(*) as book_count'))
            ->groupBy('location')
            ->orderBy('location')
            ->get()
            ->map(function($item) {
                return [
                    'location' => $item->location,
                    'count' => $item->book_count
                ];
            })
            ->toArray();
            
        $locationsOnDisplay = Inventory::where('storage_type', 'Trung bay')
            ->select('location', DB::raw('COUNT(*) as book_count'))
            ->groupBy('location')
            ->orderBy('location')
            ->get()
            ->map(function($item) {
                return [
                    'location' => $item->location,
                    'count' => $item->book_count
                ];
            })
            ->toArray();
        
        return view('admin.inventory.create', compact(
            'books', 
            'receiptNumber',
            'categories', 
            'publishers',
            'locationsInStock',
            'locationsOnDisplay'
        ));
    }

    public function store(Request $request)
    {
        $bookInputType = $request->book_input_type ?? 'existing';
        
        if ($bookInputType === 'new') {
            // Validate cho sách mới
            $request->validate([
                'receipt_date' => 'required|date',
                'ten_sach' => 'required|string|max:255',
                'tac_gia' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'quantity' => 'required|integer|min:1',
                'location' => 'required|string|max:100',
                'storage_type' => 'required|in:Kho,Trung bay',
                'unit_price' => 'nullable|numeric|min:0',
                'supplier' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:500',
                'nha_xuat_ban_id' => 'nullable|exists:publishers,id',
                'nam_xuat_ban' => 'nullable|integer|min:1900|max:' . date('Y'),
                'gia' => 'nullable|numeric|min:0',
                'mo_ta' => 'nullable|string',
            ]);
        } else {
            // Validate cho sách có sẵn
            $request->validate([
                'receipt_date' => 'required|date',
                'book_id' => 'required|exists:books,id',
                'quantity' => 'required|integer|min:1',
                'location' => 'required|string|max:100',
                'storage_type' => 'required|in:Kho,Trung bay',
                'unit_price' => 'nullable|numeric|min:0',
                'supplier' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:500',
            ]);
        }

        DB::beginTransaction();
        try {
            // Nếu là sách mới, tạo Book trước
            if ($bookInputType === 'new') {
                $book = Book::create([
                    'ten_sach' => $request->ten_sach,
                    'tac_gia' => $request->tac_gia,
                    'category_id' => $request->category_id,
                    'nha_xuat_ban_id' => $request->nha_xuat_ban_id,
                    'nam_xuat_ban' => $request->nam_xuat_ban ?? date('Y'),
                    'gia' => $request->gia ?? $request->unit_price ?? 0,
                    'mo_ta' => $request->mo_ta,
                    'trang_thai' => 'active',
                    'danh_gia_trung_binh' => 0,
                    'so_luong_ban' => 0,
                    'so_luot_xem' => 0,
                ]);
                $bookId = $book->id;
            } else {
                $bookId = $request->book_id;
            }

            // Tính tổng giá
            $unitPrice = $request->unit_price ?? 0;
            $totalPrice = $unitPrice * $request->quantity;

            // Tạo số phiếu
            $receiptNumber = InventoryReceipt::generateReceiptNumber();

            // Tạo phiếu nhập (chỉ tạo phiếu, không tạo Inventory items)
            // Inventory items sẽ được tạo khi duyệt phiếu
            $receipt = InventoryReceipt::create([
                'receipt_number' => $receiptNumber,
                'receipt_date' => $request->receipt_date,
                'book_id' => $bookId,
                'quantity' => $request->quantity,
                'storage_location' => $request->location,
                'storage_type' => $request->storage_type,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'supplier' => $request->supplier,
                'received_by' => Auth::id(),
                'status' => 'pending', // Cần phê duyệt để tạo Inventory items
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->route('admin.inventory.receipts')
                ->with('success', 'Phiếu nhập kho đã được tạo thành công! Vui lòng duyệt phiếu để hoàn tất nhập kho.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $inventory = Inventory::with(['book', 'creator', 'transactions.performer'])
            ->findOrFail($id);

        return view('admin.inventory.show', compact('inventory'));
    }

    public function showByBook($book_id)
    {
        $book = Book::findOrFail($book_id);
        
        $inventories = Inventory::with(['creator', 'transactions.performer', 'receipt'])
            ->where('book_id', $book_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Lấy tất cả transactions của tất cả inventory items
        $inventoryIds = $inventories->pluck('id');
        $allTransactions = InventoryTransaction::with(['performer', 'inventory'])
            ->whereIn('inventory_id', $inventoryIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // Tính số phiếu nhập duy nhất cho sách này
        $receiptIds = $inventories->pluck('receipt_id')->filter()->unique();
        $totalReceipts = $receiptIds->count();
        
        // Tính toán thống kê
        $stats = [
            'total' => $inventories->count(),
            'in_kho' => $inventories->where('storage_type', 'Kho')->count(),
            'on_display' => $inventories->where('storage_type', 'Trung bay')->count(),
            'available' => $inventories->where('status', 'Co san')->count(),
            'borrowed' => $inventories->where('status', 'Dang muon')->count(),
            'remaining' => $inventories->where('storage_type', 'Kho')->count() - $inventories->where('storage_type', 'Kho')->where('status', 'Dang muon')->count(),
            'damaged' => $inventories->filter(function($inv) {
                return $inv->status == 'Hong' || $inv->condition == 'Hong';
            })->count(),
            'total_value' => $inventories->sum('purchase_price'),
            'total_receipts' => $totalReceipts,
        ];

        // Lấy thông tin chi tiết từ quyển đầu tiên (đại diện)
        $firstInventory = $inventories->first();

        return view('admin.inventory.show-by-book', compact('book', 'inventories', 'stats', 'allTransactions', 'firstInventory'));
    }

    public function edit($id)
    {
        $inventory = Inventory::findOrFail($id);
        $books = Book::all();

        return view('admin.inventory.edit', compact('inventory', 'books'));
    }

    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'location' => 'required|string|max:100',
            'storage_type' => 'required|in:Kho,Trung bay',
            'condition' => 'required|in:Moi,Tot,Trung binh,Cu,Hong',
            'status' => 'required|in:Co san,Dang muon,Mat,Hong,Thanh ly',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'hinh_anh' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $oldLocation = $inventory->location;
        $oldStorageType = $inventory->storage_type;
        $oldCondition = $inventory->condition;
        $oldStatus = $inventory->status;

        // Xử lý upload ảnh
        $imagePath = $inventory->hinh_anh; // Giữ ảnh cũ nếu không upload ảnh mới
        
        if ($request->hasFile('hinh_anh')) {
            try {
                // Xóa ảnh cũ nếu có
                if ($inventory->hinh_anh && Storage::disk('public')->exists($inventory->hinh_anh)) {
                    FileUploadService::deleteFile($inventory->hinh_anh, 'public');
                }
                
                // Upload ảnh mới - đảm bảo directory không rỗng
                $result = FileUploadService::uploadImage(
                    $request->file('hinh_anh'),
                    'inventory', // Directory name - không được rỗng
                    [
                        'max_size' => 2048, // 2MB
                        'resize' => true,
                        'width' => 800,
                        'height' => 800,
                        'disk' => 'public',
                    ]
                );
                $imagePath = $result['path'];
            } catch (\Exception $e) {
                \Log::error('Upload error:', ['message' => $e->getMessage()]);
                return redirect()->back()
                    ->withErrors(['hinh_anh' => $e->getMessage()])
                    ->withInput();
            }
        }

        $inventory->update([
            'location' => $request->location,
            'storage_type' => $request->storage_type,
            'condition' => $request->condition,
            'status' => $request->status,
            'purchase_price' => $request->purchase_price,
            'purchase_date' => $request->purchase_date,
            'notes' => $request->notes,
            'hinh_anh' => $imagePath,
        ]);

        // Tạo transaction nếu có thay đổi
        if ($oldLocation !== $request->location || 
            $oldStorageType !== $request->storage_type ||
            $oldCondition !== $request->condition || 
            $oldStatus !== $request->status) {
            
            InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'type' => 'Kiem ke',
                'quantity' => 1,
                'from_location' => $oldLocation,
                'to_location' => $request->location,
                'condition_before' => $oldCondition,
                'condition_after' => $request->condition,
                'status_before' => $oldStatus,
                'status_after' => $request->status,
                'reason' => 'Cập nhật thông tin',
                'notes' => $request->notes,
                'performed_by' => Auth::id(),
            ]);
        }

        return redirect()->route('admin.inventory.show', $inventory->id)
            ->with('success', 'Thông tin sách đã được cập nhật thành công!');
    }

    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);
        
        // Không cho phép xóa sách đã bán (status = 'Thanh ly')
        if ($inventory->status === 'Thanh ly') {
            return back()->with('error', 'Không thể xóa sách đã bán! Dữ liệu sách đã bán cần được giữ lại để theo dõi lịch sử bán hàng.');
        }
        
        // Lưu book_id để kiểm tra sau khi xóa Inventory
        $bookId = $inventory->book_id;
        $bookTitle = $inventory->book->ten_sach ?? 'N/A';

        // Tạo transaction thanh lý
        InventoryTransaction::create([
            'inventory_id' => $inventory->id,
            'type' => 'Thanh ly',
            'quantity' => 1,
            'from_location' => $inventory->location,
            'condition_before' => $inventory->condition,
            'status_before' => $inventory->status,
            'status_after' => 'Thanh ly',
            'reason' => 'Xóa khỏi hệ thống',
            'performed_by' => Auth::id(),
        ]);

        // Xóa Inventory
        $inventory->delete();
        
        // Kiểm tra xem Book còn Inventory nào không (không tính sách đã bán)
        $remainingInventories = Inventory::where('book_id', $bookId)
            ->where('status', '!=', 'Thanh ly')
            ->count();
        
        if ($remainingInventories == 0) {
            // Kiểm tra xem còn sách đã bán không
            $soldInventories = Inventory::where('book_id', $bookId)
                ->where('status', 'Thanh ly')
                ->count();
            
            if ($soldInventories == 0) {
                // Nếu không còn Inventory nào (kể cả sách đã bán), xóa Book khỏi quản lý sách
                $book = Book::find($bookId);
                if ($book) {
                    // Log trước khi xóa
                    \Log::info("Xóa Book #{$bookId} ({$bookTitle}) vì không còn Inventory nào");
                    
                    // Xóa Book
                    $book->delete();
                    
                    return back()->with('success', 'Sách đã được xóa khỏi kho và quản lý sách thành công!');
                }
            } else {
                // Còn sách đã bán, không xóa Book
                return back()->with('success', 'Sách đã được xóa khỏi kho thành công! (Dữ liệu sách đã bán vẫn được giữ lại)');
            }
        }

        return back()->with('success', 'Sách đã được xóa khỏi kho thành công!');
    }

    public function destroyByBook($book_id)
    {
        // Kiểm tra Book có tồn tại không
        $book = Book::findOrFail($book_id);
        $bookTitle = $book->ten_sach;
        
        // Chỉ lấy các inventory không phải sách đã bán
        $inventories = Inventory::where('book_id', $book_id)
            ->where('status', '!=', 'Thanh ly')
            ->get();
        
        // Đếm số lượng sách đã bán
        $soldInventories = Inventory::where('book_id', $book_id)
            ->where('status', 'Thanh ly')
            ->count();
        
        if ($inventories->isEmpty()) {
            if ($soldInventories > 0) {
                return back()->with('error', 'Không thể xóa! Sách này còn ' . $soldInventories . ' quyển đã bán. Dữ liệu sách đã bán cần được giữ lại để theo dõi lịch sử bán hàng.');
            }
            return back()->with('error', 'Không tìm thấy sách trong kho!');
        }

        $count = $inventories->count();

        // Tạo transaction thanh lý cho từng quyển sách (chỉ các sách chưa bán)
        foreach ($inventories as $inventory) {
            InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'type' => 'Thanh ly',
                'quantity' => 1,
                'from_location' => $inventory->location,
                'condition_before' => $inventory->condition,
                'status_before' => $inventory->status,
                'status_after' => 'Thanh ly',
                'reason' => 'Xóa tất cả quyển sách khỏi hệ thống',
                'performed_by' => Auth::id(),
            ]);

            // Xóa Inventory
            $inventory->delete();
        }
        
        // Kiểm tra xem còn inventory nào không (kể cả sách đã bán)
        $allRemainingInventories = Inventory::where('book_id', $book_id)->count();
        
        if ($allRemainingInventories == 0) {
            // Nếu không còn Inventory nào (kể cả sách đã bán), xóa Book khỏi quản lý sách
            // Log trước khi xóa
            \Log::info("Xóa Book #{$book_id} ({$bookTitle}) vì đã xóa tất cả {$count} quyển sách khỏi kho");
            
            // Xóa Book
            $book->delete();

            return back()->with('success', "Đã xóa thành công {$count} quyển sách khỏi kho và xóa sách khỏi quản lý sách!");
        } else {
            // Còn sách đã bán, không xóa Book
            $message = "Đã xóa thành công {$count} quyển sách khỏi kho!";
            if ($soldInventories > 0) {
                $message .= " (Dữ liệu {$soldInventories} quyển sách đã bán vẫn được giữ lại)";
            }
            return back()->with('success', $message);
        }
    }

    public function transfer(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'to_location' => 'required|string|max:100',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldLocation = $inventory->location;

        $inventory->update([
            'location' => $request->to_location,
        ]);

        // Tạo transaction chuyển kho
        InventoryTransaction::create([
            'inventory_id' => $inventory->id,
            'type' => 'Chuyen kho',
            'quantity' => 1,
            'from_location' => $oldLocation,
            'to_location' => $request->to_location,
            'condition_before' => $inventory->condition,
            'condition_after' => $inventory->condition,
            'status_before' => $inventory->status,
            'status_after' => $inventory->status,
            'reason' => $request->reason,
            'notes' => $request->notes,
            'performed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Sách đã được chuyển kho thành công!');
    }

    public function repair(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'condition_after' => 'required|in:Moi,Tot,Trung binh,Cu,Hong',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldCondition = $inventory->condition;

        $inventory->update([
            'condition' => $request->condition_after,
        ]);

        // Tạo transaction sửa chữa
        InventoryTransaction::create([
            'inventory_id' => $inventory->id,
            'type' => 'Sua chua',
            'quantity' => 1,
            'condition_before' => $oldCondition,
            'condition_after' => $request->condition_after,
            'status_before' => $inventory->status,
            'status_after' => $inventory->status,
            'reason' => $request->reason,
            'notes' => $request->notes,
            'performed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Sách đã được sửa chữa thành công!');
    }

    public function transactions(Request $request)
    {
        $query = InventoryTransaction::with(['inventory.book', 'performer']);

        // Lọc theo loại giao dịch
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Lọc theo khoảng thời gian
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Lọc theo người thực hiện
        if ($request->filled('performer_id')) {
            $query->where('performed_by', $request->performer_id);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);
        $users = \App\Models\User::all();

        return view('admin.inventory.transactions', compact('transactions', 'users'));
    }

    public function dashboard()
    {
        $stats = [
            'total_books' => Inventory::count(),
            'books_in_stock' => Inventory::where('storage_type', 'Kho')->count(),
            'books_on_display' => Inventory::where('storage_type', 'Trung bay')->count(),
            'available_books' => Inventory::where('status', 'Co san')->count(),
            'available_in_stock' => Inventory::where('storage_type', 'Kho')->where('status', 'Co san')->count(),
            'available_on_display' => Inventory::where('storage_type', 'Trung bay')->where('status', 'Co san')->count(),
            'borrowed_books' => Inventory::where('status', 'Dang muon')->count(),
            'borrowed_from_stock' => Inventory::where('storage_type', 'Kho')->where('status', 'Dang muon')->count(),
            'borrowed_from_display' => Inventory::where('storage_type', 'Trung bay')->where('status', 'Dang muon')->count(),
            'damaged_books' => Inventory::where('condition', 'Hong')->count(),
            'lost_books' => Inventory::where('status', 'Mat')->count(),
            'recent_transactions' => InventoryTransaction::with(['inventory.book', 'performer'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'transactions_by_type' => InventoryTransaction::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get(),
            'books_by_storage_type' => Inventory::selectRaw('storage_type, COUNT(*) as count')
                ->groupBy('storage_type')
                ->get(),
        ];

        return view('admin.inventory.dashboard', compact('stats'));
    }

    public function scanBarcode(Request $request)
    {
        $barcode = $request->get('barcode');
        
        if (!$barcode) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mã vạch không được để trống'
            ], 400);
        }

        $inventory = Inventory::with(['book', 'creator'])
            ->where('barcode', $barcode)
            ->first();

        if (!$inventory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy sách với mã vạch này'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    /**
     * Hiển thị form nhập kho
     */
    public function createReceipt()
    {
        $books = Book::with(['category', 'publisher'])->get();
        $receiptNumber = InventoryReceipt::generateReceiptNumber();
        $categories = \App\Models\Category::all();
        $publishers = \App\Models\Publisher::all();
        
        // Lấy tất cả sách để hiển thị trong modal (không phân trang)
        // Bao gồm số lượng còn lại trong kho
        $allBooks = Book::with(['category', 'publisher'])
            ->select('books.*')
            ->selectRaw('COALESCE(SUM(CASE WHEN inventories.status = "Co san" THEN 1 ELSE 0 END), 0) as so_luong_con_lai')
            ->leftJoin('inventories', 'books.id', '=', 'inventories.book_id')
            ->groupBy('books.id')
            ->orderBy('ten_sach')
            ->get();
        
        // Lấy danh sách vị trí đã sử dụng kèm số lượng sách, phân loại theo storage_type
        $locationsInStock = Inventory::where('storage_type', 'Kho')
            ->select('location', DB::raw('COUNT(*) as book_count'))
            ->groupBy('location')
            ->orderBy('location')
            ->get()
            ->map(function($item) {
                return [
                    'location' => $item->location,
                    'count' => $item->book_count
                ];
            })
            ->toArray();
            
        $locationsOnDisplay = Inventory::where('storage_type', 'Trung bay')
            ->select('location', DB::raw('COUNT(*) as book_count'))
            ->groupBy('location')
            ->orderBy('location')
            ->get()
            ->map(function($item) {
                return [
                    'location' => $item->location,
                    'count' => $item->book_count
                ];
            })
            ->toArray();
        
        return view('admin.inventory.create-receipt', compact(
            'books', 
            'allBooks',
            'receiptNumber', 
            'categories', 
            'publishers',
            'locationsInStock',
            'locationsOnDisplay'
        ));
    }

    /**
     * Lưu phiếu nhập kho
     */
    public function storeReceipt(Request $request)
    {
        // Kiểm tra xem có phải là form mới (nhiều sách) hay form cũ (một sách)
        if ($request->has('books') && is_array($request->books)) {
            // Form mới: nhiều sách
            $request->validate([
                'receipt_date' => 'required|date',
                'books' => 'required|array|min:1',
                'books.*.book_id' => 'required|exists:books,id',
                'books.*.quantity' => 'required|integer|min:1',
                'books.*.storage_location' => 'required|string|max:100',
                'books.*.storage_type' => 'required|in:Kho,Trung bay',
                'books.*.unit_price' => 'nullable|numeric|min:0',
                'books.*.notes' => 'nullable|string|max:500',
                'supplier' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();
            try {
                // Tạo số phiếu
                $receiptNumber = InventoryReceipt::generateReceiptNumber();
                
                // Tạo phiếu nhập
                $receipt = InventoryReceipt::create([
                    'receipt_number' => $receiptNumber,
                    'receipt_date' => $request->receipt_date,
                    'book_id' => array_values($request->books)[0]['book_id'], // Giữ book_id đầu tiên để tương thích
                    'quantity' => array_sum(array_column($request->books, 'quantity')), // Tổng số lượng
                    'storage_location' => array_values($request->books)[0]['storage_location'], // Vị trí đầu tiên
                    'storage_type' => array_values($request->books)[0]['storage_type'], // Loại đầu tiên
                    'unit_price' => 0, // Sẽ tính từ items
                    'total_price' => 0, // Sẽ tính từ items
                    'supplier' => $request->supplier,
                    'received_by' => Auth::id(),
                    'status' => 'pending',
                    'notes' => $request->notes,
                ]);

                // Tạo receipt items cho từng sách
                $totalPrice = 0;
                foreach ($request->books as $bookData) {
                    $unitPrice = $bookData['unit_price'] ?? 0;
                    $quantity = $bookData['quantity'];
                    $itemTotalPrice = $unitPrice * $quantity;
                    $totalPrice += $itemTotalPrice;

                    InventoryReceiptItem::create([
                        'receipt_id' => $receipt->id,
                        'book_id' => $bookData['book_id'],
                        'quantity' => $quantity,
                        'storage_location' => $bookData['storage_location'],
                        'storage_type' => $bookData['storage_type'],
                        'unit_price' => $unitPrice,
                        'total_price' => $itemTotalPrice,
                        'notes' => $bookData['notes'] ?? null,
                    ]);
                }

                // Cập nhật tổng giá cho receipt
                $receipt->update([
                    'total_price' => $totalPrice
                ]);

                DB::commit();

                // Sau khi tạo phiếu nhập thành công, chuyển sang trang chi tiết để kiểm tra
                return redirect()->route('admin.inventory.receipts.show', $receipt->id)
                    ->with('success', 'Đã tạo phiếu nhập kho với ' . count($request->books) . ' loại sách. Vui lòng chờ Admin phê duyệt để nhập kho.');
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Store receipt error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        } else {
            // Form cũ: một sách (để tương thích với code cũ)
            $bookInputType = $request->book_input_type ?? 'existing';
            
            if ($bookInputType === 'new') {
                $request->validate([
                    'receipt_date' => 'required|date',
                    'ten_sach' => 'required|string|max:255',
                    'tac_gia' => 'required|string|max:255',
                    'category_id' => 'required|exists:categories,id',
                    'quantity' => 'required|integer|min:1',
                    'storage_location' => 'required|string|max:100',
                    'storage_type' => 'required|in:Kho,Trung bay',
                    'unit_price' => 'nullable|numeric|min:0',
                    'supplier' => 'nullable|string|max:255',
                    'notes' => 'nullable|string|max:500',
                    'nha_xuat_ban_id' => 'nullable|exists:publishers,id',
                    'nam_xuat_ban' => 'nullable|integer|min:1900|max:' . date('Y'),
                    'gia' => 'nullable|numeric|min:0',
                    'mo_ta' => 'nullable|string',
                ]);
            } else {
                $request->validate([
                    'receipt_date' => 'required|date',
                    'book_id' => 'required|exists:books,id',
                    'quantity' => 'required|integer|min:1',
                    'storage_location' => 'required|string|max:100',
                    'storage_type' => 'required|in:Kho,Trung bay',
                    'unit_price' => 'nullable|numeric|min:0',
                    'supplier' => 'nullable|string|max:255',
                    'notes' => 'nullable|string|max:500',
                ]);
            }

            DB::beginTransaction();
            try {
                // Nếu là sách mới, tạo Book trước
                if ($bookInputType === 'new') {
                    $book = Book::create([
                        'ten_sach' => $request->ten_sach,
                        'tac_gia' => $request->tac_gia,
                        'category_id' => $request->category_id,
                        'nha_xuat_ban_id' => $request->nha_xuat_ban_id,
                        'nam_xuat_ban' => $request->nam_xuat_ban ?? date('Y'),
                        'gia' => $request->gia ?? $request->unit_price ?? 0,
                        'mo_ta' => $request->mo_ta,
                        'trang_thai' => 'active',
                        'danh_gia_trung_binh' => 0,
                        'so_luong_ban' => 0,
                        'so_luot_xem' => 0,
                    ]);
                    $bookId = $book->id;
                } else {
                    $bookId = $request->book_id;
                }

                // Tính tổng giá
                $unitPrice = $request->unit_price ?? 0;
                $totalPrice = $unitPrice * $request->quantity;

                // Tạo số phiếu
                $receiptNumber = InventoryReceipt::generateReceiptNumber();

                // Tạo phiếu nhập
                $receipt = InventoryReceipt::create([
                    'receipt_number' => $receiptNumber,
                    'receipt_date' => $request->receipt_date,
                    'book_id' => $bookId,
                    'quantity' => $request->quantity,
                    'storage_location' => $request->storage_location,
                    'storage_type' => $request->storage_type,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'supplier' => $request->supplier,
                    'received_by' => Auth::id(),
                    'status' => 'pending',
                    'notes' => $request->notes,
                ]);

                // Tạo receipt item để tương thích với hệ thống mới
                InventoryReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'book_id' => $bookId,
                    'quantity' => $request->quantity,
                    'storage_location' => $request->storage_location,
                    'storage_type' => $request->storage_type,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'notes' => $request->notes,
                ]);

                DB::commit();

                // Sau khi tạo phiếu nhập thành công, chuyển sang trang chi tiết
                return redirect()->route('admin.inventory.receipts.show', $receipt->id)
                    ->with('success', 'Phiếu nhập kho đã được tạo và đã cập nhật vào kho!');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        }
    }

    /**
     * Danh sách phiếu nhập kho
     */
    public function receipts(Request $request)
    {
        $query = InventoryReceipt::with(['book', 'receiver', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->where('receipt_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('receipt_date', '<=', $request->to_date);
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        $receipts = $query->orderBy('created_at', 'desc')->paginate(20);
        $books = Book::all();

        return view('admin.inventory.receipts', compact('receipts', 'books'));
    }

    /**
     * Chi tiết phiếu nhập kho
     */
    public function showReceipt($id)
    {
        $receipt = InventoryReceipt::with(['book', 'receiver', 'approver', 'inventories', 'items.book'])
            ->findOrFail($id);

        return view('admin.inventory.show-receipt', compact('receipt'));
    }

    /**
     * Phê duyệt phiếu nhập kho
     */
    public function approveReceipt($id)
    {
        $receipt = InventoryReceipt::with('items')->findOrFail($id);

        if ($receipt->status !== 'pending') {
            return back()->with('error', 'Phiếu nhập này đã được xử lý!');
        }

        DB::beginTransaction();
        try {
            $this->processReceiptInventories($receipt);

            DB::commit();

            return back()->with('success', 'Phiếu nhập kho đã được phê duyệt và sách đã được nhập vào kho thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Approve receipt error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Có lỗi xảy ra khi phê duyệt phiếu nhập kho: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý tạo Inventory + Transaction và cập nhật tồn kho cho một phiếu nhập
     */
    private function processReceiptInventories(InventoryReceipt $receipt)
    {
        // Nếu đã có inventory cho receipt này và trạng thái đã approved thì bỏ qua
        $existingInventories = Inventory::where('receipt_id', $receipt->id)->count();
        if ($existingInventories > 0 && $receipt->status === 'approved') {
            return;
        }

        // Cập nhật trạng thái phiếu nhập kho nếu chưa approved
        if ($receipt->status !== 'approved') {
            $receipt->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
            ]);
        }

        // Kiểm tra xem có receipt items không (form mới)
        if ($receipt->items()->count() > 0) {
            // Xử lý từng receipt item
            $processedBooks = [];
            
            foreach ($receipt->items as $item) {
                $existingInventoriesItem = Inventory::where('receipt_id', $receipt->id)
                    ->where('book_id', $item->book_id)
                    ->count();
                
                if ($existingInventoriesItem == 0) {
                    // Tạo Inventory items cho từng quyển sách
                    for ($i = 0; $i < $item->quantity; $i++) {
                        $baseNumber = Inventory::count() + 1;
                        $barcode = 'BK' . str_pad($baseNumber, 6, '0', STR_PAD_LEFT);
                        
                        // Đảm bảo mã vạch là unique
                        $counter = 0;
                        while (Inventory::where('barcode', $barcode)->exists() && $counter < 100) {
                            $baseNumber++;
                            $barcode = 'BK' . str_pad($baseNumber, 6, '0', STR_PAD_LEFT);
                            $counter++;
                        }
                        
                        $inventory = Inventory::create([
                            'book_id' => $item->book_id,
                            'barcode' => $barcode,
                            'location' => $item->storage_location,
                            'condition' => 'Moi',
                            'status' => 'Co san',
                            'purchase_price' => $item->unit_price ?? 0,
                            'purchase_date' => $receipt->receipt_date,
                            'storage_type' => $item->storage_type,
                            'receipt_id' => $receipt->id,
                            'created_by' => $receipt->received_by,
                        ]);

                        // Tạo transaction nhập kho
                        InventoryTransaction::create([
                            'inventory_id' => $inventory->id,
                            'type' => 'Nhap kho',
                            'quantity' => 1,
                            'to_location' => $item->storage_location,
                            'condition_after' => 'Moi',
                            'status_after' => 'Co san',
                            'reason' => 'Nhập kho theo phiếu: ' . $receipt->receipt_number,
                            'notes' => $item->notes ?? $receipt->notes,
                            'performed_by' => Auth::id(),
                        ]);
                    }
                }
                
                // Lưu book_id để cập nhật số lượng sau
                if (!in_array($item->book_id, $processedBooks)) {
                    $processedBooks[] = $item->book_id;
                }
            }
            
            // Cập nhật số lượng cho từng sách
            foreach ($processedBooks as $bookId) {
                $totalQuantity = InventoryReceiptItem::whereHas('receipt', function($q) {
                    $q->where('status', 'approved');
                })
                ->where('book_id', $bookId)
                ->sum('quantity');
                
                $book = Book::findOrFail($bookId);
                $book->update([
                    'so_luong' => $totalQuantity
                ]);
                
                // Cập nhật hoặc tạo PurchasableBook
                $this->updatePurchasableBookFromBook($book);
            }
        } else {
            // Form cũ: xử lý như trước
            $existingInventoriesOld = Inventory::where('receipt_id', $receipt->id)->count();
            
            if ($existingInventoriesOld == 0) {
                for ($i = 0; $i < $receipt->quantity; $i++) {
                    $baseNumber = Inventory::count() + $i + 1;
                    $barcode = 'BK' . str_pad($baseNumber, 6, '0', STR_PAD_LEFT);
                    
                    $counter = 0;
                    while (Inventory::where('barcode', $barcode)->exists() && $counter < 100) {
                        $baseNumber++;
                        $barcode = 'BK' . str_pad($baseNumber, 6, '0', STR_PAD_LEFT);
                        $counter++;
                    }
                    
                    $inventory = Inventory::create([
                        'book_id' => $receipt->book_id,
                        'barcode' => $barcode,
                        'location' => $receipt->storage_location,
                        'condition' => 'Moi',
                        'status' => 'Co san',
                        'purchase_price' => $receipt->unit_price ?? 0,
                        'purchase_date' => $receipt->receipt_date,
                        'storage_type' => $receipt->storage_type,
                        'receipt_id' => $receipt->id,
                        'created_by' => $receipt->received_by,
                    ]);

                    InventoryTransaction::create([
                        'inventory_id' => $inventory->id,
                        'type' => 'Nhap kho',
                        'quantity' => 1,
                        'to_location' => $receipt->storage_location,
                        'condition_after' => 'Moi',
                        'status_after' => 'Co san',
                        'reason' => 'Nhập kho theo phiếu: ' . $receipt->receipt_number,
                        'notes' => $receipt->notes,
                        'performed_by' => Auth::id(),
                    ]);
                }
            }

            $totalQuantity = InventoryReceipt::where('book_id', $receipt->book_id)
                ->where('status', 'approved')
                ->sum('quantity');

            $book = Book::findOrFail($receipt->book_id);
            $book->update([
                'so_luong' => $totalQuantity
            ]);

            $this->updatePurchasableBookFromBook($book);
        }
    }

    /**
     * Từ chối phiếu nhập kho
     */
    public function rejectReceipt(Request $request, $id)
    {
        $receipt = InventoryReceipt::findOrFail($id);

        if ($receipt->status !== 'pending') {
            return back()->with('error', 'Phiếu nhập này đã được xử lý!');
        }

        $receipt->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'notes' => ($receipt->notes ?? '') . "\nLý do từ chối: " . ($request->reason ?? ''),
        ]);

        return back()->with('success', 'Phiếu nhập kho đã bị từ chối!');
    }

    /**
     * Hiển thị form xuất kho để trưng bày
     */
    public function createDisplayAllocation()
    {
        $books = Book::all();
        return view('admin.inventory.create-display-allocation', compact('books'));
    }

    /**
     * Lưu phân bổ trưng bày
     */
    public function storeDisplayAllocation(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity_on_display' => 'required|integer|min:1',
            'display_area' => 'required|string|max:100',
            'display_start_date' => 'required|date',
            'display_end_date' => 'nullable|date|after:display_start_date',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Kiểm tra số lượng sách có sẵn trong kho
            $availableInStock = Inventory::where('book_id', $request->book_id)
                ->where('storage_type', 'Kho')
                ->where('status', 'Co san')
                ->count();

            if ($availableInStock < $request->quantity_on_display) {
                return back()->withInput()->with('error', 
                    'Số lượng sách trong kho không đủ! Chỉ còn ' . $availableInStock . ' cuốn.');
            }

            // Lấy các sách từ kho để chuyển ra trưng bày
            $inventories = Inventory::where('book_id', $request->book_id)
                ->where('storage_type', 'Kho')
                ->where('status', 'Co san')
                ->limit($request->quantity_on_display)
                ->get();

            // Cập nhật số lượng còn lại trong kho
            $remainingInStock = $availableInStock - $request->quantity_on_display;

            // Tạo phân bổ trưng bày
            $allocation = DisplayAllocation::create([
                'book_id' => $request->book_id,
                'quantity_on_display' => $request->quantity_on_display,
                'quantity_in_stock' => $remainingInStock,
                'display_area' => $request->display_area,
                'display_start_date' => $request->display_start_date,
                'display_end_date' => $request->display_end_date,
                'allocated_by' => Auth::id(),
                'notes' => $request->notes,
            ]);

            // Chuyển các sách từ kho sang trưng bày
            foreach ($inventories as $inventory) {
                $oldLocation = $inventory->location;
                
                $inventory->update([
                    'storage_type' => 'Trung bay',
                    'location' => $request->display_area,
                ]);

                // Tạo transaction xuất kho
                InventoryTransaction::create([
                    'inventory_id' => $inventory->id,
                    'type' => 'Xuat kho',
                    'quantity' => 1,
                    'from_location' => $oldLocation,
                    'to_location' => $request->display_area,
                    'condition_before' => $inventory->condition,
                    'condition_after' => $inventory->condition,
                    'status_before' => $inventory->status,
                    'status_after' => $inventory->status,
                    'reason' => 'Xuất kho để trưng bày',
                    'notes' => $request->notes,
                    'performed_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.inventory.display-allocations')
                ->with('success', 'Phân bổ trưng bày đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Danh sách phân bổ trưng bày
     */
    public function displayAllocations(Request $request)
    {
        $query = DisplayAllocation::with(['book', 'allocator']);

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        if ($request->filled('display_area')) {
            $query->where('display_area', 'like', "%{$request->display_area}%");
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('display_end_date', '<', now()->toDateString());
            }
        }

        $allocations = $query->orderBy('created_at', 'desc')->paginate(20);
        $books = Book::all();

        return view('admin.inventory.display-allocations', compact('allocations', 'books'));
    }

    /**
     * Thu hồi sách từ trưng bày về kho
     */
    public function returnFromDisplay(Request $request, $id)
    {
        $allocation = DisplayAllocation::findOrFail($id);

        $request->validate([
            'return_location' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Lấy các sách đang trưng bày
            $inventories = Inventory::where('book_id', $allocation->book_id)
                ->where('storage_type', 'Trung bay')
                ->where('location', $allocation->display_area)
                ->where('status', 'Co san')
                ->limit($allocation->quantity_on_display)
                ->get();

            // Chuyển các sách về kho
            foreach ($inventories as $inventory) {
                $oldLocation = $inventory->location;

                $inventory->update([
                    'storage_type' => 'Kho',
                    'location' => $request->return_location,
                ]);

                // Tạo transaction nhập lại kho
                InventoryTransaction::create([
                    'inventory_id' => $inventory->id,
                    'type' => 'Nhap kho',
                    'quantity' => 1,
                    'from_location' => $oldLocation,
                    'to_location' => $request->return_location,
                    'condition_before' => $inventory->condition,
                    'condition_after' => $inventory->condition,
                    'status_before' => $inventory->status,
                    'status_after' => $inventory->status,
                    'reason' => 'Thu hồi từ trưng bày về kho',
                    'notes' => $request->notes,
                    'performed_by' => Auth::id(),
                ]);
            }

            // Cập nhật phân bổ
            $allocation->update([
                'quantity_on_display' => 0,
                'quantity_in_stock' => Inventory::where('book_id', $allocation->book_id)
                    ->where('storage_type', 'Kho')
                    ->where('status', 'Co san')
                    ->count(),
                'display_end_date' => now()->toDateString(),
            ]);

            DB::commit();

            return back()->with('success', 'Sách đã được thu hồi về kho thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Báo cáo tổng hợp kho
     */
    public function report()
    {
        // Thống kê tổng quan kho
        $totalBooksInStock = Inventory::inStock()->count();
        $availableInStock = Inventory::inStock()->where('status', 'Co san')->count();
        $borrowedFromStock = Inventory::inStock()->where('status', 'Dang muon')->count();
        $remainingInStock = $totalBooksInStock - $borrowedFromStock;
        
        // Thống kê nhập kho
        $totalImported = InventoryReceipt::where('storage_type', 'Kho')
            ->where('status', 'approved')
            ->sum('quantity');
        $totalImportedReceipts = InventoryReceipt::where('storage_type', 'Kho')
            ->where('status', 'approved')
            ->count();
        
        // Danh sách phiếu nhập kho
        $importReceipts = InventoryReceipt::with(['book', 'receiver', 'approver'])
            ->where('storage_type', 'Kho')
            ->where('status', 'approved')
            ->orderBy('receipt_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Thống kê theo từng sách - Đồng bộ từ cả quản lý sách và kho
        // Lấy tất cả sách từ bảng books (quản lý sách)
        $allBooks = Book::orderBy('ten_sach')->get()->keyBy('id');
        
        // Lấy thống kê từ inventories (kho) theo từng book_id
        // Map condition: Tot -> Moi, Trung binh -> Cu
        $inventoryStats = Inventory::inStock()
            ->select('book_id', DB::raw('count(*) as total_count'))
            ->selectRaw('sum(case when status = "Dang muon" then 1 else 0 end) as borrowed_count')
            ->selectRaw('sum(case when status = "Thanh ly" then 1 else 0 end) as sold_count')
            ->selectRaw('sum(case when status = "Mat" then 1 else 0 end) as lost_count')
            ->selectRaw('sum(case when (`condition` = "Moi" or `condition` = "Tot") and `condition` != "Hong" and status != "Hong" and status != "Mat" and status != "Thanh ly" and status != "Dang muon" then 1 else 0 end) as new_count')
            ->selectRaw('sum(case when (`condition` = "Cu" or `condition` = "Trung binh") and `condition` != "Hong" and status != "Hong" and status != "Mat" and status != "Thanh ly" and status != "Dang muon" then 1 else 0 end) as old_count')
            ->selectRaw('sum(case when `condition` = "Hong" or status = "Hong" then 1 else 0 end) as damaged_count')
            ->selectRaw('sum(case when (`condition` = "Hong" or status = "Hong") and status not in ("Dang muon", "Thanh ly", "Mat") then 1 else 0 end) as damaged_available_count')
            ->groupBy('book_id')
            ->get()
            ->keyBy('book_id');
        
        // Đồng bộ hóa: kết hợp dữ liệu từ books (quản lý sách) và inventories (kho)
        // Bước 1: Xử lý sách từ quản lý sách (books)
        $booksInStock = $allBooks->map(function($book) use ($inventoryStats) {
            $stats = $inventoryStats->get($book->id);
            
            $total = $stats ? (int)$stats->total_count : 0;
            $borrowed = $stats ? (int)$stats->borrowed_count : 0;
            $sold = $stats ? (int)$stats->sold_count : 0;
            $lost = $stats ? (int)$stats->lost_count : 0;
            $damaged = $stats ? (int)$stats->damaged_count : 0;
            $damagedAvailable = $stats ? (int)$stats->damaged_available_count : 0;
            
            // Có sẵn = Tổng - Đã mượn - Đã bán - Sách mất - Sách hỏng có sẵn
            // (Sách hỏng đang mượn/đã bán/đã mất đã được tính vào borrowed/sold/lost rồi)
            $available = max(0, $total - $borrowed - $sold - $lost - $damagedAvailable);
            
            return [
                'book' => $book,
                'total' => $total,
                'available' => $available,
                'borrowed' => $borrowed,
                'sold' => $sold,
                'lost' => $lost,
                'remaining' => max(0, $total - $borrowed),
                'new' => $stats ? (int)$stats->new_count : 0,
                'old' => $stats ? (int)$stats->old_count : 0,
                'damaged' => $damaged,
            ];
        });
        
        // Bước 2: Xử lý sách có trong kho nhưng không có trong quản lý sách (lỗi dữ liệu)
        $orphanedInventoryStats = $inventoryStats->filter(function($stat) use ($allBooks) {
            return !$allBooks->has($stat->book_id);
        });
        
        // Thêm các sách orphaned vào danh sách
        foreach ($orphanedInventoryStats as $stat) {
            $book = Book::find($stat->book_id);
            if ($book) {
                $total = (int)$stat->total_count;
                $borrowed = (int)$stat->borrowed_count;
                $sold = (int)$stat->sold_count;
                $lost = (int)$stat->lost_count;
                $damaged = (int)$stat->damaged_count;
                $damagedAvailable = (int)$stat->damaged_available_count;
                
                // Có sẵn = Tổng - Đã mượn - Đã bán - Sách mất - Sách hỏng có sẵn
                $available = max(0, $total - $borrowed - $sold - $lost - $damagedAvailable);
                
                // Nếu tìm thấy book, thêm vào danh sách
                $booksInStock->put($book->id, [
                    'book' => $book,
                    'total' => $total,
                    'available' => $available,
                    'borrowed' => $borrowed,
                    'sold' => $sold,
                    'lost' => $lost,
                    'remaining' => max(0, $total - $borrowed),
                    'new' => (int)$stat->new_count,
                    'old' => (int)$stat->old_count,
                    'damaged' => $damaged,
                ]);
            }
        }
        
        // Lọc và sắp xếp: chỉ hiển thị sách có trong kho (total > 0) và sắp xếp theo tên
        $booksInStock = $booksInStock
            ->filter(function($item) {
                return $item['total'] > 0;
            })
            ->sortBy(function($item) {
                return $item['book']->ten_sach ?? '';
            })
            ->values();
        
        // Danh sách ai đang mượn sách từ kho
        // Lấy tất cả Inventory từ kho đang mượn với BorrowItem liên kết
        // Lấy tất cả BorrowItem có inventory từ kho và đang mượn
        $borrowItemsFromStock = BorrowItem::with(['borrow.reader', 'borrow.librarian', 'book', 'inventory'])
            ->whereNotNull('inventorie_id')
            ->whereHas('inventory', function($query) {
                $query->where('storage_type', 'Kho')
                      ->where('status', 'Dang muon');
            })
            ->where(function($query) {
                // Lấy cả BorrowItem đang mượn hoặc có Borrow đang mượn
                $query->where('trang_thai', 'Dang muon')
                      ->orWhereHas('borrow', function($q) {
                          $q->where('trang_thai', 'Dang muon');
                      });
            })
            ->orderBy('ngay_muon', 'desc')
            ->get();
        
        // Lấy tất cả Inventory từ kho đang mượn
        $allBorrowedInventories = Inventory::inStock()
            ->where('status', 'Dang muon')
            ->with('book')
            ->get();
        
        // Lọc ra các Inventory không có BorrowItem hoặc BorrowItem không đang mượn
        $inventoriesWithoutBorrowItem = $allBorrowedInventories->filter(function($inventory) use ($borrowItemsFromStock) {
            // Kiểm tra xem có BorrowItem nào liên kết với inventory này không
            $hasActiveBorrowItem = $borrowItemsFromStock->contains(function($borrowItem) use ($inventory) {
                return $borrowItem->inventory && $borrowItem->inventory->id == $inventory->id;
            });
            return !$hasActiveBorrowItem;
        });
        
        // Chuyển đổi BorrowItem sang format chuẩn
        $currentBorrows = $borrowItemsFromStock->map(function($borrowItem) {
            return (object)[
                'id' => $borrowItem->borrow->id ?? null,
                'reader' => $borrowItem->borrow->reader ?? null,
                'librarian' => $borrowItem->borrow->librarian ?? null,
                'book' => $borrowItem->book ?? $borrowItem->inventory->book ?? null,
                'ngay_muon' => $borrowItem->ngay_muon,
                'ngay_hen_tra' => $borrowItem->ngay_hen_tra,
                'borrow_item' => $borrowItem,
                'inventory' => $borrowItem->inventory ?? null,
            ];
        });
        
        // Thêm các Inventory không có BorrowItem
        foreach ($inventoriesWithoutBorrowItem as $inventory) {
            $currentBorrows->push((object)[
                'id' => null,
                'reader' => null,
                'librarian' => null,
                'book' => $inventory->book,
                'ngay_muon' => null,
                'ngay_hen_tra' => null,
                'borrow_item' => null,
                'inventory' => $inventory,
            ]);
        }
        
        // Sắp xếp theo ngày mượn (giảm dần)
        $currentBorrows = $currentBorrows->sortByDesc(function($item) {
            if (!$item->ngay_muon) return 0;
            return is_string($item->ngay_muon) ? strtotime($item->ngay_muon) : $item->ngay_muon->timestamp;
        })->values();
        
        // Danh sách ai đã trả sách (gần đây)
        // Lấy cả BorrowItem có inventorie_id và không có (để hiển thị đầy đủ)
        $returnedBorrowItemsWithInventory = BorrowItem::with(['borrow.reader', 'borrow.librarian', 'book', 'inventory'])
            ->whereNotNull('inventorie_id')
            ->whereHas('inventory', function($query) {
                $query->where('storage_type', 'Kho');
            })
            ->where('trang_thai', 'Da tra')
            ->whereHas('borrow', function($query) {
                $query->where('trang_thai', 'Da tra');
            })
            ->orderBy('ngay_tra_thuc_te', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Lấy thêm các BorrowItem đã trả nhưng chưa có inventorie_id (có thể từ kho)
        // Tìm các BorrowItem đã trả có book_id trùng với Inventory từ kho
        $returnedBorrowItemsWithoutInventory = BorrowItem::with(['borrow.reader', 'borrow.librarian', 'book'])
            ->whereNull('inventorie_id')
            ->where('trang_thai', 'Da tra')
            ->whereHas('borrow', function($query) {
                $query->where('trang_thai', 'Da tra');
            })
            ->whereHas('book', function($query) {
                // Chỉ lấy sách có trong kho
                $query->whereHas('inventories', function($q) {
                    $q->where('storage_type', 'Kho');
                });
            })
            ->orderBy('ngay_tra_thuc_te', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Gộp hai collection lại
        $allReturnedBorrowItems = $returnedBorrowItemsWithInventory->concat($returnedBorrowItemsWithoutInventory);
        
        // Sắp xếp lại theo ngày trả thực tế (giảm dần) và giới hạn 100 bản ghi
        $allReturnedBorrowItems = $allReturnedBorrowItems->sortByDesc(function($item) {
            if ($item->ngay_tra_thuc_te) {
                return is_string($item->ngay_tra_thuc_te) ? strtotime($item->ngay_tra_thuc_te) : $item->ngay_tra_thuc_te->timestamp;
            }
            return $item->updated_at ? $item->updated_at->timestamp : 0;
        })->take(100)->values();
        
        // Chuyển đổi sang collection để view dễ xử lý
        $returnedBorrows = $allReturnedBorrowItems->map(function($item) {
            return (object)[
                'id' => $item->borrow->id ?? null,
                'reader' => $item->borrow->reader ?? null,
                'librarian' => $item->borrow->librarian ?? null,
                'book' => $item->book,
                'ngay_muon' => $item->ngay_muon,
                'ngay_hen_tra' => $item->ngay_hen_tra,
                'ngay_tra_thuc_te' => $item->ngay_tra_thuc_te,
                'borrow_item' => $item,
            ];
        });
        
        // Thống kê mượn/trả theo thời gian
        $borrowStats = [
            'today' => BorrowItem::whereNotNull('inventorie_id')
                ->whereHas('inventory', function($query) {
                    $query->where('storage_type', 'Kho');
                })
                ->whereHas('borrow', function($query) {
                    $query->where('trang_thai', 'Dang muon');
                })
                ->whereDate('ngay_muon', today())
                ->count(),
            'this_month' => BorrowItem::whereNotNull('inventorie_id')
                ->whereHas('inventory', function($query) {
                    $query->where('storage_type', 'Kho');
                })
                ->whereHas('borrow', function($query) {
                    $query->where('trang_thai', 'Dang muon');
                })
                ->whereMonth('ngay_muon', now()->month)
                ->whereYear('ngay_muon', now()->year)
                ->count(),
            'returned_today' => BorrowItem::whereNotNull('inventorie_id')
                ->whereHas('inventory', function($query) {
                    $query->where('storage_type', 'Kho');
                })
                ->where('trang_thai', 'Da tra')
                ->whereDate('ngay_tra_thuc_te', today())
                ->count(),
            'returned_this_month' => BorrowItem::whereNotNull('inventorie_id')
                ->whereHas('inventory', function($query) {
                    $query->where('storage_type', 'Kho');
                })
                ->where('trang_thai', 'Da tra')
                ->whereMonth('ngay_tra_thuc_te', now()->month)
                ->whereYear('ngay_tra_thuc_te', now()->year)
                ->count(),
        ];
        
        // Thống kê theo tình trạng sách
        $newBooks = Inventory::inStock()->where('condition', 'Moi')->count();
        $oldBooks = Inventory::inStock()->where('condition', 'Cu')->count();
        $damagedBooks = Inventory::inStock()->where(function($query) {
            $query->where('condition', 'Hong')
                  ->orWhere('status', 'Hong');
        })->count();
        $soldBooks = Inventory::inStock()->where('status', 'Thanh ly')->count();
        
        $stats = [
            // Tổng quan
            'total_books_in_stock' => $totalBooksInStock,
            'available_in_stock' => $availableInStock,
            'borrowed_from_stock' => $borrowedFromStock,
            'remaining_in_stock' => $remainingInStock,
            
            // Nhập kho
            'total_imported' => $totalImported,
            'total_imported_receipts' => $totalImportedReceipts,
            'import_receipts' => $importReceipts,
            
            // Chi tiết theo sách
            'books_in_stock' => $booksInStock,
            
            // Ai mượn
            'current_borrows' => $currentBorrows,
            
            // Ai trả
            'returned_borrows' => $returnedBorrows,
            
            // Thống kê mượn/trả
            'borrow_stats' => $borrowStats,
            
            // Thống kê theo tình trạng
            'new_books' => $newBooks,
            'old_books' => $oldBooks,
            'damaged_books' => $damagedBooks,
            'sold_books' => $soldBooks,
        ];

        return view('admin.inventory.report', compact('stats'));
    }

    /**
     * Đồng bộ hóa dữ liệu - Liên kết Inventory với BorrowItem
     * Tìm BorrowItem có book_id trùng với Inventory và set inventorie_id
     * Đồng bộ cả BorrowItem đang mượn và đã trả
     */
    public function syncInventoryBorrowItems(Request $request)
    {
        try {
            DB::beginTransaction();

            // Lấy tất cả Inventory từ kho đang mượn nhưng chưa có BorrowItem liên kết
            $inventoriesWithoutBorrowItem = Inventory::inStock()
                ->where('status', 'Dang muon')
                ->with('book')
                ->whereDoesntHave('borrowItems', function($query) {
                    $query->where('trang_thai', 'Dang muon');
                })
                ->get();

            // Lấy tất cả Inventory từ kho đã trả (status = 'Co san' nhưng có thể có BorrowItem đã trả chưa liên kết)
            $inventoriesReturned = Inventory::inStock()
                ->where('status', 'Co san')
                ->with('book')
                ->get();

            $syncedCount = 0;
            $syncedReturnedCount = 0;
            $skippedCount = 0;
            $resetCount = 0;

            foreach ($inventoriesWithoutBorrowItem as $inventory) {
                // Kiểm tra xem Inventory này đã được liên kết với BorrowItem nào chưa
                $existingLink = BorrowItem::where('inventorie_id', $inventory->id)->first();
                if ($existingLink) {
                    $skippedCount++;
                    continue;
                }

                // Tìm BorrowItem đang mượn có book_id trùng và chưa có inventorie_id
                $borrowItem = BorrowItem::where('book_id', $inventory->book_id)
                    ->whereNull('inventorie_id')
                    ->where('trang_thai', 'Dang muon')
                    ->whereHas('borrow', function($query) {
                        $query->where('trang_thai', 'Dang muon');
                    })
                    ->first();

                if ($borrowItem) {
                    // Liên kết BorrowItem với Inventory
                    $borrowItem->update([
                        'inventorie_id' => $inventory->id
                    ]);
                    $syncedCount++;
                } else {
                    // Nếu không tìm thấy BorrowItem, tìm Borrow đang mượn có book_id trùng
                    $borrow = Borrow::where('reader_id', '!=', null)
                        ->where('trang_thai', 'Dang muon')
                        ->whereHas('items', function($query) use ($inventory) {
                            $query->where('book_id', $inventory->book_id)
                                  ->where('trang_thai', 'Dang muon');
                        })
                        ->with('items')
                        ->first();

                    if ($borrow) {
                        // Tìm BorrowItem của Borrow này có book_id trùng và chưa có inventorie_id
                        $existingBorrowItem = $borrow->items
                            ->where('book_id', $inventory->book_id)
                            ->where('trang_thai', 'Dang muon')
                            ->whereNull('inventorie_id')
                            ->first();

                        if ($existingBorrowItem) {
                            // Liên kết với Inventory
                            $existingBorrowItem->update([
                                'inventorie_id' => $inventory->id
                            ]);
                            $syncedCount++;
                        } else {
                            // Tạo BorrowItem mới cho Borrow này
                            $newBorrowItem = BorrowItem::create([
                                'borrow_id' => $borrow->id,
                                'book_id' => $inventory->book_id,
                                'inventorie_id' => $inventory->id,
                                'trang_thai' => 'Dang muon',
                                'ngay_muon' => $borrow->ngay_muon ?? now(),
                                'ngay_hen_tra' => $borrow->ngay_hen_tra ?? now()->addDays(7),
                                'tien_thue' => 0,
                                'tien_ship' => 0,
                            ]);
                            $syncedCount++;
                        }
                    } else {
                        // Không tìm thấy Borrow, có thể Inventory này được set status = 'Dang muon' nhưng không có Borrow thực tế
                        // Đặt lại status về 'Co san' vì không có ai mượn thực sự
                        $inventory->update([
                            'status' => 'Co san'
                        ]);
                        $resetCount++;
                    }
                }
            }

            // Đồng bộ các BorrowItem đã trả chưa có inventorie_id
            foreach ($inventoriesReturned as $inventory) {
                // Kiểm tra xem Inventory này đã được liên kết với BorrowItem đã trả nào chưa
                $existingReturnedLink = BorrowItem::where('inventorie_id', $inventory->id)
                    ->where('trang_thai', 'Da tra')
                    ->first();
                
                if ($existingReturnedLink) {
                    continue; // Đã có liên kết
                }

                // Tìm BorrowItem đã trả có book_id trùng và chưa có inventorie_id
                $returnedBorrowItem = BorrowItem::where('book_id', $inventory->book_id)
                    ->whereNull('inventorie_id')
                    ->where('trang_thai', 'Da tra')
                    ->whereHas('borrow', function($query) {
                        $query->where('trang_thai', 'Da tra');
                    })
                    ->orderBy('ngay_tra_thuc_te', 'desc')
                    ->first();

                if ($returnedBorrowItem) {
                    // Liên kết BorrowItem đã trả với Inventory
                    $returnedBorrowItem->update([
                        'inventorie_id' => $inventory->id
                    ]);
                    $syncedReturnedCount++;
                }
            }

            DB::commit();

            $message = "Đồng bộ hoàn tất! ";
            $message .= "Đã liên kết: {$syncedCount} inventory đang mượn với BorrowItem. ";
            if ($syncedReturnedCount > 0) {
                $message .= "Đã liên kết: {$syncedReturnedCount} inventory đã trả với BorrowItem. ";
            }
            if ($resetCount > 0) {
                $message .= "Đã đặt lại {$resetCount} inventory về trạng thái 'Có sẵn' (không tìm thấy Borrow tương ứng). ";
            }
            if ($skippedCount > 0) {
                $message .= "Bỏ qua: {$skippedCount} inventory (đã có liên kết).";
            }

            return redirect()
                ->route('admin.inventory.report')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.inventory.report')
                ->with('error', 'Có lỗi xảy ra khi đồng bộ: ' . $e->getMessage());
        }
    }

    /**
     * Đồng bộ hóa tất cả sản phẩm từ kho lên trang chủ
     * Đảm bảo tất cả sách trong kho đều có trang_thai = 'active' để hiển thị trên trang chủ
     */
    public function syncToHomepage(Request $request)
    {
        try {
            DB::beginTransaction();

            // Lấy tất cả các book_id từ inventories (cả kho và trưng bày)
            $bookIds = Inventory::select('book_id')
                ->distinct()
                ->pluck('book_id')
                ->toArray();

            // Đếm số lượng sách cần đồng bộ
            $totalBooks = count($bookIds);
            $updatedCount = 0;
            $alreadyActiveCount = 0;

            // Cập nhật tất cả sách trong kho để có trang_thai = 'active'
            foreach ($bookIds as $bookId) {
                $book = Book::find($bookId);
                if ($book) {
                    // Chỉ cập nhật nếu sách chưa active
                    if ($book->trang_thai !== 'active') {
                        $book->update(['trang_thai' => 'active']);
                        $updatedCount++;
                    } else {
                        $alreadyActiveCount++;
                    }
                }
            }

            DB::commit();

            $message = "Đồng bộ hóa thành công! ";
            $message .= "Tổng số sách trong kho: {$totalBooks}. ";
            $message .= "Đã cập nhật: {$updatedCount} sách. ";
            $message .= "Đã active sẵn: {$alreadyActiveCount} sách.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'total_books' => $totalBooks,
                        'updated' => $updatedCount,
                        'already_active' => $alreadyActiveCount,
                    ]
                ]);
            }

            return redirect()->route('admin.inventory.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = 'Có lỗi xảy ra khi đồng bộ hóa: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Import tất cả sách từ quản lý sách vào kho
     * Tạo inventory record cho tất cả sách chưa có trong kho
     */
    public function importAllBooks(Request $request)
    {
        try {
            DB::beginTransaction();

            // Bước 1: Tạo Inventory items cho các phiếu nhập kho đã được duyệt từ quản lý sách nhưng chưa có Inventory items
            $approvedReceiptsFromBookManagement = InventoryReceipt::where('status', 'approved')
                ->where('supplier', 'Cập nhật trực tiếp từ quản lý sách')
                ->get();

            $receiptItemsCreated = 0;
            $errors = [];

            foreach ($approvedReceiptsFromBookManagement as $receipt) {
                // Kiểm tra xem phiếu đã có Inventory items chưa
                $existingInventories = Inventory::where('receipt_id', $receipt->id)->count();
                
                if ($existingInventories == 0) {
                    // Chưa có Inventory items, tạo mới
                    try {
                        for ($i = 0; $i < $receipt->quantity; $i++) {
                            $baseNumber = Inventory::count() + $receiptItemsCreated + $i + 1;
                            $barcode = 'BK' . str_pad($baseNumber, 6, '0', STR_PAD_LEFT);
                            
                            // Đảm bảo mã vạch là unique
                            $counter = 0;
                            while (Inventory::where('barcode', $barcode)->exists() && $counter < 100) {
                                $baseNumber++;
                                $barcode = 'BK' . str_pad($baseNumber, 6, '0', STR_PAD_LEFT);
                                $counter++;
                            }
                            
                            $inventory = Inventory::create([
                                'book_id' => $receipt->book_id,
                                'barcode' => $barcode,
                                'location' => $receipt->storage_location,
                                'condition' => 'Moi',
                                'status' => 'Co san',
                                'purchase_price' => $receipt->unit_price ?? 0,
                                'purchase_date' => $receipt->receipt_date,
                                'storage_type' => $receipt->storage_type,
                                'receipt_id' => $receipt->id,
                                'created_by' => $receipt->received_by,
                            ]);

                            // Tạo transaction nhập kho
                            InventoryTransaction::create([
                                'inventory_id' => $inventory->id,
                                'type' => 'Nhap kho',
                                'quantity' => 1,
                                'to_location' => $receipt->storage_location,
                                'condition_after' => 'Moi',
                                'status_after' => 'Co san',
                                'reason' => 'Nhập kho theo phiếu: ' . $receipt->receipt_number,
                                'notes' => $receipt->notes . ' (Được tạo khi import từ quản lý kho)',
                                'performed_by' => Auth::id(),
                            ]);
                        }
                        $receiptItemsCreated += $receipt->quantity;
                    } catch (\Exception $e) {
                        $errors[] = "Phiếu {$receipt->receipt_number}: " . $e->getMessage();
                    }
                }
            }

            // Bước 2: Import các sách mới chưa có trong kho
            // Lấy tất cả book_id đã có trong inventory
            $existingBookIds = Inventory::select('book_id')
                ->distinct()
                ->pluck('book_id')
                ->toArray();

            // Lấy tất cả sách chưa có trong kho
            $booksToImport = Book::whereNotIn('id', $existingBookIds)
                ->get();

            $imported = 0;
            $skipped = 0;

            foreach ($booksToImport as $book) {
                try {
                    // Tạo mã vạch tự động
                    $baseNumber = Inventory::count() + $imported + $receiptItemsCreated + 1;
                    $barcode = 'BK' . str_pad($baseNumber, 6, '0', STR_PAD_LEFT);
                    
                    // Đảm bảo mã vạch là unique
                    $counter = 0;
                    while (Inventory::where('barcode', $barcode)->exists() && $counter < 100) {
                        $baseNumber++;
                        $barcode = 'BK' . str_pad($baseNumber, 6, '0', STR_PAD_LEFT);
                        $counter++;
                    }

                    // Tạo inventory record
                    $inventory = Inventory::create([
                        'book_id' => $book->id,
                        'barcode' => $barcode,
                        'location' => 'Kho chính',
                        'storage_type' => 'Kho',
                        'condition' => 'Moi',
                        'status' => 'Co san',
                        'purchase_price' => $book->gia ?? null,
                        'purchase_date' => now(),
                        'created_by' => Auth::id(),
                        'notes' => 'Tự động import từ quản lý sách',
                    ]);

                    // Tạo transaction nhập kho
                    InventoryTransaction::create([
                        'inventory_id' => $inventory->id,
                        'type' => 'Nhap kho',
                        'quantity' => 1,
                        'to_location' => 'Kho chính',
                        'condition_after' => 'Moi',
                        'status_after' => 'Co san',
                        'reason' => 'Tự động import tất cả sách từ quản lý sách',
                        'notes' => 'Import tự động cho sách: ' . $book->ten_sach,
                        'performed_by' => Auth::id(),
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Sách '{$book->ten_sach}' (ID: {$book->id}): " . $e->getMessage();
                }
            }

            // Đếm số sách đã có trong kho (bỏ qua)
            $skipped = count($existingBookIds);

            DB::commit();

            $message = "";
            if ($receiptItemsCreated > 0) {
                $message .= "Đã tạo {$receiptItemsCreated} bản copy sách từ các phiếu nhập kho đã duyệt. ";
            }
            if ($imported > 0) {
                $message .= "Đã import thành công {$imported} sách mới vào kho! ";
            }
            if ($skipped > 0) {
                $message .= "Đã bỏ qua {$skipped} sách đã có trong kho. ";
            }
            if (empty($message)) {
                $message = "Không có sách nào cần import.";
            }
            if (!empty($errors)) {
                $message .= "Có " . count($errors) . " lỗi.";
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'receipt_items_created' => $receiptItemsCreated,
                        'imported' => $imported,
                        'skipped' => $skipped,
                        'errors' => $errors,
                    ]
                ]);
            }

            return redirect()->route('admin.inventory.index')
                ->with('success', $message)
                ->with('import_errors', $errors);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = 'Có lỗi xảy ra khi import sách: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Export inventory to Excel
     */
    public function export(Request $request)
    {
        $filename = 'inventory_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        
        return Excel::download(new InventoryExport($request), $filename);
    }

    /**
     * Import inventory from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        $fullPath = null;
        try {
            $file = $request->file('file');
            
            if (!$file || !$file->isValid()) {
                return back()->with('error', 'File không hợp lệ!');
            }
            
            // Lưu file tạm thời để đảm bảo có đường dẫn hợp lệ
            $tempPath = $file->storeAs('temp', 'import_' . time() . '_' . $file->getClientOriginalName(), 'local');
            $fullPath = storage_path('app/' . $tempPath);
            
            if (!file_exists($fullPath)) {
                return back()->with('error', 'Không thể lưu file tạm thời!');
            }
            
            // Đọc file Excel từ đường dẫn đã lưu
            $data = Excel::toArray(new \stdClass(), $fullPath);
            
            if (empty($data) || empty($data[0])) {
                // Xóa file tạm trước khi return
                if ($fullPath && file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                return back()->with('error', 'File không có dữ liệu!');
            }

            $rows = $data[0];
            $header = array_shift($rows); // Bỏ dòng header
            
            $imported = 0;
            $errors = [];

            DB::beginTransaction();
            
            foreach ($rows as $index => $row) {
                try {
                    // Giả sử format: book_id, barcode, location, condition, status, purchase_price, purchase_date
                    if (count($row) < 4) continue;

                    $bookId = $row[0] ?? null;
                    $barcode = $row[1] ?? null;
                    $location = $row[2] ?? 'Kho chính';
                    $condition = $row[3] ?? 'Moi';
                    $status = $row[4] ?? 'Co san';
                    $purchasePrice = $row[5] ?? null;
                    $purchaseDate = $row[6] ?? now();

                    if (!$bookId || !Book::find($bookId)) {
                        $errors[] = "Dòng " . ($index + 2) . ": Không tìm thấy sách với ID {$bookId}";
                        continue;
                    }

                    // Tạo mã vạch tự động nếu không có
                    if (!$barcode) {
                        $barcode = 'BK' . str_pad(Inventory::count() + 1, 6, '0', STR_PAD_LEFT);
                    }

                    // Kiểm tra mã vạch đã tồn tại
                    if (Inventory::where('barcode', $barcode)->exists()) {
                        $errors[] = "Dòng " . ($index + 2) . ": Mã vạch {$barcode} đã tồn tại";
                        continue;
                    }

                    $inventory = Inventory::create([
                        'book_id' => $bookId,
                        'barcode' => $barcode,
                        'location' => $location,
                        'condition' => $condition,
                        'status' => $status,
                        'purchase_price' => $purchasePrice,
                        'purchase_date' => $purchaseDate,
                        'storage_type' => 'Kho',
                        'created_by' => Auth::id(),
                    ]);

                    // Tạo transaction nhập kho
                    InventoryTransaction::create([
                        'inventory_id' => $inventory->id,
                        'type' => 'Nhap kho',
                        'quantity' => 1,
                        'to_location' => $location,
                        'condition_after' => $condition,
                        'status_after' => $status,
                        'reason' => 'Nhập kho từ file Excel',
                        'performed_by' => Auth::id(),
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Dòng " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Đã nhập thành công {$imported} sách vào kho!";
            if (!empty($errors)) {
                $message .= " Có " . count($errors) . " lỗi.";
                return back()->with('warning', $message)->with('import_errors', $errors);
            }

            // Xóa file tạm sau khi xử lý xong
            if ($fullPath && file_exists($fullPath)) {
                @unlink($fullPath);
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Xóa file tạm nếu có lỗi
            if ($fullPath && file_exists($fullPath)) {
                @unlink($fullPath);
            }
            
            return back()->with('error', 'Có lỗi xảy ra khi nhập file: ' . $e->getMessage());
        }
    }

    /**
     * Đồng bộ hóa dữ liệu cho 3 phần: Sách Đã Bán, Đặt Trước, Đánh Giá
     */
    public function syncData(Request $request)
    {
        try {
            DB::beginTransaction();

            $results = [
                'sold_books' => [
                    'synced' => 0,
                    'errors' => []
                ],
                // 'reservations' => [ // Đã xóa chức năng đặt trước
                //     'synced' => 0,
                //     'errors' => []
                // ],
                'reviews' => [
                    'synced' => 0,
                    'errors' => []
                ]
            ];

            // ========== 1. ĐỒNG BỘ SÁCH ĐÃ BÁN ==========
            // Tính tổng số lượng đã bán theo từng PurchasableBook
            $soldByPurchasableBook = \App\Models\OrderItem::whereHas('order', function($query) {
                $query->where('payment_status', 'paid')
                      ->whereIn('status', ['processing', 'shipped', 'delivered']);
            })
            ->select('purchasable_book_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('purchasable_book_id')
            ->get()
            ->keyBy('purchasable_book_id');

            // Cập nhật so_luong_ban cho từng PurchasableBook và đồng bộ với Inventory
            foreach ($soldByPurchasableBook as $purchasableBookId => $soldData) {
                try {
                    $purchasableBook = \App\Models\PurchasableBook::find($purchasableBookId);
                    if (!$purchasableBook) {
                        continue;
                    }

                    $totalSold = (int)$soldData->total_sold;

                    // Cập nhật so_luong_ban trong PurchasableBook
                    $purchasableBook->update(['so_luong_ban' => $totalSold]);

                    // Tìm Book tương ứng với PurchasableBook (dựa trên tên sách)
                    $book = Book::where('ten_sach', $purchasableBook->ten_sach)->first();
                    
                    if ($book) {
                        // Đếm số lượng Inventory có status 'Thanh ly'
                        $soldInventories = Inventory::where('book_id', $book->id)
                            ->where('storage_type', 'Kho')
                            ->where('status', 'Thanh ly')
                            ->count();

                        // Nếu số lượng Inventory 'Thanh ly' ít hơn số lượng đã bán, cập nhật thêm
                        if ($soldInventories < $totalSold) {
                            $needToUpdate = $totalSold - $soldInventories;
                            $availableInventories = Inventory::where('book_id', $book->id)
                                ->where('storage_type', 'Kho')
                                ->where('status', 'Co san')
                                ->limit($needToUpdate)
                                ->get();
                            
                            foreach ($availableInventories as $inventory) {
                                $inventory->update(['status' => 'Thanh ly']);
                            }
                        }

                        $results['sold_books']['synced']++;
                    }
                } catch (\Exception $e) {
                    $results['sold_books']['errors'][] = "Lỗi đồng bộ PurchasableBook #{$purchasableBookId}: " . $e->getMessage();
                }
            }

            // ========== 2. ĐỒNG BỘ ĐÁNH GIÁ ==========
            // (Đã xóa phần đồng bộ đặt trước vì chức năng đặt trước đã bị loại bỏ)
            // Đảm bảo Review đồng bộ với Book và User, cập nhật rating trung bình
            $reviews = \App\Models\Review::with(['book', 'user'])->get();

            foreach ($reviews as $review) {
                try {
                    // Kiểm tra Book có tồn tại không
                    if (!$review->book) {
                        $results['reviews']['errors'][] = "Review #{$review->id}: Book không tồn tại";
                        continue;
                    }

                    // Kiểm tra User có tồn tại không
                    if (!$review->user) {
                        $results['reviews']['errors'][] = "Review #{$review->id}: User không tồn tại";
                        continue;
                    }

                    // Kiểm tra và cập nhật is_verified dựa trên việc user đã mượn sách chưa
                    $user = $review->user;
                    $book = $review->book;
                    
                    // Kiểm tra user đã mượn sách này chưa (qua Reader)
                    $reader = \App\Models\Reader::where('email', $user->email)->first();
                    $hasBorrowed = false;
                    
                    if ($reader) {
                        $hasBorrowed = \App\Models\Borrow::where('reader_id', $reader->id)
                            ->where('book_id', $book->id)
                            ->where('trang_thai', 'Da tra')
                            ->exists();
                    }

                    // Cập nhật is_verified
                    if ($hasBorrowed && !$review->is_verified) {
                        $review->update(['is_verified' => true]);
                    }

                    // Cập nhật status nếu chưa có
                    if (!$review->status) {
                        $review->update(['status' => 'approved']);
                    }

                    $results['reviews']['synced']++;
                } catch (\Exception $e) {
                    $results['reviews']['errors'][] = "Lỗi đồng bộ Review #{$review->id}: " . $e->getMessage();
                }
            }

            // Cập nhật danh_gia_trung_binh cho tất cả Book
            $allBooks = Book::with('verifiedReviews')->get();
            foreach ($allBooks as $book) {
                $averageRating = $book->verifiedReviews()->avg('rating');
                $book->update(['danh_gia_trung_binh' => $averageRating ?? 0]);
            }

            // Cập nhật danh_gia_trung_binh cho tất cả PurchasableBook
            $allPurchasableBooks = \App\Models\PurchasableBook::all();
            foreach ($allPurchasableBooks as $purchasableBook) {
                // Tìm Book tương ứng
                $book = Book::where('ten_sach', $purchasableBook->ten_sach)->first();
                if ($book) {
                    $purchasableBook->update(['danh_gia_trung_binh' => $book->danh_gia_trung_binh ?? 0]);
                }
            }

            DB::commit();

            $message = "Đồng bộ hóa dữ liệu thành công!\n";
            $message .= "- Sách Đã Bán: {$results['sold_books']['synced']} bản ghi\n";
            $message .= "- Đánh Giá: {$results['reviews']['synced']} bản ghi";

            if (!empty($results['sold_books']['errors']) || 
                !empty($results['reviews']['errors'])) {
                $message .= "\n\nCó một số lỗi trong quá trình đồng bộ.";
            }

            return back()->with('success', $message)->with('sync_results', $results);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi đồng bộ hóa dữ liệu: ' . $e->getMessage());
        }
    }
        public function returnToStock($id)
    {
        $inventory = Inventory::findOrFail($id);

        // Cập nhật status về "có sẵn"
        $inventory->status = 'Co san'; // hoặc 'Có sẵn' tuỳ cột status
        $inventory->save();

        return back()->with('success', 'Đã hoàn kho thành công!');
    }

    /**
     * Cập nhật hoặc tạo PurchasableBook từ Book
     * Đảm bảo sản phẩm có thể bán được sau khi duyệt phiếu nhập kho
     */
    private function updatePurchasableBookFromBook(Book $book)
    {
        try {
            // Tìm PurchasableBook đã tồn tại với cùng tên sách
            $purchasableBook = PurchasableBook::where('ten_sach', $book->ten_sach)->first();
            
            // Tính số lượng tồn kho từ inventories (chỉ lấy sách có sẵn trong kho)
            $availableStockForPurchase = Inventory::where('book_id', $book->id)
                ->where('storage_type', 'Kho')
                ->where('status', 'Co san')
                ->count();
            
            // Nếu không có trong inventories, sử dụng so_luong từ bảng books
            $stockQuantity = $availableStockForPurchase > 0 ? $availableStockForPurchase : ($book->so_luong ?? 0);
            
            if ($purchasableBook) {
                // Cập nhật PurchasableBook đã tồn tại
                $purchasableBook->update([
                    'so_luong_ton' => $stockQuantity,
                    // Đồng bộ thông tin từ Book nếu cần
                    'gia' => $book->gia ?? $purchasableBook->gia,
                    'hinh_anh' => $book->hinh_anh ?? $purchasableBook->hinh_anh,
                    'mo_ta' => $book->mo_ta ?? $purchasableBook->mo_ta,
                ]);
            } else {
                // Tạo mới PurchasableBook nếu chưa có
                $book->load('publisher');
                
                PurchasableBook::create([
                    'ten_sach' => $book->ten_sach,
                    'tac_gia' => $book->tac_gia ?? 'Chưa cập nhật',
                    'mo_ta' => $book->mo_ta,
                    'hinh_anh' => $book->hinh_anh,
                    'gia' => $book->gia ?? 111000,
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
                    'danh_gia_trung_binh' => $book->danh_gia_trung_binh ?? 0,
                    'so_luot_xem' => $book->so_luot_xem ?? 0,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error updating PurchasableBook from Book', [
                'book_id' => $book->id,
                'book_name' => $book->ten_sach,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Không throw exception để không làm gián đoạn quá trình duyệt phiếu
        }
    }
}