<?php

namespace App\Http\Controllers;
 use App\Models\Book;
use App\Services\AdvancedSearchService;
use Illuminate\Http\Request;
use App\Models\Inventory;

use Illuminate\Support\Facades\Auth;

class AdvancedSearchController extends Controller
{
    protected $searchService;

    public function __construct(AdvancedSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function index()
    {
        $popularQueries = $this->searchService->getPopularQueries();
        $searchStats = $this->searchService->getSearchStats();
        
        return view('admin.search.index', compact('popularQueries', 'searchStats'));
    }

    public function searchBooks(Request $request)
    {
        $query = $request->get('q', '');
        $filters = $request->except(['q', 'page']);
        
        $books = $this->searchService->searchBooks($query, $filters);
        $categories = \App\Models\Category::all();

        return view('admin.search.books', compact('books', 'query', 'filters', 'categories'));
    }

    public function searchReaders(Request $request)
    {
        $query = $request->get('q', '');
        $filters = $request->except(['q', 'page']);
        
        $readers = $this->searchService->searchReaders($query, $filters);

        return view('admin.search.readers', compact('readers', 'query', 'filters'));
    }

    public function searchBorrows(Request $request)
    {
        $query = $request->get('q', '');
        $filters = $request->except(['q', 'page']);
        
        $borrows = $this->searchService->searchBorrows($query, $filters);
        $readers = \App\Models\Reader::all();
        $books = \App\Models\Book::all();

        return view('admin.search.borrows', compact('borrows', 'query', 'filters', 'readers', 'books'));
    }

    public function globalSearch(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $results = $this->searchService->globalSearch($query);

        return response()->json($results);
    }

    public function getSuggestions(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'books');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $suggestions = $this->searchService->getSuggestions($query, $type);

        return response()->json($suggestions);
    }

    public function searchStats(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        $stats = $this->searchService->getSearchStats($fromDate, $toDate);

        return response()->json($stats);
    }

    public function popularQueries(Request $request)
    {
        $type = $request->get('type');
        $limit = $request->get('limit', 10);
        
        $queries = $this->searchService->getPopularQueries($type, $limit);

        return response()->json($queries);
    }

    // API endpoints
    public function apiSearchBooks(Request $request)
    {
        $query = $request->get('q', '');
        $filters = $request->except(['q', 'page']);
        
        $books = $this->searchService->searchBooks($query, $filters);

        return response()->json([
            'status' => 'success',
            'data' => $books,
            'query' => $query,
            'filters' => $filters
        ]);
    }

    public function apiSearchReaders(Request $request)
    {
        $query = $request->get('q', '');
        $filters = $request->except(['q', 'page']);
        
        $readers = $this->searchService->searchReaders($query, $filters);

        return response()->json([
            'status' => 'success',
            'data' => $readers,
            'query' => $query,
            'filters' => $filters
        ]);
    }

    public function apiGlobalSearch(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Query is required'
            ], 400);
        }

        $results = $this->searchService->globalSearch($query);

        return response()->json([
            'status' => 'success',
            'data' => $results,
            'query' => $query
        ]);
    }

    public function apiSuggestions(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'books');
        
        if (empty($query)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Query is required'
            ], 400);
        }

        $suggestions = $this->searchService->getSuggestions($query, $type);

        return response()->json([
            'status' => 'success',
            'data' => $suggestions,
            'query' => $query,
            'type' => $type
        ]);
    }

    // Simple autocomplete endpoints for borrow form
    public function autocompleteReaders(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $readers = \App\Models\Reader::where('trang_thai', 'Hoat dong')
            ->where(function($q) use ($query) {
                $q->where('ho_ten', 'like', "%{$query}%")
                  ->orWhere('so_the_doc_gia', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'ho_ten', 'so_the_doc_gia', 'email', 'so_dien_thoai', 'tinh_thanh', 'huyen', 'xa', 'so_nha']);

        return response()->json($readers);
    }

  

 // Autocomplete sách với tổng số lượng khả dụng
public function autocompleteBooks(Request $request)
{
    $query = $request->get('q', '');
    if (strlen($query) < 2) return response()->json([]);

    // Lấy các book có ít nhất 1 inventory
    $books = Book::where('ten_sach', 'like', "%{$query}%")
        ->orWhere('tac_gia', 'like', "%{$query}%")
        ->limit(100)
        ->get();

    $result = $books->map(function ($book) {
        // Tổng số lượng khả dụng
        $availableQty = Inventory::where('book_id', $book->id)
            ->where('status', 'Co san')
            ->count(); // hoặc sum('so_luong') nếu inventory có nhiều cuốn

        return [
            'id' => $book->id,
            'ten_sach' => $book->ten_sach,
            'tac_gia' => $book->tac_gia,
            'nam_xuat_ban' => $book->nam_xuat_ban,
            'gia' => $book->gia,
            'loai_sach' => $book->loai_sach,
            'so_luong' => $availableQty,
        ];
    });

    return response()->json($result);
}

// Lấy inventory của sách
public function getBookInventories($bookId)
{
    $inventories = Inventory::where('book_id', $bookId)
        ->get(['id', 'location', 'condition', 'status']);

    return response()->json($inventories);
}



}