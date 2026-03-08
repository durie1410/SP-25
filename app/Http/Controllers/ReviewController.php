<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Models\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['book', 'user']);

        // Lọc theo sách
        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        // Lọc theo rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Lọc theo trạng thái xác minh
        if ($request->filled('verified')) {
            $query->where('is_verified', $request->verified);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'borrow_item_id' => 'nullable|integer|exists:borrow_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $book = Book::findOrFail($request->book_id);
        $hasBorrowed = $book->hasCompletedBorrowByUser(Auth::id());

        if (!$hasBorrowed) {
            return back()->withErrors(['rating' => 'Chỉ người đã thuê và hoàn tất đơn mượn mới có thể đánh giá sách này.']);
        }

        $readerId = Reader::where('user_id', Auth::id())->value('id');

        $eligibleBorrowItems = BorrowItem::query()
            ->where('book_id', $request->book_id)
            ->whereHas('borrow', function ($query) use ($readerId) {
                $query->where('reader_id', $readerId)
                    ->where(function ($statusQuery) {
                        $statusQuery->where('trang_thai', 'Da tra')
                            ->orWhereIn('trang_thai_chi_tiet', [
                                Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                                Borrow::STATUS_HOAN_TAT_DON_HANG,
                            ]);
                    });
            })
            ->orderByDesc('ngay_tra_thuc_te')
            ->orderByDesc('updated_at')
            ->get();

        $borrowItem = $request->filled('borrow_item_id')
            ? $eligibleBorrowItems->firstWhere('id', (int) $request->borrow_item_id)
            : $eligibleBorrowItems->first();

        if (!$borrowItem) {
            return back()->withErrors(['rating' => 'Không tìm thấy lượt thuê hợp lệ để đánh giá.']);
        }

        $existingReview = Review::where('borrow_item_id', $borrowItem->id)->first();

        if ($existingReview) {
            return back()->withErrors(['rating' => 'Bạn đã đánh giá lượt thuê này rồi.']);
        }

        Review::create([
            'book_id' => $request->book_id,
            'user_id' => Auth::id(),
            'borrow_item_id' => $borrowItem->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_verified' => $hasBorrowed,
            'status' => 'approved',
        ]);

        $book->refreshAverageRating();

        return back()->with('success', 'Đánh giá của bạn đã được gửi thành công!');
    }

    public function show($id)
    {
        $review = Review::with(['book', 'user', 'comments.user'])
            ->findOrFail($id);

        return view('admin.reviews.show', compact('review'));
    }

    public function edit($id)
    {
        $review = Review::findOrFail($id);
        
        // Chỉ cho phép user sở hữu review hoặc admin chỉnh sửa
        if ($review->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403);
        }

        return view('admin.reviews.edit', compact('review'));
    }

    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        
        // Chỉ cho phép user sở hữu review hoặc admin chỉnh sửa
        if ($review->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('admin.reviews.show', $review->id)
            ->with('success', 'Đánh giá đã được cập nhật thành công!');
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        
        // Chỉ cho phép user sở hữu review hoặc admin xóa
        if ($review->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'Đánh giá đã được xóa thành công!');
    }

    // API endpoint để lấy đánh giá của một sách
    public function getBookReviews($bookId)
    {
        $reviews = Review::with(['user', 'comments.user'])
            ->where('book_id', $bookId)
            ->verified()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ]);
    }

    // API endpoint để tạo đánh giá
    public function createReview(Request $request)
    {
        // Debug: Log request data
        Log::info('Review request data:', $request->all());
        
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'borrow_item_id' => 'nullable|integer|exists:borrow_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $book = Book::findOrFail($request->book_id);
        $hasBorrowed = $book->hasCompletedBorrowByUser(Auth::id());

        if (!$hasBorrowed) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chỉ người đã thuê và hoàn tất đơn mượn mới có thể đánh giá sách này.'
            ], 422);
        }

        $readerId = Reader::where('user_id', Auth::id())->value('id');

        $eligibleBorrowItems = BorrowItem::query()
            ->where('book_id', $request->book_id)
            ->whereHas('borrow', function ($query) use ($readerId) {
                $query->where('reader_id', $readerId)
                    ->where(function ($statusQuery) {
                        $statusQuery->where('trang_thai', 'Da tra')
                            ->orWhereIn('trang_thai_chi_tiet', [
                                Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                                Borrow::STATUS_HOAN_TAT_DON_HANG,
                            ]);
                    });
            })
            ->orderByDesc('ngay_tra_thuc_te')
            ->orderByDesc('updated_at')
            ->get();

        $borrowItem = $request->filled('borrow_item_id')
            ? $eligibleBorrowItems->firstWhere('id', (int) $request->borrow_item_id)
            : $eligibleBorrowItems->first();

        if (!$borrowItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy lượt thuê hợp lệ để đánh giá.'
            ], 422);
        }

        $existingReview = Review::where('borrow_item_id', $borrowItem->id)->first();

        if ($existingReview) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn đã đánh giá lượt thuê này rồi.'
            ], 400);
        }

        $review = Review::create([
            'book_id' => $request->book_id,
            'user_id' => Auth::id(),
            'borrow_item_id' => $borrowItem->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_verified' => $hasBorrowed,
            'status' => 'approved',
        ]);

        $book->refreshAverageRating();

        // Debug: Log created review
        Log::info('Created review:', $review->toArray());

        return response()->json([
            'status' => 'success',
            'message' => 'Đánh giá đã được gửi thành công!',
            'data' => $review->load('user')
        ], 201);
    }
}