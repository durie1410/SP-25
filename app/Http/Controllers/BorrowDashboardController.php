<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\Book;
use App\Models\Reader;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BorrowDashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateRange = $request->get('date_range', '30'); // Mặc định 30 ngày
        $startDate = Carbon::now()->subDays($dateRange);
        $endDate = Carbon::now();

        // Thống kê tổng quan
        $stats = $this->getOverviewStats($startDate, $endDate);
        
        // Thống kê theo thời gian
        $timeStats = $this->getTimeStats($startDate, $endDate);
        
        // Top sách được mượn nhiều nhất
        $topBooks = $this->getTopBooks($startDate, $endDate);
        
        // Top độc giả mượn nhiều nhất
        $topReaders = $this->getTopReaders($startDate, $endDate);
        
        // Thống kê theo trạng thái
        $statusStats = $this->getStatusStats();
        
        // Thống kê quá hạn
        $overdueStats = $this->getOverdueStats();

        return view('admin.borrows.dashboard', compact(
            'stats', 
            'timeStats', 
            'topBooks', 
            'topReaders', 
            'statusStats', 
            'overdueStats',
            'dateRange'
        ));
    }

    private function getOverviewStats($startDate, $endDate)
    {
        return [
            'total_borrows' => Borrow::whereBetween('created_at', [$startDate, $endDate])->count(),
            'active_borrows' => Borrow::where('trang_thai', 'Dang muon')->count(),
            'returned_books' => Borrow::where('trang_thai', 'Da tra')
                ->whereBetween('ngay_tra_thuc_te', [$startDate, $endDate])
                ->count(),
            'overdue_books' => Borrow::where('trang_thai', 'Dang muon')
                ->where('ngay_hen_tra', '<', now()->toDateString())
                ->count(),
            'total_readers' => Reader::where('trang_thai', 'Hoat dong')->count(),
            'total_books' => Book::count(),
        ];
    }

    private function getTimeStats($startDate, $endDate)
    {
        $borrows = Borrow::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $returns = Borrow::whereBetween('ngay_tra_thuc_te', [$startDate, $endDate])
            ->whereNotNull('ngay_tra_thuc_te')
            ->selectRaw('DATE(ngay_tra_thuc_te) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'borrows' => $borrows,
            'returns' => $returns,
        ];
    }

    private function getTopBooks($startDate, $endDate, $limit = 10)
    {
        return Borrow::whereBetween('created_at', [$startDate, $endDate])
            ->with('book')
            ->selectRaw('book_id, COUNT(*) as borrow_count')
            ->groupBy('book_id')
            ->orderBy('borrow_count', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'book' => $item->book,
                    'count' => $item->borrow_count
                ];
            });
    }

    private function getTopReaders($startDate, $endDate, $limit = 10)
    {
        return Borrow::whereBetween('created_at', [$startDate, $endDate])
            ->with('reader')
            ->selectRaw('reader_id, COUNT(*) as borrow_count')
            ->groupBy('reader_id')
            ->orderBy('borrow_count', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'reader' => $item->reader,
                    'count' => $item->borrow_count
                ];
            });
    }

    private function getStatusStats()
    {
        return [
            'dang_muon' => Borrow::where('trang_thai', 'Dang muon')->count(),
            'da_tra' => Borrow::where('trang_thai', 'Da tra')->count(),
            'qua_han' => Borrow::where('trang_thai', 'Qua han')->count(),
            'mat_sach' => Borrow::where('trang_thai', 'Mat sach')->count(),
        ];
    }

    private function getOverdueStats()
    {
        $overdueBorrows = Borrow::where('trang_thai', 'Dang muon')
            ->where('ngay_hen_tra', '<', now()->toDateString())
            ->with(['reader', 'book'])
            ->get();

        return [
            'count' => $overdueBorrows->count(),
            'borrows' => $overdueBorrows->take(10), // Chỉ lấy 10 phiếu đầu tiên
            'total_days' => $overdueBorrows->sum(function($borrow) {
                return now()->diffInDays($borrow->ngay_hen_tra);
            }),
        ];
    }

    public function export(Request $request)
    {
        $dateRange = $request->get('date_range', '30');
        $startDate = Carbon::now()->subDays($dateRange);
        $endDate = Carbon::now();

        $borrows = Borrow::whereBetween('created_at', [$startDate, $endDate])
            ->with(['reader', 'book', 'librarian'])
            ->get();

        $filename = "borrow_report_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($borrows) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'ID', 'Độc giả', 'Sách', 'Ngày mượn', 'Hạn trả',
                'Ngày trả', 'Trạng thái', 'Thủ thư'
            ]);

            // Data
            foreach ($borrows as $borrow) {
                fputcsv($file, [
                    $borrow->id,
                    $borrow->reader->ho_ten,
                    $borrow->book->ten_sach,
                    $borrow->ngay_muon->format('d/m/Y'),
                    $borrow->ngay_hen_tra->format('d/m/Y'),
                    $borrow->ngay_tra_thuc_te ? $borrow->ngay_tra_thuc_te->format('d/m/Y') : '',
                    $borrow->trang_thai,
                    $borrow->librarian ? $borrow->librarian->name : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}


