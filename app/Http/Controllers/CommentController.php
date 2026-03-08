<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowItem;
use App\Models\Comment;
use App\Models\Review;
use App\Models\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'review_id' => 'required|exists:reviews,id',
            'content' => 'required|string|max:500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'review_id' => $request->review_id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        return back()->with('success', 'Bình luận đã được thêm thành công!');
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        
        // Chỉ cho phép user sở hữu comment hoặc admin chỉnh sửa
        if ($comment->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return back()->with('success', 'Bình luận đã được cập nhật thành công!');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        
        // Chỉ cho phép user sở hữu comment hoặc admin xóa
        if ($comment->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Bình luận đã được xóa thành công!');
    }

    public function like($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->increment('likes_count');

        return response()->json([
            'status' => 'success',
            'likes_count' => $comment->likes_count
        ]);
    }

    public function approve($id)
    {
        $comment = Comment::findOrFail($id);
        
        // Chỉ admin mới có thể duyệt comment
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $comment->update(['is_approved' => true]);

        return back()->with('success', 'Bình luận đã được duyệt thành công!');
    }

    public function reject($id)
    {
        $comment = Comment::findOrFail($id);
        
        // Chỉ admin mới có thể từ chối comment
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $comment->update(['is_approved' => false]);

        return back()->with('success', 'Bình luận đã bị từ chối!');
    }

    // API endpoint để lấy comments của một review
    public function getReviewComments($reviewId)
    {
        $comments = Comment::with(['user', 'replies.user'])
            ->where('review_id', $reviewId)
            ->whereNull('parent_id')
            ->approved()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $comments
        ]);
    }

    // API endpoint để tạo comment
    public function createComment(Request $request)
    {
        $request->validate([
            'review_id' => 'required|exists:reviews,id',
            'content' => 'required|string|max:500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'review_id' => $request->review_id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Bình luận đã được thêm thành công!',
            'data' => $comment->load('user')
        ], 201);
    }

    // Public method để tạo comment cho book (tự động tạo review nếu chưa có)
    public function storePublic(Request $request, $bookId)
    {
        $request->validate([
            'borrow_item_id' => 'nullable|integer|exists:borrow_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|max:1500',
        ]);

        $book = Book::findOrFail($bookId);

        if (!$book->hasCompletedBorrowByUser(Auth::id())) {
            return back()
                ->withErrors(['content' => 'Bạn chỉ có thể bình luận và đánh giá sau khi đã thuê xong sách này.'])
                ->withInput();
        }

        $readerId = Reader::where('user_id', Auth::id())->value('id');

        $completedBorrowItems = BorrowItem::query()
            ->where('book_id', $bookId)
            ->whereHas('borrow', function ($query) use ($readerId) {
                $query->where('reader_id', $readerId)
                    ->where(function ($statusQuery) {
                        $statusQuery->where('trang_thai', 'Da tra')
                            ->orWhereIn('trang_thai_chi_tiet', [
                                \App\Models\Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                                \App\Models\Borrow::STATUS_HOAN_TAT_DON_HANG,
                            ]);
                    });
            })
            ->orderByDesc('ngay_tra_thuc_te')
            ->orderByDesc('updated_at')
            ->get();

        $borrowItem = null;

        if ($request->filled('borrow_item_id')) {
            $borrowItem = $completedBorrowItems->firstWhere('id', (int) $request->borrow_item_id);
        }

        if (!$borrowItem) {
            $borrowItem = $completedBorrowItems->first();
        }

        if (!$borrowItem) {
            return back()
                ->withErrors(['content' => 'Không tìm thấy lượt thuê hợp lệ để gắn với đánh giá này.'])
                ->withInput();
        }

        $review = Review::firstOrNew(
            [
                'borrow_item_id' => $borrowItem->id,
            ]
        );

        $isUpdating = $review->exists;

        if ($isUpdating) {
            if ((int) $review->user_id !== (int) Auth::id()) {
                abort(403);
            }

            if (!$review->canBeEditedBy(Auth::id())) {
                return back()
                    ->withErrors([
                        'content' => 'Đánh giá này đã hết thời gian chỉnh sửa. Bạn chỉ có thể sửa trong ' . Review::EDIT_WINDOW_HOURS . ' giờ kể từ lúc gửi.',
                    ])
                    ->withInput();
            }
        }

        $review->fill([
            'book_id' => $bookId,
            'user_id' => Auth::id(),
            'rating' => (int) $request->rating,
            'comment' => trim($request->content),
            'is_verified' => true,
            'status' => 'approved',
        ]);

        $review->save();

        $book->refreshAverageRating();

        return back()->with('success', $isUpdating
            ? 'Đánh giá của bạn đã được cập nhật thành công!'
            : 'Đánh giá của bạn đã được gửi thành công!');
    }
}