<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Reader;
use App\Models\Borrow;
// use App\Models\Reservation; // Model đã bị xóa
use App\Models\Fine;
use App\Models\Category;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BulkOperationService
{
    /**
     * Bulk update books
     */
    public function bulkUpdateBooks($bookIds, $data)
    {
        $validFields = ['category_id', 'trang_thai'];
        $updateData = array_intersect_key($data, array_flip($validFields));
        
        if (empty($updateData)) {
            return [
                'success' => false,
                'message' => 'No valid fields to update'
            ];
        }

        try {
            DB::beginTransaction();
            
            $updatedCount = Book::whereIn('id', $bookIds)->update($updateData);
            
            // Log bulk operation
            AuditService::log('bulk_update', Book::class, [], $updateData, 
                "Bulk updated {$updatedCount} books with data: " . json_encode($updateData));
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Successfully updated {$updatedCount} books",
                'updated_count' => $updatedCount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk update books failed', [
                'book_ids' => $bookIds,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update books: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk delete books
     */
    public function bulkDeleteBooks($bookIds)
    {
        try {
            DB::beginTransaction();
            
            $books = Book::whereIn('id', $bookIds)->get();
            $deletedCount = 0;
            
            foreach ($books as $book) {
                // Check if book has active borrows
                $activeBorrows = Borrow::where('book_id', $book->id)
                    ->where('trang_thai', 'Dang muon')
                    ->count();
                
                if ($activeBorrows > 0) {
                    continue; // Skip books with active borrows
                }
                
                // Log deletion
                AuditService::logDeleted($book, "Book '{$book->ten_sach}' deleted via bulk operation");
                
                $book->delete();
                $deletedCount++;
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} books",
                'deleted_count' => $deletedCount,
                'skipped_count' => count($bookIds) - $deletedCount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk delete books failed', [
                'book_ids' => $bookIds,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to delete books: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk update readers
     */
    public function bulkUpdateReaders($readerIds, $data)
    {
        $validFields = ['trang_thai', 'faculty_id', 'department_id'];
        $updateData = array_intersect_key($data, array_flip($validFields));
        
        if (empty($updateData)) {
            return [
                'success' => false,
                'message' => 'No valid fields to update'
            ];
        }

        try {
            DB::beginTransaction();
            
            $updatedCount = Reader::whereIn('id', $readerIds)->update($updateData);
            
            // Log bulk operation
            AuditService::log('bulk_update', Reader::class, [], $updateData, 
                "Bulk updated {$updatedCount} readers with data: " . json_encode($updateData));
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Successfully updated {$updatedCount} readers",
                'updated_count' => $updatedCount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk update readers failed', [
                'reader_ids' => $readerIds,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update readers: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk return books
     */
    public function bulkReturnBooks($borrowIds)
    {
        try {
            DB::beginTransaction();
            
            $borrows = Borrow::whereIn('id', $borrowIds)
                ->where('trang_thai', 'Dang muon')
                ->get();
            
            $returnedCount = 0;
            
            foreach ($borrows as $borrow) {
                $borrow->update([
                    'trang_thai' => 'Da tra',
                    'ngay_tra_thuc_te' => now()->toDateString(),
                ]);
                
                $returnedCount++;
                
                // Log return
                AuditService::log('book_returned', $borrow, [], [], 
                    "Book returned via bulk operation");
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Successfully returned {$returnedCount} books",
                'returned_count' => $returnedCount,
                'skipped_count' => count($borrowIds) - $returnedCount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk return books failed', [
                'borrow_ids' => $borrowIds,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to return books: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk cancel reservations
     */
    public function bulkCancelReservations($reservationIds)
    {
        try {
            DB::beginTransaction();
            
            // Reservation model đã bị xóa
            $reservations = collect();
            
            $cancelledCount = 0;
            
            foreach ($reservations as $reservation) {
                $reservation->update(['status' => 'cancelled']);
                $cancelledCount++;
                
                // Log cancellation
                AuditService::log('reservation_cancelled', $reservation, [], [], 
                    "Reservation cancelled via bulk operation");
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Successfully cancelled {$cancelledCount} reservations",
                'cancelled_count' => $cancelledCount,
                'skipped_count' => count($reservationIds) - $cancelledCount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk cancel reservations failed', [
                'reservation_ids' => $reservationIds,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to cancel reservations: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk create fines
     */
    public function bulkCreateFines($borrowIds, $reason = 'Trả sách muộn')
    {
        try {
            DB::beginTransaction();
            
            $borrows = Borrow::whereIn('id', $borrowIds)
                ->where('trang_thai', 'Dang muon')
                ->where('ngay_hen_tra', '<', now()->toDateString())
                ->get();
            
            $finesCreated = 0;
            
            foreach ($borrows as $borrow) {
                $daysOverdue = Carbon::parse($borrow->ngay_hen_tra)->diffInDays(now());
                $fineAmount = $daysOverdue * 5000; // 5,000 VND per day
                
                // Check if fine already exists
                $existingFine = Fine::where('borrow_id', $borrow->id)
                    ->where('status', 'pending')
                    ->first();
                
                if (!$existingFine) {
                    Fine::create([
                        'reader_id' => $borrow->reader_id,
                        'borrow_id' => $borrow->id,
                        'amount' => $fineAmount,
                        'reason' => $reason,
                        'status' => 'pending',
                    ]);
                    
                    $finesCreated++;
                    
                    // Log fine creation
                    AuditService::log('fine_created', null, [], [], 
                        "Fine created for overdue book via bulk operation");
                }
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Successfully created {$finesCreated} fines",
                'fines_created' => $finesCreated,
                'skipped_count' => count($borrowIds) - $finesCreated
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk create fines failed', [
                'borrow_ids' => $borrowIds,
                'reason' => $reason,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to create fines: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk update categories
     */
    public function bulkUpdateCategories($categoryIds, $data)
    {
        $validFields = ['trang_thai', 'color', 'icon'];
        $updateData = array_intersect_key($data, array_flip($validFields));
        
        if (empty($updateData)) {
            return [
                'success' => false,
                'message' => 'No valid fields to update'
            ];
        }

        try {
            DB::beginTransaction();
            
            $updatedCount = Category::whereIn('id', $categoryIds)->update($updateData);
            
            // Log bulk operation
            AuditService::log('bulk_update', Category::class, [], $updateData, 
                "Bulk updated {$updatedCount} categories with data: " . json_encode($updateData));
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Successfully updated {$updatedCount} categories",
                'updated_count' => $updatedCount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk update categories failed', [
                'category_ids' => $categoryIds,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update categories: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk import books from CSV
     */
    public function bulkImportBooks($csvData)
    {
        try {
            DB::beginTransaction();
            
            $importedCount = 0;
            $errors = [];
            
            foreach ($csvData as $index => $row) {
                try {
                    $book = Book::create([
                        'ten_sach' => $row['ten_sach'],
                        'category_id' => $row['category_id'],
                        'nha_xuat_ban_id' => $row['nha_xuat_ban_id'],
                        'tac_gia' => $row['tac_gia'],
                        'nam_xuat_ban' => $row['nam_xuat_ban'],
                        'mo_ta' => $row['mo_ta'] ?? '',
                        'gia' => $row['gia'] ?? 0,
                        'trang_thai' => $row['trang_thai'] ?? 'active',
                    ]);
                    
                    $importedCount++;
                    
                    // Log import
                    AuditService::logCreated($book, "Book '{$book->ten_sach}' imported via bulk operation");
                    
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Successfully imported {$importedCount} books",
                'imported_count' => $importedCount,
                'error_count' => count($errors),
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk import books failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to import books: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get bulk operation statistics
     */
    public function getBulkOperationStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            'books' => [
                'total' => Book::count(),
                'active' => Book::where('trang_thai', 'active')->count(),
                'inactive' => Book::where('trang_thai', 'inactive')->count(),
            ],
            'readers' => [
                'total' => Reader::count(),
                'active' => Reader::where('trang_thai', 'Hoat dong')->count(),
                'suspended' => Reader::where('trang_thai', 'Tam dung')->count(),
                'blocked' => Reader::where('trang_thai', 'Khoa')->count(),
            ],
            'borrows' => [
                'active' => Borrow::where('trang_thai', 'Dang muon')->count(),
                'overdue' => Borrow::where('trang_thai', 'Dang muon')
                    ->where('ngay_hen_tra', '<', $today)->count(),
            ],
            'reservations' => [
                'pending' => 0, // Reservation model đã bị xóa
                'confirmed' => 0,
                'ready' => 0,
            ],
            'fines' => [
                'pending' => Fine::where('status', 'pending')->count(),
                'total_amount' => Fine::where('status', 'pending')->sum('amount'),
            ],
        ];
    }
}























