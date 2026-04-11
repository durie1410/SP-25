<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BulkOperationService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BulkOperationController extends Controller
{
    protected $bulkOperationService;

    public function __construct(BulkOperationService $bulkOperationService)
    {
        $this->bulkOperationService = $bulkOperationService;
    }

    /**
     * Display bulk operations page
     */
    public function index()
    {
        $stats = $this->bulkOperationService->getBulkOperationStats();
        return view('admin.bulk-operations.index', compact('stats'));
    }

    /**
     * Bulk update books
     */
    public function bulkUpdateBooks(Request $request): JsonResponse
    {
        $request->validate([
            'book_ids' => 'required|array|min:1',
            'book_ids.*' => 'exists:books,id',
            'category_id' => 'nullable|exists:categories,id',
            'trang_thai' => 'nullable|in:active,inactive',
        ]);

        $bookIds = $request->input('book_ids');
        $data = $request->only(['category_id', 'trang_thai']);
        $data = array_filter($data); // Remove null values

        $result = $this->bulkOperationService->bulkUpdateBooks($bookIds, $data);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Bulk delete books
     */
    public function bulkDeleteBooks(Request $request): JsonResponse
    {
        $request->validate([
            'book_ids' => 'required|array|min:1',
            'book_ids.*' => 'exists:books,id',
        ]);

        $bookIds = $request->input('book_ids');
        $result = $this->bulkOperationService->bulkDeleteBooks($bookIds);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Bulk update readers
     */
    public function bulkUpdateReaders(Request $request): JsonResponse
    {
        $request->validate([
            'reader_ids' => 'required|array|min:1',
            'reader_ids.*' => 'exists:readers,id',
            'trang_thai' => 'nullable|in:Hoat dong,Tam dung,Khoa',
            'faculty_id' => 'nullable|exists:faculties,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $readerIds = $request->input('reader_ids');
        $data = $request->only(['trang_thai', 'faculty_id', 'department_id']);
        $data = array_filter($data); // Remove null values

        $result = $this->bulkOperationService->bulkUpdateReaders($readerIds, $data);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Bulk return books
     */
    public function bulkReturnBooks(Request $request): JsonResponse
    {
        $request->validate([
            'borrow_ids' => 'required|array|min:1',
            'borrow_ids.*' => 'exists:borrows,id',
        ]);

        $borrowIds = $request->input('borrow_ids');
        $result = $this->bulkOperationService->bulkReturnBooks($borrowIds);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Bulk cancel reservations
     */
    public function bulkCancelReservations(Request $request): JsonResponse
    {
        $request->validate([
            'reservation_ids' => 'required|array|min:1',
            'reservation_ids.*' => 'exists:reservations,id',
        ]);

        $reservationIds = $request->input('reservation_ids');
        $result = $this->bulkOperationService->bulkCancelReservations($reservationIds);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Bulk create fines
     */
    public function bulkCreateFines(Request $request): JsonResponse
    {
        $request->validate([
            'borrow_ids' => 'required|array|min:1',
            'borrow_ids.*' => 'exists:borrows,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $borrowIds = $request->input('borrow_ids');
        $reason = $request->input('reason', 'Trả sách muộn');

        $result = $this->bulkOperationService->bulkCreateFines($borrowIds, $reason);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Bulk update categories
     */
    public function bulkUpdateCategories(Request $request): JsonResponse
    {
        $request->validate([
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'trang_thai' => 'nullable|in:active,inactive',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
        ]);

        $categoryIds = $request->input('category_ids');
        $data = $request->only(['trang_thai', 'color', 'icon']);
        $data = array_filter($data); // Remove null values

        $result = $this->bulkOperationService->bulkUpdateCategories($categoryIds, $data);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Bulk import books
     */
    public function bulkImportBooks(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $csvData = $this->parseCsvFile($file);

        if (empty($csvData)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid data found in CSV file'
            ], 400);
        }

        $result = $this->bulkOperationService->bulkImportBooks($csvData);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Get bulk operation statistics
     */
    public function getStats(): JsonResponse
    {
        $stats = $this->bulkOperationService->getBulkOperationStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Parse CSV file
     */
    protected function parseCsvFile($file)
    {
        $csvData = [];
        $handle = fopen($file->getPathname(), 'r');
        
        if ($handle !== false) {
            $headers = fgetcsv($handle); // Get headers
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    $csvData[] = array_combine($headers, $row);
                }
            }
            
            fclose($handle);
        }

        return $csvData;
    }

    /**
     * Export books to CSV
     */
    public function exportBooks(Request $request)
    {
        $request->validate([
            'book_ids' => 'nullable|array',
            'book_ids.*' => 'exists:books,id',
            'format' => 'nullable|in:csv,xlsx',
        ]);

        $bookIds = $request->input('book_ids');
        $format = $request->input('format', 'csv');

        $query = \App\Models\Book::with(['category', 'publisher']);
        
        if ($bookIds) {
            $query->whereIn('id', $bookIds);
        }

        $books = $query->get();

        if ($format === 'csv') {
            return $this->exportBooksToCsv($books);
        } else {
            return $this->exportBooksToExcel($books);
        }
    }

    /**
     * Export books to CSV
     */
    protected function exportBooksToCsv($books)
    {
        $filename = 'books_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($books) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Tên sách', 'Tác giả', 'Thể loại', 'Nhà xuất bản',
                'Năm xuất bản', 'Trạng thái', 'Giá', 'Mô tả'
            ]);
            
            // Data
            foreach ($books as $book) {
                fputcsv($file, [
                    $book->id,
                    $book->ten_sach,
                    $book->tac_gia,
                    $book->category->ten_the_loai ?? 'N/A',
                    $book->publisher->ten_nha_xuat_ban ?? 'N/A',
                    $book->nam_xuat_ban,
                    $book->trang_thai,
                    $book->gia,
                    $book->mo_ta
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export books to Excel
     */
    protected function exportBooksToExcel($books)
    {
        // This would typically use Laravel Excel package
        // For now, we'll return a simple response
        return response()->json([
            'success' => false,
            'message' => 'Excel export not implemented yet'
        ], 501);
    }
}























