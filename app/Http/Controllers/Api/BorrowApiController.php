<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\Book;
use App\Models\Reader;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class BorrowApiController extends Controller
{
    /**
     * Lấy danh sách phiếu mượn của tác giả
     */
    public function getReaderBorrows(Request $request): JsonResponse
    {
        $readerId = $request->get('reader_id');
        
        if (!$readerId) {
            return response()->json([
                'success' => false,
                'message' => 'Reader ID is required'
            ], 400);
        }

        $borrows = Borrow::where('reader_id', $readerId)
            ->with(['book', 'librarian'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($borrow) {
                return [
                    'id' => $borrow->id,
                    'book_title' => $borrow->book->ten_sach,
                    'book_author' => $borrow->book->tac_gia,
                    'book_image' => $borrow->book->hinh_anh,
                    'borrow_date' => $borrow->ngay_muon->format('d/m/Y'),
                    'due_date' => $borrow->ngay_hen_tra->format('d/m/Y'),
                    'return_date' => $borrow->ngay_tra_thuc_te ? $borrow->ngay_tra_thuc_te->format('d/m/Y') : null,
                    'status' => $borrow->trang_thai,
                    'is_overdue' => $borrow->isOverdue(),
                    'days_overdue' => $borrow->days_overdue,
                    'librarian_name' => $borrow->librarian ? $borrow->librarian->name : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $borrows
        ]);
    }

    /**
     * Lấy chi tiết phiếu mượn
     */
    public function getBorrowDetail($id): JsonResponse
    {
        $borrow = Borrow::with(['book', 'reader', 'librarian', 'fines'])
            ->find($id);

        if (!$borrow) {
            return response()->json([
                'success' => false,
                'message' => 'Borrow record not found'
            ], 404);
        }

        $data = [
            'id' => $borrow->id,
            'reader' => [
                'id' => $borrow->reader->id,
                'name' => $borrow->reader->ho_ten,
                'card_number' => $borrow->reader->so_the_doc_gia,
                'email' => $borrow->reader->email,
            ],
            'book' => [
                'id' => $borrow->book->id,
                'title' => $borrow->book->ten_sach,
                'author' => $borrow->book->tac_gia,
                'year' => $borrow->book->nam_xuat_ban,
                'image' => $borrow->book->hinh_anh,
                'description' => $borrow->book->mo_ta,
            ],
            'borrow_date' => $borrow->ngay_muon->format('d/m/Y'),
            'due_date' => $borrow->ngay_hen_tra->format('d/m/Y'),
            'return_date' => $borrow->ngay_tra_thuc_te ? $borrow->ngay_tra_thuc_te->format('d/m/Y') : null,
            'status' => $borrow->trang_thai,
            'is_overdue' => $borrow->isOverdue(),
            'days_overdue' => $borrow->days_overdue,
            'librarian' => $borrow->librarian ? [
                'id' => $borrow->librarian->id,
                'name' => $borrow->librarian->name,
            ] : null,
            'notes' => $borrow->ghi_chu,
            'fines' => $borrow->fines->map(function($fine) {
                return [
                    'id' => $fine->id,
                    'type' => $fine->loai_phat,
                    'amount' => $fine->so_tien,
                    'status' => $fine->status,
                    'due_date' => $fine->ngay_den_han ? Carbon::parse($fine->ngay_den_han)->format('d/m/Y') : null,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Lấy danh sách sách có thể mượn
     */
    public function getAvailableBooks(Request $request): JsonResponse
    {
        $query = Book::whereDoesntHave('borrows', function($q) {
            $q->where('trang_thai', 'Dang muon');
        });

        // Tìm kiếm theo tên sách hoặc tác giả
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('ten_sach', 'like', "%{$search}%")
                  ->orWhere('tac_gia', 'like', "%{$search}%");
            });
        }

        // Lọc theo danh mục
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        $books = $query->with('category')
            ->paginate(20)
            ->map(function($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->ten_sach,
                    'author' => $book->tac_gia,
                    'year' => $book->nam_xuat_ban,
                    'image' => $book->hinh_anh,
                    'description' => $book->mo_ta,
                    'category' => $book->category ? [
                        'id' => $book->category->id,
                        'name' => $book->category->ten_danh_muc,
                    ] : null,
                    'average_rating' => $book->average_rating,
                    'reviews_count' => $book->reviews_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    /**
     * Tạo phiếu mượn mới
     */
    public function createBorrow(Request $request): JsonResponse
    {
        $request->validate([
            'reader_id' => 'required|exists:readers,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'nullable|date',
            'due_date' => 'nullable|date|after:borrow_date',
            'notes' => 'nullable|string',
        ]);

        // Kiểm tra sách đã được mượn chưa
        $existingBorrow = Borrow::where('book_id', $request->book_id)
            ->where('trang_thai', 'Dang muon')
            ->first();

        if ($existingBorrow) {
            return response()->json([
                'success' => false,
                'message' => 'Book is already borrowed'
            ], 400);
        }

        $borrowData = [
            'reader_id' => $request->reader_id,
            'book_id' => $request->book_id,
            'librarian_id' => auth()->id(),
            'ngay_muon' => $request->borrow_date ?: now()->toDateString(),
            'ngay_hen_tra' => $request->due_date ?: now()->addDays(14)->toDateString(),
            'ghi_chu' => $request->notes,
        ];

        $borrow = Borrow::create($borrowData);

        return response()->json([
            'success' => true,
            'message' => 'Borrow created successfully',
            'data' => [
                'id' => $borrow->id,
                'borrow_date' => $borrow->ngay_muon->format('d/m/Y'),
                'due_date' => $borrow->ngay_hen_tra->format('d/m/Y'),
            ]
        ], 201);
    }

    /**
     * Trả sách
     */
    public function returnBook(Request $request, $id): JsonResponse
    {
        $borrow = Borrow::find($id);

        if (!$borrow) {
            return response()->json([
                'success' => false,
                'message' => 'Borrow record not found'
            ], 404);
        }

        if (!$borrow->canReturn()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot return this book'
            ], 400);
        }

        $borrow->update([
            'trang_thai' => 'Da tra',
            'ngay_tra_thuc_te' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Book returned successfully',
            'data' => [
                'return_date' => $borrow->ngay_tra_thuc_te->format('d/m/Y'),
                'was_overdue' => $borrow->isOverdue(),
                'days_overdue' => $borrow->days_overdue,
            ]
        ]);
    }

    /**
     * Lấy thống kê mượn sách của tác giả
     */
    public function getReaderStats(Request $request): JsonResponse
    {
        $readerId = $request->get('reader_id');
        
        if (!$readerId) {
            return response()->json([
                'success' => false,
                'message' => 'Reader ID is required'
            ], 400);
        }

        $stats = [
            'total_borrows' => Borrow::where('reader_id', $readerId)->count(),
            'active_borrows' => Borrow::where('reader_id', $readerId)->where('trang_thai', 'Dang muon')->count(),
            'returned_books' => Borrow::where('reader_id', $readerId)->where('trang_thai', 'Da tra')->count(),
            'overdue_books' => Borrow::where('reader_id', $readerId)
                ->where('trang_thai', 'Dang muon')
                ->where('ngay_hen_tra', '<', now()->toDateString())
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}

