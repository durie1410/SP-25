<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BookApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Lấy book_id đã có trong inventory (đã duyệt phiếu nhập kho)
        $bookIdsFromInventory = Inventory::select('book_id')
            ->distinct()
            ->pluck('book_id')
            ->toArray();

        $query = Book::with('category')
            ->whereIn('id', $bookIdsFromInventory);

        // Tìm kiếm theo tên sách hoặc tác giả
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ten_sach', 'like', "%{$search}%")
                  ->orWhere('tac_gia', 'like', "%{$search}%");
            });
        }

        // Lọc theo thể loại
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Lọc theo năm xuất bản
        if ($request->filled('year')) {
            $query->where('nam_xuat_ban', $request->year);
        }

        // Phân trang
        $perPage = $request->get('per_page', 20);
        $books = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $books->items(),
            'pagination' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
                'has_more' => $books->hasMorePages(),
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        // Lấy book_id đã có trong inventory (đã duyệt phiếu nhập kho)
        $bookIdsFromInventory = Inventory::select('book_id')
            ->distinct()
            ->pluck('book_id')
            ->toArray();

        // Kiểm tra sách có trong inventory không
        if (!in_array($id, $bookIdsFromInventory)) {
            return response()->json([
                'success' => false,
                'message' => 'Sách này hiện không có sẵn trong kho.'
            ], 404);
        }

        $book = Book::with(['category', 'inventories'])->find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sách'
            ], 404);
        }

        // Tính số lượng có sẵn
        $availableCount = $book->inventories->where('status', 'Co san')->count();
        $totalCount = $book->inventories->count();

        return response()->json([
            'success' => true,
            'book' => [
                'id' => $book->id,
                'ten_sach' => $book->ten_sach,
                'tac_gia' => $book->tac_gia,
                'mo_ta' => $book->mo_ta,
                'hinh_anh' => $book->hinh_anh,
                'category' => $book->category,
                'available_count' => $availableCount,
                'total_count' => $totalCount,
                'nam_xuat_ban' => $book->nam_xuat_ban,
                'so_trang' => $book->so_trang,
                'nha_xuat_ban' => $book->nha_xuat_ban,
            ]
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::withCount('books')->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function featured(): JsonResponse
    {
        // Lấy book_id đã có trong inventory (đã duyệt phiếu nhập kho)
        $bookIdsFromInventory = Inventory::select('book_id')
            ->distinct()
            ->pluck('book_id')
            ->toArray();

        // Lấy sách mới nhất - CHỈ sách đã có trong kho
        $newBooks = Book::with('category')
            ->whereIn('id', $bookIdsFromInventory)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Lấy sách được mượn nhiều nhất - CHỈ sách đã có trong kho
        $popularBooks = Book::with('category')
            ->whereIn('id', $bookIdsFromInventory)
            ->withCount('borrows')
            ->orderBy('borrows_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'new_books' => $newBooks,
                'popular_books' => $popularBooks,
            ]
        ]);
    }

    public function toggleFavorite(Request $request): JsonResponse
    {
        $request->validate([
            'book_id' => 'required|exists:books,id'
        ]);

        $bookId = $request->book_id;
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng chức năng yêu thích.'
            ], 401);
        }

        // Kiểm tra xem user đã yêu thích sách này chưa
        $existingFavorite = Favorite::where('book_id', $bookId)
            ->where('user_id', $userId)
            ->first();

        if ($existingFavorite) {
            // Nếu đã yêu thích thì bỏ yêu thích
            $existingFavorite->delete();
            $message = 'Đã bỏ yêu thích sách';
            $isFavorited = false;
        } else {
            // Nếu chưa yêu thích thì thêm vào yêu thích
            Favorite::create([
                'book_id' => $bookId,
                'user_id' => $userId
            ]);
            $message = 'Đã thêm vào danh sách yêu thích';
            $isFavorited = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $isFavorited,
            'favorites_count' => Favorite::where('book_id', $bookId)->count(),
        ]);
    }
}