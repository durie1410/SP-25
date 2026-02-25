<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Document;
use App\Models\Reader;
use App\Models\Inventory;
use App\Services\CacheService;
use Illuminate\Http\Request;

class PublicBookController extends Controller
{
    public function show($id, Request $request)
    {
        $book = Book::with([
            'category',
            'publisher',
            'reviews' => function($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
            'reviews.comments.user',
            'inventories'
        ])->findOrFail($id);

        // Tăng lượt xem
        $book->increment('so_luot_xem');

        // Kiểm tra mode từ query parameter (mặc định là 'borrow' nếu không có)
        $mode = $request->get('mode', 'borrow'); // 'buy' hoặc 'borrow'
        
        // Lấy thông tin độc giả hiện tại nếu ở chế độ mượn
        $currentReader = null;
        if ($mode === 'borrow' && auth()->check()) {
            $currentReader = Reader::where('user_id', auth()->id())->first();
        }

        // Lấy tất cả book_id từ kho (cả kho và trưng bày) - CHỈ hiển thị sách đã có trong kho
        $bookIdsFromInventory = Inventory::select('book_id')
            ->distinct()
            ->pluck('book_id')
            ->toArray();

        // Lấy sách liên quan (cùng thể loại) - CHỈ lấy sách có trong kho
        $relatedBooks = collect();
        if (!empty($bookIdsFromInventory)) {
            $relatedBooks = Book::where('category_id', $book->category_id)
                ->where('id', '!=', $book->id)
                ->where('trang_thai', 'active')
                ->whereIn('id', $bookIdsFromInventory)
                ->with(['category', 'publisher'])
                ->limit(6)
                ->get();
        }

        // Lấy sách mua nhiều nhất (top selling) - CHỈ lấy sách có trong kho
        $top_selling_books = collect();
        if (!empty($bookIdsFromInventory)) {
            $top_selling_books = Book::where('trang_thai', 'active')
                ->where('id', '!=', $book->id)
                ->whereIn('id', $bookIdsFromInventory)
                ->with(['category', 'publisher'])
                ->orderBy('so_luong_ban', 'desc')
                ->orderBy('so_luot_xem', 'desc')
                ->limit(5)
                ->get();
        }

        // Lấy sách xem nhiều nhất (most viewed) - CHỈ lấy sách có trong kho
        $most_viewed_books = collect();
        if (!empty($bookIdsFromInventory)) {
            $most_viewed_books = Book::where('trang_thai', 'active')
                ->where('id', '!=', $book->id)
                ->whereIn('id', $bookIdsFromInventory)
                ->with(['category', 'publisher'])
                ->orderBy('so_luot_xem', 'desc')
                ->limit(5)
                ->get();
        }

        // Lấy sách cùng chủ đề (cùng category) - CHỈ lấy sách có trong kho
        $same_topic_books = collect();
        if (!empty($bookIdsFromInventory)) {
            $same_topic_books = Book::where('category_id', $book->category_id)
                ->where('id', '!=', $book->id)
                ->where('trang_thai', 'active')
                ->whereIn('id', $bookIdsFromInventory)
                ->with(['category', 'publisher'])
                ->orderBy('so_luong_ban', 'desc')
                ->orderBy('so_luot_xem', 'desc')
                ->limit(12)
                ->get();
            
            // Nếu không đủ sách cùng chủ đề, lấy thêm sách nổi bật khác có trong kho
            if ($same_topic_books->count() < 12) {
                $remaining = 12 - $same_topic_books->count();
                $additional_books = Book::where('id', '!=', $book->id)
                    ->where('trang_thai', 'active')
                    ->whereIn('id', $bookIdsFromInventory)
                    ->whereNotIn('id', $same_topic_books->pluck('id'))
                    ->with(['category', 'publisher'])
                    ->orderBy('so_luong_ban', 'desc')
                    ->orderBy('so_luot_xem', 'desc')
                    ->limit($remaining)
                    ->get();
                
                $same_topic_books = $same_topic_books->merge($additional_books);
            }
        }

        // Lấy thông tin tác giả
        $author = null;
        if ($book->tac_gia) {
            $author = \App\Models\Author::where('ten_tac_gia', 'like', '%' . $book->tac_gia . '%')->first();
        }

        // Thống kê sách
        // Số lượng tồn kho có thể mua: sách trong kho (storage_type = 'Kho') và có sẵn (status = 'Co san')
        $availableStockForPurchase = $book->inventories()
            ->where('storage_type', 'Kho')
            ->where('status', 'Co san')
            ->count();
        
        // Nếu không có trong inventories, sử dụng so_luong từ bảng books
        $stockQuantity = $availableStockForPurchase > 0 ? $availableStockForPurchase : ($book->so_luong ?? 0);
        
        $stats = [
            'total_reviews' => $book->reviews()->count(),
            'average_rating' => $book->reviews()->avg('rating') ?? 0,
            'total_copies' => $book->inventories()->count(),
            'available_copies' => $book->inventories()->where('status', 'Co san')->count(),
            'borrowed_copies' => $book->inventories()->where('status', 'Dang muon')->count(),
            'stock_quantity' => $stockQuantity, // Số lượng tồn kho có thể mua
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

        // Kiểm tra KYC status của user
        $kycStatus = 'unverified';
        if (auth()->check()) {
            $verification = \Illuminate\Support\Facades\DB::table('user_verifications')
                ->where('user_id', auth()->id())
                ->first();
            if ($verification && $verification->verified_status === 'approved') {
                $kycStatus = 'verified';
            }
        } else {
            $kycStatus = 'guest';
        }

        return view('books.show', compact(
            'book', 
            'relatedBooks', 
            'stats', 
            'isFavorited', 
            'userReview',
            'top_selling_books',
            'most_viewed_books',
            'same_topic_books',
            'author',
            'mode',
            'currentReader',
            'kycStatus'
        ));
    }

    public function index(Request $request)
    {
        // Lấy tất cả book_id từ kho (cả kho và trưng bày) - CHỈ hiển thị sách đã có trong kho
        $bookIdsFromInventory = Inventory::select('book_id')
            ->distinct()
            ->pluck('book_id')
            ->toArray();

        $query = Book::with('category')
            ->whereIn('id', $bookIdsFromInventory);

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

        // Sắp xếp
        $sort = $request->get('sort', 'new');
        switch ($sort) {
            case 'name-asc':
                $query->orderBy('ten_sach', 'asc');
                break;
            case 'name-desc':
                $query->orderBy('ten_sach', 'desc');
                break;
            case 'new':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Lấy danh sách sách sau khi lọc với phân trang
        $books = !empty($bookIdsFromInventory)
            ? $query->paginate(12)->appends($request->query())
            : collect()->paginate(12);

        // Lấy danh sách thể loại để hiển thị dropdown (cached)
        $categories = CacheService::getActiveCategories();

        return view('books.public', compact('books', 'categories'));
    }

    public function showDiemSach($id)
    {
        $book = Book::with([
            'category',
            'publisher'
        ])->findOrFail($id);

        // Tăng lượt xem
        $book->increment('so_luot_xem');

        // Lấy danh sách categories cho sidebar
        $categories = Category::withCount('books')->orderBy('ten_the_loai')->get();

        // Lấy tất cả book_id từ kho (cả kho và trưng bày) - CHỈ hiển thị sách đã có trong kho
        $bookIdsFromInventory = Inventory::select('book_id')
            ->distinct()
            ->pluck('book_id')
            ->toArray();

        // Lấy tin nổi bật (có thể lấy từ bảng news hoặc books mới nhất) - CHỈ lấy sách có trong kho
        $hotNews = collect();
        if (!empty($bookIdsFromInventory)) {
            $hotNews = Book::where('trang_thai', 'active')
                ->where('id', '!=', $book->id)
                ->whereIn('id', $bookIdsFromInventory)
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get();
        }

        return view('books.diem-sach', compact('book', 'categories', 'hotNews'));
    }

    public function showTinTuc($id)
    {
        $document = Document::findOrFail($id);

        // Lấy danh sách categories cho sidebar
        $categories = Category::withCount('books')->orderBy('ten_the_loai')->get();

        // Lấy tin nổi bật (các document khác)
        $hotNews = Document::where('id', '!=', $document->id)
            ->orderBy('published_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        return view('books.tin-tuc', compact('document', 'categories', 'hotNews'));
    }
}
