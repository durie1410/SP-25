<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Reader;
use App\Models\Borrow;
use App\Models\Category;
// use App\Models\Reservation; // Model đã bị xóa
use App\Models\Review;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MobileApiController extends Controller
{
    /**
     * Get mobile app dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $reader = Reader::where('user_id', $user->id)->first();

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Reader profile not found'
            ], 404);
        }

        $today = now()->toDateString();

        $data = [
            'reader' => [
                'id' => $reader->id,
                'name' => $reader->ho_ten,
                'email' => $reader->email,
                'card_number' => $reader->so_the_doc_gia,
                'status' => $reader->trang_thai,
                'expiry_date' => $reader->ngay_het_han,
                'is_expired' => $reader->ngay_het_han < $today,
            ],
            'stats' => [
                'active_borrows' => Borrow::where('reader_id', $reader->id)
                    ->where('trang_thai', 'Dang muon')->count(),
                'total_borrows' => Borrow::where('reader_id', $reader->id)->count(),
                'overdue_books' => Borrow::where('reader_id', $reader->id)
                    ->where('trang_thai', 'Dang muon')
                    ->where('ngay_hen_tra', '<', $today)->count(),
                'pending_reservations' => 0, // Reservation model đã bị xóa
                'total_fines' => \App\Models\Fine::where('reader_id', $reader->id)
                    ->where('status', 'pending')->sum('amount'),
            ],
            'recent_borrows' => $this->getRecentBorrows($reader->id),
            'upcoming_returns' => $this->getUpcomingReturns($reader->id),
            'notifications' => $this->getNotifications($reader->id),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get recent borrows
     */
    protected function getRecentBorrows($readerId, $limit = 5)
    {
        return Borrow::with(['book.category'])
            ->where('reader_id', $readerId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($borrow) {
                return [
                    'id' => $borrow->id,
                    'book_title' => $borrow->book->ten_sach,
                    'book_author' => $borrow->book->tac_gia,
                    'category' => $borrow->book->category->ten_the_loai ?? 'N/A',
                    'borrow_date' => $borrow->ngay_muon,
                    'due_date' => $borrow->ngay_hen_tra,
                    'status' => $borrow->trang_thai,
                    'is_overdue' => $borrow->isOverdue(),
                    'days_overdue' => $borrow->days_overdue,
                ];
            });
    }

    /**
     * Get upcoming returns
     */
    protected function getUpcomingReturns($readerId, $limit = 5)
    {
        $today = now()->toDateString();
        $nextWeek = now()->addWeek()->toDateString();

        return Borrow::with(['book'])
            ->where('reader_id', $readerId)
            ->where('trang_thai', 'Dang muon')
            ->whereBetween('ngay_hen_tra', [$today, $nextWeek])
            ->orderBy('ngay_hen_tra', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($borrow) {
                return [
                    'id' => $borrow->id,
                    'book_title' => $borrow->book->ten_sach,
                    'book_author' => $borrow->book->tac_gia,
                    'due_date' => $borrow->ngay_hen_tra,
                    'days_remaining' => now()->diffInDays($borrow->ngay_hen_tra, false),
                    ];
            });
    }

    /**
     * Get notifications
     */
    protected function getNotifications($readerId, $limit = 10)
    {
        $notifications = [];

        // Overdue books
        $overdueBorrows = Borrow::with(['book'])
            ->where('reader_id', $readerId)
            ->where('trang_thai', 'Dang muon')
            ->where('ngay_hen_tra', '<', now()->toDateString())
            ->get();

        foreach ($overdueBorrows as $borrow) {
            $notifications[] = [
                'type' => 'overdue',
                'title' => 'Sách quá hạn',
                'message' => "Sách '{$borrow->book->ten_sach}' đã quá hạn {$borrow->days_overdue} ngày",
                'data' => [
                    'borrow_id' => $borrow->id,
                    'book_title' => $borrow->book->ten_sach,
                    'days_overdue' => $borrow->days_overdue,
                ],
                'created_at' => $borrow->ngay_hen_tra,
                'priority' => 'high'
            ];
        }

        // Expiring reservations - Reservation model đã bị xóa
        $expiringReservations = collect();

        foreach ($expiringReservations as $reservation) {
            $notifications[] = [
                'type' => 'reservation_expiring',
                'title' => 'Đặt chỗ sắp hết hạn',
                'message' => "Đặt chỗ sách '{$reservation->book->ten_sach}' sắp hết hạn",
                'data' => [
                    'reservation_id' => $reservation->id,
                    'book_title' => $reservation->book->ten_sach,
                    'expiry_date' => $reservation->expiry_date,
                ],
                'created_at' => $reservation->expiry_date,
                'priority' => 'medium'
            ];
        }

        // Sort by created_at desc and limit
        usort($notifications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($notifications, 0, $limit);
    }

    /**
     * Search books
     */
    public function searchBooks(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->input('query');
        $categoryId = $request->input('category_id');
        $limit = $request->input('limit', 20);

        $books = Book::with(['category', 'reviews'])
            ->where('trang_thai', 'active')
            ->where(function($q) use ($query) {
                $q->where('ten_sach', 'like', "%{$query}%")
                  ->orWhere('tac_gia', 'like', "%{$query}%")
                  ->orWhere('mo_ta', 'like', "%{$query}%");
            });

        if ($categoryId) {
            $books->where('category_id', $categoryId);
        }

        $results = $books->limit($limit)->get()->map(function($book) {
            return [
                'id' => $book->id,
                'title' => $book->ten_sach,
                'author' => $book->tac_gia,
                'category' => $book->category->ten_the_loai ?? 'N/A',
                'description' => $book->mo_ta,
                'image' => $book->hinh_anh ? asset('storage/' . $book->hinh_anh) : null,
                'rating' => $book->danh_gia_trung_binh,
                'reviews_count' => $book->reviews->count(),
                'is_available' => $book->isAvailable(),
                'can_reserve' => $book->canBeReserved(),
                'published_year' => $book->nam_xuat_ban,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $results,
            'query' => $query,
            'total' => $results->count()
        ]);
    }

    /**
     * Get book details
     */
    public function getBookDetails($id): JsonResponse
    {
        $book = Book::with(['category', 'reviews.user', 'borrows'])
            ->where('trang_thai', 'active')
            ->find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }

        $data = [
            'id' => $book->id,
            'title' => $book->ten_sach,
            'author' => $book->tac_gia,
            'category' => $book->category->ten_the_loai ?? 'N/A',
            'description' => $book->mo_ta,
            'image' => $book->hinh_anh ? asset('storage/' . $book->hinh_anh) : null,
            'rating' => $book->danh_gia_trung_binh,
            'reviews_count' => $book->reviews->count(),
            'published_year' => $book->nam_xuat_ban,
            'price' => $book->gia,
            'is_available' => $book->isAvailable(),
            'can_reserve' => $book->canBeReserved(),
            'total_copies' => $book->total_copies,
            'available_copies' => $book->available_copies,
            'borrowed_copies' => $book->borrowed_copies,
            'reviews' => $book->reviews->where('is_verified', true)->take(10)->map(function($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'user_name' => $review->user->name,
                    'created_at' => $review->created_at,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get categories
     */
    public function getCategories(): JsonResponse
    {
        $categories = Category::where('trang_thai', 'active')
            ->withCount('books')
            ->orderBy('ten_the_loai')
            ->get()
            ->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->ten_the_loai,
                    'description' => $category->mo_ta,
                    'books_count' => $category->books_count,
                    'color' => $category->color,
                    'icon' => $category->icon,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get reader's borrow history
     */
    public function getBorrowHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $reader = Reader::where('user_id', $user->id)->first();

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Reader profile not found'
            ], 404);
        }

        $perPage = $request->input('per_page', 15);
        $status = $request->input('status'); // all, active, returned, overdue

        $query = Borrow::with(['book.category'])
            ->where('reader_id', $reader->id);

        if ($status) {
            switch ($status) {
                case 'active':
                    $query->where('trang_thai', 'Dang muon');
                    break;
                case 'returned':
                    $query->where('trang_thai', 'Da tra');
                    break;
                case 'overdue':
                    $query->where('trang_thai', 'Dang muon')
                          ->where('ngay_hen_tra', '<', now()->toDateString());
                    break;
            }
        }

        $borrows = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $data = $borrows->map(function($borrow) {
            return [
                'id' => $borrow->id,
                'book_title' => $borrow->book->ten_sach,
                'book_author' => $borrow->book->tac_gia,
                'category' => $borrow->book->category->ten_the_loai ?? 'N/A',
                'borrow_date' => $borrow->ngay_muon,
                'due_date' => $borrow->ngay_hen_tra,
                'return_date' => $borrow->ngay_tra_thuc_te,
                'status' => $borrow->trang_thai,
                'is_overdue' => $borrow->isOverdue(),
                'days_overdue' => $borrow->days_overdue,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $borrows->currentPage(),
                'last_page' => $borrows->lastPage(),
                'per_page' => $borrows->perPage(),
                'total' => $borrows->total(),
            ]
        ]);
    }

    /**
     * Get reader's reservations
     */
    public function getReservations(Request $request): JsonResponse
    {
        $user = $request->user();
        $reader = Reader::where('user_id', $user->id)->first();

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Reader profile not found'
            ], 404);
        }

        // Reservation model đã bị xóa
        $reservations = collect();

        return response()->json([
            'success' => true,
            'data' => $reservations
        ]);
    }

    /**
     * Get reader's fines
     */
    public function getFines(Request $request): JsonResponse
    {
        $user = $request->user();
        $reader = Reader::where('user_id', $user->id)->first();

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Reader profile not found'
            ], 404);
        }

        $fines = \App\Models\Fine::with(['borrow.book'])
            ->where('reader_id', $reader->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($fine) {
                return [
                    'id' => $fine->id,
                    'amount' => $fine->amount,
                    'reason' => $fine->reason,
                    'status' => $fine->status,
                    'created_at' => $fine->created_at,
                    'paid_at' => $fine->paid_at,
                    'book_title' => $fine->borrow ? $fine->borrow->book->ten_sach : 'N/A',
                    'borrow_id' => $fine->borrow_id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $fines,
            'total_pending' => $fines->where('status', 'pending')->sum('amount'),
            'total_paid' => $fines->where('status', 'paid')->sum('amount'),
        ]);
    }

    /**
     * Create reservation
     */
    public function createReservation(Request $request): JsonResponse
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $reader = Reader::where('user_id', $user->id)->first();

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Reader profile not found'
            ], 404);
        }

        $book = Book::findOrFail($request->book_id);

        // Reservation model đã bị xóa
        return response()->json([
            'success' => false,
            'message' => 'Reservation feature has been removed'
        ], 410);
    }

    /**
     * Cancel reservation
     */
    public function cancelReservation(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $reader = Reader::where('user_id', $user->id)->first();

        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Reader profile not found'
            ], 404);
        }

        // Reservation model đã bị xóa
        return response()->json([
            'success' => false,
            'message' => 'Reservation feature has been removed'
        ], 410);
    }
}























